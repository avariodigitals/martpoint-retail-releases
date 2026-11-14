-- Store-controlled FEFO/FIFO rotation and auditable sale/return batch links.
-- MANUAL is the safe initial mode: legacy on-hand quantities are not silently
-- guessed into batches. A pharmacy admin can enable FEFO/FIFO after reconciling.
CREATE TABLE IF NOT EXISTS `db_inventory_rotation_settings` (
  `store_id` INT NOT NULL,
  `rotation_method` VARCHAR(10) NOT NULL DEFAULT 'MANUAL',
  `updated_by` INT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_salesitem_batch_allocations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sales_id` INT NOT NULL,
  `salesitems_id` INT NOT NULL,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NULL,
  `item_id` INT NOT NULL,
  `barcode_id` INT NOT NULL,
  `batch_lot` VARCHAR(100) NULL,
  `expire_date` DATE NULL,
  `qty_allocated` DECIMAL(20,4) NOT NULL,
  `qty_returned` DECIMAL(20,4) NOT NULL DEFAULT 0,
  `rotation_method` VARCHAR(10) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sale_item` (`sales_id`,`salesitems_id`),
  KEY `idx_batch_source` (`barcode_id`,`item_id`,`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_salesreturn_batch_allocations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `salesreturnitems_id` INT NOT NULL,
  `allocation_id` BIGINT UNSIGNED NOT NULL,
  `barcode_id` INT NOT NULL,
  `qty_returned` DECIMAL(20,4) NOT NULL,
  `return_status` VARCHAR(20) NOT NULL DEFAULT 'Return',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_return_item` (`salesreturnitems_id`),
  KEY `idx_return_allocation` (`allocation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
