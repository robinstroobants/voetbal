<?php
// parents_ui.php - Wordt ingeladen onderaan lineup.php in PUBLIC_SHARE_MODE
if (!isset($gameId)) return; // Veilige fallback

// Zorg dat de tabel bestaat (workaround voor lokale database synchronisatie)
$pdo->exec("
CREATE TABLE IF NOT EXISTS game_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    parent_email VARCHAR(255) NULL,
    event_type VARCHAR(50) NOT NULL,
    player_id INT NULL,
    player_out_id INT NULL,
    event_minute INT NOT NULL DEFAULT 0,
    is_confirmed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    FOREIGN KEY (player_out_id) REFERENCES players(id) ON DELETE SET NULL
);
");

// Haal alle spelersnamen op voor de client-side lookup
$stmtP = $pdo->prepare("
    SELECT p.id, p.first_name, p.last_name 
    FROM players p
    JOIN game_selections gs ON p.id = gs.player_id
    WHERE gs.game_id = ?
");
$stmtP->execute([$gameId]);
$gamePlayers = $stmtP->fetchAll(PDO::FETCH_ASSOC);
$playerMap = [];
foreach($gamePlayers as $p) {
    $playerMap[$p['id']] = $p['first_name'] . ' ' . $p['last_name'];
}

// Check if it's a tournament via de is_tournament vlag (block_labels kan leeg zijn maar tornooi nog steeds actief)
$stmtTour = $pdo->prepare("
    SELECT g.is_tournament, g.block_labels, g.opponent, t.name as team_name,
           COALESCE(t.timezone, 'Europe/Brussels') as timezone,
           COALESCE(t.show_lineup_to_parents, 0) as show_lineup_to_parents
    FROM games g
    LEFT JOIN teams t ON t.id = g.team_id
    WHERE g.id = ?
");
$stmtTour->execute([$gameId]);
$gameRow = $stmtTour->fetch(PDO::FETCH_ASSOC);
$isTournament = !empty($gameRow['is_tournament']);
$teamName = htmlspecialchars($gameRow['team_name'] ?? 'Ons team');
$gameOpponent = htmlspecialchars($gameRow['opponent'] ?? '');
$gameBlockLabels = json_decode($gameRow['block_labels'] ?? '[]', true) ?: [];
$teamTimezone = $gameRow['timezone'] ?? 'Europe/Brussels';
$showLineupToParents = !empty($gameRow['show_lineup_to_parents']);

// Bereken UTC-offset in minuten voor de team-tijdzone (voor JS timestamp display)
$tzObj = new DateTimeZone($teamTimezone);
$utcOffset = $tzObj->getOffset(new DateTime('now', new DateTimeZone('UTC'))); // seconden
$timezoneOffsetMinutes = intval($utcOffset / 60);

// Haal de shifts (blokken) op uit het lineup object
$shifts_data = [];
$totalBlocksCount = 1;
$cumulative_min = 0;
$game_start_mins = []; // Voor tornooien om de tijd per wedstrijd (game_counter) bij te houden

$event_to_game = [];
$event_to_part = [];
$event_total_parts = [];
if (isset($lineup) && isset($lineup->game_parts)) {
    foreach ($lineup->game_parts as $g_counter => $g_parts) {
        foreach ($g_parts as $part_idx => $g_idx) {
            $event_to_game[$g_idx] = $g_counter;
            $event_to_part[$g_idx] = $part_idx + 1;
            $event_total_parts[$g_idx] = count($g_parts);
        }
    }
}

if (isset($lineup) && isset($lineup->events)) {
    foreach($lineup->events as $idx => $ev) {
        $duration_seconds = (float)($ev['duration'] ?? 0);
        $duration_minutes = $duration_seconds / 60.0;
        
        $pitch_with_pos = [];
        foreach (($ev['lineup'] ?? []) as $pos => $pid) {
            $pitch_with_pos[] = ['id' => $pid, 'pos' => $pos];
        }
        
        $current_game_counter = $event_to_game[$idx] ?? 1;
        if ($isTournament) {
            if (!isset($game_start_mins[$current_game_counter])) {
                $game_start_mins[$current_game_counter] = 0;
            }
            $start_minute = $game_start_mins[$current_game_counter];
            $game_start_mins[$current_game_counter] += $duration_minutes;
        } else {
            $start_minute = $cumulative_min;
            $cumulative_min += $duration_minutes;
        }
        
        $title = "Blok " . ($idx + 1);
        $total_parts = $event_total_parts[$idx] ?? 1;
        $part = $event_to_part[$idx] ?? 1;
        
        $game_prefix = "Wedstrijd $current_game_counter";
        $game_block_labels = json_decode($matchData['game']['block_labels'] ?? '[]', true) ?: [];
        if (!empty($game_block_labels[$current_game_counter - 1])) {
            $game_prefix = $game_block_labels[$current_game_counter - 1];
        }
        
        if ($total_parts == 1) {
            $title = $game_prefix;
        } elseif ($total_parts == 2) {
            $title = $game_prefix . ", Helft " . $part;
        } else {
            $title = $game_prefix . ", Deel " . $part;
        }
        
        $bench_with_pos = [];
        foreach ($ev['bench'] ?? [] as $bid) {
            $bench_with_pos[] = ['id' => $bid, 'pos' => 'bank'];
        }

        $shifts_data[] = [
            'index' => $idx + 1,
            'title' => $title,
            'game_counter' => $current_game_counter,
            'duration' => $duration_minutes,
            'start_minute' => $start_minute,
            'bench' => $bench_with_pos,
            'pitch' => $pitch_with_pos
        ];
    }
    $totalBlocksCount = count($shifts_data);
} else {
    // Fallback indien geen schema (alles op veld, 1 blok van 45m)
    $fallback_pitch = [];
    foreach (array_keys($playerMap) as $pid) {
        $fallback_pitch[] = ['id' => $pid, 'pos' => '?'];
    }
    
    $shifts_data[] = [
        'index' => 1,
        'title' => 'Wedstrijd 1',
        'game_counter' => 1,
        'duration' => 45,
        'start_minute' => 0,
        'bench' => [],
        'pitch' => $fallback_pitch
    ];
}

// Bepaal de huidige shift op basis van het aantal 'period_start' events (wisselmomenten doorgegeven door ouders)
$stmtBlocks = $pdo->prepare("SELECT created_at FROM game_events WHERE game_id = ? AND event_type IN ('match_start', 'period_start') AND is_deleted = 0 ORDER BY created_at ASC");
$stmtBlocks->execute([$gameId]);
$blockEvents = $stmtBlocks->fetchAll(PDO::FETCH_COLUMN);

// matchEndedAt is enkel geldig als match_end het LAATSTE status event is
// (geen period_start erna — want dan is er een nieuwe game gestart)
$stmtMatchEnd = $pdo->prepare("
    SELECT created_at FROM game_events
    WHERE game_id = ? AND event_type = 'match_end' AND is_deleted = 0
      AND created_at >= COALESCE(
          (SELECT MAX(created_at) FROM game_events WHERE game_id = ? AND event_type = 'period_start' AND is_deleted = 0),
          '1970-01-01'
      )
    ORDER BY created_at DESC LIMIT 1
");
$stmtMatchEnd->execute([$gameId, $gameId]);
$matchEndedAt = $stmtMatchEnd->fetchColumn();

$matchStarted = count($blockEvents) > 0;

// totalGames: hoogste game_counter in alle shifts (nodig vóór currentGameCounter berekening)
$totalGames = 1;
foreach ($shifts_data as $s) {
    if (($s['game_counter'] ?? 1) > $totalGames) $totalGames = $s['game_counter'];
}

// Game-niveau: elk block event (match_start + period_start) = 1 wedstrijdje
// Block 1 = game 1, Block 2 = game 2, etc.
$currentGameCounter = max(1, count($blockEvents)); // 1-based, minimum 1
if ($currentGameCounter > $totalGames) $currentGameCounter = $totalGames;

// Zoek de EERSTE shift van het huidige game_counter (voor lineup-data)
$currentShiftIndex = 0;
foreach ($shifts_data as $idx => $shift) {
    if (($shift['game_counter'] ?? 1) === $currentGameCounter) {
        $currentShiftIndex = $idx;
        break;
    }
}

$stmtLastStatus = $pdo->prepare("SELECT event_type, created_at FROM game_events WHERE game_id = ? AND event_type IN ('match_start', 'period_start', 'period_end') AND is_deleted = 0 ORDER BY id DESC LIMIT 1");
$stmtLastStatus->execute([$gameId]);
$lastStatusEvent = $stmtLastStatus->fetch(PDO::FETCH_ASSOC);

$activeBlockEventTimeMs = 'null';
$isPaused = false;
$pausedAtMs = 'null';
if ($matchStarted && isset($blockEvents[$currentGameCounter - 1])) {
    // UTC-aware: MySQL slaat op in UTC, PHP strtotime() interpreteert als lokale tijd
    // Fix: altijd expliciet als UTC parsen zodat JS new Date().getTime() klopt
    $dt = new DateTime($blockEvents[$currentGameCounter - 1], new DateTimeZone('UTC'));
    $activeBlockEventTimeMs = $dt->getTimestamp() * 1000;
    if ($lastStatusEvent && $lastStatusEvent['event_type'] === 'period_end') {
        $isPaused = true;
        $dtPaused = new DateTime($lastStatusEvent['created_at'], new DateTimeZone('UTC'));
        $pausedAtMs = $dtPaused->getTimestamp() * 1000;
    }
}
?>

<style>
.parents-bottom-bar {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
    border: 1px solid #e9ecef;
}
.parents-clock-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.parents-clock {
    font-family: monospace;
    font-size: 1.2rem;
    font-weight: bold;
    color: #1d1d1f;
    background: #f5f5f7;
    padding: 2px 8px;
    border-radius: 6px;
    border: 1px solid #d2d2d7;
}
.parents-block-label {
    font-size: 0.7rem;
    font-weight: bold;
    color: #86868b;
    text-transform: uppercase;
    margin-bottom: 2px;
}
#liveEventsFeed {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes pulseWissel {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.7); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(255, 159, 67, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 159, 67, 0); }
}
.btn-wissel-due {
    animation: pulseWissel 2s infinite !important;
    background-color: #ff9f43 !important;
    border-color: #ff9f43 !important;
    color: white !important;
}
</style>

</style>

<div class="d-print-none mt-1 mb-2 w-100" id="parentsShareTabsContainer">
  <ul class="nav nav-pills nav-fill bg-white p-1 rounded shadow-sm border mb-2" id="parentsTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active fw-bold text-dark border-0" data-bs-toggle="pill" data-bs-target="#tab-tracker" type="button" role="tab"><i class="fa-solid fa-stopwatch me-1"></i> Match Tracker</button>
    </li>
    <?php if ($showLineupToParents): ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link fw-bold text-dark border-0" data-bs-toggle="pill" data-bs-target="#tab-lineup" type="button" role="tab"><i class="fa-solid fa-clipboard-list me-1"></i> Opstelling</button>
    </li>
    <?php endif; ?>
  </ul>
  
  <div class="tab-content" id="parentsTabsContent">
    <div class="tab-pane fade show active" id="tab-tracker" role="tabpanel">
       
        <div class="parents-bottom-bar w-100">
            <div id="liveClockContainer" class="parents-clock-container w-100 d-flex justify-content-center flex-column align-items-center">
                <?php if ($matchStarted): ?>
                    <div class="w-100 text-center mb-2">
                        <div class="badge bg-light text-dark border px-3 py-2 shadow-sm" style="font-size: 0.95rem;" id="currentBlockLabel"><?= $matchStarted ? htmlspecialchars($gameBlockLabels[$currentGameCounter - 1] ?? ('Wedstrijd ' . $currentGameCounter)) : '' ?></div>
                    </div>
                    <div class="d-flex justify-content-center align-items-center gap-3 w-100 flex-nowrap">
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label">Timer</div>
                            <div class="parents-clock" id="liveClockDisplay">00:00</div>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label">Score</div>
                            <div class="parents-clock text-primary border-primary bg-white" id="liveScoreDisplay">0 - 0</div>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label" id="lblActionBtn">Actie</div>
                            <!-- Contextual action buttons - shown/hidden by updateWisselHint() -->
                            <div id="actionBtnsContainer" class="d-flex flex-column gap-1 w-100">
                                <button id="btnStartVolgende" class="btn btn-success shadow-sm w-100 fw-bold d-none" style="padding: 3px 6px; font-size: 0.78rem; height: auto;"></button>
                                <button id="btnFluitAf" class="btn btn-warning shadow-sm w-100 fw-bold d-none" style="padding: 3px 6px; font-size: 0.78rem; height: auto;"></button>
                                <button id="btnEindeMatch" class="btn btn-danger shadow-sm w-100 fw-bold d-none" style="padding: 3px 6px; font-size: 0.78rem; height: auto;"></button>
                                <button id="btnUitzonderingWissel" class="btn btn-outline-secondary shadow-sm w-100 d-none" style="padding: 3px 6px; font-size: 0.78rem; height: auto;" onclick="openEventModal('wissel')">🔄 Uitzondering</button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn btn-primary fw-bold" onclick="startMatch()">▶ Start Match</button>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2 w-100 justify-content-center mt-1">
                <button id="btnGoal" class="btn btn-primary fw-bold shadow-sm flex-fill" onclick="openEventModal('goal')">⚽ Goal</button>
                <button id="btnOppGoal" class="btn btn-danger fw-bold shadow-sm flex-fill" onclick="openEventModal('opp_goal')">🥅 Tegengoal</button>
            </div>

            <hr class="w-100 my-2 text-muted">
            <div class="d-flex justify-content-between align-items-center w-100 mb-0">
                <h6 class="fw-bold text-muted mb-0" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-list-check me-1"></i> Wedstrijdverloop
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span id="lastRefreshLabel" class="text-muted" style="font-size: 0.7rem;"></span>
                    <button id="btnRefreshFeed" onclick="manualRefreshFeed()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" style="font-size: 0.75rem; padding: 2px 8px;">
                        <i class="fa-solid fa-rotate-right"></i> Ververs
                    </button>
                </div>
            </div>
            <div id="liveEventsFeed" class="w-100 pb-1 mt-1"></div>
        </div>
        
    </div>
    <div class="tab-pane fade" id="tab-lineup" role="tabpanel">
        <!-- JS moves lineup content here -->
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const titleElem = document.getElementById('dynamic-page-title');
    const tabsContainer = document.getElementById('parentsShareTabsContainer');
    const tabLineup = document.getElementById('tab-lineup');
    
    if (titleElem && tabsContainer && tabLineup) {
        titleElem.parentNode.insertBefore(tabsContainer, titleElem.nextSibling);
        let nextNode = tabsContainer.nextSibling;
        const nodesToMove = [];
        while (nextNode) {
            if (nextNode.nodeName !== 'SCRIPT' && nextNode.nodeName !== 'STYLE' && nextNode.id !== 'eventActionModal' && nextNode.id !== 'parentIdentityModal') {
                nodesToMove.push(nextNode);
            }
            nextNode = nextNode.nextSibling;
        }
        nodesToMove.forEach(n => tabLineup.appendChild(n));
    }
});
</script>

