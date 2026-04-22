<?php
// inspect_schema.php
require_once __DIR__ . '/../getconn.php';
$page_title = 'Schema Diagnostics';
require_once __DIR__ . '/../header.php'; // Zorg dat we de admin layout pakken

$schemaId = isset($_GET['schema']) ? (int)$_GET['schema'] : null;
$format = $_GET['format'] ?? null;

if (!$schemaId) {
    $stmtAll = $pdo->query("SELECT id, game_format, player_count, team_id, is_original, schema_data FROM lineups ORDER BY game_format ASC, player_count ASC, id DESC");
    $all_schemas = $stmtAll->fetchAll();
    ?>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0 radius-15">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fa-solid fa-microscope text-primary mb-3" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold">Schema Diagnostics & Revisor</h2>
                            <p class="text-muted">Analyseer en repareer wiskundige theoriematrices en JSON schema instellingen direct vanuit je browser.</p>
                        </div>
                        
                       
                        <form method="GET" class="border p-4 rounded bg-white shadow-sm mb-5">
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass me-2"></i>Specifieke Diagnose</h5>
                            <p class="small text-muted mb-4">Kopieer de gegevens uit je PHPUnit mislukking (bv. <code>Bestand: DB_ID: 2 (8v8_0gk_4x15_9sp)</code>) en plak ze rechtstreeks hieronder.</p>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold small text-muted">Bestandsnaam (Format)</label>
                                    <input type="text" name="format" class="form-control form-control-lg border-2 shadow-sm" placeholder="Bv. 8v8_0gk_4x15_9sp" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">Schema / DB ID</label>
                                    <input type="number" name="schema" class="form-control form-control-lg border-2 shadow-sm" placeholder="Bv. 2" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="fa-solid fa-stethoscope me-2"></i>Laad in Viewer</button>
                        </form>
                        
                        <?php
                        // Auto-Scan all schemas!
                        $broken_schemas = [];
                        foreach ($all_schemas as $row) {
                            $shifts = json_decode($row['schema_data'], true);
                            if (!$shifts) continue;
                            
                            preg_match('/_(\d+)gk_/', $row['game_format'], $matches);
                            $sc_gk_count = isset($matches[1]) ? (int)$matches[1] : 0;
                            $sc_playercount = (int)$row['player_count'];
                            if ($sc_playercount == 0) $sc_playercount = count($shifts[0]['lineup']) + count($shifts[0]['bench']);
                            
                            $pt = array_fill(0, $sc_playercount, 0);
                            $pt_pos1 = array_fill(0, $sc_playercount, 0);
                            $sc_errors = [];
                            $sc_max_dur = 0;
                            
                            foreach ($shifts as $i => $shift) {
                                if (!is_numeric($i)) continue;
                                $dur = $shift['duration'] ?? 0;
                                $sc_max_dur += $dur;
                                foreach ($shift['lineup'] ?? [] as $pos => $pid) {
                                    $pt[$pid] += $dur;
                                    if ($pos == 1) $pt_pos1[$pid] += $dur;
                                }
                                
                                if ($i % 2 === 1) { // 2e helft
                                    $prevShift = $shifts[$i - 1];
                                    $prevBench = array_values($prevShift['bench'] ?? []);
                                    $currLineup = array_values($shift['lineup'] ?? []);
                                    foreach ($prevBench as $benchSitter) {
                                        if ($benchSitter >= $sc_gk_count && !in_array($benchSitter, $currLineup)) {
                                            $sc_errors[] = "Double Bank Penalty";
                                        }
                                    }
                                    
                                    $expIn = []; $expOut = [];
                                    foreach ($prevShift['lineup'] as $pos => $sp_oud) {
                                        if (isset($shift['lineup'][$pos])) {
                                            $sp_nw = $shift['lineup'][$pos];
                                            if ($sp_oud !== $sp_nw) {
                                                $expIn[$pos] = $sp_nw;
                                                $expOut[$pos] = $sp_oud;
                                            }
                                        }
                                    }
                                    if ($expIn != ($shift['subs']['in'] ?? [])) $sc_errors[] = "Subs-In fout";
                                    if ($expOut != ($shift['subs']['out'] ?? [])) $sc_errors[] = "Subs-Out fout";
                                }
                            }
                            
                            $filtered = [];
                            for ($p=0; $p<$sc_playercount; $p++) {
                                // Als al je veld-minuten gewijd zijn aan positie 1, ben je een toegewijde doelman
                                if ($pt[$p] > 0 && $pt[$p] === $pt_pos1[$p]) {
                                    continue;
                                }
                                $filtered[] = $pt[$p];
                            }
                            if (count(array_unique($filtered)) > 2) $sc_errors[] = "Speeltijd disbalans";
                            
                            if (!empty($sc_errors)) {
                                $broken_schemas[] = [
                                    'id' => $row['id'],
                                    'format' => $row['game_format'],
                                    'errors' => array_unique($sc_errors)
                                ];
                            }
                        }
                        ?>
                        
                        <h5 class="fw-bold mb-3 mt-5"><i class="fa-solid fa-radar me-2 text-danger"></i>Actuele Unit Test Fouten</h5>
                        <?php if (empty($broken_schemas)): ?>
                            <div class="alert alert-success border-0 shadow-sm"><i class="fa-solid fa-check-circle me-2"></i>Jouw theorie database is volledig zuiver! Geen enkel schema rapporteert momenteel integriteitsfouten.</div>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 border-start border-warning border-4 shadow-sm mb-3">
                                <strong>Let op:</strong> De onderstaande schema's produceren momenteel een fatale crash in the <code>SchemaValidationTest</code>.
                            </div>
                            <div class="list-group shadow-sm">
                                <?php foreach($broken_schemas as $bs): ?>
                                    <a href="/admin/inspect_schema?format=<?= urlencode($bs['format']) ?>&schema=<?= $bs['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold">Schema <?= $bs['id'] ?></span>
                                            <small class="text-muted ms-2">(<?= $bs['format'] ?>)</small>
                                            <div class="small text-danger mt-1">
                                                <i class="fa-solid fa-bug me-1"></i> <?= implode(' & ', $bs['errors']) ?>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-muted"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                         <div class="bg-light p-4 rounded mb-4 shadow-sm border">
                            <h5 class="fw-bold text-dark"><i class="fa-solid fa-scale-balanced me-2"></i>De 3 Gouden Spelregels van het Algoritme</h5>
                            <p class="small text-muted mb-3">Wanneer een wisselschema faalt in je unit testen of vastloopt bij generatie, is één van onderstaande regels verbroken tijdens the creatie in de database.</p>
                            <ul class="mb-0 text-dark small">
                                <li class="mb-2"><strong>1. Matrix Disbalans:</strong> Het totale aantal speelminuten mag over een hele wedstrijd maximaal op uiterlijk stipt <strong class="text-danger">twee</strong> verschillende lengtes vallen d.m.v wiskundige perfectie (Bv. 45 minuten en 60 minuten). Iedere afwijking hierbuiten veroorzaakt onmogelijk rekenwerk voor de auto-generator in producties!</li>
                                <li class="mb-2"><strong>2. Double-Bank Penalty:</strong> Een veldspeler kan en mag onder geen beding in 2 opeenvolgende "helften" van een kwart-game vastgelijmd worden aan de bank. Iedereen roteert systematisch mee.</li>
                                <li class="mb-0"><strong>3. Array Integriteit:</strong> Elke "shift" of "kwartje" leunt op de wiskundige wetten. Er kan geen veldpositie overgeslagen worden (nul check), spelers mogen nooit kloonposities dragen en <code>subs->out</code> en <code>subs->in</code> mutaties verplichten strikte handhaving t.o.v. de vorige array lineup!</li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'autofix_subs') {
    $stmtSchema = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
    $stmtSchema->execute([$schemaId]);
    $schema_json = $stmtSchema->fetchColumn();
    if ($schema_json) {
        $loaded_shifts = json_decode($schema_json, true);
        foreach ($loaded_shifts as $i => &$shift) {
            if (!is_numeric($i) || $i % 2 !== 1 || !isset($loaded_shifts[$i - 1])) continue;
            
            $expectedIn = [];
            $expectedOut = [];
            foreach ($loaded_shifts[$i - 1]['lineup'] as $pos => $speler_oud) {
                if (isset($shift['lineup'][$pos])) {
                    $speler_nieuw = $shift['lineup'][$pos];
                    if ($speler_oud !== $speler_nieuw) {
                        $expectedIn[$pos] = $speler_nieuw;
                        $expectedOut[$pos] = $speler_oud;
                    }
                }
            }
            if (!isset($shift['subs'])) $shift['subs'] = [];
            $shift['subs']['in'] = $expectedIn;
            $shift['subs']['out'] = $expectedOut;
        }
        $stmtUpd = $pdo->prepare("UPDATE lineups SET schema_data = ? WHERE id = ?");
        $stmtUpd->execute([json_encode($loaded_shifts), $schemaId]);
        header("Location: /admin/inspect_schema?schema=" . urlencode($schemaId) . "&format=" . urlencode($format) . "&fixed=1");
        exit;
    }
}

