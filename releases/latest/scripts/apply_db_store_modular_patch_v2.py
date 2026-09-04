#!/usr/bin/env python3
"""
Robust db_store modularization patch for Store_model.php and Store_profile_model.php.

Uses function-boundary detection (brace counting) and marker matching to replace
only the intended blocks. Backs up the original files before patching.

Run:
    python3 /Users/ralphmore/Herd/martpointretailapp/apply_db_store_modular_patch_v2.py
"""

import os
import re
import shutil
from datetime import datetime

BASE = "/Users/ralphmore/Herd/martpointretailapp/application/models/"


def backup(path):
    ts = datetime.now().strftime("%Y%m%d_%H%M%S")
    bak = path + ".bak_" + ts
    shutil.copy2(path, bak)
    print(f"Backed up {path} -> {bak}")
    return bak


def find_function_bounds(lines, func_name):
    """Return (start_index, end_index) 0-based line indices of the function body,
    including the opening and closing braces."""
    pattern = re.compile(r"^\s*(public\s+)?function\s+" + re.escape(func_name) + r"\s*\(")
    start = None
    for i, line in enumerate(lines):
        if pattern.match(line):
            start = i
            break
    if start is None:
        return None

    # Find the opening brace of the function. Usually on the same line or next line(s).
    brace_open = None
    for i in range(start, min(start + 5, len(lines))):
        if "{" in lines[i]:
            brace_open = i
            break
    if brace_open is None:
        return None

    # Count braces from the function's opening brace to find its closing brace.
    depth = 0
    for i in range(brace_open, len(lines)):
        for ch in lines[i]:
            if ch == "{":
                depth += 1
            elif ch == "}":
                depth -= 1
                if depth == 0:
                    return (start, i)
    return None


def find_marker_in_range(lines, start, end, marker, occurrence=1):
    """Find the line index of the Nth occurrence of marker within [start, end]."""
    count = 0
    for i in range(start, end + 1):
        if marker in lines[i]:
            count += 1
            if count == occurrence:
                return i
    return None


