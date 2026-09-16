<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_leads extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_leads')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'name'            => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => FALSE],
                'phone'           => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => TRUE],
                'email'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
                'source'          => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'manual', 'null' => FALSE, 'comment' => 'manual | storefront | walk_in | referral | whatsapp | phone | other'],
                'status'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'new', 'null' => FALSE, 'comment' => 'new | contacted | qualified | converted | lost'],
                'interest'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'comment' => 'What the lead is interested in'],
                'notes'           => ['type' => 'TEXT', 'null' => TRUE],
                'assigned_to'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'converted_customer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'converted_at'    => ['type' => 'DATETIME', 'null' => TRUE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
                'updated_at'      => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('store_id');
            $this->dbforge->add_key('converted_customer_id');
            $this->dbforge->create_table('db_leads', TRUE, ['ENGINE' => 'InnoDB']);
            $this->db->query("ALTER TABLE db_leads ADD KEY idx_store_status (store_id, status)");
        }
    }

    public function down() {
        if ($this->db->table_exists('db_leads')) {
            $this->dbforge->drop_table('db_leads');
        }
    }
}
