<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_profile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('business_profile_model', 'bp_model');
    }

    public function index() {
        $this->permission_check('store_edit');
        $store_id = get_current_store_id();
        $data = $this->data;
        $data['page_title'] = 'Business Profile';
        $data['profile'] = $this->bp_model->get_profile($store_id);
        $data['presets'] = $this->bp_model->get_available_presets();
        $data['business_types'] = mp_get_business_types();
        $data['business_models'] = mp_get_business_models();
        $data['feature_flags'] = mp_get_feature_flags();
        $data['label_defaults'] = mp_get_label_defaults();
        $data['workflow_templates'] = mp_get_workflow_templates();
        $data['dashboard_templates'] = mp_get_dashboard_templates();
        $this->load->model('storefront_model');
        $data['storefront_themes'] = $this->storefront_model->getThemesByIndustryForStore($data['profile']['industry_type'] ?? null, true);
        $data['content'] = $this->load->view('business_profile', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save() {
        $this->permission_check('store_edit');
        $store_id = get_current_store_id();

        $industry_type = $this->input->post('industry_type', TRUE);
        $business_model = $this->input->post('business_model', TRUE);
        $workflow_template_key = $this->input->post('workflow_template_key', TRUE);
        $dashboard_template_key = $this->input->post('dashboard_template_key', TRUE);
        $storefront_theme_key = $this->input->post('storefront_theme_key', TRUE);

        // Load storefront model so we can validate the selected theme against the industry.
        $this->load->model('storefront_model');

        // When the business type changes, make sure the storefront theme follows the new preset
        // unless the user explicitly picked a different theme.
        $old = $this->bp_model->get_profile($store_id);
        $oldIndustry = $old['industry_type'] ?? '';
        $oldThemeKey = $old['storefront_theme_key'] ?? '';
        if (!empty($industry_type) && $industry_type !== $oldIndustry) {
            $allowed = $this->storefront_model->getThemesByIndustryForStore($industry_type, false);
            if(empty($storefront_theme_key) || $storefront_theme_key === $oldThemeKey || !isset($allowed[$storefront_theme_key])){
                // Default to the first allowed theme for the new industry
                $storefront_theme_key = array_keys($allowed)[0];
            }
        }

        // Final validation: the selected theme must be in the allowed set for the industry.
        if (!empty($storefront_theme_key)) {
            $allowed = $this->storefront_model->getThemesByIndustryForStore($industry_type, false);
            if (!isset($allowed[$storefront_theme_key])) {
                $storefront_theme_key = array_keys($allowed)[0];
            }
        } else {
            $allowed = $this->storefront_model->getThemesByIndustryForStore($industry_type, false);
            $storefront_theme_key = array_keys($allowed)[0];
        }

        // Feature flags JSON
        $flags = $this->input->post('feature_flags', TRUE);
        $feature_flags_json = null;
        if (is_array($flags)) {
            $clean = [];
            foreach (mp_get_feature_flags() as $key => $label) {
                $clean[$key] = isset($flags[$key]) ? '1' : '0';
            }
            $feature_flags_json = json_encode($clean);
        }

        // Label overrides JSON
        $labels = $this->input->post('label_overrides', TRUE);
        $label_overrides_json = null;
        if (is_array($labels)) {
            $clean_labels = [];
            foreach ($labels as $k => $v) {
                $v = trim($v);
                if ($v !== '') {
                    $clean_labels[$k] = $v;
                }
            }
            if (!empty($clean_labels)) {
                $label_overrides_json = json_encode($clean_labels);
            }
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

        $result = $this->bp_model->update_profile($store_id, $update);

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
            echo json_encode(['status' => 'success', 'message' => 'Business Profile updated successfully.', 'csrf_hash' => $this->security->get_csrf_hash()]);
        } else {
            $err = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Failed to update Business Profile. DB error: ' . ($err['message'] ?? 'Unknown'), 'csrf_hash' => $this->security->get_csrf_hash()]);
        }
    }

    public function get_preset() {
        $this->permission_check('store_edit');
        $industry_type = $this->input->post('industry_type', TRUE);
        $preset = $this->bp_model->get_preset($industry_type);
        if ($preset) {
            echo json_encode(['status' => 'success', 'preset' => $preset]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Preset not found.']);
        }
    }
}
