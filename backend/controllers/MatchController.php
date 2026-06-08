<?php
// backend/controllers/MatchController.php

class MatchController {
    private $matchModel;

    public function __construct() {
        // Sécurité : On vérifie que l'utilisateur est bien connecté en session PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->matchModel = new MatchModel();
    }

    public function getNextProfile() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé. Veuillez vous connecter."]);
            return;
        }

        // Récupération des coordonnées envoyées en GET
        $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
        $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

        try {
            $profile = $this->matchModel->findTargets($_SESSION['user_id'], $lat, $lng);
            
            http_response_code(200);
            echo json_encode(["profile" => $profile ? $profile : null]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement profil : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }
    public function handleInteraction() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        // Récupération des données JSON envoyées par le Fetch
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['target_id']) || empty($data['type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes."]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $targetId = intval($data['target_id']);
        $type = $data['type']; // 'like' ou 'pass'

        try {
            // 1. Enregistrement du choix en BDD
            $this->matchModel->saveInteraction($userId, $targetId, $type);
            Logger::log("L'utilisateur ID $userId a mis un [$type] sur l'ID $targetId", "INFO");

            // 2. Si c'est un Like, on vérifie s'il y a match réciproque
            $isMatch = false;
            if ($type === 'like') {
                $isMatch = $this->matchModel->checkMatch($userId, $targetId);
                if ($isMatch) {
                    $this->matchModel->createMatch($userId, $targetId);
                    Logger::log("MATCH détecté entre $userId et $targetId !", "INFO");
                }
            }

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "isMatch" => $isMatch,
                "message" => $isMatch ? "C'est un Match ! ❤️" : "Interaction enregistrée."
            ]);

        } catch (Exception $e) {
            Logger::log("Erreur lors de l'interaction : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors du traitement."]);
        }
    }
}