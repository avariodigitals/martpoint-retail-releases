-- ===========================================================================
-- 4.0.9.152 — The treatment chart a physio signs for each session, plus the
-- two grants the Care Queue review asked for.
--
-- 1. db_session_treatment_notes
--    A physio treatment visit now records its own note, keyed to the exact
--    booked session (session_id) rather than to the visit only. This is the
--    paper TREATMENT CHARTS sheet in the clinic: SESSION DATE | TREATMENT
--    OFFERED | SIGN | REMARKS, one signed row per session. Because the row
--    carries the session number and the package size, a card can say
--    "session 3 of 5" from the note itself, and the assessment remark and the
--    treatment note stay two different records with two different jobs.
--    UNIQUE (store_id, session_id): one chart per session, amended in place.
--
-- 2. sessions_note_add
--    Recording that treatment is a clinical act. Granted to every role that
--    already completes a session; never inherited by the desk.
--
-- 3. appointments_reschedule
--    Moving a booking to another day was carried by appointments_edit, so any
--    role that could edit a booking could re-date it. Granted to every role
--    that may already cancel one (the desk), and nothing broader.
--
-- All grants are idempotent and never re-add a key an admin revoked.
-- ===========================================================================

CREATE TABLE IF NOT EXISTS `db_session_treatment_notes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `session_id` INT(11) NOT NULL COMMENT 'db_treatment_sessions row this treatment was given for',
  `encounter_id` INT(11) DEFAULT NULL COMMENT 'Care-queue visit that delivered it',
  `patient_id` INT(11) NOT NULL,
  `session_no` INT(11) DEFAULT NULL COMMENT 'Nth session of the course, snapshot for the chart',
  `units_total` INT(11) DEFAULT NULL COMMENT 'Size of the course, snapshot for the chart',
  `treatment_date` DATE NOT NULL,
  `treatment_offered` TEXT NULL COMMENT 'What was actually done, as signed on the chart',
  `remarks` TEXT NULL,
  `treated_by` INT(11) NULL DEFAULT NULL COMMENT 'Physio who gave this session',
  `treated_by_name` VARCHAR(120) NULL DEFAULT NULL,
  `signed_by` INT(11) DEFAULT NULL COMMENT 'User who first signed the chart',
  `signed_by_name` VARCHAR(120) DEFAULT NULL,
  `amended_by` INT(11) NULL DEFAULT NULL COMMENT 'Later editor of the chart',
  `amended_by_name` VARCHAR(120) NULL DEFAULT NULL,
  `amended_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_store_session` (`store_id`,`session_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The physio who completes a session may record its treatment.
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT DISTINCT p.`store_id`, p.`role_id`, 'sessions_note_add'
FROM `db_permissions` p
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id` AND rv.`permissions` = 'sessions_note_add'
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id` AND have.`permissions` = 'sessions_note_add'
WHERE p.`permissions` = 'sessions_complete'
  AND rv.`id` IS NULL AND have.`id` IS NULL;

-- Re-dating a booking is the desk's call: whoever may both cancel a booking and
-- check a patient in. Editing a booking's notes alone does not buy this.
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT DISTINCT c.`store_id`, c.`role_id`, 'appointments_reschedule'
FROM `db_permissions` c
JOIN `db_permissions` q ON q.`store_id` = c.`store_id` AND q.`role_id` = c.`role_id` AND q.`permissions` = 'care_checkin'
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = c.`store_id` AND rv.`role_id` = c.`role_id` AND rv.`permissions` = 'appointments_reschedule'
LEFT JOIN `db_permissions` have
  ON have.`store_id` = c.`store_id` AND have.`role_id` = c.`role_id` AND have.`permissions` = 'appointments_reschedule'
WHERE c.`permissions` = 'appointments_cancel'
  AND rv.`id` IS NULL AND have.`id` IS NULL;
