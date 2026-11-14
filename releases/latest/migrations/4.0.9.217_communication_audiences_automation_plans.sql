-- Central owns automation plans, templates and registered workflow slots.
-- Companion tables preserve existing subscriptions, workflows and messages.
CREATE TABLE IF NOT EXISTS db_communication_auto_plans (
 plan_key VARCHAR(40) NOT NULL PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 max_workflows INT UNSIGNED NOT NULL,
 monthly_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 annual_kobo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 template_keys_json TEXT NOT NULL,
 enabled TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_auto_plans (plan_key,name,max_workflows,monthly_kobo,annual_kobo,template_keys_json)
 SELECT 'plan_a','Plan A',10,monthly_kobo,annual_kobo,'["welcome","winback","newsletter","custom"]' FROM db_communication_catalogue WHERE module_key='automation';
INSERT IGNORE INTO db_communication_auto_plans VALUES ('plan_b','Plan B',20,0,0,'["welcome","winback","newsletter","custom"]',1);
CREATE TABLE IF NOT EXISTS db_communication_auto_templates (
 template_key VARCHAR(40) NOT NULL PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 description VARCHAR(500) NOT NULL,
 segment_key VARCHAR(40) NOT NULL,
 subject VARCHAR(255) NOT NULL DEFAULT '',
 message TEXT NOT NULL,
 interval_days INT UNSIGNED NOT NULL DEFAULT 30,
 enabled TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO db_communication_auto_templates VALUES
 ('welcome','Welcome new customers','A recurring introduction to customers created in the last 30 days.','new_30','Welcome to our community','Welcome, {name}. We are glad you are here. Reply if you need help.',30,1),
 ('winback','Reconnect with lapsed customers','Follow up with customers who have not completed a sale for at least 30 days.','lapsed_30','We would love to see you again','Hi {name}, it has been a while. Here is what is new with us.',30,1),
 ('newsletter','Regular audience update','A scheduled update to active newsletter subscribers.','subscribers','Your regular update','Hi {name}, here are our latest news and updates.',30,1),
 ('custom','Custom recurring campaign','Choose an audience, schedule and message within your plan allowance.','communications','A message from our team','Hi {name}, here is an update from our team.',30,1);
CREATE TABLE IF NOT EXISTS db_communication_auto_subscriptions (
 account_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 plan_key VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_auto_request_plans (
 request_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 plan_key VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_auto_registrations (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 account_id BIGINT UNSIGNED NOT NULL,
 workflow_key VARCHAR(100) NOT NULL,
 name VARCHAR(150) NOT NULL,
 template_key VARCHAR(40) NOT NULL,
 enabled TINYINT NOT NULL DEFAULT 0,
 archived TINYINT NOT NULL DEFAULT 0,
 updated_at DATETIME NOT NULL,
 UNIQUE KEY comm_auto_registration (account_id,workflow_key),
 KEY comm_auto_slots (account_id,archived,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS db_communication_auto_workflows (
 workflow_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
 store_id INT NOT NULL,
 template_key VARCHAR(40) NOT NULL DEFAULT 'custom',
 archived TINYINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