def patch_store_model():
    path = os.path.join(BASE, "Store_model.php")
    backup(path)
    with open(path, "r") as f:
        lines = f.readlines()

    # --- Patch save_registration() ---
    bounds = find_function_bounds(lines, "save_registration")
    if not bounds:
        print("ERROR: Could not locate save_registration() in Store_model.php")
        return
    fstart, fend = bounds

    start = find_marker_in_range(lines, fstart, fend, '$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");')
    end = find_marker_in_range(lines, start, fend, '$store_id = $this->db->insert_id();')
    if start is None or end is None:
        print(f"ERROR: Could not locate save_registration block in Store_model.php (start={start}, end={end})")
        return

    new_save = """\t\t$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
\t\t$this->db->trans_begin();
\t\t// Core store identity only - everything else lives in modular tables
\t\t$data = array(
\t\t\t'store_code'\t\t\t\t=> $store_code,
\t\t\t'store_name'\t\t\t\t=> $store_name,
\t\t\t'mobile'\t\t\t\t\t=> $mobile,
\t\t\t'phone'\t\t\t\t\t\t=> '',
\t\t\t'email'\t\t\t\t\t\t=> $email,
\t\t\t'country'\t\t\t\t\t=> $country,
\t\t\t'state'\t\t\t\t\t\t=> $state,
\t\t\t'city'\t\t\t\t\t\t=> $city,
\t\t\t'address'\t\t\t\t\t=> ' ',
\t\t\t'postcode'\t\t\t\t\t=> '',
\t\t\t'currency_id'\t\t\t\t=> $currency,
\t\t\t'currency_placement'\t=> $currency_placement,
\t\t\t'timezone'\t\t\t\t\t=> $timezone,
\t\t\t'date_format'\t\t\t\t=> $date_format,
\t\t\t'time_format'\t\t\t\t=> $time_format,
\t\t\t/*System Info*/
\t\t\t'created_date' \t\t\t\t=> $CUR_DATE,
\t\t\t'created_time' \t\t\t\t=> $CUR_TIME,
\t\t\t'created_by' \t\t\t\t=> $first_name,
\t\t\t'system_ip' \t\t\t\t=> $SYSTEM_IP,
\t\t\t'system_name' \t\t\t\t=> $SYSTEM_NAME,
\t\t\t'status' \t\t\t\t\t=> 1,
\t\t);

\t\t$this->db->select("count(*) as store_code_count");
\t\t$this->db->where("upper(store_code)", strtoupper($store_code));
\t\t$store_code_count = $this->db->get('db_store')->row()->store_code_count;
\t\tif($store_code_count>0){
\t\t\techo "Sorry! Store Code Already Exist!\\nPlease Change Store Code";exit();
\t\t}

\t\t$q1 = $this->db->insert('db_store', $data);
\t\tif(!$q1){
\t\t\techo "failed";exit();
\t\t}

\t\t$store_id = $this->db->insert_id();

\t\t// Seed modular settings for this new store
\t\t$this->seed_modular_settings($store_id);

\t\t// Apply registration-specific overrides from the form
\t\tif ($this->db->table_exists('db_store_inventory_settings')) {
\t\t\t$inventory = array(
\t\t\t\t'category_init' => $category_init,
\t\t\t\t'item_init' => $item_init,
\t\t\t\t'supplier_init' => $supplier_init,
\t\t\t\t'purchase_init' => $purchase_init,
\t\t\t\t'purchase_return_init' => $purchase_return_init,
\t\t\t\t'customer_init' => $customer_init,
\t\t\t\t'sales_init' => $sales_init,
\t\t\t\t'sales_return_init' => $sales_return_init,
\t\t\t\t'expense_init' => $expense_init,
\t\t\t\t'quotation_init' => $quotation_init,
\t\t\t\t'money_transfer_init' => $money_transfer_init,
\t\t\t\t'accounts_init' => $accounts_init,
\t\t\t\t'sales_payment_init' => $sales_payment_init,
\t\t\t\t'sales_return_payment_init' => $sales_return_payment_init,
\t\t\t\t'purchase_payment_init' => $purchase_payment_init,
\t\t\t\t'purchase_return_payment_init' => $purchase_return_payment_init,
\t\t\t\t'expense_payment_init' => $expense_payment_init,
\t\t\t\t'cust_advance_init' => $cust_advance_init,
\t\t\t);
\t\t\t$this->db->where('store_id', $store_id)->update('db_store_inventory_settings', $inventory);
\t\t}

\t\tif ($this->db->table_exists('db_store_receipt_settings')) {
\t\t\t$receipt = array(
\t\t\t\t'sales_invoice_format_id' => $sales_invoice_format_id,
\t\t\t\t'pos_invoice_format_id' => $pos_invoice_format_id,
\t\t\t\t'sales_invoice_footer_text' => $sales_invoice_footer_text,
\t\t\t\t'invoice_terms' => $invoice_terms,
\t\t\t\t'previous_balance_bit' => $previous_balance_bit,
\t\t\t\t'round_off' => $round_off,
\t\t\t\t'change_return' => $change_return,
\t\t\t\t'decimals' => $decimals,
\t\t\t\t'qty_decimals' => $qty_decimals,
\t\t\t);
\t\t\t$this->db->where('store_id', $store_id)->update('db_store_receipt_settings', $receipt);
\t\t}

\t\tif ($this->db->table_exists('db_store_pos_settings')) {
\t\t\t$pos = array(
\t\t\t\t'sales_discount' => $sales_discount,
\t\t\t\t'mrp_column' => $mrp_column,
\t\t\t\t'show_signature' => $show_signature,
\t\t\t\t'previous_balance_bit' => $previous_balance_bit,
\t\t\t);
\t\t\t$this->db->where('store_id', $store_id)->update('db_store_pos_settings', $pos);
\t\t}

\t\tif ($this->db->table_exists('db_store_settings')) {
\t\t\tmp_set_store_setting($store_id, 'general', 'language_id', $language_id, 'int');
\t\t}
"""

    lines = lines[:start] + [new_save] + lines[end + 1:]

    # --- Patch verify_and_save() ---
    bounds = find_function_bounds(lines, "verify_and_save")
    if not bounds:
        print("ERROR: Could not locate verify_and_save() in Store_model.php")
        with open(path, "w") as f:
            f.writelines(lines)
        return
    fstart, fend = bounds

    # Replace from the first $data = array( inside verify_and_save to the first insert_id
    vstart = find_marker_in_range(lines, fstart, fend, '$data = array(', occurrence=1)
    vend = find_marker_in_range(lines, vstart, fend, '$store_id = $this->db->insert_id();')
    if vstart is None or vend is None:
        print(f"ERROR: Could not locate verify_and_save block in Store_model.php (vstart={vstart}, vend={vend})")
        with open(path, "w") as f:
            f.writelines(lines)
        return

    new_verify = """\t\t// Core db_store fields only - everything else lives in modular tables
\t\t$data = array(
\t\t\t'store_code'\t\t\t\t=> $store_code,
\t\t\t'store_name'\t\t\t\t=> $store_name,
\t\t\t'mobile'\t\t\t\t\t=> $mobile,
\t\t\t'phone'\t\t\t\t\t\t=> '',
\t\t\t'email'\t\t\t\t\t\t=> $email,
\t\t\t'country'\t\t\t\t\t=> $country,
\t\t\t'state'\t\t\t\t\t\t=> $state,
\t\t\t'city'\t\t\t\t\t\t=> $city,
\t\t\t'address'\t\t\t\t\t=> ' ',
\t\t\t'postcode'\t\t\t\t\t=> '',
\t\t\t'currency_id'\t\t\t\t=> $currency,
\t\t\t'currency_placement'\t=> $currency_placement,
\t\t\t'timezone'\t\t\t\t\t=> $timezone,
\t\t\t'date_format'\t\t\t\t=> $date_format,
\t\t\t'time_format'\t\t\t\t=> $time_format,
\t\t);

\t\t// Modular inventory settings
\t\t$inventory_data = array(
\t\t\t'category_init'\t\t\t\t=> $category_init,
\t\t\t'item_init'\t\t\t\t\t=> $item_init,
\t\t\t'supplier_init'\t\t\t\t=> $supplier_init,
\t\t\t'purchase_init'\t\t\t\t=> $purchase_init,
\t\t\t'purchase_return_init'\t=> $purchase_return_init,
\t\t\t'customer_init'\t\t\t\t=> $customer_init,
\t\t\t'sales_init'\t\t\t\t=> $sales_init,
\t\t\t'sales_return_init'\t\t=> $sales_return_init,
\t\t\t'expense_init'\t\t\t\t=> $expense_init,
\t\t\t'quotation_init'\t\t\t=> $quotation_init,
\t\t\t'money_transfer_init'\t\t=> $money_transfer_init,
\t\t\t'accounts_init'\t\t\t\t=> $accounts_init,
\t\t\t'sales_payment_init'\t\t=> $sales_payment_init,
\t\t\t'sales_return_payment_init'\t=> $sales_return_payment_init,
\t\t\t'purchase_payment_init'\t\t=> $purchase_payment_init,
\t\t\t'purchase_return_payment_init'\t=> $purchase_return_payment_init,
\t\t\t'expense_payment_init'\t\t=> $expense_payment_init,
\t\t\t'cust_advance_init'\t\t\t=> $cust_advance_init,
\t\t);

\t\t// Modular receipt settings
\t\t$receipt_data = array(
\t\t\t'sales_invoice_format_id'\t\t=> $sales_invoice_format_id,
\t\t\t'pos_invoice_format_id'\t\t\t=> $pos_invoice_format_id,
\t\t\t'sales_invoice_footer_text'\t=> $sales_invoice_footer_text,
\t\t\t'invoice_terms'\t\t\t\t=> $invoice_terms,
\t\t\t'previous_balance_bit'\t\t=> $previous_balance_bit,
\t\t\t'round_off'\t\t\t\t\t=> $round_off,
\t\t\t'change_return'\t\t\t\t=> $change_return,
\t\t\t'decimals'\t\t\t\t\t=> $decimals,
\t\t\t'qty_decimals'\t\t\t\t=> $qty_decimals,
\t\t\t't_and_c_status'\t\t\t=> $t_and_c_status,
\t\t\t't_and_c_status_pos'\t\t=> $t_and_c_status_pos,
\t\t\t'number_to_words'\t\t\t=> $number_to_words,
\t\t);

\t\t// Modular POS settings
\t\t$pos_data = array(
\t\t\t'sales_discount'\t\t\t=> $sales_discount,
\t\t\t'mrp_column'\t\t\t\t=> $mrp_column,
\t\t\t'show_signature'\t\t\t=> $show_signature,
\t\t\t'previous_balance_bit'\t\t=> $previous_balance_bit,
\t\t\t'default_account_id'\t\t=> (!empty($default_account_id))?$default_account_id:null,
\t\t);

\t\t// Modular theme settings
\t\t$theme_data = array();
\t\tif(!empty($store_logo)){
\t\t\t$theme_data['store_logo'] = $store_logo;
\t\t}
\t\tif(!empty($signature)){
\t\t\t$theme_data['signature'] = $signature;
\t\t}

\t\t// Modular tax settings
\t\t$tax_data = array();
\t\tif(gst_number()){
\t\t\t$tax_data['gst_no'] = $gst_no;
\t\t}
\t\tif(vat_number()){
\t\t\t$tax_data['vat_no'] = $vat_no;
\t\t}
\t\tif(pan_number()){
\t\t\t$tax_data['pan_no'] = $pan_no;
\t\t}

\t\tif($command=='save'){
\t\t\t$this->db->select("count(*) as store_code_count");
\t\t\t$this->db->where("upper(store_code)", strtoupper($store_code));
\t\t\t$store_code_count = $this->db->get('db_store')->row()->store_code_count;
\t\t\tif($store_code_count>0){
\t\t\t\techo "Sorry! Store Code Already Exist!\\nPlease Change Store Code";exit();
\t\t\t}
\t\t\t$extra_info = array(
\t\t\t\t'invoice_view'\t\t\t\t=> 1,
\t\t\t\t'sms_status'\t\t\t\t=> 0,
\t\t\t\t'language_id'\t\t\t\t=> $language_id,
\t\t\t\t/*System Info*/
\t\t\t\t'created_date' \t\t\t\t=> $CUR_DATE,
\t\t\t\t'created_time' \t\t\t\t=> $CUR_TIME,
\t\t\t\t'created_by' \t\t\t\t=> $CUR_USERNAME,
\t\t\t\t'system_ip' \t\t\t\t=> $SYSTEM_IP,
\t\t\t\t'system_name' \t\t\t\t=> $SYSTEM_NAME,
\t\t\t\t'status' \t\t\t\t\t=> 1,
\t\t\t);
\t\t\t$data=array_merge($data,$extra_info);
\t\t\t$q1 = $this->db->insert('db_store', $data);
\t\t\t$store_id = $this->db->insert_id();

\t\t\t// Seed modular settings for this new store
\t\t\t$this->seed_modular_settings($store_id);

\t\t\t// Apply form overrides to modular tables
\t\t\tif ($this->db->table_exists('db_store_inventory_settings')) {
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_inventory_settings', $inventory_data);
\t\t\t}
\t\t\tif ($this->db->table_exists('db_store_receipt_settings')) {
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_receipt_settings', $receipt_data);
\t\t\t}
\t\t\tif ($this->db->table_exists('db_store_pos_settings')) {
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_pos_settings', $pos_data);
\t\t\t}
\t\t\tif ($this->db->table_exists('db_store_theme_settings') && !empty($theme_data)) {
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_theme_settings', $theme_data);
\t\t\t}
\t\t\tif ($this->db->table_exists('db_store_tax_settings') && !empty($tax_data)) {
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_tax_settings', $tax_data);
\t\t\t}
"""

    lines = lines[:vstart] + [new_verify] + lines[vend + 1:]

    with open(path, "w") as f:
        f.writelines(lines)
    print(f"Patched Store_model.php (save_registration lines {start+1}-{end+1}, verify_and_save lines {vstart+1}-{vend+1})")


