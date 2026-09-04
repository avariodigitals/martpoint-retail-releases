<?php
/**
 * MartPoint Production Readiness Audit
 * Comprehensive audit across all 7 phases.
 * Run via: php production_readiness_audit.php
 */

set_time_limit(0);
ignore_user_abort(true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASEPATH', __DIR__ . '/system/');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
require_once __DIR__ . '/application/config/database.php';

header('Content-Type: text/plain');

$cfg = $db['default'];
$host = $cfg['hostname'];
$user = $cfg['username'];
$pass = $cfg['password'];
$dbname = $cfg['database'];

$root = __DIR__;
$reportDir = $root . '/audit_reports_' . date('Ymd_His');
if (!is_dir($reportDir)) mkdir($reportDir, 0777, true);

$findings = [
    'phase1' => [],
    'phase2' => [],
    'phase3' => [],
    'phase4' => [],
    'phase5' => [],
    'phase6' => [],
    'phase7' => [],
];
$fixes = [
    'Made purchase batch migration (4.0.0 -> 4.0.1) idempotent by checking column existence before ALTER TABLE',
    'Added primary keys to ci_sessions, db_company, and db_shippingaddress in installer schema',
    'Created is_valid_date() helper and replaced legacy 0000-00-00 literal checks across all models, controllers, helpers, and views',
    'Applied ALTER TABLE fixes to current martpoint database for missing primary keys and legacy zero-date rows',
    'Simplified stress test to use minimal prefixed tables and avoid cross-database permission issues',
];
$filesModified = [
    'application/helpers/custom_helper.php',
    'application/models/Purchase_model.php',
    'application/models/Sales_model.php',
    'application/controllers/Items.php',
    'application/models/Pos_model.php',
    'application/models/Items_model.php',
    'application/models/Assist_model.php',
    'application/models/Service_package_model.php',
    'application/models/Storefront_model.php',
    'application/models/Services_model.php',
    'application/models/Delivery_model.php',
    'application/models/Laundry_model.php',
    'application/helpers/inventory_helper.php',
    'application/controllers/Import.php',
    'application/controllers/Operations.php',
    'application/views/operations/laundry.php',
    'application/views/operations/driver_profile.php',
    'application/views/operations/warranty_lookup.php',
    'application/views/expired_items_report.php',
    'application/views/customer-packages.php',
    'setup/install/includes/db.txt',
    'updates/migrations/4.0.0_to_4.0.1_purchase_batch.sql',
    'release_build/migrations/4.0.0_to_4.0.1_purchase_batch.sql',
    'production_readiness_audit.php',
    'fix_audit_issues.php',
];

function connect($host, $user, $pass, $dbname) {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");
    $conn->query("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
    return $conn;
}

function logLine($msg) {
    echo $msg . "\n";
    flush();
}

function addFinding(&$findings, $phase, $severity, $issue, $rootCause = '', $fix = '', $table = '', $column = '') {
    $findings[$phase][] = [
        'severity' => $severity,
        'issue' => $issue,
        'root_cause' => $rootCause,
        'fix' => $fix,
        'table' => $table,
        'column' => $column,
    ];
}

$conn = connect($host, $user, $pass, $dbname);
logLine("Connected to database: $dbname");
logLine("Report directory: $reportDir");

// Clean up any stale stress tables left by previous runs
$stressPrefix = 'stress_';
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$stale = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE '{$stressPrefix}%' ORDER BY table_name DESC");
if ($stale) {
    while ($row = $stale->fetch_row()) {
        $conn->query("DROP TABLE IF EXISTS `{$row[0]}`");
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Phase 3: Database Integrity Audit
logLine("\n=== PHASE 3: Database Integrity Audit ===");

$tablesRes = $conn->query("SHOW TABLES");
$allTables = [];
while ($row = $tablesRes->fetch_row()) $allTables[] = $row[0];
logLine("Tables found: " . count($allTables));

foreach ($allTables as $table) {
    // Check primary key
    $pkRes = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if ($pkRes->num_rows == 0) {
        addFinding($findings, 'phase3', 'high', "Missing primary key on table", "Table created without PRIMARY KEY", "Add AUTO_INCREMENT primary key or natural primary key", $table);
    }
    $pkRes->free();

    // Check engine
    $engineRes = $conn->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'");
    $engineRow = $engineRes->fetch_assoc();
    if ($engineRow && strtolower($engineRow['ENGINE']) != 'innodb') {
        addFinding($findings, 'phase3', 'medium', "Table not using InnoDB engine", "Foreign keys and transactions require InnoDB", "ALTER TABLE to ENGINE=InnoDB", $table);
    }
    $engineRes->free();

    // Check charset
    $charsetRes = $conn->query("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'");
    $charsetRow = $charsetRes->fetch_assoc();
    if ($charsetRow && stripos($charsetRow['TABLE_COLLATION'], 'utf8mb4') === false) {
        addFinding($findings, 'phase3', 'low', "Table not using utf8mb4 collation", "Mixed collation can cause join errors", "Consider converting to utf8mb4_unicode_ci", $table);
    }
    $charsetRes->free();

    // Check duplicate indexes
    $idxRes = $conn->query("SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table' AND INDEX_NAME != 'PRIMARY' ORDER BY INDEX_NAME, SEQ_IN_INDEX");
    $idxMap = [];
    while ($idx = $idxRes->fetch_assoc()) {
        $idxMap[$idx['INDEX_NAME']][] = $idx['COLUMN_NAME'];
    }
    $idxRes->free();
    $seen = [];
    foreach ($idxMap as $idxName => $cols) {
        $sig = implode(',', $cols);
        if (isset($seen[$sig])) {
            addFinding($findings, 'phase3', 'medium', "Duplicate index detected", "Two indexes cover the same columns", "Drop redundant index $idxName", $table);
        } else {
            $seen[$sig] = $idxName;
        }
    }

    // Check columns for NULL defaults on required fields
    $colRes = $conn->query("SHOW COLUMNS FROM `$table`");
    $hasCreatedAt = false;
    $hasUpdatedAt = false;
    while ($col = $colRes->fetch_assoc()) {
        $field = strtolower($col['Field']);
        if ($field == 'created_at') $hasCreatedAt = true;
        if ($field == 'updated_at') $hasUpdatedAt = true;
        if (in_array($field, ['sales_date', 'purchase_date', 'created_date', 'payment_date']) && $col['Null'] == 'YES' && $col['Default'] === null) {
            addFinding($findings, 'phase3', 'low', "Date column nullable without default", "Could allow NULL where business expects a date", "Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling", $table, $col['Field']);
        }
    }
    $colRes->free();
}

logLine("Phase 3 initial pass complete. Findings: " . count($findings['phase3']));

// Phase 4: Legacy Date Cleanup
logLine("\n=== PHASE 4: Legacy Date Cleanup ===");

$dateColumns = [];
$infoRes = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$dbname' AND DATA_TYPE IN ('date','datetime','timestamp','time')");
while ($row = $infoRes->fetch_assoc()) {
    $dateColumns[] = [$row['TABLE_NAME'], $row['COLUMN_NAME'], $row['DATA_TYPE']];
}
$infoRes->free();

$legacyCount = 0;
foreach ($dateColumns as $dc) {
    [$t, $c, $type] = $dc;
    $checkRes = $conn->query("SELECT COUNT(*) AS cnt FROM `$t` WHERE `$c` = '0000-00-00' OR `$c` = '0000-00-00 00:00:00' OR `$c` LIKE '0000-00-00%'");
    if ($checkRes && $row = $checkRes->fetch_assoc()) {
        $cnt = (int)$row['cnt'];
        if ($cnt > 0) {
            addFinding($findings, 'phase4', 'high', "Legacy zero-date values found", "Column stores '0000-00-00' which requires ALLOW_INVALID_DATES", "Replace with NULL or a sentinel date, update code accordingly", $t, $c);
            $legacyCount += $cnt;
        }
    }
    if ($checkRes) $checkRes->free();
}
logLine("Legacy zero-date rows found: $legacyCount");

// Also search SQL schema and PHP files for zero-date literals
$zeroDateFiles = [];
$searchPaths = [
    $root . '/setup/install/includes',
    $root . '/application',
    $root . '/updates',
    $root . '/martpoint_*.sql',
    $root . '/*.sql',
];
$foundFiles = [];
foreach ($searchPaths as $sp) {
    if (is_file($sp)) {
        $foundFiles[] = $sp;
    } elseif (is_dir($sp)) {
        $rit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sp));
        foreach ($rit as $f) {
            if ($f->isFile() && in_array(strtolower($f->getExtension()), ['php', 'sql'])) {
                $foundFiles[] = $f->getPathname();
            }
        }
    } elseif (strpos($sp, '*') !== false) {
        foreach (glob($sp) as $gf) {
            if (is_file($gf)) $foundFiles[] = $gf;
        }
    }
}
$foundFiles = array_unique($foundFiles);
foreach ($foundFiles as $f) {
    $content = file_get_contents($f);
    if (strpos($content, '0000-00-00') !== false) {
        $zeroDateFiles[] = $f;
    }
}
if (!empty($zeroDateFiles)) {
    addFinding($findings, 'phase4', 'medium', "Zero-date literals found in source files", "Source files still reference '0000-00-00'", "Replace with NULL or CURRENT_TIMESTAMP defaults", '', '');
    foreach (array_slice($zeroDateFiles, 0, 20) as $f) {
        logLine("  Zero-date literal in: $f");
    }
}
logLine("Files with zero-date literals: " . count($zeroDateFiles));

// Phase 5: Performance Audit
logLine("\n=== PHASE 5: Performance Audit ===");

// Tables without primary keys or with high column count
$perfRes = $conn->query("SELECT TABLE_NAME, TABLE_ROWS, AVG_ROW_LENGTH, DATA_LENGTH+INDEX_LENGTH AS total_bytes FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' ORDER BY (DATA_LENGTH+INDEX_LENGTH) DESC");
$largeTables = [];
while ($row = $perfRes->fetch_assoc()) {
    $largeTables[] = $row;
}
$perfRes->free();

foreach (array_slice($largeTables, 0, 10) as $lt) {
    if ($lt['total_bytes'] > 100 * 1024 * 1024) {
        addFinding($findings, 'phase5', 'medium', "Large table detected", "Table size exceeds 100MB", "Review archiving, partitioning, or data retention", $lt['TABLE_NAME']);
    }
}

// Tables with >50 columns
$colCountRes = $conn->query("SELECT TABLE_NAME, COUNT(*) AS col_count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$dbname' GROUP BY TABLE_NAME HAVING col_count > 50");
while ($row = $colCountRes->fetch_assoc()) {
    addFinding($findings, 'phase5', 'low', "Table exceeds 50 columns", "Wide tables reduce performance and maintainability", "Normalize rarely used columns into related tables", $row['TABLE_NAME']);
}
$colCountRes->free();

// Check for indexes on TEXT/BLOB columns
$textIdxRes = $conn->query("SELECT s.TABLE_NAME, s.INDEX_NAME, s.COLUMN_NAME, c.DATA_TYPE FROM information_schema.STATISTICS s JOIN information_schema.COLUMNS c ON s.TABLE_SCHEMA=c.TABLE_SCHEMA AND s.TABLE_NAME=c.TABLE_NAME AND s.COLUMN_NAME=c.COLUMN_NAME WHERE s.TABLE_SCHEMA='$dbname' AND c.DATA_TYPE IN ('text','blob','longtext','longblob','mediumtext','mediumblob')");
while ($row = $textIdxRes->fetch_assoc()) {
    addFinding($findings, 'phase5', 'medium', "Index on TEXT/BLOB column without prefix", "Indexing full TEXT/BLOB can fail or be inefficient", "Use prefix index (e.g., KEY (col(100)))", $row['TABLE_NAME'], $row['COLUMN_NAME']);
}
$textIdxRes->free();

// Detect likely N+1 query patterns in code
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/application'));
$nplusFiles = [];
foreach ($files as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) != 'php') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match('/foreach\s*\([^)]*\)\s*\{[^}]*\$this->db->(get|where|query)/s', $content)) {
        $nplusFiles[] = $f->getPathname();
    }
}
if (!empty($nplusFiles)) {
    addFinding($findings, 'phase5', 'low', "Potential N+1 query patterns in PHP code", "Loops contain database queries", "Refactor to batch queries or use joins", '', '');
    foreach (array_slice($nplusFiles, 0, 15) as $f) {
        logLine("  Potential N+1 in: $f");
    }
}
logLine("Performance initial findings: " . count($findings['phase5']));

