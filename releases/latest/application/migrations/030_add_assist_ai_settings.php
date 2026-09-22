<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_assist_ai_settings extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_sitesettings')) return;

        $columns = [
            'assist_ai_enabled'  => ['type' => 'TINYINT', 'constraint' => 1, 'null' => FALSE, 'default' => 0],
            'assist_ai_provider' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => FALSE, 'default' => 'groq'],
            'assist_ai_endpoint' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
            'assist_ai_model'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
            'assist_ai_key'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
        ];
        foreach ($columns as $name => $def) {
            if (!$this->db->field_exists($name, 'db_sitesettings')) {
                $this->dbforge->add_column('db_sitesettings', [$name => $def]);
            }
        }
    }

    public function down() {
        foreach (['assist_ai_enabled','assist_ai_provider','assist_ai_endpoint','assist_ai_model','assist_ai_key'] as $col) {
            if ($this->db->field_exists($col, 'db_sitesettings')) {
                $this->dbforge->drop_column('db_sitesettings', $col);
            }
        }
    }
}
