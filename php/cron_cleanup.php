<?php
require_once __DIR__ . '/core/getconn.php';

// 1. Verwijder onbevestigde accounts (wachtlijst of gewoon) die ouder zijn dan 48 uur
try {
    // Verwijder ook bijhorende user_teams voor we de user verwijderen als foreign keys dit niet automatisch doen
    // In ons geval is er nog geen team als ze onbevestigd en op de wachtlijst staan, maar voor de zekerheid:
    $pdo->exec("DELETE ut FROM user_teams ut JOIN users u ON ut.user_id = u.id WHERE u.is_verified = 0 AND u.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)");
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE is_verified = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)");
    $stmt->execute();
    $deletedUsers = $stmt->rowCount();
    
    // 2. Verwijder verlopen team_invitations ouder dan 7 dagen
    $stmtInv = $pdo->prepare("DELETE FROM team_invitations WHERE expires_at < NOW()");
    $stmtInv->execute();
    $deletedInvites = $stmtInv->rowCount();

    echo "Cleanup succesvol uitgevoerd.\n";
    echo "- Verwijderde onbevestigde accounts (>48u): $deletedUsers\n";
    echo "- Verwijderde verlopen uitnodigingen: $deletedInvites\n";
    
} catch (Exception $e) {
    error_log("Cron cleanup error: " . $e->getMessage());
    echo "Fout bij cleanup: " . $e->getMessage();
}
