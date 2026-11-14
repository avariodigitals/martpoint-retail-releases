-- Batch reconciliation (v4.0.9.236)
--
-- The till, the invoice screen and the online store all draw from the batch
-- ledger when rotation is FEFO or FIFO, and they refuse a sale when that
-- ledger holds less than the line needs — even when the item's own stock
-- figure says otherwise. Until now the only way to close that disagreement
-- was a database edit, so a store with legacy rows could not sell at the
-- counter. This migration adds the records the new screen needs:
--  1. db_batch_reconciliations — one row per balancing decision: what each
--     side said, which side the operator declared correct, and the stock
--     adjustment document or batch row that was written to close the gap.
--  2. batch_reconciliation_view / batch_reconciliation_manage grants, carried
--     to whoever may already adjust stock. Idempotent, and never re-adds a
--     key an admin revoked.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD COLUMN IF NOT EXISTS. Nothing here touches a stock
-- figure — a balance is always a human decision recorded on screen.

SET @db := DATABASE();

-- 1. One ledger row per balancing decision ---------------------------------
CREATE TABLE IF NOT EXISTS `db_batch_reconciliations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL DEFAULT 0,
  `warehouse_id` INT NOT NULL DEFAULT 0,
  `item_id` INT NOT NULL DEFAULT 0,
  `count_qty` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `lot_qty` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `delta_qty` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `fix` VARCHAR(20) NOT NULL DEFAULT '',
  `barcode_id` INT NULL DEFAULT NULL,
  `adjustment_id` INT NULL DEFAULT NULL,
  `batch_lot` VARCHAR(100) NULL DEFAULT NULL,
  `expire_date` DATE NULL DEFAULT NULL,
  `reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_by` INT NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_brec_scope` (`store_id`,`warehouse_id`,`item_id`),
  KEY `idx_brec_time` (`store_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Grants for existing installs -----------------------------------------
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT p.`store_id`, p.`role_id`, s.`permissions`
FROM (SELECT DISTINCT `store_id`, `role_id` FROM `db_permissions` WHERE `permissions` = 'stock_adjustment_view') p
JOIN (
  SELECT 'batch_reconciliation_view' AS `permissions`
  UNION ALL SELECT 'batch_reconciliation_manage'
) s
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id` AND rv.`permissions` = s.`permissions`
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id` AND have.`permissions` = s.`permissions`
WHERE rv.`id` IS NULL AND have.`id` IS NULL;
