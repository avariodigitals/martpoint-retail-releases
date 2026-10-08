-- ============================================================================
-- MartPoint 4.0.9.123 — orphan-table repair: tables a FRESH INSTALL never got
--
-- SYMPTOM
--
--   A brand-new install is missing 15 tables that shipped code queries. The
--   Nylon Factory fails outright ("Table db_nylon_jobs does not exist"), and
--   the equipment / service-job screens fail the same way. The Central fleet
--   registry (db_fleet_installs / db_fleet_commands) and institutional
--   customer contacts are also silently absent.
--
-- CAUSE
--
--   The installer schema (setup/install/includes/db.txt) stamps a version and
--   the migration runner resumes ABOVE it — currently 4.0.9.59. Everything at
--   or below that stamp is assumed to already live in the installer schema.
--
--   These tables are created by 4.0.9.43, 4.0.9.53 and 4.0.9.54, all BELOW
--   4.0.9.59, so a fresh install skips those files — and db.txt never carried
--   the tables either. Long-lived installs have them (they ran those migrations
--   when those were current); a fresh install does not.
--
--   This is the same class of gap migration 4.0.9.102 fixed for three tables.
--   mp_release.php check could not see it at the time: section 3 counted every
--   registered migration as a creator without asking whether that migration
--   actually RUNS on a fresh install. Section 3 now filters by the stamp, and
--   this file is what makes it pass.
--
-- WHAT IT DOES
--
--   CREATE TABLE IF NOT EXISTS for each of the 15 tables, definitions copied
--   verbatim from the migration that originally created them.
--
--   * Existing installs: every table already exists -> complete no-op.
--   * Fresh installs: the tables are created, so the affected features work.
--   * Re-running: idempotent.
--
--   Column definitions are NOT trimmed to "what is used today" — a table that
--   exists but lacks a column its code reads is the same failure wearing a
--   different hat.
--
-- WHY A MIGRATION AND NOT A db.txt EDIT
--
--   db.txt is a 209 KB vendor schema that the installer parses as one unit;
--   editing it risks the install path itself. A guarded migration above the
--   stamp is the pattern 4.0.9.102 already established and is strictly safer:
--   it runs on fresh installs and no-ops on existing ones.
--
-- Idempotent. Safe on fresh installs and safe to re-run.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE IF NOT EXISTS `db_nylon_item_specs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `item_class` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'finished_good' COMMENT 'raw_material|film_roll|finished_good|consumable',
  `material` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'LDPE|HDPE|LLDPE|PP|Recycled|Other',
  `product_form` VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bag|film_roll|sheet|tubing|liners',
  `bag_type` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'vest|flat|punch-handle|zip|garbage|bread|other',
  `width_cm` DECIMAL(10,2) DEFAULT NULL,
  `length_cm` DECIMAL(10,2) DEFAULT NULL,
  `thickness_micron` DECIMAL(10,2) DEFAULT NULL,
  `colour` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_type` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|surface|flexo|gravure|custom',
  `design_ref` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Customer-specific design / artwork reference',
  `kg_per_piece` DECIMAL(12,6) DEFAULT NULL COMMENT 'Explicit per-product conversion; NULL = unknown, never assumed',
  `kg_per_roll` DECIMAL(12,4) DEFAULT NULL COMMENT 'For film rolls: weight of one standard roll',
  `pieces_per_roll` DECIMAL(12,2) DEFAULT NULL COMMENT 'For converted rolls',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_item` (`store_id`,`item_id`),
  KEY `idx_class` (`item_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_machines` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_code` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `machine_name` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `machine_type` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'extruder|printer|cutter|sealer|puncher|packer|other',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `idx_type` (`machine_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_artworks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `custom_order_id` INT(11) NOT NULL,
  `file_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_no` INT(5) NOT NULL DEFAULT 1,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`custom_order_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL DEFAULT 0,
  `job_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_kind` VARCHAR(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order' COMMENT 'order|stock',
  `custom_order_id` INT(11) DEFAULT NULL,
  `product_item_id` INT(11) DEFAULT NULL COMMENT 'Finished item being produced (may be a film roll)',
  `planned_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `planned_unit_id` INT(11) DEFAULT NULL,
  `pipeline` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot of stage keys built at creation, comma separated',
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned' COMMENT 'planned|in_progress|on_hold|completed|cancelled',
  `priority` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `due_date` DATE DEFAULT NULL,
  `est_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `est_other_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_other_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`store_id`,`job_code`),
  KEY `idx_order` (`custom_order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_stages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `seq` INT(3) NOT NULL DEFAULT 1,
  `stage_key` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|in_progress|done|skipped',
  `input_item_id` INT(11) DEFAULT NULL,
  `output_item_id` INT(11) DEFAULT NULL,
  `planned_input_qty` DECIMAL(15,3) DEFAULT NULL,
  `planned_output_qty` DECIMAL(15,3) DEFAULT NULL,
  `machine_id` INT(11) DEFAULT NULL,
  `requires_artwork` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `stage_id` INT(11) NOT NULL,
  `shift_label` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Morning|Afternoon|Night|free text',
  `machine_id` INT(11) DEFAULT NULL,
  `operator_id` INT(11) DEFAULT NULL,
  `work_date` DATE DEFAULT NULL,
  `qty_in` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Input consumed, in the input item base unit',
  `good_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Saleable/WIP output, in the output item base unit',
  `reject_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `scrap_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Reusable scrap returned to a scrap item',
  `waste_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Unrecoverable waste — recorded, no stock credit',
  `scrap_item_id` INT(11) DEFAULT NULL COMMENT 'Item credited with reusable scrap',
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Input item cost per base unit at posting time',
  `material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|approved|reversed',
  `adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment id that moved the stock',
  `reversal_adjustment_id` INT(11) DEFAULT NULL,
  `submitted_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `reversed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reversed_at` DATETIME DEFAULT NULL,
  `reversal_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_stage` (`stage_id`),
  KEY `store_id` (`store_id`),
  KEY `idx_work_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_nylon_job_costs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `cost_type` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'labour|machine|power|packaging|overhead|other',
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated` TINYINT(1) NOT NULL DEFAULT 0,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_quotation_revisions` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`      INT(11) NOT NULL,
  `quotation_id`  INT(11) NOT NULL,
  `revision_no`   INT(11) NOT NULL DEFAULT 0,
  `revision_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_json`   MEDIUMTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot of quotation header fields',
  `items_json`    MEDIUMTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot of quotation item rows',
  `created_by`    INT(11) DEFAULT NULL,
  `created_date`  DATE DEFAULT NULL,
  `created_time`  TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qrev_quote` (`quotation_id`),
  KEY `idx_qrev_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_customer_equipment` (
  `id`                        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`                  INT(11) NOT NULL,
  `customer_id`               INT(11) NOT NULL,
  `site_id`                   INT(11) DEFAULT NULL COMMENT 'db_shippingaddress.id',
  `item_id`                   INT(11) NOT NULL,
  `barcode_id`                INT(11) DEFAULT NULL COMMENT 'db_item_barcodes.id (serialised unit)',
  `serial_number`             VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model`                     VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_id`                  INT(11) DEFAULT NULL,
  `salesitems_id`             INT(11) DEFAULT NULL,
  `sale_date`                 DATE DEFAULT NULL,
  `install_date`              DATE DEFAULT NULL,
  `commissioned_date`         DATE DEFAULT NULL,
  `warranty_months`           INT(11) NOT NULL DEFAULT 0,
  `warranty_start`            DATE DEFAULT NULL,
  `warranty_end`              DATE DEFAULT NULL,
  `coverage_type`             VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT 'standard' COMMENT 'standard|extended|contract|none',
  `equipment_status`          VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'installed' COMMENT 'delivered|installed|in_service|under_repair|decommissioned',
  `calibration_interval_months` INT(11) DEFAULT NULL,
  `last_calibration_date`     DATE DEFAULT NULL,
  `next_calibration_date`     DATE DEFAULT NULL,
  `notes`                     TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by`                INT(11) DEFAULT NULL,
  `created_date`              DATE DEFAULT NULL,
  `created_time`              TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_equip_saleline` (`salesitems_id`, `barcode_id`),
  KEY `idx_equip_customer` (`customer_id`),
  KEY `idx_equip_item` (`item_id`),
  KEY `idx_equip_nextcal` (`next_calibration_date`),
  KEY `idx_equip_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_jobs` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`         INT(11) NOT NULL,
  `job_code`         VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count_id`         INT(11) DEFAULT NULL,
  `customer_id`      INT(11) NOT NULL,
  `site_id`          INT(11) DEFAULT NULL COMMENT 'db_shippingaddress.id',
  `equipment_id`     INT(11) DEFAULT NULL COMMENT 'db_customer_equipment.id',
  `sales_id`         INT(11) DEFAULT NULL COMMENT 'Originating sale (install jobs)',
  `job_type`         VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'maintenance' COMMENT 'installation|commissioning|calibration|maintenance|repair|inspection|training',
  `priority`         VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'low|normal|high|urgent',
  `status`           VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open' COMMENT 'open|assigned|scheduled|in_progress|on_hold|completed|cancelled',
  `assigned_user_id` INT(11) DEFAULT NULL COMMENT 'Engineer/technician (db_users.id)',
  `scheduled_date`   DATE DEFAULT NULL,
  `scheduled_time`   VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_date`     DATE DEFAULT NULL,
  `completed_date`   DATE DEFAULT NULL,
  `title`            VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description`      TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolution_notes` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `labour_charge`    DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `parts_total`      DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `created_by`       INT(11) DEFAULT NULL,
  `created_date`     DATE DEFAULT NULL,
  `created_time`     TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjob_code` (`store_id`, `job_code`),
  KEY `idx_sjob_customer` (`customer_id`),
  KEY `idx_sjob_equipment` (`equipment_id`),
  KEY `idx_sjob_status` (`status`),
  KEY `idx_sjob_assigned` (`assigned_user_id`),
  KEY `idx_sjob_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_job_items` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`       INT(11) NOT NULL,
  `job_id`         INT(11) NOT NULL,
  `item_id`        INT(11) NOT NULL,
  `barcode_id`     INT(11) DEFAULT NULL,
  `qty`            DECIMAL(20,2) NOT NULL DEFAULT 1.00,
  `price_per_unit` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `total`          DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `created_by`     INT(11) DEFAULT NULL,
  `created_date`   DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjitems_job` (`job_id`),
  KEY `idx_sjitems_item` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_service_job_visits` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`    INT(11) NOT NULL,
  `job_id`      INT(11) NOT NULL,
  `visit_date`  DATE DEFAULT NULL,
  `engineer_id` INT(11) DEFAULT NULL,
  `notes`       TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outcome`     VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. completed|follow_up_required|parts_on_order',
  `created_by`  INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sjvisits_job` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------
-- Added by the exhaustive sub-stamp sweep. These three are referenced by
-- SHIPPED code but were created only by 4.0.9.43 / 4.0.9.54, both BELOW
-- the 4.0.9.59 installer stamp - so a fresh install skipped them, leaving
-- the customer-contacts feature and the whole Central fleet registry dead.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_customer_contacts` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id`     INT(11) NOT NULL,
  `customer_id`  INT(11) NOT NULL,
  `contact_name` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_title`   VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. Lab Manager, Procurement Officer',
  `email`        VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone`        VARCHAR(30)  COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary`   TINYINT(1) NOT NULL DEFAULT 0,
  `notes`        TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status`       TINYINT(1) NOT NULL DEFAULT 1,
  `created_by`   INT(11) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_customer` (`customer_id`),
  KEY `idx_contacts_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fleet_installs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `install_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `install_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_install_url` (`install_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fleet_commands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `install_id` int(11) NOT NULL,
  `command` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `result` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_install_status` (`install_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
