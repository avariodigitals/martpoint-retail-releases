CREATE TABLE IF NOT EXISTS db_central_billing_catalogue_control (
 id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
 price_owner VARCHAR(10) NOT NULL DEFAULT 'CENTRAL',
 source_version VARCHAR(120) DEFAULT NULL,
 source_hash CHAR(64) DEFAULT NULL,
 sync_status VARCHAR(20) NOT NULL DEFAULT 'NOT_SYNCED',
 synced_at DATETIME DEFAULT NULL,
 updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_central_billing_catalogue_control (id,updated_at) VALUES (1,UTC_TIMESTAMP());
