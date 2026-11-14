-- MartPoint 4.0.9.97 — printing storefront themes and private quote files
-- Idempotent schema update. Existing storefront settings and selections remain intact.

SET FOREIGN_KEY_CHECKS = 0;

SET @has_show_prices = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_storefront_settings' AND COLUMN_NAME = 'show_prices'
);
SET @show_prices_sql = IF(@has_show_prices = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `show_prices` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1'
);
PREPARE show_prices_stmt FROM @show_prices_sql;
EXECUTE show_prices_stmt;
DEALLOCATE PREPARE show_prices_stmt;

UPDATE `db_storefront_themes`
SET `theme_name` = 'Press & Co.',
    `description` = 'Bold commercial printing with a backend-driven service catalogue and quote-first customer journey.',
    `default_primary_color` = '#24563B',
    `default_secondary_color` = '#D6B26C',
    `default_font_family` = 'Inter',
    `industry` = 'printing', `status` = 1, `sort_order` = 20
WHERE `theme_key` = 'print_inkpress';

UPDATE `db_storefront_themes`
SET `theme_name` = 'Paper Atelier',
    `description` = 'Premium design and print studio with editorial typography and considered project presentation.',
    `default_primary_color` = '#B94830',
    `default_secondary_color` = '#315C48',
    `default_font_family` = 'Playfair Display',
    `industry` = 'printing', `status` = 1, `sort_order` = 21
WHERE `theme_key` = 'print_papercraft';

UPDATE `db_storefront_themes`
SET `theme_name` = 'PrintDesk',
    `description` = 'Product-focused print catalogue with category filters and a guided quotation brief.',
    `default_primary_color` = '#2454E6',
    `default_secondary_color` = '#D6B26C',
    `default_font_family` = 'Inter',
    `industry` = 'printing', `status` = 1, `sort_order` = 22
WHERE `theme_key` = 'print_neonprint';

-- The old fourth printing design is replaced by the approved three-theme set.
UPDATE `db_storefront_themes` SET `status` = 0 WHERE `theme_key` = 'print_works';

-- Carry stores still using the retired default onto Press & Co. Keep all other
-- store settings and any explicit selection of one of the new three themes.
UPDATE `db_storefront_settings` s
JOIN `db_store_industry_settings` i ON i.store_id = s.store_id
JOIN `db_storefront_themes` old_t ON old_t.id = s.theme_id AND old_t.theme_key = 'print_works'
JOIN `db_storefront_themes` press_t ON press_t.theme_key = 'print_inkpress'
SET s.theme_id = press_t.id,
    i.storefront_theme_key = 'print_inkpress'
WHERE i.industry_type = 'printing';

UPDATE `db_store`
JOIN `db_store_industry_settings` i ON i.store_id = db_store.id
SET db_store.storefront_theme_key = 'print_inkpress'
WHERE i.industry_type = 'printing' AND db_store.storefront_theme_key = 'print_works';

CREATE TABLE IF NOT EXISTS `db_print_enquiry_files` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `lead_id` INT(11) NOT NULL,
  `original_name` VARCHAR(240) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(120) DEFAULT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_print_enquiry_lead` (`store_id`, `lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
