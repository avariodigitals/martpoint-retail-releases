-- Communications subscriptions are separate from prepaid channel credits.
-- Run on Central and fleets. All changes are additive and rerunnable.
CREATE TABLE IF NOT EXISTS db_communication_catalogue (
 module_key VARCHAR(20) NOT NULL PRIMARY KEY,
 monthly_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 annual_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 credit_price_kobo INT UNSIGNED NOT NULL DEFAULT 100,
 packages_json TEXT NOT NULL,
 enabled TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_catalogue VALUES
 ('sms',0,0,800,'[5000,10000,15000,20000]',1),
 ('email',0,0,100,'[5000,10000,15000,20000]',1),
 ('automation',600000,6000000,100,'[]',1),
 ('whatsapp',0,0,100,'[]',0),('socials',0,0,100,'[]',0);
CREATE TABLE IF NOT EXISTS db_communication_settings (
 id TINYINT NOT NULL PRIMARY KEY,
 sales_email VARCHAR(254) NOT NULL DEFAULT 'sales@martpoint.com.ng',
 technical_email VARCHAR(254) DEFAULT NULL,
 bank_instructions TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_settings (id) VALUES (1);
CREATE TABLE IF NOT EXISTS db_communication_fleet_modules (
 fleet_id INT UNSIGNED NOT NULL,
 module_key VARCHAR(20) NOT NULL,
 allowed TINYINT NOT NULL DEFAULT 1,
 updated_by VARCHAR(100) DEFAULT NULL,
 updated_at DATETIME DEFAULT NULL,
 PRIMARY KEY (fleet_id,module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_modules (
 account_id BIGINT UNSIGNED NOT NULL,
 module_key VARCHAR(20) NOT NULL,
 allowed TINYINT NOT NULL DEFAULT 1,
 merchant_enabled TINYINT NOT NULL DEFAULT 0,
 subscribed_until DATETIME DEFAULT NULL,
 billing_period VARCHAR(10) DEFAULT NULL,
 billing_source VARCHAR(30) DEFAULT NULL,
 updated_by VARCHAR(100) DEFAULT NULL,
 updated_at DATETIME DEFAULT NULL,
 PRIMARY KEY (account_id,module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_wallets (
 account_id BIGINT UNSIGNED NOT NULL,
 channel VARCHAR(10) NOT NULL,
 balance_units BIGINT NOT NULL DEFAULT 0,
 purchased_units BIGINT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY (account_id,channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_ledger (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 channel VARCHAR(10) NOT NULL,
 event_key VARCHAR(150) NOT NULL,
 units BIGINT NOT NULL,
 balance_units BIGINT NOT NULL,
 message_id BIGINT UNSIGNED DEFAULT NULL,
 note VARCHAR(500) DEFAULT NULL,
 actor VARCHAR(100) DEFAULT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY comm_event (account_id,event_key),
 KEY comm_history (account_id,channel,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_requests (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 request_key VARCHAR(100) NOT NULL,
 module_key VARCHAR(20) NOT NULL,
 purpose VARCHAR(20) NOT NULL,
 billing_period VARCHAR(10) DEFAULT NULL,
 amount_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 credit_units BIGINT UNSIGNED NOT NULL DEFAULT 0,
 status VARCHAR(25) NOT NULL DEFAULT 'requested',
 payment_reference VARCHAR(100) DEFAULT NULL,
 contact_email VARCHAR(254) NOT NULL,
 note VARCHAR(500) DEFAULT NULL,
 created_at DATETIME NOT NULL,
 resolved_at DATETIME DEFAULT NULL,
 resolved_by VARCHAR(100) DEFAULT NULL,
 UNIQUE KEY comm_request (account_id,request_key),
 UNIQUE KEY comm_payment (payment_reference),
 KEY comm_pending (status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 event_key VARCHAR(150) NOT NULL UNIQUE,
 account_id BIGINT UNSIGNED NOT NULL,
 subject VARCHAR(255) NOT NULL,
 body TEXT NOT NULL,
 delivered_at DATETIME DEFAULT NULL,
 attempts INT NOT NULL DEFAULT 0,
 retry_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_budgets (
 account_id BIGINT UNSIGNED NOT NULL,
 campaign_id VARCHAR(80) NOT NULL,
 channel VARCHAR(10) NOT NULL,
 budget_units BIGINT UNSIGNED NOT NULL,
 used_units BIGINT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY (account_id,campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_contacts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 name VARCHAR(190) NOT NULL DEFAULT '',
 email VARCHAR(190) NOT NULL DEFAULT '',
 phone VARCHAR(30) NOT NULL DEFAULT '',
 consent_at DATETIME NOT NULL,
 UNIQUE KEY comm_contact (store_id,email,phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_campaign_designs (
 campaign_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 store_id INT NOT NULL,
 template_key VARCHAR(30) NOT NULL DEFAULT 'letter',
 accent VARCHAR(7) NOT NULL DEFAULT '#6554c0',
 button_label VARCHAR(80) DEFAULT NULL,
 button_url VARCHAR(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_automations (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL,
 name VARCHAR(150) NOT NULL,
 channel VARCHAR(10) NOT NULL,
 segment_key VARCHAR(30) NOT NULL,
 subject VARCHAR(255) DEFAULT NULL,
 message TEXT NOT NULL,
 enabled TINYINT NOT NULL DEFAULT 0,
 budget_units INT UNSIGNED NOT NULL,
 interval_days INT UNSIGNED NOT NULL DEFAULT 30,
 next_run_at DATETIME NOT NULL,
 last_run_at DATETIME DEFAULT NULL,
 last_error VARCHAR(500) DEFAULT NULL,
 created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_automation_runs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 automation_id BIGINT UNSIGNED NOT NULL,
 scheduled_at DATETIME NOT NULL,
 campaign_id BIGINT UNSIGNED DEFAULT NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'pending',
 UNIQUE KEY comm_run (automation_id,scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
