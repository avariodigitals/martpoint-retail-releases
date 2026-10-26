-- ============================================================================
-- MartPoint — schema repair (EXISTING installs)
--
-- PURPOSE
--
-- Adds tables and columns that are missing from an install which is already
-- in service. Purely additive: nothing here drops, renames or rewrites data.
--
-- WHY A SEPARATE FILE FROM db_schema_catchup.sql
--
-- The catch-up file also records every migration as applied and stamps the
-- current version. Both are correct ONLY on a brand-new, empty database.
-- On a live install they would mark migrations as applied that may never have
-- run, and stamp the newest version over a genuinely incomplete chain — the
-- exact silent failure that made an install report "up to date" while its
-- database was many releases behind.
--
-- Migrations remain the ONLY thing that advances db_schema_migrations and the
-- recorded version. This file touches neither.
--
-- 131 table(s) and 141 column(s), taken from a fully migrated database.
--
-- Idempotent: CREATE TABLE IF NOT EXISTS, and each ADD COLUMN is guarded on
-- both the table and the column — so it is safe to run repeatedly, and the
-- ORDER of statements cannot cause a failure (a guarded ALTER for a table that
-- does not exist yet is simply skipped by MySQL).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION innodb_strict_mode = OFF;
-- db.txt leaves autocommit OFF for the whole session. DDL commits implicitly so
-- the schema below would stick anyway, but any future DML appended here would be
-- silently rolled back on disconnect. Be explicit.
SET AUTOCOMMIT = 1;

