<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_meat_frozen_item_fields extends CI_Migration {

    public function up() {
        $fields = [
            'carcass_template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'item_production_mode',
                'comment'    => 'Link to db_item_carcass_templates (butchery workflow)'
            ],
            'is_carcass' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'carcass_template_id',
                'comment'    => 'This item is a whole carcass before cutting'
            ],
            'portion_of_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'is_carcass',
                'comment'    => 'Parent item this cut/portion was produced from'
            ],
            'storage_temp_min' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => null,
                'null'       => true,
                'after'      => 'portion_of_item_id',
                'comment'    => 'Minimum storage temperature in Celsius'
            ],
            'storage_temp_max' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => null,
                'null'       => true,
                'after'      => 'storage_temp_min',
                'comment'    => 'Maximum storage temperature in Celsius'
            ],
            'thaw_time_hours' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'storage_temp_max',
                'comment'    => 'Hours required to thaw safely'
            ],
            'use_within_hours_after_thaw' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'thaw_time_hours',
                'comment'    => 'Shelf life in hours after thawing'
            ],
            'frozen_shelf_life_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'use_within_hours_after_thaw',
                'comment'    => 'Shelf life in days while frozen'
            ],
        ];

        foreach ($fields as $column => $definition) {
            if (!$this->db->field_exists($column, 'db_items')) {
                $this->dbforge->add_column('db_items', [$column => $definition]);
            }
        }
    }

    public function down() {
        $columns = [
            'carcass_template_id',
            'is_carcass',
            'portion_of_item_id',
            'storage_temp_min',
            'storage_temp_max',
            'thaw_time_hours',
            'use_within_hours_after_thaw',
            'frozen_shelf_life_days',
        ];
        foreach ($columns as $column) {
            if ($this->db->field_exists($column, 'db_items')) {
                $this->dbforge->drop_column('db_items', $column);
            }
        }
    }
}
