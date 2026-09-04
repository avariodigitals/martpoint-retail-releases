<?php
/**
 * One-time patch to update Store_model.php and Store_profile_model.php
 * for the db_store modularization.
 *
 * Run this from the repository root:
 *   php apply_store_modular_patch.php
 */

$base = __DIR__ . '/application/models/';

$store_model = $base . 'Store_model.php';
$profile_model = $base . 'Store_profile_model.php';

// ---------------------------------------------------------------------------
// Patch Store_model.php
// ---------------------------------------------------------------------------
if (file_exists($store_model)) {
    $content = file_get_contents($store_model);

    // Replace store_making_codes() body
    $old = <<<'PHP'
	public function store_making_codes(){
		 /*Create Store Code*/
		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
        $store_id=$this->db->query('select max(id)+1 as store_id from db_store')->row()->store_id;
		$data = array();
        $data['store_code'] = 'ST'.str_pad($store_id, 4, '0', STR_PAD_LEFT);
        $data['category_init'] ="CT"."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['item_init'] ="IT".str_pad($store_id, 2, '0', STR_PAD_LEFT);
        $data['supplier_init'] ="SU"."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['purchase_init'] ="PU"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['purchase_return_init'] ="PR"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['customer_init'] ="CU"."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['sales_init'] ="SL"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['sales_return_init'] ="SR"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['expense_init'] ="EX"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['accounts_init'] ="AC"."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['quotation_init'] ="QT"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['money_transfer_init'] ="MT"."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['sales_payment_init'] ="SP"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['sales_return_payment_init'] ="SRP"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['purchase_payment_init'] ="PP"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['purchase_return_payment_init'] ="PRP"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['expense_payment_init'] ="XP"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['cust_advance_init'] ="ADV"."/".date("Y")."/".str_pad($store_id, 2, '0', STR_PAD_LEFT)."/";
        $data['language_id'] =1;
        $data['sales_discount'] =0;
        $data['change_return'] =1;
        $data['mrp_column'] =1;
        $data['show_signature'] =0;
        $data['previous_balance_bit'] =1;
        $data['round_off'] =1;
        $data['sales_invoice_format_id'] =3;
        $data['pos_invoice_format_id'] =1;
        $data['sales_invoice_footer_text'] ='This is footer text. It is in Store Management.';
        $data['invoice_terms'] ='';
        $data['t_and_c_status'] =1;
        $data['t_and_c_status_pos'] =1;
        $data['qty_decimals'] =2;
        $data['number_to_words'] ='Default';
        $data['default_account_id'] ='';
        $data['cash_account_id'] ='';
        return $data;
	}
PHP;

    $new = <<<'PHP'
	public function store_making_codes(){
		 /*Create Store Code*/
		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
        $store_id=$this->db->query('select max(id)+1 as store_id from db_store')->row()->store_id;
		$data = array();
        $data['store_code'] = 'ST'.str_pad($store_id, 4, '0', STR_PAD_LEFT);
        return $data;
	}

	/**
	 * Default settings for the modular store configuration tables.
	 * Called during store registration to seed the new tables.
	 */
	public function store_modular_defaults($store_id){
		$sid = str_pad($store_id, 2, '0', STR_PAD_LEFT);
		return array(
			// Inventory/document init codes
			'category_init' => 'CT/'.$sid.'/',
			'item_init' => 'IT'.$sid,
			'supplier_init' => 'SU/'.$sid.'/',
			'purchase_init' => 'PU/'.date("Y").'/'.$sid.'/',
			'purchase_return_init' => 'PR/'.date("Y").'/'.$sid.'/',
			'customer_init' => 'CU/'.$sid.'/',
			'sales_init' => 'SL/'.date("Y").'/'.$sid.'/',
			'sales_return_init' => 'SR/'.date("Y").'/'.$sid.'/',
			'expense_init' => 'EX/'.date("Y").'/'.$sid.'/',
			'accounts_init' => 'AC/'.$sid.'/',
			'quotation_init' => 'QT/'.date("Y").'/'.$sid.'/',
			'money_transfer_init' => 'MT/'.$sid.'/',
			'sales_payment_init' => 'SP/'.date("Y").'/'.$sid.'/',
			'sales_return_payment_init' => 'SRP/'.date("Y").'/'.$sid.'/',
			'purchase_payment_init' => 'PP/'.date("Y").'/'.$sid.'/',
			'purchase_return_payment_init' => 'PRP/'.date("Y").'/'.$sid.'/',
			'expense_payment_init' => 'XP/'.date("Y").'/'.$sid.'/',
			'cust_advance_init' => 'ADV/'.date("Y").'/'.$sid.'/',
			// Receipt settings
			'invoice_view' => 1,
			'sales_invoice_format_id' => 3,
			'pos_invoice_format_id' => 1,
			'sales_invoice_footer_text' => 'This is footer text. It is in Store Management.',
			'invoice_terms' => '',
			'previous_balance_bit' => 1,
			'round_off' => 1,
			'change_return' => 1,
			'decimals' => 2,
			'qty_decimals' => 2,
			'number_to_words' => 'Default',
			't_and_c_status' => 1,
			't_and_c_status_pos' => 1,
			// POS settings
			'sales_discount' => 0,
			'mrp_column' => 1,
			'show_signature' => 0,
			'default_account_id' => '',
			'cash_account_id' => '',
			// Notification settings
			'sms_status' => 0,
			// Industry settings
			'industry_type' => 'general_retail',
			'business_model' => 'product_based',
			'workflow_template_key' => 'retail_standard',
			'dashboard_template_key' => 'general_retail',
			'storefront_theme_key' => 'general_retail',
			// General settings
			'language_id' => 1,
		);
	}

	/**
	 * Seed the modular settings tables for a newly created store.
	 */
	public function seed_modular_settings($store_id){
		$defaults = $this->store_modular_defaults($store_id);

		if ($this->db->table_exists('db_store_inventory_settings')) {
			$inventory_keys = array('category_init','item_init','supplier_init','purchase_init','purchase_return_init','customer_init','sales_init','sales_return_init','expense_init','accounts_init','quotation_init','money_transfer_init','sales_payment_init','sales_return_payment_init','purchase_payment_init','purchase_return_payment_init','expense_payment_init','cust_advance_init');
			$inventory = array('store_id' => $store_id);
			foreach ($inventory_keys as $k) {
				$inventory[$k] = $defaults[$k];
			}
			$this->db->insert('db_store_inventory_settings', $inventory);
		}

		if ($this->db->table_exists('db_store_receipt_settings')) {
			$receipt_keys = array('invoice_view','sales_invoice_format_id','pos_invoice_format_id','sales_invoice_footer_text','invoice_terms','previous_balance_bit','round_off','change_return','decimals','qty_decimals','number_to_words','t_and_c_status','t_and_c_status_pos');
			$receipt = array('store_id' => $store_id);
			foreach ($receipt_keys as $k) {
				$receipt[$k] = $defaults[$k];
			}
			$this->db->insert('db_store_receipt_settings', $receipt);
		}

		if ($this->db->table_exists('db_store_pos_settings')) {
			$this->db->insert('db_store_pos_settings', array(
				'store_id' => $store_id,
				'sales_discount' => $defaults['sales_discount'],
				'mrp_column' => $defaults['mrp_column'],
				'show_signature' => $defaults['show_signature'],
				'previous_balance_bit' => $defaults['previous_balance_bit'],
				'default_account_id' => $defaults['default_account_id'],
				'cash_account_id' => $defaults['cash_account_id'],
			));
		}

		if ($this->db->table_exists('db_store_notification_settings')) {
			$this->db->insert('db_store_notification_settings', array(
				'store_id' => $store_id,
				'sms_status' => $defaults['sms_status'],
			));
		}

		if ($this->db->table_exists('db_store_industry_settings')) {
			$this->db->insert('db_store_industry_settings', array(
				'store_id' => $store_id,
				'industry_type' => $defaults['industry_type'],
				'business_model' => $defaults['business_model'],
				'workflow_template_key' => $defaults['workflow_template_key'],
				'dashboard_template_key' => $defaults['dashboard_template_key'],
				'storefront_theme_key' => $defaults['storefront_theme_key'],
			));
		}

		if ($this->db->table_exists('db_store_settings')) {
			mp_set_store_setting($store_id, 'general', 'language_id', $defaults['language_id'], 'int');
		}
	}
PHP;

    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
        echo "Patched store_making_codes() in Store_model.php\n";
    } else {
        echo "WARNING: Could not find store_making_codes() block in Store_model.php\n";
    }

    // Replace save_registration db_store insert + add modular seeding
    $new2 = <<<'PHP'
		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
		$this->db->trans_begin();
		// Core store identity only - everything else lives in modular tables
		$data = array(
			'store_code'				=> $store_code,
			'store_name'				=> $store_name,
			'mobile'					=> $mobile,
			'phone'						=> '',
			'email'						=> $email,
			'country'					=> $country,
			'state'						=> $state,
			'city'						=> $city,
			'address'					=> ' ',
			'postcode'					=> '',
			'currency_id'				=> $currency,
			'currency_placement'		=> $currency_placement,
			'timezone'					=> $timezone,
			'date_format'				=> $date_format,
			'time_format'				=> $time_format,
			/*System Info*/
			'created_date'				=> $CUR_DATE,
			'created_time'				=> $CUR_TIME,
			'created_by'				=> $first_name,
			'system_ip'					=> $SYSTEM_IP,
			'system_name'				=> $SYSTEM_NAME,
			'status'					=> 1,
		);

			$this->db->select("count(*) as store_code_count");
			$this->db->where("upper(store_code)", strtoupper($store_code));
			$store_code_count = $this->db->get('db_store')->row()->store_code_count;
			if($store_code_count>0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}

			$q1 = $this->db->insert('db_store', $data);
			if(!$q1){
				echo "failed";exit();
			}

			$store_id = $this->db->insert_id();

			// Seed modular settings for this new store
			$this->seed_modular_settings($store_id);

			// Apply any registration-specific overrides from the form
			if ($this->db->table_exists('db_store_inventory_settings')) {
				$inventory = array(
					'category_init' => $category_init,
					'item_init' => $item_init,
					'supplier_init' => $supplier_init,
					'purchase_init' => $purchase_init,
					'purchase_return_init' => $purchase_return_init,
					'customer_init' => $customer_init,
					'sales_init' => $sales_init,
					'sales_return_init' => $sales_return_init,
					'expense_init' => $expense_init,
					'quotation_init' => $quotation_init,
					'money_transfer_init' => $money_transfer_init,
					'accounts_init' => $accounts_init,
					'sales_payment_init' => $sales_payment_init,
					'sales_return_payment_init' => $sales_return_payment_init,
					'purchase_payment_init' => $purchase_payment_init,
					'purchase_return_payment_init' => $purchase_return_payment_init,
					'expense_payment_init' => $expense_payment_init,
					'cust_advance_init' => $cust_advance_init,
				);
				$this->db->where('store_id', $store_id)->update('db_store_inventory_settings', $inventory);
			}

			if ($this->db->table_exists('db_store_receipt_settings')) {
				$receipt = array(
					'sales_invoice_format_id' => $sales_invoice_format_id,
					'pos_invoice_format_id' => $pos_invoice_format_id,
					'sales_invoice_footer_text' => $sales_invoice_footer_text,
					'invoice_terms' => $invoice_terms,
					'previous_balance_bit' => $previous_balance_bit,
					'round_off' => $round_off,
					'change_return' => $change_return,
					'decimals' => $decimals,
					'qty_decimals' => $qty_decimals,
				);
				$this->db->where('store_id', $store_id)->update('db_store_receipt_settings', $receipt);
			}

			if ($this->db->table_exists('db_store_pos_settings')) {
				$pos = array(
					'sales_discount' => $sales_discount,
					'mrp_column' => $mrp_column,
					'show_signature' => $show_signature,
					'previous_balance_bit' => $previous_balance_bit,
				);
				$this->db->where('store_id', $store_id)->update('db_store_pos_settings', $pos);
			}

			if ($this->db->table_exists('db_store_settings')) {
				mp_set_store_setting($store_id, 'general', 'language_id', $language_id, 'int');
			}
