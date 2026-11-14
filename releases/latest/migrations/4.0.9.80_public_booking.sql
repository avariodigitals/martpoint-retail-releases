-- =====================================================================
-- 4.0.9.80 — public booking (slot picker) for the booking integrator
-- =====================================================================
--
-- The website booking widget can optionally let a visitor pick a real slot
-- instead of only leaving a lead. That needs three things:
--
--   1. a per-store switch, so a clinic opts IN (the default must be lead-only,
--      because a public form that can take any slot will fill the wrong ones)
--   2. per-store opening hours, so the widget offers the hours the clinic
--      actually works rather than a hard-coded 9-5
--   3. a public reference on the appointment, so a retried submission books
--      once instead of twice (same replay-safety rule as db_leads.submission_ref)
--
-- Settings live in db_store where intake_key already lives, so every store
-- (every fleet member) gets its own switch and its own hours.

-- ---------------------------------------------------------------------
-- 1. Per-store public booking switch. OFF by default.
-- ---------------------------------------------------------------------
SET @col := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'db_store'
     AND column_name = 'public_booking'
);
SET @sql := IF(@col = 0,
  'ALTER TABLE `db_store` ADD COLUMN `public_booking` TINYINT(1) NOT NULL DEFAULT 0',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 2. Per-store opening hours, as {"0":["09:00","13:00"], "1":["09:00","17:00"]}
--    Keyed by weekday 0=Sunday. A weekday absent from the JSON is CLOSED.
--    NULL means "use the built-in clinic default" (Mon-Fri 09:00-17:00,
--    Sat 09:00-13:00), so an unconfigured store still gets a working widget.
-- ---------------------------------------------------------------------
SET @col := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'db_store'
     AND column_name = 'booking_hours_json'
);
SET @sql := IF(@col = 0,
  'ALTER TABLE `db_store` ADD COLUMN `booking_hours_json` TEXT NULL',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 3. Replay-safe public bookings.
-- ---------------------------------------------------------------------
SET @col := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'db_appointments'
     AND column_name = 'public_ref'
);
SET @sql := IF(@col = 0,
  'ALTER TABLE `db_appointments` ADD COLUMN `public_ref` VARCHAR(80) NULL',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Lookup is always (store_id, public_ref) — the same submission arriving twice
-- must not book twice.
SET @idx := (
  SELECT COUNT(*) FROM information_schema.statistics
   WHERE table_schema = DATABASE() AND table_name = 'db_appointments'
     AND index_name = 'idx_appts_public_ref'
);
SET @sql := IF(@idx = 0,
  'CREATE INDEX `idx_appts_public_ref` ON `db_appointments` (`store_id`, `public_ref`)',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 4. Service length, so a slot reflects how long the appointment really is.
--    Without it every public slot would be the 30-minute default, and a
--    60-minute treatment would be offered in a 30-minute gap.
-- ---------------------------------------------------------------------
SET @col := (
  SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE() AND table_name = 'db_items'
     AND column_name = 'duration_min'
);
SET @sql := IF(@col = 0,
  'ALTER TABLE `db_items` ADD COLUMN `duration_min` INT NULL',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
