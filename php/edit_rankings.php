<?php
require_once("game.php");

$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    require_once __DIR__ . '/header.php';
    echo '<div class="container mt-5">';
    echo '<div class="alert alert-info shadow-sm text-center py-5">';
    echo '  <i class="fa-solid fa-face-smile-wink fa-3x text-primary mb-3"></i>';
    echo '  <h3>Fun Formats hebben geen Matrix nodig!</h3>';
    echo '  <p class="mb-0">Bij 2v2 en 3v3 draait het volledig om plezier. De exacte opstelling of matrix scores maken hier niets uit en the generator verdeelt de speeltijd gewoon eerlijk.</p>';
    echo '  <a href="/" class="btn btn-primary mt-4"><i class="fa-solid fa-arrow-left me-2"></i>Terug naar dashboard</a>';
    echo '</div>';
    echo '</div>';
    require_once __DIR__ . '/footer.php';
    exit;
}

require_once __DIR__ . '/core/getconn.php';
$page_title = 'Team & Positie Rankings';

// Haal alle spelers op die GEEN vaste doelman zijn, specifiek voor deze ploeg
$stmtPlayers = $pdo->prepare("SELECT id, first_name, last_name, is_doelman FROM players WHERE team_id = ? ORDER BY first_name ASC, last_name ASC");
$stmtPlayers->execute([$_SESSION['team_id']]);
$players = [];
$team_ranking_valid_players = []; // Voor initiële weergave in tabblad 1
while ($p = $stmtPlayers->fetch(PDO::FETCH_ASSOC)) {
    $players[$p['id']] = $p;
    // Database structuur zorgt soms fouten, fallback op strikte controle
    if ($p['is_doelman'] == 0 || $p['is_doelman'] === null) {
        $team_ranking_valid_players[] = $p['id'];
    }
}

