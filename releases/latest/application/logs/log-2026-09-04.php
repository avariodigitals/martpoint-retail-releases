<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-04 00:03:57 --> Query error: Unknown column 'sold.qty' in 'having clause' - Invalid query: SELECT `i`.`id`, `i`.`item_name`, `i`.`stock`, `i`.`purchase_price`, `i`.`sales_price`, COALESCE(sold.qty, 0) as qty_sold, COALESCE(sold.rev, 0) as revenue, `c`.`category_name`
FROM `db_items` `i`
LEFT JOIN `db_category` `c` ON `c`.`id` = `i`.`category_id`
LEFT JOIN (SELECT si.item_id, SUM(si.sales_qty) as qty, SUM(si.total_cost) as rev
			FROM db_salesitems si
			INNER JOIN db_sales s ON s.id = si.sales_id
			WHERE s.sales_status='Final' AND s.store_id='2'
			AND s.sales_date >= '2026-08-05' AND s.sales_date <= '2026-09-04'
			GROUP BY si.item_id) sold ON `sold`.`item_id` = `i`.`id`
WHERE `i`.`store_id` = '2'
AND `i`.`service_bit` = 0
AND `i`.`status` = 1
HAVING (COALESCE(sold.qty,0) + i.stock) >0
ORDER BY `qty_sold` DESC
ERROR - 2026-09-04 00:03:57 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Reports_model.php 4617
ERROR - 2026-09-04 00:04:41 --> Query error: Unknown column 'sold.qty' in 'having clause' - Invalid query: SELECT `i`.`id`, `i`.`item_name`, `i`.`stock`, `i`.`purchase_price`, `i`.`sales_price`, COALESCE(sold.qty, 0) as qty_sold, COALESCE(sold.rev, 0) as revenue, `c`.`category_name`
FROM `db_items` `i`
LEFT JOIN `db_category` `c` ON `c`.`id` = `i`.`category_id`
LEFT JOIN (SELECT si.item_id, SUM(si.sales_qty) as qty, SUM(si.total_cost) as rev
			FROM db_salesitems si
			INNER JOIN db_sales s ON s.id = si.sales_id
			WHERE s.sales_status='Final' AND s.store_id='2'
			AND s.sales_date >= '2026-08-05' AND s.sales_date <= '2026-09-04'
			GROUP BY si.item_id) sold ON `sold`.`item_id` = `i`.`id`
