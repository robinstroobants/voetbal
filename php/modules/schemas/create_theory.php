<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/models/MatchManager.php';

$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2 && isset($_POST['format'])) {
        // Form step 1 was submitted. Store in session or hidden fields
        $format = $_POST['format'];
        $doelmannen = (int)$_POST['doelmannen'];
        $blocks = (int)$_POST['blocks'];
        $block_duration = (int)$_POST['block_duration'];
        $sub_freq = $_POST['sub_freq'];
        $player_count = (int)$_POST['player_count'];
        
        // Build format string e.g. 8v8_1gk_4x15_15min or 8v8_1gk_4x15_7.5min
        $format_string = "{$format}_{$doelmannen}gk_{$blocks}x{$block_duration}";
        if ($sub_freq != $block_duration) {
            $format_string .= "_{$sub_freq}min";
        }

        // Fetch team players to show selection list
        $stmtPlayers = $pdo->prepare("SELECT id, first_name, last_name FROM players WHERE team_id = ? AND deleted_at IS NULL ORDER BY first_name");
        $stmtPlayers->execute([$_SESSION['team_id']]);
        $all_players = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($step === 3 && isset($_POST['format_string'])) {
        // Form step 2 was submitted. Process selection and create dummy game.
        $format_string = $_POST['format_string'];
        $player_count = (int)$_POST['player_count'];
        $selected_players = $_POST['players'] ?? [];
        $selected_gks = $_POST['goalkeepers'] ?? [];
        
        if (count($selected_players) !== $player_count) {
            $error = "Je moet exact $player_count spelers selecteren. Je hebt er " . count($selected_players) . " gekozen.";
            $step = 2; // Return to step 2
            
            // Refetch players
            $stmtPlayers = $pdo->prepare("SELECT id, first_name, last_name FROM players WHERE team_id = ? AND deleted_at IS NULL ORDER BY first_name");
            $stmtPlayers->execute([$_SESSION['team_id']]);
            $all_players = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);
        } else {
            try {
                $pdo->beginTransaction();
                
                // Create Dummy Game
                $stmtIns = $pdo->prepare("INSERT INTO games (team_id, game_date, opponent, format, is_theory) VALUES (?, NOW(), '[THEORIE TEMPLATE]', ?, 1)");
                $stmtIns->execute([$_SESSION['team_id'], $format_string]);
                $dummy_game_id = $pdo->lastInsertId();
                
                // Save Selection
                $mm = new MatchManager($pdo);
                $mm->saveSelection($dummy_game_id, $selected_players, 2, $selected_gks);
                
                $pdo->commit();
                
                header("Location: /games/$dummy_game_id/builder");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Fout bij opslaan: " . $e->getMessage();
                $step = 1;
            }
        }
    }
}

$page_title = 'Schema Wizard';
require_once dirname(__DIR__, 2) . '/header.php';
?>

