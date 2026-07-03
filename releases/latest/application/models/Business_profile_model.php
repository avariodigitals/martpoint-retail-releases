<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_profile_model extends CI_Model {

    public function get_profile($store_id) {
        $columns = ['industry_type', 'business_model', 'feature_flags_json', 'workflow_template_key', 'dashboard_template_key', 'storefront_theme_key', 'label_overrides_json', 'industry_settings_json'];
        $available = [];
        foreach ($columns as $col) {
            if ($this->db->field_exists($col, 'db_store')) {
                $available[] = $col;
            }
        }
        if (empty($available)) {
            return [];
        }
        $q = $this->db
            ->select(implode(',', $available))
            ->where('id', $store_id)
            ->get('db_store');
        if (!$q || !method_exists($q, 'row_array')) {
            return [];
        }
        $row = $q->row_array();
        return $row ?: [];
    }

    public function update_profile($store_id, $data) {
        $allowed = [
            'industry_type','business_model','feature_flags_json',
            'workflow_template_key','dashboard_template_key','storefront_theme_key',
            'label_overrides_json','industry_settings_json'
        ];
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
