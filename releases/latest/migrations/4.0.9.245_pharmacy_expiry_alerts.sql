-- Expiry alerts on the batch ledger (v4.0.9.245)
--
-- The pharmacy screens could already answer "what is expiring" when somebody
-- opened them; nothing told the pharmacist before they asked. Wave 3 built the
-- batch-level report (4.0.9.238) — this turns that same report into a daily
-- list that reaches the shop on the two channels they configured on the Expiry
-- Settings screen: email and WhatsApp.
--
-- Everything lives on the store's existing `db_expiry_settings` row so the
-- alert is switched on from the screen that already owns the warning window —
-- no second place to configure the same promise:
--   whatsapp_alerts_enabled — the WhatsApp channel switch, off by default so a
--     fresh install never messages anybody by accident.
--   alert_phone            — the number the digest goes to, next to alert_email.
--   last_alert_slot        — the day the last digest that reached the shop went
--     out. The nightly sweep re-runs idempotently: same day, one message.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD COLUMN IF NOT EXISTS, and older fleets have no
-- db_expiry_settings table at all until the 3.0→latest upgrade creates it.
-- Nothing here moves a quantity or changes a selling rule — every new column
-- defaults to the behaviour the app had yesterday.

-- 1. WhatsApp channel switch ----------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings'
       AND COLUMN_NAME = 'whatsapp_alerts_enabled') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings') > 0,
  'ALTER TABLE `db_expiry_settings` ADD COLUMN `whatsapp_alerts_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `alert_email`',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. The number the digest is sent to -------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings'
       AND COLUMN_NAME = 'alert_phone') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings') > 0,
  'ALTER TABLE `db_expiry_settings` ADD COLUMN `alert_phone` VARCHAR(30) NULL AFTER `whatsapp_alerts_enabled`',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Daily dedupe stamp ---------------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings'
       AND COLUMN_NAME = 'last_alert_slot') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_expiry_settings') > 0,
  'ALTER TABLE `db_expiry_settings` ADD COLUMN `last_alert_slot` DATE NULL AFTER `alert_phone`',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
