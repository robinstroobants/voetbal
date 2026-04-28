ALTER TABLE user_feedback ADD COLUMN status ENUM('open', 'resolved', 'ignored') DEFAULT 'open' AFTER created_at;