// Format fallback ophalen van de database als the parameter leeg was in de submit (want dat gebeurt soms bij handmatige ID's)
if (empty($format) && $schemaId) {
    $stmtF = $pdo->prepare("SELECT game_format FROM lineups WHERE id = ?");
    $stmtF->execute([$schemaId]);
    $format = $stmtF->fetchColumn() ?: '8v8_0gk_4x15_9sp'; // Safe fallback
}

$stmtSchema = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
$stmtSchema->execute([$schemaId]);
$schema_json = $stmtSchema->fetchColumn();

if (!$schema_json) {
    echo "<div class='container mt-5'><div class='alert alert-warning shadow-sm fw-bold border-warning'><i class='fa-solid fa-triangle-exclamation me-2'></i>Schema ID <strong>$schemaId</strong> niet gevonden in de database.</div></div>";
    require_once __DIR__ . '/../footer.php';
    exit;
}

$shifts = json_decode($schema_json, true);

// Fake player names database voor visualisatie - zorg voor genoeg namen om "dubbele" te vermijden!
$fakeNames = ["Loris", "Arda", "Miel", "Jack", "Jayden", "Seppe", "Tiebe", "Murat", "Vinn", "Rune", "Staf", "Daan", "Bram", "Tom", "Lars", "Jesse", "Milan", "Noah", "Sem", "Lucas", "Liam", "Finn", "Mason", "Luuk"];

