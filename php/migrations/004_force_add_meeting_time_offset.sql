ALTER TABLE `teams` ADD COLUMN `meeting_time_offset` INT NOT NULL DEFAULT '60' AFTER `is_active`;
