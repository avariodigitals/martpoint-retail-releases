-- Update the stock quotation email copy to describe the secure customer
-- accept/decline page. Preserve customer-customized templates.
UPDATE `db_email_templates`
SET `html_body` = REPLACE(`html_body`, 'To accept, reply to this email or contact us before the validity date.', 'Use the secure link below to accept or decline the quotation and send us a note.'),
    `text_body` = REPLACE(`text_body`, 'To accept, reply to this email or contact us before the validity date.', 'Use the secure link below to accept or decline the quotation and send us a note.')
WHERE `template_key` = 'quotation_sent';

UPDATE `db_email_templates`
SET `html_body` = REPLACE(`html_body`, 'is attached below.', 'is ready to review using the secure link below.'),
    `text_body` = REPLACE(`text_body`, 'is ready.\n\nTotal:', 'is ready to review using the secure link below.\n\nTotal:')
WHERE `template_key` = 'quotation_sent';
