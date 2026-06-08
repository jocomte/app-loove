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
        $sql = "SELECT u.id, u.firstname, u.bio, u.birthdate,
               up.url AS photo,
               (6371 * acos(cos(radians(:my_lat_1)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians(:my_lng)) + sin(radians(:my_lat_2)) * sin(radians(u.latitude)))) AS distance 
                FROM users u
                LEFT JOIN photos up ON up.user_id = u.id AND up.is_main = 1
                WHERE u.id != :my_id 
                AND u.is_active = 1
                AND u.id NOT IN (
                    SELECT target_id FROM interactions WHERE interactor_id = :my_id_exclude
                )
                AND u.relationship_type = :my_type
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

        $profile = $stmt->fetch();
        if ($profile && !empty($profile['photo'])) {
            $profile['photo_url'] = $profile['photo'];
        }

        return $profile;
    }

    /**
     * Enregistre un choix (Like ou Pass) entre deux utilisateurs
     */
    public function saveInteraction($interactorId, $targetId, $type) {
        $sql = "INSERT INTO interactions (interactor_id, target_id, type, created_at) 
                VALUES (:interactor_id, :target_id, :type, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':interactor_id', $interactorId, PDO::PARAM_INT);
        $stmt->bindValue(':target_id', $targetId, PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Vérifie si la cible a également liké l'utilisateur connecté (Détection de Match)
     */
    public function checkMatch($userId, $targetId) {
        $sql = "SELECT id FROM interactions 
                WHERE interactor_id = :target_id 
                AND target_id = :user_id 
                AND type = 'like' 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':target_id', $targetId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }

    public function createMatch($userId, $targetId) {
        $existing = $this->getExistingMatch($userId, $targetId);
        if ($existing) {
            return true;
        }

        $userOne = min($userId, $targetId);
        $userTwo = max($userId, $targetId);

        $sql = "INSERT INTO matches (user_one_id, user_two_id, created_at) VALUES (:user_one_id, :user_two_id, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_one_id' => $userOne,
            ':user_two_id' => $userTwo,
        ]);
    }

    private function getExistingMatch($userId, $targetId) {
        $sql = "SELECT id FROM matches
                WHERE (user_one_id = :user_id AND user_two_id = :target_id)
                   OR (user_one_id = :target_id AND user_two_id = :user_id)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':target_id' => $targetId,
        ]);
        return $stmt->fetchColumn() ?: null;
    }
}