-- Stepped weight pricing: base price covers the included kg, then each extra step adds a charge.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'weight_pricing_enabled');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `weight_pricing_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Use stepped weight pricing at POS''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'weight_pricing_base_kg');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `weight_pricing_base_kg` DECIMAL(10,4) NOT NULL DEFAULT 1.0000 COMMENT ''Weight included in base price''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'weight_pricing_increment_kg');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `weight_pricing_increment_kg` DECIMAL(10,4) NOT NULL DEFAULT 0.0500 COMMENT ''Additional weight step in kg''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'weight_pricing_increment_charge');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `weight_pricing_increment_charge` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT ''Charge per additional weight step''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'weight_pricing_mode');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `weight_pricing_mode` VARCHAR(32) NOT NULL DEFAULT ''kg_plus_lines'' COMMENT ''Weight pricing formula''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
