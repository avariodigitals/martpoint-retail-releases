#!/usr/bin/env python3
"""
Patch Store_model.php and Store_profile_model.php for db_store modularization.

Run:
    python3 /Users/ralphmore/Herd/martpointretailapp/apply_store_modular_patch_v2.py
"""

import re

BASE = "/Users/ralphmore/Herd/martpointretailapp/application/models/"

def patch_store_model():
    path = BASE + "Store_model.php"
    with open(path, "r") as f:
        lines = f.readlines()

    # Find the start of save_registration db_store block
    start = None
    for i, line in enumerate(lines):
        if '$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");' in line:
            start = i
            break

    if start is None:
        print("ERROR: Could not find save_registration start in Store_model.php")
        return

    # Find the end of the db_store insert block (the insert_id line)
    end = None
    for i in range(start, min(start + 120, len(lines))):
        if "$store_id = $this->db->insert_id();" in lines[i]:
            end = i
            break

    if end is None:
        print("ERROR: Could not find save_registration end in Store_model.php")
        return

    replacement = """\t\t$this->db->query(\"ALTER TABLE db_store AUTO_INCREMENT = 1\");
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
\t\t\t'created_date'\t\t\t\t=> $CUR_DATE,
\t\t\t'created_time'\t\t\t\t=> $CUR_TIME,
\t\t\t'created_by'\t\t\t\t=> $first_name,
\t\t\t'system_ip'\t\t\t\t\t=> $SYSTEM_IP,
\t\t\t'system_name'\t\t\t\t=> $SYSTEM_NAME,
\t\t\t'status'\t\t\t\t\t=> 1,
\t\t);

\t\t\t$this->db->select(\"count(*) as store_code_count\");
\t\t\t$this->db->where(\"upper(store_code)\", strtoupper($store_code));
\t\t\t$store_code_count = $this->db->get('db_store')->row()->store_code_count;
\t\t\tif($store_code_count>0){
\t\t\t\techo \"Sorry! Store Code Already Exist!\\nPlease Change Store Code\";exit();
\t\t\t}

\t\t\t$q1 = $this->db->insert('db_store', $data);
\t\t\tif(!$q1){
\t\t\t\techo \"failed\";exit();
\t\t\t}

\t\t\t$store_id = $this->db->insert_id();

\t\t\t// Seed modular settings for this new store
\t\t\t$this->seed_modular_settings($store_id);

\t\t\t// Apply registration-specific overrides from the form
\t\t\tif ($this->db->table_exists('db_store_inventory_settings')) {
\t\t\t\t$inventory = array(
\t\t\t\t\t'category_init' => $category_init,
\t\t\t\t\t'item_init' => $item_init,
\t\t\t\t\t'supplier_init' => $supplier_init,
\t\t\t\t\t'purchase_init' => $purchase_init,
\t\t\t\t\t'purchase_return_init' => $purchase_return_init,
\t\t\t\t\t'customer_init' => $customer_init,
\t\t\t\t\t'sales_init' => $sales_init,
\t\t\t\t\t'sales_return_init' => $sales_return_init,
\t\t\t\t\t'expense_init' => $expense_init,
\t\t\t\t\t'quotation_init' => $quotation_init,
\t\t\t\t\t'money_transfer_init' => $money_transfer_init,
\t\t\t\t\t'accounts_init' => $accounts_init,
\t\t\t\t\t'sales_payment_init' => $sales_payment_init,
\t\t\t\t\t'sales_return_payment_init' => $sales_return_payment_init,
\t\t\t\t\t'purchase_payment_init' => $purchase_payment_init,
\t\t\t\t\t'purchase_return_payment_init' => $purchase_return_payment_init,
\t\t\t\t\t'expense_payment_init' => $expense_payment_init,
\t\t\t\t\t'cust_advance_init' => $cust_advance_init,
\t\t\t\t);
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_inventory_settings', $inventory);
\t\t\t}

\t\t\tif ($this->db->table_exists('db_store_receipt_settings')) {
\t\t\t\t$receipt = array(
\t\t\t\t\t'sales_invoice_format_id' => $sales_invoice_format_id,
\t\t\t\t\t'pos_invoice_format_id' => $pos_invoice_format_id,
\t\t\t\t\t'sales_invoice_footer_text' => $sales_invoice_footer_text,
\t\t\t\t\t'invoice_terms' => $invoice_terms,
\t\t\t\t\t'previous_balance_bit' => $previous_balance_bit,
\t\t\t\t\t'round_off' => $round_off,
\t\t\t\t\t'change_return' => $change_return,
\t\t\t\t\t'decimals' => $decimals,
\t\t\t\t\t'qty_decimals' => $qty_decimals,
\t\t\t\t);
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_receipt_settings', $receipt);
\t\t\t}

\t\t\tif ($this->db->table_exists('db_store_pos_settings')) {
\t\t\t\t$pos = array(
\t\t\t\t\t'sales_discount' => $sales_discount,
\t\t\t\t\t'mrp_column' => $mrp_column,
\t\t\t\t\t'show_signature' => $show_signature,
\t\t\t\t\t'previous_balance_bit' => $previous_balance_bit,
\t\t\t\t);
\t\t\t\t$this->db->where('store_id', $store_id)->update('db_store_pos_settings', $pos);
\t\t\t}

\t\t\tif ($this->db->table_exists('db_store_settings')) {
\t\t\t\tmp_set_store_setting($store_id, 'general', 'language_id', $language_id, 'int');
\t\t\t}
"""

    new_lines = lines[:start] + [replacement] + lines[end+1:]
    with open(path, "w") as f:
        f.writelines(new_lines)
    print(f"Patched save_registration() in Store_model.php (lines {start+1}-{end+1})")

