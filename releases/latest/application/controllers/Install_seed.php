<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Post-Installation Seeder
 * Runs once after fresh installation to create default roles,
 * permissions, and expense categories.
 * 
 * Does NOT require authentication.
 * Safe to re-run (idempotent via model checks).
 */
class Install_seed extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('business_profile');
        $this->load->model('default_data_model', 'seeder');
        $this->load->model('business_profile_model', 'bp_model');
    }

    /**
     * Run default data seeding for all retail stores
     * then show the welcome celebration page
     */
    public function index() {
        // Business type chosen in the installer (backwards compatible if not set)
        $industry_type  = $this->input->get('industry_type', TRUE) ?: 'general_retail';
        $business_model = $this->input->get('business_model', TRUE) ?: '';

        // Seed all non-admin stores (store_id > 1)
        $stores = $this->db->where('id >', 1)->get('db_store')->result();

        foreach ($stores as $store) {
            $this->seeder->seed_store_defaults($store->id);
            $this->_apply_business_profile($store->id, $industry_type, $business_model);
        }

        // Mark installation as complete so the public entry point never
        // accidentally redirects back into the installer.
        $lock_file = APPPATH . 'config/installed.lock';
        if (!file_exists($lock_file)) {
            $lock_content = date('Y-m-d H:i:s') . ' | MartPoint ' . app_version() . PHP_EOL;
            @file_put_contents($lock_file, $lock_content);
        }

        // Show beautiful welcome celebration page
        $this->load->view('install_welcome');
    }

    /**
     * Apply the business-type preset to a store.
     * Falls back to general_retail if an invalid type is supplied.
     */
    private function _apply_business_profile($store_id, $industry_type, $business_model = '') {
        $presets = mp_get_business_presets();
        $industry_type = (isset($presets[$industry_type])) ? $industry_type : 'general_retail';
        $preset = $presets[$industry_type];

        $business_models = mp_get_business_models();
        if (empty($business_model) || !isset($business_models[$business_model])) {
            $business_model = $preset['business_model'] ?? 'product_based';
        }

        $feature_flags = [];
        foreach (mp_get_feature_flags() as $key => $label) {
            $feature_flags[$key] = (isset($preset['features']) && in_array($key, $preset['features'], true)) ? '1' : '0';
        }

        $data = [
            'industry_type'         => $industry_type,
            'business_model'        => $business_model,
            'workflow_template_key' => $preset['workflow_template'] ?? 'retail_standard',
            'dashboard_template_key'=> $preset['dashboard_template'] ?? 'general_retail',
            'storefront_theme_key'  => $preset['theme_key'] ?? 'general_retail',
            'feature_flags_json'    => json_encode($feature_flags),
            'label_overrides_json'  => !empty($preset['labels']) ? json_encode($preset['labels']) : null,
            'industry_settings_json'=> null,
        ];

        $this->bp_model->update_profile($store_id, $data);
    }
}
