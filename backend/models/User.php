<?php
// backend/models/User.php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crée un nouvel utilisateur dans la base de données (non vérifié au départ)
     */
    public function create($data) {
        $sql = "INSERT INTO users (lastname, firstname, email, password, birthdate, gender, orientation, relationship_type, is_verified, verification_code) 
                VALUES (:lastname, :firstname, :email, :password, :birthdate, :gender, :orientation, :relationship_type, 0, :verification_code)";
        
        $stmt = $this->db->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        return $stmt->execute([
            ':lastname'          => htmlspecialchars(strip_tags($data['lastname'])),
            ':firstname'         => htmlspecialchars(strip_tags($data['firstname'])),
            ':email'             => filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? $data['email'] : null,
            ':password'          => $hashedPassword,
            ':birthdate'         => $data['birthdate'],
            ':gender'            => $data['gender'],
            ':orientation'       => $data['orientation'],
            ':relationship_type' => $data['relationship_type'],
            ':verification_code' => $data['verification_code'] ?? null
        ]);
    }

    /**
     * Vérifie si un email existe déjà dans la base
     */
    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Récupère un utilisateur par son email pour vérifier son mot de passe
     */
    public function getUserByEmail($email) {
        $sql = "SELECT id, firstname, lastname, password, is_premium, is_active, is_verified, verification_code FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Valide le code de vérification email et active le compte
     */
    public function verifyEmailCode($email, $code) {
        $sql = "SELECT id, firstname FROM users WHERE email = :email AND verification_code = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':code' => $code]);
        $user = $stmt->fetch();

        if ($user) {
            $updateSql = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([':id' => $user['id']]);
            return $user;
        }
        return false;
    }

    /**
     * Récupère un utilisateur complet par son ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, firstname, lastname, email, bio, relationship_type, is_premium, is_admin, latitude, longitude FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Met à jour les informations de profil d'un utilisateur
     */
    public function updateProfile($id, $data) {
        $fields = [
            "firstname = :firstname",
            "lastname = :lastname",
            "bio = :bio",
            "relationship_type = :relationship_type",
        ];

        if (isset($data['latitude']) && isset($data['longitude'])) {
            $fields[] = "latitude = :latitude";
            $fields[] = "longitude = :longitude";
        }

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':firstname', htmlspecialchars(strip_tags($data['firstname'])), PDO::PARAM_STR);
        $stmt->bindValue(':lastname', htmlspecialchars(strip_tags($data['lastname'])), PDO::PARAM_STR);
        $stmt->bindValue(':bio', isset($data['bio']) ? htmlspecialchars(strip_tags($data['bio'])) : null, PDO::PARAM_STR);
        $stmt->bindValue(':relationship_type', $data['relationship_type'], PDO::PARAM_STR);

        if (isset($data['latitude']) && isset($data['longitude'])) {
            $stmt->bindValue(':latitude', $data['latitude'], PDO::PARAM_STR);
            $stmt->bindValue(':longitude', $data['longitude'], PDO::PARAM_STR);
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateLocation($id, $latitude, $longitude) {
        $sql = "UPDATE users SET latitude = :latitude, longitude = :longitude WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Récupère tous les utilisateurs pour l'administration
     */
    public function getAllUsers() {
        $sql = "SELECT id, firstname, lastname, email, birthdate, gender, orientation, relationship_type, is_premium, is_active, is_admin, created_at 
                FROM users 
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Supprime définitivement un utilisateur
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Active ou désactive un compte utilisateur (bannissement)
     */
    public function updateStatus($id, $isActive) {
        $sql = "UPDATE users SET is_active = :is_active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':is_active', $isActive, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Met à jour le statut Premium d'un utilisateur
     */
    public function updatePremium($id, $isPremium) {
        $sql = "UPDATE users SET is_premium = :is_premium WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':is_premium', $isPremium, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
} 