<!-- Modal: Wie ben jij? -->
<div class="modal fade" id="parentIdentityModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true" data-bs-focus="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold">👋 Volg de match live mee!</h5>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">Wil je graag updates zoals goals en wissels kunnen doorgeven? Vul even je gegevens in zodat we (en de coach) weten wie wat doorgeeft.</p>
        <div class="mb-3">
            <label class="form-label fw-bold small">Jouw naam <span class="text-muted">(bv. "Jan - papa van Raf")</span></label>
            <input type="text" class="form-control mb-2" id="parentNameInput" placeholder="Jouw naam..." maxlength="100">
            <label class="form-label fw-bold small">Jouw E-mailadres</label>
            <input type="email" class="form-control" id="parentEmailInput" placeholder="naam@voorbeeld.com">
        </div>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="skipIdentity()">Kijken (zonder interactie)</button>
        <button type="button" class="btn btn-primary fw-bold" onclick="saveIdentity()">Opslaan & Meehelpen</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Event Finetuner & Speler -->
<div class="modal fade" id="eventActionModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-warning text-dark border-0">
        <h5 class="modal-title fw-bold" id="eventModalTitle">Actie Doorgeven</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="eventTypeInput">
        
        <!-- Wie scoorde er? (Enkel) -->
        <div id="goalPlayerSelect" class="mb-3" style="display:none;">
            <label class="form-label fw-bold small text-primary">Wie heeft er gescoord?</label>
            <select class="form-select mb-3" id="goalPlayerId">
                <option value="">Selecteer een speler...</option>
                <!-- JS vult dit -->
            </select>
            
            <label class="form-label fw-bold small text-info">Wie gaf de assist? (Optioneel)</label>
            <select class="form-select mb-2" id="assistPlayerId">
                <option value="">Geen assist / Onbekend</option>
                <!-- JS vult dit -->
            </select>
        </div>
        
        <!-- Wissel Menu -->
        <div id="wisselMenu" class="mb-3" style="display:none;">
            
            <div id="wisselBlockActionsContainer" class="card mb-3 border-success">
                <div class="card-body p-3 text-center" id="wisselBlockActionsBody">
                    <!-- JS vult dit -->
                </div>
            </div>

            <div class="text-center text-muted small fw-bold mb-3">- OF INDIVIDUELE WISSEL (UITZONDERING) -</div>

            <div class="card bg-light border-0">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-success">Wie komt er IN het veld? (Bankzitters)</label>
                    <select class="form-select mb-3" id="wisselPlayerInId">
                        <option value="">Selecteer speler IN...</option>
                        <!-- JS vult dit -->
                    </select>
                    <label class="form-label fw-bold small text-danger">Wie gaat er UIT? (Veldspelers)</label>
                    <select class="form-select" id="wisselPlayerOutId">
                        <option value="">Selecteer speler UIT...</option>
                        <!-- JS vult dit -->
                    </select>
                </div>
            </div>
        </div>

        <hr>
        
        <!-- Tijd Finetuner -->
        <label class="form-label fw-bold small">Speelminuut (kan je nog bijsturen)</label>
        <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded border">
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(-5)">-5</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(-2)">-2</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(-1)">-1</button>
            </div>
            <div class="fw-bold fs-4 text-primary" id="eventMinuteDisplay">0'</div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(1)">+1</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(2)">+2</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustMinute(5)">+5</button>
            </div>
        </div>

      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary text-dark bg-white border" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-warning fw-bold" id="btnSaveEvent" onclick="submitEvent()">Opslaan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Tijdstip Aanpassen -->
