-- ============================================================================
-- 4.0.9.148 — Referral configuration: reasons, urgency statuses, partner sync keys
--
-- Referring a patient used to mean typing the destination and the reason by
-- hand every time. This makes both selectable and configurable:
--
--   * db_referral_options holds the store's own lists — the referral reasons
--     and the urgency statuses ("Routine", "Urgent", "Emergency"). The reason
--     is a dropdown with an "Other" write-in; the urgency list carries one
--     default, so a new referral starts on the status the facility chooses
--     instead of on a hard-coded first row. Staff edit the wording on the
--     Referral partners screen; the seed values live in Referrals_model so
--     the lists and the screens can never drift apart.
--   * db_referral_partners gains source_system / external_key / is_seeded so a
--     future shared-directory sync can upsert partners by an external id
--     instead of duplicating them. Nothing syncs yet; the keys are here so the
--     table does not have to be rebuilt later.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_referral_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `group_code` varchar(20) NOT NULL,            -- reason|urgency
  `code` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refopt` (`store_id`,`group_code`,`code`),
  KEY `ix_refopt` (`store_id`,`group_code`,`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Future shared-directory sync hooks. Guarded so re-running is a no-op.
SET @mp_col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_referral_partners' AND COLUMN_NAME = 'source_system');
SET @mp_sql = IF(@mp_col = 0,
  'ALTER TABLE `db_referral_partners` ADD COLUMN `source_system` varchar(40) DEFAULT NULL AFTER `notes`',
  'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_referral_partners' AND COLUMN_NAME = 'external_key');
SET @mp_sql = IF(@mp_col = 0,
  'ALTER TABLE `db_referral_partners` ADD COLUMN `external_key` varchar(80) DEFAULT NULL AFTER `source_system`',
  'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_referral_partners' AND COLUMN_NAME = 'is_seeded');
SET @mp_sql = IF(@mp_col = 0,
  'ALTER TABLE `db_referral_partners` ADD COLUMN `is_seeded` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_active`',
  'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_col = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_referral_partners' AND INDEX_NAME = 'uk_refpart_ext');
SET @mp_sql = IF(@mp_col = 0,
  'ALTER TABLE `db_referral_partners` ADD UNIQUE KEY `uk_refpart_ext` (`store_id`,`source_system`,`external_key`)',
  'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
