<?php
require_once '../getconn.php';
$email = $_GET['email'] ?? '';
if ($email) {
    $stmt = $pdo->prepare("SELECT id, email, is_verified, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        echo "User exists. Hash starts with: " . substr($user['password_hash'], 0, 10);
    } else {
        echo "User NOT found.";
    }
} else {
    echo "Provide email.";
}
