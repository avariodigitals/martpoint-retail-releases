-- 4.0.9.184 — Building Materials project billing milestones

CREATE TABLE IF NOT EXISTS db_building_project_milestones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  milestone_key VARCHAR(80) NOT NULL,
  title VARCHAR(160) NOT NULL,
  percentage DECIMAL(7,3) NOT NULL DEFAULT 0,
  amount DECIMAL(20,4) NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'planned',
  sales_id INT NULL,
  request_key VARCHAR(80) NOT NULL,
  payload_hash CHAR(64) NOT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_job_key (store_id, job_id, milestone_key),
  UNIQUE KEY store_request (store_id, request_key),
  KEY store_job (store_id, job_id),
  KEY store_sales (store_id, sales_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
