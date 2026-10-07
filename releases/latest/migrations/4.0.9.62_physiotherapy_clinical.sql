-- ============================================================================
-- MartPoint — Physiotherapy & Rehabilitation — Stage 3: clinical encounters,
-- assessments, investigations, patient documents and paper consent.
--
-- New: structured vitals sets/entries (draft|final, per-value measured_at and
-- "not measured"), versioned assessment templates, assessments (draft|final)
-- with append-only amendments, investigations lifecycle, consents lifecycle,
-- document access log, encounter intake-completion + clinical columns.
--
-- Idempotent: CREATE TABLE IF NOT EXISTS + information_schema-guarded ALTERs.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- Vitals — a SET per intake save. Entries carry value + unit + measured_at;
-- not_measured is explicit (a blank/zero reading is never confused with
-- "not taken"). Draft sets are editable; final sets are immutable — a
-- correction after final is a NEW set (history is preserved).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_encounter_vitals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `encounter_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `status` VARCHAR(12) NOT NULL DEFAULT 'draft' COMMENT 'draft|final',
  `notes` TEXT NULL,
  `recorded_by` INT(11) DEFAULT NULL,
  `recorded_by_name` VARCHAR(100) DEFAULT NULL,
  `finalized_at` DATETIME DEFAULT NULL,
  `finalized_by` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_encounter_vital_entries` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `vitals_id` INT(11) NOT NULL,
  `vital_key` VARCHAR(40) NOT NULL COMMENT 'bp_systolic|bp_diastolic|pulse|temperature|spo2|resp_rate|weight|height|pain_score|other',
  `label` VARCHAR(80) DEFAULT NULL,
  `value_text` VARCHAR(80) DEFAULT NULL COMMENT 'raw display value; NULL when not_measured',
  `value_num` DECIMAL(10,3) DEFAULT NULL COMMENT 'numeric where parseable, for trending',
  `unit` VARCHAR(20) DEFAULT NULL COMMENT 'mmHg|bpm|°C|%|/min|kg|cm|/10',
  `not_measured` TINYINT(1) NOT NULL DEFAULT 0,
  `measured_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_set` (`vitals_id`),
  KEY `idx_store_vital` (`store_id`,`vital_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Assessment templates — versioned. The bundled template is marked
-- provisional until the agreed five-page clinical form replaces it.
-- sections_json: [{key,title,fields:[{key,label,type,required,unit,options}]}]
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_assessment_templates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL DEFAULT 0 COMMENT '0 = platform template, shared',
  `template_key` VARCHAR(60) NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `version` INT(11) NOT NULL DEFAULT 1,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active' COMMENT 'active|archived',
  `provisional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Stand-in pending the agreed clinical form',
  `sections_json` MEDIUMTEXT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_version` (`store_id`,`template_key`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assessments — draft is editable by the assessor; final is immutable.
-- Corrections to a final assessment are AMENDMENTS (append-only, attributed),
-- never an overwrite of answers_json.
CREATE TABLE IF NOT EXISTS `db_assessments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `encounter_id` INT(11) NOT NULL,
  `episode_id` INT(11) DEFAULT NULL,
  `patient_id` INT(11) NOT NULL,
  `template_id` INT(11) NOT NULL,
  `template_key` VARCHAR(60) DEFAULT NULL,
  `template_version` INT(11) DEFAULT NULL,
  `status` VARCHAR(12) NOT NULL DEFAULT 'draft' COMMENT 'draft|final',
  `answers_json` MEDIUMTEXT NULL COMMENT 'section_key.field_key => value',
  `assessor_user_id` INT(11) DEFAULT NULL,
  `assessor_name` VARCHAR(100) DEFAULT NULL,
  `finalized_at` DATETIME DEFAULT NULL,
  `finalized_by` INT(11) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_assessment_amendments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `assessment_id` INT(11) NOT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `changes_json` MEDIUMTEXT NOT NULL COMMENT '[{field,label,from,to}] — from is the ORIGINAL stored answer',
  `amended_by` INT(11) DEFAULT NULL,
  `amended_by_name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assessment` (`assessment_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Investigations — request → pending → result_received → reviewed.
-- Uploading a result never flips it to reviewed; review is an explicit,
-- separately-permissioned clinician action. External providers are recorded
-- on the request row (referral register arrives in a later stage).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_investigations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `episode_id` INT(11) DEFAULT NULL,
  `encounter_id` INT(11) DEFAULT NULL,
  `count_id` INT(11) DEFAULT NULL,
  `request_ref` VARCHAR(30) DEFAULT NULL,
  `test_name` VARCHAR(160) NOT NULL,
  `category` VARCHAR(60) DEFAULT NULL COMMENT 'imaging|lab|functional|other',
  `priority` VARCHAR(12) NOT NULL DEFAULT 'routine' COMMENT 'routine|urgent',
  `status` VARCHAR(20) NOT NULL DEFAULT 'requested' COMMENT 'requested|pending|result_received|reviewed|cancelled',
  `facility_name` VARCHAR(160) DEFAULT NULL COMMENT 'External lab/imaging centre; NULL = in-house',
  `external_ref` VARCHAR(80) DEFAULT NULL,
  `request_notes` TEXT NULL,
  `requested_by` INT(11) DEFAULT NULL,
  `requested_by_name` VARCHAR(100) DEFAULT NULL,
  `requested_at` DATETIME DEFAULT NULL,
  `result_summary` TEXT NULL,
  `result_document_id` INT(11) DEFAULT NULL COMMENT 'db_patient_documents attachment',
  `result_received_at` DATETIME DEFAULT NULL,
  `result_entered_by` INT(11) DEFAULT NULL,
  `reviewed_by` INT(11) DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `cancelled_by` INT(11) DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `cancel_reason` VARCHAR(500) DEFAULT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_req_ref` (`store_id`,`request_ref`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_encounter` (`store_id`,`encounter_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Consents (paper-first). A consent record tracks the lifecycle of a printed,
-- signed form. The signed scan lives in db_patient_documents (private store).
-- Uploading a scan makes it 'awaiting_verification' — completed only after a
-- privileged user verifies the required signatories. Re-issuing a changed
-- consent supersedes the old one (never overwritten).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_consents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `patient_id` INT(11) NOT NULL,
  `episode_id` INT(11) DEFAULT NULL,
  `encounter_id` INT(11) DEFAULT NULL,
  `consent_type` VARCHAR(60) NOT NULL DEFAULT 'general_treatment' COMMENT 'general_treatment|procedure|home_visit|data_sharing|other',
  `title` VARCHAR(160) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending' COMMENT 'pending|awaiting_verification|completed|declined|superseded',
  `document_id` INT(11) DEFAULT NULL COMMENT 'db_patient_documents signed scan',
  `signatories_json` TEXT NULL COMMENT '[{role:patient|guardian|witness|clinician, name, signed_at}]',
  `body_text` TEXT NULL COMMENT 'Rendered consent wording at generation time (snapshot)',
  `declined_reason` VARCHAR(500) DEFAULT NULL,
  `declined_by_name` VARCHAR(100) DEFAULT NULL,
  `declined_at` DATETIME DEFAULT NULL,
  `verified_by` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `superseded_by_id` INT(11) DEFAULT NULL,
  `notes` TEXT NULL,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(30) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `system_ip` VARCHAR(50) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_patient` (`store_id`,`patient_id`),
  KEY `idx_store_status` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Document access log — every view/download/upload/release of a clinical file.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_document_access_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `document_id` INT(11) NOT NULL,
  `version_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(20) NOT NULL COMMENT 'view|download|upload|release|verify|denied',
  `user_id` INT(11) DEFAULT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `ip` VARCHAR(50) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc` (`document_id`),
  KEY `idx_store` (`store_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Encounter additions — intake completion + assigned handler.
-- ---------------------------------------------------------------------------
SET @has_intake_at := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_encounters' AND COLUMN_NAME = 'intake_completed_at');
SET @sql := IF(@has_intake_at = 0,
  'ALTER TABLE `db_encounters` ADD COLUMN `intake_completed_at` DATETIME NULL AFTER `vitals_json`, ADD COLUMN `intake_by` INT(11) NULL AFTER `intake_completed_at`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Provisional assessment template — clearly labelled stand-in pending the
-- agreed five-page clinical assessment form. store_id 0 = shared platform
-- template; clinics may add store-scoped versions later.
-- ---------------------------------------------------------------------------
INSERT INTO `db_assessment_templates`
  (`store_id`,`template_key`,`name`,`version`,`status`,`provisional`,`sections_json`,`created_by`,`created_at`)
SELECT 0,'physio_initial','Physiotherapy Initial Assessment (Provisional)',1,'active',1,
  '[{"key":"history","title":"History & Presentation","fields":[
      {"key":"presenting_complaint","label":"Presenting complaint","type":"textarea","required":true},
      {"key":"history","label":"History of complaint","type":"textarea","required":true},
      {"key":"pmh","label":"Past medical history","type":"textarea","required":false},
      {"key":"medications","label":"Current medications","type":"textarea","required":false},
      {"key":"red_flags","label":"Red flags screen","type":"select","required":true,"options":["none","possible","present"]}
    ]},
    {"key":"examination","title":"Examination","fields":[
      {"key":"observation","label":"Observation / posture / gait","type":"textarea","required":false},
      {"key":"rom","label":"Range of movement findings","type":"textarea","required":true},
      {"key":"strength","label":"Strength / power","type":"textarea","required":false},
      {"key":"palpation","label":"Palpation findings","type":"textarea","required":false},
      {"key":"special_tests","label":"Special tests","type":"textarea","required":false}
    ]},
    {"key":"impression","title":"Impression & Plan","fields":[
      {"key":"clinical_impression","label":"Clinical impression","type":"textarea","required":true},
      {"key":"goals","label":"Treatment goals","type":"textarea","required":true},
      {"key":"initial_plan","label":"Initial plan","type":"textarea","required":true}
    ]}]',
  'system',NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `db_assessment_templates`
  WHERE `store_id` = 0 AND `template_key` = 'physio_initial' AND `version` = 1
);
