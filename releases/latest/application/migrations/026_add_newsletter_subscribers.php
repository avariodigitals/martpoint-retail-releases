<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_newsletter_subscribers extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_newsletter_subscribers')) {
            $this->dbforge->add_field([
                'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'email'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'source'          => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'newsletter', 'null' => FALSE, 'comment' => 'newsletter | footer | other'],
                'ip_address'      => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => TRUE],
                'status'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => FALSE],
                'created_at'      => ['type' => 'DATETIME', 'null' => FALSE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('store_id');
            $this->dbforge->create_table('db_newsletter_subscribers', TRUE, ['ENGINE' => 'InnoDB']);
            // one row per email per store
            $this->db->query("ALTER TABLE db_newsletter_subscribers ADD UNIQUE KEY uk_store_email (store_id, email)");
        }
    }

    public function down() {
        if ($this->db->table_exists('db_newsletter_subscribers')) {
            $this->dbforge->drop_table('db_newsletter_subscribers');
        }
    }
}
