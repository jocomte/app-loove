<?php
// backend/controllers/PhotoController.php

class PhotoController {
    private $photoModel;
    private $uploadsDir;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->photoModel = new PhotoModel();
        $this->uploadsDir = __DIR__ . '/../uploads/';

        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }

    public function uploadPhoto() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        if (!isset($_FILES['photo'])) {
            http_response_code(400);
            echo json_encode(["error" => "Aucune photo fournie."]);
            return;
        }

        $file = $_FILES['photo'];
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["error" => "Erreur lors de l'upload de la photo."]);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(["error" => "La photo doit faire moins de 5 Mo."]);
            return;
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!isset($allowedTypes[$mimeType])) {
            http_response_code(400);
            echo json_encode(["error" => "Format d'image non supporté. Utilisez JPG, PNG ou WEBP."]);
            return;
        }

        $extension = $allowedTypes[$mimeType];
        $filename = uniqid('photo_') . '.' . $extension;
        $destination = $this->uploadsDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            http_response_code(500);
            echo json_encode(["error" => "Impossible d'enregistrer la photo."]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $photoUrl = "/app-loove/backend/uploads/{$filename}";
        $currentPhotos = $this->photoModel->getPhotosByUser($userId);
        $isPrimary = count($currentPhotos) === 0;

        if (!$this->photoModel->savePhoto($userId, $photoUrl, $isPrimary)) {
            unlink($destination);
            http_response_code(500);
            echo json_encode(["error" => "Impossible d'enregistrer la photo en base." ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Photo enregistrée avec succès.",
            "photo_url" => $photoUrl,
        ]);
    }

    public function listPhotos() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $photos = $this->photoModel->getPhotosByUser($_SESSION['user_id']);

        http_response_code(200);
        echo json_encode(["photos" => $photos]);
    }

    public function deletePhoto() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['photo_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID de photo manquant."]);
            return;
        }

        $photoId = intval($data['photo_id']);
        $userId = $_SESSION['user_id'];

        try {
            $photo = $this->photoModel->getPhotoById($photoId);

            if (!$photo) {
                http_response_code(444);
                echo json_encode(["error" => "Photo introuvable."]);
                return;
            }

            if (intval($photo['user_id']) !== $userId) {
                http_response_code(403);
                echo json_encode(["error" => "Vous n'avez pas l'autorisation de supprimer cette photo."]);
                return;
            }

            // Suppression du fichier physique
            $filename = basename($photo['url']);
            $filepath = $this->uploadsDir . $filename;
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Suppression en BDD
            $this->photoModel->deletePhoto($photoId, $userId);

            // Si c'était la photo principale, on en définit une autre si possible
            if (intval($photo['is_main']) === 1) {
                $remaining = $this->photoModel->getPhotosByUser($userId);
                if (count($remaining) > 0) {
                    $this->photoModel->setMainPhoto($remaining[0]['id'], $userId);
                }
            }

            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Photo supprimée avec succès."]);

        } catch (Exception $e) {
            Logger::log("Erreur suppression photo : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de la suppression."]);
        }
    }

    public function setMainPhoto() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['photo_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID de photo manquant."]);
            return;
        }

        $photoId = intval($data['photo_id']);
        $userId = $_SESSION['user_id'];

        try {
            $photo = $this->photoModel->getPhotoById($photoId);

            if (!$photo) {
                http_response_code(404);
                echo json_encode(["error" => "Photo introuvable."]);
                return;
            }

            if (intval($photo['user_id']) !== $userId) {
                http_response_code(403);
                echo json_encode(["error" => "Vous n'avez pas l'autorisation de modifier cette photo."]);
                return;
            }

            $this->photoModel->setMainPhoto($photoId, $userId);

            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Photo principale mise à jour avec succès."]);

        } catch (Exception $e) {
            Logger::log("Erreur définition photo principale : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de la mise à jour."]);
        }
    }
}
