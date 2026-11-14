-- Money that arrives against an online order, as its own record (v4.0.9.234).
--
-- The journey a pharmacy owner described ends with "auto-reconciled online
-- payments / verified transfers". Until now the order row could hold exactly one
-- financial fact: payment_status, an ENUM, plus one stamped paystack_reference and
-- paystack_amount. That shape cannot express the things that actually happen at a
-- Nigerian counter:
--
--   * the customer pays half today by transfer and half on collection;
--   * two transfers arrive for one order, and only a human can tell which is real;
--   * a card charge lands short (or over) the amount invoiced;
--   * money arrives for an order that has already been cancelled or refunded.
--
-- In every one of those cases today's code had nowhere to put the money. The two
-- verifiers (`Storefront::_verifyAndFulfilPaystackOrder`, `Paystack::webhook`)
-- require amount == grand_total to the cent; anything else fails the check and,
-- in the webhook's case, is dropped without even an exception row. A clerk
-- confirming a transfer by hand flips payment_status to paid for the FULL total
-- and records no amount, no bank reference and no payment line — so the ledger
-- cannot see the money it has taken. `partially_paid` exists in the ENUM and in
-- both order screens' dropdowns, but no code anywhere computes it.
--
-- This table is the missing fact: one row per sum of money presented against one
-- order, whether it came from the card gateway or from a bank transfer a person
-- intends to verify. The order's payment_status stops being the source of truth
-- and becomes a *derived* answer — see Online_payments_model::settleOrder(), which
-- adds up the verified rows and only then decides unpaid / partially_paid / paid.
--
-- Deliberate choices:
--
--   * `status` is 'pending' for anything a human has not confirmed, and only
--     'verified' rows count towards what is owed. A transfer the customer claims
--     is not money until somebody who can see the bank account says so, so a
--     typed-in reference cannot mark an order paid by itself. Verification is
--     gated on the same payments_reconcile permission as the exception queue.
--   * (store_id, dedupe_key) is UNIQUE so a replayed webhook, a browser retry or
--     the same bank slip keyed twice produces one row, not two. Nothing here is
--     ever trusted to "check then insert": the constraint is the guard, and the
--     insert is INSERT IGNORE.
--   * people are stored as db_users.id, never as usernames, and the screen prints
--     a first name. verified_by is NULL for a gateway verification because nobody
--     at the store did it.
--   * salespayment_id links a verified row to the db_salespayments line it became,
--     so posting money to the ledger is idempotent and the counter's own
--     Paid/Partial/Unpaid arithmetic
--     (Sales_model::update_sales_payment_status_by_sales_id) stays the single
--     place those totals are computed.
--   * no defaults on the enum-ish columns: the model writes them explicitly, which
--     keeps the DDL free of quoted literals inside a prepared statement.
--   * no foreign keys, matching the rest of the schema.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_payments')=0,
'CREATE TABLE `db_online_order_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `online_order_id` INT NOT NULL,
  `provider` VARCHAR(20) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` VARCHAR(8) NULL DEFAULT NULL,
  `reference` VARCHAR(120) NULL DEFAULT NULL,
  `status` VARCHAR(12) NOT NULL,
  `note` VARCHAR(255) NULL DEFAULT NULL,
  `recorded_by` INT NULL DEFAULT NULL,
  `recorded_at` DATETIME NULL DEFAULT NULL,
  `verified_by` INT NULL DEFAULT NULL,
  `verified_at` DATETIME NULL DEFAULT NULL,
  `salespayment_id` INT NULL DEFAULT NULL,
  `dedupe_key` CHAR(32) NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_online_payment_dedupe` (`store_id`,`dedupe_key`),
  KEY `idx_online_payment_order` (`store_id`,`online_order_id`,`status`)
)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
