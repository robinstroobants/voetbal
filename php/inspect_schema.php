<?php
// inspect_schema.php
require_once 'getconn.php';
$page_title = 'Schema Diagnostics';
require_once 'header.php'; // Zorg dat we de admin layout pakken

$format = $_GET['format'] ?? '8v8_0gk_4x15_9sp';
$schemaId = isset($_GET['schema']) ? (int)$_GET['schema'] : 20000;

$stmtSchema = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
$stmtSchema->execute([$schemaId]);
$schema_json = $stmtSchema->fetchColumn();

if (!$schema_json) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Schema $schemaId niet gevonden in de database.</div></div>";
    require_once 'footer.php';
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
            if ($benchSitter < $gk_count) continue; // Goalies mogen gerust 2 helften banken (een hele wedstrijd)
            if (!in_array($benchSitter, $currLineup)) {
                $speelMins = $playtimes[$benchSitter] / 60;
                $logic_errors[] = "<i class=\"fa-regular fa-hand-point-right me-2 text-primary\"></i> Zoals je kan zien in onderstaand schema staat <strong>Speler {$benchSitter}</strong> in <strong>Wedstrijd {$w}</strong> zowel in helft 1 als 2 op de bank. Kijk naar zijn totale speelminuten ({$speelMins} min) en zoek een speler die in die periode wél (lang) op het veld staat. Die kan waarschijnlijk gerust zijn positie ruilen met speler {$benchSitter} zonder de tijdslimiet-theorie te breken. (Referentie: Index ".($i-1)." en Index {$i}).";
            }
        }
        
        // Regel 4: subs->out list klopt wiskundig niet
        $expectedOut = [];
        $actualOut = $shift['subs']['out'] ?? [];
        foreach ($prevShift['lineup'] as $pos => $speler_oud) {
            if (isset($shift['lineup'][$pos])) {
                $speler_nieuw = $shift['lineup'][$pos];
                if ($speler_oud !== $speler_nieuw) {
                    $expectedOut[$pos] = $speler_oud;
                }
            }
        }
        if ($expectedOut != $actualOut) {
            $logic_errors[] = "<strong>{$context}:</strong> De berekende <code>subs->out</code> array in het PHP bestand bij index {$i} is wiskundig onjuist qua posities of veldspelers (vergeleken met de lineup van index ".($i-1).").";
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
    if ($playtimesPos1[$p] >= $max_game_duration) {
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
        <strong>Auto-Gen:</strong> Er bestond nog géén enkele historische match voor <code><?= htmlspecialchars($format) ?></code> in je administratie om de editor test-context mee aan op te starten. Het systeem heeft razendsnel een neppe match ("DUMMY REVISOR MATCH") voor je klaargezet. Je kunt deze eventueel achteraf opruimen via je <a href="manage_games.php" class="fw-bold">Wedstrijden overzicht</a>.
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
                        <a href="schema_editor.php?game_id=<?= $editorContext['game_id'] ?>&schema_id=<?= $schemaId ?>&volgorde=<?= urlencode($editorContext['player_order']) ?>&overwrite_mode=1" class="btn btn-warning shadow-sm fw-bold">
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
<?php require_once 'footer.php'; ?>
