<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_laundry_service_type_to_items extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('laundry_service_type', 'db_items')) {
            $fields = [
                'laundry_service_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'description',
                    'comment'    => 'wash_iron, wash_only, iron_only, dry_clean'
                ],
            ];
            $this->dbforge->add_column('db_items', $fields);
        }
    }

    public function down() {
        if ($this->db->field_exists('laundry_service_type', 'db_items')) {
            $this->dbforge->drop_column('db_items', 'laundry_service_type');
        }
    }
}
