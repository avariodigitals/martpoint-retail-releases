-- Credentials for the alternative portal.nigeriabulksms.com HTTP API.
CREATE TABLE IF NOT EXISTS db_nigeriabulksms (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  store_id INT(11) NOT NULL,
  username VARCHAR(190) NOT NULL DEFAULT '',
  password TEXT NOT NULL,
  sender_id VARCHAR(50) NOT NULL DEFAULT 'MartPoint',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
