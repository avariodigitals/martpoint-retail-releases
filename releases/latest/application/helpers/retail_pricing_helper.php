<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Optional store rule: retail is wholesale plus a markup, never purchase cost. */
function mp_normalize_retail_markup_percent($value) {
    if (!is_scalar($value) || trim((string)$value) === '' || !is_numeric($value)) return null;
    $percent = (float)$value;
    return is_finite($percent) && $percent >= 0 && $percent <= 10000 ? $percent : null;
}

function mp_retail_markup_config($store_id = null) {
    $store_id = (int)($store_id ?: get_current_store_id());
    $config = ['percent' => null, 'decimals' => 2];
    if (!mp_feature_enabled_for_store('auto_retail_markup', $store_id)) return $config;
    $config['percent'] = mp_normalize_retail_markup_percent(
        mp_get_store_setting($store_id, 'inventory', 'retail_markup_percent', '')
    );
    $config['decimals'] = max(0, min(4, (int)mp_get_store_receipt_setting($store_id, 'decimals', 2)));
    return $config;
}

function mp_retail_price_from_wholesale($wholesale, $retail, array $config) {
    if ($config['percent'] === null || !is_scalar($wholesale)) return $retail;
    $value = str_replace(',', '', trim((string)$wholesale));
    if ($value === '' || !is_numeric($value) || !is_finite((float)$value) || (float)$value < 0) return $retail;
    return round((float)$value * (1 + $config['percent'] / 100), $config['decimals']);
}

/** Used by imports; partial updates and services keep their original values. */
function mp_apply_retail_markup(array $data, array $config, $wholesale_key = 'sales_price', $retail_key = 'mrp') {
    if ($config['percent'] !== null && empty($data['service_bit']) && array_key_exists($wholesale_key, $data)) {
        $data[$retail_key] = mp_retail_price_from_wholesale($data[$wholesale_key], $data[$retail_key] ?? null, $config);
    }
    return $data;
}