def patch_store_profile_model():
    path = BASE + "Store_profile_model.php"
    with open(path, "r") as f:
        content = f.read()

    # Replace the data array and the update block with modular equivalents
    old_start = "\t\t$data = array("
    old_end = "\t\treturn \"failed\";"

    idx_start = content.find(old_start)
    if idx_start == -1:
        print("ERROR: Could not find update_store data array in Store_profile_model.php")
        return

    idx_end = content.find(old_end, idx_start)
    if idx_end == -1:
        print("ERROR: Could not find update_store end in Store_profile_model.php")
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
\t\t\t'quotation_init'\t\t=> $quotation_init,
\t\t\t'money_transfer_init'\t=> $money_transfer_init,
\t\t\t'accounts_init'\t\t\t\t=> $accounts_init,
\t\t\t'sales_payment_init'\t\t=> $sales_payment_init,
\t\t\t'sales_return_payment_init'\t=> $sales_return_payment_init,
\t\t\t'purchase_payment_init'\t\t=> $purchase_payment_init,
\t\t\t'purchase_return_payment_init'\t=> $purchase_return_payment_init,
\t\t\t'expense_payment_init'\t=> $expense_payment_init,
\t\t\t'cust_advance_init'\t=> $cust_advance_init,
\t\t);

\t\t// Modular receipt settings
\t\t$receipt_data = array(
\t\t\t'sales_invoice_format_id'\t=> $sales_invoice_format_id,
\t\t\t'pos_invoice_format_id'\t\t=> $pos_invoice_format_id,
\t\t\t'sales_invoice_footer_text'\t=> $sales_invoice_footer_text,
\t\t\t'invoice_terms'\t\t\t\t=> $invoice_terms,
\t\t\t'previous_balance_bit'\t=> $previous_balance_bit,
\t\t\t'round_off'\t\t\t\t\t=> $round_off,
\t\t\t'change_return'\t\t\t\t=> $change_return,
\t\t\t'decimals'\t\t\t\t\t=> $decimals,
\t\t\t'qty_decimals'\t\t\t\t=> $qty_decimals,
\t\t\t't_and_c_status'\t\t=> $t_and_c_status,
\t\t\t't_and_c_status_pos'\t=> $t_and_c_status_pos,
\t\t\t'number_to_words'\t\t=> $number_to_words,
\t\t);

\t\t// Modular POS settings
\t\t$pos_data = array(
\t\t\t'sales_discount'\t\t=> $sales_discount,
\t\t\t'mrp_column'\t\t\t\t=> $mrp_column,
\t\t\t'show_signature'\t\t=> $show_signature,
\t\t\t'previous_balance_bit'\t=> $previous_balance_bit,
\t\t\t'default_account_id'\t=> (isset($default_account_id) && !empty($default_account_id))?$default_account_id:null,
\t\t);

\t\t// Modular industry settings
\t\t$industry_data = array(
\t\t\t'industry_type'\t\t\t=> $industry_type,
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
\t\t\t$this->db->select(\"count(*) as store_code_count\");
\t\t\t$this->db->where(\"upper(store_code)\", strtoupper($store_code));
\t\t\t$this->db->where(\"id !=\", $q_id);
\t\t\t$store_code_count = $this->db->get('db_store')->row()->store_code_count;
\t\t\tif($store_code_count>0){
\t\t\t\techo \"Sorry! Store Code Already Exist!\\nPlease Change Store Code\";exit();
\t\t\t}

\t\t\t$q1 = $this->db->where('id',$q_id)->update('db_store', $data);
\t\t\tif(!$q1){
\t\t\t\t$this->db->trans_rollback();
\t\t\t\treturn \"failed\";
\t\t\t}

\t\t\t// Write to modular tables
\t\t\tif($this->db->table_exists('db_store_inventory_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_inventory_settings', $inventory_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_receipt_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_receipt_settings', $receipt_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_pos_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_pos_settings', $pos_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_industry_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_industry_settings', $industry_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_tax_settings') && !empty($tax_data)){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_tax_settings', $tax_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_theme_settings') && !empty($theme_data)){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_theme_settings', $theme_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_storefront_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_storefront_settings', $storefront_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_payment_settings')){
\t\t\t\t$this->_mp_upsert_modular_row($q_id, 'db_store_payment_settings', $payment_data);
\t\t\t}
\t\t\tif($this->db->table_exists('db_store_settings')){
\t\t\t\tmp_set_store_setting($q_id, 'general', 'language_id', $language_id, 'int');
\t\t\t\tforeach($nin_data as $k => $v){
\t\t\t\t\tmp_set_store_setting($q_id, 'nin_api', $k, $v, 'string');
\t\t\t\t}
\t\t\t}

\t\t\t$this->db->trans_commit();
\t\t\t$this->session->unset_userdata('currency');
\t\t\treturn \"success\";
\t}

\t/**
\t * Upsert helper for modular settings tables.
\t */
\tprivate function _mp_upsert_modular_row($store_id, $table, $data){
\t\tif(empty($data)){
\t\t\treturn true;
\t\t}
\t\t$exists = $this->db->where('store_id', $store_id)->get($table)->num_rows();
\t\tif($exists){
\t\t\treturn $this->db->where('store_id', $store_id)->update($table, $data);
\t\t}
\t\t$data['store_id'] = $store_id;
\t\treturn $this->db->insert($table, $data);
\t}
"""

    content = content[:idx_start] + replacement + content[idx_end:]
    with open(path, "w") as f:
        f.write(content)
    print("Patched update_store() in Store_profile_model.php")

if __name__ == "__main__":
    patch_store_model()
    patch_store_profile_model()
    print("Patch complete.")
