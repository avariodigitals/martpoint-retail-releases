-- Add staff commission tracking to held sale items (matches db_salesitems)
ALTER TABLE `db_holditems`
  ADD COLUMN IF NOT EXISTS `staff_id` int(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `commission_amount` double(20,2) DEFAULT 0.00;
