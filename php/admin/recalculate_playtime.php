<?php
require_once dirname(__DIR__) . '/core/getconn.php';
require_once dirname(__DIR__) . '/models/MatchManager.php';

// Enkel superadmin of lokaal via command-line (CLI)
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        die("Toegang geweigerd: Enkel voor superadmins.");
    }
}

$mm = new MatchManager($pdo);

echo "Starten met herberekening van alle historische speelminuten...\n";

// Haal alle games op die een definitieve opstelling hebben
$stmt = $pdo->query("
    SELECT g.id, g.opponent, g.game_date 
    FROM games g
    JOIN game_lineups gl ON g.id = gl.game_id
    WHERE gl.is_final = 1
");

$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($games as $game) {
    echo "Verwerken van Game ID {$game['id']} ({$game['opponent']} - {$game['game_date']})...\n";
    $mm->syncGameLogs($game['id']);
    $count++;
}

echo "Succes! $count wedstrijden succesvol gesynchroniseerd naar de nieuwe logtabellen.\n";
