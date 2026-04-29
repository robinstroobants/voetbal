<?php
session_start();
session_unset();
session_destroy();
header("Location: /login?logout=1");
exit;
?>
