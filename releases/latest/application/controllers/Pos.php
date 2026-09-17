<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//use chillerlan\QRCode\{QRCode, QROptions};


class Pos extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('pos_model','pos_model');
		$this->load->helper('sms_template_helper');
		$this->load->helper('accounts');
	}

	public function is_sms_enabled(){
		return is_sms_enabled();
	}
	
	public function index()
	{
		$this->permission_check('pos');

		if(is_mobile() && !is_tablet() && $this->input->get('mobile') !== '0'){
			redirect(base_url('mobile/pos'), 'refresh');
			return;
		}

		$data=$this->data;

		// Check if cashier is clocked in (Store Admin is exempt from attendance)
		$data['needs_clock_in'] = false;
		$data['clock_in_time'] = '';
		$data['attendance_exempt'] = is_store_admin();
		$roleName = trim($this->session->userdata('role_name') ?: '');
		if(!$data['attendance_exempt'] && stripos($roleName, 'cashier') !== false){
			$this->load->model('attendance_model');
			$userId = $this->session->userdata('inv_userid');
			$data['needs_clock_in'] = !$this->attendance_model->needsClockOut($userId);
			if(!$data['needs_clock_in']){
				$record = $this->attendance_model->getAttendanceRecord($userId, date('Y-m-d'));
				$data['clock_in_time'] = ($record && !empty($record->clock_in)) ? date('H:i', strtotime($record->clock_in)) : '';
			}
		}

		// Cashier shift (Z-Report) status for the POS top bar
		$data['open_shift'] = null;
		$data['can_manage_shifts'] = false;
		if($this->permissions('cashier_shifts_manage') && mp_feature_enabled('cashier_shifts')){
			$data['can_manage_shifts'] = true;
			if($this->db->table_exists('db_cashier_shifts')){
				$this->load->model('cashier_shifts_model');
				$data['open_shift'] = $this->cashier_shifts_model->get_open_shift();
				$data['tills']      = $this->cashier_shifts_model->get_tills_for_user();
				if(!empty($data['open_shift'])){
					$data['expected'] = $this->cashier_shifts_model->compute_expected($data['open_shift']);
				}
				// Till account to use as default for cash Pay in POS
				$data['till_account_id']   = (!empty($data['open_shift']) && !empty($data['open_shift']->cash_account_id)) ? $data['open_shift']->cash_account_id : get_cash_account_id();
				$data['till_account_name'] = (!empty($data['open_shift']) && !empty($data['open_shift']->account_name)) ? $data['open_shift']->account_name : get_account_name($data['till_account_id']);
			}
		}

		//Sales Code
		$init_code=get_only_init_code('sales');
      	$count_id=get_last_count_id('db_sales');

		$data['page_title']='POS';
		$data['init_code']=$init_code;
		$data['count_id']=$count_id;
		

		$data['warehouse_id'] = '';
		$data['result'] = $this->get_hold_invoice_list();
		$data['tot_count'] = $this->get_hold_invoice_count();
		$data['walkin_customer_id'] = get_walk_in_customer_id();
		$data['is_restaurant'] = mp_feature_enabled('kitchen_workflow');
		$data['is_laundry'] = mp_feature_enabled('laundry_workflow');
		$data['manager_approvals_enabled'] = mp_feature_enabled('manager_approvals');
		// Available tables for restaurant table selection
		$store_id = get_current_store_id();
		$data['tables'] = [];
		if(mp_feature_enabled('table_management')){
			$this->load->model('tables_model','pos_tables');
			$data['tables'] = $this->pos_tables->get_all($store_id);
		}

		// Staff list for commission assignment
		$data['staff_list'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		// Service-to-staff mapping for POS staff assignment dropdown filtering
		$service_staff = $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_service_staff')->result();
		$service_staff_map = [];
		foreach ($service_staff as $ss) {
			$service_staff_map[$ss->service_id][] = $ss->staff_id;
		}
		// Desktop POS data: products and customers
		// Show sellable items only: single items AND variant children.
		// Variant parents (child_bit=0 with children) are containers, not sellable, so they are excluded.
		// No hard LIMIT — the view filters client-side, so capping here would silently hide
		// products created on mobile/elsewhere.
		$store_id = get_current_store_id();
		$this->load->model('expiry_settings_model');
		$expiry_settings = $this->expiry_settings_model->get_settings($store_id);
		$data['stop_selling_expired'] = (int) ($expiry_settings->stop_selling_expired ?? 1);
		$today = date('Y-m-d');
		$products_query = $this->db->query("
			SELECT a.id, a.item_name AS name, COALESCE(NULLIF(a.mrp,0), a.sales_price) AS price,
			       a.sales_price AS wholesale, c.category_name AS category,
			       a.category_id, a.brand_id, a.sku, a.item_code, u.unit_name AS unit,
			       COALESCE(a.alert_qty, 0) AS alert_qty,
			       a.tax_id, a.tax_type,
			       IF(a.tax_id > 0, 1, 0) AS tax,
			       a.item_image AS image,
			       a.item_group,
			       a.parent_id,
			       a.expire_date,
			       a.batch_lot,
			       b.tax AS tax_value
			FROM db_items a
			LEFT JOIN db_tax b ON b.id = a.tax_id
			LEFT JOIN db_category c ON c.id = a.category_id
			LEFT JOIN db_units u ON u.id = a.unit_id
			WHERE a.store_id = ?
			  AND a.status = 1
			  AND a.service_bit != 1
			  AND (a.not_for_sale IS NULL OR a.not_for_sale = 0)
			  AND (
			    -- Variant children (sellable, have own price/stock)
			    a.child_bit = 1
			    OR
			    -- Single items with no children (not a variant parent container)
			    ((a.child_bit = 0 OR a.child_bit IS NULL) AND a.parent_id IS NULL
			     AND NOT EXISTS (SELECT 1 FROM db_items c WHERE c.parent_id = a.id))
			  )
			ORDER BY a.item_name
		", [$store_id]);
		$products = $products_query->result_array();
		$warehouse_id = get_store_warehouse_id();

		// Resolve soonest-expiring batch per product for expiry warnings
		$batch_map = [];
		if ($this->db->table_exists('db_item_barcodes') && !empty($products)) {
			$ids = array_column($products, 'id');
			$placeholders = implode(',', array_fill(0, count($ids), '?'));
			$batches = $this->db->query("SELECT item_id, expire_date, batch_lot FROM db_item_barcodes WHERE status = 1 AND qty > 0 AND expire_date IS NOT NULL AND item_id IN ($placeholders) ORDER BY expire_date ASC, id ASC", $ids)->result();
			foreach ($batches as $b) {
				if (!isset($batch_map[$b->item_id])) {
					$batch_map[$b->item_id] = ['expire_date' => $b->expire_date, 'batch_lot' => $b->batch_lot];
				}
			}
		}

		$this->load->model('item_selling_units_model', 'selling_units');
		foreach ($products as &$p) {
			$p['id'] = (int) $p['id'];
			$p['price'] = (float) $p['price'];
			$p['wholesale'] = (float) $p['wholesale'];
			$p['tax'] = (bool) $p['tax'];
			$p['tax_value'] = (float) $p['tax_value'];
			$p['tax_id'] = (int) $p['tax_id'];
			$p['parent_id'] = isset($p['parent_id']) ? (int) $p['parent_id'] : null;
			// Add stock information
			$p['stock'] = (int) total_available_qty_items_of_warehouse($warehouse_id, null, $p['id']);
			$p['outOfStock'] = $p['stock'] <= 0;
			$p['alert_qty'] = (int) ($p['alert_qty'] ?? 0);
			// Prefer barcode/batch expiry over item-level expiry
			if (isset($batch_map[$p['id']])) {
				$p['expire_date'] = $batch_map[$p['id']]['expire_date'];
				$p['batch_lot'] = $batch_map[$p['id']]['batch_lot'];
			}
			$p['expired'] = !empty($p['expire_date']) && is_valid_date($p['expire_date']) && $p['expire_date'] < $today && $data['stop_selling_expired'] === 1;
			$p['expire_date'] = !empty($p['expire_date']) ? $p['expire_date'] : null;
			$p['batch_lot'] = !empty($p['batch_lot']) ? $p['batch_lot'] : null;
			// Attach selling units for pack/piece/carton picker
			$p['selling_units'] = [];
			if(mp_feature_enabled('multi_unit_selling') && $this->db->table_exists('db_item_selling_units')){
				$sus = $this->selling_units->get_units($p['id'], $store_id);
				foreach($sus as $su){
					$p['selling_units'][] = [
						'unit_id' => $su->unit_id,
						'unit_name' => $su->unit_name,
						'unit_shortcode' => $su->shortcode,
						'conversion_factor' => (float)$su->conversion_factor,
						'selling_price' => (float)$su->selling_price,
						'wholesale_price' => (float)$su->wholesale_price,
						'barcode' => $su->barcode,
						'is_default' => (bool) $su->is_default,
					];
				}
			}
		}
		$data['products'] = $products;
		// Pharmacy: pre-load a medical note (and its patient) when dispensing
		$medical_note_id = (int) $this->input->get('medical_note_id');
		$medical_note = null;
		if ($medical_note_id && $this->db->table_exists('db_medical_notes')) {
			$medical_note = $this->db->where('id', $medical_note_id)->where('store_id', $store_id)->get('db_medical_notes')->row();
		}
		$data['medical_note_id'] = $medical_note ? $medical_note_id : 0;
		$data['medical_note_customer_id'] = $medical_note ? (int) $medical_note->customer_id : null;
		$data['pos_retail_button'] = mp_feature_enabled('pos_retail_button');
		$data['pos_wholesale_button'] = mp_feature_enabled('pos_wholesale_button');
		$data['default_price_type'] = (empty($data['pos_retail_button']) && !empty($data['pos_wholesale_button'])) ? 'wholesale' : 'retail';

		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('customer_name', 'asc');
		$customer_fields = ['id', 'customer_name'];
		foreach (['mobile', 'loyalty_points', 'store_credit_balance', 'gift_card_balance', 'sales_due', 'credit_limit'] as $f) {
			if ($this->db->field_exists($f, 'db_customers')) $customer_fields[] = $f;
		}
		$this->db->select(implode(',', $customer_fields));
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('customer_name', 'asc');
		$data['customers'] = $this->db->get('db_customers')->result_array();

		// Customer redeemables (loyalty, coupons, gift cards, store credit)
		$data['customer_redeemables'] = $this->get_customer_redeemables($data['customers']);
		$this->load->model('loyalty_model', 'loyalty');
		$data['loyalty_settings'] = $this->loyalty->get_settings();

		// Recent holds for today (no stale history)
		$hold_store_id = get_current_store_id();
		$this->db->select('h.id, h.reference_id, h.sales_date, h.grand_total, h.customer_id, c.customer_name');
		$this->db->from('db_hold h');
		$this->db->join('db_customers c', 'c.id = h.customer_id', 'left');
		$this->db->where('h.store_id', $hold_store_id);
		$this->db->where('h.sales_date', date('Y-m-d'));
		$this->db->order_by('h.id', 'desc');
		$this->db->limit(20);
		$data['holds'] = $this->db->get()->result_array();

		// Sales target and today's sales progress
		$site = get_site_details();
		$data['daily_target'] = (float) ($site->sales_target ?? 50000);
		$favicon_path = !empty($site->favicon) ? $site->favicon : '';
		$data['favicon_url'] = (!empty($favicon_path) && file_exists($favicon_path)) ? base_url($favicon_path) : base_url('uploads/site/icon.webp');

		$this->db->select('COALESCE(SUM(grand_total),0) as today_sales');
		$this->db->from('db_sales');
		$this->db->where('store_id', $store_id);
		$this->db->where('sales_date', date('Y-m-d'));
		$this->db->where('sales_status', 'Final');
		$today_sales_row = $this->db->get()->row();
		$data['today_sales'] = (float) ($today_sales_row ? $today_sales_row->today_sales : 0);

		// Top selling products for the last 7 days
		$this->db->select('i.item_name as name, COALESCE(SUM(si.sales_qty),0) as sold', false);
		$this->db->from('db_salesitems si');
		$this->db->join('db_items i', 'i.id = si.item_id', 'left');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->where('s.store_id', $store_id);
		$this->db->where('s.sales_date >=', date('Y-m-d', strtotime('-7 days')));
		$this->db->where('s.sales_status', 'Final');
		$this->db->group_by('si.item_id');
		$this->db->order_by('sold', 'desc');
		$this->db->limit(3);
		$data['top_selling'] = $this->db->get()->result_array();

		$data['warehouse_id'] = get_store_warehouse_id();

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['user_id'] = $this->session->userdata('inv_userid') ?? 0;
		$data['role_name'] = $this->session->userdata('role_name') ?? '';
		
		// Fetch user profile picture
		$user_profile = $this->db->select('profile_picture')->where('id', $data['user_id'])->get('db_users')->row();
		$data['user_profile_picture'] = ($user_profile && !empty($user_profile->profile_picture)) ? base_url($user_profile->profile_picture) : '';
		
		$name_parts = explode(' ', trim($data['display_name']));
		$data['user_initial'] = '';
		foreach ($name_parts as $i => $part) {
			if ($i < 2) $data['user_initial'] .= strtoupper(substr($part, 0, 1));
		}
		$data['user_initial'] = substr($data['user_initial'], 0, 2);

		$data['service_staff_map'] = $service_staff_map;
		$data['shipping_fees'] = $this->_get_shipping_fees();
		$data['sale_shipping'] = null;
		$this->load->view('pos_desktop',$data);
	}

	//adding new item from Modal
	public function newcustomer(){
	
		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
			$this->load->model('customers_model');
			$result=$this->customers_model->verify_and_save();
			if($result === 'success'){
				$customer_id = $this->db->insert_id();
				$nin_bvn = $this->input->post('nin_bvn', TRUE);
				$nin_verified = $this->input->post('nin_verified', TRUE);
				$this->db->where('id', $customer_id)->update('db_customers', array(
					'nin_bvn' => $nin_bvn,
					'nin_verified' => (!empty($nin_verified)) ? 1 : 0
				));
			}
			//fetch latest item details
			$res=array();
			$query=$this->db->query("select id,customer_name from db_customers order by id desc limit 1");
			$res['id']=$query->row()->id;
			$res['customer_name']=$query->row()->customer_name;
			$res['result']=$result;
			
			echo json_encode($res);

		} 
		else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function get_details(){
		echo $this->pos_model->get_details();
	}
	public function receive_order(){
	    echo $this->pos_model->receive_order();
	}
	public function pos_save_update(){
	    $response = $this->pos_model->pos_save_update();

	    $explode = explode("<<<###>>>",$response);
	    if($explode['0']=='success'){
	    	$init_code=get_only_init_code('sales');
      		$count_id=get_last_count_id('db_sales');
      		$customer_remaining_advance=get_customer_details($_REQUEST['customer_id'])->tot_advance;
	    	$sales_id = $explode['1'];
	    	$sales_code = $this->db->select('sales_code')->where('id',$sales_id)->get('db_sales')->row()->sales_code;
	    	$pdf_token = get_pdf_token('sales', $sales_id, $sales_code);
	    	$response .="<<<###>>>".$init_code."<<<###>>>".$count_id."<<<###>>>".$customer_remaining_advance."<<<###>>>".$pdf_token."<<<###>>>".$sales_code;

	    	// Link the sale to the originating medical note (Pharmacy workflow)
	    	$medical_note_id = (int) $this->input->post('medical_note_id', TRUE);
	    	if ($medical_note_id && $this->db->table_exists('db_medical_notes')) {
	    		$this->db->where('id', $medical_note_id)->where('store_id', get_current_store_id())->update('db_medical_notes', ['sales_id' => $sales_id]);
	    	}
	    }
	    echo $response;
	}
	public function edit($sales_id){
		$this->belong_to('db_sales',$sales_id);
		$this->permission_check('sales_edit');
	    $data=$this->data;
	    $data['sales_id']=$sales_id;
	    $data['page_title']='POS Update';

	    //Get sales details
	    $sales_details = get_sales_details($sales_id);
	    $customer_id = $sales_details->customer_id;
	    $init_code = $sales_details->init_code;
	    $count_id = $sales_details->count_id;

	    $data['warehouse_id'] = '';
	    
	    $data['customer_id']=$customer_id;
	    $data['init_code']=$init_code;
	    $data['count_id']=$count_id;
	    $data['result'] = $this->get_hold_invoice_list();
		$data['tot_count'] = $this->get_hold_invoice_count();
		$data['walkin_customer_id'] = get_walk_in_customer_id();
		$data['is_restaurant'] = mp_feature_enabled('kitchen_workflow');
		$data['is_laundry'] = mp_feature_enabled('laundry_workflow');
		$data['manager_approvals_enabled'] = mp_feature_enabled('manager_approvals');
		// Available tables for restaurant table selection
		$store_id = get_current_store_id();
		$data['tables'] = [];
		if(mp_feature_enabled('table_management')){
			$this->load->model('tables_model','pos_tables');
			$data['tables'] = $this->pos_tables->get_all($store_id);
		}

		// Staff list for commission assignment
		$data['staff_list'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		// Cashier shift (Z-Report) status
		$data['open_shift'] = null;
		$data['can_manage_shifts'] = false;
		if($this->permissions('cashier_shifts_manage') && mp_feature_enabled('cashier_shifts')){
			$data['can_manage_shifts'] = true;
			if($this->db->table_exists('db_cashier_shifts')){
				$this->load->model('cashier_shifts_model');
				$data['open_shift'] = $this->cashier_shifts_model->get_open_shift();
				$data['tills']      = $this->cashier_shifts_model->get_tills_for_user();
				if(!empty($data['open_shift'])){
					$data['expected'] = $this->cashier_shifts_model->compute_expected($data['open_shift']);
				}
				// Till account to use as default for cash Pay in POS
				$data['till_account_id']   = (!empty($data['open_shift']) && !empty($data['open_shift']->cash_account_id)) ? $data['open_shift']->cash_account_id : get_cash_account_id();
				$data['till_account_name'] = (!empty($data['open_shift']) && !empty($data['open_shift']->account_name)) ? $data['open_shift']->account_name : get_account_name($data['till_account_id']);
			}
		}
		$data['shipping_fees'] = $this->_get_shipping_fees();
		$data['sale_shipping'] = ($this->db->field_exists('shipping_fee', 'db_sales') && !empty($sales_details->shipping_fee_id))
			? ['id' => (int)$sales_details->shipping_fee_id, 'fee' => (float)$sales_details->shipping_fee, 'label' => (string)($sales_details->shipping_label ?? '')]
			: null;
		$this->load->view('pos',$data);
	}

	/**
	 * Enabled manual-shipping fees for the current store (feature-flag gated).
	 */
	private function _get_shipping_fees(){
		if(!mp_feature_enabled('manual_shipping') || !$this->db->table_exists('db_shipping_fees')) return [];
		return $this->db->where('store_id', get_current_store_id())->where('is_enabled', 1)
			->order_by('sort_order', 'asc')->order_by('id', 'asc')
			->get('db_shipping_fees')->result();
	}

	public function fetch_sales($sales_id){
	    $result=$this->pos_model->edit_pos($sales_id);
	}
	/* ######################################## HOLD INVOICE ############################# */
	public function hold_invoice(){
	    echo $this->pos_model->hold_list_save_update();
	}
	public function hold_invoice_list(){
		$data =array();
		$data['result'] = $this->get_hold_invoice_list();
		$data['tot_count'] = $this->get_hold_invoice_count();
		echo json_encode($data);
	}

	public function get_hold_invoice_list(){
		$data =array();
		$result= $this->pos_model->hold_invoice_list();
		return $result;
	}
	public function get_hold_invoice_count(){
		$q1=$this->db->query("SELECT * FROM db_hold WHERE store_id=".get_current_store_id());
		return $q1->num_rows();
	}
	public function hold_invoice_delete($invoice_id){
		header('Content-Type: text/plain; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		ob_start();
		$this->permission_check('sales_add');
		$result=$this->pos_model->hold_invoice_delete($invoice_id);
		ob_end_clean();
		echo trim($result);
		exit;
	}

	public function get_holds_ajax(){
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		ob_start();
		$this->permission_check('sales_add');
		$store_id = get_current_store_id();
		$this->db->select('h.id, h.reference_id, h.sales_date, h.grand_total, h.customer_id, c.customer_name');
		$this->db->from('db_hold h');
		$this->db->join('db_customers c', 'c.id = h.customer_id', 'left');
		$this->db->where('h.store_id', $store_id);
		$this->db->where('h.sales_date', date('Y-m-d'));
		$this->db->order_by('h.id', 'desc');
		$this->db->limit(20);
		$holds = $this->db->get()->result_array();
		
		$formatted = [];
		foreach ($holds as $h) {
			$formatted[] = [
				'id' => (int) $h['id'],
				'reference_id' => $h['reference_id'] ?? '',
				'customer_name' => $h['customer_name'] ?? 'Walk-in',
				'grand_total_formatted' => store_number_format($h['grand_total'] ?? 0, true),
				'sales_date' => show_date($h['sales_date'] ?? ''),
			];
		}
		ob_end_clean();
		echo json_encode(['holds' => $formatted], JSON_UNESCAPED_UNICODE);
		exit;
	}
	public function get_todays_sales_ajax(){
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		ob_start();
		$this->permission_check('sales_add');
		$store_id = get_current_store_id();
		$this->db->select('s.id, s.sales_code, s.grand_total, s.created_time, c.customer_name');
		$this->db->from('db_sales s');
		$this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
		$this->db->where('s.store_id', $store_id);
		$this->db->where('s.sales_date', date('Y-m-d'));
		$this->db->where('s.sales_status', 'Final');
		$this->db->order_by('s.id', 'desc');
		$this->db->limit(50);
		$sales = $this->db->get()->result_array();
		
		$formatted = [];
		foreach ($sales as $s) {
			$formatted[] = [
				'id' => (int) $s['id'],
				'sales_code' => $s['sales_code'] ?? '',
				'customer_name' => $s['customer_name'] ?? 'Walk-in',
				'grand_total_formatted' => store_number_format($s['grand_total'] ?? 0, true),
				'time' => $s['created_time'] ? date('H:i', strtotime($s['created_time'])) : '',
			];
		}
		ob_end_clean();
		echo json_encode(['sales' => $formatted], JSON_UNESCAPED_UNICODE);
		exit;
	}
	public function hold_invoice_edit(){
		echo $this->pos_model->hold_invoice_edit();
	}
	public function add_payment_row(){
		return $this->load->view('modals_pos_payment/modal_payments_multi_sub');
	}

	public function print_qr($data='')
	{
		$this->load->model('Qrcode_model','qr');

		return $this->qr->qr_image($data);

		exit;

		$data  = trim($data);	

		//if the parameter value has slash
		$data = base64_decode(str_replace('-', '=', str_replace('_', '/', $data)));

		// quick and simple:
		//return '<img src="'.(new QRCode)->render($data).'" alt="QR Code" />';
		$options = new QROptions([
			
		]);

		return (!empty($data)) ? '<img src="'.(new QRCode($options))->render($data).'" alt="QR Code" />' : '';		
	}

	//Print sales POS invoice 
	public function print_invoice_pos($sales_id){
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_invoice');
		$data=array_merge($data,array('sales_id'=>$sales_id));
		
		$this->load->view('sal-invoice-pos',$data);
		
	}
	public function get_item_details(){
		echo $this->pos_model->get_item_details($this->input->post('item_id'));
	}

	/**
	 * AJAX: Return attribute types and values for a product (for POS variant picker)
	 */
	public function get_item_attribute_types(){
		$item_id = (int)$this->input->post('item_id', TRUE);
		$this->load->model('items_model');
		$item = $this->db->where('id', $item_id)->get('db_items')->row();
		$attribute_types = !empty($item) && !empty($item->attribute_types_json) ? json_decode($item->attribute_types_json, true) : array();
		$this->load->model('attributes_model','attributes');
		$attribute_map = array();
		if(!empty($attribute_types)){
			foreach($attribute_types as $type){
				$this->db->where('store_id', get_current_store_id());
				$this->db->where('attribute_type', $type);
				$this->db->where('status', 1);
				$this->db->order_by('sort_order','asc');
				$this->db->order_by('attribute_value','asc');
				$q = $this->db->get('db_attributes');
				$attribute_map[$type] = array();
				foreach($q->result() as $r){
					$attribute_map[$type][] = $r->attribute_value;
				}
			}
		}
		echo json_encode(array('status'=>'success','has_attributes'=>!empty($attribute_types),'attribute_types'=>$attribute_types,'attribute_map'=>$attribute_map));
	}

	/**
	 * AJAX: Find the child item_id for selected attribute values.
	 */
	public function find_child_item_by_attributes(){
		$parent_id = (int)$this->input->post('parent_id', TRUE);
		$attributes = $this->input->post('attributes', TRUE); // JSON or array
		$attributes = is_string($attributes) ? json_decode($attributes, true) : $attributes;
		if(empty($attributes)) { echo json_encode(array('status'=>'error','message'=>'No attributes selected')); return; }
		$this->load->model('items_model');
		$child_id = $this->items_model->find_child_item_id_by_attributes($parent_id, $attributes);
		if($child_id){
			echo json_encode(array('status'=>'success','item_id'=>$child_id));
		} else {
			echo json_encode(array('status'=>'error','message'=>'No child variant found for the selected attributes.'));
		}
	}

	private function get_customer_redeemables($customers){
		$redeemables = array();
		if(empty($customers)) return $redeemables;
		$store_id = get_current_store_id();
		$customer_ids = array_column($customers, 'id');
		$ids = implode(',', array_map('intval', $customer_ids));
		if(empty($ids)) return $redeemables;

		$walkin_id = get_walk_in_customer_id() ?: -1;

		// Seed empty arrays for each customer
		foreach($customers as $c){
			$is_walkin = ((int)$c['id'] === (int)$walkin_id);
			$redeemables[$c['id']] = array(
				'loyalty_points' => $is_walkin ? 0 : (float)($c['loyalty_points'] ?? 0),
				'store_credit_balance' => $is_walkin ? 0 : (float)($c['store_credit_balance'] ?? 0),
				'gift_card_balance' => $is_walkin ? 0 : (float)($c['gift_card_balance'] ?? 0),
				'coupons' => array(),
				'gift_cards' => array(),
				'store_credit' => array(),
			);
		}

		// Customer-specific coupons
		if($this->db->table_exists('db_customer_coupons')){
			$this->db->select('a.id, a.customer_id, a.code, a.value, a.type, a.description, b.name as coupon_name');
			$this->db->from('db_customer_coupons a');
			$this->db->join('db_coupons b', 'b.id = a.coupon_id', 'left');
			$this->db->where('a.store_id', $store_id);
			$this->db->where('a.status', 1);
			$this->db->where("a.customer_id IN ($ids)");
			$q = $this->db->get()->result_array();
			foreach($q as $r){
				if((int)$r['customer_id'] === (int)$walkin_id) continue;
				$redeemables[$r['customer_id']]['coupons'][] = $r;
			}
		}

		// Gift cards
		if($this->db->table_exists('db_gift_cards')){
			$this->db->select('id, customer_id, card_number as code, current_balance as balance');
			$this->db->where('store_id', $store_id);
			$this->db->where("customer_id IN ($ids)");
			$this->db->where('current_balance >', 0);
			$q = $this->db->get('db_gift_cards')->result_array();
			foreach($q as $r){
				if((int)$r['customer_id'] === (int)$walkin_id) continue;
				$redeemables[$r['customer_id']]['gift_cards'][] = $r;
			}
		}

		// Store credit
		if($this->db->table_exists('db_store_credit')){
			$this->db->select('id, customer_id, balance');
			$this->db->where('store_id', $store_id);
			$this->db->where("customer_id IN ($ids)");
			$this->db->where('balance >', 0);
			$q = $this->db->get('db_store_credit')->result_array();
			foreach($q as $r){
				if((int)$r['customer_id'] === (int)$walkin_id) continue;
				$redeemables[$r['customer_id']]['store_credit'][] = $r;
			}
		}

		return $redeemables;
	}

}
