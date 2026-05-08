<?php
require_once dirname(__DIR__, 1) . '/core/getconn.php';
require_once dirname(__DIR__, 1) . '/lib/PlayerScoreMatrixGenerator.php';

header('Content-Type: application/json');

try {
    $teamId = $_SESSION['team_id'];

    // ── 1. Spelers ophalen ─────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT id, favorite_positions, is_doelman
        FROM players
        WHERE team_id = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$teamId]);
    $playersRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allPlayerIds   = [];
    $isDoelman      = [];
    $favoritePositions = [];

    foreach ($playersRaw as $p) {
        $allPlayerIds[] = $p['id'];
        $isDoelman[$p['id']] = (bool)$p['is_doelman'];

        // favorite_positions is een comma-separated string van positie-nummers
        $favs = [];
        if (!empty($p['favorite_positions'])) {
            $favs = array_map('trim', explode(',', $p['favorite_positions']));
        }
        $favoritePositions[$p['id']] = $favs;
    }

    // ── 2. Overall ranking ophalen ────────────────────────────────────────
    // Volgorde: team_rank ASC → best naar slechtst
    // Doelmannen worden NIET meegenomen in de team_ranking (apart behandeld via gk_scores)
    $stmtTR = $pdo->prepare("
        SELECT ptr.player_id
        FROM player_team_ranking ptr
        JOIN players p ON ptr.player_id = p.id
        WHERE p.team_id = ?
          AND p.deleted_at IS NULL
        ORDER BY ptr.team_rank ASC
    ");
    $stmtTR->execute([$teamId]);
    $overallRanking = $stmtTR->fetchAll(PDO::FETCH_COLUMN);

    // Voeg spelers zonder team_rank achteraan toe (nieuw toegevoegde spelers)
    foreach ($allPlayerIds as $pid) {
        if (!in_array($pid, $overallRanking)) {
            $overallRanking[] = $pid;
        }
    }

    // Doelmannen uit overall ranking filteren (hun score wordt apart bepaald via gk_scores)
    $overallRanking = array_values(array_filter($overallRanking, fn($pid) => !($isDoelman[$pid] ?? false)));

    // ── 3. Positie rankings + exclude-lijst ophalen ───────────────────────
    // Assigned = staat in position_rankings tabel → gesleept naar de "Rank" bak
    // Excluded = veldspeler die NIET in de ranking staat → "Speelt hier NOOIT" bak
    $stmtPR = $pdo->prepare("
        SELECT position_id, player_id
        FROM position_rankings pr
        JOIN players p ON pr.player_id = p.id
        WHERE p.team_id = ?
          AND p.deleted_at IS NULL
        ORDER BY position_id ASC, pos_rank ASC
    ");
    $stmtPR->execute([$teamId]);
    $posRankRows = $stmtPR->fetchAll(PDO::FETCH_ASSOC);

    // Groepeer per positie
    $assignedPerPos = []; // [posId => [pid, pid, ...]]  (best → worst)
    foreach ($posRankRows as $row) {
        $assignedPerPos[$row['position_id']][] = $row['player_id'];
    }

    // Stel de veldspelers-IDs vast (niet-doelmannen)
    $fieldPlayerIds = array_values(array_filter($allPlayerIds, fn($pid) => !($isDoelman[$pid] ?? false)));

    // Bouw $positionRankings op met exclude-lijst voor de generator
    // Positie 1 (keeper) wordt volledig apart afgehandeld via gk_scores → niet in generator
    $positionRankings = [];
    foreach ($assignedPerPos as $posId => $rankedPlayers) {
        if ((int)$posId === 1) continue; // Keeper positie → apart

        // Exclude = veldspelers die NIET in de assigned list staan voor deze positie
        $excludeList = array_values(array_diff($fieldPlayerIds, $rankedPlayers));

        $positionRankings[$posId] = [
            'ranking' => $rankedPlayers,
            'exclude' => $excludeList,
        ];
    }

    // ── 4. Generator aanroepen ────────────────────────────────────────────
    $generator = new PlayerScoreMatrixGenerator(
        overallRanking:    $overallRanking,
        positionRankings:  $positionRankings,
        favoritePositions: $favoritePositions,
    );

    // Optioneel: gewichten aanpassen (defaults zijn goed, maar hier aanpasbaar)
    // $generator->setDropFactorPerRank(0.18);   // sterkte van interne variantie
    // $generator->setMaxOverallBonus(10.0);      // lage impact overall kwaliteit
    // $generator->setBaseTopScore(85.0);
    // $generator->setBaseBottomScore(40.0);

    $matrix = $generator->generateMatrix();

    // ── 5. Doelmannen-scores ophalen (aparte logica, ongewijzigd) ──────────
    $gkScores = $pdo->prepare("SELECT player_id, score FROM gk_scores WHERE player_id IN (
        SELECT id FROM players WHERE team_id = ? AND deleted_at IS NULL
    )");
    $gkScores->execute([$teamId]);
    $gkScoreMap = $gkScores->fetchAll(PDO::FETCH_KEY_PAIR);

    // ── 6. Alle bekende posities bepalen ──────────────────────────────────
    $allPositions = array_map('intval', array_keys($assignedPerPos));
    if (!in_array(1, $allPositions)) $allPositions[] = 1; // Altijd positie 1 meenemen
    sort($allPositions);

    // ── 7. Scores wegschrijven naar DB ────────────────────────────────────
    $pdo->beginTransaction();

    // Verwijder de scores van vandaag (zelfde dag-overschrijf logica als voorheen)
    $pdo->exec("DELETE FROM player_scores WHERE DATE(score_date) = CURDATE()");

    $insertStmt = $pdo->prepare("
        INSERT INTO player_scores (player_id, position, score, score_date)
        VALUES (?, ?, ?, NOW())
    ");

    foreach ($allPlayerIds as $pid) {
        foreach ($allPositions as $posId) {

            if ($posId === 1) {
                // ── Keeper positie: altijd via gk_scores ─────────────────
                if ($isDoelman[$pid] ?? false) {
                    $score = isset($gkScoreMap[$pid]) ? (int)$gkScoreMap[$pid] : 95;
                } else {
                    // Veldspeler: 0 tenzij hij als backup keeper aangeduid is
                    $score = isset($gkScoreMap[$pid]) ? (int)$gkScoreMap[$pid] : 0;
                }
            } elseif ($isDoelman[$pid] ?? false) {
                // ── Doelmannen krijgen 0 op alle veldposities ─────────────
                $score = 0;
            } else {
                // ── Veldspeler: gebruik de generator-output ───────────────
                $score = (int)round($matrix[$pid][$posId] ?? 0);
            }

            $insertStmt->execute([$pid, $posId, $score]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
