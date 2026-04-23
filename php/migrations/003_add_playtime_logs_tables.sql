CREATE TABLE `game_playtime_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_id` int NOT NULL,
  `player_id` int NOT NULL,
  `coach_id` int DEFAULT NULL,
  `seconds_played` int DEFAULT '0',
  `seconds_bank` int DEFAULT '0',
  `seconds_gk` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_game_player` (`game_id`,`player_id`),
  KEY `player_id` (`player_id`),
  KEY `coach_id` (`coach_id`),
  CONSTRAINT `game_playtime_logs_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `game_playtime_logs_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `game_playtime_logs_ibfk_3` FOREIGN KEY (`coach_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `game_shift_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_id` int NOT NULL,
  `player_id` int NOT NULL,
  `shift_index` int NOT NULL,
  `position` varchar(20) NOT NULL,
  `duration_seconds` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_game_player_shift` (`game_id`,`player_id`,`shift_index`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `game_shift_logs_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `game_shift_logs_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
