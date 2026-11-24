-- Set the platform bank account displayed for manual SMS and email credit requests.
UPDATE db_communication_sms_settings
SET bank_name = 'Access Bank',
    bank_account_name = 'MartPoint Solutions & systems LTD',
    bank_account_number = '0012386745'
WHERE id = 1
  AND (bank_account_number IS NULL OR bank_account_number = '');
