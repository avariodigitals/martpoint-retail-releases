-- Plant quotations can contain quoted parts and services that are not stocked items.
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotationitems' AND COLUMN_NAME='manual_item_name');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_quotationitems ADD COLUMN manual_item_name VARCHAR(255) NULL AFTER item_id','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotationitems' AND COLUMN_NAME='part_code');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_quotationitems ADD COLUMN part_code VARCHAR(100) NULL AFTER manual_item_name','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotationitems' AND COLUMN_NAME='unit_label');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_quotationitems ADD COLUMN unit_label VARCHAR(40) NULL AFTER part_code','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