// 1. Team Ranking inladen
$stmtTeamRank = $pdo->prepare("SELECT ptr.player_id FROM player_team_ranking ptr JOIN players p ON ptr.player_id = p.id WHERE p.team_id = ? ORDER BY ptr.team_rank ASC");
$stmtTeamRank->execute([$_SESSION['team_id']]);
$team_ranking = [];
while ($r = $stmtTeamRank->fetch(PDO::FETCH_ASSOC)) {
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
$stmtPos = $pdo->prepare("SELECT DISTINCT ps.position FROM player_scores ps JOIN players p ON ps.player_id = p.id WHERE p.team_id = ? ORDER BY ps.position ASC");
$stmtPos->execute([$_SESSION['team_id']]);
$positions = [];
while ($pos = $stmtPos->fetch(PDO::FETCH_ASSOC)) {
    if ($pos['position'] > 0) $positions[] = $pos['position'];
}
if(empty($positions)) $positions = range(1, 11);

// 3. Positie rankings inladen
$pos_ranks = [];
$stmtPR = $pdo->prepare("SELECT pr.position_id, pr.player_id FROM position_rankings pr JOIN players p ON pr.player_id = p.id WHERE p.team_id = ? ORDER BY pr.position_id ASC, pr.pos_rank ASC");
$stmtPR->execute([$_SESSION['team_id']]);
while ($pr = $stmtPR->fetch(PDO::FETCH_ASSOC)) {
    $pos_ranks[$pr['position_id']][] = $pr['player_id'];
}
// 4. Goalie Scores inladen
$gk_scores = [];
$stmtGK = $pdo->prepare("SELECT gks.player_id, gks.score FROM gk_scores gks JOIN players p ON gks.player_id = p.id WHERE p.team_id = ?");
$stmtGK->execute([$_SESSION['team_id']]);
while ($g = $stmtGK->fetch(PDO::FETCH_ASSOC)) {
    $gk_scores[$g['player_id']] = $g['score'];
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
    <?php include __DIR__ . '/header.php'; ?>

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
                <button class="nav-link active fw-bold" id="team-tab" data-bs-toggle="pill" data-bs-target="#team-rank" type="button" role="tab"><i class="fa-solid fa-users me-2"></i>Veldspelers Rank</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="pos-tab" data-bs-toggle="pill" data-bs-target="#pos-rank" type="button" role="tab"><i class="fa-solid fa-map-location-dot me-2"></i>Positie Ranks</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="gk-tab" data-bs-toggle="pill" data-bs-target="#gk-rank" type="button" role="tab"><i class="fa-solid fa-hands-bubbles me-2"></i>Doelmannen Matrix</button>
            </li>
        </ul>

        <div class="tab-content border-top pt-4" id="rankingTabsContent">
            
            <!-- TAB 1: TEAM RANKING -->
            <div class="tab-pane fade show active" id="team-rank" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="alert alert-info py-2"><i class="fa-solid fa-circle-info me-2"></i>Sleep de spelers en zet ze op volgorde van absolute sterspeler naar minst sterk.</div>
                        <?php
                        $team_ranking = array_intersect($team_ranking, $team_ranking_valid_players);
                        $missing = array_diff($team_ranking_valid_players, $team_ranking);
                        $team_ranking = array_merge($team_ranking, $missing);
                        ?>
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
                            <?php foreach ($positions as $pos): 
                                if ($pos == 1) continue; // Skip keeper pos in general tab
                            ?>
                                <option value="<?= $pos ?>">Positie <?= $pos ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-danger"><i class="fa-solid fa-ban me-2"></i>Speelt hier NOOIT (Score=0)</h5>
                        <p class="text-muted small">Deze spelers mogen hier nooit spelen.</p>
                        <ul id="unassignedList" class="list-group sortable-list"></ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-success"><i class="fa-solid fa-check-double me-2"></i>Rank op deze positie</h5>
                        <p class="text-muted small">Sleep spelers hierin (Bovenaan = absolute specialisten).</p>
                        <ul id="assignedList" class="list-group sortable-list bg-white border-primary"></ul>
                    </div>
                </div>
            </div>
            
            <!-- TAB 3: GOALKEEPER OVERRIDES -->
            <div class="tab-pane fade" id="gk-rank" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary"><i class="fa-solid fa-user-shield me-2"></i>Vaste Doelmannen</h5>
                        <p class="text-muted small">Regel de exacte score voor Positie 1.</p>
                        
                        <?php 
                        $hasGoalie = false;
                        foreach ($players as $pid => $p): 
                            if ($p['is_doelman'] == 1):
                                $hasGoalie = true;
                                $score = isset($gk_scores[$pid]) ? $gk_scores[$pid] : 95;
                        ?>
                            <div class="card p-3 mb-3 border-0 shadow-sm">
                                <label class="fw-bold fs-5 mb-2"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> <span class="badge bg-primary float-end" id="gk_score_val_<?= $pid ?>"><?= $score ?>/100</span></label>
                                <input type="range" class="form-range gk-slider" data-id="<?= $pid ?>" min="50" max="100" step="1" value="<?= $score ?>">
                            </div>
                        <?php endif; endforeach; 
                        
                        if (!$hasGoalie): ?>
                            <div class="alert alert-warning border-warning">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Geen Vaste Doelman gevonden!</strong><br>
                                Je hebt het vinkje 'Vaste Doelman' nog bij niemand aangezet. Ga naar <a href="/players" class="fw-bold text-dark">Spelers Bewerken</a> om je doelman(nen) aan te duiden!
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="text-warning"><i class="fa-solid fa-hands-bubbles me-2"></i>Extra Handschoenen (Veldspelers)</h5>
                        <p class="text-muted small">Duid veldspelers aan als backup doelman.</p>
                        
                        <div id="rsrv-list">
                            <?php foreach ($players as $pid => $p): 
                                if ($p['is_doelman'] == 0 && isset($gk_scores[$pid]) && $gk_scores[$pid] > 0):
                                    $score = $gk_scores[$pid];
                            ?>
                                <div class="card p-3 mb-3 border-0 shadow-sm rsrv-card" id="rsrv_card_<?= $pid ?>">
                                    <label class="fw-bold mb-2"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> 
                                        <button class="btn btn-sm btn-link text-danger float-end p-0 remove-rsrv" data-id="<?= $pid ?>"><i class="fa-solid fa-trash"></i></button>
                                        <span class="badge bg-warning text-dark float-end me-3" id="gk_score_val_<?= $pid ?>"><?= $score ?>/100</span>
                                    </label>
                                    <input type="range" class="form-range gk-slider" data-id="<?= $pid ?>" min="0" max="90" step="1" value="<?= $score ?>">
                                </div>
                            <?php endif; endforeach; ?>
                        </div>

                        <hr>
                        <div class="d-flex gap-2">
                            <select id="rsrv_add_select" class="form-select border-warning">
                                <option value="">+ Voeg backup keeper toe...</option>
                                <?php foreach ($players as $pid => $p): 
                                    if ($p['is_doelman'] == 0 && empty($gk_scores[$pid])):
                                ?>
                                    <option value="<?= $pid ?>" data-name="<?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                            <button class="btn btn-warning" id="btnAddRsrv"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <?php include __DIR__ . '/footer.php'; ?>
    
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
                if (p.is_doelman == 1) return; // Doelmannen worden in hun EIGEN tab behandeld!
                
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
        // LOGIC: GOALKEEPERS & RESERVES (TAB 3)
        // ----------------------------------------------------
        document.body.addEventListener('input', function(e) {
            if(e.target.classList.contains('gk-slider')) {
                const pid = e.target.getAttribute('data-id');
                const val = e.target.value;
                document.getElementById('gk_score_val_' + pid).innerText = val + '/100';
            }
        });
        
        document.body.addEventListener('change', function(e) {
            if(e.target.classList.contains('gk-slider')) {
                const pid = e.target.getAttribute('data-id');
                const val = e.target.value;
                postData('api_save_gk_scores.php', { player_id: pid, score: val });
            }
        });

        document.body.addEventListener('click', function(e) {
            if(e.target.closest('.remove-rsrv')) {
                const btn = e.target.closest('.remove-rsrv');
                const pid = btn.getAttribute('data-id');
                postData('api_save_gk_scores.php', { player_id: pid, score: 0 });
                document.getElementById('rsrv_card_' + pid).remove();
                // Add back to select
                const select = document.getElementById('rsrv_add_select');
                const player = playersData[pid];
                const opt = document.createElement('option');
                opt.value = pid;
                opt.innerText = player.first_name + ' ' + player.last_name;
                select.appendChild(opt);
            }
        });

        document.getElementById('btnAddRsrv').addEventListener('click', function() {
            const select = document.getElementById('rsrv_add_select');
            const pid = select.value;
            if(!pid) return;
            const textName = select.options[select.selectedIndex].text;
            
            // Build card
            const container = document.getElementById('rsrv-list');
            const card = document.createElement('div');
            card.className = "card p-3 mb-3 border-0 shadow-sm rsrv-card";
            card.id = "rsrv_card_" + pid;
            card.innerHTML = `
                <label class="fw-bold mb-2">${textName} 
                    <button class="btn btn-sm btn-link text-danger float-end p-0 remove-rsrv" data-id="${pid}"><i class="fa-solid fa-trash"></i></button>
                    <span class="badge bg-warning text-dark float-end me-3" id="gk_score_val_${pid}">50/100</span>
                </label>
                <input type="range" class="form-range gk-slider" data-id="${pid}" min="0" max="90" step="1" value="50">
            `;
            container.appendChild(card);
            
            // Remove from select
            select.options[select.selectedIndex].remove();
            
            // Save initial 50
            postData('api_save_gk_scores.php', { player_id: pid, score: 50 });
        });

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
