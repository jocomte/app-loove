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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.interactions : ~0 rows (environ)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.matches : ~0 rows (environ)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.messages : ~0 rows (environ)

-- Listage de la structure de table app-loove. photos
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.photos : ~0 rows (environ)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.reports : ~0 rows (environ)

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Listage des données de la table app-loove.users : ~11 rows (environ)
INSERT INTO `users` (`id`, `lastname`, `firstname`, `email`, `password`, `birthdate`, `gender`, `orientation`, `bio`, `relationship_type`, `latitude`, `longitude`, `is_premium`, `is_active`, `created_at`) VALUES
	(1, 'Colas', 'johan', 'jojoco3003@gmail.com', '$2y$10$lu7P1xp3MW4C2fQqo.mWW.QLyE14mVnXu/WFKMjoXcPbfoW3gu2kS', '2007-03-30', 'male', 'women', NULL, 'casual', 47.90200000, 1.90300000, 0, 1, '2026-05-22 08:00:28'),
	(2, 'Colas', 'johan', 'jo300@gmail.com', '$2y$10$7jBVKyoWcWrA0WQCSIeKiul7CB2gd/wTEghMpKzD2XO8fr4pZ.wF2', '2007-03-30', 'male', 'women', NULL, 'casual', 47.90200000, 1.90300000, 0, 1, '2026-05-22 08:06:13'),
	(3, 'Colas', 'johan', 'jo@sfr.fr', '$2y$10$kM48LuDWGOmHpaCD4bCad..I3f2Ou5GF73pChmQfrI7GtqyR.CYXO', '2007-03-30', 'male', 'women', NULL, 'serious', NULL, NULL, 0, 1, '2026-05-22 08:08:07'),
	(4, 'petit', 'maxime', 'max@fr.fr', '$2y$10$WFYAMcfKllm/XpPVxQXenenPmStKuwu2EM3AL.H0a35A7Dz6xre86', '2003-11-04', 'male', 'women', NULL, 'serious', NULL, NULL, 0, 1, '2026-05-26 09:39:41'),
	(5, 'colas', 'johanna', 'johanna@gmail.com', '$2y$10$Pc5RWrF3CyaONrSj9LBXluldQiuF46Y1rYgkRGjPMxNF/sWryH4j6', '2005-07-07', 'female', 'men', NULL, 'serious', NULL, NULL, 0, 1, '2026-05-26 09:42:12'),
	(6, 'dupont', 'elina', 'elina@sfr.fr', '$2y$10$F7IFD.RR5./1sPQPm1lozeMCQSD2UtH4hy.Idj6xGvzWHeywUEI.W', '2006-09-21', 'female', 'men', NULL, 'casual', NULL, NULL, 0, 1, '2026-05-26 09:47:44'),
	(7, 'Zivkovic', 'Ilija', 'ilija@gmail.com', '$2y$10$mEnMd82q6spxygb7OOEjHeEIqZ1wlCsg5Vf2KMEPC6O5vu1cT2pAu', '2007-03-02', 'male', 'everyone', NULL, 'serious', 47.90000000, 1.90000000, 0, 1, '2026-05-26 12:45:52'),
	(8, 'gidel', 'julien', 'julien@coda.school', '$2y$10$oufKYlK56PDSW3xbsHgMdOd.HW7ntG9ReNyB91aI84aaM7cvPlaJm', '1995-09-06', 'male', 'women', NULL, 'casual', NULL, NULL, 0, 1, '2026-05-29 12:51:58'),
	(9, 'del', 'ahouahoun', 'del@gmail.com', '$2y$10$h83QTBLLyMMjgyKdS6r/kevFzTMdOqlKAG6E21bVgL./yZvorXjmK', '2003-03-11', 'male', 'women', '', 'casual', NULL, NULL, 0, 1, '2026-06-01 09:26:51'),
	(10, 'Doom', 'TevAito', 'doomtevaito@gmail.com', '$2y$10$S7Oo8U1G/spOXvWfDOjqNuMvfKV1Rbu2vWP3e5ljeTBMQb1RhlDkm', '2026-06-09', 'male', 'men', NULL, 'casual', NULL, NULL, 0, 1, '2026-06-01 10:12:59'),
	(11, 'AHOUANHOU', 'Fidel', 'fidelahouanhou@gmail.com', '$2y$10$SOPhLjBtoGK01MnQm/79d.KnBuTHXcc0EH9/LvrtUVmOUuyeRUqb.', '2026-06-03', 'male', 'women', NULL, 'casual', NULL, NULL, 0, 1, '2026-06-01 10:13:05'),
	(12, 'col', 'ilo', 'ilo@gmail.com', '$2y$10$0vrTJ.r5kMCVjLPBmYABg.y3Kz4b.S/JxbQJ6rGocZ6xx747BhpGC', '2003-12-21', 'female', 'men', NULL, 'casual', NULL, NULL, 0, 1, '2026-06-05 09:31:59');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
