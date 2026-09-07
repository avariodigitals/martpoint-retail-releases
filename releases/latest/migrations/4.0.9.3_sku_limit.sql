-- ========================================================================
-- 4.0.9.3 — SKU limit + Online product limit for fair-usage licensing
-- Adds sku_limit and override_sku_limit columns to db_subscription_license
-- so the total number of SKUs (including variant children) can be capped
-- independently of the product_limit (which now counts top-level products
-- only, not variant children).
-- Also adds online_product_limit to cap how many products can be published
-- to the customer-facing online store, and changes publish_online default
-- from 1 to 0 so new products stay offline until explicitly published.
-- ========================================================================

SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_license'
    AND column_name = 'sku_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_license` ADD COLUMN `sku_limit` int(11) DEFAULT 10000 AFTER `product_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_license'
    AND column_name = 'override_sku_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_license` ADD COLUMN `override_sku_limit` int(11) DEFAULT NULL AFTER `override_product_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- online_product_limit: caps products visible on the online store
SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_license'
    AND column_name = 'online_product_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_license` ADD COLUMN `online_product_limit` int(11) DEFAULT 500 AFTER `sku_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_license'
    AND column_name = 'override_online_product_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_license` ADD COLUMN `override_online_product_limit` int(11) DEFAULT NULL AFTER `override_sku_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add sku_limit + online_product_limit to the plans table
SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_plans'
    AND column_name = 'sku_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_plans` ADD COLUMN `sku_limit` int(11) NOT NULL DEFAULT 10000 AFTER `product_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT 1 FROM information_schema.columns
  WHERE table_name = 'db_subscription_plans'
    AND column_name = 'online_product_limit'
    AND table_schema = DATABASE());
SET @sql = IF(@col_exists IS NULL,
  'ALTER TABLE `db_subscription_plans` ADD COLUMN `online_product_limit` int(11) NOT NULL DEFAULT 500 AFTER `sku_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill sku_limit and online_product_limit on existing plans
UPDATE `db_subscription_plans` SET `sku_limit` = 10000,   `online_product_limit` = 500   WHERE `product_limit` <= 500;
UPDATE `db_subscription_plans` SET `sku_limit` = 50000,   `online_product_limit` = 2000  WHERE `product_limit` > 500  AND `product_limit` <= 2000;
UPDATE `db_subscription_plans` SET `sku_limit` = 150000,  `online_product_limit` = 5000  WHERE `product_limit` > 2000 AND `product_limit` <= 5000;
UPDATE `db_subscription_plans` SET `sku_limit` = 500000,  `online_product_limit` = 20000 WHERE `product_limit` > 5000;

-- Change publish_online default from 1 to 0 so new products stay offline
-- until the merchant explicitly publishes them to the online store.
ALTER TABLE `db_items` MODIFY COLUMN `publish_online` tinyint(1) NOT NULL DEFAULT 0;
