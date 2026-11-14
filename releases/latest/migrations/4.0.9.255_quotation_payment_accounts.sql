-- Persist the optional Account List bank destination selected for each quote.
SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_quotation' AND column_name='invoice_account_id')=0,
  'ALTER TABLE db_quotation ADD COLUMN invoice_account_id INT(11) DEFAULT NULL', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_quotation' AND index_name='idx_db_quotation_invoice_account')=0,
  'CREATE INDEX idx_db_quotation_invoice_account ON db_quotation (invoice_account_id)', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
