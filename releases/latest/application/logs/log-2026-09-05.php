<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-05 09:49:02 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-05 09:49:02 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-05 09:51:26 --> Updater httpGet failed for https://raw.githubusercontent.com/avariodigitals/martpoint-retail-releases/main/releases/latest/release-manifest.json: Module 'herd' already loaded
ERROR - 2026-09-05 09:51:26 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-05 09:51:26 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-05 12:04:19 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-05 12:04:35 --> Query error: Unknown column 'nin_provider' in 'field list' - Invalid query: UPDATE `db_store` SET `nin_api_url` = '', `nin_api_key` = '', `nin_api_provider` = NULL, `nin_api_cost` = '50', `nin_provider` = 'ninbvnportal', `bvn_provider` = 'ninbvnportal', `interswitch_client_id` = '', `interswitch_client_secret` = '', `nin_api_enabled` = 0
WHERE `id` = '2'
ERROR - 2026-09-05 12:04:45 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:04:45 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6169'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:04:45 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:04:45 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:05:03 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:05:03 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6169'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:05:03 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:05:03 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:05:16 --> Could not find the language line "this_is_a_computer_generated_invoice"
ERROR - 2026-09-05 11:05:16 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-05 12:05:43 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:05:43 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6168'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:05:43 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:05:43 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:06:22 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:06:22 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6168'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:06:22 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:06:23 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:06:57 --> Query error: Unknown column 'nin_provider' in 'field list' - Invalid query: UPDATE `db_store` SET `nin_api_url` = '', `nin_api_key` = '', `nin_api_provider` = NULL, `nin_api_cost` = '50', `nin_provider` = 'ninbvnportal', `bvn_provider` = 'ninbvnportal', `interswitch_client_id` = '', `interswitch_client_secret` = '', `nin_api_enabled` = 0
WHERE `id` = '2'
ERROR - 2026-09-05 12:07:12 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:07:12 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6169'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:07:12 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:07:12 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:07:54 --> Could not find the language line "this_is_a_computer_generated_invoice"
ERROR - 2026-09-05 12:08:19 --> Could not find the language line "cashier"
ERROR - 2026-09-05 12:08:19 --> Query error: Expression #4 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.c.tax_type' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `b`.`tax`, `b`.`tax_name`, COALESCE(SUM(a.tax_amt), 0) AS sum_of_tax_amt, `c`.`tax_type`
FROM `db_salesitems` `a`
LEFT JOIN `db_tax` `b` ON `b`.`id`=`a`.`tax_id`
LEFT JOIN `db_items` `c` ON `c`.`id`=`a`.`item_id`
WHERE `a`.`sales_id` = '6169'
GROUP BY `a`.`tax_id`
ERROR - 2026-09-05 12:08:19 --> Severity: error --> Exception: Call to a member function num_rows() on bool /Users/ralphmore/Herd/martpointretailapp/application/views/sal-invoice-pos.php 292
ERROR - 2026-09-05 11:08:19 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:11:22 --> Query error: Unknown column 'nin_provider' in 'field list' - Invalid query: UPDATE `db_store` SET `nin_api_url` = '', `nin_api_key` = '', `nin_api_provider` = NULL, `nin_api_cost` = '50', `nin_provider` = 'ninbvnportal', `bvn_provider` = 'ninbvnportal', `interswitch_client_id` = '', `interswitch_client_secret` = '', `nin_api_enabled` = 0
WHERE `id` = '2'
ERROR - 2026-09-05 12:11:33 --> Could not find the language line "cashier"
ERROR - 2026-09-05 11:11:33 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 11:48:44 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 13:06:05 --> Query error: Unknown column 'a.unit_name' in 'field list' - Invalid query: 
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.category_id, a.brand_id, a.sku, a.unit_name AS unit,
			       COALESCE(a.alert_qty, 0) AS alert_qty,
			       a.tax_id, a.tax_type,
			       IF(a.tax_id > 0, 1, 0) AS tax,
			       a.item_image AS image,
			       b.tax AS tax_value
			FROM db_items a
			LEFT JOIN db_tax b ON b.id = a.tax_id
			LEFT JOIN db_category c ON c.id = a.category_id
			WHERE a.store_id = '2'
			  AND a.status = 1
			  AND a.service_bit != 1
			  AND (
			    (a.parent_id IS NULL AND NOT EXISTS (SELECT 1 FROM db_items WHERE parent_id = a.id))
			    OR
			    (a.parent_id IS NOT NULL)
			  )
			ORDER BY a.item_name
			LIMIT 50
		
