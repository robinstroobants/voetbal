<?php
session_start();
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}
require_once dirname(__DIR__, 2) . '/core/getconn.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['is_verified'] == 1) {
            $error = "Dit account is al geactiveerd.";
        } else {
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($update->execute([$user['id']])) {
                // Fetch user info for the email notification
                $stmtInfo = $pdo->prepare("SELECT u.first_name, u.last_name, u.email, u.account_status, t.name as team_name 
                                           FROM users u 
                                           LEFT JOIN user_teams ut ON u.id = ut.user_id 
                                           LEFT JOIN teams t ON ut.team_id = t.id 
                                           WHERE u.id = ?");
                $stmtInfo->execute([$user['id']]);
                $uInfo = $stmtInfo->fetch();
                
                if ($uInfo && $uInfo['account_status'] === 'pending') {
                    require_once dirname(__DIR__, 2) . '/core/Mailer.php';
                    $admin_subject = "Nieuwe wachtlijst aanmelding BEVESTIGD: " . $uInfo['first_name'] . " " . $uInfo['last_name'];
                    $admin_msg = "Er is een nieuwe registratie op Lineup en het e-mailadres is zojuist bevestigd.\nNaam: " . $uInfo['first_name'] . " " . $uInfo['last_name'] . "\nEmail: " . $uInfo['email'] . "\nTeam: " . ($uInfo['team_name'] ?: 'Onbekend') . "\n\nDeze gebruiker staat op de wachtlijst en wacht op goedkeuring in het admin dashboard (sectie Gebruikers).";
                    Mailer::send('robin@webbit.be', $admin_subject, $admin_msg);
                }

                // Redirect user to login with success message so they don't stay on verify page
                header("Location: /login?msg=verified");
                exit;
            } else {
                $error = "Er is een database fout opgetreden. Probeer het later opnieuw.";
            }
        }
    } else {
        $error = "Ongeldige of verlopen activatielink.";
    }
} else {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activeren - Lineup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f7; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.04); border: 0; padding: 2.5rem; max-width: 450px; width: 100%; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <i class="fa-solid fa-triangle-exclamation text-danger mb-4" style="font-size: 3rem;"></i>
        <h3 class="fw-bold">Verificatie Mislukt</h3>
        <p class="text-muted mt-2 fw-medium"><?= htmlspecialchars($error) ?></p>
        <a href="/login" class="btn btn-primary mt-4 w-100 rounded-pill py-2 fw-bold text-white shadow-sm">Terug naar Inloggen</a>
    </div>
</body>
</html>
