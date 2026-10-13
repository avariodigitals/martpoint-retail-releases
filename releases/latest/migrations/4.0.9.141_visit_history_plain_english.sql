-- ============================================================
-- 4.0.9.141 — Visit history reads as plain English
--
-- The visit-history feed used to print the login username and raw
-- internal codes, so staff saw rows like
--
--   "storeadm — handover to physiotherapy  by storeadm"
--
-- The actor is now shown in its own "by ..." line, as a first name,
-- so the username duplicated inside the note is stripped here.
--
-- Old intake notes were written as "<username> — <note>" and the same
-- username was stored in created_by_name, so that column is the exact
-- prefix to cut. Matching on it (rather than joining db_users) also
-- covers rows whose user has since been deleted.
--
-- Idempotent: once the prefix is gone the LIKE no longer matches.
-- ============================================================

-- 1. Strip the "<username> — " prefix. CHAR_LENGTH() counts characters,
--    not bytes, so the three characters of the separator are removed
--    correctly from multibyte text.
UPDATE `db_encounter_events`
SET `note` = NULLIF(TRIM(SUBSTRING(`note`, CHAR_LENGTH(`created_by_name`) + 4)), '')
WHERE `event` = 'intake_completed'
  AND `created_by_name` IS NOT NULL
  AND `created_by_name` <> ''
  AND `note` LIKE CONCAT(`created_by_name`, ' — %');

-- 2. Normalise the boilerplate handover note to sentence case.
UPDATE `db_encounter_events`
SET `note` = 'Handed over to the physiotherapist'
WHERE `event` = 'intake_completed'
  AND LOWER(TRIM(`note`)) = 'handover to physiotherapy';

-- 3. Vitals notes carried our raw row id, e.g. "Vitals finalized (set #38)".
--    Staff have no use for a database row number. LOCATE() is used instead of
--    REGEXP_REPLACE() so this runs on MySQL 5.7 as well as 8 / MariaDB.
UPDATE `db_encounter_events`
SET `note` = TRIM(SUBSTRING(`note`, 1, LOCATE(' (set #', `note`) - 1))
WHERE `event` = 'vitals'
  AND LOCATE(' (set #', `note`) > 0;
