-- MartPoint 4.0.9.220 — Client release prep: billing VAT, send caps, lists, templates, reports.
-- Additive and rerunnable. Fleet databases keep the Central-side tables empty; Central
-- databases keep the store-side tables empty. Nothing rewrites existing money or history.
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---------------------------------------------------------------------------
-- 1. VAT on Communications subscriptions, recharges and add-on requests.
--    amount_kobo keeps meaning "the gross the store is actually charged"; the new
--    columns record the tax basis and the VAT line so Central can bill from them.
-- ---------------------------------------------------------------------------
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests' AND COLUMN_NAME = 'net_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_communication_requests` ADD COLUMN `net_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Tax basis in kobo -  equals amount_kobo when no VAT applies'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests' AND COLUMN_NAME = 'tax_bps') > 0,
  'SELECT 1',
  'ALTER TABLE `db_communication_requests` ADD COLUMN `tax_bps` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''VAT basis points snapshot at request time'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests' AND COLUMN_NAME = 'tax_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_communication_requests` ADD COLUMN `tax_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''VAT charged on this request in kobo'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests' AND COLUMN_NAME = 'total_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_communication_requests` ADD COLUMN `total_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Gross charge in kobo (net + VAT)'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill requests recorded before VAT existed: gross was charged with no tax,
-- so the stored amount is both the tax basis and the total. Prepared only when
-- the table is present, so an unrelated database can still run this migration.
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_requests') = 0,
  'SELECT 1',
  'UPDATE `db_communication_requests` SET net_kobo=amount_kobo, total_kobo=amount_kobo WHERE net_kobo=0 AND amount_kobo>0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Messaging add-on packages are priced by the MartPoint catalogue and billed by
-- Central on activation, so they carry the same net/VAT/gross snapshot.
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests' AND COLUMN_NAME = 'net_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_addon_requests` ADD COLUMN `net_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Add-on price without VAT at request time'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests' AND COLUMN_NAME = 'tax_bps') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_addon_requests` ADD COLUMN `tax_bps` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''VAT basis points snapshot at request time'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests' AND COLUMN_NAME = 'tax_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_addon_requests` ADD COLUMN `tax_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''VAT payable on this add-on in kobo'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_addon_requests' AND COLUMN_NAME = 'total_kobo') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_addon_requests` ADD COLUMN `total_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Gross add-on charge in kobo (net + VAT)'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- 2. Abuse control: Central sets recipients-per-hour caps, platform-wide and
--    per store account. 0 means "use the platform default"/"uncapped".
-- ---------------------------------------------------------------------------
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings' AND COLUMN_NAME = 'hourly_email_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_settings` ADD COLUMN `hourly_email_cap` INT UNSIGNED NOT NULL DEFAULT 600 COMMENT ''Platform default email recipients allowed per rolling hour per store'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings' AND COLUMN_NAME = 'hourly_sms_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_settings` ADD COLUMN `hourly_sms_cap` INT UNSIGNED NOT NULL DEFAULT 300 COMMENT ''Platform default SMS segments allowed per rolling hour per store'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts' AND COLUMN_NAME = 'hourly_email_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_accounts` ADD COLUMN `hourly_email_cap` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Per-store email cap per hour -  0 follows the platform default'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts' AND COLUMN_NAME = 'hourly_sms_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_accounts` ADD COLUMN `hourly_sms_cap` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Per-store SMS cap per hour -  0 follows the platform default'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts' AND COLUMN_NAME = 'audience_list_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_accounts` ADD COLUMN `audience_list_cap` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''Audience lists this store may keep -  0 follows the platform default'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The store normally sends under its own name and reply address. These two
-- columns let MartPoint pin either one for a specific store; empty means
-- "use whatever the store set on itself".
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts' AND COLUMN_NAME = 'sender_name') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_accounts` ADD COLUMN `sender_name` VARCHAR(190) NOT NULL DEFAULT '''' COMMENT ''MartPoint-pinned email display name -  empty uses the store name'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_accounts' AND COLUMN_NAME = 'sender_reply_to') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_accounts` ADD COLUMN `sender_reply_to` VARCHAR(190) NOT NULL DEFAULT '''' COMMENT ''MartPoint-pinned Reply-To address -  empty uses the store reply address'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_messaging_settings' AND COLUMN_NAME = 'audience_list_cap') > 0,
  'SELECT 1',
  'ALTER TABLE `db_messaging_settings` ADD COLUMN `audience_list_cap` INT UNSIGNED NOT NULL DEFAULT 25 COMMENT ''Default audience lists allowed per store'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- 3. Named audience lists. Imports choose a destination list; campaigns can
