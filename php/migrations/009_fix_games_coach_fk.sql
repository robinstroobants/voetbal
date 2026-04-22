-- Oude foreign key constraint verwijderen (wees naar de oude coaches tabel)
ALTER TABLE games DROP FOREIGN KEY fk_games_coach;

-- Nieuwe foreign key toevoegen (wijst nu correct naar de SaaS users tabel)
ALTER TABLE games ADD CONSTRAINT fk_games_coach FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE SET NULL;
