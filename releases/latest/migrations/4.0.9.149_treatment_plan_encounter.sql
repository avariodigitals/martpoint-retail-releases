-- ===========================================================================
-- 4.0.9.149 — Treatment plans raised from a care-queue visit.
--
-- The physiotherapist's assessment recommendation (treatment service,
-- amount, sessions) now lands as a real treatment plan, so the Treatment
-- Plans screen shows what was prescribed instead of staying empty. The link
-- back to the visit makes the upsert idempotent: re-saving the remark amends
-- the one plan for that visit rather than stacking duplicates.
--
-- Idempotent: information_schema-guarded ALTER.
-- ===========================================================================

SET @tbl := 'db_treatment_plans';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=@tbl AND column_name='encounter_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=@tbl)>0,
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `encounter_id` INT(11) NULL DEFAULT NULL COMMENT ''Care-queue visit that raised the plan'' AFTER `assessment_id`, ADD KEY `idx_encounter` (`encounter_id`)'), 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
