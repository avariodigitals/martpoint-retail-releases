-- Separate credit-billed dispatches from the .171 customer-inbox message log.
-- Never ALTER, rename or delete db_messaging_messages: it belongs to the inbox.
-- Existing financial-only history is copied through the guarded Central setup
-- action, not guessed or overwritten by this additive migration.
CREATE TABLE IF NOT EXISTS db_messaging_dispatches (
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

CREATE TABLE IF NOT EXISTS db_messaging_schema_imports (
 source_table VARCHAR(64) NOT NULL PRIMARY KEY,
 source_count BIGINT UNSIGNED NOT NULL,
 source_max_id BIGINT UNSIGNED NOT NULL,
 imported_at DATETIME NOT NULL,
 reviewed_by VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
