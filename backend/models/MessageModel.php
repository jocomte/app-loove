<?php
// backend/models/MessageModel.php

class MessageModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function getMatchId($userId, $partnerId) {
        $sql = "SELECT id FROM matches
                WHERE (user_one_id = :user_id AND user_two_id = :partner_id)
                   OR (user_one_id = :partner_id AND user_two_id = :user_id)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_id' => $partnerId,
        ]);
        return $stmt->fetchColumn() ?: null;
    }

    public function sendMessage($senderId, $receiverId, $content) {
        $matchId = $this->getMatchId($senderId, $receiverId);
        if (!$matchId) {
            return false;
        }

        $sql = "INSERT INTO messages (match_id, sender_id, content, is_read, sent_at) 
                VALUES (:match_id, :sender_id, :content, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':match_id' => $matchId,
            ':sender_id' => $senderId,
            ':content' => htmlspecialchars(strip_tags($content)),
        ]);
    }

    public function getInbox($userId) {
        $sql = "SELECT
                    m.id AS match_id,
                    CASE WHEN m.user_one_id = :user_id THEN m.user_two_id ELSE m.user_one_id END AS partner_id,
                    u.firstname,
                    u.lastname,
                    p.url AS partner_photo,
                    COALESCE(MAX(msg.sent_at), m.created_at) AS last_at,
                    COALESCE(SUM(CASE WHEN msg.sender_id != :user_id AND msg.is_read = 0 THEN 1 ELSE 0 END), 0) AS unread_count
                FROM matches m
                JOIN users u ON u.id = CASE WHEN m.user_one_id = :user_id THEN m.user_two_id ELSE m.user_one_id END
                LEFT JOIN photos p ON p.user_id = u.id AND p.is_main = 1
                LEFT JOIN messages msg ON msg.match_id = m.id
                WHERE m.user_one_id = :user_id OR m.user_two_id = :user_id
                GROUP BY m.id, partner_id, u.firstname, u.lastname, p.url, m.created_at
                ORDER BY last_at DESC, m.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getConversation($userId, $partnerId) {
        $matchId = $this->getMatchId($userId, $partnerId);
        if (!$matchId) {
            return [];
        }

        $sql = "SELECT
                    m.id,
                    m.sender_id,
                    m.content,
                    m.sent_at AS created_at,
                    CASE WHEN m.sender_id = :user_id THEN 1 ELSE 0 END AS is_sender,
                    CONCAT(u.firstname, ' ', u.lastname) AS partner_name
                FROM messages m
                JOIN users u ON u.id = :partner_id
                WHERE m.match_id = :match_id
                ORDER BY m.sent_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_id' => $partnerId,
            ':match_id' => $matchId,
        ]);
        return $stmt->fetchAll();
    }

    public function markAsRead($userId, $partnerId) {
        $matchId = $this->getMatchId($userId, $partnerId);
        if (!$matchId) {
            return false;
        }

        $sql = "UPDATE messages SET is_read = 1 WHERE match_id = :match_id AND sender_id = :partner_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':match_id' => $matchId,
            ':partner_id' => $partnerId,
        ]);
    }
}
