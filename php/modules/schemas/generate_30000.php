<?php
require_once dirname(__DIR__, 2) . '/core/getconn.php';
require_once dirname(__DIR__, 2) . '/models/MatchManager.php';
$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getSelection(47);

$selectie = array_filter(array_map('trim', explode(',', $matchData['selectie'])));
$gk = array_filter(array_map('trim', explode(',', $matchData['doelmannen'])));
$squad = array_values(array_merge($gk, $selectie));
$player_scores = $matchData['player_scores'];

$map = []; 
for($i=0; $i<9; $i++) {
    $map[$i+1] = $squad[$i+1];
}

$NUM_SCHEMAS = 5;
$START_ID = 30000;

function isValidBenchSchedule($benchSpots) {
    foreach ($benchSpots as $pid => $blocks) {
        sort($blocks);
        for ($i = 0; $i < count($blocks) - 1; $i++) {
            if ($blocks[$i+1] == $blocks[$i] + 1) return false;
        }
    }
    return true;
}

function generateBenchSchedule() {
    $players = [1,2,3,4,5,6,7,8,9];
    while (true) {
        shuffle($players);
        $spotsNeeded = [];
        $p1 = $players[0]; $p2 = $players[1];
        foreach ($players as $p) $spotsNeeded[$p] = ($p == $p1 || $p == $p2) ? 1 : 2;
        $pool = [];
        foreach ($spotsNeeded as $p => $n) for ($i=0; $i<$n; $i++) $pool[] = $p;
        shuffle($pool);
        $blocks = array_fill(0, 8, []);
        $valid = true; $idx = 0;
        for ($b=0; $b<8; $b++) {
            $pA = $pool[$idx++]; $pB = $pool[$idx++];
            if ($pA == $pB) { $valid = false; break; }
            $blocks[$b] = [$pA, $pB];
        }
        if (!$valid) continue;
        $benchSpotsByPlayer = [];
        for ($b=0; $b<8; $b++) foreach ($blocks[$b] as $p) $benchSpotsByPlayer[$p][] = $b;
        if (isValidBenchSchedule($benchSpotsByPlayer)) return $blocks;
    }
}

function getPermutations($items) {
    if (empty($items)) return [[]]; 
    $return = [];
    for ($i = 0; $i < count($items); $i++) {
        $el = $items[$i];
        $subItems = $items;
        array_splice($subItems, $i, 1);
        $subPerms = getPermutations($subItems);
        foreach ($subPerms as $sp) {
            $return[] = array_merge([$el], $sp);
        }
    }
    return $return;
}

function calcBlockScore($lineup, $map, $player_scores) {
    $score = 0;
    foreach($lineup as $pos => $pid) {
        if ($pid == 0) continue; 
        $real_pid = $map[$pid];
        if (isset($player_scores[$real_pid][$pos])) {
            $score += $player_scores[$real_pid][$pos] * 7.5;
        }
    }
    return $score;
}

class Solver {
    public $benchSchedule;
    public $fieldPos = [2, 4, 5, 7, 9, 10, 11];
    public $solution = null;
    public $map;
    public $player_scores;
    public $bestScoreSoFar = -1;
    public $bestAssignments = null;
    
    // We want the HIGHEST score that satisfies min 2 positions.
    // We will do DFS but instead of returning exactly 1, we traverse to find best.
    // Or simpler: at each step we pick the absolute highest scoring valid paths,
    // and if we find a valid completely generated match, we compare the total score.
    
