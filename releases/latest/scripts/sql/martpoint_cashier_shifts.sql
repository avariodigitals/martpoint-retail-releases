-- ============================================================
-- MartPoint Retail — Cashier Shift Reconciliation (Z-Report)
-- Adds per-cashier till open/close with expected vs counted
-- variance per payment method, plus optional manager sign-off.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_cashier_shifts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `shift_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Human-readable code e.g. ZR/2026/08/0001',
  `cashier_user_id` INT(11) NOT NULL,
  `cashier_username` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Denormalised username used in db_salespayments.created_by',
  `till_label` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Till / drawer identifier (free text)',
  `opening_float` DECIMAL(20,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cash counted at shift open',
  `opened_at` DATETIME NOT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `status` ENUM('open','closed','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `total_expected_cash` DECIMAL(20,4) NOT NULL DEFAULT 0.0000 COMMENT 'Opening float + cash sales - cash returns - cash expenses',
  `total_counted_cash` DECIMAL(20,4) NOT NULL DEFAULT 0.0000 COMMENT 'Cash physically counted at close',
  `cash_variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000 COMMENT 'counted - expected',
  `total_expected_other` DECIMAL(20,4) NOT NULL DEFAULT 0.0000 COMMENT 'Sum of non-cash payment methods expected',
  `total_counted_other` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `other_variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `transactions` INT(11) NOT NULL DEFAULT 0 COMMENT 'Number of sales payments attributed to this shift',
  `manager_user_id` INT(11) DEFAULT NULL COMMENT 'Approving manager at close (NULL if no sign-off)',
  `manager_username` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` ENUM('not_required','approved','rejected','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_required',
  `close_note` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_cashier` (`cashier_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_opened` (`opened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_cashier_shift_counts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shift_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `payment_type` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Matches db_payment_modes.code / db_salespayments.payment_type',
  `affects_cash_in_hand` TINYINT(1) NOT NULL DEFAULT 0,
  `expected_amount` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `counted_amount` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `txn_count` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_shift` (`shift_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
