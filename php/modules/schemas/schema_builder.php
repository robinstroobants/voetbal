<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/models/MatchManager.php';

$gameId = $_GET['game_id'] ?? 0;
if (!$gameId) die("Game ID ontbreekt");

$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getSelection($gameId);

if (empty($matchData)) {
    die("Kan selectie en format niet inladen voor game " . htmlspecialchars($gameId));
}

$format = $matchData['format'];
$doelmannen = $matchData['doelmannen'] ?? '';
$selectie = $matchData['selectie'] ?? '';

// Array van spelers bepalen
$gk_arr = array_filter(array_map('trim', explode(',', $doelmannen)));
$sel_arr = array_filter(array_map('trim', explode(',', $selectie)));
$squad = array_merge($gk_arr, $sel_arr);
$aantal = count($squad);
$gk_count = count($gk_arr);

// Zorg er voor dat we $search_format hebben (bv 8v8_1gk_4x15)
$search_format = $format;
if (strpos($format, 'gk') === false) {
    if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $format, $matches)) {
        $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
    }
}
$full_format = $search_format . "_" . $aantal . "sp";

// Aantal blokken en speelduur (in minutes!) extraheren
$nr_of_games = 4;
$game_duration_min = 15;
$sub_duration_min_parsed = 15;

if (preg_match('/_(\d+)x(\d+)(?:_([0-9.]+)min)?$/', $search_format, $m)) {
    $nr_of_games = (int)$m[1];
    $game_duration_min = (int)$m[2];
    $sub_duration_min_parsed = isset($m[3]) ? (float)$m[3] : $game_duration_min;
}

// Build patterns
$patterns = [];

// 1. Standaard helften (if parsed is different from game duration)
if ($sub_duration_min_parsed != $game_duration_min) {
    $blocks = [];
    $part_count = $game_duration_min / $sub_duration_min_parsed;
    for ($i=0; $i<$nr_of_games; $i++) {
        for ($j=0; $j<$part_count; $j++) {
            $blocks[] = $sub_duration_min_parsed;
        }
    }
    $patterns['default'] = [
        'name' => "Standaard: Wissel om de {$sub_duration_min_parsed}m",
        'blocks' => $blocks
    ];
}

// 2. Niet wisselen
$blocks = array_fill(0, $nr_of_games, $game_duration_min);
$patterns['no_sub'] = [
    'name' => "Niet wisselen ($nr_of_games wedstrijden van {$game_duration_min}m)",
    'blocks' => $blocks
];

// 3. Halverwege (if game_duration_min > 5 and even or 15)
if ($game_duration_min % 2 == 0 || $game_duration_min == 15) {
    $blocks = [];
    $half = $game_duration_min / 2;
    for ($i=0; $i<$nr_of_games; $i++) {
        $blocks[] = $half;
        $blocks[] = $half;
    }
    if (!isset($patterns['default']) || $patterns['default']['blocks'] !== $blocks) {
        $patterns['half'] = [
            'name' => "Wissel halverwege (Helften van {$half}m)",
            'blocks' => $blocks
        ];
    }
}

// 4. Custom 10-5 for 15min games
if ($game_duration_min == 15 && $nr_of_games >= 2) {
    $blocks = [];
    for ($i=0; $i<$nr_of_games; $i++) {
        if ($i < 2) {
            $blocks[] = 7.5; $blocks[] = 7.5;
        } else if ($i % 2 == 0) {
            $blocks[] = 10; $blocks[] = 5;
        } else {
            $blocks[] = 5; $blocks[] = 10;
        }
    }
    $patterns['custom_10_5_end'] = [
        'name' => "W1&W2 helften, W3(10m-5m), W4(5m-10m)",
        'blocks' => $blocks
    ];
    
    $blocks_start = [];
    for ($i=0; $i<$nr_of_games; $i++) {
        if ($i >= 2) {
            $blocks_start[] = 7.5; $blocks_start[] = 7.5;
        } else if ($i % 2 == 0) {
            $blocks_start[] = 10; $blocks_start[] = 5;
        } else {
            $blocks_start[] = 5; $blocks_start[] = 10;
        }
    }
    $patterns['custom_10_5_start'] = [
        'name' => "W1(10m-5m), W2(5m-10m), W3&W4 helften",
        'blocks' => $blocks_start
    ];
    
    $blocks2 = [];
    for ($i=0; $i<$nr_of_games; $i++) {
        if ($i % 2 == 0) {
            $blocks2[] = 10; $blocks2[] = 5;
        } else {
            $blocks2[] = 5; $blocks2[] = 10;
        }
    }
    $patterns['custom_10_5_all'] = [
        'name' => "Afwisselend 10m-5m en 5m-10m per wedstrijd",
        'blocks' => $blocks2
    ];
    
    $blocks3 = [];
    for ($i=0; $i<$nr_of_games; $i++) {
        if ($i % 2 == 0) {
            $blocks3[] = 5; $blocks3[] = 10;
        } else {
            $blocks3[] = 10; $blocks3[] = 5;
        }
    }
    $patterns['custom_5_10_all'] = [
        'name' => "Afwisselend 5m-10m en 10m-5m per wedstrijd",
        'blocks' => $blocks3
    ];
}

$selected_pattern_key = $_GET['pattern'] ?? (isset($patterns['half']) ? 'half' : 'default');
if (!isset($patterns[$selected_pattern_key])) {
    $selected_pattern_key = array_key_first($patterns);
}
$selected_pattern = $patterns[$selected_pattern_key];

// Generate shift definitions with smart labels
$shift_definitions = [];
$game_idx = 1;
$current_game_min = 0;
$part_idx = 1;
$total_minutes = 0;

foreach ($selected_pattern['blocks'] as $dur) {
    if ($current_game_min == 0) {
        $part_idx = 1;
    }
    
    $label = "Wedstrijd $game_idx";
    if ($dur < $game_duration_min) {
        $label .= " (Helft $part_idx)";
    }
    
    $shift_definitions[] = [
        'duration' => $dur,
        'label' => $label,
        'game_counter' => $game_idx
    ];
    
    $current_game_min += $dur;
    $total_minutes += $dur;
    if (abs($current_game_min - $game_duration_min) < 0.01) {
        $game_idx++;
        $current_game_min = 0;
    } else {
        $part_idx++;
    }
}
$number_of_shifts = count($shift_definitions);

// In the UI, the shifts will be generated by JS.
$player_info = $matchData['player_info'] ?? [];
$players_json = [];

// Volgorde parameter vastleggen (nodig voor save payload en API):
$volgorde = implode(',', $squad);

foreach ($squad as $idx => $pid) {
    $players_json[$pid] = [
        'id' => $pid,
        'sidx' => $idx,
        'name' => htmlspecialchars($player_info[$pid]['display_name'] ?? $player_info[$pid]['first_name'] ?? $pid),
        'is_gk' => ($idx < $gk_count)
    ];
}

// Fetch historical season stats for these exact players before this game
$teamId = $_SESSION['team_id'] ?? 0;
$gameDate = $matchData['game']['game_date'];
$seasonStatsData = $matchManager->getSeasonStatsForSelection($teamId, $gameDate, $squad);

// Map the DB PIDs to JS Sidx (array indices)
$seasonStatsJson = [];

