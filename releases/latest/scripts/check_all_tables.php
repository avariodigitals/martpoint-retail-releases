<?php
$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed");

$tables = ['db_loyalty_settings','db_loyalty_tiers','db_loyalty_points','db_loyalty_bonus_rules',
    'db_gift_cards','db_store_credit','db_store_credit_usage','db_customers'];
header('Content-Type: text/plain');
echo "=== TABLE STATUS ===\n\n";
foreach ($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    echo str_pad($t, 30) . ": " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";
}
$conn->close();
echo "\nDone.\n";
