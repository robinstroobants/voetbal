<?php
require_once __DIR__ . '/core/getconn.php';

$team_id = (int)($_SESSION['team_id'] ?? 0);

if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    header("Location: /admin");
    exit;
}

// 1. Calculate Onboarding Status
$stmtP = $pdo->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND deleted_at IS NULL");
$stmtP->execute([$team_id]);
$players_count = (int)$stmtP->fetchColumn();

$stmtC = $pdo->prepare("SELECT COUNT(*) FROM coaches WHERE team_id = ?");
$stmtC->execute([$team_id]);
$coaches_count = (int)$stmtC->fetchColumn();

// Extraheer format requirements
$stmtF = $pdo->prepare("SELECT default_format, meeting_time_offset FROM teams WHERE id = ?");
$stmtF->execute([$_SESSION['team_id']]);
$teamData = $stmtF->fetch(PDO::FETCH_ASSOC);
$default_format = $teamData['default_format'] ?? '8v8';
$meeting_time_offset = $teamData['meeting_time_offset'] ?? 45;

$required_players = 8;
if (preg_match('/^(\d+)v\d+/', $default_format, $matches)) {
    $required_players = (int)$matches[1];
}

$max_players = 24;
if (strpos($default_format, '2v2') === 0 || strpos($default_format, '3v3') === 0) {
    $max_players = 12;
}
$remaining_players = max(0, $max_players - $players_count);

$onboarding_complete = ($players_count >= $required_players);

// Haal de Dashboard Data op indien onboarding compleet is
$next_games = [];
$past_games = [];
$future_games_count = 0;
$missing_matrix_count = 0;