<div class="container mt-4 mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fa-solid fa-flask text-primary me-2"></i>Schema Wizard</h2>
            <p class="text-muted">Ontwerp een opstelling from scratch zonder wedstrijd.</p>
        </div>
        <div>
            <a href="/games" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left"></i> Annuleren</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            
            <?php if ($step === 1): ?>
                <h4 class="mb-4">Stap 1: Parameters Instellen</h4>
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Wedstrijdvorm</label>
                            <select name="format" class="form-select" required>
                                <option value="5v5">5 v 5</option>
                                <option value="8v8" selected>8 v 8</option>
                                <option value="11v11">11 v 11</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Aantal Doelmannen in de ploeg</label>
                            <select name="doelmannen" class="form-select" required>
                                <option value="0">Geen vaste doelman (wisselen)</option>
                                <option value="1" selected>1 Vaste doelman</option>
                                <option value="2">2 Vaste doelmannen</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Aantal Veldspelers Totaal (incl GKs)</label>
                            <input type="number" name="player_count" class="form-control" value="10" min="5" max="25" required>
                        </div>
                        
                        <div class="col-md-4 mt-4">
                            <label class="form-label">Aantal Wedstrijdblokken</label>
                            <select name="blocks" class="form-select" required>
                                <option value="1">1 (doorlopend)</option>
                                <option value="2">2 (helften)</option>
                                <option value="3">3</option>
                                <option value="4" selected>4 (kwartjes)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-4">
                            <label class="form-label">Minuten per Blok</label>
                            <input type="number" name="block_duration" class="form-control" value="15" required>
                        </div>
                        <div class="col-md-4 mt-4">
                            <label class="form-label">Wissel Frequentie (Minuten)</label>
                            <select name="sub_freq" class="form-select" required>
                                <option value="5">Om de 5 minuten</option>
                                <option value="7.5" selected>Om de 7.5 minuten (mid-kwartjes)</option>
                                <option value="10">Om de 10 minuten</option>
                                <option value="15" >Om de 15 minuten</option>
                                <option value="20">Om de 20 minuten</option>
                                <option value="30">Om de 30 minuten</option>
                                <option value="45">Om de 45 minuten</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary fw-bold px-4">Volgende <i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </form>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const blockDurationInput = document.querySelector('input[name="block_duration"]');
                    const subFreqSelect = document.querySelector('select[name="sub_freq"]');
                    
                    if (blockDurationInput && subFreqSelect) {
                        blockDurationInput.addEventListener('input', function() {
                            const blockVal = parseFloat(this.value);
                            if (!isNaN(blockVal)) {
                                const halfVal = blockVal / 2;
                                let optionExists = false;
                                for (let i = 0; i < subFreqSelect.options.length; i++) {
                                    if (parseFloat(subFreqSelect.options[i].value) === halfVal) {
                                        optionExists = true;
                                        break;
                                    }
                                }
                                if (optionExists) {
                                    subFreqSelect.value = halfVal;
                                }
                            }
                        });
                    }
                });
                </script>
            
            <?php elseif ($step === 2): ?>
                <h4 class="mb-4">Stap 2: Spelers Selecteren</h4>
                <p class="text-muted">Kies exact <strong><?= $player_count ?></strong> spelers voor dit schema template.</p>
                
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="format_string" value="<?= htmlspecialchars($format_string) ?>">
                    <input type="hidden" name="player_count" value="<?= $player_count ?>">
                    <input type="hidden" name="doelmannen" value="<?= htmlspecialchars($_POST['doelmannen'] ?? 0) ?>">
                    
                    <?php $doelmannen_count = (int)($_POST['doelmannen'] ?? 0); ?>
                    <div class="row">
                    <?php foreach ($all_players as $p): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 player-card cursor-pointer border-0 shadow-sm transition-all" onclick="togglePlayer(<?= $p['id'] ?>)" id="card-<?= $p['id'] ?>">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input player-checkbox" type="checkbox" name="players[]" value="<?= $p['id'] ?>" id="chk-<?= $p['id'] ?>" 
                                            <?= (in_array($p['id'], $selected_players ?? [])) ? 'checked' : '' ?>
                                            onclick="event.stopPropagation(); updateCount();">
                                        <label class="form-check-label ms-2" for="chk-<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                        </label>
                                    </div>
                                    <?php if ($doelmannen_count > 0): ?>
                                    <div class="gk-wrapper" style="display: <?= (in_array($p['id'], $selected_players ?? [])) ? 'block' : 'none' ?>;" id="gk-wrap-<?= $p['id'] ?>">
                                        <input type="checkbox" name="goalkeepers[]" value="<?= $p['id'] ?>" id="gk-<?= $p['id'] ?>" 
                                            <?= (in_array($p['id'], $selected_gks ?? [])) ? 'checked' : '' ?>
                                            class="btn-check gk-checkbox" autocomplete="off" onclick="event.stopPropagation(); enforceGkLimit(this);">
                                        <label class="btn btn-sm btn-outline-warning gk-btn" for="gk-<?= $p['id'] ?>" title="Klik om te markeren als Doelman" onclick="event.stopPropagation();">GK</label>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    
                    <div class="position-sticky bottom-0 bg-white p-3 shadow-lg border-top rounded mt-4 d-flex justify-content-between align-items-center" style="z-index: 1020;">
                        <div>
                            <strong>Geselecteerd:</strong> <span id="sel-count" class="badge bg-secondary fs-6">0</span> / <?= $player_count ?>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.history.back()">Terug</button>
                            <button type="submit" class="btn btn-success fw-bold px-4" id="btn-submit" disabled><i class="fa-solid fa-hammer me-2"></i>Bouw Schema</button>
                        </div>
                    </div>
                </form>

                <script>
                function togglePlayer(id) {
                    const chk = document.getElementById('chk-' + id);
                    chk.checked = !chk.checked;
                    updateCount();
                }

                function updateCount() {
                    let count = document.querySelectorAll('.player-checkbox:checked').length;
                    let target = <?= $player_count ?>;
                    let badge = document.getElementById('sel-count');
                    let btn = document.getElementById('btn-submit');
                    
                    badge.innerText = count;
                    if(count === target) {
                        badge.className = "badge bg-success fs-6";
                        btn.disabled = false;
                    } else if (count > target) {
                        badge.className = "badge bg-danger fs-6";
                        btn.disabled = true;
                    } else {
                        badge.className = "badge bg-secondary fs-6";
                        btn.disabled = true;
                    }
                    
                    // Style cards and show/hide GK button
                    document.querySelectorAll('.player-checkbox').forEach(chk => {
                        let card = document.getElementById('card-' + chk.value);
                        let gkWrap = document.getElementById('gk-wrap-' + chk.value);
                        if(chk.checked) {
                            card.classList.add('border-primary', 'bg-light');
                            if(gkWrap) gkWrap.style.display = 'block';
                        } else {
                            card.classList.remove('border-primary', 'bg-light');
                            if(gkWrap) gkWrap.style.display = 'none';
                            // Uncheck GK if player is unselected
                            let gkChk = document.getElementById('gk-' + chk.value);
                            if(gkChk) gkChk.checked = false;
                        }
                    });
                }
                
                function enforceGkLimit(checkbox) {
                    let targetGk = <?= $doelmannen_count ?? 0 ?>;
                    if(targetGk === 0) return;
                    let currentGks = document.querySelectorAll('.gk-checkbox:checked').length;
                    if(currentGks > targetGk && checkbox.checked) {
                        alert("Je hebt ingesteld dat je schema maximaal " + targetGk + " doelman(nen) heeft.");
                        checkbox.checked = false;
                    }
                }

                document.addEventListener('DOMContentLoaded', updateCount);
                </script>
                <style>
                    .player-card:hover { transform: translateY(-2px); border-color: #0d6efd !important; }
                    .gk-btn { border-radius: 50%; width: 30px; height: 30px; padding: 0; line-height: 28px; font-size: 0.7rem; font-weight: bold;}
                    .btn-check:checked + .gk-btn { background-color: #ffc107; color: #000; }
                </style>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
