<?php
/**
 * Store Settings Helper
 *
 * Provides a unified, backward-compatible way to read and write store
 * configuration after the db_store modularization.
 *
 * Reads from the new modular tables first, then falls back to the old db_store
 * columns so existing installations keep working while fresh installations use
 * the slim db_store.
 *
 * The helper is loaded automatically by the autoload configuration.
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('mp_get_store_setting')) {
    /**
     * Read a value from the flexible db_store_settings key/value table.
     *
     * @param int    $store_id
     * @param string $group
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    function mp_get_store_setting($store_id, $group, $key, $default = null) {
        $CI =& get_instance();
        if (!$CI->db->table_exists('db_store_settings')) {
            return $default;
        }
        $row = $CI->db
            ->select('setting_value, value_type')
            ->where('store_id', $store_id)
            ->where('setting_group', $group)
            ->where('setting_key', $key)
            ->get('db_store_settings')
            ->row();
        if (!$row) {
            return $default;
        }
        return _mp_cast_setting_value($row->setting_value, $row->value_type);
    }
}

if (!function_exists('mp_set_store_setting')) {
    /**
     * Write a value to the flexible db_store_settings key/value table.
     *
     * @param int    $store_id
     * @param string $group
     * @param string $key
     * @param mixed  $value
     * @param string $type string|int|float|bool|json
     * @return bool
     */
    function mp_set_store_setting($store_id, $group, $key, $value, $type = 'string') {
        $CI =& get_instance();
        if (!$CI->db->table_exists('db_store_settings')) {
            return false;
        }
        $data = [
            'store_id'      => $store_id,
            'setting_group' => $group,
            'setting_key'   => $key,
            'setting_value' => $value,
            'value_type'    => $type,
        ];
        $exists = $CI->db
            ->where('store_id', $store_id)
            ->where('setting_group', $group)
            ->where('setting_key', $key)
            ->get('db_store_settings')
            ->num_rows();
        if ($exists) {
            return $CI->db
                ->where('store_id', $store_id)
                ->where('setting_group', $group)
                ->where('setting_key', $key)
                ->update('db_store_settings', $data);
        }
        return $CI->db->insert('db_store_settings', $data);
    }
}

if (!function_exists('mp_get_store_receipt_setting')) {
    function mp_get_store_receipt_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_receipt_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_pos_setting')) {
    function mp_get_store_pos_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_pos_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_inventory_setting')) {
    function mp_get_store_inventory_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_inventory_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_notification_setting')) {
    function mp_get_store_notification_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_notification_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_theme_setting')) {
    function mp_get_store_theme_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_theme_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_industry_setting')) {
    function mp_get_store_industry_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_industry_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_tax_setting')) {
    function mp_get_store_tax_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_tax_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_storefront_setting')) {
    function mp_get_store_storefront_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_storefront_settings', $key, $default);
    }
}

if (!function_exists('mp_get_store_payment_setting')) {
    function mp_get_store_payment_setting($store_id, $key, $default = null) {
        return _mp_get_structured_setting($store_id, 'db_store_payment_settings', $key, $default);
    }
}

if (!function_exists('mp_set_store_receipt_setting')) {
    function mp_set_store_receipt_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_receipt_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_pos_setting')) {
    function mp_set_store_pos_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_pos_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_inventory_setting')) {
    function mp_set_store_inventory_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_inventory_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_notification_setting')) {
    function mp_set_store_notification_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_notification_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_theme_setting')) {
    function mp_set_store_theme_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_theme_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_industry_setting')) {
    function mp_set_store_industry_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_industry_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_tax_setting')) {
    function mp_set_store_tax_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_tax_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_storefront_setting')) {
    function mp_set_store_storefront_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_storefront_settings', [$key => $value]);
    }
}

if (!function_exists('mp_set_store_payment_setting')) {
    function mp_set_store_payment_setting($store_id, $key, $value) {
        return _mp_set_structured_setting($store_id, 'db_store_payment_settings', [$key => $value]);
    }
}

if (!function_exists('mp_get_store_all_settings')) {
    /**
     * Fetch all structured settings for a store as a merged array.
     * Handy for controllers that used to SELECT * FROM db_store.
     *
     * @param int $store_id
     * @return array
     */
    function mp_get_store_all_settings($store_id) {
        $tables = [
            'db_store_receipt_settings',
            'db_store_pos_settings',
            'db_store_inventory_settings',
            'db_store_notification_settings',
            'db_store_theme_settings',
            'db_store_industry_settings',
            'db_store_tax_settings',
            'db_store_storefront_settings',
            'db_store_payment_settings',
        ];
        $result = [];
        $CI =& get_instance();
        foreach ($tables as $table) {
            if (!$CI->db->table_exists($table)) {
                continue;
            }
            $row = $CI->db->where('store_id', $store_id)->get($table)->row_array();
            if ($row) {
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $result = array_merge($result, $row);
            }
        }
        // Flexible settings
        if ($CI->db->table_exists('db_store_settings')) {
            $rows = $CI->db->where('store_id', $store_id)->get('db_store_settings')->result();
            foreach ($rows as $row) {
                $result[$row->setting_key] = _mp_cast_setting_value($row->setting_value, $row->value_type);
            }
        }
        return $result;
    }
}

if (!function_exists('_mp_get_structured_setting')) {
    /**
     * Internal: read a single column from a structured modular table.
     * Falls back to db_store if the new table is missing or the row is absent.
     */
    function _mp_get_structured_setting($store_id, $table, $key, $default = null) {
        $CI =& get_instance();

        if ($CI->db->table_exists($table)) {
            $row = $CI->db->where('store_id', $store_id)->get($table)->row();
            if ($row && property_exists($row, $key) && $row->{$key} !== null) {
                return $row->{$key};
            }
        }

        // Fallback to db_store if the column still exists
        if ($CI->db->field_exists($key, 'db_store')) {
            $row = $CI->db->select($key)->where('id', $store_id)->get('db_store')->row();
            if ($row && property_exists($row, $key) && $row->{$key} !== null) {
                return $row->{$key};
            }
        }

        return $default;
    }
}

if (!function_exists('_mp_set_structured_setting')) {
    /**
     * Internal: insert/update a row in a structured modular table.
     */
    function _mp_set_structured_setting($store_id, $table, $data) {
        $CI =& get_instance();
        if (!$CI->db->table_exists($table)) {
            return false;
        }
        $data['store_id'] = $store_id;
        $exists = $CI->db->where('store_id', $store_id)->get($table)->num_rows();
        if ($exists) {
            return $CI->db->where('store_id', $store_id)->update($table, $data);
        }
        return $CI->db->insert($table, $data);
    }
}

if (!function_exists('_mp_cast_setting_value')) {
    /**
     * Internal: cast a key/value setting to its declared type.
     */
    function _mp_cast_setting_value($value, $type) {
        if ($value === null) {
            return null;
        }
        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            case 'string':
            default:
                return $value;
        }
    }
}
