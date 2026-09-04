<?php
define('BASEPATH', true);
require_once 'application/config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if table exists
$res = $mysqli->query("SHOW TABLES LIKE 'db_kitchen_orders'");
if ($res->num_rows === 0) {
    echo "ERROR: db_kitchen_orders table does NOT exist.<br>";
    echo "Run: http://localhost:8888/fix_kitchen_orders_table.php<br><br>";
} else {
    echo "OK: db_kitchen_orders table exists.<br>";
    // Check columns
    $cols = $mysqli->query("SHOW COLUMNS FROM db_kitchen_orders");
    echo "Columns: ";
    while($c = $cols->fetch_assoc()) { echo $c['Field'] . " "; }
    echo "<br><br>";
}

// Check if there are any Final sales today
$today = date('Y-m-d');
$q = $mysqli->query("SELECT COUNT(*) as cnt FROM db_sales WHERE sales_status='Final' AND DATE(created_date) = '$today' AND status=1");
$row = $q->fetch_assoc();
echo "Today's Final sales: " . $row['cnt'] . "<br>";

$mysqli->close();
