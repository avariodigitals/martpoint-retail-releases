-- Snapshot the earning bed on its charge, so transfer/reuse cannot move history.
CREATE TABLE IF NOT EXISTS db_physio_payment_accounts (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 store_id INT NOT NULL,
 account_code VARCHAR(40) NOT NULL,
 bed_id INT UNSIGNED NULL,
 PRIMARY KEY(id), UNIQUE KEY uq_physio_account(store_id,account_code),
 UNIQUE KEY uq_physio_bed(store_id,bed_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT IGNORE INTO db_physio_payment_accounts (store_id,account_code,bed_id)
 SELECT store_id,CONCAT('BED-',id),id FROM db_beds;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salesitems' AND COLUMN_NAME='physio_account_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_salesitems ADD COLUMN physio_account_id INT UNSIGNED NULL, ADD KEY ix_physio_account (store_id,physio_account_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_salespayments' AND COLUMN_NAME='physio_account_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_salespayments ADD COLUMN physio_account_id INT UNSIGNED NULL, ADD KEY ix_physio_account (store_id,physio_account_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_patient_wallet_txns' AND COLUMN_NAME='physio_account_id');
SET @mp_sql=IF(@mp_col=0,'ALTER TABLE db_patient_wallet_txns ADD COLUMN physio_account_id INT UNSIGNED NULL, ADD KEY ix_physio_account (store_id,physio_account_id)','DO 0');
PREPARE mp_stmt FROM @mp_sql; EXECUTE mp_stmt; DEALLOCATE PREPARE mp_stmt;
SET @mp_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_daily_charges' AND COLUMN_NAME='bed_id');
SET @mp_sql = IF(@mp_col=0, 'ALTER TABLE db_daily_charges ADD COLUMN bed_id INT UNSIGNED NULL, ADD KEY ix_charge_bed (store_id,bed_id,charge_date)', 'DO 0');
PREPARE mp_stmt FROM @mp_sql;
EXECUTE mp_stmt;
DEALLOCATE PREPARE mp_stmt;
-- Recover historical bed charges from the final occupancy overlapping that day.
-- Unresolvable charges remain NULL for review, rather than guessing a bed.
UPDATE db_daily_charges c SET c.bed_id=(
 SELECT o.bed_id FROM db_bed_occupancy o
 WHERE o.store_id=c.store_id AND o.admission_id=c.admission_id
 AND c.posted_at IS NOT NULL AND o.from_at <= c.posted_at
 AND o.from_at < DATE_ADD(c.charge_date,INTERVAL 1 DAY)
 AND (o.to_at IS NULL OR o.to_at > c.charge_date)
 ORDER BY o.from_at DESC,o.id DESC LIMIT 1
) WHERE c.charge_code='bed' AND c.bed_id IS NULL;
UPDATE db_salesitems i JOIN db_daily_charges c ON c.sales_item_id=i.id AND c.store_id=i.store_id
 JOIN db_physio_payment_accounts a ON a.store_id=c.store_id AND a.bed_id=c.bed_id
 SET i.physio_account_id=a.id WHERE c.charge_code='bed' AND i.physio_account_id IS NULL;
-- Old payments/services without trustworthy occupancy timestamps stay unassigned.