-- ----------------------------------------------------------------------------
-- 1. Missing tables (131)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_admissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `admission_code` varchar(30) NOT NULL,
  `reason` text,
  `care_plan` text,
  `clinician_user_id` int DEFAULT NULL,
  `admitted_by` int DEFAULT NULL,
  `admitted_at` datetime NOT NULL,
  `outpatient_decision` varchar(20) NOT NULL DEFAULT 'continue',
  `outpatient_decision_note` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `invoice_id` int DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_adm_code` (`store_id`,`admission_code`),
  KEY `ix_adm_patient` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_appointment_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `event` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'booked|confirmed|rescheduled|arrived|cancelled|no_show|completed|note',
  `from_value` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_value` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_appt` (`store_id`,`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_appointments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `warehouse_id` int DEFAULT NULL COMMENT 'Branch',
  `patient_id` int DEFAULT NULL,
  `lead_id` int DEFAULT NULL,
  `service_id` int DEFAULT NULL COMMENT 'db_services catalogue line',
  `staff_user_id` int DEFAULT NULL COMMENT 'Assigned clinician',
  `count_id` int DEFAULT NULL,
  `booking_ref` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `duration_min` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested' COMMENT 'requested|proposed|confirmed|checked_in|completed|cancelled|no_show',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual' COMMENT 'manual|lead|portal|walk_in',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancel_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `slot_key` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'staff_user_id|Y-m-d H:i â€” set only while status occupies the slot',
  `rescheduled_from_id` int DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `reminder_queued` tinyint(1) NOT NULL DEFAULT '0',
  `public_ref` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_booking_ref` (`store_id`,`booking_ref`),
  UNIQUE KEY `uk_store_slot` (`store_id`,`slot_key`),
  KEY `idx_store_branch_sched` (`store_id`,`warehouse_id`,`scheduled_at`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_appts_public_ref` (`store_id`,`public_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_approval_delegations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `delegator_user_id` int NOT NULL,
  `delegate_user_id` int NOT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Comma-separated approval types this covers',
  `amount_limit` decimal(15,2) DEFAULT NULL COMMENT 'NULL = unlimited within scope',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|revoked|expired',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_delegate` (`store_id`,`delegate_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_assessment_amendments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `assessment_id` int NOT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changes_json` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '[{field,label,from,to}] â€” from is the ORIGINAL stored answer',
  `amended_by` int DEFAULT NULL,
  `amended_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assessment` (`assessment_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_assessment_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0' COMMENT '0 = platform template, shared',
  `template_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|archived',
  `provisional` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Stand-in pending the agreed clinical form',
  `sections_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_version` (`store_id`,`template_key`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_assessments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `encounter_id` int NOT NULL,
  `episode_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `template_id` int NOT NULL,
  `template_key` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_version` int DEFAULT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|final',
  `answers_json` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'section_key.field_key => value',
  `assessor_user_id` int DEFAULT NULL,
  `assessor_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `finalized_by` int DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_bed_occupancy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `bed_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `from_at` datetime NOT NULL,
  `to_at` datetime DEFAULT NULL,
  `open_reason` varchar(20) NOT NULL DEFAULT 'admission',
  `close_reason` varchar(20) DEFAULT NULL,
  `booked_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_occ_bed` (`bed_id`,`to_at`),
  KEY `ix_occ_adm` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_bed_transfers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `from_bed_id` int NOT NULL,
  `to_bed_id` int NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `requested_by` varchar(50) DEFAULT NULL,
  `porter_task_id` int DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'requested',
  `failure_note` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_transfer` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_beds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `ward_id` int NOT NULL,
  `bed_label` varchar(60) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `daily_rate` decimal(15,2) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bed` (`ward_id`,`bed_label`),
  KEY `ix_bed_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_bulksmsng` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `api_token` text NOT NULL,
  `sender_id` varchar(50) NOT NULL DEFAULT 'BulkSMS',
  `base_url` varchar(255) NOT NULL DEFAULT 'https://www.bulksmsnigeria.com/api',
  `gateway` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_bundle_components` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `bundle_item_id` int NOT NULL,
  `component_item_id` int NOT NULL,
  `qty` decimal(12,2) NOT NULL DEFAULT '1.00',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bundle_comp` (`bundle_item_id`,`component_item_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_campaign_sends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `campaign_id` int NOT NULL,
  `channel` varchar(20) NOT NULL,
  `recipient` varchar(191) NOT NULL,
  `customer_name` varchar(191) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `error` varchar(255) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `unsub_token` varchar(64) DEFAULT NULL,
  `claimed_at` datetime DEFAULT NULL,
  `attempts` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_csend` (`campaign_id`,`channel`,`recipient`),
  KEY `idx_csend_store` (`store_id`),
  KEY `idx_csend_unsub` (`unsub_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `segment_id` int DEFAULT NULL,
  `segment_key` varchar(40) DEFAULT NULL,
  `audience_json` text,
  `channel` varchar(20) NOT NULL DEFAULT 'email',
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `audience_count` int NOT NULL DEFAULT '0',
  `sent_count` int NOT NULL DEFAULT '0',
  `failed_count` int NOT NULL DEFAULT '0',
  `created_by` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_camp_store` (`store_id`),
  KEY `idx_camp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_care_episodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `count_id` int DEFAULT NULL,
  `episode_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` int DEFAULT NULL COMMENT 'Branch (db_warehouse)',
  `episode_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outpatient' COMMENT 'outpatient|inpatient',
  `responsible_user_id` int DEFAULT NULL COMMENT 'Responsible clinician',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `closure_outcome` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'completed|continuing_outpatient|transferred|left_against_advice|lost_to_followup|deceased',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT 'open|closed',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_episode_code` (`store_id`,`episode_code`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_branch_status` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_consents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `episode_id` int DEFAULT NULL,
  `encounter_id` int DEFAULT NULL,
  `consent_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general_treatment' COMMENT 'general_treatment|procedure|home_visit|data_sharing|other',
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|awaiting_verification|completed|declined|superseded',
  `document_id` int DEFAULT NULL COMMENT 'db_patient_documents signed scan',
  `signatories_json` text COLLATE utf8mb4_unicode_ci COMMENT '[{role:patient|guardian|witness|clinician, name, signed_at}]',
  `body_text` text COLLATE utf8mb4_unicode_ci COMMENT 'Rendered consent wording at generation time (snapshot)',
  `declined_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declined_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `superseded_by_id` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_customer_contacts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `contact_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_title` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. Lab Manager, Procurement Officer',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_customer` (`customer_id`),
  KEY `idx_contacts_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_customer_equipment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `site_id` int DEFAULT NULL COMMENT 'db_shippingaddress.id',
  `item_id` int NOT NULL,
  `barcode_id` int DEFAULT NULL COMMENT 'db_item_barcodes.id (serialised unit)',
  `serial_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_id` int DEFAULT NULL,
  `salesitems_id` int DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `install_date` date DEFAULT NULL,
  `commissioned_date` date DEFAULT NULL,
  `warranty_months` int NOT NULL DEFAULT '0',
  `warranty_start` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `coverage_type` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'standard' COMMENT 'standard|extended|contract|none',
  `equipment_status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'installed' COMMENT 'delivered|installed|in_service|under_repair|decommissioned',
  `calibration_interval_months` int DEFAULT NULL,
  `last_calibration_date` date DEFAULT NULL,
  `next_calibration_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equip_saleline` (`salesitems_id`,`barcode_id`),
  KEY `idx_equip_customer` (`customer_id`),
  KEY `idx_equip_item` (`item_id`),
  KEY `idx_equip_nextcal` (`next_calibration_date`),
  KEY `idx_equip_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_customer_segments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `name` varchar(120) NOT NULL,
  `segment_key` varchar(40) NOT NULL,
  `definition_json` text,
  `created_by` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cseg_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_daily_charges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `charge_date` date NOT NULL,
  `charge_code` varchar(40) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '1.00',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sales_id` int DEFAULT NULL,
  `sales_item_id` int DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'posted',
  `posted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_charge` (`admission_id`,`charge_date`,`charge_code`),
  KEY `ix_charge` (`store_id`,`admission_id`,`charge_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_daily_rates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `rate_code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_rate` (`store_id`,`rate_code`,`effective_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_debt_reminder_audit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `action` varchar(20) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `actor` varchar(50) DEFAULT NULL,
  `event_key` varchar(80) DEFAULT NULL,
  `amount_due` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_dra_store` (`store_id`,`action`),
  KEY `ix_dra_key` (`event_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_debt_reminder_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_mode` varchar(10) NOT NULL DEFAULT 'legacy',
  `frequency` varchar(16) NOT NULL DEFAULT 'weekly',
  `grace_days` int NOT NULL DEFAULT '0',
  `send_hour` tinyint NOT NULL DEFAULT '9',
  `max_reminders` int NOT NULL DEFAULT '0',
  `include_opening_debt` tinyint(1) NOT NULL DEFAULT '1',
  `template_key` varchar(60) NOT NULL DEFAULT 'debt_reminder',
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_drc_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_debt_reminder_pauses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `pause_scope` varchar(10) NOT NULL,
  `patient_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `paused_by` int DEFAULT NULL,
  `paused_at` datetime DEFAULT NULL,
  `resume_at` datetime DEFAULT NULL,
  `resumed_by` int DEFAULT NULL,
  `resumed_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `ix_drp_store_scope` (`store_id`,`pause_scope`,`active`),
  KEY `ix_drp_invoice` (`invoice_id`,`active`),
  KEY `ix_drp_patient` (`patient_id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_discharges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'recommended',
  `recommended_by` int DEFAULT NULL,
  `recommended_at` datetime DEFAULT NULL,
  `recommendation_note` varchar(255) DEFAULT NULL,
  `decided_by` int DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(255) DEFAULT NULL,
  `discharge_reason` varchar(255) DEFAULT NULL,
  `summary` text,
  `follow_up` varchar(255) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `actual_discharge_at` datetime DEFAULT NULL,
  `porter_task_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_discharge` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_document_access_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `document_id` int NOT NULL,
  `version_id` int DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'view|download|upload|release|verify|denied',
  `user_id` int DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc` (`document_id`),
  KEY `idx_store` (`store_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_document_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `document_id` int NOT NULL,
  `version_no` int NOT NULL DEFAULT '1',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Relative to private patient-docs dir',
  `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_evidence_json` text COLLATE utf8mb4_unicode_ci,
  `uploaded_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_doc_version` (`document_id`,`version_no`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_encounter_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `encounter_id` int NOT NULL,
  `event` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'checked_in|stage_change|vitals|note|closed',
  `from_stage` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_stage` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_enc` (`store_id`,`encounter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_encounter_vital_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `vitals_id` int NOT NULL,
  `vital_key` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'bp_systolic|bp_diastolic|pulse|temperature|spo2|resp_rate|weight|height|pain_score|other',
  `label` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value_text` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'raw display value; NULL when not_measured',
  `value_num` decimal(10,3) DEFAULT NULL COMMENT 'numeric where parseable, for trending',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mmHg|bpm|Â°C|%|/min|kg|cm|/10',
  `not_measured` tinyint(1) NOT NULL DEFAULT '0',
  `measured_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_set` (`vitals_id`),
  KEY `idx_store_vital` (`store_id`,`vital_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_encounter_vitals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `encounter_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|final',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` int DEFAULT NULL,
  `recorded_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `finalized_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_encounters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `episode_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `warehouse_id` int DEFAULT NULL,
  `appointment_id` int DEFAULT NULL,
  `count_id` int DEFAULT NULL,
  `encounter_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin_at` datetime DEFAULT NULL,
  `clinician_user_id` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT 'open|in_progress|completed|cancelled',
  `vitals_json` text COLLATE utf8mb4_unicode_ci COMMENT 'measured_at/recorder/values+units; not_measured flags distinct from zero',
  `intake_completed_at` datetime DEFAULT NULL,
  `intake_by` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `finalized_at` datetime DEFAULT NULL,
  `finalized_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin_key` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_stage` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'waiting_nurse|nursing_intake|waiting_physio|with_physio|awaiting_finance|closed',
  `queue_stage_at` datetime DEFAULT NULL,
  `assigned_to` int DEFAULT NULL COMMENT 'Current handler â€” nurse during intake, physio during consult',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_encounter_code` (`store_id`,`encounter_code`),
  UNIQUE KEY `uk_store_checkin` (`store_id`,`checkin_key`),
  KEY `idx_store_episode` (`store_id`,`episode_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_branch_status` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_external_referrals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `admission_id` int DEFAULT NULL,
  `episode_id` int DEFAULT NULL,
  `destination` varchar(160) NOT NULL,
  `reason` text,
  `urgency` varchar(15) NOT NULL DEFAULT 'routine',
  `requested_by` varchar(50) DEFAULT NULL,
  `handover_doc_id` int DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `departed_at` datetime DEFAULT NULL,
  `procedure_status` varchar(255) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `return_assessment_id` int DEFAULT NULL,
  `reviewed_by` varchar(50) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `exception_flag` tinyint(1) NOT NULL DEFAULT '0',
  `exception_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_extref` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_fleet_commands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `install_id` int NOT NULL,
  `command` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` mediumtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `resume_count` smallint unsigned NOT NULL DEFAULT '0',
  `resumed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_install_status` (`install_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fleet_installs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `install_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `install_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `license_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_left` int DEFAULT NULL,
  `usage_json` text COLLATE utf8mb4_unicode_ci,
  `admin_pass` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cron_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cron_scheduled` tinyint(1) NOT NULL DEFAULT '0',
  `cron_auto` tinyint(1) NOT NULL DEFAULT '0',
  `store_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_city` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_state` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_country` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_otp_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_otp_expires` datetime DEFAULT NULL,
  `update_stage` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_step` tinyint unsigned NOT NULL DEFAULT '0',
  `update_detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_fail_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_fail_message` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_fail_at` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `migrations_applied` int unsigned NOT NULL DEFAULT '0',
  `migration_newest` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_install_url` (`install_url`),
  KEY `idx_update_stage` (`update_stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fund_reservations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  `entitlement_id` int DEFAULT NULL,
  `amount_reserved` decimal(15,2) NOT NULL DEFAULT '0.00',
  `amount_consumed` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|exhausted|released|cancelled',
  `source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'purchase' COMMENT 'purchase|migration|grant',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_customer` (`store_id`,`customer_id`,`status`),
  KEY `idx_entitlement` (`entitlement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_import_batches` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL,
  `import_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customers',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filepath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded',
  `field_map_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dup_policy` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'skip',
  `has_header` tinyint(1) NOT NULL DEFAULT '1',
  `total_rows` int unsigned NOT NULL DEFAULT '0',
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `ok_rows` int unsigned NOT NULL DEFAULT '0',
  `error_rows` int unsigned NOT NULL DEFAULT '0',
  `dup_rows` int unsigned NOT NULL DEFAULT '0',
  `updated_rows` int unsigned NOT NULL DEFAULT '0',
  `error_message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `db_import_rows` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int unsigned NOT NULL,
  `store_id` int NOT NULL,
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `action` varchar(16) NOT NULL,
  `targets_json` text,
  `dupe_hint` varchar(160) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `rolled_back_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_import_dedupe` (`store_id`,`source_table`,`source_id`,`batch_id`),
  KEY `idx_rows_seen` (`store_id`,`source_table`,`source_id`),
  KEY `idx_rows_batch` (`batch_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_inpatient_policies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `policy_key` varchar(40) NOT NULL,
  `policy_value` varchar(160) DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_policy` (`store_id`,`policy_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_investigation_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `investigation_id` int NOT NULL,
  `version_no` int NOT NULL DEFAULT '1',
  `document_id` int DEFAULT NULL COMMENT 'db_patient_documents attachment',
  `result_summary` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'current' COMMENT 'current|superseded',
  `entered_by` int DEFAULT NULL,
  `entered_at` datetime DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `supersedes_id` int DEFAULT NULL COMMENT 'Result version this replaces',
  `amend_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mandatory on amendment',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inv_version` (`investigation_id`,`version_no`),
  KEY `idx_store` (`store_id`,`investigation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_investigations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `episode_id` int DEFAULT NULL,
  `encounter_id` int DEFAULT NULL,
  `count_id` int DEFAULT NULL,
  `request_ref` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'imaging|lab|functional|other',
  `priority` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'routine' COMMENT 'routine|urgent',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested' COMMENT 'requested|pending|result_received|reviewed|cancelled',
  `facility_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'External lab/imaging centre; NULL = in-house',
  `external_ref` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_notes` text COLLATE utf8mb4_unicode_ci,
  `requested_by` int DEFAULT NULL,
  `requested_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `result_summary` text COLLATE utf8mb4_unicode_ci,
  `result_document_id` int DEFAULT NULL COMMENT 'db_patient_documents attachment',
  `result_received_at` datetime DEFAULT NULL,
  `result_entered_by` int DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `cancelled_by` int DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_req_ref` (`store_id`,`request_ref`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_item_addons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `item_id` int DEFAULT NULL,
  `service_id` int DEFAULT NULL,
  `linked_item_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_qty` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`store_id`,`item_id`),
  KEY `idx_service` (`store_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_item_upsells` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `trigger_item_id` int DEFAULT NULL,
  `upsell_item_id` int NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_upsell` (`store_id`,`trigger_item_id`,`upsell_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_lead_activities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `activity_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'note|call|whatsapp|email|visit|status_change|assignment|followup|appointment|converted',
  `note` text COLLATE utf8mb4_unicode_ci,
  `meta_json` text COLLATE utf8mb4_unicode_ci COMMENT 'Structured detail: old/new status, assignee, appt ref',
  `created_by` int DEFAULT NULL COMMENT 'db_users.id',
  `created_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_lead` (`store_id`,`lead_id`),
  KEY `idx_lead_time` (`lead_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_leave_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `expected_return` datetime DEFAULT NULL,
  `billing_policy` varchar(15) NOT NULL DEFAULT 'half',
  `bed_hold` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(20) NOT NULL DEFAULT 'requested',
  `requested_by` varchar(50) DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `departed_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `overdue` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_leave` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_marketing_suppressions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `channel` varchar(20) NOT NULL DEFAULT 'all',
  `recipient` varchar(191) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'link',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_msupp` (`store_id`,`channel`,`recipient`),
  KEY `idx_msupp_recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_meal_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `meal_date` date NOT NULL,
  `slot` varchar(20) NOT NULL,
  `meal_type_id` int NOT NULL,
  `diet_note` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ordered',
  `ordered_by` varchar(50) DEFAULT NULL,
  `provided_by` int DEFAULT NULL,
  `provided_at` datetime DEFAULT NULL,
  `charge_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_meal` (`store_id`,`admission_id`,`meal_date`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_meal_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `name` varchar(80) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'any',
  `charge` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mealtype` (`store_id`,`name`,`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_migration_batches` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `source_system` varchar(40) NOT NULL,
  `label` varchar(160) DEFAULT NULL,
  `mode` varchar(12) NOT NULL DEFAULT 'import',
  `status` varchar(24) NOT NULL DEFAULT 'running',
  `rows_seen` int NOT NULL DEFAULT '0',
  `rows_imported` int NOT NULL DEFAULT '0',
  `rows_skipped` int NOT NULL DEFAULT '0',
  `rows_conflicts` int NOT NULL DEFAULT '0',
  `rows_failed` int NOT NULL DEFAULT '0',
  `checkpoint_json` text,
  `started_by` int DEFAULT NULL,
  `started_by_name` varchar(80) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `rolled_back_at` datetime DEFAULT NULL,
  `rolled_back_by` int DEFAULT NULL,
  `notes` text,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_batch_store` (`store_id`,`source_system`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_migration_rows` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int unsigned NOT NULL,
  `store_id` int NOT NULL,
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `action` varchar(16) NOT NULL,
  `targets_json` text,
  `dupe_hint` varchar(160) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `rolled_back_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_import_dedupe` (`store_id`,`source_table`,`source_id`,`batch_id`),
  KEY `idx_rows_seen` (`store_id`,`source_table`,`source_id`),
  KEY `idx_rows_batch` (`batch_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_notification_queue` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `event_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Idempotent event reference',
  `channel` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email' COMMENT 'email|sms',
  `template_key` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_json` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued' COMMENT 'queued|sent|failed|suppressed',
  `attempts` int NOT NULL DEFAULT '0',
  `last_error` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_event` (`store_id`,`event_key`),
  KEY `idx_status_sched` (`status`,`scheduled_at`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nursing_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `task_id` int DEFAULT NULL,
  `note_type` varchar(20) NOT NULL,
  `shift` varchar(10) DEFAULT NULL,
  `body` text,
  `recorded_by` varchar(50) DEFAULT NULL,
  `recorded_by_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_nnote` (`store_id`,`admission_id`,`note_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_nursing_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `task_code` varchar(40) NOT NULL,
  `label` varchar(160) NOT NULL,
  `task_date` date NOT NULL,
  `due_at` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `done_at` datetime DEFAULT NULL,
  `done_by` int DEFAULT NULL,
  `result_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ntask` (`admission_id`,`task_code`,`task_date`),
  KEY `ix_ntask` (`store_id`,`status`,`due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_nylon_artworks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `custom_order_id` int NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_no` int NOT NULL DEFAULT '1',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`custom_order_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_item_specs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `item_id` int NOT NULL,
  `item_class` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'finished_good' COMMENT 'raw_material|film_roll|finished_good|consumable',
  `material` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'LDPE|HDPE|LLDPE|PP|Recycled|Other',
  `product_form` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bag|film_roll|sheet|tubing|liners',
  `bag_type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'vest|flat|punch-handle|zip|garbage|bread|other',
  `width_cm` decimal(10,2) DEFAULT NULL,
  `length_cm` decimal(10,2) DEFAULT NULL,
  `thickness_micron` decimal(10,2) DEFAULT NULL,
  `colour` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|surface|flexo|gravure|custom',
  `design_ref` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Customer-specific design / artwork reference',
  `kg_per_piece` decimal(12,6) DEFAULT NULL COMMENT 'Explicit per-product conversion; NULL = unknown, never assumed',
  `kg_per_roll` decimal(12,4) DEFAULT NULL COMMENT 'For film rolls: weight of one standard roll',
  `pieces_per_roll` decimal(12,2) DEFAULT NULL COMMENT 'For converted rolls',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_item` (`store_id`,`item_id`),
  KEY `idx_class` (`item_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_costs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `cost_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'labour|machine|power|packaging|overhead|other',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated` tinyint(1) NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `stage_id` int NOT NULL,
  `shift_label` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Morning|Afternoon|Night|free text',
  `machine_id` int DEFAULT NULL,
  `operator_id` int DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `qty_in` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'Input consumed, in the input item base unit',
  `good_qty` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'Saleable/WIP output, in the output item base unit',
  `reject_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `scrap_qty` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'Reusable scrap returned to a scrap item',
  `waste_qty` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'Unrecoverable waste â€” recorded, no stock credit',
  `scrap_item_id` int DEFAULT NULL COMMENT 'Item credited with reusable scrap',
  `unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'Input item cost per base unit at posting time',
  `material_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|approved|reversed',
  `adjustment_id` int DEFAULT NULL COMMENT 'db_stockadjustment id that moved the stock',
  `reversal_adjustment_id` int DEFAULT NULL,
  `submitted_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `reversed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL,
  `reversal_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_stage` (`stage_id`),
  KEY `store_id` (`store_id`),
  KEY `idx_work_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_stages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `seq` int NOT NULL DEFAULT '1',
  `stage_key` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|in_progress|done|skipped',
  `input_item_id` int DEFAULT NULL,
  `output_item_id` int DEFAULT NULL,
  `planned_input_qty` decimal(15,3) DEFAULT NULL,
  `planned_output_qty` decimal(15,3) DEFAULT NULL,
  `machine_id` int DEFAULT NULL,
  `requires_artwork` tinyint(1) NOT NULL DEFAULT '0',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_jobs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `warehouse_id` int NOT NULL DEFAULT '0',
  `job_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_kind` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order' COMMENT 'order|stock',
  `custom_order_id` int DEFAULT NULL,
  `product_item_id` int DEFAULT NULL COMMENT 'Finished item being produced (may be a film roll)',
  `planned_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `planned_unit_id` int DEFAULT NULL,
  `pipeline` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot of stage keys built at creation, comma separated',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned' COMMENT 'planned|in_progress|on_hold|completed|cancelled',
  `priority` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `due_date` date DEFAULT NULL,
  `est_material_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `est_other_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `act_material_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `act_other_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`store_id`,`job_code`),
  KEY `idx_order` (`custom_order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_machines` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `machine_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `machine_name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `machine_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'extruder|printer|cutter|sealer|puncher|packer|other',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `idx_type` (`machine_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_opening_position_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `position_id` int NOT NULL,
  `item_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'debt|unused_money|unused_sessions|active_admission',
  `payload_json` text COLLATE utf8mb4_unicode_ci COMMENT 'Type-specific detail: payer, evidence ref, plan/service, units...',
  `amount` decimal(15,2) DEFAULT NULL COMMENT 'NULL = unknown/pending, never invented zero',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|entered|reviewed|approved|rejected',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_document_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_position` (`position_id`),
  KEY `idx_store_type` (`store_id`,`item_type`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_opening_positions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `cutoff_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imported' COMMENT 'imported|position_pending|entered|reviewed|approved|rejected',
  `entered_by` int DEFAULT NULL,
  `entered_at` datetime DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `reject_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wallet_balance' COMMENT 'wallet_balance|prepaid_sessions|outstanding_debt',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `units` decimal(10,2) NOT NULL DEFAULT '0.00',
  `service_item_id` int DEFAULT NULL,
  `ref_id` int DEFAULT NULL COMMENT 'Posted wallet txn / entitlement / sale id after approval',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_bill_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `sales_id` int NOT NULL,
  `sales_item_id` int DEFAULT NULL COMMENT 'db_salesitems row',
  `plan_id` int DEFAULT NULL,
  `plan_item_id` int DEFAULT NULL,
  `plan_version` int DEFAULT NULL,
  `item_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_amt` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entitlement_id` int DEFAULT NULL COMMENT 'Entitlement spawned once line is paid/funded',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sales` (`sales_id`),
  KEY `idx_plan` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `episode_id` int DEFAULT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'consent|assessment|investigation_result|session_report|referral|external_procedure|prescription|discharge|payment_evidence|other',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|awaiting_signatures|awaiting_verification|completed|declined|superseded',
  `current_version_id` int DEFAULT NULL,
  `signatories_json` text COLLATE utf8mb4_unicode_ci COMMENT 'required signatories incl. proxy capacity',
  `released_to_patient` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Explicit clinical release only',
  `released_by` int DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `withdrawn_by` int DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `withdraw_reason` varchar(240) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_category` (`store_id`,`category`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `event` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'registered|deceased|deceased_corrected|duplicate_flagged|status_change|linked_customer',
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_json` text COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_event` (`store_id`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `ref_type` varchar(20) NOT NULL,
  `ref_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `is_private` tinyint(1) NOT NULL DEFAULT '1',
  `follow_up_status` varchar(20) NOT NULL DEFAULT 'new',
  `follow_up_by` int DEFAULT NULL,
  `follow_up_at` datetime DEFAULT NULL,
  `follow_up_note` varchar(500) DEFAULT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'portal',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_feedback_ref` (`store_id`,`patient_id`,`ref_type`,`ref_id`),
  KEY `ix_feedback_store` (`store_id`,`follow_up_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_patient_portal_proxies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `relationship` varchar(80) DEFAULT NULL,
  `identity` varchar(150) NOT NULL,
  `scope_csv` varchar(255) NOT NULL DEFAULT 'appointments,progress',
  `auth_secret` varchar(255) DEFAULT NULL,
  `invite_token_hash` varchar(64) DEFAULT NULL,
  `invite_expires_at` datetime DEFAULT NULL,
  `invited_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `auth_version` int NOT NULL DEFAULT '1',
  `failed_attempts` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'invited',
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proxy_identity` (`store_id`,`patient_id`,`identity`),
  KEY `ix_proxy_patient` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_patient_portal_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `identity` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Verified email or phone',
  `auth_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Password/PIN hash',
  `verified_at` datetime DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invited' COMMENT 'invited|active|suspended',
  `last_login_at` datetime DEFAULT NULL,
  `failed_attempts` int NOT NULL DEFAULT '0',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invite_token_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invite_expires_at` datetime DEFAULT NULL,
  `invited_by` int DEFAULT NULL,
  `auth_version` int NOT NULL DEFAULT '1',
  `locked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_identity` (`store_id`,`identity`),
  KEY `idx_store_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_proxies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `portal_user_id` int NOT NULL COMMENT 'The proxy caregiver account',
  `relationship` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope_json` text COLLATE utf8mb4_unicode_ci COMMENT 'Explicit granted scopes',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|revoked',
  `granted_by` int DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_portal_user` (`portal_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patient_testimonials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `feedback_id` int DEFAULT NULL,
  `body` text NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `display_mode` varchar(20) NOT NULL DEFAULT 'anonymous',
  `display_name` varchar(100) DEFAULT NULL,
  `publish_consent` tinyint(1) NOT NULL DEFAULT '0',
  `consent_at` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `moderated_by` int DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `moderation_note` varchar(255) DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_testimonial_store` (`store_id`,`status`),
  KEY `ix_testimonial_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_patient_wallet_txns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `txn_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'fund|reserve|release|consume|refund|adjust|opening',
  `direction` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'credit' COMMENT 'credit|debit|memo',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `operation_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique idempotency key, e.g. session:12:consume',
  `reservation_id` int DEFAULT NULL,
  `sales_id` int DEFAULT NULL,
  `salespayment_id` int DEFAULT NULL,
  `custadvance_id` int DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_op` (`store_id`,`operation_key`),
  KEY `idx_store_customer` (`store_id`,`customer_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_reservation` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_patients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL COMMENT '1:1 link to db_customers (financial identity)',
  `count_id` int DEFAULT NULL COMMENT 'Store-scoped sequence for patient_code',
  `patient_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `marital_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nok_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Next of kin',
  `nok_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nok_relationship` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_ids_json` text COLLATE utf8mb4_unicode_ci COMMENT 'External system IDs, e.g. {"smart_hospital":"123"}',
  `portal_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|invited|active|suspended',
  `deceased` tinyint(1) NOT NULL DEFAULT '0',
  `deceased_date` date DEFAULT NULL,
  `deceased_recorded_by` int DEFAULT NULL,
  `deceased_notes` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `duplicate_of_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_customer` (`store_id`,`customer_id`),
  UNIQUE KEY `uk_store_patient_code` (`store_id`,`patient_code`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_nok_phone` (`nok_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_payment_evidence` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `channel` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'transfer|pos|cash|online',
  `payer_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'May differ from patient (family/employer/insurer)',
  `payment_ref` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_id` int DEFAULT NULL COMMENT 'db_patient_documents evidence file',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|verified|rejected',
  `verified_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `reject_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custadvance_id` int DEFAULT NULL COMMENT 'Set when verification posts the advance',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_id` int DEFAULT NULL,
  `plan_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_ref` (`store_id`,`payment_ref`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_sale` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_payment_exceptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `exception_type` varchar(32) NOT NULL DEFAULT 'unmatched',
  `provider` varchar(32) NOT NULL DEFAULT 'manual',
  `provider_owner` varchar(16) NOT NULL DEFAULT 'merchant',
  `reference` varchar(191) NOT NULL DEFAULT '',
  `sales_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `salespayment_id` int DEFAULT NULL,
  `amount` decimal(15,4) DEFAULT NULL,
  `expected_amount` decimal(15,4) DEFAULT NULL,
  `currency` varchar(8) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'open',
  `detail` text,
  `detected_by` varchar(32) NOT NULL DEFAULT 'scan',
  `payload` mediumtext,
  `dedupe_key` char(32) NOT NULL DEFAULT '',
  `created_date` datetime DEFAULT NULL,
  `created_by` varchar(64) DEFAULT NULL,
  `resolved_date` datetime DEFAULT NULL,
  `resolved_by` varchar(64) DEFAULT NULL,
  `resolution_note` text,
  `status_flag` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exc_dedupe` (`store_id`,`dedupe_key`),
  KEY `idx_exc_store_status` (`store_id`,`status`),
  KEY `idx_exc_sales` (`sales_id`),
  KEY `idx_exc_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_permission_revocations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `role_id` int NOT NULL,
  `permissions` varchar(100) NOT NULL,
  `revoked_by` varchar(50) DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_revocation` (`store_id`,`role_id`,`permissions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_plan_entitlements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `plan_id` int DEFAULT NULL,
  `sale_id` int DEFAULT NULL COMMENT 'Purchase invoice, when bought via POS',
  `service_id` int DEFAULT NULL,
  `units_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `units_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `funded_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `consumed_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `source` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'purchase' COMMENT 'purchase|migration|grant',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|exhausted|cancelled|expired',
  `expiry_date` date DEFAULT NULL,
  `valuation_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Basis for migrated/unfunded units',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_sale` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_portal_policies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `policy_key` varchar(60) NOT NULL,
  `policy_value` varchar(255) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_policy` (`store_id`,`policy_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_porter_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `task_type` varchar(30) NOT NULL,
  `ref_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `from_location` varchar(120) DEFAULT NULL,
  `to_location` varchar(120) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `requested_by` varchar(50) DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `notes` text,
  `completed_by` int DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_ptask` (`store_id`,`status`,`task_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_print_artworks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `version_no` int NOT NULL DEFAULT '1',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'immutable content hash',
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded' COMMENT 'uploaded|approved|rejected',
  `customer_approved` tinyint(1) NOT NULL DEFAULT '0',
  `customer_approved_at` datetime DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_version` (`job_id`,`version_no`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_authorizations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `artwork_id` int DEFAULT NULL,
  `artwork_version` int NOT NULL DEFAULT '0' COMMENT 'fingerprint — version change invalidates',
  `approver_id` int DEFAULT NULL,
  `backup_approver_id` int DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested' COMMENT 'requested|authorized|rejected|returned|invalidated',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urgent_override` tinyint(1) NOT NULL DEFAULT '0',
  `override_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decided_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `category_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec_schema_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ordered field template for intake/spec',
  `stage_preset_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ordered default stage keys for this category',
  `size_units_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Allowed dimension units, e.g. ["mm","cm","in","m"]',
  `supplies_material` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Category accepts customer-supplied materials',
  `sort_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'seeded preset (still configurable)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat` (`store_id`,`category_key`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_category_calcs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `category_id` int NOT NULL,
  `calc_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sheet|roll_area|imposition|garment_sizes|none',
  `config_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'e.g. {sheets_per_pack:500,roll_width_cm:106,ups_per_sheet:4}',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat` (`store_id`,`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_clearances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `artwork_id` int DEFAULT NULL,
  `decision` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|cleared|rejected',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleared_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleared_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_consumable_allocations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `consumable_id` int NOT NULL,
  `job_id` int NOT NULL,
  `basis_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'impressions/area/volume used',
  `estimated` tinyint(1) NOT NULL DEFAULT '1',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_consumable` (`consumable_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_custody_moves` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `material_id` int NOT NULL COMMENT 'db_print_customer_materials.id',
  `customer_id` int DEFAULT NULL,
  `job_id` int DEFAULT NULL,
  `batch_ref` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'groups the signed legs of one logical movement',
  `move_type` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'receive|issue|finish|damage|handover_finished|handover_unused|allocate|cancel|adjust|consume|transfer_out|transfer_in',
  `from_position` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NULL for the initial receipt',
  `to_position` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `size_breakdown_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'per-move size split when known',
  `colour_breakdown_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stage_id` int DEFAULT NULL,
  `stage_log_id` int DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'REQUIRED for damage, adjust, cancel',
  `authorized_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'who authorized the movement',
  `reviewed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'authorized review of damage/adjustment/discrepancy',
  `reviewed_at` datetime DEFAULT NULL,
  `review_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n/a' COMMENT 'n/a|pending|reviewed|rejected',
  `outcome_type` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'n/a|disposal|replacement|compensation|discount|no_liability|undecided',
  `outcome_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outcome_cost` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'compensation/replacement cost belonging to the JOB, not to company material',
  `reverses_move_id` int DEFAULT NULL,
  `handover_to` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'recipient on a handover',
  `handover_reference` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_batch` (`batch_ref`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_type` (`move_type`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_customer_materials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `job_id` int DEFAULT NULL COMMENT 'NULL = received before a job exists (unallocated balance)',
  `line_id` int DEFAULT NULL,
  `receipt_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_type` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'garment|fabric|paper|substrate|other',
  `item_id` int DEFAULT NULL COMMENT 'link to a NON-STOCK item if the job line uses one',
  `unit_id` int DEFAULT NULL,
  `unit_label` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_received` decimal(15,3) NOT NULL DEFAULT '0.000',
  `condition_in` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'good' COMMENT 'good|fair|damaged|mixed|unknown',
  `condition_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_breakdown_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'garments: {"S":10,"M":20,"L":5}',
  `colour_breakdown_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'colours: {"White":30,"Navy":25}',
  `breakdown_total` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'sum of the breakdowns; must reconcile to qty_received or the gap is flagged',
  `breakdown_mismatch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = breakdown does not reconcile; flagged, never silently corrected',
  `received_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `acknowledged_by` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'who signed the material in',
  `acknowledged_at` datetime DEFAULT NULL,
  `ack_reference` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT 'open|allocated|partially_processed|processed|closed|cancelled',
  `qty_custody` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_in_production` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_finished` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_damaged` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_returned` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_collected_finished` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_consumed` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_allocated_other_jobs` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'moved to another job of the SAME customer',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt_code` (`store_id`,`receipt_code`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_enquiry_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `lead_id` int NOT NULL,
  `original_name` varchar(240) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_print_enquiry_lead` (`store_id`,`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_fulfilments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `kind` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collection' COMMENT 'collection|delivery',
  `recipient_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_signature_ref` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfilled_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfilled_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_item_cost_estimates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int NOT NULL,
  `cost_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'labour' COMMENT 'design|labour|machine|outsource|other',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = estimated allocation, 0 = measured',
  `basis` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'impressions|area|machine_reading|manual',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_item_design` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int NOT NULL,
  `design_mode` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|customer_supplied|new_design|modify_supplied|reuse_previous',
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `designer_id` int DEFAULT NULL COMMENT 'assigned staff / designer',
  `expected_date` date DEFAULT NULL,
  `source_artwork_id` int DEFAULT NULL COMMENT 'reuse_previous / modify: which artwork',
  `charge_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'original customer design charge',
  `charge_waived` tinyint(1) NOT NULL DEFAULT '0',
  `waived_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `waiver_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waived_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waived_at` datetime DEFAULT NULL,
  `internal_cost` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'internal design cost incl. outsourcing',
  `internal_cost_basis` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_item_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int NOT NULL,
  `plan_type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'material' COMMENT 'material|operation',
  `item_id` int DEFAULT NULL COMMENT 'db_items.id for inventory materials',
  `operation_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'cutting|trimming|binding|lamination|pressing|other',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `plan_unit_id` int DEFAULT NULL COMMENT 'unit the plan is expressed in',
  `base_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'plan_qty converted to the item base/stock unit',
  `base_unit_id` int DEFAULT NULL,
  `conversion_factor` decimal(18,6) NOT NULL DEFAULT '1.000000' COMMENT 'explicit factor applied (never assumed)',
  `conversion_ok` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = no known conversion; flagged for manual review',
  `est_unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `est_total_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `wastage_pct` decimal(8,3) NOT NULL DEFAULT '0.000' COMMENT 'setup/wastage allowance %',
  `wastage_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `roll_width` decimal(15,4) DEFAULT NULL,
  `roll_length` decimal(15,4) DEFAULT NULL,
  `area_sqm` decimal(15,4) DEFAULT NULL,
  `cost_basis` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'estimated|manual|measured',
  `template_key` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'which category/product template populated this',
  `quotation_version` int NOT NULL DEFAULT '1' COMMENT 'estimate is preserved against this quotation version',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_item_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int NOT NULL,
  `service_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'design',
  `design_mode` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'only when the type is mode-aware',
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assignee_id` int DEFAULT NULL COMMENT 'assigned staff / designer / installer',
  `expected_date` date DEFAULT NULL,
  `source_artwork_id` int DEFAULT NULL,
  `charge_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `charge_waived` tinyint(1) NOT NULL DEFAULT '0',
  `waived_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `waiver_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waived_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waived_at` datetime DEFAULT NULL,
  `internal_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `internal_cost_basis` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`job_id`,`line_id`),
  KEY `idx_type` (`service_key`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_job_lines` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `category_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'snapshot key',
  `item_id` int DEFAULT NULL COMMENT 'material/output item link if stock-tracked',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'per-line spec (dimensions, units, colors, size breakdown)',
  `schema_version` int NOT NULL DEFAULT '1' COMMENT 'spec schema version captured with',
  `qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `unit_id` int DEFAULT NULL,
  `width` decimal(15,3) DEFAULT NULL,
  `height` decimal(15,3) DEFAULT NULL,
  `dim_unit` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_breakdown_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'apparel size counts {"S":10,"M":20}',
  `supplied_material` tinyint(1) NOT NULL DEFAULT '0',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `customer_supplied_material` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = customer-owned stock, never deducted from inventory',
  `quotation_item_id` int DEFAULT NULL COMMENT 'Maps this print line to its db_quotationitems row',
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'in_percentage | fixed',
  `discount_input` decimal(15,4) DEFAULT NULL COMMENT 'percent value or fixed amount',
  `discount_amt` decimal(15,2) DEFAULT NULL,
  `tax_id` int DEFAULT NULL COMMENT 'db_tax.id; NULL falls back to the job-level tax',
  `tax_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Inclusive | Exclusive',
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_category` (`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_jobs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `warehouse_id` int NOT NULL DEFAULT '0',
  `job_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_order_id` int DEFAULT NULL COMMENT 'commercial parent (quotation/balance)',
  `customer_id` int DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specifications_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'snapshot spec at intake',
  `planned_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `unit_id` int DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `quotation_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|draft|issued|accepted|converted',
  `quotation_id` int DEFAULT NULL,
  `quote_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deposit_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deposit_policy_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'snapshot of configurable deposit rule at intake',
  `artwork_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|uploaded|approved|rejected',
  `design_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|pending|cleared|rejected',
  `authorization_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|requested|authorized|rejected|invalidated',
  `payment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid' COMMENT 'unpaid|partial|verified|paid',
  `production_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned' COMMENT 'planned|in_progress|on_hold|completed|cancelled',
  `fulfilment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|partially_collected|collected|delivered',
  `est_material_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `est_labour_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `est_outsource_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `act_material_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `act_labour_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `act_outsource_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cost_complete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'all postable costs recorded',
  `referral_id` int DEFAULT NULL,
  `referral_rule_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'rule snapshot at attribution',
  `referral_eligible_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `referral_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|attributed|earned|payable|paid|recovered|cancelled',
  `referral_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` int DEFAULT NULL COMMENT 'assigned owner (staff)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `quotation_revision_accepted` int DEFAULT NULL COMMENT 'Revision number the customer accepted (NULL = none)',
  `quotation_accepted_at` datetime DEFAULT NULL COMMENT 'When the customer accepted that revision',
  `quote_reaccept_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Set when a new revision changes agreed specs/price/terms',
  `quote_reaccept_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_on` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = exempt (default), 1 = apply tax to the quotation',
  `tax_id` int DEFAULT NULL COMMENT 'db_tax.id when tax_on = 1',
  `tax_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Inclusive | Exclusive',
  `tax_rate` decimal(10,4) DEFAULT NULL COMMENT 'Rate snapshot at issue time',
  `tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax on the quotation subtotal',
  `quote_lifecycle_status` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'issued' COMMENT 'mirrors db_quotation.lifecycle_status',
  `quote_response_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quote_expired_at` datetime DEFAULT NULL,
  `invoice_style` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'combined|detailed — how job lines are grouped on the invoice',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`store_id`,`job_code`),
  KEY `idx_order` (`custom_order_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_production` (`production_status`),
  KEY `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_machine_consumables` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `machine_ref` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'machine name/id on the print stage',
  `item_id` int DEFAULT NULL COMMENT 'db_items.id if stock-tracked',
  `consumable_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toner' COMMENT 'ink|toner|printhead|drum|blade|other',
  `installed_at` datetime DEFAULT NULL,
  `installed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity_basis` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'impressions|area|volume|weight',
  `capacity_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `remaining_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'capacity left for allocation',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine` (`machine_ref`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_machine_maintenance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `machine_id` int NOT NULL,
  `visit_type` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'service' COMMENT 'service|repair|fault|inspection|calibration|install|other',
  `performed_by_type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal' COMMENT 'internal|external',
  `technician_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_contact` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_date` date NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `downtime_minutes` int NOT NULL DEFAULT '0',
  `fault_code` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `symptom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `work_performed` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `labour_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `parts_cost` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'parts BOUGHT for this visit, not already-issued stock',
  `outsource_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `other_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `parts_issued_value` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'value of parts already issued from stock — deliberately NOT added to total_cost',
  `expense_id` int DEFAULT NULL COMMENT 'db_expense.id when the visit raises an expense; NULL when it does not',
  `reading_id` int DEFAULT NULL COMMENT 'db_print_machine_readings.id recorded at service',
  `mono_at_service` decimal(20,3) DEFAULT NULL,
  `colour_at_service` decimal(20,3) DEFAULT NULL,
  `next_service_at` date DEFAULT NULL,
  `next_service_impressions` decimal(20,3) DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'closed' COMMENT 'open|closed',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `recorded_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine_date` (`machine_id`,`visit_date`),
  KEY `idx_type` (`visit_type`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_machine_readings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `machine_id` int NOT NULL,
  `reading_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'run' COMMENT 'opening|run|correction|reset|replacement|service|manual',
  `reading_date` date NOT NULL,
  `reading_time` time DEFAULT NULL,
  `mono_reading` decimal(20,3) DEFAULT NULL,
  `colour_reading` decimal(20,3) DEFAULT NULL,
  `counter_unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'impressions',
  `prev_reading_id` int DEFAULT NULL,
  `mono_delta` decimal(20,3) DEFAULT NULL,
  `colour_delta` decimal(20,3) DEFAULT NULL,
  `delta` decimal(20,3) DEFAULT NULL COMMENT 'total counter activity; NULL when invalid',
  `delta_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = reset/replacement/backwards — not activity',
  `delta_invalid_reason` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_baseline` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = baseline only, never output or cost',
  `job_id` int DEFAULT NULL,
  `stage_id` int DEFAULT NULL,
  `stage_log_id` int DEFAULT NULL COMMENT 'db_print_stage_logs.id when entered with a run',
  `corrects_reading_id` int DEFAULT NULL COMMENT 'correction chain — the superseded row is kept',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'REQUIRED for correction/reset/replacement',
  `authorized_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'authorized handling of a correction/reset/replacement',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recorded' COMMENT 'recorded|superseded|void',
  `recorded_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine_date` (`machine_id`,`reading_date`),
  KEY `idx_type` (`reading_type`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_machine_supplies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `machine_id` int DEFAULT NULL COMMENT 'NULL only for opening_loaded rows captured before the machine is registered',
  `item_id` int DEFAULT NULL COMMENT 'db_items.id for stock-tracked consumables/parts',
  `supply_type` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toner' COMMENT 'ink|toner|printhead|drum|blade|fuser|part|other',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unit_id` int DEFAULT NULL,
  `base_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'requested qty converted to the item base unit',
  `conversion_factor` decimal(18,6) NOT NULL DEFAULT '1.000000',
  `conversion_ok` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = no known conversion; flagged, never assumed',
  `issued_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `installed_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `consumed_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'manually reported consumption',
  `returned_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'unused quantity returned to stores',
  `status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested' COMMENT 'requested|approved|issued|partially_installed|installed|consumed|partially_returned|returned|rejected|cancelled',
  `unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `issued_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `consumed_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cost_known` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = opening stock whose cost is explicitly unknown',
  `opening_loaded` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = captured as already loaded, not a new issue',
  `allocated_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `allocated_estimated` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = estimated allocation, 0 = measured',
  `capacity_basis` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'impressions|area|volume|weight',
  `capacity_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'total life of this supply for allocation purposes',
  `remaining_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'capacity left to allocate to jobs',
  `approval_log_id` int DEFAULT NULL COMMENT 'db_approval_logs.id',
  `issue_adjustment_id` int DEFAULT NULL COMMENT 'db_stockadjustment.id — the ONE deduction',
  `return_adjustment_id` int DEFAULT NULL COMMENT 'db_stockadjustment.id — the ONE credit back',
  `reversal_adjustment_id` int DEFAULT NULL,
  `job_id` int DEFAULT NULL,
  `stage_id` int DEFAULT NULL,
  `stage_log_id` int DEFAULT NULL,
  `warehouse_id` int DEFAULT NULL,
  `requested_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` datetime DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `installed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` datetime DEFAULT NULL,
  `returned_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `return_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine` (`machine_id`),
  KEY `idx_status` (`status`),
  KEY `idx_job` (`job_id`),
  KEY `idx_item` (`item_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_machines` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `branch_id` int NOT NULL DEFAULT '0' COMMENT '0 = store default branch',
  `machine_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'shop-unique identifier / asset tag',
  `name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_no` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL COMMENT 'db_print_categories.id this machine mainly serves',
  `machine_category` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'press|digital|large_format|cutting|finishing|binding|other',
  `location` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supported_stages_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'stage keys this machine can perform',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available' COMMENT 'available|maintenance|out_of_service',
  `status_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_changed_at` datetime DEFAULT NULL,
  `status_changed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reading_mode` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'optional' COMMENT 'required|optional|unavailable',
  `has_colour_counter` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = separate colour + mono counters',
  `counter_unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'impressions' COMMENT 'impressions|sheets|metres|minutes|units',
  `counter_rollover_at` decimal(20,3) DEFAULT NULL COMMENT 'counter wraps here; differences across a wrap are computed, not guessed',
  `supports_multiple_loads` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = more than one consumable may be loaded at once (CMYK)',
  `service_interval_days` int DEFAULT NULL COMMENT 'calendar-based service interval',
  `service_interval_impressions` decimal(20,3) DEFAULT NULL COMMENT 'counter-based service interval',
  `last_service_at` date DEFAULT NULL,
  `next_service_at` date DEFAULT NULL,
  `next_service_impressions` decimal(20,3) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = retired from the register (history kept)',
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_machine_code` (`store_id`,`machine_code`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`machine_category`),
  KEY `idx_active` (`status_active`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_maintenance_parts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `maintenance_id` int NOT NULL,
  `supply_id` int DEFAULT NULL COMMENT 'db_print_machine_supplies.id when the part came from stores',
  `item_id` int DEFAULT NULL,
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unit_id` int DEFAULT NULL,
  `unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `from_inventory` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = already deducted by its issuance; NOT expensed again',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visit_supply` (`maintenance_id`,`supply_id`),
  KEY `idx_maintenance` (`maintenance_id`),
  KEY `idx_supply` (`supply_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_material_issues` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int NOT NULL,
  `plan_id` int DEFAULT NULL COMMENT 'db_print_item_plans row this issues for',
  `item_id` int DEFAULT NULL,
  `warehouse_id` int DEFAULT NULL,
  `status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved' COMMENT 'reserved|issued|partially_consumed|consumed|returned|released',
  `planned_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'planned requirement incl. wastage',
  `reserved_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'soft reservation (no stock move)',
  `issued_qty` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'deducted from stock at issue',
  `consumed_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `returned_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `wastage_qty` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unit_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `issued_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `consumed_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `reserve_ref` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_adjustment_id` int DEFAULT NULL COMMENT 'db_stockadjustment id for the issue (deduction)',
  `consume_adjustment_id` int DEFAULT NULL COMMENT 'WIP consumption (no stock move; kept for trace)',
  `return_adjustment_id` int DEFAULT NULL COMMENT 'db_stockadjustment id for the return (credit back)',
  `reversal_adjustment_id` int DEFAULT NULL,
  `issued_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `consumed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `payment_kind` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deposit' COMMENT 'deposit|collection|refund|credit|reversal',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `method` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received' COMMENT 'received|unverified|verified|reversed',
  `ledger_payment_id` int DEFAULT NULL COMMENT 'db_salespayments.id',
  `ledger_ref` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_quotation_review` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `quotation_id` int NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `decision` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|linked|lapsed|kept',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quotation` (`store_id`,`quotation_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_referral_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `referral_id` int NOT NULL,
  `kind` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payout' COMMENT 'payout|recovery|adjustment',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral` (`referral_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_referrals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `partner_id` int DEFAULT NULL COMMENT 'optional db_users / partner',
  `source_ref` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rule_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'snapshot: {rate_type, rate, eligible_basis, exclude_tax, exclude_pass_through}',
  `eligible_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `projected_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `earned_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payable_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attributed' COMMENT 'attributed|earned|payable|paid|recovered|cancelled',
  `payout_ref` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `paid_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_schema_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `category_id` int NOT NULL,
  `category_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_no` int NOT NULL DEFAULT '1',
  `schema_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `changed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_ver` (`category_id`,`version_no`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_service_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `service_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode_aware` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = exposes the design-mode choices (customer_supplied/new_design/…)',
  `icon` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-plus',
  `sort_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_type` (`store_id`,`service_key`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_spec_change_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `line_id` int DEFAULT NULL,
  `change_type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'specification' COMMENT 'specification|material|dimension|finishing|quantity',
  `original_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revised_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cost_impact` decimal(15,2) NOT NULL DEFAULT '0.00',
  `original_deadline` date DEFAULT NULL,
  `revised_deadline` date DEFAULT NULL,
  `reapproval_required` tinyint(1) NOT NULL DEFAULT '1',
  `reapproval_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
  `reauthorization_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|authorized|rejected',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_stage_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `stage_id` int NOT NULL,
  `machine_id` int DEFAULT NULL,
  `operator_id` int DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `qty_in` decimal(15,3) NOT NULL DEFAULT '0.000',
  `good_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `rework_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `partially_done_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `reject_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `waste_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `scrap_item_id` int DEFAULT NULL,
  `scrap_qty` decimal(15,3) NOT NULL DEFAULT '0.000',
  `input_cost` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'actual historic cost of consumed input',
  `outsource_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `labour_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|approved|reversed',
  `adjustment_id` int DEFAULT NULL,
  `reversal_adjustment_id` int DEFAULT NULL,
  `submitted_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `reversed_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL,
  `reversal_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `machine_confirmed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = operator explicitly confirmed the actual machine for this run',
  `machine_id_planned` int DEFAULT NULL COMMENT 'the proposed machine at the time of the run',
  `machine_override_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'why an unavailable machine was used; requires authorization',
  `machine_override_authorized_by` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` datetime DEFAULT NULL COMMENT 'manual run start',
  `ended_at` datetime DEFAULT NULL COMMENT 'manual run end',
  `counter_start_id` int DEFAULT NULL COMMENT 'db_print_machine_readings.id at run start',
  `counter_end_id` int DEFAULT NULL COMMENT 'db_print_machine_readings.id at run end',
  `counter_activity` decimal(20,3) DEFAULT NULL COMMENT 'machine activity for the run — NOT accepted output',
  `accepted_qty` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT 'accepted (saleable) output, distinct from counter activity',
  `qty_unit_id` int DEFAULT NULL COMMENT 'unit the run quantities are expressed in',
  `references_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'supporting references: batch / job-card / sample refs',
  `run_kind` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'machine' COMMENT 'machine|manual|outsourced — manual and outsourced runs carry NO machine',
  `reading_discrepancy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'why readings and quantities disagree; unresolved until reviewed',
  `outsource_vendor` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'vendor for an outsourced run',
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_stage` (`stage_id`),
  KEY `idx_work_date` (`work_date`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_print_stages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `seq` int NOT NULL DEFAULT '1',
  `stage_key` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stage_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|in_progress|done|skipped|outsourced',
  `input_item_id` int DEFAULT NULL,
  `output_item_id` int DEFAULT NULL,
  `planned_input_qty` decimal(15,3) DEFAULT NULL,
  `planned_output_qty` decimal(15,3) DEFAULT NULL,
  `machine_id` int DEFAULT NULL,
  `outsourced` tinyint(1) NOT NULL DEFAULT '0',
  `outsource_vendor` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_artwork` tinyint(1) NOT NULL DEFAULT '0',
  `requires_authorization` tinyint(1) NOT NULL DEFAULT '0',
  `assigned_to` int DEFAULT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_product_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `item_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `reviewer_name` varchar(120) NOT NULL,
  `reviewer_email` varchar(150) DEFAULT NULL,
  `reviewer_key` varchar(64) NOT NULL,
  `rating` tinyint NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `review_text` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) DEFAULT NULL,
  `moderated_by` int DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `moderation_note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review` (`store_id`,`item_id`,`reviewer_key`),
  KEY `idx_item_status` (`store_id`,`item_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_quotation_lifecycle_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `quotation_id` int NOT NULL,
  `job_id` int DEFAULT NULL,
  `from_status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ui|email|whatsapp|system|cron',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `response_token_fingerprint` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'sha256 of the response token that produced this entry',
  PRIMARY KEY (`id`),
  KEY `idx_quotation` (`quotation_id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_token_fp` (`response_token_fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_quotation_revisions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `quotation_id` int NOT NULL,
  `revision_no` int NOT NULL DEFAULT '0',
  `revision_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_json` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Snapshot of quotation header fields',
  `items_json` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Snapshot of quotation item rows',
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qrev_quote` (`quotation_id`),
  KEY `idx_qrev_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_rate_limits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bucket` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. intake:{store_id}:{ip}',
  `window_start` datetime NOT NULL,
  `hits` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bucket_window` (`bucket`,`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_review_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `store_id` int NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reporter_key` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_report` (`review_id`,`reporter_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_sendchamp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `api_key` text NOT NULL,
  `sender_id` varchar(50) NOT NULL DEFAULT 'MartPoint',
  `route` varchar(50) NOT NULL DEFAULT 'non_dnd_nigeria',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_job_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `item_id` int NOT NULL,
  `barcode_id` int DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT '1.00',
  `price_per_unit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjitems_job` (`job_id`),
  KEY `idx_sjitems_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_job_visits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_id` int NOT NULL,
  `visit_date` date DEFAULT NULL,
  `engineer_id` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `outcome` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. completed|follow_up_required|parts_on_order',
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjvisits_job` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_jobs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `job_code` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count_id` int DEFAULT NULL,
  `customer_id` int NOT NULL,
  `site_id` int DEFAULT NULL COMMENT 'db_shippingaddress.id',
  `equipment_id` int DEFAULT NULL COMMENT 'db_customer_equipment.id',
  `sales_id` int DEFAULT NULL COMMENT 'Originating sale (install jobs)',
  `job_type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'maintenance' COMMENT 'installation|commissioning|calibration|maintenance|repair|inspection|training',
  `priority` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'low|normal|high|urgent',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT 'open|assigned|scheduled|in_progress|on_hold|completed|cancelled',
  `assigned_user_id` int DEFAULT NULL COMMENT 'Engineer/technician (db_users.id)',
  `scheduled_date` date DEFAULT NULL,
  `scheduled_time` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `resolution_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `labour_charge` decimal(20,2) NOT NULL DEFAULT '0.00',
  `parts_total` decimal(20,2) NOT NULL DEFAULT '0.00',
  `created_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjob_code` (`store_id`,`job_code`),
  KEY `idx_sjob_customer` (`customer_id`),
  KEY `idx_sjob_equipment` (`equipment_id`),
  KEY `idx_sjob_status` (`status`),
  KEY `idx_sjob_assigned` (`assigned_user_id`),
  KEY `idx_sjob_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_stock_alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `item_id` int NOT NULL,
  `item_name` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `token` varchar(64) NOT NULL,
  `notified` tinyint(1) NOT NULL DEFAULT '0',
  `send_attempts` tinyint NOT NULL DEFAULT '0',
  `last_attempt_at` datetime DEFAULT NULL,
  `unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `notified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stock_alert` (`store_id`,`item_id`,`email`),
  KEY `idx_stock_alert_item` (`item_id`,`notified`),
  KEY `idx_stock_alert_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_store_backup_pre_modularization` (
  `id` int NOT NULL DEFAULT '0',
  `store_code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_website` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `logo` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `upi_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `country` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `postcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cid` int DEFAULT NULL,
  `category_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `item_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `supplier_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_return_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `customer_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sales_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sales_return_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expense_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `accounts_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `journal_init` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cust_advance_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `invoice_view` int DEFAULT NULL COMMENT '1=Standard,2=Indian GST',
  `sms_status` int DEFAULT NULL COMMENT '1=Enable 0=Disable',
  `status` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `currency_id` int DEFAULT NULL,
  `currency_placement` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_format` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_format` int DEFAULT NULL,
  `sales_discount` double(20,4) DEFAULT NULL,
  `currencysymbol_id` int DEFAULT NULL,
  `regno_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fav_icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `change_return` int DEFAULT NULL,
  `sales_invoice_format_id` int DEFAULT NULL,
  `pos_invoice_format_id` int DEFAULT NULL,
  `sales_invoice_footer_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `round_off` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `decimals` int DEFAULT '2',
  `money_transfer_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sales_payment_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sales_return_payment_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_payment_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_return_payment_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expense_payment_init` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `current_subscriptionlist_id` int DEFAULT '0',
  `smtp_host` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_user` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_pass` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_status` int DEFAULT '0',
  `sms_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_id` int NOT NULL,
  `mrp_column` int DEFAULT '0',
  `invoice_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `previous_balance_bit` int DEFAULT '1' COMMENT '1=Show, 0=Hide - Shows on sales invoice',
  `nin_api_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `nin_api_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_api_key` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_api_provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ninbvnportal',
  `nin_api_cost` decimal(10,2) NOT NULL DEFAULT '50.00',
  `qty_decimals` int DEFAULT '2',
  `industry_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_flags_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `label_overrides_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `business_model` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `workflow_template_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dashboard_template_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `storefront_theme_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `industry_settings_json` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_cart_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `cart_id` int NOT NULL,
  `channel` varchar(20) NOT NULL,
  `mode` varchar(10) NOT NULL DEFAULT 'manual',
  `recipient` varchar(191) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `actor` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sf_remind_cart` (`cart_id`),
  KEY `idx_sf_remind_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `cart_token` varchar(64) NOT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `customer_name` varchar(191) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(191) DEFAULT NULL,
  `items_json` mediumtext,
  `subtotal` decimal(15,4) DEFAULT NULL,
  `coupon_code` varchar(64) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `order_id` int DEFAULT NULL,
  `reminder_count` int NOT NULL DEFAULT '0',
  `reminder_sent_at` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `opt_out` tinyint(1) NOT NULL DEFAULT '0',
  `send_attempts` int NOT NULL DEFAULT '0',
  `last_send_attempt_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sf_cart_token` (`store_id`,`cart_token`),
  KEY `idx_sf_carts_status` (`status`),
  KEY `idx_sf_carts_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_customer_otp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `purpose` varchar(32) NOT NULL DEFAULT 'storefront',
  `verified` tinyint(1) DEFAULT '0',
  `attempts` int DEFAULT '0',
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_otp_lookup` (`store_id`,`purpose`,`phone`,`email`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_customer_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `session_token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `event_type` varchar(40) NOT NULL,
  `item_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `value` decimal(15,4) DEFAULT NULL,
  `meta` text,
  `consented` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `event_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sf_events_store` (`store_id`),
  KEY `idx_sf_events_type` (`event_type`),
  KEY `idx_sf_events_created` (`created_at`),
  KEY `idx_sf_events_eid` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_plan_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `plan_version` int NOT NULL DEFAULT '1' COMMENT 'Version of the plan this line belongs to',
  `item_id` int NOT NULL COMMENT 'db_items row (service_bit=1 service)',
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot â€” services can be renamed later',
  `qty` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Price snapshot at plan write time',
  `sessions_per_unit` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT 'Session units each purchased unit yields',
  `funding` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bill' COMMENT 'bill|wallet â€” how the line is intended to be covered',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plan` (`plan_id`,`plan_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_plan_versions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `version` int NOT NULL,
  `snapshot_json` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'Full plan + items state at this version',
  `change_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_version` (`plan_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `plan_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `episode_id` int DEFAULT NULL,
  `assessment_id` int DEFAULT NULL COMMENT 'db_assessments the plan is based on',
  `clinician_id` int NOT NULL COMMENT 'Owning physiotherapist (user id)',
  `branch_id` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft|active|completed|cancelled',
  `care_setting` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outpatient' COMMENT 'outpatient|inpatient',
  `title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `goals` text COLLATE utf8mb4_unicode_ci,
  `review_points` text COLLATE utf8mb4_unicode_ci COMMENT 'Structured review checkpoints (JSON array of {at, note})',
  `version` int NOT NULL DEFAULT '1',
  `billing_fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'sha of billable content â€” approval validity token',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `cancelled_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_plan_code` (`store_id`,`plan_code`),
  KEY `idx_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_episode` (`episode_id`),
  KEY `idx_clinician` (`clinician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_treatment_sessions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `plan_id` int DEFAULT NULL,
  `entitlement_id` int DEFAULT NULL COMMENT 'db_plan_entitlements funding the visit',
  `patient_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `encounter_id` int DEFAULT NULL COMMENT 'Care-queue visit, if run through queue',
  `branch_id` int DEFAULT NULL,
  `clinician_id` int DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `session_no` int DEFAULT NULL COMMENT 'Nth session consumed from its entitlement',
  `units_total` decimal(10,2) DEFAULT NULL COMMENT 'Denominator snapshot for the ticket',
  `fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Fee snapshot applied once on completion',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled' COMMENT 'scheduled|checked_in|in_progress|completed|cancelled|no_show|interrupted',
  `checkin_key` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Idempotent check-in key',
  `checked_in_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `fee_posted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Entitlement consumed + fee applied',
  `consume_txn_id` int DEFAULT NULL COMMENT 'db_patient_wallet_txns consume row (reservation-funded)',
  `cancel_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_by` int DEFAULT NULL,
  `reversal_of` int DEFAULT NULL COMMENT 'set on the correcting session state after reversal',
  `reversed` tinyint(1) NOT NULL DEFAULT '0',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `reversed_by` int DEFAULT NULL,
  `reversed_at` datetime DEFAULT NULL,
  `reversal_approval_id` int DEFAULT NULL COMMENT 'db_approval_logs id authorising the financial reversal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_checkin` (`store_id`,`checkin_key`),
  KEY `idx_plan` (`plan_id`,`status`),
  KEY `idx_patient` (`store_id`,`patient_id`,`status`),
  KEY `idx_entitlement` (`entitlement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_wards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ward` (`store_id`,`name`),
  KEY `ix_ward_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `migrations` (
  `version` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------------------
-- 2. Missing columns (141)
-- ----------------------------------------------------------------------------

SET @tbl := 'db_approval_logs'; SET @col := 'applied_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL COMMENT ''When the approved change actually took effect'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'delegation_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'expires_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'flagged_for_audit';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0 COMMENT ''MD self-override and other auditor-review flags'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'target_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'target_module';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''Whitelisted module key, never a raw table name'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'target_version';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_logs'; SET @col := 'withdrawn_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'clinical_discharge_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'clinical_discharge_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'credit_override_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'credit_override_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'invoice_cancellation_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'invoice_cancellation_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'leave_transfer_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'leave_transfer_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'md_discount_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'md_discount_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'opening_position_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'opening_position_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'plan_material_change_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'plan_material_change_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'refund_wallet_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'refund_wallet_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'wallet_adjustment_approval_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_approval_settings'; SET @col := 'wallet_adjustment_approval_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'artwork_required';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'design_ref';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'dispatched_qty';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(15,3) NOT NULL DEFAULT 0.000 COMMENT ''Delivered quantity (order selling unit) for partial dispatches'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'order_qty';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(15,3) NULL DEFAULT NULL COMMENT ''Quantity in the agreed selling unit'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'order_unit_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL COMMENT ''db_units id the customer ordered in (kg/roll/piece/bundle/carton)'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_custom_orders'; SET @col := 'repeat_of_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL COMMENT ''custom order this repeat run was cloned from'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_expiry_settings'; SET @col := 'created_date';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` date NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_expiry_settings'; SET @col := 'created_time';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` time NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_item_barcodes'; SET @col := 'purchase_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_item_barcodes'; SET @col := 'purchaseitems_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'bundle_pricing';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''fixed'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'duration_min';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'is_bundle';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'max_order_qty';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(12,2) NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'min_order_qty';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(12,2) NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_items'; SET @col := 'qty_step';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(12,2) NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'attribution_json';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''page_url, utm_source/medium/campaign — secrets stripped'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'duplicate_of_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'enquiry';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'next_followup_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'patient_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'preferred_branch_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'preferred_contact';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'preferred_date';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` date NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_leads'; SET @col := 'submission_ref';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_order_items'; SET @col := 'download_count';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NOT NULL DEFAULT 0 COMMENT ''Number of times the customer has downloaded'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_order_items'; SET @col := 'download_expires_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL COMMENT ''When the download link expires'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_order_items'; SET @col := 'download_token';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''Secure signed token for file access'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_order_items'; SET @col := 'parent_line_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'channel_user_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'confirmation_token';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'coupon_code';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'coupon_discount';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(15,4) NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'customer_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'delivery_quote_pending';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'fulfilled_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'promotion_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_amount';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` decimal(12,2) NULL DEFAULT NULL COMMENT ''Amount refunded at the provider'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_confirmed_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_confirmed_by';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_disposition';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''returned|not_shipped|kept'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_reference';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''External provider refund reference/id'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'refund_restocked';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0 COMMENT ''1 when goods/fulfilment justified a restock'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'shipping_method';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'source_channel';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT ''web'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'stock_adjusted';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'stock_reserved_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'stock_state';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` enum(''none'',''reserved'',''committed'',''released'') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''none'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_online_orders'; SET @col := 'token_expires_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_promotion_usage'; SET @col := 'order_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_purchase'; SET @col := 'quotation_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_purchase'; SET @col := 'sales_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'converted_sales_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL COMMENT ''db_sales.id this quotation was converted into (one-to-one guard)'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'expired_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'lifecycle_status';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''issued'' COMMENT ''issued|accepted|declined|cancelled|expired'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'reminder_1d_sent_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL COMMENT ''1-day expiry reminder, sent once'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'reminder_3d_sent_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL COMMENT ''3-day expiry reminder, sent once'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'responded_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL COMMENT ''when accepted/declined/cancelled'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'response_reason';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''why it was declined/cancelled'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'response_token_expires_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'response_token_hash';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'revision_no';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'revision_note';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_quotation'; SET @col := 'shippingaddress_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sales'; SET @col := 'admission_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sales'; SET @col := 'plan_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sales'; SET @col := 'shippingaddress_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_services'; SET @col := 'industry_fields_json';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` json NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_shippingaddress'; SET @col := 'is_primary';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_shippingaddress'; SET @col := 'site_name';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'audit_trail_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'auto_update_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'base_domain';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'central_slim_menu';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'cpanel_host';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'cpanel_token';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'cpanel_user';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'deploy_key';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'fleet_key';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'fleet_url';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'github_branch';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'github_repo';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'github_token';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_active';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_message';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '''''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_severity';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''investigating'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_source';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''local'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_started_at';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'incident_url';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''https://www.martpoint.com.ng/status'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'install_key';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_sitesettings'; SET @col := 'status_feed_url';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''https://www.martpoint.com.ng/status/feed'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_store'; SET @col := 'booking_hours_json';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_store'; SET @col := 'cid';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_store'; SET @col := 'intake_key';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT ''Shared secret for server-to-server lead intake'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_store'; SET @col := 'public_booking';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_store'; SET @col := 'signature';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'abandoned_after_hours';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NOT NULL DEFAULT 24'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'allow_products_online';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0 COMMENT ''For service stores: allow selling products online (enables cart)'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'auto_recovery_channel';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''email'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'auto_recovery_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'auto_recovery_test';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'background_color';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '''''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'cart_recovery_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'catalogue_mode';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''products'' COMMENT ''services|products|both'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'city_shipping_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'city_shipping_json';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'min_order_qty';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` int NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'require_tracking_consent';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'reviews_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'reviews_require_approval';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'sendchamp_json';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'show_prices';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 0'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'testimonial_source';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT ''custom'''),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'tiktok_pixel_id';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @tbl := 'db_storefront_settings'; SET @col := 'upsells_enabled';
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl) = 1
  AND (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) = 0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` tinyint(1) NOT NULL DEFAULT 1'),
  'DO 0');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
