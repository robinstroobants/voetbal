-- Voeg is_tournament vlag toe aan de games tabel
ALTER TABLE games
    ADD COLUMN is_tournament TINYINT(1) NOT NULL DEFAULT 0 AFTER is_theory;

-- Zet bestaande games met block_labels op is_tournament = 1
UPDATE games
SET is_tournament = 1
WHERE block_labels IS NOT NULL
  AND block_labels != '[]'
  AND JSON_LENGTH(block_labels) > 0;
