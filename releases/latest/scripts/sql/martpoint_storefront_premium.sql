-- ============================================================
-- MartPoint Premium Storefront Migration
-- Themes, Banners, Homepage Builder, Custom Domains
-- ============================================================

-- 1. Theme Registry
CREATE TABLE IF NOT EXISTS db_storefront_themes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  theme_key VARCHAR(50) NOT NULL UNIQUE,
  theme_name VARCHAR(100) NOT NULL,
  industry VARCHAR(50) NOT NULL,
  description TEXT,
  default_primary_color VARCHAR(20) DEFAULT '#3B82F6',
  default_secondary_color VARCHAR(20) DEFAULT '#10B981',
  default_font_family VARCHAR(100) DEFAULT 'Inter',
  preview_image VARCHAR(255),
  status TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed themes
INSERT INTO db_storefront_themes (theme_key, theme_name, industry, description, default_primary_color, default_secondary_color, default_font_family, sort_order) VALUES
('general_retail', 'General Retail', 'general', 'Clean, modern default theme for any retail store.', '#3B82F6', '#10B981', 'Inter', 1),
('healthcare_pro', 'HealthCare Pro', 'pharmacy', 'Professional pharmacy and healthcare theme with trust-focused design.', '#005EB8', '#00A86B', 'Inter', 2),
('beauty_luxe', 'Beauty Luxe', 'beauty', 'Elegant beauty and cosmetics theme with soft aesthetics.', '#F8A4C8', '#D4AF37', 'Playfair Display', 3),
('urban_fashion', 'Urban Fashion', 'fashion', 'Bold fashion and apparel theme with editorial layouts.', '#111111', '#FF3B30', 'Montserrat', 4),
('tech_hub', 'Tech Hub', 'electronics', 'Modern electronics and gadgets theme with tech-forward design.', '#0A2540', '#635BFF', 'Inter', 5),
('fresh_market', 'Fresh Market', 'grocery', 'Warm supermarket and grocery theme with organic feel.', '#2E7D32', '#FF6F00', 'Inter', 6),
('food_express', 'Food Express', 'restaurant', 'Appetizing restaurant and food ordering theme.', '#D32F2F', '#FBC02D', 'Inter', 7),
('service_pro', 'Service Pro', 'services', 'Professional services theme for agencies and consultancies.', '#1A237E', '#00BCD4', 'Inter', 8)
ON DUPLICATE KEY UPDATE theme_name = VALUES(theme_name);

-- 2. Banners
CREATE TABLE IF NOT EXISTS db_storefront_banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  banner_title VARCHAR(255),
  banner_subtitle VARCHAR(500),
  desktop_image VARCHAR(255),
  mobile_image VARCHAR(255),
  button_text VARCHAR(100),
  button_url VARCHAR(500),
  display_order INT DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Homepage Sections (toggleable per store)
