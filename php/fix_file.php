<?php
$f = 'wisselschemas/8v8_1gk_4x15_10sp.php';
$content = file_get_contents($f);

// The logic block to move
$logic = 'if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}



$wisselschema_index[$key] = $te_gebruiken_schema;
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];

$positions_per_index = array();
foreach($ws[$wisselschema_index[$key]] as $blokjes){
  //dpr($blokjes["lineup"]);
  foreach($blokjes["lineup"] as $p => $i){
    $positions_per_index[$i][$p] = $p;
  }
}
$wisselschema_meta[$key]["positions"] = $positions_per_index;';

// Remove the logic from wherever it is (note: exact string match might fail if line endings differ, let's use regex or str_replace carefully)
$clean = str_replace($logic, '', $content);
$clean .= "\n\n" . $logic . "\n";
file_put_contents($f, $clean);