// Extract playercount
preg_match('/_(\\d+)sp$/', $format, $matches);
$playercount = isset($matches[1]) ? (int)$matches[1] : 0;
if ($playercount == 0) {
    // deduce from first shift
    $playercount = count($shifts[0]['lineup']) + count($shifts[0]['bench']);
}

// Genereer de unieke reeks playernames voor deze specifieke run
$simPlayers = [];
for ($i=0; $i<$playercount; $i++) {
    $simPlayers[$i] = $fakeNames[$i % count($fakeNames)] . " ($i)";
}

// Bereken stats in een Pre-pass zodat foutmeldingen reeds toegang hebben tot finale data
$playtimes = array_fill(0, $playercount, 0);
$playtimesPos1 = array_fill(0, $playercount, 0);
$playerPositionsGrid = []; // [player_id => [shift_id => pos]]
$logic_errors = [];
$has_subs_error = false;
$shiftCount = 0;

foreach ($shifts as $i => $shift) {
    if (!is_numeric($i)) continue;
    $shiftCount++;
    $dur = $shift['duration'] ?? 0;
    foreach ($shift['lineup'] as $pos => $pid) {
        $playtimes[$pid] += $dur;
        if ($pos == 1) {
            $playtimesPos1[$pid] += $dur;
        }
        $playerPositionsGrid[$pid][$i] = $pos;
    }
    foreach ($shift['bench'] as $pid) {
        $playerPositionsGrid[$pid][$i] = 'BANK';
    }
}

