<?php
$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);


//echo $key;
$ws=array();

$ws[7] = array(
    0 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 0,
            2  => 8,
            4  => 4,
            5  => 6,
            7  => 7,
            9  => 5,
            10 => 9,
            11 => 2
        ),
        "bench"        => array(1, 3),
        "duration"     => 7.5 * 60,
        "game_counter" => 1
    ),
    1 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 1,
            2  => 8,
            4  => 4,
            5  => 6,
            7  => 7,
            9  => 5,
            10 => 9,
            11 => 3
        ),
        "bench"        => array(0, 2),
        "subs"         => array(
            "in"  => array(1 => 1, 11 => 3),
            "out" => array(1 => 0, 11 => 2)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 1
    ),
    2 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 0,
            2  => 2,
            4  => 9,
            5  => 8,
            7  => 5,
            9  => 7,
            10 => 4,
            11 => 3
        ),
        "bench"        => array(1, 6),
        "duration"     => 7.5 * 60,
        "game_counter" => 2
    ),
    3 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 1,
            2  => 2,
            4  => 9,
            5  => 8,
            7  => 5,
            9  => 6,
            10 => 4,
            11 => 3
        ),
        "bench"        => array(0, 7),
        "subs"         => array(
            "in"  => array(1 => 1, 9 => 6),
            "out" => array(1 => 0, 9 => 7)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 2
    ),
    4 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 1,
            2  => 6,
            4  => 9,
            5  => 3,
            7  => 2,
            9  => 7,
            10 => 8,
            11 => 5
        ),
        "bench"        => array(0, 4),
        "duration"     => 7.5 * 60,
        "game_counter" => 3
    ),
    5 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 0,
            2  => 6,
            4  => 9,
            5  => 3,
            7  => 2,
            9  => 7,
            10 => 4,
            11 => 5
        ),
        "bench"        => array(1, 8),
        "subs"         => array(
            "in"  => array(1 => 0, 10 => 4),
            "out" => array(1 => 1, 10 => 8)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 3
    ),
    6 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 1,
            2  => 5,
            4  => 4,
            5  => 6,
            7  => 2,
            9  => 3,
            10 => 8,
            11 => 7
        ),
        "bench"        => array(0, 9),
        "duration"     => 7.5 * 60,
        "game_counter" => 4
    ),
    7 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 0,
            2  => 9,
            4  => 4,
            5  => 6,
            7  => 2,
            9  => 3,
            10 => 8,
            11 => 7
        ),
        "bench"        => array(1, 5),
        "subs"         => array(
            "in"  => array(1 => 0, 2 => 9),
            "out" => array(1 => 1, 2 => 5)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 4
    )
);

$ws[158] = array(
    0 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 0,
            2  => 8,
            4  => 4,
            5  => 6,
            7  => 7,
            9  => 5,
            10 => 9,
            11 => 2
        ),
        "bench"        => array(1, 3),
        "duration"     => 7.5 * 60,
        "game_counter" => 1
    ),
    1 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 1,
            2  => 8,
            4  => 4,
            5  => 6,
            7  => 7,
            9  => 5,
            10 => 9,
            11 => 3
        ),
        "bench"        => array(0, 2),
        "subs"         => array(
            "in"  => array(1 => 1, 11 => 3),
            "out" => array(1 => 0, 11 => 2)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 1
    ),
    2 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 0,
            2  => 2,
            4  => 9,
            5  => 8,
            7  => 5,
            9  => 7,
            10 => 4,
            11 => 3
        ),
        "bench"        => array(1, 6),
        "duration"     => 7.5 * 60,
        "game_counter" => 2
    ),
    3 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 1,
            2  => 2,
            4  => 9,
            5  => 8,
            7  => 5,
            9  => 6,
            10 => 4,
            11 => 3
        ),
        "bench"        => array(0, 7),
        "subs"         => array(
            "in"  => array(1 => 1, 9 => 6),
            "out" => array(1 => 0, 9 => 7)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 2
    ),
    4 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 1,
            2  => 6,
            4  => 9,
            5  => 3,
            7  => 2,
            9  => 7,
            10 => 8,
            11 => 5
        ),
        "bench"        => array(0, 4),
        "duration"     => 7.5 * 60,
        "game_counter" => 3
    ),
    5 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 0,
            2  => 6,
            4  => 9,
            5  => 3,
            7  => 2,
            9  => 7,
            10 => 4,
            11 => 5
        ),
        "bench"        => array(1, 8),
        "subs"         => array(
            "in"  => array(1 => 0, 10 => 4),
            "out" => array(1 => 1, 10 => 8)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 3
    ),
    6 => array(
        "start"        => 0,
        "lineup"       => array(
            1  => 1,
            2  => 5,
            4  => 4,
            5  => 8,
            7  => 2,
            9  => 3,
            10 => 6,
            11 => 7
        ),
        "bench"        => array(0, 9),
        "duration"     => 7.5 * 60,
        "game_counter" => 4
    ),
    7 => array(
        "start"        => 7.5,
        "lineup"       => array(
            1  => 0,
            2  => 9,
            4  => 4,
            5  => 8,
            7  => 2,
            9  => 3,
            10 => 6,
            11 => 7
        ),
        "bench"        => array(1, 5),
        "subs"         => array(
            "in"  => array(1 => 0, 2 => 9),
            "out" => array(1 => 1, 2 => 5)
        ),
        "duration"     => 7.5 * 60,
        "game_counter" => 4
    )
);

