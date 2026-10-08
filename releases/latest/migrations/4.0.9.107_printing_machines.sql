-- ============================================================================
-- MartPoint 4.0.9.107 — Printing: machine register, run-level machine
-- confirmation, counter readings, maintenance and customer-owned materials.
--
-- Scope: printing business type ONLY. Everything here is gated by the store's
-- printing industry / printing_workflow feature; no other industry reads these
-- tables. Existing Nylon functionality is untouched — Nylon keeps its own
-- db_nylon_* tables and is not migrated onto these.
--
-- REUSE, DO NOT DUPLICATE. This migration deliberately does not create:
--   * a stock engine   — consumable/part issuance posts through the existing
--                        single posting method (Printing_model::post_stock_moves).
--   * an approval      — approvals ride approval_helper + db_approval_logs
--                        (target_module / target_id / target_version).
--   * a payment engine — job money stays in db_print_payments + db_salespayments.
--   * an audit trail   — db_audit_trail.
--
-- Design invariants (do not collapse):
--   * counter difference  != accepted output     (machine activity, not good output)
--   * issued              != loaded/installed    (two distinct states)
--   * installed           != consumed            (allocation is separate again)
--   * production issuance != consumption         (custody positions)
--   * production complete != customer collection (custody positions)
--   * customer-owned stock has NO company cost   (and no company valuation)
--
-- Idempotent. MySQL 5.7+/MariaDB compatible. Safe to run more than once.
-- Older MySQL has no `ADD COLUMN IF NOT EXISTS`, so guarded ALTERs use a
-- PREPARE/EXECUTE pair against information_schema with `DO 0` in the
-- else-branch (a bare `SELECT 1` emits a row per guard and litters the log).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- SECTION 1 — Machine register (db_print_machines)
--
-- One row per physical machine. Supported production stages live in
-- supported_stages_json (stage keys, e.g. ["print","lamination"]) so a machine
-- can be offered only for the stages it actually performs.
--
-- Counter configuration is per-machine because a shop genuinely has all of:
--   reading_mode = required    → a reading MUST be entered for a run
--   reading_mode = optional    → a reading may be entered
--   reading_mode = unavailable → this machine has no counters at all.
-- A machine with has_colour_counter keeps separate colour + mono counters.
-- All readings in this phase are MANUAL — no machine integration.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_machines` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `branch_id` INT(11) NOT NULL DEFAULT 0 COMMENT '0 = store default branch',
  `machine_code` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'shop-unique identifier / asset tag',
  `name` VARCHAR(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_no` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL COMMENT 'db_print_categories.id this machine mainly serves',
  `machine_category` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other'
      COMMENT 'press|digital|large_format|cutting|finishing|binding|other',
  `location` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supported_stages_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'stage keys this machine can perform',
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available'
      COMMENT 'available|maintenance|out_of_service',
  `status_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_changed_at` DATETIME DEFAULT NULL,
  `status_changed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  -- counter configuration
  `reading_mode` VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'optional'
      COMMENT 'required|optional|unavailable',
  `has_colour_counter` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = separate colour + mono counters',
  `counter_unit` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'impressions'
      COMMENT 'impressions|sheets|metres|minutes|units',
  `counter_rollover_at` DECIMAL(20,3) DEFAULT NULL COMMENT 'counter wraps here; differences across a wrap are computed, not guessed',
  `supports_multiple_loads` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = more than one consumable may be loaded at once (CMYK)',
  -- maintenance schedule
  `service_interval_days` INT(6) DEFAULT NULL COMMENT 'calendar-based service interval',
  `service_interval_impressions` DECIMAL(20,3) DEFAULT NULL COMMENT 'counter-based service interval',
  `last_service_at` DATE DEFAULT NULL,
  `next_service_at` DATE DEFAULT NULL,
  `next_service_impressions` DECIMAL(20,3) DEFAULT NULL,
  `purchase_date` DATE DEFAULT NULL,
  `purchase_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `status_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = retired from the register (history kept)',
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_machine_code` (`store_id`,`machine_code`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`machine_category`),
  KEY `idx_active` (`status_active`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- SECTION 1 — Counter readings (db_print_machine_readings)
--
-- APPEND-ONLY. A reading is never edited. A correction writes a NEW row whose
-- corrects_reading_id points at the row it supersedes, and the superseded row
-- keeps its value (status='superseded') so history is preserved, not rewritten.
-- counter_reset and counter_replacement are their own reading types and REQUIRE
-- a reason plus an authorized handler.
--
-- opening_baseline = 1 marks a dated opening reading. An opening reading
-- establishes the baseline ONLY: it must never create historical output or
-- cost, so nothing sums opening rows as production.
--
-- The delta across a reset/replacement is NOT real activity. delta is stored as
-- NULL for those rows with delta_invalid_reason explaining why, so no report can
-- silently read a counter reset as a huge output spike.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_machine_readings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_id` INT(11) NOT NULL,
  `reading_type` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'run'
      COMMENT 'opening|run|correction|reset|replacement|service|manual',
  `reading_date` DATE NOT NULL,
  `reading_time` TIME DEFAULT NULL,
  -- mono / colour counters (a non-colour machine uses mono only)
  `mono_reading` DECIMAL(20,3) DEFAULT NULL,
  `colour_reading` DECIMAL(20,3) DEFAULT NULL,
  `counter_unit` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'impressions',
  -- deltas computed by the server, never entered by hand
  `prev_reading_id` INT(11) DEFAULT NULL,
  `mono_delta` DECIMAL(20,3) DEFAULT NULL,
  `colour_delta` DECIMAL(20,3) DEFAULT NULL,
  `delta` DECIMAL(20,3) DEFAULT NULL COMMENT 'total counter activity; NULL when invalid',
  `delta_valid` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = reset/replacement/backwards — not activity',
  `delta_invalid_reason` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_baseline` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = baseline only, never output or cost',
  -- provenance
  `job_id` INT(11) DEFAULT NULL,
  `stage_id` INT(11) DEFAULT NULL,
  `stage_log_id` INT(11) DEFAULT NULL COMMENT 'db_print_stage_logs.id when entered with a run',
  `corrects_reading_id` INT(11) DEFAULT NULL COMMENT 'correction chain — the superseded row is kept',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'REQUIRED for correction/reset/replacement',
  `authorized_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'authorized handling of a correction/reset/replacement',
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recorded'
      COMMENT 'recorded|superseded|void',
  `recorded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine_date` (`machine_id`,`reading_date`),
  KEY `idx_type` (`reading_type`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- SECTION 2 — Run-level machine confirmation + readings on the stage log
--
-- db_print_stages.machine_id already holds the PLANNED machine. A run records
-- the ACTUAL machine, who confirmed it and when — planning a machine is not the
-- same as having run on it. Manual and outsourced runs carry NO machine at all,
-- so run_kind exists to make that explicit rather than leaving a fictitious id.
-- ----------------------------------------------------------------------------
SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='machine_confirmed');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `machine_confirmed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = operator explicitly confirmed the actual machine for this run''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='machine_id_planned');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `machine_id_planned` INT(11) DEFAULT NULL COMMENT ''the proposed machine at the time of the run''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='machine_override_reason');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `machine_override_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT ''why an unavailable machine was used; requires authorization''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='machine_override_authorized_by');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `machine_override_authorized_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='started_at');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `started_at` DATETIME DEFAULT NULL COMMENT ''manual run start''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='ended_at');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `ended_at` DATETIME DEFAULT NULL COMMENT ''manual run end''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='counter_start_id');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `counter_start_id` INT(11) DEFAULT NULL COMMENT ''db_print_machine_readings.id at run start''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='counter_end_id');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `counter_end_id` INT(11) DEFAULT NULL COMMENT ''db_print_machine_readings.id at run end''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='counter_activity');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `counter_activity` DECIMAL(20,3) DEFAULT NULL COMMENT ''machine activity for the run — NOT accepted output''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='accepted_qty');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `accepted_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT ''accepted (saleable) output, distinct from counter activity''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='qty_unit_id');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `qty_unit_id` INT(11) DEFAULT NULL COMMENT ''unit the run quantities are expressed in''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='references_json');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `references_json` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT ''supporting references: batch / job-card / sample refs''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='run_kind');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `run_kind` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''machine'' COMMENT ''machine|manual|outsourced — manual and outsourced runs carry NO machine''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='reading_discrepancy');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `reading_discrepancy` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT ''why readings and quantities disagree; unresolved until reviewed''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- An outsourced run names its vendor on the RUN, not only on the stage. A stage
-- can be partly outsourced and partly done in-house, so the vendor belongs to
-- the individual run.
SET @c = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_stage_logs' AND column_name='outsource_vendor');
SET @s = IF(@c=0, 'ALTER TABLE `db_print_stage_logs` ADD COLUMN `outsource_vendor` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT ''vendor for an outsourced run''', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
