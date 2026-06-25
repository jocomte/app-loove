<?php
// backend/controllers/VisitController.php

class VisitController {
    private $visitModel;
    private $userModel;

    public function __construct() {
        $this->visitModel = new VisitModel();
        $this->userModel = new User();
    }

    /**
     * Enregistre une visite sur un profil
     */
    public function logVisit() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['visited_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID du profil visité manquant."]);
            return;
        }

        $visitorId = $_SESSION['user_id'];
        $visitedId = intval($data['visited_id']);

        try {
            $this->visitModel->logVisit($visitorId, $visitedId);
            http_response_code(200);
            echo json_encode(["message" => "Visite enregistrée."]);
        } catch (Exception $e) {
            // Échoue silencieusement pour l'utilisateur car c'est une action de fond
            Logger::log("Erreur enregistrement visite : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }

    /**
     * Récupère la liste des visiteurs (accessible en clair uniquement par les Premium)
     */
    public function getVisits() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $user = $this->userModel->getUserById($userId);
            $visits = $this->visitModel->getVisitsForUser($userId);

            if ($user && $user['is_premium'] == 1) {
                // Utilisateur premium : on renvoie la liste complète en clair
                http_response_code(200);
                echo json_encode([
                    "is_premium" => true,
                    "visits" => $visits
                ]);
            } else {
                // Utilisateur standard : on renvoie la liste floutée/partielle (anonymisée)
                $anonymizedVisits = [];
                foreach ($visits as $visit) {
                    $anonymizedVisits[] = [
                        "id" => $visit['id'],
                        "viewed_at" => $visit['viewed_at'],
                        // On ne donne pas l'identité pour inciter à payer
                        "firstname" => "Profil secret",
                        "lastname" => "👤",
                        "visitor_photo" => null
                    ];
                }

                http_response_code(200);
                echo json_encode([
                    "is_premium" => false,
                    "visits" => $anonymizedVisits
                ]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur récupération visites : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur interne serveur."]);
        }
    }
}
