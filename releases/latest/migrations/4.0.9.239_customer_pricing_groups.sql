-- Store-scoped Wholesale-based customer pricing. Safe to re-run; no catalogue changes.
CREATE TABLE IF NOT EXISTS db_customer_pricing_groups (
 id INT NOT NULL AUTO_INCREMENT, store_id INT NOT NULL,
 name VARCHAR(100) NOT NULL, direction VARCHAR(10) NOT NULL DEFAULT 'Increase',
 percent DECIMAL(10,4) NOT NULL DEFAULT 0, active TINYINT NOT NULL DEFAULT 1,
 updated_at DATETIME NOT NULL, PRIMARY KEY(id), UNIQUE KEY uq_customer_price_group(store_id,name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_customers' AND COLUMN_NAME='pricing_group_id')=0, 'ALTER TABLE db_customers ADD COLUMN pricing_group_id INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_customers' AND COLUMN_NAME='pricing_rule_mode')=0, 'ALTER TABLE db_customers ADD COLUMN pricing_rule_mode VARCHAR(10) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='pricing_snapshot')=0, 'ALTER TABLE db_salesitems ADD COLUMN pricing_snapshot TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_quotationitems' AND COLUMN_NAME='pricing_snapshot')=0, 'ALTER TABLE db_quotationitems ADD COLUMN pricing_snapshot TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_holditems' AND COLUMN_NAME='pricing_snapshot')=0, 'ALTER TABLE db_holditems ADD COLUMN pricing_snapshot TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_holditems' AND COLUMN_NAME='unit_id')=0, 'ALTER TABLE db_holditems ADD COLUMN unit_id INT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_holditems' AND COLUMN_NAME='unit_name')=0, 'ALTER TABLE db_holditems ADD COLUMN unit_name VARCHAR(100) NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_holditems' AND COLUMN_NAME='conversion_factor')=0, 'ALTER TABLE db_holditems ADD COLUMN conversion_factor DECIMAL(20,6) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
