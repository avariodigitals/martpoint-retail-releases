<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_profile_model extends CI_Model {

    public function get_profile($store_id) {
        $columns = ['industry_type', 'business_model', 'feature_flags_json', 'workflow_template_key', 'dashboard_template_key', 'storefront_theme_key', 'label_overrides_json', 'industry_settings_json'];

        // 1. Prefer the new modular industry settings table
        if ($this->db->table_exists('db_store_industry_settings')) {
            $q = $this->db->select(implode(',', $columns))->where('store_id', $store_id)->get('db_store_industry_settings');
            if ($q && method_exists($q, 'row_array')) {
                $row = $q->row_array();
                if ($row) {
                    return $row;
                }
            }
        }

        // 2. Fallback: older dedicated business-profile table
        if ($this->db->table_exists('db_store_business_profile')) {
            $q = $this->db->select(implode(',', $columns))->where('store_id', $store_id)->get('db_store_business_profile');
            if ($q && method_exists($q, 'row_array')) {
                $row = $q->row_array();
                return $row ?: [];
            }
            return [];
        }

        // 3. Fallback: old db_store columns
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
