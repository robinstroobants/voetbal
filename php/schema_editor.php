<?php
require_once 'getconn.php';
require_once 'MatchManager.php';

$gameId = $_GET['game_id'] ?? 0;
$schemaId = $_GET['schema_id'] ?? 0;
$volgorde = $_GET['volgorde'] ?? '';

if (!$gameId || !$schemaId || empty($volgorde)) {
    die("Minimale setup data ontbreekt (game_id, schema_id, volgorde vereist).");
}

$stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$gameId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) die("Match not found");

$format = $game['format'];
$list_of_players = explode(',', $volgorde);
$aantal = count($list_of_players);

$stmtGk = $pdo->prepare("SELECT SUM(is_goalkeeper) FROM game_selections WHERE game_id = ?");
$stmtGk->execute([$gameId]);
$gk_count = (int)$stmtGk->fetchColumn();

// Find filename
$search_format = $format;
if (strpos($format, 'gk') === false) {
    if (preg_match('/^(\d+v\d+)_(\d+x\d+)$/', $format, $matches)) {
        $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
    }
}
$wissel_file = __DIR__ . "/wisselschemas/" . $search_format . "_" . $aantal . "sp.php";

if (!file_exists($wissel_file)) {
    die("Kan schemabestand niet vinden: " . basename($wissel_file));
}

include $wissel_file;
if (!isset($ws[$schemaId])) {
    die("Schema ID $schemaId niet gevonden in bestand.");
}

$schema = $ws[$schemaId];

// Helper for playernames
$stmtPlayers = $pdo->query("SELECT id, first_name, last_name FROM players");
$playersMap = [];
while($row = $stmtPlayers->fetch(PDO::FETCH_ASSOC)) {
    $playersMap[$row['id']] = $row['first_name'];
}

function getPlayerName($pid, $volgorde_arr, $gk_count, $schema_idx) {
    global $playersMap;
    // Goalies have fixed IDs often or are indices < gk_count
    if ($schema_idx < $gk_count) {
        $real_id = $volgorde_arr[$schema_idx] ?? 0;
        return ($playersMap[$real_id] ?? 'Doelman') . " (GK)";
    }
    
    $real_id = $volgorde_arr[$schema_idx] ?? 0;
    return $playersMap[$real_id] ?? "Speler $schema_idx";
}

$page_title = "Bewerk Wisselschema";
require_once 'header.php';
?>

