-- ---------------------------------------------------------------------------
-- Care Queue rework (2026-10-12)
--
-- Stage flow is now: check-in -> WAITING FOR NURSE (register + bill only:
-- no vitals, no visit history) -> NURSING INTAKE (vitals + bill check +
-- history, per role) -> WAITING FOR THERAPIST (shared situational awareness;
-- nurse/reception on duty push one by one) -> WITH THERAPIST (full clinical
-- view; assessment remark with recommended amount/sessions REQUIRED before
-- hand-off) -> AWAITING FINANCE (business-only: bills, no clinical data).
--
-- Adds:
--   db_encounters.assessment_remark / recommended_amount /
--   recommended_sessions / remark_by / remark_at
--     The physiotherapist's mandatory completion remark, recorded when the
--     visit is pushed to Finance.
--   db_sales.encounter_id
--     Links a visit bill (assessment fee or any counter bill) raised at the
--     waiting-for-nurse stage back to the encounter that spawned it.
--
-- Idempotent: CREATE/ALTER guarded by information_schema checks.
-- ---------------------------------------------------------------------------

SET @tbl := 'db_encounters';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='assessment_remark')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '`',
    ' ADD COLUMN `assessment_remark` TEXT NULL COMMENT ''Required physio remark before finance hand-off'' AFTER `intake_by`,',
    ' ADD COLUMN `recommended_amount` DECIMAL(15,2) NULL DEFAULT NULL COMMENT ''How much the physio recommends'' AFTER `assessment_remark`,',
    ' ADD COLUMN `recommended_sessions` INT(11) NULL DEFAULT NULL COMMENT ''How many sessions recommended'' AFTER `recommended_amount`,',
    ' ADD COLUMN `remark_by` INT(11) NULL DEFAULT NULL AFTER `recommended_sessions`,',
    ' ADD COLUMN `remark_at` DATETIME NULL DEFAULT NULL AFTER `remark_by`'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Visit bills (assessment fee / counter bill raised at check-in) link back
-- to the encounter, exactly as plan bills link to db_sales.plan_id.
SET @tbl := 'db_sales';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='encounter_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `encounter_id` INT(11) NULL DEFAULT NULL COMMENT ''Care-queue visit bill link'' AFTER `plan_id`, ADD KEY `idx_encounter` (`encounter_id`)'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
