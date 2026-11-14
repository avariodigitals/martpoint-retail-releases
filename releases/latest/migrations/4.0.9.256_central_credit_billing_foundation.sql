-- Additive Central-to-Admin billing metadata. Existing rows remain legacy.
ALTER TABLE db_communication_settings
  ADD COLUMN next_billing_enabled TINYINT NOT NULL DEFAULT 0,
  ADD COLUMN next_billing_catalogue_hash CHAR(64) DEFAULT NULL,
  ADD COLUMN next_billing_catalogue_imported_at DATETIME DEFAULT NULL;
ALTER TABLE db_communication_requests
  ADD COLUMN billing_mode VARCHAR(16) NOT NULL DEFAULT 'legacy',
  ADD COLUMN canonical_business_id CHAR(36) DEFAULT NULL,
  ADD COLUMN canonical_invoice_id CHAR(36) DEFAULT NULL,
  ADD COLUMN canonical_invoice_number VARCHAR(80) DEFAULT NULL,
  ADD COLUMN canonical_invoice_item_id CHAR(36) DEFAULT NULL,
  ADD COLUMN central_intent_id CHAR(36) DEFAULT NULL,
  ADD UNIQUE KEY central_comm_intent (central_intent_id),
  ADD UNIQUE KEY central_comm_invoice_line (canonical_invoice_item_id);
ALTER TABLE db_messaging_purchases
  ADD COLUMN billing_mode VARCHAR(16) NOT NULL DEFAULT 'legacy',
  ADD COLUMN canonical_business_id CHAR(36) DEFAULT NULL,
  ADD COLUMN canonical_invoice_id CHAR(36) DEFAULT NULL,
  ADD COLUMN canonical_invoice_item_id CHAR(36) DEFAULT NULL,
  ADD UNIQUE KEY central_purchase_invoice_line (canonical_invoice_item_id);

CREATE TABLE IF NOT EXISTS db_central_billing_fulfillments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  event_id CHAR(36) NOT NULL,
  invoice_item_id CHAR(36) NOT NULL,
  account_id BIGINT UNSIGNED NOT NULL,
  channel VARCHAR(10) NOT NULL,
  credit_units BIGINT UNSIGNED NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'applied',
  created_at DATETIME NOT NULL,
  UNIQUE KEY central_fulfillment_event (event_id),
  UNIQUE KEY central_fulfillment_line (invoice_item_id),
  KEY central_fulfillment_account (account_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
