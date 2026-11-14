-- ============================================================================
-- MartPoint 4.0.9.150 — Repair round_off so every invoice breakdown adds up
--
-- The manual-shipping (delivery fee) feature added a "Shipping" line to the
-- printed invoice and correctly folded the fee into grand_total. Two things
-- still left the printed breakdown unable to reconcile with the total:
--
--   1. The classic POS (Pos_model::pos_save_update and hold_list_save_update)
--      stored round_off = grand_total - subtotal. But subtotal there is the
--      PRE-discount item total, so that difference swallowed the discount, the
--      coupon AND the delivery fee into the "Round Off" line.
--   2. The fee is charged correctly but sits outside the item lines, so any
--      breakdown that omitted it looked like the total had been inflated
--      (receipt 22,500 vs items 17,500).
--
-- The invoices print this breakdown, so round_off is whatever is left over:
--
--   Subtotal + Other Charges - Coupon - Discount + Tax + Shipping + Round Off
--     = Grand Total
--
-- MONEY-NEUTRAL BY DESIGN. Only db_sales.round_off is rewritten. grand_total,
-- paid_amount, subtotal and shipping_fee are never touched, so no takings
-- figure, no report total and no customer balance moves.
--
-- Rows that already reconcile to within 1 kobo are left exactly as they are,
-- so re-running this migration is a no-op.
--
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

UPDATE `db_sales` s
SET s.`round_off` = ROUND(
        s.`grand_total`
      - COALESCE(s.`subtotal`, 0)
      - COALESCE(s.`other_charges_amt`, 0)
      + COALESCE(s.`coupon_amt`, 0)
      + COALESCE(s.`tot_discount_to_all_amt`, 0)
      - COALESCE(s.`shipping_fee`, 0)
      - (SELECT COALESCE(SUM(si.`tax_amt`), 0) FROM `db_salesitems` si WHERE si.`sales_id` = s.`id`)
    , 2)
WHERE s.`grand_total` IS NOT NULL
  AND ABS(COALESCE(s.`round_off`, 0) - (
        s.`grand_total`
      - COALESCE(s.`subtotal`, 0)
      - COALESCE(s.`other_charges_amt`, 0)
      + COALESCE(s.`coupon_amt`, 0)
      + COALESCE(s.`tot_discount_to_all_amt`, 0)
      - COALESCE(s.`shipping_fee`, 0)
      - (SELECT COALESCE(SUM(si.`tax_amt`), 0) FROM `db_salesitems` si WHERE si.`sales_id` = s.`id`)
    )) > 0.01;
