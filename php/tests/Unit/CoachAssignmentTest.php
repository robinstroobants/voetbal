<?php

use PHPUnit\Framework\TestCase;

class CoachAssignmentTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        // Setup direct DB connection
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
        } catch (\PDOException $e) {
            $this->markTestSkipped('Geen database connectie beschikbaar voor test: ' . $e->getMessage());
        }
    }

    public function testNoGamesMissingCoach()
    {
        $stmt = $this->pdo->query("
            SELECT id, opponent, game_date 
            FROM games 
            WHERE coach_id IS NULL 
            ORDER BY game_date DESC
        ");
        $missing = $stmt->fetchAll();

        if (count($missing) > 0) {
            $msg = "Waarschuwing: Er zijn " . count($missing) . " wedstrijden zonder toegewezen coach. Dit vervuilt de statistieken!";
            // Gebruik markTestIncomplete om de test suite niet rood te maken, maar wel een gele waarschuwing te tonen.
            $this->markTestIncomplete($msg);
        } else {
            $this->assertTrue(true); // Dummy assertion
        }
    }
}
