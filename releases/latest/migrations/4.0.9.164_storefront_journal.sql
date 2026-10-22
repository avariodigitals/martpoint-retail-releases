-- Store-scoped editorial articles. Commerce and CRM records remain shared.
CREATE TABLE IF NOT EXISTS db_storefront_posts (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 store_id INT NOT NULL, slug VARCHAR(180) NOT NULL, title VARCHAR(255) NOT NULL,
 excerpt TEXT NULL, body MEDIUMTEXT NOT NULL, image_url VARCHAR(1500) NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'draft', published_at DATETIME NULL,
 created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
 UNIQUE KEY store_slug (store_id,slug), KEY public_posts (store_id,status,published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
