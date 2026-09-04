<?php
// This script fixes collation issues when importing MySQL 8 dumps to older servers
// Usage: Place next to your .sql file and run via browser, or adjust paths below

$inputFile = isset($_GET['file']) ? $_GET['file'] : 'missing_tables.sql';
$outputFile = str_replace('.sql', '_fixed.sql', $inputFile);

if (!file_exists($inputFile)) {
    die("File not found: {$inputFile}. Upload your .sql file to the same folder as this script, or pass ?file=yourfile.sql");
}

$content = file_get_contents($inputFile);

// Replace MySQL 8 collation with compatible one
$content = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $content);
$content = str_replace('utf8mb4_0900_as_ci', 'utf8mb4_unicode_ci', $content);
$content = str_replace('utf8mb4_0900_as_cs', 'utf8mb4_unicode_ci', $content);
$content = str_replace('utf8mb4_0900_bin', 'utf8mb4_bin', $content);

file_put_contents($outputFile, $content);

echo "Fixed! New file created: {$outputFile}<br>";
echo "Import <b>{$outputFile}</b> into phpMyAdmin instead of the original.<br>";
echo "File size: " . number_format(filesize($outputFile)) . " bytes";
