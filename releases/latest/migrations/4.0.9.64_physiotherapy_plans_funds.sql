-- ===========================================================================
-- 4.0.9.64 — Physiotherapy Stage 4: treatment plans, sessions, billing,
--            patient-funds plumbing.
--
-- Money model: docs/physiotherapy_funds_ledger.md is the contract.
--   * db_patient_wallet_txns is the ONE authoritative clinical funds ledger
--     (created in .60). All postings go through Patient_funds_model.
--   * db_custadvance remains the RETAIL advance; value moves between the two
--     only via an atomic transfer pair — never readable in both at once.
--   * Revenue is recognised at bill (db_sales) time — package sale posts
--     revenue upfront; session completion consumes entitlements only.
--   * Migrated prepaid sessions create db_plan_entitlements source='migration'
--     with NO sale and NO wallet txn — no duplicate revenue or credit.
--
-- Idempotent: CREATE TABLE IF NOT EXISTS + information_schema-guarded ALTERs.
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- Treatment plans — clinician-owned, linked to assessment + care episode.
-- Items carry immutable price snapshots; every material change bumps version
-- and writes a full snapshot into db_treatment_plan_versions.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_treatment_plans` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `plan_code` VARCHAR(30) DEFAULT NULL,
  `count_id` INT(11) DEFAULT NULL,
  `patient_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `episode_id` INT(11) DEFAULT NULL,
  `assessment_id` INT(11) DEFAULT NULL COMMENT 'db_assessments the plan is based on',
  `clinician_id` INT(11) NOT NULL COMMENT 'Owning physiotherapist (user id)',
  `branch_id` INT(11) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft|active|completed|cancelled',
  `care_setting` VARCHAR(20) NOT NULL DEFAULT 'outpatient' COMMENT 'outpatient|inpatient',
  `title` VARCHAR(160) DEFAULT NULL,
  `goals` TEXT NULL,
  `review_points` TEXT NULL COMMENT 'Structured review checkpoints (JSON array of {at, note})',
  `version` INT(11) NOT NULL DEFAULT 1,
  `billing_fingerprint` VARCHAR(64) DEFAULT NULL COMMENT 'sha of billable content — approval validity token',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `cancelled_reason` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_plan_code` (`store_id`,`plan_code`),
  KEY `idx_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_episode` (`episode_id`),
  KEY `idx_clinician` (`clinician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_plan_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `plan_id` INT(11) NOT NULL,
  `plan_version` INT(11) NOT NULL DEFAULT 1 COMMENT 'Version of the plan this line belongs to',
  `item_id` INT(11) NOT NULL COMMENT 'db_items row (service_bit=1 service)',
  `item_name` VARCHAR(255) DEFAULT NULL COMMENT 'Snapshot — services can be renamed later',
  `qty` DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Price snapshot at plan write time',
  `sessions_per_unit` DECIMAL(10,2) NOT NULL DEFAULT 1 COMMENT 'Session units each purchased unit yields',
  `funding` VARCHAR(20) NOT NULL DEFAULT 'bill' COMMENT 'bill|wallet — how the line is intended to be covered',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plan` (`plan_id`,`plan_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_plan_versions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `plan_id` INT(11) NOT NULL,
  `version` INT(11) NOT NULL,
  `snapshot_json` MEDIUMTEXT NULL COMMENT 'Full plan + items state at this version',
  `change_reason` VARCHAR(255) DEFAULT NULL,
  `changed_by` INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_version` (`plan_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Treatment sessions — one row per delivered unit.
