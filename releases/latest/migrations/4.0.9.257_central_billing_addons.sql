-- Link Central non-licence subscription invoices to their existing request rows.
ALTER TABLE db_communication_requests
  ADD COLUMN canonical_product_type VARCHAR(16) NOT NULL DEFAULT 'CREDIT',
  ADD COLUMN canonical_addon_key VARCHAR(60) DEFAULT NULL;

ALTER TABLE db_central_billing_fulfillments
  MODIFY channel VARCHAR(20) DEFAULT NULL,
  ADD COLUMN product_type VARCHAR(16) NOT NULL DEFAULT 'CREDIT',
  ADD COLUMN addon_key VARCHAR(60) DEFAULT NULL,
  ADD COLUMN billing_period VARCHAR(10) DEFAULT NULL;