// Check if team has ANY periods defined to show the toggle
$hasActivePeriod = false;
$stmtPeriodsCheck = $pdo->prepare("SELECT COUNT(*) FROM team_periods WHERE team_id = ?");
$stmtPeriodsCheck->execute([$teamId]);
if ($stmtPeriodsCheck->fetchColumn() > 0) {
    $hasActivePeriod = true;
}

foreach ($squad as $idx => $pid) {
    $st = $seasonStatsData[$pid] ?? ['played' => 0, 'bank' => 0, 'gk' => 0, 'available' => 0, 'period_played' => 0, 'period_available' => 0];
    // If user confirmed GK counts as played time: we use 'played' + 'gk' for total played.
    // However, game_playtime_logs `seconds_played` currently ALREADY INCLUDES gk time (as per syncGameLogs logic pos 1 counts as 'played').
    // So 'played' is the total field time.
    $seasonStatsJson[$idx] = [
        'histPlayed' => $st['played'],
        'histAvailable' => $st['available'],
        'periodPlayed' => $st['period_played'] ?? 0,
        'periodAvailable' => $st['period_available'] ?? 0
    ];
}

// Calculate pre-game analysis
$pregame_analysis_html = '';
$playPositions = [1, 2, 4, 5, 7, 9, 10, 11];
if (strpos($search_format, '5v5') !== false) {
    $playPositions = [1, 2, 4, 5, 9];
}
$fieldPositions = array_filter($playPositions, fn($p) => $p != 1);
$numFieldPositions = count($fieldPositions);

$fixedGkIdPHP = $gk_count === 1 ? (int)reset($gk_arr) : null;
$numFieldPlayers = count($squad) - ($fixedGkIdPHP !== null ? 1 : 0);
$totalBlocks = count($shift_definitions);
$totalFieldBlocks = $numFieldPositions * $totalBlocks;

if ($numFieldPlayers > 0 && $totalFieldBlocks > 0 && $fixedGkIdPHP !== null) {
    $block_dur = $shift_definitions[0]['duration'];
    $base_blocks = floor($totalFieldBlocks / $numFieldPlayers);
    $extra_blocks = $totalFieldBlocks % $numFieldPlayers;
    
    $players_extra = $extra_blocks;
    $players_base = $numFieldPlayers - $players_extra;
    
    $base_mins = $base_blocks * $block_dur;
    $extra_mins = $base_mins + $block_dur;
    
    if ($players_extra > 0) {
        $sortedPlayers = [];
        
        // Fetch last match playtime for these players
        $lastMatchPlaytimes = [];
        $placeholders = implode(',', array_fill(0, count($squad), '?'));
        // Get the latest game before this one for each player
        // Since we only need the last game, we can query the most recent game id per player,
        // but a simple ORDER BY with PHP filtering is usually fast enough for a small squad.
        $queryLastGame = "
            SELECT p.player_id, p.seconds_played, p.seconds_gk, p.seconds_bank, g.id as game_id, g.opponent, g.game_date, g.is_home
            FROM game_playtime_logs p
            JOIN games g ON p.game_id = g.id
            WHERE p.player_id IN ($placeholders) 
              AND g.team_id = ? 
              AND g.game_date < ?
            ORDER BY g.game_date DESC, g.id DESC
        ";
        $paramsLastGame = array_merge($squad, [$teamId, $gameDate]);
        $stmtLast = $pdo->prepare($queryLastGame);
        $stmtLast->execute($paramsLastGame);
        
        while ($row = $stmtLast->fetch(PDO::FETCH_ASSOC)) {
            $pid = $row['player_id'];
            if (!isset($lastMatchPlaytimes[$pid])) {
                $lastMatchPlaytimes[$pid] = [
                    'mins' => round($row['seconds_played'] / 60, 1),
                    'opponent' => $row['opponent'],
                    'date' => date('d-m-Y', strtotime($row['game_date'])),
                    'location' => isset($row['is_home']) && $row['is_home'] == 1 ? 'Thuis' : 'Uit',
                    'bank' => round($row['seconds_bank'] / 60, 1),
                    'gk' => round($row['seconds_gk'] / 60, 1),
                    'game_id' => $row['game_id']
                ];
            }
        }
        
        $minutesGroups = [];
        foreach ($squad as $idx => $pid) {
            if ($fixedGkIdPHP !== null && (int)$pid === $fixedGkIdPHP) continue;
            
            $st = $seasonStatsJson[$idx];
            $periodAvail = $st['periodAvailable'];
            $periodPct = $periodAvail > 0 ? ($st['periodPlayed'] / $periodAvail) : 0;
            
            $histAvail = $st['histAvailable'];
            $histPct = $histAvail > 0 ? ($st['histPlayed'] / $histAvail) : 0;
            
            $sortedPlayers[] = [
                'name' => htmlspecialchars($player_info[$pid]['display_name'] ?? $player_info[$pid]['first_name'] ?? $pid),
                'periodPct' => $periodPct,
                'histPct' => $histPct
            ];
            
            // For the last match display
            if ($fixedGkIdPHP !== null && (int)$pid === $fixedGkIdPHP) continue;
            
            $gameInfo = $lastMatchPlaytimes[$pid] ?? null;
            $mins = $gameInfo['mins'] ?? 0;
            
            if (!isset($minutesGroups[(string)$mins])) {
                $minutesGroups[(string)$mins] = [];
            }
            $pName = htmlspecialchars($player_info[$pid]['display_name'] ?? $player_info[$pid]['first_name'] ?? $pid);
            
            if ($gameInfo) {
                $titleText = htmlspecialchars("Gespeeld tegen " . $gameInfo['opponent'] . " op " . $gameInfo['date']);
                $contentHtml = htmlspecialchars(
                    "<div class='small'>" .
                    "<b>Tegenstander:</b> " . htmlspecialchars($gameInfo['opponent']) . "<br>" .
                    "<b>Locatie:</b> " . $gameInfo['location'] . "<br>" .
                    "<b>Datum:</b> " . $gameInfo['date'] . "<br>" .
                    "<b>Veld:</b> " . $gameInfo['mins'] . "m<br>" .
                    "<b>Doelman:</b> " . $gameInfo['gk'] . "m<br>" .
                    "<b>Bank:</b> " . $gameInfo['bank'] . "m" .
                    "</div>"
                );
                
                $minutesGroups[(string)$mins][] = "<strong class='text-dark' style='cursor: pointer; text-decoration: none; border-bottom: 1px solid transparent; transition: border-bottom 0.2s;' onmouseover='this.style.borderBottom=\"1px solid #000\"' onmouseout='this.style.borderBottom=\"1px solid transparent\"' title='" . $titleText . "' data-bs-toggle='popover' data-bs-trigger='focus' tabindex='0' data-bs-html='true' data-bs-title='Match Details' data-bs-content='" . $contentHtml . "'>$pName</strong>";
            } else {
                $minutesGroups[(string)$mins][] = "<strong>$pName</strong>";
            }
        }
        
        // Sort the minutes groups descending (highest minutes first, or you can do lowest first)
        krsort($minutesGroups);
        $lastMatchHtml = '';
        if (!empty($minutesGroups)) {
            $lastMatchHtml = '<div class="p-2 bg-white rounded border mb-2">';
            $lastMatchHtml .= '<p class="mb-1 fw-bold text-dark" style="font-size: 0.8rem;"><i class="fa-solid fa-clock-rotate-left text-secondary me-1"></i>Speeltijd vorige wedstrijd</p>';
            foreach ($minutesGroups as $mins => $names) {
                $lastMatchHtml .= '<p class="mb-0 text-muted" style="font-size: 0.75rem;">' . $mins . 'm: ' . implode(', ', $names) . '</p>';
            }
            $lastMatchHtml .= '</div>';
        }
        
        usort($sortedPlayers, function($a, $b) use ($hasActivePeriod) {
            if ($hasActivePeriod) {
                if (abs($a['periodPct'] - $b['periodPct']) > 0.001) {
                    return $a['periodPct'] <=> $b['periodPct'];
                }
            }
            if (abs($a['histPct'] - $b['histPct']) > 0.001) {
                return $a['histPct'] <=> $b['histPct'];
            }
            return strcmp(strtolower($a['name']), strtolower($b['name']));
        });
        
        $suggestedExtra = array_slice($sortedPlayers, 0, $players_extra);
        $suggestedBase = array_slice($sortedPlayers, $players_extra);
        
        $extraNames = array_map(fn($p) => "<strong>" . $p['name'] . "</strong>", $suggestedExtra);
        $baseNames = array_map(fn($p) => "<strong>" . $p['name'] . "</strong>", $suggestedBase);
        
        $pregame_analysis_html = '
        <div class="card mb-3 border-info shadow-sm" style="border-width: 2px;">
            <div class="card-header bg-info text-white fw-bold d-flex align-items-center py-2" style="font-size: 0.9rem; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#pregameCollapse" aria-expanded="true">
                <i class="fa-solid fa-lightbulb text-warning me-2"></i> Pre-Game Analyse
                <i class="fa-solid fa-chevron-down ms-auto"></i>
            </div>
            <div class="collapse show" id="pregameCollapse">
                <div class="card-body bg-light text-dark p-3">
                    <p class="mb-2" style="font-size: 0.8rem; line-height: 1.3;">Met ' . $numFieldPlayers . ' veldspelers voor ' . $numFieldPositions . ' posities resulteert dit in:</p>
                    <ul class="mb-3" style="font-size: 0.8rem; line-height: 1.3; padding-left: 20px;">
                        <li><strong>' . $players_extra . ' spelers</strong> spelen <strong>' . $extra_mins . 'm</strong> (' . ($base_blocks + 1) . ' blokjes)</li>
                        <li><strong>' . $players_base . ' spelers</strong> spelen <strong>' . $base_mins . 'm</strong> (' . $base_blocks . ' blokjes)</li>
                    </ul>
                    ' . $lastMatchHtml . '
                    <div class="p-2 bg-white rounded border mb-2">
                        <p class="mb-1 fw-bold text-success" style="font-size: 0.8rem;"><i class="fa-solid fa-arrow-up me-1"></i>Meeste minuten (' . $extra_mins . 'm)</p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Aanbevolen: ' . implode(', ', $extraNames) . '</p>
                    </div>
                    <div class="p-2 bg-white rounded border">
                        <p class="mb-1 fw-bold text-danger" style="font-size: 0.8rem;"><i class="fa-solid fa-arrow-down me-1"></i>Minste minuten (' . $base_mins . 'm)</p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Aanbevolen: ' . implode(', ', $baseNames) . '</p>
                    </div>
                </div>
            </div>
        </div>';
    }
}