PHP;

    // Match from "ALTER TABLE db_store AUTO_INCREMENT = 1" to "$store_id = $this->db->insert_id();"
    $pattern = '/\s*\$this->db->query\("ALTER TABLE db_store AUTO_INCREMENT = 1"\);\s*\$this->db->trans_begin\(\);\s*\$data = array\([^;]+\);\s*\$this->db->select\("count\(\*\) as store_code_count"\);\s*\$this->db->where\("upper\(store_code\)", strtoupper\(\$store_code\)\);\s*\$store_code_count = \$this->db->get\(\'db_store\'\)->row\(\)->store_code_count;\s*if\(\$store_code_count>0\)\{\s*echo "Sorry! Store Code Already Exist!\\nPlease Change Store Code";exit\(\);\s*\}\s*\$extra_info = array\([^;]+\);\s*\$data=array_merge\(\$data,\$extra_info\);\s*\$q1 = \$this->db->insert\(\'db_store\', \$data\);\s*if\(!\$q1\)\{\s*echo "failed";exit\(\);\s*\}\s*\$store_id = \$this->db->insert_id\(\);/s';

    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $new2, $content, 1);
        echo "Patched save_registration() in Store_model.php\n";
    } else {
        echo "WARNING: Could not find save_registration() insert block in Store_model.php\n";
    }

    file_put_contents($store_model, $content);
}

