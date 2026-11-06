-- Oil & gas: record what actually arrives on a stock transfer.
--
-- A tanker that leaves with 3,000 L and arrives with 2,940 L must not post the
-- sent quantity as the receiving site's stock. received_qty stays NULL for the
-- legacy "post what was sent" behaviour, so existing transfers are untouched;
-- receipt_status carries the arrival state for stores that opt in.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransferitems' AND column_name = 'received_qty');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransferitems` ADD COLUMN `received_qty` DOUBLE(20,4) NULL COMMENT ''Quantity the receiving site counted in''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'receipt_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `receipt_status` VARCHAR(30) NOT NULL DEFAULT ''Posted'' COMMENT ''Posted, In Transit, Awaiting Approval, Received''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'received_by');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `received_by` VARCHAR(50) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'received_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `received_date` DATE NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'variance_reason');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `variance_reason` VARCHAR(255) NULL COMMENT ''Why the arriving quantity differs from the sent quantity''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'approval_log_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `approval_log_id` INT NULL COMMENT ''db_approval_logs row for an accepted shortfall''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_stocktransfer' AND column_name = 'approval_note');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_stocktransfer` ADD COLUMN `approval_note` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.248-transfer-receipt', NOW());
