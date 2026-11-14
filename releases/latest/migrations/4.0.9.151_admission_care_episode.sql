-- ===========================================================================
-- 4.0.9.151 — Admissions belong to a care episode.
--
-- A care episode is one spell of care under one type (inpatient|outpatient),
-- and the patient profile already groups history that way. An admission was
-- the one thing that did not know which spell it was: no column, no link, so
-- the ward record and the episode record described the same stay without ever
-- meeting. This adds the link and backfills it.
--
-- Two columns land on db_admissions:
--   episode_id         the care episode this stay belongs to
--   treatment_plan_id  the plan being followed for this stay
--
-- The second replaces the old `care_plan` free-text box, which was typed at
-- admission, shown once, and read by nothing. The column stays so existing
-- notes are not lost — the screens just stop asking for it.
--
-- Backfill is honest about uncertainty: an admission joins the patient's most
-- recent episode that had already started when the patient was admitted, and
-- only if there is no such episode does it take the patient's first episode.
-- Nothing is invented — an admission with no episode at all stays unlinked.
--
-- Idempotent: information_schema-guarded ALTERs; every UPDATE runs only on
-- rows still carrying NULL.
-- ===========================================================================

-- ---- admissions -> care episode ------------------------------------------
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema=DATABASE() AND table_name='db_admissions'
                  AND column_name='episode_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables
       WHERE table_schema=DATABASE() AND table_name='db_admissions')>0,
  'ALTER TABLE `db_admissions` ADD COLUMN `episode_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT ''Care episode this stay belongs to'' AFTER `patient_id`, ADD KEY `idx_adm_episode` (`episode_id`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---- admissions -> treatment plan ----------------------------------------
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema=DATABASE() AND table_name='db_admissions'
                  AND column_name='treatment_plan_id')=0
  AND (SELECT COUNT(*) FROM information_schema.tables
       WHERE table_schema=DATABASE() AND table_name='db_admissions')>0,
  'ALTER TABLE `db_admissions` ADD COLUMN `treatment_plan_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT ''Plan followed for this stay (replaces the care_plan note)'' AFTER `care_plan`, ADD KEY `idx_adm_plan` (`treatment_plan_id`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---- backfill: episode covering the admission ----------------------------
-- Pass 1 — the episode already running when the patient was admitted.
UPDATE `db_admissions` a
JOIN `db_care_episodes` ep
  ON ep.id = (
    SELECT e.id FROM `db_care_episodes` e
    WHERE e.patient_id = a.patient_id
      AND e.store_id   = a.store_id
      AND e.started_at <= a.admitted_at
    ORDER BY e.started_at DESC, e.id DESC
    LIMIT 1
  )
SET a.episode_id = ep.id
WHERE a.episode_id IS NULL;

-- Pass 2 — admitted before any episode was opened (older records).
UPDATE `db_admissions` a
JOIN `db_care_episodes` ep
  ON ep.id = (
    SELECT e.id FROM `db_care_episodes` e
    WHERE e.patient_id = a.patient_id
      AND e.store_id   = a.store_id
    ORDER BY e.started_at ASC, e.id ASC
    LIMIT 1
  )
SET a.episode_id = ep.id
WHERE a.episode_id IS NULL;

-- ---- backfill: plan for that episode -------------------------------------
-- A plan already written against the episode wins; otherwise the patient's
-- newest plan, so the admission at least names what is being followed.
UPDATE `db_admissions` a
JOIN `db_treatment_plans` tp
  ON tp.id = (
    SELECT t.id FROM `db_treatment_plans` t
    WHERE t.store_id   = a.store_id
      AND t.patient_id = a.patient_id
    ORDER BY (t.episode_id <=> a.episode_id) DESC, t.id DESC
    LIMIT 1
  )
SET a.treatment_plan_id = tp.id
WHERE a.treatment_plan_id IS NULL;
