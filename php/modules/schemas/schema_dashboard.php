<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/models/MatchManager.php';

$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id AND team_id = :team_id");
$stmt->execute(['id' => $gameId, 'team_id' => $_SESSION['team_id']]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    header("Location: /games");
    exit;
}

// Check active period
$stmtPeriod = $pdo->prepare("SELECT id, name FROM team_periods WHERE team_id = ? AND ? BETWEEN start_date AND end_date");
$stmtPeriod->execute([$_SESSION['team_id'], $game['game_date']]);
$activePeriod = $stmtPeriod->fetch(PDO::FETCH_ASSOC);

// Check of er al een definitieve is
$stmtCheck = $pdo->prepare("SELECT id FROM game_lineups WHERE game_id = ? AND is_final = 1");
$stmtCheck->execute([$gameId]);
$has_final = $stmtCheck->fetchColumn();

// Fetch bestaande voorselecties (niet-definitief)
$stmtPreviews = $pdo->prepare("
    SELECT gl.id, gl.score, gl.created_at, gl.schema_id, gl.player_order, l.is_original, l.player_count, l.schema_data
    FROM game_lineups gl
    JOIN lineups l ON gl.schema_id = l.id
    WHERE gl.game_id = ? AND gl.is_final = 0 
    ORDER BY gl.created_at DESC
");
$stmtPreviews->execute([$gameId]);
$previews = $stmtPreviews->fetchAll(PDO::FETCH_ASSOC);

// Map player names for previews
$mm = new MatchManager($pdo);
$matchData = $mm->getSelection($gameId);
$playerInfo = $matchData['player_info'] ?? [];
$matchGks = array_filter(array_map('trim', explode(',', $matchData['doelmannen'] ?? '')));

foreach($previews as &$p) {
    $pids = explode(',', $p['player_order']);
    
    // Parse schema data to calculate playtime and unique positions
    $schema_data = json_decode($p['schema_data'], true);
    $playerMinutes = array_fill_keys($pids, 0);
    $playerPositions = array_fill_keys($pids, []);
    
    if (is_array($schema_data)) {
        foreach($schema_data as $block) {
            $dur = isset($block['duration']) ? (int)$block['duration'] / 60 : 0;
            if (isset($block['lineup']) && is_array($block['lineup'])) {
                foreach($block['lineup'] as $pos => $generic_id) {
                    if (isset($pids[$generic_id])) {
                        $pid = $pids[$generic_id];
                        $playerMinutes[$pid] += $dur;
                        if ($pos != 1) { // pos 1 is GK, skip for field positions count
                            $playerPositions[$pid][$pos] = true;
                        }
                    }
                }
            }
        }
    }
    
    // Group by minutes
    $grouped = [];
    foreach($playerMinutes as $pid => $mins) {
        $roundedMins = round($mins);
        if (!isset($grouped[$roundedMins])) $grouped[$roundedMins] = [];
        if (isset($playerInfo[$pid])) {
            $grouped[$roundedMins][] = $playerInfo[$pid]['first_name'];
        }
    }
    krsort($grouped); // Sort high minutes to low
    $p['grouped_mins'] = $grouped;
    
    // Calculate minimum unique field positions for FIELD players
    $min_pos = 999;
    foreach($playerPositions as $pid => $posList) {
        if (!empty($playerInfo[$pid]['is_goalkeeper']) || in_array((string)$pid, $matchGks)) continue; // skip GK
        if (empty($playerMinutes[$pid])) continue; // skip fully benched players
        
        $count = count($posList);
        if ($count < $min_pos) {
            $min_pos = $count;
        }
    }
    if ($min_pos === 999) $min_pos = 0; // fallback if no field players
    
    if ($min_pos >= 3) {
        $p['min_pos_badge'] = '<span class="badge bg-success" title="Zeer gevarieerd!"><i class="fa-solid fa-check-double me-1"></i>Min. 3 posities</span>';
    } elseif ($min_pos == 2) {
        $p['min_pos_badge'] = '<span class="badge bg-warning text-dark" title="Elke speler doet minstens 2 posities"><i class="fa-solid fa-check me-1"></i>Min. 2 posities</span>';
    } else {
        $p['min_pos_badge'] = '<span class="badge bg-danger" title="Geen of weinig variatie!"><i class="fa-solid fa-xmark me-1"></i>Geen min. posities</span>';
    }
    
    // Check if it's purely original or modified
    $p['type'] = $p['is_original'] == 1 ? 'AI Gegenereerd' : 'Manueel';
    $p['type_icon'] = $p['is_original'] == 1 ? 'fa-wand-magic-sparkles text-primary' : 'fa-hammer text-warning';
}
unset($p); // Critical to prevent PHP reference overwrite bug!

// Auto-redirect naar de definitieve opstelling om een nutteloze extra klik op het dashboard te vermijden
if ($has_final && empty($_GET['force_dashboard'])) {
    header("Location: /games/$gameId/lineup");
    exit;
}

// Check if generating is requested immediately via url param
if (isset($_GET['generate']) && $_GET['generate'] == 1) {
    unset($_SESSION["logged_generation_$gameId"]); // Allow billing for explicit re-generation
    header("Location: /games/$gameId/lineup?generate=1");
    exit;
}

// Process legacy form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['legacy_id'])) {
    $legacy_id = (int)$_POST['legacy_id'];
    // Dit zullen we later uitbreiden met de echte legacy load logica
    header("Location: /games/$gameId/schema?msg=legacy_loaded");
    exit;
}

