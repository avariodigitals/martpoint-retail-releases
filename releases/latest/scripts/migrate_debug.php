<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

echo "Connected OK\n";

// Check db_item_barcodes columns
$res = $conn->query("SHOW COLUMNS FROM db_item_barcodes LIKE 'serial_number'");
echo "serial_number exists: " . ($res->num_rows > 0 ? "YES" : "NO") . "\n";

// Try simple ALTER
echo "Testing ALTER...\n";
$cols = $conn->query("SHOW COLUMNS FROM db_item_barcodes LIKE 'serial_number'");
if ($cols->num_rows == 0) {
    $sql = "ALTER TABLE db_item_barcodes ADD COLUMN serial_number VARCHAR(100) NULL AFTER batch_lot";
    if ($conn->query($sql)) echo "Added serial_number OK\n";
    else echo "ERROR adding serial_number: " . $conn->error . "\n";
} else {
    echo "serial_number already exists\n";
}

$conn->close();
echo "Done\n";
