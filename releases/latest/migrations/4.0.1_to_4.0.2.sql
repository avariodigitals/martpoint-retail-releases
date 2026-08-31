-- MartPoint 4.0.1 -> 4.0.2 migration
-- Consolidates all runtime-created tables from model constructors into the
-- authoritative installer schema. This migration is idempotent and safe
-- to run on existing installations that already have some of these tables.
-- It should be executed automatically via Updates_model::index() before login.

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ========================================================================
-- Storefront tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_storefront_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    store_slug VARCHAR(100) NOT NULL DEFAULT '',
    store_description TEXT,
    store_banner VARCHAR(255),
    store_logo VARCHAR(255),
    whatsapp_number VARCHAR(20) DEFAULT '',
    store_email VARCHAR(100) DEFAULT '',
    store_phone VARCHAR(20) DEFAULT '',
    store_address TEXT,
    default_branch_id INT DEFAULT 0,
    store_status ENUM('active','maintenance','deactivated') DEFAULT 'active',
    allow_paystack TINYINT(1) DEFAULT 1,
    allow_whatsapp TINYINT(1) DEFAULT 1,
    allow_pay_on_delivery TINYINT(1) DEFAULT 1,
    allow_services TINYINT(1) DEFAULT 1,
    allow_backorder TINYINT(1) DEFAULT 0,
    show_search TINYINT(1) DEFAULT 1,
    show_categories TINYINT(1) DEFAULT 1,
    show_whatsapp_cta TINYINT(1) DEFAULT 1,
    featured_products_limit INT DEFAULT 8,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    theme_id INT DEFAULT NULL,
    primary_color VARCHAR(20) DEFAULT '#3B82F6',
    secondary_color VARCHAR(20) DEFAULT '#10B981',
    font_family VARCHAR(100) DEFAULT 'Inter',
    button_style VARCHAR(50) DEFAULT 'rounded',
    store_headline VARCHAR(255) DEFAULT NULL,
    store_subheadline VARCHAR(500) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    desktop_banner VARCHAR(255) DEFAULT NULL,
    mobile_banner VARCHAR(255) DEFAULT NULL,
    instagram_url VARCHAR(500) DEFAULT NULL,
    facebook_url VARCHAR(500) DEFAULT NULL,
    tiktok_url VARCHAR(500) DEFAULT NULL,
    x_url VARCHAR(500) DEFAULT NULL,
    youtube_url VARCHAR(500) DEFAULT NULL,
    business_hours TEXT DEFAULT NULL,
    announcement_bar VARCHAR(500) DEFAULT NULL,
    announcement_bar_color VARCHAR(20) DEFAULT '#0F172A',
    preview_mode TINYINT(1) DEFAULT 0,
    preview_theme_id INT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    footer_bg_color VARCHAR(20) DEFAULT '#0F172A',
    header_text_color VARCHAR(20) DEFAULT '',
    footer_style VARCHAR(50) DEFAULT 'standard',
    footer_about_us TEXT DEFAULT NULL,
    footer_text_color VARCHAR(20) DEFAULT '#94A3B8',
    footer_address_url VARCHAR(500) DEFAULT NULL,
    button_color VARCHAR(20) DEFAULT '#3B82F6',
    meta_keywords VARCHAR(255) DEFAULT NULL,
    google_analytics_id VARCHAR(50) DEFAULT NULL,
    facebook_pixel_id VARCHAR(50) DEFAULT NULL,
    robots_index TINYINT(1) DEFAULT 1,
    custom_head_scripts TEXT DEFAULT NULL,
    UNIQUE KEY uk_storefront_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    service_name VARCHAR(200) NOT NULL,
    service_image VARCHAR(255),
    category_id INT DEFAULT 0,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_price DECIMAL(12,2) DEFAULT NULL,
    service_duration VARCHAR(50),
    description TEXT,
    available_online TINYINT(1) DEFAULT 1,
    requires_appointment TINYINT(1) DEFAULT 0,
    requires_note TINYINT(1) DEFAULT 0,
    location_type ENUM('in-store','customer-location','online') DEFAULT 'in-store',
    sort_order INT DEFAULT 0,
    deposit_required TINYINT(1) DEFAULT 0,
    deposit_percent DECIMAL(10,2) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, status, available_online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_online_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    order_code VARCHAR(50) NOT NULL,
    customer_name VARCHAR(200),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    order_type ENUM('product','service','mixed') DEFAULT 'product',
    order_status ENUM('pending','paid','processing','ready','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','partially_paid','failed','refunded') DEFAULT 'unpaid',
    payment_method ENUM('paystack','whatsapp','pay_on_delivery') DEFAULT 'pay_on_delivery',
    paystack_reference VARCHAR(100),
    paystack_amount DECIMAL(12,2) DEFAULT 0,
    subtotal DECIMAL(12,2) DEFAULT 0,
    delivery_fee DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    grand_total DECIMAL(12,2) DEFAULT 0,
    service_date DATE,
    service_time VARCHAR(20),
    service_note TEXT,
    table_number VARCHAR(20),
    qr_code_id INT DEFAULT 0,
    whatsapp_sent TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, order_status),
    INDEX idx_created (created_at),
    INDEX idx_paystack (paystack_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_online_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type ENUM('product','service') DEFAULT 'product',
    item_id INT NOT NULL,
    item_name VARCHAR(200),
    item_image VARCHAR(255),
    qty INT DEFAULT 1,
    unit_price DECIMAL(12,2) DEFAULT 0,
    total_price DECIMAL(12,2) DEFAULT 0,
    service_note TEXT,
    status TINYINT(1) DEFAULT 1,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    banner_type ENUM('hero','promo') DEFAULT 'hero',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_homepage_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    section_key VARCHAR(50) NOT NULL,
    section_label VARCHAR(100),
    is_enabled TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    config_json TEXT,
    UNIQUE KEY uk_store_section (store_id, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    qr_name VARCHAR(200),
    qr_type ENUM('store','product','service','category','table') DEFAULT 'store',
    related_id INT DEFAULT 0,
    table_number VARCHAR(20),
    qr_image VARCHAR(255),
    qr_data TEXT,
    download_count INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_type (store_id, qr_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS db_storefront_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    source VARCHAR(100) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    search_term VARCHAR(255) DEFAULT NULL,
    is_new_user TINYINT(1) NOT NULL DEFAULT 1,
    session_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_created (store_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- Delivery tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_delivery_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    schedule_code VARCHAR(50) NOT NULL,
    route_name VARCHAR(255) NULL,
    schedule_date DATE NOT NULL,
    driver_id INT NULL,
    driver_name VARCHAR(255) NULL,
    vehicle VARCHAR(100) NULL,
    notes TEXT NULL,
    status ENUM('planned','ready','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'planned',
    created_date DATE NULL,
    created_time VARCHAR(20) NULL,
    created_by VARCHAR(50) NULL,
    system_ip VARCHAR(50) NULL,
    system_name VARCHAR(100) NULL,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status),
    INDEX idx_schedule_date (schedule_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_delivery_schedule_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    sales_id INT NOT NULL,
    sales_code VARCHAR(50) NULL,
    customer_id INT NULL,
    customer_name VARCHAR(255) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    delivery_sequence INT NOT NULL DEFAULT 0,
    delivery_status ENUM('pending','out_for_delivery','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
    delivered_at DATETIME NULL,
    delivery_notes TEXT NULL,
    signature TEXT NULL,
    photo_proof VARCHAR(255) NULL,
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_sales_id (sales_id),
    INDEX idx_delivery_status (delivery_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_delivery_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    emergency_contact_name VARCHAR(255) NULL,
    emergency_contact_phone VARCHAR(50) NULL,
    nin VARCHAR(50) NULL COMMENT 'National Identification Number',
    driver_license VARCHAR(100) NULL COMMENT 'FRSC Driver License Number',
    license_expiry DATE NULL,
    vehicle VARCHAR(100) NULL,
    vehicle_type ENUM('motorcycle','car','van','truck','bicycle','keke') NULL DEFAULT 'motorcycle',
    vehicle_color VARCHAR(50) NULL,
    license_plate VARCHAR(50) NULL,
    employment_type ENUM('full_time','contract','part_time','intern') NULL DEFAULT 'contract',
    hire_date DATE NULL,
    photo VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('active','inactive','on_leave','suspended') NOT NULL DEFAULT 'active',
    created_date DATE NULL,
    created_by VARCHAR(50) NULL,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Membership tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    plan_name VARCHAR(255) NOT NULL,
    plan_code VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    billing_cycle ENUM('monthly','quarterly','annual') NOT NULL DEFAULT 'monthly',
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    free_services_per_period INT NOT NULL DEFAULT 0,
    priority_booking TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status),
    INDEX idx_billing_cycle (billing_cycle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    next_billing_date DATE NULL,
    auto_renew TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'active',
    payment_status ENUM('paid','overdue','pending') NOT NULL DEFAULT 'paid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date),
    INDEX idx_next_billing (next_billing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_membership_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_id INT NOT NULL,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NULL,
    payment_period_start DATE NOT NULL,
    payment_period_end DATE NOT NULL,
    status ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_membership_id (membership_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Custom orders tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_custom_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    order_code VARCHAR(50) NOT NULL,
    customer_id INT NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(255) NULL,
    specifications_json JSON NULL,
    quoted_price DECIMAL(12,2) DEFAULT 0,
    deposit_amount DECIMAL(12,2) DEFAULT 0,
    deposit_paid DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) DEFAULT 0,
    balance_due DECIMAL(12,2) DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    workflow_template_key VARCHAR(50) DEFAULT 'standard',
    notes TEXT NULL,
    staff_id INT NULL DEFAULT 0,
    staff_name VARCHAR(255) NULL,
    order_date DATE NOT NULL,
    due_date DATE NULL,
    delivery_date DATE NULL,
    sales_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_item_id (item_id),
    INDEX idx_status (status),
    INDEX idx_order_code (order_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_custom_order_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    custom_order_id INT NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    changed_by INT NULL,
    changed_by_name VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_custom_order_id (custom_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Treatment notes tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_treatment_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    customer_id INT NOT NULL,
    service_type VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    treatment_date DATE NOT NULL,
    staff_id INT NULL DEFAULT 0,
    staff_name VARCHAR(255) NULL,
    products_used TEXT NULL,
    recommendations TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_treatment_date (treatment_date),
    INDEX idx_service_type (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_treatment_note_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treatment_note_id INT NOT NULL,
    item_id INT NOT NULL,
    qty DECIMAL(12,3) NOT NULL DEFAULT 0,
    item_name VARCHAR(255) NULL,
    consumable_unit VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_treatment_note_id (treatment_note_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Email & report tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_email_logs (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    email_type VARCHAR(64) NOT NULL DEFAULT '',
    provider_used VARCHAR(32) NOT NULL DEFAULT '',
    recipient VARCHAR(512) NOT NULL DEFAULT '',
    subject VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('sent','failed','pending','retrying') NOT NULL DEFAULT 'pending',
    error_message TEXT,
    triggered_by VARCHAR(64) DEFAULT NULL,
    related_module VARCHAR(64) DEFAULT NULL,
    related_record_id VARCHAR(64) DEFAULT NULL,
    provider_response TEXT,
    created_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_type (email_type),
    KEY idx_store (store_id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_email_templates (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    template_key VARCHAR(64) NOT NULL,
    template_name VARCHAR(128) NOT NULL,
    subject VARCHAR(255) NOT NULL DEFAULT '',
    html_body TEXT,
    text_body TEXT,
    status TINYINT(1) NOT NULL DEFAULT 1,
    send_copy_to_owner TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_template_key_store (template_key, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_email_settings (
    store_id INT NOT NULL PRIMARY KEY,
    email_provider VARCHAR(50) DEFAULT 'smtp',
    email_from_name VARCHAR(255) NULL DEFAULT NULL,
    email_from_email VARCHAR(255) NULL DEFAULT NULL,
    email_reply_to VARCHAR(255) NULL DEFAULT NULL,
    smtp_crypto VARCHAR(50) NULL DEFAULT NULL,
    resend_api_key VARCHAR(255) NULL DEFAULT NULL,
    resend_from_email VARCHAR(255) NULL DEFAULT NULL,
    resend_from_name VARCHAR(255) NULL DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_report_schedules (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    report_type VARCHAR(64) NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
    template_name VARCHAR(128) DEFAULT NULL,
    frequency VARCHAR(16) NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
    send_time VARCHAR(8) NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
    email_enabled TINYINT(1) NOT NULL DEFAULT 1,
    email_recipients VARCHAR(500) DEFAULT NULL COMMENT 'comma-separated emails',
    email_template_key VARCHAR(64) DEFAULT 'daily_business_summary',
    whatsapp_enabled TINYINT(1) NOT NULL DEFAULT 0,
    whatsapp_numbers VARCHAR(500) DEFAULT NULL COMMENT 'comma-separated with country code',
    whatsapp_message_template TEXT DEFAULT NULL,
    last_run_at DATETIME DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_report_type_store (report_type, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- Debt reminder tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_debt_reminder_settings (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    customer_id INT(11) unsigned NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    frequency VARCHAR(16) NOT NULL DEFAULT 'weekly' COMMENT 'daily,3days,weekly,biweekly,monthly',
    max_reminders INT(11) NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    reminder_count INT(11) NOT NULL DEFAULT 0,
    last_reminder_sent DATETIME DEFAULT NULL,
    send_email TINYINT(1) NOT NULL DEFAULT 1,
    send_sms TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_customer_store (customer_id, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_debt_reminder_history (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    customer_id INT(11) unsigned NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    amount_due DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    channel VARCHAR(16) NOT NULL DEFAULT 'email' COMMENT 'email,sms,whatsapp',
    status VARCHAR(16) NOT NULL DEFAULT 'sent' COMMENT 'sent,failed',
    error_message TEXT DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_customer (customer_id),
    KEY idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- Attendance / shifts tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    shift_name VARCHAR(100) NOT NULL DEFAULT '',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    grace_minutes INT NOT NULL DEFAULT 0,
    location_lat DECIMAL(10,8) DEFAULT NULL,
    location_lng DECIMAL(11,8) DEFAULT NULL,
    location_radius_meters INT NOT NULL DEFAULT 100,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_user_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shift_id INT NOT NULL,
    store_id INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    user_id INT NOT NULL,
    shift_id INT DEFAULT NULL,
    attendance_date DATE NOT NULL,
    clock_in TIME DEFAULT NULL,
    clock_out TIME DEFAULT NULL,
    clock_in_lat DECIMAL(10,8) DEFAULT NULL,
    clock_in_lng DECIMAL(11,8) DEFAULT NULL,
    clock_out_lat DECIMAL(10,8) DEFAULT NULL,
    clock_out_lng DECIMAL(11,8) DEFAULT NULL,
    face_image VARCHAR(255) DEFAULT NULL,
    face_image_out VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'present',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (attendance_date),
    INDEX idx_user (user_id),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- Recipe / production tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_recipe_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    name VARCHAR(100) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    recipe_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NULL,
    description TEXT NULL,
    product_item_id INT NULL COMMENT 'db_items id of final product',
    margin_pct DECIMAL(5,2) NOT NULL DEFAULT 30 COMMENT 'Sales margin % applied to production cost',
    yield_qty DECIMAL(10,3) NOT NULL DEFAULT 1,
    yield_unit VARCHAR(50) NOT NULL DEFAULT 'piece',
    prep_time INT NULL COMMENT 'minutes',
    cook_time INT NULL COMMENT 'minutes',
    notes TEXT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipe_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    item_id INT NULL COMMENT 'db_items id if linked',
    item_name VARCHAR(255) NOT NULL,
    qty DECIMAL(15,3) NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL DEFAULT 'gram',
    cost_per_unit DECIMAL(15,2) NOT NULL DEFAULT 0,
    wastage_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recipe_id (recipe_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipe_production_runs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    recipe_id INT NOT NULL,
    batch_id INT NULL COMMENT 'db_production_batches id if linked',
    planned_qty DECIMAL(10,3) NOT NULL DEFAULT 0,
    actual_yield DECIMAL(10,3) NULL,
    actual_cost DECIMAL(15,2) NULL,
    staff_id INT NULL,
    notes TEXT NULL,
    run_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_recipe_id (recipe_id),
    INDEX idx_batch_id (batch_id),
    INDEX idx_run_date (run_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Production batches
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_production_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    batch_code VARCHAR(50) NOT NULL,
    batch_name VARCHAR(255) NOT NULL,
    batch_type VARCHAR(50) DEFAULT 'general',
    scheduled_date DATE NOT NULL,
    scheduled_time VARCHAR(20) NULL,
    equipment VARCHAR(255) NULL,
    staff_id INT NULL DEFAULT 0,
    staff_name VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'planned',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_production_batch_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    item_type VARCHAR(50) NOT NULL DEFAULT 'custom_order',
    item_id INT NOT NULL,
    item_name VARCHAR(255) NULL,
    quantity INT NOT NULL DEFAULT 1,
    notes TEXT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_id (batch_id),
    INDEX idx_item_type (item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Service packages
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_service_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    package_code VARCHAR(50) NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    package_image VARCHAR(255) NULL,
    pricing_model ENUM('fixed','calculated') NOT NULL DEFAULT 'fixed',
    package_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_type VARCHAR(20) NULL,
    discount DECIMAL(15,2) NULL,
    redemption_type ENUM('single','multi') NOT NULL DEFAULT 'single',
    expiry_type ENUM('none','days','date') NOT NULL DEFAULT 'none',
    expiry_days INT NULL,
    expiry_date DATE NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_date DATE NULL,
    created_time VARCHAR(20) NULL,
    created_by VARCHAR(50) NULL,
    system_ip VARCHAR(50) NULL,
    system_name VARCHAR(100) NULL,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_package_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    item_type ENUM('service','product') NOT NULL DEFAULT 'service',
    item_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_package_id (package_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    customer_id INT NOT NULL,
    package_id INT NOT NULL,
    sale_id INT NULL,
    sale_items_id INT NULL,
    package_code VARCHAR(50) NOT NULL,
    total_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
    remaining_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
    expiry_date DATE NULL,
    status ENUM('active','fully_redeemed','expired','cancelled') NOT NULL DEFAULT 'active',
    created_date DATE NULL,
    created_time VARCHAR(20) NULL,
    created_by VARCHAR(50) NULL,
    INDEX idx_customer_id (customer_id),
    INDEX idx_package_id (package_id),
    INDEX idx_store_id (store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_package_redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_package_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity_redeemed DECIMAL(10,2) NOT NULL DEFAULT 1,
    service_order_id INT NULL,
    sale_id INT NULL,
    notes TEXT NULL,
    redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    redeemed_by VARCHAR(50) NULL,
    INDEX idx_customer_package_id (customer_package_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Laundry tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_laundry_orders (
    id INT(10) NOT NULL AUTO_INCREMENT,
    sales_id INT(10) NOT NULL,
    store_id INT(10) NOT NULL,
    tag_number VARCHAR(50) NULL,
    service_type VARCHAR(50) NULL DEFAULT 'standard',
    notes TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'dropped_off',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sales_id (sales_id),
    KEY idx_store_id (store_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS db_laundry_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laundry_order_id INT NOT NULL,
    salesitem_id INT NOT NULL,
    item_id INT NOT NULL,
    service_type VARCHAR(20) NOT NULL DEFAULT 'wash_iron',
    item_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_laundry_order_id (laundry_order_id),
    INDEX idx_item_status (item_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================================
-- Subscription / license tables
-- ========================================================================
CREATE TABLE IF NOT EXISTS db_subscription_license (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    store_id INT(11) NOT NULL,
    license_code VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    plan_name VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Basic',
    subscription_start_date DATE DEFAULT NULL,
    subscription_end_date DATE DEFAULT NULL,
    subscription_status VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE',
    branch_limit INT(11) DEFAULT 1,
    user_limit INT(11) DEFAULT 5,
    renewal_amount DECIMAL(20,2) DEFAULT NULL,
    last_renewal_date DATE DEFAULT NULL,
    suspension_reason TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    reminder_90_sent TINYINT(1) DEFAULT 0,
    reminder_60_sent TINYINT(1) DEFAULT 0,
    reminder_30_last_sent DATE DEFAULT NULL,
    reminder_10_last_sent DATE DEFAULT NULL,
    expiry_notice_sent TINYINT(1) DEFAULT 0,
    expired_followup_count INT(11) DEFAULT 0,
    expired_followup_last_sent DATE DEFAULT NULL,
    activated_by VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    updated_date DATE DEFAULT NULL,
    updated_time TIME DEFAULT NULL,
    status INT(1) DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY store_id (store_id),
    KEY license_code (license_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_license_otps (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    otp_code VARCHAR(10) NOT NULL,
    otp_type VARCHAR(20) NOT NULL DEFAULT 'generate',
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_license_history (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    license_code VARCHAR(500) DEFAULT NULL,
    plan_name VARCHAR(100) DEFAULT NULL,
    domain VARCHAR(255) DEFAULT NULL,
    activated_at DATETIME DEFAULT NULL,
    deactivated_at DATETIME DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_brevo (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    api_key VARCHAR(255) DEFAULT NULL,
    sender_name VARCHAR(50) DEFAULT NULL,
    status INT(1) DEFAULT '0',
    KEY store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================================
-- Add columns that were previously added by runtime ALTER TABLE statements
-- Idempotent MySQL-compatible dynamic SQL (ALTER TABLE ADD COLUMN IF NOT EXISTS
-- is not supported on all MySQL distributions used by the user base).
-- ========================================================================

-- db_items
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'accept_custom_order');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `accept_custom_order` TINYINT(1) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'custom_order_fields_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `custom_order_fields_json` JSON DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'requires_quote');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `requires_quote` TINYINT(1) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'requires_deposit');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `requires_deposit` TINYINT(1) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'workflow_template_key');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `workflow_template_key` VARCHAR(50) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'recipe_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `recipe_id` INT DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'recipe_margin_pct');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `recipe_margin_pct` DECIMAL(10,2) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'not_for_sale');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `not_for_sale` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'consumable_unit');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `consumable_unit` VARCHAR(50) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_services
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_services' AND column_name = 'industry_fields_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_services` ADD COLUMN `industry_fields_json` JSON DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_store
-- Shrink legacy modular init columns to TEXT first so the remaining ALTERs
-- do not exceed InnoDB's 8126-byte row size limit on older installations.
SET @sql = IF(EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'db_store'),
  'ALTER TABLE `db_store`
   MODIFY COLUMN `category_init` TEXT,
   MODIFY COLUMN `item_init` TEXT,
   MODIFY COLUMN `supplier_init` TEXT,
   MODIFY COLUMN `purchase_init` TEXT,
   MODIFY COLUMN `purchase_return_init` TEXT,
   MODIFY COLUMN `customer_init` TEXT,
   MODIFY COLUMN `sales_init` TEXT,
   MODIFY COLUMN `sales_return_init` TEXT,
   MODIFY COLUMN `expense_init` TEXT,
   MODIFY COLUMN `quotation_init` TEXT,
   MODIFY COLUMN `money_transfer_init` TEXT,
   MODIFY COLUMN `accounts_init` TEXT,
   MODIFY COLUMN `sales_payment_init` TEXT,
   MODIFY COLUMN `sales_return_payment_init` TEXT,
   MODIFY COLUMN `purchase_payment_init` TEXT,
   MODIFY COLUMN `purchase_return_payment_init` TEXT,
   MODIFY COLUMN `expense_payment_init` TEXT,
   MODIFY COLUMN `cust_advance_init` TEXT',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'industry_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `industry_type` VARCHAR(50) DEFAULT \'general_retail\' NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'business_model');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `business_model` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'feature_flags_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `feature_flags_json` JSON NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'workflow_template_key');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `workflow_template_key` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'dashboard_template_key');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `dashboard_template_key` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'storefront_theme_key');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `storefront_theme_key` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'label_overrides_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `label_overrides_json` JSON NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'industry_settings_json');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `industry_settings_json` JSON NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_license
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_license' AND column_name = 'installation_fingerprint');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_subscription_license` ADD COLUMN `installation_fingerprint` VARCHAR(255) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_units
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'parent_unit_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_units` ADD COLUMN `parent_unit_id` INT DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'conversion_factor');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_units` ADD COLUMN `conversion_factor` DECIMAL(15,6) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ========================================================================
-- Tables historically created in Updates.php
-- ========================================================================

CREATE TABLE IF NOT EXISTS db_shippingaddress (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `store_id` INT(10) DEFAULT NULL,
  `country_id` INT(10) DEFAULT NULL,
  `state_id` INT(10) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `postcode` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `status` INT(1) DEFAULT NULL,
  `customer_id` INT(10) DEFAULT NULL,
  `location_link` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `db_shippingaddress_fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `db_customers`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `db_shippingaddress_fk_store` FOREIGN KEY (`store_id`) REFERENCES `db_store`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'shippingaddress_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_customers` ADD COLUMN `shippingaddress_id` INT(10) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS db_coupons (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` double(20,2) DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `db_coupons_fk_store` FOREIGN KEY (`store_id`) REFERENCES `db_store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_customer_coupons (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `customer_id` int(10) DEFAULT NULL,
  `coupon_id` int(10) DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` double(20,2) DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `customer_id` (`customer_id`),
  KEY `coupon_id` (`coupon_id`),
  CONSTRAINT `db_customer_coupons_fk_store` FOREIGN KEY (`store_id`) REFERENCES `db_store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `db_customer_coupons_fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `db_customers`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `db_customer_coupons_fk_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `db_coupons`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_bankdetails (
  `id` INT(5) NOT NULL AUTO_INCREMENT,
  `store_id` INT(5) DEFAULT NULL,
  `country_id` INT(5) DEFAULT NULL,
  `holder_name` VARCHAR(250) DEFAULT NULL,
  `bank_name` VARCHAR(250) DEFAULT NULL,
  `branch_name` VARCHAR(250) DEFAULT NULL,
  `code` VARCHAR(250) DEFAULT NULL COMMENT 'IFSC or Bank Code',
  `account_type` VARCHAR(250) DEFAULT NULL,
  `account_number` VARCHAR(250) DEFAULT NULL,
  `other_details` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` INT(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `db_bankdetails_fk_store` FOREIGN KEY (`store_id`) REFERENCES `db_store`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO db_bankdetails (`id`, `store_id`, `status`) VALUES (1, 1, 1);

CREATE TABLE IF NOT EXISTS db_fivemojo (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_id` int(5) DEFAULT NULL,
  `url` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `token` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `instance_id` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `status` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `db_fivemojo_fk_store` FOREIGN KEY (`store_id`) REFERENCES `db_store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon dependent columns on db_sales / db_salesreturn
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'coupon_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_sales` ADD COLUMN `coupon_id` INT(10) NULL, ADD FOREIGN KEY (`coupon_id`) REFERENCES `db_customer_coupons`(`id`) ON UPDATE CASCADE ON DELETE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'coupon_amt');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_sales` ADD COLUMN `coupon_amt` DOUBLE(20,2) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesreturn' AND column_name = 'coupon_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesreturn` ADD COLUMN `coupon_id` INT NULL, ADD COLUMN `coupon_amt` DOUBLE(20,4) NULL, ADD FOREIGN KEY (`coupon_id`) REFERENCES `db_customer_coupons`(`id`) ON UPDATE CASCADE ON DELETE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'coupon_amt');
SET @sql = IF(@col_exists = 0, 'SELECT 1', 'ALTER TABLE `db_sales` CHANGE `coupon_amt` `coupon_amt` DOUBLE(20,2) DEFAULT 0 NULL');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesreturn' AND column_name = 'coupon_amt');
SET @sql = IF(@col_exists = 0, 'SELECT 1', 'ALTER TABLE `db_salesreturn` CHANGE `coupon_amt` `coupon_amt` DOUBLE(20,2) DEFAULT 0 NULL');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ========================================================================
-- Remaining db_store columns that were historically added in Updates.php
-- These are required before the 4.0.3 modularization migration copies data.
-- ========================================================================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'mrp_column');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `mrp_column` INT(1) DEFAULT 0 NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'invoice_terms');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `invoice_terms` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'previous_balance_bit');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `previous_balance_bit` INT(1) DEFAULT 1 NULL COMMENT \'1=Show, 0=Hide - Shows on sales invoice\'', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 't_and_c_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `t_and_c_status` INT(1) DEFAULT 1 NULL COMMENT \'1=Show, 0=Hide - Shows on sales invoice\'', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'number_to_words');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `number_to_words` VARCHAR(250) DEFAULT \'Default\' NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 't_and_c_status_pos');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `t_and_c_status_pos` INT(1) DEFAULT 1 NULL AFTER `t_and_c_status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'qty_decimals');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `qty_decimals` INT(5) DEFAULT 2 NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'signature');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `signature` TEXT NULL AFTER `qty_decimals`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'show_signature');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `show_signature` INT(1) DEFAULT 0 NULL AFTER `signature`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_store' AND column_name = 'default_account_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_store` ADD COLUMN `default_account_id` INT(10) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ========================================================================
-- Update version marker
-- ========================================================================
UPDATE db_sitesettings SET version = '4.0.2' WHERE id = 1;

SET FOREIGN_KEY_CHECKS = 1;
