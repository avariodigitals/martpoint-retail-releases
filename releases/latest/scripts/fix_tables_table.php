<?php
// Run this from browser: http://localhost:8888/fix_tables_table.php
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

$sql = "CREATE TABLE IF NOT EXISTS db_tables (
    id INT(10) NOT NULL AUTO_INCREMENT,
    store_id INT(10) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    table_code VARCHAR(20) NULL,
    zone VARCHAR(50) NULL,
    capacity INT(5) NOT NULL DEFAULT 4,
    status VARCHAR(20) NOT NULL DEFAULT 'available',
    qr_code_url VARCHAR(255) NULL,
    sort_order INT(5) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($conn->query($sql) === TRUE) {
    echo "db_tables table created successfully.<br>";
} else {
    echo "Error: " . $conn->error . "<br>";
}

$conn->close();
echo "Done.";
