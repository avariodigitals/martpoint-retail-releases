<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_staff_commission_fields extends CI_Migration {

    public function up() {
        // Commission configuration on services/items
        if (!$this->db->field_exists('commission_type', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'commission_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'none', 'comment' => 'none|flat|percent', 'after' => 'laundry_service_type'],
            ]);
        }
        if (!$this->db->field_exists('commission_value', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'commission_value' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'commission_type'],
            ]);
        }

        // Staff assignment + earned commission on each sale line item
        if (!$this->db->field_exists('staff_id', 'db_salesitems')) {
            $this->dbforge->add_column('db_salesitems', [
                'staff_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'barcode_id'],
            ]);
        }
        if (!$this->db->field_exists('commission_amount', 'db_salesitems')) {
            $this->dbforge->add_column('db_salesitems', [
                'commission_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'staff_id'],
            ]);
        }
        // Also add to hold items so hold invoices preserve staff assignment
        if (!$this->db->field_exists('staff_id', 'db_holditems')) {
            $this->dbforge->add_column('db_holditems', [
                'staff_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'barcode_id'],
            ]);
        }
        if (!$this->db->field_exists('commission_amount', 'db_holditems')) {
            $this->dbforge->add_column('db_holditems', [
                'commission_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'staff_id'],
            ]);
        }
    }

    public function down() {
        $this->dbforge->drop_column('db_items', 'commission_type');
        $this->dbforge->drop_column('db_items', 'commission_value');
        $this->dbforge->drop_column('db_salesitems', 'staff_id');
        $this->dbforge->drop_column('db_salesitems', 'commission_amount');
        $this->dbforge->drop_column('db_holditems', 'staff_id');
        $this->dbforge->drop_column('db_holditems', 'commission_amount');
    }
}
