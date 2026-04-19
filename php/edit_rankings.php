<?php
require_once 'getconn.php';
$page_title = 'Team & Positie Rankings';

// Haal alle spelers op die GEEN vaste doelman zijn
$players_result = $conn->query("SELECT id, first_name, last_name, is_doelman FROM players WHERE is_doelman = 0 OR is_doelman IS NULL");
$players = [];
while ($p = $players_result->fetch_assoc()) {
    $players[$p['id']] = $p;
}

// 1. Team Ranking inladen
$team_rank_result = $conn->query("SELECT player_id FROM player_team_ranking ORDER BY team_rank ASC");
$team_ranking = [];
while ($r = $team_rank_result->fetch_assoc()) {
    if(isset($players[$r['player_id']])) {
        $team_ranking[] = $r['player_id'];
    }
}
// Voeg overblijvende (nieuwe) spelers toe onderaan de ranking
foreach ($players as $id => $p) {
    if (!in_array($id, $team_ranking)) {
        $team_ranking[] = $id;
    }
}

// 2. Posities bepalen op basis van player_scores database (indien die bestaat)
$pos_result = $conn->query("SELECT DISTINCT position FROM player_scores ORDER BY position ASC");
$positions = [];
while ($pos = $pos_result->fetch_assoc()) {
    if ($pos['position'] > 0) $positions[] = $pos['position'];
}
if(empty($positions)) $positions = range(1, 11);

