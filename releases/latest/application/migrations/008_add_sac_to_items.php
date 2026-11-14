<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sac_to_items extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('sac', 'db_items')) {
            $fields = [
                'sac' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'hsn',
                    'comment'    => 'Service Accounting Code (service-only)'
                ],
            ];
            $this->dbforge->add_column('db_items', $fields);
        }
    }

    public function down() {
        if ($this->db->field_exists('sac', 'db_items')) {
            $this->dbforge->drop_column('db_items', 'sac');
        }
    }
}
