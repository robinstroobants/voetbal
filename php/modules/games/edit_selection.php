<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/models/MatchManager.php';

$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
if (isset($_POST['game_id'])) {
    $gameId = (int)$_POST['game_id'];
}

// Haal wedstrijd info op
$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id AND team_id = :team_id");
$stmt->execute(['id' => $gameId, 'team_id' => $_SESSION['team_id']]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header("Location: /games");
    exit;
}

$manager = new MatchManager($pdo);

// 1. Haal huidige selecties uit db op voor weergave
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

// 2. Check hoeveel game_lineups er zijn voor deze match
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM game_lineups WHERE game_id = ?");
$stmtCheck->execute([$gameId]);
$lineupsCount = (int)$stmtCheck->fetchColumn();

// 3. Verwerk save formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_selection') {
    $selectedPlayers = $_POST['players'] ?? []; // Array of checked player IDs
    $goalkeepers = $_POST['goalkeepers'] ?? []; // Array of checked goalkeeper IDs
    
    $allSelected = array_unique(array_merge($selectedPlayers, $goalkeepers));
    
    // Kijk of the selectie afwijkt in PHP
    $selection_changed = false;
    $newSelectedMap = array_flip($allSelected);
    $newGkMap = array_flip($goalkeepers);

    if (count($currentSelectedMap) !== count($newSelectedMap) || count($currentGoalkeeperMap) !== count($newGkMap)) {
        $selection_changed = true;
    } else {
        foreach ($newSelectedMap as $id => $val) {
            if (!isset($currentSelectedMap[$id])) { $selection_changed = true; break; }
        }
        if (!$selection_changed) {
            foreach ($newGkMap as $id => $val) {
                if (!isset($currentGoalkeeperMap[$id])) { $selection_changed = true; break; }
            }
        }
    }
    
    // Opslaan db status en geef aan of de selectie is gewijzigd (zodat schemas gewist worden)
    $manager->saveSelection($gameId, $allSelected, 2, $goalkeepers, $selection_changed);
    
    // Als de selectie is gewijzigd en er waren lineups, synchroniseer de logs om oude minuten te wissen
    if ($selection_changed && $lineupsCount > 0) {
        $manager->syncGameLogs($gameId);
    }
    
    // Redirect direct naar de opstellingengenerator na opslaan
    header("Location: /games/" . $gameId . "/schema");
    exit;
}

// Haal alle actieve spelers op
$stmtPlayers = $pdo->prepare("SELECT * FROM players WHERE team_id = ? ORDER BY first_name, last_name");
$stmtPlayers->execute([$_SESSION['team_id']]);
$allPlayers = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Selectie Beheren: ' . htmlspecialchars($game['opponent']);
require_once dirname(__DIR__, 2) . '/header.php';
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
            <a href="/games" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left me-2"></i>Terug</a>
        </div>
    </div>

    <form method="post" class="card shadow-sm border-0">
        <input type="hidden" name="action" value="save_selection">
        <input type="hidden" name="game_id" value="<?= $gameId ?>">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Spelerslijst</h5>
            <span class="badge bg-light text-dark" id="count_badge">0 geselecteerd</span>
        </div>
        
        <div class="card-body bg-light border-bottom p-3">
            <label class="form-label fw-bold"><i class="fa-solid fa-paste text-primary me-2"></i>Slimme Scanner (WhatsApp lijstje plakken)</label>
            <textarea id="smart-scanner" class="form-control mb-2" rows="2" placeholder="Plak hier je lijst met namen. De scanner zal deze herkennen en automatisch aanvinken!"></textarea>
            <div id="scanner-feedback" class="small"></div>
            <div class="text-end mt-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelection()"><i class="fa-solid fa-trash me-1"></i>Wis alle vinkjes</button>
            </div>
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
                            <div class="text-muted small"><?= htmlspecialchars($player['first_name']) ?></div>
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
let initialCheckboxes = [];
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
document.addEventListener("DOMContentLoaded", function() {
    updateCounts();
    document.querySelectorAll('input[type="checkbox"]').forEach(c => {
        initialCheckboxes.push({ element: c, checked: c.checked });
    });
});

document.querySelector('form').addEventListener('submit', function(e) {
    let isChanged = false;
    initialCheckboxes.forEach(item => {
        if (item.element.checked !== item.checked) isChanged = true;
    });
    
    <?php if ($lineupsCount > 0): ?>
    if (isChanged) {
        if (!confirm("Let op: Er zijn al opgeslagen voorselecties of een finale opstelling voor deze wedstrijd gegenereerd!\n\nDoor de selectie te wijzigen, vervallen al deze opstellingen en zullen ze definitief gewist worden.\n\nWeet je zeker dat je wilt doorgaan?")) {
            e.preventDefault();
            return false;
        }
    }
    <?php endif; ?>
});

