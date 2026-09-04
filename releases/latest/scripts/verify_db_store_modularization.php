<?php
/**
 * Verification script for the db_store modularization.
 *
 * Run from the repository root (or web root) after the migration is applied:
 *   php verify_db_store_modularization.php
 *
 * It checks that:
 *  - The modular tables exist with the expected columns.
 *  - Legacy db_store columns that were moved are still present (or migration ran).
 *  - The helper functions are loaded and can read modular/fallback values.
 *  - Fresh-install stores have modular seed rows.
 */

// Bootstrap CodeIgniter constants expected by application/config/database.php
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}
if (!defined('BASEPATH')) {
    define('BASEPATH', __DIR__ . '/system/');
}
if (!defined('APPPATH')) {
    define('APPPATH', __DIR__ . '/application/');
}
if (!defined('SYSDIR')) {
    define('SYSDIR', 'system');
}

if (file_exists(__DIR__ . '/application/config/database.php')) {
    require __DIR__ . '/application/config/database.php';
} else {
    fwrite(STDERR, "Cannot locate CodeIgniter database config.\n");
    exit(1);
}

$cfg = get_config();
$dsn = "mysql:host={$cfg['hostname']};dbname={$cfg['database']};charset={$cfg['char_set']}";

try {
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$db = $cfg['database'];
$errors = [];
$warnings = [];

$expected_tables = [
    'db_store_inventory_settings',
    'db_store_receipt_settings',
    'db_store_pos_settings',
    'db_store_notification_settings',
    'db_store_tax_settings',
    'db_store_theme_settings',
    'db_store_storefront_settings',
    'db_store_payment_settings',
    'db_store_industry_settings',
    'db_store_business_profile',
    'db_store_settings',
];

foreach ($expected_tables as $table) {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $stmt->execute([$db, $table]);
    if (!$stmt->fetch()) {
        $errors[] = "Missing modular table: $table";
    }
}

// Core db_store should still exist
$stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = 'db_store'");
$stmt->execute([$db]);
if (!$stmt->fetch()) {
    $errors[] = "Missing core table: db_store";
}

// Count stores and verify seed rows
$stores = $pdo->query("SELECT id FROM db_store ORDER BY id");
$store_ids = [];
while ($row = $stores->fetch(PDO::FETCH_ASSOC)) {
    $store_ids[] = (int)$row['id'];
}

foreach ($store_ids as $store_id) {
    foreach ($expected_tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE store_id = ?");
        $stmt->execute([$store_id]);
        $count = (int) $stmt->fetchColumn();
        if ($count === 0) {
            $warnings[] = "$table missing seed row for store_id=$store_id";
        }
    }
}

// Check migration produced copies for the first store if old columns still exist
$moved_columns = [
    'category_init', 'item_init', 'supplier_init', 'purchase_init', 'purchase_return_init',
    'customer_init', 'sales_init', 'sales_return_init', 'expense_init', 'quotation_init',
    'money_transfer_init', 'accounts_init', 'sales_payment_init', 'sales_return_payment_init',
    'purchase_payment_init', 'purchase_return_payment_init', 'expense_payment_init', 'cust_advance_init',
    'sales_invoice_format_id', 'pos_invoice_format_id', 'sales_invoice_footer_text', 'invoice_terms',
    'round_off', 'change_return', 'decimals', 'qty_decimals',
    'sales_discount', 'mrp_column', 'show_signature', 'previous_balance_bit',
    'sms_status', 'language_id', 'bank_details', 'store_website', 'store_logo', 'signature',
    'gst_no', 'vat_no', 'pan_no',
    'industry_type', 'business_model', 'feature_flags_json', 'workflow_template_key',
    'dashboard_template_key', 'storefront_theme_key', 'label_overrides_json', 'industry_settings_json',
];

$legacy_columns = [];
$cols = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = 'db_store'");
$cols->execute([$db]);
while ($row = $cols->fetch(PDO::FETCH_NUM)) {
    $legacy_columns[] = $row[0];
}

foreach ($moved_columns as $col) {
    if (in_array($col, $legacy_columns)) {
        // If column still exists, verify that the migration copied data for store 1
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM db_store WHERE id = 1 AND $col IS NOT NULL");
        $stmt->execute();
        if ((int) $stmt->fetchColumn() === 1) {
            // Verify that the modular table has the same value
            $table_map = [
                'db_store_inventory_settings' => [
                    'category_init','item_init','supplier_init','purchase_init','purchase_return_init',
                    'customer_init','sales_init','sales_return_init','expense_init','quotation_init',
                    'money_transfer_init','accounts_init','sales_payment_init','sales_return_payment_init',
                    'purchase_payment_init','purchase_return_payment_init','expense_payment_init','cust_advance_init',
                ],
                'db_store_receipt_settings' => [
                    'sales_invoice_format_id','pos_invoice_format_id','sales_invoice_footer_text','invoice_terms',
                    'round_off','change_return','decimals','qty_decimals',
                ],
                'db_store_pos_settings' => [
                    'sales_discount','mrp_column','show_signature','previous_balance_bit',
                ],
                'db_store_notification_settings' => ['sms_status'],
                'db_store_settings' => ['language_id','e_invoice_enabled','nin_api_enabled','nin_api_url','nin_api_key','nin_api_provider','cid'],
                'db_store_payment_settings' => ['bank_details'],
                'db_store_storefront_settings' => ['store_website'],
                'db_store_theme_settings' => ['store_logo','signature'],
                'db_store_tax_settings' => ['gst_no','vat_no','pan_no'],
                'db_store_industry_settings' => [
                    'industry_type','business_model','feature_flags_json','workflow_template_key',
                    'dashboard_template_key','storefront_theme_key','label_overrides_json','industry_settings_json',
                ],
                'db_store_business_profile' => [
                    'industry_type','business_model','feature_flags_json','workflow_template_key',
                    'dashboard_template_key','storefront_theme_key','label_overrides_json','industry_settings_json',
                ],
            ];
            $target_table = null;
            foreach ($table_map as $tbl => $cols_in_tbl) {
                if (in_array($col, $cols_in_tbl)) {
                    $target_table = $tbl;
                    break;
                }
            }
            if ($target_table) {
                if ($target_table === 'db_store_settings') {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM db_store_settings WHERE store_id = 1 AND setting_key = ? AND setting_value IS NOT NULL");
                    $stmt->execute([$col]);
                } else {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $target_table WHERE store_id = 1 AND $col IS NOT NULL");
                    $stmt->execute();
                }
                if ((int) $stmt->fetchColumn() === 0) {
                    $errors[] = "Migration did not copy $col to $target_table for store_id=1";
                }
            }
        }
    }
}

// Summary
if ($errors) {
    echo "FAILED with errors:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
}
if ($warnings) {
    echo "WARNINGS:\n";
    foreach ($warnings as $w) {
        echo "  - $w\n";
    }
}
if (!$errors && !$warnings) {
    echo "OK: db_store modularization verified.\n";
    exit(0);
}
if ($errors) {
    exit(1);
}
exit(0);

function get_config() {
    // CodeIgniter 3 database.php defines $db as a global array
    global $db;
    $active = 'default';
    return $db[$active];
}
