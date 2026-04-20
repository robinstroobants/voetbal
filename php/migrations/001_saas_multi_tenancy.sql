-- 001_saas_multi_tenancy.sql
-- Dit script vormt de huidige tabellen om tot een SaaS multi-tenant huurder model met subscriptions

-- 1. Voeg SaaS specifieke profiel kolommen toe aan users en the social login handlers
ALTER TABLE `users` 
  ADD COLUMN `team_id` INT NULL AFTER `id`,
  ADD COLUMN `first_name` VARCHAR(50) NULL AFTER `email`,
  ADD COLUMN `last_name` VARCHAR(50) NULL AFTER `first_name`,
  ADD COLUMN `oauth_provider` VARCHAR(50) NULL AFTER `role`,
  ADD COLUMN `oauth_uid` VARCHAR(255) NULL AFTER `oauth_provider`;

-- Koppel users expliciet aan een bepaald admin tenant team, let op: admin roles kunnen dit bypassen in php code
ALTER TABLE `users` 
  ADD CONSTRAINT `FK_Users_Teams` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL;

-- 2. Voeg facturatie / sub status limieten toe aan het team
ALTER TABLE `teams`
  ADD COLUMN `subscription_plan` VARCHAR(50) NOT NULL DEFAULT 'trial' AFTER `default_format`,
  ADD COLUMN `subscription_valid_until` DATETIME NULL AFTER `subscription_plan`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `subscription_valid_until`;

-- Update huidige theorie tactieken: maak theorie scheidenes per ploeg mogelijk 
-- Aangezien alles nu U11 Thes IP theorie is, vullen we momenteel team_id = 1
ALTER TABLE `lineups`
  ADD COLUMN `team_id` INT NULL AFTER `id`;

-- UPDATE alle bestaande theorien zodat ze bij tenant id 1 steken 
UPDATE `lineups` SET `team_id` = 1 WHERE `team_id` IS NULL;

-- Handhaaf key constraint
ALTER TABLE `lineups`
  ADD CONSTRAINT `FK_Lineups_Teams` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE;

-- 3. Mock data voor het test (U11 Thes IP) account met Brent / Shirley 
-- Eerst zorgen we dat U11 actief is met abbo voor altijd, voor test.
UPDATE `teams` SET `subscription_plan` = 'yearly', `subscription_valid_until` = '2030-01-01 00:00:00', `name` = 'U11 Thes IP' WHERE `id` = 1;

-- Update de dummy admin naar superadmin
UPDATE `users` SET `role` = 'superadmin' WHERE `id` = 1;

-- Steek Brent & Shirley in als coach users met default paswoord (welkom123) verbonden aan ploeg 1 
-- Wachtwoord is md5/bcrypt hashed. we gebruiken bcrypt hash voor "welkom123" => "$2y$10$A61fB57jP3Y.2qC7S9Z0a.cWMyg6PDBA79kFwIReN74PqT/6eS3/6"
INSERT INTO `users` (`team_id`, `email`, `first_name`, `last_name`, `password_hash`, `role`) 
VALUES 
  (1, 'brent@thesip.be', 'Brent', 'Coach', '$2y$10$A61fB57jP3Y.2qC7S9Z0a.cWMyg6PDBA79kFwIReN74PqT/6eS3/6', 'coach'),
  (1, 'shirley@thesip.be', 'Shirley', 'Coach', '$2y$10$A61fB57jP3Y.2qC7S9Z0a.cWMyg6PDBA79kFwIReN74PqT/6eS3/6', 'coach');
