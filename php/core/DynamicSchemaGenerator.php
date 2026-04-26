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

    public function generate($squad, $gk_arr, $format, $pattern_key, $use_period = false) {
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
        $num_pos = count($fieldPositions);
        
        if ($num_field_players <= 0 || $num_pos > $num_field_players) {
            // Failsafe: Too few players to even fill the field!
            return false;
        }

        // 4. Load player names and stats
        $placeholders = implode(',', array_fill(0, count($squad), '?'));
        $stmt = $this->pdo->prepare("SELECT id, first_name, display_name FROM players WHERE id IN ($placeholders)");
        $stmt->execute($squad);
        $names = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $names[$row['id']] = $row['display_name'] ?: $row['first_name'];
        }

        $seasonStatsData = $this->matchManager->getSeasonStatsForSelection($this->teamId, $this->gameDate, $squad);

        $ordered_squad = [];
        if ($fixed_gk_idx !== null) {
            $ordered_squad[0] = reset($gk_arr);
        }

        $field_players = [];
        $idx_counter = ($fixed_gk_idx !== null) ? 1 : 0;
        
        foreach ($squad as $pid) {
            if ($fixed_gk_idx !== null && $pid == $ordered_squad[0]) continue;
            
            $ordered_squad[$idx_counter] = $pid;
            $st = $seasonStatsData[$pid] ?? ['played' => 0, 'available' => 0, 'period_played' => 0, 'period_available' => 0];
            $pct_season = ($st['available'] > 0) ? ($st['played'] / $st['available']) : 0;
            $pct_period = ($st['period_available'] > 0) ? ($st['period_played'] / $st['period_available']) : 0;
            $name = strtolower($names[$pid] ?? '');
            
            $field_players[$idx_counter] = [
                'idx' => $idx_counter,
                'pid' => $pid,
                'mins_game' => 0,
                'pct_period' => $pct_period,
                'pct_season' => $pct_season,
                'name' => $name
            ];
            $idx_counter++;
        }

        // 5. Build schema sequentially using greedy heuristic
        $schema_parts = [];
        $current_start = 0;

        foreach ($blocks as $shift_idx => $dur_min) {
            // Sort exactly according to user rules
            uasort($field_players, function($a, $b) use ($use_period) {
                // 1. Minste speelminuten deze wedstrijd
                if (abs($a['mins_game'] - $b['mins_game']) > 0.01) {
                    return $a['mins_game'] <=> $b['mins_game'];
                }
                
                // 2. Minste speelminuten deze periode (indien met periodes gedefinieerd EN de coach heeft die toggle aangevinkt)
                if ($use_period) {
                    if (abs($a['pct_period'] - $b['pct_period']) > 0.001) {
                        return $a['pct_period'] <=> $b['pct_period'];
                    }
                }
                
                // 3. Minste speelminuten dit seizoen
                if (abs($a['pct_season'] - $b['pct_season']) > 0.001) {
                    return $a['pct_season'] <=> $b['pct_season'];
                }
                
                // 4. Naam alfabetisch
                return strcmp($a['name'], $b['name']);
            });

            // Select top N players for the field
            $selected_indexes = array_slice(array_keys($field_players), 0, $num_pos);
            
            $shift_data = [
                'duration' => $dur_min * 60, // seconds
                'start' => $current_start,
                'lineup' => [],
                'bench' => []
            ];

            if ($fixed_gk_idx !== null) {
                $shift_data['lineup'][1] = 0; // Index 0 is GK
            }

            // Assign positions greedily based on player scores to maximize rating
            $available_positions = $fieldPositions;
            foreach ($selected_indexes as $idx) {
                $pid = $field_players[$idx]['pid'];
                $best_pos = -1;
                $best_score = -1;
                $best_pos_key = -1;
                
                foreach ($available_positions as $k => $pos) {
                    $score = $this->playerScores[$pid][$pos] ?? 0;
                    if ($score > $best_score) {
                        $best_score = $score;
                        $best_pos = $pos;
                        $best_pos_key = $k;
                    }
                }
                
                // Even if all scores are 0, pick the first available
                if ($best_pos == -1 && !empty($available_positions)) {
                    $best_pos_key = array_key_first($available_positions);
                    $best_pos = $available_positions[$best_pos_key];
                }
                
                if ($best_pos != -1) {
                    $shift_data['lineup'][$best_pos] = $idx;
                    unset($available_positions[$best_pos_key]);
                }
            }

            // Add remaining to bench and update minutes
            foreach ($field_players as $idx => &$fp) {
                if (in_array($idx, $selected_indexes)) {
                    $fp['mins_game'] += $dur_min;
                } else {
                    $shift_data['bench'][] = $idx;
                }
            }
            unset($fp);

            // Generate subs logic
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
            $current_start += $dur_min;
        }

        // Return perfectly indexed schema + the ordered squad mapping
        ksort($ordered_squad);
        return [
            'schema_parts' => $schema_parts,
            'ordered_squad' => array_values($ordered_squad)
        ];
    }
}
