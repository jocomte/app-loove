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

        // 4. Génération du code de vérification à 6 chiffres
        $verificationCode = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $data['verification_code'] = $verificationCode;

        // 5. Tentative de création
        try {
            if ($this->userModel->create($data)) {
                Logger::log("[EMAIL VERIFICATION] Code généré pour " . $data['email'] . " : " . $verificationCode . " (Mode Démo / Soutenance)", "INFO");

                http_response_code(200);
                echo json_encode([
                    "message" => "Inscription réussie ! Un code de vérification à 6 chiffres a été généré.",
                    "requires_verification" => true,
                    "email" => $data['email'],
                    "dev_code" => $verificationCode
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Une erreur est survenue lors de l'inscription."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur lors de l'inscription : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de la création du compte."]);
        }
    }

    public function verifyEmail() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['email']) || empty($data['code'])) {
            http_response_code(400);
            echo json_encode(["error" => "Email et code de vérification requis."]);
            return;
        }

        $user = $this->userModel->verifyEmailCode($data['email'], $data['code']);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];

            Logger::log("Adresse email vérifiée et session ouverte pour : " . $data['email'], "INFO");

            http_response_code(200);
            echo json_encode([
                "message" => "Email vérifié avec succès ! Bienvenue.",
                "user" => [
                    "id" => $user['id'],
                    "firstname" => $user['firstname']
                ]
            ]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Code de vérification invalide."]);
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

        if (isset($user['is_verified']) && $user['is_verified'] == 0) {
            http_response_code(403);
            echo json_encode(["error" => "Veuillez vérifier votre adresse email avec le code reçu avant de vous connecter."]);
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
/**
     * Charge les données de l'utilisateur connecté
     */
    public function getProfile() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        try {
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            http_response_code(200);
            echo json_encode(["user" => $user]);
        } catch (Exception $e) {
            Logger::log("Erreur chargement profil : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur."]);
        }
    }

    /**
     * Modifie les données de l'utilisateur connecté
     */
    public function editProfile() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['relationship_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Champs obligatoires manquants."]);
            return;
        }

        try {
            if ($this->userModel->updateProfile($_SESSION['user_id'], $data)) {
                Logger::log("Profil mis à jour pour l'utilisateur ID : " . $_SESSION['user_id'], "INFO");
                http_response_code(200);
                echo json_encode(["message" => "Profil mis à jour avec succès !"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Impossible de mettre à jour le profil."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur modification profil : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur interne serveur."]);
        }
    }

    public function updateLocation() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorisé."]);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            http_response_code(400);
            echo json_encode(["error" => "Latitude et longitude sont requises."]);
            return;
        }

        $latitude = filter_var($data['latitude'], FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($data['longitude'], FILTER_VALIDATE_FLOAT);

        if ($latitude === false || $longitude === false) {
            http_response_code(400);
            echo json_encode(["error" => "Latitude ou longitude invalide."]);
            return;
        }

        try {
            if ($this->userModel->updateLocation($_SESSION['user_id'], $latitude, $longitude)) {
                Logger::log("Localisation mise à jour pour l'utilisateur ID : " . $_SESSION['user_id'], "INFO");
                http_response_code(200);
                echo json_encode(["message" => "Localisation enregistrée."]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Impossible de mettre à jour la localisation."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur mise à jour localisation : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur serveur lors de la mise à jour de la localisation."]);
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
            );
        }

        session_destroy();
        http_response_code(200);
        echo json_encode(["message" => "Déconnexion réussie."]);
    }

    /**
     * Active le statut premium (appelé après paiement simulé)
     */
    public function upgradePremium() {
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
            if ($this->userModel->updatePremium($userId, 1)) {
                Logger::log("Utilisateur ID $userId a acheté l'offre Premium", "INFO");
                http_response_code(200);
                echo json_encode(["message" => "Félicitations ! Vous êtes maintenant membre Premium."]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Impossible d'activer l'abonnement Premium."]);
            }
        } catch (Exception $e) {
            Logger::log("Erreur achat premium : " . $e->getMessage(), "ERROR");
            http_response_code(500);
            echo json_encode(["error" => "Erreur interne serveur."]);
        }
    }
}