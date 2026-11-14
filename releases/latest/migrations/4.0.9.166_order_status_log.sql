-- MartPoint Order Status Machine (v4.0.9.166)
-- Every channel that takes an order — counter, Online Store, table QR — moves
-- that order through the same lifecycle, and each move is now written down.
--
-- Before this, the states lived in two unmanaged columns: an online order's
-- `order_status` and the kitchen ticket's `kds_status`. The kitchen screen
-- wrote its own column and a fixed map into the order's, with no rule about
-- what could follow what, so a served ticket could be pushed back to `new`,
-- and nothing recorded who moved an order or when. A dispute about whether a
-- meal was handed over had no evidence behind it.
--
-- This adds the record only. It deliberately adds NO new status values: the
-- lifecycle is expressed through the two columns businesses already have, so
-- retail, clinic and garden stores keep the states they and their shoppers
-- already see. See Order_lifecycle_model for how the states map.
--
-- Rollback: DROP TABLE `db_order_status_log`; and
-- ALTER TABLE `db_kitchen_orders` MODIFY `kds_status`
--   enum('new','preparing','ready','served') NOT NULL DEFAULT 'new';
-- (nothing reads the table or the new value before this migration's matching
--  release, so a rollback loses only the transition history recorded since.)
-- Idempotent. Safe to run again.

CREATE TABLE IF NOT EXISTS `db_order_status_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `online_order_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'db_online_orders.id; 0 for a counter order',
  `sales_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'db_sales.id once the order is a receipt; 0 while it is only an order',
  `kitchen_order_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'db_kitchen_orders.id for this order, if it ever reached a kitchen',
  `from_status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NULL COMMENT 'canonical state before, empty when the order was created',
  `to_status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'canonical state after',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `actor` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL COMMENT 'display name, never a username',
  `channel` VARCHAR(20) COLLATE utf8mb4_unicode_ci NULL COMMENT 'online|qr|counter|kds|system|cron',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_online_order` (`online_order_id`),
  KEY `idx_sales` (`sales_id`),
  KEY `idx_store_created` (`store_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The kitchen ticket needs one state the board cannot currently express: this
-- order is dead. Without it a cancelled table order stays on the pass, which
-- sends food out for a guest who has gone. Additive — no existing ticket
-- changes value, and every board query names the states it wants.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders'
              AND COLUMN_NAME='kds_status' AND COLUMN_TYPE LIKE '%cancelled%')=0
             AND (SELECT COUNT(*) FROM information_schema.TABLES
              WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders')=1,
  'ALTER TABLE `db_kitchen_orders` MODIFY `kds_status` enum(''new'',''preparing'',''ready'',''served'',''cancelled'') NOT NULL DEFAULT ''new''',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
