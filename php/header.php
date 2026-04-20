<?php
// Default page title
$page_title = $page_title ?? 'Voetbal App';
$is_localhost = isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
?>
<?php
// Forceer NGINX (zoals SiteGround SuperCacher) en browsers om The Matrix Nooit te cachen!
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0"); // Proxies blockeren
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Datepicker -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    
    <!-- Custom Core Styles -->
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-light pb-5">
    <nav class="navbar navbar-expand-lg bg-dark mb-4 w-100 d-print-none" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fa-regular fa-futbol me-2"></i>Squadly</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="manage_games.php">
                        <i class="fa-regular fa-calendar-days me-2"></i>Wedstrijden
                    </a></li>
                    
                    <?php if ($is_localhost): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard_performance.php">
                        <i class="fa-solid fa-gauge-high me-2 text-warning"></i>Performance
                    </a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear me-2"></i>Settings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="edit_players.php"><i class="fa-solid fa-users me-2"></i>Spelers</a></li>
                            <li><a class="dropdown-item" href="edit_rankings.php"><i class="fa-solid fa-ranking-star me-2 fw-bold text-primary"></i>Rankings</a></li>
                            <li><a class="dropdown-item" href="edit_scores.php"><i class="fa-solid fa-star me-2"></i>Matrix (Old)</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