def patch_store_profile_model():
    path = os.path.join(BASE, "Store_profile_model.php")
    backup(path)
    with open(path, "r") as f:
        lines = f.readlines()

    bounds = find_function_bounds(lines, "update_store")
    if not bounds:
        print("ERROR: Could not locate update_store() in Store_profile_model.php")
        return
    fstart, fend = bounds

    start = find_marker_in_range(lines, fstart, fend, '$data = array(', occurrence=1)
    end = find_marker_in_range(lines, start, fend, "$this->db->where('id',$q_id)->update('db_store', $data);")
    if start is None or end is None:
        print(f"ERROR: Could not locate update_store block in Store_profile_model.php (start={start}, end={end})")
        return

    replacement = """\t\t// Core db_store fields only
\t\t$data = array(
\t\t\t'store_code'\t\t\t\t=> $store_code,
\t\t\t'store_name'\t\t\t\t=> $store_name,
\t\t\t'mobile'\t\t\t\t\t=> $mobile,
\t\t\t'phone'\t\t\t\t\t\t=> $phone,
\t\t\t'email'\t\t\t\t\t\t=> $email,
\t\t\t'country'\t\t\t\t\t=> $country,
\t\t\t'state'\t\t\t\t\t\t=> $state,
\t\t\t'city'\t\t\t\t\t\t=> $city,
\t\t\t'address'\t\t\t\t\t=> $address,
\t\t\t'postcode'\t\t\t\t\t=> $postcode,
\t\t\t'currency_id'\t\t\t\t=> $currency,
\t\t\t'currency_placement'\t=> $currency_placement,
\t\t\t'timezone'\t\t\t\t\t=> $timezone,
\t\t\t'date_format'\t\t\t\t=> $date_format,
\t\t\t'time_format'\t\t\t\t=> $time_format,
\t\t);

\t\t// Modular inventory settings
\t\t$inventory_data = array(
\t\t\t'category_init'\t\t\t\t=> $category_init,
\t\t\t'item_init'\t\t\t\t\t=> $item_init,
\t\t\t'supplier_init'\t\t\t\t=> $supplier_init,
\t\t\t'purchase_init'\t\t\t\t=> $purchase_init,
\t\t\t'purchase_return_init'\t=> $purchase_return_init,
\t\t\t'customer_init'\t\t\t\t=> $customer_init,
\t\t\t'sales_init'\t\t\t\t=> $sales_init,
\t\t\t'sales_return_init'\t\t=> $sales_return_init,
\t\t\t'expense_init'\t\t\t\t=> $expense_init,
\t\t\t'quotation_init'\t\t\t=> $quotation_init,
\t\t\t'money_transfer_init'\t\t=> $money_transfer_init,
\t\t\t'accounts_init'\t\t\t\t=> $accounts_init,
\t\t\t'sales_payment_init'\t\t=> $sales_payment_init,
\t\t\t'sales_return_payment_init'\t=> $sales_return_payment_init,
\t\t\t'purchase_payment_init'\t\t=> $purchase_payment_init,
\t\t\t'purchase_return_payment_init'\t=> $purchase_return_payment_init,
\t\t\t'expense_payment_init'\t\t=> $expense_payment_init,
\t\t\t'cust_advance_init'\t\t\t=> $cust_advance_init,
\t\t);

\t\t// Modular receipt settings
\t\t$receipt_data = array(
\t\t\t'sales_invoice_format_id'\t\t=> $sales_invoice_format_id,
\t\t\t'pos_invoice_format_id'\t\t\t=> $pos_invoice_format_id,
\t\t\t'sales_invoice_footer_text'\t=> $sales_invoice_footer_text,
\t\t\t'invoice_terms'\t\t\t\t=> $invoice_terms,
\t\t\t'previous_balance_bit'\t\t=> $previous_balance_bit,
\t\t\t'round_off'\t\t\t\t\t=> $round_off,
\t\t\t'change_return'\t\t\t\t=> $change_return,
\t\t\t'decimals'\t\t\t\t\t=> $decimals,
\t\t\t'qty_decimals'\t\t\t\t=> $qty_decimals,
\t\t\t't_and_c_status'\t\t\t=> $t_and_c_status,
\t\t\t't_and_c_status_pos'\t\t=> $t_and_c_status_pos,
\t\t\t'number_to_words'\t\t\t=> $number_to_words,
\t\t);

\t\t// Modular POS settings
\t\t$pos_data = array(
\t\t\t'sales_discount'\t\t\t=> $sales_discount,
\t\t\t'mrp_column'\t\t\t\t=> $mrp_column,
\t\t\t'show_signature'\t\t\t=> $show_signature,
\t\t\t'previous_balance_bit'\t\t=> $previous_balance_bit,
\t\t\t'default_account_id'\t\t=> (isset($default_account_id) && !empty($default_account_id))?$default_account_id:null,
\t\t);

\t\t// Modular industry settings
\t\t$industry_data = array(
\t\t\t'industry_type'\t\t\t\t=> $industry_type,
\t\t\t'business_model'\t\t\t=> $business_model,
\t\t\t'workflow_template_key'\t\t=> $workflow_template_key,
\t\t\t'dashboard_template_key'\t\t=> $dashboard_template_key,
\t\t\t'storefront_theme_key'\t\t=> $storefront_theme_key,
\t\t\t'feature_flags_json'\t\t=> $feature_flags_json,
\t\t\t'label_overrides_json'\t\t=> $label_overrides_json,
\t\t\t'industry_settings_json'\t=> $industry_settings_json,
\t\t);

\t\t// Modular tax settings
\t\t$tax_data = array();
\t\tif(gst_number()){
\t\t\t$tax_data['gst_no'] = $gst_no;
\t\t}
\t\tif(vat_number()){
\t\t\t$tax_data['vat_no'] = $vat_no;
\t\t}
\t\tif(pan_number()){
\t\t\t$tax_data['pan_no'] = $pan_no;
\t\t}

\t\t// Modular theme settings
\t\t$theme_data = array();
\t\tif(!empty($store_logo)){
\t\t\t$theme_data['store_logo'] = $store_logo;
\t\t}
\t\tif(!empty($signature)){
\t\t\t$theme_data['signature'] = $signature;
\t\t}

\t\t// Modular storefront settings
\t\t$storefront_data = array(
\t\t\t'store_website' => $store_website,
\t\t);
\t\tif(!empty($storefront_theme_key)){
\t\t\t$storefront_data['storefront_theme_key'] = $storefront_theme_key;
\t\t}

\t\t// Modular payment settings
\t\t$payment_data = array(
\t\t\t'bank_details' => $bank_details,
\t\t);

\t\t// NIN API key/value settings
\t\t$nin_data = array(
\t\t\t'nin_api_enabled' => $nin_api_enabled,
\t\t\t'nin_api_url' => $nin_api_url,
\t\t\t'nin_api_key' => $nin_api_key,
\t\t\t'nin_api_provider' => $nin_api_provider,
\t\t);

\t\t// Store code uniqueness check
\t\t$this->db->select("count(*) as store_code_count");
\t\t$this->db->where("upper(store_code)", strtoupper($store_code));
\t\t$this->db->where("id !=", $q_id);
\t\t$store_code_count = $this->db->get('db_store')->row()->store_code_count;
\t\tif($store_code_count>0){
\t\t\techo "Sorry! Store Code Already Exist!\\nPlease Change Store Code";exit();
\t\t}

\t\t$q1 = $this->db->where('id',$q_id)->update('db_store', $data);
\t\tif(!$q1){
\t\t\t$this->db->trans_rollback();
\t\t\treturn "failed";
\t\t}

\t\t// Write to modular tables
\t\tif($this->db->table_exists('db_store_inventory_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_inventory_settings', $inventory_data);
\t\t}
\t\tif($this->db->table_exists('db_store_receipt_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_receipt_settings', $receipt_data);
\t\t}
\t\tif($this->db->table_exists('db_store_pos_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_pos_settings', $pos_data);
\t\t}
\t\tif($this->db->table_exists('db_store_industry_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_industry_settings', $industry_data);
\t\t}
\t\tif($this->db->table_exists('db_store_tax_settings') && !empty($tax_data)){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_tax_settings', $tax_data);
\t\t}
\t\tif($this->db->table_exists('db_store_theme_settings') && !empty($theme_data)){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_theme_settings', $theme_data);
\t\t}
\t\tif($this->db->table_exists('db_store_storefront_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_storefront_settings', $storefront_data);
\t\t}
\t\tif($this->db->table_exists('db_store_payment_settings')){
\t\t\t_mp_set_structured_setting($q_id, 'db_store_payment_settings', $payment_data);
\t\t}
\t\tif($this->db->table_exists('db_store_settings')){
\t\t\tmp_set_store_setting($q_id, 'general', 'language_id', $language_id, 'int');
\t\t\tforeach($nin_data as $k => $v){
\t\t\t\tmp_set_store_setting($q_id, 'nin_api', $k, $v, 'string');
\t\t\t}
\t\t}

\t\t$this->db->trans_commit();
\t\t$this->session->unset_userdata('currency');
\t\treturn "success";
"""

    lines = lines[:start] + [replacement] + lines[end + 1:]

    with open(path, "w") as f:
        f.writelines(lines)
    print(f"Patched update_store() in Store_profile_model.php (lines {start+1}-{end+1})")


if __name__ == "__main__":
    patch_store_model()
    patch_store_profile_model()
    print("Patch complete. Please run the verification script.")
