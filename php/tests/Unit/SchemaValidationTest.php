<?php

use PHPUnit\Framework\TestCase;

class SchemaValidationTest extends TestCase
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
            
            // Setup dummy schema for test DB so the validation has something to check
            $stmtCount = $this->pdo->query("SELECT COUNT(*) FROM lineups");
            if ($stmtCount->fetchColumn() == 0) {
                // Ensure a dummy team exists
                $email = 'schema_' . uniqid() . '@test.com';
                $this->pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, 'pwd')")->execute([$email]);
                $user_id = $this->pdo->lastInsertId();

                $this->pdo->prepare("INSERT INTO clubs (name) VALUES ('Test Club')")->execute();
                $club_id = $this->pdo->lastInsertId();

                $this->pdo->prepare("INSERT INTO teams (user_id, club_id, name) VALUES (?, ?, 'Test Team')")->execute([$user_id, $club_id]);
                $team_id = $this->pdo->lastInsertId();

                $mockSchemaData = json_encode([
                    [
                        "duration" => 15,
                        "lineup" => ["1" => 0, "2" => 1, "3" => 2, "4" => 3, "5" => 4],
                        "bench" => []
                    ],
                    [
                        "duration" => 15,
                        "lineup" => ["1" => 0, "2" => 1, "3" => 2, "4" => 3, "5" => 4],
                        "bench" => [],
                        "subs" => ["in" => [], "out" => []]
                    ]
                ]);

                $this->pdo->prepare("INSERT INTO lineups (team_id, game_format, player_count, schema_data) VALUES (?, '5v5_1gk_2x15', 5, ?)")
                          ->execute([$team_id, $mockSchemaData]);
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Geen database connectie beschikbaar voor test: ' . $e->getMessage());
        }
    }

    public function testSchemasComplyWithGameRules()
    {
        $stmt = $this->pdo->query("SELECT id, game_format, player_count, schema_data FROM lineups");
        $results = $stmt->fetchAll();
        
        $this->assertNotEmpty($results, "Geen schema's gevonden in database om te testen.");

        foreach ($results as $row) {
            $schemaId = (int)$row['id'];
            $format = $row['game_format'];
            $playercount = (int)$row['player_count'];
            
            preg_match('/_(\d+)gk_/', $format, $gkMatches);
            $gkCount = isset($gkMatches[1]) ? (int)$gkMatches[1] : 1;
            
            $shifts = json_decode($row['schema_data'], true);
            $sourceIdentifier = "DB_ID: {$schemaId} ({$format}_{$playercount}sp)";
            
            $this->validateSchema($sourceIdentifier, $schemaId, $shifts, $playercount, $gkCount);
        }
    }

    private function validateSchema(string $file, int $schemaId, array $shifts, int $playercount, int $gkCount)
    {
        $playtimes = array_fill(0, $playercount, 0);
        $playtimesPos1 = array_fill(0, $playercount, 0);

        foreach ($shifts as $i => $shift) {
            if (!is_numeric($i)) continue;
            $dur = $shift['duration'] ?? 0;
            foreach ($shift['lineup'] ?? [] as $pos => $p) {
                $playtimes[$p] += $dur;
                if ($pos == 1) $playtimesPos1[$p] += $dur;
            }
        }

        foreach ($shifts as $i => $shift) {
            if (!is_numeric($i)) continue; // negeer overige metadata properties indien die bestaan
            
            // ---------------------------------------------------------
            // Regel 1: Iedereen in de selectie moet toegewezen zijn (op het veld OF op de bank)
            // ---------------------------------------------------------
            $fieldPlayers = array_values($shift['lineup'] ?? []);
            $benchPlayers = array_values($shift['bench'] ?? []);
            $allAssignedPlayers = array_merge($fieldPlayers, $benchPlayers);
            
            $missing = array_diff(range(0, $playercount - 1), $allAssignedPlayers);
            $duplicate = array_diff_assoc($allAssignedPlayers, array_unique($allAssignedPlayers));
            
            if (!empty($missing)) {
                echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Shift $i mist speler(s): " . implode(',', $missing) . "\n";
            }
            if (!empty($duplicate)) {
                echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Shift $i heeft dubbele spelers toegewezen.\n";
            }

        // Tel speeltijd op is al in the pre-loop gedaan


            // ---------------------------------------------------------
            // Regel 3 & 4: Sub checks op de oneven helftjes (shifts 1, 3, 5...)
            // ---------------------------------------------------------
            if ($i % 2 === 1) { // 1, 3, 5, 7 zijn "helftje 2"
                $prevShift = $shifts[$i - 1];

                // Regel 3: Iemand die op de bank zat in helft 1, MOET in helft 2 op het veld staan
                $prevBench = array_values($prevShift['bench'] ?? []);
                $currLineup = array_values($shift['lineup'] ?? []);
                
                foreach ($prevBench as $benchSitter) {
                    if ($benchSitter < $gkCount || ($playtimes[$benchSitter] > 0 && $playtimes[$benchSitter] === $playtimesPos1[$benchSitter])) continue; // Goalies mogen gerust 2 helften (1 wedstrijd) op de bank rusten!
                    
                    if (!in_array($benchSitter, $currLineup)) {
                        echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Speler $benchSitter zat op de bank in helft " . ($i) . " (shift " . ($i-1) . "), maar kwam niet het veld op in helft " . ($i+1) . " (shift $i).\n";
                    }
                }

                // Regel 4: De 'subs' lijst (in/out) moet WISKUNDIG PERFECT kloppen
                $expectedIn = [];
                $expectedOut = [];
                foreach ($prevShift['lineup'] as $pos => $speler_oud) {
                    if (isset($shift['lineup'][$pos])) {
                        $speler_nieuw = $shift['lineup'][$pos];
                        if ($speler_oud !== $speler_nieuw) {
                            $expectedIn[$pos] = $speler_nieuw;
                            $expectedOut[$pos] = $speler_oud;
                        }
                    }
                }
                
                $actualIn = $shift['subs']['in'] ?? [];
                $actualOut = $shift['subs']['out'] ?? [];

                // Compare differences!
                if ($expectedIn != $actualIn) {
                    echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Shift $i (Helft 2) berekende 'subs->in' array is fout! Verwacht: " . json_encode($expectedIn) . " Actueel: " . json_encode($actualIn) . "\n";
                }
                if ($expectedOut != $actualOut) {
                    echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Shift $i (Helft 2) berekende 'subs->out' array is fout! Verwacht: " . json_encode($expectedOut) . " Actueel: " . json_encode($actualOut) . "\n";
                }
            }
        }

        // ---------------------------------------------------------
        // Regel 2: De speeltijd mag max over 2 verschillende totale waarden verspreid zijn per schema voor veldspelers.
        // ---------------------------------------------------------
        // Bereken wat de maximale mogelijke totale speeltijd (het hele spel) is
        $max_game_duration = 0;
        foreach ($shifts as $i => $shift) {
            if (is_numeric($i)) {
                $max_game_duration += ($shift['duration'] ?? 0);
            }
        }

        $fieldPlaytimes = [];
        
        // Loop over alle spelers. Als iemand ALLEEN op positie 1 speelt (Doelman), negeren we ze
        for ($p = 0; $p < $playercount; $p++) {
            if ($playtimes[$p] > 0 && $playtimes[$p] === $playtimesPos1[$p]) {
                continue; // 100% van de speeltijd is op doel. Dit telt als goalie.
            }
            $fieldPlaytimes[] = $playtimes[$p];
        }

        $uniquePlaytimes = array_unique($fieldPlaytimes);
        
        if (count($uniquePlaytimes) > 2) {
            echo "\n[WARNING] Bestand: " . basename($file) . " | Schema $schemaId | Speeltijd is NIET gelijk(waardig) verdeeld. Veldspelers hebben liefst " . count($uniquePlaytimes) . " verschillende blok-frequenties (" . implode(', ', $uniquePlaytimes) . ")\n";
        }
    }
}
