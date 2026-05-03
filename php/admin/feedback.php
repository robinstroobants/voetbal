<?php
$page_title = "Feedback & Bugs - Admin";
require_once dirname(__DIR__) . '/core/getconn.php';
require_once dirname(__DIR__) . '/core/Mailer.php';

const ADMIN_BCC = 'robin@webbit.be';

// Verwerk acties VOOR header.php (anders is output al gestuurd)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);

    if ($_POST['action'] === 'update_status') {
        $status = $_POST['status'];
        if (in_array($status, ['open', 'resolved', 'ignored'])) {
            // Haal huidige status + user email op vóór de update
            $stmtOld = $pdo->prepare("
                SELECT f.status, f.feedback_type, f.description, u.email, u.first_name
                FROM user_feedback f
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.id = ?
            ");
            $stmtOld->execute([$id]);
            $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("UPDATE user_feedback SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            // Stuur mail bij resolved, als er een email is en het nog niet resolved was
            $mailSent = false;
            if ($status === 'resolved' && ($old['status'] ?? '') !== 'resolved' && !empty($old['email'])) {
                $mailSent = true;
                $firstName  = htmlspecialchars($old['first_name'] ?? 'Coach');
                $type       = $old['feedback_type'] ?? 'Feedback';
                $isIdea     = strtolower($type) === 'idee';

                $subject = $isIdea
                    ? "Jouw idee werd uitgevoerd! 🎉"
                    : "Jouw bug werd opgelost! ✅";

                $intro = $isIdea
                    ? "Goed nieuws — jouw idee werd geïmplementeerd in de app!"
                    : "Goed nieuws — de bug die je meldde werd opgelost!";

                $cta = $isIdea
                    ? "Bekijk de nieuwe functionaliteit en laat ons weten wat je ervan vindt."
                    : "Je kunt de fix uittesten en ons laten weten als alles naar behoren werkt.";

                $originalText = htmlspecialchars($old['description'] ?? '');

                $body = "
                    <div style='font-family: sans-serif; max-width: 560px; margin: auto; color: #333;'>
                        <h2 style='color: #198754;'>" . ($isIdea ? "💡 Idee uitgevoerd" : "✅ Bug opgelost") . "</h2>
                        <p>Hallo {$firstName},</p>
                        <p>{$intro}</p>
                        <blockquote style='border-left: 3px solid #ccc; margin: 16px 0; padding: 8px 16px; color: #555; font-style: italic;'>
                            {$originalText}
                        </blockquote>
                        <p>{$cta}</p>
                        <p>
                            <a href='https://lineupheroes.com' style='background: #198754; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block;'>
                                Open de app
                            </a>
                        </p>
                        <hr style='margin-top: 32px; border: none; border-top: 1px solid #eee;'>
                        <p style='font-size: 0.8rem; color: #aaa;'>Lineup Heroes — jouw voetbalplanner</p>
                    </div>
                ";

                Mailer::send($old['email'], $subject, $body, true, ADMIN_BCC);
                $_SESSION['feedback_flash'] = 'Mail verstuurd naar ' . htmlspecialchars($old['email']) . '.';
            }
        }
    } elseif ($_POST['action'] === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM user_feedback WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Voorkom form resubmission
    header("Location: /admin/feedback");
    exit;
}

require_once dirname(__DIR__) . '/header.php';

// Haal feedback op (inclusief details over team en user indien van toepassing)
$stmt = $pdo->query("
    SELECT f.*, u.first_name, u.last_name, u.email as user_email, t.name as team_name
    FROM user_feedback f
    LEFT JOIN users u ON f.user_id = u.id
    LEFT JOIN teams t ON f.team_id = t.id
    ORDER BY CASE WHEN f.status = 'open' THEN 0 ELSE 1 END, f.created_at DESC
");
$feedback_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash notice
$flash = null;
if (!empty($_SESSION['feedback_flash'])) {
    $flash = $_SESSION['feedback_flash'];
    unset($_SESSION['feedback_flash']);
}

?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fa-solid fa-bug text-warning me-2"></i>Feedback &amp; Bug Reports</h2>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="statusFilter" id="filterOpen" value="open" checked>
            <label class="btn btn-outline-danger" for="filterOpen"><i class="fa-solid fa-circle-dot me-1"></i>Open</label>
            <input type="radio" class="btn-check" name="statusFilter" id="filterResolved" value="resolved">
            <label class="btn btn-outline-success" for="filterResolved"><i class="fa-solid fa-check me-1"></i>Opgelost</label>
            <input type="radio" class="btn-check" name="statusFilter" id="filterAll" value="all">
            <label class="btn btn-outline-secondary" for="filterAll">Alle</label>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="fa-solid fa-envelope-circle-check"></i>
        <span><?= htmlspecialchars($flash) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

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
                                $rowStatus = $item['status'] ?? 'open';
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
                                
                                $ua = $item['user_agent'] ?? '';
                                $deviceIcon = '<i class="fa-solid fa-desktop" title="Desktop"></i>';
                                if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
                                    $deviceIcon = '<i class="fa-solid fa-mobile-screen" title="Mobiel"></i>';
                                }
                                if (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) {
                                    $deviceIcon = '<i class="fa-solid fa-tablet-screen-button" title="Tablet"></i>';
                                }
                                
                                $os = 'Onbekend OS';
                                if (stripos($ua, 'Windows') !== false) $os = 'Windows';
                                elseif (stripos($ua, 'Mac OS') !== false) $os = 'MacOS';
                                elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
                                elseif (stripos($ua, 'Android') !== false) $os = 'Android';
                                elseif (stripos($ua, 'iPhone') !== false) $os = 'iOS';
                                elseif (stripos($ua, 'iPad') !== false) $os = 'iPadOS';
                                
                                // Extract screen size if appended
                                $screen = '';
                                if (preg_match('/Screen: (\d+x\d+)/', $ua, $matches)) {
                                    $screen = ' <span class="text-muted ms-1">(' . $matches[1] . ')</span>';
                                }

                                $deviceHtml = "<div class='mt-1'><span class='badge bg-light text-secondary border' title='" . htmlspecialchars($ua) . "'>$deviceIcon $os$screen</span></div>";
                            ?>
                            <tr class="<?= $item['status'] !== 'open' ? 'opacity-75 bg-light' : '' ?>" data-status="<?= htmlspecialchars($rowStatus) ?>">
                                <td class="small" style="white-space:nowrap;">
                                    <?= date('d/m/Y', strtotime($item['created_at'])) ?><br>
                                    <span class="text-muted"><?= date('H:i', strtotime($item['created_at'])) ?></span>
                                </td>
                                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($item['status'] ?? 'open') ?></span></td>
                                <td class="<?= $typeClass ?>"><?= htmlspecialchars($item['feedback_type']) ?></td>
                                <td><?= $userDisplay ?><?= $deviceHtml ?></td>
                                <td>
                                    <?php
                                        $desc = $item['description'];
                                        $isLong = mb_strlen($desc) > 120;
                                        $preview = $isLong ? mb_substr($desc, 0, 120) . '\u2026' : $desc;
                                        $typeLabel = htmlspecialchars($item['feedback_type']);
                                        $dateLabel = date('d/m/Y H:i', strtotime($item['created_at']));
                                        $urlVal = htmlspecialchars($item['url'] ?? '', ENT_QUOTES);
                                    ?>
                                    <?php if ($isLong): ?>
                                    <div
                                        style="max-width:400px; font-size:0.9rem; cursor:pointer;"
                                        title="Klik om volledig te lezen"
                                        onclick="showMessage(<?= htmlspecialchars(json_encode($desc), ENT_QUOTES) ?>, '<?= $typeLabel ?>', '<?= $dateLabel ?>', '<?= $urlVal ?>')"
                                    >
                                        <span style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($preview)) ?></span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:0.7rem;">Meer lezen</span>
                                    </div>
                                    <?php else: ?>
                                    <div style="max-width:400px; font-size:0.9rem; white-space:pre-wrap;"><?= nl2br(htmlspecialchars($desc)) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['url'])): ?>
                                        <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" class="small text-decoration-none mt-1 d-inline-block"><i class="fa-solid fa-link me-1"></i>URL</a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <form method="POST" action="/admin/feedback" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="open" <?= ($item['status'] === 'open' || empty($item['status'])) ? 'selected' : '' ?>>Open</option>
                                                <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Opgelost</option>
                                                <option value="ignored" <?= $item['status'] === 'ignored' ? 'selected' : '' ?>>Negeren</option>
                                            </select>
                                        </form>
                                        <form method="POST" action="/admin/feedback" class="d-inline"
                                              onsubmit="return confirm('Verwijder deze feedback definitief?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Verwijderen">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php if (!empty($item['error_log'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Bekijk error log"
                                            data-log="<?= htmlspecialchars($item['error_log'], ENT_QUOTES) ?>"
                                            onclick="showLog(this.dataset.log)">
                                            <i class="fa-solid fa-terminal"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
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

<!-- Error Log Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="logModalLabel"><i class="fa-solid fa-terminal me-2"></i>Error Log (laatste 10 regels)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <pre id="logModalContent" style="background:#1e1e1e; color:#d4d4d4; padding:20px; margin:0; font-size:0.78rem; white-space:pre-wrap; word-break:break-all; min-height:200px;"></pre>
      </div>
    </div>
  </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 bg-light">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="messageModalLabel"><i class="fa-solid fa-comment-dots me-2 text-primary"></i>Feedback Melding</h5>
          <small id="messageModalMeta" class="text-muted"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="messageModalBody" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.7;"></div>
        <div id="messageModalUrl" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<script>
function showLog(logContent) {
    document.getElementById('logModalContent').textContent = logContent || 'Geen log beschikbaar.';
    new bootstrap.Modal(document.getElementById('logModal')).show();
}
function showMessage(text, type, date, url) {
    document.getElementById('messageModalBody').textContent = text || '';
    document.getElementById('messageModalMeta').textContent = type + ' \u2014 ' + date;
    const urlEl = document.getElementById('messageModalUrl');
    if (url) {
        urlEl.innerHTML = '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-link me-1"></i>Open URL</a>';
    } else {
        urlEl.innerHTML = '';
    }
    new bootstrap.Modal(document.getElementById('messageModal')).show();
}

function applyFilter(value) {
    const rows = document.querySelectorAll('tbody tr[data-status]');
    let visible = 0;
    rows.forEach(row => {
        const show = value === 'all' || row.dataset.status === value;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    // Toon "geen resultaten" als alles verborgen is
    let emptyRow = document.getElementById('filterEmptyRow');
    if (visible === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'filterEmptyRow';
            emptyRow.innerHTML = '<td colspan="6" class="text-center py-4 text-muted">Geen meldingen gevonden voor dit filter.</td>';
            document.querySelector('tbody').appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Default: toon enkel open
    applyFilter('open');
    document.querySelectorAll('input[name="statusFilter"]').forEach(radio => {
        radio.addEventListener('change', () => applyFilter(radio.value));
    });
});
</script>

<?php require_once dirname(__DIR__) . '/footer.php'; ?>
