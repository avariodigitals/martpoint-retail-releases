<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_deplete_recipe_on_sale extends CI_Migration {

    public function up() {
        if (!$this->db->field_exists('deplete_recipe_on_sale', 'db_items')) {
            $this->dbforge->add_column('db_items', [
                'deplete_recipe_on_sale' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'recipe_margin_pct',
                    'comment'    => 'Deduct recipe ingredients directly at POS sale (1 = yes)'
                ],
            ]);
        }
    }

    public function down() {
        if ($this->db->field_exists('deplete_recipe_on_sale', 'db_items')) {
            $this->dbforge->drop_column('db_items', 'deplete_recipe_on_sale');
        }
    }
}
