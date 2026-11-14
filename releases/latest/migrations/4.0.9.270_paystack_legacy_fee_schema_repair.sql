-- Repair the legacy Paystack table layout that has no platform_fee_value.
-- Fee columns are added safely, and legacy values are copied only when both
-- legacy source columns exist.
SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts' AND COLUMN_NAME='platform_fee_percent');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_payment_gateway_accounts` ADD COLUMN `platform_fee_percent` DECIMAL(8,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts' AND COLUMN_NAME='platform_fee_fixed');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_payment_gateway_accounts` ADD COLUMN `platform_fee_fixed` DECIMAL(18,2) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @legacy = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts' AND COLUMN_NAME IN ('platform_fee_type','platform_fee_value'));
SET @s = IF(@t=1 AND @legacy=2, 'UPDATE `db_payment_gateway_accounts` SET `platform_fee_percent` = CASE WHEN `platform_fee_type` = ''percentage'' THEN `platform_fee_value` ELSE 0 END, `platform_fee_fixed` = CASE WHEN `platform_fee_type` = ''flat'' THEN `platform_fee_value` ELSE 0 END WHERE `platform_fee_percent` = 0 AND `platform_fee_fixed` = 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_settings');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_settings' AND COLUMN_NAME='platform_fee_percent');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_paystack_settings` ADD COLUMN `platform_fee_percent` DECIMAL(8,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_settings' AND COLUMN_NAME='platform_fee_fixed');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_paystack_settings` ADD COLUMN `platform_fee_fixed` DECIMAL(18,2) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @legacy = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_settings' AND COLUMN_NAME IN ('platform_fee_type','platform_fee_value'));
SET @s = IF(@t=1 AND @legacy=2, 'UPDATE `db_paystack_settings` SET `platform_fee_percent` = CASE WHEN `platform_fee_type` = ''percentage'' THEN `platform_fee_value` ELSE 0 END, `platform_fee_fixed` = CASE WHEN `platform_fee_type` = ''flat'' THEN `platform_fee_value` ELSE 0 END WHERE `platform_fee_percent` = 0 AND `platform_fee_fixed` = 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
