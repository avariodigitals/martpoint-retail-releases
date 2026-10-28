-- Inert commercial history: no Items, stock, sales or accounting foreign keys/hooks.
CREATE TABLE IF NOT EXISTS db_legacy_import_batches (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, source_key VARCHAR(100) NOT NULL,
 source_sha256 CHAR(64) NOT NULL, counts_json MEDIUMTEXT NOT NULL,
 imported_at DATETIME NOT NULL,
 KEY legacy_batch_store (store_id,source_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_legacy_customers (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, source_key VARCHAR(100) NOT NULL,
 source_id BIGINT UNSIGNED NOT NULL, customer_id INT NOT NULL,
 source_json MEDIUMTEXT NOT NULL, source_hash CHAR(64) NOT NULL,
 batch_id BIGINT UNSIGNED NOT NULL,
 UNIQUE KEY legacy_customer_source (store_id,source_key,source_id),
 KEY legacy_customer_target (store_id,customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_legacy_documents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, source_key VARCHAR(100) NOT NULL,
 document_type VARCHAR(12) NOT NULL, source_id BIGINT UNSIGNED NOT NULL,
 customer_id INT NOT NULL, source_customer_id BIGINT UNSIGNED NOT NULL,
 document_number VARCHAR(100) NOT NULL, document_date DATE DEFAULT NULL,
 raw_date VARCHAR(30) NOT NULL, raw_due_date VARCHAR(30) DEFAULT NULL,
 source_class INT NOT NULL DEFAULT 0, source_currency VARCHAR(20) DEFAULT NULL,
 source_status VARCHAR(40) NOT NULL,
 subtotal DECIMAL(20,4) NOT NULL, tax DECIMAL(20,4) NOT NULL,
 discount DECIMAL(20,4) NOT NULL, shipping DECIMAL(20,4) NOT NULL,
 total DECIMAL(20,4) NOT NULL, paid_amount DECIMAL(20,4) NOT NULL,
 review_reason VARCHAR(500) DEFAULT NULL,
 source_json MEDIUMTEXT NOT NULL, source_hash CHAR(64) NOT NULL,
 batch_id BIGINT UNSIGNED NOT NULL,
 UNIQUE KEY legacy_document_source (store_id,source_key,document_type,source_id),
 KEY legacy_document_customer (store_id,customer_id,document_date,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_legacy_lines (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, document_id BIGINT UNSIGNED NOT NULL,
 source_id BIGINT UNSIGNED NOT NULL,
 product_name TEXT NOT NULL, description MEDIUMTEXT NOT NULL,
 product_code VARCHAR(100) DEFAULT NULL, unit_label VARCHAR(100) DEFAULT NULL,
 quantity DECIMAL(20,4) NOT NULL, unit_price DECIMAL(20,4) NOT NULL,
 tax DECIMAL(20,4) NOT NULL, discount DECIMAL(20,4) NOT NULL,
 line_total DECIMAL(20,4) NOT NULL,
 UNIQUE KEY legacy_line_source (store_id,document_id,source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_legacy_transactions (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, source_key VARCHAR(100) NOT NULL,
 source_id BIGINT UNSIGNED NOT NULL,
 customer_id INT DEFAULT NULL, payer_customer_id INT DEFAULT NULL,
 document_id BIGINT UNSIGNED DEFAULT NULL,
 payment_date DATE DEFAULT NULL, raw_date VARCHAR(30) NOT NULL,
 source_relation INT NOT NULL, source_category BIGINT UNSIGNED NOT NULL,
 debit DECIMAL(20,4) NOT NULL, credit DECIMAL(20,4) NOT NULL,
 payment_method VARCHAR(100) DEFAULT NULL, note TEXT,
 review_reason VARCHAR(500) DEFAULT NULL,
 source_json MEDIUMTEXT NOT NULL, source_hash CHAR(64) NOT NULL,
 batch_id BIGINT UNSIGNED NOT NULL,
 UNIQUE KEY legacy_transaction_source (store_id,source_key,source_id),
 KEY legacy_transaction_customer (store_id,customer_id,payment_date),
 KEY legacy_transaction_payer (store_id,payer_customer_id,payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_legacy_revisions (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, batch_id BIGINT UNSIGNED NOT NULL,
 source_table VARCHAR(50) NOT NULL, target_id BIGINT UNSIGNED NOT NULL,
 previous_json MEDIUMTEXT NOT NULL, recorded_at DATETIME NOT NULL,
 KEY legacy_revision_store (store_id,batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
