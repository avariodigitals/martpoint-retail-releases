-- Newsletter subscribers captured from storefront signup forms
CREATE TABLE IF NOT EXISTS db_newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'newsletter',
  ip_address VARCHAR(45) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uk_store_email (store_id, email),
  KEY idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
