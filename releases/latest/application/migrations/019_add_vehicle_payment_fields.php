<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_vehicle_payment_fields extends CI_Migration {

    public function up() {
        if ($this->db->table_exists('db_vehicles')) {
            if (!$this->db->field_exists('sold_date', 'db_vehicles')) {
                $this->db->query("ALTER TABLE `db_vehicles` ADD COLUMN `sold_date` datetime DEFAULT NULL AFTER `customer_name`");
            }
            if (!$this->db->field_exists('amount_paid', 'db_vehicles')) {
                $this->db->query("ALTER TABLE `db_vehicles` ADD COLUMN `amount_paid` decimal(18,2) NOT NULL DEFAULT 0.00 AFTER `sold_date`");
            }
            if (!$this->db->field_exists('payment_method', 'db_vehicles')) {
                $this->db->query("ALTER TABLE `db_vehicles` ADD COLUMN `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `amount_paid`");
            }
            if (!$this->db->field_exists('sold_by', 'db_vehicles')) {
                $this->db->query("ALTER TABLE `db_vehicles` ADD COLUMN `sold_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `payment_method`");
            }
        }
    }

    public function down() {
        $columns = ['sold_date','amount_paid','payment_method','sold_by'];
        foreach ($columns as $col) {
            if ($this->db->field_exists($col, 'db_vehicles')) {
                $this->dbforge->drop_column('db_vehicles', $col);
            }
        }
    }
}
