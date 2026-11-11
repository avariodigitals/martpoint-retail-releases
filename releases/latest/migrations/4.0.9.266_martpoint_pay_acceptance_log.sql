-- Immutable record of fleet acceptance of the Central MartPoint Pay fee terms.
CREATE TABLE IF NOT EXISTS `db_payment_subaccount_acceptances` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `provider` VARCHAR(32) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `terms_version` VARCHAR(64) NOT NULL,
  `platform_fee_percent` DECIMAL(8,4) NOT NULL DEFAULT 0,
  `platform_fee_fixed` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `fee_bearer` VARCHAR(16) NOT NULL,
  `example_amount` DECIMAL(18,2) NOT NULL,
  `example_platform_fee` DECIMAL(18,2) NOT NULL,
  `terms_snapshot` TEXT NOT NULL,
  `accepted_by` VARCHAR(64) NOT NULL DEFAULT '',
  `accepted_by_user_id` INT(11) DEFAULT NULL,
  `accepted_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mp_pay_accept_store` (`provider`, `store_id`, `accepted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
