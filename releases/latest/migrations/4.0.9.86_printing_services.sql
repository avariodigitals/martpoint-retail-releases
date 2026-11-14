-- ============================================================================
-- MartPoint 4.0.9.86 — Printing: configurable services (design is one of many)
--
-- Design must NOT be hardcoded. This adds a configurable service-type registry
-- and a generic per-line service table. Each service separates a CUSTOMER CHARGE
-- from an INTERNAL COST, supports discount/waiver with a full audit, and keeps
-- the internal cost when the customer charge is waived.
--
-- Financial posting rides the existing ledger via caller code; these tables hold
-- the service definition + audit, not the money movement.
--
-- Idempotent. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Configurable service types (built-ins ship in code; these are store extras)
CREATE TABLE IF NOT EXISTS `db_print_service_types` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `service_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode_aware` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = exposes the design-mode choices (customer_supplied/new_design/…)',
  `icon` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT 'fa-plus',
  `sort_order` INT(5) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_type` (`store_id`,`service_key`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generic per-line services (design = service_key 'design')
CREATE TABLE IF NOT EXISTS `db_print_item_services` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NOT NULL,
  `service_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'design',
  `design_mode` VARCHAR(24) COLLATE utf8mb4_unicode_ci NULL COMMENT 'only when the type is mode-aware',
  `instructions` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `assignee_id` INT(11) NULL COMMENT 'assigned staff / designer / installer',
  `expected_date` DATE NULL,
  `source_artwork_id` INT(11) NULL,
  -- customer-facing charge
  `charge_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `charge_waived` TINYINT(1) NOT NULL DEFAULT 0,
  `waived_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `waiver_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `waived_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `waived_at` DATETIME NULL,
  -- internal cost (never erased by a waiver)
  `internal_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `internal_cost_basis` VARCHAR(120) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`job_id`,`line_id`),
  KEY `idx_type` (`service_key`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed a couple of common extras for any store that already has print categories
INSERT IGNORE INTO `db_print_service_types` (`store_id`,`service_key`,`label`,`mode_aware`,`icon`,`sort_order`)
SELECT DISTINCT store_id, 'installation', 'Installation', 0, 'fa-wrench', 10 FROM `db_print_categories`;

SET FOREIGN_KEY_CHECKS = 1;
