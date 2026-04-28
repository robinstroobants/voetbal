<?php
$page_title = "Feedback & Bugs - Admin";
require_once dirname(__DIR__) . '/header.php';

// Verwerk status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    if (in_array($status, ['open', 'resolved', 'ignored'])) {
        $stmt = $pdo->prepare("UPDATE user_feedback SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
    // Voorkom form resubmission
    header("Location: /admin/feedback");
    exit;
}

// Haal feedback op (inclusief details over team en user indien van toepassing)
$stmt = $pdo->query("
    SELECT f.*, u.first_name, u.last_name, u.email as user_email, t.name as team_name
    FROM user_feedback f
    LEFT JOIN users u ON f.user_id = u.id
    LEFT JOIN teams t ON f.team_id = t.id
    ORDER BY CASE WHEN f.status = 'open' THEN 0 ELSE 1 END, f.created_at DESC
");
$feedback_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-bug text-warning me-2"></i>Feedback & Bug Reports</h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Datum</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Gebruiker</th>
                            <th>Melding</th>
                            <th class="text-end">Actie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($feedback_items)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Geen feedback gevonden.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($feedback_items as $item): 
                                $badgeClass = 'bg-secondary';
                                if ($item['status'] === 'open') $badgeClass = 'bg-danger';
                                if ($item['status'] === 'resolved') $badgeClass = 'bg-success';
                                
                                $typeClass = 'text-dark';
                                if ($item['feedback_type'] === 'Bug') $typeClass = 'text-danger fw-bold';
                                if ($item['feedback_type'] === 'Idee') $typeClass = 'text-primary fw-bold';
                                
                                $userDisplay = 'Onbekend';
                                if ($item['user_id']) {
                                    $userDisplay = htmlspecialchars($item['first_name'] . ' ' . $item['last_name']);
                                    if ($item['team_name']) $userDisplay .= " <br><small class='text-muted'>(" . htmlspecialchars($item['team_name']) . ")</small>";
                                } else {
                                    // Zou ouders/public kunnen zijn
                                    $userDisplay = '<i class="fa-solid fa-earth-europe text-muted me-1" title="Publieke Share of Ongelogd"></i> Gast';
                                }
                            ?>
                            <tr class="<?= $item['status'] !== 'open' ? 'opacity-75 bg-light' : '' ?>">
                                <td class="small" style="white-space:nowrap;">
                                    <?= date('d/m/Y', strtotime($item['created_at'])) ?><br>
                                    <span class="text-muted"><?= date('H:i', strtotime($item['created_at'])) ?></span>
                                </td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($item['status'] ?? 'open') ?></span></td>
                                <td class="<?= $typeClass ?>"><?= htmlspecialchars($item['feedback_type']) ?></td>
                                <td><?= $userDisplay ?></td>
                                <td>
                                    <div style="max-width: 400px; max-height: 80px; overflow-y: auto; font-size: 0.9rem;">
                                        <?= nl2br(htmlspecialchars($item['description'])) ?>
                                    </div>
                                    <?php if (!empty($item['url'])): ?>
                                        <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" class="small text-decoration-none mt-1 d-inline-block"><i class="fa-solid fa-link me-1"></i>URL</a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/admin/feedback" class="d-inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="open" <?= ($item['status'] === 'open' || empty($item['status'])) ? 'selected' : '' ?>>Open</option>
                                            <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Opgelost</option>
                                            <option value="ignored" <?= $item['status'] === 'ignored' ? 'selected' : '' ?>>Negeren</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/footer.php'; ?>
