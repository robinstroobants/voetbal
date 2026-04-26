<?php

class DynamicSchemaGenerator {
    private $pdo;
    private $teamId;
    private $gameDate;
    private $matchManager;
    private $playerScores;

    public function __construct($pdo, $teamId, $gameDate, $matchManager, $playerScores) {
        $this->pdo = $pdo;
        $this->teamId = $teamId;
        $this->gameDate = $gameDate;
        $this->matchManager = $matchManager;
        $this->playerScores = $playerScores;
    }

    public function generate($squad, $gk_arr, $format, $pattern_key) {
        // 1. Determine match settings based on format
        $gk_count = count($gk_arr);
        $aantal = count($squad);
        
        $search_format = $format;
        if (strpos($format, 'gk') === false) {
            if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $format, $matches)) {
                $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
            }
        }
        
        $nr_of_games = 4;
        $game_duration_min = 15;
        $sub_duration_min_parsed = 15;
        if (preg_match('/_(\d+)x(\d+)(?:_([0-9.]+)min)?$/', $search_format, $m)) {
            $nr_of_games = (int)$m[1];
            $game_duration_min = (int)$m[2];
            $sub_duration_min_parsed = isset($m[3]) ? (float)$m[3] : $game_duration_min;
        }

        // 2. Rebuild the blocks array based on the selected pattern
        $blocks = [];
        if ($pattern_key === 'no_sub') {
            $blocks = array_fill(0, $nr_of_games, $game_duration_min);
        } elseif ($pattern_key === 'half') {
            $half = $game_duration_min / 2;
            for ($i=0; $i<$nr_of_games; $i++) {
                $blocks[] = $half; $blocks[] = $half;
            }
        } elseif ($pattern_key === 'custom_10_5_end' && $game_duration_min == 15 && $nr_of_games >= 2) {
            for ($i=0; $i<$nr_of_games; $i++) {
                if ($i < 2) { $blocks[] = 7.5; $blocks[] = 7.5; }
                else if ($i % 2 == 0) { $blocks[] = 10; $blocks[] = 5; }
                else { $blocks[] = 5; $blocks[] = 10; }
            }
        } elseif ($pattern_key === 'custom_10_5_start' && $game_duration_min == 15 && $nr_of_games >= 2) {
            for ($i=0; $i<$nr_of_games; $i++) {
                if ($i >= 2) { $blocks[] = 7.5; $blocks[] = 7.5; }
                else if ($i % 2 == 0) { $blocks[] = 10; $blocks[] = 5; }
                else { $blocks[] = 5; $blocks[] = 10; }
            }
        } elseif ($pattern_key === 'custom_10_5_all' && $game_duration_min == 15) {
            for ($i=0; $i<$nr_of_games; $i++) {
                if ($i % 2 == 0) { $blocks[] = 10; $blocks[] = 5; }
                else { $blocks[] = 5; $blocks[] = 10; }
            }
        } elseif ($pattern_key === 'custom_5_10_all' && $game_duration_min == 15) {
            for ($i=0; $i<$nr_of_games; $i++) {
                if ($i % 2 == 0) { $blocks[] = 5; $blocks[] = 10; }
                else { $blocks[] = 10; $blocks[] = 5; }
            }
        } else {
            // Default
            $part_count = $game_duration_min / $sub_duration_min_parsed;
            for ($i=0; $i<$nr_of_games; $i++) {
                for ($j=0; $j<$part_count; $j++) {
                    $blocks[] = $sub_duration_min_parsed;
                }
            }
        }

        // 3. Setup positions
        $playPositions = [1, 2, 4, 5, 7, 9, 10, 11];
        if (strpos($search_format, '5v5') !== false) {
            $playPositions = [1, 2, 4, 5, 9];
        }
        $fieldPositions = array_values(array_filter($playPositions, fn($p) => $p != 1));
        
        $fixed_gk_idx = ($gk_count === 1) ? 0 : null; // Index 0 is always the GK if gk_count == 1
        
        $num_shifts = count($blocks);
        $num_field_players = $aantal - ($fixed_gk_idx !== null ? 1 : 0);
        
        if ($num_field_players <= 0 || count($fieldPositions) > $num_field_players) {
            // Failsafe: Too few players to even fill the field!
            return false;
        }

        // 4. Calculate target shifts per index
        $total_field_slots = $num_shifts * count($fieldPositions);
        $base_shifts = floor($total_field_slots / $num_field_players);
        $extra_shifts = $total_field_slots % $num_field_players;

        $index_targets = [];
        $start_idx = ($fixed_gk_idx !== null) ? 1 : 0;
        for ($i = 0; $i < $num_field_players; $i++) {
            $idx = $start_idx + $i;
            $index_targets[$idx] = $base_shifts + ($i < $extra_shifts ? 1 : 0);
        }

        // 5. Generate template with backtracking for "no consecutive benching"
        $template = $this->generateTemplate($blocks, $fieldPositions, $index_targets, $start_idx, $num_field_players);
        
        if (!$template) {
            // If strict no-consecutive benching fails, fallback to simple round-robin
            $template = $this->generateFallbackTemplate($blocks, $fieldPositions, $index_targets, $start_idx, $num_field_players);
        }

        // 6. Map players to indexes based on historical playtime and scores
        $ordered_squad = $this->mapPlayersToTemplate($squad, $gk_arr, $index_targets, $start_idx, $extra_shifts);

        // 7. Format the schema data
        $schema_parts = [];
        $current_start = 0;
        foreach ($blocks as $shift_idx => $dur) {
            $shift_data = [
                'duration' => $dur * 60, // seconds
                'start' => $current_start,
                'lineup' => [],
                'bench' => []
            ];
            
            // GK logic
            if ($fixed_gk_idx !== null) {
                $shift_data['lineup'][1] = 0; // index 0 is GK
            }
            
            // Field players
            $field_indexes_playing = $template[$shift_idx];
            foreach ($fieldPositions as $i => $pos) {
                $shift_data['lineup'][$pos] = $field_indexes_playing[$i];
            }
            
            // Bench
            for ($i = 0; $i < $num_field_players; $i++) {
                $idx = $start_idx + $i;
                if (!in_array($idx, $field_indexes_playing)) {
                    $shift_data['bench'][] = $idx;
                }
            }
            
            // Generate subs array for compatibility
            if ($shift_idx > 0) {
                $prev_lineup = $schema_parts[$shift_idx - 1]['lineup'];
                $shift_data['subs'] = ['in' => [], 'out' => []];
                foreach ($prev_lineup as $pos => $idx) {
                    if (!in_array($idx, $shift_data['lineup'])) {
                        $shift_data['subs']['out'][$pos] = $idx;
                    }
                }
                foreach ($shift_data['lineup'] as $pos => $idx) {
                    if (!in_array($idx, $prev_lineup)) {
                        $shift_data['subs']['in'][$pos] = $idx;
                    }
                }
            }
            
            $schema_parts[$shift_idx] = $shift_data;
            $current_start += $dur;
        }

        return [
            'schema_parts' => $schema_parts,
            'ordered_squad' => $ordered_squad
        ];
    }

    private function generateTemplate($blocks, $fieldPositions, $index_targets, $start_idx, $num_field_players) {
        $num_shifts = count($blocks);
        $num_pos = count($fieldPositions);
        $template = [];
        $current_targets = $index_targets;
        
        $prev_bench = [];
        for ($s = 0; $s < $num_shifts; $s++) {
            $shift_playing = [];
            
            // 1. MUST PLAY: everyone who was benched in previous shift
            foreach ($prev_bench as $idx) {
                if ($current_targets[$idx] > 0) {
                    $shift_playing[] = $idx;
                    $current_targets[$idx]--;
                }
            }
            
            if (count($shift_playing) > $num_pos) {
                return false; // Impossible to satisfy no-consecutive benching
            }
            
            // 2. Fill remaining positions greedily by who needs the most shifts
            $needed = $num_pos - count($shift_playing);
            if ($needed > 0) {
                $available = [];
                for ($i = 0; $i < $num_field_players; $i++) {
                    $idx = $start_idx + $i;
                    if (!in_array($idx, $shift_playing) && $current_targets[$idx] > 0) {
                        $available[$idx] = $current_targets[$idx];
                    }
                }
                
                arsort($available); // highest target first
                $added = 0;
                foreach ($available as $idx => $target) {
                    $shift_playing[] = $idx;
                    $current_targets[$idx]--;
                    $added++;
                    if ($added == $needed) break;
                }
            }
            
            // Update bench for next shift
            $prev_bench = [];
            for ($i = 0; $i < $num_field_players; $i++) {
                $idx = $start_idx + $i;
                if (!in_array($idx, $shift_playing)) {
                    $prev_bench[] = $idx;
                }
            }
            
            $template[$s] = $shift_playing;
        }
        
        // Final sanity check: did everyone meet their target?
        foreach ($current_targets as $remaining) {
            if ($remaining > 0) return false;
        }
        
        return $template;
    }

    private function generateFallbackTemplate($blocks, $fieldPositions, $index_targets, $start_idx, $num_field_players) {
        $num_shifts = count($blocks);
        $num_pos = count($fieldPositions);
        $template = [];
        
        // Simple queue based round-robin
        $queue = [];
        foreach ($index_targets as $idx => $target) {
            for ($i = 0; $i < $target; $i++) {
                $queue[] = $idx;
            }
        }
        
        // Try to interleave to avoid consecutive benching heuristically
        $idx_counts = array_count_values($queue);
        arsort($idx_counts);
        $interleaved = [];
        while (!empty($idx_counts)) {
            $placed = false;
            foreach ($idx_counts as $idx => $count) {
                if ($count > 0) {
                    $interleaved[] = $idx;
                    $idx_counts[$idx]--;
                    if ($idx_counts[$idx] == 0) unset($idx_counts[$idx]);
                    $placed = true;
                }
            }
            if (!$placed) break;
        }
        
        $q_idx = 0;
        for ($s = 0; $s < $num_shifts; $s++) {
            $shift_playing = [];
            for ($p = 0; $p < $num_pos; $p++) {
                if ($q_idx < count($interleaved)) {
                    $shift_playing[] = $interleaved[$q_idx];
                    $q_idx++;
                }
            }
            $template[$s] = $shift_playing;
        }
        
        return $template;
    }

    private function mapPlayersToTemplate($squad, $gk_arr, $index_targets, $start_idx, $extra_shifts) {
        // Sort players by historical playtime
        $seasonStatsData = $this->matchManager->getSeasonStatsForSelection($this->teamId, $this->gameDate, $squad);
        
        $field_players = [];
        $gk_id = (count($gk_arr) == 1) ? reset($gk_arr) : null;
        
        foreach ($squad as $pid) {
            if ($pid == $gk_id) continue;
            
            $st = $seasonStatsData[$pid] ?? ['played' => 0, 'available' => 0];
            $pct = ($st['available'] > 0) ? ($st['played'] / $st['available']) : 0;
            
            $field_players[] = [
                'id' => $pid,
                'pct' => $pct
            ];
        }
        
        // Lowest percentage first -> gets the extra shifts
        usort($field_players, function($a, $b) {
            return $a['pct'] <=> $b['pct'];
        });
        
        $ordered_squad = [];
        if ($gk_id !== null) {
            $ordered_squad[0] = $gk_id;
        }
        
        foreach ($field_players as $i => $fp) {
            // The first $extra_shifts players in the sorted list need the 'extra' shift target.
            // In $index_targets, indexes $start_idx to $start_idx + $extra_shifts - 1 have the +1 shift.
            $ordered_squad[$start_idx + $i] = $fp['id'];
        }
        
        // Ensure array is 0-indexed sequentially
        ksort($ordered_squad);
        return array_values($ordered_squad);
    }
}
