-- Branch contact location, shared across all industries. Safe to run again.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='address')=0, 'ALTER TABLE db_warehouse ADD COLUMN `address` VARCHAR(500) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='city')=0, 'ALTER TABLE db_warehouse ADD COLUMN `city` VARCHAR(100) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='state')=0, 'ALTER TABLE db_warehouse ADD COLUMN `state` VARCHAR(100) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='country')=0, 'ALTER TABLE db_warehouse ADD COLUMN `country` VARCHAR(100) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='postcode')=0, 'ALTER TABLE db_warehouse ADD COLUMN `postcode` VARCHAR(30) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
