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
      4  => 4,
      5  => 6,
      7  => 7,
      9  => 5,
      10 => 9,
      11 => 2
    ),
    "bench" => array(1, 3),
    "duration" => 7.5 * 60,
    "game_counter" => 1
  ),
  1 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 1,
    2  => 8,
    4  => 4,
    5  => 6,
    7  => 7,
    9  => 5,
    10 => 9,
    11 => 3
  ),
  "bench" => array(0, 2),
  "subs" => array(
    "in" => array(1 => 1, 11 => 3),
    "out" => array(1 => 0, 11 => 2)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 1
  ),
  2 => array(
  "start" => 0,
  "lineup" => array(
    1  => 0,
    2  => 2,
    4  => 9,
    5  => 8,
    7  => 5,
    9  => 7,
    10 => 4,
    11 => 3
  ),
  "bench" => array(1, 6),
  "duration" => 7.5 * 60,
  "game_counter" => 2
),
3 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 1,
    2  => 2,
    4  => 9,
    5  => 8,
    7  => 5,
    9  => 6,
    10 => 4,
    11 => 3
  ),
  "bench" => array(0, 7),
  "subs" => array(
    "in" => array(1 => 1, 9 => 6),
    "out" => array(1 => 0, 9 => 7)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 2
),
4 => array(
  "start" => 0,
  "lineup" => array(
    1  => 1,
    2  => 6,
    4  => 9,
    5  => 3,
    7  => 2,
    9  => 7,
    10 => 8,
    11 => 5
  ),
  "bench" => array(0, 4),
  "duration" => 7.5 * 60,
  "game_counter" => 3
), 
5 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 0,
    2  => 6,
    4  => 9,
    5  => 3,
    7  => 2,
    9  => 7,
    10 => 4,
    11 => 5
  ),
  "bench" => array(1, 8),
  "subs" => array(
    "in" => array(1 => 0, 10 => 4),
    "out" => array(1 => 1, 10 => 8)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 3
),
6 => array(
  "start" => 0,
  "lineup" => array(
    1  => 1,
    2  => 5,
    4  => 4,
    5  => 6,
    7  => 2,
    9  => 3,
    10 => 8,
    11 => 7
  ),
  "bench" => array(0, 9),
  "duration" => 7.5 * 60,
  "game_counter" => 4
),
7 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 0,
    2  => 9,
    4  => 4,
    5  => 6,
    7  => 2,
    9  => 3,
    10 => 8,
    11 => 7
  ),
  "bench" => array(1, 5),
  "subs" => array(
    "in" => array(1 => 0, 2 => 9),
    "out" => array(1 => 1, 2 => 5)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 4
));


$ws[] = array(
  0 => array(
    "start" => 0,
    "lineup" => array(
      1  => 0,
      2  => 8,
      4  => 4,
      5  => 6,
      7  => 7,
      9  => 5,
      10 => 9,
      11 => 2
    ),
    "bench" => array(1, 3),
    "duration" => 7.5 * 60,
    "game_counter" => 1
  ),
  1 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 1,
    2  => 8,
    4  => 4,
    5  => 6,
    7  => 7,
    9  => 5,
    10 => 9,
    11 => 3
  ),
  "bench" => array(0, 2),
  "subs" => array(
    "in" => array(1 => 1, 11 => 3),
    "out" => array(1 => 0, 11 => 2)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 1
  ),
  2 => array(
  "start" => 0,
  "lineup" => array(
    1  => 0,
    2  => 2,
    4  => 9,
    5  => 8,
    7  => 5,
    9  => 7,
    10 => 4,
    11 => 3
  ),
  "bench" => array(1, 6),
  "duration" => 7.5 * 60,
  "game_counter" => 2
),
3 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 1,
    2  => 2,
    4  => 9,
    5  => 8,
    7  => 5,
    9  => 6,
    10 => 4,
    11 => 3
  ),
  "bench" => array(0, 7),
  "subs" => array(
    "in" => array(1 => 1, 9 => 6),
    "out" => array(1 => 0, 9 => 7)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 2
),
4 => array(
  "start" => 0,
  "lineup" => array(
    1  => 1,
    2  => 6,
    4  => 9,
    5  => 3,
    7  => 2,
    9  => 7,
    10 => 8,
    11 => 5
  ),
  "bench" => array(0, 4),
  "duration" => 7.5 * 60,
  "game_counter" => 3
), 
5 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 0,
    2  => 6,
    4  => 9,
    5  => 3,
    7  => 2,
    9  => 7,
    10 => 4,
    11 => 5
  ),
  "bench" => array(1, 8),
  "subs" => array(
    "in" => array(1 => 0, 10 => 4),
    "out" => array(1 => 1, 10 => 8)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 3
),
6 => array(
  "start" => 0,
  "lineup" => array(
    1  => 1,
    2  => 5,
    4  => 4,
    5  => 8,
    7  => 2,
    9  => 3,
    10 => 6,
    11 => 7
  ),
  "bench" => array(0, 9),
  "duration" => 7.5 * 60,
  "game_counter" => 4
),
7 => array(
  "start" => 7.5,
  "lineup" => array(
    1  => 0,
    2  => 9,
    4  => 4,
    5  => 8,
    7  => 2,
    9  => 3,
    10 => 6,
    11 => 7
  ),
  "bench" => array(1, 5),
  "subs" => array(
    "in" => array(1 => 0, 2 => 9),
    "out" => array(1 => 1, 2 => 5)
  ),
  "duration" => 7.5 * 60,
  "game_counter" => 4
));




$wisselschema_index["8v8_2gk_4x15"] = array_rand($ws);

$events["8v8_2gk_4x15"][10] = $ws[$wisselschema_index["8v8_2gk_4x15"] ];

?>