    public function solve($blockIdx, $currentAssignments, $playerPosSets, $currentScore) {
        if ($blockIdx == 8) {
            foreach ($playerPosSets as $pid => $arr) {
                if ($pid == 0) continue; 
                $c = count($arr);
                // MIN 2 POSITIES CONSTRAINT:
                if ($c < 2) return false;
            }
            if ($currentScore > $this->bestScoreSoFar) {
                $this->bestScoreSoFar = $currentScore;
                $this->bestAssignments = $currentAssignments;
            }
            return true; // we found one, can keep searching branches if we want
        }
        
        $benched = $this->benchSchedule[$blockIdx];
        $activePlayers = [];
        for ($i=1; $i<=9; $i++) {
            if (!in_array($i, $benched)) $activePlayers[] = $i;
        }
        
        if ($blockIdx % 2 == 1) {
            $prevLineup = $currentAssignments[$blockIdx - 1];
            $baseAssign = [];
            $openPos = [];
            $newPlayers = [];
            
            foreach ($this->fieldPos as $pos) {
                $prevP = $prevLineup[$pos];
                if (in_array($prevP, $activePlayers)) {
                    $baseAssign[$pos] = $prevP;
                } else {
                    $openPos[] = $pos;
                }
            }
            foreach ($activePlayers as $p) {
                if (!in_array($p, $baseAssign)) {
                    $newPlayers[] = $p;
                }
            }
            
            $perms = [
                [$openPos[0] => $newPlayers[0], $openPos[1] => $newPlayers[1]],
                [$openPos[0] => $newPlayers[1], $openPos[1] => $newPlayers[0]]
            ];
            
            $branches = [];
            foreach ($perms as $subAssign) {
                $assign = $baseAssign;
                foreach ($subAssign as $k => $v) $assign[$k] = $v;
                $lineupScore = calcBlockScore($assign, $this->map, $this->player_scores);
                $branches[] = ['assign' => $assign, 'score' => $lineupScore];
            }
            
            // Sort branches by score DESC
            usort($branches, function($a, $b) { return $b['score'] <=> $a['score']; });
            
            foreach($branches as $branch) {
                $assign = $branch['assign'];
                $sScore = $branch['score'];
                
                $newSets = $playerPosSets;
                foreach($assign as $pos => $pid) $newSets[$pid][$pos] = true;
                
                $nextAssignments = $currentAssignments;
                $nextAssignments[$blockIdx] = $assign;
                
                $this->solve($blockIdx + 1, $nextAssignments, $newSets, $currentScore + $sScore);
            }
        } else {
            $perms = getPermutations($activePlayers);
            $branches = [];
            foreach ($perms as $perm) {
                $assign = [];
                for ($i=0; $i<7; $i++) $assign[$this->fieldPos[$i]] = $perm[$i];
                $lineupScore = calcBlockScore($assign, $this->map, $this->player_scores);
                $branches[] = ['assign' => $assign, 'score' => $lineupScore];
            }
            
            // Sort by score DESC locally
            usort($branches, function($a, $b) { return $b['score'] <=> $a['score']; });
            
            // Only search the top 5 highest scoring permutations to avoid massive performance penalty
            // By only searching the top 2 heavily optimized quarter configurations, we keep speed high
            // but might dip into 2nd best if the absolute best forces a player < 2 positions.
            $topBranches = array_slice($branches, 0, 2);
            
            foreach ($topBranches as $branch) {
                $assign = $branch['assign'];
                $sScore = $branch['score'];
                
                $newSets = $playerPosSets;
                foreach($assign as $pos => $pid) $newSets[$pid][$pos] = true;
                
                $nextAssignments = $currentAssignments;
                $nextAssignments[$blockIdx] = $assign;
                
                $this->solve($blockIdx + 1, $nextAssignments, $newSets, $currentScore + $sScore);
            }
        }
        
        return ($this->bestScoreSoFar > -1);
    }
}

$bestSchemas = [];
ini_set('max_execution_time', 0);

// We will generate 30 distinct base schedules, optimize each, and take the Top 5.
for ($iter = 0; $iter < 30; $iter++) {
    $bs = generateBenchSchedule();
    
    $solver = new Solver();
    $solver->benchSchedule = $bs;
    $solver->map = $map;
    $solver->player_scores = $player_scores;
    
    $initialSets = [];
    for($i=1; $i<=9; $i++) $initialSets[$i] = [];
    
    $solver->solve(0, [], $initialSets, 0);
    
    if ($solver->bestScoreSoFar > -1) {
        $bestSchemas[] = [
            'score' => $solver->bestScoreSoFar,
            'assignments' => $solver->bestAssignments,
            'benchSchedule' => $bs
        ];
    }
}

usort($bestSchemas, function($a, $b) { return $b['score'] <=> $a['score']; });
$top5 = array_slice($bestSchemas, 0, $NUM_SCHEMAS);
$outputSchemas = [];

foreach($top5 as $idx => $sObj) {
    if (empty($sObj)) continue;
    $schema = [];
    $prevLineup = [];
    $bs = $sObj['benchSchedule'];
    $assigns = $sObj['assignments'];
    
    for($b=0; $b<8; $b++) {
        $benched = $bs[$b];
        $lineup = $assigns[$b];
        $lineup[1] = 0; 
        ksort($lineup);
        
        $subIn = []; $subOut = [];
        if ($b % 2 == 1 && $b > 0) {
            foreach($lineup as $pos => $pid) {
                if (isset($prevLineup[$pos]) && $prevLineup[$pos] != $lineup[$pos]) {
                    $subOut[$pos] = $prevLineup[$pos];
                    $subIn[$pos] = $lineup[$pos];
                }
            }
        }
        
        $part = [
            "start" => ($b % 2 == 0) ? 0 : 7.5,
            "lineup" => $lineup,
            "bench" => count($benched) > 0 ? array_values($benched) : [],
        ];
        if (!empty($subIn)) {
            $part["subs"] = ["in" => $subIn, "out" => $subOut];
        }
        $part["duration"] = 7.5 * 60;
        $part["game_counter"] = floor($b / 2) + 1;
        
        $schema[$b] = $part;
        $prevLineup = $lineup;
    }
    $outputSchemas[$START_ID + $idx] = $schema;
}

$phpCode = "";
foreach ($outputSchemas as $id => $schema) {
    ob_start();
    echo "\$ws[$id] = " . var_export($schema, true) . ";\n";
    $phpCode .= ob_get_clean() . "\n";
}
file_put_contents("schemas_30000.txt", $phpCode);
echo "Generated top 5 schemas (Min 2 pos). Max Score: " . $top5[0]['score'] . "\n";