<style>
.editor-block {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    padding: 15px;
    border-left: 5px solid #0d6efd;
}
.pos-wrapper {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 5px;
    min-height: 60px;
    margin-bottom: 15px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pos-wrapper[data-pos="bench"] {
    border-color: #ffc107;
    background: #fff8e1;
}
.pos-badge {
    position: absolute;
    top: -10px;
    left: 10px;
    background: #6c757d;
    color: white;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: bold;
}
.player-item {
    background: #0d6efd;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: grab;
    user-select: none;
    font-weight: 500;
    width: 100%;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.1s;
}
.player-item:active {
    cursor: grabbing;
    transform: scale(0.95);
}
.player-item.is-gk {
    background: #dc3545;
    pointer-events: none; /* Block dragging goalies for now */
    opacity: 0.8;
}
.drag-over {
    background: #e9ecef;
    border-color: #0d6efd;
}
</style>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fa-solid fa-pen-ruler text-primary me-2"></i>Schema Editor</h2>
            <p class="text-muted mb-0">Base Schema: <strong><?= $schemaId ?></strong> &middot; Veld: <strong><?= $search_format ?></strong></p>
        </div>
        <div>
            <a href="lineup.php?wedstrijd=<?= $gameId ?>" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left me-1"></i> Terug</a>
            <button class="btn btn-success" onclick="saveSchema()"><i class="fa-solid fa-floppy-disk me-1"></i> Opslaan Als Nieuw</button>
        </div>
    </div>
    <div class="alert alert-info border-info">
        <i class="fa-solid fa-lightbulb me-2"></i> <strong>Hoe werkt het?</strong> Sleep een speler vanuit het speelveld of de bank op een andere speler om hun posities binnen dat speelblok om te wisselen.
    </div>

    <?php
    $all_positions = [];
    foreach($schema as $shift) {
        if(isset($shift['lineup'])) {
            foreach(array_keys($shift['lineup']) as $p) {
                if(!in_array($p, $all_positions)) $all_positions[] = $p;
            }
        }
    }
    sort($all_positions);
    ?>

    <div id="schema-blocks">
        <?php 
        $grouped_shifts = [];
        foreach($schema as $shift_idx => $block) {
            if (!is_numeric($shift_idx)) continue;
            $gc = $block['game_counter'] ?? 1;
            $grouped_shifts[$gc][$shift_idx] = $block;
        }
        
        foreach($grouped_shifts as $gc => $shifts): 
        ?>
        <div class="game-container border rounded p-3 mb-4 bg-white shadow-sm" data-game-counter="<?= $gc ?>">
            <h4 class="mb-3 text-dark"><i class="fa-solid fa-stopwatch me-2"></i>Wedstrijd <?= $gc ?></h4>
            
            <?php foreach($shifts as $shift_idx => $block): ?>
            <div class="editor-block mb-3 p-3 bg-light border rounded" data-shift="<?= $shift_idx ?>" data-duration="<?= $block['duration'] ?>">
                <h5 class="mb-3 text-primary border-bottom pb-2">Blokje <?= $shift_idx ?> <small class="text-muted">(<?= $block['duration']/60 ?> min)</small></h5>
                <div class="row">
                    <div class="col-md-9">
                        <h6 class="text-muted mb-3"><i class="fa-solid fa-people-group me-1"></i>Op Het Veld</h6>
                        <div class="row">
                            <?php foreach($block['lineup'] as $pos => $s_idx): ?>
                            <div class="col-4 col-sm-3 col-md-2">
                                <div class="pos-wrapper bg-white" data-pos="<?= $pos ?>">
                                    <span class="pos-badge">P <?= $pos ?></span>
                                    <div class="player-item <?= $s_idx < $gk_count ? 'is-gk' : '' ?>" draggable="<?= $s_idx < $gk_count ? 'false' : 'true' ?>" data-sidx="<?= $s_idx ?>" data-name="<?= htmlspecialchars(getPlayerName(0, $list_of_players, $gk_count, $s_idx)) ?>">
                                        <?= htmlspecialchars(getPlayerName(0, $list_of_players, $gk_count, $s_idx)) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-3 bg-white rounded p-3 shadow-sm border">
                        <h6 class="text-muted mb-3"><i class="fa-solid fa-chair me-1"></i>Bank</h6>
                        <div class="row">
                            <?php if(!empty($block['bench'])): foreach($block['bench'] as $b_pos => $s_idx): ?>
                            <div class="col-6 col-md-12">
                                <div class="pos-wrapper" data-pos="bench">
                                    <span class="pos-badge bg-warning text-dark"><i class="fa-solid fa-bed"></i> Bank</span>
                                    <div class="player-item" draggable="true" data-sidx="<?= $s_idx ?>" data-name="<?= htmlspecialchars(getPlayerName(0, $list_of_players, $gk_count, $s_idx)) ?>">
                                        <?= htmlspecialchars(getPlayerName(0, $list_of_players, $gk_count, $s_idx)) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; else: ?>
                                <div class="col-12 text-center text-muted small py-3">Geen spelers op de bank in deze shift</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-5 mb-4 shadow-sm border-0 bg-transparent">
        <h4 class="mb-4 text-dark"><i class="fa-solid fa-chart-pie me-2"></i>Live Speler Statistieken</h4>
        <div class="row" id="stats-cards">
            <!-- Dynamically populated via JS -->
        </div>
    </div>
</div>

<script>
let draggedItem = null;

document.querySelectorAll('.player-item[draggable="true"]').forEach(item => {
    item.addEventListener('dragstart', function(e) {
        draggedItem = this;
        setTimeout(() => this.style.opacity = '0.5', 0);
    });
    
    item.addEventListener('dragend', function() {
        setTimeout(() => {
            this.style.opacity = '1';
            draggedItem = null;
        }, 0);
    });
    
    // Also attach to wrappers to allow drop tracking securely
    let wrapper = item.parentNode;
    wrapper.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    
    wrapper.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });
    
    wrapper.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        if (draggedItem !== null && draggedItem !== this.firstElementChild) {
            let targetItem = this.querySelector('.player-item');
            if (targetItem && targetItem.classList.contains('is-gk')) {
                alert("Je kan niet met de keeper wisselen!");
                return;
            }
            
            let sidxA = draggedItem.getAttribute('data-sidx');
            let nameA = draggedItem.getAttribute('data-name');
            
            let sidxB = targetItem ? targetItem.getAttribute('data-sidx') : null;
            let nameB = targetItem ? targetItem.getAttribute('data-name') : null;
            
            if (!sidxB) return; // Ignore drops on empty space without target item
            
            let sourceWrapper = draggedItem.parentNode;
            let posA = sourceWrapper.getAttribute('data-pos');
            let posB = this.getAttribute('data-pos');
            
            // Find current game container
            let gameContainer = this.closest('.game-container');
            
            if (posA !== 'bench' && posB !== 'bench') {
                // TACTISCHE VELD WISSEL: Wissel de POSITIES doorheen het hele wedstrijd-kwartier
                gameContainer.querySelectorAll('.editor-block').forEach(block => {
                    let wrapperA = block.querySelector('.pos-wrapper[data-pos="' + posA + '"]');
                    let wrapperB = block.querySelector('.pos-wrapper[data-pos="' + posB + '"]');
                    
                    if (wrapperA && wrapperB) {
                        let itemA = wrapperA.querySelector('.player-item');
                        let itemB = wrapperB.querySelector('.player-item');
                        
                        if (itemA && itemB && !itemA.classList.contains('is-gk') && !itemB.classList.contains('is-gk')) {
                            let tempSidx = itemA.getAttribute('data-sidx');
                            let tempName = itemA.getAttribute('data-name');
                            
                            itemA.setAttribute('data-sidx', itemB.getAttribute('data-sidx'));
                            itemA.setAttribute('data-name', itemB.getAttribute('data-name'));
                            itemA.innerText = itemB.getAttribute('data-name');
                            
                            itemB.setAttribute('data-sidx', tempSidx);
                            itemB.setAttribute('data-name', tempName);
                            itemB.innerText = tempName;
                        }
                    }
                });
            } else {
                // BANK <-> VELD WISSEL: Wissel de IDENTITEITEN (de volledige rotaties) doorheen het kwartier
                gameContainer.querySelectorAll('.player-item').forEach(item => {
                    let thisSidx = item.getAttribute('data-sidx');
                    if (thisSidx === sidxA) {
                        item.setAttribute('data-sidx', sidxB);
                        item.setAttribute('data-name', nameB);
                        item.innerText = nameB;
                    } else if (thisSidx === sidxB) {
                        item.setAttribute('data-sidx', sidxA);
                        item.setAttribute('data-name', nameA);
                        item.innerText = nameA;
                    }
                });
            }
            recalculateStats();
        }
    });
});

