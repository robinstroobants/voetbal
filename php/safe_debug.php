<?php

$ws_fname = str_replace("sp.php","",basename(__FILE__));
$key_parts = explode("_",$ws_fname);
$aantal_spelers = array_pop($key_parts);
$key = implode("_",$key_parts);

$ws=array();

$ws[11] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  

  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 8,
      5  => 4,
      7  => 2,
      9  => 7,
      10  => 9,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 

  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 1,
      5  => 4,
      7  => 6,
      9  => 7,
      10  => 9,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 
$ws[207] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 8,
      5  => 9,
      7  => 6,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 4
        , 7 => 6
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 




$ws[404] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[600] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[794] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[987] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 8,
      5  => 9,
      7  => 2,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 9,
      7  => 6,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[1181] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 4,
      7  => 2,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 4,
      7  => 6,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[1375] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 9,
      5  => 8,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 9,
      5  => 8,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[1568] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 7,
      7  => 4,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      5  => 7,
      7  => 4,
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 3,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 1,
      5  => 3,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 4,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 
  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 1,
      4  => 9,
      5  => 5,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        2 => 1
        , 5 => 5
      ),
      "out" => array(
         2 => 7
        , 5 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 


$ws[7777] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 5,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 9,
      4  => 8,
      5  => 6,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,5
    ),
    "subs" => array(
      "in" => array(
         5 => 6
        , 7 => 7
      ),
      "out" => array(
         5 => 5
        , 7 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 1,
      5  => 5,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 1,
      5  => 9,
      7  => 4,
      9  => 7,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 2 => 6
      ),
      "out" => array(
        2 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 9,
      5  => 7,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 5
    ),
    "bench" => array(
      4, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 2,
      4  => 9,
      5  => 1,
      7  => 6,
      9  => 8,
      10  => 3,
      11  => 4
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        ,11 => 4
      ),
      "out" => array(
         5 => 7
        , 11 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 

$ws[8888] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 7,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      4,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         2 => 6
        , 7 => 4
      ),
      "out" => array(
         2 => 5
        , 7 => 7
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  
  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 1,
      5  => 4,
      7  => 7,
      9  => 2,
      10  => 9,
      11  => 6
    ),
    "bench" => array(
      8,3
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
      9  => 3,
      10  => 8,
      11  => 6
    ),
    "bench" => array(
      9,2
    ),
    "subs" => array(
      "in" => array(
        9 => 3
        , 10 => 8
      ),
      "out" => array(
         9 => 2
        , 10 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 8,
      5  => 9,
      7  => 2,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      1,6
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ), 
  5 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 3,
      4  => 1,
      5  => 9,
      7  => 6,
      9  => 7,
      10  => 4,
      11  => 5
    ),
    "bench" => array(
      8,2
    ),
    "subs" => array(
      "in" => array(
        4 => 1
        , 7 => 6
      ),
      "out" => array(
        7 => 2 
        , 4 =>  8

      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),   
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 9,
      5  => 7,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      5, 1
     
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ), 

  7 => array(
    "start" => 7.5,    
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 9,
      5  => 1,
      7  => 6,
      9  => 3,
      10  => 8,
      11  => 2
    ),
    "bench" => array(
      7,4
    ),
    "subs" => array(
      "in" => array(
        5 => 1
        , 2 => 5
      ),
      "out" => array(
         5 => 7
        , 2 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  
); 


$ws[9999] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 2,
      7  => 4,
      9  => 7,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      9,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),
  1 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 7,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      2,6
    ),
    "subs" => array(
      "in" => array(
         11 => 3
        , 5 => 9
      ),
      "out" => array(
         11 => 6
        , 5 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),  
  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 9,
      5  => 2,
      7  => 7,
      11  => 4,
      10  => 1,
      9  => 3
    ),
    "bench" => array(
      8,5
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),
  3 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 9,
      5  => 2,
      7  => 7,
      11  => 5,
      10  => 8,
      9  => 3
    ),
    "bench" => array(
      1,4
    ),
    "subs" => array(
      "in" => array(
         10 => 8
        , 11 => 5
      ),
      "out" => array(
         10 => 1
        , 11 => 4
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),  
  
  4 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 2,
      10  => 1,
      11  => 6
    ),
    "bench" => array(
      7,3
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),
  5 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 5,
      4  => 8,
      5  => 9,
      7  => 4,
      9  => 7,
      10  => 1,
      11  => 3
    ),
    "bench" => array(
      2,6
    ),
    "subs" => array(
      "in" => array(
         11 => 3
        , 9 => 7
      ),
      "out" => array(
         11 => 6
        , 9 => 2
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),  
  
  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 9,
      5  => 2,
      7  => 7,
      11  => 5,
      10  => 1,
      9  => 3
    ),
    "bench" => array(
      8,4
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
  7 => array(
    "start" => 7.5,   
    "lineup" => array(
      1  => 0,
      2  => 6,
      4  => 9,
      5  => 2,
      7  => 8,
      11  => 4,
      10  => 1,
      9  => 3
    ),
    "bench" => array(
      7,5
    ),
    "subs" => array(
      "in" => array(
         7 => 8
        , 11 => 4
      ),
      "out" => array(
         7 => 7
        , 11 => 5
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),  

); 







$ws[10000] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 2,
      7 => 3,
      9 => 4,
      10 => 1,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 2,
      7 => 5,
      9 => 4,
      10 => 1,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        7 => 5,
      ),
      'out' => 
      array (
        2 => 9,
        7 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 4,
      7 => 5,
      9 => 1,
      10 => 8,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 4,
      7 => 5,
      9 => 1,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 7,
        11 => 2,
      ),
      'out' => 
      array (
        10 => 8,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 9,
      7 => 7,
      9 => 1,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 3,
      5 => 9,
      7 => 7,
      9 => 4,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        9 => 4,
      ),
      'out' => 
      array (
        4 => 5,
        9 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 9,
      7 => 7,
      9 => 5,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 9,
      7 => 8,
      9 => 5,
      10 => 6,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 8,
        10 => 6,
      ),
      'out' => 
      array (
        7 => 7,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10001] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 4,
      7 => 3,
      9 => 1,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 4,
      7 => 3,
      9 => 7,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        9 => 7,
      ),
      'out' => 
      array (
        2 => 2,
        9 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 9,
      7 => 2,
      9 => 3,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 9,
      7 => 8,
      9 => 4,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 8,
        9 => 4,
      ),
      'out' => 
      array (
        7 => 2,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 9,
      7 => 7,
      9 => 8,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 4,
      7 => 7,
      9 => 8,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 4,
        10 => 1,
      ),
      'out' => 
      array (
        5 => 9,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 5,
      7 => 1,
      9 => 2,
      10 => 3,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 8,
      7 => 1,
      9 => 2,
      10 => 3,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        11 => 7,
      ),
      'out' => 
      array (
        5 => 5,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10002] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 5,
      5 => 2,
      7 => 9,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 5,
      5 => 2,
      7 => 9,
      9 => 8,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        10 => 1,
      ),
      'out' => 
      array (
        2 => 7,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 3,
      5 => 7,
      7 => 6,
      9 => 9,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 3,
      5 => 5,
      7 => 1,
      9 => 9,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        7 => 1,
      ),
      'out' => 
      array (
        5 => 7,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 6,
      7 => 3,
      9 => 8,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 6,
      7 => 3,
      9 => 5,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 5,
        11 => 9,
      ),
      'out' => 
      array (
        9 => 8,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 1,
      7 => 4,
      9 => 3,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 1,
      7 => 4,
      9 => 2,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 2,
        10 => 9,
      ),
      'out' => 
      array (
        9 => 3,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10003] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 2,
      5 => 9,
      7 => 4,
      9 => 1,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 2,
      5 => 9,
      7 => 5,
      9 => 1,
      10 => 8,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 5,
        10 => 8,
      ),
      'out' => 
      array (
        7 => 4,
        10 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 4,
      7 => 3,
      9 => 2,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 4,
      7 => 3,
      9 => 7,
      10 => 9,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        11 => 8,
      ),
      'out' => 
      array (
        9 => 2,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 8,
      7 => 5,
      9 => 6,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 8,
      7 => 7,
      9 => 9,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 7,
        9 => 9,
      ),
      'out' => 
      array (
        7 => 5,
        9 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 4,
      7 => 7,
      9 => 9,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 3,
      7 => 7,
      9 => 9,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        11 => 1,
      ),
      'out' => 
      array (
        5 => 4,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10004] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 2,
      5 => 7,
      7 => 3,
      9 => 4,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 2,
      5 => 9,
      7 => 3,
      9 => 1,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        9 => 1,
      ),
      'out' => 
      array (
        5 => 7,
        9 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 1,
      7 => 6,
      9 => 4,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 2,
      7 => 6,
      9 => 4,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        5 => 2,
      ),
      'out' => 
      array (
        4 => 7,
        5 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 7,
      7 => 8,
      9 => 2,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 7,
      7 => 8,
      9 => 3,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        9 => 3,
      ),
      'out' => 
      array (
        2 => 4,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 3,
      7 => 9,
      9 => 8,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 6,
      7 => 9,
      9 => 5,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 6,
        9 => 5,
      ),
      'out' => 
      array (
        5 => 3,
        9 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10005] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 1,
      7 => 5,
      9 => 4,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 8,
      7 => 6,
      9 => 4,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        7 => 6,
      ),
      'out' => 
      array (
        5 => 1,
        7 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 4,
      7 => 6,
      9 => 5,
      10 => 8,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 3,
      7 => 6,
      9 => 5,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        10 => 9,
      ),
      'out' => 
      array (
        5 => 4,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 6,
      7 => 9,
      9 => 3,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 2,
      7 => 7,
      9 => 3,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 2,
        7 => 7,
      ),
      'out' => 
      array (
        5 => 6,
        7 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 8,
      7 => 7,
      9 => 9,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 4,
      5 => 8,
      7 => 1,
      9 => 9,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        7 => 1,
      ),
      'out' => 
      array (
        4 => 5,
        7 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10006] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 9,
      5 => 3,
      7 => 7,
      9 => 6,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 9,
      5 => 5,
      7 => 7,
      9 => 6,
      10 => 8,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        10 => 8,
      ),
      'out' => 
      array (
        5 => 3,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 5,
      7 => 7,
      9 => 6,
      10 => 9,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 5,
      7 => 7,
      9 => 4,
      10 => 9,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 4,
        11 => 8,
      ),
      'out' => 
      array (
        9 => 6,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 2,
      5 => 1,
      7 => 8,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 2,
      5 => 1,
      7 => 8,
      9 => 3,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        10 => 9,
      ),
      'out' => 
      array (
        2 => 6,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 4,
      7 => 2,
      9 => 9,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 4,
      7 => 1,
      9 => 9,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        7 => 1,
      ),
      'out' => 
      array (
        2 => 7,
        7 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10007] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 2,
      7 => 6,
      9 => 5,
      10 => 7,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 2,
      7 => 6,
      9 => 8,
      10 => 7,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        9 => 8,
      ),
      'out' => 
      array (
        4 => 3,
        9 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 6,
      5 => 9,
      7 => 2,
      9 => 1,
      10 => 5,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 4,
      5 => 9,
      7 => 2,
      9 => 1,
      10 => 5,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        11 => 7,
      ),
      'out' => 
      array (
        4 => 6,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 7,
      7 => 8,
      9 => 9,
      10 => 6,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 7,
      7 => 8,
      9 => 5,
      10 => 6,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 5,
        11 => 2,
      ),
      'out' => 
      array (
        9 => 9,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 3,
      7 => 7,
      9 => 2,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 3,
      7 => 7,
      9 => 8,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 6,
        9 => 8,
      ),
      'out' => 
      array (
        4 => 1,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10008] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 8,
      7 => 7,
      9 => 6,
      10 => 2,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 1,
      5 => 8,
      7 => 7,
      9 => 6,
      10 => 2,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        11 => 3,
      ),
      'out' => 
      array (
        4 => 5,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 2,
      7 => 5,
      9 => 1,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 2,
      7 => 5,
      9 => 6,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 6,
        11 => 7,
      ),
      'out' => 
      array (
        9 => 1,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 2,
      7 => 8,
      9 => 7,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 5,
      5 => 2,
      7 => 8,
      9 => 7,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        11 => 6,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 9,
      7 => 4,
      9 => 5,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 9,
      7 => 4,
      9 => 5,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 8,
        11 => 3,
      ),
      'out' => 
      array (
        10 => 7,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10009] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 9,
      5 => 6,
      7 => 5,
      9 => 2,
      10 => 8,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 9,
      5 => 3,
      7 => 5,
      9 => 2,
      10 => 8,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 7,
        5 => 3,
      ),
      'out' => 
      array (
        2 => 4,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 3,
      7 => 4,
      9 => 1,
      10 => 5,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 9,
      7 => 2,
      9 => 1,
      10 => 5,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        7 => 2,
      ),
      'out' => 
      array (
        5 => 3,
        7 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 4,
      7 => 5,
      9 => 7,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 4,
      7 => 8,
      9 => 7,
      10 => 9,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 8,
        10 => 9,
      ),
      'out' => 
      array (
        7 => 5,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 5,
      7 => 9,
      9 => 1,
      10 => 3,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 5,
      7 => 9,
      9 => 8,
      10 => 3,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 6,
        9 => 8,
      ),
      'out' => 
      array (
        4 => 2,
        9 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10010] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 3,
      5 => 6,
      7 => 4,
      9 => 5,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 2,
      5 => 8,
      7 => 4,
      9 => 5,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 2,
        5 => 8,
      ),
      'out' => 
      array (
        4 => 3,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 8,
      7 => 3,
      9 => 1,
      10 => 2,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 8,
      7 => 3,
      9 => 1,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 4,
        11 => 5,
      ),
      'out' => 
      array (
        2 => 9,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 7,
      7 => 1,
      9 => 9,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 7,
      7 => 1,
      9 => 9,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 4,
        10 => 2,
      ),
      'out' => 
      array (
        2 => 3,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 2,
      7 => 8,
      9 => 5,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 2,
      7 => 8,
      9 => 5,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        4 => 1,
      ),
      'out' => 
      array (
        2 => 9,
        4 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10011] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 3,
      7 => 8,
      9 => 9,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 3,
      7 => 8,
      9 => 9,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        10 => 7,
      ),
      'out' => 
      array (
        4 => 1,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 4,
      5 => 5,
      7 => 7,
      9 => 1,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 3,
      5 => 5,
      7 => 7,
      9 => 1,
      10 => 9,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        11 => 8,
      ),
      'out' => 
      array (
        4 => 4,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 7,
      7 => 4,
      9 => 3,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 7,
      7 => 4,
      9 => 3,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        11 => 2,
      ),
      'out' => 
      array (
        4 => 8,
        11 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 4,
      5 => 3,
      7 => 6,
      9 => 8,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 2,
      7 => 6,
      9 => 8,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 7,
        5 => 2,
      ),
      'out' => 
      array (
        2 => 1,
        5 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10012] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 4,
      5 => 5,
      7 => 3,
      9 => 7,
      10 => 6,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 4,
      5 => 1,
      7 => 3,
      9 => 7,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 1,
        10 => 2,
      ),
      'out' => 
      array (
        5 => 5,
        10 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 8,
      5 => 1,
      7 => 6,
      9 => 9,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 1,
      7 => 6,
      9 => 9,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        10 => 2,
      ),
      'out' => 
      array (
        2 => 4,
        10 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 7,
      5 => 2,
      7 => 1,
      9 => 4,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 2,
      7 => 1,
      9 => 4,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        10 => 9,
      ),
      'out' => 
      array (
        2 => 8,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 1,
      5 => 7,
      7 => 5,
      9 => 2,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 1,
      5 => 9,
      7 => 5,
      9 => 2,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        11 => 6,
      ),
      'out' => 
      array (
        5 => 7,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10013] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 9,
      5 => 8,
      7 => 1,
      9 => 7,
      10 => 4,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 5,
      7 => 1,
      9 => 7,
      10 => 4,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 6,
        5 => 5,
      ),
      'out' => 
      array (
        4 => 9,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 3,
      5 => 2,
      7 => 8,
      9 => 5,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 4,
      7 => 8,
      9 => 5,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        5 => 4,
      ),
      'out' => 
      array (
        4 => 3,
        5 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 2,
      7 => 5,
      9 => 9,
      10 => 8,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 2,
      7 => 5,
      9 => 6,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 6,
        11 => 7,
      ),
      'out' => 
      array (
        9 => 9,
        11 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 1,
      7 => 5,
      9 => 8,
      10 => 6,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 1,
      7 => 4,
      9 => 3,
      10 => 6,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 4,
        9 => 3,
      ),
      'out' => 
      array (
        7 => 5,
        9 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10014] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 6,
      7 => 9,
      9 => 7,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 2,
      5 => 6,
      7 => 9,
      9 => 7,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        4 => 2,
      ),
      'out' => 
      array (
        2 => 3,
        4 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 5,
      7 => 9,
      9 => 2,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 7,
      7 => 9,
      9 => 2,
      10 => 6,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 7,
        10 => 6,
      ),
      'out' => 
      array (
        5 => 5,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 4,
      9 => 6,
      10 => 2,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 4,
      9 => 6,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 9,
        11 => 7,
      ),
      'out' => 
      array (
        10 => 2,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 5,
      5 => 1,
      7 => 7,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 5,
      5 => 6,
      7 => 7,
      9 => 3,
      10 => 8,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 6,
        10 => 8,
      ),
      'out' => 
      array (
        5 => 1,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10015] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 4,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 8,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        10 => 8,
      ),
      'out' => 
      array (
        2 => 7,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 3,
      5 => 6,
      7 => 8,
      9 => 1,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 3,
      5 => 9,
      7 => 8,
      9 => 1,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        5 => 9,
      ),
      'out' => 
      array (
        2 => 4,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 8,
      7 => 3,
      9 => 7,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 9,
      5 => 8,
      7 => 3,
      9 => 7,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        11 => 5,
      ),
      'out' => 
      array (
        2 => 6,
        11 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 4,
      7 => 3,
      9 => 2,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 4,
      7 => 9,
      9 => 2,
      10 => 8,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 9,
        10 => 8,
      ),
      'out' => 
      array (
        7 => 3,
        10 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10016] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 6,
      5 => 1,
      7 => 5,
      9 => 3,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 6,
      5 => 1,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        10 => 4,
      ),
      'out' => 
      array (
        7 => 5,
        10 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 2,
      7 => 9,
      9 => 6,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 5,
      5 => 2,
      7 => 9,
      9 => 6,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 1,
        10 => 8,
      ),
      'out' => 
      array (
        2 => 4,
        10 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 6,
      7 => 7,
      9 => 8,
      10 => 1,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 9,
      7 => 7,
      9 => 8,
      10 => 1,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        5 => 9,
      ),
      'out' => 
      array (
        4 => 2,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 5,
      7 => 8,
      9 => 4,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 5,
      7 => 1,
      9 => 4,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 1,
        10 => 7,
      ),
      'out' => 
      array (
        7 => 8,
        10 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10017] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 8,
      7 => 1,
      9 => 3,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 8,
      7 => 6,
      9 => 9,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 6,
        9 => 9,
      ),
      'out' => 
      array (
        7 => 1,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 1,
      7 => 6,
      9 => 9,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 9,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 8,
        7 => 7,
      ),
      'out' => 
      array (
        4 => 4,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 9,
      7 => 5,
      9 => 8,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 2,
      7 => 3,
      9 => 8,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 2,
        7 => 3,
      ),
      'out' => 
      array (
        5 => 9,
        7 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 8,
      5 => 3,
      7 => 2,
      9 => 6,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 3,
      7 => 2,
      9 => 6,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        10 => 4,
      ),
      'out' => 
      array (
        4 => 8,
        10 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10018] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 8,
      5 => 1,
      7 => 5,
      9 => 6,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 1,
      7 => 5,
      9 => 6,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        4 => 9,
      ),
      'out' => 
      array (
        2 => 4,
        4 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 7,
      7 => 4,
      9 => 6,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 5,
      7 => 4,
      9 => 2,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        9 => 2,
      ),
      'out' => 
      array (
        5 => 7,
        9 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 2,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 3,
      7 => 7,
      9 => 4,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        9 => 4,
      ),
      'out' => 
      array (
        5 => 1,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 2,
      5 => 3,
      7 => 1,
      9 => 9,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 2,
      5 => 3,
      7 => 1,
      9 => 8,
      10 => 6,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 8,
        10 => 6,
      ),
      'out' => 
      array (
        9 => 9,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10019] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 9,
      7 => 8,
      9 => 2,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 9,
      7 => 8,
      9 => 6,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        9 => 6,
      ),
      'out' => 
      array (
        2 => 5,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 7,
      5 => 4,
      7 => 2,
      9 => 9,
      10 => 3,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 7,
      5 => 4,
      7 => 2,
      9 => 9,
      10 => 6,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 1,
        10 => 6,
      ),
      'out' => 
      array (
        2 => 8,
        10 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 9,
      7 => 6,
      9 => 4,
      10 => 5,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 7,
      7 => 6,
      9 => 1,
      10 => 5,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 7,
        9 => 1,
      ),
      'out' => 
      array (
        5 => 9,
        9 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 6,
      7 => 1,
      9 => 3,
      10 => 8,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 6,
      7 => 1,
      9 => 3,
      10 => 2,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        10 => 2,
      ),
      'out' => 
      array (
        2 => 7,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10020] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 1,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 7,
      5 => 1,
      7 => 5,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        7 => 5,
      ),
      'out' => 
      array (
        2 => 6,
        7 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 7,
      5 => 5,
      7 => 1,
      9 => 2,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 4,
      7 => 1,
      9 => 2,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        5 => 4,
      ),
      'out' => 
      array (
        4 => 7,
        5 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 9,
      7 => 7,
      9 => 6,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 9,
      7 => 4,
      9 => 1,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 4,
        9 => 1,
      ),
      'out' => 
      array (
        7 => 7,
        9 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 9,
      7 => 2,
      9 => 6,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 1,
      7 => 2,
      9 => 6,
      10 => 3,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 1,
        10 => 3,
      ),
      'out' => 
      array (
        5 => 9,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10021] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 3,
      7 => 8,
      9 => 7,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 9,
      7 => 8,
      9 => 7,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        11 => 1,
      ),
      'out' => 
      array (
        5 => 3,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 3,
      7 => 9,
      9 => 6,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 3,
      7 => 9,
      9 => 8,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 8,
        11 => 5,
      ),
      'out' => 
      array (
        9 => 6,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 2,
      7 => 5,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 2,
      7 => 5,
      9 => 1,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        9 => 1,
      ),
      'out' => 
      array (
        4 => 7,
        9 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 4,
      7 => 3,
      9 => 9,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 4,
      7 => 2,
      9 => 9,
      10 => 6,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        11 => 5,
      ),
      'out' => 
      array (
        7 => 3,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10022] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 6,
      7 => 8,
      9 => 4,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 3,
      7 => 8,
      9 => 4,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        5 => 3,
      ),
      'out' => 
      array (
        2 => 1,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 1,
      5 => 2,
      7 => 3,
      9 => 6,
      10 => 5,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 1,
      5 => 8,
      7 => 3,
      9 => 6,
      10 => 9,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        10 => 9,
      ),
      'out' => 
      array (
        5 => 2,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 5,
      5 => 2,
      7 => 8,
      9 => 1,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 5,
      5 => 2,
      7 => 8,
      9 => 4,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 4,
        10 => 3,
      ),
      'out' => 
      array (
        9 => 1,
        10 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 5,
      5 => 4,
      7 => 3,
      9 => 9,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 4,
      7 => 3,
      9 => 9,
      10 => 1,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        11 => 8,
      ),
      'out' => 
      array (
        2 => 2,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10023] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 4,
      7 => 1,
      9 => 7,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 2,
      7 => 1,
      9 => 7,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        5 => 2,
      ),
      'out' => 
      array (
        4 => 5,
        5 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 8,
      7 => 4,
      9 => 5,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 3,
      5 => 9,
      7 => 4,
      9 => 5,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        5 => 9,
      ),
      'out' => 
      array (
        2 => 1,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 7,
      7 => 8,
      9 => 3,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 9,
      7 => 8,
      9 => 6,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        9 => 6,
      ),
      'out' => 
      array (
        5 => 7,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 3,
      7 => 2,
      9 => 6,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 3,
      7 => 2,
      9 => 8,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 8,
        11 => 5,
      ),
      'out' => 
      array (
        9 => 6,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10024] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 4,
      9 => 8,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 5,
      9 => 8,
      10 => 3,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 5,
        11 => 9,
      ),
      'out' => 
      array (
        7 => 4,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 6,
      7 => 3,
      9 => 9,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 1,
      7 => 3,
      9 => 9,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        5 => 1,
      ),
      'out' => 
      array (
        2 => 7,
        5 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 7,
      7 => 5,
      9 => 1,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 2,
      5 => 7,
      7 => 5,
      9 => 1,
      10 => 4,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 2,
        11 => 3,
      ),
      'out' => 
      array (
        4 => 8,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 9,
      5 => 6,
      7 => 1,
      9 => 4,
      10 => 5,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 6,
      7 => 1,
      9 => 4,
      10 => 5,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        11 => 7,
      ),
      'out' => 
      array (
        2 => 8,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10025] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 3,
      7 => 9,
      9 => 7,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 8,
      7 => 9,
      9 => 7,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        5 => 8,
      ),
      'out' => 
      array (
        2 => 5,
        5 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 7,
      7 => 2,
      9 => 1,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 9,
      7 => 2,
      9 => 1,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        5 => 9,
      ),
      'out' => 
      array (
        4 => 6,
        5 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 9,
      5 => 7,
      7 => 8,
      9 => 6,
      10 => 3,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 6,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        10 => 5,
      ),
      'out' => 
      array (
        7 => 8,
        10 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 3,
      5 => 5,
      7 => 1,
      9 => 8,
      10 => 2,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 3,
      5 => 5,
      7 => 9,
      9 => 8,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 9,
        11 => 4,
      ),
      'out' => 
      array (
        7 => 1,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10026] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 4,
      7 => 5,
      9 => 2,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 1,
      7 => 5,
      9 => 2,
      10 => 6,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 1,
        11 => 9,
      ),
      'out' => 
      array (
        5 => 4,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 4,
      7 => 5,
      9 => 8,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 4,
      7 => 5,
      9 => 3,
      10 => 6,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 3,
        11 => 2,
      ),
      'out' => 
      array (
        9 => 8,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 3,
      10 => 9,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 3,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        11 => 6,
      ),
      'out' => 
      array (
        2 => 5,
        11 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 2,
      5 => 7,
      7 => 4,
      9 => 5,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 2,
      5 => 7,
      7 => 4,
      9 => 1,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 9,
        9 => 1,
      ),
      'out' => 
      array (
        2 => 6,
        9 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10027] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 5,
      9 => 6,
      10 => 3,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 5,
      9 => 8,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 8,
        11 => 4,
      ),
      'out' => 
      array (
        9 => 6,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 8,
      5 => 2,
      7 => 3,
      9 => 6,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 2,
      7 => 7,
      9 => 6,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 1,
        7 => 7,
      ),
      'out' => 
      array (
        2 => 9,
        7 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 6,
      7 => 2,
      9 => 4,
      10 => 3,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 6,
      7 => 7,
      9 => 4,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 7,
        10 => 5,
      ),
      'out' => 
      array (
        7 => 2,
        10 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 1,
      7 => 6,
      9 => 2,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 4,
      7 => 6,
      9 => 5,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 4,
        9 => 5,
      ),
      'out' => 
      array (
        5 => 1,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10028] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 5,
      9 => 9,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 7,
      7 => 8,
      9 => 9,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        7 => 8,
      ),
      'out' => 
      array (
        4 => 2,
        7 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 2,
      7 => 6,
      9 => 3,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 4,
      5 => 2,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        10 => 1,
      ),
      'out' => 
      array (
        4 => 7,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 8,
      7 => 4,
      9 => 7,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 3,
      5 => 8,
      7 => 4,
      9 => 7,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        10 => 9,
      ),
      'out' => 
      array (
        4 => 6,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 2,
      5 => 3,
      7 => 6,
      9 => 1,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 2,
      5 => 3,
      7 => 7,
      9 => 1,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 7,
        10 => 4,
      ),
      'out' => 
      array (
        7 => 6,
        10 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10029] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 3,
      5 => 1,
      7 => 6,
      9 => 5,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 3,
      5 => 1,
      7 => 4,
      9 => 8,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 4,
        9 => 8,
      ),
      'out' => 
      array (
        7 => 6,
        9 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 6,
      5 => 3,
      7 => 7,
      9 => 1,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 6,
      5 => 9,
      7 => 7,
      9 => 2,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        9 => 2,
      ),
      'out' => 
      array (
        5 => 3,
        9 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 9,
      7 => 3,
      9 => 8,
      10 => 6,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 9,
      7 => 3,
      9 => 7,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        10 => 4,
      ),
      'out' => 
      array (
        9 => 8,
        10 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 3,
      5 => 5,
      7 => 9,
      9 => 4,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 3,
      5 => 5,
      7 => 1,
      9 => 4,
      10 => 7,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 1,
        10 => 7,
      ),
      'out' => 
      array (
        7 => 9,
        10 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10030] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 5,
      7 => 8,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 2,
        7 => 8,
      ),
      'out' => 
      array (
        4 => 9,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 6,
      7 => 9,
      9 => 2,
      10 => 7,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 6,
      7 => 9,
      9 => 4,
      10 => 7,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        9 => 4,
      ),
      'out' => 
      array (
        2 => 3,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 5,
      5 => 8,
      7 => 2,
      9 => 6,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 4,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        9 => 4,
      ),
      'out' => 
      array (
        4 => 5,
        9 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 4,
      7 => 9,
      9 => 7,
      10 => 2,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 4,
      7 => 8,
      9 => 3,
      10 => 2,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 8,
        9 => 3,
      ),
      'out' => 
      array (
        7 => 9,
        9 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10031] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 9,
      7 => 2,
      9 => 5,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 9,
      7 => 2,
      9 => 5,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        10 => 1,
      ),
      'out' => 
      array (
        2 => 7,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 2,
      5 => 7,
      7 => 3,
      9 => 6,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 2,
      5 => 7,
      7 => 3,
      9 => 6,
      10 => 4,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        11 => 8,
      ),
      'out' => 
      array (
        2 => 1,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 4,
      7 => 2,
      9 => 6,
      10 => 5,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 3,
      5 => 7,
      7 => 2,
      9 => 6,
      10 => 5,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        5 => 7,
      ),
      'out' => 
      array (
        4 => 8,
        5 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 3,
      7 => 9,
      9 => 8,
      10 => 7,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 3,
      7 => 5,
      9 => 8,
      10 => 7,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        7 => 5,
      ),
      'out' => 
      array (
        2 => 6,
        7 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10032] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 2,
      5 => 3,
      7 => 4,
      9 => 1,
      10 => 6,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 3,
      7 => 4,
      9 => 1,
      10 => 6,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 7,
        11 => 9,
      ),
      'out' => 
      array (
        4 => 2,
        11 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 7,
      7 => 5,
      9 => 1,
      10 => 8,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 3,
      7 => 5,
      9 => 4,
      10 => 8,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        9 => 4,
      ),
      'out' => 
      array (
        5 => 7,
        9 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 6,
      7 => 1,
      9 => 5,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 6,
      7 => 2,
      9 => 5,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        10 => 8,
      ),
      'out' => 
      array (
        7 => 1,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 9,
      7 => 2,
      9 => 8,
      10 => 1,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 5,
      7 => 2,
      9 => 8,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        11 => 6,
      ),
      'out' => 
      array (
        5 => 9,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10033] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 8,
      5 => 5,
      7 => 3,
      9 => 6,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 8,
      5 => 5,
      7 => 3,
      9 => 7,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        10 => 1,
      ),
      'out' => 
      array (
        9 => 6,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 5,
      7 => 4,
      9 => 6,
      10 => 7,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 6,
      10 => 7,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        7 => 2,
      ),
      'out' => 
      array (
        5 => 5,
        7 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 4,
      5 => 9,
      7 => 5,
      9 => 3,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 4,
      5 => 2,
      7 => 5,
      9 => 3,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        5 => 2,
      ),
      'out' => 
      array (
        2 => 6,
        5 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 8,
      7 => 1,
      9 => 7,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 8,
      7 => 3,
      9 => 5,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 3,
        9 => 5,
      ),
      'out' => 
      array (
        7 => 1,
        9 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10034] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 4,
      7 => 3,
      9 => 2,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 7,
      5 => 4,
      7 => 5,
      9 => 2,
      10 => 1,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        7 => 5,
      ),
      'out' => 
      array (
        2 => 6,
        7 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 7,
      7 => 9,
      9 => 2,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 6,
      5 => 5,
      7 => 9,
      9 => 1,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        9 => 1,
      ),
      'out' => 
      array (
        5 => 7,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 1,
      7 => 2,
      9 => 4,
      10 => 6,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 9,
      7 => 2,
      9 => 3,
      10 => 6,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 9,
        9 => 3,
      ),
      'out' => 
      array (
        5 => 1,
        9 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 4,
      7 => 8,
      9 => 5,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 2,
      7 => 8,
      9 => 5,
      10 => 6,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 2,
        11 => 9,
      ),
      'out' => 
      array (
        5 => 4,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10035] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 9,
      7 => 1,
      9 => 3,
      10 => 2,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 5,
      7 => 1,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        10 => 4,
      ),
      'out' => 
      array (
        5 => 9,
        10 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 5,
      5 => 8,
      7 => 2,
      9 => 4,
      10 => 7,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 3,
      7 => 2,
      9 => 4,
      10 => 7,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        5 => 3,
      ),
      'out' => 
      array (
        2 => 1,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 4,
      5 => 7,
      7 => 8,
      9 => 5,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 3,
      5 => 9,
      7 => 8,
      9 => 5,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 3,
        5 => 9,
      ),
      'out' => 
      array (
        4 => 4,
        5 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 3,
      5 => 5,
      7 => 7,
      9 => 4,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 3,
      5 => 6,
      7 => 1,
      9 => 4,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 6,
        7 => 1,
      ),
      'out' => 
      array (
        5 => 5,
        7 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10036] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 7,
      7 => 4,
      9 => 1,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 7,
      7 => 4,
      9 => 1,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 9,
        10 => 2,
      ),
      'out' => 
      array (
        2 => 3,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 8,
      7 => 9,
      9 => 6,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 8,
      7 => 9,
      9 => 7,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        10 => 1,
      ),
      'out' => 
      array (
        9 => 6,
        10 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 2,
      7 => 3,
      9 => 6,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 2,
      7 => 3,
      9 => 6,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        4 => 8,
      ),
      'out' => 
      array (
        2 => 4,
        4 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 6,
      7 => 2,
      9 => 9,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 3,
      7 => 2,
      9 => 9,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        10 => 1,
      ),
      'out' => 
      array (
        5 => 6,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10037] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 2,
      5 => 6,
      7 => 4,
      9 => 9,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 6,
      7 => 4,
      9 => 9,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 1,
        4 => 8,
      ),
      'out' => 
      array (
        2 => 5,
        4 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 4,
      7 => 5,
      9 => 6,
      10 => 2,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 8,
      7 => 3,
      9 => 6,
      10 => 2,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        7 => 3,
      ),
      'out' => 
      array (
        5 => 4,
        7 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 6,
      7 => 8,
      9 => 5,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 2,
      7 => 8,
      9 => 5,
      10 => 9,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 2,
        10 => 9,
      ),
      'out' => 
      array (
        5 => 6,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 6,
      5 => 8,
      7 => 1,
      9 => 4,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 6,
      5 => 8,
      7 => 9,
      9 => 4,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        7 => 9,
      ),
      'out' => 
      array (
        2 => 7,
        7 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10038] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 8,
      7 => 3,
      9 => 5,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 4,
      7 => 3,
      9 => 5,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 9,
        5 => 4,
      ),
      'out' => 
      array (
        2 => 2,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 5,
      7 => 4,
      9 => 8,
      10 => 7,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 1,
      7 => 4,
      9 => 8,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 1,
        11 => 3,
      ),
      'out' => 
      array (
        5 => 5,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 5,
      5 => 1,
      7 => 2,
      9 => 9,
      10 => 4,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 6,
      5 => 1,
      7 => 2,
      9 => 9,
      10 => 4,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 7,
        4 => 6,
      ),
      'out' => 
      array (
        2 => 3,
        4 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 2,
      7 => 1,
      9 => 5,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 8,
      5 => 2,
      7 => 6,
      9 => 5,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 8,
        7 => 6,
      ),
      'out' => 
      array (
        4 => 7,
        7 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10039] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 3,
      7 => 4,
      9 => 5,
      10 => 2,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 3,
      7 => 4,
      9 => 5,
      10 => 2,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 1,
        11 => 9,
      ),
      'out' => 
      array (
        2 => 7,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 3,
      5 => 8,
      7 => 9,
      9 => 2,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 4,
      5 => 8,
      7 => 9,
      9 => 1,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        9 => 1,
      ),
      'out' => 
      array (
        4 => 3,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 1,
      7 => 7,
      9 => 2,
      10 => 4,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 2,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 8,
        11 => 5,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 6,
      5 => 7,
      7 => 1,
      9 => 3,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 2,
      5 => 7,
      7 => 1,
      9 => 3,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 2,
        10 => 8,
      ),
      'out' => 
      array (
        4 => 6,
        10 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10040] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 9,
      5 => 4,
      7 => 8,
      9 => 1,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 6,
      7 => 8,
      9 => 1,
      10 => 2,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        5 => 6,
      ),
      'out' => 
      array (
        2 => 7,
        5 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 2,
      5 => 7,
      7 => 6,
      9 => 4,
      10 => 3,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 7,
      7 => 5,
      9 => 4,
      10 => 3,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        7 => 5,
      ),
      'out' => 
      array (
        4 => 2,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 6,
      5 => 4,
      7 => 2,
      9 => 5,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 6,
      5 => 4,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 3,
        11 => 9,
      ),
      'out' => 
      array (
        9 => 5,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 9,
      7 => 6,
      9 => 5,
      10 => 1,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 9,
      7 => 6,
      9 => 5,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 4,
        10 => 8,
      ),
      'out' => 
      array (
        2 => 2,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10041] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 1,
      5 => 3,
      7 => 5,
      9 => 9,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 2,
      5 => 4,
      7 => 5,
      9 => 9,
      10 => 6,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 2,
        5 => 4,
      ),
      'out' => 
      array (
        4 => 1,
        5 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 6,
      7 => 9,
      9 => 1,
      10 => 3,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 7,
      5 => 6,
      7 => 4,
      9 => 1,
      10 => 3,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 4,
        11 => 5,
      ),
      'out' => 
      array (
        7 => 9,
        11 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 8,
      5 => 1,
      7 => 7,
      9 => 4,
      10 => 2,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 1,
      7 => 5,
      9 => 4,
      10 => 2,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        7 => 5,
      ),
      'out' => 
      array (
        2 => 9,
        7 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 3,
      7 => 9,
      9 => 7,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 5,
      5 => 3,
      7 => 9,
      9 => 7,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        11 => 1,
      ),
      'out' => 
      array (
        2 => 8,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10042] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 9,
      5 => 3,
      7 => 5,
      9 => 4,
      10 => 6,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 9,
      5 => 3,
      7 => 5,
      9 => 4,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 8,
        11 => 7,
      ),
      'out' => 
      array (
        10 => 6,
        11 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 8,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 9,
      7 => 6,
      9 => 8,
      10 => 3,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        5 => 9,
      ),
      'out' => 
      array (
        4 => 1,
        5 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 8,
      5 => 7,
      7 => 4,
      9 => 5,
      10 => 2,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 7,
      7 => 4,
      9 => 5,
      10 => 2,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        4 => 9,
      ),
      'out' => 
      array (
        2 => 3,
        4 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 4,
      5 => 6,
      7 => 9,
      9 => 2,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 5,
      5 => 6,
      7 => 9,
      9 => 7,
      10 => 8,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        9 => 7,
      ),
      'out' => 
      array (
        4 => 4,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10043] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 6,
      7 => 3,
      9 => 9,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 6,
      7 => 3,
      9 => 4,
      10 => 8,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 7,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 4,
        10 => 8,
      ),
      'out' => 
      array (
        9 => 9,
        10 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 5,
      5 => 9,
      7 => 7,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 2,
      5 => 9,
      7 => 7,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        4 => 2,
      ),
      'out' => 
      array (
        2 => 1,
        4 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 5,
      7 => 4,
      9 => 7,
      10 => 1,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 5,
      7 => 4,
      9 => 7,
      10 => 9,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 9,
        11 => 6,
      ),
      'out' => 
      array (
        10 => 1,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 3,
      7 => 6,
      9 => 1,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 7,
      5 => 3,
      7 => 2,
      9 => 1,
      10 => 5,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        11 => 4,
      ),
      'out' => 
      array (
        7 => 6,
        11 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10044] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 6,
      7 => 1,
      9 => 9,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 8,
      5 => 3,
      7 => 1,
      9 => 9,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 3,
        11 => 7,
      ),
      'out' => 
      array (
        5 => 6,
        11 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 3,
      5 => 9,
      7 => 4,
      9 => 6,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 3,
      5 => 1,
      7 => 4,
      9 => 6,
      10 => 5,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 7,
        5 => 1,
      ),
      'out' => 
      array (
        2 => 2,
        5 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 6,
      7 => 5,
      9 => 2,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 6,
      7 => 3,
      9 => 8,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 3,
        9 => 8,
      ),
      'out' => 
      array (
        7 => 5,
        9 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 4,
      7 => 2,
      9 => 9,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 6,
      5 => 8,
      7 => 2,
      9 => 1,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        9 => 1,
      ),
      'out' => 
      array (
        5 => 4,
        9 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10045] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 8,
      7 => 1,
      9 => 2,
      10 => 6,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 7,
      5 => 8,
      7 => 1,
      9 => 2,
      10 => 6,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 7,
        11 => 5,
      ),
      'out' => 
      array (
        4 => 4,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 7,
      7 => 3,
      9 => 9,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 2,
      5 => 7,
      7 => 1,
      9 => 9,
      10 => 5,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        7 => 1,
      ),
      'out' => 
      array (
        2 => 4,
        7 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 4,
      5 => 8,
      7 => 2,
      9 => 9,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 8,
      7 => 6,
      9 => 9,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 7,
        7 => 6,
      ),
      'out' => 
      array (
        2 => 3,
        7 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 3,
      5 => 2,
      7 => 7,
      9 => 8,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 3,
      5 => 2,
      7 => 7,
      9 => 5,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        9 => 5,
      ),
      'out' => 
      array (
        2 => 9,
        9 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10046] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 9,
      5 => 7,
      7 => 4,
      9 => 2,
      10 => 6,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 1,
      4 => 8,
      5 => 7,
      7 => 4,
      9 => 2,
      10 => 6,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 8,
        11 => 5,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 7,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 2,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 8,
      7 => 1,
      9 => 3,
      10 => 2,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        7 => 1,
      ),
      'out' => 
      array (
        4 => 7,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 3,
      7 => 5,
      9 => 6,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 3,
      7 => 5,
      9 => 4,
      10 => 8,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 4,
        11 => 2,
      ),
      'out' => 
      array (
        9 => 6,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 9,
      7 => 5,
      9 => 2,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 1,
      5 => 3,
      7 => 5,
      9 => 2,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        5 => 3,
      ),
      'out' => 
      array (
        4 => 8,
        5 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10047] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 6,
      7 => 1,
      9 => 8,
      10 => 3,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 2,
      5 => 6,
      7 => 1,
      9 => 8,
      10 => 3,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        11 => 9,
      ),
      'out' => 
      array (
        2 => 4,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 9,
      7 => 7,
      9 => 1,
      10 => 2,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 5,
      5 => 6,
      7 => 7,
      9 => 1,
      10 => 2,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 6,
        11 => 8,
      ),
      'out' => 
      array (
        5 => 9,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 1,
      5 => 9,
      7 => 4,
      9 => 6,
      10 => 8,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 9,
      7 => 4,
      9 => 6,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        10 => 7,
      ),
      'out' => 
      array (
        2 => 3,
        10 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 7,
      5 => 3,
      7 => 8,
      9 => 9,
      10 => 2,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 3,
      7 => 8,
      9 => 9,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        10 => 1,
      ),
      'out' => 
      array (
        2 => 6,
        10 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10048] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 8,
      7 => 9,
      9 => 7,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 4,
      4 => 2,
      5 => 8,
      7 => 3,
      9 => 7,
      10 => 1,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 3,
        11 => 5,
      ),
      'out' => 
      array (
        7 => 9,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 1,
      7 => 5,
      9 => 6,
      10 => 2,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 8,
      7 => 5,
      9 => 6,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        10 => 4,
      ),
      'out' => 
      array (
        5 => 1,
        10 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 9,
      7 => 2,
      9 => 6,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 4,
      5 => 3,
      7 => 2,
      9 => 6,
      10 => 5,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 4,
        5 => 3,
      ),
      'out' => 
      array (
        4 => 8,
        5 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 2,
      7 => 6,
      9 => 9,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 5,
      5 => 7,
      7 => 1,
      9 => 9,
      10 => 3,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 7,
        7 => 1,
      ),
      'out' => 
      array (
        5 => 2,
        7 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[10049] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 6,
      7 => 4,
      9 => 1,
      10 => 9,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 8,
      5 => 6,
      7 => 2,
      9 => 1,
      10 => 9,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        11 => 3,
      ),
      'out' => 
      array (
        7 => 4,
        11 => 5,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 5,
      7 => 2,
      9 => 3,
      10 => 9,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 8,
      5 => 5,
      7 => 7,
      9 => 3,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 7,
        10 => 1,
      ),
      'out' => 
      array (
        7 => 2,
        10 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 4,
      7 => 8,
      9 => 9,
      10 => 7,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 6,
      5 => 1,
      7 => 8,
      9 => 9,
      10 => 7,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 1,
        11 => 5,
      ),
      'out' => 
      array (
        5 => 4,
        11 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 3,
      5 => 1,
      7 => 5,
      9 => 2,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 3,
      5 => 1,
      7 => 5,
      9 => 2,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        11 => 9,
      ),
      'out' => 
      array (
        2 => 6,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);



