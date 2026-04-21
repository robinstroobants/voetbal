-- 007_saas_collaboration.sql
-- Maak de tabellen aan voor het uitnodigen van meerdere coaches per ploeg

CREATE TABLE IF NOT EXISTS `user_teams` (
    `user_id` INT NOT NULL,
    `team_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `team_id`),
    CONSTRAINT `fk_user_teams_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_teams_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `team_invitations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `team_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_team_invitations_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `idx_team_inv_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zorg dat alle bestaande gebruikers toegang hebben tot hun eigen primary team_id in de nieuwe pivot table
INSERT IGNORE INTO `user_teams` (`user_id`, `team_id`)
SELECT `id`, `team_id` FROM `users` WHERE `team_id` IS NOT NULL;
