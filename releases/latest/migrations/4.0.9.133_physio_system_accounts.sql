-- Optional bed-linked system accounts. No historical money is reposted.
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_physio_payment_accounts' AND COLUMN_NAME='system_account_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_physio_payment_accounts ADD COLUMN system_account_id INT NULL, ADD UNIQUE KEY uq_physio_system_account (store_id,system_account_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_sales' AND COLUMN_NAME='physio_system_account_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_sales ADD COLUMN physio_system_account_id INT NULL','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
