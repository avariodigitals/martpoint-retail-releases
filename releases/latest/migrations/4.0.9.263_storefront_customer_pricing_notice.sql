-- Store-controlled customer pricing sign-in popup and editable copy.
-- Existing storefronts keep the current prompt enabled with clear group wording.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='customer_pricing_notice_enabled')=0, 'ALTER TABLE db_storefront_settings ADD customer_pricing_notice_enabled TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='customer_pricing_notice_title')=0, 'ALTER TABLE db_storefront_settings ADD customer_pricing_notice_title VARCHAR(120) NOT NULL DEFAULT ''Customer pricing''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='customer_pricing_notice_text')=0, 'ALTER TABLE db_storefront_settings ADD customer_pricing_notice_text VARCHAR(500) NOT NULL DEFAULT ''Sign in to My Account to check whether the store has assigned your account to a customer pricing group.''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='customer_pricing_notice_link_text')=0, 'ALTER TABLE db_storefront_settings ADD customer_pricing_notice_link_text VARCHAR(80) NOT NULL DEFAULT ''Sign in to My Account''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
