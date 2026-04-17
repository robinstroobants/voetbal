<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$building_lineup = 0;
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

$ws=array();

// --- SCHEMA 12 ---
$ws[12] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 5, 
      7  => 4,
      9  => 2,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      1 
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 3,
      5  => 1, 
      7  => 4,
      9  => 2,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         5 => 1
      ),
      "out" => array(
         5 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2
  // 0 Vinn
  // 1 Thibo
  // 2 Miel
  // 3 Tiebe
  // 4 MuratY
  // 5 Arda
  // 6 Wannes
  // 7 Jayden
  // 8 Rune
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 8,
      5  => 7, 
      7  => 2,
      9  => 6,
      10  => 1,
      11  => 5
    ),
    "bench" => array(
      4
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 8,
      5  => 4, 
      7  => 2,
      9  => 6,
      10  => 1,
      11  => 5
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        5 => 4
      ),
      "out" => array(
         5 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  

  // GAME 3
  // 0 Vinn
  // 1 Thibo
  // 2 Miel
  // 3 Tiebe
  // 4 MuratY
  // 5 Arda
  // 6 Wannes
  // 7 Jayden
  // 8 Rune
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 4,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      0
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 4,
      10  => 3,
      11  => 0
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        11 => 0
      ),
      "out" => array(
        11 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4
  // 0 Vinn
  // 1 Thibo
  // 2 Miel
  // 3 Tiebe
  // 4 MuratY
  // 5 Arda
  // 6 Wannes
  // 7 Jayden
  // 8 Rune
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 2,
      2  => 4,
      4  => 1,
      5  => 7,
      7  => 0,
      9  => 3,
      10  => 6,
      11  => 5
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 2,
      2  => 4,
      4  => 1,
      5  => 7,
      7  => 0,
      9  => 8,
      10  => 6,
      11  => 5
    ),
    "bench" => array(
      3
    ),
    "subs" => array(
      "in" => array(
        9 => 8
      ),
      "out" => array(
         9 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[227] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 8,
      9  => 6,
      10  => 7,
      11  => 3
    ),
    "bench" => array(2),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 8, 
      9  => 2,
      10  => 7,
      11  => 3
    ),
    "bench" => array(6),
    "subs" => array(
      "in" => array(
           9 => 2
      ),
      "out" => array(
           9 => 6
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 1,
      7  => 2,
      9  => 5,
      10  => 7, 
      11  => 3
    ),
    "bench" => array(4),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 1,
      7  => 2,
      9  => 5, 
      10  => 7,
      11  => 4
    ),
    "bench" => array(3),
    "subs" => array(
      "in" => array(
          11 => 4
      ),
      "out" => array(
           11 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 5
    ),
    "bench" => array(0),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 8,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 0
    ),
    "bench" => array(5),
    "subs" => array(
      "in" => array(
          11 => 0
      ),
      "out" => array(
        11 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 5, 
      5  => 1,
      7  => 7,
      9  => 3,
      10  => 4,
      11  => 2
    ),
    "bench" => array(6),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 5,
      5  => 6,
      7  => 7,
      9  => 3, 
      10  => 4,
      11  => 2
    ),
    "bench" => array(1),
    "subs" => array(
      "in" => array(
        5 => 6
      ),
      "out" => array(
         5 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 








// LOGICA ONDERAAN
if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}

$wisselschema_index[$key] = $te_gebruiken_schema;
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];

$positions_per_index = array();
foreach($ws[$wisselschema_index[$key]] as $blokjes){
  foreach($blokjes["lineup"] as $p => $i){
    $positions_per_index[$i][$p] = $p;
  }
}
$wisselschema_meta[$key]["positions"] = $positions_per_index;

if ($te_gebruiken_schema == 11){
}
?>