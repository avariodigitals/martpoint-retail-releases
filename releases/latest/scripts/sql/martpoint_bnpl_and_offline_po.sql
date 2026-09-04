-- MartPoint BNPL + Offline PO Database Migration
-- Run this SQL to create the required tables
-- SAFE: Uses IF NOT EXISTS to avoid breaking existing data

DELIMITER $$
DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists$$
CREATE PROCEDURE mp_add_column_if_not_exists(
    IN pTable VARCHAR(64),
    IN pColumn VARCHAR(64),
    IN pDefinition VARCHAR(255)
)
BEGIN
    DECLARE colCount INT;
    SELECT COUNT(*) INTO colCount
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = pTable
      AND COLUMN_NAME = pColumn;
    IF colCount = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', pTable, ' ADD COLUMN ', pColumn, ' ', pDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ============================================
-- BUY NOW PAY LATER (BNPL) TABLES
-- ============================================

DROP TABLE IF EXISTS `db_installment_payments`;
DROP TABLE IF EXISTS `db_installment_plans`;

CREATE TABLE `db_installment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `sales_id` int DEFAULT NULL COMMENT 'linked sale invoice',
  `customer_id` int NOT NULL,
  `plan_code` varchar(50) NOT NULL,
  `total_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `down_payment_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `down_payment_paid` tinyint(1) NOT NULL DEFAULT '0',
  `installment_count` int NOT NULL DEFAULT '1',
  `installment_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `frequency` varchar(20) NOT NULL DEFAULT 'weekly' COMMENT 'weekly, biweekly, monthly',
  `first_due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active, completed, cancelled, defaulted',
  `late_fee_per_day` double(20,4) NOT NULL DEFAULT '0.0000',
  `total_paid` double(20,4) NOT NULL DEFAULT '0.0000',
  `total_late_fees` double(20,4) NOT NULL DEFAULT '0.0000',
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `sales_id` (`sales_id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `first_due_date` (`first_due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `db_installment_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `installment_number` int NOT NULL DEFAULT '1',
  `due_date` date NOT NULL,
  `amount_due` double(20,4) NOT NULL DEFAULT '0.0000',
  `amount_paid` double(20,4) NOT NULL DEFAULT '0.0000',
  `late_fee` double(20,4) NOT NULL DEFAULT '0.0000',
  `paid_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, paid, overdue, partial',
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_note` text,
  `payment_reference` varchar(100) DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `due_date` (`due_date`),
  KEY `status` (`status`),
  CONSTRAINT `db_installment_payments_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `db_installment_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- OFFLINE PURCHASE QUEUE (server-side backup)
-- This stores purchase data that was queued offline
-- and synced when back online
-- ============================================

CREATE TABLE IF NOT EXISTS `db_offline_purchase_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `reference_no` varchar(100) DEFAULT NULL,
  `supplier_id` int NOT NULL,
  `warehouse_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_status` varchar(50) DEFAULT 'Draft' COMMENT 'Draft, Ordered, Received',
  `subtotal` double(20,4) DEFAULT '0.0000',
  `other_charges_amt` double(20,4) DEFAULT '0.0000',
  `discount_to_all_amt` double(20,4) DEFAULT '0.0000',
  `grand_total` double(20,4) DEFAULT '0.0000',
  `paid_amount` double(20,4) DEFAULT '0.0000',
  `payment_status` varchar(50) DEFAULT 'Unpaid',
  `purchase_note` text,
  `items_json` longtext COMMENT 'serialized cart items',
  `payments_json` longtext COMMENT 'serialized payments',
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  `sync_status` varchar(20) DEFAULT 'pending' COMMENT 'pending, synced, failed',
  `synced_at` datetime DEFAULT NULL,
  `sync_error` text,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERMISSIONS: Add BNPL permissions to db_permissions table
-- Schema: id, store_id, role_id, permissions (varchar)
-- ============================================

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_plans'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_plans'
WHERE d.permissions = 'sales_view' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_payment'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_payment'
WHERE d.permissions = 'sales_payment_view' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_report'
WHERE d.permissions = 'sales_report' AND p2.id IS NULL;

-- ============================================
-- APPROVAL SETTINGS: Add BNPL approval columns (MySQL-safe)
-- ============================================
CALL mp_add_column_if_not_exists('db_approval_settings', 'bnpl_approval_enabled', "TINYINT(1) NOT NULL DEFAULT 0");
CALL mp_add_column_if_not_exists('db_approval_settings', 'bnpl_approval_method', "VARCHAR(30) NOT NULL DEFAULT 'none'");

-- ============================================
-- CUSTOMERS: Add NIN/BVN verification columns (MySQL-safe)
-- ============================================
CALL mp_add_column_if_not_exists('db_customers', 'nin_bvn', "VARCHAR(50) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_customers', 'nin_verified', "TINYINT(1) NOT NULL DEFAULT 0");
CALL mp_add_column_if_not_exists('db_customers', 'nin_verified_at', "DATETIME DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived', "TINYINT(1) NOT NULL DEFAULT 0");
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived_by', "VARCHAR(50) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_customers', 'nin_waived_at', "DATETIME DEFAULT NULL");

-- ============================================
-- STORE: Add NIN API settings columns (MySQL-safe)
-- ============================================
CALL mp_add_column_if_not_exists('db_store', 'nin_api_enabled', "TINYINT(1) NOT NULL DEFAULT 0");
CALL mp_add_column_if_not_exists('db_store', 'nin_api_url', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_store', 'nin_api_key', "VARCHAR(500) DEFAULT NULL");
CALL mp_add_column_if_not_exists('db_store', 'nin_api_provider', "VARCHAR(50) DEFAULT 'ninbvnportal'");
CALL mp_add_column_if_not_exists('db_store', 'nin_api_cost', "DECIMAL(10,2) NOT NULL DEFAULT 50.00");

-- ============================================
-- NIN VERIFICATION LOG: Track usage for billing
-- ============================================
CREATE TABLE IF NOT EXISTS db_nin_verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  user_id INT DEFAULT NULL,
  user_name VARCHAR(50) DEFAULT NULL,
  customer_id INT DEFAULT NULL,
  nin_bvn VARCHAR(50) DEFAULT NULL,
  provider VARCHAR(50) DEFAULT 'demo',
  status VARCHAR(20) DEFAULT NULL,
  response_message TEXT DEFAULT NULL,
  cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_mock TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_created (store_id, created_at),
  INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;
