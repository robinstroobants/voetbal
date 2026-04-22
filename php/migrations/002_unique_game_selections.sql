-- Voorkom dubbele spelers in één wedstrijd (bvb door dubbelklik op dupliceren)
-- Wis eerst eventuele historische dubbele records
DELETE s1 FROM game_selections s1
INNER JOIN game_selections s2 
WHERE s1.id > s2.id AND s1.game_id = s2.game_id AND s1.player_id = s2.player_id;

-- Voeg unieke sleutel toe
ALTER TABLE game_selections ADD UNIQUE KEY `uq_game_player` (`game_id`, `player_id`);
