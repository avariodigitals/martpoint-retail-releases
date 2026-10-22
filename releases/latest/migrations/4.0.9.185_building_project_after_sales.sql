-- 4.0.9.185 — Building Materials installed assets and after-sales controls

CREATE TABLE IF NOT EXISTS db_building_project_assets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  equipment_id INT UNSIGNED NOT NULL,
  material_id INT UNSIGNED NULL,
  asset_tag VARCHAR(120) NOT NULL,
  serial_number VARCHAR(120) NULL,
  model VARCHAR(160) NULL,
  warranty_months INT NOT NULL DEFAULT 12,
  warranty_start DATE NULL,
  warranty_end DATE NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'installed',
  note TEXT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  UNIQUE KEY store_asset_tag (store_id, asset_tag),
  KEY store_job (store_id, job_id),
  KEY store_equipment (store_id, equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_building_project_maintenance (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  asset_id INT UNSIGNED NOT NULL,
  due_date DATE NOT NULL,
  interval_months INT NOT NULL DEFAULT 12,
  status VARCHAR(30) NOT NULL DEFAULT 'planned',
  note TEXT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  KEY store_asset (store_id, asset_id),
  KEY store_due (store_id, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_building_project_warranty_claims (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  asset_id INT UNSIGNED NOT NULL,
  claim_reference VARCHAR(120) NOT NULL,
  issue TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  resolution TEXT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_request (store_id, request_key),
  UNIQUE KEY store_claim_reference (store_id, claim_reference),
  KEY store_asset (store_id, asset_id),
  KEY store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
