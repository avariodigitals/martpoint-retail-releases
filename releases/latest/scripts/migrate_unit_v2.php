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

function addCol($conn, $table, $col, $def) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    if ($res->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $def";
        if ($conn->query($sql)) return "ADDED: $table.$col";
        return "ERROR: $table.$col: " . $conn->error;
    }
    return "EXISTS: $table.$col (skipped)";
}

$out = [];
$out[] = addCol($conn, 'db_item_barcodes', 'serial_number', "VARCHAR(100) NULL AFTER batch_lot");
$out[] = addCol($conn, 'db_item_barcodes', 'imei_number', "VARCHAR(50) NULL AFTER serial_number");
$out[] = addCol($conn, 'db_item_barcodes', 'warranty_months', "INT(3) NULL DEFAULT 0 AFTER imei_number");
$out[] = addCol($conn, 'db_salesitems', 'barcode_id', "INT(11) NULL DEFAULT 0 AFTER item_id");
$out[] = addCol($conn, 'db_holditems', 'barcode_id', "INT(11) NULL DEFAULT 0 AFTER item_id");
$out[] = addCol($conn, 'db_salesitemsreturn', 'barcode_id', "INT(11) NULL DEFAULT 0 AFTER item_id");

// Check what columns db_items actually has
$cols_res = $conn->query("SHOW COLUMNS FROM db_items");
$cols = [];
while ($c = $cols_res->fetch_assoc()) $cols[] = $c['Field'];
$out[] = "\ndb_items columns: " . implode(', ', $cols);

// Only migrate if serial_number/imei_number exist on db_items
if (!in_array('serial_number', $cols) || !in_array('imei_number', $cols)) {
    $out[] = "\nERROR: db_items does not have serial_number or imei_number columns yet!";
    $out[] = "Run migration 003 first (adds these columns to db_items).";
} else {
    $items = $conn->query("SELECT id, serial_number, imei_number, warranty_months, custom_barcode, purchase_price, sales_price, mrp, opening_stock, warehouse_id FROM db_items WHERE (serial_number IS NOT NULL AND serial_number != '') OR (imei_number IS NOT NULL AND imei_number != '')");
    if ($items === false) {
        $out[] = "\nSELECT failed: " . $conn->error;
    } else {
        $migrated = 0;
        $skipped = 0;
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

            $stmt = $conn->prepare("INSERT INTO db_item_barcodes (item_id, barcode, batch_lot, serial_number, imei_number, warranty_months, purchase_price, sales_price, mrp, qty, warehouse_id, status, created_date, created_time) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE(), CURTIME())");
            $barcode = $item['custom_barcode'] ?? '';
            $warranty = (int)($item['warranty_months'] ?? 0);
            $pp = (float)($item['purchase_price'] ?? 0);
            $sp = (float)($item['sales_price'] ?? 0);
            $mrp = (float)($item['mrp'] ?? 0);
            $qty = (int)($item['opening_stock'] ?? 1);
            if ($qty < 1) $qty = 1;
            $wh = (int)($item['warehouse_id'] ?? 0);

            $stmt->bind_param("isssidddiii", $item['id'], $barcode, $sn, $imei, $warranty, $pp, $sp, $mrp, $qty, $wh);
            if ($stmt->execute()) $migrated++;
            else $out[] = "Migrate error item {$item['id']}: " . $conn->error;
        }
        $out[] = "\nMigrated: $migrated item(s)";
        $out[] = "Skipped:  $skipped item(s)";
    }
}

$conn->close();

header('Content-Type: text/plain');
echo "=== UNIT TRACKING MIGRATION ===\n\n";
foreach ($out as $o) echo $o . "\n";
echo "\nDone.\n";
