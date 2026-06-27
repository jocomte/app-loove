<?php
// backend/migrate.php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getInstance();
    echo "--- Début de la migration ---\n";

    // 1. Ajouter la colonne is_admin si elle n'existe pas
    $cols = $db->query("SHOW COLUMNS FROM users LIKE 'is_admin'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE users ADD COLUMN is_admin tinyint(1) DEFAULT 0");
        echo "[OK] Colonne 'is_admin' ajoutée à la table 'users'.\n";
    } else {
        echo "[INFO] La colonne 'is_admin' existe déjà.\n";
    }

    // 2. Définir jojoco3003@gmail.com en tant qu'admin
    $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE email = :email");
    $stmt->execute([':email' => 'jojoco3003@gmail.com']);
    echo "[OK] Utilisateur 'jojoco3003@gmail.com' promu administrateur.\n";

    // 3. Créer la table visits si elle n'existe pas
    $db->exec("CREATE TABLE IF NOT EXISTS `visits` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `visitor_id` int(11) NOT NULL,
      `visited_id` int(11) NOT NULL,
      `viewed_at` timestamp NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `visitor_id` (`visitor_id`),
      KEY `visited_id` (`visited_id`),
      CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`visited_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] Table 'visits' validée/créée.\n";

    // 4. Ajouter les colonnes is_verified et verification_code pour la vérification par e-mail
    $colsVerified = $db->query("SHOW COLUMNS FROM users LIKE 'is_verified'")->fetchAll();
    if (empty($colsVerified)) {
        $db->exec("ALTER TABLE users ADD COLUMN is_verified tinyint(1) DEFAULT 1, ADD COLUMN verification_code varchar(10) DEFAULT NULL");
        echo "[OK] Colonnes 'is_verified' et 'verification_code' ajoutées à la table 'users'.\n";
    } else {
        echo "[INFO] Les colonnes 'is_verified' et 'verification_code' existent déjà.\n";
    }

    echo "--- Migration terminée avec succès ! ---\n";
} catch (Exception $e) {
    echo "[ERREUR] Erreur de migration : " . $e->getMessage() . "\n";
    exit(1);
}

