-- ============================================================
-- 4.0.9.72 — Physiotherapy Stage 6: patient portal, document
-- release, communications, feedback, testimonials and assessment
-- template management. All statements guarded / idempotent.
-- ============================================================

-- ---------- Portal accounts: verified invitations + separate auth ----------
-- invite_token_hash / invite_expires_at drive the verified-invitation flow;
-- auth_version lets revocation/password change invalidate live sessions.
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_patient_portal_users' AND column_name='invite_token_hash');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_patient_portal_users` ADD COLUMN `invite_token_hash` varchar(64) DEFAULT NULL, ADD COLUMN `invite_expires_at` datetime DEFAULT NULL, ADD COLUMN `invited_by` int DEFAULT NULL, ADD COLUMN `auth_version` int NOT NULL DEFAULT 1, ADD COLUMN `locked_until` datetime DEFAULT NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------- Caregiver / proxy access (scoped, revocable) ----------
CREATE TABLE IF NOT EXISTS `db_patient_portal_proxies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `relationship` varchar(80) DEFAULT NULL,
  `identity` varchar(150) NOT NULL,                  -- email or phone
  `scope_csv` varchar(255) NOT NULL DEFAULT 'appointments,progress',
  `auth_secret` varchar(255) DEFAULT NULL,
  `invite_token_hash` varchar(64) DEFAULT NULL,
  `invite_expires_at` datetime DEFAULT NULL,
  `invited_by` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `auth_version` int NOT NULL DEFAULT 1,
  `failed_attempts` int NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'invited',   -- invited|active|revoked
  `revoked_at` datetime DEFAULT NULL,
  `revoked_by` int DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_proxy_identity` (`store_id`,`patient_id`,`identity`),
  KEY `ix_proxy_patient` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------- Private feedback (never auto-publishes) ----------
CREATE TABLE IF NOT EXISTS `db_patient_feedback` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `ref_type` varchar(20) NOT NULL,                   -- encounter|admission|session|sale
  `ref_id` int NOT NULL,
  `rating` tinyint NOT NULL,                          -- 1..5
  `comment` text DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 1,
  `follow_up_status` varchar(20) NOT NULL DEFAULT 'new', -- new|acknowledged|in_progress|resolved
  `follow_up_by` int DEFAULT NULL,
  `follow_up_at` datetime DEFAULT NULL,
  `follow_up_note` varchar(500) DEFAULT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'portal',    -- portal|staff
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_feedback_ref` (`store_id`,`patient_id`,`ref_type`,`ref_id`),
  KEY `ix_feedback_store` (`store_id`,`follow_up_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------- Testimonials — separate publication consent + moderation ----------
CREATE TABLE IF NOT EXISTS `db_patient_testimonials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `feedback_id` int DEFAULT NULL,
  `body` text NOT NULL,
  `rating` tinyint DEFAULT NULL,
  `display_mode` varchar(20) NOT NULL DEFAULT 'anonymous', -- anonymous|first_name|custom
  `display_name` varchar(100) DEFAULT NULL,
  `publish_consent` tinyint(1) NOT NULL DEFAULT 0,   -- explicit, separate from feedback
  `consent_at` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',   -- pending|approved|rejected|withdrawn
  `moderated_by` int DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL,
  `moderation_note` varchar(255) DEFAULT NULL,
  `withdrawn_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_testimonial_store` (`store_id`,`status`),
  KEY `ix_testimonial_patient` (`store_id`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------- Portal / feedback policies (tenant-configurable) ----------
CREATE TABLE IF NOT EXISTS `db_portal_policies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `policy_key` varchar(60) NOT NULL,
  `policy_value` varchar(255) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_policy` (`store_id`,`policy_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO db_portal_policies (store_id, policy_key, policy_value, updated_by, updated_at)
SELECT p.store_id, k.policy_key, k.policy_value, 'system', NOW()
FROM (SELECT DISTINCT store_id FROM db_store_industry_settings WHERE industry_type='physiotherapy_rehabilitation'
      UNION SELECT DISTINCT store_id FROM db_store_business_profile WHERE industry_type='physiotherapy_rehabilitation') p
JOIN (
  SELECT 'portal_enabled' AS policy_key, '1' AS policy_value
  UNION ALL SELECT 'invite_expiry_hours','72'
  UNION ALL SELECT 'feedback_enabled','1'
  UNION ALL SELECT 'feedback_cooldown_days','7'
  UNION ALL SELECT 'reminder_hours_before','24'
  UNION ALL SELECT 'testimonials_enabled','1'
) k ON 1=1
WHERE NOT EXISTS (
  SELECT 1 FROM db_portal_policies x
  WHERE x.store_id = p.store_id AND x.policy_key = k.policy_key
);

-- ---------- Appointment reminders (queue-once marker) ----------
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_appointments' AND column_name='reminder_queued');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_appointments` ADD COLUMN `reminder_queued` tinyint(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------- Document release withdrawal (audit) ----------
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_patient_documents' AND column_name='withdrawn_by');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_patient_documents` ADD COLUMN `withdrawn_by` int DEFAULT NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_patient_documents' AND column_name='withdrawn_at');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_patient_documents` ADD COLUMN `withdrawn_at` datetime DEFAULT NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_patient_documents' AND column_name='withdraw_reason');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_patient_documents` ADD COLUMN `withdraw_reason` varchar(240) DEFAULT NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
