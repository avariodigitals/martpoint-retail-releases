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
        $this->load->model('default_data_model', 'seeder');
    }

    /**
     * Run default data seeding for all retail stores
     * then show the welcome celebration page
     */
    public function index() {
        // Seed all non-admin stores (store_id > 1)
        $stores = $this->db->where('id >', 1)->get('db_store')->result();

        foreach ($stores as $store) {
            $this->seeder->seed_store_defaults($store->id);
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
}