// 3. Positie rankings inladen
$pos_ranks = [];
$pos_ranks_result = $conn->query("SELECT position_id, player_id FROM position_rankings ORDER BY position_id ASC, pos_rank ASC");
while ($pr = $pos_ranks_result->fetch_assoc()) {
    $pos_ranks[$pr['position_id']][] = $pr['player_id'];
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Lineup App</title>
    <!-- Bootstrap and Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sortable-list { min-height: 200px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6; }
        .list-group-item { cursor: grab; margin-bottom: 5px; border-radius: 6px !important; border: 1px solid #e9ecef; }
        .list-group-item:active { cursor: grabbing; }
        .drag-handle { color: #adb5bd; cursor: grab; }
        .ghost-class { opacity: 0.4; background-color: #e2e3e5; }
        .ranked-number { display: inline-block; width: 25px; height: 25px; line-height: 25px; text-align: center; border-radius: 50%; background: #0d6efd; color: white; font-size: 0.8em; margin-right: 10px; font-weight: bold;}
    </style>
</head>
<body class="bg-light pb-5">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h2><i class="fa-solid fa-ranking-star text-warning me-2"></i> Rank & Drop Dashboard</h2>
            <div class="d-flex align-items-center gap-3">
                <div id="saveAlert" class="badge bg-success" style="opacity: 0; transition: opacity 0.5s;">
                    <i class="fa-solid fa-check me-1"></i> Autosaved!
                </div>
                <button id="btnGenerateMatrix" class="btn btn-dark fw-bold px-4 shadow-sm">
                    <i class="fa-solid fa-calculator text-warning me-2"></i>Genereer Scores Matrix
                </button>
            </div>
        </div>
        
        <!-- Tabs Navigatie -->
        <ul class="nav nav-pills mb-4 gap-2" id="rankingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="team-tab" data-bs-toggle="pill" data-bs-target="#team-rank" type="button" role="tab"><i class="fa-solid fa-users me-2"></i>Algemene Team Rank</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="pos-tab" data-bs-toggle="pill" data-bs-target="#pos-rank" type="button" role="tab"><i class="fa-solid fa-map-location-dot me-2"></i>Positie Ranks (Drag)</button>
            </li>
        </ul>

        <div class="tab-content border-top pt-4" id="rankingTabsContent">
            
            <!-- TAB 1: TEAM RANKING -->
            <div class="tab-pane fade show active" id="team-rank" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="alert alert-info py-2"><i class="fa-solid fa-circle-info me-2"></i>Sleep de spelers en zet ze op volgorde van absolute sterspeler naar minst sterk.</div>
                        <ul id="teamRankingList" class="list-group sortable-list">
                            <?php foreach ($team_ranking as $index => $pid): 
                                $p = $players[$pid]; 
                                $name = $p['first_name'] . ' ' . $p['last_name'];
                            ?>
                            <li class="list-group-item d-flex align-items-center fw-bold" data-id="<?= $pid ?>">
                                <i class="fa-solid fa-grip-vertical drag-handle me-3"></i>
                                <span class="ranked-number team-num"><?= $index + 1 ?></span>
                                <?= htmlspecialchars($name) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB 2: POSITION RANKING -->
            <div class="tab-pane fade" id="pos-rank" role="tabpanel">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Kies de veldpositie:</label>
                        <select id="positionSelect" class="form-select form-select-lg border-primary">
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos ?>">Positie <?= $pos ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-danger"><i class="fa-solid fa-ban me-2"></i>Speelt hier NOOIT (Score=0)</h5>
                        <p class="text-muted small">Deze spelers mogen hier nooit spelen.</p>
                        <ul id="unassignedList" class="list-group sortable-list">
                            <!-- Populated via JS -->
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-success"><i class="fa-solid fa-check-double me-2"></i>Rank op deze positie</h5>
                        <p class="text-muted small">Sleep spelers hierin (Bovenaan = absolute specialisten).</p>
                        <ul id="assignedList" class="list-group sortable-list bg-white border-primary">
                            <!-- Populated via JS -->
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <!-- Data injectie voor Javascript -->
    <script>
        const playersData = <?= json_encode($players) ?>;
        // Pos ranks matrix: pos_id => array of player_ids
        let positionRanksData = <?= json_encode((object)$pos_ranks) ?>;
        // Zorg dat elke positie minimaal gekend is in JS
        const allPositions = <?= json_encode($positions) ?>;
        allPositions.forEach(pos => {
            if(!positionRanksData[pos]) positionRanksData[pos] = [];
        });

        // ----------------------------------------------------
        // LOGIC: TEAM RANKING (TAB 1)
        // ----------------------------------------------------
        const teamListEl = document.getElementById('teamRankingList');
        Sortable.create(teamListEl, {
            animation: 150,
            ghostClass: 'ghost-class',
            onEnd: function (evt) {
                updateTeamNumbers();
                saveTeamRanking();
            }
        });

        function updateTeamNumbers() {
            let items = teamListEl.querySelectorAll('.team-num');
            items.forEach((span, index) => { span.innerText = index + 1; });
        }

        function saveTeamRanking() {
            let order = [];
            teamListEl.querySelectorAll('li').forEach(li => {
                order.push(li.getAttribute('data-id'));
            });
            
            postData('api_save_rankings.php', { action: 'team', order: order });
        }

        // ----------------------------------------------------
        // LOGIC: POSITION RANKING (TAB 2)
        // ----------------------------------------------------
        const selectedPosEl = document.getElementById('positionSelect');
        const unassignedListEl = document.getElementById('unassignedList');
        const assignedListEl = document.getElementById('assignedList');

        // Maak beide lijsten connecteerbaar (sleep van link naar rechts)
        Sortable.create(unassignedListEl, {
            group: 'shared', // set both lists to same group
            animation: 150,
            ghostClass: 'ghost-class',
            onEnd: saveCurrentPositionRanking
        });

        Sortable.create(assignedListEl, {
            group: 'shared',
            animation: 150,
            ghostClass: 'ghost-class',
            onEnd: saveCurrentPositionRanking,
            onSort: function(evt) {
                // Herbereken de bolletjes nummertjes rechts na droppen
                let items = assignedListEl.querySelectorAll('.pos-num');
                items.forEach((span, index) => { span.innerText = index + 1; });
            }
        });

        // Render UI functie
        function renderPositionUI() {
            const pos = selectedPosEl.value;
            const assignedIds = positionRanksData[pos] || [];
            
            unassignedListEl.innerHTML = '';
            assignedListEl.innerHTML = '';

            // Bepaal welke speler in welke bak zit
            Object.values(playersData).forEach(p => {
                const name = p.first_name + ' ' + p.last_name;
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center fw-medium';
                li.setAttribute('data-id', p.id);
                
                const idx = assignedIds.indexOf(p.id);
                if (idx !== -1) {
                    // Zit in Assigned! Push hem op juiste volgorde (doen we hieronder post-render)
                } else {
                    li.innerHTML = `<i class="fa-solid fa-grip-vertical drag-handle me-3"></i> ${name}`;
                    li.classList.add('text-muted');
                    unassignedListEl.appendChild(li);
                }
            });

            // Bouw de Assignd lijst op EXACTE volgorde
            assignedIds.forEach((pid, index) => {
                const p = playersData[pid];
                if(p) {
                    const name = p.first_name + ' ' + p.last_name;
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex align-items-center fw-bold border-primary';
                    li.setAttribute('data-id', p.id);
                    li.innerHTML = `<i class="fa-solid fa-grip-vertical drag-handle me-3"></i><span class="ranked-number pos-num bg-success">${index + 1}</span> ${name}`;
                    assignedListEl.appendChild(li);
                }
            });
        }

        function saveCurrentPositionRanking() {
            const pos = selectedPosEl.value;
            let order = [];
            assignedListEl.querySelectorAll('li').forEach((li, index) => {
                order.push(li.getAttribute('data-id'));
                // live update visual numbers
                let numSpan = li.querySelector('.pos-num');
                if(!numSpan) {
                    numSpan = document.createElement('span');
                    numSpan.className = "ranked-number pos-num bg-success";
                    li.insertBefore(numSpan, li.childNodes[1] || li.firstChild);
                    li.className = 'list-group-item d-flex align-items-center fw-bold border-primary';
                    li.classList.remove('text-muted');
                }
                numSpan.innerText = index + 1;
            });
            
            // Clean up unassigned visual styles
            unassignedListEl.querySelectorAll('li').forEach(li => {
                let numSpan = li.querySelector('.pos-num');
                if(numSpan) numSpan.remove();
                li.className = 'list-group-item d-flex align-items-center fw-medium text-muted';
            });

            // Opslaan in Javascript State
            positionRanksData[pos] = order;
            
            // Zend naar PHP API
            postData('api_save_rankings.php', { action: 'position', position_id: pos, order: order });
        }

        selectedPosEl.addEventListener('change', renderPositionUI);
        
        // Initiele draw
        renderPositionUI();

        // ----------------------------------------------------
        // HELPER FUNCTIE: AJAX Opslaan
        // ----------------------------------------------------
        function postData(url, data) {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(response => {
                if(response.success) {
                    const alert = document.getElementById('saveAlert');
                    alert.style.opacity = 1;
                    setTimeout(() => alert.style.opacity = 0, 1500);
                }
            });
        }

        // ----------------------------------------------------
        // LOGIC: GENERATE MATRIX (API)
        // ----------------------------------------------------
        document.getElementById('btnGenerateMatrix').addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Genereren...';
            btn.disabled = true;

            fetch('api_generate_scores.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert("Matrix succesvol en wiskundig accuraat gegenereerd!");
                    window.location.href = 'edit_scores.php';
                } else {
                    alert("Er ging iets mis: " + data.error);
                    btn.innerHTML = '<i class="fa-solid fa-calculator text-warning me-2"></i>Genereer Scores Matrix';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert("Fout bij genereren.");
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
