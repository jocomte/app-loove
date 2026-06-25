<?php
// backend/models/ReportModel.php

class ReportModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Enregistre un signalement
     */
    public function createReport($reporterId, $reportedId, $reason) {
        $sql = "INSERT INTO reports (reporter_id, reported_id, reason, status) 
                VALUES (:reporter_id, :reported_id, :reason, 'pending')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':reporter_id' => $reporterId,
            ':reported_id' => $reportedId,
            ':reason'      => htmlspecialchars(strip_tags($reason))
        ]);
    }

    /**
     * Récupère tous les signalements pour l'admin
     */
    public function getAllReports() {
        $sql = "SELECT r.id, r.reporter_id, r.reported_id, r.reason, r.status, r.created_at,
                       u1.firstname as reporter_firstname, u1.lastname as reporter_lastname,
                       u2.firstname as reported_firstname, u2.lastname as reported_lastname
                FROM reports r
                JOIN users u1 ON r.reporter_id = u1.id
                JOIN users u2 ON r.reported_id = u2.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Met à jour le statut d'un signalement
     */
    public function updateReportStatus($reportId, $status) {
        $sql = "UPDATE reports SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $reportId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