<div class="modal fade" id="editTimeModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-bottom-0">
        <h5 class="modal-title fs-6 fw-bold"><i class="fa-regular fa-clock text-primary me-1"></i> Tijdstip Aanpassen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
         <input type="hidden" id="editTimeEventId">
         <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
            <button class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;" onclick="adjustEditTime(-1)"><i class="fa-solid fa-minus"></i></button>
            <h2 id="editTimeDisplay" class="mb-0 fw-bold text-dark" style="font-family: monospace;">00:00</h2>
            <button class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;" onclick="adjustEditTime(1)"><i class="fa-solid fa-plus"></i></button>
         </div>
         <div id="editTimeSuggestionContainer" class="mt-4" style="display:none;">
            <p class="small text-muted mb-2"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Suggestie obv startuur & duur:</p>
            <button class="btn btn-sm btn-info text-dark fw-bold w-100 rounded-pill" id="editTimeSuggestionBtn" onclick="applySuggestedTime()">Suggestie (00:00)</button>
         </div>
      </div>
      <div class="modal-footer p-2 bg-light border-top-0">
         <button class="btn btn-primary w-100 fw-bold rounded-pill" onclick="saveEditTime()"><i class="fa-solid fa-save me-1"></i> Opslaan</button>
      </div>
    </div>
  </div>
</div>