$page_title = 'Opstellingen: ' . htmlspecialchars($game['opponent']);
require_once dirname(__DIR__, 2) . '/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Kies Opstelling Methode</h2>
            <p class="text-muted mb-0">Wedstrijd: <strong><?= htmlspecialchars($game['opponent']) ?></strong> op <?= date('d/m/Y', strtotime($game['game_date'])) ?> 
               <span class="badge bg-secondary ms-2"><?= htmlspecialchars($game['format']) ?></span>
            </p>
        </div>
        <div>
            <a href="/games" class="btn btn-outline-secondary me-2"><i class="fa-solid fa-arrow-left me-2"></i>Terug naar kalender</a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'legacy_loaded'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-check"></i> Legacy schema commando ontvangen (nog te implementeren).</div>
    <?php endif; ?>

    <div class="row g-4">
        
        <?php if ($has_final): ?>
        <div class="col-md-12">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h4 class="mb-1"><i class="fa-solid fa-check-circle me-2"></i> Er is al een definitieve opstelling</h4>
                        <p class="mb-0 text-white-50">Je hebt reeds een opstelling vastgelegd voor deze wedstrijd.</p>
                    </div>
                    <a href="/games/<?= $gameId ?>/lineup" class="btn btn-light text-success fw-bold px-4">Bekijk Opstelling</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4 d-flex flex-column">
                    <div class="display-4 text-primary mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h5 class="card-title fw-bold">ProLineup AI</h5>
                    <p class="card-text text-muted mb-4" style="min-height: 120px;">Vind de <strong>sterkst mogelijke tactische opstelling</strong> op basis van honderden historische schema's en positiescores, terwijl de speelminuten uiteraard mooi in balans blijven.</p>
                    <a href="/games/<?= $gameId ?>/lineup?generate=1" class="btn btn-primary w-100 fw-bold mt-auto">Genereer met ProLineup</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4 d-flex flex-column">
                    <div class="display-4 text-warning mb-3">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                    <h5 class="card-title fw-bold">Lineup Lab</h5>
                    <p class="card-text text-muted mb-4" style="min-height: 120px;">Neem de touwtjes in handen en <strong>bouw handmatig je ideale tactiek</strong> op het interactieve canvas. Laat je tijdens het schuiven ondersteunen door live datagestuurde assistentie, wiskundige checks en rotatie-advies.</p>
                    <a href="/games/<?= $gameId ?>/builder" class="btn btn-warning text-dark w-100 fw-bold mt-auto">Open Lineup Lab</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="card-body text-center p-4 d-flex flex-column">
                    <div class="display-4 text-success mb-3">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark">EqualPlay AI</h5>
                    <p class="card-text text-muted mb-4" style="min-height: 120px;">Genereer een schema met de absolute focus op <strong>gelijke speeltijd voor iedereen</strong> over alle posities heen. Positiescores worden pas in tweede instantie bekeken.</p>
                    
                    <div class="mt-auto">
                        <a href="/games/<?= $gameId ?>/lineup?generate=1&dynamic=1" class="btn btn-success w-100 fw-bold">Genereer met EqualPlay</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-none">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-info mb-3">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h5 class="card-title fw-bold">Legacy Schema</h5>
                    <p class="card-text text-muted mb-4">Laad een historisch of specifiek schema in op basis van een gekend Legacy ID (bv. 7777).</p>
                    
                    <form method="POST" class="mt-auto">
                        <div class="input-group">
                            <input type="number" class="form-control" name="legacy_id" placeholder="Schema ID" required>
                            <button class="btn btn-info text-white fw-bold" type="submit">Inladen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>

    <?php if (count($previews) > 0): ?>
    <div class="mt-5">
        <h4 class="mb-3"><i class="fa-solid fa-layer-group me-2"></i>Bestaande Voorselecties</h4>
        <p class="text-muted mb-3">Je hebt de volgende schemas reeds gebouwd of gegenereerd. Kies er één om verder te bekijken of definitief te maken.</p>
        <div class="row g-3">
            <?php foreach($previews as $p): ?>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="card-title mb-1 fw-bold text-dark">Voorselectie #<?= $p['id'] ?> <small class="text-muted fw-normal ms-1">(Schema #<?= $p['schema_id'] ?>)</small></h6>
                                <div class="mb-1">
                                    <small class="text-muted"><i class="fa-solid <?= $p['type_icon'] ?> me-1"></i> <?= $p['type'] ?></small>
                                </div>
                                <div class="mb-1">
                                    <?= $p['min_pos_badge'] ?>
                                </div>
                                <div>
                                    <small class="text-muted"><i class="fa-regular fa-clock me-1"></i> <?= date('d M Y - H:i', strtotime($p['created_at'])) ?></small>
                                </div>
                            </div>
                            <?php if ($p['score'] > 0): ?>
                            <div class="text-end">
                                <span class="badge bg-<?= $p['score'] >= 80 ? 'success' : ($p['score'] >= 60 ? 'warning' : 'danger') ?> fs-6 mb-1"><?= number_format($p['score'], 1) ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bg-light p-2 rounded mt-3 mb-3 border">
                            <p class="small text-muted mb-2 border-bottom pb-1" style="font-size: 0.75rem;">
                                <strong><i class="fa-solid fa-hourglass-half me-1"></i> Speelminuten</strong>
                            </p>
                            <?php foreach($p['grouped_mins'] as $mins => $names): ?>
                            <div class="d-flex mb-1" style="font-size: 0.75rem;">
                                <span class="badge bg-secondary me-2" style="width: 40px;"><?= $mins ?>m</span>
                                <span class="text-muted text-truncate"><?= htmlspecialchars(implode(', ', $names)) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="/games/<?= $gameId ?>/lineup?preview=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary w-100 fw-bold">Bekijk Preview</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