$ws[20000] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 2,
      7 => 6,
      9 => 3,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 2,
        10 => 8,
      ),
      'out' => 
      array (
        5 => 5,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 9,
        9 => 7,
      ),
      'out' => 
      array (
        2 => 5,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 6,
        11 => 1,
      ),
      'out' => 
      array (
        7 => 2,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        10 => 8,
      ),
      'out' => 
      array (
        4 => 1,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[20001] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 9,
      9 => 3,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 9,
        11 => 4,
      ),
      'out' => 
      array (
        7 => 2,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        10 => 1,
      ),
      'out' => 
      array (
        2 => 5,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        9 => 7,
      ),
      'out' => 
      array (
        5 => 5,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 7,
        11 => 2,
      ),
      'out' => 
      array (
        5 => 8,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[20002] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 9,
      5 => 5,
      7 => 2,
      9 => 1,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 5,
        9 => 1,
      ),
      'out' => 
      array (
        5 => 8,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 6,
        11 => 7,
      ),
      'out' => 
      array (
        7 => 2,
        11 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 3,
      9 => 7,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 3,
        10 => 1,
      ),
      'out' => 
      array (
        7 => 2,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        5 => 8,
        11 => 9,
      ),
      'out' => 
      array (
        5 => 7,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[20003] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        11 => 1,
      ),
      'out' => 
      array (
        2 => 5,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 2,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 8,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 2,
        10 => 8,
      ),
      'out' => 
      array (
        2 => 9,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 5,
        9 => 7,
      ),
      'out' => 
      array (
        2 => 9,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 7,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 2,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 6,
        9 => 2,
      ),
      'out' => 
      array (
        7 => 7,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[20004] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 8,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 2,
        9 => 8,
      ),
      'out' => 
      array (
        7 => 6,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 2,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 2,
        10 => 4,
      ),
      'out' => 
      array (
        9 => 3,
        10 => 1,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        11 => 7,
      ),
      'out' => 
      array (
        4 => 1,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 9,
      5 => 4,
      7 => 2,
      9 => 3,
      10 => 1,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        5 => 4,
      ),
      'out' => 
      array (
        2 => 5,
        5 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);



