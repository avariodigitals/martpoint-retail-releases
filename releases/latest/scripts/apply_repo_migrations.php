<?php
/**
 * Apply all MartPoint repo migration SQL files to the current database.
 * Tolerates already-existing tables/columns so it can be re-run safely.
 * Usage: php apply_repo_migrations.php
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASEPATH', __DIR__ . '/system/');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/application/config/database.php';
$db = $db['default'];

$conn = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$files = [
    'release_build/migrations/3.0_to_4.0.0.sql',
    'release_build/migrations/4.0.0_to_4.0.1_purchase_batch.sql',
    'martpoint_subscription_plans.sql',
    'martpoint_subscription_plans_and_overrides.sql',
    'martpoint_loyalty_rewards.sql',
    'martpoint_payment_modes.sql',
    'martpoint_approvals.sql',
    'martpoint_batch_lot_mrp_migration.sql',
    'martpoint_bnpl_and_offline_po.sql',
    'martpoint_paystack_integration.sql',
    'martpoint_storefront_premium.sql',
    'martpoint_auto_update_v1.sql',
    'add_hold_delete_columns.sql',
    'add_missing_approval_columns.sql',
    'fix_approval_pin.sql',
    'fix_approval_pin_simple.sql',
    'fix_mysql_migrations.sql',
    'fix_business_profile_columns.sql',
];

$out = [];

// Only apply missing_tables_fix.sql if at least one of its target tables is missing
$missingFixPath = __DIR__ . '/missing_tables_fix.sql';
if (file_exists($missingFixPath)) {
    $tablesNeedingFix = false;
    $content = file_get_contents($missingFixPath);
    preg_match_all('/CREATE TABLE `?(\w+)`?\s*\(/i', $content, $matches);
    foreach ($matches[1] as $table) {
        $res = $conn->query("SHOW TABLES LIKE '$table'");
        if ($res->num_rows == 0) { $tablesNeedingFix = true; break; }
    }
    if ($tablesNeedingFix) {
        $files[] = 'missing_tables_fix.sql';
    } else {
        $out[] = "SKIP: missing_tables_fix.sql (all tables already exist)";
    }
}

$conn->close();

$host = escapeshellarg($db['hostname']);
$user = escapeshellarg($db['username']);
$pass = escapeshellarg($db['password']);
$name = escapeshellarg($db['database']);

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        $out[] = "SKIP (not found): $file";
        continue;
    }
    $cmd = "mysql -h $host -u $user -p$pass $name --force --show-warnings < " . escapeshellarg($path) . " 2>&1";
    $result = shell_exec($cmd);
    if ($result === null) {
        $out[] = "$file: executed (no output)";
    } else {
        $lines = array_filter(array_map('trim', explode("\n", $result)));
        if (empty($lines)) {
            $out[] = "$file: OK";
        } else {
            // Filter out password warning and duplicate errors
            $filtered = [];
            foreach ($lines as $line) {
                if (stripos($line, 'Using a password') !== false) continue;
                if (preg_match('/Duplicate column name|Table already exists|Duplicate key name|Can\'t DROP|multiple primary key defined/i', $line)) {
                    $filtered[] = "(ignored) " . $line;
                    continue;
                }
                $filtered[] = $line;
            }
            $out[] = "$file: " . (empty($filtered) ? "OK" : implode(" | ", array_slice($filtered, 0, 10)));
        }
    }
}

// Reconnect and bump version marker
$conn2 = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
$conn2->query("UPDATE db_sitesettings SET version = '4.0.2' WHERE id = 1");
$conn2->close();
$out[] = "Version marker updated to 4.0.2";

header('Content-Type: text/plain');
echo "=== REPO MIGRATIONS ===\n\n";
foreach ($out as $o) echo $o . "\n";
echo "\nDone. Clear cache and reload app.\n";
