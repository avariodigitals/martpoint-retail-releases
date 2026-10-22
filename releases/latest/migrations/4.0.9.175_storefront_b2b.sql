-- 4.0.9.175 — B2B storefront capabilities
--
-- Additive only. Nothing is dropped, renamed or rewritten destructively.
-- A store that never enables a B2B theme is unaffected: every new flag is
-- read through a column-existence guard in PHP.
--
-- selling_mode separates "what a store sells" (catalogue_mode: products /
-- services / both) from "how it sells" (catalogue / ecommerce / hybrid).
-- It is set only in MartPoint admin; the public site never offers a switch.
-- Backfill happens once, when the column is first added, so an existing
-- e-commerce store keeps its current behaviour.

SET @db := DATABASE();

-- ── selling mode ──────────────────────────────────────────────────────────
SET @has_selling_mode := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_storefront_settings' AND COLUMN_NAME = 'selling_mode'
);
SET @selling_mode_sql := IF(@has_selling_mode = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `selling_mode` VARCHAR(20) NOT NULL DEFAULT ''ecommerce''',
  'SELECT 1');
PREPARE selling_mode_stmt FROM @selling_mode_sql;
EXECUTE selling_mode_stmt;
DEALLOCATE PREPARE selling_mode_stmt;

UPDATE `db_storefront_settings`
SET `selling_mode` = CASE `catalogue_mode`
    WHEN 'services' THEN 'catalogue'
    WHEN 'both' THEN 'hybrid'
    ELSE 'ecommerce'
  END
WHERE @has_selling_mode = 0
  AND (`selling_mode` IS NULL OR `selling_mode` = 'ecommerce');

-- Catalogue pricing is opt-in: a catalogue can show "Price on request"
-- without leaking a price the merchant did not intend to publish.
SET @has_show_prices := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_storefront_settings' AND COLUMN_NAME = 'show_prices'
);
SET @show_prices_sql := IF(@has_show_prices = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `show_prices` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE show_prices_stmt FROM @show_prices_sql;
EXECUTE show_prices_stmt;
DEALLOCATE PREPARE show_prices_stmt;

-- ── product specifications ────────────────────────────────────────────────
-- A JSON array of {label, value} rows. Kept on the item so it travels with
-- the product into every theme, quote and future export without duplicating
-- the catalogue.
SET @has_specs := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_items' AND COLUMN_NAME = 'specs_json'
);
SET @specs_sql := IF(@has_specs = 0,
  'ALTER TABLE `db_items` ADD COLUMN `specs_json` TEXT NULL',
  'SELECT 1');
PREPARE specs_stmt FROM @specs_sql;
EXECUTE specs_stmt;
DEALLOCATE PREPARE specs_stmt;

-- ── editorial kinds ───────────────────────────────────────────────────────
-- Insights, project cases and application pages share db_storefront_posts,
-- so one editor, one publishing workflow and one set of uploads serve all
-- three. `kind` only decides which public page lists the entry.
SET @has_kind := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_storefront_posts' AND COLUMN_NAME = 'kind'
);
SET @kind_sql := IF(@has_kind = 0,
  'ALTER TABLE `db_storefront_posts` ADD COLUMN `kind` VARCHAR(20) NOT NULL DEFAULT ''insight''',
  'SELECT 1');
PREPARE kind_stmt FROM @kind_sql;
EXECUTE kind_stmt;
DEALLOCATE PREPARE kind_stmt;

SET @has_meta := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_storefront_posts' AND COLUMN_NAME = 'meta_json'
);
SET @meta_sql := IF(@has_meta = 0,
  'ALTER TABLE `db_storefront_posts` ADD COLUMN `meta_json` TEXT NULL',
  'SELECT 1');
PREPARE meta_stmt FROM @meta_sql;
EXECUTE meta_stmt;
DEALLOCATE PREPARE meta_stmt;

SET @has_kind_index := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_storefront_posts' AND INDEX_NAME = 'public_kind'
);
SET @kind_index_sql := IF(@has_kind_index = 0,
  'ALTER TABLE `db_storefront_posts` ADD KEY `public_kind` (`store_id`,`kind`,`status`,`published_at`)',
  'SELECT 1');
PREPARE kind_index_stmt FROM @kind_index_sql;
EXECUTE kind_index_stmt;
DEALLOCATE PREPARE kind_index_stmt;

-- ── enquiry routing fields ────────────────────────────────────────────────
-- These mirror the B2B enquiry form. The original form kept everything in
-- db_leads.notes JSON; that stays for backwards compatibility, but proper
-- columns let the CRM list, filter and assign project enquiries.
SET @has_company := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_leads' AND COLUMN_NAME = 'company'
);
SET @company_sql := IF(@has_company = 0,
  'ALTER TABLE `db_leads` ADD COLUMN `company` VARCHAR(180) NULL',
  'SELECT 1');
PREPARE company_stmt FROM @company_sql;
EXECUTE company_stmt;
DEALLOCATE PREPARE company_stmt;

SET @has_enquiry_type := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_leads' AND COLUMN_NAME = 'enquiry_type'
);
SET @enquiry_type_sql := IF(@has_enquiry_type = 0,
  'ALTER TABLE `db_leads` ADD COLUMN `enquiry_type` VARCHAR(60) NULL',
  'SELECT 1');
PREPARE enquiry_type_stmt FROM @enquiry_type_sql;
EXECUTE enquiry_type_stmt;
DEALLOCATE PREPARE enquiry_type_stmt;

SET @has_project_location := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_leads' AND COLUMN_NAME = 'project_location'
);
SET @project_location_sql := IF(@has_project_location = 0,
  'ALTER TABLE `db_leads` ADD COLUMN `project_location` VARCHAR(180) NULL',
  'SELECT 1');
PREPARE project_location_stmt FROM @project_location_sql;
EXECUTE project_location_stmt;
DEALLOCATE PREPARE project_location_stmt;

SET @has_item_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_leads' AND COLUMN_NAME = 'item_id'
);
SET @item_id_sql := IF(@has_item_id = 0,
  'ALTER TABLE `db_leads` ADD COLUMN `item_id` INT NULL',
  'SELECT 1');
PREPARE item_id_stmt FROM @item_id_sql;
EXECUTE item_id_stmt;
DEALLOCATE PREPARE item_id_stmt;

-- ── product documents ─────────────────────────────────────────────────────
-- Datasheets, certificates, drawings and manuals attached to a catalogue
-- item. Public rows are downloadable from the product page; private rows are
-- available only to signed-in staff through an authenticated endpoint.
CREATE TABLE IF NOT EXISTS `db_storefront_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `doc_type` VARCHAR(30) NOT NULL DEFAULT 'datasheet',
  `file_path` VARCHAR(500) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `visibility` VARCHAR(10) NOT NULL DEFAULT 'public',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  KEY `store_item` (`store_id`,`item_id`,`visibility`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
