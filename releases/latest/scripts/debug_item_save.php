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

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$item_id) {
    echo "Usage: debug_item_save.php?id=ITEM_ID\n";
    exit;
}

// Check db_items
$item = $conn->query("SELECT id, item_name, serial_number, imei_number, warranty_months, custom_barcode FROM db_items WHERE id=$item_id LIMIT 1")->fetch_assoc();
echo "=== db_items (item_id=$item_id) ===\n";
if ($item) {
    echo "Name: {$item['item_name']}\n";
    echo "SN:   " . ($item['serial_number'] ?: '(empty)') . "\n";
    echo "IMEI: " . ($item['imei_number'] ?: '(empty)') . "\n";
    echo "Warranty: {$item['warranty_months']}\n";
} else {
    echo "Item not found\n";
}

// Check db_item_barcodes
$bc = $conn->query("SELECT * FROM db_item_barcodes WHERE item_id=$item_id AND status=1 ORDER BY id ASC");
echo "\n=== db_item_barcodes ===\n";
echo "Rows found: " . $bc->num_rows . "\n";
while ($r = $bc->fetch_assoc()) {
    echo "--- Row {$r['id']} ---\n";
    echo "  Barcode: " . ($r['barcode'] ?: '(empty)') . "\n";
    echo "  Serial:  " . ($r['serial_number'] ?: '(empty)') . "\n";
    echo "  IMEI:    " . ($r['imei_number'] ?: '(empty)') . "\n";
    echo "  Warranty: {$r['warranty_months']}\n";
    echo "  PP: {$r['purchase_price']} | SP: {$r['sales_price']} | MRP: {$r['mrp']} | Qty: {$r['qty']}\n";
}

// Check if columns exist
$cols = $conn->query("SHOW COLUMNS FROM db_item_barcodes");
echo "\n=== db_item_barcodes columns ===\n";
$has_serial = false;
$has_imei = false;
$has_warranty = false;
while ($c = $cols->fetch_assoc()) {
    if ($c['Field'] == 'serial_number') $has_serial = true;
    if ($c['Field'] == 'imei_number') $has_imei = true;
    if ($c['Field'] == 'warranty_months') $has_warranty = true;
}
echo "serial_number: " . ($has_serial ? 'YES' : 'NO') . "\n";
echo "imei_number: " . ($has_imei ? 'YES' : 'NO') . "\n";
echo "warranty_months: " . ($has_warranty ? 'YES' : 'NO') . "\n";

$conn->close();
