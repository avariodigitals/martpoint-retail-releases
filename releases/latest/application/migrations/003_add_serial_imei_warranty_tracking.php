<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_serial_imei_warranty_tracking extends CI_Migration {

    public function up() {
        // Add tracking fields to items
        if (!$this->db->field_exists('serial_number', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'serial_number' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true, 'after' => 'custom_barcode'],
                'imei_number' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'after' => 'serial_number'],
                'warranty_months' => ['type' => 'INT', 'constraint' => 3, 'null' => true, 'default' => 0, 'after' => 'imei_number'],
            ]);
        }

        // Add sold tracking fields to sales items
        if (!$this->db->field_exists('sold_serial_number', 'db_salesitems')) {
            $this->dbforge->add_column('db_salesitems', [
                'sold_serial_number' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true, 'after' => 'item_id'],
                'sold_imei_number' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'after' => 'sold_serial_number'],
            ]);
        }

        // Add sold tracking to purchase items (for returns)
        if (!$this->db->field_exists('sold_serial_number', 'db_purchaseitems')) {
            $this->dbforge->add_column('db_purchaseitems', [
                'sold_serial_number' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true, 'after' => 'item_id'],
                'sold_imei_number' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'after' => 'sold_serial_number'],
            ]);
        }

        // Add sold tracking to hold items
        if (!$this->db->field_exists('sold_serial_number', 'db_holditems')) {
            $this->dbforge->add_column('db_holditems', [
                'sold_serial_number' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true, 'after' => 'item_id'],
                'sold_imei_number' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true, 'after' => 'sold_serial_number'],
            ]);
        }
    }

    public function down() {
        $this->dbforge->drop_column('db_items', 'serial_number');
        $this->dbforge->drop_column('db_items', 'imei_number');
        $this->dbforge->drop_column('db_items', 'warranty_months');
        $this->dbforge->drop_column('db_salesitems', 'sold_serial_number');
        $this->dbforge->drop_column('db_salesitems', 'sold_imei_number');
        $this->dbforge->drop_column('db_purchaseitems', 'sold_serial_number');
        $this->dbforge->drop_column('db_purchaseitems', 'sold_imei_number');
        $this->dbforge->drop_column('db_holditems', 'sold_serial_number');
        $this->dbforge->drop_column('db_holditems', 'sold_imei_number');
    }
}
