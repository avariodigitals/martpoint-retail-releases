<?php
define('BASEPATH', true);
require_once 'application/config/database.php';

$host = $db['default']['hostname'];
$user = $db['default']['username'];
$pass = $db['default']['password'];
$dbname = $db['default']['database'];

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h3>1. Check if db_items has laundry_service_type column</h3>";
$r = $conn->query("SHOW COLUMNS FROM db_items LIKE 'laundry_service_type'");
if ($r->num_rows == 0) {
    echo "<b style='color:red'>MISSING!</b> Column does not exist.<br>";
    // Create it
    $conn->query("ALTER TABLE db_items ADD COLUMN laundry_service_type VARCHAR(30) NULL AFTER description");
    echo "Created column.<br>";
} else {
    echo "<b style='color:green'>EXISTS</b><br>";
}

echo "<h3>2. Check db_laundry_order_items service_type values</h3>";
$r = $conn->query("SELECT li.id, i.item_name, i.service_bit, li.service_type AS stored_type, i.laundry_service_type AS configured_type
                   FROM db_laundry_order_items li
                   JOIN db_items i ON i.id = li.item_id
                   LIMIT 10");
if ($r->num_rows == 0) {
    echo "No laundry order items found.<br>";
} else {
    echo "<table border=1 cellpadding=5><tr><th>ID</th><th>Name</th><th>service_bit</th><th>Stored Type</th><th>Configured Type</th></tr>";
    while ($row = $r->fetch_assoc()) {
        $match = ($row['stored_type'] == $row['configured_type']) ? 'green' : 'red';
        echo "<tr style='color:$match'><td>{$row['id']}</td><td>{$row['item_name']}</td><td>{$row['service_bit']}</td><td>{$row['stored_type']}</td><td>{$row['configured_type']}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>3. Check services with laundry_service_type configured</h3>";
$r = $conn->query("SELECT id, item_name, service_bit, laundry_service_type FROM db_items WHERE service_bit = 1 AND (laundry_service_type IS NOT NULL AND laundry_service_type != '') LIMIT 10");
if ($r->num_rows == 0) {
    echo "No services have laundry_service_type configured.<br>";
} else {
    echo "<table border=1 cellpadding=5><tr><th>ID</th><th>Name</th><th>service_bit</th><th>laundry_service_type</th></tr>";
    while ($row = $r->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['item_name']}</td><td>{$row['service_bit']}</td><td>{$row['laundry_service_type']}</td></tr>";
    }
    echo "</table>";
}

echo "<h3>4. Check all services (service_bit=1)</h3>";
$r = $conn->query("SELECT id, item_name, laundry_service_type FROM db_items WHERE service_bit = 1 LIMIT 10");
echo "<table border=1 cellpadding=5><tr><th>ID</th><th>Name</th><th>laundry_service_type</th></tr>";
while ($row = $r->fetch_assoc()) {
    $type = $row['laundry_service_type'] ?: '<i>NULL/empty</i>';
    echo "<tr><td>{$row['id']}</td><td>{$row['item_name']}</td><td>$type</td></tr>";
}
echo "</table>";

$conn->close();