// Check run for errors
foreach ($shifts as $i => $shift) {
    if (!is_numeric($i)) continue;
    
    $w = floor($i / 2) + 1;
    $h = ($i % 2) + 1;
    $context = "Wedstrijd $w (Helft $h)";
    
    // Check 1 en 2: Missing of Dubbele spelers
    $fieldPlayers = array_values($shift['lineup'] ?? []);
    $benchPlayers = array_values($shift['bench'] ?? []);
    $allAssignedPlayers = array_merge($fieldPlayers, $benchPlayers);
    $missing = array_diff(range(0, $playercount - 1), $allAssignedPlayers);
    $duplicate = array_diff_assoc($allAssignedPlayers, array_unique($allAssignedPlayers));
    
    if (!empty($missing)) {
        $logic_errors[] = "<strong>{$context}:</strong> Mist speler(s): " . implode(', ', array_map(function($id){return "<code>Speler {$id}</code>";},$missing));
    }
    if (!empty($duplicate)) {
        $logic_errors[] = "<strong>{$context}:</strong> Heeft dubbele spelers in de array toegewezen.";
    }

    // Check 3 en 4: Substituties en bankcontroles (op de 2e helft van een game / oneven index)
    if ($i % 2 === 1) { // 1, 3, 5, 7 zijn "helftje 2"
        $prevShift = $shifts[$i - 1];
        
        // Regel 3: Check of iemand 2x na elkaar ("2 helftjes") op de bank zit
        $prevBench = array_values($prevShift['bench'] ?? []);
        $currLineup = array_values($shift['lineup'] ?? []);
        foreach ($prevBench as $benchSitter) {
            // Goalies mogen gerust 2 helften banken (een hele wedstrijd)
            if ($benchSitter < $gk_count || ($playtimes[$benchSitter] > 0 && $playtimes[$benchSitter] === $playtimesPos1[$benchSitter])) {
                continue; 
            }
            if (!in_array($benchSitter, $currLineup)) {
                $speelMins = $playtimes[$benchSitter] / 60;
                $logic_errors[] = "<i class=\"fa-regular fa-hand-point-right me-2 text-primary\"></i> Zoals je kan zien in onderstaand schema staat <strong>Speler {$benchSitter}</strong> in <strong>Wedstrijd {$w}</strong> zowel in helft 1 als 2 op de bank. Kijk naar zijn totale speelminuten ({$speelMins} min) en zoek een speler die in die periode wél (lang) op het veld staat. Die kan waarschijnlijk gerust zijn positie ruilen met speler {$benchSitter} zonder de tijdslimiet-theorie te breken. (Referentie: Index ".($i-1)." en Index {$i}).";
            }
        }
        
        // Regel 4: subs->out list klopt wiskundig niet
        $expectedIn = [];
        $expectedOut = [];
        $actualIn = $shift['subs']['in'] ?? [];
        $actualOut = $shift['subs']['out'] ?? [];
        foreach ($prevShift['lineup'] as $pos => $speler_oud) {
            if (isset($shift['lineup'][$pos])) {
                $speler_nieuw = $shift['lineup'][$pos];
                if ($speler_oud !== $speler_nieuw) {
                    $expectedIn[$pos] = $speler_nieuw;
                    $expectedOut[$pos] = $speler_oud;
                }
            }
        }
        if ($expectedIn != $actualIn) {
            $logic_errors[] = "<strong>{$context}:</strong> De berekende <code>subs->in</code> array klopt niet wiskundig (Inkomende bankspelers).";
            $has_subs_error = true;
        }
        if ($expectedOut != $actualOut) {
            $logic_errors[] = "<strong>{$context}:</strong> De berekende <code>subs->out</code> array is wiskundig onjuist qua posities (Vergeleken met de vorige shift lineup).";
            $has_subs_error = true;
        }
    }
}

