-- Secure customer response links for quotations. Tokens are stored as SHA-256
-- hashes; issuing a new link invalidates the previous one.
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotation' AND COLUMN_NAME='response_token_hash') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `response_token_hash` CHAR(64) NULL, ADD COLUMN `response_token_expires_at` DATETIME NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
