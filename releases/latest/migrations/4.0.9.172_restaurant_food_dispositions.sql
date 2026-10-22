-- RestaurantOS M3: prepared-food cancellation/refund disposition.
-- Additive and repeatable. Prepared or wasted food must never be returned to
-- sellable stock; the existing reservation/commit movement remains the stock
-- source of truth and this table records the non-restockable disposition.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='stock_state')=1,
  'ALTER TABLE db_online_orders MODIFY COLUMN `stock_state` ENUM(''none'',''reserved'',''committed'',''released'',''wasted'') NOT NULL DEFAULT ''none''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `db_order_stock_dispositions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `online_order_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `quantity` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `disposition` VARCHAR(20) NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_stock_disposition` (`store_id`,`online_order_id`,`item_id`,`disposition`),
  KEY `idx_stock_disposition_order` (`store_id`,`online_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
