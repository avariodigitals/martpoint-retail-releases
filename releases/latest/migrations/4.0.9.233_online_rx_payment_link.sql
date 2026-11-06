-- Secure payment link for an approved online prescription order (v4.0.9.233).
--
-- The journey a pharmacy owner described: the shopper uploads the prescription
-- at checkout, a pharmacist approves it, and only THEN the customer is sent an
-- invoice by SMS and email with a link they can pay straight from — no sign-up,
-- no login, no "call us back". That invoice has to include the delivery charge
-- they were quoted, or the store is asking for less money than it priced.
--
-- Until now there was no way to send a customer back to an existing order.
-- Paystack is initialised client-side at checkout, and the reference
-- (db_online_orders.order_code) only ever lived in that browser session. If the
-- shopper left the page — which an unapproved prescription guarantees, because
-- they are waiting days for a pharmacist — the money path was gone and the
-- counter had to ring them.
--
-- Three additive columns, deliberately modelled on the WhatsApp confirm link
-- that already works (confirmation_token / token_expires_at, verified by
-- Omni::wa_confirm):
--
--   * pay_link_token_hash holds a SHA-256 hash of the 32-character link secret,
--     never the secret itself, following db_quotation.response_token_hash. A
--     database dump or a read-replica leak therefore cannot mint live payment
--     URLs. The visitor presents the secret in the URL; the lookup hashes it and
--     matches.
--
--   * pay_link_expires_at bounds the window. The link is addressed to a phone
--     number or mailbox the store does not control, so it must die. Order
--     approval is not a permanent payment authorisation.
--
--   * pay_link_sent_at is the audit answer for "did we actually invoice this
--     person, and when" — the screen shows it beside a resend button so a
--     pharmacist never sends the same customer two competing amounts.
--
-- No index on the expiry: the link is resolved by token hash, which is indexed,
-- and the row count is one per order.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='pay_link_token_hash')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `pay_link_token_hash` CHAR(64) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND INDEX_NAME='idx_online_pay_link_token')=0,
  'ALTER TABLE db_online_orders ADD INDEX `idx_online_pay_link_token` (`pay_link_token_hash`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='pay_link_expires_at')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `pay_link_expires_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='pay_link_sent_at')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `pay_link_sent_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
