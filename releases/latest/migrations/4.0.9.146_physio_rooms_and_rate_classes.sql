-- ============================================================================
-- 4.0.9.146 — Physiotherapy rooms and room-class daily rates
--
-- Daily ward billing used to be priced per BED: one facility-wide 'bed' rate
-- with an optional per-bed override. A facility that prices by room category
-- (Private AC, Private Non-AC, Semi-Private, General Ward) had no way to say
-- that, so every category had to be faked with per-bed overrides.
--
-- This migration adds the missing middle layer:
--
--   room class  = the price category ("Private - AC")
--   room        = the physical room, which belongs to a ward and has a class
--   bed         = sits in a room (optionally — open wards may keep beds loose)
--
-- The nightly charge for a bed-day prices from the bed's room class, keeping
-- the versioned db_daily_rates history and the per-bed override for exceptions.
-- A 'bed' rate row carrying a room_class_id is that class's price; the rows
-- without a class stay as the facility-wide fallback, so every install keeps
-- billing exactly as before until rooms are organised.
--
-- No prices are seeded: a rate is money, and money is never guessed. The room
-- classes themselves are seeded so the pick-list exists on day one.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_room_classes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `class_code` varchar(30) NOT NULL,
  `class_name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_room_class` (`store_id`,`class_code`),
  KEY `ix_room_class_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_rooms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `ward_id` int unsigned NOT NULL,
  `room_class_id` int unsigned NOT NULL,
  `room_label` varchar(60) NOT NULL,
  `capacity` tinyint unsigned NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active', -- active|maintenance|closed
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_room` (`ward_id`,`room_label`),
  KEY `ix_room_store` (`store_id`,`status`),
  KEY `ix_room_class` (`store_id`,`room_class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Bed -> room. NULL keeps open-ward beds working on the facility-wide rate.
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_beds' AND COLUMN_NAME='room_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_beds ADD COLUMN room_id int unsigned NULL AFTER ward_id, ADD KEY ix_bed_room (store_id,room_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

-- Class-aware daily rates: a 'bed' rate scoped to one room class.
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_daily_rates' AND COLUMN_NAME='room_class_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_daily_rates ADD COLUMN room_class_id int unsigned NULL AFTER rate_code, ADD KEY ix_rate_class (store_id,rate_code,room_class_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

-- Seed the standard room categories for every store that already runs wards,
-- so the pick-list exists before anyone organises rooms. Prices stay empty.
INSERT IGNORE INTO `db_room_classes`
  (`store_id`,`class_code`,`class_name`,`description`,`status`,`created_date`,`created_time`,`created_by`)
SELECT w.`store_id`, s.`class_code`, s.`class_name`, s.`description`, 1, CURDATE(), CURDATE(), 'system'
FROM (SELECT DISTINCT `store_id` FROM `db_wards`) w
CROSS JOIN (
  SELECT 'PRIVATE_AC' AS `class_code`, 'Private - AC' AS `class_name`, 'Single-occupancy private room with air conditioning' AS `description`
  UNION ALL SELECT 'PRIVATE_NONAC', 'Private - Non-AC', 'Single-occupancy private room without air conditioning'
  UNION ALL SELECT 'SEMI_PRIVATE', 'Semi-Private', 'Two or more beds sharing one room'
  UNION ALL SELECT 'GENERAL', 'General Ward', 'Open ward bed space'
  UNION ALL SELECT 'HDU', 'ICU / High Dependency', 'High-dependency or intensive care space'
) s;
