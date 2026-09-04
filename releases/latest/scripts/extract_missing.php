<?php
$backupFile = __DIR__ . '/backups/db_20260623_170906.sql';
$outputFile = __DIR__ . '/missing_tables.sql';

$missingTables = [
    'db_approval_logs', 'db_approval_settings', 'db_brevo',
    'db_debt_reminder_history', 'db_debt_reminder_settings',
    'db_email_logs', 'db_email_templates', 'db_expiry_settings',
    'db_license_history', 'db_license_otps',
    'db_online_order_items', 'db_online_orders',
    'db_qr_codes', 'db_report_schedules', 'db_services',
    'db_storefront_analytics', 'db_storefront_brands',
    'db_storefront_faqs', 'db_storefront_instagram',
    'db_storefront_settings', 'db_storefront_testimonials',
    'db_subscription_license'
];

$content = file_get_contents($backupFile);
if ($content === false) {
    die("Cannot read backup file\n");
}

$output = "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($missingTables as $table) {
    $pattern = '/(DROP TABLE IF EXISTS `' . preg_quote($table, '/') . '`;\s*CREATE TABLE `' . preg_quote($table, '/') . '`.*?)(?=DROP TABLE IF EXISTS `[^`]+`;|$)/s';
    if (preg_match($pattern, $content, $matches)) {
        $block = trim($matches[1]);
        // Remove trailing whitespace/newlines but keep the semicolon
        $output .= $block . "\n\n";
        echo "Extracted: {$table}\n";
    } else {
        echo "WARNING: Could not extract {$table}\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents($outputFile, $output);
echo "\nDone! Saved to: missing_tables.sql\n";
