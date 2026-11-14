-- Oil & gas: let a head office narrow a capability away from one site.
-- A location only ever switches a company capability OFF for itself.
-- An absent key inherits the company setting, so existing installs are unchanged.
-- Switching a capability off leaves every record it produced readable.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_warehouse' AND column_name = 'feature_flags_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_warehouse` ADD COLUMN `feature_flags_json` TEXT NULL COMMENT ''Location capability overrides; null/absent flag inherits the company setting''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.247-oil-gas-location-flags', NOW());