$ws[30000] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 6,
      9 => 3,
      10 => 8,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 6,
      9 => 3,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        11 => 4,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 3,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        11 => 3,
      ),
      'out' => 
      array (
        9 => 8,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        11 => 2,
      ),
      'out' => 
      array (
        4 => 1,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 4,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        9 => 4,
      ),
      'out' => 
      array (
        2 => 5,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[30001] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 1,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 7,
        5 => 1,
      ),
      'out' => 
      array (
        4 => 9,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 5,
      1 => 2,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 2,
      10 => 4,
      11 => 5,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 3,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 2,
        11 => 5,
      ),
      'out' => 
      array (
        9 => 3,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 9,
        11 => 4,
      ),
      'out' => 
      array (
        4 => 1,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 3,
      9 => 7,
      10 => 4,
      11 => 8,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 6,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 3,
        11 => 8,
      ),
      'out' => 
      array (
        7 => 2,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[30002] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 4,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 7,
      7 => 2,
      9 => 3,
      10 => 8,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 1,
        11 => 4,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 8,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 7,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        7 => 7,
        9 => 8,
      ),
      'out' => 
      array (
        7 => 2,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 9,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 7,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 4,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 7,
        11 => 9,
      ),
      'out' => 
      array (
        10 => 4,
        11 => 6,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 7,
      10 => 4,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 3,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 7,
      10 => 4,
      11 => 1,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 3,
        11 => 1,
      ),
      'out' => 
      array (
        2 => 5,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[30003] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 7,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 8,
      5 => 7,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 9,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 8,
        11 => 4,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 8,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 7,
      10 => 3,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        9 => 7,
        10 => 3,
      ),
      'out' => 
      array (
        9 => 8,
        10 => 4,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 1,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 6,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 5,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 6,
        4 => 1,
      ),
      'out' => 
      array (
        2 => 5,
        4 => 9,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 1,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 2,
      1 => 7,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 7,
      5 => 8,
      7 => 6,
      9 => 2,
      10 => 4,
      11 => 9,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 7,
        9 => 2,
      ),
      'out' => 
      array (
        4 => 1,
        9 => 3,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);

