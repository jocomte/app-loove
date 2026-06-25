<?php
// backend/seed.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/Logger.php';

try {
    $db = Database::getInstance();
    echo "--- Début du peuplement de la base de données (Seeding) ---\n";

    // Désactiver les clés étrangères pour nettoyer proprement
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("TRUNCATE TABLE messages");
    $db->exec("TRUNCATE TABLE matches");
    $db->exec("TRUNCATE TABLE visits");
    $db->exec("TRUNCATE TABLE reports");
    $db->exec("TRUNCATE TABLE interactions");
    $db->exec("TRUNCATE TABLE photos");
    $db->exec("TRUNCATE TABLE users");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "[OK] Tables vidées avec succès.\n";

    // Mot de passe commun pour tous les comptes
    $passwordHash = password_hash("password123", PASSWORD_BCRYPT);

    // 1. Insertion des Utilisateurs
    $users = [
        [
            'id' => 1,
            'firstname' => 'Johan',
            'lastname' => 'Colas',
            'email' => 'jojoco3003@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '1997-06-15',
            'gender' => 'male',
            'orientation' => 'women',
            'relationship_type' => 'serious',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 1
        ],
        [
            'id' => 2,
            'firstname' => 'Emma',
            'lastname' => 'Dubois',
            'email' => 'emma@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '2001-04-12',
            'gender' => 'female',
            'orientation' => 'men',
            'relationship_type' => 'serious',
            'latitude' => 48.8600,
            'longitude' => 2.3400,
            'is_premium' => 1,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 3,
            'firstname' => 'Léa',
            'lastname' => 'Martinez',
            'email' => 'lea@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '1998-09-28',
            'gender' => 'female',
            'orientation' => 'men',
            'relationship_type' => 'serious',
            'latitude' => 48.8700,
            'longitude' => 2.3600,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 4,
            'firstname' => 'Chloé',
            'lastname' => 'Bernard',
            'email' => 'chloe@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '2003-11-05',
            'gender' => 'female',
            'orientation' => 'men',
            'relationship_type' => 'serious',
            'latitude' => 48.8400,
            'longitude' => 2.3200,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 5,
            'firstname' => 'Thomas',
            'lastname' => 'Durand',
            'email' => 'thomas@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '1996-02-18',
            'gender' => 'male',
            'orientation' => 'women',
            'relationship_type' => 'serious',
            'latitude' => 48.8800,
            'longitude' => 2.3000,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 6,
            'firstname' => 'Sofia',
            'lastname' => 'Rossi',
            'email' => 'sofia@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '2000-07-22',
            'gender' => 'female',
            'orientation' => 'everyone',
            'relationship_type' => 'casual',
            'latitude' => 48.8300,
            'longitude' => 2.3800,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 7,
            'firstname' => 'Lucas',
            'lastname' => 'Petit',
            'email' => 'lucas@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '1999-05-10',
            'gender' => 'male',
            'orientation' => 'everyone',
            'relationship_type' => 'casual',
            'latitude' => 48.8900,
            'longitude' => 2.3300,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ],
        [
            'id' => 8,
            'firstname' => 'Alice',
            'lastname' => 'Robert',
            'email' => 'alice@gmail.com',
            'password' => $passwordHash,
            'birthdate' => '1995-10-30',
            'gender' => 'female',
            'orientation' => 'women',
            'relationship_type' => 'friendship',
            'latitude' => 48.8200,
            'longitude' => 2.3500,
            'is_premium' => 0,
            'is_active' => 1,
            'is_admin' => 0
        ]
    ];

    $userInsert = $db->prepare("INSERT INTO users (id, firstname, lastname, email, password, birthdate, gender, orientation, relationship_type, latitude, longitude, is_premium, is_active, is_admin, bio) 
                                VALUES (:id, :firstname, :lastname, :email, :password, :birthdate, :gender, :orientation, :relationship_type, :latitude, :longitude, :is_premium, :is_active, :is_admin, :bio)");

    foreach ($users as $u) {
        $bio = "Je m'appelle " . $u['firstname'] . ". Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !";
        $userInsert->execute([
            ':id' => $u['id'],
            ':firstname' => $u['firstname'],
            ':lastname' => $u['lastname'],
            ':email' => $u['email'],
            ':password' => $u['password'],
            ':birthdate' => $u['birthdate'],
            ':gender' => $u['gender'],
            ':orientation' => $u['orientation'],
            ':relationship_type' => $u['relationship_type'],
            ':latitude' => $u['latitude'],
            ':longitude' => $u['longitude'],
            ':is_premium' => $u['is_premium'],
            ':is_active' => $u['is_active'],
            ':is_admin' => $u['is_admin'],
            ':bio' => $bio
        ]);
    }
    echo "[OK] 8 utilisateurs insérés.\n";

    // 2. Insertion des Photos de Profil
    $photos = [
        ['user_id' => 1, 'url' => '/app-loove/backend/uploads/johan.png', 'is_main' => 1],
        ['user_id' => 2, 'url' => '/app-loove/backend/uploads/emma.png', 'is_main' => 1],
        ['user_id' => 3, 'url' => '/app-loove/backend/uploads/lea.png', 'is_main' => 1],
        ['user_id' => 4, 'url' => '/app-loove/backend/uploads/chloe.png', 'is_main' => 1],
        // Les autres utilisent des placeholders ou photos génériques
        ['user_id' => 5, 'url' => '/app-loove/backend/uploads/emma.png', 'is_main' => 1], // Juste pour avoir une photo
        ['user_id' => 6, 'url' => '/app-loove/backend/uploads/lea.png', 'is_main' => 1],
        ['user_id' => 7, 'url' => '/app-loove/backend/uploads/johan.png', 'is_main' => 1],
        ['user_id' => 8, 'url' => '/app-loove/backend/uploads/chloe.png', 'is_main' => 1]
    ];

    $photoInsert = $db->prepare("INSERT INTO photos (user_id, url, is_main) VALUES (:user_id, :url, :is_main)");
    foreach ($photos as $p) {
        $photoInsert->execute([
            ':user_id' => $p['user_id'],
            ':url' => $p['url'],
            ':is_main' => $p['is_main']
        ]);
    }
    echo "[OK] Photos de profil enregistrées.\n";

    // 3. Insertion des interactions et visites
    // Emma a visité et aimé Johan
    $db->exec("INSERT INTO visits (visitor_id, visited_id, viewed_at) VALUES (2, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR))");
    $db->exec("INSERT INTO interactions (interactor_id, target_id, action, created_at) VALUES (2, 1, 'like', DATE_SUB(NOW(), INTERVAL 2 HOUR))");

    // Léa a visité Johan
    $db->exec("INSERT INTO visits (visitor_id, visited_id, viewed_at) VALUES (3, 1, DATE_SUB(NOW(), INTERVAL 45 MINUTE))");

    // Thomas a visité Johan
    $db->exec("INSERT INTO visits (visitor_id, visited_id, viewed_at) VALUES (5, 1, DATE_SUB(NOW(), INTERVAL 1 DAY))");

    echo "[OK] Interactions initiales et historique des visites insérés.\n";

    // 4. Insertion d'un Match existant (Chloé et Johan)
    $db->exec("INSERT INTO matches (id, user_one_id, user_two_id, created_at) VALUES (1, 1, 4, DATE_SUB(NOW(), INTERVAL 3 DAY))");
    $db->exec("INSERT INTO interactions (interactor_id, target_id, action, created_at) VALUES (1, 4, 'like', DATE_SUB(NOW(), INTERVAL 3 DAY))");
    $db->exec("INSERT INTO interactions (interactor_id, target_id, action, created_at) VALUES (4, 1, 'like', DATE_SUB(NOW(), INTERVAL 3 DAY))");
    echo "[OK] Match créé entre Johan (ID: 1) et Chloé (ID: 4).\n";

    // 5. Insertion des Messages de la Discussion (Chloé et Johan)
    $messages = [
        ['match_id' => 1, 'sender_id' => 4, 'content' => 'Salut Johan ! Comment tu vas ?', 'is_read' => 1, 'sent_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
        ['match_id' => 1, 'sender_id' => 1, 'content' => 'Salut Chloé ! Ça va super et toi ? Qu\'est-ce que tu fais de beau sur Paris ?', 'is_read' => 1, 'sent_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
        ['match_id' => 1, 'sender_id' => 4, 'content' => 'Je viens de commencer mon stage en design, j\'adore cette ville !', 'is_read' => 1, 'sent_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['match_id' => 1, 'sender_id' => 4, 'content' => 'Tu as des bons coins de terrasse à conseiller pour boire un verre ?', 'is_read' => 0, 'sent_at' => date('Y-m-d H:i:s', strtotime('-10 minutes'))]
    ];

    $msgInsert = $db->prepare("INSERT INTO messages (match_id, sender_id, content, is_read, sent_at) VALUES (:match_id, :sender_id, :content, :is_read, :sent_at)");
    foreach ($messages as $m) {
        $msgInsert->execute([
            ':match_id' => $m['match_id'],
            ':sender_id' => $m['sender_id'],
            ':content' => $m['content'],
            ':is_read' => $m['is_read'],
            ':sent_at' => $m['sent_at']
        ]);
    }
    echo "[OK] 4 messages de discussion insérés (dont 1 non lu de Chloé).\n";

    // 6. Insertion d'un Signalement (Sofia a signalé Thomas)
    $db->exec("INSERT INTO reports (reporter_id, reported_id, reason, status, created_at) 
               VALUES (6, 5, 'Propos insultants et agressifs lors des échanges par messages privés.', 'pending', DATE_SUB(NOW(), INTERVAL 5 HOUR))");
    echo "[OK] 1 signalement en attente de modération inséré.\n";

    echo "--- Peuplement de la base terminé avec succès ! ---\n";
} catch (Exception $e) {
    echo "[ERREUR] Erreur lors du seeding : " . $e->getMessage() . "\n";
    exit(1);
}