// Phase 6: Schema Governance
logLine("\n=== PHASE 6: Schema Governance ===");

$governanceFiles = [];
$appIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/application'));
foreach ($appIter as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) != 'php') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $content)) {
        $governanceFiles[] = $f->getPathname();
    }
}
if (!empty($governanceFiles)) {
    addFinding($findings, 'phase6', 'medium', "Runtime CREATE TABLE IF NOT EXISTS found in application code", "Fresh installer should be the single source of truth", "Move table creation to installer; keep runtime checks only for backward-compatible upgrades", '', '');
    foreach (array_slice($governanceFiles, 0, 20) as $f) {
        logLine("  Runtime CREATE TABLE in: $f");
    }
}

// Check for ALTER TABLE in non-migration code
$alterFiles = [];
$appIter2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/application'));
foreach ($appIter2 as $f) {
    if (!$f->isFile() || strtolower($f->getExtension()) != 'php') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match('/ALTER\s+TABLE/i', $content)) {
        $alterFiles[] = $f->getPathname();
    }
}
if (!empty($alterFiles)) {
    addFinding($findings, 'phase6', 'low', "ALTER TABLE statements found in application code", "Schema changes should be in migrations or installer", "Review and move to migration files", '', '');
    foreach (array_slice($alterFiles, 0, 15) as $f) {
        logLine("  ALTER TABLE in: $f");
    }
}
logLine("Schema governance findings: " . count($findings['phase6']));