-- Lifecycle: scheduled → checked_in → in_progress → completed
--                  ↘ cancelled / no_show ↗ (from scheduled/checked_in)
--            in_progress → interrupted (resumable → in_progress, or completed)
-- fee/fee_posted make completion a once-only financial event; consume is keyed
-- 'consume:session:<id>' on the wallet ledger (unique per store).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_treatment_sessions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `plan_id` INT(11) DEFAULT NULL COMMENT 'NULL on plan-less sessions, e.g. migrated prepay',
  `entitlement_id` INT(11) DEFAULT NULL COMMENT 'db_plan_entitlements funding the visit',
  `patient_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `encounter_id` INT(11) DEFAULT NULL COMMENT 'Care-queue visit, if run through queue',
  `branch_id` INT(11) DEFAULT NULL,
  `clinician_id` INT(11) DEFAULT NULL,
  `scheduled_at` DATETIME DEFAULT NULL,
  `session_no` INT(11) DEFAULT NULL COMMENT 'Nth session consumed from its entitlement',
  `units_total` DECIMAL(10,2) DEFAULT NULL COMMENT 'Denominator snapshot for the ticket',
  `fee` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Fee snapshot applied once on completion',
  `status` VARCHAR(20) NOT NULL DEFAULT 'scheduled' COMMENT 'scheduled|checked_in|in_progress|completed|cancelled|no_show|interrupted',
  `checkin_key` VARCHAR(80) DEFAULT NULL COMMENT 'Idempotent check-in key',
  `checked_in_at` DATETIME DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `fee_posted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Entitlement consumed + fee applied',
  `consume_txn_id` INT(11) DEFAULT NULL COMMENT 'db_patient_wallet_txns consume row (reservation-funded)',
  `cancel_reason` VARCHAR(255) DEFAULT NULL,
  `cancelled_by` INT(11) DEFAULT NULL,
  `reversal_of` INT(11) DEFAULT NULL COMMENT 'set on the correcting session state after reversal',
  `reversed` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_checkin` (`store_id`,`checkin_key`),
  KEY `idx_plan` (`plan_id`,`status`),
  KEY `idx_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_entitlement` (`entitlement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Itemised patient bill lines — link each billable row on a db_sales invoice
-- back to its treatment-plan line. The sales header carries totals/payments/
-- due; this table is the clinical itemisation + entitlement source.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patient_bill_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `sales_id` INT(11) NOT NULL,
  `sales_item_id` INT(11) DEFAULT NULL COMMENT 'db_salesitems row',
  `plan_id` INT(11) DEFAULT NULL,
  `plan_item_id` INT(11) DEFAULT NULL,
  `plan_version` INT(11) DEFAULT NULL,
  `item_id` INT(11) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `qty` DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `discount_amt` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `entitlement_id` INT(11) DEFAULT NULL COMMENT 'Entitlement spawned once line is paid/funded',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sales` (`sales_id`),
  KEY `idx_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link the sales header to its source treatment plan (guarded).
SET @tbl := 'db_sales';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='plan_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `plan_id` INT(11) NULL DEFAULT NULL AFTER `quotation_id`, ADD KEY `idx_plan` (`plan_id`)'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Payment evidence may now point at the bill it settles and the plan it funds.
SET @tbl := 'db_payment_evidence';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='sale_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `sale_id` INT(11) NULL DEFAULT NULL, ADD COLUMN `plan_id` INT(11) NULL DEFAULT NULL, ADD KEY `idx_sale` (`sale_id`)'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Opening positions need typed payloads so migrated prepaid sessions /
-- migrated wallet balances are reviewable before posting.
SET @tbl := 'db_opening_positions';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='position_type')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `position_type` VARCHAR(30) NOT NULL DEFAULT ''wallet_balance'' COMMENT ''wallet_balance|prepaid_sessions|outstanding_debt'', ADD COLUMN `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00, ADD COLUMN `units` DECIMAL(10,2) NOT NULL DEFAULT 0.00, ADD COLUMN `service_item_id` INT(11) NULL DEFAULT NULL, ADD COLUMN `ref_id` INT(11) NULL DEFAULT NULL COMMENT ''Posted wallet txn / entitlement / sale id after approval'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------------------------------------------------------------------------
-- New approval types on db_approval_settings (enabled+method pairs).
-- Existing clinical types from .60: credit_override, refund_wallet,
-- plan_material_change. Added here: md_discount (bill discounts needing MD
-- authority) and wallet_adjustment (restricted funds adjustments).
-- ---------------------------------------------------------------------------
SET @tbl := 'db_approval_settings';

SET @type := 'md_discount';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'wallet_adjustment';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Re-run safety: if an earlier .64 build created plan_id NOT NULL, relax it so
-- plan-less sessions (migrated prepay) can be scheduled.
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_treatment_sessions' AND column_name='plan_id' AND is_nullable='NO')>0,
  'ALTER TABLE `db_treatment_sessions` MODIFY COLUMN `plan_id` INT(11) DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
