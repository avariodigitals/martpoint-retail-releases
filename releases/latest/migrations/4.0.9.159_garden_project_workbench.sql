-- Project field records and task cards extend existing service jobs.
-- No catalogue, stock, payment or financial ledger is added.
CREATE TABLE IF NOT EXISTS db_service_project_records (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL,
 record_type VARCHAR(30) NOT NULL, fields_json MEDIUMTEXT NULL, note TEXT NULL,
 request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
 created_by INT NULL, created_at DATETIME NOT NULL,
 PRIMARY KEY(id), UNIQUE KEY store_request(store_id,request_key), KEY store_job(store_id,job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS db_service_project_tasks (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 store_id INT NOT NULL, job_id INT UNSIGNED NOT NULL,
 title VARCHAR(200) NOT NULL, description TEXT NULL,
 status VARCHAR(24) NOT NULL DEFAULT 'todo', assigned_user_id INT NULL, due_date DATE NULL,
 version INT NOT NULL DEFAULT 1, request_key VARCHAR(80) NOT NULL, payload_hash CHAR(64) NOT NULL,
 created_by INT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 PRIMARY KEY(id), UNIQUE KEY store_request(store_id,request_key), KEY store_job_status(store_id,job_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
