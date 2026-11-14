-- 4.0.9.181 — Building Materials project materials and procurement
-- Additive, tenant-scoped and idempotent. Reservations are planning records;
-- physical stock changes only when the linked shared purchase is received.

CREATE TABLE IF NOT EXISTS db_building_project_materials (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  item_id INT NULL,
  material_name VARCHAR(200) NOT NULL,
  required_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  unit_label VARCHAR(50) NOT NULL DEFAULT 'unit',
  unit_cost DECIMAL(20,4) NOT NULL DEFAULT 0,
  available_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  reserved_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  ordered_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  received_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  issued_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  returned_qty DECIMAL(20,4) NOT NULL DEFAULT 0,
  supplier_id INT NULL,
  purchase_id INT NULL,
  purchase_item_id INT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'planned',
  expected_date DATE NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  KEY store_job (store_id, job_id),
  KEY store_item (store_id, item_id),
  KEY store_purchase (store_id, purchase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_building_project_procurement (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  supplier_id INT NULL,
  purchase_id INT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'ordered',
  expected_date DATE NULL,
  note TEXT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  UNIQUE KEY store_purchase (store_id, purchase_id),
  KEY store_job (store_id, job_id),
  KEY store_supplier (store_id, supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
