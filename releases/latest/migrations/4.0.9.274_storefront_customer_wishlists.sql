CREATE TABLE IF NOT EXISTS `db_storefront_wishlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_customer_item` (`store_id`, `customer_id`, `item_id`),
  KEY `idx_store_customer` (`store_id`, `customer_id`),
  KEY `idx_store_item` (`store_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
