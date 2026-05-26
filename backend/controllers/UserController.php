<?php
// backend/controllers/UserController.php

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        // Récupération des données JSON envoyées dans le corps de la requête HTTP
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // 1. Validation de la présence des champs obligatoires
        $requiredFields = ['lastname', 'firstname', 'email', 'password', 'birthdate', 'gender', 'orientation', 'relationship_type'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "Le champ '$field' est obligatoire."]);
                return;
            }
        }

        // 2. Vérification du format de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Le format de l'adresse email est invalide."]);
            return;
        }

        // 3. Vérification de l'unicité de l'email
        if ($this->userModel->emailExists($data['email'])) {
            http_response_code(409); // Conflit
            echo json_encode(["error" => "Cette adresse email est déjà utilisée."]);
            return;
        }

        // 4. Tentative de création
        try {
            if ($this->userModel->create($data)) {
                http_response_code(200); // Created
                echo json_encode(["message" => "Inscription réussie !"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Une erreur est survenue lors de l'inscription."]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
        }
    }
    
public function login() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Email et mot de passe requis."]);
        return;
    }

    $user = $this->userModel->getUserByEmail($data['email']);

    // Connexion Réussie
    if ($user && password_verify($data['password'], $user['password'])) {
        
        if (!$user['is_active']) {
            // Log de sécurité : tentative sur un compte banni
            Logger::log("Tentative de connexion sur un compte désactivé : " . $data['email'], "SECURITY");
            
            http_response_code(403);
            echo json_encode(["error" => "Votre compte a été désactivé par la modération."]);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];

        // Log d'information standard
        Logger::log("Utilisateur connecté avec succès : ID " . $user['id'] . " (" . $data['email'] . ")", "INFO");

        http_response_code(200);
        echo json_encode([
            "message" => "Connexion réussie !",
            "user" => [
                "id" => $user['id'],
                "firstname" => $user['firstname']
            ]
        ]);
    } else {
        // Log d'avertissement de sécurité : mot de passe ou email faux
        Logger::log("Échec d'authentification pour l'email : " . $data['email'], "WARN");

        http_response_code(401);
        echo json_encode(["error" => "Identifiants incorrects."]);
    }
}
}