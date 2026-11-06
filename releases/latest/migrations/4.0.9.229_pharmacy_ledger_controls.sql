-- Pharmacy ledger controls & dispensing gates (v4.0.9.229)
--
-- One additive migration for the readiness-audit fixes:
--  1. prescription_required on db_items — the catalogue flag that lets the
--     till refuse an Rx medicine without a linked, reviewed prescription.
--  2. db_purchaseitemsreturn_batch_allocations — which physical lot each
--     supplier return drained, so returns stop corrupting the batch ledger
--     and editing/deleting a return restores the exact lots.
--  3. db_recalls / db_recall_quarantine — freeze a batch across every
--     warehouse and keep the list of invoices it reached (traceability is
--     read live from db_salesitem_batch_allocations).
--  4. pos_idempotency_key on db_sales — an offline replay of the same till
--     sale lands once, not twice, for every store (not just restaurants).
--  5. db_stocktransfer_batch_allocations — which physical lot rows each
--     warehouse transfer moved, so edits and deletes restore the exact lots.
--  6. online_order_id on db_medical_notes — the online order that raised a
--     pending prescription review, so review can gate fulfilment.
--  7. recall_view / recall_add grants carried to existing stores.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD COLUMN IF NOT EXISTS. Nothing here rewrites history;
-- existing rows read back as "no Rx rule" and "never quarantined".

SET @db := DATABASE();

-- 1. Rx-required medicines ------------------------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_items' AND COLUMN_NAME='prescription_required')=0,
  'ALTER TABLE db_items ADD COLUMN `prescription_required` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Supplier returns -> original lot allocations ---------------------------
CREATE TABLE IF NOT EXISTS `db_purchaseitemsreturn_batch_allocations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL DEFAULT 0,
  `return_id` INT NOT NULL,
  `purchaseitemsreturn_id` INT NULL DEFAULT NULL,
  `barcode_id` INT NOT NULL,
  `qty` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `created_date` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ptr_alloc_return` (`return_id`),
  KEY `idx_ptr_alloc_barcode` (`barcode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Recalls and quarantine ----------------------------------------------
-- status 3 on db_item_barcodes means quarantined (2 is already used by
-- warranty-return review): every selling path filters on status = 1, so a
-- frozen lot is unsellable at POS and online.
CREATE TABLE IF NOT EXISTS `db_recalls` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `recall_code` VARCHAR(40) NOT NULL,
  `item_id` INT NOT NULL,
  `batch_lot` VARCHAR(100) NULL DEFAULT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `notes` TEXT NULL DEFAULT NULL,
  `initiated_by` VARCHAR(100) NULL DEFAULT NULL,
  `initiated_at` DATETIME NULL DEFAULT NULL,
  `closed_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_recall_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_recall_quarantine` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `recall_id` INT NOT NULL,
  `barcode_id` INT NOT NULL,
  `previously_sellable` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rq_recall` (`recall_id`),
  KEY `idx_rq_barcode` (`barcode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Offline replay protection for every till ------------------------------
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='pos_idempotency_key')=0,
  'ALTER TABLE db_sales ADD COLUMN `pos_idempotency_key` VARCHAR(64) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Unique per store; historical rows are NULL and MySQL keeps NULLs distinct
-- in unique indexes, so no existing sale conflicts with another.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='uniq_pos_idempotency')=0,
  'ALTER TABLE db_sales ADD UNIQUE INDEX `uniq_pos_idempotency` (`store_id`,`pos_idempotency_key`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. Stock transfers -> lot provenance --------------------------------------
-- A transfer drains lots in the source warehouse and rebuilds (or adds to)
-- matching lots in the destination. This table records exactly which physical
-- rows each transfer moved, so editing or deleting a transfer restores the
-- same lots instead of leaving the batch ledger short.
CREATE TABLE IF NOT EXISTS `db_stocktransfer_batch_allocations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `stocktransfer_id` INT NOT NULL,
  `stocktransferitems_id` INT NULL DEFAULT NULL,
  `item_id` INT NOT NULL,
  `warehouse_from` INT NOT NULL,
  `warehouse_to` INT NOT NULL,
  `source_barcode_id` INT NOT NULL,
  `dest_barcode_id` INT NOT NULL,
  `qty` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `created_date` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_st_alloc_transfer` (`stocktransfer_id`),
  KEY `idx_st_alloc_source` (`source_barcode_id`),
  KEY `idx_st_alloc_dest` (`dest_barcode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Online prescription requests ------------------------------------------
-- An online order containing Rx-only medicine records a pending prescription
-- review note; this column ties the note back to the order it came from.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND COLUMN_NAME='online_order_id')=0,
  'ALTER TABLE db_medical_notes ADD COLUMN `online_order_id` INT NULL DEFAULT NULL, ADD KEY `idx_mnote_online_order` (`online_order_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7. Recall permissions for existing installs -------------------------------
-- recall_view / recall_add gate the new Recalls & quarantine screen. Whoever
-- may already adjust stock may freeze medicine; the grant is idempotent and
-- never re-adds a key an admin revoked.
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT p.`store_id`, p.`role_id`, s.`permissions`
FROM (SELECT DISTINCT `store_id`, `role_id` FROM `db_permissions` WHERE `permissions` = 'stock_adjustment_view') p
JOIN (
  SELECT 'recall_view' AS `permissions`
  UNION ALL SELECT 'recall_add'
) s
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id` AND rv.`permissions` = s.`permissions`
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id` AND have.`permissions` = s.`permissions`
WHERE rv.`id` IS NULL AND have.`id` IS NULL;
