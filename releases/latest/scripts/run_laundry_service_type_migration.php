<?php
// Run this from browser: http://localhost:8888/run_laundry_service_type_migration.php
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

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM db_items LIKE 'laundry_service_type'");
if ($result->num_rows > 0) {
    echo "Column <code>laundry_service_type</code> already exists in <code>db_items</code>.<br>";
} else {
    $sql = "ALTER TABLE db_items
            ADD COLUMN laundry_service_type VARCHAR(30) NULL
            COMMENT 'wash_iron, wash_only, iron_only, dry_clean'
            AFTER description";
    if ($conn->query($sql) === TRUE) {
        echo "Column <code>laundry_service_type</code> added to <code>db_items</code> successfully.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Done.";
