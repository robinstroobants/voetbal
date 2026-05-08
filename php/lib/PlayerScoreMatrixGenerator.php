<?php
/**
 * PlayerScoreMatrixGenerator
 *
 * Genereert een 2D score-matrix [player_id][position_id] => float (0–100)
 * op basis van:
 *   - Algehele spelerskwaliteit  (lage impact)
 *   - Positionele rangschikking  (medium impact)
 *   - Favoriete posities         (hoge impact — bepaalt interne variantie)
 *   - Exclude-lijst              (harde 0)
 *
 * Eenvoudig uitbreidbaar: voeg extra DataSource::add() aanroepen toe
 * in generateMatrix() en pas de gestandaardiseerde gewichten aan.
 */
class PlayerScoreMatrixGenerator
{
    // ─── Gewichten (finetunable) ────────────────────────────────────────────

    /**
     * Maximale bonus voor de algeheel beste speler (0 = slechtste).
     * De bonus schaalt lineair: beste speler = MAX_OVERALL_BONUS, slechtste = 0.
     * Iets verhoogd zodat de beste speler op zijn favoriete positie 100 benadert.
     */
    private float $maxOverallBonus = 15.0;

    /**
     * Basisscores per positie-rang:
     *   rang 1 (beste) → BASE_TOP_SCORE
     *   rang N (slechtste) → BASE_BOTTOM_SCORE
     * Tussenliggende rangen worden lineair geïnterpoleerd.
     *
     * Verhoogd zodat topspelers op hun favoriete positie richting 95-100 gaan.
     * De variabiliteit wordt gehandhaafd via de drop-off curve (zie dropFactorPerRank).
     */
    private float $baseTopScore    = 92.0;
    private float $baseBottomScore = 55.0;

    /**
     * Interne variantie: hoeveel "drop-off" een speler krijgt voor posities
     * die NIET in zijn favorietenlijst staan.
     *
     * Licht verhoogd (0.18 → 0.20) om variabiliteit te bewaren ondanks hogere basis.
     * Bv. 0.20 = elke stap verder van favoriet → -20% van de huidige score.
     * HOGE IMPACT: hogere waarde = grotere kloof tussen beste en slechtste positie.
     */
    private float $dropFactorPerRank = 0.20;

    /**
     * Maximale reductie als gevolg van de drop-off curve (0–1).
     * Een speler scoort nooit minder dan (1 - MAX_DROP) × zijn basesscore
     * (tenzij hij op de exclude-lijst staat → harde 0).
     */
    private float $maxDropFraction = 0.65;

    // ─── State ─────────────────────────────────────────────────────────────

    /** @var array|null Huidige actieve matrix */
    private ?array $currentMatrix = null;

    /** @var array|null Backup van de vorige matrix */
    private ?array $backupMatrix = null;

    // ─── Constructor ────────────────────────────────────────────────────────

    /**
     * @param array $overallRanking    [player_id, ...]  — best → worst
     * @param array $positionRankings  [position_id => ['ranking' => [...], 'exclude' => [...]]]
     * @param array $favoritePositions [player_id => [pos_id, pos_id, ...]]  — favoriet → minst favoriet
     */
    public function __construct(
        private readonly array $overallRanking,
        private readonly array $positionRankings,
        private readonly array $favoritePositions,
    ) {}

    // ─── Publieke API ───────────────────────────────────────────────────────

