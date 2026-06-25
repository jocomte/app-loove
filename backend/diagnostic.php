<?php
session_start();

// Inclusion des classes
spl_autoload_register(function ($className) {
    if (file_exists(__DIR__ . '/config/' . $className . '.php')) {
        require_once __DIR__ . '/config/' . $className . '.php';
    } elseif (file_exists(__DIR__ . '/controllers/' . $className . '.php')) {
        require_once __DIR__ . '/controllers/' . $className . '.php';
    } elseif (file_exists(__DIR__ . '/models/' . $className . '.php')) {
        require_once __DIR__ . '/models/' . $className . '.php';
    }
});

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorisé."]);
    exit();
}

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // Vérifier les interactions
    $interactionStmt = $db->prepare("SELECT * FROM interactions WHERE interactor_id = :uid OR target_id = :uid LIMIT 10");
    $interactionStmt->execute([':uid' => $userId]);
    $interactions = $interactionStmt->fetchAll();
    
    // Vérifier les matches
    $matchStmt = $db->prepare("SELECT m.*, u1.firstname as user_one_name, u2.firstname as user_two_name FROM matches m LEFT JOIN users u1 ON u1.id = m.user_one_id LEFT JOIN users u2 ON u2.id = m.user_two_id WHERE m.user_one_id = :uid OR m.user_two_id = :uid LIMIT 10");
    $matchStmt->execute([':uid' => $userId]);
    $matches = $matchStmt->fetchAll();
    
    // Vérifier les messages
    $messageStmt = $db->prepare("SELECT msg.*, m.user_one_id, m.user_two_id FROM messages msg JOIN matches m ON msg.match_id = m.id WHERE m.user_one_id = :uid OR m.user_two_id = :uid ORDER BY msg.sent_at DESC LIMIT 20");
    $messageStmt->execute([':uid' => $userId]);
    $messages = $messageStmt->fetchAll();
    
    // Récupérer les informations utilisateur
    $userStmt = $db->prepare("SELECT id, firstname, lastname, email FROM users WHERE id = :uid");
    $userStmt->execute([':uid' => $userId]);
    $user = $userStmt->fetch();
    
    http_response_code(200);
    echo json_encode([
        "status" => "OK",
        "user" => $user,
        "interactions_count" => count($interactions),
        "interactions" => $interactions,
        "matches_count" => count($matches),
        "matches" => $matches,
        "messages_count" => count($messages),
        "recent_messages" => array_slice($messages, 0, 5)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
