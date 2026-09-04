<?php
// Standalone diagnostic + fix script
// Open in browser: http://localhost:8888/fix_commission_and_profit.php
// This script runs outside CI and connects directly to MySQL

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

echo "=== MartPoint Diagnostic ===\n";
echo "Database: {$cfg['database']}\n\n";

// ─── 1. Commission Columns ───
echo "--- Staff Commission Columns ---\n";
function ensure_col($mysqli, $table, $col, $def) {
    $r = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    if ($r && $r->num_rows > 0) {
        return "EXISTS  $table.$col";
    }
    if ($mysqli->query("ALTER TABLE `$table` ADD COLUMN `$col` $def")) {
        return "ADDED   $table.$col";
    }
    return "ERROR   $table.$col: " . $mysqli->error;
}
echo ensure_col($mysqli, 'db_items',       'commission_type',     "VARCHAR(20) DEFAULT 'none'") . "\n";
echo ensure_col($mysqli, 'db_items',       'commission_value',    "DECIMAL(18,2) DEFAULT 0") . "\n";
echo ensure_col($mysqli, 'db_salesitems',  'staff_id',            "INT(11) NULL") . "\n";
echo ensure_col($mysqli, 'db_salesitems',  'commission_amount',   "DECIMAL(18,2) DEFAULT 0") . "\n";
echo ensure_col($mysqli, 'db_holditems',   'staff_id',            "INT(11) NULL") . "\n";
echo ensure_col($mysqli, 'db_holditems',   'commission_amount',   "DECIMAL(18,2) DEFAULT 0") . "\n";

// ─── 2. Today's Sales & Profit ───
$today = date('Y-m-d');
echo "\n--- Today ($today) ---\n";
$q = $mysqli->query("SELECT COALESCE(SUM(grand_total),0) as revenue FROM db_sales WHERE sales_date='$today' AND sales_status='Final'");
$revenue = $q->fetch_assoc()['revenue'];
echo "Revenue:  $revenue\n";

$q = $mysqli->query("SELECT COALESCE(SUM(b.purchase_price * b.sales_qty),0) as cost FROM db_sales a LEFT JOIN db_salesitems b ON a.id=b.sales_id WHERE a.sales_date='$today' AND a.sales_status='Final'");
$cost = $q->fetch_assoc()['cost'];
echo "Cost:     $cost\n";
echo "Profit:   " . ($revenue - $cost) . "\n";

// ─── 3. Sales Breakdown ───
echo "\n--- Today's Sales Breakdown ---\n";
$q = $mysqli->query("SELECT a.id, a.sales_code, a.grand_total, b.item_id, b.sales_qty, b.price_per_unit, b.purchase_price, (b.sales_qty * b.purchase_price) as line_cost FROM db_sales a LEFT JOIN db_salesitems b ON a.id=b.sales_id WHERE a.sales_date='$today' AND a.sales_status='Final'");
if ($q->num_rows == 0) {
    echo "No sales today.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        echo "Sale #{$r['id']} {$r['sales_code']}: total={$r['grand_total']}, qty={$r['sales_qty']}, unit_price={$r['price_per_unit']}, purchase_price={$r['purchase_price']}, line_cost={$r['line_cost']}\n";
    }
}

// ─── 4. Service Items ───
echo "\n--- Service Items ---\n";
$q = $mysqli->query("SELECT id, item_name, price, purchase_price, sales_price, commission_type, commission_value, service_bit FROM db_items WHERE service_bit=1");
if ($q->num_rows == 0) {
    echo "No service items found.\n";
} else {
    while ($r = $q->fetch_assoc()) {
        $profit = $r['sales_price'] - $r['price'];
        echo "Item #{$r['id']} '{$r['item_name']}': expense(price)={$r['price']}, purchase_price={$r['purchase_price']}, sales_price={$r['sales_price']}, per_unit_profit=$profit, commission={$r['commission_type']}/{$r['commission_value']}\n";
    }
}

// ─── 5. Commission Records ───
echo "\n--- Commission Records ---\n";
$q = $mysqli->query("SELECT COUNT(*) as cnt FROM db_salesitems WHERE staff_id IS NOT NULL AND commission_amount > 0");
$cnt = $q->fetch_assoc()['cnt'];
echo "Total commission records: $cnt\n";
if ($cnt > 0) {
    $q = $mysqli->query("SELECT si.id, si.staff_id, si.commission_amount, s.sales_code, s.sales_date, i.item_name FROM db_salesitems si LEFT JOIN db_sales s ON s.id=si.sales_id LEFT JOIN db_items i ON i.id=si.item_id WHERE si.staff_id IS NOT NULL AND si.commission_amount > 0 ORDER BY s.sales_date DESC LIMIT 5");
    while ($r = $q->fetch_assoc()) {
        echo "  #{$r['id']} sale={$r['sales_code']} date={$r['sales_date']} item={$r['item_name']} staff={$r['staff_id']} commission={$r['commission_amount']}\n";
    }
}

echo "\n=== Done ===\n";
echo "You can delete this file now.\n";
