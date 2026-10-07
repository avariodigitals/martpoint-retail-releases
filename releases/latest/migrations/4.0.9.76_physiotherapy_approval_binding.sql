-- ============================================================
-- MartPoint 4.0.9.76 — Physiotherapy hardening
-- db_approval_logs.target_version widened to VARCHAR(64) so
-- approval fingerprints can be real hashes (sha256) instead of
-- CRC32 packed into a signed INT (overflow-clamped + collision-
-- prone). Guarded/idempotent.
-- ============================================================

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_approval_logs'
    AND COLUMN_NAME = 'target_version' AND DATA_TYPE = 'int');
SET @s := IF(@c = 1,
  'ALTER TABLE `db_approval_logs` MODIFY `target_version` varchar(64) NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
