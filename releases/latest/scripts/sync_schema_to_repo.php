<?php
/**
 * Sync current database schema to the repo install schema (setup/install/includes/db.txt).
 * Creates missing tables and adds missing columns/indexes safely.
 * Usage: php sync_schema_to_repo.php
 * Back up first.
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
mysqli_report(MYSQLI_REPORT_OFF);

$schemaFile = __DIR__ . '/setup/install/includes/db.txt';
if (!file_exists($schemaFile)) die("Schema file not found: $schemaFile");
$schema = file_get_contents($schemaFile);

// Remove SQL comments
$schema = preg_replace('/--[^\n]*\n/', "\n", $schema);
$schema = preg_replace('/\/\*.*?\*\//s', "\n", $schema);

// Split into statements by semicolons at end of lines (db.txt style)
$statements = array_filter(array_map('trim', preg_split('/;\s*(?=\n)/s', $schema)));

$out = [];

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;

    // CREATE TABLE
    if (preg_match('/CREATE TABLE\s+`?(\w+)`?\s*\((.*)\)\s*ENGINE=(.+)/is', $stmt, $m)) {
        $table = $m[1];
        $body = $m[2];
        $engine = trim($m[3]);

        // Check if table exists
        $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
        if ($res->num_rows == 0) {
            $sql = "CREATE TABLE IF NOT EXISTS `$table` ($body) ENGINE=$engine;";
            if ($conn->query($sql)) {
                $out[] = "CREATED TABLE: $table";
            } else {
                $out[] = "ERROR creating $table: " . $conn->error;
            }
            continue;
        }

        // Table exists: compare columns and add missing ones
        // Extract column definitions: match lines like `col` type ...,
        preg_match_all('/^\s*`(\w+)`\s+([^,\n]+)/mi', $body, $colMatches, PREG_SET_ORDER);
        $targetCols = [];
        foreach ($colMatches as $cm) {
            $targetCols[strtolower($cm[1])] = trim($cm[2]);
        }

        $curRes = $conn->query("SHOW COLUMNS FROM `$table`");
        $currentCols = [];
        while ($row = $curRes->fetch_assoc()) {
            $currentCols[strtolower($row['Field'])] = $row;
        }

        foreach ($targetCols as $col => $def) {
            if (isset($currentCols[$col])) continue;
            // Skip constraint lines that look like columns
            if (in_array($col, ['primary', 'key', 'unique', 'constraint', 'index', 'foreign', 'check'])) continue;

            // Clean up definition: remove inline comments
            $def = preg_replace('/\s+COMMENT\s+\'.*?\'/i', '', $def);
            $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $def";
            if ($conn->query($sql)) {
                $out[] = "ADDED COLUMN: $table.$col";
            } else {
                $out[] = "ERROR adding $table.$col: " . $conn->error . " | SQL: $sql";
            }
        }
        continue;
    }

    // ALTER TABLE - indexes / primary keys / auto_increment
    if (preg_match('/ALTER TABLE\s+`?(\w+)`?\s+(.+)/is', $stmt, $m)) {
        $table = $m[1];
        $rest = $m[2];
        // Check table exists
        $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
        if ($res->num_rows == 0) {
            $out[] = "SKIP ALTER (table missing): $table";
            continue;
        }
        // Try to execute the ALTER safely; ignore duplicate key/foreign key errors
        if (!$conn->query($stmt)) {
            $err = $conn->error;
            if (!preg_match('/Duplicate key name|Multiple primary key defined|Can\'t DROP|Duplicate foreign key constraint/i', $err)) {
                $out[] = "ERROR ALTER $table: $err | SQL: " . substr($stmt, 0, 120);
            }
        }
        continue;
    }

    // INSERT seed data - skip to avoid overwriting existing data
    if (preg_match('/^INSERT\s+/is', $stmt)) {
        continue;
    }
}

// Update version
$conn->query("UPDATE db_sitesettings SET version = '4.0.2' WHERE id = 1");
$out[] = "Version marker updated to 4.0.2";

$conn->close();

header('Content-Type: text/plain');
echo "=== SCHEMA SYNC ===\n\n";
foreach ($out as $o) echo $o . "\n";
echo "\nDone.\n";
