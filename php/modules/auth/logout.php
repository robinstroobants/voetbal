<?php
if (session_status() === PHP_SESSION_NONE) session_start(); // Veiligheidsnet indien direct aangeroepen
session_unset();
session_destroy();
header("Location: /login?logout=1");
exit;
?>
