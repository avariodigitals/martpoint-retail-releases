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

// Find items with serial/imei in db_items
$items = $conn->query("SELECT id, item_name, serial_number, imei_number, warranty_months, custom_barcode FROM db_items WHERE (serial_number IS NOT NULL AND serial_number != '') OR (imei_number IS NOT NULL AND imei_number != '') ORDER BY id DESC LIMIT 10");
echo "=== db_items (last 10 items with Serial/IMEI) ===\n";
if ($items->num_rows == 0) {
    echo "No items with Serial/IMEI in db_items\n";
} else {
    while ($r = $items->fetch_assoc()) {
        echo "ID: {$r['id']} | Name: {$r['item_name']} | Barcode: {$r['custom_barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']} | Warranty: {$r['warranty_months']}\n";
    }
}

// Find items with serial/imei in db_item_barcodes
$bc = $conn->query("SELECT b.id as bc_id, b.item_id, a.item_name, b.barcode, b.serial_number, b.imei_number, b.warranty_months, b.qty FROM db_item_barcodes b LEFT JOIN db_items a ON a.id = b.item_id WHERE (b.serial_number IS NOT NULL AND b.serial_number != '') OR (b.imei_number IS NOT NULL AND b.imei_number != '') ORDER BY b.id DESC LIMIT 10");
echo "\n=== db_item_barcodes (last 10 rows with Serial/IMEI) ===\n";
if ($bc->num_rows == 0) {
    echo "No barcode rows with Serial/IMEI\n";
} else {
    while ($r = $bc->fetch_assoc()) {
        echo "BC_ID: {$r['bc_id']} | ItemID: {$r['item_id']} | Name: {$r['item_name']} | Barcode: {$r['barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']} | Warranty: {$r['warranty_months']} | Qty: {$r['qty']}\n";
    }
}

// Show ALL barcode rows for a specific item (if found)
$bc_all = $conn->query("SELECT b.*, a.item_name FROM db_item_barcodes b LEFT JOIN db_items a ON a.id = b.item_id ORDER BY b.id DESC LIMIT 20");
echo "\n=== All recent db_item_barcodes rows ===\n";
while ($r = $bc_all->fetch_assoc()) {
    echo "BC_ID: {$r['id']} | ItemID: {$r['item_id']} | Name: {$r['item_name']} | Barcode: {$r['barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']} | Warranty: {$r['warranty_months']} | PP: {$r['purchase_price']} | SP: {$r['sales_price']} | MRP: {$r['mrp']} | Qty: {$r['qty']}\n";
}

$conn->close();
echo "\nDone.\n";
