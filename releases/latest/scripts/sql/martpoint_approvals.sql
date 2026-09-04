-- MartPoint Retail Approval System Tables
-- Run this SQL to create approval tables

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

-- Add approval PIN to users table
CALL mp_add_column_if_not_exists('db_users', 'approval_pin', 'VARCHAR(64) NULL DEFAULT NULL');
DROP PROCEDURE IF EXISTS mp_add_column_if_not_exists;

CREATE TABLE IF NOT EXISTS db_approval_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  
  -- Global switch
  approval_system_enabled TINYINT(1) NOT NULL DEFAULT 0,
  business_control_mode ENUM('simple','controlled') NOT NULL DEFAULT 'simple',
  allow_self_approval TINYINT(1) NOT NULL DEFAULT 0,
  
  -- Approval method per type: none, manager_pin, manager_password, owner_pin, owner_password, either
  discount_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  discount_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  discount_limit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  
  price_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  price_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  void_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  void_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  sale_return_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  sale_return_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  edit_completed_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  edit_completed_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  credit_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  credit_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  credit_limit_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  credit_limit_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  customer_balance_adjustment_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  customer_balance_adjustment_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  negative_stock_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  negative_stock_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  stock_adjustment_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  stock_adjustment_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  inventory_transfer_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  inventory_transfer_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  cost_price_change_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cost_price_change_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  selling_price_change_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  selling_price_change_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  expense_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  expense_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  expense_threshold DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  
  cash_variance_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cash_variance_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  reopen_shift_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  reopen_shift_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  online_refund_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  online_refund_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  cancel_online_order_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cancel_online_order_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  manual_payment_confirmation_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  manual_payment_confirmation_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  purchase_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  purchase_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  purchase_price_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  purchase_price_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',

  hold_delete_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  hold_delete_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  UNIQUE KEY unique_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_approval_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  branch_id INT NULL,
  
  action_type VARCHAR(50) NOT NULL,
  approval_type VARCHAR(50) NOT NULL,
  
  requesting_user_id INT NOT NULL,
  requesting_user_name VARCHAR(100) NULL,
  approving_user_id INT NULL,
  approving_user_name VARCHAR(100) NULL,
  
  reason TEXT NULL,
  previous_value TEXT NULL,
  new_value TEXT NULL,
  
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  approval_method_used VARCHAR(30) NULL,
  
  amount DECIMAL(15,2) NULL,
  threshold DECIMAL(15,2) NULL,
  
  device_info VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL,
  
  KEY idx_store (store_id),
  KEY idx_type (approval_type),
  KEY idx_status (status),
  KEY idx_date (created_at),
  KEY idx_requester (requesting_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
