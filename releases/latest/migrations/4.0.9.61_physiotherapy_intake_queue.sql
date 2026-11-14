-- ============================================================================
-- MartPoint 4.0.9.61 — Physiotherapy & Rehabilitation: Stage 2
-- Website intake -> leads -> appointments -> check-in -> care queue.
-- Adds: lead activity log, appointment events + clinician slot guard,
-- idempotent encounter check-in key + queue stage, patient audit events,
-- per-store intake key, and a rate-limit counter table.
-- Idempotent: CREATE TABLE IF NOT EXISTS + information_schema-guarded ALTERs.
-- MySQL 5.7+ / InnoDB. Runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---------------------------------------------------------------------------
-- Lead activity log — owner assignment, follow-ups, status changes, notes.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_lead_activities` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `lead_id` INT(11) NOT NULL,
  `activity_type` VARCHAR(30) NOT NULL COMMENT 'note|call|whatsapp|email|visit|status_change|assignment|followup|appointment|converted',
  `note` TEXT NULL,
  `meta_json` TEXT NULL COMMENT 'Structured detail: old/new status, assignee, appt ref',
  `created_by` INT(11) DEFAULT NULL COMMENT 'db_users.id',
  `created_by_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_lead` (`store_id`,`lead_id`),
  KEY `idx_lead_time` (`lead_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Appointments — slot guard + reschedule history.
-- slot_key = staff_user_id|scheduled_at while the booking occupies the slot;
-- cleared on cancel/no_show/reschedule so cancelled rows never block a slot.
-- ---------------------------------------------------------------------------
SET @tbl := 'db_appointments';

SET @col := 'slot_key';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(80) NULL DEFAULT NULL COMMENT ''staff_user_id|Y-m-d H:i — set only while status occupies the slot'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name='uk_store_slot')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_appointments` ADD UNIQUE KEY `uk_store_slot` (`store_id`,`slot_key`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'rescheduled_from_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'arrived_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS `db_appointment_events` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `appointment_id` INT(11) NOT NULL,
  `event` VARCHAR(40) NOT NULL COMMENT 'booked|confirmed|rescheduled|arrived|cancelled|no_show|completed|note',
  `from_value` VARCHAR(80) DEFAULT NULL,
  `to_value` VARCHAR(80) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_by_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_appt` (`store_id`,`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Encounters — idempotent check-in + queue stage.
-- checkin_key: unique per (store_id, checkin_key). Booked arrivals use
-- "appt:{id}", walk-ins use a client-supplied uuid; retries return the
-- same encounter rather than creating a second visit.
-- queue_stage tracks visit position — deliberately separate from the
-- appointment status and from financial state.
-- ---------------------------------------------------------------------------
SET @tbl := 'db_encounters';

SET @col := 'checkin_key';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(80) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name='uk_store_checkin')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  'ALTER TABLE `db_encounters` ADD UNIQUE KEY `uk_store_checkin` (`store_id`,`checkin_key`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'queue_stage';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(30) NULL DEFAULT NULL COMMENT ''waiting_nurse|nursing_intake|waiting_physio|with_physio|awaiting_finance|closed'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'queue_stage_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'assigned_to';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL COMMENT ''Current handler — nurse during intake, physio during consult'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS `db_encounter_events` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `encounter_id` INT(11) NOT NULL,
  `event` VARCHAR(40) NOT NULL COMMENT 'checked_in|stage_change|vitals|note|closed',
  `from_stage` VARCHAR(30) DEFAULT NULL,
  `to_stage` VARCHAR(30) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_by_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_enc` (`store_id`,`encounter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Patient audit events — deceased/correction/merge/portal actions.
-- Every sensitive registry flag carries actor + reason + timestamp.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patient_events` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `event` VARCHAR(40) NOT NULL COMMENT 'registered|deceased|deceased_corrected|duplicate_flagged|status_change|linked_customer',
  `reason` VARCHAR(500) DEFAULT NULL,
  `meta_json` TEXT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_by_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_event` (`store_id`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Server-to-server intake — per-store key on db_store + rate counters.
-- The intake endpoint authenticates with X-MartPoint-Intake-Key; secrets are
-- generated per store, never hard-coded.
-- ---------------------------------------------------------------------------
SET @tbl := 'db_store';
SET @col := 'intake_key';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(64) NULL DEFAULT NULL COMMENT ''Shared secret for server-to-server lead intake'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS `db_rate_limits` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bucket` VARCHAR(80) NOT NULL COMMENT 'e.g. intake:{store_id}:{ip}',
  `window_start` DATETIME NOT NULL,
  `hits` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bucket_window` (`bucket`,`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
