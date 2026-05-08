-- Score Matrix Snapshots: bewaar historische versies van de score matrix per team
CREATE TABLE IF NOT EXISTS score_matrix_snapshots (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    team_id       INT NOT NULL,
    label         VARCHAR(100) NULL,          -- optioneel label (bv. "Voor AI-ronde 3")
    snapshot_data JSON NOT NULL,              -- [player_id][position] => score
    created_by    INT NULL,                   -- user_id die de snapshot aanmaakte (NULL = auto)
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_team_created (team_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
