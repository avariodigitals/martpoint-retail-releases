-- Oil & gas: the forecourt — tanks, pumps, the meter readings, and the shift that ties them together.
--
-- A filling station dispenses from a physical tank through a pump whose meter
-- only ever goes up, and the day is proved by comparing three accounts that no
-- single one of them can fake: what the pump measured, what the till rang up,
-- and what a dip says is actually left in the tank.
--
-- Nothing here touches db_warehouseitems. The till already books the sale, so
-- this is a measurement and reconciliation layer, NOT a second stock ledger —
-- double counting would be worse than no forecourt at all. Every table is new,
-- so a store that does not run a forecourt is untouched by this file.
--
-- db_fuel_tank_book: the expected litres in a tank are fills minus what the
-- meters pumped minus written-off losses, plus any correction a manager signed
-- for. That single rule is what the dip is checked against.

CREATE TABLE IF NOT EXISTS `db_fuel_tank` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL COMMENT 'The site this tank stands on',
  `tank_name` VARCHAR(120) NOT NULL,
  `item_id` INT NOT NULL DEFAULT 0 COMMENT 'Product the tank holds (db_items.id)',
  `capacity_litres` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'Nominal capacity, for overfill protection',
  `safe_fill_litres` DOUBLE(20,4) NULL COMMENT 'Highest the tank may be filled to (usually 90-95% of capacity)',
  `status` TINYINT NOT NULL DEFAULT 1,
  `note` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_date` DATE NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_tank_site` (`store_id`,`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Every movement of litres in or out of a tank. Fills come from a counted-in
-- load or a hand entry; corrections and write-offs are the manager-signed side.
CREATE TABLE IF NOT EXISTS `db_fuel_tank_movement` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `tank_id` INT NOT NULL,
  `item_id` INT NOT NULL DEFAULT 0,
  `litres` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'Signed: positive arrives, negative leaves',
  `movement_type` VARCHAR(20) NOT NULL DEFAULT 'fill' COMMENT 'fill, metered, correction, write_off',
  `transfer_id` INT NULL COMMENT 'db_stocktransfer row when a counted-in load filled this tank',
  `dip_id` INT NULL COMMENT 'db_fuel_dip row when a physical check drove this line',
  `shift_id` INT NULL COMMENT 'db_fuel_shift row when pump meters drove this line',
  `reason` VARCHAR(255) NULL,
  `approval_log_id` INT NULL COMMENT 'db_approval_logs row when a manager signed for it',
  `created_by` VARCHAR(50) NULL,
  `movement_date` DATE NOT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_movement_tank` (`store_id`,`tank_id`),
  KEY `idx_fuel_movement_transfer` (`transfer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A dip reading: the physical truth about one tank at one moment.
CREATE TABLE IF NOT EXISTS `db_fuel_dip` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `tank_id` INT NOT NULL,
  `item_id` INT NOT NULL DEFAULT 0,
  `level_litres` DOUBLE(20,4) NOT NULL COMMENT 'Litres the dip (or automatic gauge) says are in the tank',
  `book_litres` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'What the tank was expected to hold when the dip was taken',
  `dip_date` DATE NOT NULL,
  `dip_time` VARCHAR(10) NULL,
  `decision_status` ENUM('none','awaiting','accepted','declined') NOT NULL DEFAULT 'none' COMMENT 'awaiting = over tolerance, so nothing is written off until a manager signs',
  `approval_log_id` INT NULL,
  `note` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_dip_tank` (`store_id`,`tank_id`,`dip_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A pump (or a nozzle treated as its own dispenser) and the product it gives.
CREATE TABLE IF NOT EXISTS `db_fuel_pump` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL,
  `tank_id` INT NOT NULL DEFAULT 0 COMMENT 'Which tank it draws from',
  `item_id` INT NOT NULL DEFAULT 0,
  `pump_name` VARCHAR(120) NOT NULL,
  `opening_meter` DOUBLE(20,3) NOT NULL DEFAULT 0 COMMENT 'The meter face when the pump was registered; meters only climb from here',
  `status` TINYINT NOT NULL DEFAULT 1,
  `note` VARCHAR(255) NULL,
  `created_by` VARCHAR(50) NULL,
  `created_date` DATE NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_pump_site` (`store_id`,`warehouse_id`),
  KEY `idx_fuel_pump_tank` (`tank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One duty on one site. Mirrors the cashier shift shape: opened, closed,
-- expected against counted, variance, and a manager's decision when the
-- difference is bigger than the company allows.
CREATE TABLE IF NOT EXISTS `db_fuel_shift` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `warehouse_id` INT NOT NULL,
  `shift_code` VARCHAR(50) NULL,
  `status` ENUM('open','closed','void') NOT NULL DEFAULT 'open',
  `opened_at` DATETIME NOT NULL,
  `opened_by` INT NULL,
  `opened_by_name` VARCHAR(100) NULL,
  `closed_at` DATETIME NULL,
  `closed_by` INT NULL,
  `closed_by_name` VARCHAR(100) NULL,
  `litres_metered` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'Sum of every pump meter advanced during the duty',
  `litres_till` DOUBLE(20,4) NOT NULL DEFAULT 0 COMMENT 'What the till rang up for the same window',
  `variance_litres` DOUBLE(20,4) NOT NULL DEFAULT 0,
  `variance_value` DOUBLE(20,4) NOT NULL DEFAULT 0,
  `variance_percent` DOUBLE(8,3) NOT NULL DEFAULT 0,
  `approval_status` ENUM('not_required','pending','approved','rejected') NOT NULL DEFAULT 'not_required',
  `approval_log_id` INT NULL,
  `variance_reason` VARCHAR(255) NULL,
  `close_note` TEXT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fuel_shift_site` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The counters for one pump on one duty. The pair is what makes a forecourt
-- auditable: nobody can move today's litres into yesterday's shift.
CREATE TABLE IF NOT EXISTS `db_fuel_meter` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `shift_id` INT NOT NULL,
  `pump_id` INT NOT NULL,
  `item_id` INT NOT NULL DEFAULT 0,
  `opening_meter` DOUBLE(20,3) NOT NULL DEFAULT 0,
  `closing_meter` DOUBLE(20,3) NULL,
  `litres_pumped` DOUBLE(20,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fuel_meter_shift_pump` (`shift_id`,`pump_id`),
  KEY `idx_fuel_meter_pump` (`store_id`,`pump_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.249-oil-gas-forecourt', NOW());
