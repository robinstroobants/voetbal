<?php
// Gecentraliseerde Router en Auth Middleware
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Check of het IP adres op slot is
// We laden we de DB connectie als we dit op termijn nodig hebben, maar auth_check is hier voldoende
// Omdat require_once __DIR__ . '/core/getconn.php' al in de files gebeurt.
require_once __DIR__ . '/core/Permissions.php';

function enforce_subscription_write_access() {
    if (isset($_SESSION['is_read_only']) && $_SESSION['is_read_only'] === true) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
            header("Location: /?error=subscription_expired");
            exit;
        }
    }
}

function enforce_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login");
        exit;
    }
    enforce_subscription_write_access();
}

// enforce_role is nu vervangen door Permissions::enforce()

// 1. Parsing URI
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$path = rtrim($path, '/');
if (empty($path)) {
    $path = '/';
}

$route_matched = false;

// 2. Exact Static UI Routes
$routes = [
    '/' => ['target' => 'index.php', 'auth' => true],
    '/login' => ['target' => 'modules/auth/login.php', 'auth' => false],
    '/logout' => ['target' => 'modules/auth/logout.php', 'auth' => false],
    '/register' => ['target' => 'modules/auth/register.php', 'auth' => false],
    '/forgot_password' => ['target' => 'modules/auth/forgot_password.php', 'auth' => false],
    '/reset_password' => ['target' => 'modules/auth/reset_password.php', 'auth' => false],
    '/verify.php' => ['target' => 'modules/auth/verify.php', 'auth' => false],
    '/google_auth' => ['target' => 'modules/auth/google_auth.php', 'auth' => false],
    '/google_callback' => ['target' => 'modules/auth/google_callback.php', 'auth' => false],
    '/games' => ['target' => 'modules/games/manage_games.php', 'auth' => true, 'permission' => Permissions::PERM_MANAGE_GAMES],
    '/players' => ['target' => 'modules/players/edit_players.php', 'auth' => true, 'permission' => Permissions::PERM_MANAGE_PLAYERS],
    '/scores' => ['target' => 'modules/players/edit_scores.php', 'auth' => true, 'permission' => Permissions::PERM_EDIT_SCORES],
    '/stats' => ['target' => 'modules/players/stats.php', 'auth' => true, 'permission' => Permissions::PERM_MANAGE_GAMES],
    '/settings' => ['target' => 'settings.php', 'auth' => true, 'permission' => Permissions::PERM_MANAGE_TEAM_SETTINGS],
    '/settings/periods' => ['target' => 'manage_periods.php', 'auth' => true, 'permission' => Permissions::PERM_MANAGE_TEAM_SETTINGS]
];

