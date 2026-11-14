<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_customer_notes extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_customer_notes')) {
            $this->dbforge->add_field([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => TRUE],
                'store_id'      => ['type' => 'INT', 'constraint' => 11, 'null' => FALSE, 'default' => 1],
                'customer_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => FALSE],
                'note'          => ['type' => 'TEXT', 'null' => FALSE],
                'created_by'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
                'created_by_id' => ['type' => 'INT', 'constraint' => 11, 'null' => TRUE],
                'created_date'  => ['type' => 'DATE', 'null' => TRUE],
                'created_time'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE],
                'created_at'    => ['type' => 'DATETIME', 'null' => TRUE],
                'updated_at'    => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('store_id');
            $this->dbforge->add_key('customer_id');
            $this->dbforge->create_table('db_customer_notes', TRUE, ['ENGINE' => 'InnoDB']);
        }

        // Seed the legacy single notes blob into history (only once per note text)
        if ($this->db->table_exists('db_customers') && $this->db->field_exists('notes', 'db_customers')) {
            $this->db->query("
                INSERT INTO db_customer_notes (store_id, customer_id, note, created_by, created_by_id, created_date, created_time)
                SELECT c.store_id, c.id, c.notes, c.created_by, NULL, c.created_date, c.created_time
                FROM db_customers c
                WHERE c.notes IS NOT NULL AND TRIM(c.notes) <> ''
                  AND NOT EXISTS (
                      SELECT 1 FROM db_customer_notes n
                      WHERE n.customer_id = c.id AND n.note = c.notes
                  )
            ");
        }
    }

    public function down() {
        if ($this->db->table_exists('db_customer_notes')) {
            $this->dbforge->drop_table('db_customer_notes');
        }
    }
}
