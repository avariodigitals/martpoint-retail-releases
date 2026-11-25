-- Repair customer-facing duplicate codes, old storefront Paystack overposts,
-- and the source label shown for online-store invoices.

-- Keep the oldest customer on its existing code. Give later records an auditable
-- suffix containing their immutable customer row id so every code is unique.
UPDATE db_customers c
JOIN db_customers earlier
  ON earlier.store_id = c.store_id
 AND earlier.customer_code = c.customer_code
 AND earlier.id < c.id
SET c.customer_code = CONCAT(LEFT(c.customer_code, 6), '-D', c.id)
WHERE c.customer_code IS NOT NULL
  AND c.customer_code <> '';

-- Older storefront code posted the full Paystack charge as customer payment.
-- For a single verified Paystack payment line that exceeds its online invoice,
-- cap only the sales ledger line at the invoice total. The gross gateway amount
-- remains intact in db_online_order_payments for settlement/reconciliation.
UPDATE db_salespayments sp
JOIN db_sales s
  ON s.id = sp.sales_id
 AND s.store_id = sp.store_id
JOIN db_online_order_payments op
  ON op.salespayment_id = sp.id
 AND op.store_id = s.store_id
 AND op.online_order_id = s.online_order_id
 AND op.provider = 'paystack'
 AND op.status = 'verified'
LEFT JOIN db_salespayments other
  ON other.sales_id = sp.sales_id
 AND other.store_id = sp.store_id
 AND other.id <> sp.id
 AND other.status = 1
 AND COALESCE(other.confirmation_status, 1) = 1
SET sp.payment = ROUND(s.grand_total, 2)
WHERE s.online_order_id IS NOT NULL
  AND sp.status = 1
  AND COALESCE(sp.confirmation_status, 1) = 1
  AND other.id IS NULL
  AND sp.payment > s.grand_total
  AND op.amount > s.grand_total;

-- Recalculate paid totals and payment status for storefront sales with linked
-- verified gateway lines after the cap above.
UPDATE db_sales s
JOIN db_online_order_payments op
  ON op.store_id = s.store_id
 AND op.online_order_id = s.online_order_id
 AND op.status = 'verified'
JOIN db_salespayments sp
  ON sp.id = op.salespayment_id
 AND sp.store_id = s.store_id
 AND sp.sales_id = s.id
JOIN (
  SELECT store_id, sales_id, SUM(payment) AS paid_total
  FROM db_salespayments
  WHERE status = 1
    AND COALESCE(confirmation_status, 1) = 1
  GROUP BY store_id, sales_id
) paid
  ON paid.store_id = s.store_id
 AND paid.sales_id = s.id
SET s.paid_amount = paid.paid_total,
    s.payment_status = CASE
      WHEN paid.paid_total >= s.grand_total THEN 'Paid'
      WHEN paid.paid_total > 0 THEN 'Partial'
      ELSE 'Unpaid'
    END
WHERE s.online_order_id IS NOT NULL;

-- Refresh the denormalized customer due for customers with linked online
-- payments, using the existing Final/Opening sale basis.
UPDATE db_customers c
JOIN (
  SELECT s.store_id, s.customer_id,
         SUM(s.grand_total) - SUM(COALESCE(s.paid_amount, 0)) AS sales_due
  FROM db_sales s
  JOIN (
    SELECT DISTINCT linked.store_id, linked.customer_id
    FROM db_sales linked
    JOIN db_online_order_payments op
      ON op.store_id = linked.store_id
     AND op.online_order_id = linked.online_order_id
     AND op.status = 'verified'
    JOIN db_salespayments sp
      ON sp.id = op.salespayment_id
     AND sp.store_id = linked.store_id
     AND sp.sales_id = linked.id
  ) affected
    ON affected.store_id = s.store_id
   AND affected.customer_id = s.customer_id
  WHERE s.sales_status IN ('Final', 'Opening')
    AND s.customer_id IS NOT NULL
  GROUP BY s.store_id, s.customer_id
) due
  ON due.store_id = c.store_id
 AND due.customer_id = c.id
SET c.sales_due = due.sales_due;

-- This label is shown as the invoice creator in the Sales list.
UPDATE db_sales
SET created_by = 'OnlineStore'
WHERE online_order_id IS NOT NULL
  AND created_by = 'RestaurantOS';
