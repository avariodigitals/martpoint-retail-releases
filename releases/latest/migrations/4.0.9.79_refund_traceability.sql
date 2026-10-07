-- 4.0.9.79 — Refund traceability and external-refund confirmation.
--
-- "Marked refunded in MartPoint" is a BOOKKEEPING state. It does not move
-- money. These columns record the external provider refund that must exist
-- before the state is accepted, plus the goods disposition that decides
-- whether stock is returned to the shelf.
--
-- refund_disposition:
--   returned   → goods came back, restock
--   not_shipped→ fulfilment was cancelled, nothing left the shelf, restock
--   kept       → customer keeps the goods, do NOT restock
--
-- All statements guarded and idempotent.

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_reference');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_reference VARCHAR(100) NULL COMMENT ''External provider refund reference/id''','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_amount');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_amount DECIMAL(12,2) NULL COMMENT ''Amount refunded at the provider''','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_disposition');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_disposition VARCHAR(20) NULL COMMENT ''returned|not_shipped|kept''','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_restocked');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_restocked TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 when goods/fulfilment justified a restock''','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_confirmed_by');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_confirmed_by VARCHAR(100) NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_confirmed_at');
SET @s = IF(@c=0,'ALTER TABLE db_online_orders ADD COLUMN refund_confirmed_at DATETIME NULL','SELECT 1'); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
