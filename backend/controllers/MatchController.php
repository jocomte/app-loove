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
}