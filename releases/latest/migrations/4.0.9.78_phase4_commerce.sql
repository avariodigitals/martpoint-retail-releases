-- Phase 4: product reviews, composite bundles, add-ons, checkout upsells,
-- quantity rules. All statements idempotent for safe re-run.

-- Quantity rules on items (per item; variant children are items so
-- variant-scope is native) + bundle markers.
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='min_order_qty');
SET @s = IF(@c=0,'ALTER TABLE db_items ADD COLUMN min_order_qty DECIMAL(12,2) NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='max_order_qty');
SET @s = IF(@c=0,'ALTER TABLE db_items ADD COLUMN max_order_qty DECIMAL(12,2) NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='qty_step');
SET @s = IF(@c=0,'ALTER TABLE db_items ADD COLUMN qty_step DECIMAL(12,2) NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='is_bundle');
SET @s = IF(@c=0,'ALTER TABLE db_items ADD COLUMN is_bundle TINYINT(1) NOT NULL DEFAULT 0','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_items' AND COLUMN_NAME='bundle_pricing');
SET @s = IF(@c=0,"ALTER TABLE db_items ADD COLUMN bundle_pricing VARCHAR(20) NOT NULL DEFAULT 'fixed'",'SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Composite bundle components
CREATE TABLE IF NOT EXISTS db_bundle_components (
  id INT NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  bundle_item_id INT NOT NULL,
  component_item_id INT NOT NULL,
  qty DECIMAL(12,2) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bundle_comp (bundle_item_id, component_item_id),
  KEY idx_store (store_id)
);

-- Optional add-ons attached to a product or an online service.
-- linked_item_id (optional) points at an inventory item whose stock is
-- consumed when the add-on is chosen.
CREATE TABLE IF NOT EXISTS db_item_addons (
  id INT NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  item_id INT NULL,
  service_id INT NULL,
  linked_item_id INT NULL,
  name VARCHAR(150) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  max_qty INT NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_item (store_id, item_id),
  KEY idx_service (store_id, service_id)
);

-- Checkout upsell suggestions. trigger_item_id NULL = cart-wide suggestion.
CREATE TABLE IF NOT EXISTS db_item_upsells (
  id INT NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  trigger_item_id INT NULL,
  upsell_item_id INT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_upsell (store_id, trigger_item_id, upsell_item_id)
);

-- Product reviews — separate from merchant-curated testimonials.
CREATE TABLE IF NOT EXISTS db_product_reviews (
  id INT NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  item_id INT NOT NULL,
  order_id INT NULL,
  customer_id INT NULL,
  reviewer_name VARCHAR(120) NOT NULL,
  reviewer_email VARCHAR(150) NULL,
  reviewer_key VARCHAR(64) NOT NULL,
  rating TINYINT NOT NULL,
  title VARCHAR(150) NULL,
  review_text TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  moderated_by INT NULL,
  moderated_at DATETIME NULL,
  moderation_note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_review (store_id, item_id, reviewer_key),
  KEY idx_item_status (store_id, item_id, status)
);

-- Abuse/spam reports on reviews.
CREATE TABLE IF NOT EXISTS db_review_reports (
  id INT NOT NULL AUTO_INCREMENT,
  review_id INT NOT NULL,
  store_id INT NOT NULL,
  reason VARCHAR(255) NULL,
  reporter_key VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_report (review_id, reporter_key)
);

-- Order-line linking for bundle components and add-ons. parent_line_id
-- references db_online_order_items.id of the selling line (bundle parent or
-- the line an add-on was chosen on) — distinct from db_items.parent_id,
-- which is the variant-parent link.
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='parent_line_id');
SET @s = IF(@c=0,'ALTER TABLE db_online_order_items ADD COLUMN parent_line_id INT NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
ALTER TABLE db_online_order_items
  MODIFY COLUMN item_type ENUM('product','service','digital','course','membership','bundle','bundle_component','addon') NOT NULL DEFAULT 'product';

-- Storefront settings: order-level minimum + feature toggles.
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='min_order_qty');
SET @s = IF(@c=0,'ALTER TABLE db_storefront_settings ADD COLUMN min_order_qty INT NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='reviews_enabled');
SET @s = IF(@c=0,'ALTER TABLE db_storefront_settings ADD COLUMN reviews_enabled TINYINT(1) NOT NULL DEFAULT 1','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='reviews_require_approval');
SET @s = IF(@c=0,'ALTER TABLE db_storefront_settings ADD COLUMN reviews_require_approval TINYINT(1) NOT NULL DEFAULT 1','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='upsells_enabled');
SET @s = IF(@c=0,'ALTER TABLE db_storefront_settings ADD COLUMN upsells_enabled TINYINT(1) NOT NULL DEFAULT 1','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