// Bepaal de balansfouten direct hier, VOORDAT we de auto-gen in gang zetten!
$max_game_duration = 0;
foreach($shifts as $i => $s) {
    if (is_numeric($i)) $max_game_duration += ($s['duration'] ?? 0);
}

$filtered_playtimes = [];
$full_time_players = [];
for ($p=0; $p<$playercount; $p++) {
    // Check of de speler een toegewijde doelman is (al zijn speelminuten zijn op pos 1)
    if ($playtimes[$p] > 0 && $playtimes[$p] === $playtimesPos1[$p]) {
        $full_time_players[] = $p;
    } else {
        $filtered_playtimes[$p] = $playtimes[$p];
    }
}

$unique_playtimes = array_unique($filtered_playtimes);
$unique_playtimes_min = array_map(function($val) { return $val / 60; }, $unique_playtimes);

$total_pos = count($shifts[0]['lineup']) - count($full_time_players);
$total_slots = $total_pos * $shiftCount;
$rotating_playercount = $playercount - count($full_time_players);

$ideal_slots_per_player = floor($total_slots / $rotating_playercount);
$remainder = $total_slots % $rotating_playercount;
$ideal_time_base = $ideal_slots_per_player * ($shifts[0]['duration']);
$ideal_time_offset = ($ideal_slots_per_player + 1) * ($shifts[0]['duration']);

$is_broken = (count($unique_playtimes) > 2) || (count($logic_errors) > 0);
?>