    /**
     * Genereer de score-matrix.
     * Slaat de huidige matrix op als backup vóór het overschrijven.
     *
     * @return array [player_id][position_id] => float
     */
    public function generateMatrix(): array
    {
        // Backup de huidige matrix (als die bestaat) vóór overschrijven
        if ($this->currentMatrix !== null) {
            $this->backupMatrix = $this->currentMatrix;
        }

        $matrix  = [];
        $nTotal  = count($this->overallRanking);
        $allPositions = array_keys($this->positionRankings);

        foreach ($this->overallRanking as $rank0 => $playerId) {

            // ── 1. Overall-bonus (lage impact) ─────────────────────────────
            // Rang 0 = beste → maxOverallBonus; rang N-1 = slechtste → 0
            $overallBonus = ($nTotal > 1)
                ? $this->maxOverallBonus * (1 - $rank0 / ($nTotal - 1))
                : $this->maxOverallBonus;

            // ── 2. Bouw positie-scores op ──────────────────────────────────
            $favPositions = $this->favoritePositions[$playerId] ?? [];

            foreach ($allPositions as $posId) {

                // Harde 0: speler staat op exclude-lijst voor deze positie
                $excludeList = $this->positionRankings[$posId]['exclude'] ?? [];
                if (in_array($playerId, $excludeList, true)) {
                    $matrix[$playerId][$posId] = 0.0;
                    continue;
                }

                // ── 2a. Positie-basis (medium impact) ──────────────────────
                // Zoek de rang van de speler in de positie-ranking
                $posRanking = $this->positionRankings[$posId]['ranking'] ?? [];
                $posRank    = array_search($playerId, $posRanking, true);
                $nPos       = count($posRanking);

                if ($posRank !== false && $nPos > 1) {
                    // Lineaire interpolatie: rang 0 → baseTopScore, rang N-1 → baseBottomScore
                    $baseScore = $this->baseTopScore
                        - ($posRank / ($nPos - 1)) * ($this->baseTopScore - $this->baseBottomScore);
                } elseif ($posRank === 0) {
                    $baseScore = $this->baseTopScore;
                } else {
                    // Speler staat niet in de positie-ranking → gebruik gemiddelde basissscore
                    $baseScore = ($this->baseTopScore + $this->baseBottomScore) / 2;
                }

                // ── 2b. Interne variantie (hoge impact) ────────────────────
                // Hoe ver staat deze positie van de favorietenlijst van de speler?
                $favRank = array_search($posId, $favPositions, true);

                if ($favRank === false) {
                    // Positie staat NIET in de favorietenlijst → maximale drop
                    $dropMultiplier = 1 - $this->maxDropFraction;
                } else {
                    // Exponentiële drop-off: elke stap verder = dropFactorPerRank minder
                    // favRank = 0 → multiplier 1.0  (favoriete positie, geen drop)
                    // favRank = 1 → multiplier (1 - dropFactor)
                    // favRank = 2 → multiplier (1 - dropFactor)^2
                    $rawMultiplier  = pow(1 - $this->dropFactorPerRank, $favRank);
                    $minMultiplier  = 1 - $this->maxDropFraction;
                    $dropMultiplier = max($rawMultiplier, $minMultiplier);
                }

                $baseScore *= $dropMultiplier;

                // ── 2c. Combineer met overall-bonus ────────────────────────
                $finalScore = $baseScore + $overallBonus;

                // ── 2d. Uitbreidingspunt: extra dataSources ─────────────────
                // Voeg hier toekomstige bonus/malus bronnen toe:
                // $finalScore += $this->applyDataSource1($playerId, $posId);
                // $finalScore += $this->applyDataSource2($playerId, $posId);

                // Clamp naar [0, 100]
                $matrix[$playerId][$posId] = round(min(100.0, max(0.0, $finalScore)), 1);
            }
        }

        $this->currentMatrix = $matrix;
        return $matrix;
    }

    // ─── Backup & Restore ───────────────────────────────────────────────────

    /** @return array|null De huidige actieve matrix */
    public function getMatrix(): ?array
    {
        return $this->currentMatrix;
    }

    /** @return array|null De backup van de vorige generatie */
    public function getBackup(): ?array
    {
        return $this->backupMatrix;
    }

    /**
     * Herstel de backup als actieve matrix.
     * De huidige matrix wordt daarbij zelf de nieuwe backup.
     *
     * @return array|null De herstelde matrix, of null als er geen backup is
     */
    public function restoreBackup(): ?array
    {
        if ($this->backupMatrix === null) {
            return null;
        }
        // Wissel current ↔ backup
        [$this->currentMatrix, $this->backupMatrix] = [$this->backupMatrix, $this->currentMatrix];
        return $this->currentMatrix;
    }

    // ─── Gewichten aanpassen (fluent setters) ───────────────────────────────

    public function setMaxOverallBonus(float $v): self   { $this->maxOverallBonus   = $v; return $this; }
    public function setBaseTopScore(float $v): self       { $this->baseTopScore       = $v; return $this; }
    public function setBaseBottomScore(float $v): self    { $this->baseBottomScore    = $v; return $this; }
    public function setDropFactorPerRank(float $v): self  { $this->dropFactorPerRank  = $v; return $this; }
    public function setMaxDropFraction(float $v): self    { $this->maxDropFraction    = $v; return $this; }
}


// ═══════════════════════════════════════════════════════════════════════════
// TEST SCRIPT — alleen uitvoeren via CLI: php PlayerScoreMatrixGenerator.php
// ═══════════════════════════════════════════════════════════════════════════
if (php_sapi_name() !== 'cli') return;

// ── Mock data: 5 spelers, 4 posities ────────────────────────────────────────

$overallRanking = [
    'P1',   // Beste speler overall
    'P2',
    'P3',
    'P4',
    'P5',   // Slechtste speler overall
];

