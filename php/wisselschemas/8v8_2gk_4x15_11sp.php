<?php

$key = str_replace(".php","",basename(__FILE__, '.php'));
//echo $key;
$ws=array();
$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 9,
      5  => 10,
      7  => 6,
      9  => 3,
      10 => 2,
      11 => 4
    ),
    "bench" => array(
      1,5,7
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),

  1 => array(
    "start" => 7.5,
    "lineup" => array(
      1  => 0,
      2  => 7,
      4  => 9,
      5  => 10,
      7  => 5,
      9  => 3,
      10 => 2,
      11 => 4
    ),
    "bench" => array(
      1,8,6
    ),
    "subs" => array(
      "in" => array(
         2 => 7,
         7 => 5
      ),
      "out" => array(
         2 => 8,
         7 => 6
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 1
  ),


  2 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 6,
      4  => 2,
      5  => 5,
      7  => 8,
      9  => 3,
      10 => 10,
      11 => 7
    ),
    "bench" => array(
      0,9,4
    ),
    "duration" => 7.5*60,
    "game_counter" => 2
  ),

  3 => array(
    "start" => 7.5,
    "lineup" => array(
      1  => 1,
      2  => 6,
      4  => 2,
      5  => 5,
      7  => 8,
      9  => 4,
      10 => 9,
      11 => 7
    ),
    "bench" => array(
      0,10,3
    ),
    "subs" => array(
      "in" => array(
        9  => 4,
        10 => 9
      ),
      "out" => array(
        9  => 3,
        10 => 10
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
      4  => 9,
      5  => 3,
      7  => 5,
      9  => 8,
      10 => 10,
      11 => 6
    ),
    "bench" => array(
      1,2,7
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),

  5 => array(
    "start" => 7.5,
    "lineup" => array(
      1  => 0,
      2  => 4,
      4  => 2,
      5  => 7,
      7  => 5,
      9  => 8,
      10 => 10,
      11 => 6
    ),
    "bench" => array(
      1,9,3
    ),
    "subs" => array(
      "in" => array(
        4 => 2,
        5 => 7
      ),
      "out" => array(
        5 => 7,
        4 => 9
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 3
  ),

  6 => array(
    "start" => 0,
    "lineup" => array(
      1  => 1,
      2  => 5,
      4  => 10,
      5  => 7,
      7  => 8,
      9  => 9,
      10 => 4,
      11 => 3
    ),
    "bench" => array(
      0,6,2
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),

  7 => array(
    "start" => 7.5,
    "lineup" => array(
      1  => 1,
      2  => 2,
      4  => 10,
      5  => 6,
      7  => 7,
      9  => 9,
      10 => 4,
      11 => 3
    ),
    "bench" => array(
      0,8,5
    ),
    "subs" => array(
      "in" => array(
        2 => 2,
        7 => 6
      ),
      "out" => array(
        2 => 5,
        7 => 8
      )
    ),
    "duration" => 7.5*60,
    "game_counter" => 4
  ),
);
$wisselschema_index["8v8_2gk_4x15"] = array_rand($ws);
$events["8v8_2gk_4x15"][11] = $ws[$wisselschema_index["8v8_2gk_4x15"] ];


?>