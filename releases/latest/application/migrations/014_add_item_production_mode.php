<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_item_production_mode extends CI_Migration {

    public function up() {
        if ($this->db->field_exists('deplete_recipe_on_sale', 'db_items')) {
            $this->dbforge->drop_column('db_items', 'deplete_recipe_on_sale');
        }
        if (!$this->db->field_exists('item_production_mode', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'item_production_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'batch',
                    'null'       => false,
                    'after'      => 'recipe_margin_pct',
                    'comment'    => 'batch | sale_deplete | component'
                ],
            ]);
        }
    }

    public function down() {
        if ($this->db->field_exists('item_production_mode', 'db_items')) {
            $this->dbforge->drop_column('db_items', 'item_production_mode');
        }
    }
}
