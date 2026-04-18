<?php
$NUM_SCHEMAS = 50;
$START_ID = 10000;

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

class Solver {
    public $benchSchedule;
    public $fieldPos = [2, 4, 5, 7, 9, 10, 11];
    public $solution = null;
    
    public function solve($blockIdx, $currentAssignments, $playerPosSets) {
        if ($blockIdx == 8) {
            foreach ($playerPosSets as $pid => $arr) {
                if ($pid == 0) continue; 
                $c = count($arr);
                if ($c < 3 || $c > 4) return false;
            }
            $this->solution = $currentAssignments;
            return true;
        }
        
        $benched = $this->benchSchedule[$blockIdx];
        $activePlayers = [];
        for ($i=1; $i<=9; $i++) {
            if (!in_array($i, $benched)) $activePlayers[] = $i;
        }
        
        if ($blockIdx % 2 == 1) {
            // Odd block (mid-quarter). 5 stay, 2 come in.
            $prevLineup = $currentAssignments[$blockIdx - 1];
            $baseAssign = [];
            $openPos = [];
            $newPlayers = [];
            
            // Map the 5 staying players
            foreach ($this->fieldPos as $pos) {
                $prevP = $prevLineup[$pos];
                if (in_array($prevP, $activePlayers)) {
                    $baseAssign[$pos] = $prevP;
                } else {
                    $openPos[] = $pos;
                }
            }
            // Find the 2 new players
            foreach ($activePlayers as $p) {
                if (!in_array($p, $baseAssign)) {
                    $newPlayers[] = $p;
                }
            }
            
            // Should always be 2 openPos and 2 newPlayers
            // We have exactly 2 permutations!
            $perms = [
                [$openPos[0] => $newPlayers[0], $openPos[1] => $newPlayers[1]],
                [$openPos[0] => $newPlayers[1], $openPos[1] => $newPlayers[0]]
            ];
            shuffle($perms);
            
            foreach ($perms as $subAssign) {
                $assign = $baseAssign;
                foreach ($subAssign as $k => $v) $assign[$k] = $v;
                
                $newSets = $playerPosSets;
                $fail = false;
                foreach ($subAssign as $pos => $pid) {
                    $newSets[$pid][$pos] = true;
                    if (count($newSets[$pid]) > 4) { $fail = true; break; }
                }
                if ($fail) continue;
                
                $nextAssignments = $currentAssignments;
                $nextAssignments[$blockIdx] = $assign;
                
                if ($this->solve($blockIdx + 1, $nextAssignments, $newSets)) {
                    return true;
                }
            }
        } else {
            // Even block (quarter start). Free permutation of 7 players into 7 slots.
            $perms = $this->getPermutations($activePlayers);
            shuffle($perms); // Try random assignments
            
            // Limit checks per level so it doesn't run forever
            $attempts = 0;
            foreach ($perms as $perm) {
                if ($attempts++ > 150) break; // Don't try all 5040 recursively, pick 150 branches max
                
                $assign = [];
                $newSets = $playerPosSets;
                $fail = false;
                
                for ($i=0; $i<7; $i++) {
                    $pid = $perm[$i];
                    $pos = $this->fieldPos[$i];
                    $assign[$pos] = $pid;
                    
                    // Allow pruning early
                    $newSets[$pid][$pos] = true;
                    if (count($newSets[$pid]) > 4) { $fail = true; break; }
                }
                if ($fail) continue;
                
                $nextAssignments = $currentAssignments;
                $nextAssignments[$blockIdx] = $assign;
                
                if ($this->solve($blockIdx + 1, $nextAssignments, $newSets)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Quick permutation generator
    private function getPermutations($items) {
        if (empty($items)) { 
            return [[]]; 
        }
        $return = [];
        for ($i = 0; $i < count($items); $i++) {
            $el = $items[$i];
            $subItems = $items;
            array_splice($subItems, $i, 1);
            $subPerms = $this->getPermutations($subItems);
            foreach ($subPerms as $sp) {
                $return[] = array_merge([$el], $sp);
            }
        }
        return $return;
    }
}

$allSchemas = [];
while(count($allSchemas) < $NUM_SCHEMAS) {
    $bs = generateBenchSchedule();
    $solver = new Solver();
    $solver->benchSchedule = $bs;
    $initialSets = [];
    for($i=1; $i<=9; $i++) $initialSets[$i] = [];
    
    if ($solver->solve(0, [], $initialSets)) {
        $schema = [];
        $assigns = $solver->solution;
        $prevLineup = [];
        
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
        
        $id = $START_ID + count($allSchemas);
        $allSchemas[$id] = $schema;
        echo "Found schema #$id\n";
    }
}

$phpCode = "";
foreach ($allSchemas as $id => $schema) {
    ob_start();
    echo "\$ws[$id] = " . var_export($schema, true) . ";\n";
    $phpCode .= ob_get_clean() . "\n";
}
file_put_contents("new_schemas.txt", $phpCode);
echo "Completed total ".$NUM_SCHEMAS." schemas.\n";

