<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_butchery_frozen_tables extends CI_Migration {

    public function up() {
        // 1. Carcass templates — reusable cutting patterns (full cow, quarter, etc.)
        if (!$this->db->table_exists('db_item_carcass_templates')) {
            $this->db->query("CREATE TABLE `db_item_carcass_templates` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `template_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `template_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `source_item_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent carcass item',
                `expected_total_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
                `expected_total_yield_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `created_date` date DEFAULT NULL,
                `created_time` time DEFAULT NULL,
                `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `source_item_id` (`source_item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 2. Expected cuts for each template
        if (!$this->db->table_exists('db_item_carcass_template_cuts')) {
            $this->db->query("CREATE TABLE `db_item_carcass_template_cuts` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `template_id` int(11) UNSIGNED NOT NULL,
                `cut_item_id` int(11) NOT NULL DEFAULT 0,
                `cut_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `expected_qty` decimal(10,2) NOT NULL DEFAULT 1.00,
                `expected_weight_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
                `expected_weight_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `template_id` (`template_id`),
                KEY `cut_item_id` (`cut_item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 3. Carcass share reservations (cow sharing / group buy)
        if (!$this->db->table_exists('db_carcass_shares')) {
            $this->db->query("CREATE TABLE `db_carcass_shares` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `carcass_item_id` int(11) NOT NULL DEFAULT 0,
                `batch_id` int(11) UNSIGNED DEFAULT NULL,
                `customer_id` int(11) DEFAULT NULL,
                `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `share_fraction` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1/4' COMMENT '1/4, 1/8, custom',
                `reserved_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
                `reserved_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
                `deposit_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
                `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved' COMMENT 'reserved|paid|cut|delivered|cancelled',
                `created_date` date DEFAULT NULL,
                `created_time` time DEFAULT NULL,
                `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `carcass_item_id` (`carcass_item_id`),
                KEY `customer_id` (`customer_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 4. Freezer locations / zones
        if (!$this->db->table_exists('db_freezer_locations')) {
            $this->db->query("CREATE TABLE `db_freezer_locations` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `location_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `location_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `temp_min` decimal(5,2) DEFAULT NULL,
                `temp_max` decimal(5,2) DEFAULT NULL,
                `capacity_volume` decimal(10,2) NOT NULL DEFAULT 0.00,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `created_date` date DEFAULT NULL,
                `created_time` time DEFAULT NULL,
                `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 5. Received carcasses (actual animals received for cutting)
        if (!$this->db->table_exists('db_received_carcasses')) {
            $this->db->query("CREATE TABLE `db_received_carcasses` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `lot_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `carcass_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `receiving_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
                `slaughter_date` date DEFAULT NULL,
                `expected_yield_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
                `freezer_location_id` int(11) UNSIGNED DEFAULT NULL,
                `carcass_template_id` int(11) UNSIGNED DEFAULT NULL,
                `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received' COMMENT 'received|cutting|completed|written_off',
                `notes` text COLLATE utf8mb4_unicode_ci,
                `created_date` date DEFAULT NULL,
                `created_time` time DEFAULT NULL,
                `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `status` (`status`),
                KEY `carcass_template_id` (`carcass_template_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 6. Actual cut records per received carcass
        if (!$this->db->table_exists('db_carcass_cut_records')) {
            $this->db->query("CREATE TABLE `db_carcass_cut_records` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `received_carcass_id` int(11) UNSIGNED NOT NULL,
                `cut_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `expected_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
                `actual_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
                `packs` int(11) NOT NULL DEFAULT 1,
                `waste` decimal(10,2) NOT NULL DEFAULT 0.00,
                `notes` text COLLATE utf8mb4_unicode_ci,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `received_carcass_id` (`received_carcass_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }

        // 7. Temperature log entries
        if (!$this->db->table_exists('db_temperature_logs')) {
            $this->db->query("CREATE TABLE `db_temperature_logs` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `freezer_location_id` int(11) UNSIGNED NOT NULL,
                `recorded_at` datetime NOT NULL,
                `temperature_c` decimal(5,2) NOT NULL,
                `recorded_by` int(11) DEFAULT NULL,
                `notes` text COLLATE utf8mb4_unicode_ci,
                `alert_sent` tinyint(1) NOT NULL DEFAULT 0,
                `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'normal|warning|critical',
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `freezer_location_id` (`freezer_location_id`),
                KEY `recorded_at` (`recorded_at`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    }

    public function down() {
        $this->dbforge->drop_table('db_temperature_logs', true);
        $this->dbforge->drop_table('db_freezer_locations', true);
        $this->dbforge->drop_table('db_carcass_cut_records', true);
        $this->dbforge->drop_table('db_received_carcasses', true);
        $this->dbforge->drop_table('db_carcass_shares', true);
        $this->dbforge->drop_table('db_item_carcass_template_cuts', true);
        $this->dbforge->drop_table('db_item_carcass_templates', true);
    }
}