if (isset($routes[$path])) {
    $r = $routes[$path];
    if ($r['auth']) enforce_auth();
    if (isset($r['permission'])) Permissions::enforce($r['permission']);
    
    require_once __DIR__ . '/' . $r['target'];
    $route_matched = true;
} else {
    // 3. Dynamic Parameter Routes via Regex
    
    // ADMIN SUB-DIRECTORY ROUTING
    if (preg_match('#^/admin$#', $path)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_TENANTS);
        require_once __DIR__ . '/admin/index.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/admin/schemas$#', $path)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_TENANTS);
        require_once __DIR__ . '/admin/manage_schemas.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/admin/([a-zA-Z0-9_\-]+)$#', $path, $matches)) {
        enforce_auth();
        if ($matches[1] === 'impersonate' && ($_GET['action'] ?? '') === 'stop' && isset($_SESSION['original_user_id'])) {
            // Bypass permission check when stopping impersonation
        } else {
            Permissions::enforce(Permissions::PERM_MANAGE_TENANTS);
        }
        $real_file = __DIR__ . '/admin/' . $matches[1] . '.php';
        if (file_exists($real_file)) {
            require_once $real_file;
            $route_matched = true;
        }
    }
    // OUDE GAME ROUTES
    elseif (preg_match('#^/games/(\d+)/edit$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_GAMES);
        $_GET['edit_game'] = $matches[1];
        require_once __DIR__ . '/modules/games/manage_games.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/schema$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_GENERATE_LINEUPS);
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/modules/schemas/schema_dashboard.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/lineup$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_GENERATE_LINEUPS);
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/modules/schemas/lineup.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/selection$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_GAMES);
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/modules/games/edit_selection.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/duplicate$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_GAMES);
        $_GET['duplicate_game'] = $matches[1];
        require_once __DIR__ . '/modules/games/manage_games.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/builder$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_GENERATE_LINEUPS);
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/modules/schemas/schema_builder.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/editor$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_GENERATE_LINEUPS);
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/modules/schemas/schema_editor.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/players/(\d+)/dashboard$#', $path, $matches)) {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_PLAYERS);
        $_GET['id'] = $matches[1];
        require_once __DIR__ . '/modules/players/player_dashboard.php';
        $route_matched = true;
    }
    elseif ($path === '/missing_coaches') {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_MANAGE_GAMES);
        require_once __DIR__ . '/modules/games/missing_coaches.php';
        $route_matched = true;
    }
    elseif ($path === '/schemas/wizard') {
        enforce_auth();
        Permissions::enforce(Permissions::PERM_USE_THEORY_WIZARD);
        require_once __DIR__ . '/modules/schemas/create_theory.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/share/([a-zA-Z0-9]+)$#', $path, $matches)) {
        $_GET['token'] = $matches[1];
        require_once __DIR__ . '/public_share.php';
        $route_matched = true;
    }
    // Expliciete API route fallbacks voor als browser JS files cacht of router ltrim() de path niet goed detecteert
    elseif ($path === '/api/api_save_lineup.php' || $path === '/api_save_lineup.php') {
        enforce_auth();
        require_once __DIR__ . '/api/api_save_lineup.php';
        $route_matched = true;
    }
    elseif ($path === '/api/api_save_schema.php' || $path === '/api_save_schema.php') {
        enforce_auth();
        require_once __DIR__ . '/api/api_save_schema.php';
        $route_matched = true;
    }
}

// 4. Fallback voor native requests (.php, scripts, API)
// Op deze manier breekt AJAX en form afhandeling niet onmiddellijk tijdens de migratie.
if (!$route_matched) {
    if (preg_match('/^\/([a-zA-Z0-9_\-]+)$/', $path, $m)) {
        $real_file = __DIR__ . '/' . $m[1] . '.php';
        if (file_exists($real_file)) {
            $public_files = ['login.php', 'register.php', 'logout.php', 'run_migrations.php', 'cron_cleanup.php', 'migrate_waitlist.php', 'migrate_usage.php'];
            if (!in_array(basename($real_file), $public_files)) {
                enforce_auth();
            }
            require_once $real_file;
            $route_matched = true;
        }
    }
}

if (!$route_matched) {
    if (preg_match('/\.php$/', $path)) {
        $real_file = __DIR__ . '/' . ltrim($path, '/');
        if (file_exists($real_file)) {
            $public_files = ['login.php', 'register.php', 'logout.php', 'run_migrations.php', 'cron_cleanup.php', 'migrate_waitlist.php', 'migrate_usage.php'];
            if (!in_array(basename($real_file), $public_files)) {
                enforce_auth();
            }
            require_once $real_file;
            $route_matched = true;
        }
    }
}

// 5. Laatste reddingsboei: 404
if (!$route_matched) {
    error_log("ROUTER 404: " . $_SERVER['REQUEST_METHOD'] . " " . $uri . " (path: " . $path . ")");
    http_response_code(404);
    echo "<h1 style='text-align:center; margin-top: 50px; font-family: sans-serif;'>404 Pagina Niet Gevonden</h1>";
    echo "<p style='text-align:center;'><a href='/'>Ga terug naar start</a></p>";
    exit;
}
