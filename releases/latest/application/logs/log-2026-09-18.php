<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-09-18 02:47:47 --> Could not find the language line "this_is_a_computer_generated_invoice"
ERROR - 2026-09-18 17:43:42 --> Severity: Core Warning --> Module 'herd' already loaded Unknown 0
ERROR - 2026-09-18 17:50:47 --> Severity: error --> Exception: Call to undefined method CI_Loader::currency() /Users/ralphmore/Herd/martpointretailapp/application/views/perfume/dashboard.php 34
ERROR - 2026-09-18 18:04:03 --> Query error: Duplicate entry '' for key 'db_item_barcodes.uk_serial_number' - Invalid query: INSERT INTO `db_item_barcodes` (`item_id`, `barcode`, `batch_lot`, `serial_number`, `imei_number`, `purchase_price`, `sales_price`, `mrp`, `qty`, `warehouse_id`, `status`, `created_date`, `created_time`) VALUES (3081, '', '0903', '', '', '85.00', '85.00', '85.00', '5000.00', '2', 1, '2026-09-18', '18:04:03')
ERROR - 2026-09-18 18:09:09 --> Query error: Duplicate entry '' for key 'db_item_barcodes.uk_serial_number' - Invalid query: INSERT INTO `db_item_barcodes` (`item_id`, `barcode`, `batch_lot`, `serial_number`, `imei_number`, `purchase_price`, `sales_price`, `mrp`, `qty`, `warehouse_id`, `status`, `created_date`, `created_time`, `expire_date`) VALUES (3083, '', '08984', '', '', '3550.00', '3550.00', '3500.00', '20000.00', '2', 1, '2026-09-18', '18:09:09', '2026-09-18')
ERROR - 2026-09-18 18:09:40 --> Query error: Duplicate entry '' for key 'db_item_barcodes.uk_serial_number' - Invalid query: INSERT INTO `db_item_barcodes` (`item_id`, `barcode`, `batch_lot`, `serial_number`, `imei_number`, `purchase_price`, `sales_price`, `mrp`, `qty`, `warehouse_id`, `status`, `created_date`, `created_time`, `expire_date`) VALUES (3084, '', '08984', '', '', '3550.00', '3550.00', '3500.00', '20000.00', '2', 1, '2026-09-18', '18:09:40', '2026-09-18')
ERROR - 2026-09-18 18:10:01 --> Query error: Duplicate entry '' for key 'db_item_barcodes.uk_serial_number' - Invalid query: INSERT INTO `db_item_barcodes` (`item_id`, `barcode`, `batch_lot`, `serial_number`, `imei_number`, `purchase_price`, `sales_price`, `mrp`, `qty`, `warehouse_id`, `status`, `created_date`, `created_time`, `expire_date`) VALUES (3085, 'tepee', '08984', '', '', '3550.00', '3550.00', '3500.00', '20000.00', '2', 1, '2026-09-18', '18:10:01', '2026-09-18')
ERROR - 2026-09-18 19:19:51 --> Query error: Expression #2 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.db_perfume_wastage.item_name' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `item_id`, `item_name`, SUM(qty) AS qty_lost, SUM(total_cost) AS cost, COUNT(*) AS events
FROM `db_perfume_wastage`
WHERE `store_id` = '2'
AND `status` = 1
GROUP BY `item_id`
ORDER BY `cost` DESC
 LIMIT 8
ERROR - 2026-09-18 19:19:51 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Perfume_model.php 341
ERROR - 2026-09-18 19:19:53 --> Query error: Expression #2 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.db_perfume_wastage.item_name' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `item_id`, `item_name`, SUM(qty) AS qty_lost, SUM(total_cost) AS cost, COUNT(*) AS events
FROM `db_perfume_wastage`
WHERE `store_id` = '2'
AND `status` = 1
GROUP BY `item_id`
ORDER BY `cost` DESC
 LIMIT 8
ERROR - 2026-09-18 19:19:53 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Perfume_model.php 341
ERROR - 2026-09-18 19:20:35 --> Query error: Expression #2 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'martpoint.db_perfume_wastage.item_name' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by - Invalid query: SELECT `item_id`, `item_name`, SUM(qty) AS qty_lost, SUM(total_cost) AS cost, COUNT(*) AS events
FROM `db_perfume_wastage`
WHERE `store_id` = '2'
AND `status` = 1
GROUP BY `item_id`
ORDER BY `cost` DESC
 LIMIT 8
ERROR - 2026-09-18 19:20:35 --> Severity: error --> Exception: Call to a member function result() on bool /Users/ralphmore/Herd/martpointretailapp/application/models/Perfume_model.php 341
