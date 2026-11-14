-- Fashion measurement notebook and remote size requests.
-- store_id=0 dress types are the editable-by-copy global starter catalogue.
CREATE TABLE IF NOT EXISTS `db_fashion_dress_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL DEFAULT 0,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fashion_dress_type` (`store_id`,`name`),
  KEY `idx_fashion_dress_types` (`store_id`,`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `db_fashion_dress_types` (`store_id`,`name`,`sort_order`) VALUES
(0,'Dress / Gown',10),(0,'Blouse',20),(0,'Skirt',30),(0,'Trousers',40),(0,'Shirt',50),
(0,'Jacket / Blazer',60),(0,'Suit',70),(0,'Agbada',80),(0,'Kaftan',90),(0,'Buba and Iro',100),
(0,'Ankara / African Print Outfit',110),(0,'Aso Ebi',120),(0,'Dashiki',130),(0,'Fila',140),
(0,'Senator Wear',150),(0,'Kampala / Adire Outfit',160),(0,'Traditional Wrapper',170),
(0,'Wedding Outfit',180),(0,'Uniform',190),(0,'Custom Wear',200);

CREATE TABLE IF NOT EXISTS `db_fashion_measurements` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `dress_type_id` INT DEFAULT NULL,
  `dress_type_name` VARCHAR(100) NOT NULL,
  `size_standard` VARCHAR(8) NOT NULL DEFAULT 'UK',
  `unit` VARCHAR(8) NOT NULL DEFAULT 'cm',
  `measurements_json` MEDIUMTEXT NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `source` VARCHAR(20) NOT NULL DEFAULT 'staff',
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_by_id` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fashion_measure_customer` (`store_id`,`customer_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_fashion_size_requests` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `customer_id` INT DEFAULT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `size_standard` VARCHAR(8) NOT NULL DEFAULT 'UK',
  `unit` VARCHAR(8) NOT NULL DEFAULT 'cm',
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `expires_at` DATETIME DEFAULT NULL,
  `submitted_at` DATETIME DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fashion_size_request_token` (`token_hash`),
  KEY `idx_fashion_size_request_customer` (`store_id`,`customer_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
