<?php
// Open this in browser: http://localhost:8888/patch_laundry_db.php
define('BASEPATH', true);
require_once 'application/config/database.php';

$host = $db['default']['hostname'];
$user = $db['default']['username'];
$pass = $db['default']['password'];
$dbname = $db['default']['database'];

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

echo "<h2>Laundry DB Patch</h2>";

// 1. Add column to db_items
$r = $conn->query("SHOW COLUMNS FROM db_items LIKE 'laundry_service_type'");
if ($r->num_rows == 0) {
    $conn->query("ALTER TABLE db_items ADD COLUMN laundry_service_type VARCHAR(30) NULL AFTER description");
    echo "<p>Added <code>laundry_service_type</code> to <code>db_items</code></p>";
} else {
    echo "<p><code>laundry_service_type</code> already exists in <code>db_items</code></p>";
}

// 2. Back-patch existing services that were saved before the column existed
//    (they'll have NULL; we can't know what they were meant to be, so leave them)
//    But if any service has it set, make sure db_laundry_order_items uses it
$affected = $conn->query("UPDATE db_laundry_order_items li
    JOIN db_items i ON i.id = li.item_id
    SET li.service_type = i.laundry_service_type,
        li.updated_at = NOW()
    WHERE i.laundry_service_type IS NOT NULL
      AND i.laundry_service_type != ''
      AND li.service_type != i.laundry_service_type");
if ($affected) {
    echo "<p>Synced " . $conn->affected_rows . " laundry order item(s) to their configured service type.</p>";
} else {
    echo "<p>No existing laundry order items needed syncing (already correct).</p>";
}

// 3. Show current state
echo "<h3>Current Service Configurations</h3>";
echo "<table border=1 cellpadding=5><tr><th>ID</th><th>Service Name</th><th>Configured Type</th></tr>";
$r = $conn->query("SELECT id, item_name, laundry_service_type FROM db_items WHERE service_bit = 1 ORDER BY id DESC LIMIT 20");
while ($row = $r->fetch_assoc()) {
    $type = $row['laundry_service_type'] ?: '<i style=color:#999>not set</i>';
    echo "<tr><td>{$row['id']}</td><td>{$row['item_name']}</td><td>$type</td></tr>";
}
echo "</table>";

// 4. Show laundry order items state
echo "<h3>Current Laundry Order Items</h3>";
echo "<table border=1 cellpadding=5><tr><th>Item</th><th>Stored Type</th><th>Configured Type</th><th>Match?</th></tr>";
$r = $conn->query("SELECT i.item_name, li.service_type AS stored, i.laundry_service_type AS configured
                   FROM db_laundry_order_items li
                   JOIN db_items i ON i.id = li.item_id
                   LIMIT 20");
while ($row = $r->fetch_assoc()) {
    $match = ($row['stored'] == $row['configured']) ? 'Yes' : '<b style=color:red>No</b>';
    echo "<tr><td>{$row['item_name']}</td><td>{$row['stored']}</td><td>{$row['configured']}</td><td>$match</td></tr>";
}
echo "</table>";

$conn->close();
echo "<p><b>Done.</b> Now go to <b>Services → Add New</b>, select a Laundry Service Type, save, and reload the Laundry workflow page.</p>";
