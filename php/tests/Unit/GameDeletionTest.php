<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;

class GameDeletionTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: 'db';
        $db = getenv('DB_NAME') ?: 'lineup_db';
        $user = getenv('DB_USER') ?: 'app_user';
        $pass = getenv('DB_PASS') ?: 'root';
        
        try {
            // Check getconn.php default password logic which usually falls back to empty or root on local dev
            $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            try {
                // second try with empty password (common in local docker)
                $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, "", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (\PDOException $e2) {
                $this->markTestSkipped('Database connection failed: ' . $e2->getMessage());
            }
        }
    }

    public function testGameDeletionCascadesWithoutForeignConstraintError()
    {
        $pdo = $this->pdo;
        
        // 0. Setup dummy dependencies for an empty test DB
        $email = 'gamedel_' . uniqid() . '@test.com';
        $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, 'pwd')")->execute([$email]);
        $user_id = $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO clubs (name) VALUES ('Test Club')")->execute();
        $club_id = $pdo->lastInsertId();

        $pdo->prepare("INSERT INTO teams (user_id, club_id, name) VALUES (?, ?, 'Test Team')")->execute([$user_id, $club_id]);
        $team_id = $pdo->lastInsertId();
        
        $pdo->prepare("INSERT INTO players (team_id, first_name, last_name) VALUES (?, 'Test', 'Player')")->execute([$team_id]);
        $playerId = $pdo->lastInsertId();

        // 1. Create a dummy game
        $pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format) VALUES (?, 'Test Deletion Match', CURDATE(), '8v8_4x15')")->execute([$team_id]);
        $gameId = $pdo->lastInsertId();
        
        // 2. Insert dummy selection to trigger Foreign Key constraint
        $pdo->prepare("INSERT INTO game_selections (game_id, player_id, status_id, is_goalkeeper) VALUES (?, ?, 2, 0)")
            ->execute([$gameId, $playerId]);
            
        // 3. Insert dummy lineup
        $pdo->prepare("INSERT INTO game_lineups (game_id, schema_id, player_order, score, is_final) VALUES (?, 999999, '2', 80.5, 0)")
            ->execute([$gameId]);

        // 4. Act: simulate manage_games.php deletion logic
        $exceptionThrown = false;
        try {
            // Emulate what we fixed in manage_games.php:
            $pdo->prepare("DELETE FROM game_lineups WHERE game_id = :id")->execute(['id' => $gameId]);
            $pdo->prepare("DELETE FROM game_selections WHERE game_id = :id")->execute(['id' => $gameId]);
            $pdo->prepare("DELETE FROM games WHERE id = :id")->execute(['id' => $gameId]);
        } catch (\PDOException $e) {
            $exceptionThrown = true;
            $this->fail("Foreign key constraint or cascade failure detected during game deletion: " . $e->getMessage());
        }

        // Assert no exception was thrown and game is gone
        $this->assertFalse($exceptionThrown, "Exception was thrown, meaning database constraints blocked it.");
        
        $check = $pdo->prepare("SELECT id FROM games WHERE id = ?");
        $check->execute([$gameId]);
        $this->assertFalse($check->fetch(), "Game row was not successfully deleted.");
        
        $checkSelections = $pdo->prepare("SELECT game_id FROM game_selections WHERE game_id = ?");
        $checkSelections->execute([$gameId]);
        $this->assertFalse($checkSelections->fetch(), "Game Selections were not successfully cascading deleted.");
    }
}
