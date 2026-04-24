<?php
// public_share.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/core/getconn.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("<div style='font-family:sans-serif;text-align:center;margin-top:50px;'><h2>Fout</h2><p>Geen token opgegeven.</p></div>");
}

$stmt = $pdo->prepare("SELECT id, team_id, opponent, share_expires_at FROM games WHERE share_token = ?");
$stmt->execute([$token]);
$game = $stmt->fetch();

if (!$game) {
    die("<div style='font-family:sans-serif;text-align:center;margin-top:50px;'><h2>Fout</h2><p>Deze deellink is ongeldig of werd ingetrokken door de coach.</p></div>");
}

if ($game['share_expires_at'] && strtotime($game['share_expires_at']) < time()) {
    die("<div style='font-family:sans-serif;text-align:center;margin-top:50px;'><h2>Fout</h2><p>Deze deellink is inmiddels vervallen.</p></div>");
}

// Controleer of de wedstrijd nog steeds definitief is
$stmtFinal = $pdo->prepare("SELECT id FROM game_lineups WHERE game_id = ? AND is_final = 1");
$stmtFinal->execute([$game['id']]);
if (!$stmtFinal->fetchColumn()) {
    die("<div style='font-family:sans-serif;text-align:center;margin-top:50px;'><h2>Fout</h2><p>De opstelling voor deze wedstrijd is nog niet of niet meer definitief gemaakt.</p></div>");
}

// Trick het systeem om de game in publieke modus in te laden
define('PUBLIC_SHARE_MODE', true);
$_GET['wedstrijd'] = $game['id'];
$_SESSION['team_id'] = $game['team_id']; // Voorkom crashes in generator.php
$page_title = "Opstelling: " . htmlspecialchars($game['opponent']);

// We laden gewoon lineup.php in, maar lineup.php moet zich nu gedragen als readonly publieke view.
require_once __DIR__ . '/modules/schemas/lineup.php';
