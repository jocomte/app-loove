<?php
// backend/controllers/ReportController.php

class ReportController {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    /**
     * Permet à un utilisateur de signaler un autre utilisateur
     */
    public function submitReport() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Veuillez vous connecter pour signaler ce profil."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['reported_id']) || empty($data['reason'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données manquantes (ID signalé ou motif)."]);
            return;
        }

        $reporterId = $_SESSION['user_id'];
        $reportedId = intval($data['reported_id']);
        $reason = $data['reason'];

        if ($reporterId === $reportedId) {
            http_response_code(400);
            echo json_encode(["error" => "Vous ne pouvez pas vous signaler vous-même."]);
            return;
        }

        try {
            if ($this->reportModel->createReport($reporterId, $reportedId, $reason)) {
                Logger::log("Utilisateur ID $reporterId a signalé l'utilisateur ID $reportedId pour : $reason", "WARN");
                http_response_code(200);
                echo json_encode(["message" => "Signalement enregistré avec succès. Merci de nous aider à maintenir la communauté sûre !"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Impossible d'enregistrer le signalement."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur enregistrement signalement : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de la soumission du signalement."]);
        }
    }
}
