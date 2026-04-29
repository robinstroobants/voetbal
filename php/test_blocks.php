<?php
require_once __DIR__ . '/core/getconn.php';
$gameId = 111;

$stmtGame = $pdo->prepare("SELECT id, team_id, format, date_format(game_date, '%d/%m/%Y') as game_date_formatted FROM games WHERE id = ?");
$stmtGame->execute([$gameId]);
$game = $stmtGame->fetch(PDO::FETCH_ASSOC);

$format = $game['format'];
$search_format = $format;
$aantal = 10;
$gk_count = 1;

if (strpos($format, 'gk') === false) {
    if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $format, $matches)) {
        $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
    }
}
$full_format = $search_format . "_" . $aantal . "sp";

$parts = explode('_', $search_format);
$time_part = end($parts);
$nr_of_games = 1;
$game_duration_min = 60;
$sub_duration_min_parsed = 15;

if (preg_match('/^(\d+)x(\d+)(.*)$/', $time_part, $m)) {
    $game_duration_min = (int)$m[1] * (int)$m[2];
    $sub_duration_min_parsed = (int)$m[2];
    if (strpos($m[3], 'g') !== false && preg_match('/(\d+)g/', $m[3], $gm)) {
        $nr_of_games = (int)$gm[1];
    }
}

$patterns = [];

if ($sub_duration_min_parsed != $game_duration_min) {
    $blocks = [];
    $part_count = $game_duration_min / $sub_duration_min_parsed;
    for ($i=0; $i<$nr_of_games; $i++) {
        for ($j=0; $j<$part_count; $j++) {
            $blocks[] = $sub_duration_min_parsed;
        }
    }
    $patterns['default'] = [
        'name' => "Standaard: Wissel om de {$sub_duration_min_parsed}m",
        'blocks' => $blocks
    ];
}

$blocks = array_fill(0, $nr_of_games, $game_duration_min);
$patterns['no_sub'] = [
    'name' => "Niet wisselen ($nr_of_games wedstrijden van {$game_duration_min}m)",
    'blocks' => $blocks
];

if ($game_duration_min % 2 == 0 || $game_duration_min == 15) {
    $blocks = [];
    $half = $game_duration_min / 2;
    for ($i=0; $i<$nr_of_games; $i++) {
        $blocks[] = $half;
        $blocks[] = $half;
    }
    if (!isset($patterns['default']) || $patterns['default']['blocks'] !== $blocks) {
        $patterns['half'] = [
            'name' => "Wissel halverwege (Helften van {$half}m)",
            'blocks' => $blocks
        ];
    }
}

$selected_pattern_key = $_GET['pattern'] ?? (isset($patterns['half']) ? 'half' : 'default');
if (!isset($patterns[$selected_pattern_key])) {
    $selected_pattern_key = array_key_first($patterns);
}
$selected_pattern = $patterns[$selected_pattern_key];

$shift_definitions = [];
foreach ($selected_pattern['blocks'] as $dur) {
    $shift_definitions[] = ['duration' => $dur];
}
echo "Number of shift definitions: " . count($shift_definitions) . "\n";
echo "Selected pattern key: " . $selected_pattern_key . "\n";
print_r($shift_definitions);
