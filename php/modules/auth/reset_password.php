<?php
session_start();
require_once dirname(__DIR__, 2) . '/core/getconn.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';
$user = null;

if ($token) {
    // Check if token exists and is not expired
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

if (!$user && empty($_POST)) {
    $error = "Ongeldige of verlopen reset link. Vraag een nieuwe aan via de login pagina.";
} elseif ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = "Je nieuwe wachtwoord moet minimaal 6 tekens lang zijn.";
    } elseif ($password !== $password_confirm) {
        $error = "De ingevulde wachtwoorden komen niet overeen.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $update = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        if ($update->execute([$hash, $user['id']])) {
            header("Location: /login?msg=password_reset");
            exit;
        } else {
            $error = "Er liep iets mis bij het updaten van de database. Probeer het later opnieuw.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw Wachtwoord Instellen - Lineup</title>
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
            max-width: 440px;
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
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-wrap">
            <i class="fa-solid fa-key main-icon"></i>
            <h1>Nieuw Wachtwoord</h1>
            <p>Stel een nieuw, veilig wachtwoord in voor je Lineup account.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!$user && empty($_POST)): ?>
            <div class="switch-link mt-4">
                <a href="/forgot_password"><i class="fa-solid fa-arrow-left me-1"></i> Vraag een nieuwe link aan</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="password" name="password" placeholder="Nieuw wachtwoord" required autofocus>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password_confirm" placeholder="Herhaal wachtwoord" required>
                </div>
                
                <button type="submit" class="btn-submit">Wachtwoord opslaan</button>
            </form>
        <?php endif; ?>
        
        <div class="footer-text">
            &copy; <?= date('Y') ?> Lineup. Alle rechten voorbehouden.
        </div>
    </div>

</body>
</html>
