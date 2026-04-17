<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$building_lineup = 0;
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

$ws=array();

// --- SCHEMA 11 ---
$ws[11] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 4, // Speler 9 stond hier. Wisselspeler 4 neemt plek in.
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6 // Alleen 6 zit nog op de bank (4 speelt nu)
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 komt erin voor 5
      4  => 8,
      5  => 4, // Blijft staan
      7  => 7, // 7 blijft staan (was wissel met 9, maar 9 is weg en 4 speelt al)
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
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
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8, // Speler 9 stond hier. Wisselspeler 8 neemt plek in.
      11  => 6
    ),
    "bench" => array(
      3 // Alleen 3 zit wissel
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3, // 3 komt erin voor 2
      10  => 8, // Blijft staan
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  

  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 8,
      5  => 4,
      7  => 2,
      9  => 7,
      10  => 1, // Speler 9 stond hier. Wisselspeler 1 neemt plek in.
      11  => 5
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 1, // Blijft staan
      5  => 4,
      7  => 6, // 6 komt erin voor 2 (positie 7)
      9  => 7,
      10  => 1, // Blijft staan
      11  => 5
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        7 => 6
      ),
      "out" => array(
        7 => 2
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
      2  => 7,
      4  => 5, // Speler 9 stond hier. Wisselspeler 5 neemt plek in.
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 komt erin voor 7
      4  => 5, // Blijft staan
      5  => 4, // Was oorspronkelijk wissel 5->4, maar 5 speelt al hele match. Dus 4 blijft staan.
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 


// --- SCHEMA 404 ---
$ws[404] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 4, // 9 stond hier -> 4 pakt plek
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 5
      4  => 8,
      5  => 4, 
      7  => 7, // Geen wissel hier mogelijk
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
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
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8, // 9 stond hier -> 8 pakt plek
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3, // 3 erin voor 2
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1, // 9 stond hier -> 1 pakt plek
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 8, 
      5  => 3,
      7  => 6, // 6 erin voor 2
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        7 => 6
      ),
      "out" => array(
        7 => 2 
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
      2  => 7,
      4  => 5, // 9 stond hier -> 5 pakt plek
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 erin voor 7
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

// --- SCHEMA 600 (Aangepast voor balans 52,5 min) ---
$ws[600] = array(
  // GAME 1: Wissel 6 (bank) -> 5 (eruit)
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 4, // 9 stond hier -> 4 pakt plek
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 5 (Positie 2)
      4  => 8,
      5  => 4,
      7  => 7, 
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2: Wissel 3 (bank) -> 2 (eruit)
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8, // 9 stond hier -> 8 pakt plek
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3, // 3 erin voor 2 (Positie 9)
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3: Wissel 8 (bank) -> 4 (eruit)
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1, // 9 stond hier -> 1 pakt plek
      4  => 6, // 6 speelt (want 8 zit op de bank)
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 6,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 8, // 8 erin voor 4 (Positie 10)
      11  => 5
    ),
    "bench" => array(
      4
    ),
    "subs" => array(
      "in" => array(
        10 => 8
      ),
      "out" => array(
        10 => 4 
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4: Wissel 1 (bank) -> 7 (eruit)
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 5, // 9 stond hier -> 5 pakt plek
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 erin voor 7 (Positie 2)
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
);
// --- SCHEMA 794 ---
$ws[794] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 4, // 9 stond hier -> 4 pakt plek
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 5
      4  => 8,
      5  => 4,
      7  => 7, 
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
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
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8, // 9 stond hier -> 8 pakt plek
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3, // 3 erin voor 2
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1, // 9 stond hier -> 1 pakt plek
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 8,
      5  => 3,
      7  => 6, // 6 erin voor 2
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        7 => 6
      ),
      "out" => array(
        7 => 2 
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
      2  => 7,
      4  => 5, // 9 stond hier -> 5 pakt plek
      5  => 4,
      7  => 6,
      9  => 3, // Oorspronkelijk hier gewisseld met 8, maar 8 speelt nu vast
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 erin voor 7
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 3, 
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

// --- SCHEMA 987 ---
// --- SCHEMA 987 (Aangepast voor balans 52,5 min) ---
$ws[987] = array(
  // GAME 1: Wissel 6 (bank) -> 5 (eruit)
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 5 (Positie 2)
      4  => 8,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2: Wissel 3 (bank) -> 2 (eruit)
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 2,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 3, // 3 erin voor 2 (Positie 9)
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3: Wissel 8 (bank) -> 4 (eruit)
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 6, // 8 zat hier, 6 neemt over want 8 zit op de bank
      5  => 1,
      7  => 2,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 8, // 8 erin voor 4 (Positie 2)
      4  => 6,
      5  => 1,
      7  => 2,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      4
    ),
    "subs" => array(
      "in" => array(
        2 => 8
      ),
      "out" => array(
        2 => 4 
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4: Wissel 1 (bank) -> 7 (eruit)
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 erin voor 7 (Positie 2)
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
);

// --- SCHEMA 1181 (Aangepast voor balans 52,5 min) ---
$ws[1181] = array(
  // GAME 1: Wissel 6 (bank) -> 2 (eruit)
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 8,
      5  => 4,
      7  => 7,
      9  => 5,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 2
      4  => 8,
      5  => 4,
      7  => 7,
      9  => 5,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  // GAME 2: Wissel 5 (bank) -> 8 (eruit)
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5, // 5 erin voor 8
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      8
    ),
    "subs" => array(
      "in" => array(
        4 => 5
      ),
      "out" => array(
         4 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3: Wissel 3 (bank) -> 4 (eruit)
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8,
      11  => 1
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 3, // 3 erin voor 4
      7  => 7,
      9  => 2,
      10  => 8,
      11  => 1
    ),
    "bench" => array(
      4
    ),
    "subs" => array(
      "in" => array(
        5 => 3
      ),
      "out" => array(
        5 => 4 
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  // GAME 4: Wissel 11 (bank) -> 7 (eruit)
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 3,
      7  => 7,
      9  => 2,
      10  => 8,
      11  => 4
    ),
    "bench" => array(
      1
    ), // Let op: Speler 11 in array (waarschijnlijk speler nr 6 in jouw telling, of echt nr 11 als je doortelt)
       // Ik ga uit van de index. Als index 11 speler '3' was in vorig schema, heb ik die hierboven gebruikt.
       // In dit blokje zit speler met ID '11' (positie) op de bank.
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 5,
      5  => 3,
      7  => 1, 
      9  => 2,
      10  => 8,
      11  => 4
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        7 => 1
      ),
      "out" => array(
         7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
);
// --- SCHEMA 1375 ---
$ws[1375] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 4, // 9 stond hier -> 4 pakt plek
      5  => 8,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6, // 6 erin voor 5
      4  => 4,
      5  => 8,
      7  => 7, // Was eerst 4, maar die speelt vast op plek van 9. Dus 7 blijft.
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
      ),
      "out" => array(
         2 => 5
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
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 8, // 9 stond hier -> 8 pakt plek
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 3, // 3 erin voor 2
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
      ),
      "out" => array(
         9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  // GAME 3
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 1, // 9 stond hier -> 1 pakt plek
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 8, 
      5  => 3,
      7  => 6, // 6 erin voor 2
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        7 => 6
      ),
      "out" => array(
        7 => 2 
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
      2  => 7,
      4  => 5, // 9 stond hier -> 5 pakt plek
      5  => 4,
      7  => 6,
      9  => 3, // 3 blijft
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1, // 1 erin voor 7
      4  => 5,
      5  => 4,
      7  => 6,
      9  => 3, // 3 blijft
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
        2 => 1
      ),
      "out" => array(
         2 => 7
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