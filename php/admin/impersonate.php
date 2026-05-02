<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
        $stmt = $pdo->prepare("SELECT id, email, role, is_beta_user, first_name, last_active_team_id 
                               FROM users 
                               WHERE id = ? LIMIT 1");
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

            // Fetch Teams for target user
            $stmtWs = $pdo->prepare("SELECT ut.team_id, t.name, t.default_format, t.default_game_parts, t.subscription_valid_until FROM user_teams ut JOIN teams t ON ut.team_id = t.id WHERE ut.user_id = ?");
            $stmtWs->execute([$user['id']]);
            $teams = $stmtWs->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['available_teams'] = $teams;

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_beta_user'] = $user['is_beta_user'];
            $_SESSION['impersonated_first_name'] = $user['first_name'] ?: 'Coach';
            
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
