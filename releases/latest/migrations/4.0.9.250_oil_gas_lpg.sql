-- Oil & gas: LPG — the cylinder is the tank, and the scale is the meter.
--
-- A cooking-gas business sells a weight, not a count. Every sale passes over a
-- scale twice: the empty cylinder has a tare stamped on its collar, the filled
-- one is weighed gross, and the difference is the gas the customer pays for. The
-- cylinder itself is a hired shell the shop owns, it walks out with a customer
-- and it is expected back, and the money held against it is a deposit — not a
-- sale, and not stock.
--
-- Like the forecourt, this is a measurement and custody layer, NOT a second
-- stock ledger. The bulk gas left the store's stock when the till rung the
-- refill up; deducting it again here would double count and the books would
-- never close. So:
--   db_fuel_lpg_refill    records what the scale said (gross - tare = net kg).
--   db_fuel_cylinder      is one shell: its size, its own weight, and who holds
--                         it right now. A code is unique per company, because
--                         two shells with the same stamp cannot be told apart
--                         when one of them walks out.
--   db_fuel_cylinder_movement is the shell's history — every time it changed
--                         hands or state, in one line, signed by whoever did it.
--   db_fuel_cylinder_deposit  is money-only. Paid and refunded rows net to what
--                         the shop owes a customer; no stock row is ever
--                         involved, so a deposit can never look like a sale.
--
-- Every table here is new and every one is keyed by store_id, so a store that
-- runs no LPG is untouched by this file.

CREATE TABLE IF NOT EXISTS `db_fuel_cylinder` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL DEFAULT 0 COMMENT 'The site this cylinder normally stands at',
  `cylinder_code` VARCHAR(60) NOT NULL COMMENT 'The number stamped on the shell',
  `item_id` INT NOT NULL DEFAULT 0 COMMENT 'Which gas it is filled with (db_items.id)',
  `size_kg` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'Net gas it is filled to, e.g. 12.5',
  `tare_kg` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'What the empty shell itself weighs',
  `status` ENUM('in_shop','out','lost','scrapped') NOT NULL DEFAULT 'in_shop' COMMENT 'Who is holding it',
  `is_full` TINYINT NOT NULL DEFAULT 0 COMMENT '1 when it has been refilled and is ready to go out',
  `customer_id` INT NOT NULL DEFAULT 0 COMMENT 'Who holds it while status is out',
  `note` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_date` DATE NULL,
  `last_movement_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fuel_cylinder_code` (`store_id`,`cylinder_code`),
  KEY `idx_fuel_cylinder_site` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One line every time a shell changes state or changes hands.
CREATE TABLE IF NOT EXISTS `db_fuel_cylinder_movement` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `cylinder_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL DEFAULT 0,
  `movement_type` VARCHAR(20) NOT NULL COMMENT 'registered, refilled, issued, returned, given, received, lost, scrapped',
  `customer_id` INT NULL,
  `net_kg` DOUBLE(20,4) NULL COMMENT 'Gas weighed for this event, when there was a weighing',
  `from_status` VARCHAR(12) NULL,
  `to_status` VARCHAR(12) NULL,
  `refill_id` INT NULL,
  `deposit_id` INT NULL,
  `reason` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_cyl_move` (`store_id`,`cylinder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- What the scale said. Net is always gross minus the shell's own tare.
CREATE TABLE IF NOT EXISTS `db_fuel_lpg_refill` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL DEFAULT 0,
  `cylinder_id` INT NOT NULL,
  `item_id` INT NOT NULL DEFAULT 0,
  `gross_kg` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'Shell plus gas, as it sat on the scale',
  `tare_kg` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'The shell weight that was subtracted',
  `net_kg` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'The gas the customer is charged for',
  `refill_date` DATE NOT NULL,
  `refill_time` VARCHAR(10) NULL,
  `sale_reference` VARCHAR(60) NULL COMMENT 'The till receipt this refill was rung up on',
  `linked_sale_id` INT NULL COMMENT 'The db_sales row that receipt turned out to be',
  `note` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lpg_refill_site` (`store_id`,`warehouse_id`,`refill_date`),
  KEY `idx_lpg_refill_cylinder` (`cylinder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Money held against a shell. Paid and refunded rows net to the liability; this
-- table never touches a stock or an item row.
CREATE TABLE IF NOT EXISTS `db_fuel_cylinder_deposit` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL DEFAULT 0,
  `cylinder_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `amount` DOUBLE(20,2) NOT NULL DEFAULT 0,
  `event_type` ENUM('paid','refunded') NOT NULL DEFAULT 'paid',
  `payment_mode` VARCHAR(40) NULL,
  `note` VARCHAR(255) NULL,
  `deposit_date` DATE NOT NULL,
  `created_by` VARCHAR(50) NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lpg_deposit_customer` (`store_id`,`customer_id`),
  KEY `idx_lpg_deposit_cylinder` (`cylinder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.250-oil-gas-lpg', NOW());
