<?php
/**
 * Sync current MartPoint database schema to the repo's install schema (db.txt).
 * Run via: php sync_db_to_repo.php
 * Back up your database first.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/application/config/database.php';

$db = $db['default'];
$host = $db['hostname'];
$user = $db['username'];
$pass = $db['password'];
$dbname = $db['database'];

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$schemaFile = __DIR__ . '/setup/install/includes/db.txt';
if (!file_exists($schemaFile)) die("Schema file not found: $schemaFile");

$schema = file_get_contents($schemaFile);

// Normalize: remove comments, collapse whitespace
$schema = preg_replace('/--[^\n]*\n/', "\n", $schema);
$schema = preg_replace('/\/\*.*?\*\//s', "", $schema);

// Extract CREATE TABLE statements
preg_match_all('/CREATE TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?\s*\((.*?)\)\s*ENGINE=([^;]+);/is', $schema, $matches, PREG_SET_ORDER);

$out = [];
$missingTables = [];
$alteredTables = [];

foreach ($matches as $m) {
    $table = $m[1];
    $body = $m[3];
    $engine = trim($m[3]);

    // Check table existence
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res->num_rows == 0) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table` ($body) ENGINE=$engine;";
        if ($conn->query($sql)) {
            $out[] = "CREATED TABLE: $table";
            $missingTables[] = $table;
        } else {
            $out[] = "ERROR creating $table: " . $conn->error;
        }
        continue;
    }

    // Extract column definitions from schema
    preg_match_all('/^\s*`?(\w+)`?\s+(\w+[^,\n]*)/mi', $body, $colMatches, PREG_SET_ORDER);
    $schemaCols = [];
    foreach ($colMatches as $cm) {
        $schemaCols[strtolower($cm[1])] = trim($cm[2]);
    }

    // Get current columns
    $curRes = $conn->query("SHOW COLUMNS FROM `$table`");
    $currentCols = [];
    while ($row = $curRes->fetch_assoc()) {
        $currentCols[strtolower($row['Field'])] = $row;
    }

    // Add missing columns
    foreach ($schemaCols as $col => $def) {
        if (isset($currentCols[$col])) continue;
        // Skip constraints/keys/indexes that look like column names
        if (in_array(strtolower($col), ['primary', 'key', 'unique', 'constraint', 'index', 'foreign'])) continue;

        $def = preg_replace('/AUTO_INCREMENT/i', 'AUTO_INCREMENT', $def);
        $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $def";
        if ($conn->query($sql)) {
            $out[] = "ADDED COLUMN: $table.$col";
            $alteredTables[$table][] = $col;
        } else {
            $out[] = "ERROR adding $table.$col: " . $conn->error;
        }
    }
}

// Update version in db_sitesettings
$conn->query("UPDATE db_sitesettings SET version = '4.0.2' WHERE id = 1");
$out[] = "Updated db_sitesettings.version to 4.0.2";

$conn->close();

header('Content-Type: text/plain');
echo "=== DB SCHEMA SYNC ===\n\n";
foreach ($out as $o) echo $o . "\n";
echo "\nDone. Reload your app.\n";
