<?php
// Gecentraliseerde Router en Auth Middleware
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Check of het IP adres op slot is
// We laden we de DB connectie als we dit op termijn nodig hebben, maar auth_check is hier voldoende
// Omdat require_once 'getconn.php' al in de files gebeurt.

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

function enforce_role($required_role) {
    if ($required_role === 'superadmin') {
        $role = $_SESSION['role'] ?? '';
        $original_role = $_SESSION['original_role'] ?? '';
        if ($role !== 'superadmin' && $original_role !== 'superadmin') {
            header("Location: /");
            exit;
        }
    }
}

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
    '/login' => ['target' => 'login.php', 'auth' => false],
    '/logout' => ['target' => 'logout.php', 'auth' => false],
    '/register' => ['target' => 'register.php', 'auth' => false],
    '/games' => ['target' => 'manage_games.php', 'auth' => true],
    '/players' => ['target' => 'edit_players.php', 'auth' => true],
    '/scores' => ['target' => 'edit_scores.php', 'auth' => true],
    '/stats' => ['target' => 'stats.php', 'auth' => true],
    '/settings' => ['target' => 'settings.php', 'auth' => true]
];

if (isset($routes[$path])) {
    $r = $routes[$path];
    if ($r['auth']) enforce_auth();
    if (isset($r['role'])) enforce_role($r['role']);
    
    require_once __DIR__ . '/' . $r['target'];
    $route_matched = true;
} else {
    // 3. Dynamic Parameter Routes via Regex
    
    // ADMIN SUB-DIRECTORY ROUTING
    if (preg_match('#^/admin$#', $path)) {
        enforce_auth();
        enforce_role('superadmin');
        require_once __DIR__ . '/admin/index.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/admin/schemas$#', $path)) {
        enforce_auth();
        enforce_role('superadmin');
        require_once __DIR__ . '/admin/manage_schemas.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/admin/([a-zA-Z0-9_\-]+)$#', $path, $matches)) {
        enforce_auth();
        enforce_role('superadmin');
        $real_file = __DIR__ . '/admin/' . $matches[1] . '.php';
        if (file_exists($real_file)) {
            require_once $real_file;
            $route_matched = true;
        }
    }
    // OUDE GAME ROUTES
    elseif (preg_match('#^/games/(\d+)/edit$#', $path, $matches)) {
        enforce_auth();
        $_GET['edit_game'] = $matches[1];
        require_once __DIR__ . '/manage_games.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/lineup$#', $path, $matches)) {
        enforce_auth();
        // inject parameter for legacy lineup.php
        $_GET['wedstrijd'] = $matches[1];
        require_once __DIR__ . '/lineup.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/selection$#', $path, $matches)) {
        enforce_auth();
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/edit_selection.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/duplicate$#', $path, $matches)) {
        enforce_auth();
        $_GET['duplicate_game'] = $matches[1];
        require_once __DIR__ . '/manage_games.php';
        $route_matched = true;
    }
    elseif (preg_match('#^/games/(\d+)/builder$#', $path, $matches)) {
        enforce_auth();
        $_GET['game_id'] = $matches[1];
        require_once __DIR__ . '/schema_builder.php';
        $route_matched = true;
    }
}

// 4. Fallback voor native requests (.php, scripts, API)
// Op deze manier breekt AJAX en form afhandeling niet onmiddellijk tijdens de migratie.
if (!$route_matched) {
    if (preg_match('/^\/([a-zA-Z0-9_\-]+)$/', $path, $m)) {
        $real_file = __DIR__ . '/' . $m[1] . '.php';
        if (file_exists($real_file)) {
            $public_files = ['login.php', 'register.php', 'logout.php', 'run_migrations.php', 'cron_cleanup.php'];
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
            $public_files = ['login.php', 'register.php', 'logout.php', 'run_migrations.php', 'cron_cleanup.php'];
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
    http_response_code(404);
    echo "<h1 style='text-align:center; margin-top: 50px; font-family: sans-serif;'>404 Pagina Niet Gevonden</h1>";
    echo "<p style='text-align:center;'><a href='/'>Ga terug naar start</a></p>";
    exit;
}
