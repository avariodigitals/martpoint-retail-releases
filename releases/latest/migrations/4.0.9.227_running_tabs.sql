-- Running tabs (v4.0.9.227) — Bar/Lounge and Restaurant floor service.
--
-- A tab is a grouping record only. Every round is still an ordinary db_sales
-- invoice and every payment still lives in db_salespayments, so the financial
-- ledger is unchanged: enabling this migration cannot move, duplicate or
-- re-price a single historical row. db_sales.tab_id is the link, and the tab's
-- running total is derived from its rounds at read time so it can never drift.
--
-- Additive and repeatable, in the house style (information_schema guards,
-- PREPARE/EXECUTE) because MySQL 5.7 / MariaDB have no ADD COLUMN IF NOT
-- EXISTS. Fresh installs get the same shape from
-- setup/install/includes/db_install_extensions.sql.

SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `db_tabs` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `store_id` INT(10) NOT NULL,
  `warehouse_id` INT(10) NOT NULL DEFAULT 0,
  `tab_code` VARCHAR(30) NOT NULL,
  `tab_type` VARCHAR(20) NOT NULL DEFAULT 'table' COMMENT 'table | bar | guest',
  `table_id` INT(10) NOT NULL DEFAULT 0,
  `customer_id` INT(10) NULL DEFAULT NULL,
  `guest_name` VARCHAR(120) NULL DEFAULT NULL,
  `waiter_user_id` INT(10) NULL DEFAULT NULL,
  `covers` INT(5) NOT NULL DEFAULT 0,
  `note` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open' COMMENT 'open | settled | cancelled',
  `idempotency_key` VARCHAR(96) NULL DEFAULT NULL COMMENT 'client retry key',
  `opened_by` VARCHAR(50) NULL DEFAULT NULL,
  `opened_at` DATETIME NULL DEFAULT NULL,
  `settled_at` DATETIME NULL DEFAULT NULL,
  `cancelled_at` DATETIME NULL DEFAULT NULL,
  `cancel_reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_tab_idempotency` (`store_id`,`idempotency_key`),
  KEY `idx_tabs_store_status` (`store_id`,`status`),
  KEY `idx_tabs_store_table` (`store_id`,`table_id`),
  KEY `idx_tabs_store_outlet` (`store_id`,`warehouse_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='tab_id')=0,
  'ALTER TABLE db_sales ADD COLUMN `tab_id` INT(11) NULL DEFAULT NULL AFTER `table_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ordinary lookup index, not a foreign key: db_sales already rejects rows whose
-- warehouse_id matches no outlet (db_sales_ibfk_3), and a tab FK would do the
-- same damage to counter sales when the tab row is cleaned up later.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='idx_sales_tab')=0,
  'ALTER TABLE db_sales ADD INDEX `idx_sales_tab` (`store_id`,`tab_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
