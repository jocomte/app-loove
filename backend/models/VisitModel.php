<?php
// backend/models/VisitModel.php

class VisitModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Enregistre une visite (évite les doublons consécutifs dans la même heure)
     */
    public function logVisit($visitorId, $visitedId) {
        if ($visitorId === $visitedId) return false;

        // Évite de loguer plusieurs fois de suite si c'est la même heure
        $checkSql = "SELECT id FROM visits 
                     WHERE visitor_id = :visitor_id AND visited_id = :visited_id 
                       AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                     LIMIT 1";
        $stmt = $this->db->prepare($checkSql);
        $stmt->execute([
            ':visitor_id' => $visitorId,
            ':visited_id' => $visitedId
        ]);
        if ($stmt->fetch()) {
            return false;
        }

        $sql = "INSERT INTO visits (visitor_id, visited_id) VALUES (:visitor_id, :visited_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':visitor_id' => $visitorId,
            ':visited_id' => $visitedId
        ]);
    }

    /**
     * Récupère la liste des personnes ayant visité le profil de l'utilisateur
     */
    public function getVisitsForUser($visitedId) {
        $sql = "SELECT v.id, v.visitor_id, v.viewed_at, u.firstname, u.lastname,
                       (SELECT url FROM photos WHERE user_id = u.id AND is_main = 1 LIMIT 1) as visitor_photo
                FROM visits v
                JOIN users u ON v.visitor_id = u.id
                WHERE v.visited_id = :visited_id
                ORDER BY v.viewed_at DESC
                LIMIT 30";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':visited_id', $visitedId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
