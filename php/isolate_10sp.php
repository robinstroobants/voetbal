<?php
$content = file_get_contents('wisselschemas/8v8_1gk_4x15_10sp.php');
// strip out the logic block at the bottom
$logicMarker = 'if (!isset($te_gebruiken_schema)){';
$pos = strpos($content, $logicMarker);
$safeContent = substr($content, 0, $pos);
file_put_contents('safe_10sp.php', $safeContent);
require 'safe_10sp.php';
echo "Keys from safe file:\n";
print_r(array_keys($ws));
