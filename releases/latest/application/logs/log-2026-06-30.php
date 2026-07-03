<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-06-30 17:34:02 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 17:34:02 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 17:55:35 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 17:55:35 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 17:57:13 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 17:57:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 17:58:00 --> Severity: error --> Exception: Call to undefined method Operations::permission_check() /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 53
ERROR - 2026-06-30 17:58:56 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 17:58:56 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Kitchen_model.php 55
ERROR - 2026-06-30 17:58:57 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 17:58:57 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Kitchen_model.php 55
ERROR - 2026-06-30 17:59:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 17:59:10 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Kitchen_model.php 55
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:00:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:00:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:00:10 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Kitchen_model.php 55
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 15
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 17
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 19
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 21
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 23
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 24
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 25
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 26
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 27
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 28
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 29
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 32
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 33
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 36
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 38
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 40
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 42
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 45
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 48
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 50
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 51
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 54
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 56
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 58
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 72
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 21
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: SITE_TITLE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 23
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 41
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 46
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 51
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 56
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 61
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 66
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 71
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 130
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 171
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 207
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 210
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 241
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 258
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 262
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 267
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 271
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 279
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 302
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 306
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 330
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 334
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 339
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 359
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 363
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 367
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 371
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 375
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 380
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 400
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 401
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 402
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 403
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 404
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 433
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 441
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 446
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 451
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 460
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 463
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 490
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 556
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 560
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 580
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 584
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 608
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 612
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 616
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 620
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 651
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 657
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 660
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 669
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 691
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 696
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 700
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 704
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 708
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 712
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 716
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 722
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 737
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 741
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 745
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 750
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 753
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 758
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 762
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 765
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 769
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 773
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 777
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 781
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 787
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 790
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 793
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 797
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 803
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 809
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 815
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 833
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 834
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 835
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 836
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 838
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 840
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 841
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 842
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 843
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 844
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 845
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 846
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 847
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 848
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 849
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 863
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 866
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 875
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 949
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 954
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 975
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 976
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 979
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 980
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 997
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1000
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1026
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1029
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1033
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1044
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1045
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1046
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1052
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1056
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1059
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1080
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1084
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1088
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1092
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1095
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1104
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1109
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1114
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1117
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1120
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1123
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1127
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1131
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1135
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1152
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1154
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1155
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 8
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 10
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 11
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 12
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 13
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 14
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 17
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 18
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 19
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 20
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 21
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 22
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 23
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 24
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 25
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 29
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 31
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 33
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 35
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 45
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 47
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 50
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 51
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 58
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 59
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 61
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 63
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 65
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 66
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 67
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 70
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 75
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 79
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 103
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 132
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 135
ERROR - 2026-06-30 18:00:54 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 225
ERROR - 2026-06-30 18:01:11 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:01:11 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 15
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 17
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 19
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 21
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 23
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 24
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 25
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 26
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 27
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 28
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 29
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 32
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 33
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 36
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 38
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 40
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 42
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 45
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 48
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 50
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 51
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 54
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 56
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 58
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 72
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 21
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: SITE_TITLE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 23
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 41
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 46
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 51
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 56
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 61
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 66
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 71
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 130
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 171
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 207
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 210
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 241
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 258
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 262
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 267
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 271
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 279
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 302
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 306
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 330
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 334
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 339
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 359
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 363
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 367
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 371
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 375
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 380
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 400
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 401
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 402
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 403
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 404
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 433
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 441
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 446
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 451
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 460
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 463
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 490
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 556
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 560
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 580
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 584
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 608
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 612
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 616
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 620
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 651
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 657
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 660
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 669
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 691
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 696
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 700
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 704
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 708
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 712
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 716
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 722
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 737
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 741
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 745
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 750
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 753
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 758
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 762
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 765
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 769
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 773
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 777
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 781
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 787
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 790
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 793
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 797
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 803
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 809
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 815
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 833
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 834
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 835
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 836
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 838
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 840
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 841
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 842
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 843
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 844
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 845
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 846
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 847
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 848
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 849
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 863
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 866
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 875
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 949
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 954
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 975
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 976
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 979
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 980
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 997
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1000
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1026
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1029
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1033
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1044
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1045
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1046
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1052
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1056
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1059
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1080
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1084
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1088
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1092
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1095
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1104
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1109
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1114
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1117
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1120
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1123
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1127
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1131
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1135
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1152
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1154
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1155
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 8
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 10
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 11
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 12
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 13
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 14
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 17
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 18
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 19
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 20
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 21
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 22
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 23
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 24
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 25
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 29
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 31
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 33
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 35
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 45
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 47
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 50
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 51
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 58
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 59
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 61
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 63
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 65
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 66
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 67
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 70
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 75
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 79
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 103
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 132
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 135
ERROR - 2026-06-30 18:01:25 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 225
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 15
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 17
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 19
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 21
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 23
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 24
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 25
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 26
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 27
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 28
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 29
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 32
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 33
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 36
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 38
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 40
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 42
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 45
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 48
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 50
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 51
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 54
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 56
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 58
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 72
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 21
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: SITE_TITLE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 23
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 41
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 46
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 51
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 56
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 61
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 66
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 71
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 130
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 171
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 207
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 210
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 241
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 258
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 262
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 267
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 271
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 279
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 302
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 306
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 330
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 334
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 339
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 359
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 363
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 367
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 371
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 375
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 380
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 400
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 401
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 402
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 403
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 404
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 433
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 441
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 446
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 451
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 460
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 463
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 490
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 556
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 560
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 580
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 584
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 608
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 612
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 616
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 620
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 651
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 657
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 660
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 669
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 691
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 696
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 700
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 704
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 708
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 712
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 716
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 722
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 737
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 741
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 745
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 750
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 753
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 758
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 762
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 765
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 769
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 773
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 777
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 781
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 787
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 790
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 793
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 797
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 803
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 809
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 815
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 833
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 834
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 835
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 836
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 838
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 840
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 841
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 842
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 843
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 844
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 845
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 846
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 847
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 848
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 849
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 863
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 866
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 875
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 949
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 954
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 975
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 976
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 979
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 980
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 997
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1000
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1026
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1029
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1033
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1044
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1045
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1046
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1052
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1056
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1059
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1080
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1084
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1088
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1092
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1095
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1104
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1109
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1114
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1117
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1120
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1123
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1127
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1131
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1135
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1152
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1154
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1155
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 8
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 10
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 11
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 12
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 13
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 14
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 17
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 18
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 19
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 20
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 21
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 22
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 23
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 24
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 25
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 29
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 31
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 33
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 35
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 45
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 47
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 50
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 51
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 58
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 59
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 61
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 63
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 65
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 66
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 67
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 70
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 75
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 79
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 103
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 132
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 135
ERROR - 2026-06-30 18:01:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 225
ERROR - 2026-06-30 18:01:49 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:01:49 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Kitchen_model.php 55
ERROR - 2026-06-30 18:01:49 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:01:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 15
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 17
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 19
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 21
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 23
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 24
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 25
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 26
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 27
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 28
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 29
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 32
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 33
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 36
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 38
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 40
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 42
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 45
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 48
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 50
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 51
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 54
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 56
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 58
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_css.php 72
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 21
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: SITE_TITLE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 23
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 41
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 46
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 51
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 56
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 61
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 66
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 71
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 115
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 130
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 171
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 207
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 210
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 241
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 258
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 262
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 267
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 271
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 279
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 302
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 306
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 330
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 334
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 339
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 359
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 363
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 367
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 371
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 375
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 380
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 400
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 401
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 402
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 403
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 404
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 433
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 441
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 446
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 451
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 460
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 463
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 490
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 556
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 560
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 580
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 584
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 608
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 612
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 616
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 620
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 651
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 657
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 660
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 669
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 691
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 696
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 700
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 704
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 708
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 712
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 716
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 722
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 737
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 741
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 745
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 750
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 753
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 758
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 762
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 765
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 769
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 773
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 777
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 781
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 787
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 790
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 793
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 797
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 803
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 809
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 815
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 833
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 834
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 835
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 836
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 838
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 840
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 841
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 842
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 843
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 844
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 845
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 846
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 847
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 848
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 849
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 863
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 866
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 875
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 949
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 954
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 975
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 976
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 979
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 980
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 997
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1000
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1026
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1029
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1033
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1044
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1045
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1046
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1052
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1056
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1059
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1080
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1084
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1088
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1092
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1095
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1104
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1109
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1114
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1117
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1120
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1123
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1127
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1131
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1135
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1152
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1154
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: base_url /Users/ralphmore/Sites/localhost/martpoint retail/application/views/sidebar.php 1155
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 8
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 10
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 11
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 12
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 13
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 14
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 17
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 18
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 19
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 20
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 21
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 22
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 23
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 24
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 25
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 29
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 31
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 33
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 35
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 45
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 47
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 50
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 51
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 58
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 59
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 61
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 63
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 65
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 66
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 67
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 70
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 75
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 79
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 103
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 124
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 132
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: VIEW_DATE /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 135
ERROR - 2026-06-30 18:02:27 --> Severity: Notice --> Undefined variable: theme_link /Users/ralphmore/Sites/localhost/martpoint retail/application/views/comman/code_js.php 225
ERROR - 2026-06-30 18:03:56 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:03:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:03:59 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:03:59 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:08 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:08 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:13 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:13 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:14 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:14 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:52 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:52 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:52 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:04:56 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:04:56 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:04:56 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:19 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:23 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:05:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:05:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:00 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:00 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:04 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:05 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:05 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:09 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:06:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:15 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:06:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:22 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:06:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:06:36 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:06:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:09:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:09:47 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:09:47 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:10:04 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:10:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:05 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:10:06 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:10:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:00 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:11:00 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:01 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:01 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:11:24 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:11:24 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:11:24 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:11:24 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:11:24 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:24 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:11:24 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:11:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 270
ERROR - 2026-06-30 18:11:44 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 270
ERROR - 2026-06-30 18:11:44 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:11:44 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:11:44 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:11:44 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:07 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:12:07 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 272
ERROR - 2026-06-30 18:12:07 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 272
ERROR - 2026-06-30 18:12:07 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:12:07 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:07 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:12:07 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:25 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:12:25 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 273
ERROR - 2026-06-30 18:12:25 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 273
ERROR - 2026-06-30 18:12:25 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:12:25 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:25 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:12:25 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:35 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:12:35 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 274
ERROR - 2026-06-30 18:12:35 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 274
ERROR - 2026-06-30 18:12:35 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:12:35 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:35 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:12:35 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:12:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 122
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 275
ERROR - 2026-06-30 18:12:48 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 275
ERROR - 2026-06-30 18:12:49 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:12:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:12:49 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:12:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:13:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:39 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:39 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:13:39 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:39 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:13:39 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:40 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:13:40 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:40 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:13:40 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:40 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:13:40 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:40 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:13:40 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:40 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 235
ERROR - 2026-06-30 18:13:40 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:13:40 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:13:40 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:13:40 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:08 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:08 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:08 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:08 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:09 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:09 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:09 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:09 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:09 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:09 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:10 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:10 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:10 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:10 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:10 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:10 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:10 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:10 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:10 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:10 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:11 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:11 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:11 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:11 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:11 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:11 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:11 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:11 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:11 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:12 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:12 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:13 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:14:13 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:16 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:16 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:16 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:14:16 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:19 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:19 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:22 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:22 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:14:22 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:38 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:38 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:38 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:14:38 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:14:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 4
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 82
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 96
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:44 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:/Applications/MAMP/bin/php/php7.4.33/lib/php') /Users/ralphmore/Sites/localhost/martpoint retail/application/views/operations/kitchen.php 237
ERROR - 2026-06-30 18:14:44 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:14:44 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:14:44 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:14:44 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:17:30 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:30 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:31 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:31 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:17:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:17:33 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:33 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:33 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:33 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:33 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:33 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:33 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:34 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:17:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:48 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/css
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/bootstrap
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/dist
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/toastr
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/plugins
ERROR - 2026-06-30 18:17:49 --> 404 Page Not Found: Operations/js
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:17:49 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:20:11 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:20:12 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:20:12 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:12 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:20:12 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:20:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:20 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:20:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:23 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:20:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:23 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:20:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:25 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:20:25 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:20:26 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:20:26 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:22:05 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 18:22:05 --> Could not find the language line "company_address"
ERROR - 2026-06-30 18:22:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:22:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:22:42 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:22:42 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:23:18 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:23:18 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:23:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:23:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:23:59 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:23:59 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:24:01 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:24:01 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:24:45 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:24:45 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:25:20 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:25:20 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:38 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:26:39 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:27:04 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 18:27:17 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:27:17 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `s`.`status` = 1
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:28:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:28:44 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:28:45 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:28:45 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:28:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:28:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:07 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:07 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:43 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:43 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:47 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:29:48 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:30:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:30:46 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:31:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:31:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`sales_date`, `s`.`created_date`, `s`.`created_time`, `s`.`customer_id`, `c`.`customer_name`, `s`.`grand_total`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:32:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:32:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:32 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` IN('new', 'preparing', 'ready')
ORDER BY `ko`.`created_at` ASC
 LIMIT 50
ERROR - 2026-06-30 18:33:33 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT `ko`.`id` as `kitchen_order_id`, `ko`.`kds_status`, `ko`.`created_at` as `kitchen_created`, `ko`.`updated_at`, `s`.`id` as `sales_id`, `s`.`sales_code`, `s`.`customer_id`, `c`.`customer_name`, `s`.`description` as `order_note`
FROM `db_kitchen_orders` `ko`
LEFT JOIN `db_sales` `s` ON `s`.`id` = `ko`.`sales_id`
LEFT JOIN `db_customers` `c` ON `c`.`id` = `s`.`customer_id`
WHERE `ko`.`store_id` = '2'
AND `ko`.`kds_status` = 'served'
ORDER BY `ko`.`created_at` ASC
 LIMIT 10
ERROR - 2026-06-30 18:34:18 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT ko.id as kitchen_order_id, ko.kds_status, ko.created_at as kitchen_created, ko.updated_at,
                    s.id as sales_id, s.sales_code, s.customer_id, c.customer_name, s.description as order_note
                    FROM db_kitchen_orders ko
                    LEFT JOIN db_sales s ON s.id = ko.sales_id
                    LEFT JOIN db_customers c ON c.id = s.customer_id
                    WHERE ko.store_id = '2' AND ko.kds_status IN ('new','preparing','ready')
                    ORDER BY ko.created_at ASC LIMIT 10
ERROR - 2026-06-30 18:34:18 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 100
ERROR - 2026-06-30 18:34:19 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT ko.id as kitchen_order_id, ko.kds_status, ko.created_at as kitchen_created, ko.updated_at,
                    s.id as sales_id, s.sales_code, s.customer_id, c.customer_name, s.description as order_note
                    FROM db_kitchen_orders ko
                    LEFT JOIN db_sales s ON s.id = ko.sales_id
                    LEFT JOIN db_customers c ON c.id = s.customer_id
                    WHERE ko.store_id = '2' AND ko.kds_status IN ('new','preparing','ready')
                    ORDER BY ko.created_at ASC LIMIT 10
ERROR - 2026-06-30 18:34:19 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 100
ERROR - 2026-06-30 18:34:21 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT ko.id as kitchen_order_id, ko.kds_status, ko.created_at as kitchen_created, ko.updated_at,
                    s.id as sales_id, s.sales_code, s.customer_id, c.customer_name, s.description as order_note
                    FROM db_kitchen_orders ko
                    LEFT JOIN db_sales s ON s.id = ko.sales_id
                    LEFT JOIN db_customers c ON c.id = s.customer_id
                    WHERE ko.store_id = '2' AND ko.kds_status IN ('new','preparing','ready')
                    ORDER BY ko.created_at ASC LIMIT 10
ERROR - 2026-06-30 18:34:21 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 100
ERROR - 2026-06-30 18:34:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT ko.id as kitchen_order_id, ko.kds_status, ko.created_at as kitchen_created, ko.updated_at,
                    s.id as sales_id, s.sales_code, s.customer_id, c.customer_name, s.description as order_note
                    FROM db_kitchen_orders ko
                    LEFT JOIN db_sales s ON s.id = ko.sales_id
                    LEFT JOIN db_customers c ON c.id = s.customer_id
                    WHERE ko.store_id = '2' AND ko.kds_status IN ('new','preparing','ready')
                    ORDER BY ko.created_at ASC LIMIT 10
ERROR - 2026-06-30 18:34:22 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 100
ERROR - 2026-06-30 18:34:22 --> Query error: Unknown column 's.description' in 'field list' - Invalid query: SELECT ko.id as kitchen_order_id, ko.kds_status, ko.created_at as kitchen_created, ko.updated_at,
                    s.id as sales_id, s.sales_code, s.customer_id, c.customer_name, s.description as order_note
                    FROM db_kitchen_orders ko
                    LEFT JOIN db_sales s ON s.id = ko.sales_id
                    LEFT JOIN db_customers c ON c.id = s.customer_id
                    WHERE ko.store_id = '2' AND ko.kds_status IN ('new','preparing','ready')
                    ORDER BY ko.created_at ASC LIMIT 10
ERROR - 2026-06-30 18:34:22 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/controllers/Operations.php 100
ERROR - 2026-06-30 18:36:26 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 18:36:26 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:36:27 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 18:36:27 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 18:55:34 --> Severity: error --> Exception: Unable to locate the model you have specified: Categories_model /Users/ralphmore/Sites/localhost/martpoint retail/system/core/Loader.php 348
ERROR - 2026-06-30 18:56:07 --> Severity: error --> Exception: Unable to locate the model you have specified: Categories_model /Users/ralphmore/Sites/localhost/martpoint retail/system/core/Loader.php 348
ERROR - 2026-06-30 18:56:07 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 18:56:07 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:18:34 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:18:34 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:40:17 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:40:17 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:44:26 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:44:26 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:44:41 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:44:57 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:45:12 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:45:27 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:45:36 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 19:45:42 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:45:56 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:45:57 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:46:00 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:48:06 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 19:48:43 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:48:43 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:48:44 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:48:44 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:48:46 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:48:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:48:48 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 19:48:48 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 19:48:56 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:48:59 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:49:15 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:49:30 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:49:45 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:00 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:15 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:21 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:27 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:43 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:50:58 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:51:09 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:51:25 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:51:40 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:51:55 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:52:05 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:52:21 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:52:36 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:52:51 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:52:58 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 19:53:06 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:09 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:25 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:37 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:38 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:38 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:53:54 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:09 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:24 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:39 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:47 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:54 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:54 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:54 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:55 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:55 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:55 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:55 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:54:57 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:13 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:28 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:34 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:40 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:44 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:45 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:55:45 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:56:01 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:56:05 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 19:56:12 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:56:28 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:56:43 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:56:58 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:57:13 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:57:28 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:57:43 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:57:59 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:58:01 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:58:17 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 19:58:32 --> Query error: Table 'marttes.laundry_orders' doesn't exist - Invalid query: INSERT INTO laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM sales s
                WHERE s.store_id = '2' AND s.status = 1 AND s.sales_status = 'Final'
                  AND s.id NOT IN (
                      SELECT sales_id FROM laundry_orders WHERE store_id = '2'
                  )
ERROR - 2026-06-30 20:18:33 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 20:20:10 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 20:34:22 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:34:22 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:34:55 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:34:55 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:34:56 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:34:56 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:35:36 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 20:35:36 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:35:38 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 20:35:38 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:35:42 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:35:42 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:35:45 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:35:45 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:35:46 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:35:46 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:36:03 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 20:36:03 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:36:13 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:36:13 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:36:20 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 20:36:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:36:26 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:36:26 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:37:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 20:37:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:37:20 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 20:37:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:37:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 20:37:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:37:20 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 20:37:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:37:28 --> Query error: Unknown column 'i.item_id' in 'field list' - Invalid query: SELECT `si`.`sales_qty`, `si`.`description`, `i`.`item_name`, `i`.`item_id`
FROM `db_salesitems` `si`
LEFT JOIN `db_items` `i` ON `i`.`id` = `si`.`item_id`
WHERE `si`.`sales_id` = '2'
ERROR - 2026-06-30 20:37:28 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 162
ERROR - 2026-06-30 20:40:05 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 20:40:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:40:05 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 20:40:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:40:23 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 20:40:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:40:23 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 20:40:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:41:02 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 20:41:02 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 20:41:02 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 20:41:02 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:08:20 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:08:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:08:20 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:08:20 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:11:21 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 21:11:21 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:11:33 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:11:33 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:11:33 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:11:33 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:12:28 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 21:12:28 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:13:23 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:13:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:13:23 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:13:23 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:14:29 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:14:29 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:14:29 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:14:29 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:14:32 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:14:32 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:14:32 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:14:32 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:21:39 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:22:31 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:22:56 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:23:24 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:24:52 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:24:59 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:24:59 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:24:59 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:24:59 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:05 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:25:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:05 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:25:05 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:08 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:25:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:08 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:25:08 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:15 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:25:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:25:15 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:25:15 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:45 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 308
ERROR - 2026-06-30 21:26:46 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:26:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:46 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:26:46 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:54 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:26:54 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:55 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:26:55 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:57 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:26:57 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:26:57 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:26:57 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:28:06 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:28:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:28:06 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:28:06 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:28:09 --> 404 Page Not Found: Well-known/appspecific
ERROR - 2026-06-30 21:28:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:28:09 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-06-30 21:28:09 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:32:40 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:32:56 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:33:10 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:33:26 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:33:40 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:33:56 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:11 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:26 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:41 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:34:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:35:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:35:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:35:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:35:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:36:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:36:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:36:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:36:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:37:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:37:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:37:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:37:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:38:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:38:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:38:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:38:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:39:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:39:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:39:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:39:59 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:40:14 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:40:29 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:40:44 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:40:45 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:41:01 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:41:11 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:41:27 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:41:28 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 21:41:28 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 21:41:42 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:41:57 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:42:12 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:42:27 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:42:32 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:42:47 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:43:02 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:43:17 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:43:33 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:43:48 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:44:03 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:44:18 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:45:19 --> Severity: error --> Exception: syntax error, unexpected 'UPDATE' (T_STRING), expecting ')' /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Laundry_model.php 371
ERROR - 2026-06-30 21:46:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:47:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:47:32 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:47:33 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:47:41 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:47:56 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:48:11 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:48:26 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:48:41 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:48:56 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:11 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:13 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:16 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:27 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:27 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:27 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:28 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:43 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:49:58 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:50:13 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:50:28 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:50:43 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:50:58 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:00 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:02 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:04 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:04 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:04 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:04 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:05 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:05 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:35 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:51:50 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:52:05 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:52:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:52:35 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:52:50 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:53:05 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:53:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:53:36 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:53:51 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:54:06 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:54:21 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:55:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:56:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:57:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:58:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 21:59:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:00:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:01:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:02:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:03:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:04:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:05:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:06:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:07:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:08:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:09:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:10:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:11:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:12:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:13:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:14:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:15:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:16:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:17:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:18:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:19:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:20:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:21:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:22:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:23:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:24:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:25:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:26:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:27:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:28:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:29:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:30:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:31:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:32:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:33:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:34:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:35:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:36:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:37:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:38:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:39:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:40:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:41:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:42:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:43:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:44:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:45:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:46:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:47:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:48:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:49:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:50:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:51:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:52:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:53:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:54:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:55:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:56:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:57:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:58:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 22:59:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:00:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:01:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:02:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:03:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:04:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:05:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:06:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:07:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:07:39 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:08:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:09:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:10:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:11:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:12:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:13:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:14:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:15:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:16:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:17:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:18:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:19:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:20:20 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:21:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:22:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:23:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:24:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:25:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:26:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:27:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:28:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:29:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:29:26 --> 404 Page Not Found: Run_staff_commission_migrationphp/index
ERROR - 2026-06-30 23:29:26 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
ERROR - 2026-06-30 23:30:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:31:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:32:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:33:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:34:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:35:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:36:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:37:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:38:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:39:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:40:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:41:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:42:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:43:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:44:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:45:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:46:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:47:19 --> Query error: Illegal mix of collations (utf8mb4_0900_ai_ci,IMPLICIT) and (utf8mb4_unicode_ci,IMPLICIT) for operation '<>' - Invalid query: UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type
ERROR - 2026-06-30 23:50:47 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Sites/localhost/martpoint retail/application/models/Pos_model.php 317
ERROR - 2026-06-30 23:50:59 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-06-30 23:50:59 --> Severity: error --> Exception: Call to undefined function base_url() /Users/ralphmore/Sites/localhost/martpoint retail/application/views/errors/html/error_404.php 87
