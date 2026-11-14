-- Voids, compliments and waiter attribution (v4.0.9.230) — Bar/Lounge floor work.
--
-- A lounge runs on three things the ledger could only guess at before now:
--
--   * A round that never reached the guest. The waiter punched three bottles,
--     two went back on the shelf and one broke. The bill must stop carrying
--     their money AND the stock must go back. A cancellation does neither in
--     those words, and wastage moves stock without touching the bill, so all
--     three were being recorded as the same event. 'Voided' is now its own
--     sales_status, which every revenue query already excludes by filtering
--     sales_status='Final' — the money reverses without a second ledger.
--
--   * A complimentary round. The house gives it, the stock still leaves the
--     shelf and the cost still lands on COGS — only the charge disappears.
--     Marking it as a discount instead would hide it inside the discount
--     figures and make a free pour look like a price reduction.
--
-- Every column is nullable and additive. Rows written before this migration
-- read back as "not recorded"; no historical invoice, line or movement is
-- rewritten, so ordinary Retail and Restaurant invoices are untouched.
--
-- Waiter attribution, tips and the optional service charge ship in 4.0.9.231.

SET @db := DATABASE();

-- ── The round-level void ────────────────────────────────────────────────────
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='voided_at')=0,
  'ALTER TABLE db_sales ADD COLUMN `voided_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='voided_by')=0,
  'ALTER TABLE db_sales ADD COLUMN `voided_by` VARCHAR(50) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='void_reason')=0,
  'ALTER TABLE db_sales ADD COLUMN `void_reason` VARCHAR(255) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── The line-level compliment ───────────────────────────────────────────────
-- comp_amount is the charge that was given away. It is stored rather than
-- derived because a later edit of the invoice would otherwise erase the
-- evidence of what the house actually spent on the table.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='comp_bit')=0,
  'ALTER TABLE db_salesitems ADD COLUMN `comp_bit` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='comp_amount')=0,
  'ALTER TABLE db_salesitems ADD COLUMN `comp_amount` DOUBLE(20,4) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='comp_by')=0,
  'ALTER TABLE db_salesitems ADD COLUMN `comp_by` VARCHAR(50) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='comp_reason')=0,
  'ALTER TABLE db_salesitems ADD COLUMN `comp_reason` VARCHAR(255) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='comp_at')=0,
  'ALTER TABLE db_salesitems ADD COLUMN `comp_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Compliments are reported per store over a period, so they are indexed behind
-- the store that gave them. Ordinary index, no foreign key: the actor is a
-- username string like created_by, and a renamed account must not orphan the
-- record of who gave the round away.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND INDEX_NAME='idx_salesitem_comp')=0,
  'ALTER TABLE db_salesitems ADD INDEX `idx_salesitem_comp` (`store_id`,`comp_bit`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
