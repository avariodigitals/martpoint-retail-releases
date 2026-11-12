-- Communications top-ups: enforce the published custom minimum and retain
-- whether the amount the merchant entered included VAT or was VAT-exclusive.
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
   WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_communication_requests')=0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_communication_requests'
        AND COLUMN_NAME='vat_mode')>0,
  'SELECT 1',
  'ALTER TABLE `db_communication_requests` ADD COLUMN `vat_mode` VARCHAR(10) NOT NULL DEFAULT ''exclusive'' COMMENT ''Whether the entered top-up amount included VAT'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The minimum is an editable commercial setting. Do not rewrite existing
-- values here; Next Admin or the Central catalogue owner supplies it.
