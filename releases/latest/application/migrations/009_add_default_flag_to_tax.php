<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_default_flag_to_tax extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('is_default', 'db_tax')) {
            $fields = [
                'is_default' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'status',
                    'comment'    => '1 = default tax for new items (one per store)'
                ],
            ];
            $this->dbforge->add_column('db_tax', $fields);
        }
    }

    public function down() {
        if ($this->db->field_exists('is_default', 'db_tax')) {
            $this->dbforge->drop_column('db_tax', 'is_default');
        }
    }
}
