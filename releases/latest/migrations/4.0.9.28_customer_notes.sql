-- ============================================================================
-- MartPoint 4.0.9.28 — Customer notes history
-- Customer notes become append-only entries in db_customer_notes so every
-- note keeps its own author and timestamp (full history). Only the creator
-- of a note may edit it. The legacy db_customers.notes blob is seeded into
-- the history table once, then left untouched for backwards compatibility.
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `db_customer_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `store_id` INT NOT NULL DEFAULT 1,
    `customer_id` INT NOT NULL,
    `note` TEXT NOT NULL,
    `created_by` VARCHAR(255) NULL,
    `created_by_id` INT NULL,
    `created_date` DATE NULL,
    `created_time` VARCHAR(20) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_store_id` (`store_id`),
    INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed the legacy single notes blob into history (only once per note text)
INSERT INTO `db_customer_notes` (`store_id`, `customer_id`, `note`, `created_by`, `created_by_id`, `created_date`, `created_time`)
SELECT c.store_id, c.id, c.notes, c.created_by, NULL, c.created_date, c.created_time
FROM `db_customers` c
WHERE c.notes IS NOT NULL AND TRIM(c.notes) <> ''
  AND NOT EXISTS (
      SELECT 1 FROM `db_customer_notes` n
      WHERE n.customer_id = c.id AND n.note = c.notes
  );

SET FOREIGN_KEY_CHECKS = 1;
