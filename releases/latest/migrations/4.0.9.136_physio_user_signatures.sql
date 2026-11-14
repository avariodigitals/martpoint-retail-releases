-- Store a clinician's own signature image for controlled clinical documents.
SET @has_col := (SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'db_users' AND column_name = 'signature');
SET @sql := IF(@has_col = 0,
  'ALTER TABLE `db_users` ADD COLUMN `signature` VARCHAR(255) NULL AFTER `profile_picture`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
