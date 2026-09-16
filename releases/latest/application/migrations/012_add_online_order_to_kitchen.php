<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_online_order_to_kitchen extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return;
        }
        if (!$this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
            $this->dbforge->add_column('db_kitchen_orders', [
                'online_order_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'sales_id',
                    'comment'    => 'Linked db_online_orders.id for table QR orders'
                ],
            ]);
        }
    }

    public function down() {
        if ($this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
            $this->dbforge->drop_column('db_kitchen_orders', 'online_order_id');
        }
    }
}