$ws[30004] = array (
  0 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 9,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 2,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  1 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 7,
      4 => 5,
      5 => 8,
      7 => 6,
      9 => 3,
      10 => 1,
      11 => 4,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        11 => 4,
      ),
      'out' => 
      array (
        4 => 9,
        11 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 1.0,
  ),
  2 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 8,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 3,
      1 => 5,
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  3 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 5,
      5 => 3,
      7 => 2,
      9 => 7,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 1,
      1 => 8,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        4 => 5,
        5 => 3,
      ),
      'out' => 
      array (
        4 => 1,
        5 => 8,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 2.0,
  ),
  4 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 1,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 4,
      1 => 6,
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  5 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 5,
      4 => 9,
      5 => 8,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 6,
    ),
    'bench' => 
    array (
      0 => 7,
      1 => 1,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        10 => 4,
        11 => 6,
      ),
      'out' => 
      array (
        10 => 1,
        11 => 7,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 3.0,
  ),
  6 => 
  array (
    'start' => 0,
    'lineup' => 
    array (
      1 => 0,
      2 => 9,
      4 => 1,
      5 => 5,
      7 => 2,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 6,
      1 => 8,
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
  7 => 
  array (
    'start' => 7.5,
    'lineup' => 
    array (
      1 => 0,
      2 => 8,
      4 => 1,
      5 => 5,
      7 => 6,
      9 => 3,
      10 => 4,
      11 => 7,
    ),
    'bench' => 
    array (
      0 => 9,
      1 => 2,
    ),
    'subs' => 
    array (
      'in' => 
      array (
        2 => 8,
        7 => 6,
      ),
      'out' => 
      array (
        2 => 9,
        7 => 2,
      ),
    ),
    'duration' => 450.0,
    'game_counter' => 4.0,
  ),
);



$ws[50000] = array(
  0 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(9,2),
    'duration' => 450,
    'game_counter' => 1
  ),
  1 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 2,
7 => 6,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(8,5),
    'subs' => array(
      'in' => array(
2 => 9,
5 => 2
),
      'out' => array(
2 => 5,
5 => 8
)
    ),
    'duration' => 450,
    'game_counter' => 1
  ),
  2 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(4,7),
    'duration' => 450,
    'game_counter' => 2
  ),
  3 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 2,
