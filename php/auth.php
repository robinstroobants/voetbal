<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect user to subscription page if they try to perform actions with expired subscription
// But we allow GET requests across most read-pages
function enforce_subscription_write_access() {
    if (isset($_SESSION['is_read_only']) && $_SESSION['is_read_only'] === true) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
            header("Location: index.php?error=subscription_expired");
            exit;
        }
    }
}

enforce_subscription_write_access();
?>
