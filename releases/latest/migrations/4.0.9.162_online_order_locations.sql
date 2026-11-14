-- MartPoint Online Order Location (v4.0.9.162)
-- An online or table-QR order is placed against ONE outlet: a customer picks
-- which of the store's locations they are ordering from, and that location
-- cooks it, hands it over and loses the stock. Until now the order carried no
-- location at all, so every downstream surface (kitchen, reports, stock) had
-- to treat the whole store as one invisible counter.
--
-- Additive only: warehouse_id = 0 means "no location chosen", which is exactly
-- how existing orders behaved, so nothing changes for a store that has not
-- turned the picker on.
--
-- Rollback: ALTER TABLE db_online_orders DROP COLUMN warehouse_id;
-- (no code reads the column before this migration's matching release, so a
--  rollback costs nothing but the location choices recorded since.)
-- Idempotent. Safe to run again.

SET @db := DATABASE();

-- 1. The chosen outlet.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='warehouse_id')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `warehouse_id` INT(11) NOT NULL DEFAULT 0 AFTER `store_id`',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Outlet-filtered order lists are the whole point of the column.
-- A partially-applied older attempt can leave the same index name behind but
-- without warehouse_id (dropping a column may preserve the remaining index
-- prefix). Check the shape, not only the name, so re-running this migration
-- repairs that state instead of falsely reporting success.
SET @has_store_warehouse := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders'
    AND INDEX_NAME='idx_store_warehouse' AND COLUMN_NAME='warehouse_id');
SET @s := IF(@has_store_warehouse=0
  AND (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db
       AND TABLE_NAME='db_online_orders' AND INDEX_NAME='idx_store_warehouse')>0,
  'ALTER TABLE db_online_orders DROP INDEX `idx_store_warehouse`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF(@has_store_warehouse=0,
  'ALTER TABLE db_online_orders ADD INDEX `idx_store_warehouse` (`store_id`, `warehouse_id`)',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Honour the default the merchant already set — but only where it cannot be
--    a guess. Online Store settings have carried a "Default Branch" for a while
--    and nothing ever applied it to an order. For a store with one selling
--    location that is simply where those orders were made, so it is recorded.
--    For a store with several, no evidence exists for orders taken before the
--    picker opened, and attributing them all to whichever branch is the default
--    today would quietly rewrite each outlet's history. Those stay unassigned
--    and read "No outlet" until a merchant moves one deliberately.
UPDATE `db_online_orders` o
  JOIN `db_storefront_settings` s ON s.`store_id` = o.`store_id`
  JOIN `db_warehouse` w ON w.`id` = s.`default_branch_id` AND w.`store_id` = o.`store_id`
  JOIN (SELECT `store_id`, COUNT(*) AS active_branches FROM `db_warehouse` WHERE `status` = 1 GROUP BY `store_id`) one
    ON one.`store_id` = o.`store_id` AND one.`active_branches` = 1
  SET o.`warehouse_id` = s.`default_branch_id`
WHERE o.`warehouse_id` = 0 AND s.`default_branch_id` > 0;
