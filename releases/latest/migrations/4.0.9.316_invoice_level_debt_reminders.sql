-- Store reminder preferences and send history per sales invoice.
CREATE TABLE IF NOT EXISTS `db_debt_reminder_invoice_state` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL,
  `invoice_id` int unsigned NOT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `frequency` varchar(16) DEFAULT NULL,
  `max_reminders` int DEFAULT NULL,
  `reminder_count` int unsigned NOT NULL DEFAULT 0,
  `last_reminder_sent` datetime DEFAULT NULL,
  `send_email` tinyint(1) DEFAULT NULL,
  `send_sms` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_debt_reminder_invoice` (`store_id`,`invoice_id`),
  KEY `ix_debt_reminder_invoice_schedule` (`store_id`,`enabled`,`last_reminder_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Old reminder history remains intact; new events identify the exact invoice.
SET @has_invoice_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_debt_reminder_history' AND COLUMN_NAME = 'invoice_id'
);
SET @ddl := IF(@has_invoice_id = 0,
  'ALTER TABLE `db_debt_reminder_history` ADD COLUMN `invoice_id` int unsigned DEFAULT NULL AFTER `customer_id`, ADD KEY `ix_debt_reminder_history_invoice` (`store_id`,`invoice_id`,`sent_at`)',
  'SELECT 1'
);
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
