-- 4.0.9.179 — tenant-scoped, non-PHP HTML storefront themes

CREATE TABLE IF NOT EXISTS `db_storefront_uploaded_themes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `theme_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `theme_key` varchar(50) NOT NULL,
  `theme_name` varchar(120) NOT NULL,
  `theme_version` varchar(30) NOT NULL,
  `industry` varchar(50) NOT NULL DEFAULT 'general',
  `manifest_json` mediumtext NOT NULL,
  `storage_path` varchar(255) NOT NULL,
  `extracted_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `unsupported_capabilities_json` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_uploaded_theme_id` (`theme_id`),
  UNIQUE KEY `uk_uploaded_theme_key` (`theme_key`),
  KEY `idx_uploaded_theme_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_uploaded_theme_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `theme_id` int(10) unsigned NOT NULL,
  `settings_json` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uploaded_theme_settings` (`store_id`,`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @db := DATABASE();
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_uploaded_themes' AND COLUMN_NAME='theme_id');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_uploaded_themes` ADD COLUMN `theme_id` int(10) unsigned NOT NULL AFTER `id`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_themes' AND COLUMN_NAME='theme_source');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_themes` ADD COLUMN `theme_source` varchar(20) NOT NULL DEFAULT ''builtin'' AFTER `theme_key`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_themes' AND COLUMN_NAME='owner_store_id');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_themes` ADD COLUMN `owner_store_id` int(10) unsigned DEFAULT NULL AFTER `theme_source`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_themes' AND COLUMN_NAME='theme_version');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_themes` ADD COLUMN `theme_version` varchar(30) DEFAULT NULL AFTER `theme_name`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_themes' AND COLUMN_NAME='manifest_json');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_themes` ADD COLUMN `manifest_json` mediumtext DEFAULT NULL AFTER `preview_image`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_themes' AND COLUMN_NAME='storage_path');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_themes` ADD COLUMN `storage_path` varchar(255) DEFAULT NULL AFTER `manifest_json`', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='previous_theme_id');
SET @s := IF(@c=0, 'ALTER TABLE `db_storefront_settings` ADD COLUMN `previous_theme_id` int(10) DEFAULT NULL', 'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
