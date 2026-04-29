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

// Check if it's a tournament by looking at block_labels
$stmtTour = $pdo->prepare("SELECT block_labels FROM games WHERE id = ?");
$stmtTour->execute([$gameId]);
$gameRow = $stmtTour->fetch(PDO::FETCH_ASSOC);
$isTournament = false;
if ($gameRow && !empty($gameRow['block_labels']) && $gameRow['block_labels'] !== 'null' && $gameRow['block_labels'] !== '[]') {
    $isTournament = true;
}

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
        
        $shifts_data[] = [
            'index' => $idx + 1,
            'title' => $title,
            'duration' => $duration_minutes,
            'start_minute' => $start_minute,
            'bench' => array_values($ev['bench'] ?? []),
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

$stmtMatchEnd = $pdo->prepare("SELECT created_at FROM game_events WHERE game_id = ? AND event_type = 'match_end' AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1");
$stmtMatchEnd->execute([$gameId]);
$matchEndedAt = $stmtMatchEnd->fetchColumn();

$matchStarted = count($blockEvents) > 0;
// De currentShiftIndex is hoeveel block events er zijn (1 event = shift index 0, 2 events = shift index 1)
$currentShiftIndex = max(0, count($blockEvents) - 1);
if ($currentShiftIndex >= count($shifts_data)) {
    $currentShiftIndex = count($shifts_data) - 1; // Cap op laatste blok
}

$activeBlockEventTimeMs = 'null';
if ($matchStarted) {
    // Tijdstip van de start van het huidige blok
    $activeBlockEventTimeMs = strtotime($blockEvents[$currentShiftIndex]) * 1000;
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

<div class="container d-print-none mt-3 mb-4" id="parentsShareTabsContainer">
  <ul class="nav nav-pills nav-fill bg-white p-1 rounded shadow-sm border mb-3" id="parentsTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active fw-bold text-dark border-0" data-bs-toggle="pill" data-bs-target="#tab-tracker" type="button" role="tab"><i class="fa-solid fa-stopwatch me-1"></i> Match Tracker</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link fw-bold text-dark border-0" data-bs-toggle="pill" data-bs-target="#tab-lineup" type="button" role="tab"><i class="fa-solid fa-clipboard-list me-1"></i> Opstelling</button>
    </li>
  </ul>
  
  <div class="tab-content" id="parentsTabsContent">
    <div class="tab-pane fade show active d-flex flex-column align-items-center" id="tab-tracker" role="tabpanel">
       
        <div class="parents-bottom-bar w-100">
            <div id="liveClockContainer" class="parents-clock-container w-100 d-flex justify-content-center">
                <?php if ($matchStarted): ?>
                    <div class="d-flex justify-content-center align-items-center gap-3 w-100 flex-nowrap">
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label" id="currentBlockLabel">Loading...</div>
                            <div class="parents-clock" id="liveClockDisplay">00:00</div>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label">Score</div>
                            <div class="parents-clock text-primary border-primary bg-white" id="liveScoreDisplay">0 - 0</div>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-grow-1" style="flex-basis: 33%;">
                            <div class="parents-block-label">Wissel</div>
                            <button id="btnWissel" class="btn btn-secondary shadow-sm w-100" style="padding: 3px 8px; font-weight: bold; height: 32px;" onclick="openEventModal('wissel')">🔄 Wissel</button>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn btn-primary fw-bold" onclick="startMatch()">▶ Start Match</button>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2 w-100 justify-content-center mt-1">
                <button class="btn btn-primary fw-bold shadow-sm flex-fill" onclick="openEventModal('goal')">⚽ Goal</button>
                <button class="btn btn-danger fw-bold shadow-sm flex-fill" onclick="openEventModal('opp_goal')">🥅 Tegengoal</button>
            </div>
            <hr class="w-100 my-1 text-muted">
            <h6 class="text-start fw-bold text-muted w-100 mb-0" style="font-size: 0.85rem;"><i class="fa-solid fa-list-check me-1"></i> Wedstrijdverloop</h6>
            <div id="liveEventsFeed" class="w-100 pb-1"></div>
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
            
            <?php if ($currentShiftIndex < $totalBlocksCount - 1): ?>
            <div class="card mb-3 border-success">
                <div class="card-body p-3 text-center">
                    <h6 class="fw-bold mb-1">Volgend Blok Starten</h6>
                    <p class="small text-muted mb-2">Bevestig dat het geplande wisselmoment (<span id="modalNextBlockName">Blok <?= $currentShiftIndex + 2 ?></span>) is ingegaan. De timer springt dan automatisch naar de start van het nieuwe blok.</p>
                    <button class="btn btn-success btn-sm fw-bold w-100" onclick="submitNextBlock()">▶ Start <span id="modalNextBlockBtnName">Blok <?= $currentShiftIndex + 2 ?></span></button>
                </div>
            </div>
            <div class="text-center text-muted small fw-bold mb-3">- OF INDIVIDUELE WISSEL (UITZONDERING) -</div>
            <?php endif; ?>

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

<script>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    
    const matchStarted = <?= $matchStarted ? 'true' : 'false' ?>;
    let activeBlockEventTimeMs = <?= $activeBlockEventTimeMs ?>;
    let currentShiftIndex = <?= $currentShiftIndex ?>;
    const shiftsData = <?= json_encode($shifts_data) ?>;
    const playerMap = <?= json_encode($playerMap) ?>;
    const gameId = <?= (int)$gameId ?>;

    let currentCalculatedMinute = 0;
    let currentAdjustedMinute = 0;
    let clockInterval = null;
    let matchEndedAtMs = <?= $matchEndedAt ? strtotime($matchEndedAt) * 1000 : 'null' ?>;

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
        if (email) {
            localStorage.setItem('parent_email', email);
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

    function getActiveShift() {
        return shiftsData[currentShiftIndex] || shiftsData[0];
    }
    
    function isMatchEnded() {
        return blockEvents.includes('match_end_time'); // We'll track match end by checking the last event
    }

    function calculateElapsedMinutes() {
        if (!matchStarted) return 0;
        const shift = getActiveShift();
        const now = new Date().getTime();
        let diffMs = now - activeBlockEventTimeMs;
        if (diffMs < 0) diffMs = 0;
        
        const minutesInCurrentBlock = diffMs / 60000;
        return Math.floor(shift.start_minute + minutesInCurrentBlock);
    }
    
    function formatClock() {
        if (!matchStarted) return '00:00';
        const shift = getActiveShift();
        const now = matchEndedAtMs ? matchEndedAtMs : new Date().getTime();
        let diffMs = now - activeBlockEventTimeMs;
        if (diffMs < 0) diffMs = 0;
        
        const totalSeconds = Math.floor((shift.start_minute * 60) + (diffMs / 1000));
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
            
            if (!matchEndedAtMs) {
                clockInterval = setInterval(() => {
                    display.innerText = formatClock();
                    updateBlockLabel();
                    updateWisselHint();
                }, 1000);
            }
        }
    }

    function updateWisselHint() {
        if (!matchStarted || matchEndedAtMs) return;
        const shift = getActiveShift();
        const now = new Date().getTime();
        let diffMs = now - activeBlockEventTimeMs;
        if (diffMs < 0) diffMs = 0;
        
        const currentSeconds = (shift.start_minute * 60) + (diffMs / 1000);
        const expectedEndSeconds = (shift.start_minute + shift.duration) * 60;
        
        const btn = document.getElementById('btnWissel');
        if (btn) {
            // Check if we are past the expected duration
            if (currentSeconds >= expectedEndSeconds) {
                if (currentShiftIndex < shiftsData.length - 1) {
                    btn.classList.add('btn-wissel-due');
                    btn.classList.remove('btn-danger');
                    let nShift = shiftsData[currentShiftIndex + 1];
                    btn.innerHTML = '🔄 ' + (nShift.title || 'Wissel?');
                    btn.onclick = () => openEventModal('wissel');
                } else {
                    btn.classList.add('btn-wissel-due');
                    btn.classList.replace('btn-secondary', 'btn-danger');
                    btn.innerHTML = '🛑 Einde Match?';
                    btn.onclick = () => {
                        if (confirm("Is de wedstrijd definitief afgelopen?")) {
                            sendApiEventObject({
                                action: 'log_event',
                                game_id: gameId,
                                parent_email: getParentEmail(),
                                event_type: 'match_end',
                                event_minute: Math.floor(currentSeconds / 60)
                            });
                        }
                    };
                }
            } else {
                btn.classList.remove('btn-wissel-due');
                btn.classList.remove('btn-danger');
                if (!btn.classList.contains('btn-secondary')) btn.classList.add('btn-secondary');
                btn.innerHTML = '🔄 Wissel';
                btn.onclick = () => openEventModal('wissel');
            }
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
                    opt.innerText = (item.pos && item.pos !== '?') ? `(#${item.pos}) ` + playerMap[item.id] : playerMap[item.id];
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

    function submitNextBlock() {
        const email = getParentEmail();
        if (!email) return alert("Je e-mailadres is vereist.");
        
        if(!confirm("Start het volgende wisselblok nu? De timer wordt aangepast naar de geplande starttijd van dit blok.")) return;
        
        const nextShift = shiftsData[currentShiftIndex + 1];
        if (!nextShift) return;

        sendApiEvent('period_start', Math.floor(nextShift.start_minute));
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

        const shift = getActiveShift();
        document.getElementById('eventTypeInput').value = type;
        document.getElementById('btnSaveEvent').style.display = 'block';
        
        if (type === 'goal') {
            document.getElementById('eventModalTitle').innerText = '⚽ Goal Melden';
            document.getElementById('goalPlayerSelect').style.display = 'block';
            document.getElementById('wisselMenu').style.display = 'none';
            populateDropdown('goalPlayerId', shift.pitch, true);
            populateDropdown('assistPlayerId', shift.pitch, true);
        } else if (type === 'opp_goal') {
            document.getElementById('eventModalTitle').innerText = '🥅 Tegengoal Melden';
            document.getElementById('goalPlayerSelect').style.display = 'none';
            document.getElementById('wisselMenu').style.display = 'none';
        } else if (type === 'wissel') {
            document.getElementById('eventModalTitle').innerText = '🔄 Wissel Menu';
            document.getElementById('goalPlayerSelect').style.display = 'none';
            document.getElementById('wisselMenu').style.display = 'block';
            populateDropdown('wisselPlayerInId', shift.bench, false);
            populateDropdown('wisselPlayerOutId', shift.pitch, true);
            
            // Als we wissel openen, verberg de algemene opslaan knop om verwarring te voorkomen, tenzij ze manueel kiezen.
            // Eigenlijk laten we hem gewoon staan voor de individuele wissel.
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

    function updateBlockLabel() {
        if (!matchStarted) return;
        const shift = getActiveShift();
        const lbl = document.getElementById('currentBlockLabel');
        if (lbl) {
            lbl.innerText = shift.title || `Blok ${shift.index} / ${shiftsData.length}`;
        }
    }

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
            if (assistPid) { payload.assist_player_id = assistPid; }
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
        let payload = {
            action: 'log_event',
            game_id: gameId,
            parent_email: email,
            event_type: eventType,
            event_minute: eventMinute
        };
        if (playerId) payload.player_id = playerId;
        if (playerOutId) payload.player_out_id = playerOutId;
        sendApiEventObject(payload);
    }

    function sendApiEventObject(payload) {
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
            } else {
                alert("Fout bij opslaan: " + (data.message || ''));
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
        const feed = document.getElementById('liveEventsFeed');
        feed.innerHTML = '';
        
        let homeScore = 0;
        let awayScore = 0;
        
        // Calculate score
        events.forEach(e => {
            if (e.event_type === 'goal') {
                homeScore++;
            } else if (e.event_type === 'opp_goal' || e.event_type === 'tegengoal') {
                awayScore++;
            }
        });
        
        const scoreDisplay = document.getElementById('liveScoreDisplay');
        if (scoreDisplay) {
            scoreDisplay.innerText = homeScore + ' - ' + awayScore;
        }
        
        // Render all events, reverse them to show newest on top
        const visibleEvents = events.filter(e => e.event_type !== 'match_start' && e.event_type !== 'period_start').reverse();
        
        visibleEvents.forEach(e => {
            const el = document.createElement('div');
            el.className = 'py-2 border-bottom d-flex justify-content-between align-items-center w-100 text-dark';
            el.style.fontSize = '0.9rem';
            
            let text = e.event_minute + "' ";
            if (e.event_type === 'goal') {
                text += '⚽ ' + (e.p1_first || 'Onbekend');
                if (e.p2_first) {
                    text += ' (' + e.p2_first + ')';
                }
            } else if (e.event_type === 'opp_goal' || e.event_type === 'tegengoal') {
                text += '🥅 Tegendoelpunt';
            } else if (e.event_type === 'substitution') {
                text += '🔄 Wissel: ' + (e.p1_first || '?') + ' IN, ' + (e.p2_first || '?') + ' UIT';
            } else if (e.event_type === 'match_end') {
                text += '🛑 Einde Wedstrijd';
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

    if (matchStarted) {
        fetchLiveEvents();
        setInterval(fetchLiveEvents, 5000); // Check every 5 seconds
    }
</script>
