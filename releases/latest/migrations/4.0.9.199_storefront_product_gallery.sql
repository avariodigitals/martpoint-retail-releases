-- Add supplemental storefront product photos without replacing db_items.item_image.
-- Images are scoped by both store and product; no cross-tenant lookups are valid.
CREATE TABLE IF NOT EXISTS db_item_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  store_id BIGINT UNSIGNED NOT NULL,
  item_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_item_images_store_item (store_id, item_id),
  KEY idx_item_images_order (store_id, item_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
