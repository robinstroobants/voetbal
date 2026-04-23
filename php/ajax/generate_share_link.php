<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../getconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niet ingelogd.']);
    exit;
}

$team_id = (int)$_SESSION['team_id'];
$game_id = (int)($_POST['game_id'] ?? 0);
$expires_in_hours = (int)($_POST['expires_in'] ?? 24); // 0 means forever

if (!$game_id) {
    echo json_encode(['success' => false, 'error' => 'Geen wedstrijd id opgegeven.']);
    exit;
}

// Controleer of de wedstrijd van dit team is
$stmt = $pdo->prepare("SELECT id, share_token FROM games WHERE id = ? AND team_id = ?");
$stmt->execute([$game_id, $team_id]);
$game = $stmt->fetch();

if (!$game) {
    echo json_encode(['success' => false, 'error' => 'Wedstrijd niet gevonden of geen toegang.']);
    exit;
}

// Check of de opstelling definitief is
$stmtFinal = $pdo->prepare("SELECT id FROM game_lineups WHERE game_id = ? AND is_final = 1");
$stmtFinal->execute([$game_id]);
if (!$stmtFinal->fetchColumn()) {
    echo json_encode(['success' => false, 'error' => 'Er is nog geen definitieve opstelling voor deze wedstrijd.']);
    exit;
}

// Bepaal expiration
$expires_at = null;
if ($expires_in_hours > 0) {
    $expires_at = date('Y-m-d H:i:s', strtotime("+$expires_in_hours hours"));
}

// Bepaal of generate een nieuwe token nodig is
$token = $game['share_token'];
if (empty($token)) {
    $token = bin2hex(random_bytes(16));
}

// Update database
$update = $pdo->prepare("UPDATE games SET share_token = ?, share_expires_at = ? WHERE id = ?");
if ($update->execute([$token, $expires_at, $game_id])) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $link = "$protocol://$host/share/$token";
    
    echo json_encode([
        'success' => true, 
        'link' => $link, 
        'token' => $token,
        'expires_at' => $expires_at ? date('d-m-Y H:i', strtotime($expires_at)) : 'Nooit'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database fout.']);
}
