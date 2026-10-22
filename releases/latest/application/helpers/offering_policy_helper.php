<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Shared online service pricing policy; legacy services remain fixed-price. */
function mp_service_price_model($service) {
    $fields = json_decode($service->industry_fields_json ?? '',true) ?: [];
    $model = $fields['price_model'] ?? 'fixed';
    // Unknown policy fails closed instead of becoming a zero-price purchase.
    return in_array($model,['fixed','starting','quote'],true) ? $model : 'quote';
}

function mp_service_pos_item_id($service) {
    $fields = json_decode($service->industry_fields_json ?? '',true) ?: [];
    return (int)($fields['pos_item_id'] ?? 0);
}
