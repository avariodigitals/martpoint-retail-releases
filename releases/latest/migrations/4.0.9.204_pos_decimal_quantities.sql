-- 4.0.9.204 — POS quantity precision.
-- Widening scale preserves existing values while allowing stores configured
-- for up to four quantity decimals to keep the entered amount through stock
-- movements. Statements are safe to re-run if a migration is resumed.
ALTER TABLE `db_items` MODIFY `stock` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_holditems` MODIFY `sales_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_salesitems` MODIFY `sales_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_purchaseitems` MODIFY `purchase_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_purchaseitemsreturn` MODIFY `return_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_salesitemsreturn` MODIFY `return_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_stockadjustmentitems` MODIFY `adjustment_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_stocktransferitems` MODIFY `transfer_qty` DOUBLE(20,4) DEFAULT NULL;
ALTER TABLE `db_warehouseitems` MODIFY `available_qty` DOUBLE(20,4) DEFAULT NULL;
