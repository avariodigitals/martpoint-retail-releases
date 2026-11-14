<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_business_profile_columns extends CI_Migration {

    public function up() {
        $fields = [
            'industry_type' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'comment' => 'Business type preset key (e.g. salon_barbershop, restaurant)',
                'after' => 'store_name'
            ],
            'business_model' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'comment' => 'product_based, service_based, product_and_service',
                'after' => 'industry_type'
            ],
            'feature_flags_json' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object of enabled/disabled feature flags',
                'after' => 'business_model'
            ],
            'workflow_template_key' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'comment' => 'Selected workflow template preset key',
                'after' => 'feature_flags_json'
            ],
            'dashboard_template_key' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'comment' => 'Selected dashboard template preset key',
                'after' => 'workflow_template_key'
            ],
            'storefront_theme_key' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'comment' => 'Selected storefront theme preset key',
                'after' => 'dashboard_template_key'
            ],
            'label_overrides_json' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object of label customizations',
                'after' => 'storefront_theme_key'
            ],
            'industry_settings_json' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON object of industry-specific settings (catalogue, etc.)',
                'after' => 'label_overrides_json'
            ]
        ];

        $this->dbforge->add_column('db_store', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('db_store', 'industry_type');
        $this->dbforge->drop_column('db_store', 'business_model');
        $this->dbforge->drop_column('db_store', 'feature_flags_json');
        $this->dbforge->drop_column('db_store', 'workflow_template_key');
        $this->dbforge->drop_column('db_store', 'dashboard_template_key');
        $this->dbforge->drop_column('db_store', 'storefront_theme_key');
        $this->dbforge->drop_column('db_store', 'label_overrides_json');
        $this->dbforge->drop_column('db_store', 'industry_settings_json');
    }
}
