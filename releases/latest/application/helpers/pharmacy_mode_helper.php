<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function mp_pharmacy_industry($industry) {
    return in_array(strtolower(trim((string)$industry)), ['pharmacy', 'medical_store', 'healthcare'], true);
}

/** Legacy is an upgrade marker, not a third selectable mode. */
function mp_pharmacy_resolve_mode($stored) {
    if ($stored === 'standard') return 'standard';
    if ($stored === 'advanced' || $stored === 'legacy' || $stored === null) return 'advanced';
    return 'standard'; // Invalid explicit settings fail closed.
}

function mp_pharmacy_stored_mode($storeId = null, $refresh = false) {
    static $cache = [];
    $storeId = (int)($storeId ?: get_current_store_id());
    if ($refresh) unset($cache[$storeId]);
    if (array_key_exists($storeId, $cache)) return $cache[$storeId];
    $reader = mp_read_db();
    // Older installs keep their current behaviour until migration .205 runs.
    if (!$reader->field_exists('pharmacy_mode', 'db_store')) return $cache[$storeId] = 'legacy';
    $row = $reader->select('pharmacy_mode')->where('id', $storeId)->get('db_store')->row();
    return $cache[$storeId] = ($row ? $row->pharmacy_mode : 'standard');
}

function mp_pharmacy_advanced($storeId = null) {
    $storeId = (int)($storeId ?: get_current_store_id());
    return mp_pharmacy_industry(mp_get_store_profile($storeId)['industry_type'] ?? '')
        && mp_pharmacy_resolve_mode(mp_pharmacy_stored_mode($storeId)) === 'advanced';
}

function mp_pharmacy_standard($storeId = null) {
    $storeId = (int)($storeId ?: get_current_store_id());
    return mp_pharmacy_industry(mp_get_store_profile($storeId)['industry_type'] ?? '')
        && mp_pharmacy_resolve_mode(mp_pharmacy_stored_mode($storeId)) === 'standard';
}

/** Extra workflow routes are unavailable in Standard, including direct requests. */
function mp_pharmacy_advanced_route($controller, $method) {
    $controller = strtolower((string)$controller);
    $method = strtolower((string)$method);
    if (in_array($controller, ['warehouse', 'stock_transfer'], true)) return true;
    if ($controller === 'pharmacy_workspace' && $method === 'locations') return true;
    return $controller === 'mobile' && in_array($method, ['stock_transfers', 'stock_transfer_form', 'save_stock_transfer', 'delete_stock_transfer'], true);
}

/** Store-owned stock locations only; keep every stored warehouse and ledger intact. */
function mp_pharmacy_own_warehouse_ids($storeId) {
    $reader = mp_read_db();
    $reader->where('store_id', (int)$storeId)->where('status', 1);
    if ($reader->field_exists('branch_type', 'db_warehouse')) {
        $reader->group_start()->where_not_in('branch_type', ['partner', 'pickup_hub'])->or_where('branch_type IS NULL', null, false)->group_end();
    }
    return array_map('intval', array_column($reader->select('id')->get('db_warehouse')->result_array(), 'id'));
}

function mp_pharmacy_pickup_method($method) {
    return (bool)preg_match('/pick[\s_-]*up|collect/i', (string)$method);
}

function mp_pharmacy_custom_ordering($storedMode, $legacyEnabled) {
    if ($storedMode === 'legacy' || $storedMode === null) return (bool)$legacyEnabled;
    return $storedMode === 'advanced';
}

/** Apply effective behaviour without changing the saved legacy settings. */
function mp_pharmacy_storefront_settings($settings) {
    if (!$settings || !mp_pharmacy_industry(mp_get_store_profile($settings->store_id)['industry_type'] ?? '')) return $settings;
    $settings = clone $settings;
    $mode = mp_pharmacy_stored_mode($settings->store_id);
    $settings->partner_marketplace_enabled = mp_pharmacy_custom_ordering($mode, !empty($settings->partner_marketplace_enabled)) ? 1 : 0;
    if (mp_pharmacy_resolve_mode($mode) === 'standard') {
        // No branch selection or collection workflow in Standard. Checkout resolves
        // its own stock source on the server rather than trusting a saved hub id.
        $settings->default_branch_id = 0;
        $methods = json_decode($settings->shipping_methods_json ?? '', true);
        if (is_array($methods)) {
            $settings->shipping_methods_json = json_encode(array_values(array_filter($methods, function($method) {
                return !mp_pharmacy_pickup_method(($method['type'] ?? '').' '.($method['name'] ?? ''));
            })));
        }
    }
    return $settings;
}
