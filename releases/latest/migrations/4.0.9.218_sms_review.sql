-- Communications review: private evidence, Sender IDs, credit bundles and scheduling.
-- Companion tables keep this migration additive and safe to rerun.
CREATE TABLE IF NOT EXISTS db_communication_documents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id INT NOT NULL,
 kind VARCHAR(20) NOT NULL,
 reference_id BIGINT UNSIGNED NOT NULL,
 slot VARCHAR(20) NOT NULL,
 filename VARCHAR(190) NOT NULL,
 mime VARCHAR(40) NOT NULL,
 sha256 CHAR(64) NOT NULL,
 content MEDIUMBLOB NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY document_slot (kind,reference_id,slot),
 KEY account_documents (account_id,kind,reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_communication_sms_settings (
 id INT NOT NULL PRIMARY KEY,
 sender_mode VARCHAR(10) NOT NULL DEFAULT 'shared',
 shared_sender VARCHAR(11) NOT NULL DEFAULT '',
 bank_name VARCHAR(100) NOT NULL DEFAULT '',
 bank_account_name VARCHAR(190) NOT NULL DEFAULT '',
 bank_account_number VARCHAR(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_sms_settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS db_communication_sender_requests (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id INT NOT NULL,
 request_key VARCHAR(100) NOT NULL,
 sender_id VARCHAR(11) NOT NULL,
 business_name VARCHAR(190) NOT NULL,
 contact_email VARCHAR(190) NOT NULL,
 sample_message TEXT NOT NULL,
 status VARCHAR(15) NOT NULL DEFAULT 'pending',
 note VARCHAR(500) NOT NULL DEFAULT '',
 reviewed_by VARCHAR(100) NULL,
 reviewed_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY sender_replay (account_id,request_key),
 KEY sender_account (account_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_sender_assignments (
 account_id INT NOT NULL PRIMARY KEY,
 request_id BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_communication_credit_rules (
 channel VARCHAR(10) NOT NULL PRIMARY KEY,
 minimum_kobo INT NOT NULL DEFAULT 10000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_credit_rules (channel) VALUES ('sms'),('email');
CREATE TABLE IF NOT EXISTS db_communication_credit_packages (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 channel VARCHAR(10) NOT NULL,
 name VARCHAR(100) NOT NULL,
 amount_kobo INT NOT NULL,
 credit_units INT NOT NULL,
 enabled TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_request_packages (
 request_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 package_id INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_communication_campaign_schedules (
 campaign_id INT NOT NULL PRIMARY KEY,
 store_id INT NOT NULL,
 scheduled_at DATETIME NOT NULL,
 budget_units INT NOT NULL,
 status VARCHAR(15) NOT NULL DEFAULT 'scheduled',
 last_error VARCHAR(500) NULL,
 created_at DATETIME NOT NULL,
 KEY due_campaign (status,scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Central must explicitly assign the new birthday template to eligible plans.
INSERT IGNORE INTO db_communication_auto_templates
 (template_key,name,description,segment_key,subject,message,interval_days,enabled)
 VALUES ('birthday','Birthday wishes','Check recorded customer birthdays daily. Uses an Automation slot and channel credits.','birthday_today','Happy birthday!','Happy birthday, {name}! Everyone at {store} wishes you a wonderful day.',1,1);
