<?php
// Run this in your browser: http://localhost/martpoint%20retail/run_service_deposit_migration.php
// It will add deposit columns to db_items and db_services if they don't exist

$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$errors = [];
$created = [];

// 1. Add deposit columns to db_items
$item_cols = [
    'deposit_required' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'deposit_percent'  => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
];

foreach ($item_cols as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM db_items LIKE '$col'");
    if ($res->num_rows == 0) {
        if ($conn->query("ALTER TABLE db_items ADD COLUMN $col $def")) {
            $created[] = "db_items.$col";
        } else {
            $errors[] = "db_items.$col: " . $conn->error;
        }
    }
}

// 2. Add deposit columns to db_services
$service_cols = [
    'deposit_required' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'deposit_percent'  => 'DECIMAL(10,2) NOT NULL DEFAULT 0',
];

foreach ($service_cols as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM db_services LIKE '$col'");
    if ($res->num_rows == 0) {
        if ($conn->query("ALTER TABLE db_services ADD COLUMN $col $def")) {
            $created[] = "db_services.$col";
        } else {
            $errors[] = "db_services.$col: " . $conn->error;
        }
    }
}

$conn->close();

header('Content-Type: text/plain');
echo "=== MARTPOINT SERVICE DEPOSIT MIGRATION ===\n\n";

if (!empty($created)) {
    echo "CREATED:\n";
    foreach ($created as $c) echo "  + $c\n";
}

if (!empty($errors)) {
    echo "\nERRORS:\n";
    foreach ($errors as $e) echo "  ! $e\n";
}

if (empty($created) && empty($errors)) {
    echo "Nothing to do. All columns already exist.\n";
}

echo "\nDone.\n";
