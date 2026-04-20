<?php
require_once __DIR__ . '/getconn.php';

echo "SaaS Credentials herstellen...\n";

try {
    // Genereer de juiste hash voor "welkom123"
    $hash = password_hash('welkom123', PASSWORD_BCRYPT);
    
    // Zorg ervoor dat gebruiker ID 1 altijd de superadmin is met een bekend email en passwoord
    $adminEmail = 'robin@webbit.be';
    
    // Check of ID 1 bestaat
    $check = $pdo->query("SELECT id, email FROM users WHERE id = 1")->fetch();
    if ($check) {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ?, role = 'superadmin' WHERE id = 1");
        $stmt->execute([$adminEmail, $hash]);
        echo "SUPERADMIN account vernieuwd:\n- Email: $adminEmail\n- Wachtwoord: welkom123\n\n";
    } else {
        // Indien geen user 1, maak er eentje aan
        $stmt = $pdo->prepare("INSERT INTO users (id, team_id, email, password_hash, role) VALUES (1, 1, ?, ?, 'superadmin')");
        $stmt->execute([$adminEmail, $hash]);
        echo "SUPERADMIN account aangemaakt:\n- Email: $adminEmail\n- Wachtwoord: welkom123\n\n";
    }

    // Fix the ongeldige hashes van Brent en Shirley
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email IN ('brent@thes.be', 'shirley@thes.be')");
    $stmt->execute([$hash]);
    echo "COACH accounts hersteld (brent@thes.be / shirley@thes.be):\n- Wachtwoord: welkom123\n\n";
    
    echo "Succes! Je kan nu via het login scherm op de live server binnen als superadmin.\n";
    echo "Verwijder dit bestand nu voor de veiligheid: rm reset_admin.php\n";
    
} catch (Exception $e) {
    echo "Fout: " . $e->getMessage() . "\n";
}
