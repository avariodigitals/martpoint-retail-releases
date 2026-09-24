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
            $this->_apply_email_defaults($store->id);
        }

        // Create any extra staff accounts captured during installation
        $this->_create_pending_users($stores);

        // Provisioned installs: apply the per-install admin password Central
        // generated (seeded admin creds are identical on every install), then
        // remove the provision file so creds never linger in the docroot.
        $this->_apply_provision_file();

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

        // Seed industry-specific master data (units, categories, formula types)
        $this->load->model('Default_data_model', 'default_data');
        $this->default_data->seed_industry_defaults($store_id, $industry_type);
    }

    /**
     * Default every new store to the Resend email provider (credentials can be
     * overridden later under Settings → Email Settings) and install the
     * standard email templates so notifications work out of the box.
     */
    private function _apply_email_defaults($store_id) {
        // Provider: Resend. API key/sender are left blank on purpose — the
        // store admin overrides them in Email Settings.
        if ($this->db->table_exists('db_email_settings')) {
            $existing = $this->db->where('store_id', $store_id)->get('db_email_settings')->row();
            if (!$existing) {
                $store = $this->db->where('id', $store_id)->get('db_store')->row();
                $from_name = ($store && !empty($store->store_name)) ? $store->store_name : 'MartPoint';
                $this->db->insert('db_email_settings', [
                    'store_id'          => $store_id,
                    'email_provider'    => 'resend',
                    'email_from_name'   => $from_name,
                    'resend_from_name'  => $from_name,
                ]);
            }
        }

        // Standard email templates (invoice, receipt, OTP, reports, etc.)
        $this->load->model('Email_template_model', 'email_templates');
        if (method_exists($this->email_templates, 'seedDefaults')) {
            $this->email_templates->seedDefaults($store_id);
        }

        // Default report schedules (daily summary / low stock)
        $this->load->model('Report_schedule_model', 'report_schedules');
        if (method_exists($this->report_schedules, 'seedDefaults')) {
            $this->report_schedules->seedDefaults($store_id);
        }
    }

    /**
     * Create the extra staff users captured on the installer screen.
     * The installer stores them (with bcrypt-hashed passwords) in
     * db_store_settings under installer/pending_staff_users; we create the
     * accounts here after roles are seeded so role names resolve to real
     * role ids, then clear the stored payload.
     */
    private function _create_pending_users($stores) {
        if (empty($stores) || !$this->db->table_exists('db_store_settings')) {
            return;
        }
        $store_id = (int)$stores[0]->id;

        $row = $this->db->where('setting_group', 'installer')
            ->where('setting_key', 'pending_staff_users')
            ->get('db_store_settings')->row();
        if (!$row || empty($row->setting_value)) {
            return;
        }
        $pending = json_decode($row->setting_value, true);
        // Consume the payload whether or not it is usable.
        $this->db->where('id', $row->id)->delete('db_store_settings');
        if (!is_array($pending) || empty($pending['users'])) {
            return;
        }

        $default_password = 'martpoint123';

        // Default warehouse for new staff: the seeded Business HQ warehouse
        $hq = $this->db->where('store_id', $store_id)->order_by('id', 'asc')
            ->get('db_warehouse')->row();
        $hq_id = $hq ? (int)$hq->id : null;

        foreach ($pending['users'] as $u) {
            $full_name = trim((string)($u['name'] ?? ''));
            $username  = trim((string)($u['username'] ?? ''));
            $email     = trim((string)($u['email'] ?? ''));
            $role_name = trim((string)($u['role'] ?? ''));

            if ($full_name === '' || $username === '') {
                continue;
            }
            if ($email === '') {
                $email = $username;
            }
            // Skip duplicates
            $dup = $this->db->where('username', $username)
                ->or_where('email', $email)
                ->get('db_users')->num_rows();
            if ($dup > 0) {
                continue;
            }

            // Resolve role name → role_id (roles were just seeded for this store)
            $role_id = 2; // Store Admin fallback
            $role = $this->db->where('store_id', $store_id)
                ->where('UPPER(role_name)', strtoupper($role_name))
                ->get('db_roles')->row();
            if ($role) {
                $role_id = (int)$role->id;
            }

            $parts = preg_split('/\s+/', $full_name, 2);
            // Installer already bcrypt-hashes supplied passwords; fall back to
            // the documented default when none was provided.
            $pass_hash = trim((string)($u['pass_hash'] ?? ''));
            if ($pass_hash === '' || password_get_info($pass_hash)['algoName'] === 'unknown') {
                $pass_hash = password_hash($default_password, PASSWORD_BCRYPT);
            }

            $next_user_id = $this->db->select('COALESCE(MAX(id),0)+1 as next_id')
                ->get('db_users')->row()->next_id;

            $this->db->insert('db_users', [
                'id'                   => $next_user_id,
                'store_id'             => $store_id,
                'username'             => $username,
                'first_name'           => $parts[0],
                'last_name'            => $parts[1] ?? '',
                'password'             => $pass_hash,
                'email'                => $email,
                'mobile'               => trim((string)($u['mobile'] ?? '')),
                'role_id'              => $role_id,
                'role_name'            => $role ? $role->role_name : 'Store Admin',
                'default_warehouse_id' => $hq_id,
                'status'               => 1,
                'created_date'         => date('Y-m-d'),
                'created_time'         => date('h:i:s a'),
                'created_by'           => 'installer',
            ]);

            // Warehouse assignment (matches Users_model behaviour for non-admin staff)
            if ($hq_id && $role_id != 1 && $role_id != 2) {
                $max_wh = $this->db->select('COALESCE(MAX(id),0)+1 as nid')
                    ->get('db_userswarehouses')->row()->nid;
                $this->db->insert('db_userswarehouses', [
                    'id' => $max_wh, 'user_id' => $next_user_id, 'warehouse_id' => $hq_id,
                ]);
            }
        }
    }

    /**
     * Central-provisioned installs drop mp_provision.php in the docroot with
     * DB creds (already consumed by the installer) and a unique admin_pass.
     * Apply it to the seeded admin accounts (master + storeadm) so every
     * install gets its own credentials — the partner never learns them.
     * The file is always deleted afterwards.
     */
    private function _apply_provision_file() {
        $path = FCPATH . 'mp_provision.php';
        if (!is_file($path)) {
            return;
        }
        try {
            $prov = @include $path;
            @unlink($path);
            if (!is_array($prov)) {
                return;
            }
            $pass = trim((string) ($prov['admin_pass'] ?? ''));
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                // Cover both seeded admin rows — match username OR the fixed
                // seed ids so neither account keeps the shared default.
                $this->db->group_start()
                    ->where_in('username', ['master', 'storeadm'])
                    ->or_where_in('id', [1, 2])
                    ->group_end()
                    ->update('db_users', ['password' => $hash]);
            }
            $cronKey = trim((string) ($prov['cron_key'] ?? ''));
            if ($cronKey !== '' && preg_match('/^[A-Za-z0-9_\-]{6,64}$/', $cronKey)) {
                $cfg = FCPATH . 'application/config/config.php';
                if (is_writable($cfg)) {
                    $code = (string) file_get_contents($cfg);
                    $line = "\$config['cron_secret_key'] = '" . $cronKey . "';";
                    if (strpos($code, 'cron_secret_key') !== false) {
                        $code = preg_replace("/\\\$config\['cron_secret_key'\]\s*=\s*'[^']*';/", $line, $code, 1);
                    } else {
                        $code = rtrim($code) . "\n\n" . $line . "\n";
                    }
                    file_put_contents($cfg, $code);
                }
            }
        } catch (Exception $e) {
            @unlink($path);
        }
    }
}
