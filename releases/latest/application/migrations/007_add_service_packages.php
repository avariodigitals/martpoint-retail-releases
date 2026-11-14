<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_service_packages extends CI_Migration {

    public function up() {
        // 1. Add package_bit to db_items so packages appear in the catalogue
        if (!$this->db->field_exists('package_bit', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'package_bit' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'service_bit',
                    'comment'    => '0=normal item, 1=service package'
                ],
            ]);
        }

        // 2. Package definitions
        if (!$this->db->table_exists('db_service_packages')) {
            $this->db->query("
                CREATE TABLE `db_service_packages` (
                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `store_id` int(11) NOT NULL,
                  `package_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                  `description` text COLLATE utf8mb4_unicode_ci,
                  `package_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `pricing_model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed' COMMENT 'fixed|calculated',
                  `package_price` decimal(18,2) NOT NULL DEFAULT 0.00,
                  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'fixed|percentage',
                  `discount` decimal(18,2) DEFAULT 0.00,
                  `redemption_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single' COMMENT 'single|multi',
                  `expiry_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|days|date',
                  `expiry_days` int(11) DEFAULT 0,
                  `expiry_date` date DEFAULT NULL,
                  `status` tinyint(1) NOT NULL DEFAULT 1,
                  `created_date` date DEFAULT NULL,
                  `created_time` time DEFAULT NULL,
                  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `system_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `store_id` (`store_id`),
                  KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. Package items (services/products included in a package)
        if (!$this->db->table_exists('db_service_package_items')) {
            $this->db->query("
                CREATE TABLE `db_service_package_items` (
                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `package_id` int(11) UNSIGNED NOT NULL,
                  `item_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'service' COMMENT 'service|product',
                  `item_id` int(11) NOT NULL,
                  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
                  `sort_order` int(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`),
                  KEY `package_id` (`package_id`),
                  KEY `item_id` (`item_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 4. Customer packages (sold packages)
        if (!$this->db->table_exists('db_customer_packages')) {
            $this->db->query("
                CREATE TABLE `db_customer_packages` (
                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `store_id` int(11) NOT NULL,
                  `customer_id` int(11) NOT NULL,
                  `package_id` int(11) UNSIGNED NOT NULL,
                  `sale_id` int(11) UNSIGNED DEFAULT NULL,
                  `sale_items_id` int(11) UNSIGNED DEFAULT NULL,
                  `package_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `total_uses` int(11) NOT NULL DEFAULT 1,
                  `remaining_uses` int(11) NOT NULL DEFAULT 1,
                  `expiry_date` date DEFAULT NULL,
                  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active|expired|fully_redeemed|cancelled',
                  `created_date` date DEFAULT NULL,
                  `created_time` time DEFAULT NULL,
                  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `store_id` (`store_id`),
                  KEY `customer_id` (`customer_id`),
                  KEY `package_id` (`package_id`),
                  KEY `sale_id` (`sale_id`),
                  KEY `status` (`status`),
                  KEY `expiry_date` (`expiry_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 5. Package redemptions
        if (!$this->db->table_exists('db_customer_package_redemptions')) {
            $this->db->query("
                CREATE TABLE `db_customer_package_redemptions` (
                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `customer_package_id` int(11) UNSIGNED NOT NULL,
                  `item_id` int(11) NOT NULL,
                  `quantity_redeemed` decimal(10,2) NOT NULL DEFAULT 1.00,
                  `service_order_id` int(11) UNSIGNED DEFAULT NULL,
                  `sale_id` int(11) UNSIGNED DEFAULT NULL,
                  `notes` text COLLATE utf8mb4_unicode_ci,
                  `redeemed_at` datetime DEFAULT NULL,
                  `redeemed_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `customer_package_id` (`customer_package_id`),
                  KEY `item_id` (`item_id`),
                  KEY `service_order_id` (`service_order_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down() {
        $this->dbforge->drop_column('db_items', 'package_bit');
        $this->dbforge->drop_table('db_customer_package_redemptions', true);
        $this->dbforge->drop_table('db_customer_packages', true);
        $this->dbforge->drop_table('db_service_package_items', true);
        $this->dbforge->drop_table('db_service_packages', true);
    }
}