$positionRankings = [
    'GK' => [
        'ranking' => ['P1', 'P3', 'P5', 'P2', 'P4'],
        'exclude' => ['P4'],   // P4 mag nooit in doel
    ],
    'DEF' => [
        'ranking' => ['P2', 'P1', 'P3', 'P5', 'P4'],
        'exclude' => [],
    ],
    'MID' => [
        'ranking' => ['P3', 'P2', 'P4', 'P1', 'P5'],
        'exclude' => [],
    ],
    'FWD' => [
        'ranking' => ['P4', 'P2', 'P5', 'P1', 'P3'],
        'exclude' => [],
    ],
];

$favoritePositions = [
    'P1' => ['GK', 'DEF'],           // P1 is keeper, goed in verdediging maar verder slecht
    'P2' => ['DEF', 'MID', 'FWD'],   // P2 is veelzijdige verdediger
    'P3' => ['MID', 'DEF'],          // P3 is klassieke middenvelder
    'P4' => ['FWD', 'MID'],          // P4 is aanvaller, wordt nooit keeper
    'P5' => ['MID', 'FWD', 'DEF'],   // P5 is middenvelder, redelijk veelzijdig
];

// ── Genereer eerste matrix ────────────────────────────────────────────────────
$generator = new PlayerScoreMatrixGenerator($overallRanking, $positionRankings, $favoritePositions);
$matrix1   = $generator->generateMatrix();

// ── Druk eerste matrix overzichtelijk af ────────────────────────────────────
$positions = ['GK', 'DEF', 'MID', 'FWD'];
$colW      = 8;
$nameW     = 6;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          SCORE MATRIX — Generatie 1                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo str_pad('', $nameW) . ' │ ';
foreach ($positions as $pos) {
    echo str_pad($pos, $colW);
}
echo "\n" . str_repeat('─', $nameW + 2 + count($positions) * $colW) . "\n";

foreach ($overallRanking as $rank0 => $pid) {
    echo str_pad($pid, $nameW) . ' │ ';
    foreach ($positions as $pos) {
        $score = $matrix1[$pid][$pos] ?? '—';
        $flag  = ($score === 0.0) ? '(0!)' : '';
        echo str_pad($score . $flag, $colW);
    }
    echo "  ← overall rank " . ($rank0 + 1) . "\n";
}

// ── Toon interne variantie per speler ────────────────────────────────────────
echo "\nInterne variantie (max − min per speler, excl. harde nullen):\n";
foreach ($overallRanking as $pid) {
    $scores = array_filter($matrix1[$pid], fn($s) => $s > 0);
    $max    = $scores ? max($scores) : 0;
    $min    = $scores ? min($scores) : 0;
    echo "  $pid: max=$max, min=$min, spread=" . round($max - $min, 1) . "\n";
}

// ── Genereer tweede matrix (backup-test) ─────────────────────────────────────
// Pas de gewichten aan en genereer opnieuw → matrix1 wordt automatisch backup
$matrix2 = $generator
    ->setDropFactorPerRank(0.30)     // Grotere variantie in generatie 2
    ->setMaxOverallBonus(5.0)        // Minder overall-impact
    ->generateMatrix();

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          SCORE MATRIX — Generatie 2 (aangepaste gewichten)  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo str_pad('', $nameW) . ' │ ';
foreach ($positions as $pos) {
    echo str_pad($pos, $colW);
}
echo "\n" . str_repeat('─', $nameW + 2 + count($positions) * $colW) . "\n";

foreach ($overallRanking as $pid) {
    echo str_pad($pid, $nameW) . ' │ ';
    foreach ($positions as $pos) {
        $score = $matrix2[$pid][$pos] ?? '—';
        $flag  = ($score === 0.0) ? '(0!)' : '';
        echo str_pad($score . $flag, $colW);
    }
    echo "\n";
}

// ── Backup testen ────────────────────────────────────────────────────────────
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          BACKUP TESTEN                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

$backup = $generator->getBackup();
echo "Backup bestaat: " . ($backup !== null ? "JA ✓" : "NEE ✗") . "\n";

$restored = $generator->restoreBackup();
echo "Matrix hersteld naar generatie 1: " . ($restored !== null ? "JA ✓" : "NEE ✗") . "\n";

// Vergelijk een cel om te bevestigen dat het echt generatie-1 is
$testCell = $restored['P1']['GK'] ?? null;
$expected = $matrix1['P1']['GK'] ?? null;
echo "P1/GK na herstel: $testCell (verwacht: $expected) → " . ($testCell === $expected ? "OK ✓" : "MISMATCH ✗") . "\n";

echo "\n✅ Test voltooid.\n\n";
