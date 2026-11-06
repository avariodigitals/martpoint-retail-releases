-- Waiter attribution, tips and an optional service charge (v4.0.9.231).
--
-- A lounge pays its floor staff out of what the guests leave, so three things
-- have to be visible on the bill itself and not in a notebook by the till:
--
--   * Which waiter owns the round. Before now the tab named a waiter and that
--     name was overwritten at handover, so yesterday's bill could not say who
--     actually served it. Each invoice carries its own waiter, and the tab's
--     waiter is copied onto the round when the round joins the bill — which is
--     how "every order is tied to a waiter" works without a second order table.
--
--   * The tip. The cashier keys it, and it is recorded FOR a named waiter.
--     It deliberately does NOT join grand_total: a tip is money the house is
--     only holding, and folding it into the bill total would inflate revenue,
--     tax and every outlet report by the amount that belongs to a person.
--
--   * A service charge the guest may or may not accept. This one IS house
--     money, so its amount also lands in other_charges_amt, which the existing
--     money formula and reports already add up. The two descriptors are kept
--     beside it so the charge can be re-set or removed later without guessing
--     how much of other_charges_amt was service charge.
--
-- Every column is nullable or zero-default and additive. Invoices written
-- before this migration read back as "not recorded", so Retail, Restaurant and
-- clinic history keep their meaning.

SET @db := DATABASE();

-- ── The waiter behind the round ─────────────────────────────────────────────
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='waiter_user_id')=0,
  'ALTER TABLE db_sales ADD COLUMN `waiter_user_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The waiter sheet asks "what did this person sell this week", so the report
-- reads behind the store and the date. Ordinary index, no foreign key: a
-- departed staff member must not orphan the sales they stood behind.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='idx_sales_waiter')=0,
  'ALTER TABLE db_sales ADD INDEX `idx_sales_waiter` (`store_id`,`sales_date`,`waiter_user_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── The tip, kept out of house revenue ──────────────────────────────────────
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='tip_amount')=0,
  'ALTER TABLE db_sales ADD COLUMN `tip_amount` DOUBLE(20,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tip_for_user_id is the waiter the money belongs to. It is stored apart from
-- waiter_user_id because a guest sometimes tips the person who brought the
-- round in, not the one who has the tab now.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='tip_for_user_id')=0,
  'ALTER TABLE db_sales ADD COLUMN `tip_for_user_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='tip_by')=0,
  'ALTER TABLE db_sales ADD COLUMN `tip_by` VARCHAR(50) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='tip_at')=0,
  'ALTER TABLE db_sales ADD COLUMN `tip_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Optional per-bill service charge ────────────────────────────────────────
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='service_charge_pct')=0,
  'ALTER TABLE db_sales ADD COLUMN `service_charge_pct` DECIMAL(6,2) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='service_charge_amt')=0,
  'ALTER TABLE db_sales ADD COLUMN `service_charge_amt` DOUBLE(20,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
