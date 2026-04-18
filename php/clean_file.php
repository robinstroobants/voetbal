<?php
$f = 'wisselschemas/8v8_1gk_4x15_10sp.php';
$content = file_get_contents($f);

// Find where $ws[10000] starts.
$pos = strpos($content, '$ws[10000] =');
if ($pos !== false) {
    $firstPart = substr($content, 0, $pos);
    
    $logicMarker = 'if (!isset($te_gebruiken_schema)){';
    $logicPos = strpos($content, $logicMarker);
    if ($logicPos !== false) {
        $logicPart = substr($content, $logicPos);
        file_put_contents($f, $firstPart . "\n" . $logicPart);
        echo "Cleaned successfully.\n";
    } else {
        echo "Could not find logic block.\n";
    }
} else {
    echo "10000 not found.\n";
}