// ---------------------------------------------------------------------------
// Patch Store_profile_model.php
// ---------------------------------------------------------------------------
if (file_exists($profile_model)) {
    $content = file_get_contents($profile_model);

    $old = <<<'PHP'
		$data = array(
	    						'store_code'				=> $store_code,
	    						'store_name'				=> $store_name,
	    						'store_website'				=> $store_website,
	    						'mobile'					=> $mobile,
	    						'phone'						=> $phone,
	    						'email'						=> $email,
	    						'country'					=> $country,
	    						'state'						=> $state,
	    						'city'						=> $city,
	    						'address'					=> $address,
	    						'postcode'					=> $postcode,
	    						'bank_details'				=> $bank_details,
	    						'category_init'				=> $category_init,
	    						'item_init'					=> $item_init,
	    						'supplier_init'				=> $supplier_init,
	    						'purchase_init'				=> $purchase_init,
	    						'purchase_return_init'		=> $purchase_return_init,
	    						'customer_init'				=> $customer_init,
	    						'sales_init'				=> $sales_init,
	    						'sales_return_init'			=> $sales_return_init,
	    						'expense_init'				=> $expense_init,
	    						'quotation_init'			=> $quotation_init,
	    						'money_transfer_init'		=> $money_transfer_init,
	    						'accounts_init'				=> $accounts_init,
	    						'currency_id'				=> $currency,
	    						'currency_placement'		=> $currency_placement,
	    						'timezone'					=> $timezone,
	    						'date_format'				=> $date_format,
	    						'time_format'				=> $time_format,
	    						'sales_discount'			=> $sales_discount,
	    						'sales_discount'			=> $sales_discount,
	    						'change_return'				=> $change_return,
	    						'sales_invoice_format_id'	=> $sales_invoice_format_id,
	    						'pos_invoice_format_id'		=> $pos_invoice_format_id,
	    						'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
	    						'invoice_terms'				=> $invoice_terms,
	    						'round_off'					=> $round_off,
	    						'language_id'				=> $language_id,
	    						'decimals'					=> $decimals,
	    						'qty_decimals'					=> $qty_decimals,
	    						'sales_payment_init'		=> $sales_payment_init,
	    						'sales_return_payment_init'	=> $sales_return_payment_init,
	    						'purchase_payment_init'		=> $purchase_payment_init,
	    						'purchase_return_payment_init'	=> $purchase_return_payment_init,
	    						'expense_payment_init'	=> $expense_payment_init,
	    						'cust_advance_init'	=> $cust_advance_init,
	    						'mrp_column'	=> $mrp_column,
	    						'show_signature'	=> $show_signature,
	    						'previous_balance_bit'	=> $previous_balance_bit,
	    						't_and_c_status'	=> $t_and_c_status,
	    						't_and_c_status_pos'	=> $t_and_c_status_pos,
	    						'number_to_words'	=> $number_to_words,
	    						'default_account_id'	=> (isset($default_account_id) && !empty($default_account_id))?$default_account_id:null,
	    					);

		if(!empty($store_logo)){
			$data['store_logo']=$store_logo;
		}
		if(!empty($signature)){
			$data['signature']=$signature;
		}
		/*custom helper*/
		if(gst_number()){
			$data['gst_no']=$gst_no;
		}
		if(vat_number()){
			$data['vat_no']=$vat_no;
		}
		if(pan_number()){
			$data['pan_no']=$pan_no;
		}
		/*end*/

		
			$this->db->select("count(*) as store_code_count");
			$this->db->where("upper(store_code)", strtoupper($store_code));
			$this->db->where("id !=", $q_id);
			$store_code_count = $this->db->get('db_store')->row()->store_code_count;
			if($store_code_count>0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}

			$q1 = $this->db->where('id',$q_id)->update('db_store', $data);
			if($q1){
				$this->db->trans_commit();
				$this->session->unset_userdata('currency');
				//$this->session->set_flashdata('success', 'Success!! Record Updated Successfully! ');
				return "success";
			}

		

		return "failed";
	}
