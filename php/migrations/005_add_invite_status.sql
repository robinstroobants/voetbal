ALTER TABLE team_invitations ADD COLUMN status ENUM('pending', 'accepted') NOT NULL DEFAULT 'pending';
