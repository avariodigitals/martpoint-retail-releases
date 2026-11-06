-- Allow each standard sales invoice to reference one bank destination from
-- the store's Account List. POS receipts keep using their existing settings.
SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='ac_accounts' AND column_name='bank_name')=0,
  'ALTER TABLE ac_accounts ADD COLUMN bank_name VARCHAR(120) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='ac_accounts' AND column_name='bank_account_name')=0,
  'ALTER TABLE ac_accounts ADD COLUMN bank_account_name VARCHAR(190) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='ac_accounts' AND column_name='bank_account_number')=0,
  'ALTER TABLE ac_accounts ADD COLUMN bank_account_number VARCHAR(40) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='invoice_account_id')=0,
  'ALTER TABLE db_sales ADD COLUMN invoice_account_id INT(11) DEFAULT NULL', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;

SET @mp_sql := IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='db_sales' AND index_name='idx_db_sales_invoice_account')=0,
  'CREATE INDEX idx_db_sales_invoice_account ON db_sales (invoice_account_id)', 'SELECT 1');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
