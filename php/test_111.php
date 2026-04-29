<?php
$format = '8v8_4x15';
$gk_count = 1;
$search_format = $format;
if (strpos($format, 'gk') === false) {
    if (preg_match('/^(\d+v\d+)_(\d+x\d+.*)$/', $format, $matches)) {
        $search_format = $matches[1] . '_' . $gk_count . 'gk_' . $matches[2];
    }
}
echo "search_format: $search_format\n";
