<?php

class MatchModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère les profils compatibles qui n'ont pas encore été aimés ou passés
     */
    public function findTargets($userId, $latitude, $longitude) {
        // 1. Récupération des préférences de l'utilisateur connecté
        $userSql = "SELECT gender, orientation, relationship_type FROM users WHERE id = :id";
        $stmt = $this->db->prepare($userSql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $me = $stmt->fetch();

        if (!$me) return null;

        // 2. Requête avec marqueurs 100% uniques : :my_lat_1, :my_lat_2, :my_lng
        $sql = "SELECT id, firstname, bio, birthdate,
               (6371 * acos(cos(radians(:my_lat_1)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:my_lng)) + sin(radians(:my_lat_2)) * sin(radians(latitude)))) AS distance 
                FROM users 
                WHERE id != :my_id 
                AND is_active = 1
                AND id NOT IN (
                    SELECT target_id FROM interactions WHERE interactor_id = :my_id_exclude
                )
                AND relationship_type = :my_type
                HAVING distance < 50 
                ORDER BY distance ASC 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        
        $lat = $latitude ?? 47.902981;
        $lng = $longitude ?? 1.903758;

        // On lie chaque marqueur unique à sa valeur correspondante
        $stmt->bindValue(':my_lat_1', $lat, PDO::PARAM_STR);
        $stmt->bindValue(':my_lng', $lng, PDO::PARAM_STR);
        $stmt->bindValue(':my_lat_2', $lat, PDO::PARAM_STR);
        
        $stmt->bindValue(':my_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':my_id_exclude', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':my_type', $me['relationship_type'], PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch();
    }
}