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

/**
 * Dispensing gate: refuse a sale containing prescription-only medicines
 * unless an approved prescription for the same patient is linked to it.
 * Returns '' when dispensing is allowed, otherwise a staff-facing message.
 * With $asList=true it instead returns the names of the Rx-only items in the
 * cart ([] when there are none) — used by online checkout before ordering.
 */
function mp_prescription_gate($storeId, $itemIds, $medicalNoteId = 0, $customerId = 0, $asList = false) {
    $storeId = (int)$storeId;
    $listless = $asList ? [] : '';
    $itemIds = array_values(array_filter(array_map('intval', (array)$itemIds)));
    if (!$itemIds || !mp_feature_enabled_for_store('prescription_required', $storeId)) return $listless;
    $db = mp_read_db();
    if (!$db->field_exists('prescription_required', 'db_items')) return $listless;

    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    $rows = $db->query(
        "SELECT i.id, i.item_name, IFNULL(pi.prescription_required, 0) AS parent_required, IFNULL(i.prescription_required, 0) AS self_required
           FROM db_items i
           LEFT JOIN db_items pi ON pi.id = i.parent_id
          WHERE i.store_id = ? AND i.id IN ($placeholders)",
        array_merge([$storeId], $itemIds)
    )->result();

    $names = [];
    foreach ($rows as $row) {
        if ((int)$row->self_required === 1 || (int)$row->parent_required === 1) {
            $names[] = trim((string)$row->item_name) !== '' ? $row->item_name : ('Item #' . $row->id);
        }
    }
    if (!$names) return $listless;

    $medicalNoteId = (int)$medicalNoteId;
    if ($medicalNoteId <= 0) {
        if ($asList) return $names;
        return 'This sale includes prescription-only medicine (' . implode(', ', $names) . '). '
            . "Record or open the patient's prescription first, then link it to this sale.";
    }
    if (!$db->field_exists('review_status', 'db_medical_notes')) {
        return 'Prescription review is not set up on this store yet, so prescription-only medicine cannot be dispensed against note #'
            . $medicalNoteId . '. Contact MartPoint support.';
    }
    $note = $db->select('id, customer_id, review_status')
        ->where('id', $medicalNoteId)->where('store_id', $storeId)
        ->get('db_medical_notes')->row();
    if (!$note) {
        return "The linked prescription record (#$medicalNoteId) does not belong to this store. "
            . 'Pick the patient’s prescription from this store’s records.';
    }
    if ((string)$note->review_status !== 'approved') {
        $status = ($note->review_status === 'rejected') ? 'rejected' : 'still awaiting pharmacist review';
        return 'This prescription is ' . $status . ', so the medicine cannot be dispensed yet. '
            . 'A pharmacist must approve the prescription record first.';
    }
    $customerId = (int)$customerId;
    if ($customerId <= 0) {
        return 'Prescription-only medicines need a patient. Select the customer the prescription was written for.';
    }
    if ((int)$note->customer_id !== $customerId) {
        return 'The linked prescription belongs to a different patient than the customer on this sale. '
            . 'Link the prescription record that matches customer #' . $customerId . '.';
    }
    return $listless;
}
