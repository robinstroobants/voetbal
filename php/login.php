<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

require_once 'getconn.php';

$error = '';
$msg_success = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'registered') {
        $msg_success = "Je account is aangemaakt! We hebben een activatielink gestuurd naar je e-mailadres.";
    } elseif ($_GET['msg'] === 'verified') {
        $msg_success = "Je e-mailadres is met succes geverifieerd. Je kan nu inloggen.";
    } elseif ($_GET['msg'] === 'password_reset') {
        $msg_success = "Je wachtwoord is succesvol gewijzigd. Je kan nu inloggen met je nieuwe wachtwoord.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT u.id, u.email, u.password_hash, u.role, u.team_id, u.is_verified, u.is_beta_user, t.name as team_name, t.default_format, t.subscription_valid_until 
                               FROM users u 
                               LEFT JOIN teams t ON u.team_id = t.id 
                               WHERE u.email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                $error = "Je account is nog niet geactiveerd. Controleer je e-mail inbox (of spam) voor de activatielink.";
            } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_beta_user'] = $user['is_beta_user'];
            
            // Fetch Workspaces (Multi-Team Support)
            $stmtWs = $pdo->prepare("SELECT ut.team_id, t.name, t.default_format, t.subscription_valid_until FROM user_teams ut JOIN teams t ON ut.team_id = t.id WHERE ut.user_id = ?");
            $stmtWs->execute([$user['id']]);
            $workspaces = $stmtWs->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['available_teams'] = $workspaces;

            $_SESSION['team_id'] = $user['team_id'];
            $_SESSION['team_name'] = $user['team_name'];
            $_SESSION['default_format'] = $user['default_format'] ?: '8v8';
            
            // Fallback to first available team if primary is missing
            if (!$_SESSION['team_id'] && !empty($workspaces)) {
                $_SESSION['team_id'] = $workspaces[0]['team_id'];
                $_SESSION['team_name'] = $workspaces[0]['name'];
                $_SESSION['default_format'] = $workspaces[0]['default_format'] ?: '8v8';
                $user['subscription_valid_until'] = $workspaces[0]['subscription_valid_until'];
            }
            
            // Check aubscription
            $validUntil = strtotime($user['subscription_valid_until']);
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
            }
        } else {
            $error = "Ongeldig emailadres of wachtwoord.";
        }
    } else {
        $error = "Vul alle velden in.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - Lineup</title>
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
            <h1>Inloggen op Lineup</h1>
            <p>Welkom terug, Coach.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($msg_success): ?>
            <div class="success" style="background: #ebf5df; border: 1px solid #d4e8c1; color: #3b7b1e; padding: 12px; border-radius: 10px; font-size: 0.9rem; margin-bottom: 24px; text-align: center;">
                <i class="fa-solid fa-circle-check me-1"></i> <?= htmlspecialchars($msg_success) ?>
            </div>
        <?php endif; ?>

        <div class="login-layout">
            <div class="login-main">
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="E-mailadres" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Wachtwoord" required>
                    </div>
                    
                    <div style="text-align: right; margin-bottom: 20px;">
                        <a href="forgot_password.php" style="color: var(--apple-blue); text-decoration: none; font-size: 0.85rem; font-weight: 500;">Wachtwoord vergeten?</a>
                    </div>

                    <button type="submit" class="btn-submit">Ga verder</button>
                    
                    <div class="switch-link">
                        Nog geen account? <a href="register.php">Ontdek Lineup</a>
                    </div>
                </form>
            </div>

            <div class="divider"><span>of</span></div>
            
            <div class="login-social">
                <button type="button" class="btn-social" onclick="alert('Google Login in opbouw!');">
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
                <button type="button" class="btn-social" onclick="alert('Apple Login in opbouw!');">
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