function recalculateStats() {
    let stats = {};
    
    document.querySelectorAll('.editor-block').forEach(block => {
        let durationSec = parseFloat(block.getAttribute('data-duration')) || 0;
        let durationMin = durationSec / 60;
        
        block.querySelectorAll('.pos-wrapper').forEach(wrapper => {
            let pos = wrapper.getAttribute('data-pos');
            let playerItem = wrapper.querySelector('.player-item');
            if(!playerItem) return;
            
            let sidx = playerItem.getAttribute('data-sidx');
            let name = playerItem.getAttribute('data-name');
            
            if(!stats[sidx]) {
                stats[sidx] = { name: name, playtime: 0, benchtime: 0, map: {} };
            }
            
            if(pos === 'bench') {
                stats[sidx].benchtime += durationMin;
            } else {
                stats[sidx].playtime += durationMin;
                stats[sidx].map[pos] = (stats[sidx].map[pos] || 0) + durationMin;
            }
        });
    });
    
    let container = document.querySelector('#stats-cards');
    container.innerHTML = '';
    
    // Format minutes nicely (e.g. 7.5 => 7:30)
    let formatTime = (min) => {
        if (Number.isInteger(min)) return min;
        let m = Math.floor(min);
        let s = Math.round((min - m) * 60);
        return m + ":" + (s < 10 ? '0' : '') + s;
    };
    
    // Sort array by schema index for logic consistency
    let sortedKeys = Object.keys(stats).sort((a,b) => parseInt(a) - parseInt(b));
    let schemaPositions = <?= json_encode($all_positions) ?>;
    
    sortedKeys.forEach(sidx => {
        let s = stats[sidx];
        let posCount = Object.keys(s.map).length;
        
        let wrapper = document.createElement('div');
        wrapper.className = 'col-md-3 col-sm-6 mb-4';
        
        let textColor = (posCount < 2 && s.playtime > 0) ? 'text-danger' : 'text-primary';
        let bgBadge = (posCount < 2 && s.playtime > 0) ? 'bg-danger' : 'bg-primary';
        
        let listHTML = '';
        
        // Output positie metrics gesorteerd
        schemaPositions.forEach(p => {
            let pt = s.map[p];
            if (pt) {
                listHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm">
                  <div>
                    <h6 class="my-0">${p}</h6>
                  </div>
                  <span class="text-muted">${formatTime(pt)}</span>
                </li>`;
            }
        });
        
        if (s.benchtime > 0) {
            listHTML += `
                <li class="list-group-item d-flex justify-content-between lh-sm bg-light">
                  <div>
                    <h6 class="my-0">bench</h6>
                  </div>
                  <span class="text-muted fw-bold">${formatTime(s.benchtime)}</span>
                </li>`;
        }
        
        wrapper.innerHTML = `
            <div class="h-100 border-0 bg-transparent">
              <h5 class="d-flex justify-content-between align-items-center mb-3 ps-2">
                <span class="${textColor}">${s.name}</span>
                <span class="badge ${bgBadge} rounded-pill shadow-sm" title="Totale Speeltijd">${formatTime(s.playtime)}</span>
              </h5>
              <ul class="list-group mb-3 shadow-sm border-0">
                  ${listHTML}
              </ul>
            </div>
        `;
        container.appendChild(wrapper);
    });
}

// Initial calculation
recalculateStats();

function saveSchema(forceUpdate = false) {
    let payload = [];
    
    document.querySelectorAll('.editor-block').forEach(block => {
        let shiftIdx = block.getAttribute('data-shift');
        let shiftData = { shift: shiftIdx, lineup: {}, bench: [] };
        
        block.querySelectorAll('.pos-wrapper').forEach(wrapper => {
            let pos = wrapper.getAttribute('data-pos');
            let playerDiv = wrapper.querySelector('.player-item');
            if (!playerDiv) return;
            
            let sidx = parseInt(playerDiv.getAttribute('data-sidx'));
            
            if (pos === 'bench') {
                shiftData.bench.push(sidx);
            } else {
                shiftData.lineup[pos] = sidx;
            }
        });
        
        payload.push(shiftData);
    });
    
    let btn = document.querySelector('button[onclick="saveSchema()"]');
    btn.innerHTML = '<i class=\"fa-solid fa-spinner fa-spin\"></i> Bezig...';
    btn.disabled = true;

    fetch('api_save_schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            game_id: <?= $gameId ?>,
            format: '<?= $search_format ?>',
            aantal: <?= $aantal ?>,
            volgorde: '<?= $volgorde ?>',
            original_schema_id: <?= $schemaId ?>,
            blocks: payload,
            force_settings_update: forceUpdate
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.requires_confirm) {
            btn.innerHTML = '<i class=\"fa-solid fa-floppy-disk me-1\"></i> Opslaan Als Nieuw';
            btn.disabled = false;
            
            if (confirm(data.confirm_msg)) {
                saveSchema(true);
            }
            return;
        }
        
        if (data.success) {
            if (data.is_duplicate) {
                alert("Dit schema deelt al perfect dezelfde theorie als een bestaand schema (ID: " + data.new_id + "). We hebben een dubbel bespaard en jou hieraan gekoppeld!");
            } else {
                alert("Schema succesvol opgeslagen! Je wordt doorgestuurd naar de opstelling.");
            }
            window.location.href = 'lineup.php?wedstrijd=<?= $gameId ?>&preview=' + data.lineup_id;
        } else {
            alert('Fout: ' + (data.error || 'Onbekende fout'));
            btn.innerHTML = '<i class=\"fa-solid fa-floppy-disk me-1\"></i> Opslaan Als Nieuw';
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert("Er is een fout opgetreden bij het verbinden.");
        console.error(err);
        btn.disabled = false;
    });
}
</script>

<?php require_once 'footer.php'; ?>
