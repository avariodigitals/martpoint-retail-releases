<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('mp_storefront_footer_logo_url')) {
    /** Resolve the optional footer mark, falling back to the normal store logo. */
    function mp_storefront_footer_logo_url($settings, $fallback = '')
    {
        $candidates = [
            trim((string) ($settings->footer_logo ?? '')),
            trim((string) ($settings->store_logo ?? '')),
            trim((string) $fallback),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }
            if (preg_match('#^https?://#i', $candidate)) {
                return $candidate;
            }

            $relative = ltrim($candidate, '/');
            if (is_file(FCPATH . $relative)) {
                return base_url($relative);
            }
        }

        return '';
    }
}

if (!function_exists('mp_storefront_show_powered_by')) {
    /**
     * Keep MartPoint attribution on every plan except Enterprise.
     * Missing legacy licensing schema must fail closed (show the attribution).
     */
    function mp_storefront_show_powered_by($settings = null, $licensePlan = null)
    {
        if ($licensePlan === null) {
            try {
                $CI =& get_instance();
                $storeId = (int) ($settings->store_id ?? $settings->id ?? 0);
                if ($storeId > 0 && isset($CI->db)
                    && $CI->db->table_exists('db_subscription_license')
                    && $CI->db->field_exists('plan_name', 'db_subscription_license')
                    && $CI->db->field_exists('store_id', 'db_subscription_license')) {
                    $row = $CI->db->select('plan_name')
                        ->where('store_id', $storeId)
                        ->limit(1)
                        ->get('db_subscription_license')->row();
                    $licensePlan = $row->plan_name ?? null;
                }
            } catch (Throwable $e) {
                // Attribution stays enabled when plan lookup is unavailable.
            }
        }

        return strtolower(trim((string) $licensePlan)) !== 'enterprise';
    }
}
