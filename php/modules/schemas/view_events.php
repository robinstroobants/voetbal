<?php
// Standalone page to view events for a game
if (!isset($_GET['game_id'])) {
    header("Location: /");
    exit;
}
$gameId = (int)$_GET['game_id'];

require_once dirname(__DIR__, 2) . '/core/getconn.php';
$page_title = "Wedstrijdverslag";
require_once dirname(__DIR__, 2) . '/header.php';

// Controleer of wedstrijd bestaat en van dit team is
$stmt = $pdo->prepare("SELECT id, opponent FROM games WHERE id = ? AND team_id = ?");
$stmt->execute([$gameId, $_SESSION['team_id']]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Wedstrijd niet gevonden of geen toegang.</div></div>";
    require_once dirname(__DIR__, 2) . '/footer.php';
    exit;
}

?>
<div class="container mt-4 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fa-solid fa-list-check text-primary me-2"></i> Verslag: <?= htmlspecialchars($match['opponent']) ?></h4>
        <div>
            <a href="/games/<?= $gameId ?>/lineup" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar Opstelling</a>
        </div>
    </div>
</div>

<?php 
// Include the partial
require_once __DIR__ . '/events_dashboard.php'; 
?>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