<script>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    
    const matchStarted = <?= $matchStarted ? 'true' : 'false' ?>;
    const isPaused = <?= $isPaused ? 'true' : 'false' ?>;
    const pausedAtMs = <?= $pausedAtMs ?>;
    let activeBlockEventTimeMs = <?= $activeBlockEventTimeMs ?>;
    let currentShiftIndex = <?= $currentShiftIndex ?>;
    const shiftsData = <?= json_encode($shifts_data) ?>;
    const playerMap = <?= json_encode($playerMap) ?>;
    const gameId = <?= (int)$gameId ?>;
    const teamName = <?= json_encode($teamName) ?>;
    const gameBlockLabels = <?= json_encode($gameBlockLabels) ?>;
    const gameOpponent = <?= json_encode($gameOpponent) ?>;
    const isTournament = <?= $isTournament ? 'true' : 'false' ?>;
    const currentGameCounter = <?= (int)$currentGameCounter ?>;
    const totalGames = <?= (int)$totalGames ?>;
    // Tijdzone offset in minuten t.o.v. UTC (voor timestamp display in feed)
    const timezoneOffsetMinutes = <?= (int)$timezoneOffsetMinutes ?>;

    let currentCalculatedMinute = 0;
    let currentAdjustedMinute = 0;
    let clockInterval = null;
    let matchEndedAtMs = <?php
        if ($matchEndedAt) {
            $dtEnd = new DateTime($matchEndedAt, new DateTimeZone('UTC'));
            echo $dtEnd->getTimestamp() * 1000;
        } else {
            echo 'null';
        }
    ?>;
    // Als de match al gepauzeerd of beëindigd is (server zegt dit via PHP vars),
    // dan mag de auto-trigger NIET opnieuw afvuren na een page reload.
    let autoEventTriggered = isPaused || !!matchEndedAtMs;

    document.addEventListener('DOMContentLoaded', function() {
        window.scrollTo(0,0);
        
        const storedEmail = localStorage.getItem('parent_email');
        const skipIdentityFlag = localStorage.getItem('skip_identity');
        
        if (!storedEmail && skipIdentityFlag !== '1') {
            var myModal = new bootstrap.Modal(document.getElementById('parentIdentityModal'));
            myModal.show();
        }
        
        if (matchStarted) {
            startClockInterval();
        }
    });

    function saveIdentity() {
        const email = document.getElementById('parentEmailInput').value.trim();
        const name = document.getElementById('parentNameInput').value.trim();
        if (email) {
            localStorage.setItem('parent_email', email);
            if (name) localStorage.setItem('parent_name', name);
            bootstrap.Modal.getInstance(document.getElementById('parentIdentityModal')).hide();
        } else {
            alert('Vul een geldig e-mailadres in of kies voor "Kijken".');
        }
    }

    function skipIdentity() {
        localStorage.setItem('skip_identity', '1');
        bootstrap.Modal.getInstance(document.getElementById('parentIdentityModal')).hide();
    }

    function getParentEmail() {
        return localStorage.getItem('parent_email') || '';
    }

    function getParentName() {
        return localStorage.getItem('parent_name') || '';
    }

    // Hulpfunctie: geef de game-naam voor een gegeven game_counter
    function getGameLabel(gc) {
        if (isTournament) {
            return gameBlockLabels[gc - 1] || ('Wedstrijd ' + gc);
        } else {
            return 'Wedstrijd ' + gc;
        }
    }

    // Hulpfunctie: geef de eerste shift van een gegeven game_counter
    function getFirstShiftOfGame(gc) {
        return shiftsData.find(s => s.game_counter === gc) || shiftsData[0];
    }

    function getActiveShift() {
        return shiftsData[currentShiftIndex] || shiftsData[0];
    }
    
    function isMatchEnded() {
        return !!matchEndedAtMs;
    }

    // Timer loopt enkel als de match gestart is, NIET gepauzeerd en NIET beëindigd
    function isTimerRunning() {
        return matchStarted && !isPaused && !isMatchEnded();
    }

    function updateGoalButtons() {
        const running = isTimerRunning();
        const btnGoal    = document.getElementById('btnGoal');
        const btnOppGoal = document.getElementById('btnOppGoal');
        if (!btnGoal || !btnOppGoal) return;
        btnGoal.disabled    = !running;
        btnOppGoal.disabled = !running;
        btnGoal.title    = running ? '' : 'Timer moet lopen om een goal te kunnen loggen';
        btnOppGoal.title = running ? '' : 'Timer moet lopen om een tegengoal te kunnen loggen';
        btnGoal.classList.toggle('opacity-50', !running);
        btnOppGoal.classList.toggle('opacity-50', !running);
    }

    function calculateElapsedMinutes() {
        if (!matchStarted) return 0;
        const firstShift = getFirstShiftOfGame(currentGameCounter);
        const now = new Date().getTime();
        let diffMs = Math.max(0, now - activeBlockEventTimeMs);
        if (isPaused) diffMs = Math.max(0, pausedAtMs - activeBlockEventTimeMs);
        // Tornooi: per game timer (start altijd op 0)
        // Gewone match: cumulatief (firstShift.start_minute geeft offset)
        const baseSeconds = isTournament ? 0 : (firstShift.start_minute * 60);
        return Math.floor((baseSeconds + diffMs / 1000) / 60) + 1;
    }
    
    function formatClock() {
        if (!matchStarted) return '00:00';
        // Tornooi gepauzeerd = tussen wedstrijden → timer klaar op 00:00
        if (isPaused && isTournament) return '00:00';
        const firstShift = getFirstShiftOfGame(currentGameCounter);
        const now = matchEndedAtMs ? matchEndedAtMs : new Date().getTime();
        let diffMs = Math.max(0, now - activeBlockEventTimeMs);
        if (isPaused) diffMs = Math.max(0, pausedAtMs - activeBlockEventTimeMs);
        // Tornooi: reset naar 0 per game | Gewone match: cumulatief
        const baseSeconds = isTournament ? 0 : (firstShift.start_minute * 60);
        const totalSeconds = Math.floor(baseSeconds + diffMs / 1000);
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function startClockInterval() {
        if (clockInterval) clearInterval(clockInterval);
        const display = document.getElementById('liveClockDisplay');
        if (display) {
            display.innerText = formatClock();
            updateBlockLabel();
            updateWisselHint();
            // Interval loopt als de match bezig is (niet gepauzeerd tussen wedstrijdjes EN niet helemaal gedaan)
            if (matchStarted && !matchEndedAtMs && !isPaused) {
                clockInterval = setInterval(() => {
                    display.innerText = formatClock();
                    updateBlockLabel();
                    updateWisselHint();
                }, 1000);
            }
        }
    }

    function updateBlockLabel() {
        const lbl = document.getElementById('activeBlockLabel');
        if (!lbl) return;
        if (!matchStarted) {
            lbl.innerText = '';
            return;
        }
        if (matchEndedAtMs) {
            lbl.innerText = '\ud83c\udfc1 Einde';
            return;
        }
        // Toon alleen de naam van de huidige wedstrijd (geen helft-vermelding)
        lbl.innerText = getGameLabel(currentGameCounter);
    }

    function updateWisselHint() {
        if (!matchStarted) return;

        // Ververs de block label
        const blockLbl = document.getElementById('activeBlockLabel');
        if (blockLbl) blockLbl.innerText = matchEndedAtMs ? '\ud83c\udfc1 Einde' : getGameLabel(currentGameCounter);

        // Overtijd auto-trigger (op basis van de TOTALE gameDuur, niet per helft)
        if (!matchEndedAtMs && !isPaused) {
            const firstShift = getFirstShiftOfGame(currentGameCounter);
            // Som alle helften van de huidige game op voor de totale duur
            const totalGameDurSec = shiftsData
                .filter(s => s.game_counter === currentGameCounter)
                .reduce((acc, s) => acc + s.duration * 60, 0);
            const now = new Date().getTime();
            let diffMs = Math.max(0, now - activeBlockEventTimeMs);
            if (diffMs / 1000 >= totalGameDurSec * 1.20 && !autoEventTriggered) {
                autoEventTriggered = true;
                if (currentGameCounter < totalGames) {
                    sendAutoEvent('period_end', calculateElapsedMinutes());
                } else {
                    sendAutoEvent('match_end', calculateElapsedMinutes());
                }
            }
        }

        const btnStartVolgende = document.getElementById('btnStartVolgende');
        const btnFluitAf = document.getElementById('btnFluitAf');
        const btnEindeMatch = document.getElementById('btnEindeMatch');
        const btnUitzonderingWissel = document.getElementById('btnUitzonderingWissel');
        const lbl = document.getElementById('lblActionBtn');

        const hideAll = () => {
            [btnStartVolgende, btnFluitAf, btnEindeMatch, btnUitzonderingWissel].forEach(b => b && b.classList.add('d-none'));
        };
        const showBtn = (btn, html, cls, onClick) => {
            btn.innerHTML = html;
            btn.className = 'btn ' + cls + ' shadow-sm w-100 fw-bold';
            btn.style = 'padding: 3px 6px; font-size: 0.78rem;';
            btn.onclick = onClick;
            btn.classList.remove('d-none');
        };

        hideAll();
        updateGoalButtons();

        if (matchEndedAtMs) {
            // Match volledig gedaan — geen knoppen
            return;
        }

        if (isPaused) {
            // Tussen wedstrijdjes — toon 'Start [volgende]'
            const nextGC = currentGameCounter + 1;
            if (nextGC <= totalGames) {
                const nextLabel = getGameLabel(nextGC);
                if (lbl) lbl.innerText = 'Volgende';
                showBtn(btnStartVolgende, '\u25b6 Start ' + nextLabel, 'btn-success btn-wissel-due',
                    () => sendStartGame(nextGC));
            }
            return;
        }

        // Game loopt — toon Stop
        const gameName = getGameLabel(currentGameCounter);
        if (currentGameCounter >= totalGames) {
            // Laatste wedstrijd — Stop = match_end
            if (lbl) lbl.innerText = 'Einde';
            showBtn(btnEindeMatch, '\u23f9 Stop ' + gameName, 'btn-danger', () => submitMatchEnd());
        } else {
            // Niet-laatste — Stop = period_end (pauze tussen wedstrijdjes)
            if (lbl) lbl.innerText = 'Stop';
            showBtn(btnFluitAf, '\u23f9 Stop ' + gameName, 'btn-warning', () => submitPauseBlock());
        }
    }

    function populateDropdown(selectId, playersData, isPitch) {
        const sel = document.getElementById(selectId);
        if (selectId === 'assistPlayerId') {
            sel.innerHTML = '<option value="">Geen assist / Onbekend</option>';
        } else {
            sel.innerHTML = '<option value="">Selecteer speler...</option>';
        }
        if (isPitch) {
            playersData.forEach(item => {
                if (playerMap[item.id]) {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.innerText = playerMap[item.id];
                    sel.appendChild(opt);
                }
            });
        } else {
            playersData.forEach(id => {
                if (playerMap[id]) {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.innerText = playerMap[id];
                    sel.appendChild(opt);
                }
            });
        }
    }

    function startMatch() {
        const email = getParentEmail();
        if (!email) {
            alert("Je moet een e-mailadres opgeven om de wedstrijd te kunnen starten!");
            localStorage.removeItem('skip_identity');
            new bootstrap.Modal(document.getElementById('parentIdentityModal')).show();
            return;
        }
        
        if(!confirm("Ben je zeker dat de match NU start?")) return;
        
        sendApiEvent('match_start', 0);
    }

    // Start de volgende wedstrijd (game-niveau: 1 period_start = 1 wedstrijd)
    function sendStartGame(targetGameCounter) {
        autoEventTriggered = true; // Voorkom dubbele auto-trigger
        sendApiEventObject({
            action:               'start_game',
            game_id:              gameId,
            parent_email:         getParentEmail() || 'auto@systeem',
            parent_name:          getParentName() || null,
            target_game_counter:  targetGameCounter
        });
    }
    
    function submitPauseBlock() {
        if (!matchStarted) return;
        const gameName = getGameLabel(currentGameCounter);
        if (!confirm(`Stop ${gameName} en pauzeer de timer?`)) return;
        autoEventTriggered = true; // Voorkom dubbele auto-trigger
        sendApiEvent('period_end', calculateElapsedMinutes());
    }

    function submitMatchEnd() {
        if (!matchStarted) return;
        const gameName = getGameLabel(currentGameCounter);
        if (!confirm(`Stop ${gameName} en beëindig het tornooi?`)) return;
        autoEventTriggered = true; // Voorkom dubbele auto-trigger
        sendApiEvent('match_end', calculateElapsedMinutes());
    }

    function openEventModal(type) {
        const email = getParentEmail();
        if (!email) {
            alert("Je moet je e-mailadres opgeven om acties te kunnen loggen.");
            localStorage.removeItem('skip_identity');
            new bootstrap.Modal(document.getElementById('parentIdentityModal')).show();
            return;
        }
        if (!matchStarted) {
            alert("Start eerst de wedstrijd met de timer links onderaan!");
            return;
        }
        // Blokkeer goal/tegengoal als de timer niet loopt (gepauzeerd of wedstrijd gedaan)
        if ((type === 'goal' || type === 'opp_goal') && !isTimerRunning()) {
            alert("De timer loopt niet. Start de volgende wedstrijd voor je een goal logt.");
            return;
        }

        const shift = getActiveShift();
        document.getElementById('eventTypeInput').value = type;
        document.getElementById('btnSaveEvent').style.display = 'block';
        
        if (type === 'goal') {
            document.getElementById('eventModalTitle').innerText = '⚽ Goal Melden';
            document.getElementById('goalPlayerSelect').style.display = 'block';
            document.getElementById('wisselMenu').style.display = 'none';
            // Alle spelers (pitch + bank) — iedereen kan scoren
            const allPlayers = [...(shift.pitch || []), ...(shift.bench || [])]
                .sort((a, b) => {
                    const na = (playerMap[a.id] ? playerMap[a.id].first_name + ' ' + playerMap[a.id].last_name : '');
                    const nb = (playerMap[b.id] ? playerMap[b.id].first_name + ' ' + playerMap[b.id].last_name : '');
                    return na.localeCompare(nb);
                });
            populateDropdown('goalPlayerId', allPlayers, true);
            populateDropdown('assistPlayerId', allPlayers, true);
        } else if (type === 'opp_goal') {
            document.getElementById('eventModalTitle').innerText = '🥅 Tegengoal Melden';
            document.getElementById('goalPlayerSelect').style.display = 'none';
            document.getElementById('wisselMenu').style.display = 'none';
        } else if (type === 'wissel') {
            document.getElementById('eventModalTitle').innerText = '🔄 Wissel Menu';
            document.getElementById('goalPlayerSelect').style.display = 'none';
            document.getElementById('wisselMenu').style.display = 'block';
            
            const wisselBody = document.getElementById('wisselBlockActionsBody');
            wisselBody.innerHTML = '';
            
            if (currentShiftIndex < shiftsData.length - 1) {
                document.getElementById('wisselBlockActionsContainer').style.display = 'block';
                const cShift = shiftsData[currentShiftIndex];
                const nShift = shiftsData[currentShiftIndex + 1];
                const shiftTitle = nShift.title || ('Blok ' + nShift.index);
                
                wisselBody.innerHTML = `<h6 class="fw-bold mb-1">Schema Opvolgen</h6>`;
                
                if (isPaused) {
                    wisselBody.innerHTML += `<p class="small text-muted mb-2">De wedstrijd is gepauzeerd. Bevestig dat het volgende deel gestart is.</p>`;
                    wisselBody.innerHTML += `<button class="btn btn-success btn-sm fw-bold w-100" onclick="submitNextBlock()">▶ Start ${shiftTitle}</button>`;
                } else {
                    if (cShift.game_counter === nShift.game_counter) {
                        wisselBody.innerHTML += `<p class="small text-muted mb-2">Wissels doorgevoerd? Bevestig de start van de volgende shift (vliegende wissel).</p>`;
                        wisselBody.innerHTML += `<button class="btn btn-success btn-sm fw-bold w-100" onclick="submitNextBlock()">▶ Start ${shiftTitle}</button>`;
                    } else {
                        wisselBody.innerHTML += `<p class="small text-muted mb-2">Het huidige wedstrijdje zit erop. Fluit af voor de rust/nieuwe opstelling.</p>`;
                        wisselBody.innerHTML += `<button class="btn btn-warning btn-sm fw-bold w-100 mb-2 text-dark" onclick="submitPauseBlock()">⏹ Fluit af (Pauzeer Timer)</button>`;
                    }
                }
            } else {
                document.getElementById('wisselBlockActionsContainer').style.display = 'block';
                wisselBody.innerHTML = `<h6 class="fw-bold mb-1">Einde Wedstrijd</h6>`;
                if (!isMatchEnded()) {
                    wisselBody.innerHTML += `<p class="small text-muted mb-2">Zit de laatste wedstrijd er helemaal op?</p>`;
                    wisselBody.innerHTML += `<button class="btn btn-danger btn-sm fw-bold w-100" onclick="submitMatchEnd()">🛑 Fluit definitief af</button>`;
                } else {
                    wisselBody.innerHTML += `<p class="small text-muted mb-0">De wedstrijd is afgerond.</p>`;
                }
            }
            
            populateDropdown('wisselPlayerInId', shift.bench, false);
            populateDropdown('wisselPlayerOutId', shift.pitch, true);
        }

        currentCalculatedMinute = calculateElapsedMinutes();
        currentAdjustedMinute = currentCalculatedMinute;
        updateMinuteDisplay();

        var myModal = new bootstrap.Modal(document.getElementById('eventActionModal'));
        myModal.show();
    }

    function adjustMinute(amount) {
        let newMin = currentAdjustedMinute + amount;
        if (newMin < 0) newMin = 0;
        currentAdjustedMinute = newMin;
        updateMinuteDisplay();
    }

    function updateMinuteDisplay() {
        document.getElementById('eventMinuteDisplay').innerText = currentAdjustedMinute + "'";
    }

    // updateBlockLabel is now integrated in updateWisselHint above

    function submitEvent() {
        const type = document.getElementById('eventTypeInput').value;
        const email = getParentEmail();
        
        let payload = {
            action: 'log_event',
            game_id: gameId,
            parent_email: email,
            event_type: type,
            event_minute: currentAdjustedMinute
        };

        if (type === 'goal') {
            const pid = document.getElementById('goalPlayerId').value;
            if (!pid) { alert("Selecteer de speler die gescoord heeft."); return; }
            payload.player_id = pid;
            const assistPid = document.getElementById('assistPlayerId').value;
            if (assistPid) { payload.player_out_id = assistPid; }
        } else if (type === 'opp_goal') {
            // Niets extra nodig
        } else if (type === 'wissel') {
            const pIn = document.getElementById('wisselPlayerInId').value;
            const pOut = document.getElementById('wisselPlayerOutId').value;
            if (!pIn || !pOut) { alert("Selecteer wie in en uit gaat voor een individuele wissel."); return; }
            if (pIn === pOut) { alert("Je kan niet wisselen met dezelfde speler."); return; }
            payload.player_id = pIn;
            payload.player_out_id = pOut;
            payload.event_type = 'substitution';
        }

        sendApiEventObject(payload);
    }

    function sendApiEvent(eventType, eventMinute, playerId = null, playerOutId = null) {
        const email = getParentEmail();
        const name = getParentName();
        const payload = {
            action: 'log_event',
            game_id: gameId,
            parent_email: email,
            parent_name: name || null,
            event_type: eventType,
            event_minute: eventMinute
        };
        if (playerId) payload.player_id = playerId;
        if (playerOutId) payload.player_out_id = playerOutId;
        sendApiEventObject(payload);
    }

    // Fire-and-forget auto events: GEEN location.reload() — de fetchLiveEvents poll
    // detecteert de state change en doet de reload. Zo vermijden we een infinite reload loop
    // wanneer het auto-event wordt geduplicated door de server.
    function sendAutoEvent(eventType, eventMinute) {
        fetch('/api/api_game_events.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'log_event',
                game_id: gameId,
                parent_email: 'auto@systeem',
                event_type: eventType,
                event_minute: eventMinute
            })
        }).catch(() => {}); // Stilletjes falen is ok — poll vangt het op
    }

    function btnLoading(btn) {
        if (!btn || !btn.classList) return;
        btn.disabled = true;
        if (!btn.hasAttribute('data-original-text')) {
            btn.setAttribute('data-original-text', btn.innerHTML);
        }
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        setTimeout(() => {
            if(btn && btn.hasAttribute('data-original-text')) { 
                btn.disabled = false; 
                btn.innerHTML = btn.getAttribute('data-original-text'); 
            }
        }, 5000);
    }

    function sendApiEventObject(payload) {
        // Always include parent_name if we have it and it's not already set
        if (!payload.parent_name) {
            const name = getParentName();
            if (name) payload.parent_name = name;
        }
        if (document.activeElement && document.activeElement.tagName === 'BUTTON') {
            btnLoading(document.activeElement);
        }
        fetch('/api/api_game_events.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                const modalEl = document.getElementById('eventActionModal');
                if (modalEl) {
                    const modalInst = bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) modalInst.hide();
                }
                location.reload(); // Herlaad om de nieuwe shifts en timer direct correct te zetten
            } else if (data.status === 'deduped') {
                // Event was al geregistreerd door iemand anders — geen actie nodig, gewoon refreshen
                location.reload();
            } else if (data.status === 'warning') {
                if (confirm(data.warning_text)) {
                    payload.force = true;
                    sendApiEventObject(payload);
                } else {
                    location.reload();
                }
            } else {
                // Server weigerde het event (bv. timer loopt niet) — toon melding, sluit modal en herlaad
                alert(data.message || 'Fout bij opslaan.');
                const modalEl = document.getElementById('eventActionModal');
                if (modalEl) { const m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); }
                location.reload(); // Sync de UI naar de werkelijke match state
            }
        }).catch(err => {
            alert("Verbindingsfout.");
        });
    }
    function fetchLiveEvents() {
        if (!matchStarted) return;
        fetch('/api/api_game_events.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'get_events', game_id: gameId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.events) {
                renderLiveEvents(data.events);
            }
        }).catch(() => {});
    }

    function renderLiveEvents(events) {
        // Sync state: reload if block changed or paused state changed
        let serverBlockStarts = events.filter(e => e.event_type === 'match_start' || e.event_type === 'period_start');
        let serverBlockCount = serverBlockStarts.length;
        if (serverBlockCount > shiftsData.length) serverBlockCount = shiftsData.length;
        
        let lastPeriodStart = serverBlockStarts[serverBlockStarts.length - 1];
        let lastPeriodEnd = events.filter(e => e.event_type === 'period_end').pop();
        let isServerPaused = !!(lastPeriodEnd && (!lastPeriodStart || parseInt(lastPeriodEnd.id) > parseInt(lastPeriodStart.id)));
        
        let lastReload = sessionStorage.getItem('last_sync_reload_' + gameId);
        let now = Date.now();
        let canReload = !lastReload || (now - parseInt(lastReload) > 10000); // min 10s between reloads
        
        if (serverBlockCount > 0 && serverBlockCount - 1 > currentShiftIndex) {
            if (canReload) {
                sessionStorage.setItem('last_sync_reload_' + gameId, now);
                location.reload();
            } else {
                console.warn("Prevented infinite reload (BlockCount mismatch).", serverBlockCount, currentShiftIndex);
            }
            return;
        }
        if (isServerPaused !== isPaused) {
            if (canReload) {
                sessionStorage.setItem('last_sync_reload_' + gameId, now);
                location.reload();
            } else {
                console.warn("Prevented infinite reload (Paused mismatch). server:", isServerPaused, "local:", isPaused, "lastStart:", lastPeriodStart?.id, "lastEnd:", lastPeriodEnd?.id);
            }
            return;
        }

        const feed = document.getElementById('liveEventsFeed');
        feed.innerHTML = '';
        
        // isTournament is een globale const (gedeclareerd bovenaan de script tag)
        let homeScore = 0;
        let awayScore = 0;
        
        let currentBlockIndex = 1;
        let activeGameCounter = 1;
        window.startEventPerBlock = {};
        
        // Bijhouden block start tijden voor relatieve minuten berekening
        window.blockStartCreatedAt = {};
        window.gameStartCreatedAt = {}; // Voor tornooi: start van elke game (game_counter)

        // Calculate score and assign blocks
        events.forEach(e => {
            let tempBlockIndex = currentBlockIndex;
            if (e.event_type === 'match_start' || e.event_type === 'period_start') {
                if (window.startEventPerBlock[currentBlockIndex]) tempBlockIndex++;
            }
            
            let shiftData = shiftsData[tempBlockIndex - 1];
            // Game-niveau: blockIndex IS game_counter
            let blockGameCounter = tempBlockIndex;
            // Gebruik de eerste shift van de huidige game voor lineup
            const firstShiftOfBlock = getFirstShiftOfGame(blockGameCounter);
            
            if (isTournament && blockGameCounter !== activeGameCounter) {
                homeScore = 0;
                awayScore = 0;
                activeGameCounter = blockGameCounter;
            }

            // Score vóór increment opslaan (voor weergave NA de goal)
            e._scoreBeforeEvent = homeScore + '-' + awayScore;
            
            if (e.event_type === 'goal') {
                homeScore++;
            } else if (e.event_type === 'opp_goal' || e.event_type === 'tegengoal' || !e.event_type || e.event_type.trim() === '') {
                awayScore++;
            }
            
            if (e.event_type === 'match_start' || e.event_type === 'period_start') {
                if (window.startEventPerBlock[currentBlockIndex]) {
                    currentBlockIndex++;
                }
                window.startEventPerBlock[currentBlockIndex] = e;
                window.blockStartCreatedAt[currentBlockIndex] = e.created_at;
                // Game-niveau: blockIndex IS de game_counter (block 1 = game 1, block 2 = game 2)
                const gc = currentBlockIndex;
                if (!window.gameStartCreatedAt[gc]) {
                    window.gameStartCreatedAt[gc] = e.created_at;
                }
                e._blockIndex = currentBlockIndex;
                e._gameCounter = gc;
            } else if (e.event_type === 'match_end' || e.event_type === 'period_end') {
                e._blockIndex = currentBlockIndex;
                currentBlockIndex++;
            } else {
                e._blockIndex = currentBlockIndex;
            }
            
            e._homeScore = homeScore;
            e._awayScore = awayScore;
        });
        
        const scoreDisplay = document.getElementById('liveScoreDisplay');
        if (scoreDisplay) {
            scoreDisplay.innerText = homeScore + ' - ' + awayScore;
        }
        
        const visibleEvents = [...events].reverse();
        
        visibleEvents.forEach(e => {
            const el = document.createElement('div');
            el.className = 'py-2 border-bottom d-flex justify-content-between align-items-center w-100 text-dark';
            el.style.fontSize = '0.9rem';
            
            let timeStr = '';
            if (e.created_at) {
                // UTC timestamp uit DB omzetten naar lokale teamtijdzone
                const utcMs = new Date(e.created_at.replace(' ', 'T') + 'Z').getTime();
                const localMs = utcMs + timezoneOffsetMinutes * 60 * 1000;
                const d = new Date(localMs);
                timeStr = String(d.getUTCHours()).padStart(2,'0') + ':' + String(d.getUTCMinutes()).padStart(2,'0');
            }
            
            const isStatusEvent = ['match_start', 'period_start', 'period_end', 'match_end'].includes(e.event_type);

            // Sla substitution events volledig over — wissels worden niet getoond in de tracker
            if (e.event_type === 'substitution') return;

            // Bereken de matchminuut:
            // - Tornooi: relatief t.o.v. start van de huidige GAME (game_counter), timer reset per game
            // - Gewone match: cumulatief t.o.v. match_start
            // Matchminuut: altijd berekenen van timestamps (UTC-correct met +Z suffix)
            // Stored event_minute kan kapotte waarden bevatten van oude versies
            let relMin = e.event_minute || 0; // fallback
            if (!isStatusEvent && e.created_at) {
                let baseTs;
                if (isTournament) {
                    // Game-niveau: e._blockIndex IS de game_counter
                    const gc = e._blockIndex;
                    baseTs = window.gameStartCreatedAt[gc];
                } else {
                    baseTs = window.blockStartCreatedAt[1]; // match_start
                }
                if (baseTs) {
                    // +Z forceert UTC interpretatie — anders behandelt browser als lokale tijd
                    const bStart = new Date(baseTs.replace(' ', 'T') + 'Z').getTime();
                    const eTime  = new Date(e.created_at.replace(' ', 'T') + 'Z').getTime();
                    const diffSec = (eTime - bStart) / 1000;
                    if (diffSec >= 0) relMin = Math.floor(diffSec / 60) + 1;
                }
            }
            
            let text = isStatusEvent
                ? `<strong class="text-primary me-1" style="cursor:pointer;" onclick="openEditTimeModal(${e.id}, '${timeStr}', '${e.event_type}', ${e._blockIndex})">${timeStr} <i class="fa-solid fa-pen text-muted ms-1" style="font-size:0.7rem;"></i></strong> `
                : `<strong>${relMin}'</strong> `;
            
            let byWho = '';
            if (!isStatusEvent && e.parent_name) {
                byWho = `<span class="text-muted" style="font-size:0.75rem;"> — ${e.parent_name}</span>`;
            } else if (!isStatusEvent && e.parent_email && e.parent_email !== 'auto@systeem') {
                byWho = `<span class="text-muted" style="font-size:0.75rem;"> — ${e.parent_email.split('@')[0]}</span>`;
            }

            if (e.event_type === 'goal') {
                const newScore = e._homeScore + '-' + e._awayScore;
                text += `⚽ <strong>${newScore}</strong> ` + (e.p1_first || 'Onbekend');
                if (e.p2_first) text += ' (' + e.p2_first + ')';
                text += byWho;
            } else if (e.event_type === 'opp_goal' || e.event_type === 'tegengoal' || !e.event_type || e.event_type.trim() === '') {
                const newScore = e._homeScore + '-' + e._awayScore;
                text += `🥅 <strong>${newScore}</strong> Tegendoelpunt` + byWho;
            } else if (e.event_type === 'match_end') {
                // blockIndex = game_counter van de afgelopen game
                const gc = e._blockIndex;
                const opponent = gameBlockLabels[gc - 1] || gameOpponent || 'Tegenstander';
                if (isTournament) {
                    text += `${teamName} - ${opponent} <strong>${e._homeScore}-${e._awayScore}</strong>`;
                } else {
                    text += '\ud83c\udfc1 Einde' + (e.parent_email === 'auto@systeem' ? ' (auto)' : '');
                }
            } else if (e.event_type === 'period_end') {
                // blockIndex = game_counter van de afgelopen game (elke period_end = einde van 1 wedstrijd)
                const gc = e._blockIndex;
                const opponent = gameBlockLabels[gc - 1] || gameOpponent || 'Tegenstander';
                if (isTournament) {
                    text += `${teamName} - ${opponent} <strong>${e._homeScore}-${e._awayScore}</strong>`;
                } else {
                    text += '\u23f8 Einde Wedstrijd ' + gc + (e.parent_email === 'auto@systeem' ? ' (auto)' : '');
                }
            } else if (e.event_type === 'match_start' || e.event_type === 'period_start') {
                // e._gameCounter = blockIndex = game_counter (block 1=game 1, block 2=game 2)
                const gc = e._gameCounter || e._blockIndex;
                text += `▶ Start ${getGameLabel(gc)}` + (e.parent_email === 'auto@systeem' ? ' (auto)' : '');
            } else {
                text += '[' + e.event_type + ']';
            }
            
            const textSpan = document.createElement('span');
            textSpan.innerHTML = text + (e.is_confirmed == 1 ? ' <i class="fa-solid fa-check text-success ms-1"></i>' : '');
            el.appendChild(textSpan);

            if (e.parent_email === getParentEmail() && e.is_confirmed == 0) {
                const delBtn = document.createElement('button');
                delBtn.className = 'btn btn-sm btn-link text-danger p-0 m-0 text-decoration-none';
                delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                delBtn.style.fontSize = '1.2rem';
                delBtn.style.lineHeight = '1';
                delBtn.onclick = () => deleteOwnEvent(e.id);
                el.appendChild(delBtn);
            }
            
            feed.appendChild(el);
        });
    }

    function deleteOwnEvent(eventId) {
        if (!confirm("Ben je zeker dat je deze foutieve actie wil verwijderen?")) return;
        const email = getParentEmail();
        fetch('/api/api_game_events.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'delete_own_event', event_id: eventId, parent_email: email })
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                fetchLiveEvents();
            } else {
                alert("Kon event niet verwijderen.");
            }
        });
    }

    // Telemetry - Monitor Memory, DOM & Page Load
    // page_load_ms via PerformanceNavigationTiming: works in ALL browsers (Chrome, Firefox, Safari)
    // js_heap_mb via performance.memory: Chromium only (returns 0 in Firefox/Safari — that's fine)
    setInterval(() => {
        let jsHeapMb = 0;
        if (performance && performance.memory) {
            jsHeapMb = Math.round(performance.memory.usedJSHeapSize / 1024 / 1024 * 100) / 100;
        }

        let pageLoadMs = 0;
        try {
            const nav = performance.getEntriesByType('navigation')[0];
            if (nav) pageLoadMs = Math.round(nav.loadEventEnd - nav.startTime);
        } catch(e) {}

        let domNodes = document.getElementsByTagName('*').length;
        let uType = 'guest';
        <?php if (isset($_SESSION['user_id'])): ?>uType = 'coach';<?php elseif (!empty($_SESSION['parent_email'])): ?>uType = 'parent';<?php endif; ?>

        fetch('/api/api_telemetry.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'log_telemetry',
                game_id: gameId,
                user_type: uType,
                identifier: parentEmail || 'guest',
                js_heap_mb: jsHeapMb,
                dom_nodes: domNodes,
                page_load_ms: pageLoadMs,
                page: 'share/<?= $game_id ?? '' ?>'
            })
        }).catch(() => {}); // Silent fail - telemetry is non-critical
    }, 60000); // Elke minuut

    // Manuele refresh door de ouder
    function manualRefreshFeed() {
        const btn = document.getElementById('btnRefreshFeed');
        const lbl = document.getElementById('lastRefreshLabel');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-rotate-right fa-spin"></i> Bezig...'; }
        fetchLiveEvents();
        setTimeout(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Ververs'; }
            const now = new Date();
            const hhmm = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0');
            if (lbl) lbl.innerText = 'Bijgewerkt om ' + hhmm;
        }, 1200);
    }

    let fetchIntervalId = null;
    if (matchStarted) {
        // Eerste fetch bij paginastart
        fetchLiveEvents();
        // Auto-poll elke 75 seconden — goed evenwicht tussen realtime en DB-load
        fetchIntervalId = setInterval(() => {
            if (isMatchEnded()) {
                clearInterval(fetchIntervalId);
            } else {
                fetchLiveEvents();
                // GA4 tracking van auto-refresh
                if (typeof gtag === 'function') {
                    gtag('event', 'live_feed_auto_refresh', { game_id: gameId });
                }
            }
        }, 75000);
    }

    let currentEditTimeStr = '';
    
    function parseTimeToMins(str) {
        if (!str) return 0;
        let parts = str.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }
    
    function formatMinsToTime(mins) {
        let h = Math.floor(mins / 60);
        let m = mins % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    }

    function adjustEditTime(delta) {
        let mins = parseTimeToMins(currentEditTimeStr) + delta;
        if (mins < 0) mins = 0;
        currentEditTimeStr = formatMinsToTime(mins);
        document.getElementById('editTimeDisplay').innerText = currentEditTimeStr;
    }

    function applySuggestedTime() {
        currentEditTimeStr = document.getElementById('editTimeSuggestionBtn').dataset.time;
        document.getElementById('editTimeDisplay').innerText = currentEditTimeStr;
    }

    function openEditTimeModal(eventId, timeStr, eventType, blockIndex) {
        document.getElementById('editTimeEventId').value = eventId;
        currentEditTimeStr = timeStr;
        document.getElementById('editTimeDisplay').innerText = currentEditTimeStr;
        
        let sugContainer = document.getElementById('editTimeSuggestionContainer');
        sugContainer.style.display = 'none';
        
        if (eventType === 'period_end' || eventType === 'match_end') {
            if (window.startEventPerBlock && window.startEventPerBlock[blockIndex]) {
                let startStr = window.startEventPerBlock[blockIndex].created_at.substring(11, 16);
                let shiftData = shiftsData[blockIndex - 1]; // blockIndex is 1-based
                if (shiftData && shiftData.duration) {
                    let endMins = parseTimeToMins(startStr) + parseInt(shiftData.duration);
                    let suggestedTime = formatMinsToTime(endMins);
                    
                    let sugBtn = document.getElementById('editTimeSuggestionBtn');
                    sugBtn.dataset.time = suggestedTime;
                    sugBtn.innerHTML = 'Suggestie (' + suggestedTime + ')';
                    sugContainer.style.display = 'block';
                }
            }
        }
        
        new bootstrap.Modal(document.getElementById('editTimeModal')).show();
    }

    function saveEditTime() {
        let eid = document.getElementById('editTimeEventId').value;
        fetch('/api/api_game_events.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_event_time',
                event_id: eid,
                new_time: currentEditTimeStr
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('editTimeModal')).hide();
                fetchLiveEvents();
            } else {
                alert("Er liep iets mis bij het opslaan.");
            }
        });
    }

</script>
