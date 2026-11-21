-- Store the client business portfolio ID returned by Facebook Login for Business.
-- This identifies which business the Business Integration System User token belongs to.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_ai_ads_connections' AND COLUMN_NAME='meta_business_id')=0,
  'ALTER TABLE `db_ai_ads_connections` ADD COLUMN `meta_business_id` VARCHAR(80) DEFAULT NULL AFTER `meta_user_id`',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