$page_title = "Bouw Schema Manueel";
require_once dirname(__DIR__, 2) . '/header.php';
?>
<style>
.pool-container { background: #f8f9fa; min-height: 400px; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
.pool-player { background: #0d6efd; color: white; padding: 10px; margin-bottom: 8px; border-radius: 6px; cursor: grab; text-align: center; font-weight: bold; border: 2px solid transparent; transition: all 0.2s;}
.pool-player.is-gk { background: #dc3545; opacity: 0.8;}
.pool-player.on-bench-priority { background: #ffc107 !important; color: #000; border-color: #d39e00; }
.pos-wrapper { background: #fff; border: 2px dashed #ccc; border-radius: 6px; min-height: 40px; display: flex; align-items: center; justify-content: center; position: relative; margin-bottom: 10px;}
.pos-wrapper[data-pos="bench"] { border-color: #ffc107; background: #fff8e1; }
.pos-badge { position: absolute; top: -10px; left: 5px; background: #6c757d; color: white; font-size: 0.6rem; padding: 2px 5px; border-radius: 8px; z-index: 2;}
.pos-wrapper .pool-player { margin-bottom: 0; width: 100%; border-radius: 4px; padding: 3px; font-size: 0.85rem; z-index: 1;}

.shift-block.locked { opacity: 0.6; filter: grayscale(50%); }
.shift-block.locked .card-body { pointer-events: none; }
.shift-block.locked .card-header { background: #e9ecef !important; }
</style>

<div class="container mt-4 mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fa-solid fa-hammer text-warning me-2"></i>Schema Builder</h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Wedstrijd format: <?= $search_format ?></span>
                <div class="d-flex align-items-center bg-white border rounded px-2 py-1 shadow-sm">
                    <label class="small text-muted me-2 fw-bold text-nowrap"><i class="fa-solid fa-stopwatch me-1"></i>Wisselpatroon:</label>
                    <select class="form-select form-select-sm border-0 fw-bold" style="background-color: transparent; width: auto;" onchange="if(confirm('Als je het patroon wijzigt, wordt je huidige schema gereset. Doorgaan?')) window.location.href='?game_id=<?= $gameId ?>&pattern='+this.value">
                        <?php foreach($patterns as $key => $pattern): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $key === $selected_pattern_key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pattern['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-2 d-flex align-items-center gap-4">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="copyLineupToggle" checked style="cursor: pointer;">
                    <label class="form-check-label small text-muted fw-bold" for="copyLineupToggle" style="cursor: pointer; padding-top:2px;">
                        Opstelling enkel overnemen binnen de helftjes van dezelfde wedstrijd
                    </label>
                </div>
                <?php if($hasActivePeriod): ?>
                <div class="form-check form-switch mb-0" title="Schakel in om de sortering ook te baseren op de actuele periode">
                    <input class="form-check-input" type="checkbox" id="togglePeriodStats" checked onchange="calculateStats()" style="cursor: pointer;">
                    <label class="form-check-label small text-primary fw-bold" for="togglePeriodStats" style="cursor: pointer; padding-top:2px;">
                        Periode-statistieken gebruiken
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <a href="/games/<?= $gameId ?>/lineup" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left"></i> Terug</a>
            <button class="btn btn-success" onclick="saveNewSchema()" id="btnSave" disabled><i class="fa-solid fa-floppy-disk me-1"></i> Opslaan</button>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Pool -->
        <div class="col-md-3">
            <?= $pregame_analysis_html ?>
            
            <div class="pool-container sticky-top" style="top: 20px;">
                <h5 class="mb-3 text-dark"><i class="fa-solid fa-users me-2"></i>Selectie</h5>
                <div id="player-pool" class="d-flex flex-column gap-2">
                    <!-- JS fills this initially -->
                </div>
               
                <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
                    <h5 class="mb-0 text-dark"><i class="fa-solid fa-chart-line me-2"></i>Live Statistieken</h5>
                </div>
                <div class="table-responsive bg-white rounded shadow-sm mb-3">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Speler</th>
                                <th class="text-center">Minuten</th>
                                <?php if($hasActivePeriod): ?>
                                <th class="text-center period-col" title="Periode Historiek + deze Match">Periode %</th>
                                <?php endif; ?>
                                <th class="text-center" title="Seizoen Historiek + deze Match">Seizoen %</th>
                            </tr>
                        </thead>
                        <tbody id="live-stats-tbody">
                            <!-- JS fills this dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- The Canvas -->
        <div class="col-md-9">
            <div class="row" id="shifts-canvas">
                <!-- JS generates shifts here -->
            </div>
            
            <h4 class="mt-4 mb-3 text-dark d-none" id="position-stats-title"><i class="fa-solid fa-stopwatch text-primary me-2"></i>Posities per speler</h4>
            <div class="row" id="position-stats-canvas">
                <!-- JS generates position stats here -->
            </div>
        </div>
    </div>
</div>

<script>
// Data
const shiftDefinitions = <?= json_encode($shift_definitions) ?>;
const numShifts = <?= $number_of_shifts ?>;
const totalMinutes = <?= $total_minutes ?>;
const playerCount = <?= $aantal ?>;
const formatStr = "<?= $search_format ?>";
const playersMap = <?= json_encode($players_json) ?>;
const seasonStatsMap = <?= json_encode($seasonStatsJson) ?>;
const gameId = <?= $gameId ?>;
const volgordeStr = "<?= $volgorde ?>";
const gkCount = <?= $gk_count ?>;
const fixedGkId = <?= $gk_count === 1 ? json_encode((int)reset($gk_arr)) : 'null' ?>;

let playPositions = [1, 2, 4, 5, 7, 9, 10, 11];
if (formatStr.includes('5v5')) {
    playPositions = [1, 2, 4, 5, 9];
}
let numField = playPositions.length; 
let maxBench = playerCount - (numField); 

let shiftData = []; 
let globalPlayerStats = {}; 

for(let pid in playersMap) {
    globalPlayerStats[playersMap[pid].sidx] = { name: playersMap[pid].name, is_gk: playersMap[pid].is_gk, fieldMin: 0, benchMin: 0, priority: 0 };
}

function initBuilder() {
    let canvas = document.getElementById('shifts-canvas');
    let pool = document.getElementById('player-pool');
    
    // Fill initial pool
    for(let pid in playersMap) {
        if (fixedGkId !== null && parseInt(pid) === fixedGkId) continue;
        let p = playersMap[pid];
        let el = document.createElement('div');
        el.className = 'pool-player' + (p.is_gk ? ' is-gk' : '');
        el.setAttribute('draggable', 'true');
        el.setAttribute('data-sidx', p.sidx);
        el.innerHTML = p.name;
        
        bindDragEvents(el);
        pool.appendChild(el);
    }
    
    // Form shifts logic
    for(let i=0; i<numShifts; i++) {
        let def = shiftDefinitions[i];
        let subDurationMin = def.duration;
        let gCounter = def.game_counter;
        let shiftIdx = i;
        
        let initialLineup = {};
        if (fixedGkId !== null) {
            initialLineup[1] = playersMap[fixedGkId].sidx;
        }

        shiftData.push({ shift: shiftIdx, duration: subDurationMin*60, game_counter: gCounter, start: "00:00", lineup: initialLineup, bench: [] });
        
        let col = document.createElement('div');
        col.className = 'col-12 col-xxl-6 mb-4';

        let block = document.createElement('div');
        block.className = 'card h-100 shift-block ' + (i > 0 ? 'locked border-secondary' : 'border-primary');
        block.id = 'shift-' + i;
        
        let html = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">${def.label} <small class="text-muted fw-normal">(${subDurationMin} min)</small></h5>
                <span class="badge bg-secondary" id="counter-${i}">0 / ${playerCount}</span>
            </div>
            <div class="card-body bg-light">`;
            
        html += `
            <div class="row mx-0 px-2 py-3">
                 <div class="col-md-9 field-area">
                    <div class="row px-2">`;
                    
        // Definieer de visuele rijen per format
        let formationRows = [];
        if (formatStr.includes('5v5')) {
            formationRows = [ [9], [11, 7], [4], [1] ];
        } else if (formatStr.includes('11v11')) {
            formationRows = [ [11, 9, 7], [8, 10, 6], [5, 4, 3, 2], [1] ];
        } else {
            // Default 8v8
            formationRows = [ [11, 9, 7], [10], [5, 4, 2], [1] ];
        }

        formationRows.forEach(rowPositions => {
            let rowHtml = `<div class="d-flex justify-content-center w-100 mb-2">`;
            let hasBoxes = false;
            rowPositions.forEach(pos => {
                hasBoxes = true;
                
                let innerPlayer = '';
                let extraStyles = '';
                if (fixedGkId !== null && pos === 1) {
                    let sidx = playersMap[fixedGkId].sidx;
                    let pName = playersMap[fixedGkId].name;
                    innerPlayer = `<div class="pool-player shadow-sm is-gk locked" draggable="false" data-sidx="${sidx}" data-id="${fixedGkId}" style="pointer-events: none; opacity: 0.9;">${pName}</div>`;
                    extraStyles = ' border-color: #dc3545; background-color: #f8d7da;';
                }
                
                rowHtml += `<div class="px-1" style="flex: 1; max-width: 30%;"><div class="pos-wrapper shadow-sm" data-pos="${pos}" data-shift="${i}" style="${extraStyles}"><span class="pos-badge">Pos ${pos}</span>${innerPlayer}</div></div>`;
            });
            rowHtml += `</div>`;
            if (hasBoxes) html += rowHtml;
        });
        
        html += `   </div>
                 </div>
                 <div class="col-md-3 bench-area px-1">
                    <div class="row px-2">`;
        
        for(let b=0; b<maxBench; b++) {
            html += `<div class="col-12 px-1"><div class="pos-wrapper shadow-sm" data-pos="bench" data-shift="${i}"><span class="pos-badge bg-warning text-dark"><i class="fa-solid fa-bed"></i> Bank</span></div></div>`;
        }
        
        html += `   </div>
                 </div>
            </div>
            </div>
            <div class="card-footer bg-white text-end py-2">
                ${i > 0 ? `<button class="btn btn-sm btn-outline-danger btn-reset d-none me-2" onclick="resetBlock(${i})"><i class="fa-solid fa-arrow-left"></i> Wis & Naar Vorige</button>` : ''}
                <button class="btn btn-sm btn-primary btn-lock d-none" onclick="lockBlock(${i})">Vastzetten & Volgende <i class="fa-solid fa-arrow-right"></i></button>
                <button class="btn btn-sm btn-outline-warning btn-unlock d-none" onclick="unlockBlock(${i})"><i class="fa-solid fa-unlock"></i> Bewerk Vanaf Hier</button>
            </div>
            `;
            
        block.innerHTML = html;
        col.appendChild(block);
        canvas.appendChild(col);
        
        block.querySelectorAll('.pos-wrapper').forEach(pw => {
            pw.addEventListener('dragover', e => { e.preventDefault(); pw.style.borderColor = '#0d6efd'; });
            pw.addEventListener('dragleave', e => { pw.style.borderColor = ''; });
            pw.addEventListener('drop', e => handleDrop(e, pw));
        });
        
        if(i === 0) {
            pool.addEventListener('dragover', e => { e.preventDefault(); });
            pool.addEventListener('drop', e => handleDropToPool(e, pool));
        }
    }
    
    // Zorg ervoor dat statistieken zichtbaar zijn bij laden pagina
    calculateStats();
}

let draggedEl = null;
function bindDragEvents(el) {
    el.addEventListener('dragstart', function(e) {
        draggedEl = this;
        setTimeout(() => this.classList.add('opacity-50'), 0);
    });
    el.addEventListener('dragend', function(e) {
        this.classList.remove('opacity-50');
        draggedEl = null;
    });
}

function handleDropToPool(e, pool) {
    e.preventDefault();
    if(draggedEl && !draggedEl.classList.contains('locked')) {
        let parent = draggedEl.closest('.pos-wrapper');
        if(parent) {
            let shiftIdx = parseInt(parent.getAttribute('data-shift'));
            let block = document.getElementById('shift-' + shiftIdx);
            if(!block.classList.contains('locked')) {
                pool.appendChild(draggedEl);
                updateShiftData(shiftIdx);
                calculateStats();
            }
        }
    }
}

function handleDrop(e, dropZone) {
    e.preventDefault();
    dropZone.style.borderColor = '';
    
    if(!draggedEl) return;
    
    let targetShiftIdx = parseInt(dropZone.getAttribute('data-shift'));
    let currentBlock = document.getElementById('shift-' + targetShiftIdx);
    if(currentBlock.classList.contains('locked')) {
        return;
    }
    
    let sourceContainer = draggedEl.parentNode;
    let existingPlayer = dropZone.querySelector('.pool-player');
    if (existingPlayer) {
        sourceContainer.appendChild(existingPlayer);
        dropZone.appendChild(draggedEl);
    } else {
        dropZone.appendChild(draggedEl);
    }
    
    updateShiftData(targetShiftIdx);
    calculateStats();
}

function updateShiftData(shiftIdx) {
    let block = document.getElementById('shift-' + shiftIdx);
    
    // --- AUTO-FILL BENCH FEATURE ---
    // Controleer of alle veldposities volzet zijn
    let fieldPositions = Array.from(block.querySelectorAll('.pos-wrapper:not([data-pos="bench"])'));
    let fieldFilledCount = fieldPositions.filter(pw => pw.querySelector('.pool-player')).length;
    
    // Alleen auto-fill uitvoeren als het blok in bewerking is en er nog spelers in de pool zitten
    let currentPlayerCountInBlock = block.querySelectorAll('.pool-player').length;
    
    if (fieldFilledCount === fieldPositions.length && currentPlayerCountInBlock < playerCount) {
        let poolContainer = document.getElementById('player-pool');
        let remainingPlayers = Array.from(poolContainer.querySelectorAll('.pool-player'));
        
        if (remainingPlayers.length > 0) {
            let emptyBenchPositions = Array.from(block.querySelectorAll('.pos-wrapper[data-pos="bench"]')).filter(pw => !pw.querySelector('.pool-player'));
            remainingPlayers.forEach(p => {
                let targetPw = emptyBenchPositions.shift();
                if (targetPw) {
                    targetPw.appendChild(p);
                }
            });
        }
    }
    // --- END AUTO-FILL ---
    
    let sData = shiftData[shiftIdx];
    sData.lineup = {};
    if (fixedGkId !== null) sData.lineup[1] = playersMap[fixedGkId].sidx;
    sData.bench = [];
    
    let playerCountInBlock = 0;
    
    block.querySelectorAll('.pos-wrapper').forEach(pw => {
        let playerEl = pw.querySelector('.pool-player');
        if(playerEl) {
            playerCountInBlock++;
            let pSidx = parseInt(playerEl.getAttribute('data-sidx'));
            let pos = pw.getAttribute('data-pos');
            if(pos === 'bench') {
                sData.bench.push(pSidx);
            } else {
                sData.lineup[pos] = pSidx;
            }
        }
    });
    
    updateCounter(shiftIdx);
    
    let btnLock = block.querySelector('.btn-lock');
    if(playerCountInBlock === playerCount) {
        btnLock.classList.remove('d-none');
    } else {
        btnLock.classList.add('d-none');
    }
}

function resetBlock(shiftIdx) {
    if(shiftIdx === 0) return;
    
    let currentBlock = document.getElementById('shift-' + shiftIdx);
    
    // Clear non-fixed players
    currentBlock.querySelectorAll('.pool-player').forEach(el => {
        if(!el.classList.contains('locked')) el.remove();
    });
    updateShiftData(shiftIdx);
    
    // Make current locked again visually (but as future block)
    currentBlock.classList.add('locked');
    currentBlock.classList.replace('border-primary', 'border-secondary');
    currentBlock.querySelector('.btn-lock').classList.add('d-none');
    if(currentBlock.querySelector('.btn-reset')) currentBlock.querySelector('.btn-reset').classList.add('d-none');
    
    // Unlock previous
    let prevIdx = shiftIdx - 1;
    let prevBlock = document.getElementById('shift-' + prevIdx);
    prevBlock.classList.remove('locked');
    prevBlock.classList.replace('border-success', 'border-primary');
    prevBlock.classList.replace('border-secondary', 'border-primary');
    prevBlock.querySelector('.btn-unlock').classList.add('d-none');
    prevBlock.querySelector('.btn-lock').classList.remove('d-none');
    if(prevBlock.querySelector('.btn-reset')) prevBlock.querySelector('.btn-reset').classList.remove('d-none');
    
    calculateStats();
    prevBlock.scrollIntoView({behavior: "smooth", block: "center"});
}

function lockBlock(shiftIdx) {
    if (shiftIdx > 0) {
        let currentLineupStr = JSON.stringify(shiftData[shiftIdx].lineup);
        let previousLineupStr = JSON.stringify(shiftData[shiftIdx - 1].lineup);
        
        let currDef = shiftDefinitions[shiftIdx];
        let prevDef = shiftDefinitions[shiftIdx - 1];
        
        if (currDef.game_counter === prevDef.game_counter && currentLineupStr === previousLineupStr) {
            if (!confirm("Let op: Je hebt exact dezelfde opstelling (en bankzitters) als in het vorige blokje. Wil je deze opstelling toch 2x na elkaar spelen binnen deze wedstrijd?")) {
                return;
            }
        }
    }

    let block = document.getElementById('shift-' + shiftIdx);
    block.classList.add('locked');
    block.classList.replace('border-primary', 'border-success');
    block.querySelector('.btn-lock').classList.add('d-none');
    if(block.querySelector('.btn-reset')) block.querySelector('.btn-reset').classList.add('d-none');
    block.querySelector('.btn-unlock').classList.remove('d-none');
    
    calculateStats();
    
    let nextShiftIdx = shiftIdx + 1;
    if(nextShiftIdx < numShifts) {
        let nextBlock = document.getElementById('shift-' + nextShiftIdx);
        nextBlock.classList.remove('locked');
        nextBlock.classList.replace('border-secondary', 'border-primary');
        nextBlock.querySelector('.btn-lock').classList.remove('d-none');
        if(nextBlock.querySelector('.btn-reset')) nextBlock.querySelector('.btn-reset').classList.remove('d-none');
        
        let currentSData = shiftData[shiftIdx];
        
        // Bepaal of we de veldspelers mogen kopiëren naar het veld, of naar de bank moeten verplaatsen
        let shouldCopy = true;
        let copyToggle = document.getElementById('copyLineupToggle');
        if (copyToggle && copyToggle.checked) {
            let currDef = shiftDefinitions[shiftIdx];
            let nextDef = shiftDefinitions[nextShiftIdx];
            if (currDef.game_counter !== nextDef.game_counter) {
                shouldCopy = false;
            }
        }
        
        let nextBenchCount = 0;
        currentSData.bench.forEach((s, idx) => {
            if (shouldCopy) {
                fillNextBlockPos(nextShiftIdx, 'bench', s, nextBenchCount++);
            } else {
                fillNextBlockPool(s);
            }
        });
        
        Object.keys(currentSData.lineup).forEach(pos => {
            if (fixedGkId !== null && parseInt(pos) === 1) return;
            if (shouldCopy) {
                fillNextBlockPos(nextShiftIdx, pos, currentSData.lineup[pos], 0);
            } else {
                fillNextBlockPool(currentSData.lineup[pos]);
            }
        });
        
        updateShiftData(nextShiftIdx);
        calculateStats(); // Ensure the pool is sorted correctly after elements are added
        nextBlock.scrollIntoView({behavior: "smooth", block: "center"});
    } else {
        document.getElementById('btnSave').disabled = false;
        alert("Alle kwartjes zijn ingevuld! Je kan je schema nu opslaan.");
    }
}

function unlockBlock(shiftIdx) {
    if(!confirm("Waarschuwing: Alle blokken na deze blok worden gereset. Ben je zeker?")) return;
    
    let currentBlock = document.getElementById('shift-' + shiftIdx);
    currentBlock.classList.remove('locked');
    currentBlock.classList.replace('border-success', 'border-primary');
    currentBlock.querySelector('.btn-unlock').classList.add('d-none');
    currentBlock.querySelector('.btn-lock').classList.remove('d-none');
    if(currentBlock.querySelector('.btn-reset')) currentBlock.querySelector('.btn-reset').classList.remove('d-none');
    
    document.getElementById('btnSave').disabled = true;
    
    for(let i = shiftIdx + 1; i < numShifts; i++) {
        let laterBlock = document.getElementById('shift-' + i);
        laterBlock.classList.add('locked');
        laterBlock.classList.replace('border-primary', 'border-secondary');
        laterBlock.classList.replace('border-success', 'border-secondary');
        laterBlock.querySelector('.btn-unlock').classList.add('d-none');
        laterBlock.querySelector('.btn-lock').classList.add('d-none');
        if(laterBlock.querySelector('.btn-reset')) laterBlock.querySelector('.btn-reset').classList.add('d-none');
        
        laterBlock.querySelectorAll('.pool-player').forEach(el => {
            if(!el.classList.contains('locked')) el.remove();
        });
        updateShiftData(i);
    }
    calculateStats();
}

function fillNextBlockPos(targetShiftIdx, pos, sidx, benchIdx) {
    let container;
    if(pos === 'bench') {
        let benches = document.querySelectorAll(`#shift-${targetShiftIdx} .pos-wrapper[data-pos="bench"]`);
        if(benchIdx < benches.length) {
            container = benches[benchIdx];
        } else {
            // Te weinig bank slots! Stuur naar pool als fallback
            fillNextBlockPool(sidx);
            return;
        }
    } else {
        container = document.querySelector(`#shift-${targetShiftIdx} .pos-wrapper[data-pos="${pos}"]`);
    }
    
    if(container) {
        let pName = "";
        let isGk = false;
        for(let pid in playersMap) {
            if(playersMap[pid].sidx == sidx) { pName = playersMap[pid].name; isGk = playersMap[pid].is_gk; }
        }
        
        let el = document.createElement('div');
        el.className = 'pool-player shadow-sm';
        el.setAttribute('draggable', 'true');
        el.setAttribute('data-sidx', sidx);
        
        let pStats = globalPlayerStats[sidx];
        if (pStats && pStats.benchMin > 0) {
            el.classList.add('on-bench-priority');
            el.innerText = pName + " ⏸ " + pStats.benchMin + "m";
        } else {
            el.innerText = pName + (isGk ? " (GK)" : "");
        }
        
        bindDragEvents(el);
        container.appendChild(el);
    }
}

function fillNextBlockPool(sidx) {
    let pool = document.getElementById('player-pool');
    let pName = "";
    let isGk = false;
    for(let pid in playersMap) {
        if(playersMap[pid].sidx == sidx) { pName = playersMap[pid].name; isGk = playersMap[pid].is_gk; }
    }
    
    let el = document.createElement('div');
    el.className = 'pool-player shadow-sm' + (isGk ? ' is-gk' : '');
    el.setAttribute('draggable', 'true');
    el.setAttribute('data-sidx', sidx);
    
    let pStats = globalPlayerStats[sidx];
    if (pStats && pStats.benchMin > 0) {
        el.classList.add('on-bench-priority');
        el.innerText = pName + " ⏸ " + pStats.benchMin + "m";
    } else {
        el.innerText = pName + (isGk ? " (GK)" : "");
    }
    
    bindDragEvents(el);
    pool.appendChild(el);
}

function updateCounter(shiftIdx) {
    let block = document.getElementById('shift-' + shiftIdx);
    let count = block.querySelectorAll('.pool-player').length;
    let badge = document.getElementById('counter-' + shiftIdx);
    badge.innerText = count + " / " + playerCount;
    if(count === playerCount) {
        badge.classList.replace('bg-secondary', 'bg-success');
    } else {
        badge.classList.replace('bg-success', 'bg-secondary');
    }
}

function calculateStats() {
    // Reset priority
    for(let i in globalPlayerStats) {
        globalPlayerStats[i].priority = 0; globalPlayerStats[i].benchMin=0; globalPlayerStats[i].fieldMin=0;
        globalPlayerStats[i].positions = {};
    }
    // Calculate from locked shifts
    shiftData.forEach((sData, i) => {
        let block = document.getElementById('shift-' + i);
        if(block.classList.contains('locked')) {
            sData.bench.forEach(s => {
                if (globalPlayerStats[s]) {
                    globalPlayerStats[s].benchMin += (sData.duration / 60);
                    globalPlayerStats[s].priority += 10;
                }
            });
            Object.keys(sData.lineup).forEach(pos => {
                let s = sData.lineup[pos];
                if (globalPlayerStats[s]) {
                    globalPlayerStats[s].fieldMin += (sData.duration / 60);
                    if (!globalPlayerStats[s].positions[pos]) globalPlayerStats[s].positions[pos] = 0;
                    globalPlayerStats[s].positions[pos] += (sData.duration / 60);
                }
            });
        }
    });

    // Compute matchAvailable for each player
    for(let i in globalPlayerStats) {
        globalPlayerStats[i].matchAvailable = globalPlayerStats[i].fieldMin + globalPlayerStats[i].benchMin;
    }

    // Update pool visual sorting
    let pool = document.getElementById('player-pool');
    let poolItems = Array.from(pool.querySelectorAll('.pool-player'));
    
    let sortPlayersFunc = (sidxA, sidxB) => {
        let pA = globalPlayerStats[sidxA] || { fieldMin: 0, matchAvailable: 0 };
        let pB = globalPlayerStats[sidxB] || { fieldMin: 0, matchAvailable: 0 };
        
        // 1. Primaire sortering: Wedstrijd percentage (laagste eerst)
        let ratioA = pA.matchAvailable > 0 ? (pA.fieldMin / pA.matchAvailable) : 0;
        let ratioB = pB.matchAvailable > 0 ? (pB.fieldMin / pB.matchAvailable) : 0;
        
        if (Math.abs(ratioA - ratioB) > 0.001) {
            return ratioA - ratioB; // ascending
        }
        
        let sA = seasonStatsMap[sidxA] || { histPlayed: 0, histAvailable: 0, periodPlayed: 0, periodAvailable: 0 };
        let sB = seasonStatsMap[sidxB] || { histPlayed: 0, histAvailable: 0, periodPlayed: 0, periodAvailable: 0 };
        
        let usePeriodStats = document.getElementById('togglePeriodStats') ? document.getElementById('togglePeriodStats').checked : false;
        
        // 2. Secundaire sortering: Periode percentage (indien beschikbaar en groter dan 0, en toggle staat aan)
        if (usePeriodStats) {
            let periodAvailableA = parseInt(sA.periodAvailable);
            let periodAvailableB = parseInt(sB.periodAvailable);
            
            if (periodAvailableA > 0 || periodAvailableB > 0) {
                let periodRatioA = periodAvailableA > 0 ? (parseInt(sA.periodPlayed) / periodAvailableA) : 0;
                let periodRatioB = periodAvailableB > 0 ? (parseInt(sB.periodPlayed) / periodAvailableB) : 0;
                
                if (Math.abs(periodRatioA - periodRatioB) > 0.001) {
                    return periodRatioA - periodRatioB; // ascending
                }
            }
        }
        
        // 3. Tertiaire sortering: Seizoen percentage (laagste eerst)
        let histAvailableA = parseInt(sA.histAvailable);
        let histAvailableB = parseInt(sB.histAvailable);
        
        let histRatioA = histAvailableA > 0 ? (parseInt(sA.histPlayed) / histAvailableA) : 0;
        let histRatioB = histAvailableB > 0 ? (parseInt(sB.histPlayed) / histAvailableB) : 0;
        
        if (Math.abs(histRatioA - histRatioB) > 0.001) {
            return histRatioA - histRatioB; // ascending
        }
        
        // 4. Alfabetisch op voornaam
        let nameA = (pA.name || "").toLowerCase();
        let nameB = (pB.name || "").toLowerCase();
        if (nameA < nameB) return -1;
        if (nameA > nameB) return 1;
        return 0;
    };

    poolItems.sort((a, b) => {
        let sidxA = parseInt(a.getAttribute('data-sidx'));
        let sidxB = parseInt(b.getAttribute('data-sidx'));
        return sortPlayersFunc(sidxA, sidxB);
    });
    
    poolItems.forEach(item => {
        let sidx = parseInt(item.getAttribute('data-sidx'));
        if(isNaN(sidx)) return;
        let pStats = globalPlayerStats[sidx];
        if(!pStats) return;
        
        let sData = seasonStatsMap[sidx] || { histPlayed: 0, histAvailable: 0, periodPlayed: 0, periodAvailable: 0 };
        
        let usePeriodStats = document.getElementById('togglePeriodStats') ? document.getElementById('togglePeriodStats').checked : false;
        
        let histAvailSec = parseInt(sData.histAvailable) + (pStats.matchAvailable * 60);
        let seasonPct = histAvailSec > 0 ? Math.round(((parseInt(sData.histPlayed) + (pStats.fieldMin * 60)) / histAvailSec) * 100) : 0;
        
        let pctHtml = '';
        let periodAvailSec = parseInt(sData.periodAvailable) + (pStats.matchAvailable * 60);
        
        if (usePeriodStats && periodAvailSec > 0) {
            let periodPct = Math.round(((parseInt(sData.periodPlayed) + (pStats.fieldMin * 60)) / periodAvailSec) * 100);
            pctHtml = `${periodPct}% <span style="font-size:0.6rem;">(P)</span>`;
        } else {
            pctHtml = `${seasonPct}% <span style="font-size:0.6rem;">(S)</span>`;
        }
        
        let baseText = pStats.name;
        if (pStats.is_gk) baseText += " (GK)";
        
        let infoHtml = `<span>${baseText}</span> <small class="fw-normal opacity-75">(${pStats.fieldMin}/${totalMinutes}m - ${pctHtml})</small>`;
        
        if (pStats.benchMin > 0) {
            item.classList.add('on-bench-priority');
            item.innerHTML = infoHtml + ` <br><small>⏸ Bank: ${pStats.benchMin}m</small>`;
        } else {
            item.classList.remove('on-bench-priority');
            item.innerHTML = infoHtml;
        }
        pool.appendChild(item);
    });

    // Update Live Statistics Table
    let tbody = document.getElementById('live-stats-tbody');
    let statsHtml = '';
    
    // Convert globalPlayerStats to array to sort it alphabetically by name
    let statsArr = [];
    for(let i in globalPlayerStats) {
        statsArr.push({
            sidx: i,
            name: globalPlayerStats[i].name,
            fieldMin: globalPlayerStats[i].fieldMin,
            benchMin: globalPlayerStats[i].benchMin
        });
    }
    statsArr.sort((a, b) => sortPlayersFunc(a.sidx, b.sidx));

    // For total match time context, find the total duration of locked blocks
    let totalLockedMin = 0;
    shiftData.forEach((sData, i) => {
        if(document.getElementById('shift-' + i).classList.contains('locked')) {
            totalLockedMin += (sData.duration / 60);
        }
    });

    statsArr.forEach(st => {
        let matchPerc = 0;
        let matchAvailable = st.fieldMin + st.benchMin; // Time they are part of a locked block
        if(matchAvailable > 0) {
            matchPerc = (st.fieldMin / matchAvailable) * 100;
        }
        st.matchPerc = matchPerc;
        st.matchAvailable = matchAvailable;
    });

    statsArr.forEach(st => {
        let matchText = "0m";
        let matchColor = "text-muted";
        
        if(st.matchAvailable > 0) {
            matchText = st.fieldMin + "m";
            if (st.matchPerc < 50) matchColor = "text-danger fw-bold";
            else if (st.matchPerc >= 65) matchColor = "text-success fw-bold";
            else matchColor = "text-warning fw-bold";
        } else if (totalLockedMin > 0) {
            matchText = "-";
        }
        
        // Calculate Season totals
        let hist = seasonStatsMap[st.sidx];
        if(!hist) hist = { histPlayed: 0, histAvailable: 0, periodPlayed: 0, periodAvailable: 0 };
        
        let totalSeasonPlayedSec = parseInt(hist.histPlayed) + (st.fieldMin * 60);
        let totalSeasonAvailableSec = parseInt(hist.histAvailable) + (st.matchAvailable * 60);
        
        let totalPeriodPlayedSec = parseInt(hist.periodPlayed) + (st.fieldMin * 60);
        let totalPeriodAvailableSec = parseInt(hist.periodAvailable) + (st.matchAvailable * 60);
        
        let seasonPercText = "0%";
        let seasonColor = "text-muted";
        
        if (totalSeasonAvailableSec > 0) {
            let seasonPerc = (totalSeasonPlayedSec / totalSeasonAvailableSec) * 100;
            seasonPercText = Math.round(seasonPerc) + "%";
            
            if (seasonPerc < 50) seasonColor = "text-danger fw-bold";
            else if (seasonPerc >= 65) seasonColor = "text-success fw-bold";
            else seasonColor = "text-warning fw-bold";
        }
        
        let periodPercText = "-";
        let periodColor = "text-muted";
        
        let usePeriodStats = document.getElementById('togglePeriodStats') ? document.getElementById('togglePeriodStats').checked : false;
        
        if (usePeriodStats && totalPeriodAvailableSec > 0) {
            let periodPerc = (totalPeriodPlayedSec / totalPeriodAvailableSec) * 100;
            periodPercText = Math.round(periodPerc) + "%";
            
            if (periodPerc < 50) periodColor = "text-danger fw-bold";
            else if (periodPerc >= 65) periodColor = "text-success fw-bold";
            else periodColor = "text-warning fw-bold";
        }
        
        let seasonHoverTitle = Math.round(totalSeasonPlayedSec / 60) + "m gespeeld / " + Math.round(totalSeasonAvailableSec / 60) + "m beschikbaar";
        let periodHoverTitle = Math.round(totalPeriodPlayedSec / 60) + "m gespeeld / " + Math.round(totalPeriodAvailableSec / 60) + "m beschikbaar";
        
        statsHtml += `
            <tr>
                <td class="align-middle">${st.name}</td>
                <td class="text-center align-middle ${matchColor}">${matchText} <br><small class="text-muted fw-normal">op ${st.matchAvailable}m</small></td>
                ${ <?= $hasActivePeriod ? 'true' : 'false' ?> ? `<td class="text-center align-middle period-col ${usePeriodStats ? '' : 'd-none'} ${periodColor}" title="${periodHoverTitle}" style="cursor: help;">${periodPercText}</td>` : '' }
                <td class="text-center align-middle ${seasonColor}" title="${seasonHoverTitle}" style="cursor: help;">${seasonPercText}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = statsHtml;
    
    // Toggle the header visibility for Period column
    let thPeriod = document.querySelector('th.period-col');
    if (thPeriod) {
        let usePeriodStats = document.getElementById('togglePeriodStats') ? document.getElementById('togglePeriodStats').checked : false;
        if (usePeriodStats) thPeriod.classList.remove('d-none');
        else thPeriod.classList.add('d-none');
    }

    // Render position stats per player
    let posCanvas = document.getElementById('position-stats-canvas');
    let posTitle = document.getElementById('position-stats-title');
    
    if (totalLockedMin > 0) {
        posTitle.classList.remove('d-none');
        
        let posHtml = '';
        
        // Filter players who have played at least some time
        let playersWithTime = statsArr.filter(st => st.fieldMin > 0 || st.benchMin > 0);
        
        // Sort players by total fieldMin descending
        playersWithTime.sort((a, b) => b.fieldMin - a.fieldMin);
        
        playersWithTime.forEach(st => {
            let pStats = globalPlayerStats[st.sidx];
            let listItems = '';
            
            // Render positions
            let positions = pStats.positions || {};
            let sortedPos = Object.keys(positions).sort((a,b) => positions[b] - positions[a]);
            
            sortedPos.forEach(pos => {
                let min = positions[pos];
                if (min > 0) {
                    listItems += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 border-0" style="font-size: 0.85rem; border-bottom: 1px solid #f1f3f5!important;">
                            <span>Pos <span class="badge bg-secondary rounded-pill">${pos}</span></span>
                            <span>${min}m</span>
                        </li>
                    `;
                }
            });
            
            if (pStats.benchMin > 0) {
                listItems += `
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 border-0 bg-warning bg-opacity-10" style="font-size: 0.85rem;">
                        <span class="text-dark"><i class="fa-solid fa-bed me-1"></i> Bank</span>
                        <span class="fw-bold">${pStats.benchMin}m</span>
                    </li>
                `;
            }
            
            posHtml += `
                <div class="col-6 col-md-4 col-xl-3 mb-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-bottom-0">
                            <span class="fw-bold text-primary text-truncate" style="font-size: 0.9rem;" title="${st.name}">${st.name}</span>
                            <span class="badge bg-primary rounded-pill">${st.fieldMin}m</span>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush mb-0">
                                ${listItems}
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        });
        
        posCanvas.innerHTML = posHtml;
    } else {
        posTitle.classList.add('d-none');
        posCanvas.innerHTML = '';
    }
}

function saveNewSchema() {
    let btn = document.getElementById('btnSave');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Ophalen...';
    btn.disabled = true;
    
    // Parse arrays correctly
    shiftData.forEach(sd => {
        sd.bench = sd.bench.map(s => parseInt(s));
        let fixedLineup = {};
        for(let pos in sd.lineup) { fixedLineup[pos] = parseInt(sd.lineup[pos]); }
        sd.lineup = fixedLineup;
    });

    fetch('/modules/schemas/api_save_schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            game_id: gameId,
            format: formatStr,
            aantal: playerCount,
            original_schema_id: 0,
            blocks: shiftData,
            volgorde: volgordeStr,
            force_settings_update: false,
            overwrite_mode: false
        })
    }).then(r => r.json()).then(data => {
        if(data.requires_confirm) {
            if(confirm(data.confirm_msg)) {
                submitForced();
            } else {
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Opslaan';
                btn.disabled = false;
            }
        } else if(data.success) {
            alert("Nieuw Manueel Schema succesvol opgeslagen! We gaan nu naar de weergave.");
            if(data.is_duplicate) {
                alert("Tip: Je nieuwe schema bestond toevallig reeds wiskundig (ID #" + data.new_id + ") en werd in de theorie gekoppeld!");
            }
            window.location.href = '/games/' + gameId + '/lineup?preview=' + data.lineup_id;
        } else {
            alert("Error: " + data.error);
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Opslaan';
            btn.disabled = false;
        }
    }).catch(err => {
        alert("Server error");
        btn.disabled = false;
    });
}

function submitForced() {
    fetch('/modules/schemas/api_save_schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            game_id: gameId, format: formatStr, aantal: playerCount, original_schema_id: 0, blocks: shiftData, volgorde: volgordeStr, force_settings_update: true, overwrite_mode: false
        })
    }).then(r => r.json()).then(data => {
        if(data.success){
            window.location.href = '/games/' + gameId + '/lineup?preview=' + data.lineup_id;
        } else {
            alert("Error: "+data.error);
        }
    });
}

document.addEventListener('DOMContentLoaded', initBuilder);
</script>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
