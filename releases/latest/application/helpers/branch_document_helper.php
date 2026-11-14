<?php
/** Shared seller identity for all industries. Never use the operator's active branch. */
defined('BASEPATH') OR exit('No direct script access allowed');

/** Keep business branding/tax/payment settings; replace only the selling location. */
function mp_branch_store_identity($store, $branch) {
    if (!$store || !$branch || (int)$store->id !== (int)$branch->store_id) return $store;
    $seller = clone $store;
    $seller->branch_id = (int)$branch->id;
    $seller->branch_name = trim(strip_tags((string)$branch->warehouse_name));
    $address = trim((string)($branch->address ?? ''));
    // Legacy primary locations may continue to use their business address.
    $primary = ($branch->warehouse_type ?? '') === 'System';
    if (!$primary || $address !== '') {
        foreach (['address','city','state','country','postcode'] as $field) {
            $seller->$field = trim(strip_tags((string)($branch->$field ?? '')));
        }
    }
    $seller->address = $seller->branch_name . ($seller->address !== '' ? ' — '.$seller->address : ' — Address not configured');
    foreach (['mobile','email'] as $field) {
        if (trim((string)($branch->$field ?? '')) !== '') $seller->$field = trim(strip_tags((string)$branch->$field));
    }
    if (trim((string)($branch->mobile ?? '')) !== '') $seller->phone = '';
    return $seller;
}

/** Resolve only from a tenant-scoped, persisted document; never accept a request branch. */
function mp_document_store($store, $table, $id) {
    $allowed = ['db_sales','db_quotation','db_purchase','db_salesreturn','db_purchasereturn','db_stockadjustment'];
    if (!$store || !in_array($table, $allowed, true) || (int)$id <= 0) return $store;
    $CI =& get_instance();
    $storeId = (int)$store->id;
    if ($storeId !== (int)get_current_store_id()) return $store;
    if (!$CI->db->field_exists('warehouse_id', $table)) return $store;
    $document = $CI->db->where('id', (int)$id)->where('store_id', $storeId)->get($table)->row();
    if (!$document) return $store;
    $branchId = (int)($document->warehouse_id ?? 0);
    // Older returns may only identify the original sale or purchase.
    if (!$branchId && $table === 'db_salesreturn' && !empty($document->sales_id)) return mp_document_store($store, 'db_sales', $document->sales_id);
    if (!$branchId && $table === 'db_purchasereturn' && !empty($document->purchase_id)) return mp_document_store($store, 'db_purchase', $document->purchase_id);
    if (!$branchId) return $store;
    $branch = $CI->db->where('id', $branchId)->where('store_id', $storeId)->get('db_warehouse')->row();
    return mp_branch_store_identity($store, $branch);
}

/** For compact documents that previously omitted the seller's location entirely. */
function mp_document_location_html($store) {
    if (!$store || empty($store->branch_name)) return '';
    $parts = [];
    foreach (['address','city','state','country','postcode'] as $field) {
        if (!empty($store->$field)) $parts[] = $store->$field;
    }
    return '<div class="mp-document-location" style="font-size:12px;line-height:1.5;margin:6px 0">'.htmlspecialchars(implode(', ', $parts), ENT_QUOTES, 'UTF-8').'</div>';
}
