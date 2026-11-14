-- Stable service-work snapshots attached to existing sale/order lines.
-- These are fulfilment links, not additional sale, stock or payment records.
CREATE TABLE IF NOT EXISTS db_service_fulfilment_sources (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, source_type VARCHAR(16) NOT NULL,
  source_parent_id INT NOT NULL, source_line_id INT NOT NULL,
  component_item_id INT NOT NULL DEFAULT 0,
  customer_id INT NOT NULL, pos_item_id INT NULL, online_service_id INT NULL,
  service_name VARCHAR(200) NOT NULL, quantity DECIMAL(20,2) NOT NULL,
  job_id INT UNSIGNED NULL, created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY source_component (store_id,source_type,source_line_id,component_item_id),
  UNIQUE KEY store_job (store_id,job_id),
  KEY source_parent (store_id,source_type,source_parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
