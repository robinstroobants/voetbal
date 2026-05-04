-- Add timezone and show_lineup_to_parents settings to teams table
ALTER TABLE teams
    ADD COLUMN timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/Brussels',
    ADD COLUMN show_lineup_to_parents TINYINT(1) NOT NULL DEFAULT 0;
