-- ============================================================================
-- MartPoint 4.0.9.54 — Scientific Equipment & Laboratory Supplies
-- Industry preset + commercial/after-sales schema:
--   * db_sales.shippingaddress_id        (delivery scheduling fix — code already
--                                        selects s.shippingaddress_id)
--   * db_customers.mobile widened        (lead→customer phone truncation fix)
--   * db_quotation revision history      (db_quotation_revisions snapshot table)
--   * db_purchase quotation_id/sales_id  (PO ↔ customer-order traceability)
--   * db_item_barcodes purchase link     (receiving idempotency + serial→PO trace)
--   * db_shippingaddress multi-site      (site_name + is_primary)
--   * db_customer_contacts               (multiple contacts per organisation)
--   * db_customer_equipment              (installed-equipment register)
--   * db_service_jobs / _items / _visits (install, calibrate, maintain, repair)
-- Idempotent: safe to run more than once. MySQL 5.7+/8.0 compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Helper pattern (per column): check information_schema, then ALTER.

-- ----------------------------------------------------------------------------
-- Customers: widen phone columns (lead conversion truncated to varchar(15))
-- ----------------------------------------------------------------------------
ALTER TABLE `db_customers` MODIFY COLUMN `mobile` VARCHAR(30) NULL;
ALTER TABLE `db_customers` MODIFY COLUMN `phone`  VARCHAR(30) NULL;

-- ----------------------------------------------------------------------------
-- Sales: per-sale delivery/service site
-- db_shippingaddress rows are already keyed by customer_id; this column lets a
-- sale nominate WHICH site it delivers to. Delivery_model / Mobile already
-- select s.shippingaddress_id — this column makes those queries valid.
-- ----------------------------------------------------------------------------
SET @tbl='db_sales'; SET @col='shippingaddress_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_sales` ADD COLUMN `shippingaddress_id` INT(11) NULL DEFAULT NULL AFTER `customer_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_sales' AND index_name='idx_sales_shipaddr')=0,
  'ALTER TABLE `db_sales` ADD INDEX `idx_sales_shipaddr` (`shippingaddress_id`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Backfill existing sales to their customer's primary/default site
UPDATE `db_sales` s
  JOIN `db_customers` c ON c.id = s.customer_id
  SET s.shippingaddress_id = c.shippingaddress_id
 WHERE s.shippingaddress_id IS NULL AND c.shippingaddress_id IS NOT NULL;

-- ----------------------------------------------------------------------------
-- Quotations: revision history + created_by (was never populated — $CUR_DATE bug)
-- ----------------------------------------------------------------------------
SET @tbl='db_quotation'; SET @col='revision_no';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_quotation` ADD COLUMN `revision_no` INT(11) NOT NULL DEFAULT 0 AFTER `reference_no`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='revision_note';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_quotation` ADD COLUMN `revision_note` VARCHAR(255) NULL AFTER `revision_no`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='created_by';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_quotation` ADD COLUMN `created_by` INT(11) NULL AFTER `created_time`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='shippingaddress_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_quotation` ADD COLUMN `shippingaddress_id` INT(11) NULL DEFAULT NULL', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

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

-- ----------------------------------------------------------------------------
-- Purchase orders: link PO to the customer quotation / sale it fulfils
-- ----------------------------------------------------------------------------
SET @tbl='db_purchase'; SET @col='quotation_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_purchase` ADD COLUMN `quotation_id` INT(11) NULL DEFAULT NULL AFTER `supplier_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='sales_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_purchase` ADD COLUMN `sales_id` INT(11) NULL DEFAULT NULL AFTER `quotation_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_purchase' AND index_name='idx_po_quote')=0,
  'ALTER TABLE `db_purchase` ADD INDEX `idx_po_quote` (`quotation_id`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_purchase' AND index_name='idx_po_sale')=0,
  'ALTER TABLE `db_purchase` ADD INDEX `idx_po_sale` (`sales_id`)', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ----------------------------------------------------------------------------
-- Barcode/serial/batch registry: purchase link for traceability + idempotent
-- receiving (re-saving a received PO reconciles rows instead of inflating qty)
-- ----------------------------------------------------------------------------
SET @tbl='db_item_barcodes'; SET @col='purchase_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_item_barcodes` ADD COLUMN `purchase_id` INT(11) NULL DEFAULT NULL AFTER `item_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='purchaseitems_id';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_item_barcodes` ADD COLUMN `purchaseitems_id` INT(11) NULL DEFAULT NULL AFTER `purchase_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ----------------------------------------------------------------------------
-- Shipping addresses become named sites (multiple per customer)
-- ----------------------------------------------------------------------------
SET @tbl='db_shippingaddress'; SET @col='site_name';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_shippingaddress` ADD COLUMN `site_name` VARCHAR(150) NULL DEFAULT NULL AFTER `customer_id`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col='is_primary';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name=@col)=0,
  'ALTER TABLE `db_shippingaddress` ADD COLUMN `is_primary` TINYINT(1) NOT NULL DEFAULT 0 AFTER `site_name`', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ----------------------------------------------------------------------------
-- Customer contacts: multiple contacts per organisation account
-- ----------------------------------------------------------------------------
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

-- ----------------------------------------------------------------------------
-- Customer equipment register: one row per installed/sold unit
-- Auto-populated when a serialised item is invoiced; also editable manually.
-- ----------------------------------------------------------------------------
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

-- ----------------------------------------------------------------------------
-- Service jobs: installation, commissioning, calibration, maintenance, repair
-- ----------------------------------------------------------------------------
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

-- Parts consumed on a job (decrement stock, record serial/batch where relevant)
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

-- Visit log: each engineer visit / note against a job
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

SET FOREIGN_KEY_CHECKS = 1;
