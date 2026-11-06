-- Online batch holds (v4.0.9.237)
--
-- When a shop sells from the batch ledger (rotation on FEFO or FIFO), the
-- counter, the invoice screen and the online store all draw the SAME lots. The
-- online store reserved the stock figure and the branch's own count at checkout,
-- but it left the batch ledger untouched until the order was rung up — which
-- happens after payment. In that window a till sale could walk away with units
-- an online customer had already reserved, and the paid order then had no lot
-- left to draw: money taken, no invoice the shop can raise.
--
-- This adds the record that closes the window:
--   db_onlineorder_batch_allocations — one row per lot an open online order is
--   holding, with the exact quantity taken off that lot at reservation time.
--   The row becomes the sale's batch provenance when the order is posted, and
--   the quantity goes back to the lot when the order is cancelled, refunded or
--   left unpaid past its window.
--
-- information_schema guards + PREPARE/EXECUTE, house style: MySQL 5.7 and
-- MariaDB have no ADD COLUMN IF NOT EXISTS. Nothing here moves a quantity —
-- existing open orders have no holds, and their lines are allocated from the
-- ledger when they post, exactly as before.

SET @db := DATABASE();

-- 1. One ledger row per lot held by an open online order -------------------
CREATE TABLE IF NOT EXISTS `db_onlineorder_batch_allocations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `online_order_id` INT NOT NULL DEFAULT 0,
  `online_order_item_id` INT NOT NULL DEFAULT 0,
  `store_id` INT NOT NULL DEFAULT 0,
  `warehouse_id` INT NOT NULL DEFAULT 0,
  `item_id` INT NOT NULL DEFAULT 0,
  `barcode_id` INT NOT NULL DEFAULT 0,
  `batch_lot` VARCHAR(100) NULL DEFAULT NULL,
  `expire_date` DATE NULL DEFAULT NULL,
  `qty_allocated` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `rotation_method` VARCHAR(10) NOT NULL DEFAULT '',
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_obhold_order` (`online_order_id`,`online_order_item_id`),
  KEY `idx_obhold_scope` (`store_id`,`warehouse_id`,`item_id`),
  KEY `idx_obhold_lot` (`barcode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