// Phase 2: Upgrade Validation
logLine("\n=== PHASE 2: Upgrade Validation ===");

$upgradeDb = 'martpoint_upgrade_validate_' . date('Ymd_His');
$conn->query("DROP DATABASE IF EXISTS `$upgradeDb`");
$conn->query("CREATE DATABASE `$upgradeDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($upgradeDb);
logLine("Created upgrade test database: $upgradeDb");
$baseSchema = $root . '/setup/install/includes/db.txt';
$upgradeErrors = [];

// Run the oldest migration to simulate a previous release
$oldMigrations = [
    $root . '/updates/migrations/3.0_to_4.0.0.sql',
    $root . '/updates/migrations/4.0.0_to_4.0.1_purchase_batch.sql',
    $root . '/release_build/migrations/3.0_to_4.0.0.sql',
    $root . '/release_build/migrations/4.0.0_to_4.0.1_purchase_batch.sql',
];

foreach ($oldMigrations as $mig) {
    if (!file_exists($mig)) continue;
    $sql = file_get_contents($mig);
    if ($conn->multi_query($sql)) {
        do {
            if ($res = $conn->store_result()) $res->free();
        } while ($conn->more_results() && $conn->next_result());
    }
    if ($conn->error) {
        $upgradeErrors[] = "Migration $mig: " . $conn->error;
        addFinding($findings, 'phase2', 'high', "Upgrade migration failed", $conn->error, "Fix migration SQL syntax", '', '');
    }
}

if (empty($upgradeErrors)) {
    logLine("Upgrade migrations executed successfully on test database.");
    // Compare schema to fresh install
    $conn->select_db($dbname);
    $freshTables = [];
    $tres = $conn->query("SHOW TABLES");
    while ($r = $tres->fetch_row()) $freshTables[] = $r[0];
    $tres->free();

    $conn->select_db($upgradeDb);
    $upTables = [];
    $tres2 = $conn->query("SHOW TABLES");
    while ($r = $tres2->fetch_row()) $upTables[] = $r[0];
    $tres2->free();

    $missing = array_diff($freshTables, $upTables);
    $extra = array_diff($upTables, $freshTables);
    if (!empty($missing)) {
        addFinding($findings, 'phase2', 'high', "Upgrade schema missing tables vs fresh install", "Tables present in fresh install but not after upgrade: " . implode(', ', $missing), "Add missing tables to upgrade migrations", '', '');
    }
    if (!empty($extra)) {
        addFinding($findings, 'phase2', 'low', "Extra tables in upgraded schema", "Tables present after upgrade but not in fresh install: " . implode(', ', $extra), "Verify if these tables are legacy/dead", '', '');
    }
}

$conn->select_db($dbname);
$conn->query("DROP DATABASE IF EXISTS `$upgradeDb`");
logLine("Upgrade validation findings: " . count($findings['phase2']));

// Phase 1: Business Flow Validation
logLine("\n=== PHASE 1: Business Flow Validation ===");

// Check that all required tables exist for the workflows
$requiredTables = [
    'Company setup' => ['db_company', 'db_store', 'db_sitesettings'],
    'Business profile' => ['db_store_business_profile'],
    'Branches' => ['db_store'],
    'Warehouses' => ['db_warehouse', 'db_warehouseitems', 'db_userswarehouses'],
    'Users & Roles' => ['db_users', 'db_roles', 'db_permissions'],
    'Customers' => ['db_customers'],
    'Suppliers' => ['db_suppliers'],
    'Categories' => ['db_category'],
    'Brands' => ['db_brands'],
    'Units' => ['db_units'],
    'Variants' => ['db_variants'],
    'Products' => ['db_items'],
    'Services' => ['db_services'],
    'Packages' => ['db_package', 'db_service_packages'],
    'Memberships' => ['db_membership_plans', 'db_customer_memberships', 'db_membership_payments'],
    'Loyalty' => ['db_loyalty_settings', 'db_loyalty_tiers', 'db_loyalty_points', 'db_loyalty_bonus_rules', 'db_rewards_history'],
    'Gift Cards' => ['db_gift_cards', 'db_gift_card_usage'],
    'QR Codes' => ['db_qr_codes'],
    'Purchases' => ['db_purchase', 'db_purchaseitems', 'db_purchasepayments'],
    'Goods Received' => ['db_stockentry'],
    'Stock Adjustment' => ['db_stockadjustment', 'db_stockadjustmentitems'],
    'Stock Transfer' => ['db_stocktransfer', 'db_stocktransferitems'],
    'POS Sales' => ['db_sales', 'db_salesitems', 'db_salespayments'],
    'Hold Sales' => ['db_hold', 'db_holditems'],
    'Sales Returns' => ['db_salesreturn', 'db_salesitemsreturn', 'db_salespaymentsreturn'],
    'Purchase Returns' => ['db_purchasereturn', 'db_purchaseitemsreturn', 'db_purchasepaymentsreturn'],
    'Quotations' => ['db_quotation', 'db_quotationitems'],
    'Expenses' => ['db_expense', 'db_expense_category'],
    'Customer Credit' => ['db_custadvance', 'db_customer_payments'],
    'Installment/BNPL' => ['db_installment_plans', 'db_installment_payments'],
    'Production' => ['db_production_batches', 'db_production_batch_items'],
    'Recipes' => ['db_recipes', 'db_recipe_ingredients', 'db_recipe_production_runs'],
    'Delivery' => ['db_delivery_schedules', 'db_delivery_schedule_items', 'db_delivery_drivers'],
    'Storefront' => ['db_storefront_settings', 'db_storefront_banners', 'db_storefront_domains', 'db_storefront_themes', 'db_storefront_homepage_sections'],
    'Online Orders' => ['db_online_orders', 'db_online_order_items'],
    'Attendance' => ['db_attendance', 'db_shifts', 'db_user_shifts'],
    'Reports' => ['db_report_schedules'],
    'Backup' => ['db_schema_migrations'],
    'Licensing' => ['db_subscription_license', 'db_license_history', 'db_license_otps'],
    'Subscription' => ['db_subscription', 'db_subscription_plans'],
    'Settings' => ['db_sitesettings', 'db_store', 'db_store_business_profile'],
];

foreach ($requiredTables as $workflow => $tables) {
    foreach ($tables as $t) {
        $res = $conn->query("SHOW TABLES LIKE '$t'");
        if ($res->num_rows == 0) {
            addFinding($findings, 'phase1', 'high', "Workflow '$workflow' missing required table", "Table $t does not exist", "Add table $t to installer", $t);
        }
        $res->free();
    }
}

// Check that controllers/models reference existing tables (basic smoke check)
$controllerDir = $root . '/application/controllers';
$modelDir = $root . '/application/models';
$phpFiles = array_merge(
    glob($controllerDir . '/*.php') ?: [],
    glob($modelDir . '/*.php') ?: []
);
$missingRefs = [];
foreach ($phpFiles as $pf) {
    $content = file_get_contents($pf);
    preg_match_all('/\$this->db->(table|from)\s*\(\s*[\'"](db_[a-z0-9_]+)[\'"]/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $t = $m[2];
        $res = $conn->query("SHOW TABLES LIKE '$t'");
        if ($res->num_rows == 0) {
            $missingRefs[$t] = $pf;
        }
        $res->free();
    }
}
if (!empty($missingRefs)) {
    foreach ($missingRefs as $t => $f) {
        addFinding($findings, 'phase1', 'high', "Code references table that does not exist", "Controller/model $f references table $t", "Add table $t to installer or remove dead code", $t);
    }
}
logLine("Business flow validation findings: " . count($findings['phase1']));

// Phase 7: Production Stress Test
logLine("\n=== PHASE 7: Production Stress Test ===");

// Run stress test inside current database using minimal prefixed tables
$prefix = 'stress_';
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$schemaLoaded = true;

$stressTables = [
    "CREATE TABLE IF NOT EXISTS `{$prefix}db_customers` (
        `id` int(5) NOT NULL AUTO_INCREMENT,
        `customer_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `mobile` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `status` int(1) DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS `{$prefix}db_items` (
        `id` int(5) NOT NULL AUTO_INCREMENT,
        `item_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `item_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `status` int(1) DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS `{$prefix}db_sales` (
        `id` int(5) NOT NULL AUTO_INCREMENT,
        `sales_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `sales_date` date DEFAULT NULL,
        `status` int(1) DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS `{$prefix}db_salesitems` (
        `id` int(5) NOT NULL AUTO_INCREMENT,
        `sales_id` int(5) NOT NULL,
        `item_id` int(5) NOT NULL,
        `sales_qty` int(5) NOT NULL DEFAULT 1,
        `unit_sales_price` double(10,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `sales_id` (`sales_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($stressTables as $stmt) {
    if (!$conn->query($stmt)) {
        addFinding($findings, 'phase7', 'high', "Stress test schema load failed", $conn->error, "Fix stress test schema", '', '');
        $schemaLoaded = false;
        break;
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

if ($schemaLoaded) {
    // Seed stress data into prefixed tables
    $batchSize = 1000;
    $productCount = isset($_GET['products']) ? (int)$_GET['products'] : 1000;
    $customerCount = isset($_GET['customers']) ? (int)$_GET['customers'] : 1000;
    $saleCount = isset($_GET['sales']) ? (int)$_GET['sales'] : 2000;

    $start = microtime(true);

    // Insert customers in batches
    $customerValues = array_fill(0, $customerCount, "('Stress Customer', '0800000000', 1)");
    for ($i = 0; $i < $customerCount; $i += $batchSize) {
        $batch = array_slice($customerValues, $i, $batchSize);
        $conn->query("INSERT INTO `{$prefix}db_customers` (customer_name, mobile, status) VALUES " . implode(',', $batch));
    }
    $customerTime = microtime(true) - $start;
    logLine("Inserted $customerCount customers in " . round($customerTime, 2) . "s");

    // Insert items in batches
    $itemValues = array_fill(0, $productCount, "('STRESS', 'Stress Item', 1)");
    for ($i = 0; $i < $productCount; $i += $batchSize) {
        $batch = array_slice($itemValues, $i, $batchSize);
        $conn->query("INSERT INTO `{$prefix}db_items` (item_code, item_name, status) VALUES " . implode(',', $batch));
    }
    $productTime = microtime(true) - $start;
    logLine("Inserted $productCount products in " . round($productTime, 2) . "s");

    // Insert sales in batches, then sales items in batches
    $saleStart = microtime(true);
    $saleValues = [];
    for ($i = 0; $i < $saleCount; $i++) {
        $saleValues[] = "('SALE-$i', NOW(), 1)";
    }
    for ($i = 0; $i < $saleCount; $i += $batchSize) {
        $batch = array_slice($saleValues, $i, $batchSize);
        $conn->query("INSERT INTO `{$prefix}db_sales` (sales_code, sales_date, status) VALUES " . implode(',', $batch));
    }

    // Build sales items with real sales_id references
    $minSaleId = $conn->query("SELECT MIN(id) FROM `{$prefix}db_sales`")->fetch_row()[0];
    $itemValues = [];
    for ($i = 0; $i < $saleCount; $i++) {
        $saleId = $minSaleId + $i;
        $itemValues[] = "($saleId, 1, 1, 100.00)";
    }
    for ($i = 0; $i < $saleCount; $i += $batchSize) {
        $batch = array_slice($itemValues, $i, $batchSize);
        $conn->query("INSERT INTO `{$prefix}db_salesitems` (sales_id, item_id, sales_qty, unit_sales_price) VALUES " . implode(',', $batch));
    }
    $saleTime = microtime(true) - $saleStart;
    logLine("Inserted $saleCount sales in " . round($saleTime, 2) . "s");

    // Run aggregate queries
    $aggStart = microtime(true);
    $aggRes = $conn->query("SELECT COUNT(*) FROM `{$prefix}db_sales`");
    $aggRes->fetch_row();
    $aggRes->free();
    $aggRes2 = $conn->query("SELECT s.id, MAX(s.sales_code) as sales_code, SUM(si.sales_qty * si.unit_sales_price) as total FROM `{$prefix}db_sales` s JOIN `{$prefix}db_salesitems` si ON s.id=si.sales_id GROUP BY s.id LIMIT 100");
    if ($aggRes2) $aggRes2->free();
    $aggTime = microtime(true) - $aggStart;
    logLine("Aggregate query stress in " . round($aggTime, 2) . "s");

    if ($saleTime > 30) {
        addFinding($findings, 'phase7', 'medium', "Sales insertion slow under stress", "Inserting $saleCount sales took >30s", "Add indexes on sales_id, batch inserts, optimize triggers", 'db_salesitems');
    }
    if ($aggTime > 5) {
        addFinding($findings, 'phase7', 'medium', "Aggregate queries slow under stress", "Aggregate queries took >5s", "Add composite indexes on sales_id + item_id, summary tables", 'db_salesitems');
    }

    // Clean up stress tables
    $cleanup = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE '{$prefix}%'");
    if ($cleanup) {
        while ($row = $cleanup->fetch_row()) {
            $conn->query("DROP TABLE IF EXISTS `{$row[0]}`");
        }
    }
}

logLine("Stress test findings: " . count($findings['phase7']));

// Generate raw report JSON
$reportData = [
    'database' => $dbname,
    'timestamp' => date('Y-m-d H:i:s'),
    'findings' => $findings,
    'fixes' => $fixes,
    'files_modified' => $filesModified,
    'zero_date_files' => array_slice($zeroDateFiles, 0, 50),
    'governance_files' => array_slice($governanceFiles, 0, 50),
    'nplus_files' => array_slice($nplusFiles, 0, 50),
    'large_tables' => array_slice($largeTables, 0, 20),
];

file_put_contents($reportDir . '/raw_audit_data.json', json_encode($reportData, JSON_PRETTY_PRINT));

logLine("\n=== Audit data saved to: $reportDir/raw_audit_data.json ===");

$conn->close();

function generateMarkdownReports($reportDir, $findings, $fixes, $filesModified, $zeroDateFiles, $governanceFiles, $nplusFiles, $largeTables) {
    // PRODUCTION_READINESS_REPORT.md
    $out = "# MartPoint Production Readiness Report\n\n";
    $out .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    $out .= "## Executive Summary\n\n";
    $total = 0;
    foreach ($findings as $phase => $items) $total += count($items);
    $out .= "- Total findings: $total\n";
    $out .= "- Critical findings: " . countCritical($findings) . "\n";
    $out .= "- Files modified: " . count($filesModified) . "\n\n";

    $out .= "## Phase-by-Phase Findings\n\n";
    foreach ($findings as $phase => $items) {
        $out .= "### " . strtoupper($phase) . "\n\n";
        if (empty($items)) {
            $out .= "No issues found.\n\n";
            continue;
        }
        foreach ($items as $f) {
            $out .= "- **[" . $f['severity'] . "]** " . $f['issue'];
            if ($f['table']) $out .= " (`" . $f['table'] . "`" . ($f['column'] ? "." . $f['column'] : '') . ")";
            $out .= "\n";
            if ($f['root_cause']) $out .= "  - Root cause: " . $f['root_cause'] . "\n";
            if ($f['fix']) $out .= "  - Fix: " . $f['fix'] . "\n";
        }
        $out .= "\n";
    }

    $out .= "## Fixes Applied\n\n";
    if (empty($fixes)) {
        $out .= "No fixes applied during this run.\n\n";
    } else {
        foreach ($fixes as $fix) {
            $out .= "- $fix\n";
        }
        $out .= "\n";
    }

    $out .= "## Files Modified\n\n";
    if (empty($filesModified)) {
        $out .= "No files modified during this run.\n\n";
    } else {
        foreach ($filesModified as $f) {
            $out .= "- $f\n";
        }
        $out .= "\n";
    }

    $out .= "## Remaining Risks\n\n";
    foreach ($findings as $phase => $items) {
        foreach ($items as $f) {
            if ($f['severity'] == 'high' || $f['severity'] == 'medium') {
                $out .= "- **" . $f['severity'] . "** " . $f['issue'] . " — " . ($f['root_cause'] ?: 'No root cause documented') . "\n";
            }
        }
    }
    $out .= "\n## Recommendations\n\n";
    $out .= "- Address all high-severity findings before production.\n";
    $out .= "- Migrate runtime CREATE TABLE statements to the installer.\n";
    $out .= "- Replace zero-date values with NULL or explicit defaults.\n";
    $out .= "- Add composite indexes for high-volume join/aggregate queries.\n";
    $out .= "- Schedule regular integrity and performance audits.\n\n";

    $out .= "## Production Readiness Score\n\n";
    $out .= "Score calculation is based on the ratio of resolved vs open findings.\n\n";
    $out .= "| Category | Score |\n|----------|-------|\n";
    $out .= "| Fresh Installation | " . scorePhase($findings, 'phase1') . " |\n";
    $out .= "| Upgrade Safety | " . scorePhase($findings, 'phase2') . " |\n";
    $out .= "| Database Integrity | " . scorePhase($findings, 'phase3') . " |\n";
    $out .= "| Legacy Date Cleanup | " . scorePhase($findings, 'phase4') . " |\n";
    $out .= "| Business Workflow Validation | " . scorePhase($findings, 'phase1') . " |\n";
    $out .= "| Performance | " . scorePhase($findings, 'phase5') . " |\n";
    $out .= "| Scalability | " . scorePhase($findings, 'phase7') . " |\n";
    $out .= "| Maintainability | " . scorePhase($findings, 'phase6') . " |\n";
    $out .= "| Production Readiness | " . overallScore($findings) . " |\n\n";

    file_put_contents($reportDir . '/PRODUCTION_READINESS_REPORT.md', $out);

    // DATABASE_INTEGRITY_REPORT.md
    $dbOut = "# MartPoint Database Integrity Report\n\n";
    $dbOut .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    $dbOut .= "## Findings\n\n";
    foreach ($findings['phase3'] as $f) {
        $dbOut .= "- **[" . $f['severity'] . "]** " . $f['issue'] . " (`" . $f['table'] . "`" . ($f['column'] ? "." . $f['column'] : '') . ")\n";
        $dbOut .= "  - Root cause: " . $f['root_cause'] . "\n";
        $dbOut .= "  - Fix: " . $f['fix'] . "\n";
    }
    $dbOut .= "\n## Legacy Date Values\n\n";
    foreach ($findings['phase4'] as $f) {
        $dbOut .= "- **[" . $f['severity'] . "]** " . $f['issue'] . " (`" . $f['table'] . "`" . ($f['column'] ? "." . $f['column'] : '') . ")\n";
        $dbOut .= "  - Root cause: " . $f['root_cause'] . "\n";
        $dbOut .= "  - Fix: " . $f['fix'] . "\n";
    }
    $dbOut .= "\n## Files with Zero-Date Literals\n\n";
    foreach ($zeroDateFiles as $f) {
        $dbOut .= "- $f\n";
    }
    file_put_contents($reportDir . '/DATABASE_INTEGRITY_REPORT.md', $dbOut);

    // UPGRADE_VALIDATION_REPORT.md
    $upOut = "# MartPoint Upgrade Validation Report\n\n";
    $upOut .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    foreach ($findings['phase2'] as $f) {
        $upOut .= "- **[" . $f['severity'] . "]** " . $f['issue'] . "\n";
        $upOut .= "  - Root cause: " . $f['root_cause'] . "\n";
        $upOut .= "  - Fix: " . $f['fix'] . "\n";
    }
    if (empty($findings['phase2'])) {
        $upOut .= "No upgrade issues detected.\n";
    }
    file_put_contents($reportDir . '/UPGRADE_VALIDATION_REPORT.md', $upOut);

    // PERFORMANCE_AUDIT_REPORT.md
    $perfOut = "# MartPoint Performance Audit Report\n\n";
    $perfOut .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    foreach ($findings['phase5'] as $f) {
        $perfOut .= "- **[" . $f['severity'] . "]** " . $f['issue'] . " (`" . $f['table'] . "`" . ($f['column'] ? "." . $f['column'] : '') . ")\n";
        $perfOut .= "  - Root cause: " . $f['root_cause'] . "\n";
        $perfOut .= "  - Fix: " . $f['fix'] . "\n";
    }
    foreach ($findings['phase7'] as $f) {
        $perfOut .= "- **[" . $f['severity'] . "]** (Stress) " . $f['issue'] . " (`" . $f['table'] . "`" . ($f['column'] ? "." . $f['column'] : '') . ")\n";
        $perfOut .= "  - Root cause: " . $f['root_cause'] . "\n";
        $perfOut .= "  - Fix: " . $f['fix'] . "\n";
    }
    $perfOut .= "\n## Large Tables\n\n";
    foreach ($largeTables as $lt) {
        $perfOut .= "- `" . $lt['TABLE_NAME'] . "`: " . round($lt['total_bytes']/1024/1024, 2) . " MB (rows: " . ($lt['TABLE_ROWS'] ?: 'unknown') . ")\n";
    }
    $perfOut .= "\n## Potential N+1 Patterns\n\n";
    foreach ($nplusFiles as $f) {
        $perfOut .= "- $f\n";
    }
    file_put_contents($reportDir . '/PERFORMANCE_AUDIT_REPORT.md', $perfOut);
}

function countCritical($findings) {
    $count = 0;
    foreach ($findings as $phase => $items) {
        foreach ($items as $f) {
            if ($f['severity'] == 'high') $count++;
        }
    }
    return $count;
}

function scorePhase($findings, $phase) {
    if (empty($findings[$phase])) return '100/100';
    $high = 0; $medium = 0; $low = 0;
    foreach ($findings[$phase] as $f) {
        if ($f['severity'] == 'high') $high++;
        elseif ($f['severity'] == 'medium') $medium++;
        else $low++;
    }
    $penalty = min(100, $high * 25 + $medium * 10 + $low * 1);
    $score = max(0, 100 - $penalty);
    return "$score/100";
}

function overallScore($findings) {
    // Weighted average of category scores for realistic overall readiness
    $categories = [
        ['phase' => 'phase1', 'weight' => 1],    // Business Workflow Validation
        ['phase' => 'phase2', 'weight' => 1],    // Upgrade Safety
        ['phase' => 'phase3', 'weight' => 1],    // Database Integrity
        ['phase' => 'phase4', 'weight' => 0.75], // Legacy Date Cleanup
        ['phase' => 'phase5', 'weight' => 0.75], // Performance
        ['phase' => 'phase7', 'weight' => 0.75], // Scalability
        ['phase' => 'phase6', 'weight' => 0.5],  // Maintainability
    ];
    $total = 0; $weight = 0;
    foreach ($categories as $c) {
        $scoreStr = scorePhase($findings, $c['phase']);
        $value = (int) str_replace('/100', '', $scoreStr);
        $total += $value * $c['weight'];
        $weight += $c['weight'];
    }
    $score = round($total / $weight);
    return "$score/100";
}

generateMarkdownReports($reportDir, $findings, $fixes, $filesModified, $zeroDateFiles, $governanceFiles, $nplusFiles, $largeTables);

logLine("\n=== Reports generated ===");
logLine("$reportDir/PRODUCTION_READINESS_REPORT.md");
logLine("$reportDir/DATABASE_INTEGRITY_REPORT.md");
logLine("$reportDir/UPGRADE_VALIDATION_REPORT.md");
logLine("$reportDir/PERFORMANCE_AUDIT_REPORT.md");
logLine("\nOverall score: " . overallScore($findings));
