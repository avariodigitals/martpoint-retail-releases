<?php
/**
 * Apply production-readiness audit fixes to current database.
 * Run via browser or CLI.
 */
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASEPATH', __DIR__ . '/system/');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
require_once __DIR__ . '/application/config/database.php';

$cfg = $db['default'];
$conn = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($conn->connect_error) {
    die('Connect failed: ' . $conn->connect_error);
}

$conn->query("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$errors = [];
$ok = [];

function run($conn, $sql, &$ok, &$errors) {
    if ($conn->query($sql)) {
        $ok[] = $sql;
    } else {
        $errors[] = $conn->error . " [" . substr($sql, 0, 120) . "]";
    }
}

// Fix missing primary keys
run($conn, "ALTER TABLE `ci_sessions` ADD PRIMARY KEY (`id`)", $ok, $errors);
run($conn, "ALTER TABLE `db_company` MODIFY `id` int(10) NOT NULL, ADD PRIMARY KEY (`id`)", $ok, $errors);
run($conn, "ALTER TABLE `db_shippingaddress` DROP KEY `id`, ADD PRIMARY KEY (`id`)", $ok, $errors);

// Clean legacy zero-date values in date columns
$zeroDateCols = [
    ['db_items', 'expire_date'],
    ['db_items', 'mfg_date'],
    ['db_purchaseitems', 'expire_date'],
    ['db_purchaseitems', 'mfg_date'],
    ['db_service_packages', 'expiry_date'],
    ['db_customer_packages', 'expiry_date'],
    ['db_item_barcodes', 'expire_date'],
    ['db_item_barcodes', 'mfg_date'],
];
foreach ($zeroDateCols as $tc) {
    run($conn, "UPDATE `{$tc[0]}` SET `{$tc[1]}` = NULL WHERE `{$tc[1]}` = '0000-00-00' OR `{$tc[1]}` = '0000-00-00 00:00:00'", $ok, $errors);
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

header('Content-Type: text/plain');
echo "Applied fixes to: " . $cfg['database'] . "\n";
echo "Successful: " . count($ok) . "\n";
echo "Errors: " . count($errors) . "\n";
if ($errors) {
    echo "\nERRORS:\n";
    foreach ($errors as $e) echo "- $e\n";
}
if ($ok) {
    echo "\nAPPLIED:\n";
    foreach ($ok as $s) echo "- " . substr($s, 0, 100) . "\n";
}
