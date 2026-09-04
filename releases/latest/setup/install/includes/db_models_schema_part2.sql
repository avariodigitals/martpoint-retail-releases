
-- --------------------------------------------------------
-- Delivery Tables
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Membership Tables
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Custom Orders Tables
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Treatment Notes Tables
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Email & Report Tables
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Debt Reminder Tables
-- --------------------------------------------------------

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
