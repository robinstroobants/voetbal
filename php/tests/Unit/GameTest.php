<?php

use PHPUnit\Framework\TestCase;

// Use the absolute path if possible or adjust to the new /models path
require_once dirname(__DIR__, 2) . '/models/game.php';

class GameTest extends TestCase
{
    public function testGameRendersCorrectScoreWithoutCrashing()
    {
        // 1. Arrange: Prepare mock data
        $spelers = [1, 2, 3, 4, 5];
        $format = '5v5_4x15';
        
        $mockScores = [
            1 => [1 => 80, 2 => 70, 4 => 60, 5 => 90, 7 => 50, 9 => 40, 10 => 30, 11 => 88],
            2 => [1 => 81, 2 => 71, 4 => 61, 5 => 91, 7 => 51, 9 => 41, 10 => 31, 11 => 88],
            3 => [1 => 82, 2 => 72, 4 => 62, 5 => 92, 7 => 52, 9 => 42, 10 => 32, 11 => 88],
            4 => [1 => 83, 2 => 73, 4 => 63, 5 => 93, 7 => 53, 9 => 43, 10 => 33, 11 => 88],
            5 => [1 => 84, 2 => 74, 4 => 64, 5 => 94, 7 => 54, 9 => 44, 10 => 34, 11 => 88],
        ];
        
        $mockInfo = [
            1 => ['name' => 'Speler 1'],
            2 => ['name' => 'Speler 2'],
            3 => ['name' => 'Speler 3'],
            4 => ['name' => 'Speler 4'],
            5 => ['name' => 'Speler 5'],
        ];

        // We creëren een fictief theorie-moment
        $mockEvents = [
            $format => [
                5 => [ // Aantal spelers
                    0 => ['duration' => 15, 'game_counter' => 1, 'lineup' => [1 => 0, 2 => 1, 4 => 2, 5 => 3, 7 => 4], 'bench' => []],
                    1 => ['duration' => 15, 'game_counter' => 2, 'lineup' => [1 => 1, 2 => 2, 4 => 3, 5 => 4, 7 => 0], 'bench' => []],
                ]
            ]
        ];

        // 2. Act: Instantiëren
        $game = new Game(
            $spelers, 
            false, 
            $format, 
            'random', 
            $mockScores, 
            $mockInfo, 
            $mockEvents
        );

        // 3. Assert: Zonder errors zou deze Game class een object met een property "score" moeten bevatten.
        $this->assertIsNumeric($game->score);
        $this->assertGreaterThan(0, $game->score, "Score mag niet leeg of 0 zijn na berekening");
        
        // Controleer of de speler info correct gemapt is, de 'playernames' baseert zich normaal op info
        $this->assertArrayHasKey(1, $game->playerscores, "Player 1 should be part of the mapped Game sequence");
    }
}
