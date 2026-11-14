<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_missing_vehicle_spec_fields extends CI_Migration {

    public function up() {
        if ($this->db->table_exists('db_vehicles')) {
            $cols = [
                ['name' => 'body_type',          'def' => "VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL"],
                ['name' => 'engine_capacity',    'def' => "VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL"],
                ['name' => 'drivetrain',         'def' => "VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL"],
                ['name' => 'trim_level',         'def' => "VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL"],
                ['name' => 'number_of_owners',   'def' => "INT(11) DEFAULT 0"],
                ['name' => 'registration_date',  'def' => "DATE DEFAULT NULL"],
            ];
            foreach ($cols as $c) {
                if (!$this->db->field_exists($c['name'], 'db_vehicles')) {
                    $this->db->query("ALTER TABLE `db_vehicles` ADD COLUMN `{$c['name']}` {$c['def']}");
                }
            }
        }
    }

    public function down() {
        $cols = ['body_type','engine_capacity','drivetrain','trim_level','number_of_owners','registration_date'];
        foreach ($cols as $col) {
            if ($this->db->field_exists($col, 'db_vehicles')) {
                $this->dbforge->drop_column('db_vehicles', $col);
            }
        }
    }
}
