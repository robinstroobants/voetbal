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
    <link href="/css/styles.css" rel="stylesheet">
    <!-- Print Styles -->
    <link href="/css/print.css" rel="stylesheet" media="print">
</head>
<body class="bg-light pb-5">
    <?php if (isset($_SESSION['original_user_id'])): ?>
    <div class="alert alert-warning text-center fw-bold py-2 mb-0 rounded-0 border-bottom border-warning shadow-sm sticky-top d-print-none" style="z-index: 1050;">
        <i class="fa-solid fa-user-secret me-2"></i> Je bent actief als coach: <span class="text-dark bg-white px-2 py-1 rounded ms-1 border border-warning shadow-sm"><?= htmlspecialchars($_SESSION['impersonated_first_name'] ?? 'Coach') ?></span>
        <a href="/admin/impersonate?action=stop" class="btn btn-danger btn-sm ms-3 fw-bold rounded-pill shadow-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Terug naar Beheerders-weergave</a>
    </div>
    <?php endif; ?>
    <?php if (!defined('PUBLIC_SHARE_MODE')): ?>
    <nav class="navbar navbar-expand-lg bg-dark mb-4 w-100 d-print-none" data-bs-theme="dark">
        <div class="container">
            <?php $wsCount = count($_SESSION['available_teams'] ?? []); ?>
            <?php if ($wsCount > 1 && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin')): ?>
            <div class="dropdown">
                <a class="navbar-brand fw-bold text-truncate dropdown-toggle text-white" style="max-width: 220px; cursor: pointer;" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-regular fa-futbol me-2"></i><?= htmlspecialchars($_SESSION['team_name'] ?? 'Lineup') ?>
                </a>
                <ul class="dropdown-menu shadow">
                    <li class="dropdown-header text-uppercase fw-bold"><i class="fa-solid fa-layer-group me-1"></i> Workspaces</li>
                    <?php foreach($_SESSION['available_teams'] as $ws): ?>
                        <li>
                            <form method="POST" action="switch_team.php" class="m-0 p-0">
                                <input type="hidden" name="team_id" value="<?= $ws['team_id'] ?>">
                                <button type="submit" class="dropdown-item py-2 <?= ($ws['team_id'] == $_SESSION['team_id']) ? 'fw-bold bg-light' : '' ?>">
                                    <?= htmlspecialchars($ws['name']) ?>
                                    <?= ($ws['team_id'] == $_SESSION['team_id']) ? '<i class="fa-solid fa-check text-success ms-2"></i>' : '' ?>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
            <a class="navbar-brand fw-bold text-truncate" style="max-width: 220px;" href="/">
                <i class="fa-regular fa-futbol me-2"></i><?= htmlspecialchars($_SESSION['team_name'] ?? 'Lineup') ?>
            </a>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin'): ?>
                    <li class="nav-item"><a class="nav-link fw-bold" href="/">
                        <i class="fa-solid fa-house me-2"></i>Dashboard
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="/games">
                        <i class="fa-regular fa-calendar-days me-2"></i>Wedstrijden
                    </a></li>
                    <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="/schemas/wizard">
                        <i class="fa-solid fa-flask me-2"></i>Theorie
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="/stats">
                        <i class="fa-solid fa-chart-line me-2"></i>Statistieken
                    </a></li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-success" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user-shield me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="/admin"><i class="fa-solid fa-server me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/admin/users"><i class="fa-solid fa-users text-success me-2"></i>Gebruikers</a></li>
                            <li><a class="dropdown-item" href="/admin/schemas"><i class="fa-solid fa-sitemap text-primary me-2"></i>Schema Beheer</a></li>
                            <li><a class="dropdown-item" href="/admin/inspect_schema"><i class="fa-solid fa-stethoscope text-info me-2"></i>Schema Diagnose</a></li>
                            <li><a class="dropdown-item" href="/admin/performance"><i class="fa-solid fa-gauge-high text-warning me-2"></i>Performance</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-gear me-2"></i>Settings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="/players"><i class="fa-solid fa-users me-2"></i>Spelers</a></li>
                            
                            <?php 
                            $df = $_SESSION['default_format'] ?? '8v8';
                            if (strpos($df, '2v2') !== 0 && strpos($df, '3v3') !== 0): 
                            ?>
                            <li><a class="dropdown-item" href="/scores"><i class="fa-solid fa-star me-2"></i>Score Matrix</a></li>
                            <?php if (isset($_SESSION['is_beta_user']) && $_SESSION['is_beta_user'] == 1): ?>
                            <li><a class="dropdown-item" href="/edit_rankings"><i class="fa-solid fa-flask text-warning me-2"></i>Rankings <span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;">BETA</span></a></li>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/settings"><i class="fa-solid fa-sliders me-2"></i>Team</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item ms-3 d-flex align-items-center">
                        <a class="btn btn-sm btn-outline-danger shadow-sm" href="/logout">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; // End PUBLIC_SHARE_MODE check ?>
    
    <?php if (isset($_SESSION['is_read_only']) && $_SESSION['is_read_only'] === true): ?>
    <div class="alert alert-danger mx-3 text-center fw-bold shadow-sm" role="alert" style="border: 2px solid #dc3545;">
        <i class="fa-solid fa-lock me-2"></i> Uw abonnement is vervallen. U heeft enkel nog leestoegang tot uw gegevens. Neem contact op met de beheerder om uw account te vernieuwen.
    </div>
    <?php endif; ?>
