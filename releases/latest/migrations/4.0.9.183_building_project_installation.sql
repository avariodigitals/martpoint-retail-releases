-- 4.0.9.183 — Building Materials installation checklist and handover

CREATE TABLE IF NOT EXISTS db_building_project_installation_steps (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  step_key VARCHAR(80) NOT NULL,
  title VARCHAR(200) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  note TEXT NULL,
  completed_by INT NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_job_step (store_id, job_id, step_key),
  KEY store_job (store_id, job_id),
  KEY store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_building_project_handovers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  certificate_reference VARCHAR(120) NOT NULL,
  customer_name VARCHAR(160) NOT NULL,
  note TEXT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'signed',
  signed_by INT NULL,
  signed_at DATETIME NOT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_job (store_id, job_id),
  UNIQUE KEY store_request (store_id, request_key),
  KEY store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