PHP;

    $new = <<<'PHP'
		// Core db_store fields only
		$data = array(
			'store_code'				=> $store_code,
			'store_name'				=> $store_name,
			'mobile'					=> $mobile,
			'phone'						=> $phone,
			'email'						=> $email,
			'country'					=> $country,
			'state'						=> $state,
			'city'						=> $city,
			'address'					=> $address,
			'postcode'					=> $postcode,
			'currency_id'				=> $currency,
			'currency_placement'		=> $currency_placement,
			'timezone'					=> $timezone,
			'date_format'				=> $date_format,
			'time_format'				=> $time_format,
		);

		// Modular inventory settings
		$inventory_data = array(
			'category_init'				=> $category_init,
			'item_init'					=> $item_init,
			'supplier_init'				=> $supplier_init,
			'purchase_init'				=> $purchase_init,
			'purchase_return_init'		=> $purchase_return_init,
			'customer_init'				=> $customer_init,
			'sales_init'				=> $sales_init,
			'sales_return_init'			=> $sales_return_init,
			'expense_init'				=> $expense_init,
			'quotation_init'			=> $quotation_init,
			'money_transfer_init'		=> $money_transfer_init,
			'accounts_init'				=> $accounts_init,
			'sales_payment_init'		=> $sales_payment_init,
			'sales_return_payment_init'	=> $sales_return_payment_init,
			'purchase_payment_init'		=> $purchase_payment_init,
			'purchase_return_payment_init'	=> $purchase_return_payment_init,
			'expense_payment_init'	=> $expense_payment_init,
			'cust_advance_init'	=> $cust_advance_init,
		);

		// Modular receipt settings
		$receipt_data = array(
			'sales_invoice_format_id'	=> $sales_invoice_format_id,
			'pos_invoice_format_id'		=> $pos_invoice_format_id,
			'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
			'invoice_terms'				=> $invoice_terms,
			'previous_balance_bit'	=> $previous_balance_bit,
			'round_off'					=> $round_off,
			'change_return'				=> $change_return,
			'decimals'					=> $decimals,
			'qty_decimals'				=> $qty_decimals,
			't_and_c_status'			=> $t_and_c_status,
			't_and_c_status_pos'		=> $t_and_c_status_pos,
			'number_to_words'			=> $number_to_words,
		);

		// Modular POS settings
		$pos_data = array(
			'sales_discount'			=> $sales_discount,
			'mrp_column'				=> $mrp_column,
			'show_signature'			=> $show_signature,
			'previous_balance_bit'	=> $previous_balance_bit,
			'default_account_id'		=> (isset($default_account_id) && !empty($default_account_id))?$default_account_id:null,
		);

		// Modular industry settings
		$industry_data = array(
			'industry_type'			=> $industry_type,
			'business_model'			=> $business_model,
			'workflow_template_key'		=> $workflow_template_key,
			'dashboard_template_key'		=> $dashboard_template_key,
			'storefront_theme_key'		=> $storefront_theme_key,
			'feature_flags_json'		=> $feature_flags_json,
			'label_overrides_json'		=> $label_overrides_json,
			'industry_settings_json'	=> $industry_settings_json,
		);

		// Modular tax settings
		$tax_data = array();
		if(gst_number()){
			$tax_data['gst_no'] = $gst_no;
		}
		if(vat_number()){
			$tax_data['vat_no'] = $vat_no;
		}
		if(pan_number()){
			$tax_data['pan_no'] = $pan_no;
		}

		// Modular theme settings
		$theme_data = array();
		if(!empty($store_logo)){
			$theme_data['store_logo'] = $store_logo;
		}
		if(!empty($signature)){
			$theme_data['signature'] = $signature;
		}

		// Modular storefront settings
		$storefront_data = array(
			'store_website' => $store_website,
		);
		if(!empty($storefront_theme_key)){
			$storefront_data['storefront_theme_key'] = $storefront_theme_key;
		}

		// Modular payment settings
		$payment_data = array(
			'bank_details' => $bank_details,
		);

		// NIN API key/value settings
		$nin_data = array(
			'nin_api_enabled' => $nin_api_enabled,
			'nin_api_url' => $nin_api_url,
			'nin_api_key' => $nin_api_key,
			'nin_api_provider' => $nin_api_provider,
		);

		// Store code uniqueness check
			$this->db->select("count(*) as store_code_count");
			$this->db->where("upper(store_code)", strtoupper($store_code));
			$this->db->where("id !=", $q_id);
			$store_code_count = $this->db->get('db_store')->row()->store_code_count;
			if($store_code_count>0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}

			$q1 = $this->db->where('id',$q_id)->update('db_store', $data);
			if(!$q1){
				$this->db->trans_rollback();
				return "failed";
			}

			// Write to modular tables
			if($this->db->table_exists('db_store_inventory_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_inventory_settings', $inventory_data);
			}
			if($this->db->table_exists('db_store_receipt_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_receipt_settings', $receipt_data);
			}
			if($this->db->table_exists('db_store_pos_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_pos_settings', $pos_data);
			}
			if($this->db->table_exists('db_store_industry_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_industry_settings', $industry_data);
			}
			if($this->db->table_exists('db_store_tax_settings') && !empty($tax_data)){
				$this->_mp_upsert_modular_row($q_id, 'db_store_tax_settings', $tax_data);
			}
			if($this->db->table_exists('db_store_theme_settings') && !empty($theme_data)){
				$this->_mp_upsert_modular_row($q_id, 'db_store_theme_settings', $theme_data);
			}
			if($this->db->table_exists('db_store_storefront_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_storefront_settings', $storefront_data);
			}
			if($this->db->table_exists('db_store_payment_settings')){
				$this->_mp_upsert_modular_row($q_id, 'db_store_payment_settings', $payment_data);
			}
			if($this->db->table_exists('db_store_settings')){
				mp_set_store_setting($q_id, 'general', 'language_id', $language_id, 'int');
				foreach($nin_data as $k => $v){
					mp_set_store_setting($q_id, 'nin_api', $k, $v, 'string');
				}
			}

			$this->db->trans_commit();
			$this->session->unset_userdata('currency');
			return "success";
	}

	/**
	 * Upsert helper for modular settings tables.
	 */
	private function _mp_upsert_modular_row($store_id, $table, $data){
		if(empty($data)){
			return true;
		}
		$exists = $this->db->where('store_id', $store_id)->get($table)->num_rows();
		if($exists){
			return $this->db->where('store_id', $store_id)->update($table, $data);
		}
		$data['store_id'] = $store_id;
		return $this->db->insert($table, $data);
	}
PHP;

    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
        echo "Patched update_store() in Store_profile_model.php\n";
    } else {
        echo "WARNING: Could not find update_store() block in Store_profile_model.php\n";
    }

    file_put_contents($profile_model, $content);
}

echo "Patch complete.\n";