<div class="container mt-4">
    <h2><i class="fa-solid fa-stethoscope"></i> Schema Diagnostic Viewer</h2>
    <p class="text-muted">Analyseer raw data matrices op oneerlijke speeltijd of validatiefouten.</p>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-bold">Bestand</label>
                    <input type="text" name="format" class="form-control" value="<?= htmlspecialchars($format) ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0 fw-bold">Schema ID</label>
                    <input type="number" name="schema" class="form-control" value="<?= $schemaId ?>">
                </div>
                <div class="col-auto">
                   <br>
                   <button type="submit" class="btn btn-primary d-block">Laad Visualisatie</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php
    // We ontleden het format '8v8_0gk_4x15_9sp' om de DB properties te bepalen.
    if (preg_match('/^(\d+v\d+)_(\d+)gk_(\d+x\d+)_(\d+)sp$/', $format, $matches)) {
        $db_format = $matches[1] . '_' . $matches[3]; // '8v8_4x15'
        $req_gk = (int)$matches[2];
        $req_sp = (int)$matches[4];
    } else {
        $db_format = $format;
        $req_gk = 0;
        $req_sp = 9;
    }

    // Zoek in de geschiedenis naar een geldig game_id, maar ALLEEN als we effectief een editor knop nodig hebben (bij fouten of overrides)
    $editorContext = null;
    if ($is_broken) {
        $stmt = $pdo->prepare("
            SELECT gl.game_id, gl.player_order 
            FROM game_lineups gl 
            JOIN games g ON g.id = gl.game_id 
            WHERE gl.schema_id = ? 
            AND g.format = ? 
            AND (SELECT IFNULL(SUM(is_goalkeeper), 0) FROM game_selections sq WHERE sq.game_id = g.id) = ?
            AND (SELECT COUNT(*) FROM game_selections sq WHERE sq.game_id = g.id) = ?
            ORDER BY gl.id DESC LIMIT 1
        ");
        $stmt->execute([$schemaId, $db_format, $req_gk, $req_sp]);
        $editorContext = $stmt->fetch();
        
        // Als we niets vinden in de echte speelgeschiedenis, zoek dan naar een reeds bestaande Dummy Match van een vorige laadactie!
        if (!$editorContext) {
            $stmtCheck = $pdo->prepare("
                SELECT g.id as game_id 
                FROM games g
                WHERE g.opponent = 'DUMMY REVISOR MATCH (Tijdelijk)' 
                AND g.format = ?
                AND (SELECT IFNULL(SUM(is_goalkeeper), 0) FROM game_selections sq WHERE sq.game_id = g.id) = ?
                AND (SELECT COUNT(*) FROM game_selections sq WHERE sq.game_id = g.id) = ?
                ORDER BY g.id DESC LIMIT 1
            ");
            $stmtCheck->execute([$db_format, $req_gk, $req_sp]);
            $existingDummy = $stmtCheck->fetch();
            
            if ($existingDummy) {
                // Hergebruik hem en reconstrueer volgorde
                $stmtOrder = $pdo->prepare("SELECT player_id FROM game_selections WHERE game_id = ? ORDER BY id ASC");
                $stmtOrder->execute([$existingDummy['game_id']]);
                $sel_order = $stmtOrder->fetchAll(PDO::FETCH_COLUMN);
                $editorContext = [
                    'game_id' => $existingDummy['game_id'],
                    'player_order' => implode(',', $sel_order),
                    'is_dummy' => false // Geen nieuwe aanmaak waarschuwing nodig
                ];
            } else {
                // Maak neppe game aan met PURE db format string (Enkel indien écht nóóit eerder aangemaakt)
                $stmtInsert = $pdo->prepare("INSERT INTO games (team_id, opponent, game_date, format, min_pos, coach_id) VALUES (1, 'DUMMY REVISOR MATCH (Tijdelijk)', CURDATE(), ?, 1, NULL)");
                $stmtInsert->execute([$db_format]);
                $dummyId = $pdo->lastInsertId();
                
                // Haal echte player IDs op (voorkom dubbele first_names in editor simulatie view)
                $realPlayers = $pdo->query("SELECT MIN(id) FROM players GROUP BY first_name LIMIT $req_sp")->fetchAll(PDO::FETCH_COLUMN);
        
                $stmtSel = $pdo->prepare("INSERT INTO game_selections (game_id, player_id, status_id, is_goalkeeper) VALUES (?, ?, 2, ?)");
                $dummy_order = [];
                for ($s = 0; $s < $req_sp; $s++) {
                    $is_gk = ($s < $req_gk) ? 1 : 0;
                    $fake_pid = isset($realPlayers[$s]) ? $realPlayers[$s] : (1000 + $s); 
                    $stmtSel->execute([$dummyId, $fake_pid, $is_gk]);
                    $dummy_order[] = $fake_pid;
                }
                
                $editorContext = [
                    'game_id' => $dummyId,
                    'player_order' => implode(',', $dummy_order),
                    'is_dummy' => true
                ];
            }
        }
    }
    ?>

    <h4>Overzicht: <?= htmlspecialchars($format) ?> (ID: <?= $schemaId ?>)</h4>
    
    <?php if (!empty($editorContext['is_dummy'])): ?>
    <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4 mb-4">
        <i class="fa-solid fa-robot text-warning me-2 fs-5"></i>
        <strong>Auto-Gen:</strong> Er bestond nog géén enkele historische match voor <code><?= htmlspecialchars($format) ?></code> in je administratie om de editor test-context mee aan op te starten. Het systeem heeft razendsnel een neppe match ("DUMMY REVISOR MATCH") voor je klaargezet. Je kunt deze eventueel achteraf opruimen via je Wedstrijden overzicht.
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['fixed'])): ?>
    <div class="alert alert-success shadow-sm fw-bold border-0 border-start border-success border-4 mb-4">
        <i class="fa-solid fa-check-circle text-success me-2 fs-5"></i>
        Subs array integriteit is succesvol mathematisch hersteld in the database voor Schema <?= $schemaId ?>! Je unit tests zouden dit nu moeten goedkeuren.
    </div>
    <?php endif; ?>
    
    <!-- Diagnose box & Real-World Timeline verplaatst naar boven -->
