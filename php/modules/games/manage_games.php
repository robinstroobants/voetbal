<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';

// Verwerk acties: Toevoegen, Bewerken, Verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_POST['game_id'])) {
        $check = $pdo->prepare("SELECT id FROM games WHERE id = :id AND team_id = :team_id");
        $check->execute(['id' => $_POST['game_id'], 'team_id' => $_SESSION['team_id']]);
        if ($check->fetchColumn()) {
            $pdo->prepare("DELETE FROM game_lineups WHERE game_id = :id")->execute(['id' => $_POST['game_id']]);
            $pdo->prepare("DELETE FROM game_selections WHERE game_id = :id")->execute(['id' => $_POST['game_id']]);
            $pdo->prepare("DELETE FROM games WHERE id = :id")->execute(['id' => $_POST['game_id']]);
        }
    } elseif ($action === 'save') {
        $gameId = !empty($_POST['game_id']) ? (int)$_POST['game_id'] : null;
        $opponent = trim($_POST['opponent']);
        
        $gameDateInput = $_POST['game_date'];
        $gameTimeInput = !empty($_POST['game_time']) ? trim($_POST['game_time']) : '00:00:00';
        if (strlen($gameTimeInput) === 5) {
            $gameTimeInput .= ':00'; // Append seconds for MySQL format
        }
        $gameDate = $gameDateInput . ' ' . $gameTimeInput;

        $baseFormat = $_POST['format'];
        $gameParts = $_POST['game_parts'];
        $format = $baseFormat . '_' . $gameParts;
        
        $minPos = isset($_POST['min_pos']) ? (int)$_POST['min_pos'] : 0;
        // team_id = 1 as default for now
        $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
        $isHome = isset($_POST['is_home']) ? (int)$_POST['is_home'] : 1;
        
        if ($gameId) {
            // Controleer of layout/format of coach veranderd is ten opzichte van current
            $stmtCheck = $pdo->prepare("SELECT format, coach_id FROM games WHERE id = ?");
            $stmtCheck->execute([$gameId]);
            $oldData = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $oldFormat = $oldData['format'] ?? null;
            $oldCoachId = $oldData['coach_id'] ?? null;

            if ($oldFormat !== $format) {
                // Formaat is gewijzigd, schema is nu nutteloos, opruimen!
                $pdo->prepare("DELETE FROM game_lineups WHERE game_id = ?")->execute([$gameId]);
            }

            $stmt = $pdo->prepare("UPDATE games SET opponent = :opp, is_home = :is_home, game_date = :gd, format = :fmt, min_pos = :mpos, coach_id = :cid WHERE id = :id");
            $stmt->execute(['opp' => $opponent, 'is_home' => $isHome, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos, 'cid' => $coachId, 'id' => $gameId]);
            
            // Als de coach gewijzigd is, werk dan ook de logs bij (voor historische correctie)
            if ($oldCoachId != $coachId) {
                $stmtLogs = $pdo->prepare("UPDATE game_playtime_logs SET coach_id = :cid WHERE game_id = :id");
                $stmtLogs->execute(['cid' => $coachId, 'id' => $gameId]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO games (team_id, opponent, is_home, game_date, format, min_pos, coach_id) VALUES (:team_id, :opp, :is_home, :gd, :fmt, :mpos, :cid)");
            $stmt->execute(['team_id' => $_SESSION['team_id'], 'opp' => $opponent, 'is_home' => $isHome, 'gd' => $gameDate, 'fmt' => $format, 'mpos' => $minPos, 'cid' => $coachId]);
            $newGameId = $pdo->lastInsertId();

            // Duplicatie verwerken indien gevraagd
            $sourceGameId = !empty($_POST['source_game_id']) ? (int)$_POST['source_game_id'] : null;
            if ($sourceGameId) {
                // Veiligheidscheck team
                $check = $pdo->prepare("SELECT id FROM games WHERE id = ? AND team_id = ?");
                $check->execute([$sourceGameId, $_SESSION['team_id']]);
                if ($check->fetchColumn()) {
                    $pdo->prepare("INSERT INTO game_selections (game_id, player_id, status_id, is_goalkeeper) 
                                   SELECT ?, player_id, 2, is_goalkeeper FROM game_selections WHERE game_id = ?")
                        ->execute([$newGameId, $sourceGameId]);
                }
            }
        }
    }
    // Voorkom form resubmission bij refresh
    header("Location: /games");
    exit;
}

// Haal wedstrijden op
$stmt = $pdo->prepare("
    SELECT g.*, CONCAT(c.first_name, ' ', c.last_name) AS coach_name, c.first_name AS coach_first_name,
        (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
        (SELECT GROUP_CONCAT(gs.player_id) FROM game_selections gs WHERE gs.game_id = g.id) as selected_player_ids,
        (SELECT id FROM game_lineups gl WHERE gl.game_id = g.id AND gl.is_final = 1 LIMIT 1) as final_lineup_id
    FROM games g 
    LEFT JOIN users c ON g.coach_id = c.id
    WHERE g.team_id = ? AND g.is_theory = 0
    ORDER BY (g.coach_id IS NULL OR c.first_name IS NULL) DESC, g.game_date DESC
");
$stmt->execute([$_SESSION['team_id']]);
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupedGames = [];
$groupedByWeek = [];

$stmtP = $pdo->prepare("SELECT id, first_name, last_name FROM players WHERE team_id = ?");
$stmtP->execute([$_SESSION['team_id']]);
$players = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$firstNamesCount = [];
foreach ($players as $p) {
    $firstNamesCount[$p['first_name']] = ($firstNamesCount[$p['first_name']] ?? 0) + 1;
}

$playerDisplayNames = [];
foreach ($players as $p) {
    if ($firstNamesCount[$p['first_name']] > 1) {
        $playerDisplayNames[$p['id']] = $p['first_name'] . ' ' . substr($p['last_name'], 0, 1) . '.';
    } else {
        $playerDisplayNames[$p['id']] = $p['first_name'];
    }
}
$players_count = count($players);

$stmtC = $pdo->prepare("SELECT COUNT(*) FROM coaches WHERE team_id = ?");
$stmtC->execute([$_SESSION['team_id']]);
$coaches_count = (int)$stmtC->fetchColumn();

$stmtF = $pdo->prepare("SELECT default_format FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$default_format = $stmtF->fetchColumn() ?: '8v8';

$required_players = 8;
if (preg_match('/^(\d+)v\d+/', $default_format, $matches)) {
    $required_players = (int)$matches[1];
}

$onboarding_complete = ($players_count >= $required_players);
$missing_coaches_count = 0;

foreach ($games as $game) {
    if (empty(trim((string)$game['coach_name']))) {
        $missing_coaches_count++;
    }

    $time = strtotime($game['game_date']);
    $year = (int)date('Y', $time);
    $month = (int)date('n', $time);
    
    if ($month >= 7) {
        $season = "Seizoen " . $year . "-" . ($year + 1);
        $phase = "Najaarsronde (Fase 1)"; // Jul - Dec
    } else {
        $season = "Seizoen " . ($year - 1) . "-" . $year;
        $phase = "Voorjaarsronde (Fase 2)"; // Jan - Jun
    }
    
    if (!isset($groupedGames[$season])) $groupedGames[$season] = [];
    if (!isset($groupedGames[$season][$phase])) $groupedGames[$season][$phase] = [];
    
    $groupedGames[$season][$phase][] = $game;
    
    // Groepeer per week (ISO)
    $weekNum = date('W', $time);
    $yearNum = date('o', $time); // ISO-8601 year number (zorgt dat week 1 van volgend jaar goed gaat)
    
    // Bepaal de maandag en zondag van die week voor de label
    $dt = new DateTime();
    $dt->setISODate($yearNum, $weekNum);
    $monday = $dt->format('d/m');
    $dt->modify('+6 days');
    $sunday = $dt->format('d/m');
    
    $sortKey = $yearNum . $weekNum;
    if (!isset($groupedByWeek[$sortKey])) {
        $groupedByWeek[$sortKey] = [
            'label' => "Week $weekNum ($monday - $sunday)",
            'games' => []
        ];
    }
    $groupedByWeek[$sortKey]['games'][] = $game;
}

// Sorteer de weken van nieuw naar oud
krsort($groupedByWeek);

// Ophalen van beschikbare formats uit DB voor JS
$stmtFormats = $pdo->query("SELECT DISTINCT game_format FROM lineups");
$available_parts_by_format = [];
while ($row = $stmtFormats->fetchColumn()) {
    if (preg_match('/^(\d+v\d+)_(\d+gk_)?(\d+x\d+)$/', $row, $matches)) {
        $f = $matches[1];
        $p = $matches[3];
        if (!isset($available_parts_by_format[$f])) {
            $available_parts_by_format[$f] = [];
        }
        if (!in_array($p, $available_parts_by_format[$f])) {
            $available_parts_by_format[$f][] = $p;
        }
    }
}
$json_available_parts = json_encode($available_parts_by_format);

$available_formats_all = [
    '11v11', '8v8', '5v5', '3v3', '2v2'
];

function getFormatLevel($fmtStr) {
    if (strpos($fmtStr, '2v2') === 0) return 1;
    if (strpos($fmtStr, '3v3') === 0) return 2;
    if (strpos($fmtStr, '5v5') === 0) return 3;
    if (strpos($fmtStr, '8v8') === 0) return 4;
    if (strpos($fmtStr, '11v11') === 0) return 5;
    return 0;
}

$team_level = getFormatLevel($default_format);
$available_formats = [];

foreach ($available_formats_all as $fmt) {
    $fmt_level = getFormatLevel($fmt);
    // Criterium: Alleen hetzelfde niveau OF maximaal exact 1 niveau hoger 
    if ($fmt_level == $team_level || $fmt_level == $team_level + 1) {
        $available_formats[] = $fmt;
    }
}

// Haal beschikbare coaches op (de effectieve SaaS gebruikers/coaches gekoppeld aan dit team)
$stmtC = $pdo->prepare("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name 
    FROM users u 
    INNER JOIN user_teams ut ON u.id = ut.user_id 
    WHERE ut.team_id = ? 
    ORDER BY u.first_name ASC
");
$stmtC->execute([$_SESSION['team_id']]);
$coachesData = $stmtC->fetchAll(PDO::FETCH_ASSOC);

// Definieer een palet aan onderscheidende kleuren
$badgeColors = ['bg-info text-dark', 'bg-danger', 'bg-success', 'bg-warning text-dark', 'bg-primary', 'bg-dark text-white'];
$coachColorMap = [];
foreach ($coachesData as $index => $cData) {
    $coachColorMap[$cData['name']] = $badgeColors[$index % count($badgeColors)];
}

$page_title = 'Game Management';
require_once dirname(__DIR__, 2) . '/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Wedstrijd Beheer</h2>
        <?php if ($onboarding_complete): ?>
            <div>
                <?php if (Permissions::hasPermission(Permissions::PERM_USE_THEORY_WIZARD)): ?>
                <a href="/schemas/wizard" class="btn btn-outline-warning shadow-sm me-2 fw-bold text-dark">
                    <i class="fa-solid fa-flask me-2"></i>Schema Ontwerpen
                </a>
                <?php endif; ?>
                <button class="btn btn-primary shadow-sm" onclick="openGameModal()">
                    <i class="fa-solid fa-plus me-2"></i>Nieuwe Wedstrijd
                </button>
            </div>
        <?php else: ?>
            <button class="btn btn-secondary disabled opacity-75" title="Doorloop eerst de onboarding op het dashboard">
                <i class="fa-solid fa-lock me-2"></i>Team Incompleet
            </button>
        <?php endif; ?>
    </div>

    <?php if (!$onboarding_complete): ?>
    <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4 fw-bold mb-4">
        <i class="fa-solid fa-triangle-exclamation text-warning fs-5 align-middle me-2"></i> 
        Je voldoet nog niet aan de ploegvereisten (minstens <?= $required_players ?> spelers).
        <a href="/" class="alert-link text-decoration-underline ms-2">Keer terug naar het dashboard</a> om je inschrijving af te ronden!
    </div>
    <?php endif; ?>

    <?php if ($missing_coaches_count > 0): ?>
    <div class="alert alert-info shadow-sm border-0 border-start border-info border-4 mb-4">
        <i class="fa-solid fa-circle-info text-info fs-5 align-middle me-2"></i> 
        Er zijn <strong><?= $missing_coaches_count ?> wedstrijden</strong> (bovenaan gesorteerd) waaraan nog geen coach is toegewezen. 
        Koppel de juiste coach zodat de persoonlijke statistieken correct berekend kunnen worden. 
        <a href="/missing_coaches" class="alert-link ms-2 text-decoration-underline"><i class="fa-solid fa-wrench"></i> Los ze snel hier op</a>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-3 g-2 align-items-center">
        <div class="col-md-5 col-12">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" id="gameSearch" class="form-control border-start-0" placeholder="Zoek op tegenstander of datum...">
            </div>
        </div>
        <div class="col-md-4 col-12">
            <select id="coachFilter" class="form-select shadow-sm">
                <option value="">Alle coaches (geen filter)</option>
                <option value="NO_COACH">Geen coach toegewezen</option>
                <?php foreach($coachesData as $coach): ?>
                    <option value="<?= htmlspecialchars($coach['name']) ?>"><?= htmlspecialchars($coach['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 col-12 text-md-end text-center mt-3 mt-md-0">
            <div class="form-check form-switch d-inline-block">
                <input class="form-check-input" type="checkbox" id="groupByWeekToggle">
                <label class="form-check-label fw-bold text-secondary" style="font-size: 0.9rem;" for="groupByWeekToggle">Groepeer per week</label>
            </div>
        </div>
    </div>

    <!-- Container for both views -->
    <div id="viewSeasonPhase">
        <!-- Games Overzicht Gegroepeerd -->
        <div class="accordion" id="seasonAccordion">
        <?php if(empty($games)): ?>
            <div class="alert alert-light text-center border">
                Geen wedstrijden gevonden! Tijd om er eentje te plannen.
            </div>
        <?php endif; ?>

        <?php 
        $season_counter = 0;
        foreach($groupedGames as $season => $phases): 
            $is_first_season = ($season_counter === 0);
        ?>
        <div class="accordion-item mb-3 border-0 shadow-sm rounded overflow-hidden">
            <h2 class="accordion-header" id="heading<?= $season_counter ?>">
                <button class="accordion-button <?= $is_first_season ? '' : 'collapsed' ?> bg-white fw-bold fs-5 text-primary border-bottom" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $season_counter ?>" aria-expanded="<?= $is_first_season ? 'true' : 'false' ?>" aria-controls="collapse<?= $season_counter ?>">
                    <i class="fa-solid fa-trophy me-2"></i> <?= htmlspecialchars($season) ?>
                </button>
            </h2>
            <div id="collapse<?= $season_counter ?>" class="accordion-collapse collapse <?= $is_first_season ? 'show' : '' ?>" aria-labelledby="heading<?= $season_counter ?>" data-bs-parent="#seasonAccordion">
                <div class="accordion-body p-0 bg-white">
                    
                    <?php 
                    // Voorjaar ligt later en komt dus boven Najaar bij DESC sorting
                    foreach(['Voorjaarsronde (Fase 2)', 'Najaarsronde (Fase 1)'] as $phase): 
                        if(!empty($phases[$phase])):
                    ?>
                    <div class="bg-light py-2 px-4 border-bottom fw-semibold text-secondary d-flex align-items-center">
                        <i class="fa-regular fa-calendar-days me-2"></i> <?= $phase ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-bottom">
                            <tbody>
                                <?php foreach($phases[$phase] as $game): ?>
                                <tr class="game-row" data-coach="<?= htmlspecialchars($game['coach_name'] ?: 'NO_COACH') ?>">
                                    <td class="ps-4 fw-medium text-muted date-cell" style="width: 15%">
                                        <?php 
                                            $t_date = strtotime($game['game_date']);
                                            $has_time = date('H:i:s', $t_date) !== '00:00:00';
                                            $is_future = $t_date >= strtotime('today');
                                            echo date('d/m/Y', $t_date);
                                        ?>
                                        <?php if ($has_time): ?>
                                            <br><small><i class="fa-regular fa-clock"></i> <?= date('H:i', $t_date) ?></small>
                                        <?php elseif ($is_future): ?>
                                            <br><a href="#" onclick="openGameModal(<?= htmlspecialchars(json_encode($game), ENT_QUOTES, 'UTF-8') ?>); return false;" class="text-danger fw-bold small text-decoration-none" title="Tijd instellen!"><i class="fa-solid fa-triangle-exclamation"></i> Tijd?</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-dark opp-cell">
                                        <?php if($game['coach_name']): 
                                            $cColor = isset($coachColorMap[$game['coach_name']]) ? $coachColorMap[$game['coach_name']] : 'bg-secondary text-white';
                                        ?>
                                            <span class="badge <?= $cColor ?> rounded-pill me-1"><?= htmlspecialchars($game['coach_first_name']) ?></span>
                                        <?php endif; ?>
                                        <a href="#" onclick="openGameModal(<?= htmlspecialchars(json_encode($game), ENT_QUOTES, 'UTF-8') ?>); return false;" class="text-decoration-none text-dark hover-primary" title="Bewerk Wedstrijd">
                                            <?php if(isset($game['is_home']) && $game['is_home'] == 0): ?>
                                                <i class="fa-solid fa-plane text-secondary me-1" title="Uit"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-house text-primary me-1" title="Thuis"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($game['opponent']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if($game['selection_count'] > 0): 
                                            $sel_ids = $game['selected_player_ids'] ? explode(',', $game['selected_player_ids']) : [];
                                            $names = [];
                                            foreach($sel_ids as $sid) {
                                                if (isset($playerDisplayNames[$sid])) {
                                                    $names[] = $playerDisplayNames[$sid];
                                                }
                                            }
                                            $names_str = implode(', ', $names);
                                        ?>
                                            <div class="d-flex align-items-center">
                                                <a href="/games/<?= $game['id'] ?>/selection" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 shadow-sm me-2 text-decoration-none" title="Beheer Selectie">
                                                    <i class="fa-solid fa-users me-1"></i>&nbsp;<?= $game['selection_count'] ?>
                                                </a>
                                                <span class="small text-muted" style="line-height:1.2; display:inline-block; max-width:250px; white-space:normal;"><?= htmlspecialchars($names_str) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <a href="/games/<?= $game['id'] ?>/selection" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 shadow-sm text-decoration-none" title="Maak Selectie">
                                                <i class="fa-solid fa-users me-1"></i>&nbsp;0
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4" style="width: 25%">
                                        <a href="/games/<?= $game['id'] ?>/duplicate" class="btn btn-sm btn-outline-warning me-1" title="Dupliceer met Selectie">
                                            <i class="fa-solid fa-copy"></i>
                                        </a>
                                        <a href="/games/<?= $game["id"] ?>/schema" class="btn btn-sm btn-outline-primary me-1 <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="<?= !empty($game['final_lineup_id']) ? 'Bekijk Opstelling' : 'Bereken Opstelling' ?>">
                                            <?php if(!empty($game['final_lineup_id'])): ?>
                                                <i class="fa-solid fa-eye"></i> Opstelling
                                            <?php else: ?>
                                                <i class="fa-solid fa-wand-magic-sparkles"></i> Opstelling
                                            <?php endif; ?>
                                        </a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Wedstrijd verwijderen? Dit wist ook alle direct gekoppelde selecties.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Verwijder">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        <?php 
            $season_counter++;
            endforeach; 
        ?>
    </div>
    </div> <!-- End viewSeasonPhase -->

    <div id="viewByWeek" style="display: none;">
        <?php if(empty($groupedByWeek)): ?>
            <div class="alert alert-light text-center border">
                Geen wedstrijden gevonden!
            </div>
        <?php endif; ?>
        <?php foreach($groupedByWeek as $week): ?>
            <div class="card shadow-sm border-0 mb-4 week-card">
                <div class="card-header bg-light py-2 border-bottom fw-bold text-secondary">
                    <i class="fa-regular fa-calendar-week me-2"></i> <?= htmlspecialchars($week['label']) ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php foreach($week['games'] as $game): ?>
                                <tr class="game-row" data-coach="<?= htmlspecialchars($game['coach_name'] ?: 'NO_COACH') ?>">
                                    <td class="ps-4 fw-medium text-muted date-cell playdate-cell" title="<?= date('d/m/Y', strtotime($game['game_date'])) ?>">
                                        <span><?php 
                                            $t_date = strtotime($game['game_date']);
                                            $has_time = date('H:i:s', $t_date) !== '00:00:00';
                                            $is_future = $t_date >= strtotime('today');
                                            echo date('d/m', $t_date);
                                        ?></span>
                                        <?php if ($has_time): ?>
                                            <br><small><i class="fa-regular fa-clock"></i> <?= date('H:i', $t_date) ?></small>
                                        <?php elseif ($is_future): ?>
                                            <br><a href="#" onclick="openGameModal(<?= htmlspecialchars(json_encode($game), ENT_QUOTES, 'UTF-8') ?>); return false;" class="text-danger fw-bold small text-decoration-none" title="Tijd instellen!"><i class="fa-solid fa-triangle-exclamation"></i> Tijd?</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-dark opp-cell" nowrap>
                                        <a href="#" onclick="openGameModal(<?= htmlspecialchars(json_encode($game), ENT_QUOTES, 'UTF-8') ?>); return false;" class="text-decoration-none text-dark hover-primary" title="Bewerk Wedstrijd">
                                            <?php if(isset($game['is_home']) && $game['is_home'] == 0): ?>
                                                <i class="fa-solid fa-plane text-secondary me-1" title="Uit"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-house text-primary me-1" title="Thuis"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($game['opponent']) ?>
                                        </a>
                                    </td>
                                    <td class="coach-cell">
<?php if($game['coach_name']): 
                                            $cColor = isset($coachColorMap[$game['coach_name']]) ? $coachColorMap[$game['coach_name']] : 'bg-secondary text-white';
                                        ?>
                                            <span class="badge <?= $cColor ?> rounded-pill me-1"><?= htmlspecialchars($game['coach_first_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td nowrap>
                                        <?php if($game['selection_count'] > 0): 
                                            $sel_ids = $game['selected_player_ids'] ? explode(',', $game['selected_player_ids']) : [];
                                            $names = [];
                                            foreach($sel_ids as $sid) {
                                                if (isset($playerDisplayNames[$sid])) {
                                                    $names[] = $playerDisplayNames[$sid];
                                                }
                                            }
                                            $names_str = implode(', ', $names);
                                        ?>
                                            <div class="d-flex align-items-center">
                                                <a href="/games/<?= $game['id'] ?>/selection" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 shadow-sm me-2 text-decoration-none" title="Beheer Selectie">
                                                    <i class="fa-solid fa-users me-1"></i> <?= $game['selection_count'] ?>
                                                </a>
                                                <span class="small text-muted" style="line-height:1.2; display:inline-block; max-width:250px; white-space:normal;"><?= htmlspecialchars($names_str) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <a href="/games/<?= $game['id'] ?>/selection" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 shadow-sm text-decoration-none" title="Maak Selectie">
                                                <i class="fa-solid fa-users me-1"></i> 0
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4" style="width: 25%">
                                        <a href="/games/<?= $game['id'] ?>/duplicate" class="btn btn-sm btn-outline-warning me-1" title="Dupliceer met Selectie">
                                            <i class="fa-solid fa-copy"></i>
                                        </a>
                                        <a href="/games/<?= $game["id"] ?>/schema" class="btn btn-sm btn-outline-primary me-1 <?= $game['selection_count'] == 0 ? 'disabled' : '' ?>" title="<?= !empty($game['final_lineup_id']) ? 'Bekijk Opstelling' : 'Bereken Opstelling' ?>">
                                            <?php if(!empty($game['final_lineup_id'])): ?>
                                                <i class="fa-solid fa-eye"></i> Opstelling
                                            <?php else: ?>
                                                <i class="fa-solid fa-wand-magic-sparkles"></i> Opstelling
                                            <?php endif; ?>
                                        </a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Wedstrijd verwijderen? Dit wist ook alle direct gekoppelde selecties.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Verwijder">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Game Modal -->
<div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="gameForm">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="game_id" id="modal_game_id" value="">
          <input type="hidden" name="source_game_id" id="modal_source_game_id" value="">
          
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="gameModalLabel">Wedstrijd Beheren</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          
          <div class="modal-body">
              <div class="row mb-3">
                  <div class="col-md-8">
                      <label class="form-label text-muted small fw-bold">TEGENSTANDER</label>
                      <input type="text" class="form-control" name="opponent" id="modal_opponent" required placeholder="BV. FC Barcelona">
                  </div>
                  <div class="col-md-4">
                      <label class="form-label text-muted small fw-bold d-block">LOCATIE</label>
                      <div class="btn-group w-100" role="group">
                          <input type="radio" class="btn-check" name="is_home" id="loc_home" value="1" autocomplete="off" checked>
                          <label class="btn btn-outline-primary" for="loc_home"><i class="fa-solid fa-house me-1"></i>Thuis</label>

                          <input type="radio" class="btn-check" name="is_home" id="loc_away" value="0" autocomplete="off">
                          <label class="btn btn-outline-primary" for="loc_away"><i class="fa-solid fa-plane me-1"></i>Uit</label>
                      </div>
                  </div>
              </div>
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">DATUM</label>
                      <input type="date" class="form-control" name="game_date" id="modal_game_date" required>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">STARTUUR</label>
                      <input type="time" class="form-control" name="game_time" id="modal_game_time" step="300" min="08:00" max="22:00">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">MIN. POSITIES PER SPELER</label>
                  <select class="form-select" name="min_pos" id="modal_min_pos" required>
                      <option value="0">Geen minimum</option>
                      <option value="2">Minstens 2 posities (20000+ serie)</option>
                      <option value="3">Minstens 3 posities (30000+ serie)</option>
                  </select>
                  <div class="form-text">Bepaalt of het algoritme enkel schemas toelaat waar elke speler op X unieke posities speelt.</div>
              </div>
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">COACH</label>
                  <select class="form-select" name="coach_id" id="modal_coach_id">
                      <option value="">-- Geen coach geselecteerd --</option>
                      <?php foreach ($coachesData as $cd): ?>
                          <option value="<?= $cd['id'] ?>">Coach <?= htmlspecialchars(trim($cd['name'])) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">FORMAAT</label>
                      <select class="form-select" name="format" id="modal_format" required>
                          <?php foreach ($available_formats as $fmt): ?>
                              <option value="<?= htmlspecialchars($fmt) ?>"><?= htmlspecialchars($fmt) ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="col-md-6">
                      <label class="form-label text-muted small fw-bold">WEDSTRIJD DUUR</label>
                      <select class="form-select" name="game_parts" id="modal_game_parts" required>
                          <!-- Options dynamically loaded by JS -->
                      </select>
                  </div>
              </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuleren</button>
            <button type="submit" class="btn btn-primary">Opslaan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('gameSearch');
    const coachFilter = document.getElementById('coachFilter');

    const groupByWeekToggle = document.getElementById('groupByWeekToggle');
    const viewSeasonPhase = document.getElementById('viewSeasonPhase');
    const viewByWeek = document.getElementById('viewByWeek');

    // Herstel filters uit de sessie
    const savedSearch = localStorage.getItem('manageGamesSearch');
    const savedCoach = localStorage.getItem('manageGamesCoachFilter');
    const savedToggle = localStorage.getItem('manageGamesGroupByWeek');
    
    if (savedSearch !== null) {
        searchInput.value = savedSearch;
    }
    if (savedCoach !== null) {
        coachFilter.value = savedCoach;
    }
    if (savedToggle !== null) {
        groupByWeekToggle.checked = (savedToggle === 'true');
    }

    function toggleViews() {
        if (groupByWeekToggle.checked) {
            viewSeasonPhase.style.display = 'none';
            viewByWeek.style.display = 'block';
        } else {
            viewSeasonPhase.style.display = 'block';
            viewByWeek.style.display = 'none';
        }
        localStorage.setItem('manageGamesGroupByWeek', groupByWeekToggle.checked);
    }

    const availableParts = <?= $json_available_parts ?>;
    
    window.updateGameParts = function(preselectPart = null) {
        const formatSelect = document.getElementById('modal_format');
        const partsSelect = document.getElementById('modal_game_parts');
        const selectedFormat = formatSelect.value;
        partsSelect.innerHTML = '';
        
        let parts = availableParts[selectedFormat] || [];
        if (parts.length === 0) {
            parts = ['4x15', '3x20', '2x45'];
        }

        parts.forEach(part => {
            const option = document.createElement('option');
            option.value = part;
            option.textContent = part;
            if (preselectPart && part === preselectPart) {
                option.selected = true;
            }
            partsSelect.appendChild(option);
        });
    };

    document.getElementById('modal_format').addEventListener('change', () => window.updateGameParts(null));

    // Listeners and defaults...

    function filterGames() {
        const query = searchInput.value.toLowerCase();
        const coach = coachFilter.value;
        const rows = document.querySelectorAll('.game-row');
        
        // Bewaar filters in sessie
        localStorage.setItem('manageGamesSearch', searchInput.value);
        localStorage.setItem('manageGamesCoachFilter', coachFilter.value);
        
        rows.forEach(row => {
            const rowCoach = row.getAttribute('data-coach');
            const oppText = row.querySelector('.opp-cell').textContent.toLowerCase();
            const dateText = row.querySelector('.date-cell').textContent.toLowerCase();
            
            const matchSearch = (oppText.includes(query) || dateText.includes(query));
            const matchCoach = (coach === '' || rowCoach === coach);
            
            if (matchSearch && matchCoach) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput && coachFilter) {
        searchInput.addEventListener('input', filterGames);
        coachFilter.addEventListener('change', filterGames);
        groupByWeekToggle.addEventListener('change', toggleViews);
        
        // Pas filters direct toe op on page load
        toggleViews();
        filterGames();
    }
});

function openGameModal(game = null, isDuplicate = false) {
    var modalEl = document.getElementById('gameModal');
    var modal = new bootstrap.Modal(modalEl);
    
    // Reset form
    document.getElementById('gameForm').reset();
    document.getElementById('modal_game_id').value = '';
    document.getElementById('modal_source_game_id').value = '';
    
    if (game && !isDuplicate) {
        document.getElementById('gameModalLabel').innerText = 'Wedstrijd Bewerken';
        document.getElementById('modal_game_id').value = game.id;
        document.getElementById('modal_opponent').value = game.opponent;
        document.getElementById('modal_game_date').value = game.game_date ? game.game_date.split(' ')[0] : '';
        document.getElementById('modal_game_time').value = (game.game_date && game.game_date.includes(' ') && !game.game_date.includes('00:00:00')) ? game.game_date.split(' ')[1].substring(0, 5) : '';
        document.getElementById('modal_min_pos').value = game.min_pos || '0';
        document.getElementById('modal_coach_id').value = game.coach_id || '';
        if (game.is_home === undefined || game.is_home == 1) {
            document.getElementById('loc_home').checked = true;
        } else {
            document.getElementById('loc_away').checked = true;
        }
        
        let formatBase = '8v8';
        let formatParts = '4x15';
        if (game.format) {
            const parts = game.format.split('_');
            if (parts.length >= 2) {
                formatBase = parts[0];
                formatParts = parts[parts.length - 1];
            } else {
                formatBase = game.format;
            }
        }
        document.getElementById('modal_format').value = formatBase;
        updateGameParts(formatParts);
    } else if (game && isDuplicate) {
        document.getElementById('gameModalLabel').innerText = 'Wedstrijd Dupliceren van ' + game.opponent;
        document.getElementById('modal_source_game_id').value = game.id;
        document.getElementById('modal_opponent').value = '';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_game_time').value = '09:00';
        document.getElementById('modal_min_pos').value = game.min_pos || '0';
        document.getElementById('modal_coach_id').value = game.coach_id || '';
        if (game.is_home === undefined || game.is_home == 1) {
            document.getElementById('loc_home').checked = true;
        } else {
            document.getElementById('loc_away').checked = true;
        }
        
        let formatBase = '8v8';
        let formatParts = '4x15';
        if (game.format) {
            const parts = game.format.split('_');
            if (parts.length >= 2) {
                formatBase = parts[0];
                formatParts = parts[parts.length - 1];
            } else {
                formatBase = game.format;
            }
        }
        document.getElementById('modal_format').value = formatBase;
        updateGameParts(formatParts);
    } else {
        document.getElementById('gameModalLabel').innerText = 'Nieuwe Wedstrijd Plannen';
        document.getElementById('modal_game_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_game_time').value = '09:00';
        document.getElementById('modal_min_pos').value = '0';
        document.getElementById('modal_coach_id').value = '<?= $_SESSION['user_id'] ?? '' ?>';
        document.getElementById('loc_home').checked = true;
        
        let defFormat = '<?= $_SESSION['default_format'] ?? '8v8' ?>';
        let defParts = '<?= $_SESSION['default_game_parts'] ?? '4x15' ?>';
        document.getElementById('modal_format').value = defFormat;
        updateGameParts(defParts);
    }
    
    modal.show();
}

<?php if (isset($_GET['edit_game']) || isset($_GET['duplicate_game'])): 
    $isDup = isset($_GET['duplicate_game']);
    $urlId = $isDup ? (int)$_GET['duplicate_game'] : (int)$_GET['edit_game'];
    $evtTarget = null;
    foreach ($games as $g) {
        if ((int)$g['id'] === $urlId) {
            $evtTarget = $g;
            break;
        }
    }
    if ($evtTarget):
?>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        openGameModal(<?= json_encode($evtTarget) ?>, <?= $isDup ? 'true' : 'false' ?>);
    }, 150); // Small delay to ensure bootstrap is ready
});
<?php endif; endif; ?>
</script>

<?php require_once dirname(__DIR__, 2) . '/footer.php'; ?>