9 => 7,
10 => 1,
11 => 4
),
    'bench' => array(3,6),
    'subs' => array(
      'in' => array(
9 => 7,
11 => 4
),
      'out' => array(
9 => 3,
11 => 6
)
    ),
    'duration' => 450,
    'game_counter' => 2
  ),
  4 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(4,8),
    'duration' => 450,
    'game_counter' => 3
  ),
  5 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 8,
10 => 4,
11 => 6
),
    'bench' => array(3,1),
    'subs' => array(
      'in' => array(
9 => 8,
10 => 4
),
      'out' => array(
9 => 3,
10 => 1
)
    ),
    'duration' => 450,
    'game_counter' => 3
  ),
  6 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 2,
9 => 3,
10 => 4,
11 => 9
),
    'bench' => array(7,6),
    'duration' => 450,
    'game_counter' => 4
  ),
  7 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(9,2),
    'subs' => array(
      'in' => array(
7 => 6,
11 => 7
),
      'out' => array(
7 => 2,
11 => 9
)
    ),
    'duration' => 450,
    'game_counter' => 4
  )
);
$ws[50001] = array(
  0 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 6,
9 => 3,
10 => 1,
11 => 7
),
    'bench' => array(4,2),
    'duration' => 450,
    'game_counter' => 1
  ),
  1 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 4,
