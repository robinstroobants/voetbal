<?php
require_once dirname(__DIR__) . '/core/getconn.php';
require_once __DIR__ . '/../speelhistoriek.php';

if (php_sapi_name() !== 'cli') {
    die("Alleen via CLI.");
}

echo "Starten met import van speelhistoriek.php...\n";

global $historiek_oude_speelminuten;

// Haal alle spelers op om namen naar id's te mappen (gaan uit van 1 team voor deze hardcoded historiek, of zoeken op first_name)
// Aangezien de historiek voor team 1 is (of het team van Robin), halen we gewoon alle spelers op
$stmtP = $pdo->query("SELECT id, first_name, last_name, team_id FROM players");
$players = [];
while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($players[$row['first_name']])) {
        $players[$row['first_name']] = $row['id'];
    }
}

// Handmatige mapping voor namen die in speelhistoriek.php afwijken van de database
$alias_map = [
    'NoahS' => 19, // Noah Sterckx-Geukens
    'NoahW' => 20, // Noah Willems
    'MuratC' => 8, // Murat Cilingir
    'MuratY' => 9, // Murat Yagmuroglu
];

$count = 0;

foreach ($historiek_oude_speelminuten as $game_key => $data) {
    // Voorbeeld key: "260307_Hades" of "251129_Hades"
    $parts = explode('_', $game_key, 2);
    if (count($parts) !== 2) continue;
    
    $date_str = $parts[0]; // "251129"
    $opponent = $parts[1]; // "Hades"
    
    $year = "20" . substr($date_str, 0, 2);
    $month = substr($date_str, 2, 2);
    $day = substr($date_str, 4, 2);
    $game_date = "$year-$month-$day 00:00:00";
    
    // Zoek de match
    $stmtG = $pdo->prepare("SELECT id FROM games WHERE game_date = ? AND opponent LIKE ? LIMIT 1");
    $stmtG->execute([$game_date, "%$opponent%"]);
    $game_id = $stmtG->fetchColumn();
    
    if (!$game_id) {
        echo "Waarschuwing: Wedstrijd niet gevonden voor key $game_key ($game_date vs $opponent)\n";
        continue;
    }
    
    // Verwijder bestaande logs voor deze game (die we eventueel in de vorige stap foutief berekenden of leeg lieten)
    $pdo->prepare("DELETE FROM game_playtime_logs WHERE game_id = ?")->execute([$game_id]);
    $pdo->prepare("DELETE FROM game_shift_logs WHERE game_id = ?")->execute([$game_id]);
    
    $duration = $data['duration'];
    $playtimes = $data['playtime'];
    
    $stmtTotal = $pdo->prepare("INSERT INTO game_playtime_logs (game_id, player_id, coach_id, seconds_played, seconds_bank, seconds_gk) VALUES (?, ?, NULL, ?, ?, ?)");
    $stmtShift = $pdo->prepare("INSERT INTO game_shift_logs (game_id, player_id, shift_index, position, duration_seconds) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($playtimes as $first_name => $pos_times) {
        if (isset($alias_map[$first_name])) {
            $player_id = $alias_map[$first_name];
        } elseif (isset($players[$first_name])) {
            $player_id = $players[$first_name];
        } else {
            echo "Waarschuwing: Speler $first_name niet gevonden in de database.\n";
            continue;
        }
        
        $seconds_played = 0;
        $seconds_gk = 0;
        $shift_idx = 0;
        
        foreach ($pos_times as $pos => $time) {
            $time = (int)$time;
            if ($time > 0) {
                $seconds_played += $time;
                if ((int)$pos === 1) {
                    $seconds_gk += $time;
                }
                // Simuleer een shift entry voor de volledigheid
                $stmtShift->execute([$game_id, $player_id, $shift_idx, (string)$pos, $time]);
                $shift_idx++;
            }
        }
        
        $seconds_bank = $duration - $seconds_played;
        if ($seconds_bank < 0) $seconds_bank = 0;
        
        // Sla bank shift op
        if ($seconds_bank > 0) {
            $stmtShift->execute([$game_id, $player_id, $shift_idx, 'BANK', $seconds_bank]);
        }
        
        $stmtTotal->execute([$game_id, $player_id, $seconds_played, $seconds_bank, $seconds_gk]);
    }
    
    $count++;
}

echo "Succes! $count wedstrijden uit speelhistoriek.php geïmporteerd in de database.\n";