--    select a list as their audience. Contacts stay in the shared imported
--    contact pool, so a contact never duplicates per list.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_communication_lists (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 name VARCHAR(120) NOT NULL,
 channel VARCHAR(10) NOT NULL DEFAULT 'email',
 note VARCHAR(500) NOT NULL DEFAULT '',
 contact_count INT UNSIGNED NOT NULL DEFAULT 0,
 created_by VARCHAR(60) NOT NULL DEFAULT 'system',
 created_at DATETIME NOT NULL,
 updated_at DATETIME NULL,
 UNIQUE KEY comm_list_name (store_id,name),
 KEY comm_list_store (store_id,channel,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_communication_list_contacts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 list_id BIGINT UNSIGNED NOT NULL,
 contact_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY comm_list_contact (list_id,contact_id),
 KEY comm_list_lookup (store_id,list_id),
 KEY comm_list_contact_ref (contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. Campaign email templates. Central owns the hub (store_id=0); stores keep
--    their own copies and may start from a hub design.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_communication_templates (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL DEFAULT 0,
 hub_template_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 name VARCHAR(120) NOT NULL,
 format VARCHAR(10) NOT NULL DEFAULT 'html',
 kind VARCHAR(10) NOT NULL DEFAULT 'prebuilt',
 category VARCHAR(30) NOT NULL DEFAULT 'general',
 industry VARCHAR(30) NOT NULL DEFAULT 'any',
 occasion VARCHAR(30) NOT NULL DEFAULT 'any',
 subject VARCHAR(255) NOT NULL DEFAULT '',
 preheader VARCHAR(255) NOT NULL DEFAULT '',
 blocks_json TEXT NULL,
 html_body MEDIUMTEXT NULL,
 plain_body MEDIUMTEXT NULL,
 accent VARCHAR(7) NOT NULL DEFAULT '#6554c0',
 brand_display VARCHAR(10) NOT NULL DEFAULT 'name',
 show_unsubscribe TINYINT NOT NULL DEFAULT 1,
 cta_label VARCHAR(80) NOT NULL DEFAULT '',
 cta_url VARCHAR(1000) NOT NULL DEFAULT '',
 base_template_key VARCHAR(30) NOT NULL DEFAULT '',
 enabled TINYINT NOT NULL DEFAULT 1,
 created_by VARCHAR(60) NOT NULL DEFAULT 'system',
 created_at DATETIME NOT NULL,
 updated_at DATETIME NULL,
 KEY comm_template_store (store_id,enabled,id),
 KEY comm_template_hub (hub_template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A campaign can now be bound to a stored design instead of one of the four
-- hard-coded renders. 0 keeps the legacy template_key behaviour.
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_campaign_designs') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_communication_campaign_designs' AND COLUMN_NAME = 'template_id') > 0,
  'SELECT 1',
  'ALTER TABLE `db_communication_campaign_designs` ADD COLUMN `template_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT ''db_communication_templates.id -  0 = legacy built-in design'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- 5. Campaign reporting: opens and clicks measured on the fleet that owns the
--    send rows. Kept out of the Central billing tables on purpose, so the
--    messaging setup checks stay unchanged.
-- ---------------------------------------------------------------------------
SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends' AND COLUMN_NAME = 'opened_at') > 0,
  'SELECT 1',
  'ALTER TABLE `db_campaign_sends` ADD COLUMN `opened_at` DATETIME NULL COMMENT ''First open of a campaign email'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends' AND COLUMN_NAME = 'open_count') > 0,
  'SELECT 1',
  'ALTER TABLE `db_campaign_sends` ADD COLUMN `open_count` INT UNSIGNED NOT NULL DEFAULT 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends' AND COLUMN_NAME = 'clicked_at') > 0,
  'SELECT 1',
  'ALTER TABLE `db_campaign_sends` ADD COLUMN `clicked_at` DATETIME NULL COMMENT ''First link click of a campaign email'''));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF((SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends') = 0
  OR (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_campaign_sends' AND COLUMN_NAME = 'click_count') > 0,
  'SELECT 1',
  'ALTER TABLE `db_campaign_sends` ADD COLUMN `click_count` INT UNSIGNED NOT NULL DEFAULT 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS db_communication_email_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 campaign_id BIGINT UNSIGNED NOT NULL,
 send_id BIGINT UNSIGNED NOT NULL,
 event VARCHAR(10) NOT NULL,
 url VARCHAR(1000) NOT NULL DEFAULT '',
 link_hash CHAR(32) NOT NULL DEFAULT '',
 ua_client VARCHAR(60) NOT NULL DEFAULT '',
 created_at DATETIME NOT NULL,
 KEY comm_report_campaign (campaign_id,event,created_at),
 KEY comm_report_send (send_id,event),
 KEY comm_report_links (campaign_id,link_hash,event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
