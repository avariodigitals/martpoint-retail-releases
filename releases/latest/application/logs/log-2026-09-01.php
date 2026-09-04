<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-01 00:50:54 --> Severity: error --> Exception: Call to undefined function getArrayOfBranchIds() /Users/ralphmore/Herd/martpointretailapp/application/views/warehouse/warehouse_code.php 43
ERROR - 2026-09-01 00:50:54 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-01 00:53:09 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-01 00:57:20 --> Could not find the language line "generatecustomerCoupon"
ERROR - 2026-09-01 00:58:27 --> Severity: error --> Exception: Call to undefined function getArrayOfBranchIds() /Users/ralphmore/Herd/martpointretailapp/application/views/warehouse/warehouse_code.php 43
ERROR - 2026-09-01 00:08:14 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 01:09:57 --> Severity: error --> Exception: Call to undefined method Attendance::reseed_role_permissions() /Users/ralphmore/Herd/martpointretailapp/application/core/MY_Controller.php 154
ERROR - 2026-09-01 07:44:44 --> Query error: Unknown column 'a.created_at' in 'field list' - Invalid query: 
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.tax_id, a.tax_type, a.created_at AS createdAt,
			       IF(a.tax_id > 0, 1, 0) AS tax
			FROM db_items a
			LEFT JOIN db_category c ON c.id = a.category_id
			WHERE a.store_id = '2'
			  AND a.status = 1
			  AND (a.not_for_sale IS NULL OR a.not_for_sale = 0)
			  AND a.service_bit != 1
			ORDER BY a.item_name
			LIMIT 50
		
ERROR - 2026-09-01 07:44:44 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 96
ERROR - 2026-09-01 07:46:29 --> Query error: Unknown column 'a.created_at' in 'field list' - Invalid query: 
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.tax_id, a.tax_type, a.created_at AS createdAt,
			       IF(a.tax_id > 0, 1, 0) AS tax
			FROM db_items a
			LEFT JOIN db_category c ON c.id = a.category_id
			WHERE a.store_id = '2'
			  AND a.status = 1
			  AND (a.not_for_sale IS NULL OR a.not_for_sale = 0)
			  AND a.service_bit != 1
			ORDER BY a.item_name
			LIMIT 50
		
ERROR - 2026-09-01 07:46:29 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 96
ERROR - 2026-09-01 06:48:02 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:07:14 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:08:39 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:10:29 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:14:58 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:15:12 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:22:25 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:24:24 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:26:07 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 07:26:34 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 08:11:54 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 09:17:48 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 09:17:48 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 09:18:24 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 09:18:24 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 09:18:56 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 09:18:56 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 09:19:29 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 09:19:29 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:22 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:22 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:24 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:24 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:38 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:38 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:39 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:39 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:39 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:39 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 10:10:39 --> Query error: Unknown column 'card_code' in 'field list' - Invalid query: SELECT `id`, `customer_id`, `card_code` as `code`, `balance`
FROM `db_gift_cards`
WHERE `store_id` = '2'
AND `customer_id` IN (3028,3026,3027,2)
AND `balance` > 0
ERROR - 2026-09-01 10:10:39 --> Severity: error --> Exception: Call to a member function result_array() on bool /Users/ralphmore/Herd/martpointretailapp/application/controllers/Pos.php 434
ERROR - 2026-09-01 11:05:34 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 15:08:05 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 15:23:36 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 15:32:55 --> 404 Page Not Found: Services/index
ERROR - 2026-09-01 15:32:55 --> 404 Page Not Found: About/index
ERROR - 2026-09-01 15:32:55 --> 404 Page Not Found: Contact/index
ERROR - 2026-09-01 15:32:56 --> 404 Page Not Found: Tailwindcss/index
ERROR - 2026-09-01 15:32:56 --> 404 Page Not Found: Nonexistent/index
ERROR - 2026-09-01 15:33:02 --> 404 Page Not Found: Services/index
ERROR - 2026-09-01 22:43:12 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 23:59:17 --> Sales db_salesitems OK: id=651 sales_id=6127 item_id=3038 qty=1
ERROR - 2026-09-01 23:59:17 --> Sales db_salesitems OK: id=652 sales_id=6127 item_id=3037 qty=1
ERROR - 2026-09-01 23:59:17 --> sales_note not saved: sales_note=, customer_id=2, is_walkin=yes
ERROR - 2026-09-01 23:02:11 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 23:03:17 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-01 23:26:17 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-01 23:47:48 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-09-01 23:59:08 --> 404 Page Not Found: Uploads/store
ERROR - 2026-09-01 23:59:36 --> 404 Page Not Found: Faviconico/index
