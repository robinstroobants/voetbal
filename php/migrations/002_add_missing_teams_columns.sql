ALTER TABLE `teams` ADD COLUMN `default_game_parts` VARCHAR(20) NULL DEFAULT NULL AFTER `default_format`;
ALTER TABLE `teams` ADD COLUMN `meeting_time_offset` INT NOT NULL DEFAULT '45' AFTER `is_active`;
