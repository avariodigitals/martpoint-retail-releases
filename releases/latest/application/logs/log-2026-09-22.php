<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-22 04:42:57 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-22 04:42:57 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-09-22 05:43:15 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-22 05:56:33 --> Query error: Unknown column 'sold_serial_number' in 'field list' - Invalid query: INSERT INTO `db_salesreturn` (`sales_id`, `count_id`, `return_code`, `reference_no`, `return_date`, `return_status`, `customer_id`, `other_charges_input`, `other_charges_tax_id`, `other_charges_amt`, `discount_to_all_input`, `discount_to_all_type`, `tot_discount_to_all_amt`, `subtotal`, `round_off`, `grand_total`, `return_note`, `created_date`, `created_time`, `created_by`, `system_ip`, `system_name`, `status`, `sold_serial_number`, `sold_imei_number`, `barcode_id`, `store_id`, `warehouse_id`, `coupon_id`, `coupon_amt`) VALUES ('6177', '1', 'SR0001', '', '2026-09-22', 'Return', '2', NULL, NULL, NULL, NULL, 'in_fixed', NULL, '1395.35', NULL, '1500.00', 'Back to Stock', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2', '2', NULL, '0')
ERROR - 2026-09-22 05:56:33 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`martpoint`.`db_salesitemsreturn`, CONSTRAINT `db_salesitemsreturn_ibfk_2` FOREIGN KEY (`return_id`) REFERENCES `db_salesreturn` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) - Invalid query: INSERT INTO `db_salesitemsreturn` (`sales_id`, `return_id`, `return_status`, `item_id`, `description`, `return_qty`, `base_unit_qty`, `price_per_unit`, `tax_id`, `tax_amt`, `tax_type`, `discount_input`, `discount_amt`, `discount_type`, `unit_total_cost`, `total_cost`, `purchase_price`, `status`, `sold_serial_number`, `sold_imei_number`, `barcode_id`, `store_id`) VALUES ('6177', 0, 'Return', '3068', '', '1', 1, '1500', '149', '104.65', 'Inclusive', NULL, '0.00', 'Percentage', 1500, '1500.00', '1050.0000', 1, '', '', 0, '2')
ERROR - 2026-09-22 05:56:33 --> Query error: Unknown column 'sold_serial_number' in 'field list' - Invalid query: INSERT INTO `db_salespaymentsreturn` (`payment_code`, `count_id`, `sales_id`, `return_id`, `payment_date`, `payment_type`, `payment`, `payment_note`, `created_date`, `created_time`, `created_by`, `system_ip`, `system_name`, `status`, `sold_serial_number`, `sold_imei_number`, `account_id`, `customer_id`, `store_id`) VALUES ('SRP0001', '1', '6177', 0, '2026-09-22', 'CASH', '1500.00', '', NULL, NULL, NULL, NULL, NULL, 1, '', '', '35', '2', '2')
ERROR - 2026-09-22 05:56:33 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`martpoint`.`ac_transactions`, CONSTRAINT `ac_transactions_ibfk_9` FOREIGN KEY (`ref_salespaymentsreturn_id`) REFERENCES `db_salespaymentsreturn` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) - Invalid query: INSERT INTO `ac_transactions` (`transaction_type`, `ref_salespaymentsreturn_id`, `debit_account_id`, `debit_amt`, `store_id`, `created_by`, `created_date`, `transaction_date`, `note`, `payment_code`, `customer_id`, `supplier_id`) VALUES ('SALES PAYMENT RETURN', 0, '35', '1500.00', '2', 'storeadm', '2026-09-22', NULL, '', 'SRP0001', '2', NULL)
ERROR - 2026-09-22 05:59:11 --> Query error: Unknown column 'sold_serial_number' in 'field list' - Invalid query: INSERT INTO `db_salesreturn` (`sales_id`, `count_id`, `return_code`, `reference_no`, `return_date`, `return_status`, `customer_id`, `other_charges_input`, `other_charges_tax_id`, `other_charges_amt`, `discount_to_all_input`, `discount_to_all_type`, `tot_discount_to_all_amt`, `subtotal`, `round_off`, `grand_total`, `return_note`, `created_date`, `created_time`, `created_by`, `system_ip`, `system_name`, `status`, `sold_serial_number`, `sold_imei_number`, `barcode_id`, `store_id`, `warehouse_id`, `coupon_id`, `coupon_amt`) VALUES ('6176', '1', 'SR0001', '', '2026-09-22', 'Return', '2', NULL, NULL, NULL, NULL, 'in_fixed', NULL, '1395.35', NULL, '1500.00', 'Return', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2', '2', NULL, '0')
ERROR - 2026-09-22 05:59:11 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`martpoint`.`db_salesitemsreturn`, CONSTRAINT `db_salesitemsreturn_ibfk_2` FOREIGN KEY (`return_id`) REFERENCES `db_salesreturn` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) - Invalid query: INSERT INTO `db_salesitemsreturn` (`sales_id`, `return_id`, `return_status`, `item_id`, `description`, `return_qty`, `base_unit_qty`, `price_per_unit`, `tax_id`, `tax_amt`, `tax_type`, `discount_input`, `discount_amt`, `discount_type`, `unit_total_cost`, `total_cost`, `purchase_price`, `status`, `sold_serial_number`, `sold_imei_number`, `barcode_id`, `store_id`) VALUES ('6176', 0, 'Return', '3068', '', '1', 1, '1500', '149', '104.65', 'Inclusive', NULL, '0.00', 'Percentage', 1500, '1500.00', '1050.0000', 1, '', '', 0, '2')
ERROR - 2026-09-22 05:59:11 --> Query error: Unknown column 'sold_serial_number' in 'field list' - Invalid query: INSERT INTO `db_salespaymentsreturn` (`payment_code`, `count_id`, `sales_id`, `return_id`, `payment_date`, `payment_type`, `payment`, `payment_note`, `created_date`, `created_time`, `created_by`, `system_ip`, `system_name`, `status`, `sold_serial_number`, `sold_imei_number`, `account_id`, `customer_id`, `store_id`) VALUES ('SRP0001', '1', '6176', 0, '2026-09-22', 'CASH', '1500.00', '', NULL, NULL, NULL, NULL, NULL, 1, '', '', '35', '2', '2')
ERROR - 2026-09-22 05:59:11 --> Query error: Cannot add or update a child row: a foreign key constraint fails (`martpoint`.`ac_transactions`, CONSTRAINT `ac_transactions_ibfk_9` FOREIGN KEY (`ref_salespaymentsreturn_id`) REFERENCES `db_salespaymentsreturn` (`id`) ON DELETE CASCADE ON UPDATE CASCADE) - Invalid query: INSERT INTO `ac_transactions` (`transaction_type`, `ref_salespaymentsreturn_id`, `debit_account_id`, `debit_amt`, `store_id`, `created_by`, `created_date`, `transaction_date`, `note`, `payment_code`, `customer_id`, `supplier_id`) VALUES ('SALES PAYMENT RETURN', 0, '35', '1500.00', '2', 'storeadm', '2026-09-22', NULL, '', 'SRP0001', '2', NULL)
ERROR - 2026-09-22 13:03:41 --> 404 Page Not Found: Sales_returns/index
ERROR - 2026-09-22 16:16:44 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-22 16:17:16 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-22 17:34:58 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:34:59 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-22 17:34:59 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_t' at line 4 - Invalid query: SELECT oi.item_id, oi.item_type, SUM(oi.qty) AS sold
			FROM db_online_order_items oi
			INNER JOIN db_online_orders o ON o.id = oi.order_id
			WHERE o.store_id =  AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_type
