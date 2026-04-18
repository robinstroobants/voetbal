<?php
require 'wisselschemas/8v8_1gk_4x15_10sp.php';
// Don't execute the logic block if it relies on session/game vars.
// Oh wait, requiring the file executes the logic at the end!
// The logic at the end does `$ws[$te_gebruiken_schema]` which crashes!
// So it crashes while requiring.
echo "Keys: ";
print_r(array_keys($ws));
