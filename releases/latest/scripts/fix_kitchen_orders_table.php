<?php
/**
 * DB Migration: Create db_kitchen_orders table for Kitchen Display System
 * Run once: http://localhost/martpoint-retail/fix_kitchen_orders_table.php
 */
define('BASEPATH', true);
require_once 'application/config/database.php';

$db_config = $db['default'];
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS db_kitchen_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_id INT NOT NULL,
    store_id INT NOT NULL,
    kds_status ENUM('new','preparing','ready','served') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sales (sales_id),
    KEY idx_store_status (store_id, kds_status),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "SUCCESS: db_kitchen_orders table created or already exists.\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

$mysqli->close();
