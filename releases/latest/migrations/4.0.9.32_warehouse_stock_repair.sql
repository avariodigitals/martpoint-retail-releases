-- ============================================================================
-- MartPoint 4.0.9.32 — warehouse + stock ledger repair
-- The Stock Report reads db_warehouseitems (per-warehouse ledger), which is
-- rebuilt from transactions by warehouse id. Two legacy gaps break it:
--   1. Stores with NO row in db_warehouse (older installs predating the
--      warehouse feature) — every warehouse-scoped stock query sums 0, so the
--      report is empty even though db_items.stock shows quantities.
--   2. Transactions written with NULL warehouse_id (opening stock and
--      purchases/sales saved before the column existed) — invisible to the
--      per-warehouse stock math.
-- This file seeds a default System warehouse per store and backfills NULL
-- warehouse ids so history can rebuild the ledger. Idempotent.
-- NOTE: no DELIMITER/stored procedures — runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Every store gets an active 'System' warehouse if it has none.
INSERT INTO `db_warehouse`
  (`store_id`, `warehouse_type`, `warehouse_name`, `branch_type`, `is_distribution_center`, `mobile`, `email`, `status`)
SELECT s.id, 'System', CONCAT('Warehouse-', UPPER(SUBSTRING(s.store_code,1,6))), 'branch', 0, '', '', 1
FROM `db_store` s
WHERE NOT EXISTS (
  SELECT 1 FROM `db_warehouse` w WHERE w.store_id = s.id AND w.status = 1
);

-- Fallback: stores referenced by items but missing from db_store entirely.
INSERT INTO `db_warehouse`
  (`store_id`, `warehouse_type`, `warehouse_name`, `branch_type`, `is_distribution_center`, `mobile`, `email`, `status`)
SELECT DISTINCT i.store_id, 'System', 'Warehouse-A', 'branch', 0, '', '', 1
FROM `db_items` i
WHERE i.store_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `db_warehouse` w WHERE w.store_id = i.store_id AND w.status = 1
  );

-- Backfill NULL/0 warehouse_id on stock adjustments (incl. opening stock rows)
-- to the store's System warehouse so they rebuild into the ledger.
UPDATE `db_stockadjustment` a
JOIN `db_warehouse` w ON w.store_id = a.store_id AND w.warehouse_type = 'System'
SET a.warehouse_id = w.id
WHERE a.warehouse_id IS NULL OR a.warehouse_id = 0;

UPDATE `db_stockadjustmentitems` ai
JOIN `db_warehouse` w ON w.store_id = ai.store_id AND w.warehouse_type = 'System'
SET ai.warehouse_id = w.id
WHERE ai.warehouse_id IS NULL OR ai.warehouse_id = 0;

-- Same for purchase / sales / return headers: the warehouse stock math joins
-- on b.warehouse_id, so NULL headers are invisible to it.
UPDATE `db_purchase` p
JOIN `db_warehouse` w ON w.store_id = p.store_id AND w.warehouse_type = 'System'
SET p.warehouse_id = w.id
WHERE p.warehouse_id IS NULL OR p.warehouse_id = 0;

UPDATE `db_purchasereturn` p
JOIN `db_warehouse` w ON w.store_id = p.store_id AND w.warehouse_type = 'System'
SET p.warehouse_id = w.id
WHERE p.warehouse_id IS NULL OR p.warehouse_id = 0;

UPDATE `db_sales` s
JOIN `db_warehouse` w ON w.store_id = s.store_id AND w.warehouse_type = 'System'
SET s.warehouse_id = w.id
WHERE s.warehouse_id IS NULL OR s.warehouse_id = 0;

UPDATE `db_salesreturn` s
JOIN `db_warehouse` w ON w.store_id = s.store_id AND w.warehouse_type = 'System'
SET s.warehouse_id = w.id
WHERE s.warehouse_id IS NULL OR s.warehouse_id = 0;

SET FOREIGN_KEY_CHECKS = 1;
