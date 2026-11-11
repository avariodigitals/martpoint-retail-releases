-- Let Central configure one Paystack platform charge with both a percentage
-- and a fixed NGN component, for example 2.5% + NGN 100.
SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts' AND COLUMN_NAME='platform_fee_percent');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_payment_gateway_accounts` ADD COLUMN `platform_fee_percent` DECIMAL(8,4) NOT NULL DEFAULT 0 AFTER `platform_fee_value`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_payment_gateway_accounts' AND COLUMN_NAME='platform_fee_fixed');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_payment_gateway_accounts` ADD COLUMN `platform_fee_fixed` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `platform_fee_percent`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Carry forward the existing single-component configuration on upgrade.
SET @s = IF(@t=1, 'UPDATE `db_payment_gateway_accounts` SET `platform_fee_percent` = CASE WHEN `platform_fee_type` = ''percentage'' THEN `platform_fee_value` ELSE 0 END, `platform_fee_fixed` = CASE WHEN `platform_fee_type` = ''flat'' THEN `platform_fee_value` ELSE 0 END WHERE `platform_fee_percent` = 0 AND `platform_fee_fixed` = 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
