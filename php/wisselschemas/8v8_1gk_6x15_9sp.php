<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);

$ws=array();

$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

/*
$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 0,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
        2 => 0
      ),
      "out" => array(
        2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  
  2 => array(
    "start" => 15,
    "lineup" => array(
      1  => 7,
      2  => 3,
      4  => 1,
      5  => 0,
      7  => 5,
      9  => 8,
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 22.5,    
    "lineup" => array(
      1  => 7,
      2  => 6,
      4  => 1,
      5  => 0,
      7  => 5,
      9  => 8,
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      0
    ),
    "subs" => array(
      "in" => array(
        2 => 6
      ),
      "out" => array(
        2 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  
  4 => array(
    "start" => 30,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  
  
  5 => array(
    "start" => 37.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
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
  
  6 => array(
    "start" => 45,
    "lineup" => array(
      1  => 4,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  
  
  7 => array(
    "start" => 52.5,    
    "lineup" => array(
      1  => 4,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 2,
      11  => 6
    ),
    "bench" => array(
      1
    ),
    "subs" => array(
      "in" => array(
        10 => 2
      ),
      "out" => array(
        10 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
    
);
$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 0,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
        2 => 0
      ),
      "out" => array(
        2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  
  2 => array(
    "start" => 15,
    "lineup" => array(
      1  => 7,
      2  => 3,
      4  => 1,
      5  => 4,
      7  => 5,
      9  => 8,
      10  => 0,
      11  => 2
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 22.5,    
    "lineup" => array(
      1  => 7,
      2  => 6,
      4  => 1,
      5  => 4,
      7  => 5,
      9  => 8,
      10  => 0,
      11  => 2
    ),
    "bench" => array(
      0
    ),
    "subs" => array(
      "in" => array(
        2 => 6
      ),
      "out" => array(
        2 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  
  4 => array(
    "start" => 30,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  
  
  5 => array(
    "start" => 37.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
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
  
  6 => array(
    "start" => 45,
    "lineup" => array(
      1  => 4,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  
  
  7 => array(
    "start" => 52.5,    
    "lineup" => array(
      1  => 4,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 5,
      10  => 2,
      11  => 6
    ),
    "bench" => array(
      1
    ),
    "subs" => array(
      "in" => array(
        10 => 2
      ),
      "out" => array(
        10 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
    
);
$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      0
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 0,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 6,
      10  => 7,
      11  => 8
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
        2 => 0
      ),
      "out" => array(
        2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  
  2 => array(
    "start" => 15,
    "lineup" => array(
      1  => 7,
      2  => 3,
      4  => 1,
      5  => 4,
      7  => 5,
      9  => 8,
      10  => 0,
      11  => 2
    ),
    "bench" => array(
      6
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 22.5,    
    "lineup" => array(
      1  => 7,
      2  => 6,
      4  => 1,
      5  => 4,
      7  => 5,
      9  => 8,
      10  => 0,
      11  => 2
    ),
    "bench" => array(
      0
    ),
    "subs" => array(
      "in" => array(
        2 => 6
      ),
      "out" => array(
        2 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  
  4 => array(
    "start" => 30,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
    ),
    "bench" => array(
      8
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  
  
  5 => array(
    "start" => 37.5,    
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 2,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 3
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
  
  6 => array(
    "start" => 45,
    "lineup" => array(
      1  => 5,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 4,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  
  
  7 => array(
    "start" => 52.5,    
    "lineup" => array(
      1  => 5,
      2  => 0,
      4  => 7,
      5  => 3,
      7  => 8,
      9  => 4,
      10  => 2,
      11  => 6
    ),
    "bench" => array(
      1
    ),
    "subs" => array(
      "in" => array(
        10 => 2
      ),
      "out" => array(
        10 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
    
);
*/

$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 8,
      10  => 7,
      11  => 6
    ),
    "bench" => array(
      0
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 0,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 8,
      10  => 7,
      11  => 6
    ),
    "bench" => array(
      5
    ),
    "subs" => array(
      "in" => array(
        2 => 0
      ),
      "out" => array(
        2 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 3,
      2  => 8,
      4  => 4,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 0
    ),
    "bench" => array(
      2
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 3,
      2  => 2,
      4  => 4,
      5  => 6,
      7  => 5,
      9  => 1,
      10  => 7,
      11  => 0
    ),
    "bench" => array(
      8
    ),
    "subs" => array(
      "in" => array(
        2 => 2
      ),
      "out" => array(
        2 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 3,
      5  => 2,
      7  => 0,
      9  => 8,
      10  => 4,
      11  => 6
    ),
    "bench" => array(
      7
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 3,
      5  => 2,
      7  => 0,
      9  => 8,
      10  => 7,
      11  => 6
    ),
    "bench" => array(
      4
    ),
    "subs" => array(
      "in" => array(
        10 => 7
      ),
      "out" => array(
        10 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 3,
      2  => 5,
      4  => 4,
      5  => 8,
      7  => 6,
      9  => 2,
      10  => 7,
      11  => 0
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
      1  => 3,
      2  => 5,
      4  => 4,
      5  => 8,
      7  => 1,
      9  => 2,
      10  => 7,
      11  => 0
    ),
    "bench" => array(
      6
    ),
    "subs" => array(
      "in" => array(
        7 => 1
      ),
      "out" => array(
        7 => 6
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  8 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 2,
      4  => 4,
      5  => 5,
      7  => 0,
      9  => 8,
      10  => 7,
      11  => 6
    ),
    "bench" => array(
      3
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  9 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 3,
      4  => 4,
      5  => 5,
      7  => 0,
      9  => 8,
      10  => 7,
      11  => 6
    ),
    "bench" => array(
      2
    ),
    "subs" => array(
      "in" => array(
        2 => 3
      ),
      "out" => array(
        2 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 5
  ),
  
);

$wisselschema_index[$key] = array_rand($ws);
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];


?>