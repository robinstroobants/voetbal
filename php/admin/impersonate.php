<?php
session_start();
require_once dirname(__DIR__) . '/core/getconn.php';

$action = $_GET['action'] ?? '';

if ($action === 'start') {
    // Prevent non-superadmins and already impersonating users
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin' || isset($_SESSION['original_user_id'])) {
        header("Location: /");
        exit;
    }

    $target_user_id = (int)($_POST['target_user_id'] ?? 0);
    if ($target_user_id) {
        $stmt = $pdo->prepare("SELECT u.id, u.email, u.role, u.team_id, u.is_beta_user, t.name as team_name, t.default_format, t.default_game_parts, t.subscription_valid_until, u.first_name 
                               FROM users u 
                               LEFT JOIN teams t ON u.team_id = t.id 
                               WHERE u.id = ? LIMIT 1");
        $stmt->execute([$target_user_id]);
        $user = $stmt->fetch();

        if ($user) {
            // Save original superadmin session state
            $_SESSION['original_user_id'] = $_SESSION['user_id'];
            $_SESSION['original_role'] = $_SESSION['role'];
            $_SESSION['original_team_id'] = $_SESSION['team_id'] ?? null;
            $_SESSION['original_team_name'] = $_SESSION['team_name'] ?? null;
            $_SESSION['original_is_beta_user'] = $_SESSION['is_beta_user'] ?? 0;
            $_SESSION['original_default_format'] = $_SESSION['default_format'] ?? '8v8';
            $_SESSION['original_default_game_parts'] = $_SESSION['default_game_parts'] ?? '4x15';
            $_SESSION['original_is_read_only'] = $_SESSION['is_read_only'] ?? false;
            $_SESSION['original_available_teams'] = $_SESSION['available_teams'] ?? [];

            // Fetch Workspaces for target user
            $stmtWs = $pdo->prepare("SELECT ut.team_id, t.name, t.default_format, t.default_game_parts, t.subscription_valid_until FROM user_teams ut JOIN teams t ON ut.team_id = t.id WHERE ut.user_id = ?");
            $stmtWs->execute([$user['id']]);
            $workspaces = $stmtWs->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['available_teams'] = $workspaces;

            // Set session to target user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['team_id'] = $user['team_id'];
            $_SESSION['team_name'] = $user['team_name'];
            $_SESSION['is_beta_user'] = $user['is_beta_user'];
            $_SESSION['default_format'] = $user['default_format'] ?: '8v8';
            $_SESSION['default_game_parts'] = $user['default_game_parts'] ?: '4x15';
            $_SESSION['impersonated_first_name'] = $user['first_name'] ?: 'Coach';
            
            if (!$_SESSION['team_id'] && !empty($workspaces)) {
                $_SESSION['team_id'] = $workspaces[0]['team_id'];
                $_SESSION['team_name'] = $workspaces[0]['name'];
                $_SESSION['default_format'] = $workspaces[0]['default_format'] ?: '8v8';
                $_SESSION['default_game_parts'] = $workspaces[0]['default_game_parts'] ?: '4x15';
                $user['subscription_valid_until'] = $workspaces[0]['subscription_valid_until'];
            }
            
            $validUntil = strtotime($user['subscription_valid_until']);
            if ($user['role'] !== 'superadmin' && $validUntil < time()) {
                $_SESSION['is_read_only'] = true;
            } else {
                $_SESSION['is_read_only'] = false;
            }

            header("Location: /");
            exit;
        }
    }
    header("Location: /admin");
    exit;

} elseif ($action === 'stop') {
    if (isset($_SESSION['original_user_id'])) {
        // Restore session
        $_SESSION['user_id'] = $_SESSION['original_user_id'];
        $_SESSION['role'] = $_SESSION['original_role'];
        $_SESSION['team_id'] = $_SESSION['original_team_id'];
        $_SESSION['team_name'] = $_SESSION['original_team_name'];
        $_SESSION['is_beta_user'] = $_SESSION['original_is_beta_user'];
        $_SESSION['default_format'] = $_SESSION['original_default_format'];
        $_SESSION['default_game_parts'] = $_SESSION['original_default_game_parts'];
        $_SESSION['is_read_only'] = $_SESSION['original_is_read_only'];
        $_SESSION['available_teams'] = $_SESSION['original_available_teams'];

        // Cleanup
        unset($_SESSION['original_user_id']);
        unset($_SESSION['original_role']);
        unset($_SESSION['original_team_id']);
        unset($_SESSION['original_team_name']);
        unset($_SESSION['original_is_beta_user']);
        unset($_SESSION['original_default_format']);
        unset($_SESSION['original_default_game_parts']);
        unset($_SESSION['original_is_read_only']);
        unset($_SESSION['original_available_teams']);
        unset($_SESSION['impersonated_first_name']);

        header("Location: /admin");
        exit;
    }
    header("Location: /");
    exit;
}
?>
