<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function mp_customer_pricing() {
    $CI =& get_instance();
    $CI->load->model('Customer_pricing_model', 'customer_pricing');
    return $CI->customer_pricing;
}

function mp_customer_price($customer, $normal, $wholesale = null, $store = null) {
    $store = $store ?: get_current_store_id();
    $p = mp_customer_pricing()->policy($store, $customer);
    if ($p['source'] === 'normal') return $normal;
    $base = $p['source'] === 'group' ? ($wholesale === null ? $normal : $wholesale) : $normal;
    return Customer_pricing_model::adjust($base, $p['direction'], $p['percent'], $p['decimals']);
}

function mp_customer_has_group($customer, $store = null) {
    return mp_customer_pricing()->policy($store ?: get_current_store_id(), $customer)['source'] === 'group';
}
