SET @db := DATABASE();
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sitesettings' AND COLUMN_NAME='physio_document_secret');
SET @s := IF(@c=0, 'ALTER TABLE `db_sitesettings` ADD COLUMN `physio_document_secret` VARCHAR(120) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
