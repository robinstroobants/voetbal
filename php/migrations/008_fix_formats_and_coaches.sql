-- Fix game formats: Replace _1gk_ and _2gk_ with _
UPDATE games SET format = REPLACE(format, '_1gk_', '_') WHERE format LIKE '%_1gk_%';
UPDATE games SET format = REPLACE(format, '_2gk_', '_') WHERE format LIKE '%_2gk_%';

-- Fix missing coaches (Assuming user ID 2 is Brent, the main coach for team 1)
-- In a pure SQL migration, we can fallback to the first coach found for each team
UPDATE games g
JOIN (
    SELECT team_id, MIN(id) as default_coach_id 
    FROM users 
    WHERE role = 'coach' 
    GROUP BY team_id
) u ON g.team_id = u.team_id
SET g.coach_id = u.default_coach_id
WHERE g.coach_id IS NULL OR g.coach_id = 0;

-- Clean up old original schemas that shouldn't have a team_id
UPDATE lineups SET team_id = NULL WHERE is_original = 1;
