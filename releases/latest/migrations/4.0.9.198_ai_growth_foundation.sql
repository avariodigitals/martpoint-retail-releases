-- 4.0.9.198 — MartPoint AI Growth foundation
--
-- Additive and dormant by default. This migration only creates the client-side
-- Growth records that belong to one merchant instance. Central/Fleet ownership,
-- payment verification and provider credentials remain outside this database.
-- No existing store is enabled and no existing campaign is changed.

CREATE TABLE IF NOT EXISTS `db_ai_growth_settings` (
  `store_id` INT NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `entitlement_status` VARCHAR(20) NOT NULL DEFAULT 'inactive',
  `plan_code` VARCHAR(60) NOT NULL DEFAULT 'AI_GROWTH',
  `subscription_currency` CHAR(3) NOT NULL DEFAULT 'NGN',
  `subscription_monthly_minor` BIGINT UNSIGNED NOT NULL DEFAULT 15000000,
  `subscription_ends_at` DATETIME NULL DEFAULT NULL,
  `wallet_operations_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `wallet_funding_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `low_balance_threshold_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `daily_send_limit` INT UNSIGNED NOT NULL DEFAULT 0,
  `monthly_send_limit` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`store_id`),
  KEY `idx_growth_settings_status` (`entitlement_status`, `enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_icps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `industries_json` TEXT NULL,
  `locations_json` TEXT NULL,
  `company_characteristics_json` TEXT NULL,
  `solutions_json` TEXT NULL,
  `roles_json` TEXT NULL,
  `exclusions_json` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `created_by` INT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_growth_icps_store_status` (`store_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `icp_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `company_name` VARCHAR(191) NOT NULL,
  `domain` VARCHAR(191) NULL DEFAULT NULL,
  `country` VARCHAR(100) NULL DEFAULT NULL,
  `state` VARCHAR(100) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `industry` VARCHAR(150) NULL DEFAULT NULL,
  `size_band` VARCHAR(40) NULL DEFAULT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'research',
  `relevance_score` DECIMAL(5,2) NULL DEFAULT NULL,
  `score_json` TEXT NULL,
  `evidence_json` TEXT NULL,
  `crm_lead_id` INT NULL DEFAULT NULL,
  `approved_by` INT NULL DEFAULT NULL,
  `approved_at` DATETIME NULL DEFAULT NULL,
  `researched_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_growth_account_domain` (`store_id`, `domain`),
  KEY `idx_growth_accounts_store_status` (`store_id`, `status`),
  KEY `idx_growth_accounts_icp` (`store_id`, `icp_id`),
  KEY `idx_growth_accounts_crm_lead` (`store_id`, `crm_lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_decision_makers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `full_name` VARCHAR(191) NOT NULL,
  `job_title` VARCHAR(191) NULL DEFAULT NULL,
  `email` VARCHAR(191) NULL DEFAULT NULL,
  `phone` VARCHAR(60) NULL DEFAULT NULL,
  `source` VARCHAR(40) NULL DEFAULT NULL,
  `verification_status` VARCHAR(24) NOT NULL DEFAULT 'unverified',
  `evidence_json` TEXT NULL,
  `suppressed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_growth_decision_maker_email` (`store_id`, `account_id`, `email`),
  KEY `idx_growth_decision_makers_account` (`store_id`, `account_id`),
  KEY `idx_growth_decision_makers_suppressed` (`store_id`, `suppressed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_signals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `signal_type` VARCHAR(50) NOT NULL,
  `summary` TEXT NOT NULL,
  `source_url` VARCHAR(500) NULL DEFAULT NULL,
  `source_published_at` DATETIME NULL DEFAULT NULL,
  `observed_at` DATETIME NOT NULL,
  `uncertainty` VARCHAR(20) NOT NULL DEFAULT 'unknown',
  `evidence_json` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_growth_signals_account` (`store_id`, `account_id`, `observed_at`),
  KEY `idx_growth_signals_type` (`store_id`, `signal_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_research_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `account_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `run_key` VARCHAR(191) NOT NULL,
  `provider` VARCHAR(40) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'queued',
  `request_json` MEDIUMTEXT NULL,
  `response_json` MEDIUMTEXT NULL,
  `provider_cost_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `martpoint_charge_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `currency` CHAR(3) NOT NULL DEFAULT 'NGN',
  `started_at` DATETIME NULL DEFAULT NULL,
  `completed_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_growth_research_run` (`store_id`, `run_key`),
  KEY `idx_growth_research_account` (`store_id`, `account_id`, `created_at`),
  KEY `idx_growth_research_status` (`store_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `objective` VARCHAR(120) NULL DEFAULT NULL,
  `audience_json` TEXT NULL,
  `sequence_json` MEDIUMTEXT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'draft',
  `approval_status` VARCHAR(24) NOT NULL DEFAULT 'not_submitted',
  `provider` VARCHAR(40) NULL DEFAULT NULL,
  `provider_campaign_id` VARCHAR(191) NULL DEFAULT NULL,
  `estimated_charge_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `wallet_reservation_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `daily_send_limit` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by` INT NULL DEFAULT NULL,
  `approved_by` INT NULL DEFAULT NULL,
  `approved_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_growth_campaigns_store_status` (`store_id`, `status`),
  KEY `idx_growth_campaigns_approval` (`store_id`, `approval_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_wallet_reservations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `reservation_key` VARCHAR(191) NOT NULL,
  `reference_type` VARCHAR(50) NOT NULL,
  `reference_id` VARCHAR(80) NULL DEFAULT NULL,
  `estimated_charge_minor` BIGINT UNSIGNED NOT NULL,
  `actual_charge_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `released_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `currency` CHAR(3) NOT NULL DEFAULT 'NGN',
  `status` VARCHAR(20) NOT NULL DEFAULT 'reserved',
  `provider_reference` VARCHAR(191) NULL DEFAULT NULL,
  `metadata_json` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_growth_reservation_key` (`store_id`, `reservation_key`),
  KEY `idx_growth_reservations_status` (`store_id`, `status`),
  KEY `idx_growth_reservations_reference` (`store_id`, `reference_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_growth_wallet_ledger` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `entry_type` VARCHAR(20) NOT NULL,
  `amount_minor` BIGINT NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'NGN',
  `idempotency_key` VARCHAR(191) NOT NULL,
  `reference_type` VARCHAR(50) NULL DEFAULT NULL,
  `reference_id` VARCHAR(80) NULL DEFAULT NULL,
  `reservation_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `provider_cost_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `martpoint_charge_minor` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `fx_rate_snapshot` DECIMAL(18,8) NULL DEFAULT NULL,
  `metadata_json` TEXT NULL,
  `created_by` INT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_growth_ledger_idempotency` (`store_id`, `idempotency_key`),
  KEY `idx_growth_ledger_store_date` (`store_id`, `created_at`),
  KEY `idx_growth_ledger_reservation` (`store_id`, `reservation_id`),
  KEY `idx_growth_ledger_type` (`store_id`, `entry_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ledger rows are append-only. Corrections must be represented by a new
-- reversal/refund/adjustment row, never by rewriting history.
DROP TRIGGER IF EXISTS `trg_ai_growth_ledger_no_update`;
CREATE TRIGGER `trg_ai_growth_ledger_no_update`
BEFORE UPDATE ON `db_ai_growth_wallet_ledger`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AI Growth wallet ledger is append-only';

DROP TRIGGER IF EXISTS `trg_ai_growth_ledger_no_delete`;
CREATE TRIGGER `trg_ai_growth_ledger_no_delete`
BEFORE DELETE ON `db_ai_growth_wallet_ledger`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'AI Growth wallet ledger is append-only';

