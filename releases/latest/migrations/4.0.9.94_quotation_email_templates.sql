-- ============================================================================
-- MartPoint 4.0.9.94 — Quotation email templates
--
-- A quotation must tell the customer HOW LONG it is valid. These templates are
-- seeded per store (skipped where the store already customised them), so the
-- expiry date and the reminder countdown are stated in the email itself.
--
--   quotation_sent            — the quotation, including its validity date
--   quotation_expiry_reminder — the 3-day / 1-day nudge before it lapses
--
-- Idempotent: matched on (store_id, template_key).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

INSERT INTO `db_email_templates`
  (`store_id`, `template_key`, `template_name`, `subject`, `html_body`, `text_body`, `status`, `send_copy_to_owner`)
SELECT s.store_id, v.template_key, v.template_name, v.subject, v.html_body, v.text_body, 1, 0
FROM (SELECT DISTINCT store_id FROM `db_storefront_settings` WHERE store_status = 'active') s
JOIN (
  SELECT
    'quotation_sent' AS template_key,
    'Quotation Sent' AS template_name,
    'Quotation {quotation_code} from {store_name} — valid until {expire_date}' AS subject,
    '<p>Hello {customer_name},</p><p>Your quotation <strong>{quotation_code}</strong> from {store_name} is attached below.</p><p><strong>Total:</strong> {quotation_total}<br><strong>Valid until:</strong> {expire_date} — prices and availability are held until this date.</p><p>To accept, reply to this email or contact us before the validity date. After it lapses the quotation expires and would need to be re-quoted.</p><p><a href="{quotation_link}">View the quotation</a></p><p>Thank you,<br>{store_name}</p>' AS html_body,
    'Hello {customer_name},\n\nYour quotation {quotation_code} from {store_name} is ready.\n\nTotal: {quotation_total}\nValid until: {expire_date} — prices and availability are held until this date.\n\nTo accept, reply to this email or contact us before the validity date.\n\nView the quotation:\n{quotation_link}\n\nThank you,\n{store_name}' AS text_body
  UNION ALL SELECT
    'quotation_expiry_reminder',
    'Quotation Expiry Reminder',
    'Reminder: quotation {quotation_code} expires in {days_left} day(s)',
    '<p>Hello {customer_name},</p><p>This is a reminder that quotation <strong>{quotation_code}</strong> from {store_name} expires on <strong>{expire_date}</strong> — that is {days_left} day(s) from now.</p><p><strong>Total:</strong> {quotation_total}</p><p>If you would like to proceed, please confirm before the validity date. Once it lapses the quotation expires and the price would need to be re-quoted.</p><p><a href="{quotation_link}">View the quotation</a></p><p>Thank you,<br>{store_name}</p>',
    'Hello {customer_name},\n\nReminder: quotation {quotation_code} from {store_name} expires on {expire_date} — {days_left} day(s) from now.\n\nTotal: {quotation_total}\n\nTo proceed, please confirm before the validity date.\n\nView the quotation:\n{quotation_link}\n\nThank you,\n{store_name}'
) v ON 1 = 1
WHERE NOT EXISTS (
  SELECT 1 FROM `db_email_templates` d
  WHERE d.store_id = s.store_id AND d.template_key = v.template_key
);

SET FOREIGN_KEY_CHECKS = 1;
