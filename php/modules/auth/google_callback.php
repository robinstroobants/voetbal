<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/core/Mailer.php';

$googleClientId = $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID');
$googleClientSecret = $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/google_callback";

$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => $googleClientId,
    'clientSecret' => $googleClientSecret,
    'redirectUri'  => $redirectUri,
]);

if (!empty($_GET['error'])) {
    // Got an error, probably user denied access
    header("Location: /login?error=" . urlencode("Google Login werd geannuleerd."));
    exit;
} elseif (empty($_GET['code'])) {
    // Als er geen code is, sturen we ze terug naar login
    header("Location: /login");
    exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    // State is invalid, possible CSRF attack in progress
    unset($_SESSION['oauth2state']);
    header("Location: /login?error=" . urlencode("Beveiligingsfout tijdens inloggen (Invalid State). Probeer het opnieuw."));
    exit;
}

try {
    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    $ownerDetails = $provider->getResourceOwner($token);

    $email = $ownerDetails->getEmail();
    $firstName = $ownerDetails->getFirstName();
    $lastName = $ownerDetails->getLastName();

    // Controleer of de gebruiker bestaat
    $stmt = $pdo->prepare("SELECT id, email, password_hash, role, is_verified, account_status, is_beta_user, last_active_team_id 
                           FROM users 
                           WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Bestaande gebruiker
        if (isset($user['account_status']) && $user['account_status'] === 'pending') {
            header("Location: /login?error=" . urlencode("Je staat op de wachtlijst! Een beheerder moet je account nog activeren. We contacteren je zodra je kan inloggen."));
            exit;
        }
        
        if (isset($user['account_status']) && $user['account_status'] === 'suspended') {
            header("Location: /login?error=" . urlencode("Je account is geschorst. Neem contact op met de beheerder."));
            exit;
        }

        // Zorg dat account gemarkeerd is als verified indien dat nog niet zo was
        if (!$user['is_verified']) {
            $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?")->execute([$user['id']]);
        }

        // Setup session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_beta_user'] = $user['is_beta_user'];
        
        // Fetch Teams (Multi-Team Support)
        $stmtWs = $pdo->prepare("SELECT ut.team_id, t.name, t.default_format, t.default_game_parts, t.subscription_valid_until FROM user_teams ut JOIN teams t ON ut.team_id = t.id WHERE ut.user_id = ?");
        $stmtWs->execute([$user['id']]);
        $teams = $stmtWs->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['available_teams'] = $teams;

        $_SESSION['team_id'] = null;
        
        if (!empty($teams)) {
            $selected_team = $teams[0];
            if (!empty($user['last_active_team_id'])) {
                foreach ($teams as $t) {
                    if ($t['team_id'] == $user['last_active_team_id']) {
                        $selected_team = $t;
                        break;
                    }
                }
            }
            
            $_SESSION['team_id'] = $selected_team['team_id'];
            $_SESSION['team_name'] = $selected_team['name'];
            $_SESSION['default_format'] = $selected_team['default_format'] ?: '8v8';
            $_SESSION['default_game_parts'] = $selected_team['default_game_parts'] ?: '4x15';
            $user['subscription_valid_until'] = $selected_team['subscription_valid_until'];
        }
        
        // Check subscription
        $subValid = $user['subscription_valid_until'] ?? null;
        $validUntil = $subValid ? strtotime($subValid) : 0;
        if ($user['role'] !== 'superadmin' && $validUntil < time()) {
            $_SESSION['is_read_only'] = true;
        } else {
            $_SESSION['is_read_only'] = false;
        }

        if ($user['role'] === 'superadmin') {
            header("Location: /admin");
        } else {
            header("Location: /");
        }
        exit;
    } else {
        // Nieuwe gebruiker: registreer als pending (wachtlijst)
        // Dummy paswoord genereren aangezien ze via google komen
        $dummy_password = bin2hex(random_bytes(16));
        $hash = password_hash($dummy_password, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, first_name, last_name, password_hash, is_verified, account_status) VALUES (?, ?, ?, ?, 1, 'pending')");
        $stmt->execute([$email, $firstName, $lastName, $hash]);
        
        // Notify admin
        $admin_subject = "Nieuwe (Google) wachtlijst aanmelding: " . $firstName . " " . $lastName;
        $admin_msg = "Er is een nieuwe registratie op Lineup via Google Login.\nNaam: " . $firstName . " " . $lastName . "\nEmail: " . $email . "\n\nDeze gebruiker staat op de wachtlijst en wacht op goedkeuring in het admin dashboard.";
        Mailer::send('robin@webbit.be', $admin_subject, $admin_msg);

        header("Location: /login?error=" . urlencode("Je account is aangemaakt en je e-mailadres is geverifieerd via Google! Je staat momenteel op de wachtlijst tot een beheerder je toelaat."));
        exit;
    }

} catch (Exception $e) {
    // Failed to get user details
    header("Location: /login?error=" . urlencode("Fout bij ophalen van gegevens via Google: " . $e->getMessage()));
    exit;
}
