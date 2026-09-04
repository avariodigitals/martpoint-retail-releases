<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

echo "=== Checking db_item_barcodes columns ===\n";

$required = [
    'serial_number' => "ALTER TABLE db_item_barcodes ADD COLUMN serial_number VARCHAR(100) NULL AFTER batch_lot",
    'imei_number' => "ALTER TABLE db_item_barcodes ADD COLUMN imei_number VARCHAR(100) NULL AFTER serial_number",
    'warranty_months' => "ALTER TABLE db_item_barcodes ADD COLUMN warranty_months INT(11) NULL DEFAULT 0 AFTER imei_number",
    'expire_date' => "ALTER TABLE db_item_barcodes ADD COLUMN expire_date DATE NULL AFTER qty",
    'mfg_date' => "ALTER TABLE db_item_barcodes ADD COLUMN mfg_date DATE NULL AFTER expire_date",
];

$cols = $conn->query("SHOW COLUMNS FROM db_item_barcodes");
$existing = [];
while ($c = $cols->fetch_assoc()) {
    $existing[] = $c['Field'];
}

$fixed = 0;
foreach ($required as $col => $sql) {
    if (in_array($col, $existing)) {
        echo "✓ $col exists\n";
    } else {
        echo "✗ $col MISSING — adding...\n";
        if ($conn->query($sql)) {
            echo "  ✓ Added $col\n";
            $fixed++;
        } else {
            echo "  ✗ Error: " . $conn->error . "\n";
        }
    }
}

if ($fixed > 0) {
    echo "\n=== Fixed $fixed missing column(s) ===\n";
} else {
    echo "\n=== All columns already exist ===\n";
}

$conn->close();