WHERE `i`.`store_id` = '2'
AND `i`.`service_bit` = 0
AND `i`.`status` = 1
AND `i`.`category_id` = '83'
HAVING (COALESCE(sold.qty,0) + i.stock) >0
ORDER BY `qty_sold` DESC
ERROR - 2026-09-04 00:04:41 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Reports_model.php 4617
ERROR - 2026-09-04 00:07:44 --> Severity: error --> Exception: Call to a member function permissions() on null /Users/ralphmore/Herd/martpointretailapp/application/views/pur-invoice.php 57
ERROR - 2026-09-04 00:09:04 --> Severity: error --> Exception: Call to a member function permissions() on null /Users/ralphmore/Herd/martpointretailapp/application/views/pur-invoice.php 57
ERROR - 2026-09-04 00:10:29 --> Could not find the language line "this_is_a_computer_generated_invoice"
ERROR - 2026-09-04 00:10:33 --> Could not find the language line "cashier"
ERROR - 2026-09-04 00:10:58 --> Severity: error --> Exception: syntax error, unexpected '<' /Users/ralphmore/Herd/martpointretailapp/application/views/pur-invoice.php 234
ERROR - 2026-09-04 00:11:13 --> Severity: error --> Exception: syntax error, unexpected '/' /Users/ralphmore/Herd/martpointretailapp/application/views/pur-invoice.php 234
ERROR - 2026-09-04 00:11:15 --> Severity: error --> Exception: syntax error, unexpected '/' /Users/ralphmore/Herd/martpointretailapp/application/views/pur-invoice.php 234
ERROR - 2026-09-04 00:40:45 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 26
ERROR - 2026-09-04 00:40:45 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 26
ERROR - 2026-09-04 00:40:45 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 67
ERROR - 2026-09-04 00:40:45 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 67
ERROR - 2026-09-04 00:41:26 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 34
ERROR - 2026-09-04 00:41:26 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 34
ERROR - 2026-09-04 00:41:51 --> Could not find the language line "quotation_details"
ERROR - 2026-09-04 00:42:03 --> Could not find the language line "this_is_a_computer_generated_quotation"
ERROR - 2026-09-04 00:42:03 --> Could not find the language line "thank_you_for_your_business"
ERROR - 2026-09-04 00:42:20 --> Could not find the language line "quotation_details"
ERROR - 2026-09-04 00:58:30 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3028' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 00:58:30 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2012
ERROR - 2026-09-04 01:01:15 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3028' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 01:01:15 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2012
ERROR - 2026-09-04 01:01:21 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3028' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 01:01:21 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2012
ERROR - 2026-09-04 01:38:23 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 01:44:11 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-04 01:45:03 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 06:17:18 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:17:21 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:17:33 --> 404 Page Not Found: Uploads/site
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 4
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 4
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 12
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 12
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(footer.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 224
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(): Failed opening 'footer.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 224
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 226
ERROR - 2026-09-04 07:19:39 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 226
ERROR - 2026-09-04 06:19:40 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 06:20:54 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-04 06:34:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 06:35:02 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:35:03 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:35:08 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:35:08 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:39:10 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-04 06:40:23 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:42:33 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:42:48 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 06:44:21 --> 404 Page Not Found: Uploads/site
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(comman/code_css.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 4
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(): Failed opening 'comman/code_css.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 4
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(sidebar.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 12
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(): Failed opening 'sidebar.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 12
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(footer.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 224
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(): Failed opening 'footer.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 224
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(comman/code_js.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 226
ERROR - 2026-09-04 07:44:31 --> Severity: Warning --> include(): Failed opening 'comman/code_js.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/migrate/index.php 226
ERROR - 2026-09-04 06:44:31 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 07:02:22 --> 404 Page Not Found: Templates/index
ERROR - 2026-09-04 07:02:22 --> 404 Page Not Found: Email_templates/index
ERROR - 2026-09-04 07:16:54 --> 404 Page Not Found: Site-settings/index
ERROR - 2026-09-04 07:16:55 --> 404 Page Not Found: Change-pass/index
ERROR - 2026-09-04 07:51:39 --> 404 Page Not Found: Theme/images
ERROR - 2026-09-04 08:53:18 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-04 08:20:48 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 09:32:11 --> Could not find the language line "inventory"
ERROR - 2026-09-04 09:32:13 --> Could not find the language line "search_items"
ERROR - 2026-09-04 09:32:17 --> Could not find the language line "search_items"
ERROR - 2026-09-04 09:34:27 --> Could not find the language line "inventory"
ERROR - 2026-09-04 08:39:43 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 08:52:12 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 08:52:17 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 09:52:18 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 09:52:18 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 09:53:15 --> Sales db_salesitems OK: id=701 sales_id=6167 item_id=3050 qty=1
ERROR - 2026-09-04 09:53:15 --> sales_note not saved: sales_note=, customer_id=3028, is_walkin=no
ERROR - 2026-09-04 09:53:18 --> Could not find the language line "cashier"
ERROR - 2026-09-04 08:53:18 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-04 09:53:19 --> Could not find the language line "cashier"
ERROR - 2026-09-04 08:53:20 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-04 08:53:40 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 09:56:56 --> Could not find the language line "search_items"
ERROR - 2026-09-04 09:57:03 --> Could not find the language line "search_items"
ERROR - 2026-09-04 09:57:12 --> Query error: Expression #6 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.b.available_qty' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `a`.`service_bit`, `a`.`purchase_price`, `a`.`id`, `a`.`item_name`, `a`.`item_code`, COALESCE(b.available_qty, 0) as stock, `item_group`
FROM `db_items` as `a`
LEFT JOIN `db_warehouseitems` as `b` ON `b`.`item_id`=`a`.`id`
WHERE `a`.`service_bit` =0
AND `b`.`warehouse_id` IS NULL
AND `a`.`status` = 1
AND `a`.`store_id` = '2'
AND (LOWER(a.custom_barcode) LIKE '%kl%' or LOWER(a.item_name) LIKE '%kl%' or LOWER(a.item_code) LIKE '%kl%')
GROUP BY `a`.`id`
 LIMIT 20
ERROR - 2026-09-04 09:58:19 --> Could not find the language line "cashier"
ERROR - 2026-09-04 08:58:19 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-04 09:58:25 --> Could not find the language line "this_is_a_computer_generated_invoice"
ERROR - 2026-09-04 09:43:00 --> 404 Page Not Found: Login/authenticate
ERROR - 2026-09-04 09:46:37 --> 404 Page Not Found: Business_profile/favicon.ico
ERROR - 2026-09-04 09:51:25 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 09:51:40 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 09:53:14 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:54:17 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/public_catalogue_settings.php 30
ERROR - 2026-09-04 10:54:17 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/public_catalogue_settings.php 30
ERROR - 2026-09-04 09:54:28 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 11:22:14 --> Could not find the language line "search_items"
ERROR - 2026-09-04 10:22:51 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 10:29:29 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:29:34 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 11:29:35 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3027' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:29:35 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 10:33:14 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:33:16 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 11:33:17 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3027' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:33:17 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 10:33:47 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:34:03 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-04 10:34:07 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:34:19 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:34:50 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:36:07 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:37:27 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:37:45 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:37:45 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:38:21 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:38:22 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:38:39 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:38:42 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:38:42 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 10:39:35 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 11:39:36 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:39:36 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:39:36 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:39:36 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 10:40:03 --> 404 Page Not Found: Sales/undefinedcustomers
ERROR - 2026-09-04 11:42:59 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:42:59 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:43:02 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:43:02 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:43:02 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_amount) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:43:02 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:47:49 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:47:49 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:47:52 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3027' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:47:52 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:47:52 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3027' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:47:52 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:52:57 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:52:57 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 10:57:09 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 10:58:07 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:58:07 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:58:07 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 10:58:17 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:58:17 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '2' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:58:17 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:58:29 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:58:29 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:58:29 --> Query error: Unknown column 'si.qty' in 'field list' - Invalid query: 
			SELECT i.item_name, SUM(si.qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = '3026' AND s.store_id = '2' AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		
ERROR - 2026-09-04 11:58:29 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_model.php 2013
ERROR - 2026-09-04 11:02:03 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:03:10 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 33
ERROR - 2026-09-04 12:03:10 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 33
ERROR - 2026-09-04 12:03:17 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:03:17 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:03:18 --> Query error: Table 'martpoint.db_brand' doesn't exist - Invalid query: SELECT `a`.`id`, `a`.`item_name`, `a`.`item_code`, `a`.`sales_price`, `a`.`online_price`, `a`.`discount_type`, `a`.`discount`, `a`.`stock`, `a`.`status`, `b`.`category_name`, `c`.`brand_name`
FROM `db_items` `a`
LEFT JOIN `db_category` `b` ON `b`.`id` = `a`.`category_id`
LEFT JOIN `db_brand` `c` ON `c`.`id` = `a`.`brand_id`
WHERE `a`.`store_id` = '2'
AND `a`.`status` = 1
ORDER BY `a`.`item_name` ASC
ERROR - 2026-09-04 12:03:18 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Operations.php 1596
ERROR - 2026-09-04 11:03:25 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:03:49 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:03:49 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:03:54 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 11:03:55 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:04:40 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:05:46 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:05:46 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 11:05:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:06:01 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 33
ERROR - 2026-09-04 12:06:01 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/attributes/attributes-list.php 33
ERROR - 2026-09-04 12:06:04 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:06:04 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 11:06:26 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:07:14 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/import/import_items.php 38
ERROR - 2026-09-04 12:07:14 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/import/import_items.php 38
ERROR - 2026-09-04 11:07:15 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:07:17 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:07:17 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 11:07:18 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:07:23 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 12:07:23 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 11:07:24 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:07:30 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:08:55 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 12:08:55 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/operations/price_catalogue.php 46
ERROR - 2026-09-04 11:08:55 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:08:59 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:09:06 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:11:12 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 12:11:12 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 11:11:27 --> 404 Page Not Found: Purchase/undefinedsuppliers
ERROR - 2026-09-04 12:13:39 --> Sales db_salesitems OK: id=702 sales_id=6168 item_id=3050 qty=1
ERROR - 2026-09-04 12:13:39 --> sales_note not saved: sales_note=, customer_id=3028, is_walkin=no
ERROR - 2026-09-04 11:20:32 --> 404 Page Not Found: Purchase/undefinedsuppliers
ERROR - 2026-09-04 12:23:52 --> Severity: Warning --> include(comman/code_flashdata.php): failed to open stream: No such file or directory /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 12:23:52 --> Severity: Warning --> include(): Failed opening 'comman/code_flashdata.php' for inclusion (include_path='.:') /Users/ralphmore/Herd/martpointretailapp/application/views/promotions/promotions_list.php 25
ERROR - 2026-09-04 11:24:41 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:25:46 --> Could not find the language line "quotation_details"
ERROR - 2026-09-04 12:26:01 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:26:40 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:28:21 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:28:53 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:32:24 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:32:34 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:32:34 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:32:34 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 11:32:41 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 11:33:08 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-04 12:33:31 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:35:41 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:36:11 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-04 12:36:37 --> Could not find the language line "inventory"
ERROR - 2026-09-04 12:39:57 --> Sales db_salesitems OK: id=703 sales_id=6169 item_id=3048 qty=1
ERROR - 2026-09-04 12:39:57 --> sales_note not saved: sales_note=, customer_id=3027, is_walkin=no
ERROR - 2026-09-04 12:41:41 --> Could not find the language line "quotation_details"
ERROR - 2026-09-04 12:52:15 --> Could not find the language line "quotation_details"
