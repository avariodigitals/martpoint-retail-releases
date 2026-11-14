-- Pharmacy online workflow hardening.
-- Prescription requests remain in the existing medical-notes queue. These
-- fields distinguish an online request awaiting review from a pharmacist's
-- approved/dispensed record without changing the meaning of the existing
-- status column used by the pharmacy workspace.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND COLUMN_NAME='review_status')=0,
  'ALTER TABLE db_medical_notes ADD COLUMN `review_status` VARCHAR(24) NOT NULL DEFAULT ''approved'' AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND COLUMN_NAME='review_requested_at')=0,
  'ALTER TABLE db_medical_notes ADD COLUMN `review_requested_at` DATETIME NULL DEFAULT NULL AFTER `review_status`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND COLUMN_NAME='reviewed_at')=0,
  'ALTER TABLE db_medical_notes ADD COLUMN `reviewed_at` DATETIME NULL DEFAULT NULL AFTER `review_requested_at`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND COLUMN_NAME='reviewed_by')=0,
  'ALTER TABLE db_medical_notes ADD COLUMN `reviewed_by` INT(11) NULL DEFAULT NULL AFTER `reviewed_at`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_medical_notes' AND INDEX_NAME='idx_medical_review_status')=0,
  'ALTER TABLE db_medical_notes ADD INDEX `idx_medical_review_status` (`store_id`, `review_status`, `created_date`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Existing records were already created inside the pharmacy workspace, so
-- retain their operational meaning as approved unless explicitly pending.
UPDATE db_medical_notes SET review_status = 'approved'
WHERE review_status IS NULL OR review_status = '';
