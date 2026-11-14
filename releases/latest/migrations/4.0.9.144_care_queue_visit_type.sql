-- ---------------------------------------------------------------------------
-- Care Queue visit type + tasked nurse (2026-10-12)
--
-- The waiting-for-nurse desk now decides WHAT this visit is before anything
-- else happens. That single choice forks the journey:
--
--   assessment  Waiting for Nurse -> Nursing Intake -> With Therapist
--               -> Awaiting Finance -> Closed
--               The visit raises an assessment-fee bill; the physio records
--               the mandatory assessment remark and hands it to Finance.
--
--   treatment   Waiting for Nurse -> Nursing Intake -> With Therapist
--               -> Closed
--               The visit draws a session from a paid package, so it never
--               becomes a finance matter — it closes straight from the
--               therapist.
--
-- Adds:
--   db_encounters.visit_type
--     'assessment' | 'treatment'. NULL until the desk chooses, which is what
--     keeps an unregistered arrival out of both journeys.
--   db_encounters.nurse_user_id
--     The nurse tasked with handling a treatment-session patient, so the
--     board can show who owns the patient instead of leaving it ambiguous.
--
-- Idempotent: ALTER guarded by information_schema checks.
-- ---------------------------------------------------------------------------

SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_encounters' AND column_name='visit_type')=0
  AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_encounters')>0,
  CONCAT('ALTER TABLE `db_encounters` ',
    ' ADD COLUMN `visit_type` VARCHAR(20) NULL DEFAULT NULL COMMENT ''assessment|treatment — chosen at the waiting-for-nurse desk'' AFTER `status`,',
    ' ADD COLUMN `nurse_user_id` INT(11) NULL DEFAULT NULL COMMENT ''Nurse tasked with a treatment-session patient'' AFTER `visit_type`,',
    ' ADD KEY `idx_store_visit_type` (`store_id`,`visit_type`)'),
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
