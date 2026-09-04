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

$search = isset($_GET['q']) ? $_GET['q'] : 'SN-001234';
$search_lower = strtolower($search);

echo "=== POS Search Diagnostic ===\n";
echo "Search term: '$search'\n\n";

// Check db_items
$stmt = $conn->prepare("SELECT id, item_name, custom_barcode, serial_number, imei_number FROM db_items WHERE LOWER(serial_number) LIKE ? OR LOWER(imei_number) LIKE ? OR LOWER(custom_barcode) LIKE ? LIMIT 5");
$like = "%$search_lower%";
$stmt->bind_param("sss", $like, $like, $like);
$stmt->execute();
$res = $stmt->get_result();
echo "db_items matches:\n";
if ($res->num_rows == 0) echo "  None\n";
while ($r = $res->fetch_assoc()) {
    echo "  ID: {$r['id']} | Name: {$r['item_name']} | Barcode: {$r['custom_barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']}\n";
}

// Check db_item_barcodes
$stmt2 = $conn->prepare("SELECT b.id, b.item_id, a.item_name, b.barcode, b.serial_number, b.imei_number, b.status FROM db_item_barcodes b LEFT JOIN db_items a ON a.id=b.item_id WHERE LOWER(b.serial_number) LIKE ? OR LOWER(b.imei_number) LIKE ? OR LOWER(b.barcode) LIKE ? LIMIT 5");
$stmt2->bind_param("sss", $like, $like, $like);
$stmt2->execute();
$res2 = $stmt2->get_result();
echo "\ndb_item_barcodes matches:\n";
if ($res2->num_rows == 0) echo "  None\n";
while ($r = $res2->fetch_assoc()) {
    echo "  BC_ID: {$r['id']} | ItemID: {$r['item_id']} | Name: {$r['item_name']} | Barcode: {$r['barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']} | Status: {$r['status']}\n";
}

// Show ALL barcode rows (last 10)
echo "\n=== All recent db_item_barcodes ===\n";
$all = $conn->query("SELECT b.id, b.item_id, a.item_name, b.barcode, b.serial_number, b.imei_number FROM db_item_barcodes b LEFT JOIN db_items a ON a.id=b.item_id ORDER BY b.id DESC LIMIT 10");
while ($r = $all->fetch_assoc()) {
    echo "  BC_ID: {$r['id']} | ItemID: {$r['item_id']} | Name: {$r['item_name']} | Barcode: {$r['barcode']} | SN: {$r['serial_number']} | IMEI: {$r['imei_number']}\n";
}

$conn->close();
echo "\nDone.\n";
