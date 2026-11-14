-- Extensions to canonical service jobs. Financial values are derived from
-- linked shared quotations, invoices, expenses and actual material issues.
CREATE TABLE IF NOT EXISTS db_service_job_context (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL,
  lead_id INT NULL, quotation_id INT NULL, quotation_revision INT NULL,
  stage VARCHAR(30) NOT NULL DEFAULT 'enquiry',
  inspection_json TEXT NULL, completion_notes TEXT NULL,
  estimated_materials DECIMAL(20,4) NOT NULL DEFAULT 0,
  estimated_labour DECIMAL(20,4) NOT NULL DEFAULT 0,
  estimated_other DECIMAL(20,4) NOT NULL DEFAULT 0,
  request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_job (store_id,job_id),
  UNIQUE KEY store_lead (store_id,lead_id), UNIQUE KEY store_quotation (store_id,quotation_id)
  , UNIQUE KEY store_request (store_id,request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_job_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL,
  from_stage VARCHAR(30) NULL, to_stage VARCHAR(30) NOT NULL,
  note TEXT NULL, created_by INT NULL, created_at DATETIME NOT NULL,
  PRIMARY KEY (id), KEY store_job (store_id,job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_job_expense_links (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL, expense_id INT NOT NULL,
  created_by INT NULL, created_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_expense (store_id,expense_id), KEY store_job (store_id,job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_material_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL,
  request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
  job_item_id INT UNSIGNED NOT NULL, adjustment_id INT NOT NULL,
  unit_cost DECIMAL(20,4) NOT NULL, created_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_request (store_id,request_key),
  UNIQUE KEY store_job_item (store_id,job_item_id), KEY adjustment (adjustment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
