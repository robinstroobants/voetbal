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

    public function generate($squad, $gk_arr, $format, $pattern_key, $use_period = false, $min_pos_req = 0, $compensate_last_match = true) {
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
            $playPositions = [1, 4, 7, 9, 11];
        }
        $fieldPositions = array_values(array_filter($playPositions, fn($p) => $p != 1));
        
        $rotating_gks = ($gk_count === 0);
        $fixed_gk_count = $gk_count;
        
        $num_shifts = count($blocks);
        $num_field_players = $aantal - $fixed_gk_count;
        $num_pos = count($fieldPositions);
        
        if ($num_field_players <= 0 || $num_pos > $num_field_players) {
            // Failsafe: Too few players to even fill the field!
            return false;
        }

        // 4. Load player names and stats
        $placeholders = implode(',', array_fill(0, count($squad), '?'));
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name FROM players WHERE id IN ($placeholders)");
        $stmt->execute($squad);
        $names = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $names[$row['id']] = $row['first_name'] . ' ' . substr($row['last_name'], 0, 1) . '.';
        }

        $seasonStatsData = $this->matchManager->getSeasonStatsForSelection($this->teamId, $this->gameDate, $squad);
        
        $lastMatchMins = [];
        if ($compensate_last_match && !empty($squad)) {
            $stmtLastMatch = $this->pdo->prepare("
                SELECT p.player_id, p.seconds_played 
                FROM game_playtime_logs p
                JOIN games g ON p.game_id = g.id
                WHERE p.player_id IN ($placeholders) 
                  AND g.team_id = ? 
                  AND g.game_date < ?
                ORDER BY g.game_date DESC, g.id DESC
            ");
            $params = array_merge($squad, [$this->teamId, $this->gameDate]);
            $stmtLastMatch->execute($params);
            while ($row = $stmtLastMatch->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($lastMatchMins[$row['player_id']])) {
                    $lastMatchMins[$row['player_id']] = (int)$row['seconds_played'] / 60;
                }
            }
        }

        $ordered_squad = [];
        foreach ($gk_arr as $i => $gk_pid) {
            $ordered_squad[$i] = $gk_pid;
        }

        $field_players = [];
        $idx_counter = $fixed_gk_count;
        
        foreach ($squad as $pid) {
            if (in_array($pid, $gk_arr)) continue;
            
            $ordered_squad[$idx_counter] = $pid;
            $st = $seasonStatsData[$pid] ?? ['played' => 0, 'available' => 0, 'period_played' => 0, 'period_available' => 0, 'gk' => 0, 'period_gk' => 0];
            $pct_season = ($st['available'] > 0) ? ($st['played'] / $st['available']) : 0;
            $pct_period = ($st['period_available'] > 0) ? ($st['period_played'] / $st['period_available']) : 0;
            
            $pct_season_gk = ($st['available'] > 0) ? ($st['gk'] / $st['available']) : 0;
            $pct_period_gk = ($st['period_available'] > 0) ? ($st['period_gk'] / $st['period_available']) : 0;
            
            $name = strtolower($names[$pid] ?? '');
            
            $field_players[$idx_counter] = [
                'idx' => $idx_counter,
                'pid' => $pid,
                'mins_game' => 0,
                'last_match_mins' => $lastMatchMins[$pid] ?? 0,
                'pct_period' => $pct_period,
                'pct_season' => $pct_season,
                'pct_period_gk' => $pct_period_gk,
                'pct_season_gk' => $pct_season_gk,
                'name' => $name,
                'times_gk' => 0,
                'times_benched' => 0,
                'times_field' => 0,
                'played_positions' => [],
                'random' => mt_rand()
            ];
            $idx_counter++;
        }

        // 5. Build schema sequentially using greedy heuristic
        $schema_parts = [];
        $current_start = 0;
        
        $current_game_min = 0;
        $game_idx = 1;
        $game_gks = [];

        foreach ($blocks as $shift_idx => $dur_min) {
            // Determine GK for this game part if rotating
            if ($rotating_gks && !isset($game_gks[$game_idx])) {
                uasort($field_players, function($a, $b) use ($use_period, $compensate_last_match) {
                    // 0. Absolute voorwaarde: Iedereen evenveel keren in doel per WEDSTRIJD
                    if ($a['times_gk'] !== $b['times_gk']) {
                        return $a['times_gk'] <=> $b['times_gk'];
                    }
                    // 1. Minste totale speelminuten vandaag
                    if (abs($a['mins_game'] - $b['mins_game']) > 0.01) {
                        return $a['mins_game'] <=> $b['mins_game'];
                    }
                    // 1.5 Compenseer speeltijd vorige match (indien toggle ON)
                    if ($compensate_last_match) {
                        if (abs($a['last_match_mins'] - $b['last_match_mins']) > 0.01) {
                            return $a['last_match_mins'] <=> $b['last_match_mins'];
                        }
                    }
                    // 2. Minste keren GK deze periode
                    if ($use_period && abs($a['pct_period_gk'] - $b['pct_period_gk']) > 0.001) {
                        return $a['pct_period_gk'] <=> $b['pct_period_gk'];
                    }
                    // 3. Minste keren GK dit seizoen
                    if (abs($a['pct_season_gk'] - $b['pct_season_gk']) > 0.001) {
                        return $a['pct_season_gk'] <=> $b['pct_season_gk'];
                    }
                    // 4. Random variatie (ipv alfabetisch)
                    return $a['random'] <=> $b['random'];
                });
                
                $chosen_gk_idx = array_key_first($field_players);
                $game_gks[$game_idx] = $chosen_gk_idx;
                $field_players[$chosen_gk_idx]['times_gk']++;
            }
            
            if ($rotating_gks) {
                $current_gk_idx = $game_gks[$game_idx];
            } else {
                $current_gk_idx = ($game_idx - 1) % $fixed_gk_count;
            }

            // Sort exactly according to user rules for the FIELD positions
            uasort($field_players, function($a, $b) use ($use_period, $compensate_last_match) {
                // 1. Minste speelminuten deze wedstrijd
                if (abs($a['mins_game'] - $b['mins_game']) > 0.01) {
                    return $a['mins_game'] <=> $b['mins_game'];
                }
                
                // 1.5 Compenseer speeltijd vorige match (indien toggle ON)
                if ($compensate_last_match) {
                    if (abs($a['last_match_mins'] - $b['last_match_mins']) > 0.01) {
                        return $a['last_match_mins'] <=> $b['last_match_mins'];
                    }
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
                
                // 3.5. Meeste keren op de bank (tie-breaker tegenover GKs)
                if ($a['times_benched'] !== $b['times_benched']) {
                    return $b['times_benched'] <=> $a['times_benched']; // Meer benched = hogere prioriteit om te spelen
                }
                
                // 3.6 Minste keren veldspeler
                if ($a['times_field'] !== $b['times_field']) {
                    return $a['times_field'] <=> $b['times_field'];
                }
                
                // 4. Random variatie (ipv alfabetisch)
                return $a['random'] <=> $b['random'];
            });

            // Exclude current GK from field positions
            $available_field_indexes = [];
            foreach (array_keys($field_players) as $idx) {
                if ($idx !== $current_gk_idx) {
                    $available_field_indexes[] = $idx;
                }
            }

            // Select top N players for the field
            $selected_indexes = array_slice($available_field_indexes, 0, $num_pos);
            
            $shift_data = [
                'duration' => $dur_min * 60, // seconds
                'start' => $current_start,
                'game_counter' => $game_idx,
                'lineup' => [],
                'bench' => []
            ];

            if ($current_gk_idx !== null) {
                $shift_data['lineup'][1] = $current_gk_idx; // Index 0/Chosen is GK
            }

            // Assign positions greedily based on player scores to maximize rating
            $available_positions = $fieldPositions;
            
            // PASS 1: Houd spelers op hun huidige positie indien mogelijk, tenzij het een nieuwe wedstrijd is
            $unassigned_indexes = [];
            $is_same_game = ($shift_idx > 0 && $current_game_min > 0);
            $prev_lineup = $is_same_game ? $schema_parts[$shift_idx - 1]['lineup'] : [];
            
            foreach ($selected_indexes as $idx) {
                $kept_pos = false;
                if (!empty($prev_lineup)) {
                    $old_pos = array_search($idx, $prev_lineup);
                    if ($old_pos !== false && in_array($old_pos, $available_positions)) {
                        $shift_data['lineup'][$old_pos] = $idx;
                        $key = array_search($old_pos, $available_positions);
                        if ($key !== false) {
                            unset($available_positions[$key]);
                        }
                        $kept_pos = true;
                    }
                }
                if (!$kept_pos) {
                    $unassigned_indexes[] = $idx;
                }
            }
            
            // PASS 2: Wijs overige spelers toe o.b.v. score
            foreach ($unassigned_indexes as $idx) {
                $pid = $field_players[$idx]['pid'];
                $best_pos = -1;
                $best_score = -9999;
                $best_pos_key = -1;
                
                foreach ($available_positions as $k => $pos) {
                    $score = $this->playerScores[$pid][$pos] ?? 5; // Default score
                    // Add small random noise to prevent identical scores keeping players fixed
                    $score += ($field_players[$idx]['random'] % 10) / 100;
                    
                    // Encourage variety: penalize positions already played in this match
                    if (isset($field_players[$idx]['played_positions'][$pos])) {
                        $unique_played = count($field_players[$idx]['played_positions']);
                        if ($unique_played < $min_pos_req) {
                            $score -= 100.0; // Massive penalty to strictly enforce min_pos
                        } else {
                            $score -= 5.0; // Soft penalty once minimum is met
                        }
                    }
                    
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
            
            ksort($shift_data['lineup']);

            // Add remaining to bench and update minutes and played positions
            foreach ($field_players as $idx => &$fp) {
                if (in_array($idx, $shift_data['lineup'])) {
                    $fp['mins_game'] += $dur_min;
                    $fp['times_field']++;
                    // Track position played
                    $pos_played = array_search($idx, $shift_data['lineup']);
                    if ($pos_played !== false) {
                        $fp['played_positions'][$pos_played] = true;
                    }
                } else if ($idx === $current_gk_idx) {
                    $fp['mins_game'] += $dur_min;
                } else {
                    $shift_data['bench'][] = $idx;
                    $fp['times_benched']++;
                }
            }
            unset($fp);

            // Generate subs logic
            if ($is_same_game) {
                $prev_lineup_subs = $schema_parts[$shift_idx - 1]['lineup'];
                $shift_data['subs'] = ['in' => [], 'out' => []];
                foreach ($prev_lineup_subs as $pos => $speler_oud) {
                    if (isset($shift_data['lineup'][$pos])) {
                        $speler_nieuw = $shift_data['lineup'][$pos];
                        if ($speler_oud !== $speler_nieuw) {
                            $shift_data['subs']['in'][$pos] = $speler_nieuw;
                            $shift_data['subs']['out'][$pos] = $speler_oud;
                        }
                    }
                }
            }

            $schema_parts[$shift_idx] = $shift_data;
            $current_start += $dur_min;
            
            // Track game progression to rotate GK
            $current_game_min += $dur_min;
            if (abs($current_game_min - $game_duration_min) < 0.01) {
                $game_idx++;
                $current_game_min = 0;
            }
        }

        $analysis = [
            'squad_size' => count($squad),
            'field_players' => count($field_players),
            'fixed_gks' => $fixed_gk_count,
            'format' => $search_format,
            'shifts' => $num_shifts,
            'shift_duration' => $dur_min,
            'total_match_mins' => $num_shifts * $dur_min,
            'target_avg_mins' => count($field_players) > 0 ? round((($num_shifts * $dur_min) * $num_pos) / count($field_players), 1) : 0,
            'player_stats' => []
        ];
        
        // Populate player stats for analysis
        foreach ($field_players as $idx => $fp) {
            $analysis['player_stats'][] = [
                'pid' => $fp['pid'],
                'mins_game' => $fp['mins_game'],
                'mins_season' => $fp['mins_season'] ?? 0,
                'pct_season' => $fp['pct_season'] ?? 0,
                'pct_period' => $fp['pct_period'] ?? 0,
                'pct_season_gk' => $fp['pct_season_gk'] ?? 0,
                'pct_period_gk' => $fp['pct_period_gk'] ?? 0,
                'times_gk' => $fp['times_gk'] ?? 0,
                'is_gk' => false
            ];
        }
        foreach ($gk_arr as $idx => $pid) {
            // Estimate GK mins (divided equally if multiple)
            $gk_mins = $fixed_gk_count > 0 ? ($num_shifts * $dur_min) / $fixed_gk_count : 0;
            $st = $seasonStatsData[$pid] ?? ['played' => 0, 'available' => 0, 'period_played' => 0, 'period_available' => 0];
            $pct_season = ($st['available'] > 0) ? ($st['played'] / $st['available']) : 0;
            $pct_period = ($st['period_available'] > 0) ? ($st['period_played'] / $st['period_available']) : 0;
            $analysis['player_stats'][] = [
                'pid' => $pid,
                'mins_game' => $gk_mins,
                'mins_season' => 0,
                'pct_season' => $pct_season,
                'pct_period' => $pct_period,
                'pct_season_gk' => 1.0,
                'pct_period_gk' => 1.0,
                'times_gk' => $num_shifts / max(1, $fixed_gk_count),
                'is_gk' => true
            ];
        }

        // Return perfectly indexed schema + the ordered squad mapping + analysis
        ksort($ordered_squad);
        return [
            'schema_parts' => $schema_parts,
            'ordered_squad' => array_values($ordered_squad),
            'analysis' => $analysis
        ];
    }
}
