-- ============================================================================
-- MartPoint 4.0.9.26 — Bottling Runs
-- Records each filling event against a blend batch: how many bottles were
-- filled, which packaging item was consumed, and how much bulk liquid was
-- drawn. Every run posts one stock adjustment (bottles out, bulk out,
-- finished units in) so inventory and the ledger never diverge.
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `db_bottling_runs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL DEFAULT 0,
  `batch_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'db_production_batches id this fill belongs to',
  `product_item_id` INT(11) NOT NULL COMMENT 'db_items id of the finished bottled SKU stocked in',
  `product_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bottle_item_id` INT(11) NOT NULL COMMENT 'db_items id of the empty bottle/packaging consumed',
  `bottle_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bulk_item_id` INT(11) DEFAULT NULL COMMENT 'db_items id of the bulk liquid drawn down (optional)',
  `bulk_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fill_qty` DECIMAL(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Volume per bottle, in the bulk item base unit (e.g. ml)',
  `bottles_filled` DECIMAL(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Number of bottles filled',
  `bulk_used` DECIMAL(15,3) NOT NULL DEFAULT 0.000 COMMENT 'fill_qty x bottles_filled',
  `unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Bulk + bottle cost per finished unit',
  `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'unit_cost x bottles_filled',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment id created for this run',
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  `created_by` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_product` (`product_item_id`),
  KEY `idx_date` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