7 => 2,
9 => 3,
10 => 1,
11 => 7
),
    'bench' => array(8,6),
    'subs' => array(
      'in' => array(
5 => 4,
7 => 2
),
      'out' => array(
5 => 8,
7 => 6
)
    ),
    'duration' => 450,
    'game_counter' => 1
  ),
  2 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 2,
9 => 7,
10 => 4,
11 => 6
),
    'bench' => array(3,9),
    'duration' => 450,
    'game_counter' => 2
  ),
  3 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 8,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(5,7),
    'subs' => array(
      'in' => array(
2 => 9,
9 => 3
),
      'out' => array(
2 => 5,
9 => 7
)
    ),
    'duration' => 450,
    'game_counter' => 2
  ),
  4 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(2,1),
    'duration' => 450,
    'game_counter' => 3
  ),
  5 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 2
),
    'bench' => array(7,9),
    'subs' => array(
      'in' => array(
4 => 1,
11 => 2
),
      'out' => array(
4 => 9,
11 => 7
)
    ),
    'duration' => 450,
    'game_counter' => 3
  ),
  6 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(4,8),
    'duration' => 450,
    'game_counter' => 4
  ),
  7 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 8,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(1,5),
    'subs' => array(
      'in' => array(
2 => 8,
10 => 4
),
      'out' => array(
2 => 5,
10 => 1
)
    ),
    'duration' => 450,
    'game_counter' => 4
  )
);
$ws[50002] = array(
  0 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 7,
10 => 4,
11 => 9
),
    'bench' => array(3,2),
    'duration' => 450,
    'game_counter' => 1
  ),
  1 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 2
),
    'bench' => array(7,9),
    'subs' => array(
      'in' => array(
9 => 3,
11 => 2
),
      'out' => array(
9 => 7,
11 => 9
)
    ),
    'duration' => 450,
    'game_counter' => 1
  ),
  2 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(8,1),
    'duration' => 450,
    'game_counter' => 2
  ),
  3 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(7,4),
    'subs' => array(
      'in' => array(
5 => 8,
10 => 1
),
      'out' => array(
5 => 7,
10 => 4
)
    ),
    'duration' => 450,
    'game_counter' => 2
  ),
  4 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 8,
