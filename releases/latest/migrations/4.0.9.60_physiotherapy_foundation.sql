-- ============================================================================
-- MartPoint 4.0.9.60 — Physiotherapy & Rehabilitation: foundation schema
-- Business type `physiotherapy_rehabilitation` (see docs/physiotherapy_design.md).
-- Creates the patient/care spine plus the pulled-forward dependency foundations:
-- notification queue, persistent approval extensions, private documents,
-- patient-funds journal/reservations, opening positions and portal auth.
-- Idempotent: CREATE TABLE IF NOT EXISTS + information_schema-guarded ALTERs.
-- MySQL 5.7+ / InnoDB. Runs via mysqli_multi_query.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---------------------------------------------------------------------------
-- Patient profile — clinical identity linked 1:1 to the financial customer row.
-- Money stays on db_customers; clinical data is permission-gated separately.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL COMMENT '1:1 link to db_customers (financial identity)',
  `count_id` INT(11) DEFAULT NULL COMMENT 'Store-scoped sequence for patient_code',
  `patient_code` VARCHAR(30) DEFAULT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `dob` DATE DEFAULT NULL,
  `marital_status` VARCHAR(20) DEFAULT NULL,
  `occupation` VARCHAR(100) DEFAULT NULL,
  `blood_group` VARCHAR(10) DEFAULT NULL,
  `nok_name` VARCHAR(150) DEFAULT NULL COMMENT 'Next of kin',
  `nok_phone` VARCHAR(30) DEFAULT NULL,
  `nok_relationship` VARCHAR(60) DEFAULT NULL,
  `legacy_ids_json` TEXT NULL COMMENT 'External system IDs, e.g. {"smart_hospital":"123"}',
  `portal_status` VARCHAR(20) NOT NULL DEFAULT 'none' COMMENT 'none|invited|active|suspended',
  `deceased` TINYINT(1) NOT NULL DEFAULT 0,
  `deceased_date` DATE DEFAULT NULL,
  `deceased_recorded_by` INT(11) DEFAULT NULL,
  `deceased_notes` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_patient_code` (`store_id`,`patient_code`),
  UNIQUE KEY `uk_store_customer` (`store_id`,`customer_id`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_nok_phone` (`nok_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Care episode — outpatient/inpatient context; many episodes per patient.
-- warehouse_id is the care branch (db_warehouse.branch_type='branch').
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_care_episodes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `count_id` INT(11) DEFAULT NULL,
  `episode_code` VARCHAR(30) DEFAULT NULL,
  `warehouse_id` INT(11) DEFAULT NULL COMMENT 'Branch (db_warehouse)',
  `episode_type` VARCHAR(20) NOT NULL DEFAULT 'outpatient' COMMENT 'outpatient|inpatient',
  `responsible_user_id` INT(11) DEFAULT NULL COMMENT 'Responsible clinician',
  `started_at` DATETIME DEFAULT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `closure_outcome` VARCHAR(40) DEFAULT NULL COMMENT 'completed|continuing_outpatient|transferred|left_against_advice|lost_to_followup|deceased',
  `status` VARCHAR(20) NOT NULL DEFAULT 'open' COMMENT 'open|closed',
  `notes` TEXT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_episode_code` (`store_id`,`episode_code`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_branch_status` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Encounter — a visit/check-in under an episode. Owns its own notes; vitals are
-- recorded against it. Identity must exist before stage-2 check-in.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_encounters` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `episode_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) DEFAULT NULL,
  `appointment_id` INT(11) DEFAULT NULL,
  `count_id` INT(11) DEFAULT NULL,
  `encounter_code` VARCHAR(30) DEFAULT NULL,
  `checkin_at` DATETIME DEFAULT NULL,
  `clinician_user_id` INT(11) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open' COMMENT 'open|in_progress|completed|cancelled',
  `vitals_json` TEXT NULL COMMENT 'measured_at/recorder/values+units; not_measured flags distinct from zero',
  `notes` TEXT NULL,
  `finalized_at` DATETIME DEFAULT NULL,
  `finalized_by` INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_encounter_code` (`store_id`,`encounter_code`),
  KEY `idx_store_episode` (`store_id`,`episode_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_branch_status` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Appointments — requested/proposed/confirmed/checked_in completed/cancelled/no_show.
-- Booking is never implied confirmed; staff acceptance is a status transition.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_appointments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) DEFAULT NULL COMMENT 'Branch',
  `patient_id` INT(11) DEFAULT NULL,
  `lead_id` INT(11) DEFAULT NULL,
  `service_id` INT(11) DEFAULT NULL COMMENT 'db_services catalogue line',
  `staff_user_id` INT(11) DEFAULT NULL COMMENT 'Assigned clinician',
  `count_id` INT(11) DEFAULT NULL,
  `booking_ref` VARCHAR(30) DEFAULT NULL,
  `scheduled_at` DATETIME DEFAULT NULL,
  `duration_min` INT(11) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'requested' COMMENT 'requested|proposed|confirmed|checked_in|completed|cancelled|no_show',
  `source` VARCHAR(30) NOT NULL DEFAULT 'manual' COMMENT 'manual|lead|portal|walk_in',
  `notes` TEXT NULL,
  `cancel_reason` VARCHAR(255) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_booking_ref` (`store_id`,`booking_ref`),
  KEY `idx_store_branch_sched` (`store_id`,`warehouse_id`,`scheduled_at`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Notification queue — outbox pattern. Rows are enqueued with a unique
-- (store_id,event_key); the cron consumer (stage 2) sends via Email_service/SMS.
-- A failed send never rolls back the business action.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_notification_queue` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `event_key` VARCHAR(80) NOT NULL COMMENT 'Idempotent event reference',
  `channel` VARCHAR(10) NOT NULL DEFAULT 'email' COMMENT 'email|sms',
  `template_key` VARCHAR(60) DEFAULT NULL,
  `recipient` VARCHAR(255) DEFAULT NULL,
  `payload_json` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'queued' COMMENT 'queued|sent|failed|suppressed',
  `attempts` INT(11) NOT NULL DEFAULT 0,
  `last_error` VARCHAR(500) DEFAULT NULL,
  `scheduled_at` DATETIME DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_event` (`store_id`,`event_key`),
  KEY `idx_status_sched` (`status`,`scheduled_at`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Patient documents & consent — private storage metadata + immutable versions.
-- Files live outside public uploads/; served only via a permission-checked
-- controller (stage 3). Upload/email alone never means completed consent.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patient_documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `episode_id` INT(11) DEFAULT NULL,
  `category` VARCHAR(40) NOT NULL DEFAULT 'other' COMMENT 'consent|assessment|investigation_result|session_report|referral|external_procedure|prescription|discharge|payment_evidence|other',
  `title` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'draft' COMMENT 'draft|awaiting_signatures|awaiting_verification|completed|declined|superseded',
  `current_version_id` INT(11) DEFAULT NULL,
  `signatories_json` TEXT NULL COMMENT 'required signatories incl. proxy capacity',
  `released_to_patient` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Explicit clinical release only',
  `released_by` INT(11) DEFAULT NULL,
  `released_at` DATETIME DEFAULT NULL,
  `verified_by` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_category` (`store_id`,`category`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_document_versions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `document_id` INT(11) NOT NULL,
  `version_no` INT(11) NOT NULL DEFAULT 1,
  `file_path` VARCHAR(255) NOT NULL COMMENT 'Relative to private patient-docs dir',
  `file_hash` VARCHAR(64) DEFAULT NULL,
  `file_size` INT(11) DEFAULT NULL,
  `mime` VARCHAR(100) DEFAULT NULL,
  `signature_evidence_json` TEXT NULL,
  `uploaded_by` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_doc_version` (`document_id`,`version_no`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Patient funds — journal + reservations. Authoritative spendable balance stays
-- db_customers.tot_advance (db_custadvance ledger). These tables record intent and
-- guarantee idempotency; they are never summed for spendable balance.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patient_wallet_txns` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `patient_id` INT(11) DEFAULT NULL,
  `txn_type` VARCHAR(20) NOT NULL COMMENT 'fund|reserve|release|consume|refund|adjust|opening',
  `direction` VARCHAR(10) NOT NULL DEFAULT 'credit' COMMENT 'credit|debit|memo',
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `operation_key` VARCHAR(80) NOT NULL COMMENT 'Unique idempotency key, e.g. session:12:consume',
  `reservation_id` INT(11) DEFAULT NULL,
  `sales_id` INT(11) DEFAULT NULL,
  `salespayment_id` INT(11) DEFAULT NULL,
  `custadvance_id` INT(11) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_op` (`store_id`,`operation_key`),
  KEY `idx_store_customer` (`store_id`,`customer_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_reservation` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fund_reservations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `patient_id` INT(11) DEFAULT NULL,
  `plan_id` INT(11) DEFAULT NULL,
  `entitlement_id` INT(11) DEFAULT NULL,
  `amount_reserved` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `amount_consumed` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active|exhausted|released|cancelled',
  `source` VARCHAR(30) NOT NULL DEFAULT 'purchase' COMMENT 'purchase|migration|grant',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_customer` (`store_id`,`customer_id`,`status`),
  KEY `idx_entitlement` (`entitlement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_payment_evidence` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) DEFAULT NULL,
  `customer_id` INT(11) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `channel` VARCHAR(30) DEFAULT NULL COMMENT 'transfer|pos|cash|online',
  `payer_name` VARCHAR(150) DEFAULT NULL COMMENT 'May differ from patient (family/employer/insurer)',
  `payment_ref` VARCHAR(80) DEFAULT NULL,
  `document_id` INT(11) DEFAULT NULL COMMENT 'db_patient_documents evidence file',
  `status` VARCHAR(20) NOT NULL DEFAULT 'submitted' COMMENT 'submitted|verified|rejected',
  `verified_by` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `reject_reason` VARCHAR(255) DEFAULT NULL,
  `custadvance_id` INT(11) DEFAULT NULL COMMENT 'Set when verification posts the advance',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_ref` (`store_id`,`payment_ref`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_store_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Plan entitlements — prepaid session/package units (count + funded coverage).
-- Entitlement is separate from money: funded or unfunded coverage is explicit.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_plan_entitlements` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `plan_id` INT(11) DEFAULT NULL,
  `sale_id` INT(11) DEFAULT NULL COMMENT 'Purchase invoice, when bought via POS',
  `service_id` INT(11) DEFAULT NULL,
  `units_total` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `units_used` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `funded_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `consumed_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `source` VARCHAR(20) NOT NULL DEFAULT 'purchase' COMMENT 'purchase|migration|grant',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active|exhausted|cancelled|expired',
  `expiry_date` DATE DEFAULT NULL,
  `valuation_note` VARCHAR(255) DEFAULT NULL COMMENT 'Basis for migrated/unfunded units',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_sale` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Opening positions (Stage B migration). Import (Stage A) creates identities only;
-- staff enter, reviewers approve, and only approval posts live balances.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_opening_positions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `cutoff_date` DATE DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'imported' COMMENT 'imported|position_pending|entered|reviewed|approved|rejected',
  `entered_by` INT(11) DEFAULT NULL,
  `entered_at` DATETIME DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `reject_reason` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_opening_position_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `position_id` INT(11) NOT NULL,
  `item_type` VARCHAR(30) NOT NULL COMMENT 'debt|unused_money|unused_sessions|active_admission',
  `payload_json` TEXT NULL COMMENT 'Type-specific detail: payer, evidence ref, plan/service, units...',
  `amount` DECIMAL(15,2) DEFAULT NULL COMMENT 'NULL = unknown/pending, never invented zero',
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending|entered|reviewed|approved|rejected',
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position_id`),
  KEY `idx_store_type` (`store_id`,`item_type`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Patient portal auth — separate from staff auth. Identity verified before
-- activation; proxies are explicit, scoped and revocable.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_patient_portal_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `identity` VARCHAR(150) NOT NULL COMMENT 'Verified email or phone',
  `auth_secret` VARCHAR(255) DEFAULT NULL COMMENT 'Password/PIN hash',
  `verified_at` DATETIME DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'invited' COMMENT 'invited|active|suspended',
  `last_login_at` DATETIME DEFAULT NULL,
  `failed_attempts` INT(11) NOT NULL DEFAULT 0,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_identity` (`store_id`,`identity`),
  KEY `idx_store_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_proxies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `portal_user_id` INT(11) NOT NULL COMMENT 'The proxy caregiver account',
  `relationship` VARCHAR(60) DEFAULT NULL,
  `scope_json` TEXT NULL COMMENT 'Explicit granted scopes',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active|revoked',
  `granted_by` INT(11) DEFAULT NULL,
  `revoked_at` DATETIME DEFAULT NULL,
  `revoked_by` INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_portal_user` (`portal_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Approval engine extensions — persistent requests, target binding, delegation.
-- Existing inline-PIN flow is unchanged; these columns/tables add the workflow.
-- ---------------------------------------------------------------------------

-- db_approval_logs: target binding + lifecycle (pending|approved|rejected|cancelled
-- already exist; withdrawn|expired added)
SET @tbl := 'db_approval_logs';

SET @col := 'target_module';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(40) NULL DEFAULT NULL COMMENT ''Whitelisted module key, never a raw table name'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'target_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'target_version';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL COMMENT ''Version the approval was issued against; revalidated on apply'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'expires_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'withdrawn_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'applied_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL COMMENT ''When the approved change actually took effect'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'delegation_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'flagged_for_audit';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''MD self-override and other auditor-review flags'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Extend status enum (keeps existing four values, adds withdrawn/expired)
SET @sql := IF((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_approval_logs')>0
  AND (SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=DATABASE() AND table_name='db_approval_logs'
    AND column_name='status' AND column_type LIKE '%''withdrawn''%')=0,
  "ALTER TABLE `db_approval_logs` MODIFY COLUMN `status` ENUM('pending','approved','rejected','cancelled','withdrawn','expired') NOT NULL DEFAULT 'pending'",
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

CREATE TABLE IF NOT EXISTS `db_approval_delegations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `delegator_user_id` INT(11) NOT NULL,
  `delegate_user_id` INT(11) NOT NULL,
  `scope` VARCHAR(255) NOT NULL COMMENT 'Comma-separated approval types this covers',
  `amount_limit` DECIMAL(15,2) DEFAULT NULL COMMENT 'NULL = unlimited within scope',
  `valid_from` DATETIME DEFAULT NULL,
  `valid_to` DATETIME DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active|revoked|expired',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_delegate` (`store_id`,`delegate_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- db_approval_settings: clinical/financial approval types (enabled+method pairs,
-- same convention as existing types — getApprovalTypes() picks them up in code)
SET @tbl := 'db_approval_settings';

SET @type := 'clinical_discharge';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'plan_material_change';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'refund_wallet';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'credit_override';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'invoice_cancellation';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'leave_transfer';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @type := 'opening_position';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=CONCAT(@type,'_approval_enabled'))=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @type, '_approval_enabled` TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN `', @type, '_approval_method` VARCHAR(30) NOT NULL DEFAULT ''none'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------------------------------------------------------------------------
-- Leads: stage-2 intake columns (added now so the intake API migration is code-only)
-- ---------------------------------------------------------------------------
SET @tbl := 'db_leads';

SET @col := 'submission_ref';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(80) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=@tbl AND index_name='uk_store_submission_ref')=0,
  'ALTER TABLE `db_leads` ADD UNIQUE KEY `uk_store_submission_ref` (`store_id`,`submission_ref`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'enquiry';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'preferred_contact';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` VARCHAR(20) NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'preferred_branch_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'preferred_date';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATE NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'attribution_json';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` TEXT NULL COMMENT ''page_url, utm_source/medium/campaign — secrets stripped'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'next_followup_at';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` DATETIME NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'patient_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := 'duplicate_of_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` INT NULL DEFAULT NULL'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- db_patients: duplicate-merge pointer (loser row keeps its data; status=0)
SET @tbl := 'db_patients';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='duplicate_of_id')=0,
  'ALTER TABLE `db_patients` ADD COLUMN `duplicate_of_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;
