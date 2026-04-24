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

// Check of er al een definitieve is
$stmtCheck = $pdo->prepare("SELECT id FROM game_lineups WHERE game_id = ? AND is_final = 1");
$stmtCheck->execute([$gameId]);
$has_final = $stmtCheck->fetchColumn();

// Auto-redirect naar de definitieve opstelling om een nutteloze extra klik op het dashboard te vermijden
if ($has_final && empty($_GET['force_dashboard'])) {
    header("Location: /games/$gameId/lineup");
    exit;
}

// Check if generating is requested immediately via url param
if (isset($_GET['generate']) && $_GET['generate'] == 1) {
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
                <div class="card-body text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h5 class="card-title fw-bold">AI Genereren</h5>
                    <p class="card-text text-muted mb-4">Laat het systeem de best mogelijke opstellingen berekenen op basis van spelersstatistieken en speelminuten.</p>
                    <a href="/games/<?= $gameId ?>/lineup?generate=1" class="btn btn-primary w-100 fw-bold">Bereken Nu</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-body text-center p-4">
                    <div class="display-4 text-warning mb-3">
                        <i class="fa-solid fa-hammer"></i>
                    </div>
                    <h5 class="card-title fw-bold">Zelf Bouwen</h5>
                    <p class="card-text text-muted mb-4">Gebruik de visuele drag-and-drop builder om zelf een unieke opstelling samen te stellen voor deze match.</p>
                    <a href="/games/<?= $gameId ?>/builder" class="btn btn-warning text-dark w-100 fw-bold">Open Builder</a>
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

</div>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
