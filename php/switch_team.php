<?php
session_start();
require_once 'getconn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$target_team_id = (int)($_POST['team_id'] ?? 0);

if ($target_team_id) {
    $stmt = $pdo->prepare("
        SELECT t.id, t.name, t.default_format, t.default_game_parts, t.subscription_valid_until 
        FROM user_teams ut
        JOIN teams t ON ut.team_id = t.id
        WHERE ut.user_id = ? AND ut.team_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $target_team_id]);
    $team = $stmt->fetch();
    
    if ($team) {
        $_SESSION['team_id'] = $team['id'];
        $_SESSION['team_name'] = $team['name'];
        $_SESSION['default_format'] = $team['default_format'] ?: '8v8';
        $_SESSION['default_game_parts'] = $team['default_game_parts'] ?: '4x15';
        
        $validUntil = strtotime($team['subscription_valid_until']);
        // When impersonating, role might be coach, so it strictly respects their read-only state.
        if ($_SESSION['role'] !== 'superadmin' && $validUntil < time()) {
            $_SESSION['is_read_only'] = true;
        } else {
            $_SESSION['is_read_only'] = false;
        }
    }
}

// Terug naar het vorige scherm of index
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php';
// Fix loop indien referrer switch is.
if (strpos($redirectUrl, 'switch_team.php') !== false) {
    $redirectUrl = 'index.php';
}

header("Location: " . $redirectUrl);
exit;
?>
