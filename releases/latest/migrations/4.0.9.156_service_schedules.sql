-- Reusable non-clinical recurring service schedule. Occurrences reference
-- existing db_service_jobs; cancelled/rescheduled jobs keep their occurrence.
CREATE TABLE IF NOT EXISTS db_service_schedules (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, customer_id INT NOT NULL, site_id INT NULL,
  assigned_user_id INT NULL, title VARCHAR(200) NOT NULL,
  start_date DATE NOT NULL, end_date DATE NULL,
  frequency VARCHAR(20) NOT NULL, custom_days INT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  last_generated_index INT NOT NULL DEFAULT -1,
  request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
  created_by INT NULL, created_at DATETIME NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY store_request (store_id,request_key),
  KEY store_status (store_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_schedule_occurrences (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id INT NOT NULL, schedule_id INT UNSIGNED NOT NULL,
  occurrence_date DATE NOT NULL, occurrence_index INT NOT NULL,
  job_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY schedule_index (store_id,schedule_id,occurrence_index),
  UNIQUE KEY job (store_id,job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
