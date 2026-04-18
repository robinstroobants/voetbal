<?php
require_once 'getconn.php';
require_once 'MatchManager.php';
$matchManager = new MatchManager($pdo);
$matchData = $matchManager->getSelection(47);

$selectie = array_filter(array_map('trim', explode(',', $matchData['selectie'])));
$gk = array_filter(array_map('trim', explode(',', $matchData['doelmannen'])));
$squad = array_values(array_merge($gk, $selectie));
$player_scores = $matchData['player_scores'];

$gk_player = $squad[0];
$field_players = array_slice($squad, 1);
$map = []; // Map placeholder 1..9 to actual DB ids
for($i=0; $i<9; $i++) {
    $map[$i+1] = $field_players[$i];
}

$NUM_SCHEMAS = 5;
$START_ID = 20000;

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

$bestSchemas = [];
ini_set('max_execution_time', 0);
for ($iter = 0; $iter < 100; $iter++) {
    $bs = generateBenchSchedule();
    $fieldPos = [2, 4, 5, 7, 9, 10, 11];
    
    $currentAssignments = [];
    $totalScore = 0;
    $valid = true;
    
    for ($blockIdx = 0; $blockIdx < 8; $blockIdx++) {
        $benched = $bs[$blockIdx];
        $activePlayers = [];
        for ($i=1; $i<=9; $i++) {
            if (!in_array($i, $benched)) $activePlayers[] = $i;
        }
        
        $bestPerm = null;
        $bestScore = -1;
        
        if ($blockIdx % 2 == 1) {
            // Mid-quarter subs
            $prevLineup = $currentAssignments[$blockIdx - 1];
            $baseAssign = [];
            $openPos = [];
            $newPlayers = [];
            
            foreach ($fieldPos as $pos) {
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
            
            foreach ($perms as $subAssign) {
                $assign = $baseAssign;
                foreach ($subAssign as $k => $v) $assign[$k] = $v;
                $lineupScore = calcBlockScore($assign, $map, $player_scores);
                if ($lineupScore > $bestScore) {
                    $bestScore = $lineupScore;
                    $bestPerm = $assign;
                }
            }
        } else {
            // Quarter start
            $perms = getPermutations($activePlayers);
            foreach ($perms as $perm) {
                $assign = [];
                for ($i=0; $i<7; $i++) $assign[$fieldPos[$i]] = $perm[$i];
                $lineupScore = calcBlockScore($assign, $map, $player_scores);
                if ($lineupScore > $bestScore) {
                    $bestScore = $lineupScore;
                    $bestPerm = $assign;
                }
            }
        }
        
        $currentAssignments[$blockIdx] = $bestPerm;
        $totalScore += $bestScore;
    }
    
    // Store in best array
    $schemaObj = [
        'score' => $totalScore,
        'assignments' => $currentAssignments,
        'benchSchedule' => $bs
    ];
    $bestSchemas[] = $schemaObj;
}

// Sort by score
usort($bestSchemas, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

$top5 = array_slice($bestSchemas, 0, $NUM_SCHEMAS);
$outputSchemas = [];

foreach($top5 as $idx => $sObj) {
    $schema = [];
    $prevLineup = [];
    $bs = $sObj['benchSchedule'];
    $assigns = $sObj['assignments'];
    
    for($b=0; $b<8; $b++) {
        $benched = $bs[$b];
        $lineup = $assigns[$b];
        $lineup[1] = 0; // GK
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
file_put_contents("best_schemas.txt", $phpCode);
echo "Generated top 5 schemas. Max DB Score: " . $top5[0]['score'] . "\n";
