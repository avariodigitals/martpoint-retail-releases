-- 4.0.9.77 — Phase 3 hardening: campaign suppression, send claiming,
-- unsub tokens, stock-alert claim timestamps.
-- Idempotent: CREATE IF NOT EXISTS + guarded ALTERs.

-- Recipient-level marketing opt-out. Channel 'email'/'sms' or 'all'.
-- A row here is authoritative: send-time checks consult it so a customer
-- who opts out AFTER a campaign is staged is still never contacted.
CREATE TABLE IF NOT EXISTS `db_marketing_suppressions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `channel` VARCHAR(20) NOT NULL DEFAULT 'all',
  `recipient` VARCHAR(191) NOT NULL,
  `source` VARCHAR(30) NOT NULL DEFAULT 'link',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_msupp` (`store_id`, `channel`, `recipient`),
  KEY `idx_msupp_recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Send-row hardening: unsub_token carries the public unsubscribe link,
-- claimed_at timestamps the atomic send claim (stranded claims recover),
-- attempts counts provider-call retries.
SET @t := 'db_campaign_sends';
SET @c := 'unsub_token';
SET @q := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = @c) = 0,
  'ALTER TABLE db_campaign_sends ADD COLUMN `unsub_token` VARCHAR(64) NULL DEFAULT NULL, ADD KEY `idx_csend_unsub` (`unsub_token`)',
  'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;

SET @c := 'claimed_at';
SET @q := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = @c) = 0,
  'ALTER TABLE db_campaign_sends ADD COLUMN `claimed_at` DATETIME NULL DEFAULT NULL, ADD COLUMN `attempts` SMALLINT NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE s FROM @q; EXECUTE s; DEALLOCATE PREPARE s;
