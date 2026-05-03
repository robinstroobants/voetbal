<?php
session_start();
require_once dirname(__DIR__, 2) . '/core/getconn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    if ($email) {
        $stmt = $pdo->prepare("SELECT id, first_name, account_status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Altijd de succesboodschap tonen om e-mail enumeratie te voorkomen
        $success = "Als dit e-mailadres bij ons geregistreerd staat, ontvang je spoedig verdere instructies in je mailbox.";
        
        if ($user && (!isset($user['account_status']) || $user['account_status'] !== 'pending')) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
            if ($update->execute([$token, $expires, $user['id']])) {
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                $reset_link = "$protocol://$host/reset_password?token=$token";
                
                $subject = "Wachtwoord herstellen - Lineup Heroes";
                $message = "Beste " . $user['first_name'] . ",\n\nEr is een verzoek ingediend om je wachtwoord te herstellen op Lineup Heroes.\nKlik op de onderstaande link om een nieuw wachtwoord in te stellen. Deze link is 1 uur geldig:\n$reset_link\n\nAls jij dit niet hebt aangevraagd, hoef je niets te doen en je account blijft veilig.\n\nMet vriendelijke groeten,\nHet Lineup Heroes Team";
                
                require_once dirname(__DIR__, 2) . '/core/Mailer.php';
                $mail_success = Mailer::send($email, $subject, $message);
                if (!$mail_success) {
                    error_log("Mail failed to send to $email.");
                }
            } else {
                error_log("Update query failed for user ID: " . $user['id']);
            }
        } else {
            error_log("No user found with email: $email");
        }
    } else {
        $error = "Vul een geldig e-mailadres in.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord Vergeten - Lineup Heroes</title>
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
        
        .success {
            background: #ebf5df;
            border: 1px solid #d4e8c1;
            color: #3b7b1e;
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
            <i class="fa-solid fa-lock main-icon"></i>
            <h1>Problemen bij inloggen?</h1>
            <p>Geen probleem. Vul je e-mailadres in en we sturen je een link om de toegang te herstellen.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><i class="fa-solid fa-envelope-circle-check me-1"></i> <?= htmlspecialchars($success) ?></div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="email" placeholder="E-mailadres" required autofocus>
                </div>
                
                <button type="submit" class="btn-submit">Stuur herstel link</button>
            </form>
        <?php endif; ?>

        <div class="switch-link mt-4">
            <a href="/login"><i class="fa-solid fa-arrow-left me-1"></i> Terug naar inloggen</a>
        </div>
        
        <div class="footer-text">
            &copy; <?= date('Y') ?> Lineup Heroes. Alle rechten voorbehouden.
        </div>
    </div>

</body>
</html>