$ws[305] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 2,
      9  => 7,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      6,9
    ),
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
      7  => 2, 
      9  => 9,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      3,7
    ),
    "subs" => array(
      "in" => array(
         11 => 6,
           9 => 9
      ),
      "out" => array(
         11 => 3,
           9 => 7
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
      2  => 7,
      4  => 9,
      5  => 1,
      7  => 6,
      9  => 5,
      10  => 8, 
      11  => 3
    ),
    "bench" => array(
      4,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ), 
  3 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 1,
      7  => 6,
      9  => 2, 
      10  => 4,
      11  => 3
    ),
    "bench" => array(
      8,5
    ),
    "subs" => array(
      "in" => array(
        9 => 2,
          10 => 4
      ),
      "out" => array(
         9 => 5,
           10 => 8
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
      2  => 9,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 8,
      11  => 5
    ),
    "bench" => array(
      6,0
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 1,
      2  => 9,
      4  => 4,
      5  => 3,
      7  => 6, // 6 erin voor 2
      9  => 7,
      10  => 8,
      11  => 0
    ),
    "bench" => array(
      2,5
    ),
    "subs" => array(
      "in" => array(
        7 => 6,
          11 => 0
      ),
      "out" => array(
        7 => 2 ,
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
      2  => 9,
      4  => 5, // 9 stond hier -> 5 pakt plek
      5  => 6,
      7  => 7,
      9  => 3, // Oorspronkelijk hier gewisseld met 8, maar 8 speelt nu vast
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      1,4
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 5,
      5  => 6,
      7  => 4,
      9  => 1, 
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,3
    ),
    "subs" => array(
      "in" => array(
        7 => 4
          ,9 =>1
      ),
      "out" => array(
         7 => 7,
         9 => 3
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[499] = array(
  // GAME 1
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 4,
      5  => 0,
      7  => 9,
      9  => 7,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      2
    ),
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
      7  => 9, 
      9  => 2,
      10  => 8,
      11  => 3
    ),
    "bench" => array(
      7
    ),
    "subs" => array(
      "in" => array(
           9 => 2
      ),
      "out" => array(
           9 => 7
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
      2  => 7,
      4  => 9,
      5  => 1,
      7  => 2,
      9  => 5,
      10  => 8, 
      11  => 3
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
      2  => 7,
      4  => 9,
      5  => 1,
      7  => 2,
      9  => 5, 
      10  => 8,
      11  => 4
    ),
    "bench" => array(
      3
    ),
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
      2  => 9,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 8,
      11  => 5
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
      2  => 9,
      4  => 4,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 8,
      11  => 0
    ),
    "bench" => array(
      5
    ),
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
      2  => 9,
      4  => 5, 
      5  => 1,
      7  => 8,
      9  => 3,
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      7
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 5,
      5  => 1,
      7  => 8,
      9  => 3, 
      10  => 4,
      11  => 2
    ),
    "bench" => array(
      1
    ),
    "subs" => array(
      "in" => array(
        5 => 7
      ),
      "out" => array(
         5 => 1
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

if (!isset($te_gebruiken_schema)){
 $te_gebruiken_schema = array_rand($ws);
}
if ($te_gebruiken_schema == 0){
  $te_gebruiken_schema = array_rand($ws);  
}



$wisselschema_index[$key] = $te_gebruiken_schema;
$events[$key][$aantal_spelers] = $ws[$wisselschema_index[$key]];

//$wisselschema_index["8v8_2gk_4x15"] = array_rand($ws);

//$events["8v8_2gk_4x15"][10] = $ws[$wisselschema_index["8v8_2gk_4x15"] ];

?>