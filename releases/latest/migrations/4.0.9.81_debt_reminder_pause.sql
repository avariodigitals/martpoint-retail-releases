-- ============================================================
-- 4.0.9.81 — Debt reminder pause/resume + clinic settings
-- Adds clinic-level reminder tuning and pause/resume at clinic,
-- patient and single-invoice levels, with reason/actor/resume.
-- Read-only against billing; sends route through the outbox.
-- ============================================================

-- Clinic-wide reminder settings (one row per store).
CREATE TABLE IF NOT EXISTS `db_debt_reminder_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `frequency` varchar(16) NOT NULL DEFAULT 'weekly',       -- daily|3days|weekly|biweekly|monthly
  `grace_days` int NOT NULL DEFAULT 0,                      -- overdue grace before first reminder
  `send_hour` tinyint NOT NULL DEFAULT 9,                   -- local hour to dispatch (0-23)
  `max_reminders` int NOT NULL DEFAULT 0,                   -- 0 = unlimited
  `include_opening_debt` tinyint(1) NOT NULL DEFAULT 1,     -- approved opening debt is eligible
  `template_key` varchar(60) NOT NULL DEFAULT 'debt_reminder',
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_drc_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Pause/resume records. pause_scope = clinic|patient|invoice.
-- A pause suppresses sending while active (resume_at NULL = indefinite).
CREATE TABLE IF NOT EXISTS `db_debt_reminder_pauses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `pause_scope` varchar(10) NOT NULL,                      -- clinic|patient|invoice
  `patient_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `paused_by` int DEFAULT NULL,
  `paused_at` datetime DEFAULT NULL,
  `resume_at` datetime DEFAULT NULL,                        -- NULL = until manually resumed
  `resumed_by` int DEFAULT NULL,
  `resumed_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `ix_drp_store_scope` (`store_id`,`pause_scope`,`active`),
  KEY `ix_drp_invoice` (`invoice_id`,`active`),
  KEY `ix_drp_patient` (`patient_id`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Delivery + suppression audit trail.
CREATE TABLE IF NOT EXISTS `db_debt_reminder_audit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `action` varchar(20) NOT NULL,                           -- queued|sent|retried|suppressed|paused|resumed
  `reason` varchar(255) DEFAULT NULL,
  `actor` varchar(50) DEFAULT NULL,
  `event_key` varchar(80) DEFAULT NULL,
  `amount_due` decimal(18,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_dra_store` (`store_id`,`action`),
  KEY `ix_dra_key` (`event_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
