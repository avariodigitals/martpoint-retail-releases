<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_default_flag_to_units_and_brands extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('is_default', 'db_units')) {
            $this->dbforge->add_column('db_units', [
                'is_default' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'status',
                    'comment'    => '1 = default unit for new items (one per store)'
                ],
            ]);
        }
        if (!$this->db->field_exists('is_default', 'db_brands')) {
            $this->dbforge->add_column('db_brands', [
                'is_default' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'status',
                    'comment'    => '1 = default brand for new items (one per store)'
                ],
            ]);
        }
    }

    public function down() {
        if ($this->db->field_exists('is_default', 'db_units')) {
            $this->dbforge->drop_column('db_units', 'is_default');
        }
        if ($this->db->field_exists('is_default', 'db_brands')) {
            $this->dbforge->drop_column('db_brands', 'is_default');
        }
    }
}
