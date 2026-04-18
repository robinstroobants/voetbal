<?php
$f = 'wisselschemas/8v8_1gk_4x15_10sp.php';
$content = file_get_contents($f);

// Split at logic block safely
$logicMarker = 'if (!isset($te_gebruiken_schema)){';
$pos = strpos($content, $logicMarker);
$safeContent = substr($content, 0, $pos);
file_put_contents('safe_debug.php', $safeContent);

require 'safe_debug.php';

echo "Keys total: " . count(array_keys($ws)) . "\n";
echo "Has 404? " . (isset($ws[404]) ? "YES" : "NO") . "\n";
