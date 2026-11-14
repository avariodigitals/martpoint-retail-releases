-- Vehicle service reminders (v4.0.9.244).
--
-- The service register (4.0.9.242) records when a vehicle is due back; this
-- backs the reminder sweep. Delivery reuses the established email/SMS
-- infrastructure (Email_service templates + Sms_model) — the cron only reads
-- this config, so nothing sends until a store deliberately turns reminders on.
--
--   db_vehicle_reminder_config — one row per store: window, channel switches,
--     and the per-slot dedupe stamp that makes re-runs idempotent.
--   vehicle_service_due        — the customer-facing email template, seeded
--     per store and skipped where the merchant already customised the key.

CREATE TABLE IF NOT EXISTS `db_vehicle_reminder_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Store must opt in; a fresh install never messages customers by accident',
  `days_before` TINYINT(4) NOT NULL DEFAULT 7 COMMENT 'Reminder horizon in days before next_due_date',
  `email_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `sms_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_sweep_slot` VARCHAR(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Day stamp of the last sweep that sent anything; a re-run the same day is a no-op',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vrc_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the email template for every store that already has email templates.
-- Stores created after this migration receive it from
-- Email_template_model::seedDefaults(); the (store_id, template_key) match
-- keeps this idempotent and never overwrites a merchant's customised copy.
INSERT INTO `db_email_templates`
  (`store_id`, `template_key`, `template_name`, `subject`, `html_body`, `text_body`, `status`, `send_copy_to_owner`)
SELECT s.store_id, v.template_key, v.template_name, v.subject, v.html_body, v.text_body, 1, 0
FROM (SELECT DISTINCT store_id FROM `db_email_templates`) s
JOIN (
  SELECT
    'vehicle_service_due' AS template_key,
    'Vehicle Service Due' AS template_name,
    'Time to service your {vehicle_label} at {store_name}' AS subject,
    '<p>Hello {customer_name},</p><p>Our records show your <strong>{vehicle_label}</strong> had a {service_type} on {service_date}, and the next service is due on <strong>{due_date}</strong>.</p><p>Booking the work keeps the vehicle — and your on-the-road record — in good order. Reply to this email or visit us to book a slot.</p><p>If the work has already been done elsewhere, or the details look wrong, just let us know and we will update your records.</p><p>Thank you,<br>{store_name}</p>' AS html_body,
    'Hello {customer_name},\n\nYour {vehicle_label} is due for its next service on {due_date}. The last recorded work was a {service_type} on {service_date}.\n\nReply to this email or visit {store_name} to book a slot.\n\nIf the work has already been done elsewhere, let us know and we will update your records.\n\nThank you,\n{store_name}' AS text_body
) v ON 1 = 1
WHERE NOT EXISTS (
  SELECT 1 FROM (SELECT store_id, template_key FROM `db_email_templates`) d
  WHERE d.store_id = s.store_id AND d.template_key = 'vehicle_service_due'
);
