<?php
namespace Core\Services;

use PDO;

class CleanupService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Verwijder een heel team en alle gekoppelde data.
     */
    public function deleteTeam(int $team_id): void {
        $stmtU = $this->pdo->prepare("SELECT user_id FROM user_teams WHERE team_id = ?");
        $stmtU->execute([$team_id]);
        $affected_users = $stmtU->fetchAll(PDO::FETCH_COLUMN);

        $games = $this->pdo->prepare("SELECT id FROM games WHERE team_id = ?");
        $games->execute([$team_id]);
        $gameIds = $games->fetchAll(PDO::FETCH_COLUMN);
        
        if ($gameIds) {
            $inQ = implode(',', array_fill(0, count($gameIds), '?'));
            $this->pdo->prepare("DELETE FROM game_lineups WHERE game_id IN ($inQ)")->execute($gameIds);
            $this->pdo->prepare("DELETE FROM game_selections WHERE game_id IN ($inQ)")->execute($gameIds);
            $this->pdo->prepare("DELETE FROM game_playtime_logs WHERE game_id IN ($inQ)")->execute($gameIds);
            $this->pdo->prepare("DELETE FROM game_shift_logs WHERE game_id IN ($inQ)")->execute($gameIds);
        }
        $this->pdo->prepare("DELETE FROM games WHERE team_id = ?")->execute([$team_id]);

        $players = $this->pdo->prepare("SELECT id FROM players WHERE team_id = ?");
        $players->execute([$team_id]);
        $playerIds = $players->fetchAll(PDO::FETCH_COLUMN);
        if ($playerIds) {
            $inQ = implode(',', array_fill(0, count($playerIds), '?'));
            $this->pdo->prepare("DELETE FROM player_scores WHERE player_id IN ($inQ)")->execute($playerIds);
            $this->pdo->prepare("DELETE FROM gk_scores WHERE player_id IN ($inQ)")->execute($playerIds);
            $this->pdo->prepare("DELETE FROM player_team_ranking WHERE player_id IN ($inQ)")->execute($playerIds);
            $this->pdo->prepare("DELETE FROM position_rankings WHERE player_id IN ($inQ)")->execute($playerIds);
        }
        $this->pdo->prepare("DELETE FROM players WHERE team_id = ?")->execute([$team_id]);

        $this->pdo->prepare("DELETE FROM coaches WHERE team_id = ?")->execute([$team_id]);
        $this->pdo->prepare("DELETE FROM team_invitations WHERE team_id = ?")->execute([$team_id]);
        $this->pdo->prepare("DELETE FROM user_teams WHERE team_id = ?")->execute([$team_id]);
        
        // Zorg dat neven-tabellen ook verwijderd worden
        $this->pdo->prepare("DELETE FROM team_periods WHERE team_id = ?")->execute([$team_id]);
        $this->pdo->prepare("DELETE FROM usage_logs WHERE team_id = ?")->execute([$team_id]);

        // Verwerk lineups (wisselschema's) slim: Verwijder ze, tenzij andere teams ze al gebruiken (kopieerden)
        $stmtL = $this->pdo->prepare("SELECT id FROM lineups WHERE team_id = ?");
        $stmtL->execute([$team_id]);
        $team_lineups = $stmtL->fetchAll(PDO::FETCH_COLUMN);

        if ($team_lineups) {
            foreach($team_lineups as $l_id) {
                $stmtCheckL = $this->pdo->prepare("
                    SELECT g.team_id 
                    FROM game_lineups gl 
                    JOIN games g ON gl.game_id = g.id 
                    WHERE gl.schema_id = ? AND g.team_id != ? 
                    LIMIT 1
                ");
                $stmtCheckL->execute([$l_id, $team_id]);
                $other_team = $stmtCheckL->fetchColumn();

                if ($other_team) {
                    $this->pdo->prepare("UPDATE lineups SET team_id = ? WHERE id = ?")->execute([$other_team, $l_id]);
                } else {
                    $this->pdo->prepare("DELETE FROM lineups WHERE id = ?")->execute([$l_id]);
                }
            }
        }

        $this->pdo->prepare("DELETE FROM teams WHERE id = ?")->execute([$team_id]);

        // Controleer of de users (coaches) nog aan andere teams hangen. Zo niet, gooi de user helemaal weg.
        foreach($affected_users as $uid) {
            $check = $this->pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE user_id = ?");
            $check->execute([$uid]);
            if ($check->fetchColumn() == 0) {
                $checkTeams = $this->pdo->prepare("SELECT COUNT(*) FROM teams WHERE user_id = ?");
                $checkTeams->execute([$uid]);
                if ($checkTeams->fetchColumn() == 0) {
                    $this->pdo->prepare("DELETE FROM usage_logs WHERE user_id = ?")->execute([$uid]);
                    $this->pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
                }
            }
        }
    }

    /**
     * Ruim revisor dummies en theorie schema's op.
     * @return int Aantal verwijderde records
     */
    public function cleanupDummies(bool $forceAll = false, bool $includeSchemas = false): int {
        if ($forceAll) {
            $stmtDummies = $this->pdo->prepare("SELECT id FROM games WHERE opponent LIKE '%DUMMY REVISOR MATCH%' OR is_theory = 1");
        } elseif ($includeSchemas) {
            $stmtDummies = $this->pdo->prepare("SELECT id FROM games WHERE (opponent LIKE '%DUMMY REVISOR MATCH%' AND created_at < NOW() - INTERVAL 1 HOUR) OR is_theory = 1");
        } else {
            $stmtDummies = $this->pdo->prepare("SELECT id FROM games WHERE opponent LIKE '%DUMMY REVISOR MATCH%' AND created_at < NOW() - INTERVAL 1 HOUR");
        }
        
        $stmtDummies->execute();
        $dummyIds = $stmtDummies->fetchAll(PDO::FETCH_COLUMN);

        if ($dummyIds) {
            $inQ = implode(',', array_fill(0, count($dummyIds), '?'));
            $this->pdo->prepare("DELETE FROM game_lineups WHERE game_id IN ($inQ)")->execute($dummyIds);
            $this->pdo->prepare("DELETE FROM game_selections WHERE game_id IN ($inQ)")->execute($dummyIds);
            $this->pdo->prepare("DELETE FROM games WHERE id IN ($inQ)")->execute($dummyIds);
            return count($dummyIds);
        }

        return 0;
    }
}
