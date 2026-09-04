<?php
$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$errors = [];
$created = [];

// 1. Add serial_number, imei_number, warranty_months to db_item_barcodes
$cols = $conn->query("SHOW COLUMNS FROM db_item_barcodes LIKE 'serial_number'");
if ($cols->num_rows == 0) {
    $sql = "ALTER TABLE db_item_barcodes
        ADD COLUMN serial_number VARCHAR(100) NULL AFTER batch_lot,
        ADD COLUMN imei_number VARCHAR(50) NULL AFTER serial_number,
        ADD COLUMN warranty_months INT(3) NULL DEFAULT 0 AFTER imei_number";
    if ($conn->query($sql)) $created[] = "db_item_barcodes: serial_number, imei_number, warranty_months";
    else $errors[] = "db_item_barcodes cols: " . $conn->error;
} else {
    $created[] = "db_item_barcodes columns already exist (skipped)";
}

// 2. Add barcode_id to db_salesitems
$cols = $conn->query("SHOW COLUMNS FROM db_salesitems LIKE 'barcode_id'");
if ($cols->num_rows == 0) {
    $sql = "ALTER TABLE db_salesitems ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id";
    if ($conn->query($sql)) $created[] = "db_salesitems: barcode_id";
    else $errors[] = "db_salesitems barcode_id: " . $conn->error;
} else {
    $created[] = "db_salesitems barcode_id already exists (skipped)";
}

// 3. Add barcode_id to db_holditems
$cols = $conn->query("SHOW COLUMNS FROM db_holditems LIKE 'barcode_id'");
if ($cols->num_rows == 0) {
    $sql = "ALTER TABLE db_holditems ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id";
    if ($conn->query($sql)) $created[] = "db_holditems: barcode_id";
    else $errors[] = "db_holditems barcode_id: " . $conn->error;
} else {
    $created[] = "db_holditems barcode_id already exists (skipped)";
}

// 4. Add barcode_id to db_salesitemsreturn
$cols = $conn->query("SHOW COLUMNS FROM db_salesitemsreturn LIKE 'barcode_id'");
if ($cols->num_rows == 0) {
    $sql = "ALTER TABLE db_salesitemsreturn ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id";
    if ($conn->query($sql)) $created[] = "db_salesitemsreturn: barcode_id";
    else $errors[] = "db_salesitemsreturn barcode_id: " . $conn->error;
} else {
    $created[] = "db_salesitemsreturn barcode_id already exists (skipped)";
}

// 5. Migrate existing serial/imei from db_items to db_item_barcodes
$stmt = $conn->prepare("SELECT id, serial_number, imei_number, warranty_months, custom_barcode, purchase_price, sales_price, mrp, opening_stock, warehouse_id FROM db_items WHERE (serial_number IS NOT NULL AND serial_number != '') OR (imei_number IS NOT NULL AND imei_number != '')");
$stmt->execute();
$result = $stmt->get_result();
$migrated = 0;
$skipped = 0;

while ($item = $result->fetch_assoc()) {
    // Check if already migrated
    $check = $conn->prepare("SELECT id FROM db_item_barcodes WHERE item_id = ? AND serial_number = ? AND imei_number = ?");
    $check->bind_param("iss", $item['id'], $item['serial_number'], $item['imei_number']);
    $check->execute();
    $check_res = $check->get_result();
    if ($check_res->num_rows > 0) {
        $skipped++;
        continue;
    }

    $insert = $conn->prepare("INSERT INTO db_item_barcodes (item_id, barcode, batch_lot, serial_number, imei_number, warranty_months, purchase_price, sales_price, mrp, qty, warehouse_id, status, created_date, created_time) VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE(), CURTIME())");
    $insert->bind_param("issssddddii",
        $item['id'],
        $item['custom_barcode'],
        $item['serial_number'],
        $item['imei_number'],
        $item['warranty_months'],
        $item['purchase_price'],
        $item['sales_price'],
        $item['mrp'],
        $item['opening_stock'],
        $item['warehouse_id']
    );
    if ($insert->execute()) $migrated++;
    else $errors[] = "Migrate item {$item['id']}: " . $conn->error;
}

$conn->close();

header('Content-Type: text/plain');
echo "=== UNIT TRACKING MIGRATION ===\n\n";
if (!empty($created)) { echo "SCHEMA:\n"; foreach ($created as $c) echo "  + $c\n"; }
if ($migrated > 0) echo "\nMIGRATED: $migrated existing item(s) with Serial/IMEI into db_item_barcodes\n";
if ($skipped > 0) echo "SKIPPED: $skipped item(s) already migrated\n";
if (!empty($errors)) { echo "\nERRORS:\n"; foreach ($errors as $e) echo "  ! $e\n"; }
if (empty($created) && empty($errors) && $migrated == 0 && $skipped == 0) echo "Nothing to do.\n";
echo "\nDone. Reload your pages.\n";