CREATE TABLE IF NOT EXISTS db_storefront_homepage_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  section_key VARCHAR(50) NOT NULL,
  section_label VARCHAR(100),
  is_enabled TINYINT(1) DEFAULT 1,
  display_order INT DEFAULT 0,
  config_json TEXT,
  UNIQUE KEY uk_store_section (store_id, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default sections for each existing store
INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'hero_banner', 'Hero Banner', 1, 1 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'trust_badges', 'Trust Badges', 1, 2 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'promo_banner', 'Promotional Banner', 1, 3 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'featured_categories', 'Featured Categories', 1, 4 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'featured_products', 'Featured Products', 1, 5 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'featured_services', 'Featured Services', 1, 6 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'best_sellers', 'Best Sellers', 0, 7 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'new_arrivals', 'New Arrivals', 0, 8 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'brands', 'Brands', 0, 9 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'testimonials', 'Testimonials', 0, 10 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'instagram_gallery', 'Instagram Gallery', 0, 11 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'store_info', 'Store Information', 1, 12 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'faqs', 'FAQs', 0, 13 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'contact_section', 'Contact Section', 1, 14 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'whatsapp_cta', 'WhatsApp CTA', 1, 15 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'newsletter', 'Newsletter CTA', 0, 16 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

INSERT INTO db_storefront_homepage_sections (store_id, section_key, section_label, is_enabled, display_order)
SELECT id, 'store_hours', 'Store Hours', 0, 17 FROM db_store WHERE status=1
ON DUPLICATE KEY UPDATE section_label = VALUES(section_label);

-- 4. Custom Domains
CREATE TABLE IF NOT EXISTS db_storefront_domains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  domain_type ENUM('subdomain','custom') DEFAULT 'subdomain',
  domain_value VARCHAR(255) NOT NULL,
  verification_status ENUM('pending','verified','failed') DEFAULT 'pending',
  ssl_status ENUM('pending','active','expired') DEFAULT 'pending',
  connection_status ENUM('pending','connected','disconnected') DEFAULT 'pending',
  dns_instructions TEXT,
  verified_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_domain (domain_value),
  INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Extend db_storefront_settings with theme fields
-- MySQL-safe helper procedure (MySQL does not support ADD COLUMN IF NOT EXISTS)
DELIMITER $$

DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists$$

CREATE PROCEDURE mp_add_column_if_not_exists(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_definition VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = p_table
        AND COLUMN_NAME = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', p_table, ' ADD COLUMN ', p_column, ' ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

CALL mp_add_column_if_not_exists('db_storefront_settings', 'theme_id', "INT DEFAULT NULL AFTER store_id");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'primary_color', "VARCHAR(20) DEFAULT '#3B82F6'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'secondary_color', "VARCHAR(20) DEFAULT '#10B981'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'font_family', "VARCHAR(100) DEFAULT 'Inter'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'button_style', "VARCHAR(50) DEFAULT 'rounded'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'store_headline', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'store_subheadline', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'favicon', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'desktop_banner', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'mobile_banner', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'instagram_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'facebook_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'tiktok_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'x_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'youtube_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'business_hours', "TEXT DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'announcement_bar', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'announcement_bar_color', "VARCHAR(20) DEFAULT '#0F172A'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'preview_mode', "TINYINT(1) DEFAULT 0");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'preview_theme_id', "INT DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'meta_title', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'meta_description', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'meta_keywords', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'google_analytics_id', "VARCHAR(50) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'facebook_pixel_id', "VARCHAR(50) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'robots_index', "TINYINT(1) NOT NULL DEFAULT 1");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'custom_head_scripts', "TEXT DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'footer_bg_color', "VARCHAR(20) DEFAULT '#0F172A'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'header_text_color', "VARCHAR(20) DEFAULT ''");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'button_color', "VARCHAR(20) DEFAULT '#3B82F6'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'instagram_access_token', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'instagram_username', "VARCHAR(100) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'google_places_api_key', "VARCHAR(255) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'gmb_place_id', "VARCHAR(100) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'testimonial_source', "VARCHAR(20) DEFAULT 'custom'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'trust_badges_json', "TEXT DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'newsletter_title', "VARCHAR(255) DEFAULT 'Stay in the Loop'");
CALL mp_add_column_if_not_exists('db_storefront_settings', 'newsletter_subtitle', "VARCHAR(500) DEFAULT 'Subscribe for updates, deals and new arrivals.'");

DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;

-- 3. Storefront Analytics
CREATE TABLE IF NOT EXISTS db_storefront_analytics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  page_url VARCHAR(500) NOT NULL,
  referrer VARCHAR(500) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  session_id VARCHAR(100) DEFAULT NULL,
  source VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_date (store_id, created_at),
  INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Set default theme for existing settings
UPDATE db_storefront_settings SET theme_id = (SELECT id FROM db_storefront_themes WHERE theme_key = 'general_retail' LIMIT 1) WHERE theme_id IS NULL;

-- 4. Storefront Brands
CREATE TABLE IF NOT EXISTS db_storefront_brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  brand_name VARCHAR(100) NOT NULL,
  brand_logo VARCHAR(255) DEFAULT NULL,
  brand_url VARCHAR(500) DEFAULT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Storefront Testimonials
CREATE TABLE IF NOT EXISTS db_storefront_testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  customer_photo VARCHAR(255) DEFAULT NULL,
  testimonial_text TEXT NOT NULL,
  rating INT DEFAULT 5,
  is_enabled TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Storefront Instagram Gallery
CREATE TABLE IF NOT EXISTS db_storefront_instagram (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  caption VARCHAR(255) DEFAULT NULL,
  link_url VARCHAR(500) DEFAULT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Storefront FAQs
CREATE TABLE IF NOT EXISTS db_storefront_faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  is_enabled TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
