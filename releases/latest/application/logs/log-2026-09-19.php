<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-19 08:43:45 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-19 08:43:46 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-19 08:50:35 --> Missing required table: db_email_templates. Run the 4.0.2 migration via login.
ERROR - 2026-09-19 08:50:35 --> Query error: Table 'martpoint_seed_test.db_email_templates' doesn't exist - Invalid query: SELECT COUNT(*) AS `numrows`
FROM `db_email_templates`
WHERE `store_id` = '2'
AND `template_key` = 'invoice_sent'
ERROR - 2026-09-19 08:50:35 --> Severity: Error --> Uncaught Error: Call to a member function num_rows() on bool in /Users/ralphmore/Herd/martpointretailapp/system/database/DB_query_builder.php:1429
Stack trace:
#0 /Users/ralphmore/Herd/martpointretailapp/application/models/Email_template_model.php(86): CI_DB_query_builder->count_all_results('db_email_templa...')
#1 /Users/ralphmore/Herd/martpointretailapp/application/controllers/Install_seed.php(117): Email_template_model->seedDefaults('2')
#2 /Users/ralphmore/Herd/martpointretailapp/application/controllers/Install_seed.php(37): Install_seed->_apply_email_defaults('2')
#3 /Users/ralphmore/Herd/martpointretailapp/system/core/CodeIgniter.php(532): Install_seed->index()
#4 /Users/ralphmore/Herd/martpointretailapp/index.php(388): require_once('/Users/ralphmor...')
#5 Command line code(1): include('/Users/ralphmor...')
#6 {main}
  thrown /Users/ralphmore/Herd/martpointretailapp/system/database/DB_query_builder.php 1429
ERROR - 2026-09-19 08:50:38 --> Missing required table: db_email_templates. Run the 4.0.2 migration via login.
ERROR - 2026-09-19 08:50:38 --> Query error: Table 'martpoint_seed_test.db_email_templates' doesn't exist - Invalid query: SELECT COUNT(*) AS `numrows`
FROM `db_email_templates`
WHERE `store_id` = '2'
AND `template_key` = 'invoice_sent'
ERROR - 2026-09-19 08:50:38 --> Severity: Error --> Uncaught Error: Call to a member function num_rows() on bool in /Users/ralphmore/Herd/martpointretailapp/system/database/DB_query_builder.php:1429
Stack trace:
#0 /Users/ralphmore/Herd/martpointretailapp/application/models/Email_template_model.php(86): CI_DB_query_builder->count_all_results('db_email_templa...')
#1 /Users/ralphmore/Herd/martpointretailapp/application/controllers/Install_seed.php(117): Email_template_model->seedDefaults('2')
#2 /Users/ralphmore/Herd/martpointretailapp/application/controllers/Install_seed.php(37): Install_seed->_apply_email_defaults('2')
#3 /Users/ralphmore/Herd/martpointretailapp/system/core/CodeIgniter.php(532): Install_seed->index()
#4 /Users/ralphmore/Herd/martpointretailapp/index.php(388): require_once('/Users/ralphmor...')
#5 Command line code(1): include('/Users/ralphmor...')
#6 {main}
  thrown /Users/ralphmore/Herd/martpointretailapp/system/database/DB_query_builder.php 1429
ERROR - 2026-09-19 14:34:12 --> Query error: Table 'martpoint.db_monnify_settings' doesn't exist - Invalid query: SELECT *
FROM `db_monnify_settings`
WHERE `store_id` = '2'
ERROR - 2026-09-19 14:34:12 --> Severity: error --> Exception: Call to a member function row() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Monnify_model.php 14
ERROR - 2026-09-19 18:49:48 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-19 22:56:11 --> 4.0.2 migration error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '='
ERROR - 2026-09-19 22:57:37 --> 404 Page Not Found: Theme/plugins