const allPlayers = [
    <?php foreach($allPlayers as $player): ?>
    { id: <?= $player['id'] ?>, firstName: <?= json_encode($player['first_name']) ?>, lastName: <?= json_encode($player['last_name']) ?>, fullName: <?= json_encode($player['first_name'] . ' ' . $player['last_name']) ?>, isGk: <?= (int)($player['is_doelman'] ?? 0) ?> },
    <?php endforeach; ?>
];

function normalizeName(str) {
    if (!str) return '';
    return str.toLowerCase()
              .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // verwijder accenten (bv. ç -> c, é -> e)
              .trim();
}

const allPlayersNorm = allPlayers.map(p => ({
    id: p.id,
    first: normalizeName(p.firstName),
    last: normalizeName(p.lastName),
    full: normalizeName(p.fullName),
    isGk: p.isGk
}));

function clearSelection() {
    document.querySelectorAll('.player-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.gk-checkbox').forEach(cb => cb.checked = false);
    updateCounts();
    document.getElementById('smart-scanner').value = '';
    document.getElementById('scanner-feedback').innerHTML = '';
}

document.getElementById('smart-scanner').addEventListener('input', function() {
    let text = this.value;
    if (!text.trim()) {
        document.getElementById('scanner-feedback').innerHTML = '';
        return;
    }
    
    let lines = text.split(/\r?\n|,/);
    let matchedIds = new Set();
    let unmatched = [];
    
    lines.forEach(line => {
        // Clean the line: remove ANY leading non-letter/non-number character (handles weird bullets, spaces, emojis at start)
        let cleaned = line.replace(/^[^a-zA-ZÀ-ÿ0-9]+/, '').trim();
        // Remove trailing emojis or common chat artifacts
        cleaned = cleaned.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, '').trim();
        
        if (!cleaned) return;
        
        let cleanedLower = normalizeName(cleaned);
        let bestMatch = null;
        
        // Exact full name match
        bestMatch = allPlayersNorm.find(p => p.full === cleanedLower);
        
        // Exact first name match
        if (!bestMatch) {
            let possibleMatches = allPlayersNorm.filter(p => p.first === cleanedLower);
            if (possibleMatches.length === 1) {
                bestMatch = possibleMatches[0];
            }
        }
        
        // Partial full name match (e.g. "murat y" matches "murat yilmaz")
        if (!bestMatch) {
            let possibleMatches = allPlayersNorm.filter(p => p.full.startsWith(cleanedLower));
            if (possibleMatches.length === 1) {
                bestMatch = possibleMatches[0];
            }
        }
        
        // Partial first name match (e.g. "thib" matches "thibo")
        if (!bestMatch) {
            let possibleMatches = allPlayersNorm.filter(p => p.first.startsWith(cleanedLower));
            if (possibleMatches.length === 1) {
                bestMatch = possibleMatches[0];
            }
        }
        
        // Token based fallback: every word in input must match the start of some word in the player's full name
        if (!bestMatch) {
            let inputTokens = cleanedLower.split(' ').filter(t => t.length > 0);
            let possibleMatches = allPlayersNorm.filter(p => {
                let nameTokens = p.full.split(' ');
                return inputTokens.every(it => nameTokens.some(nt => nt.startsWith(it)));
            });
            if (possibleMatches.length === 1) {
                bestMatch = possibleMatches[0];
            }
        }
        
        // Final fallback: does the full name simply contain the input string?
        if (!bestMatch) {
            let possibleMatches = allPlayersNorm.filter(p => p.full.includes(cleanedLower));
            if (possibleMatches.length === 1) {
                bestMatch = possibleMatches[0];
            }
        }
        
        if (bestMatch) {
            matchedIds.add(bestMatch.id);
        } else {
            unmatched.push(cleaned);
        }
    });
    
    // Apply checkboxes
    matchedIds.forEach(id => {
        let cb = document.querySelector('input.player-checkbox[value="'+id+'"]');
        if (cb && !cb.checked) {
            cb.checked = true;
            // Also check the GK box if this player is a default goalkeeper
            let pObj = allPlayersNorm.find(p => p.id == id);
            if (pObj && pObj.isGk) {
                let gkCb = document.querySelector('input.gk-checkbox[value="'+id+'"]');
                if (gkCb) gkCb.checked = true;
            }
        }
    });
    updateCounts();
    
    let feedback = `<span class="text-success fw-bold"><i class="fa-solid fa-check"></i> ${matchedIds.size} spelers herkend en aangevinkt.</span>`;
    if (unmatched.length > 0) {
        feedback += `<br><span class="text-danger small"><i class="fa-solid fa-triangle-exclamation"></i> Niet herkend: <strong>${unmatched.join(', ')}</strong></span>`;
    }
    document.getElementById('scanner-feedback').innerHTML = feedback;
});
</script>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
