<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('business_profile_model', 'bp_model');
    }

    public function index() {
        $this->_screen('tab_identity');
    }

    /**
     * The rail has two entries that used to open this same screen on the same
     * tab, so they looked like a duplicate. Each now lands on the tab it names.
     */
    public function facility() {
        $this->_screen('tab_identity');
    }

    public function capabilities() {
        $this->_screen('tab_features');
    }

    private function _screen($activeTab = 'tab_identity') {
        $this->permission_check('business_profile');
        $store_id = get_current_store_id();
        $data = $this->data;
        $data['page_title'] = 'Business Profile';
        $data['active_tab'] = $activeTab;
        $data['profile'] = $this->bp_model->get_profile($store_id);
        $data['presets'] = $this->bp_model->get_available_presets();
        $data['business_types'] = mp_get_business_types();
        $data['business_models'] = mp_get_business_models();
        $data['feature_flags'] = mp_get_feature_flags();
        $data['label_defaults'] = mp_get_label_defaults();
        $data['workflow_templates'] = mp_get_workflow_templates();
        $data['dashboard_templates'] = mp_get_dashboard_templates();
        // Batch rotation is configured here now (Pharmacy section), so the
        // screen needs the store's current method and whether it can be set.
        $this->load->model('Inventory_rotation_model', 'rotation_model');
        $data['rotation_method'] = $this->rotation_model->method($store_id);
        $data['rotation_available'] = $this->db->table_exists('db_inventory_rotation_settings');
        // Shown next to the switch: how many medicines would stop at the till
        // the moment this store starts selling from its batches.
        $data['rotation_gaps'] = mp_rotation_gap_count($store_id);
        $this->load->model('storefront_model');
        $data['storefront_themes'] = $this->storefront_model->getThemesByIndustryForStore($data['profile']['industry_type'] ?? null, true);
        $data['content'] = $this->load->view('business_profile', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save() {
        $this->permission_check('business_profile');
        $store_id = get_current_store_id();

        // A request with nothing posted is not a save. Run as a GET it would
        // blank the store's industry and rebuild every feature flag from the
        // fallback preset, switching the merchant's modules off. Both the
        // desktop form and the mobile form post.
        if (strtolower((string)$this->input->method()) !== 'post') {
            echo json_encode(['status' => 'error', 'message' => 'Use the Business Profile form to save.']);
            return;
        }

        // Empty means manual pricing; zero is a valid markup. Omitted controls
        // (for example a save from another tab) preserve the existing setting.
        $markup_post = $this->input->post('retail_markup_percent', TRUE);
        $markup_present = $markup_post !== null && $markup_post !== false;
        $markup_percent = mp_normalize_retail_markup_percent($markup_post);
        if ($markup_present && (!is_scalar($markup_post) || (trim((string)$markup_post) !== '' && $markup_percent === null))) {
            echo json_encode(['status'=>'error','message'=>'Retail markup must be a percentage from 0 to 10,000, or blank for manual pricing.']);
            return;
        }

        if ($markup_present && $markup_percent !== null && !$this->db->table_exists('db_store_settings')) {
            echo json_encode(['status'=>'error','message'=>'Store settings are not installed yet. Apply the latest database updates before setting a retail markup.']);
            return;
        }

        $industry_type = $this->input->post('industry_type', TRUE);
        if (strtoupper(trim((string)$industry_type)) === 'GARDEN_CENTRE_LANDSCAPING') $industry_type = 'garden_centre_landscaping';
        $business_model = $this->input->post('business_model', TRUE);
        $workflow_template_key = $this->input->post('workflow_template_key', TRUE);
        $dashboard_template_key = $this->input->post('dashboard_template_key', TRUE);
        $storefront_theme_key = $this->input->post('storefront_theme_key', TRUE);

        // Load the chosen business-type preset so every necessary field is
        // pre-filled when the user only picks a business type and hits Save.
        $presets = mp_get_business_presets();
        $preset = $presets[$industry_type] ?? $presets['general_retail'];

        if (empty($business_model) || !isset(mp_get_business_models()[$business_model])) {
            $business_model = $preset['business_model'] ?? 'product_based';
        }

        $workflow_templates = mp_get_workflow_templates();
        if (empty($workflow_template_key) || !isset($workflow_templates[$workflow_template_key])) {
            $workflow_template_key = $preset['workflow_template'] ?? 'retail_standard';
        }

        $dashboard_templates = mp_get_dashboard_templates();
        if (empty($dashboard_template_key) || !isset($dashboard_templates[$dashboard_template_key])) {
            $dashboard_template_key = $preset['dashboard_template'] ?? 'general_retail';
        }

        // Validate the storefront theme against the allowed set for the industry.
        // Order of precedence: the submitted key when valid, then the store's
        // existing saved key (so an unrelated profile save can't silently
        // revert a theme the merchant chose in Appearance — Theme_engine
        // whitelists stored selections even when they fall outside the new
        // industry group), then the preset default / first allowed theme.
        $this->load->model('storefront_model');
        $allowed = $this->storefront_model->getThemesByIndustryForStore($industry_type, false);
        if (empty($storefront_theme_key) || !isset($allowed[$storefront_theme_key])) {
            $existingProfile = mp_get_store_profile($store_id);
            $existingKey = $existingProfile['theme_key'] ?? null;
            $storefront_theme_key = $existingKey ?: (isset($allowed[$preset['theme_key'] ?? '']) ? $preset['theme_key'] : (array_keys($allowed)[0] ?? 'general_retail'));
        }

        // Feature flags JSON — see mp_merge_feature_flags() for the three cases
        // (rendered / stored-but-not-rendered / never stored) that keep a
        // section-local save from zeroing a flag on a folded-away part of the
        // screen, and a store-scoped setting from leaking into the next store.
        $flags = $this->input->post('feature_flags');
        $flags_seen = $this->input->post('feature_flags_seen');   // markers
        $stored = array();
        $existing = $this->db->select('feature_flags_json')
            ->where('store_id', $store_id)
            ->get('db_store_industry_settings')->row();
        if ($existing && !empty($existing->feature_flags_json)) {
            $decoded = json_decode($existing->feature_flags_json, true);
            if (is_array($decoded)) { $stored = $decoded; }
        }
        $preset_features = (isset($preset['features']) && is_array($preset['features']))
            ? $preset['features'] : array();
        $clean = mp_merge_feature_flags($flags, $flags_seen, $stored, $preset_features);
        $feature_flags_json = json_encode($clean);

        // Batch rotation lives with the pharmacy switches now, but saves to its
        // own per-store table. Only act when the form posted a method: saving a
        // different tab must not reset it.
        $rotation_method = strtoupper(trim((string)$this->input->post('rotation_method', TRUE)));
        $rotation_error = '';
        if (in_array($rotation_method, ['MANUAL', 'FEFO', 'FIFO'], true)) {
            $this->load->model('Inventory_rotation_model', 'rotation_model');
            // Switching onto batch selling with unbalanced lots blocks the
            // counter medicine by medicine, so the change needs a second,
            // deliberate click. Numbers come from the reconciliation model.
            $guard = $this->rotation_model->switch_guard($store_id, $rotation_method,
                (string)$this->input->post('rotation_ack', TRUE) === '1');
            if (!$guard['ok']) {
                $rotation_error = $guard['message'];
            } elseif (!$this->rotation_model->save_method($store_id, $rotation_method, get_current_user_id())) {
                $rotation_error = 'Batch rotation could not be saved on this store yet — the rotation table is not installed. Apply the latest update, then choose the method again.';
            }
        }

        // Label overrides JSON — use submitted values, then merge with preset defaults
        $labels = $this->input->post('label_overrides');
        $label_overrides_json = null;
        if (!is_null($labels) && is_array($labels)) {
            $clean_labels = $preset['labels'] ?? [];
            foreach ($labels as $k => $v) {
                $v = trim($v);
                if ($v !== '') {
                    $clean_labels[$k] = $v;
                } elseif (isset($clean_labels[$k])) {
                    unset($clean_labels[$k]);
                }
            }
            if ($industry_type === 'supplements_wellness'
                && in_array(strtolower(trim((string)($clean_labels['customer'] ?? ''))), ['member','members'], true)) {
                unset($clean_labels['customer']);
            }
            if (!empty($clean_labels)) {
                $label_overrides_json = json_encode($clean_labels);
            }
        } else {
            $label_overrides_json = !empty($preset['labels']) ? json_encode($preset['labels']) : null;
        }

        // Industry settings JSON (simple key-value from textarea or hidden)
        $industry_settings_raw = $this->input->post('industry_settings_json', TRUE);
        $industry_settings_json = null;
        if (!empty($industry_settings_raw)) {
            $decoded = json_decode($industry_settings_raw, true);
            if (is_array($decoded)) {
                $industry_settings_json = json_encode($decoded);
            }
        }

        $update = [
            'industry_type' => $industry_type,
            'business_model' => $business_model,
            'workflow_template_key' => $workflow_template_key,
            'dashboard_template_key' => $dashboard_template_key,
            'storefront_theme_key' => $storefront_theme_key,
            'feature_flags_json' => $feature_flags_json,
            'label_overrides_json' => $label_overrides_json,
            'industry_settings_json' => $industry_settings_json,
        ];

        $this->db->trans_begin();
        try {
            $result = $this->bp_model->update_profile($store_id, $update);
            if ($result && $markup_present && $this->db->table_exists('db_store_settings')) {
                $result = mp_set_store_setting($store_id, 'inventory', 'retail_markup_percent',
                    $markup_percent === null ? '' : (string)$markup_percent, 'string');
            }
            if (!$result || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $result = false;
            } else {
                $this->db->trans_commit();
            }
        } catch (InvalidArgumentException $e) {
            $this->db->trans_rollback();
            echo json_encode(['status'=>'error','message'=>$e->getMessage()]); return;
        }

        // Seed industry-specific master data (units, categories, formula types).
        // Idempotent — safe on every save, only creates what is missing.
        if ($result) {
            $this->load->model('Default_data_model', 'default_data');
            $this->default_data->seed_industry_defaults($store_id, $industry_type);

            // Backfill module permissions on existing stores that just enabled
            // the equipment/service features (additive only — never removes).
            if (!empty($clean['equipment_register']) || !empty($clean['service_jobs'])) {
                $this->default_data->seed_module_permissions($store_id);
            }

            // Demo/local demo stores also need every persisted demo surface to
            // follow the selected industry. Insert labelled examples without
            // archiving live rows or resetting stock from earlier demos.
            $this->_sync_demo_data($store_id, $industry_type);
        }

        // Sync storefront_theme_key to db_storefront_settings.theme_id so the theme takes effect immediately.
        // Business Profile is the source of truth; Appearance settings can override later.
        if ($result) {
            $this->load->model('storefront_model');
            $this->storefront_model->seedThemesIfEmpty();
            $effectiveProfile = mp_get_store_profile($store_id);
            $effectiveThemeKey = $effectiveProfile['theme_key'] ?? null;
            if (!empty($effectiveThemeKey)) {
                $theme = $this->storefront_model->getThemeByKey($effectiveThemeKey);
                if ($theme) {
                    $settings = $this->storefront_model->getSettings($store_id);
                    $update = ['theme_id' => $theme->id];
                    if (empty($settings->store_slug)) {
                        $store = get_store_details($store_id);
                        $update['store_slug'] = !empty($store->store_slug) ? $store->store_slug : strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name));
                        $update['store_status'] = 'active';
                    }
                    $this->storefront_model->saveSettings($store_id, $update);
                }
            }
        }

        if ($result) {
            $message = 'Business Profile updated successfully.';
            if ($rotation_error !== '') {
                // The profile saved; only the rotation choice did not. Say which
                // part is pending rather than reporting a blanket failure.
                $message = $rotation_error;
            }
            echo json_encode(['status' => 'success', 'message' => $message, 'csrf_hash' => $this->security->get_csrf_hash()]);
        } else {
            $err = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Failed to update Business Profile. DB error: ' . ($err['message'] ?? 'Unknown'), 'csrf_hash' => $this->security->get_csrf_hash()]);
        }
    }

    public function get_preset() {
        $this->permission_check('business_profile');
        $industry_type = $this->input->post('industry_type', TRUE);
        $preset = $this->bp_model->get_preset($industry_type);
        if ($preset) {
            echo json_encode(['status' => 'success', 'preset' => $preset]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Preset not found.']);
        }
    }

    private function _sync_demo_data($store_id, $industry_type) {
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $local_host = strpos($host, 'martpointretailapp.test') !== false
            || $host === 'localhost' || strpos($host, '127.0.0.1') !== false
            || substr($host, -5) === '.test';
        $demo_host = strpos($host, 'demo.martpoint') !== false;
        $store = $this->db->select('store_name')->where('id', (int)$store_id)->get('db_store')->row();
        $zonat_demo_store = $store && preg_match('/^Zonat\b/i', trim((string)$store->store_name));
        if (!$demo_host && (!$local_host || !$zonat_demo_store) && getenv('MP_DEMO_SEED') !== '1') return;

        try {
            $this->load->model('Demo_seeder', 'demo_seeder');
            $result = $this->demo_seeder->seed_examples($store_id, $industry_type);
            if (empty($result['ok'])) {
                log_message('error', 'Demo industry sync failed for store '.$store_id.': '.json_encode($result));
            }
        } catch (Throwable $e) {
            // Profile changes must still save if optional demo content cannot
            // be generated; record the failure for local/demo diagnostics.
            log_message('error', 'Demo industry sync exception for store '.$store_id.': '.$e->getMessage());
        }
    }
}
