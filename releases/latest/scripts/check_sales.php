<?php
// Quick diagnostic: shows latest sales and today's sales
// Open: http://localhost:8888/check_sales.php

header('Content-Type: text/plain');

// Parse CI database config
$cfg_file = file_get_contents('application/config/database.php');
$cfg = [];
foreach (['hostname','username','password','database'] as $k) {
    if (preg_match("/'$k'\s*=>\s*'([^']+)'/", $cfg_file, $m)) {
        $cfg[$k] = $m[1];
    }
}
if (empty($cfg['database'])) {
    die("Could not parse database config.\n");
}

$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error . "\n");
}

$today = date('Y-m-d');
echo "=== Check Sales Diagnostic ===\n";
echo "Today: $today\n\n";

// Latest 5 sales
echo "--- Latest 5 Sales ---\n";
$q = $mysqli->query("SELECT id, sales_code, sales_date, sales_status, store_id, grand_total, created_date, created_time FROM db_sales ORDER BY id DESC LIMIT 5");
if ($q->num_rows == 0) {
    echo "No sales found at all.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo "#{$r['id']} code={$r['sales_code']} date={$r['sales_date']} status={$r['sales_status']} store={$r['store_id']} total={$r['grand_total']} created={$r['created_date']} {$r['created_time']}\n";
    }
}

// Today's sales
echo "\n--- Today's Sales (status=Final) ---\n";
$q = $mysqli->query("SELECT id, sales_code, sales_date, sales_status, store_id, grand_total FROM db_sales WHERE sales_date='$today' AND sales_status='Final'");
if ($q->num_rows == 0) {
    echo "None.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo "#{$r['id']} code={$r['sales_code']} store={$r['store_id']} total={$r['grand_total']}\n";
    }
}

// All sales for today regardless of status
echo "\n--- Today's Sales (any status) ---\n";
$q = $mysqli->query("SELECT id, sales_code, sales_date, sales_status, store_id, grand_total FROM db_sales WHERE sales_date='$today'");
if ($q->num_rows == 0) {
    echo "None.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo "#{$r['id']} code={$r['sales_code']} status={$r['sales_status']} store={$r['store_id']} total={$r['grand_total']}\n";
    }
}

// Sales with 1970 date
echo "\n--- Sales with 1970 date ---\n";
$q = $mysqli->query("SELECT id, sales_code, sales_date, sales_status, store_id, grand_total FROM db_sales WHERE sales_date='1970-01-01'");
if ($q->num_rows == 0) {
    echo "None.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo "#{$r['id']} code={$r['sales_code']} status={$r['sales_status']} store={$r['store_id']} total={$r['grand_total']}\n";
    }
}

// Sales items breakdown for latest sale
echo "\n--- Items in Latest Sale ---\n";
$q = $mysqli->query("SELECT id FROM db_sales ORDER BY id DESC LIMIT 1");
if ($q && $q->num_rows > 0) {
    $latest_id = $q->fetch_assoc()['id'];
    $q2 = $mysqli->query("SELECT item_id, sales_qty, price_per_unit, purchase_price, commission_amount, staff_id FROM db_salesitems WHERE sales_id=$latest_id");
    while ($r = $q2->fetch_assoc()) {
        echo "  item={$r['item_id']} qty={$r['sales_qty']} price={$r['price_per_unit']} cost={$r['purchase_price']} commission={$r['commission_amount']} staff={$r['staff_id']}\n";
    }
}

echo "\n=== Done ===\n";
