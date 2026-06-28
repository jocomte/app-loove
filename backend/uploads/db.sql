-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           12.2.2-MariaDB - MariaDB Server
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.14.0.7165
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Listage de la structure de la base pour app-loove
CREATE DATABASE IF NOT EXISTS `app-loove` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `app-loove`;

-- Listage de la structure de table app-loove. interactions
CREATE TABLE IF NOT EXISTS `interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interactor_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `action` enum('like','pass') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactor_id` (`interactor_id`,`target_id`),
  KEY `target_id` (`target_id`),
  CONSTRAINT `1` FOREIGN KEY (`interactor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.interactions : ~9 rows (environ)
INSERT INTO `interactions` (`id`, `interactor_id`, `target_id`, `action`, `created_at`) VALUES
	(1, 2, 1, 'like', '2026-06-25 21:52:58'),
	(2, 1, 4, 'like', '2026-06-22 23:52:58'),
	(3, 4, 1, 'like', '2026-06-22 23:52:58'),
	(4, 1, 2, 'like', '2026-06-26 00:01:49'),
	(5, 1, 3, 'like', '2026-06-26 00:27:20'),
	(6, 2, 5, 'like', '2026-06-26 00:28:35'),
	(7, 6, 7, 'like', '2026-06-26 12:28:32'),
	(8, 6, 1, 'pass', '2026-06-26 12:29:07'),
	(9, 6, 5, 'like', '2026-06-26 12:29:11'),
	(10, 1, 6, 'like', '2026-06-26 12:32:41'),
	(11, 9, 1, 'like', '2026-06-27 23:38:00'),
	(12, 9, 5, 'like', '2026-06-27 23:38:03'),
	(13, 10, 7, 'like', '2026-06-28 01:05:47'),
	(14, 10, 1, 'like', '2026-06-28 01:10:57'),
	(15, 10, 5, 'like', '2026-06-28 01:10:58');

-- Listage de la structure de table app-loove. matches
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_one_id` int(11) NOT NULL,
  `user_two_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_one_id` (`user_one_id`),
  KEY `user_two_id` (`user_two_id`),
  CONSTRAINT `1` FOREIGN KEY (`user_one_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`user_two_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.matches : ~2 rows (environ)
INSERT INTO `matches` (`id`, `user_one_id`, `user_two_id`, `created_at`) VALUES
	(1, 1, 4, '2026-06-22 23:52:58'),
	(2, 1, 2, '2026-06-26 00:01:49');

-- Listage de la structure de table app-loove. messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.messages : ~11 rows (environ)
INSERT INTO `messages` (`id`, `match_id`, `sender_id`, `content`, `is_read`, `sent_at`) VALUES
	(1, 1, 4, 'Salut Johan ! Comment tu vas ?', 1, '2026-06-23 23:52:58'),
	(2, 1, 1, 'Salut Chloé ! Ça va super et toi ? Qu\'est-ce que tu fais de beau sur Paris ?', 1, '2026-06-23 23:52:58'),
	(3, 1, 4, 'Je viens de commencer mon stage en design, j\'adore cette ville !', 1, '2026-06-24 23:52:58'),
	(4, 1, 4, 'Tu as des bons coins de terrasse à conseiller pour boire un verre ?', 1, '2026-06-25 23:42:58'),
	(5, 1, 1, 'ouais de fou', 0, '2026-06-25 23:56:31'),
	(6, 2, 1, 'hi', 1, '2026-06-26 00:27:52'),
	(7, 2, 2, 'hi', 1, '2026-06-26 00:28:58'),
	(8, 2, 1, 'how are you', 1, '2026-06-26 00:34:40'),
	(9, 2, 2, 'gay', 1, '2026-06-26 00:35:03'),
	(10, 2, 2, 'slt', 1, '2026-06-26 12:27:02'),
	(11, 2, 1, 'Salut', 1, '2026-06-26 12:27:14');

-- Listage de la structure de table app-loove. photos
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.photos : ~8 rows (environ)
INSERT INTO `photos` (`id`, `user_id`, `url`, `is_main`) VALUES
	(1, 1, '/app-loove/backend/uploads/johan.png', 1),
	(2, 2, '/app-loove/backend/uploads/emma.png', 1),
	(3, 3, '/app-loove/backend/uploads/lea.png', 1),
	(4, 4, '/app-loove/backend/uploads/chloe.png', 1),
	(5, 5, '/app-loove/backend/uploads/emma.png', 1),
	(6, 6, '/app-loove/backend/uploads/lea.png', 1),
	(7, 7, '/app-loove/backend/uploads/johan.png', 1),
	(8, 8, '/app-loove/backend/uploads/chloe.png', 1);

-- Listage de la structure de table app-loove. reports
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) NOT NULL,
  `reported_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  KEY `reported_id` (`reported_id`),
  CONSTRAINT `1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`reported_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.reports : ~2 rows (environ)
INSERT INTO `reports` (`id`, `reporter_id`, `reported_id`, `reason`, `status`, `created_at`) VALUES
	(1, 6, 5, 'Propos insultants et agressifs lors des échanges par messages privés.', 'resolved', '2026-06-25 18:52:58'),
	(2, 6, 1, 'faux compte', 'pending', '2026-06-26 12:29:02');

-- Listage de la structure de table app-loove. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `orientation` enum('men','women','everyone') NOT NULL,
  `bio` text DEFAULT NULL,
  `relationship_type` enum('friendship','serious','casual') NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 1,
  `verification_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.users : ~10 rows (environ)
INSERT INTO `users` (`id`, `lastname`, `firstname`, `email`, `password`, `birthdate`, `gender`, `orientation`, `bio`, `relationship_type`, `latitude`, `longitude`, `is_premium`, `is_active`, `created_at`, `is_admin`, `is_verified`, `verification_code`) VALUES
	(1, 'Colas', 'Johan', 'jojoco3003@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '1997-06-15', 'male', 'women', 'Je m\'appelle Johan. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.85660000, 2.35220000, 1, 1, '2026-06-25 23:52:58', 1, 1, NULL),
	(2, 'Dubois', 'Emma', 'emma@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '2001-04-12', 'female', 'men', 'Je m\'appelle Emma. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.85660000, 2.35220000, 1, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(3, 'Martinez', 'Léa', 'lea@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '1998-09-28', 'female', 'men', 'Je m\'appelle Léa. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.87000000, 2.36000000, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(4, 'Bernard', 'Chloé', 'chloe@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '2003-11-05', 'female', 'men', 'Je m\'appelle Chloé. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.84000000, 2.32000000, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(5, 'Durand', 'Thomas', 'thomas@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '1996-02-18', 'male', 'women', 'Je m\'appelle Thomas. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.88000000, 2.30000000, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(6, 'Rossi', 'Sofia', 'sofia@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '2000-07-22', 'female', 'everyone', 'Je m&#039;appelle Sofia. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 48.83000000, 2.38000000, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(7, 'Petit', 'Lucas', 'lucas@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '1999-05-10', 'male', 'everyone', 'Je m\'appelle Lucas. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'casual', 48.89000000, 2.33000000, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(8, 'Robert', 'Alice', 'alice@gmail.com', '$2y$10$xage10mX5LVvpQ8TUfCtpuDx./RbgAI3CH/gVvHVAGhF6WhKc1YAC', '1995-10-30', 'female', 'women', 'Je m&amp;amp;#039;appelle Alice. Passionné(e) de vie parisienne, de découvertes et de bons moments partagés !', 'serious', 47.90273360, 1.90860660, 0, 1, '2026-06-25 23:52:58', 0, 1, NULL),
	(9, 'Dupont', 'Jean', 'test.verify.demo@example.com', '$2y$10$hyY/Zk7hOzJF8xyVJ0sbZ.MZBzP/vxd2D9yQxDQOOERmaO..V1DC.', '1998-03-13', 'female', 'men', '', 'serious', 48.85660000, 2.35220000, 1, 1, '2026-06-27 23:31:12', 0, 1, NULL),
	(10, 'robert', 'marie', 'robert.marie@gmail.com', '$2y$10$8gyiz9FMeHS18uiPDpAaTOesfkBUZDS5/VZlzsJ/.lB0lbWb1niSy', '2001-06-26', 'female', 'men', '', 'serious', 48.85660000, 2.35220000, 1, 1, '2026-06-28 01:04:28', 0, 1, NULL);

-- Listage de la structure de table app-loove. visits
CREATE TABLE IF NOT EXISTS `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` int(11) NOT NULL,
  `visited_id` int(11) NOT NULL,
  `viewed_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `visited_id` (`visited_id`),
  CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`visited_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listage des données de la table app-loove.visits : ~9 rows (environ)
INSERT INTO `visits` (`id`, `visitor_id`, `visited_id`, `viewed_at`) VALUES
	(1, 2, 1, '2026-06-25 21:52:58'),
	(2, 3, 1, '2026-06-25 23:07:58'),
	(3, 5, 1, '2026-06-24 23:52:58'),
	(4, 1, 2, '2026-06-26 00:01:33'),
	(5, 1, 3, '2026-06-26 00:13:08'),
	(6, 2, 5, '2026-06-26 00:16:08'),
	(7, 6, 7, '2026-06-26 12:28:24'),
	(8, 6, 1, '2026-06-26 12:28:51'),
	(9, 6, 5, '2026-06-26 12:29:07'),
	(10, 1, 6, '2026-06-26 12:32:37'),
	(11, 9, 1, '2026-06-27 23:36:18'),
	(12, 1, 9, '2026-06-27 23:37:24'),
	(13, 9, 5, '2026-06-27 23:38:00'),
	(14, 10, 7, '2026-06-28 01:04:46'),
	(15, 10, 1, '2026-06-28 01:10:52'),
	(16, 10, 5, '2026-06-28 01:10:57');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
