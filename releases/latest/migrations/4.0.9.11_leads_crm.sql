-- Leads / CRM pipeline captured from storefront contact forms and manual entry
CREATE TABLE IF NOT EXISTS db_leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'manual',
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  interest VARCHAR(255) DEFAULT NULL,
  notes TEXT,
  assigned_to INT(11) UNSIGNED DEFAULT NULL,
  converted_customer_id INT(11) UNSIGNED DEFAULT NULL,
  converted_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME DEFAULT NULL,
  KEY idx_store_status (store_id, status),
  KEY idx_converted_customer (converted_customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
