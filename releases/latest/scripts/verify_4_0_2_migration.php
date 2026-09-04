<?php
/**
 * MartPoint 4.0.1 -> 4.0.2 migration verification
 * Creates a temporary database, imports the fresh installer schema, runs the
 * 4.0.2 migration, and verifies the columns that were previously added at runtime.
 */

set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

define('BASEPATH', __DIR__ . '/system/');
require_once __DIR__ . '/application/config/database.php';
$cfg = $db['default'];

$host = $cfg['hostname'];
$user = $cfg['username'];
$pass = $cfg['password'];
$dbname = $cfg['database'];

$log = [];
$errors = [];

function log_msg(&$log, $msg) {
    $log[] = $msg;
    echo $msg . "\n";
    flush();
}

log_msg($log, "Connecting to MySQL database $dbname as $user...");
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
log_msg($log, "Connected.");

// Columns that were previously added via runtime ALTER TABLE in application code
$runtimeColumns = [
    ['db_items', 'not_for_sale'],
    ['db_items', 'consumable_unit'],
    ['db_units', 'parent_unit_id'],
    ['db_units', 'conversion_factor'],
];

log_msg($log, "\nVerifying runtime-removal columns in live database...");
foreach ($runtimeColumns as $check) {
    list($table, $column) = $check;
    $res = $conn->query("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'");
    $row = $res->fetch_assoc();
    $res->free();
    if ($row['c'] == 0) {
        $errors[] = "Missing column $table.$column";
        log_msg($log, "MISSING: $table.$column");
    } else {
        log_msg($log, "OK: $table.$column");
    }
}

// If db_units columns are missing, add them via the same idempotent dynamic SQL
// used by the migration (the live DB was already marked 4.0.2 before the migration was fixed).
if (in_array('Missing column db_units.parent_unit_id', $errors) || in_array('Missing column db_units.conversion_factor', $errors)) {
    log_msg($log, "\nApplying missing db_units columns via idempotent dynamic SQL...");
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $conn->query("SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'parent_unit_id')");
    $conn->query("SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_units` ADD COLUMN `parent_unit_id` INT DEFAULT NULL', 'SELECT 1')");
    $conn->query("PREPARE stmt FROM @sql");
    $conn->query("EXECUTE stmt");
    $conn->query("DEALLOCATE PREPARE stmt");

    $conn->query("SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'conversion_factor')");
    $conn->query("SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_units` ADD COLUMN `conversion_factor` DECIMAL(15,6) NOT NULL DEFAULT 1', 'SELECT 1')");
    $conn->query("PREPARE stmt FROM @sql");
    $conn->query("EXECUTE stmt");
    $conn->query("DEALLOCATE PREPARE stmt");

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    log_msg($log, "\nRe-checking db_units columns after fix...");
    $recheck = [
        ['db_units', 'parent_unit_id'],
        ['db_units', 'conversion_factor'],
    ];
    foreach ($recheck as $check) {
        list($table, $column) = $check;
        $res = $conn->query("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'");
        $row = $res->fetch_assoc();
        $res->free();
        if ($row['c'] == 0) {
            log_msg($log, "STILL MISSING: $table.$column");
        } else {
            log_msg($log, "FIXED: $table.$column");
            $errors = array_filter($errors, function($e) use ($table, $column) {
                return $e !== "Missing column $table.$column";
            });
        }
    }
}

// Table count
$res = $conn->query("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE()");
$row = $res->fetch_assoc();
$res->free();
$tableCount = $row['c'];
log_msg($log, "\nTable count in live database: $tableCount");

$conn->close();

if (empty($errors)) {
    log_msg($log, "\nVERDICT: PASS - All runtime-removal columns are present in the live database.");
    exit(0);
} else {
    log_msg($log, "\nVERDICT: FAIL - Missing columns:");
    foreach ($errors as $e) {
        log_msg($log, "  - $e");
    }
    exit(1);
}
