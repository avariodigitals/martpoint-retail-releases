<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_profile_model extends CI_Model {

    public function get_profile($store_id) {
        return mp_read_store_profile($store_id);
    }

    public function update_profile($store_id, $data) {
        if (isset($data['industry_type']) && strtoupper(trim($data['industry_type'])) === 'GARDEN_CENTRE_LANDSCAPING') {
            $data['industry_type'] = 'garden_centre_landscaping';
        }
        // A profile save that omits the industry settings must preserve the
        // merchant's selected operating modes and any unrelated settings.
        $existing = $this->get_profile($store_id);
        if (($data['industry_type'] ?? $existing['industry_type'] ?? '') === 'garden_centre_landscaping') {
            $this->load->helper('garden');
            $settings = json_decode($existing['industry_settings_json'] ?? '',true) ?: [];
            if (array_key_exists('industry_settings_json',$data)) {
                $posted = json_decode($data['industry_settings_json'] ?? '',true);
                if (is_array($posted)) $settings = array_replace($settings,$posted);
            }
            $settings['garden_operating_modes'] = mp_garden_modes($settings['garden_operating_modes'] ?? ['combined']);
            $data['industry_settings_json'] = json_encode($settings);
        }
        $allowed = [
            'industry_type','business_model','feature_flags_json',
            'workflow_template_key','dashboard_template_key','storefront_theme_key',
            'label_overrides_json','industry_settings_json'
        ];

        // 1. Prefer the new modular industry settings table
        if ($this->db->table_exists('db_store_industry_settings')) {
            $update = [];
            foreach ($allowed as $col) {
                if (array_key_exists($col, $data)) {
                    $update[$col] = $data[$col];
                }
            }
            if (!empty($update)) {
                $exists = $this->db->where('store_id', $store_id)->get('db_store_industry_settings')->num_rows();
                if ($exists) {
                    $ok = $this->db->where('store_id', $store_id)->update('db_store_industry_settings', $update);
                } else {
                    $update['store_id'] = $store_id;
                    $ok = $this->db->insert('db_store_industry_settings', $update);
                }
                if (!$ok) {
                    log_message('error', 'Business_profile_model update_profile failed: ' . json_encode($this->db->error()));
                    return false;
                }
                // Keep canonical db_store in sync
                $storeUpdate = [];
                foreach ($update as $col => $val) {
                    if ($col !== 'store_id' && $this->db->field_exists($col, 'db_store')) {
                        $storeUpdate[$col] = $val;
                    }
                }
                if (!empty($storeUpdate)) {
                    $this->db->where('id', $store_id)->update('db_store', $storeUpdate);
                }
                // The legacy table is still read as a fallback by
                // mp_get_store_profile and physio_enabled(). Leaving it behind
                // is what let a store resolve to two different business types
                // at once — the clinical screens read one and the rest of the
                // app read the other. Mirror the write so they cannot drift.
                if ($this->db->table_exists('db_store_business_profile')) {
                    $exists = $this->db->where('store_id', $store_id)->get('db_store_business_profile')->num_rows();
                    if ($exists) {
                        $this->db->where('store_id', $store_id)->update('db_store_business_profile', $update);
                    } else {
                        $this->db->insert('db_store_business_profile', $update);
                    }
                }
                return true;
            }
        }

        // 2. Fallback: older dedicated business-profile table
        if ($this->db->table_exists('db_store_business_profile')) {
            $update = [];
            foreach ($allowed as $col) {
                if (array_key_exists($col, $data)) {
                    $update[$col] = $data[$col];
                }
            }
            if (empty($update)) {
                return false;
            }
            $exists = $this->db->where('store_id', $store_id)->get('db_store_business_profile')->num_rows();
            if ($exists) {
                return @$this->db->where('store_id', $store_id)->update('db_store_business_profile', $update);
            }
            $update['store_id'] = $store_id;
            return @$this->db->insert('db_store_business_profile', $update);
        }

        // 3. Fallback: old db_store columns
        $update = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data) && $this->db->field_exists($col, 'db_store')) {
                $update[$col] = $data[$col];
            }
        }
        if (empty($update)) {
            return false;
        }
        return @$this->db->where('id', $store_id)->update('db_store', $update);
    }

    public function get_available_presets() {
        return mp_get_business_presets();
    }

    public function get_preset($industry_type) {
        $presets = mp_get_business_presets();
        return isset($presets[$industry_type]) ? $presets[$industry_type] : null;
    }
}
