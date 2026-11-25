-- Give each managed store a predictable, capped allowance for essential
-- storefront order emails. This is separate from purchased marketing credits.
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_messaging_settings')=0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_messaging_settings' AND COLUMN_NAME='monthly_order_email_cap')>0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_settings` ADD COLUMN `monthly_order_email_cap` INT UNSIGNED NOT NULL DEFAULT 1000 COMMENT ''Included order email recipients per store per Lagos calendar month; 0 disables included order mail'''
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