if ($onboarding_complete) {
    // 1. Eerstvolgende Wedstrijden
    $stmtNext = $pdo->prepare("
        SELECT g.*, 
            (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
            (SELECT COUNT(*) FROM game_events ge WHERE ge.game_id = g.id) as events_count,
            u.first_name as coach_name
        FROM games g 
        LEFT JOIN users u ON g.coach_id = u.id
        WHERE g.team_id = ? AND g.game_date >= CURDATE()
        ORDER BY g.game_date ASC
        LIMIT 2
    ");
    $stmtNext->execute([$team_id]);
    $next_games = $stmtNext->fetchAll(PDO::FETCH_ASSOC);

    foreach ($next_games as &$next_game) {
        $stmtSelected = $pdo->prepare("
            SELECT p.id, p.first_name, p.last_name, gs.status_id, gs.is_goalkeeper 
            FROM game_selections gs 
            JOIN players p ON gs.player_id = p.id 
            WHERE gs.game_id = ? AND p.team_id = ?
        ");
        $stmtSelected->execute([$next_game['id'], $team_id]);
        $next_game['players'] = $stmtSelected->fetchAll(PDO::FETCH_ASSOC);

        // Genereer WhatsApp Bericht Template
        $ts = strtotime($next_game['game_date']);
        $dateStr = (date('H:i', $ts) === '00:00') ? date('d/m/Y', $ts) : date('d/m/Y', $ts);
        $samenkomstStr = (date('H:i', $ts) === '00:00') ? "Nog te bepalen" : date('H:i', $ts - ($meeting_time_offset * 60));
        
        $wa_msg = "Beste ouders, hierbij de selectie voor de wedstrijd tegen *" . $next_game['opponent'] . "* op " . $dateStr . ".\n";
        $wa_msg .= "Samenkomst: *" . $samenkomstStr . "* (" . $meeting_time_offset . "min voor de start).\n\n";
        $wa_msg .= "*Selectie:*\n";
        
        $has_active_players = false;
        foreach ($next_game['players'] as $p) {
            if ($p['status_id'] != 1) { // Enkel aanwezigen
                $wa_msg .= "- " . trim($p['first_name'] . ' ' . $p['last_name']);
                if ($p['is_goalkeeper']) {
                    $wa_msg .= " (K)";
                }
                $wa_msg .= "\n";
                $has_active_players = true;
            }
        }
        
        if (!$has_active_players) {
            $wa_msg .= "Nog geen spelers geselecteerd.\n";
        }
        $next_game['whatsapp_msg'] = urlencode($wa_msg);
        $next_game['whatsapp_msg_raw'] = $wa_msg;

        // Speelminuten ophalen (match specifiek of seizoen historiek)
        $player_playtimes = [];
        $player_available = [];
        $player_postimes = [];
        $is_match_playtime = false;
        
        $stmtLineup = $pdo->prepare("SELECT schema_id, player_order, is_final FROM game_lineups WHERE game_id = ? ORDER BY is_final DESC, score DESC LIMIT 1");
        $stmtLineup->execute([$next_game['id']]);
        $lineup = $stmtLineup->fetch(PDO::FETCH_ASSOC);

        if ($lineup) {
            $is_match_playtime = true;
            $schema_id = $lineup['schema_id'];
            $players_arr = explode(',', $lineup['player_order']);
            $stmtSch = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
            $stmtSch->execute([$schema_id]);
            $schema_json = $stmtSch->fetchColumn();
            if ($schema_json) {
                $schema = json_decode($schema_json, true);
                if (isset($schema['game_parts'])) {
                    $schema = $schema['game_parts'];
                }
                $schema_total_dur = 0;
                foreach ($schema as $idx => $part) {
                    if (!is_numeric($idx)) continue;
                    $dur = $part['duration'] ?? 0;
                    $schema_total_dur += $dur;
                    if (isset($part['lineup'])) {
                        foreach ($part['lineup'] as $pos => $pIndex) {
                            if (isset($players_arr[$pIndex])) {
                                $pId = $players_arr[$pIndex];
                                $player_playtimes[$pId] = ($player_playtimes[$pId] ?? 0) + $dur;
                                if (!isset($player_postimes[$pId])) $player_postimes[$pId] = [];
                                $player_postimes[$pId][$pos] = ($player_postimes[$pId][$pos] ?? 0) + $dur;
                            }
                        }
                    }
                }
                foreach ($players_arr as $pId) {
                    if (trim($pId) !== '') $player_available[$pId] = $schema_total_dur;
                }
            }
        } else {
            $season_start = null;
            $season_end = null;
            $stmtHist = $pdo->prepare("SELECT l.schema_id, l.player_order, g.game_date FROM game_lineups l JOIN games g ON l.game_id = g.id WHERE l.is_final = 1 AND g.team_id = ?");
            $stmtHist->execute([$team_id]);
            while ($row = $stmtHist->fetch(PDO::FETCH_ASSOC)) {
                $gDate = strtotime($row['game_date']);
                if ($season_start === null || $gDate < $season_start) $season_start = $gDate;
                if ($season_end === null || $gDate > $season_end) $season_end = $gDate;

                $schema_id = $row['schema_id'];
                $players_arr = explode(',', $row['player_order']);
                $stmtSch = $pdo->prepare("SELECT schema_data FROM lineups WHERE id = ?");
                $stmtSch->execute([$schema_id]);
                $schema_json = $stmtSch->fetchColumn();
                if ($schema_json) {
                    $schema = json_decode($schema_json, true);
                    if (isset($schema['game_parts'])) {
                        $schema = $schema['game_parts'];
                    }
                    $schema_total_dur = 0;
                    foreach ($schema as $idx => $part) {
                        if (!is_numeric($idx)) continue;
                        $dur = $part['duration'] ?? 0;
                        $schema_total_dur += $dur;
                        if (isset($part['lineup'])) {
                            foreach ($part['lineup'] as $pos => $pIndex) {
                                if (isset($players_arr[$pIndex])) {
                                    $pId = $players_arr[$pIndex];
                                    $player_playtimes[$pId] = ($player_playtimes[$pId] ?? 0) + $dur;
                                }
                            }
                        }
                    }
                    foreach ($players_arr as $pId) {
                        if (trim($pId) !== '') {
                            $player_available[$pId] = ($player_available[$pId] ?? 0) + $schema_total_dur;
                        }
                    }
                }
            }
            if ($season_start !== null) {
                $next_game['season_start_fmt'] = date('d/m/Y', $season_start);
                $next_game['season_end_fmt'] = date('d/m/Y', $season_end);
            }
        }
        $next_game['is_match_playtime'] = $is_match_playtime;
        $next_game['playtimes'] = $player_playtimes;
        $next_game['postimes'] = $player_postimes;
        $next_game['available'] = $player_available;
        $next_game['is_final'] = ($lineup && $lineup['is_final'] == 1);

        // Sort players by status (present first) and then by playtime percentage (lowest first)
        usort($next_game['players'], function($a, $b) use ($player_playtimes, $player_available) {
            // Status: absent (1) to the bottom
            if ($a['status_id'] != $b['status_id']) {
                return $a['status_id'] <=> $b['status_id'];
            }
            
            // Calculate percentage for a
            $ptA = $player_playtimes[$a['id']] ?? 0;
            $avA = $player_available[$a['id']] ?? 0;
            $percA = $avA > 0 ? ($ptA / $avA) : 0;
            
            // Calculate percentage for b
            $ptB = $player_playtimes[$b['id']] ?? 0;
            $avB = $player_available[$b['id']] ?? 0;
            $percB = $avB > 0 ? ($ptB / $avB) : 0;
            
            // Sort by percentage ASC
            if (abs($percA - $percB) > 0.0001) {
                return $percA <=> $percB;
            }
            
            // Fallback to name ASC
            return strcasecmp($a['first_name'], $b['first_name']);
        });
    }
    unset($next_game);

    // 2. Historiek + Binnenkort (afgelopen + komende 7 dagen)
    $stmtPast = $pdo->prepare("
        SELECT g.*, 
            (SELECT COUNT(*) FROM game_selections gs WHERE gs.game_id = g.id) as selection_count,
            (SELECT COUNT(*) FROM game_selections gs 
                WHERE gs.game_id = g.id AND gs.is_goalkeeper = 1) as gk_count,
            (SELECT COUNT(*) FROM game_events ge WHERE ge.game_id = g.id) as events_count,
            u.first_name as coach_name
        FROM games g 
        LEFT JOIN users u ON g.coach_id = u.id
        WHERE g.team_id = ? AND g.game_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY g.game_date DESC
        LIMIT 10
    ");
    $stmtPast->execute([$team_id]);
    $past_games = $stmtPast->fetchAll(PDO::FETCH_ASSOC);

    // 3. Totaal aankomende wedstrijden (voor Quick Stats)
    $stmtFuture = $pdo->prepare("SELECT COUNT(*) FROM games WHERE team_id = ? AND game_date >= CURDATE()");
    $stmtFuture->execute([$team_id]);
    $future_games_count = (int)$stmtFuture->fetchColumn();

    // 4. Ontbrekende Matrix Scores (To-Do)
    // Een speler is OK als hij *ergens* een score heeft in player_scores of gk_scores
    $stmtMissing = $pdo->prepare("
        SELECT COUNT(*) 
        FROM players p 
        WHERE p.team_id = ? 
          AND p.deleted_at IS NULL
          AND NOT EXISTS (SELECT 1 FROM player_scores ps WHERE ps.player_id = p.id) 
          AND NOT EXISTS (SELECT 1 FROM gk_scores gks WHERE gks.player_id = p.id)
    ");
    $stmtMissing->execute([$team_id]);
    $missing_matrix_count = (int)$stmtMissing->fetchColumn();

    // 5. Tooltips logica (beschikbare co-coaches)
    $stmtUserTeams = $pdo->prepare("SELECT COUNT(*) FROM user_teams WHERE team_id = ?");
    $stmtUserTeams->execute([$team_id]);
    $current_coaches = (int)$stmtUserTeams->fetchColumn();

    $stmtInvites = $pdo->prepare("SELECT COUNT(*) FROM team_invitations WHERE team_id = ? AND expires_at > NOW()");
    $stmtInvites->execute([$team_id]);
    $pending_invites = (int)$stmtInvites->fetchColumn();

    $available_coach_slots = 3 - ($current_coaches + $pending_invites);

    // 6. Tip van de week pool
    $coaching_tips = [
        "Werk je met verschillende reeksen (zoals Najaar en Voorjaar)? Stel dan Seizoensperiodes in via de instellingen om je speelminuten eerlijker te spreiden over die specifieke weken!",
        "De 'Maak Definitief' knop bewaart niet alleen je opstelling, maar logt ook op de seconde af wie er hoelang op het veld, op de bank of in doel stond.",
        "Komt een speler toch niet opdagen? Zet hem op 'Afwezig' en het AI-algoritme zal de speelminuten van de rest automatisch herschikken om alles eerlijk te houden.",
        "Wist je dat je via Instellingen ook de standaard wedstrijdduur en het aantal wisselblokjes van je team kan configureren?"
    ];
    $tip_of_the_day = !empty($coaching_tips) ? $coaching_tips[array_rand($coaching_tips)] : null;
}

$page_title = 'Overzicht';
require_once __DIR__ . '/header.php';
?>

<style>
    /* Styling voor het nieuwe Dashboard V2 */
    .dashboard-hero {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }
    .dashboard-hero::after {
        content: '\f1e3';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        bottom: -20px;
        right: -10px;
        font-size: 10rem;
        opacity: 0.05;
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
    }
</style>

<div class="container mt-4 mb-5">
    <?php if (!$onboarding_complete): ?>
        <!-- ONBOARDING WIZARD -->
        <div class="card shadow border-0 overflow-hidden mb-4" style="border-radius: 12px;">
            <div class="bg-primary text-white p-4">
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Welkom bij Lineup!</h3>
                <p class="mb-0 text-white-50">Laten we je team snel opstarten. Werk deze stappen af om opstellingen te kunnen maken.</p>
            </div>
            
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <!-- Stap 1: Spelers -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex align-items-start">
                            <div class="bg-<?php echo ($players_count >= $required_players) ? 'success' : 'light'; ?> text-<?php echo ($players_count >= $required_players) ? 'white' : 'secondary'; ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <?php if($players_count >= $required_players): ?>
                                    <i class="fa-solid fa-check fs-5"></i>
                                <?php else: ?>
                                    <span class="fs-5 fw-bold">1</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="fw-bold">Spelers Toevoegen</h5>
                                <p class="text-muted small">Je hebt minimaal <strong><?= $required_players ?></strong> spelers nodig voor jouw format (<?= htmlspecialchars($default_format) ?>).</p>
                                <div class="progress mb-3" style="height: 10px;">
                                    <?php 
                                        $perc = min(100, round(($players_count / max(1, $required_players)) * 100));
                                        $colorClass = $perc == 100 ? 'bg-success' : 'bg-primary';
                                    ?>
                                    <div class="progress-bar <?= $colorClass ?>" role="progressbar" style="width: <?= $perc ?>%;" aria-valuenow="<?= $perc ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="mb-2 fw-semibold text-secondary">Huidig aantal: <?= $players_count ?> (Minimaal <?= $required_players ?> nodig)</div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSinglePlayerModal"><i class="fa-solid fa-user-plus me-1"></i> Eén speler</button>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBulkPlayersModal"><i class="fa-solid fa-list-ul me-1"></i> Plakken uit Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stap 2: Team Instellingen -->
                    <div class="col-md-6 border-start border-light ps-md-4">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <i class="fa-solid fa-gear fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Team Instellingen</h5>
                                <p class="text-muted small mb-2">Configureer extra opties zoals de vaste wedstrijdduur (nu <strong><?= htmlspecialchars($default_format) ?></strong>), periodes en samenkomsttijden voor je wedstrijden.</p>
                                
                                <a href="/settings" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-right me-1"></i> Bekijk Instellingen</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($onboarding_complete): ?>
            <div class="card-footer bg-success text-white text-center p-3 fw-bold">
                Jouw team is klaar! Je kan nu beginnen plannen.
            </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- COACH DASHBOARD V2 -->
        
        <!-- Welkom Bericht -->
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0">Welkom terug, Coach! <i class="fa-solid fa-hand-wave text-warning" style="font-size: 0.9em;"></i></h4>
                <p class="text-muted mb-0 mt-1 d-none d-md-block">Jouw dashboard voor team <?= htmlspecialchars($_SESSION['team_name'] ?? '') ?></p>
            </div>
            <a href="/games?new=1" class="btn btn-primary shadow-sm fw-bold rounded-pill px-4">
                <i class="fa-solid fa-plus me-1"></i> Wedstrijd Plannen
            </a>
        </div>

        <div class="row mb-4">
            <!-- Aankomende Wedstrijden -->
            <div class="col-12">
                <div class="row g-4">
                <?php if (!empty($next_games)): ?>
                <?php foreach($next_games as $idx => $next_game): ?>
                <div class="col-lg-6 col-md-12">
                <div class="dashboard-hero p-4 shadow h-100">
                    <span class="badge bg-white text-primary mb-3 fw-bold px-3 py-2 rounded-pill shadow-sm"><i class="fa-solid fa-calendar-day me-1"></i> <?= $idx === 0 ? 'Volgende Wedstrijd' : 'Daaropvolgende Wedstrijd' ?></span>
                    
                    <div class="row align-items-center">
                        <div class="col-12 text-start">
                            <h2 class="fw-bold mb-1 text-truncate text-white" title="<?= htmlspecialchars($next_game['opponent']) ?>"><?= htmlspecialchars($next_game['opponent']) ?></h2>
                            <p class="mb-2 small text-white text-opacity-75">
                                <i class="fa-regular fa-clock me-1"></i> 
                                <?php 
                                    $ts = strtotime($next_game['game_date']);
                                    $maanden = [1=>'jan',2=>'feb',3=>'mrt',4=>'apr',5=>'mei',6=>'jun',7=>'jul',8=>'aug',9=>'sep',10=>'okt',11=>'nov',12=>'dec'];
                                    $dStr = date('j', $ts) . ' ' . $maanden[(int)date('n', $ts)];
                                    if (date('H:i', $ts) !== '00:00') {
                                        $tStr = date('G', $ts) . 'u' . date('i', $ts);
                                        if (substr($tStr, -2) === '00') $tStr = substr($tStr, 0, -2);
                                        echo $dStr . ' ' . $tStr;
                                    } else {
                                        echo $dStr;
                                    }
                                ?>
                                <span class="badge bg-black bg-opacity-25 border border-white border-opacity-25 ms-1 text-white" style="font-size: 0.65em; vertical-align: middle; padding: 0.3em 0.5em;"><?= htmlspecialchars($next_game['format']) ?></span>
                                <?php if (!empty($next_game['coach_name'])): ?>
                                    <span class="mx-2 text-white">•</span> 
                                    <span class="badge bg-primary bg-opacity-50 border border-white border-opacity-25 text-white"><i class="fa-solid fa-user-tie me-1"></i><?= htmlspecialchars($next_game['coach_name']) ?></span>
                                <?php endif; ?>
                            </p>
                            
                            <?php 
                                $has_selection = $next_game['selection_count'] > 0;
                                $is_selection_ready = $next_game['selection_count'] >= $required_players; 
                                $selection_color = $is_selection_ready ? 'success' : 'warning';
                                $selection_icon = $has_selection ? 'fa-pen-to-square' : 'fa-plus';
                            ?>
                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 mb-2">
                                <?php if ($next_game['is_final']): ?>
                                <a href="/games/<?= $next_game['id'] ?>/selection" class="btn bg-white bg-opacity-10 border border-white border-opacity-10 text-white fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="Selectie (<?= $has_selection ? $next_game['selection_count'] : '0' ?>)">
                                    <i class="fa-solid fa-users text-<?= $selection_color ?>"></i>
                                </a>

                                <a href="/games/<?= $next_game['id'] ?>/lineup" class="btn btn-success text-white fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="Opstelling">
                                    <i class="fa-solid fa-table-list"></i>
                                </a>
                                <?php 
                                    $match_datetime = strtotime($next_game['match_date'] . ' ' . $next_game['time']);
                                    $hours_until_match = max(0, ($match_datetime - time()) / 3600);
                                    $needed_hours = ceil($hours_until_match + 4);
                                    if ($needed_hours <= 24) $needed_hours = 24;
                                    elseif ($needed_hours <= 48) $needed_hours = 48;
                                    else $needed_hours = ceil($needed_hours/24)*24;
                                ?>
                                <button type="button" class="btn bg-white bg-opacity-10 border border-white border-opacity-10 text-white fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="Kopieer Share Link" onclick="copyShareLink(<?= $next_game['id'] ?>, <?= $needed_hours ?>, this)">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </button>
                                <?php else: ?>
                                <a href="/games/<?= $next_game['id'] ?>/selection" class="text-decoration-none d-inline-flex align-items-center bg-white bg-opacity-10 rounded px-3 py-2 transition-transform shadow-sm border border-white border-opacity-10" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <i class="fa-solid <?= $selection_icon ?> text-<?= $selection_color ?> me-2"></i>
                                    <div class="fw-bold text-white small">Selectie (<?= $has_selection ? $next_game['selection_count'] : '0' ?>)</div>
                                </a>

                                <a href="/games/<?= $next_game['id'] ?>/schema" class="btn <?= $next_game['selection_count'] > 0 ? 'btn-warning text-dark' : 'btn-outline-light disabled' ?> fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <i class="fa-solid fa-trowel-bricks me-2"></i>
                                    <div class="fw-bold text-dark small">Opstelling</div>
                                </a>
                                <?php endif; ?>

                                <?php if ($next_game['selection_count'] > 0): ?>
                                <button type="button" class="btn btn-success fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="Kopieer selectie bericht" data-msg="<?= htmlspecialchars(json_encode($next_game['whatsapp_msg_raw']), ENT_QUOTES, 'UTF-8') ?>" onclick="copyToClipboard(this)">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($next_game['events_count'] > 0): ?>
                                <a href="/games/<?= $next_game['id'] ?>/events" class="btn bg-white bg-opacity-10 border border-white border-opacity-10 text-white fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="Bekijk Wedstrijd Events">
                                    <i class="fa-solid fa-list-ol"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($next_game['is_final']): ?>
                                <a href="/games/<?= $next_game['id'] ?>/lineup?print=1" target="_blank" class="btn bg-white bg-opacity-10 border border-white border-opacity-10 text-white fw-bold rounded px-3 py-2 shadow-sm d-inline-flex align-items-center transition-transform" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="PDF / Afdrukken">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($has_selection && !empty($next_game['players'])): ?>
                            <div class="mt-4 text-start">
                                <?php if ($next_game['is_match_playtime']): ?>
                                    <div class="small text-white text-opacity-75 fw-bold mb-2 text-uppercase" style="letter-spacing: 0.5px;"><i class="fa-regular fa-clock me-1"></i> Voorziene Speeltijd (Match)</div>
                                <?php else: ?>
                                    <div class="small text-white text-opacity-75 fw-bold mb-2 text-uppercase" style="letter-spacing: 0.5px;">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> 
                                        Speelminuten 
                                        <?php if (!empty($next_game['season_start_fmt'])): ?>
                                            (<?= $next_game['season_start_fmt'] ?> - <?= $next_game['season_end_fmt'] ?>)
                                        <?php else: ?>
                                            (Historiek)
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <ul class="list-group shadow-sm border-0 bg-transparent">
                                <?php foreach($next_game['players'] as $p): 
                                    $pt = $next_game['playtimes'][$p['id']] ?? 0;
                                    $av = $next_game['available'][$p['id']] ?? 0;
                                    $ptFormatted = "0%";
                                    if ($av > 0) {
                                        $ptFormatted = round(($pt / $av) * 100) . "%";
                                    }
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-white border-opacity-25" style="background: rgba(255, 255, 255, <?= $p['status_id'] == 1 ? '0.05' : '0.1' ?>); border-top: none; border-left: none; border-right: none;">
                                        <div class="text-truncate text-white <?= $p['status_id'] == 1 ? 'text-decoration-line-through opacity-50' : '' ?>" style="max-width: 180px; font-size: 0.9rem;">
                                            <?php if ($p['is_goalkeeper']): ?><i class="fa-solid fa-hands me-2 text-warning opacity-75"></i><?php endif; ?>
                                            <?= htmlspecialchars(stripslashes(trim($p['first_name'] . ' ' . $p['last_name']))) ?>
                                            <?php if ($next_game['is_final'] && isset($next_game['postimes'][$p['id']]) && $p['status_id'] != 1): ?>
                                                <div class="text-white text-opacity-50 mt-1" style="font-size: 0.7rem;">
                                                    <?php 
                                                    $pos_strings = [];
                                                    foreach ($next_game['postimes'][$p['id']] as $pos => $dur) {
                                                        $pos_strings[] = "P" . $pos . ": " . round($dur/60) . "m";
                                                    }
                                                    echo implode(' | ', $pos_strings);
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?= $p['status_id'] == 1 ? 'bg-danger text-white' : 'bg-white bg-opacity-25 text-white' ?> rounded-pill" style="min-width: 45px; font-size: 0.8rem;" title="<?= round($pt / 60) ?> min gespeeld">
                                            <?= $p['status_id'] == 1 ? 'afwezig' : ($next_game['is_final'] ? round($pt / 60).'m' : $ptFormatted) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                <div class="card shadow-sm border-0 mb-4 bg-light text-center" style="border-radius: 16px; border: 2px dashed var(--apple-border) !important;">
                    <div class="card-body py-5">
                        <i class="fa-regular fa-calendar-xmark text-muted mb-3" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold text-dark mb-1">Geen Aankomende Wedstrijden</h4>
                        <p class="text-muted">Er staan momenteel geen wedstrijden op de planning voor je team.</p>
                        <a href="/games?new=1" class="btn btn-primary rounded-pill mt-2">
                            <i class="fa-solid fa-plus me-1"></i> Nu Eentje Toevoegen
                        </a>
                    </div>
                </div>
                </div>
                <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mb-4">

            <!-- Historiek Tabel: full width -->
            <div class="col-12 mb-4">
                <!-- Historiek Tabel -->
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>Historiek & Binnenkort</h5>
                <div class="card shadow-sm border-0 stat-card mb-4 mb-lg-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Datum</th>
                                        <th>Tegenstander</th>
                                        <th>Coach</th>
                                        <th>Selectie</th>
                                        <th class="text-end pe-4">Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($past_games)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                Nog geen wedstrijden gespeeld in het verleden.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    
                                    <?php foreach($past_games as $game): 
                                        $gameTimestamp = strtotime($game['game_date']);
                                        $todayTimestamp = strtotime(date('Y-m-d'));
                                        $isToday = $gameTimestamp === $todayTimestamp;
                                        $isFuture = $gameTimestamp > $todayTimestamp;
                                    ?>
                                     <tr class="<?= ($isToday || $isFuture) ? 'table-info' : '' ?>">
                                        <td class="ps-4 fw-medium text-secondary small">
                                            <?= date('d/m/Y', strtotime($game['game_date'])) ?>
                                            <?php if ($isToday): ?><span class="badge bg-success text-white ms-1" style="font-size:0.65rem;">vandaag</span>
                                            <?php elseif ($isFuture): ?><span class="badge bg-info text-dark ms-1" style="font-size:0.65rem;">binnenkort</span><?php endif; ?>
                                        </td>
                                        <td class="fw-bold">
                                            <?= htmlspecialchars($game['opponent']) ?>
                                            <?php if (!empty($game['format'])): 
                                                $fmtColors = ['bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary', 'bg-dark'];
                                                $fmtTextColors = ['text-white', 'text-white', 'text-white', 'text-dark', 'text-dark', 'text-white', 'text-white'];
                                                $fmtIdx = abs(crc32($game['format'])) % count($fmtColors);
                                            ?>
                                                <span class="badge <?= $fmtColors[$fmtIdx] ?> <?= $fmtTextColors[$fmtIdx] ?> ms-1" style="font-size:0.7rem;"><?= htmlspecialchars($game['format']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($game['coach_name']): 
                                                $colors = ['bg-info text-dark', 'bg-danger', 'bg-success', 'bg-warning text-dark', 'bg-primary', 'bg-dark text-white'];
                                                $cColor = $colors[abs(crc32($game['coach_name'])) % count($colors)];
                                            ?>
                                                <span class="badge <?= $cColor ?> rounded-pill"><?= htmlspecialchars($game['coach_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small italic">Geen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($game['selection_count'] > 0): ?>
                                            <a href="/games/<?= $game['id'] ?>/selection" class="text-decoration-none" title="Bekijk selectie">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-1">
                                                    <i class="fa-solid fa-users me-1"></i><?= $game['selection_count'] ?>
                                                </span>
                                            </a>
                                            <?php if (!empty($game['gk_count']) && $game['gk_count'] > 0): ?>
                                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-25">
                                                    <?= $game['gk_count'] ?> <i class="fa-solid fa-hands"></i>
                                                </span>
                                            <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4 text-nowrap">
                                            <a href="/games/<?= $game['id'] ?>/edit" class="btn btn-sm btn-light text-secondary fw-bold rounded-pill shadow-sm me-1" title="Bewerk details">
                                                <i class="fa-solid fa-pen mt-1 mb-1"></i>
                                            </a>
                                            <a href="/games/<?= $game['id'] ?>/duplicate" class="btn btn-sm btn-light text-warning fw-bold rounded-pill shadow-sm me-1" title="Dupliceer Wedstrijd">
                                                <i class="fa-solid fa-copy me-1 mt-1 mb-1"></i> Dupliceer
                                            </a>
                                            <a href="/games/<?= $game['id'] ?>/schema" class="btn btn-sm btn-light text-primary fw-bold rounded-pill shadow-sm me-1" title="Bekijk Opstelling">
                                                <i class="fa-solid fa-eye me-1 mt-1 mb-1"></i> Detail
                                            </a>
                                            <?php if ($game['events_count'] > 0): ?>
                                            <a href="/games/<?= $game['id'] ?>/events" class="btn btn-sm btn-light text-success fw-bold rounded-pill shadow-sm me-1" title="Wedstrijd Events">
                                                <i class="fa-solid fa-list-ol mt-1 mb-1"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if (!empty($game['is_final'])): ?>
                                            <a href="/games/<?= $game['id'] ?>/lineup?print=1" target="_blank" class="btn btn-sm btn-light text-danger fw-bold rounded-pill shadow-sm" title="PDF / Afdrukken">
                                                <i class="fa-solid fa-file-pdf mt-1 mb-1"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            </div>

            <!-- Widgets & Spelers -->
            <div class="col-12">
                <!-- Reminder Widget -->
                <?php if ($missing_matrix_count > 0 && strpos($default_format, '11v11') === false): ?>
                <div class="card stat-card border-danger border-opacity-25 shadow-sm mb-4" style="background-color: #fffafb;">
                    <div class="card-body d-flex align-items-start">
                        <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                            <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-danger mb-1 mt-1">Matrix Update Nodig</h6>
                            <p class="text-secondary small mb-2">Er zijn momenteel <strong><?= $missing_matrix_count ?> spelers</strong> in je team zonder dat hun Score Matrix (volledig) is ingevuld.</p>
                            <a href="/scores" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">Nu Bijwerken</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tips & Tricks Widget -->
                <?php 
                $show_invite = ($available_coach_slots > 0 && rand(1, 100) <= 50);
                if (!$show_invite && !$tip_of_the_day && $available_coach_slots > 0) {
                    $show_invite = true;
                }
                
                if ($show_invite || $tip_of_the_day): 
                ?>
                <div class="card stat-card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-regular fa-lightbulb text-warning me-2"></i>Inzicht & Tips</h6>
                        
                        <?php if ($show_invite): ?>
                        <div class="bg-white p-3 rounded shadow-sm border border-light">
                            <div class="fw-bold text-primary mb-1" style="font-size: 0.85rem;">Samenwerken <i class="fa-solid fa-users ms-1"></i></div>
                            <p class="small text-secondary mb-2" style="font-size: 0.85rem;">Je kan nog <strong><?= $available_coach_slots ?> extra co-coaches</strong> uitnodigen in dit teamaccount. Nodig je staf uit zodat zij ook opstellingen kunnen bouwen!</p>
                            <a href="/settings" class="btn btn-sm btn-light text-primary fw-bold w-100" style="font-size: 0.75rem;">Nu Uitnodigen</a>
                        </div>
                        <?php elseif ($tip_of_the_day): ?>
                        <div class="bg-white p-3 rounded shadow-sm border border-light">
                            <div class="fw-bold text-success mb-1" style="font-size: 0.85rem;">Coach Tip <i class="fa-solid fa-graduation-cap ms-1"></i></div>
                            <p class="small text-secondary mb-0" style="font-size: 0.85rem; font-style: italic;">"<?= htmlspecialchars($tip_of_the_day) ?>"</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>


        </div>

    <?php endif; ?>
</div>

<!-- MODALS VOOR ONBOARDING -->

<!-- 1. Single Player Modal -->
<div class="modal fade" id="addSinglePlayerModal" tabindex="-1" aria-labelledby="addSingleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="addSingleLabel"><i class="fa-solid fa-user-plus me-2"></i>Eén Speler Toevoegen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="frmSinglePlayer">
              <input type="hidden" name="action" value="add_single_player">
              <div class="row">
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">VOORNAAM <span class="text-danger">*</span></label>
                      <input type="text" name="first_name" class="form-control" required placeholder="Bv. Eden">
                  </div>
                  <div class="col-6 mb-3">
                      <label class="form-label fw-bold small text-muted">ACHTERNAAM</label>
                      <input type="text" name="last_name" class="form-control" placeholder="Bv. Hazard">
                  </div>
              </div>
              <div class="mb-3">
                  <div class="form-check form-switch pt-1">
                      <input class="form-check-input" type="checkbox" name="is_doelman" id="checkDoelman" value="1">
                      <label class="form-check-label fw-bold text-dark" for="checkDoelman">Deze speler is een doelman</label>
                  </div>
              </div>
              <div class="mb-3" id="favPosContainer">
                  <label class="form-label fw-bold small text-muted">FAVORIETE POSITIES (Optioneel)</label>
                  <input type="text" name="favorite_positions" class="form-control" placeholder="Bv. 7, 11 (gescheiden met komma)">
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary" onclick="submitApicall('frmSinglePlayer')"><i class="fa-solid fa-check me-1"></i> Toevoegen</button>
      </div>
    </div>
  </div>
</div>

<!-- 2. Bulk Player Modal -->
<div class="modal fade" id="addBulkPlayersModal" tabindex="-1" aria-labelledby="addBulkLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addBulkLabel"><i class="fa-solid fa-list-ul me-2"></i>Bulk Spelers Importeren</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="alert alert-info border-0 shadow-sm">
             <i class="fa-solid fa-circle-info me-2"></i>Plak hier de namen uit bijvoorbeeld je Excel-bestand. Zet <strong>elke speler op een nieuwe regel</strong>.
             <hr class="my-2">
             <div class="small"><i class="fa-solid fa-user-shield me-1"></i> Voor dit formaat (<?= htmlspecialchars($default_format) ?>) geldt een limiet van <b><?= $max_players ?> spelers</b>. Je kunt er nu nog maximaal <b><?= $remaining_players ?></b> toevoegen.</div>
          </div>
          <form id="frmBulkPlayers">
              <input type="hidden" name="action" value="add_bulk_players">
              <div class="mb-3">
                  <textarea name="players_text" class="form-control shadow-sm" style="font-family: monospace; resize: none;" rows="12" placeholder="Jan Peeters&#10;Piet Smet&#10;Kevin De Bruyne..."></textarea>
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-primary fw-bold" onclick="submitApicall('frmBulkPlayers')"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Lijst</button>
      </div>
    </div>
  </div>
</div>

<!-- 3. Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1" aria-labelledby="addCoachLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addCoachLabel"><i class="fa-solid fa-chalkboard-user me-2"></i>Coach Toevoegen</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id="frmCoach">
              <input type="hidden" name="action" value="add_coach">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-muted">NAAM COACH <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" placeholder="Bv. Robin S." required>
                  <div class="form-text mt-2">Deze naam zal standaard als verantwoordelijke op schema's geprint kunnen worden.</div>
              </div>
          </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuleren</button>
        <button type="button" class="btn btn-success fw-bold text-white" onclick="submitApicall('frmCoach')"><i class="fa-solid fa-check me-1"></i> Toevoegen</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkDoelman = document.getElementById('checkDoelman');
    const favPosContainer = document.getElementById('favPosContainer');
    
    if(checkDoelman) {
        checkDoelman.addEventListener('change', function() {
            if (this.checked) {
                favPosContainer.style.display = 'none';
                favPosContainer.querySelector('input').value = ''; // Reset favorite positions
            } else {
                favPosContainer.style.display = 'block';
            }
        });
    }
});

function submitApicall(formId) {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const fd = new FormData(form);
    const btn = event.currentTarget || event.target;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('/api/api_onboarding_add.php', {
        method: 'POST',
        body: fd
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Herlaad the pagina na succes om progressie bij te werken!
            window.location.reload();
        } else {
            alert("Fout: " + (data.error || "Onbekende fout"));
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert("Systeemfout bij opslaan");
        btn.innerHTML = oldHtml;
        btn.disabled = false;
    });
}

function copyShareLink(gameId, hours, btn) {
    let fd = new FormData();
    fd.append('game_id', gameId);
    fd.append('expires_in', hours);
    fetch('/ajax/generate_share_link.php', {method: 'POST', body: fd})
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            navigator.clipboard.writeText(data.link).then(() => {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check text-success me-2"></i> Gekopieerd!';
                setTimeout(() => btn.innerHTML = oldHtml, 2000);
            });
        } else {
            alert(data.error);
        }
    }).catch(e => {
        alert("Kon de link niet kopiëren.");
    });
}

function copyToClipboard(button) {
    try {
        const msg = JSON.parse(button.getAttribute('data-msg'));
        navigator.clipboard.writeText(msg).then(() => {
            const icon = button.querySelector('i');
            const oldClass = icon.className;
            icon.className = 'fa-solid fa-check text-success fs-5';
            button.classList.add('bg-light');
            
            // Toon een duidelijke toast notificatie
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 end-0 p-3';
            toast.style.zIndex = '1050';
            toast.innerHTML = `
                <div class="toast show align-items-center text-bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex px-2 py-1">
                    <div class="toast-body fw-bold" style="font-size: 1rem;">
                      <i class="fa-solid fa-clipboard-check me-2 fa-lg"></i> Bericht gekopieerd! Je kan dit nu plakken in WhatsApp.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.position-fixed').remove()"></button>
                  </div>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                icon.className = oldClass;
                button.classList.remove('bg-light');
            }, 2000);
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 4000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            alert("Kon tekst niet naar klembord kopiëren.");
        });
    } catch (e) {
        console.error('Failed to parse text: ', e);
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
