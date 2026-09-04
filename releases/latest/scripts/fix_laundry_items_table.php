<?php
// Migration: Add per-item laundry tracking and service type to items
require_once 'index.php';
$ci =& get_instance();
$ci->load->database();

// 1. Add laundry_service_type to db_items
if (!$ci->db->field_exists('laundry_service_type', 'db_items')) {
    $ci->db->query("ALTER TABLE db_items ADD COLUMN laundry_service_type VARCHAR(20) NULL COMMENT 'wash_only, iron_only, wash_iron, dry_clean' AFTER item_name");
    echo "Added laundry_service_type to db_items\n";
}

// 2. Create db_laundry_order_items for per-item status tracking
if (!$ci->db->table_exists('db_laundry_order_items')) {
    $ci->db->query("CREATE TABLE db_laundry_order_items (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Created db_laundry_order_items table\n";
}

echo "Done.\n";
