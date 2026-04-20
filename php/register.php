<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'getconn.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Alleen nodig als we geen invite hebben
    $team_name = trim($_POST['team_name'] ?? '');
    $default_format = trim($_POST['default_format'] ?? '8v8');
    
    $nameParts = explode(' ', $name, 2);
    $first_name = $nameParts[0];
    $last_name = $nameParts[1] ?? '';

    // Validatie
    $has_team_data = $invited_team_id ? true : ($team_name && $default_format);

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
                    
                    $stmtUser = $pdo->prepare("INSERT INTO users (email, first_name, last_name, password_hash, role, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, 0, ?)");
                    $stmtUser->execute([$email, $first_name, $last_name, $hash, $role, $token]);
                    $user_id = $pdo->lastInsertId();

                    if ($invited_team_id) {
                        // 2. Koppel rechtstreeks aan bestaand team (Workspace)
                        $team_id = $invited_team_id;
                        $pdo->prepare("INSERT IGNORE INTO user_teams (user_id, team_id) VALUES (?, ?)")->execute([$user_id, $team_id]);
                        
                        // En update users.team_id als anchor
                        $pdo->prepare("UPDATE users SET team_id = ? WHERE id = ?")->execute([$team_id, $user_id]);

                        // 3. Vernietig de invite token
                        $pdo->prepare("DELETE FROM team_invitations WHERE token = ?")->execute([$invite_token]);
                    } else {
                        // 2. Maak het team aan met een 1 maand trial
                        $valid_until = date('Y-m-d H:i:s', strtotime("+1 month"));
                        
                        $stmtTeam = $pdo->prepare("INSERT INTO teams (user_id, club_id, name, default_format, subscription_plan, subscription_valid_until, is_active) VALUES (?, 1, ?, ?, 'trial', ?, 1)");
                        $stmtTeam->execute([$user_id, $team_name, $default_format, $valid_until]);
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
                    $headers = "From: noreply@$host\r\n";
                    $headers .= "Reply-To: noreply@$host\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();

                    @mail($email, $subject, $message, $headers);

                    // Redirect naar login pagina met melding in plaats van direct in te loggen
                    header("Location: login.php?msg=registered");
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

                        <div class="form-group">
                            <select name="default_format" class="form-control" style="width: 100%; background: #ffffff; border: 1px solid var(--apple-border); color: var(--apple-text-main); padding: 16px; border-radius: 12px; font-size: 1rem; outline: none; appearance: auto;" required>
                                <option value="">Kies Wedstrijd Formaat</option>
                                <option value="11v11">11v11</option>
                                <option value="8v8_4x15">8v8 (4x15)</option>
                                <option value="8v8_3x20">8v8 (3x20)</option>
                                <option value="8v8_4x20">8v8 (4x20)</option>
                                <option value="8v8_5x15">8v8 (5x15)</option>
                                <option value="8v8_6x15">8v8 (6x15)</option>
                                <option value="8v8_7x15">8v8 (7x15)</option>
                                <option value="5v5_4x15">5v5 (4x15)</option>
                                <option value="3v3_6x10">3v3 (6x10)</option>
                                <option value="2v2_6x10">2v2 (6x10)</option>
                            </select>
                        </div>
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
                        Heb je al een account? <a href="login.php">Log in</a>
                    </div>
                </form>
            </div>

            <div class="divider"><span>of</span></div>
            
            <div class="login-social">
                <button type="button" class="btn-social" onclick="alert('Google Register in opbouw!');">
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
                <button type="button" class="btn-social" onclick="alert('Apple Register in opbouw!');">
                    <svg viewBox="0 0 24 24" fill="#000" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.19 2.31-.88 3.5-.88 1.5 0 2.8.62 3.5 1.5-3.03 1.92-2.52 5.5.5 6.67-1.12 2.6-2.6 5.5-2.58 4.88zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.36 2.38-1.92 4.39-3.74 4.25z"/>
                    </svg>
                    Apple
                </button>
            </div>
        </div>
        
        <div class="footer-text">
            &copy; <?= date('Y') ?> Lineup. Alle rechten voorbehouden.
        </div>
    </div>

</body>
</html>
