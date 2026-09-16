<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_vehicle_module extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_vehicles')) {
            $this->db->query("CREATE TABLE `db_vehicles` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `store_id` int(11) NOT NULL,
                `vehicle_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `make` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `model` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `year` int(4) DEFAULT NULL,
                `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `mileage` int(11) DEFAULT NULL COMMENT 'Kilometers',
                `fuel_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `transmission` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `vehicle_condition` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'used' COMMENT 'new|used',
                `vin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `license_plate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `description` text COLLATE utf8mb4_unicode_ci,
                `price` decimal(18,2) NOT NULL DEFAULT 0.00,
                `cost` decimal(18,2) NOT NULL DEFAULT 0.00,
                `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available' COMMENT 'available|reserved|sold',
                `customer_id` int(11) DEFAULT NULL,
                `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
                `created_date` date DEFAULT NULL,
                `created_time` time DEFAULT NULL,
                `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `store_id` (`store_id`),
                KEY `status` (`status`),
                KEY `customer_id` (`customer_id`),
                KEY `make_model` (`make`,`model`),
                KEY `vehicle_code` (`vehicle_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    }

    public function down() {
        $this->dbforge->drop_table('db_vehicles', true);
    }
}
