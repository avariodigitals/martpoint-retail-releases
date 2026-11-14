-- Garden workflow metadata extends canonical recipes and production batches.
CREATE TABLE IF NOT EXISTS db_garden_production_templates (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, store_id INT NOT NULL, recipe_id INT NOT NULL,
 kind VARCHAR(20) NOT NULL, request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
 created_by INT NOT NULL, PRIMARY KEY(id), UNIQUE KEY store_recipe(store_id,recipe_id), UNIQUE KEY store_request(store_id,request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS db_garden_production_context (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, store_id INT NOT NULL, batch_id INT NOT NULL,
 recipe_id INT NOT NULL, product_item_id INT NOT NULL, warehouse_id INT NOT NULL, kind VARCHAR(20) NOT NULL,
 planned_qty INT NOT NULL, lost_qty INT NOT NULL DEFAULT 0, saleable_qty INT NULL,
 materials_json MEDIUMTEXT NOT NULL, expected_date DATE NULL, started_at DATETIME NULL, completed_at DATETIME NULL,
 completed_cost DECIMAL(20,4) NULL, unit_cost DECIMAL(20,4) NULL, output_adjustment_id INT NULL,
 version INT NOT NULL DEFAULT 1, request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
 created_by INT NOT NULL, created_at DATETIME NOT NULL,
 PRIMARY KEY(id), UNIQUE KEY store_batch(store_id,batch_id), UNIQUE KEY store_request(store_id,request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS db_garden_production_entries (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, store_id INT NOT NULL, batch_id INT NOT NULL,
 kind VARCHAR(20) NOT NULL, item_id INT NULL, quantity DECIMAL(20,3) NOT NULL DEFAULT 0,
 unit_cost DECIMAL(20,4) NOT NULL DEFAULT 0, cost DECIMAL(20,4) NOT NULL DEFAULT 0,
 adjustment_id INT NULL, note TEXT NULL, request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
 created_by INT NOT NULL, created_at DATETIME NOT NULL,
 PRIMARY KEY(id), UNIQUE KEY store_request(store_id,request_key), KEY store_batch(store_id,batch_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
