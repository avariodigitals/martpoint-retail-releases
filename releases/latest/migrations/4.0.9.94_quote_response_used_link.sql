-- ============================================================================
-- MartPoint 4.0.9.94 — Quotation response link: recognize already-used links
--
-- Quote_response::respond() clears db_quotation.response_token_hash once a
-- customer answers, so a customer who re-opens the emailed link from their
-- inbox previously hit a bare 404. We now keep a fingerprint of the token on
-- the lifecycle log so the controller can recognise a consumed link and show a
-- friendly "already responded / show recorded outcome" page instead.
--
-- The fingerprint is a SHA-256 of the raw token (same value already stored in
-- db_quotation.response_token_hash) — it cannot be reversed to the live token,
-- and adding the actor's IP keeps it from being a plain token lookup.
--
-- Idempotent.
-- ============================================================================

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation_lifecycle_log'
       AND COLUMN_NAME = 'response_token_fingerprint') = 0,
  'ALTER TABLE `db_quotation_lifecycle_log` ADD COLUMN `response_token_fingerprint` CHAR(64) NULL COMMENT ''sha256 of the response token that produced this entry''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_quotation_lifecycle_log'
       AND INDEX_NAME = 'idx_token_fp') = 0,
  'ALTER TABLE `db_quotation_lifecycle_log` ADD KEY `idx_token_fp` (`response_token_fingerprint`)',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
