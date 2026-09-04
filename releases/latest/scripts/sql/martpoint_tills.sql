-- ============================================================
-- MartPoint Retail — Till Accounting (per-cashier cash-in-hand)
-- Adds db_tills and links db_cashier_shifts to a real account.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_tills` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `till_name` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-friendly till label e.g. Till 1, Front Counter',
  `cashier_user_id` INT(11) DEFAULT NULL COMMENT 'Optional: assigned cashier. NULL = shared till.',
  `account_id` INT(11) NOT NULL COMMENT 'ac_accounts.id that tracks this till\'s cash balance',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default till for a cashier if no shift open',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_cashier` (`cashier_user_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `db_cashier_shifts`
  ADD COLUMN `till_id` INT(11) DEFAULT NULL COMMENT 'db_tills.id for this shift' AFTER `cashier_username`,
  ADD COLUMN `cash_account_id` INT(11) DEFAULT NULL COMMENT 'Denormalised ac_accounts.id for the till' AFTER `till_id`,
  ADD KEY `idx_till` (`till_id`);

ALTER TABLE `db_cashier_shift_counts`
  ADD COLUMN `account_id` INT(11) DEFAULT NULL COMMENT 'ac_accounts.id when this payment method affects a cash account' AFTER `affects_cash_in_hand`;
