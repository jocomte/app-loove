<?php
// backend/controllers/MessageController.php

class MessageController {
require_once __DIR__ . '/../models/User.php';
    private $messageModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->messageModel = new MessageModel();
    }

    public function sendMessage() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['receiver_id']) || empty($data['content'])) {
            http_response_code(400);
            echo json_encode(["error" => "Données incomplètes."]);
            return;
        }

        $senderId = $_SESSION['user_id'];
        $receiverId = intval($data['receiver_id']);
        $content = trim($data['content']);

        if ($senderId === $receiverId) {
            http_response_code(400);
            echo json_encode(["error" => "Impossible d'envoyer un message à vous-même."]);
            return;
        }

        if ($content === "") {
            http_response_code(400);
            echo json_encode(["error" => "Le message ne peut pas être vide."]);
            return;
        }

        // Vérifier si l'utilisateur est premium
        $userModel = new User();
        $user = $userModel->getUserById($senderId);
        $isPremium = $user && isset($user['is_premium']) ? $user['is_premium'] : 0;

        $matchId = $this->messageModel->getMatchId($senderId, $receiverId);
        if (!$matchId) {
            if ($isPremium) {
                $result = $this->messageModel->sendPremiumMessage($senderId, $receiverId, $content);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(["success" => true, "message" => "Message premium envoyé avec succès."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Erreur lors de l'envoi du message premium."]);
                }
                return;
            }
            http_response_code(400);
            echo json_encode(["error" => "Vous ne pouvez envoyer des messages qu'à un match existant."]);
            return;
        }

        // Flux normal pour les utilisateurs non premium
        try {
            $result = $this->messageModel->sendMessage($senderId, $receiverId, $content);
            if ($result) {
                http_response_code(200);
                echo json_encode(["success" => true, "message" => "Message envoyé avec succès."]);
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Impossible d'envoyer le message."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur envoi message : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de l'envoi du message."]);
        }
    }

    public function getInbox() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        try {
            $conversations = $this->messageModel->getInbox($_SESSION['user_id']);
            http_response_code(200);
            echo json_encode(["conversations" => $conversations]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement boîte de réception : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors du chargement des conversations."]);
        }
    }

    public function getConversation() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        if (empty($_GET['target_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID de conversation manquant."]);
            return;
        }

        $targetId = intval($_GET['target_id']);
        $userId = $_SESSION['user_id'];

        try {
            $messages = $this->messageModel->getConversation($userId, $targetId);
            $this->messageModel->markAsRead($userId, $targetId);
            http_response_code(200);
            echo json_encode(["messages" => $messages]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement conversation : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors du chargement de la conversation."]);
        }
    }
}
