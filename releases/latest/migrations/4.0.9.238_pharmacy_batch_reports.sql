-- Batch ledger reports (v4.0.9.238)
--
-- Pharmacy staff could already see an item's total stock and its expired
-- items, but nothing that answered "how much of this medicine is in batch
-- X, what sold from it, and how long has it been on the shelf". That is the
-- report a pharmacist needs before ordering, before a recall, and before
-- writing off stock. This migration adds no data of its own — the report
-- screen reads the ledgers that already exist (db_item_barcodes,
-- db_salesitem_batch_allocations, db_sales) — it only:
--  1. grants pharmacy_batch_report_view to whoever may already read the batch
--     ledger or the expired-items report. Idempotent, and never re-adds a key
--     an admin revoked.
--  2. adds the indexes those reports lean on. A lot-ledger scan filters by
--     location and status, the expiry report filters by expire_date, and the
--     sold-by-batch report is scoped by store and location; without these the
--     screens degrade with the size of the ledger, not the size of the shop.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD INDEX IF NOT EXISTS. Nothing here moves a quantity.

SET @db := DATABASE();

-- 1. Grants for existing installs -----------------------------------------
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT p.`store_id`, p.`role_id`, 'pharmacy_batch_report_view'
FROM (
  SELECT DISTINCT `store_id`, `role_id` FROM `db_permissions`
  WHERE `permissions` IN ('batch_reconciliation_view', 'expired_items_report')
) p
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id`
     AND rv.`permissions` = 'pharmacy_batch_report_view'
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id`
     AND have.`permissions` = 'pharmacy_batch_report_view'
WHERE rv.`id` IS NULL AND have.`id` IS NULL;

-- 2. Location-scoped lot reads --------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_item_barcodes'
       AND INDEX_NAME = 'idx_lot_wh_status') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_item_barcodes') > 0,
  'ALTER TABLE `db_item_barcodes` ADD KEY `idx_lot_wh_status` (`warehouse_id`,`status`,`item_id`)',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Expiry-dated reads ----------------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_item_barcodes'
       AND INDEX_NAME = 'idx_lot_expiry') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_item_barcodes') > 0,
  'ALTER TABLE `db_item_barcodes` ADD KEY `idx_lot_expiry` (`expire_date`)',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Sold-by-batch reads --------------------------------------------------
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_salesitem_batch_allocations'
       AND INDEX_NAME = 'idx_alloc_store_wh') = 0
  AND (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_salesitem_batch_allocations') > 0,
  'ALTER TABLE `db_salesitem_batch_allocations` ADD KEY `idx_alloc_store_wh` (`store_id`,`warehouse_id`,`sales_id`)',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
