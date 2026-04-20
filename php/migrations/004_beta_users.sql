-- Add beta flag to users
ALTER TABLE users ADD COLUMN is_beta_user TINYINT(1) DEFAULT 0 AFTER role;

-- Optioneel: Zet je eigen account/specifieke user accounts op beta indien gewenst door direct in the DB aan te passen.
