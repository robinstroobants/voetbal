<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once dirname(__DIR__, 2) . '/core/getconn.php';

$error = '';
$invite_token = $_GET['invite_token'] ?? $_POST['invite_token'] ?? '';
$invited_team_id = null;
$prefill_email = '';

if ($invite_token) {
    $stmtInv = $pdo->prepare("SELECT team_id, email FROM team_invitations WHERE token = ? AND expires_at > NOW()");
    $stmtInv->execute([$invite_token]);
    $inv_data = $stmtInv->fetch();
    if ($inv_data) {
        $invited_team_id = $inv_data['team_id'];
        $prefill_email = $inv_data['email'];
    } else {
        $error = "Deze uitnodigingslink is ongeldig of vervallen.";
        $invite_token = '';
    }
}

// Ophalen van beschikbare formats
$stmtFormats = $pdo->query("SELECT DISTINCT game_format FROM lineups");
$available_parts_by_format = [];
while ($row = $stmtFormats->fetchColumn()) {
    if (preg_match('/^(\d+v\d+)_(\d+gk_)?(\d+x\d+)$/', $row, $matches)) {
        $f = $matches[1];
        $p = $matches[3];
        if (!isset($available_parts_by_format[$f])) {
            $available_parts_by_format[$f] = [];
        }
        if (!in_array($p, $available_parts_by_format[$f])) {
            $available_parts_by_format[$f][] = $p;
        }
    }
}
$json_available_parts = json_encode($available_parts_by_format);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Alleen nodig als we geen invite hebben
    $team_name = trim($_POST['team_name'] ?? '');
    $default_format = trim($_POST['default_format'] ?? '8v8');
    $default_game_parts = trim($_POST['default_game_parts'] ?? '4x15');
    
    $nameParts = explode(' ', $name, 2);
    $first_name = $nameParts[0];
    $last_name = $nameParts[1] ?? '';

    // Validatie
    $has_team_data = $invited_team_id ? true : ($team_name && $default_format && $default_game_parts);

    if ($name && $email && $password && $has_team_data) {
        if (strlen($password) < 6) {
            $error = "Wachtwoord moet minimaal 6 tekens lang zijn.";
        } else {
            // Controleer of e-mail al bestaat
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Dit e-mailadres is al in gebruik.";
            } else {
                try {
                    $pdo->beginTransaction();

                    // 1. Maak de gebruiker eerst aan (team_id is tijdelijk NULL)
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $role = 'Coach'; 
                    $token = bin2hex(random_bytes(32));
                    
                    $account_status = $invited_team_id ? 'active' : 'pending';
                    
                    $stmtUser = $pdo->prepare("INSERT INTO users (email, first_name, last_name, password_hash, role, is_verified, verification_token, account_status) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
                    $stmtUser->execute([$email, $first_name, $last_name, $hash, $role, $token, $account_status]);
                    $user_id = $pdo->lastInsertId();

                    if ($invited_team_id) {
                        // 2. Koppel rechtstreeks aan bestaand team (Workspace)
                        $team_id = $invited_team_id;
                        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$user_id, $team_id]);
                        
                        // En update users.team_id als anchor
                        $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?")->execute([$team_id, $user_id]);

                        // 3. Update de invite status naar accepted
                        $pdo->prepare("UPDATE team_invitations SET status = 'accepted', accepted_at = NOW() WHERE token = ?")->execute([$invite_token]);
                    } else {
                        // 2. Maak het team aan met een 1 maand trial
                        $valid_until = date('Y-m-d H:i:s', strtotime("+1 month"));
                        
                        $stmtTeam = $pdo->prepare("INSERT INTO teams (user_id, club_id, name, default_format, default_game_parts, subscription_plan, subscription_valid_until, is_active) VALUES (?, 1, ?, ?, ?, 'trial', ?, 1)");
                        $stmtTeam->execute([$user_id, $team_name, $default_format, $default_game_parts, $valid_until]);
                        $team_id = $pdo->lastInsertId();

                        // 3. Koppel de gebruiker aan het zojuist gemaakte team als primary en in user_teams
                        $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?")->execute([$team_id, $user_id]);
                        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$user_id, $team_id]);
                    }

                    $pdo->commit();

                    // 4. Verstuur de verificatie e-mail
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                    $host = $_SERVER['HTTP_HOST'];
                    $verify_link = "$protocol://$host/verify.php?token=$token";
                    
                    $subject = "Activeer je Lineup account";
                    $message = "Beste $first_name,\n\nWelkom bij Lineup!\nKlik op de onderstaande link om je account te activeren:\n$verify_link\n\nMet vriendelijke groeten,\nHet Lineup Team";
                    
                    require_once dirname(__DIR__, 2) . '/core/Mailer.php';
                    Mailer::send($email, $subject, $message);
                    


                    // Redirect naar login pagina met melding in plaats van direct in te loggen
                    header("Location: /login?msg=registered");
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Er liep iets mis. Probeer het later opnieuw.";
                }
            }
        }
    } else {
        $error = "Gelieve alle velden in te vullen.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Lineup</title>
    <!-- Gebruik Inter voor een Apple-achtige of strakke uitstraling -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- FontAwesome toevoegen -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --apple-bg: #f5f5f7;
            --apple-text-main: #1d1d1f;
            --apple-text-muted: #86868b;
            --apple-border: #d2d2d7;
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-card-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--apple-bg);
            color: var(--apple-text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .login-container {
            background: var(--apple-card-bg);
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.04);
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .main-icon {
            font-size: 3rem;
            color: var(--apple-blue);
            margin-bottom: 16px;
        }

        .logo-wrap h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--apple-text-main);
            letter-spacing: -0.01em;
            margin-bottom: 8px;
        }
        
        .logo-wrap p {
            color: var(--apple-text-muted);
            font-size: 1rem;
        }

        .login-layout {
            display: flex;
            flex-direction: column;
        }

        .login-main {
            flex: 1;
        }

        .login-social {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-group input {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--apple-border);
            color: var(--apple-text-main);
            padding: 16px;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-group input::placeholder {
            color: var(--apple-text-muted);
        }

        .form-group input:focus {
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 4px rgba(0, 113, 227, 0.15);
        }

        .btn-submit {
            width: 100%;
            background: var(--apple-blue);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: var(--apple-blue-hover);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: var(--apple-text-muted);
            font-size: 0.85rem;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--apple-border);
        }
        
        .divider span { padding: 0 16px; }

        .btn-social {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #ffffff;
            color: var(--apple-text-main);
            border: 1px solid var(--apple-border);
            padding: 14px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .btn-social:hover {
            background: #f5f5f7;
        }

        .btn-social svg, .btn-social i { width: 20px; height: 20px; font-size: 20px; }
        
        .error {
            background: #fef0f0;
            border: 1px solid #fde2e2;
            color: #d93025;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 24px;
            text-align: center;
        }

        .footer-text {
            margin-top: 32px;
            text-align: center;
            color: var(--apple-text-muted);
            font-size: 0.8rem;
        }

        .switch-link {
            text-align: center;
            margin-top: 16px;
            font-size: 0.9rem;
        }

        .switch-link a {
            color: var(--apple-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .switch-link a:hover {
            text-decoration: underline;
        }

        @media (min-width: 768px) {
            .login-container {
                max-width: 760px; /* Breder op desktop voor de side-by-side layout */
            }
            .login-layout {
                flex-direction: row;
                align-items: stretch;
            }
            .divider {
                flex-direction: column;
                margin: 0 32px;
            }
            .divider::before, .divider::after {
                width: 1px;
                height: 100%;
                min-height: 30px;
            }
            .divider span { 
                padding: 16px 0; 
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-wrap">
            <i class="fa-regular fa-futbol main-icon"></i>
            <?php if ($invited_team_id): ?>
                <h1>Uitnodiging Accepteren</h1>
                <p>Maak een gratis account aan om als coach toe te treden.</p>
            <?php else: ?>
                <h1>Word lid van Lineup</h1>
                <p>Maak een gratis account aan om te starten.</p>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="login-layout">
            <div class="login-main">
                <form method="POST" action="">
                    <?php if ($invite_token): ?>
                        <input type="hidden" name="invite_token" value="<?= htmlspecialchars($invite_token) ?>">
                    <?php else: ?>
                        <div class="form-group">
                            <input type="text" name="team_name" placeholder="Naam team (bv. U11 Thes IP)" required>
                        </div>

                        <div class="form-group" style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <select name="default_format" id="default_format" class="form-control" style="width: 100%; background: #ffffff; border: 1px solid var(--apple-border); color: var(--apple-text-main); padding: 16px; border-radius: 12px; font-size: 1rem; outline: none; appearance: auto;" required>
                                    <option value="" disabled selected>Formaat</option>
                                    <option value="11v11">11v11</option>
                                    <option value="8v8">8v8</option>
                                    <option value="5v5">5v5</option>
                                    <option value="3v3">3v3</option>
                                    <option value="2v2">2v2</option>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <select name="default_game_parts" id="default_game_parts" class="form-control" style="width: 100%; background: #ffffff; border: 1px solid var(--apple-border); color: var(--apple-text-main); padding: 16px; border-radius: 12px; font-size: 1rem; outline: none; appearance: auto;" required>
                                    <option value="" disabled selected>Wedstrijdjes</option>
                                </select>
                            </div>
                        </div>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const availableParts = <?= $json_available_parts ?>;
                            const formatSelect = document.getElementById('default_format');
                            const partsSelect = document.getElementById('default_game_parts');

                            function updateParts() {
                                const selectedFormat = formatSelect.value;
                                partsSelect.innerHTML = '<option value="" disabled selected>Indeling</option>';
                                
                                if (!selectedFormat) return;
                                
                                let parts = availableParts[selectedFormat] || [];
                                if (parts.length === 0) {
                                    parts = ['4x15', '3x20', '2x45'];
                                }

                                parts.forEach(part => {
                                    const option = document.createElement('option');
                                    option.value = part;
                                    option.textContent = part;
                                    partsSelect.appendChild(option);
                                });
                            }

                            formatSelect.addEventListener('change', updateParts);
                        });
                        </script>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Je volledige naam" required autofocus>
                    </div>

                    <div class="form-group">
                        <?php if ($invite_token && $prefill_email): ?>
                            <input type="email" name="email" value="<?= htmlspecialchars($prefill_email) ?>" readonly style="background-color: #f5f5f7; color: var(--apple-text-muted);">
                        <?php else: ?>
                            <input type="email" name="email" placeholder="E-mailadres" required>
                        <?php endif; ?>
                    </div>

                    
                    
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Kies een wachtwoord" required>
                    </div>

                    <button type="submit" class="btn-submit">Account Aanmaken</button>
                    
                    <div class="switch-link">
                        Heb je al een account? <a href="/login">Log in</a>
                    </div>
                </form>
            </div>

            <div class="divider"><span>of</span></div>
            
            <div class="login-social">
                <button type="button" class="btn-social" onclick="window.location.href='/google_auth';">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                            <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                            <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                            <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                            <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                        </g>
                    </svg>
                    Google
                </button>

            </div>
        </div>
        
        <div class="footer-text">
            &copy; <?= date('Y') ?> Lineup. Alle rechten voorbehouden.
        </div>
    </div>

</body>
</html>