<?php
        $suggested_fixes = [];
        if ($is_broken) {
            $players_too_much = [];
            $players_too_little = [];
            
            // Bepaal de 'ideale' speeltijd
            $ideal_lower = $ideal_time_base;          
            $ideal_upper = $ideal_time_offset;        
            
            // Simpelste benadering: We zoeken de allerhoogste en allerlaagste in de FILTERED array
            arsort($filtered_playtimes);
            $highest_players = [];
            $lowest_players = [];
            $highest_val = max($filtered_playtimes);
            $lowest_val = min($filtered_playtimes);

            foreach($filtered_playtimes as $p => $t) {
                if ($t == $highest_val && $t > $ideal_upper) $highest_players[] = $p;
                if ($t == $lowest_val && $t < $ideal_lower) $lowest_players[] = $p;
            }
            
            // Zoek een gemeenschappelijke shift waar HogeSpeler speelt, en LageSpeler rust:
            foreach ($highest_players as $hp) {
                foreach ($lowest_players as $lp) {
                    foreach($shifts as $i => $s) {
                        if (!is_numeric($i)) continue;
                        $hp_on_field = in_array($hp, $s['lineup']);
                        $lp_on_bench = in_array($lp, $s['bench']);
                        if ($hp_on_field && $lp_on_bench) {
                            $suggested_fixes[] = "Wissel in <strong>Shift ".($i+1)."</strong> de veldpositie van <code>{$simPlayers[$hp]}</code> om met de bank-positie van <code>{$simPlayers[$lp]}</code> (Dit verplaatst ".($s['duration'] / 60)." min).";
                            break 2;
                        }
                    }
                }
            }
        }
    ?>

    <div class="row mt-4 mb-5">
        <div class="col-12">
            <h4 class="mb-3"><i class="fa-solid fa-bug"></i> Fout Assistent</h4>
            <?php if (!$is_broken): ?>
            <div class="alert alert-success shadow-sm">
                <h5><i class="fa-solid fa-check"></i> Schemamatrix in balans</h5>
                Dit schema kent <strong><?= count($unique_playtimes) ?></strong> distributie waardes (<?= implode(' en ', $unique_playtimes_min) ?> minuten). Dit valt binnen de wiskundige grens (maximaal 2 afwijkingen). De test suites beschouwen dit schema als <span class="badge bg-success">Valid</span>!
            </div>
            <?php else: ?>
            <div class="alert alert-danger shadow-sm border-0 text-dark">
                <?php if (count($logic_errors) > 0): ?>
                    <h5 class="text-danger"><i class="fa-solid fa-bug"></i> Ongeldige Wissels!</h5>
                    <p>Het bestand bevat wiskundige of logische fouten wegens ongeldige spelerswissels:</p>
                    <ul>
                        <?php foreach($logic_errors as $le): ?>
                            <li><?= $le ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($has_subs_error): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Subs Array Validatie Fout</strong> 
                            <p class="mb-2 mt-1 small">De <code>subs->in</code> of <code>subs->out</code> tabellen zijn waarschijnlijk historisch defect geraakt. Omdat the drag-and-drop editor visueel deze abstracte metadata niet toont, kan je dit nu hier in 1 klap mathematisch kalibreren d.m.v the lineup rijen met elkaar te vergelijken.</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="autofix_subs">
                                <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Repareer Subs Automatisch</button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (count($unique_playtimes) > 2): ?>
                <?php if (count($logic_errors) > 0): ?><hr><?php endif; ?>
                <h5 class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Schemamatrix Disbalans Gedetecteerd!</h5>
                <p>Spelers klokken af op <strong><?= count($unique_playtimes) ?></strong> verschillende eindtijd totalen (<?= implode(', ', $unique_playtimes_min) ?> min in plaats van maximaal 2 varianten). Dit is oneerlijk t.o.v. de stamboek theorie.</p>
                <hr>
                <strong><i class="fa-solid fa-calculator"></i> Matrix Analyse:</strong><br>
                <?php if (count($full_time_players) > 0): ?>
                - <span class="text-primary fw-bold"><?= count($full_time_players) ?> speler(s) negeren we.</span> Zij bezetten positie 1 onafgebroken voor de volle <?= $max_game_duration/60 ?> min.<br>
                <?php endif; ?>
                - Totaal op te vullen resterende wissel-slots in theorie: <?= $total_slots ?><br>
                - Dat delen we door de <?= $rotating_playercount ?> roterende veldspelers. Iedereen krijgt mathematisch recht op <code><?= $ideal_slots_per_player ?> slots</code>, en exact <code><?= $remainder ?></code> willekeurige speler(s) krijgen een extra looptijd van <code><?= $ideal_slots_per_player + 1?> slots</code>.<br>
                - Ideale speeltijden zijn dus exact <strong><?= $ideal_time_base / 60 ?> minuten</strong> en <strong><?= $ideal_time_offset / 60 ?> minuten</strong>. Niets anders is wiskundig aanvaardbaar!<br>
                <br>
                <strong><i class="fa-solid fa-lightbulb"></i> Auto-Fix Suggestie Opties:</strong><br>
                <ul class="mb-0 mt-2">
                    <?php if (!empty($suggested_fixes)): ?>
                        <?php foreach($suggested_fixes as $fix): ?>
                            <li><i class="fa-solid fa-wrench text-primary"></i> <?= $fix ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Automatische suggesties zijn nog niet mogelijk. Controleer de wisselpatronen handmatig via de code of in de editor.</li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
                
                <?php if ($editorContext): ?>
                    <div class="mt-4 border-top pt-3">
                        <a href="/schema_editor?game_id=<?= $editorContext['game_id'] ?>&schema_id=<?= $schemaId ?>&volgorde=<?= urlencode($editorContext['player_order']) ?>&overwrite_mode=1" class="btn btn-warning shadow-sm fw-bold">
                            <i class="fa-solid fa-hammer"></i> Open in Visual Revisor Modus
                        </a>
                        <small class="text-muted d-block mt-1">Hier kan je het schema grafisch herbouwen en bij opslaan <strong>overschrijft hij keihard het brondocument</strong>!</small>
                    </div>
                <?php else: ?>
                    <div class="mt-4 border-top pt-3 text-muted"><i class="fa-solid fa-circle-info"></i> Geen historische game match gevonden om de revisor editor in te kunnen laden.</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead>
                <tr>
                    <th>Speler Index</th>
                    <?php 
                        foreach($shifts as $i => $s) {
                            if (!is_numeric($i)) continue;
                            $gameNo = floor($i / 2) + 1;
                            $halfNo = ($i % 2) + 1;
                            echo "<th>Wedstrijd $gameNo<br><small class='text-muted fw-normal'>Helft $halfNo</small></th>";
                        }
                    ?>
                    <th>Totale Speeltijd</th>
                </tr>
            </thead>
            <tbody>
                <?php for($p = 0; $p < $playercount; $p++): ?>
                <tr>
                    <td class="fw-bold bg-light"><?= $simPlayers[$p] ?></td>
                    <?php for($i = 0; $i < $shiftCount; $i++): 
                        $state = $playerPositionsGrid[$p][$i] ?? '?';
                        if ($state === 'BANK') {
                            $badge = "<span class='badge bg-danger ps-3 pe-3 py-2'>BANK</span>";
                        } else {
                            $badge = "<span class='badge bg-success shadow-sm'>Pos: $state</span>";
                        }
                    ?>
                        <td><?= $badge ?></td>
                    <?php endfor; ?>
                    <td class="fw-bold fs-5 text-primary">
                        <?= ($playtimes[$p] / 60) ?> min
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
    
    <div class="alert alert-info mt-3 shadow-sm border-0">
        <i class="fa-solid fa-circle-info me-2"></i> <strong>Opmerking speeltijd theorie:</strong><br>
        Als er meer dan 2 verschillende waardes staan in de kolom "Totale Speeltijd" bij de matrix, faalt dit visueel en breekt de unit test.
    </div>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
