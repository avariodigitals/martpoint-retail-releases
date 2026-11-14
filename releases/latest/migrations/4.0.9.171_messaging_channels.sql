-- ============================================================================
-- MartPoint 4.0.9.171 — WhatsApp Business API + Meta Messaging Channels
--
-- Adds WhatsApp Business API (WhatsApp Platform) configuration columns to
-- db_storefront_settings and creates the conversation/message tables used to
-- log inbound & outbound messages across WhatsApp, Instagram DM and
-- Facebook Messenger.
--
-- Idempotent: safe to run more than once. MySQL 5.7+. Runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @tbl = 'db_storefront_settings';

-- WhatsApp Business API access token (long-lived system-user or page token)
SET @col := 'whatsapp_api_token';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(1000) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- WhatsApp Business phone number ID (the numeric ID shown in WhatsApp Manager)
SET @col := 'whatsapp_phone_number_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Webhook verify token you set in Meta for WhatsApp / Instagram / Messenger webhooks
SET @col := 'whatsapp_webhook_verify_token';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(255) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- WhatsApp Business API switched on for this store
SET @col := 'whatsapp_api_enabled';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NULL DEFAULT 0'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Facebook Page access token (used for Facebook Messenger replies)
SET @col := 'facebook_page_access_token';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(1000) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Facebook Page ID (for Messenger)
SET @col := 'facebook_page_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Instagram Business Account ID (linked to a Facebook Page)
SET @col := 'instagram_business_account_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(100) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ============================================================================
-- Conversation log (one row per customer thread per channel)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_messaging_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `channel` ENUM('whatsapp','instagram','messenger') NOT NULL DEFAULT 'whatsapp',
  `external_conversation_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Meta-provided conversation/wa_id/psid',
  `customer_phone` VARCHAR(50) NULL DEFAULT NULL,
  `customer_name` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('open','closed','snoozed') NOT NULL DEFAULT 'open',
  `last_message_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_channel` (`store_id`,`channel`),
  KEY `external` (`external_conversation_id`),
  KEY `last_message_at` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- Message log (one row per message sent or received)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_messaging_messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` INT(11) NOT NULL,
  `direction` ENUM('inbound','outbound') NOT NULL DEFAULT 'inbound',
  `message_type` ENUM('text','image','document','audio','video','interactive','system') NOT NULL DEFAULT 'text',
  `body` TEXT NULL DEFAULT NULL COMMENT 'Message text / caption',
  `meta_message_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Meta message id (for status callbacks)',
  `status` ENUM('sent','delivered','read','failed') NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conversation` (`conversation_id`),
  KEY `direction` (`direction`),
  KEY `meta_message_id` (`meta_message_id`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `db_messaging_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
