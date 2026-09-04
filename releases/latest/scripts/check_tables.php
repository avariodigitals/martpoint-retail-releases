<?php
$host = "localhost";
$user = "marttes";
$pass = "marttes";
$dbname = "marttes";

$conn = new mysqli($host, $user, $pass, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$tables = ["db_loyalty_settings", "db_loyalty_tiers", "db_loyalty_bonus_rules", "db_loyalty_points", "db_loyalty_product_points"];
foreach($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    echo $t . ": " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";
}
$conn->close();
