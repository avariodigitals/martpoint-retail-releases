-- ============================================================================
-- 4.0.9.143 — Physiotherapy referral partners
--
-- A directory of external referral partners (labs, hospitals, doctors) that a
-- therapist picks from when sending a patient out, plus the per-patient
-- referral and the dated meeting log that records what happened there.
--
-- db_patient_referrals keeps a snapshot of the partner name and type so a
-- patient's referral history still reads correctly after the partner is
-- removed from the directory; the partner_id is simply cleared.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_referral_partners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `partner_type` varchar(20) NOT NULL DEFAULT 'hospital', -- lab|hospital|doctor
  `name` varchar(160) NOT NULL,
  `specialty` varchar(160) DEFAULT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_refpart` (`store_id`,`is_active`),
  KEY `ix_refpart_type` (`store_id`,`partner_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_patient_referrals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `partner_id` int DEFAULT NULL,
  `partner_name` varchar(160) NOT NULL,
  `partner_type` varchar(20) NOT NULL DEFAULT 'hospital', -- lab|hospital|doctor
  `source` varchar(20) NOT NULL DEFAULT 'manual',          -- assessment|visit|manual
  `source_id` int DEFAULT NULL,
  `encounter_id` int DEFAULT NULL,
  `assessment_id` int DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `urgency` varchar(15) NOT NULL DEFAULT 'routine', -- routine|urgent|emergency
  `status` varchar(20) NOT NULL DEFAULT 'open',     -- open|in_progress|completed|cancelled
  `next_due_date` date DEFAULT NULL,
  `referred_by` int DEFAULT NULL,
  `referred_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_patref` (`store_id`,`patient_id`,`status`),
  KEY `ix_patref_due` (`store_id`,`status`,`next_due_date`),
  KEY `ix_patref_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_referral_meetings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `referral_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `partner_id` int DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `met_with` varchar(160) DEFAULT NULL,
  `outcome` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_refmeet` (`store_id`,`referral_id`,`meeting_date`),
  KEY `ix_refmeet_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