7 => 2,
9 => 7,
10 => 4,
11 => 6
),
    'bench' => array(3,5),
    'duration' => 450,
    'game_counter' => 3
  ),
  5 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 8,
7 => 3,
9 => 7,
10 => 4,
11 => 5
),
    'bench' => array(6,2),
    'subs' => array(
      'in' => array(
7 => 3,
11 => 5
),
      'out' => array(
7 => 2,
11 => 6
)
    ),
    'duration' => 450,
    'game_counter' => 3
  ),
  6 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(8,1),
    'duration' => 450,
    'game_counter' => 4
  ),
  7 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 8,
4 => 1,
5 => 7,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(9,5),
    'subs' => array(
      'in' => array(
2 => 8,
4 => 1
),
      'out' => array(
2 => 5,
4 => 9
)
    ),
    'duration' => 450,
    'game_counter' => 4
  )
);
$ws[50003] = array(
  0 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 5,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(8,7),
    'duration' => 450,
    'game_counter' => 1
  ),
  1 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 8,
5 => 5,
7 => 2,
9 => 7,
10 => 4,
11 => 6
),
    'bench' => array(3,1),
    'subs' => array(
      'in' => array(
4 => 8,
9 => 7
),
      'out' => array(
4 => 1,
9 => 3
)
    ),
    'duration' => 450,
    'game_counter' => 1
  ),
  2 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 6,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(2,9),
    'duration' => 450,
    'game_counter' => 2
  ),
  3 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 9,
