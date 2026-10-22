-- Additive, repeatable Garden Centre extension. No merchant data is seeded.
-- Shared items, purchase batches and adjustment documents retain their IDs.
CREATE TABLE IF NOT EXISTS db_garden_item_attributes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  item_id INT NOT NULL,
  attributes_json TEXT NOT NULL,
  updated_by INT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_item (store_id,item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit/index of shared stock adjustments; never an independent stock balance.
CREATE TABLE IF NOT EXISTS db_garden_stock_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  item_id INT NOT NULL,
  warehouse_id INT NOT NULL,
  adjustment_id INT NOT NULL,
  reason VARCHAR(30) NOT NULL,
  quantity DECIMAL(20,2) NOT NULL,
  unit_cost DECIMAL(20,4) NOT NULL,
  note TEXT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_request (store_id,request_key),
  KEY store_item (store_id,item_id), KEY adjustment (adjustment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
