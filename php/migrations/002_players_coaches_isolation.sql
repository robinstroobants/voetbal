-- 002_players_coaches_isolation.sql
-- Isolates coaches into the multi-tenant architecture. Players already have team_id.

-- Add team_id to coaches 
ALTER TABLE `coaches`
  ADD COLUMN `team_id` INT NULL AFTER `id`;

UPDATE `coaches` SET `team_id` = 1 WHERE `team_id` IS NULL;

ALTER TABLE `coaches`
  ADD CONSTRAINT `FK_Coaches_Teams` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE;
