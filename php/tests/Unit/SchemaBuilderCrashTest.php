<?php

use PHPUnit\Framework\TestCase;

class SchemaBuilderCrashTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: 'db';
        $db   = getenv('DB_NAME') ?: 'voetbal';
        $user = getenv('DB_USER') ?: 'voetbal_user';
        $pass = getenv('DB_PASS') ?: 'voetbal_pass';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            
            // Clean up old test data
            $this->pdo->exec("DELETE gs FROM game_selections gs JOIN games g ON gs.game_id = g.id WHERE g.opponent = 'CrashTest Opponent'");
            $this->pdo->exec("DELETE FROM games WHERE opponent = 'CrashTest Opponent'");
        } catch (\PDOException $e) {
            $this->markTestSkipped('Geen database connectie of opschoning gefaald: ' . $e->getMessage());
        }
    }

    public function testSchemaBuilderDoesNotCrashOnPerfectMath()
    {
        // 1. Create a dummy team
        $email = 'crash_test_' . uniqid() . '@test.com';
        $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, 'pwd')")->execute([$email]);
        $user_id = $this->pdo->lastInsertId();

        $this->pdo->prepare("INSERT INTO clubs (name) VALUES ('Test Club')")->execute();
        $club_id = $this->pdo->lastInsertId();

        $this->pdo->prepare("INSERT INTO teams (user_id, club_id, name) VALUES (?, ?, 'Test Team')")->execute([$user_id, $club_id]);
        $team_id = $this->pdo->lastInsertId();

        // 2. Create 5 players (1 GK, 4 Field) for 5v5_1gk_4x15
        $player_ids = [];
        for ($i = 0; $i < 5; $i++) {
            $this->pdo->prepare("INSERT INTO players (team_id, first_name) VALUES (?, ?)")->execute([$team_id, "Player $i"]);
            $player_ids[] = $this->pdo->lastInsertId();
        }

        // 3. Create a game with 5v5_1gk_4x15 format
        $this->pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format) VALUES (?, 'CrashTest Opponent', '2030-01-01 10:00:00', '5v5_4x15')")->execute([$team_id]);
        $game_id = $this->pdo->lastInsertId();

        // 4. Create selections
        foreach ($player_ids as $index => $pid) {
            $is_gk = ($index === 0) ? 1 : 0; // First player is GK
            $this->pdo->prepare("INSERT INTO game_selections (game_id, player_id, is_goalkeeper, status_id) VALUES (?, ?, ?, 1)")->execute([$game_id, $pid, $is_gk]);
        }

        // 5. Setup SUPERGLOBALS to simulate HTTP request to schema_builder.php
        $_SESSION['user_id'] = $user_id;
        $_SESSION['team_id'] = $team_id;
        $_GET['game_id'] = $game_id;

        // Ensure getconn.php is required internally by schema_builder, but we already have PDO.
        // The script schema_builder.php includes other files. We will buffer output to prevent polluting PHPUnit.
        
        ob_start();
        
        // We capture any fatals or warnings by setting a custom error handler
        $errors = [];
        set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors) {
            $errors[] = "$errstr in $errfile:$errline";
            return true;
        });

        try {
            $pdo = $this->pdo;
            require __DIR__ . '/../../modules/schemas/schema_builder.php';
        } catch (\Throwable $e) {
            $errors[] = "FATAL: " . $e->getMessage();
        }

        restore_error_handler();
        $output = ob_get_clean();

        // Clean up
        $this->pdo->exec("DELETE FROM game_selections WHERE game_id = $game_id");
        $this->pdo->exec("DELETE FROM games WHERE id = $game_id");
        $this->pdo->exec("DELETE FROM players WHERE team_id = $team_id");
        $this->pdo->exec("DELETE FROM teams WHERE id = $team_id");
        $this->pdo->exec("DELETE FROM users WHERE id = $user_id");

        // Asserts
        $this->assertEmpty($errors, "Schema builder veroorzaakte errors/warnings tijdens perfect math: \n" . implode("\n", $errors));
        $this->assertStringContainsString('id="shifts-canvas"', $output, "Output moet het schema builder framework bevatten.");
    }
}
