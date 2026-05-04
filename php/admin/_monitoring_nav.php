<?php
// Monitoring navigatiebalk — geïnclude in alle admin monitoring pagina's
$current_page = basename($_SERVER['SCRIPT_FILENAME'], '.php');
$nav_items = [
    ['href' => '/admin',               'label' => 'Dashboard',        'icon' => 'fa-house',           'color' => 'secondary', 'key' => 'index'],
    ['href' => '/admin/feature_load',  'label' => 'Feature Load',     'icon' => 'fa-chart-area',      'color' => 'warning',   'key' => 'feature_load'],
    ['href' => '/admin/performance',   'label' => 'Server Logs',      'icon' => 'fa-server',          'color' => 'secondary', 'key' => 'performance'],
    ['href' => '/admin/performance_clients', 'label' => 'Client Telemetry', 'icon' => 'fa-mobile-screen', 'color' => 'info', 'key' => 'performance_clients'],
    ['href' => '/admin/feedback',      'label' => 'Feedback',         'icon' => 'fa-bug',             'color' => 'danger',    'key' => 'feedback'],
    ['href' => '/admin/users',         'label' => 'Gebruikers',       'icon' => 'fa-users',           'color' => 'primary',   'key' => 'users'],
    ['href' => '/admin/manage_schemas','label' => "Schema's",         'icon' => 'fa-diagram-project', 'color' => 'primary',   'key' => 'manage_schemas'],
    ['href' => '/admin/data_export',   'label' => 'GDPR Export',      'icon' => 'fa-shield-halved',   'color' => 'success',   'key' => 'data_export'],
];
?>
<div class="d-flex align-items-center gap-2 flex-wrap mb-4 pb-3 border-bottom border-dark">
    <?php foreach ($nav_items as $item):
        $is_active = ($current_page === $item['key']);
        $btn_class = $is_active
            ? "btn btn-sm btn-{$item['color']}"
            : "btn btn-sm btn-outline-{$item['color']}";
    ?>
    <a href="<?= $item['href'] ?>" class="<?= $btn_class ?>">
        <i class="fa-solid <?= $item['icon'] ?> me-1"></i> <?= htmlspecialchars($item['label']) ?>
    </a>
    <?php endforeach; ?>
</div>
