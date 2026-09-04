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
    $startMarker = "DROP TABLE IF EXISTS `" . $table . "`";
    $startPos = strpos($content, $startMarker);
    
    if ($startPos === false) {
        echo "WARNING: Could not find {$table}\n";
        continue;
    }
    
    // Find the next DROP TABLE after this one
    $searchFrom = $startPos + strlen($startMarker);
    $nextDropPos = strpos($content, "\nDROP TABLE IF EXISTS `", $searchFrom);
    
    if ($nextDropPos === false) {
        // Last table - extract to end of file
        $block = substr($content, $startPos);
    } else {
        $block = substr($content, $startPos, $nextDropPos - $startPos);
    }
    
    $output .= trim($block) . "\n\n";
    echo "Extracted: {$table}\n";
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents($outputFile, $output);
echo "\nDone! Saved to: missing_tables.sql\n";
echo "File size: " . filesize($outputFile) . " bytes\n";
