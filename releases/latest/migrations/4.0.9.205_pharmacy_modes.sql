-- Existing rows receive the legacy marker only when the column is first added.
-- It preserves their navigation and their existing custom-storefront opt-in.
-- New stores default to Standard. Re-running never rewrites selected modes.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_store' AND COLUMN_NAME='pharmacy_mode')=0,
  'ALTER TABLE db_store ADD COLUMN pharmacy_mode VARCHAR(16) NOT NULL DEFAULT ''legacy''', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
ALTER TABLE db_store ALTER COLUMN pharmacy_mode SET DEFAULT 'standard';