ERROR - 2026-09-05 13:06:05 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 112
ERROR - 2026-09-05 13:06:09 --> Query error: Unknown column 'a.unit_name' in 'field list' - Invalid query: 
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.category_id, a.brand_id, a.sku, a.unit_name AS unit,
			       COALESCE(a.alert_qty, 0) AS alert_qty,
			       a.tax_id, a.tax_type,
			       IF(a.tax_id > 0, 1, 0) AS tax,
			       a.item_image AS image,
			       b.tax AS tax_value
			FROM db_items a
			LEFT JOIN db_tax b ON b.id = a.tax_id
			LEFT JOIN db_category c ON c.id = a.category_id
			WHERE a.store_id = '2'
			  AND a.status = 1
			  AND a.service_bit != 1
			  AND (
			    (a.parent_id IS NULL AND NOT EXISTS (SELECT 1 FROM db_items WHERE parent_id = a.id))
			    OR
			    (a.parent_id IS NOT NULL)
			  )
			ORDER BY a.item_name
			LIMIT 50
		
ERROR - 2026-09-05 13:06:09 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 112
ERROR - 2026-09-05 13:07:01 --> Query error: Unknown column 'a.unit_name' in 'field list' - Invalid query: 
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.category_id, a.brand_id, a.sku, a.unit_name AS unit,
			       COALESCE(a.alert_qty, 0) AS alert_qty,
			       a.tax_id, a.tax_type,
			       IF(a.tax_id > 0, 1, 0) AS tax,
			       a.item_image AS image,
			       b.tax AS tax_value
			FROM db_items a
			LEFT JOIN db_tax b ON b.id = a.tax_id
			LEFT JOIN db_category c ON c.id = a.category_id
			WHERE a.store_id = '2'
			  AND a.status = 1
			  AND a.service_bit != 1
			  AND (
			    (a.parent_id IS NULL AND NOT EXISTS (SELECT 1 FROM db_items WHERE parent_id = a.id))
			    OR
			    (a.parent_id IS NOT NULL)
			  )
			ORDER BY a.item_name
			LIMIT 50
		
ERROR - 2026-09-05 13:07:01 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 112
ERROR - 2026-09-05 12:10:52 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-05 12:15:24 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:16:31 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 13:16:53 --> Query error: Unknown column 'nin_provider' in 'field list' - Invalid query: UPDATE `db_store` SET `nin_api_url` = '', `nin_api_key` = '', `nin_api_provider` = NULL, `nin_api_cost` = '50', `nin_provider` = 'ninbvnportal', `bvn_provider` = 'ninbvnportal', `interswitch_client_id` = '', `interswitch_client_secret` = '', `nin_api_enabled` = 0
WHERE `id` = '2'
ERROR - 2026-09-05 12:17:02 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 13:18:04 --> Sales db_salesitems OK: id=704 sales_id=6170 item_id=3036 qty=1
ERROR - 2026-09-05 13:18:04 --> sales_note not saved: sales_note=, customer_id=2, is_walkin=yes
ERROR - 2026-09-05 12:18:05 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:18:13 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 13:23:53 --> Sales db_salesitems OK: id=705 sales_id=6171 item_id=3042 qty=1
ERROR - 2026-09-05 13:23:53 --> sales_note not saved: sales_note=, customer_id=2, is_walkin=yes
ERROR - 2026-09-05 12:23:53 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:23:55 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 13:27:13 --> Sales db_salesitems OK: id=706 sales_id=6172 item_id=3041 qty=3
ERROR - 2026-09-05 13:27:13 --> sales_note not saved: sales_note=, customer_id=2, is_walkin=yes
ERROR - 2026-09-05 12:27:14 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:27:15 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-05 12:29:23 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-05 12:52:22 --> 404 Page Not Found: Faviconico/index
