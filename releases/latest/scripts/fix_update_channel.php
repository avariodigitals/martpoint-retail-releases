<?php
/**
 * fix_update_channel.php — Add update_channel_url column if missing
 * Upload this to your web root and run via browser, or run via command line.
 */

require_once __DIR__ . '/application/config/database.php';

$host = $db[$active_group]['hostname'];
$user = $db[$active_group]['username'];
$pass = $db[$active_group]['password'];
$dbname = $db[$active_group]['database'];

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `db_sitesettings` LIKE 'update_channel_url'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Column 'update_channel_url' already exists.<br>";
    } else {
        $pdo->exec("ALTER TABLE `db_sitesettings` ADD COLUMN `update_channel_url` VARCHAR(500) DEFAULT NULL");
        echo "✅ Column 'update_channel_url' added successfully.<br>";
    }

    // Also check / create the tracking tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS `db_system_updates` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `store_id` INT NOT NULL DEFAULT 1,
      `from_version` VARCHAR(20) NOT NULL,
      `to_version` VARCHAR(20) NOT NULL,
      `status` ENUM('pending','running','success','failed','restored') NOT NULL DEFAULT 'pending',
      `current_step` TINYINT UNSIGNED NOT NULL DEFAULT 0,
      `total_steps` TINYINT UNSIGNED NOT NULL DEFAULT 8,
      `step_label` VARCHAR(100) DEFAULT NULL,
      `backup_db_path` VARCHAR(500) DEFAULT NULL,
      `backup_files_path` VARCHAR(500) DEFAULT NULL,
      `log` TEXT DEFAULT NULL,
      `error_message` TEXT DEFAULT NULL,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `completed_at` DATETIME DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `store_id` (`store_id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table 'db_system_updates' ready.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `version` VARCHAR(20) NOT NULL,
      `filename` VARCHAR(200) NOT NULL,
      `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_version_file` (`version`,`filename`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table 'db_schema_migrations' ready.<br>";

    // Seed default channel URL
    $pdo->exec("UPDATE `db_sitesettings` SET `update_channel_url` = 'https://raw.githubusercontent.com/YOUR_USERNAME/martpoint-retail-releases/main/releases/latest/' WHERE `id` = 1 AND (`update_channel_url` IS NULL OR `update_channel_url` = '')");
    echo "✅ Default channel URL set (change YOUR_USERNAME in db_sitesettings).<br>";

    echo "<br><strong>All done. You can now delete this file.</strong>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
