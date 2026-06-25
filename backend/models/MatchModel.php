<?php

class MatchModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère les profils compatibles qui n'ont pas encore été aimés ou passés
     */
    public function findTargets($userId, $latitude, $longitude, $filters = []) {
        // 1. Récupération des préférences de l'utilisateur connecté
        $userSql = "SELECT gender, orientation, relationship_type FROM users WHERE id = :id";
        $stmt = $this->db->prepare($userSql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $me = $stmt->fetch();

        if (!$me) return null;

        $lat = $latitude ?? 47.902981;
        $lng = $longitude ?? 1.903758;

        // Build dynamic SQL
        $whereClauses = [
            "u.id != :my_id",
            "u.is_active = 1",
            "u.id NOT IN (SELECT target_id FROM interactions WHERE interactor_id = :my_id_exclude)",
            // Filtrage d'orientation sexuelle réciproque
            "(:my_orientation = 'everyone' OR (:my_orientation = 'men' AND u.gender = 'male') OR (:my_orientation = 'women' AND u.gender = 'female'))",
            "(u.orientation = 'everyone' OR (u.orientation = 'men' AND :my_gender = 'male') OR (u.orientation = 'women' AND :my_gender = 'female'))"
        ];

        // Apply filters if premium
        if (!empty($filters['relation'])) {
            $whereClauses[] = "u.relationship_type = :filter_relation";
        } else {
            $whereClauses[] = "u.relationship_type = :my_type";
        }

        if (!empty($filters['age_min'])) {
            $whereClauses[] = "u.birthdate <= :age_min_date";
        }
        if (!empty($filters['age_max'])) {
            $whereClauses[] = "u.birthdate >= :age_max_date";
        }
        if (!empty($filters['keyword'])) {
            $whereClauses[] = "(u.bio LIKE :keyword OR u.firstname LIKE :keyword)";
        }

        $sql = "SELECT u.id, u.firstname, u.bio, u.birthdate,
               up.url AS photo,
               (6371 * acos(cos(radians(:my_lat_1)) * cos(radians(COALESCE(u.latitude, :my_lat_3))) * cos(radians(COALESCE(u.longitude, :my_lng_2)) - radians(:my_lng)) + sin(radians(:my_lat_2)) * sin(radians(COALESCE(u.latitude, :my_lat_3))))) AS distance 
                FROM users u
                LEFT JOIN photos up ON up.user_id = u.id AND up.is_main = 1
                WHERE " . implode(" AND ", $whereClauses) . "
                HAVING distance < 50 
                ORDER BY distance ASC 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        // Bind default values
        $stmt->bindValue(':my_lat_1', $lat, PDO::PARAM_STR);
        $stmt->bindValue(':my_lng', $lng, PDO::PARAM_STR);
        $stmt->bindValue(':my_lat_2', $lat, PDO::PARAM_STR);
        $stmt->bindValue(':my_lat_3', $lat, PDO::PARAM_STR);
        $stmt->bindValue(':my_lng_2', $lng, PDO::PARAM_STR);
        
        $stmt->bindValue(':my_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':my_id_exclude', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':my_gender', $me['gender'], PDO::PARAM_STR);
        $stmt->bindValue(':my_orientation', $me['orientation'], PDO::PARAM_STR);

        if (!empty($filters['relation'])) {
            $stmt->bindValue(':filter_relation', $filters['relation'], PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':my_type', $me['relationship_type'], PDO::PARAM_STR);
        }

        if (!empty($filters['age_min'])) {
            $minAge = intval($filters['age_min']);
            $dateMin = date('Y-m-d', strtotime("-$minAge years"));
            $stmt->bindValue(':age_min_date', $dateMin, PDO::PARAM_STR);
        }
        if (!empty($filters['age_max'])) {
            $maxAge = intval($filters['age_max']);
            $dateMax = date('Y-m-d', strtotime("-" . ($maxAge + 1) . " years +1 day"));
            $stmt->bindValue(':age_max_date', $dateMax, PDO::PARAM_STR);
        }
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%', PDO::PARAM_STR);
        }

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
        $sql = "INSERT INTO interactions (interactor_id, target_id, action, created_at) 
                VALUES (:interactor_id, :target_id, :action, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':interactor_id', $interactorId, PDO::PARAM_INT);
        $stmt->bindValue(':target_id', $targetId, PDO::PARAM_INT);
        $stmt->bindValue(':action', $type, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Vérifie si la cible a également liké l'utilisateur connecté (Détection de Match)
     */
    public function checkMatch($userId, $targetId) {
        $sql = "SELECT id FROM interactions 
                WHERE interactor_id = :target_id 
                AND target_id = :user_id 
                AND action = 'like' 
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
            Logger::log("Match déjà existant entre $userId et $targetId: ID " . $existing, "INFO");
            return true;
        }

        $userOne = min($userId, $targetId);
        $userTwo = max($userId, $targetId);

        $sql = "INSERT INTO matches (user_one_id, user_two_id, created_at) VALUES (:user_one_id, :user_two_id, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':user_one_id' => $userOne,
            ':user_two_id' => $userTwo,
        ]);
        
        if ($result) {
            Logger::log("Match créé entre $userOne et $userTwo", "INFO");
        }
        
        return $result;
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