-- Store a buyer-facing section heading on each quotation line so plant spare
-- parts can be grouped by machine / pressing stage without changing totals.
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotationitems' AND COLUMN_NAME='section_name');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_quotationitems ADD COLUMN section_name VARCHAR(160) NULL AFTER description','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
