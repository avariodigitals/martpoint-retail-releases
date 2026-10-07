-- Ensure quotation response links exist on installs that already advanced
-- their database version before the original response-link migration shipped.
SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotation' AND COLUMN_NAME='response_token_hash') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `response_token_hash` CHAR(64) NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_quotation' AND COLUMN_NAME='response_token_expires_at') = 0,
  'ALTER TABLE `db_quotation` ADD COLUMN `response_token_expires_at` DATETIME NULL',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Correct the stock email copy on stores that already have the base template.
UPDATE `db_email_templates`
SET `html_body` = REPLACE(REPLACE(`html_body`, 'is attached below.', 'is ready to review using the secure link below.'), 'To accept, reply to this email or contact us before the validity date.', 'Use the secure link below to accept or decline the quotation and send us a note.'),
    `text_body` = REPLACE(`text_body`, 'To accept, reply to this email or contact us before the validity date.', 'Use the secure link below to accept or decline the quotation and send us a note.')
WHERE `template_key` = 'quotation_sent';
