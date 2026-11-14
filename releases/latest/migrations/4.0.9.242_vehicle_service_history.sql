-- Vehicle service history (v4.0.9.242).
--
-- A garage/parts shop needs more than the purchase list db_sales.vehicle_id
-- gives: what was done to the vehicle, when, at what mileage, by whom, and
-- when it is due back. Fitments stay as sales; this table records the service
-- story per vehicle and carries the explicitly configured next-due date or
-- mileage that date-driven reminders read.

SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `db_vehicle_service_records` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `customer_id` INT(11) DEFAULT NULL COMMENT 'Denormalised from db_vehicles for scoping',
  `sales_id` INT(11) DEFAULT NULL COMMENT 'Optional bill that carried the work/parts',
  `service_type` VARCHAR(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general'
    COMMENT 'tyre_rotation|wheel_alignment|wheel_balancing|battery_replacement|oil_change|plug_replacement|inspection|repair|general',
  `service_date` DATE NOT NULL,
  `mileage_at_service` INT(11) DEFAULT NULL COMMENT 'Odometer km when the work was done',
  `technician_id` INT(11) DEFAULT NULL COMMENT 'db_users.id when the technician has an account',
  `technician_name` VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Free-text technician when there is no account',
  `notes` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_due_date` DATE DEFAULT NULL COMMENT 'Explicitly configured by staff — never inferred from the sale date alone',
  `next_due_mileage` INT(11) DEFAULT NULL COMMENT 'Mileage is entered by staff at the next visit; reminders read dates only',
  `last_reminder_slot` VARCHAR(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Due stamp of the last queued reminder; a changed due date re-arms',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vsr_store` (`store_id`),
  KEY `idx_vsr_vehicle` (`store_id`,`vehicle_id`),
  KEY `idx_vsr_due` (`store_id`,`next_due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