ERROR - 2026-09-22 17:34:59 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Storefront_model.php 723
ERROR - 2026-09-22 17:34:59 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-22 17:34:59 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_t' at line 4 - Invalid query: SELECT oi.item_id, oi.item_type, SUM(oi.qty) AS sold
			FROM db_online_order_items oi
			INNER JOIN db_online_orders o ON o.id = oi.order_id
			WHERE o.store_id =  AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_type
ERROR - 2026-09-22 17:34:59 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Storefront_model.php 723
ERROR - 2026-09-22 17:34:59 --> 404 Page Not Found: Store/mystore
ERROR - 2026-09-22 17:36:04 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-22 17:36:04 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_t' at line 4 - Invalid query: SELECT oi.item_id, oi.item_type, SUM(oi.qty) AS sold
			FROM db_online_order_items oi
			INNER JOIN db_online_orders o ON o.id = oi.order_id
			WHERE o.store_id =  AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_type
ERROR - 2026-09-22 17:36:04 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Storefront_model.php 723
ERROR - 2026-09-22 17:37:19 --> Query error: Column 'store_id' cannot be null - Invalid query: INSERT INTO `db_storefront_settings` (`theme_id`, `store_id`) VALUES ('1', NULL)
ERROR - 2026-09-22 17:37:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_t' at line 4 - Invalid query: SELECT oi.item_id, oi.item_type, SUM(oi.qty) AS sold
			FROM db_online_order_items oi
			INNER JOIN db_online_orders o ON o.id = oi.order_id
			WHERE o.store_id =  AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_type
ERROR - 2026-09-22 17:37:19 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Storefront_model.php 723
ERROR - 2026-09-22 17:37:19 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:37:20 --> 404 Page Not Found: Store/mystore
ERROR - 2026-09-22 17:37:20 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:44:44 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:44:44 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:44:44 --> 404 Page Not Found: 
ERROR - 2026-09-22 17:56:28 --> 404 Page Not Found: Mobile/online_store_products
ERROR - 2026-09-22 17:56:28 --> 404 Page Not Found: Mobile/online_store_settings
ERROR - 2026-09-22 17:56:28 --> 404 Page Not Found: Mobile/online_store_orders
ERROR - 2026-09-22 18:01:28 --> 404 Page Not Found: Store/mystore
ERROR - 2026-09-22 18:01:28 --> 404 Page Not Found: Store/mystore
ERROR - 2026-09-22 18:14:18 --> 404 Page Not Found: 
ERROR - 2026-09-22 23:31:26 --> Could not find the language line "sales_return_list"
ERROR - 2026-09-22 23:31:26 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_return_model.php 980
ERROR - 2026-09-22 23:49:43 --> Severity: Warning --> A non-numeric value encountered /Users/ralphmore/Herd/martpointretailapp/application/models/Sales_return_model.php 986
ERROR - 2026-09-22 23:45:17 --> CSRF check failed for POST /mobile/staff
