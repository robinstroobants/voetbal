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
if (isset($lineup) && isset($lineup->game_parts)) {
    foreach ($lineup->game_parts as $g_counter => $g_parts) {
        foreach ($g_parts as $g_idx) {
            $event_to_game[$g_idx] = $g_counter;
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
        
        $shifts_data[] = [
            'index' => $idx + 1,
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
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: #fff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    padding: 10px 15px;
    z-index: 1040;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 2px solid #0071e3;
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
body {
    padding-bottom: 80px;
}
#liveEventsFeed {
    position: fixed;
    bottom: 70px;
    left: 15px;
    right: 15px;
    z-index: 1030;
    display: flex;
    flex-direction: column;
    gap: 6px;
    pointer-events: none;
    align-items: center;
}
.live-event-toast {
    background: rgba(0, 0, 0, 0.75);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div id="liveEventsFeed" class="d-print-none"></div>

<div class="parents-bottom-bar d-print-none">
    <div id="liveClockContainer" class="parents-clock-container">
        <?php if ($matchStarted): ?>
            <div class="parents-block-label" id="currentBlockLabel">Blok <?= $currentShiftIndex + 1 ?> / <?= $totalBlocksCount ?></div>
            <div class="parents-clock" id="liveClockDisplay">00:00</div>
        <?php else: ?>
            <button class="btn btn-sm btn-outline-primary fw-bold" onclick="startMatch()">▶ Start Match</button>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-primary fw-bold shadow-sm" onclick="openEventModal('goal')">⚽ Goal</button>
        <button class="btn btn-sm btn-danger fw-bold shadow-sm" onclick="openEventModal('opp_goal')">🥅 Tegengoal</button>
        <button class="btn btn-sm btn-secondary shadow-sm" onclick="openEventModal('wissel')">🔄 Wissel</button>
    </div>
</div>

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
            <select class="form-select mb-2" id="goalPlayerId">
                <option value="">Selecteer een speler...</option>
                <!-- JS vult dit -->
            </select>
        </div>
        
        <!-- Wissel Menu -->
        <div id="wisselMenu" class="mb-3" style="display:none;">
            
            <?php if ($currentShiftIndex < $totalBlocksCount - 1): ?>
            <div class="card mb-3 border-success">
                <div class="card-body p-3 text-center">
                    <h6 class="fw-bold mb-1">Volgend Blok Starten</h6>
                    <p class="small text-muted mb-2">Bevestig dat het geplande wisselmoment (Blok <?= $currentShiftIndex + 2 ?>) is ingegaan. De timer springt dan automatisch naar de start van het nieuwe blok.</p>
                    <button class="btn btn-success btn-sm fw-bold w-100" onclick="submitNextBlock()">▶ Start Blok <?= $currentShiftIndex + 2 ?></button>
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
        const now = new Date().getTime();
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
            clockInterval = setInterval(() => {
                display.innerText = formatClock();
            }, 1000);
        }
    }

    function populateDropdown(selectId, playersData, isPitch) {
        const sel = document.getElementById(selectId);
        sel.innerHTML = '<option value="">Selecteer speler...</option>';
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

        sendApiEvent(payload.event_type, payload.event_minute, payload.player_id, payload.player_out_id);
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
        
        // Filter out non-display events and take the last 3
        const visibleEvents = events.filter(e => e.event_type !== 'match_start' && e.event_type !== 'period_start').slice(-3);
        
        visibleEvents.forEach(e => {
            const el = document.createElement('div');
            el.className = 'live-event-toast shadow-sm';
            
            let text = e.event_minute + "' - ";
            if (e.event_type === 'goal') {
                text += '⚽ Goal door ' + (e.p1_first || 'Onbekend');
            } else if (e.event_type === 'opp_goal') {
                text += '🥅 Tegendoelpunt';
            } else if (e.event_type === 'assist') {
                text += '👟 Assist door ' + (e.p1_first || 'Onbekend');
            } else if (e.event_type === 'substitution') {
                text += '🔄 Wissel: ' + (e.p1_first || '?') + ' IN, ' + (e.p2_first || '?') + ' UIT';
            } else {
                text += e.event_type;
            }
            
            if (e.is_confirmed == 1) {
                 text += ' ✅';
            }
            el.innerText = text;
            feed.appendChild(el);
        });
    }

    if (matchStarted) {
        fetchLiveEvents();
        setInterval(fetchLiveEvents, 5000); // Check every 5 seconds
    }
</script>
