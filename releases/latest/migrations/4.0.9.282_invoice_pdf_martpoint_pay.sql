-- Code-only update: all sales invoice PDF routes share the MartPoint payment
-- link, pending rows are excluded from paid totals, and PDF totals stay in flow.
-- Uses the existing invoice_payment_token column introduced in .277.
SELECT 1;
