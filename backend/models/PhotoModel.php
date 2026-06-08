<?php
// backend/models/PhotoModel.php

class PhotoModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function savePhoto($userId, $url, $isMain = false) {
        if ($isMain) {
            $this->clearMain($userId);
        }

        $sql = "INSERT INTO photos (user_id, url, is_main) VALUES (:user_id, :url, :is_main)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':url' => $url,
            ':is_main' => $isMain ? 1 : 0,
        ]);
    }

    public function clearMain($userId) {
        $sql = "UPDATE photos SET is_main = 0 WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
    }

    public function getPhotosByUser($userId) {
        $sql = "SELECT id, url, is_main, created_at FROM photos WHERE user_id = :user_id ORDER BY is_main DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getPrimaryPhoto($userId) {
        $sql = "SELECT url FROM photos WHERE user_id = :user_id AND is_main = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
    }
}
