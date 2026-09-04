<?php
$host = "localhost";
$user = "marttes";
$pass = "marttes";
$dbname = "marttes";

$conn = new mysqli($host, $user, $pass, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if db_loyalty_tiers exists
$res = $conn->query("SHOW TABLES LIKE 'db_loyalty_tiers'");
echo "db_loyalty_tiers: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . "\n\n";

if($res->num_rows > 0) {
    $res2 = $conn->query("SHOW COLUMNS FROM db_loyalty_tiers");
    echo "Columns in db_loyalty_tiers:\n";
    while($row = $res2->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

// Also check db_loyalty_settings
$res3 = $conn->query("SHOW TABLES LIKE 'db_loyalty_settings'");
echo "\ndb_loyalty_settings: " . ($res3->num_rows > 0 ? "EXISTS" : "MISSING") . "\n";

$conn->close();
