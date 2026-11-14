-- 4.0.9.182 — Building Materials project delivery trips and proof of delivery
-- Delivery is project-scoped; stock is issued only once a trip is signed off.

CREATE TABLE IF NOT EXISTS db_building_project_deliveries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  delivery_code VARCHAR(80) NOT NULL,
  trip_no INT NOT NULL DEFAULT 1,
  status VARCHAR(30) NOT NULL DEFAULT 'planned',
  scheduled_date DATE NULL,
  driver_name VARCHAR(160) NULL,
  vehicle VARCHAR(120) NULL,
  proof_reference VARCHAR(255) NULL,
  proof_note TEXT NULL,
  signed_by VARCHAR(160) NULL,
  signed_at DATETIME NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  UNIQUE KEY store_delivery_code (store_id, delivery_code),
  KEY store_job (store_id, job_id),
  KEY store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_building_project_delivery_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  delivery_id INT UNSIGNED NOT NULL,
  material_id INT UNSIGNED NOT NULL,
  quantity DECIMAL(20,4) NOT NULL DEFAULT 0,
  unit_label VARCHAR(50) NOT NULL DEFAULT 'unit',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY delivery_material (store_id, delivery_id, material_id),
  KEY store_delivery (store_id, delivery_id),
  KEY store_material (store_id, material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
