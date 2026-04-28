CREATE TABLE IF NOT EXISTS user_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    team_id INT NULL,
    feedback_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    url VARCHAR(255) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
