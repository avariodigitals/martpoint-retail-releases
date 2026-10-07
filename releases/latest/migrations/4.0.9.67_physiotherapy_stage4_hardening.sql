-- ===========================================================================
-- 4.0.9.67 — Physiotherapy Stage 4 review hardening.
--
--  * db_investigation_results — versioned result history. A reviewed result
--    is immutable: correction enters a NEW result version that supersedes the
--    old one and must pass a fresh clinician review. Originals are preserved.
--  * db_treatment_sessions — approval/actor columns for financial reversal:
--    reversing a session that consumed funds requires an approved
--    wallet_adjustment log; the approver and approval id are stamped here.
--
-- Idempotent: CREATE TABLE IF NOT EXISTS + information_schema-guarded ALTERs
-- + NOT EXISTS-guarded backfill.
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- Investigation result history — every entered result is an immutable version.
-- status 'current' marks the live result; 'superseded' rows are the preserved
-- originals of amended/replaced results.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_investigation_results` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `investigation_id` INT(11) NOT NULL,
  `version_no` INT(11) NOT NULL DEFAULT 1,
  `document_id` INT(11) DEFAULT NULL COMMENT 'db_patient_documents attachment',
  `result_summary` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'current' COMMENT 'current|superseded',
  `entered_by` INT(11) DEFAULT NULL,
  `entered_at` DATETIME DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `supersedes_id` INT(11) DEFAULT NULL COMMENT 'Result version this replaces',
  `amend_reason` VARCHAR(500) DEFAULT NULL COMMENT 'Mandatory on amendment',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inv_version` (`investigation_id`,`version_no`),
  KEY `idx_store` (`store_id`,`investigation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill one history row per investigation that already carries a result,
-- preserving who entered it and who reviewed it.
INSERT INTO `db_investigation_results`
  (`store_id`,`investigation_id`,`version_no`,`document_id`,`result_summary`,
   `status`,`entered_by`,`entered_at`,`reviewed_by`,`reviewed_at`,
   `created_date`,`created_time`,`created_by`)
SELECT i.store_id, i.id, 1, i.result_document_id, i.result_summary,
       'current', i.result_entered_by, i.result_received_at,
       i.reviewed_by, i.reviewed_at,
       CURDATE(), TIME(NOW()), 'migration-4.0.9.67'
FROM `db_investigations` i
WHERE (i.result_document_id IS NOT NULL OR i.result_summary IS NOT NULL)
  AND NOT EXISTS (
    SELECT 1 FROM `db_investigation_results` r
    WHERE r.investigation_id = i.id AND r.version_no = 1
  );

-- ---------------------------------------------------------------------------
-- Financial reversal audit on treatment sessions.
-- Reversal of a completed session that consumed funds requires an approved
-- wallet_adjustment approval log; the approver + log id are stamped here.
-- ---------------------------------------------------------------------------
SET @tbl := 'db_treatment_sessions';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='reversal_approval_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `reversed_by` INT(11) NULL DEFAULT NULL, ADD COLUMN `reversed_at` DATETIME NULL DEFAULT NULL, ADD COLUMN `reversal_approval_id` INT(11) NULL DEFAULT NULL COMMENT ''db_approval_logs id authorising the financial reversal'''), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
