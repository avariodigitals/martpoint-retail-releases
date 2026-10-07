-- 4.0.9.68 — Payment reconciliation: exception queue
--
-- db_payment_exceptions holds every payment anomaly detected by webhooks or
-- the on-demand reconciliation scan: provider payments that match nothing
-- locally (unmatched), amount mismatches (discrepancy), partially-paid sales
-- (partial), recorded refunds (refund) and disputes.
--
-- dedupe_key = md5(store|type|provider|reference|sales_id|order_id) makes
-- queueing idempotent: repeated scans or replayed webhooks cannot duplicate
-- an open exception.
--
-- provider_owner distinguishes merchant-owned records (Paystack/Monnify keys
-- configured under the store) from platform-owned flows (fleet/subscription
-- billing) which are never surfaced here.

CREATE TABLE IF NOT EXISTS `db_payment_exceptions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL DEFAULT 1,
  `exception_type` VARCHAR(32) NOT NULL DEFAULT 'unmatched',
  `provider` VARCHAR(32) NOT NULL DEFAULT 'manual',
  `provider_owner` VARCHAR(16) NOT NULL DEFAULT 'merchant',
  `reference` VARCHAR(191) NOT NULL DEFAULT '',
  `sales_id` INT(11) NULL DEFAULT NULL,
  `order_id` INT(11) NULL DEFAULT NULL,
  `salespayment_id` INT(11) NULL DEFAULT NULL,
  `amount` DECIMAL(15,4) NULL DEFAULT NULL,
  `expected_amount` DECIMAL(15,4) NULL DEFAULT NULL,
  `currency` VARCHAR(8) NULL DEFAULT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'open',
  `detail` TEXT NULL,
  `detected_by` VARCHAR(32) NOT NULL DEFAULT 'scan',
  `payload` MEDIUMTEXT NULL,
  `dedupe_key` CHAR(32) NOT NULL DEFAULT '',
  `created_date` DATETIME NULL DEFAULT NULL,
  `created_by` VARCHAR(64) NULL DEFAULT NULL,
  `resolved_date` DATETIME NULL DEFAULT NULL,
  `resolved_by` VARCHAR(64) NULL DEFAULT NULL,
  `resolution_note` TEXT NULL,
  `status_flag` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exc_dedupe` (`store_id`, `dedupe_key`),
  KEY `idx_exc_store_status` (`store_id`, `status`),
  KEY `idx_exc_sales` (`sales_id`),
  KEY `idx_exc_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
