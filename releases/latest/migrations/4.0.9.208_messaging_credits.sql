-- Additive messaging ledger. Central is the sole authority for balances.
-- Fleet databases keep these tables empty; no existing sales/credits are changed.
CREATE TABLE IF NOT EXISTS db_messaging_settings (
 id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
 provider_store_id INT NOT NULL DEFAULT 1,
 email_enabled TINYINT NOT NULL DEFAULT 0,
 sms_enabled TINYINT NOT NULL DEFAULT 0,
 tax_bps INT UNSIGNED NOT NULL DEFAULT 0,
 updated_by VARCHAR(100) DEFAULT NULL,
 updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_messaging_settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS db_messaging_accounts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 fleet_id INT UNSIGNED NOT NULL,
 store_id INT UNSIGNED NOT NULL,
 store_name VARCHAR(190) NOT NULL,
 reply_to VARCHAR(254) DEFAULT NULL,
 balance_kobo BIGINT NOT NULL DEFAULT 0,
 low_balance_kobo BIGINT UNSIGNED NOT NULL DEFAULT 100000,
 daily_limit_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 paused TINYINT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL,
 UNIQUE KEY messaging_tenant (fleet_id,store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_monthly (
 account_id BIGINT UNSIGNED NOT NULL,
 month_key CHAR(7) NOT NULL,
 category VARCHAR(20) NOT NULL,
 recipient_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY (account_id,month_key,category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_messages (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 request_key VARCHAR(100) NOT NULL,
 payload_hash CHAR(64) NOT NULL,
 channel VARCHAR(10) NOT NULL,
 category VARCHAR(20) NOT NULL,
 recipient VARCHAR(254) NOT NULL,
 units INT UNSIGNED NOT NULL,
 net_kobo BIGINT UNSIGNED NOT NULL,
 tax_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 total_kobo BIGINT UNSIGNED NOT NULL,
 month_key CHAR(7) NOT NULL,
 campaign_id VARCHAR(80) DEFAULT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'reserved',
 delivery_status VARCHAR(24) NOT NULL DEFAULT 'not_reported',
 provider VARCHAR(30) DEFAULT NULL,
 provider_store_id INT NOT NULL DEFAULT 1,
 provider_id VARCHAR(190) DEFAULT NULL,
 error_message VARCHAR(500) DEFAULT NULL,
 created_at DATETIME NOT NULL,
 settled_at DATETIME DEFAULT NULL,
 UNIQUE KEY messaging_request (account_id,request_key),
 KEY messaging_usage (account_id,created_at,category,status),
 KEY messaging_provider (provider,provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_ledger (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 event_key VARCHAR(150) NOT NULL,
 kind VARCHAR(30) NOT NULL,
 amount_kobo BIGINT NOT NULL,
 balance_after_kobo BIGINT NOT NULL,
 message_id BIGINT UNSIGNED DEFAULT NULL,
 reference VARCHAR(100) DEFAULT NULL,
 note VARCHAR(500) DEFAULT NULL,
 actor VARCHAR(100) DEFAULT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY messaging_ledger_event (account_id,event_key),
 KEY messaging_ledger_history (account_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_purchases (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 request_key VARCHAR(100) NOT NULL,
 reference VARCHAR(100) NOT NULL,
 provider VARCHAR(20) NOT NULL,
 provider_store_id INT NOT NULL,
 credit_kobo BIGINT UNSIGNED NOT NULL,
 tax_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 amount_kobo BIGINT UNSIGNED NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'NGN',
 buyer_email VARCHAR(254) NOT NULL,
 checkout_url TEXT DEFAULT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'pending',
 verify_after DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL,
 paid_at DATETIME DEFAULT NULL,
 UNIQUE KEY messaging_purchase_reference (reference),
 UNIQUE KEY messaging_purchase_request (account_id,request_key),
 KEY messaging_pending_purchases (status,verify_after)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_addon_requests (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 addon_key VARCHAR(60) NOT NULL,
 billing_period VARCHAR(10) NOT NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'requested',
 created_at DATETIME NOT NULL,
 UNIQUE KEY messaging_addon_request (account_id,addon_key,billing_period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_campaign_limits (
 account_id BIGINT UNSIGNED NOT NULL,
 campaign_id VARCHAR(80) NOT NULL,
 budget_kobo BIGINT UNSIGNED NOT NULL,
 approved_at DATETIME NOT NULL,
 PRIMARY KEY (account_id,campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_messaging_campaign_approvals (
 store_id INT UNSIGNED NOT NULL,
 campaign_id BIGINT UNSIGNED NOT NULL,
 approved_at DATETIME NOT NULL,
 PRIMARY KEY (store_id,campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
