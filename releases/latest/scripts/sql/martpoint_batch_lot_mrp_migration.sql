-- MartPoint Migration: Batch/Lot + Multiple Barcodes + Retail/Wholesale Price
-- Run this against your database
-- Note: This uses a stored procedure with error handler to safely add columns
-- without needing INFORMATION_SCHEMA access

DELIMITER $$

DROP PROCEDURE IF EXISTS safe_add_column$$

CREATE PROCEDURE safe_add_column(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    DECLARE CONTINUE HANDLER FOR 1060 BEGIN END;
    SET @sql = CONCAT('ALTER TABLE ', tbl, ' ADD COLUMN ', col, ' ', def);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

-- 1. Add batch_lot to db_items
CALL safe_add_column('db_items', 'batch_lot', 'VARCHAR(100) NULL AFTER mrp');

-- 2. Add batch_lot to db_purchaseitems
CALL safe_add_column('db_purchaseitems', 'batch_lot', 'VARCHAR(100) NULL AFTER description');

-- 3. Add batch_lot and price_type to db_salesitems
CALL safe_add_column('db_salesitems', 'batch_lot', 'VARCHAR(100) NULL AFTER description');
CALL safe_add_column('db_salesitems', 'price_type', "VARCHAR(20) NULL DEFAULT 'wholesale' AFTER batch_lot");

-- 4. Add batch_lot and price_type to db_holditems
CALL safe_add_column('db_holditems', 'batch_lot', 'VARCHAR(100) NULL AFTER description');
CALL safe_add_column('db_holditems', 'price_type', "VARCHAR(20) NULL DEFAULT 'wholesale' AFTER batch_lot");

-- 5. Create db_item_barcodes table for multiple barcodes/batches per item
CREATE TABLE IF NOT EXISTS db_item_barcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    barcode VARCHAR(100) NOT NULL DEFAULT '',
    batch_lot VARCHAR(100) NULL,
    purchase_price DECIMAL(20,2) DEFAULT 0,
    sales_price DECIMAL(20,2) DEFAULT 0,
    mrp DECIMAL(20,2) DEFAULT 0,
    qty DECIMAL(20,2) DEFAULT 0,
    expire_date DATE NULL,
    mfg_date DATE NULL,
    warehouse_id INT NULL,
    status TINYINT DEFAULT 1,
    created_date DATE,
    created_time TIME,
    INDEX idx_barcode (barcode),
    INDEX idx_item_id (item_id),
    INDEX idx_batch_lot (batch_lot),
    FOREIGN KEY (item_id) REFERENCES db_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add expire_date and mfg_date to existing db_item_barcodes if not present
CALL safe_add_column('db_item_barcodes', 'expire_date', 'DATE NULL AFTER qty');
CALL safe_add_column('db_item_barcodes', 'mfg_date', 'DATE NULL AFTER expire_date');

-- 5. Migrate existing custom_barcode values into db_item_barcodes
INSERT INTO db_item_barcodes (item_id, barcode, batch_lot, purchase_price, sales_price, mrp, qty, expire_date, mfg_date, status, created_date, created_time)
SELECT id, custom_barcode, batch_lot, purchase_price, sales_price, mrp, stock, expire_date, mfg_date, 1, CURDATE(), CURTIME()
FROM db_items
WHERE custom_barcode IS NOT NULL AND custom_barcode != ''
ON DUPLICATE KEY UPDATE barcode = VALUES(barcode);

-- Clean up stored procedure
DROP PROCEDURE IF EXISTS safe_add_column;
