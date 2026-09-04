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

$out = [];

// Migrate existing items with serial/imei from db_items to db_item_barcodes
// Use 'stock' column instead of 'opening_stock', no warehouse_id on db_items
$items = $conn->query("SELECT id, serial_number, imei_number, warranty_months, custom_barcode, purchase_price, sales_price, mrp, stock FROM db_items WHERE (serial_number IS NOT NULL AND serial_number != '') OR (imei_number IS NOT NULL AND imei_number != '')");

$migrated = 0;
$skipped = 0;
if ($items === false) {
    $out[] = "SELECT failed: " . $conn->error;
} else {
    while ($item = $items->fetch_assoc()) {
        $sn = $item['serial_number'] ?? '';
        $imei = $item['imei_number'] ?? '';
        $check = $conn->prepare("SELECT id FROM db_item_barcodes WHERE item_id = ? AND serial_number = ? AND imei_number = ?");
        $check->bind_param("iss", $item['id'], $sn, $imei);
        $check->execute();
        $check_res = $check->get_result();
        if ($check_res->num_rows > 0) {
            $skipped++;
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO db_item_barcodes (item_id, barcode, batch_lot, serial_number, imei_number, warranty_months, purchase_price, sales_price, mrp, qty, warehouse_id, status, created_date, created_time) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, 0, 1, CURDATE(), CURTIME())");
        $barcode = $item['custom_barcode'] ?? '';
        $warranty = (int)($item['warranty_months'] ?? 0);
        $pp = (float)($item['purchase_price'] ?? 0);
        $sp = (float)($item['sales_price'] ?? 0);
        $mrp = (float)($item['mrp'] ?? 0);
        $qty = (int)($item['stock'] ?? 1);
        if ($qty < 1) $qty = 1;

        $stmt->bind_param("isssidddii", $item['id'], $barcode, $sn, $imei, $warranty, $pp, $sp, $mrp, $qty);
        if ($stmt->execute()) $migrated++;
        else $out[] = "Migrate error item {$item['id']}: " . $conn->error;
    }
}

$conn->close();

header('Content-Type: text/plain');
echo "=== MIGRATE FIX ===\n\n";
foreach ($out as $o) echo $o . "\n";
echo "\nMigrated: $migrated item(s) with Serial/IMEI into db_item_barcodes\n";
echo "Skipped:  $skipped item(s)\n";
echo "\nDone.\n";
