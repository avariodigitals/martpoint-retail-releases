<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_table_id_to_sales extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('table_id', 'db_sales')) {
            $this->dbforge->add_column('db_sales', [
                'table_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'customer_id',
                    'comment'    => 'Restaurant table id (0 = no table)'
                ],
            ]);
        }
        if (!$this->db->field_exists('table_id', 'db_kitchen_orders')) {
            $this->dbforge->add_column('db_kitchen_orders', [
                'table_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'sales_id',
                    'comment'    => 'Restaurant table id copied from sale'
                ],
            ]);
        }
    }

    public function down() {
        if ($this->db->field_exists('table_id', 'db_sales')) {
            $this->dbforge->drop_column('db_sales', 'table_id');
        }
        if ($this->db->field_exists('table_id', 'db_kitchen_orders')) {
            $this->dbforge->drop_column('db_kitchen_orders', 'table_id');
        }
    }
}
