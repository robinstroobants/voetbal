<?php
require_once dirname(__DIR__) . '/core/getconn.php';
require_once dirname(__DIR__) . '/core/Mailer.php';

// Zorg dat de tabel bestaat als workaround voor lokale migraties
$pdo->exec("
    CREATE TABLE IF NOT EXISTS user_feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        team_id INT NULL,
        feedback_type VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        url VARCHAR(255) NULL,
        user_agent TEXT NULL,
        error_log TEXT NULL,
        status ENUM('open','resolved','ignored') DEFAULT 'open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We verwachten JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }

    $type = $data['type'] ?? 'Bug';
    $description = $data['description'] ?? '';
    $url = $data['url'] ?? '';
    $userAgent = $data['userAgent'] ?? '';

    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Description is required']);
        exit;
    }

    $userId = $_SESSION['user_id'] ?? null;
    $teamId = $_SESSION['team_id'] ?? null;
    $teamName = $_SESSION['team_name'] ?? 'Onbekend';
    $parentEmail = $data['parentEmail'] ?? null;
    
    // Haal voornaam op uit database in plaats van session
    $firstName = 'Onbekend';
    if ($parentEmail) {
        $firstName = 'Ouder (' . $parentEmail . ')';
    } elseif ($userId) {
        $stmtU = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmtU->execute([$userId]);
        $userRow = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($userRow) {
            $firstName = trim($userRow['first_name'] . ' ' . $userRow['last_name']);
        }
    }

    // Haal error log op vóór insert (zodat we het kunnen opslaan)
    $errorLogContent = null;
    $possiblePaths = [
        dirname(__DIR__) . '/api/php_errors.log',   // Docker: geconfigureerd via conf.d/error_log.ini
        ini_get('error_log'),
        '/tmp/php_errors.log',
        dirname(__DIR__) . '/api/debug_events.log',
        dirname(__DIR__) . '/error_log',
        dirname(__DIR__) . '/php_errorlog',
        '/var/log/php_errors.log',
        '/Applications/XAMPP/logs/php_error_log',
        '/Applications/MAMP/logs/php_error.log',
        '/var/log/php-fpm/error.log',
    ];
    foreach ($possiblePaths as $path) {
        if (!empty($path) && file_exists($path) && is_readable($path)) {
            $lines = file($path);
            if ($lines !== false) {
                $errorLogContent = implode('', array_slice($lines, -10));
                break;
            }
        }
    }

    // Opslaan in database (inclusief error log)
    $stmt = $pdo->prepare("INSERT INTO user_feedback (user_id, team_id, feedback_type, description, url, user_agent, error_log) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $teamId, $type, $description, $url, $userAgent, $errorLogContent]);

    // Mail versturen naar admin
    $adminEmail = 'robin@webbit.be'; // Aangezien de mailer setup ook robin@webbit.be is.
    $subject = "Nieuwe Feedback: " . $type . " van " . $firstName;
    
    $body = "<h2>Nieuwe " . htmlspecialchars($type) . " Gemeld!</h2>";
    $body .= "<p><strong>Coach:</strong> " . htmlspecialchars($firstName) . " (Team: " . htmlspecialchars($teamName) . ")</p>";
    $body .= "<p><strong>URL:</strong> <a href='" . htmlspecialchars($url) . "'>" . htmlspecialchars($url) . "</a></p>";
    $body .= "<p><strong>Beschrijving:</strong><br/>" . nl2br(htmlspecialchars($description)) . "</p>";
    $body .= "<hr/>";
    $body .= "<p><small><strong>User Agent:</strong> " . htmlspecialchars($userAgent) . "</small></p>";

    // Voeg error log toe aan mail (reuse $errorLogContent van hierboven)
    $logForMail = $errorLogContent ? htmlspecialchars($errorLogContent) : 'Geen error log beschikbaar.';
    $body .= "<h3>Laatste 10 regels Error Log:</h3>";
    $body .= "<pre style='background:#f1f1f1; padding:10px; border:1px solid #ccc; font-size:11px; overflow-x:auto; color:#d63384;'>" . $logForMail . "</pre>";

    Mailer::send($adminEmail, $subject, $body, true);

    echo json_encode(['status' => 'success']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
