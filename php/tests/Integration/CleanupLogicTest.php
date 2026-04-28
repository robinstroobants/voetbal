<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Core\Services\CleanupService;
use PDO;

class CleanupLogicTest extends TestCase
{
    private PDO $pdo;
    private CleanupService $cleanupService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $host = getenv('DB_HOST') ?: 'db';
        $db = getenv('DB_NAME') ?: 'lineup_db';
        $user = getenv('DB_USER') ?: 'app_user';
        $pass = getenv('DB_PASS') ?: '';
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Dynamically include the service if not autoloaded
        require_once dirname(__DIR__, 2) . '/core/Services/CleanupService.php';
        
        $this->cleanupService = new CleanupService($this->pdo);
    }

    public function testCleanupDummies()
    {
        // Setup dummy team for the game
        $email = 'dummy_' . uniqid() . '@test.com';
        $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, 'pwd')")->execute([$email]);
        $user_id = $this->pdo->lastInsertId();

        $this->pdo->prepare("INSERT INTO clubs (name) VALUES ('Dummy Club')")->execute();
        $club_id = $this->pdo->lastInsertId();

        $this->pdo->prepare("INSERT INTO teams (user_id, club_id, name) VALUES (?, ?, 'Dummy Team')")->execute([$user_id, $club_id]);
        $team_id = $this->pdo->lastInsertId();

        // Setup dummy record
        $stmt = $this->pdo->prepare("INSERT INTO games (team_id, opponent, game_date, is_theory, created_at) VALUES (?, ?, NOW() - INTERVAL 2 HOUR, 0, NOW() - INTERVAL 2 HOUR)");
        $stmt->execute([$team_id, 'DUMMY REVISOR MATCH 123']);
        
        // Execute
        $deletedCount = $this->cleanupService->cleanupDummies(false, false);
        
        // Assert
        $this->assertGreaterThanOrEqual(1, $deletedCount, "Failed to clean up dummies.");
    }

    public function testDeleteTeam()
    {
        $this->pdo->beginTransaction();

        try {
            // Setup
            $email = 'test_' . uniqid() . '@test.com';
            $stmt = $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, 'pwd')");
            $stmt->execute([$email]);
            $user_id = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO clubs (name) VALUES ('Test Club')");
            $stmt->execute();
            $club_id = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO teams (user_id, club_id, name) VALUES (?, ?, 'Test Team Delete')");
            $stmt->execute([$user_id, $club_id]);
            $team_id = $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO games (team_id, opponent, game_date) VALUES (?, 'Opponent 1', NOW())");
            $stmt->execute([$team_id]);
            
            $stmt = $this->pdo->prepare("INSERT INTO players (team_id, first_name, last_name) VALUES (?, 'Test', 'Player')");
            $stmt->execute([$team_id]);

            // Execute
            $this->cleanupService->deleteTeam($team_id);

            // Assert
            $checkTeam = $this->pdo->prepare("SELECT id FROM teams WHERE id = ?");
            $checkTeam->execute([$team_id]);
            $this->assertFalse($checkTeam->fetch(), "Team was not deleted.");

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
