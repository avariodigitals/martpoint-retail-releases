-- Standard invoice document state is independent from payment_status.
-- NULL preserves legacy/POS records; new standard invoices set draft/issued/sent.
-- Keep each PREPARE/EXECUTE block on the same connection (normal updater).
SET @invoice_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='invoice_status')=0,
 'ALTER TABLE db_sales ADD COLUMN invoice_status VARCHAR(16) DEFAULT NULL', 'SELECT 1');
PREPARE invoice_stmt FROM @invoice_sql;
EXECUTE invoice_stmt;
DEALLOCATE PREPARE invoice_stmt;
SET @invoice_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='invoice_sent_at')=0,
 'ALTER TABLE db_sales ADD COLUMN invoice_sent_at DATETIME DEFAULT NULL', 'SELECT 1');
PREPARE invoice_stmt FROM @invoice_sql;
EXECUTE invoice_stmt;
DEALLOCATE PREPARE invoice_stmt;
