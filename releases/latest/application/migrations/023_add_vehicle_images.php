<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_vehicle_images extends CI_Migration {

    public function up() {
        if (!$this->db->table_exists('db_vehicle_images')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'vehicle_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => FALSE],
                'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sort_order' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'updated_at' => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('vehicle_id');
            $this->dbforge->create_table('db_vehicle_images', TRUE);
        }

        // Migrate existing single-image vehicles into the gallery table
        if ($this->db->table_exists('db_vehicles') && $this->db->table_exists('db_vehicle_images')) {
            $existing = $this->db->where('image_path !=', '')->where('image_path IS NOT NULL')->get('db_vehicles')->result();
            foreach ($existing as $v) {
                $this->db->insert('db_vehicle_images', [
                    'vehicle_id' => $v->id,
                    'image_path' => $v->image_path,
                    'is_primary' => 1,
                    'sort_order' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down() {
        $this->dbforge->drop_table('db_vehicle_images', TRUE);
    }
}
