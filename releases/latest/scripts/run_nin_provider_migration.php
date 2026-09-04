<?php
// Run this in your browser: http://localhost/martpoint%20retail/run_nin_provider_migration.php
// It will add NIN/BVN provider columns to db_store if they don't exist

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

$cols = [
    'nin_api_enabled'    => 'TINYINT(1) NOT NULL DEFAULT 0',
    'nin_api_url'        => 'VARCHAR(500) DEFAULT NULL',
    'nin_api_key'        => 'VARCHAR(500) DEFAULT NULL',
    'nin_api_provider'   => 'VARCHAR(50) DEFAULT "ninbvnportal"',
    'nin_api_cost'       => 'DECIMAL(10,2) DEFAULT 50.00',
    'nin_provider'       => 'VARCHAR(50) DEFAULT "ninbvnportal"',
    'bvn_provider'       => 'VARCHAR(50) DEFAULT "ninbvnportal"',
    'interswitch_client_id'     => 'VARCHAR(255) DEFAULT NULL',
    'interswitch_client_secret' => 'VARCHAR(500) DEFAULT NULL',
];

foreach ($cols as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM db_store LIKE '$col'");
    if ($res->num_rows == 0) {
        if ($conn->query("ALTER TABLE db_store ADD COLUMN $col $def")) {
            $created[] = "db_store.$col";
        } else {
            $errors[] = "db_store.$col: " . $conn->error;
        }
    }
}

$conn->close();

header('Content-Type: text/plain');
echo "=== NIN/BVN PROVIDER MIGRATION ===\n\n";

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
