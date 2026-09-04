<?php
// Run this from browser: http://localhost:8888/fix_laundry_table.php
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

$sql = "CREATE TABLE IF NOT EXISTS db_laundry_orders (
    id INT(10) NOT NULL AUTO_INCREMENT,
    sales_id INT(10) NOT NULL,
    store_id INT(10) NOT NULL,
    tag_number VARCHAR(50) NULL,
    service_type VARCHAR(50) NULL DEFAULT 'standard',
    notes TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'dropped_off',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY sales_id (sales_id),
    KEY store_id (store_id),
    KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($conn->query($sql) === TRUE) {
    echo "db_laundry_orders table created successfully.<br>";
} else {
    echo "Error: " . $conn->error . "<br>";
}

$conn->close();
echo "Done.";
