<?php
require_once 'getconn.php';
require_once 'MatchManager.php';

$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
if (isset($_POST['game_id'])) {
    $gameId = (int)$_POST['game_id'];
}

// Haal wedstrijd info op
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
$stmt->execute(['id' => $gameId]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header("Location: manage_games.php");
    exit;
}

$manager = new MatchManager($pdo);

// Verwerk save formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_selection') {
    $selectedPlayers = $_POST['players'] ?? []; // Array of checked player IDs
    $goalkeepers = $_POST['goalkeepers'] ?? []; // Array of checked goalkeeper IDs
    
    // Safety check: a goalkeeper must also be part of the selected players to save cleanly
    // But saveSelection doesn't strictly care as long as we pass playerIds as the base
    $allSelected = array_unique(array_merge($selectedPlayers, $goalkeepers));
    
    // Status ID 2 is "Finale Selectie"
    $manager->saveSelection($gameId, $allSelected, 2, $goalkeepers);
    
    $success_msg = "Selectie succesvol opgeslagen!";
}

// Haal huidige selecties uit db op voor weergave
$stmtSel = $pdo->prepare("SELECT player_id, is_goalkeeper FROM game_selections WHERE game_id = :id");
$stmtSel->execute(['id' => $gameId]);
$currentSelRows = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

$currentSelectedMap = [];
$currentGoalkeeperMap = [];
foreach ($currentSelRows as $row) {
    $currentSelectedMap[$row['player_id']] = true;
    if ($row['is_goalkeeper'] == 1) {
        $currentGoalkeeperMap[$row['player_id']] = true;
    }
}

// Haal alle actieve spelers op
$stmtPlayers = $pdo->query("SELECT * FROM players ORDER BY first_name, last_name");
$allPlayers = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Selectie Beheren: ' . htmlspecialchars($game['opponent']);
require_once 'header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Selectie Maken</h2>
            <p class="text-muted mb-0">Wedstrijd: <strong><?= htmlspecialchars($game['opponent']) ?></strong> op <?= date('d/m/Y', strtotime($game['game_date'])) ?> 
               <span class="badge bg-secondary ms-2"><?= htmlspecialchars($game['format']) ?></span>
            </p>
        </div>
        <div>
            <a href="manage_games.php" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left me-2"></i>Terug</a>
            <a href="lineup.php?wedstrijd=<?= $gameId ?>" class="btn btn-outline-primary"><i class="fa-solid fa-calculator me-2"></i>Naar Algoritme</a>
        </div>
    </div>

    <?php if(!empty($success_msg)): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check me-2"></i> <?= $success_msg ?></div>
    <?php endif; ?>

    <form method="post" class="card shadow-sm border-0">
        <input type="hidden" name="action" value="save_selection">
        <input type="hidden" name="game_id" value="<?= $gameId ?>">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Spelerslijst</h5>
            <span class="badge bg-light text-dark" id="count_badge">0 geselecteerd</span>
        </div>
        
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item bg-light text-muted fw-bold d-flex">
                    <div style="flex:1;">Selecteer Speler</div>
                    <div style="width: 150px; text-align: center;">Is Doelman?</div>
                </div>
                <?php foreach($allPlayers as $player): 
                    $pId = $player['id'];
                    $isSelected = isset($currentSelectedMap[$pId]);
                    $isGk = isset($currentGoalkeeperMap[$pId]);
                ?>
                <label class="list-group-item d-flex align-items-center list-group-item-action toggle-row cursor-pointer" style="cursor: pointer;">
                    <div style="flex:1;" class="d-flex align-items-center">
                        <input class="form-check-input me-3 player-checkbox" type="checkbox" name="players[]" value="<?= $pId ?>" <?= $isSelected ? 'checked' : '' ?> onchange="updateCounts()">
                        <div>
                            <strong><?= htmlspecialchars($player['first_name'] . ' ' . $player['last_name']) ?></strong>
                            <div class="text-muted small"><?= htmlspecialchars($player['shortname']) ?></div>
                        </div>
                    </div>
                    <div style="width: 150px; text-align: center;" onclick="event.stopPropagation();">
                        <!-- Zorg dat de GK checkbox niet de hoofdrij checkbox aantikt in event propagatie -->
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input gk-checkbox" type="checkbox" name="goalkeepers[]" value="<?= $pId ?>" <?= $isGk ? 'checked' : '' ?> onchange="syncPlayer(this, <?= $pId ?>)">
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-footer bg-light p-3 text-end d-sticky sticky-bottom">
            <button type="submit" class="btn btn-success px-4 fw-bold">
                <i class="fa-solid fa-save me-2"></i>Selectie Opslaan
            </button>
        </div>
    </form>
</div>

<script>
function updateCounts() {
    let count = document.querySelectorAll('.player-checkbox:checked').length;
    document.getElementById('count_badge').innerText = count + ' geselecteerd';
}

// Als je iemand doelman maakt, moet hij automatisch geregistreerd staan als geselecteerde speler
function syncPlayer(gkCheckbox, pId) {
    if(gkCheckbox.checked) {
        let playerCheckbox = document.querySelector('input.player-checkbox[value="'+pId+'"]');
        if(playerCheckbox && !playerCheckbox.checked) {
            playerCheckbox.checked = true;
            updateCounts();
        }
    }
}

// Init count on page load
document.addEventListener("DOMContentLoaded", updateCounts);
</script>

<?php require_once 'footer.php'; ?>
