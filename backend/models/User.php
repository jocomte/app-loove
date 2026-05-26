<?php
// backend/models/User.php

class User {
    private $db;

    public function __construct() {
        // On récupère l'instance unique de PDO via notre Singleton
        $this->db = Database::getInstance();
    }

    /**
     * Crée un nouvel utilisateur dans la base de données
     */
    public function create($data) {
        $sql = "INSERT INTO users (lastname, firstname, email, password, birthdate, gender, orientation, relationship_type) 
                VALUES (:lastname, :firstname, :email, :password, :birthdate, :gender, :orientation, :relationship_type)";
        
        $stmt = $this->db->prepare($sql);

        // Hachage du mot de passe pour la sécurité (obligation RGPD / Sécurité)
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        return $stmt->execute([
            ':lastname'          => htmlspecialchars(strip_tags($data['lastname'])),
            ':firstname'         => htmlspecialchars(strip_tags($data['firstname'])),
            ':email'             => filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? $data['email'] : null,
            ':password'          => $hashedPassword,
            ':birthdate'         => $data['birthdate'],
            ':gender'            => $data['gender'],
            ':orientation'       => $data['orientation'],
            ':relationship_type' => $data['relationship_type']
        ]);
    }

    /**
     * Vérifie si un email existe déjà dans la base
     */
    public function getUserByEmail($email) {
        $sql = "SELECT id, firstname, lastname, password, is_premium, is_active FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}