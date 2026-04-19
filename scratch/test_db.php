<?php
require_once '/home/customer/www/lineup.webbit.be/public_html/voetbal/php/getconn.php';
$res = $conn->query("SHOW PROCESSLIST");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