9 => 2,
10 => 4,
11 => 7
),
    'bench' => array(3,6),
    'subs' => array(
      'in' => array(
7 => 9,
9 => 2
),
      'out' => array(
7 => 6,
9 => 3
)
    ),
    'duration' => 450,
    'game_counter' => 2
  ),
  4 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 2,
9 => 3,
10 => 8,
11 => 6
),
    'bench' => array(4,1),
    'duration' => 450,
    'game_counter' => 3
  ),
  5 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 7,
7 => 1,
9 => 3,
10 => 8,
11 => 4
),
    'bench' => array(6,2),
    'subs' => array(
      'in' => array(
7 => 1,
11 => 4
),
      'out' => array(
7 => 2,
11 => 6
)
    ),
    'duration' => 450,
    'game_counter' => 3
  ),
  6 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 8,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(5,7),
    'duration' => 450,
    'game_counter' => 4
  ),
  7 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 7,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(8,9),
    'subs' => array(
      'in' => array(
2 => 5,
5 => 7
),
      'out' => array(
2 => 9,
5 => 8
)
    ),
    'duration' => 450,
    'game_counter' => 4
  )
);
$ws[50004] = array(
  0 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 2,
9 => 3,
10 => 4,
11 => 7
),
    'bench' => array(6,1),
    'duration' => 450,
    'game_counter' => 1
  ),
  1 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 8,
7 => 6,
9 => 3,
10 => 1,
11 => 7
),
    'bench' => array(2,4),
    'subs' => array(
      'in' => array(
7 => 6,
10 => 1
),
      'out' => array(
7 => 2,
10 => 4
)
    ),
    'duration' => 450,
    'game_counter' => 1
  ),
  2 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 2,
9 => 3,
10 => 4,
11 => 6
),
    'bench' => array(9,7),
    'duration' => 450,
    'game_counter' => 2
  ),
  3 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 8,
7 => 7,
9 => 3,
10 => 4,
11 => 9
),
    'bench' => array(2,6),
    'subs' => array(
      'in' => array(
7 => 7,
11 => 9
),
      'out' => array(
7 => 2,
11 => 6
)
    ),
    'duration' => 450,
    'game_counter' => 2
  ),
  4 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 7,
4 => 9,
5 => 8,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(5,4),
    'duration' => 450,
    'game_counter' => 3
  ),
  5 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 9,
5 => 4,
7 => 2,
9 => 3,
10 => 1,
11 => 6
),
    'bench' => array(7,8),
    'subs' => array(
      'in' => array(
2 => 5,
5 => 4
),
      'out' => array(
2 => 7,
5 => 8
)
    ),
    'duration' => 450,
    'game_counter' => 3
  ),
  6 => array(
    'start' => 0,
    'lineup' => array(
1 => 0,
2 => 9,
4 => 1,
5 => 8,
7 => 2,
9 => 7,
10 => 4,
11 => 6
),
    'bench' => array(3,5),
    'duration' => 450,
    'game_counter' => 4
  ),
  7 => array(
    'start' => 7.5,
    'lineup' => array(
1 => 0,
2 => 5,
4 => 1,
5 => 3,
7 => 2,
9 => 7,
10 => 4,
11 => 6
),
    'bench' => array(9,8),
    'subs' => array(
      'in' => array(
2 => 5,
5 => 3
),
      'out' => array(
2 => 9,
5 => 8
)
    ),
    'duration' => 450,
    'game_counter' => 4
  )
);

