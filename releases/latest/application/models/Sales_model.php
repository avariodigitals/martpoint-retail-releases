<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_model extends CI_Model {

	//Datatable start
	var $table = 'db_sales as a';
	var $column_order = array( 
							'a.id',
							'a.sales_date',
							'a.sales_code',
							'a.reference_no',
							'b.customer_name',
							'a.grand_total',
							'a.paid_amount',
							'a.payment_status',
							'a.created_by',
							'a.return_bit',
							'a.pos',
							'a.store_id',
							'a.quotation_id',
							'a.due_date',
							'c.code',
							); //set column field database for datatable orderable
	var $column_search = array( 
							'a.id',
							'a.sales_date',
							'a.sales_code',
							'a.reference_no',
							'b.customer_name',
							'a.grand_total',
							'a.paid_amount',
							'a.payment_status',
							'a.created_by',
							'a.return_bit',
							'a.pos',
							'a.store_id',
							'a.due_date',
							'c.code',
							);//set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order  

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
	}

	private function _get_datatables_query()
	{
		$privileged_warehouses = get_privileged_warehouses_ids();

		$this->db->select($this->column_order);
		$this->db->select("(SELECT sp.payment_type FROM db_salespayments sp WHERE sp.sales_id = a.id AND sp.status = 1 LIMIT 1) as payment_type", FALSE);
		$this->db->from($this->table);
		$this->db->join('db_customers as b','b.id=a.customer_id','left');
		$this->db->join('db_customer_coupons as c','c.id=a.coupon_id','left');
		
		/*If warehouse selected*/
		$warehouse_id = $this->input->post('warehouse_id');
		$customer_id = $this->input->post('customer_id');

		if(!empty($warehouse_id)){
			//$this->db->join('db_warehouse as w','w.id='.$warehouse_id,'left');
			$this->db->where('a.warehouse_id',$warehouse_id);
		}
		else if(!is_admin() && !is_store_admin()){
     		//Find the previllaged wareshouses to the user
     		 //$this->db->join('db_warehouse as w','w.id in ('.$privileged_warehouses.')','left');
     		 $this->db->where("a.warehouse_id in ($privileged_warehouses)");
     	}

		if(!empty($customer_id)){
			$this->db->where('a.customer_id',$customer_id);
		}
		//if(!is_admin()){
	      $this->db->where("a.store_id",get_current_store_id());
	    //}
	      if(!is_admin()){
	      	if($this->session->userdata('role_id')!='2'){
	      		if(!permissions('show_all_users_sales_invoices')){
	      			$this->db->where("upper(a.created_by)",strtoupper($this->session->userdata('inv_username')));
	      		}
	      	}
	      }
	     $sales_from_date = $this->input->post('sales_from_date');
	     $sales_from_date = system_fromatted_date($sales_from_date);
	     $sales_to_date = $this->input->post('sales_to_date');
	     $sales_to_date = system_fromatted_date($sales_to_date);
	     $users = $this->input->post('users');
	     if($users && !empty($users)){
	     	$this->db->where("upper(a.created_by)",strtoupper($users));
	     }
	     if($sales_from_date!='1970-01-01'){
	     	$this->db->where("a.sales_date>=",$sales_from_date);
	     }
	     if($sales_to_date!='1970-01-01'){
	     	$this->db->where("a.sales_date<=",$sales_to_date);
	     }
	   // echo $this->db->get_compiled_select();exit();
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])) // if datatable send POST for search
			{
				
				

				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

					$this->db->like($item, $_POST['search']['value']);

				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				


				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if(isset($_POST['length']) && $_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->where("store_id",get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}
	//Datatable end

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}

	//Save Sales
	public function verify_save_and_update(){
		$CUR_DATE = date('Y-m-d');
		$CUR_TIME = date('h:i:s a');
		$CUR_USERNAME = $this->session->userdata('inv_username') ?? 'System';
		$SYSTEM_IP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
		$SYSTEM_NAME = gethostname() ?: 'localhost';
		$command = $this->input->post_get('command', TRUE);
		$sales_date = $this->input->post('sales_date', TRUE);
		$due_date = $this->input->post('due_date', TRUE);
		$reference_no = $this->input->post('reference_no', TRUE);
		$sales_status = $this->input->post('sales_status', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$other_charges_input = parse_amount($this->input->post('other_charges_input', TRUE));
		$other_charges_tax_id = $this->input->post('other_charges_tax_id', TRUE);
		$other_charges_amt = parse_amount($this->input->post_get('other_charges_amt', TRUE));
		$discount_to_all_input = parse_amount($this->input->post('discount_to_all_input', TRUE));
		$discount_to_all_type = $this->input->post('discount_to_all_type', TRUE);
		$tot_discount_to_all_amt = parse_amount($this->input->post_get('tot_discount_to_all_amt', TRUE));
		$tot_subtotal_amt = parse_amount($this->input->post_get('tot_subtotal_amt', TRUE));
		$tot_round_off_amt = parse_amount($this->input->post_get('tot_round_off_amt', TRUE));
		$tot_total_amt = parse_amount($this->input->post_get('tot_total_amt', TRUE));
		$sales_note = $this->input->post('sales_note', TRUE);
		$rowcount = $this->input->post_get('rowcount', TRUE);
		$sales_id = $this->input->post('sales_id', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$count_id = $this->input->post('count_id', TRUE);
		$init_code = $this->input->post('init_code', TRUE);
		$coupon_code = $this->input->post('coupon_code', TRUE);
		$coupon_discount_amt = parse_amount($this->input->post_get('coupon_discount_amt', TRUE));
		$invoice_terms = $this->input->post('invoice_terms', TRUE);
		$quotation_id = $this->input->post('quotation_id', TRUE);
		$amount = parse_amount($this->input->post('amount', TRUE));
		$payment_type = $this->input->post('payment_type', TRUE);
		// Only apply default payment mode if not explicitly provided (for split payments, it will be empty)
		if(empty($payment_type)){
			$payment_type = null; // Keep it null for split payments, don't apply default
		}
		$payment_note = $this->input->post('payment_note', TRUE);
		$account_id = $this->input->post('account_id', TRUE);
		$cheque_number = $this->input->post('cheque_number', TRUE);
		$cheque_period = $this->input->post('cheque_period', TRUE);
		$allow_tot_advance = $this->input->post('allow_tot_advance', TRUE);
		$send_sms = $this->input->post('send_sms', TRUE);
		//echo "<pre>";print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		
		// Check if pharmacy business type for customer notes tracking
		$CI =& get_instance();
		$store_profile = mp_get_store_profile();
		$is_pharmacy = isset($store_profile['industry_type']) && $store_profile['industry_type'] == 'pharmacy';
		
		//varify max sales usage of the package subscription
		validate_package_offers('max_invoices','db_sales');
		//END

		$this->db->trans_begin();

		// Serialize invoice-number generation per store so concurrent POS users
		// cannot be assigned the same sales_code.
		if($command == 'save' && !empty($store_id)){
			$this->db->query("SELECT 1 FROM db_store WHERE id = ? FOR UPDATE", [(int)$store_id]);
			$count_id = autosynch_sales_code();
		}

		$sales_date=system_fromatted_date($sales_date);

		$due_date=(!empty($due_date)) ? system_fromatted_date($due_date) : NULL;
		if($due_date=='1970-01-01'){
			$due_date = NULL;
		}
		//echo $due_date;exit;
		if($other_charges_input=='' || $other_charges_input==0){$other_charges_input=null;}
	    if($other_charges_tax_id=='' || $other_charges_tax_id==0){$other_charges_tax_id=null;}
	    if($other_charges_amt=='' || $other_charges_amt==0){$other_charges_amt=null;}
	    if($discount_to_all_input=='' || $discount_to_all_input==0){$discount_to_all_input=null;}
	    if($tot_discount_to_all_amt=='' || $tot_discount_to_all_amt==0){$tot_discount_to_all_amt=null;}
	    if($tot_round_off_amt=='' || $tot_round_off_amt==0){$tot_round_off_amt=null;}

	    $prev_item_ids = array();
	    
	    if(empty(trim($count_id))) {
	    	$this->db->trans_rollback();
	    	return "Invoice Number Should be not be Empty!";
	    }
	    else{
	    	if(!is_numeric($count_id)){
	    		$this->db->trans_rollback();
	    		return "Invoice Number Should be Numerical!";
	    	}
	    }


	    //Get coupon details
	    $customer_coupon_id = null;
	    if(!empty($coupon_code)){
	    	$coupon_details = get_customer_coupon_details_by_coupon_code($coupon_code);
	    	
		    if($coupon_details->num_rows()>0){
		    	if($coupon_details->row()->customer_id==$customer_id){
		    		$customer_coupon_id = $coupon_details->row()->id;		
		    	}
		    } else {
		    	// Fallback: check if this is a promotion code
		    	// Reject walk-in customers for promotion codes
		    	$walkin_id = get_walk_in_customer_id();
		    	$is_walkin = (!empty($walkin_id) && (int)$customer_id === (int)$walkin_id);
		    	if($is_walkin){
		    		$coupon_code = '';
		    	} else {
		    	try {
		    		if($this->db->table_exists('db_promotions')){
		    			$promo = $this->db->where('store_id', $store_id)
		    				->where('status', 1)
		    				->where('promotion_code', strtoupper($coupon_code))
		    				->where('start_date <=', date('Y-m-d'))
		    				->where('end_date >=', date('Y-m-d'))
		    				->get('db_promotions')->row();
		    			if($promo){
		    				// Recalculate coupon_discount_amt from the promotion if not provided
		    				if(empty($coupon_discount_amt) || $coupon_discount_amt == 0){
		    					$tot_subtotal_amt = (float)$this->input->post_get('tot_subtotal_amt', TRUE);
		    					if($tot_subtotal_amt > 0){
		    						if($promo->discount_type == 'Percentage'){
		    							$coupon_discount_amt = $tot_subtotal_amt * ($promo->discount_value / 100);
		    						} else {
		    							$coupon_discount_amt = (float)$promo->discount_value;
		    						}
		    					}
		    				}
		    			}
		    		}
		    	} catch (Exception $e) { /* Promotions table not available */ }
		    	} // end else (not walk-in)
		    }
	    }

	    //Verify Sales Code
		$this->db->where("sales_code",$init_code.$count_id);
		if($command=='update'){
			$this->db->where("id<>",$sales_id);
		}
		$this->db->from('db_sales');
		//echo $this->db->get_compiled_select();exit;
		$count = $this->db->count_all_results();
		

		if($count>0){

			$autosynch_sales_code = true;

			if($autosynch_sales_code){
				$count_id = autosynch_sales_code();
			}
			else{
				$this->db->trans_rollback();
				return "Sales Code already exist";
			}
		}
		
	    
	    $sales_entry_init = array(
							'init_code' 				=> $init_code,
		    				'count_id' 					=> $count_id,
		    				'sales_code' 				=> $init_code.$count_id,//get_init_code('sales'),
		    				/*Coupon disocunt amt*/
		    				'coupon_id' 				=> $customer_coupon_id,
		    				'coupon_amt' 				=> $coupon_discount_amt,
		    				'invoice_terms' 				=> trim($invoice_terms),
	    					);

	    if($command=='save'){//Create sales code unique if first time entry

			
		    $sales_entry = array(
		    				
		    				//'count_id' 					=> get_count_id('db_sales'),  
		    				'reference_no' 				=> $reference_no, 
		    				'sales_date' 				=> $sales_date,
		    				'due_date' 					=> $due_date,
		    				'sales_status' 				=> $sales_status,
		    				'customer_id' 				=> $customer_id,
		    				/*'warehouse_id' 				=> $warehouse_id,*/
		    				/*Other Charges*/
		    				'other_charges_input' 		=> $other_charges_input,
		    				'other_charges_tax_id' 		=> $other_charges_tax_id,
		    				'other_charges_amt' 		=> $other_charges_amt,
		    				/*Discount*/
		    				'discount_to_all_input' 	=> $discount_to_all_input,
		    				'discount_to_all_type' 		=> $discount_to_all_type,
		    				'tot_discount_to_all_amt' 	=> $tot_discount_to_all_amt,
		    				
		    				/*Subtotal & Total */
		    				'subtotal' 					=> $tot_subtotal_amt,
		    				'round_off' 				=> $tot_round_off_amt,
		    				'grand_total' 				=> $tot_total_amt,
		    				'sales_note' 				=> $sales_note,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);
		    if(isset($quotation_id)){
				$sales_entry['quotation_id'] = $quotation_id;
			}
		    $sales_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
		    $sales_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();

		   // print_r($sales_entry);exit;
			$q1 = $this->db->insert('db_sales', array_merge($sales_entry,$sales_entry_init));
			if(!$q1){
				$err = $this->db->error();
				$this->db->trans_rollback();
				return 'Failed to save sale (db_sales insert): ' . ($err['message'] ?? 'unknown error');
			}
			$sales_id = $this->db->insert_id();
			if(!$sales_id){
				$err = $this->db->error();
				$this->db->trans_rollback();
				return 'Failed to save sale (no insert_id): ' . ($err['message'] ?? 'unknown error');
			}
			//SET QUOTATION STATUS
			if(isset($quotation_id)){
				$q11 = $this->db->set("sales_status",'Converted')->where("id",$quotation_id)->update("db_quotation");
			    	if(!$q11){
			    		return false;
			    	}
			}

		}
		else if($command=='update'){	
			$sales_entry = array(
		    				//'sales_code' 				=> $sales_code, 
		    				'reference_no' 				=> $reference_no, 
		    				'sales_date' 			=> $sales_date,
		    				'due_date' 				=> $due_date,
		    				'sales_status' 			=> $sales_status,
		    				'customer_id' 				=> $customer_id,
		    				/*'warehouse_id' 				=> $warehouse_id,*/
		    				/*Other Charges*/
		    				'other_charges_input' 		=> $other_charges_input,
		    				'other_charges_tax_id' 		=> $other_charges_tax_id,
		    				'other_charges_amt' 		=> $other_charges_amt,
		    				/*Discount*/
		    				'discount_to_all_input' 	=> $discount_to_all_input,
		    				'discount_to_all_type' 		=> $discount_to_all_type,
		    				'tot_discount_to_all_amt' 	=> $tot_discount_to_all_amt,
		    				/*Subtotal & Total */
		    				'subtotal' 					=> $tot_subtotal_amt,
		    				'round_off' 				=> $tot_round_off_amt,
		    				'grand_total' 				=> $tot_total_amt,
		    				'sales_note' 			=> $sales_note,
		    			);
			//print_r($sales_entry);exit;
			$sales_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
			$sales_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();
			$q1 = $this->db->where('id',$sales_id)->update('db_sales', array_merge($sales_entry,$sales_entry_init));

			##############################################START
			//FIND THE PREVIOUSE ITEM LIST ID'S
			$prev_item_ids = $this->db->select("item_id")->from("db_salesitems")->where("sales_id",$sales_id)->get()->result_array();
			##############################################END

			$q11=$this->db->query("delete from db_salesitems where sales_id='$sales_id'");
			if(!$q11){
				return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
			}
		}
		//end

		
		//Import post data from form
		for($i=1;$i<=$rowcount;$i++){
		
			if(isset($_REQUEST['tr_item_id_'.$i]) && !empty($_REQUEST['tr_item_id_'.$i])){

				$item_id 			=$this->xss_html_filter(trim($_REQUEST['tr_item_id_'.$i]));

				// Check expiry
				try {
					$this->load->model('expiry_settings_model');
					$expiry_settings = $this->expiry_settings_model->get_settings();
					$item_check = $this->db->select('item_name,expire_date')->where('id',$item_id)->get('db_items')->row();
					if(is_valid_date($item_check->expire_date) && $expiry_settings->stop_selling_expired == 1 && $item_check->expire_date < date('Y-m-d')){
						$this->db->trans_rollback();
						return "This item has expired (".$item_check->expire_date."). Cannot sell: ".$item_check->item_name;
					}
				} catch (Exception $e) { /* Expiry settings not ready yet */ }
				$sales_qty			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_3']));
				$price_per_unit 	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_4']));
				$tax_id 			=$this->xss_html_filter(trim($_REQUEST['tr_tax_id_'.$i]));
				$tax_amt 			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_11']));
				$unit_total_cost	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_10']));
				//$discount_input	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_8']));
				$total_cost			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_9']));
				$tax_type			=$this->xss_html_filter(trim($_REQUEST['tr_tax_type_'.$i]));
				$unit_tax			=$this->xss_html_filter(trim($_REQUEST['tr_tax_value_'.$i]));
				$description		=$this->xss_html_filter(trim($_REQUEST['description_'.$i]));
				$batch_lot			=$this->xss_html_filter(trim($_REQUEST['batch_lot_'.$i] ?? ''));
				$price_type			=$this->xss_html_filter(trim($_REQUEST['price_type_'.$i] ?? 'wholesale'));

                //$discount_input  =(empty($discount_input)) ? 0 : $discount_input;
				//$discount_amt 		=($sales_qty * $unit_total_cost)*$discount_input/100;


				$discount_type 		=$this->xss_html_filter(trim($_REQUEST['item_discount_type_'.$i]));
				$discount_input 	=$this->xss_html_filter(trim($_REQUEST['item_discount_input_'.$i]));
				$discount_amt	    =$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_8']));//Amount

				$discount_amt_per_unit = $discount_amt/$sales_qty;
				if($tax_type=='Exclusive'){
					$single_unit_total_cost = $price_per_unit + ($unit_tax * $price_per_unit / 100);
				}
				else{//Inclusive
					$single_unit_total_cost =$price_per_unit;
				}
				$single_unit_total_cost -=$discount_amt_per_unit;


				if($tax_id=='' || $tax_id==0){$tax_id=null;}
				if($tax_amt=='' || $tax_amt==0){$tax_amt=null;}
				if($discount_input=='' || $discount_input==0){$discount_input=null;}
				//if($unit_total_cost=='' || $unit_total_cost==0){$unit_total_cost=null;}
				if($total_cost=='' || $total_cost==0){$total_cost=null;}
				
				

				//For Update operation only
				if($command=='update' && !update_warehouse_items($item_id)){
					return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
				}
				//end
				
				$item_details = get_item_details($item_id);
				$item_name = $item_details->item_name;
				$service_bit = $item_details->service_bit;
				// Use the item's cost (purchase_price with tax) preferentially, falling back to base price before tax
				$purchase_price = (!empty($item_details->purchase_price) && $item_details->purchase_price > 0) ? $item_details->purchase_price : $item_details->price;
				$current_stock_of_item = total_available_qty_items_of_warehouse($warehouse_id,null,$item_id);
				if($current_stock_of_item<$sales_qty && $service_bit==0){
					return $item_name." has only ".$current_stock_of_item." in Stock!!";exit;
				}
				
				$salesitems_entry = array(
		    				'sales_id' 			=> $sales_id, 
		    				'sales_status'		=> $sales_status, 
		    				'item_id' 			=> $item_id, 
		    				'description' 		=> $description, 
		    				'sales_qty' 		=> $sales_qty,
		    				'price_per_unit' 	=> $price_per_unit,
		    				'tax_type' 			=> $tax_type,
		    				'tax_id' 			=> $tax_id,
		    				'tax_amt' 			=> $tax_amt,
		    				'discount_input' 	=> $discount_input,
		    				'discount_amt' 		=> $discount_amt,
		    				'discount_type' 	=> $discount_type,
		    				'unit_total_cost' 	=> $single_unit_total_cost,
		    				'total_cost' 		=> $total_cost,
		    				'purchase_price' 	=> $purchase_price,
	    				'batch_lot' 		=> $batch_lot,
	    			'price_type' 		=> $price_type,
		    				'status'	 		=> 1,
		    				'seller_points'		=> get_seller_points($item_id) * $sales_qty,

		    			);
				
				$salesitems_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
				$q2 = $this->db->insert('db_salesitems', $salesitems_entry);
				if(!$q2){
					$err = $this->db->error();
					log_message('error', "Sales db_salesitems insert FAILED: #{$err['code']} {$err['message']} sales_id=$sales_id item_id=$item_id");
					$this->db->trans_rollback();
					return "Failed to save sale item: " . $err['message'];
				}
				$sale_items_id = $this->db->insert_id();
				log_message('error', "Sales db_salesitems OK: id=$sale_items_id sales_id=$sales_id item_id=$item_id qty=$sales_qty");

				// If this is a package, create customer package record
				if ($item_details->package_bit == 1) {
					$this->load->model('service_package_model');
					$pkg = $this->service_package_model->get_by_item_id($item_id);
					if ($pkg) {
						$this->service_package_model->create_customer_package($sales_id, $sale_items_id, $pkg->id, $customer_id);
					}
				}
				
				//UPDATE itemS QUANTITY IN itemS TABLE
				$this->load->model('pos_model');				
				$q6=$this->pos_model->update_items_quantity($item_id);
				if(!$q6){
					return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
				}
				
			}
		
		}//for end

		// WALK-IN CUSTOMER CANNOT OWE MONEY
		$walkin_paid = ($amount=='' || $amount==0) ? 0 : floatval($amount);
		if(is_walk_in_customer($customer_id) && $walkin_paid < $tot_total_amt){
			$this->db->trans_rollback();
			return "Walk-in Customer cannot have credit! Please create a registered customer profile or collect full payment.";
		}

		if($amount=='' || $amount==0){$amount=null;}
		if($amount>0 && !empty($payment_type)){

			if($amount>$tot_total_amt){
				$this->db->trans_rollback();
				return "Payble amount should not be exceeds Invoice Amount!!";
			}

			/**
			 * @update
			 * Verifieng previous and current payment total with invoice amount
			*/
			if($command=='update'){
				$tot_payment = $this->db->select('coalesce(sum(payment),0) as payment')->where('sales_id',$sales_id)->get('db_salespayments')->row()->payment;
				if(($tot_payment+$amount)>$tot_total_amt){
					$this->db->trans_rollback();
					return "Payble amount should not be exceeds Invoice Amount!!\nPlease check previous payments as well.";
				}
			}


			//is total advance payment enabled ?
			$advance_adjusted=0;
			if(isset($allow_tot_advance)){
				$tot_advance = get_customer_details($customer_id)->tot_advance;
				if($tot_advance>0){
					if($amount==$tot_advance){
						$advance_adjusted = $amount;
					}
					else if($amount>$tot_advance){
						$advance_adjusted = $tot_advance;	
					}
					else{
						$advance_adjusted =  $amount;
					}
				}
			}
			//end 

			// Look up payment_mode_id
			$pm_row = $this->db->select('id')->where('store_id', $store_id)->where('code', $payment_type)->get('db_payment_modes')->row();
			$payment_mode_id = $pm_row ? $pm_row->id : null;

			$payment_code=get_init_code('sales_payment');
			$salespayments_entry = array(
					'payment_code' 		=> $payment_code,
		    		'count_id'	  		=> get_count_id('db_salespayments'),
					'sales_id' 			=> $sales_id, 
					'payment_date'		=> $sales_date,//Current Payment with sales entry
					'payment_type' 		=> $payment_type,
					'payment_mode_id' 	=> $payment_mode_id,
					'payment' 			=> $amount,
					'payment_note' 		=> $payment_note,
					'payment_reference' 	=> $payment_reference ?? '',
					'confirmation_status'	=> $confirmation_status ?? 1,
					'created_date' 		=> $CUR_DATE,
    				'created_time' 		=> $CUR_TIME,
    				'created_by' 		=> $CUR_USERNAME,
    				'system_ip' 		=> $SYSTEM_IP,
    				'system_name' 		=> $SYSTEM_NAME,
    				'status' 			=> 1,
    				'account_id' 		=> (empty($account_id)) ? null : $account_id,
    				'customer_id' 		=> $customer_id,
    				'advance_adjusted' 	=> $advance_adjusted,
    				'cheque_number' 	=> $cheque_number,
    				'cheque_period' 	=> $cheque_period,
    				'cheque_status' 	=> "Pending",
				);
			$salespayments_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
			$q3 = $this->db->insert('db_salespayments', $salespayments_entry);
		if(!$q3){
			$err = $this->db->error();
			$this->db->trans_rollback();
			return "Failed to save sales payment at line " . __LINE__ . ": " . ($err['message'] ?? 'unknown error');
		}


			//Set the payment to specified account
			if(!empty($account_id)){
				//ACCOUNT INSERT
				$insert_bit = insert_account_transaction(array(
															'transaction_type'  	=> 'SALES PAYMENT',
															'reference_table_id'  	=> $this->db->insert_id(),
															'debit_account_id'  	=> null,
															'credit_account_id'  	=> $account_id,
															'debit_amt'  			=> 0,
															'credit_amt'  			=> $amount,
															'process'  				=> 'SAVE',
															'note'  				=> $payment_note,
															'transaction_date'  	=> $CUR_DATE,
															'payment_code'  		=> $payment_code,
															'customer_id'  			=> $customer_id,
															'supplier_id'  			=> null,
													));
				if(!$insert_bit){
					return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
				}
			}
			//end
			
		}
		
		
		

		$q10=$this->update_sales_payment_status($sales_id,$customer_id);
		if($q10!=1){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}
		
		
		if(!set_customer_tot_advance($customer_id)){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}
		/*$q10=$this->set_quotation_sales_status($sales_id);
		if(!$q10){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}*/
		
		//Dont save if invoice credit limit exceeds
		$credit_check = check_credit_limit_with_invoice($customer_id,$sales_id);
		if($credit_check !== true){
			return $credit_check;
		}


		$sms_info='';
		if(isset($send_sms) && $customer_id!=1){
			if(send_sms_using_template($sales_id,1)==true){
				$sms_info = 'SMS Has been Sent!';
			}else{
				$sms_info = 'Failed to Send SMS';
			}
		}
		

		##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$curr_item_ids = $this->db->select("item_id")->from("db_salesitems")->where("sales_id",$sales_id)->get()->result_array();
		$two_array = array_merge($prev_item_ids,$curr_item_ids);

		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($two_array);
		if(!$q7){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}
		##############################################END
		
		//Calculate Opening balance before and after invoice
		/*$q7=calculate_ob_of_customer($sales_id,$customer_id);
		if(!$q7){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}*/

		// Ensure invalid legacy due dates are stored as NULL

		//Record Loyalty Points
		if($customer_id != 1 && $tot_total_amt > 0){
			$this->load->model('loyalty_model');
			$settings = $this->loyalty_model->get_settings();
			if($settings && $settings->loyalty_enabled){
				$sale_items = $this->db->select('item_id, sales_qty, price_per_unit, discount_amt')
							->where('sales_id', $sales_id)
							->get('db_salesitems')
							->result();
				$loyalty_items = array();
				foreach($sale_items as $si){
					$loyalty_items[] = array(
						'item_id' => (int)$si->item_id,
						'qty' => (float)$si->sales_qty,
						'line_value' => max(0, (float)$si->price_per_unit * (float)$si->sales_qty - (float)($si->discount_amt ?? 0))
					);
				}
				$points = $this->loyalty_model->calculate_points_for_sale($customer_id, $tot_total_amt, $loyalty_items);
				if($points > 0){
					$this->loyalty_model->record_points($customer_id, $sales_id, $points, 'earn', 'Points earned from sale');
				}
			}
		}
		//end

		// Record promotion usage if a promotion code was used
		if(!empty($coupon_code)){
			$promo = $this->db->where('store_id', $store_id)->where('UPPER(promotion_code)', strtoupper($coupon_code))->get('db_promotions')->row();
			if($promo){
				$this->load->model('Promotions_model','promotions_m');
				$this->promotions_m->record_usage($promo->id, $customer_id, $sales_id, $store_id);
			}
		}

		$this->db->trans_commit();

		// Append sales_note to customer profile notes for tracking (works for all business types)
		// Skip walk-in customers (they don't have persistent profiles)
		if(!empty($sales_note) && !is_walk_in_customer($customer_id)){
			try {
				log_message('error', 'Saving sales_note to customer: customer_id=' . $customer_id . ', note=' . $sales_note);
				$existing_notes = $this->db->select('notes')->where('id', $customer_id)->get('db_customers')->row()->notes ?? '';
				$date_str = date('Y-m-d H:i');
				$new_entry = "\n\n[$date_str] $sales_note";
				$updated_notes = $existing_notes . $new_entry;
				$this->db->where('id', $customer_id)->update('db_customers', ['notes' => $updated_notes]);
				log_message('error', 'Customer notes updated successfully');
			} catch (Exception $e) {
				// Don't block sale if customer notes update fails
				log_message('error', 'Failed to update customer notes: ' . $e->getMessage());
			}
		} else {
			log_message('error', 'sales_note not saved: sales_note=' . $sales_note . ', customer_id=' . $customer_id . ', is_walkin=' . (is_walk_in_customer($customer_id) ? 'yes' : 'no'));
		}

		// Send invoice email to customer (non-critical — must not block save)
		if($customer_id != 1){ // Skip walk-in customer
			try {
				$this->load->model('email_service');
				$customer = get_customer_details($customer_id);
				$sales_rec = get_sales_details($sales_id);
				if(!empty($customer->email)){
					$this->email_service->sendTemplate(
						'invoice_sent',
						$customer->email,
						[
							'customer_name'   => $customer->customer_name,
							'invoice_number'  => $sales_rec->sales_code,
							'invoice_total'   => store_number_format($sales_rec->grand_total),
							'amount_paid'     => store_number_format($sales_rec->paid_amount),
							'amount_due'      => store_number_format($sales_rec->grand_total - $sales_rec->paid_amount),
							'invoice_link'    => base_url('sales/print_invoice/' . $sales_id),
							'store_name'      => get_store_details()->store_name,
						],
						['related_module' => 'sales', 'related_record_id' => $sales_id]
					);
				}
			} catch (Exception $e) {
				log_message('error', 'Invoice email failed for sales_id ' . $sales_id . ': ' . $e->getMessage());
			}
		}

		$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! '.$sms_info);
		return "success<<<###>>>$sales_id";
		
	}//verify_save_and_update() function end

	function update_sales_payment_status_by_sales_id($sales_id,$customer_id){
		$q8=$this->db->query("select COALESCE(SUM(payment),0) as payment from db_salespayments where sales_id='$sales_id'");
		$sum_of_payments=$q8->row()->payment;
		

		$payble_total=$this->db->query("select coalesce(sum(grand_total),0) as total from db_sales where id='$sales_id'")->row()->total;
		//$payble_total=$q9->row()->total;
		
		//$pending_amt=$payble_total-$sum_of_payments;

		$payment_status='';
		if($payble_total==$sum_of_payments){
			$payment_status="Paid";
		}
		else if($sum_of_payments!=0 && ($sum_of_payments<$payble_total)){
			$payment_status="Partial";
		}
		else if($sum_of_payments==0){
			$payment_status="Unpaid";
		}


		$q7=$this->db->query("update db_sales set 
							payment_status='$payment_status',
							paid_amount=$sum_of_payments 
							where id='$sales_id'");
		//$customer_id =$this->db->query("select customer_id from db_sales where id=$sales_id")->row()->customer_id;
		$q12 = $this->db->query("update db_customers set sales_due=(select COALESCE(SUM(grand_total),0)-COALESCE(SUM(paid_amount),0) from db_sales where customer_id='$customer_id' and sales_status='Final') where id=$customer_id");
		if(!$q7)
		{
			return false;
		}
		else{
			return true;
		}
	}


	function update_sales_payment_status($sales_id=null,$customer_id=null){
	//UPDATE PRODUCTS QUANTITY IN PRODUCTS TABLE
		if(empty($sales_id)){ //If sales ID not exist you need setup all the customers sales due
			$q11=$this->db->query("select id from db_customers");
			if($q11->num_rows()>0){
				foreach ($q11->result() as $res) {

					$q12=$this->db->query("select id from db_sales where customer_id=".$res->id);
					if($q12->num_rows()>0){
						foreach ($q12->result() as $res12) {
							if(!$this->update_sales_payment_status_by_sales_id($res12->id,$res->id)){
								return false;
							}
						}
					}
					else{
						$q13=$this->db->query("update db_customers set sales_due=0 where id=".$res->id);
						if(!$q13){
							return false;
						}
					}

				}
			}
			return true;
		}
		else{
					if(!$this->update_sales_payment_status_by_sales_id($sales_id,$customer_id)){
						return false;
					}
					return true;
		}
	}


	//Get sales_details
	public function get_details($id,$data){
		//Validate This sales already exist or not
		$query=$this->db->query("select * from db_sales where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['item_code']=$query->item_code;
			$data['item_name']=$query->item_name;
			$data['category_name']=$query->category_name;
			$data['hsn']=$query->hsn;
			$data['unit_name']=$query->unit_name;
			$data['available_qty']=$query->available_qty;
			$data['alert_qty']=$query->alert_qty;
			$data['sales_price']=$query->sales_price;
			$data['sales_price']=$query->sales_price;
			$data['gst_percentage']=$query->gst_percentage;
			
			return $data;
		}
	}
	public function update_status($id,$status){
		
        $query1="update db_sales set status='$status' where id=$id";
        if ($this->db->simple_query($query1)){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_sales($ids){
      	$this->db->trans_begin();

      	$q12=$this->db->select("*")->where("sales_id in ($ids)")->get("db_salesreturn");
      	if($q12->num_rows()>0){
      		foreach ($q12->result() as $res12) {
      			$sales_code = $this->db->select("sales_code")->where("id",$res12->sales_id)->get("db_sales")->row()->sales_code;
      			echo "<br>Invoice Code: ".$sales_code;
      		}
      		echo "<br>Already Raised Returns, Please Delete Before Deleting Original Invoice";
      		exit;
      	}

      	//ACCOUNT RESET
		// Get the affected payment rows
		$payment_rows = $this->db->select("id, account_id, payment_code")
								->where("sales_id in ($ids)")
								->get("db_salespayments")
								->result();
		$payment_codes = array_filter(array_unique(array_map(function($r){ return $r->payment_code; }, $payment_rows)));
		$account_ids = array_filter(array_unique(array_map(function($r){ return $r->account_id; }, $payment_rows)));

		// Delete the account transaction rows linked to this sale's payments
		if(!empty($payment_codes)){
			$this->db->where_in("payment_code", $payment_codes)
					 ->where("transaction_type", 'SALES PAYMENT')
					 ->delete("ac_transactions");
			// Also delete the payment rows themselves
			$this->db->where("sales_id in ($ids)")
					 ->delete("db_salespayments");
		}
		//ACCOUNT RESET END

      	##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$prev_item_ids = $this->db->select("item_id")->from("db_salesitems")->where("sales_id in ($ids)")->get()->result_array();
		##############################################END
		
		//RESET QUOTATION RESET
		if(!$this->reset_quotation_sales_status_to_null($ids)){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}

		// Delete the invoice line items
		$this->db->where("sales_id in ($ids)")
				 ->delete("db_salesitems");

		//find customer list group by
		$this->db->select("customer_id,id as sales_id");
		$this->db->where("id in ($ids)");
		$this->db->where("store_id",get_current_store_id());
		$this->db->group_by("customer_id");
		$customer_records=$this->db->get("db_sales");
		//end

		#----------------------------------
		$this->db->where("id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q3=$this->db->delete("db_sales");
		#----------------------------------
		
		$item_ids_first = get_in_comma_delimited($prev_item_ids);



		$q6=$this->db->query("select id from db_items where id in ('".$item_ids_first."')");
		if($q6->num_rows()>0){			
			$this->load->model('pos_model');
			foreach ($q6->result() as $res6) {
				$q6=$this->pos_model->update_items_quantity($res6->id);
				if(!$q6){
					return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
				}
			}
		}
		

    	if($customer_records->num_rows()>0){
        	foreach ($customer_records->result() as $res) {
        		if(!$this->update_sales_payment_status($res->sales_id,$res->customer_id)){
		        	return 'failed';
		        }

        	}        		
        }

		
		##############################################START
		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($prev_item_ids);
		if(!$q7){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}
		##############################################END
		
		//ACCOUNT RESET
		if(!empty($account_ids)){
			foreach ($account_ids as $acc_id) {
				if(!empty($acc_id) && !update_account_balance($acc_id)){
					return 'failed';
				}
			}
		}
		//ACCOUNT RESET END

        if($customer_records->num_rows()>0){
        	foreach ($customer_records->result() as $customer_id) {
        		if(!set_customer_tot_advance($customer_id->customer_id)){
		        	return 'failed';
		        }
        	}
        		
        }


		$this->db->trans_commit();
		return "success";
	}
	public function search_item($q){
		$json_array=array();
        $query1="select id,item_name from db_items where (upper(item_name) like upper('%$q%') or upper(item_code) like upper('%$q%'))";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>(int)$value->id, 'text'=>$value->item_name];
            }
        }

        // Also search db_item_barcodes by barcode, serial, or imei
        $this->db->select('b.item_id, a.item_name, b.barcode, b.serial_number, b.imei_number');
        $this->db->from('db_item_barcodes b');
        $this->db->join('db_items a', 'a.id = b.item_id', 'left');
        $this->db->where('b.status', 1);
        $this->db->where("(LOWER(b.barcode) LIKE '%$q%' OR LOWER(b.serial_number) LIKE '%$q%' OR LOWER(b.imei_number) LIKE '%$q%')", null, false);
        $this->db->group_by('b.item_id');
        $q2 = $this->db->get();
        if($q2->num_rows()>0){
            foreach ($q2->result() as $value) {
                $label = $value->item_name;
                if($value->barcode) $label .= ' [BC:'.$value->barcode.']';
                if($value->serial_number) $label .= ' [S/N:'.$value->serial_number.']';
                if($value->imei_number) $label .= ' [IMEI:'.$value->imei_number.']';
                $json_array[]=['id'=>(int)$value->item_id, 'text'=>$label];
            }
        }
        return json_encode($json_array);
	}
	
	public function find_item_details($id){
		$json_array=array();
        $query1="select id,hsn,alert_qty,unit_name,sales_price,sales_price,gst_percentage,available_qty from db_items where id=$id";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array=['id'=>$value->id, 
        			 'hsn'=>$value->hsn,
        			 'alert_qty'=>$value->alert_qty,
        			 'unit_name'=>$value->unit_name,
        			 'sales_price'=>$value->sales_price,
        			 'sales_price'=>$value->sales_price,
        			 'gst_percentage'=>$value->gst_percentage,
        			 'available_qty'=>$value->available_qty,
        			];
            }
        }
        return json_encode($json_array);
	}

	
	public function reset_quotation_sales_status_to_null($sales_ids){
			$this->db->where("id in($sales_ids)");
			$this->db->where("quotation_id!=''");
			$this->db->select("quotation_id");
			$quotation_ids = $this->db->get("db_sales");

			//if records exist
			if($quotation_ids->num_rows()>0){
				$tmpArr = array();
			    foreach ($quotation_ids->result() as $sub) {
			      $tmpArr[] = $sub->quotation_id;
			    }
			    $quotation_ids = implode(',', $tmpArr);
			    if(!empty($quotation_ids)){
			    	$q11 = $this->db->set("sales_status",null)->where("id in($quotation_ids)")->update("db_quotation");
			    	if(!$q11){
			    		return false;
			    	}
			    }
			}
			return true;
  	}


	
	/*v1.1*/
	public function inclusive($price, $tax_per){
		return ($tax_per!=0) ? $price/(($tax_per/100)+1)/10 : $tax_per;
	}
	public function get_items_info($rowcount,$item_id){
		$customer_id = $this->input->post('customer_id', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$barcode = $this->input->post('barcode', TRUE) ?? '';
		$price_type = $this->input->post('price_type', TRUE) ?? 'wholesale';

		// Barcode-specific lookup
		if(!empty($barcode)){
			$this->db->select('b.*, a.item_name, a.tax_id, a.tax_type, a.discount_type, a.discount, a.service_bit, t.tax, t.tax_name');
			$this->db->from('db_item_barcodes b');
			$this->db->join('db_items a', 'a.id = b.item_id', 'left');
			$this->db->join('db_tax t', 't.id = a.tax_id', 'left');
			$this->db->where('b.barcode', $barcode);
			$this->db->where('b.item_id', $item_id);
			$this->db->where('b.status', 1);
			$bc_data = $this->db->get()->row();
			if($bc_data){
				$sales_price = ($price_type == 'retail' && !empty($bc_data->mrp) && $bc_data->mrp > 0) ? $bc_data->mrp : $bc_data->sales_price;
				$sales_price = get_price_level_price($customer_id,$sales_price);
				$sales_price = number_format($sales_price,decimals(),'.','');

				// Apply active promotion (with margin protection) if module is available
				$bc_promo_name = '';
				$bc_promo_discount = 0;
				try {
					if($this->db->table_exists('db_promotions')){
						$this->load->model('promotions_model');
						$bc_promo = $this->promotions_model->compute_effective_price($item_id, $sales_price);
						if($bc_promo['has_promo']){
							$sales_price = number_format($bc_promo['price'], decimals(), '.', '');
							$bc_promo_name = $bc_promo['promo_name'];
							$bc_promo_discount = $bc_promo['discount_amount'];
						}
					}
				} catch (Exception $e) { /* Promotions module not ready yet */ }

				$item_tax_amt = ($bc_data->tax_type=='Inclusive') ? calculate_inclusive($sales_price,$bc_data->tax) :calculate_exclusive($sales_price,$bc_data->tax);
				$info = array(
					'item_id' => $item_id,
					'description' => '',
					'item_name' => $bc_data->item_name,
					'item_available_qty' => $bc_data->qty,
					'item_price' => $bc_data->purchase_price,
					'item_sales_price' => $sales_price,
					'item_tax_name' => $bc_data->tax_name,
					'item_sales_qty' => ($bc_data->qty<1 && $bc_data->service_bit!=1) ? $bc_data->qty : number_format(1,2),
					'item_tax_id' => $bc_data->tax_id,
					'item_tax' => $bc_data->tax,
					'item_tax_type' => $bc_data->tax_type,
					'item_tax_amt' => $item_tax_amt,
					'item_discount' => 0,
					'item_discount_type' => $bc_data->discount_type,
					'item_discount_input' => $bc_data->discount,
					'service_bit' => $bc_data->service_bit,
					'batch_lot' => $bc_data->batch_lot,
					'barcode' => $barcode,
					'price_type' => $price_type,
					'promo_name' => $bc_promo_name,
					'promo_discount' => $bc_promo_discount,
				);
				$this->return_row_with_data($rowcount,$info);
				return;
			}
		}

		$res1=$this->db->select('*')->from('db_items')->where("id=$item_id")->get()->row();

		// Check expiry
		try {
			$this->load->model('expiry_settings_model');
			$expiry_settings = $this->expiry_settings_model->get_settings();
			if(is_valid_date($res1->expire_date) && $expiry_settings->stop_selling_expired == 1 && $res1->expire_date < date('Y-m-d')){
				echo json_encode(array('error' => 'This item has expired ('.$res1->expire_date.'). Cannot sell expired items.'));
				return;
			}
		} catch (Exception $e) { /* Expiry settings not ready yet */ }

		$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();

		//Get Customer Price
		$price_type = $this->input->post('price_type', TRUE) ?? 'wholesale';
		$base_price = ($price_type == 'retail' && !empty($res1->mrp) && $res1->mrp > 0) ? $res1->mrp : $res1->sales_price;
		$sales_price = get_price_level_price($customer_id,$base_price);
		$sales_price = number_format($sales_price,decimals(),'.','');

		// Apply active promotion (with margin protection) if module is available
		$promo_name = '';
		$promo_discount = 0;
		try {
			if($this->db->table_exists('db_promotions')){
				$this->load->model('promotions_model');
				$promo = $this->promotions_model->compute_effective_price($item_id, $sales_price);
				if($promo['has_promo']){
					$sales_price = number_format($promo['price'], decimals(), '.', '');
					$promo_name = $promo['promo_name'];
					$promo_discount = $promo['discount_amount'];
				}
			}
		} catch (Exception $e) { /* Promotions module not ready yet */ }

		$item_available_qty = total_available_qty_items_of_warehouse($warehouse_id,null,$res1->id);// $res1->stock;

		$item_tax_amt = ($res1->tax_type=='Inclusive') ? calculate_inclusive($sales_price,$q3->tax) :calculate_exclusive($sales_price,$q3->tax);

		$info = array(
							'item_id' 					=> $res1->id,
							'description' 				=> '',
							'item_name' 				=> $res1->item_name,
							'item_available_qty' 		=> $item_available_qty,
							'item_price' 				=> $res1->price,
							'item_sales_price' 			=> $sales_price,
							'item_tax_name' 			=> $q3->tax_name,
							'item_sales_qty' 			=> ($item_available_qty<1 && $res1->service_bit!=1) ? $item_available_qty : number_format(1,2),
							'item_tax_id' 				=> $q3->id,
							'item_tax' 					=> $q3->tax,
							'item_tax_type' 			=> $res1->tax_type,
							'item_tax_amt' 				=> $item_tax_amt,
							'item_discount' 			=> 0,
							'item_discount_type' 		=> $res1->discount_type,
							'item_discount_input' 		=> $res1->discount,
							'service_bit' 				=> $res1->service_bit,
							'price_type' 				=> $price_type,
							'promo_name' 				=> $promo_name,
							'promo_discount' 			=> $promo_discount,
						);

		$this->return_row_with_data($rowcount,$info);
	}
	/* For Quotation Items List Retrieve*/
	public function return_quotation_list($quotation_id){
		$q1=$this->db->select('*')->from('db_quotationitems')->where("quotation_id=$quotation_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$res2=$this->db->query("select * from db_items where id=".$res1->item_id)->row();
			$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
			
			$info = array(
							'item_id' 					=> $res1->item_id, 
							'description' 				=> $res1->description, 
							'item_name' 				=> $res2->item_name,
							'item_available_qty' 		=> $res2->stock,
							'item_price' 				=> $res2->price, 
							'item_sales_price' 			=> $res1->price_per_unit, 
							'item_tax_name' 			=> $q3->tax_name, 
							'item_sales_qty' 			=> $res1->quotation_qty, 
							'item_tax_id' 				=> $res1->tax_id, 
							'item_tax' 					=> $q3->tax, 
							'item_tax_type' 			=> $res1->tax_type, 
							'item_tax_amt' 				=> $res1->tax_amt, 
							'item_discount' 			=> $res1->discount_input, 
							'item_discount_type' 		=> $res1->discount_type, 
							'item_discount_input' 		=> $res1->discount_input, 
							'service_bit' 				=> 1, 
						);

			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}
	/* For Purchase Items List Retrieve*/
	public function return_sales_list($sales_id){
		$q1=$this->db->select('*')->from('db_salesitems')->where("sales_id=$sales_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$res2=$this->db->query("select * from db_items where id=".$res1->item_id)->row();
			$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
			
			$info = array(
							'item_id' 					=> $res1->item_id, 
							'description' 				=> $res1->description, 
							'item_name' 				=> $res2->item_name,
							'item_available_qty' 		=> ($res2->stock + $res1->sales_qty),
							'item_price' 				=> $res2->price, 
							'item_sales_price' 			=> $res1->price_per_unit, 
							'item_tax_name' 			=> $q3->tax_name, 
							'item_sales_qty' 			=> $res1->sales_qty, 
							'item_tax_id' 				=> $res1->tax_id, 
							'item_tax' 					=> $q3->tax, 
							'item_tax_type' 			=> $res1->tax_type, 
							'item_tax_amt' 				=> $res1->tax_amt, 
							'item_discount' 			=> $res1->discount_input, 
							'item_discount_type' 		=> $res1->discount_type, 
							'item_discount_input' 		=> $res1->discount_input, 
							'service_bit' 				=> $res2->service_bit, 
							'sold_serial_number' 		=> $res1->sold_serial_number, 
							'sold_imei_number' 			=> $res1->sold_imei_number, 
							'barcode_id' 				=> $res1->barcode_id, 
						);

			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}

	public function return_row_with_data($rowcount,$info){
		$item_id = isset($info['item_id']) ? $info['item_id'] : '';
		$description = isset($info['description']) ? $info['description'] : '';
		$item_name = isset($info['item_name']) ? $info['item_name'] : '';
		$item_available_qty = isset($info['item_available_qty']) ? $info['item_available_qty'] : '';
		$item_price = isset($info['item_price']) ? $info['item_price'] : '';
		$item_sales_price = isset($info['item_sales_price']) ? $info['item_sales_price'] : '';
		$item_tax_name = isset($info['item_tax_name']) ? $info['item_tax_name'] : '';
		$item_sales_qty = isset($info['item_sales_qty']) ? $info['item_sales_qty'] : '';
		$item_tax_id = isset($info['item_tax_id']) ? $info['item_tax_id'] : '';
		$item_tax = isset($info['item_tax']) ? $info['item_tax'] : '';
		$item_tax_type = isset($info['item_tax_type']) ? $info['item_tax_type'] : '';
		$item_tax_amt = isset($info['item_tax_amt']) ? $info['item_tax_amt'] : '';
		$item_discount = isset($info['item_discount']) ? $info['item_discount'] : '';
		$item_discount_type = isset($info['item_discount_type']) ? $info['item_discount_type'] : '';
		$item_discount_input = isset($info['item_discount_input']) ? $info['item_discount_input'] : '';
		$service_bit = isset($info['service_bit']) ? $info['service_bit'] : '';
		$item_amount = ($item_sales_price * $item_sales_qty) + $item_tax_amt;
		$promo_name = isset($info['promo_name']) ? $info['promo_name'] : '';
		?>
            <tr id="row_<?=$rowcount;?>" data-row='<?=$rowcount;?>'>
               <!-- Item Name -->
               <td id="td_<?=$rowcount;?>_1">
                  <div class="si-name">
                     <a id="td_data_<?=$rowcount;?>_1" href="javascript:void()" onclick="show_sales_item_modal(<?=$rowcount;?>)" class="si-name-link" title="Edit item details"><?=$item_name;?></a>
                     <?php if(!empty($promo_name)): ?>
                        <span class="si-promo"><?= htmlspecialchars($promo_name); ?></span>
                     <?php endif; ?>
                     <span class="si-meta">In stock: <span id="tr_available_qty_<?=$rowcount;?>_13_disp"><?= $item_available_qty; ?></span></span>
                  </div>
               </td>

               <!-- Qty -->
               <td id="td_<?=$rowcount;?>_3" class="num">
                  <div class="qty-stepper">
                     <button onclick="decrement_qty(<?=$rowcount;?>)" type="button" class="qty-btn" title="Decrease"><i class="fa fa-minus"></i></button>
                     <input type="text" value="<?=format_qty($item_sales_qty);?>" class="qty-input" onkeyup="calculate_tax(<?=$rowcount;?>)" id="td_data_<?=$rowcount;?>_3" name="td_data_<?=$rowcount;?>_3">
                     <button onclick="increment_qty(<?=$rowcount;?>)" type="button" class="qty-btn" title="Increase"><i class="fa fa-plus"></i></button>
                  </div>
               </td>

               <!-- Unit Price -->
               <td id="td_<?=$rowcount;?>_10" class="num"><input type="text" name="td_data_<?=$rowcount;?>_10" id="td_data_<?=$rowcount;?>_10" class="cell-input num" onkeyup="calculate_tax(<?=$rowcount;?>)" value="<?=store_number_format($item_sales_price);?>"></td>

               <!-- Discount -->
               <td id="td_<?=$rowcount;?>_8" class="num">
                  <input type="text" data-toggle="tooltip" title="Click to Change" name="td_data_<?=$rowcount;?>_8" id="td_data_<?=$rowcount;?>_8" class="cell-input num item_discount" value="<?=store_number_format($item_discount);?>" onclick="show_sales_item_modal(<?=$rowcount;?>)" readonly>
               </td>

               <!-- Tax (amount + name) -->
               <td id="td_<?=$rowcount;?>_11" class="num">
                  <input type="text" name="td_data_<?=$rowcount;?>_11" id="td_data_<?=$rowcount;?>_11" class="cell-input num" value="<?=store_number_format($item_tax_amt);?>" readonly>
                  <a id="td_data_<?=$rowcount;?>_12" href="javascript:void()" data-toggle="tooltip" title='Click to Change' onclick="show_sales_item_modal(<?=$rowcount;?>)" class="si-tax-name"><?=$item_tax_name ;?></a>
               </td>

               <!-- Total -->
               <td id="td_<?=$rowcount;?>_9" class="num total"><input type="text" name="td_data_<?=$rowcount;?>_9" id="td_data_<?=$rowcount;?>_9" class="cell-input num total" readonly value="<?=store_number_format($item_amount);?>"></td>

               <!-- Remove -->
               <td id="td_<?=$rowcount;?>_16" class="action-col">
                  <a class="remove-btn" onclick="removerow(<?=$rowcount;?>)" title="Delete ?" name="td_data_<?=$rowcount;?>_16" id="td_data_<?=$rowcount;?>_16"><i class="fa fa-trash"></i></a>
               </td>
               <input type="hidden" id="td_data_<?=$rowcount;?>_4" name="td_data_<?=$rowcount;?>_4" value="<?=$item_sales_price;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_15" name="td_data_<?=$rowcount;?>_15" value="<?=$item_tax_id;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_5" name="td_data_<?=$rowcount;?>_5" value="<?=$item_tax_amt;?>">
               <input type="hidden" id="tr_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr_item_id_<?=$rowcount;?>" name="tr_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
               
               <input type="hidden" id="tr_tax_type_<?=$rowcount;?>" name="tr_tax_type_<?=$rowcount;?>" value="<?=$item_tax_type;?>">
               <input type="hidden" id="tr_tax_id_<?=$rowcount;?>" name="tr_tax_id_<?=$rowcount;?>" value="<?=$item_tax_id;?>">
               <input type="hidden" id="tr_tax_value_<?=$rowcount;?>" name="tr_tax_value_<?=$rowcount;?>" value="<?=$item_tax;?>">
               <input type="hidden" id="description_<?=$rowcount;?>" name="description_<?=$rowcount;?>" value="<?=$description;?>">
               <input type="hidden" id="service_bit_<?=$rowcount;?>" name="service_bit_<?=$rowcount;?>" value="<?=$service_bit;?>">
               <input type="hidden" id="batch_lot_<?=$rowcount;?>" name="batch_lot_<?=$rowcount;?>" value="<?=isset($info['batch_lot']) ? $info['batch_lot'] : '';?>">
               <input type="hidden" id="barcode_<?=$rowcount;?>" name="barcode_<?=$rowcount;?>" value="<?=isset($info['barcode']) ? $info['barcode'] : '';?>">
               <input type="hidden" id="price_type_<?=$rowcount;?>" name="price_type_<?=$rowcount;?>" value="<?=isset($info['price_type']) ? $info['price_type'] : 'wholesale';?>">

               <input type="hidden" id="item_discount_type_<?=$rowcount;?>" name="item_discount_type_<?=$rowcount;?>" value="<?=$item_discount_type;?>">
               <input type="hidden" id="item_discount_input_<?=$rowcount;?>" name="item_discount_input_<?=$rowcount;?>" value="<?=store_number_format($item_discount_input,0);?>">
            </tr>
		<?php

	}
	public function delete_payment($payment_id){
        $this->db->trans_begin();

        //ACCOUNT RESET
		$reset_accounts = $this->db->select("debit_account_id,credit_account_id")
									->where("ref_salespayments_id in ($payment_id)")
									->group_by("debit_account_id,credit_account_id")
									->get("ac_transactions");
		//ACCOUNT RESET END

		$salespayments = $this->db->query("select sales_id,customer_id from db_salespayments where id=$payment_id")->row();
		$sales_id = $salespayments->sales_id;
		$customer_id = $salespayments->customer_id;

		$q1=$this->db->query("delete from db_salespayments where id='$payment_id'");
		if(!$q1){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}
		$q2=$this->update_sales_payment_status($sales_id,$customer_id);
		if(!$q2){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}

		//ACCOUNT RESET
        if($reset_accounts->num_rows()>0){
        	foreach ($reset_accounts->result() as $res1) {
        		if(!update_account_balance($res1->debit_account_id)){
					return 'failed';
				}

				if(!update_account_balance($res1->credit_account_id)){
					return 'failed';
				}

        	}
        }
        //ACCOUNT RESET END

        if(!set_customer_tot_advance($customer_id)){
        	return 'failed';
        }
		$this->db->trans_commit();
		return "success";
		
	}

	public function show_pay_now_modal($sales_id){
		$q1=$this->db->query("select * from db_sales where id=$sales_id");
		$res1=$q1->row();
		$customer_id = $res1->customer_id;
		$q2=$this->db->query("select * from db_customers where id=$customer_id");
		$res2=$q2->row();

		$customer_name=$res2->customer_name;
	    $customer_mobile=$res2->mobile;
	    $customer_phone=$res2->phone;
	    $customer_email=$res2->email;
	    $customer_country=$res2->country_id;
	    $customer_state=$res2->state_id;
	    $customer_address=$res2->address;
	    $customer_postcode=$res2->postcode;
	    $customer_gst_no=$res2->gstin;
	    $customer_tax_number=$res2->tax_number;
	    $customer_opening_balance=$res2->opening_balance;
	    $customer_tot_advance=$res2->tot_advance;

	    $sales_date=$res1->sales_date;
	    $reference_no=$res1->reference_no;
	    $sales_code=$res1->sales_code;
	    $sales_note=$res1->sales_note;
	    $grand_total=$res1->grand_total;
	    $paid_amount=$res1->paid_amount;
	    $due_amount =$grand_total - $paid_amount;

	    if(!empty($customer_country)){
	      $customer_country = $this->db->query("select country from db_country where id='$customer_country'")->row()->country;  
	    }
	    if(!empty($customer_state)){
	      $customer_state = $this->db->query("select state from db_states where id='$customer_state'")->row()->state;  
	    }

		?>
		<div class="modal fade" id="pay_now" tabindex='-1'>
		  <div class="modal-dialog ">
		    <div class="modal-content">
		      <div class="modal-header header-custom">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center"><?= $this->lang->line('payments'); ?></h4>
		      </div>
		      <div class="modal-body">
		        
		    <div class="row">
		      <div class="col-md-12">
		      	<div class="row invoice-info">
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('customer_details'); ?>
			          <address>
			            <strong><?php echo  $customer_name; ?></strong><br>
			            <?php echo (!empty(trim($customer_mobile))) ? $this->lang->line('mobile').": ".$customer_mobile."<br>" : '';?>
			            <?php echo (!empty(trim($customer_phone))) ? $this->lang->line('phone').": ".$customer_phone."<br>" : '';?>
			            <?php echo (!empty(trim($customer_email))) ? $this->lang->line('email').": ".$customer_email."<br>" : '';?>
			            <?php echo (!empty(trim($customer_gst_no))) ? $this->lang->line('gst_number').": ".$customer_gst_no."<br>" : '';?>
			            <?php echo (!empty(trim($customer_tax_number))) ? $this->lang->line('tax_number').": ".$customer_tax_number."<br>" : '';?>
			            
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('sales_details'); ?>
			          <address>
			            <b><?= $this->lang->line('invoice'); ?> #<?php echo  $sales_code; ?></b><br>
			            <b><?= $this->lang->line('date'); ?> :<?php echo show_date($sales_date); ?></b><br>
			            <b><?= $this->lang->line('grand_total'); ?> :<?php echo $grand_total; ?></b><br>
			          </address>
			        </div>
			        <!-- /.col -->
			       
			        <div class="col-sm-4 invoice-col">
			          <b><?= $this->lang->line('paid_amount'); ?> :<span><?php echo number_format($paid_amount,2,'.',''); ?></span></b><br>
			          <b><?= $this->lang->line('due_amount'); ?> :<span id='due_amount_temp'><?php echo number_format($due_amount,decimals(),'.',''); ?></span></b><br>
			         
			        </div>
			        <!-- /.col -->
			      </div>
			      <!-- /.row -->
		      </div>
		      <div class="col-md-12">
		        <div>
		        <input type="hidden" name="payment_row_count" id='payment_row_count' value="1">
		        <div class="col-md-12  payments_div">
		          <div class="box box-solid bg-gray">
		            <div class="box-body">
			            <div class="row">
	                         <div class="col-md-12">
	                          <span for="">
	                            <label>
	                            <?= $this->lang->line('advance'); ?> : <label><?=store_number_format($customer_tot_advance)?></label>
	                          </label>
	                          </span>
	                          <div class="checkbox">
	                            <label>
	                              <input type="checkbox" id="allow_tot_advance" name="allow_tot_advance"> <?= $this->lang->line('adjust_advance_payment'); ?>
	                            </label>
	                          </div>
	                         </div>
	                  	</div>

		              <div class="row">
		         		<div class="col-md-6">
		                  <div class="">
		                  <label for="payment_date"><?= $this->lang->line('date'); ?></label>
		                    <div class="input-group date">
			                      <div class="input-group-addon">
			                      <i class="fa fa-calendar"></i>
			                      </div>
			                      <input type="text" class="form-control pull-right datepicker" value="<?= show_date(date("d-m-Y")); ?>" id="payment_date" name="payment_date" readonly>
			                    </div>
		                      <span id="payment_date_msg" style="display:none" class="text-danger"></span>
		                </div>
		               </div>
		                <div class="col-md-6">
		                  <div class="">
		                  <label for="amount"><?= $this->lang->line('amount'); ?></label>
		                    <input type="text" class="form-control text-right paid_amt" id="amount" name="amount" placeholder="" value="<?=$due_amount;?>" >
		                      <span id="amount_msg" style="display:none" class="text-danger"></span>
		                </div>
		               </div>

		               

		                <div class="col-md-6">
		                  <div class="">
		                    <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
		                    <select class="form-control" id='payment_type' name="payment_type" onchange="show_cheque_details()">
		                      <?php
		                        $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
		                         if($q1->num_rows()>0){
		                             foreach($q1->result() as $res1){
		                             echo "<option value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
		                           }
		                         }
		                         else{
		                            echo "No Records Found";
		                         }
		                        ?>
		                    </select>
		                    <span id="payment_type_msg" style="display:none" class="text-danger"></span>
		                  </div>
		                </div>
		                <div class="col-md-6">
		                  <div class="">
		                    <label for="account_id"><?= $this->lang->line('account'); ?></label>
		                    <select class="form-control" id='account_id' name="account_id">
		                    	<option value="">-None-</option>
                                <?= get_accounts_select_list(get_store_details()->default_account_id);?>
		                    </select>
		                    <span id="account_id_msg" style="display:none" class="text-danger"></span>
		                  </div>
		                </div>

		                <div class="cheque_div" style="display: none;">
		               	<div class="col-md-6">
                        <label for="cheque_number"><?= $this->lang->line('cheque_number'); ?></label>
                          <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                            <span id="cheque_number_msg" style="display:none" class="text-danger"></span>
                     	</div>
                     	<div class="col-md-6">
                     	<label for="cheque_period"><?= $this->lang->line('cheque_period'); ?></label>
                          <input type="text" class="form-control" id="cheque_period" name="cheque_period">
                            <span id="cheque_period_msg" style="display:none" class="text-danger"></span>
                     	</div>
                     	</div><!-- cheque_div -->



		            <div class="clearfix"></div>
		        </div>  
		        <div class="row">
		               <div class="col-md-12">
		                  <div class="">
		                    <label for="payment_note"><?= $this->lang->line('payment_note'); ?></label>
		                    <textarea type="text" class="form-control" id="payment_note" name="payment_note" placeholder="" ></textarea>
		                    <span id="payment_note_msg" style="display:none" class="text-danger"></span>
		                  </div>
		               </div>
		                
		            <div class="clearfix"></div>
		        </div>   
		        </div>
		        </div>
		      </div><!-- col-md-12 -->
		    </div>
		      </div><!-- col-md-9 -->
		      <!-- RIGHT HAND -->
		    </div>
		      </div>
		      <div class="modal-footer">
		      	<input type="hidden" id="customer_id" value="<?=$customer_id?>">
		        <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
		        <button type="button" onclick="save_payment(<?=$sales_id;?>)" class="btn bg-green btn-lg place_order btn-lg payment_save">Save<i class="fa  fa-check "></i></button>
		      </div>
		    </div>
		    <!-- /.modal-content -->
		  </div>
		  <!-- /.modal-dialog -->
		</div>
		<?php
	}

	public function save_payment(){
		$amount = $this->input->post('amount', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$payment_type = (!empty($payment_type)) ? $payment_type : get_default_payment_mode_code($store_id);
		$payment_date = $this->input->post('payment_date', TRUE);
		$payment_note = $this->input->post('payment_note', TRUE);
		$sales_id = $this->input->post('sales_id', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$account_id = $this->input->post('account_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$cheque_number = $this->input->post('cheque_number', TRUE);
		$cheque_period = $this->input->post('cheque_period', TRUE);
		$allow_tot_advance = $this->input->post('allow_tot_advance', TRUE);
		//print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
    	if($amount=='' || $amount==0){$amount=null;}
		if($amount>0 && !empty($payment_type)){

			$this->db->trans_begin();

			// Look up payment_mode_id
			$pm_row = $this->db->select('id')->where('store_id', $store_id)->where('code', $payment_type)->get('db_payment_modes')->row();
			$payment_mode_id = $pm_row ? $pm_row->id : null;

			// Auto-link cash payments to the active till account (or store default)
			if(empty($account_id) && strtolower($payment_type) === 'cash'){
				$account_id = get_current_cash_account_id();
				if(empty($account_id)){
					$account_id = get_cash_account_id();
				}
			}

			$payment_code=get_init_code('sales_payment');
			$salespayments_entry = array(
					'store_id' 			=> get_sales_details($sales_id)->store_id,
					'payment_code' 		=> $payment_code,
		    		'count_id'	  		=> get_count_id('db_salespayments'),
					'sales_id' 			=> $sales_id, 
					'payment_date'		=> system_fromatted_date($payment_date),//Current Payment with sales entry
					'payment_type' 		=> $payment_type,
					'payment_mode_id' 	=> $payment_mode_id,
					'payment' 			=> $amount,
					'payment_note' 		=> $payment_note,
					'created_date' 		=> $CUR_DATE,
    				'created_time' 		=> $CUR_TIME,
    				'created_by' 		=> $CUR_USERNAME,
    				'system_ip' 		=> $SYSTEM_IP,
    				'system_name' 		=> $SYSTEM_NAME,
    				'status' 			=> 1,
    				'account_id' 		=> (empty($account_id)) ? null : $account_id,
    				'customer_id' 		=> $customer_id,
    				'cheque_number' 	=> $cheque_number,
    				'cheque_period' 	=> $cheque_period,
    				'cheque_status' 	=> "Pending",
				);
		
			//is total advance payment enabled ?
			$advance_adjusted=0;
			if($allow_tot_advance=='checked'){
				$tot_advance = get_customer_details($customer_id)->tot_advance;
				if($tot_advance>0){
					if($amount==$tot_advance){
						$advance_adjusted = $amount;
					}
					else if($amount>$tot_advance){
						$advance_adjusted = $tot_advance;	
					}
					else{
						$advance_adjusted =  $amount;
					}
				}
			}
			//end 
			$salespayments_entry['advance_adjusted'] = $advance_adjusted;
			$q3 = $this->db->insert('db_salespayments', $salespayments_entry);
		if(!$q3){
			$err = $this->db->error();
			$this->db->trans_rollback();
			return "Failed to save sales payment at line " . __LINE__ . ": " . ($err['message'] ?? 'unknown error');
		}

			//Set the payment to specified account
			if(!empty($account_id)){
				//ACCOUNT INSERT
				$insert_bit = insert_account_transaction(array(
															'transaction_type'  	=> 'SALES PAYMENT',
															'reference_table_id'  	=> $this->db->insert_id(),
															'debit_account_id'  	=> null,
															'credit_account_id'  	=> $account_id,
															'debit_amt'  			=> 0,
															'credit_amt'  			=> $amount,
															'process'  				=> 'SAVE',
															'note'  				=> $payment_note,
															'transaction_date'  	=> $CUR_DATE,
															'payment_code'  		=> $payment_code,
															'customer_id'  			=> $customer_id,
															'supplier_id'  			=> null,
													));
				if(!$insert_bit){
					return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
				}
			}
			//end

			if(!set_customer_tot_advance($customer_id)){
	        	return 'failed';
	        }
			
		}
		else{
			return "Please Enter Valid Amount!";
		}
		
		$q10=$this->update_sales_payment_status($sales_id,$customer_id);
		if($q10!=1){
			return "Failed to save sale at line " . __LINE__ . ": " . (($err = $this->db->error()) ? $err['message'] : 'unknown error');
		}

		$this->db->trans_commit();

		// Send payment received email to customer (non-critical)
		if($customer_id != 1){ // Skip walk-in customer
			try {
				$this->load->model('email_service');
				$customer = get_customer_details($customer_id);
				$sales_rec = get_sales_details($sales_id);
				if(!empty($customer->email)){
					$this->email_service->sendTemplate(
						'payment_received',
						$customer->email,
						[
							'customer_name'       => $customer->customer_name,
							'invoice_number'      => $sales_rec->sales_code,
							'amount_paid'         => store_number_format($amount),
							'amount_due'          => store_number_format($sales_rec->grand_total - $sales_rec->paid_amount),
							'payment_reference'   => $payment_code,
							'store_name'          => get_store_details()->store_name,
						],
						['related_module' => 'sales_payments', 'related_record_id' => $sales_id]
					);
				}
			} catch (Exception $e) {
				log_message('error', 'Payment received email failed for sales_id ' . $sales_id . ': ' . $e->getMessage());
			}
		}

		return "success";

	}
	
	public function view_payments_modal($sales_id){
		$q1=$this->db->query("select * from db_sales where id=$sales_id");
		$res1=$q1->row();
		$customer_id = $res1->customer_id;
		$q2=$this->db->query("select * from db_customers where id=$customer_id");
		$res2=$q2->row();

		$customer_name=$res2->customer_name;
	    $customer_mobile=$res2->mobile;
	    $customer_phone=$res2->phone;
	    $customer_email=$res2->email;
	    $customer_country=$res2->country_id;
	    $customer_state=$res2->state_id;
	    $customer_address=$res2->address;
	    $customer_postcode=$res2->postcode;
	    $customer_gst_no=$res2->gstin;
	    $customer_tax_number=$res2->tax_number;
	    $customer_opening_balance=$res2->opening_balance;

	    $sales_date=$res1->sales_date;
	    $reference_no=$res1->reference_no;
	    $sales_code=$res1->sales_code;
	    $sales_note=$res1->sales_note;
	    $grand_total=$res1->grand_total;
	    $paid_amount=$res1->paid_amount;
	    $due_amount =$grand_total - $paid_amount;

	    if(!empty($customer_country)){
	      $customer_country = $this->db->query("select country from db_country where id='$customer_country'")->row()->country;  
	    }
	    if(!empty($customer_state)){
	      $customer_state = $this->db->query("select state from db_states where id='$customer_state'")->row()->state;  
	    }

		?>
		<div class="modal fade" id="view_payments_modal" tabindex='-1'>
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-header header-custom">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center"><?= $this->lang->line('payments'); ?></h4>
		      </div>
		      <div class="modal-body">
		        
		    <div class="row">
		      <div class="col-md-12">
		      	<div class="row invoice-info">
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('customer_details'); ?>
			          <address>
			            <strong><?php echo  $customer_name; ?></strong><br>
			            <?php echo (!empty(trim($customer_mobile))) ? $this->lang->line('mobile').": ".$customer_mobile."<br>" : '';?>
			            <?php echo (!empty(trim($customer_phone))) ? $this->lang->line('phone').": ".$customer_phone."<br>" : '';?>
			            <?php echo (!empty(trim($customer_email))) ? $this->lang->line('email').": ".$customer_email."<br>" : '';?>
			            <?php echo (!empty(trim($customer_gst_no))) ? $this->lang->line('gst_number').": ".$customer_gst_no."<br>" : '';?>
			            <?php echo (!empty(trim($customer_tax_number))) ? $this->lang->line('tax_number').": ".$customer_tax_number."<br>" : '';?>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('sales_details'); ?>
			          <address>
			            <b><?= $this->lang->line('invoice'); ?> #<?php echo  $sales_code; ?></b><br>
			            <b><?= $this->lang->line('date'); ?> :<?php echo show_date($sales_date); ?></b><br>
			            <b><?= $this->lang->line('grand_total'); ?> :<?php echo $grand_total; ?></b><br>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <b><?= $this->lang->line('paid_amount'); ?> :<span><?php echo number_format($paid_amount,decimals(),'.',''); ?></span></b><br>
			          <b><?= $this->lang->line('due_amount'); ?> :<span id='due_amount_temp'><?php echo number_format($due_amount,decimals(),'.',''); ?></span></b><br>
			         
			        </div>
			        <!-- /.col -->
			      </div>
			      <!-- /.row -->
		      </div>
		      <div class="col-md-12">
		       
		     
		              <div class="row">
		         		<div class="col-md-12">
		                  
		                      <table class="table table-bordered">
                                  <thead>
                                  <tr class="bg-primary">
                                    <th>#</th>
                                    <th><?= $this->lang->line('payment_date'); ?></th>
                                    <th><?= $this->lang->line('payment'); ?></th>
                                    <th><?= $this->lang->line('payment_type'); ?></th>
                                    <th><?= $this->lang->line('account'); ?></th>
                                    <th><?= $this->lang->line('payment_note'); ?></th>
                                    <th><?= $this->lang->line('created_by'); ?></th>
                                    <th><?= $this->lang->line('action'); ?></th>
                                  </tr>
                                </thead>
                                <tbody>
                                	<?php
                                	$q1=$this->db->query("select * from db_salespayments where sales_id=$sales_id");
									$i=1;
									$str = '';
									if($q1->num_rows()>0){
										foreach ($q1->result() as $res1) {
											echo "<tr>";
											echo "<td>".$i++."</td>";
											echo "<td>".show_date($res1->payment_date)."</td>";
											echo "<td>".store_number_format($res1->payment)."</td>";
											echo "<td class='text-left'>";
			                                    echo $res1->payment_type;
			                                    if(!empty($res1->cheque_number)){
				                                    echo "<br>Cheque no.:".$res1->cheque_number;
				                                    echo "<br>Period:".$res1->cheque_period;
				                                }
			                                  echo "</td>";
											echo "<td>".get_account_name($res1->account_id)."</td>";
											echo "<td>".$res1->payment_note."</td>";
											echo "<td>".ucfirst($res1->created_by)."</td>";
										
											echo "<td>
											<a onclick='show_receipt(".$res1->id.")' title='Print Receipt' class='pointer btn  btn-default' ><i class='fa fa-print'></i>
											<a onclick='delete_sales_payment(".$res1->id.")' title='Delete Payment ?' class='pointer btn  btn-danger' ><i class='fa fa-trash'></i>
											</</td>";	
											echo "</tr>";
										}
									}
									else{
										echo "<tr><td colspan='7' class='text-danger text-center'>No Records Found</td></tr>";
									}
									?>
                                </tbody>
                            </table>
		               
		               </div>
		            <div class="clearfix"></div>
		        </div>    
		       
		     
		   
		      </div><!-- col-md-9 -->
		      <!-- RIGHT HAND -->
		    </div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
		        
		      </div>
		    </div>
		    <!-- /.modal-content -->
		  </div>
		  <!-- /.modal-dialog -->
		</div>
		<?php
	}

	public function get_customer_trends($customer_id, $store_id = null){
		if(empty($store_id)) $store_id = get_current_store_id();
		$trends = array();

		$sales = $this->db->where('customer_id', $customer_id)
						   ->where('store_id', $store_id)
						   ->where('sales_status', 'Final')
						   ->get('db_sales');

		$trends['invoice_count'] = $sales->num_rows();
		$trends['total_amount'] = 0;
		$trends['paid_amount'] = 0;
		$trends['due_amount'] = 0;
		$paid_count = 0;
		$partial_count = 0;
		$unpaid_count = 0;
		$payment_days = array();

		foreach($sales->result() as $s){
			$trends['total_amount'] += floatval($s->grand_total);
			$trends['paid_amount'] += floatval($s->paid_amount);
			$trends['due_amount'] += (floatval($s->grand_total) - floatval($s->paid_amount));

			if($s->payment_status == 'Paid') $paid_count++;
			else if($s->payment_status == 'Partial') $partial_count++;
			else $unpaid_count++;

			if($s->paid_amount > 0 && !empty($s->due_date)){
				$payment_row = $this->db->where('sales_id', $s->id)
										->order_by('id', 'desc')
										->get('db_salespayments')
										->row();
				$paid_date = !empty($payment_row) ? $payment_row->payment_date : $s->sales_date;
				if(!empty($paid_date)){
					$payment_days[] = date_difference($s->due_date, $paid_date);
				}
			}
		}

		$trends['paid_count'] = $paid_count;
		$trends['partial_count'] = $partial_count;
		$trends['unpaid_count'] = $unpaid_count;
		$trends['avg_payment_days'] = count($payment_days) ? round(array_sum($payment_days) / count($payment_days), 0) : 0;

		$last_sale = $this->db->where('customer_id', $customer_id)
							  ->where('store_id', $store_id)
							  ->where('sales_status', 'Final')
							  ->order_by('id', 'desc')
							  ->get('db_sales')
							  ->row();

		$trends['last_sale_date'] = !empty($last_sale) ? show_date($last_sale->sales_date) : '-';
		$trends['last_sale_amount'] = !empty($last_sale) ? floatval($last_sale->grand_total) : 0;

		$top_items = $this->db->query("
			SELECT i.item_name, SUM(si.sales_qty) as total_qty, SUM(si.total_cost) as total_amount
			FROM db_salesitems si
			JOIN db_items i ON si.item_id = i.id
			JOIN db_sales s ON si.sales_id = s.id
			WHERE s.customer_id = ? AND s.store_id = ? AND s.sales_status = 'Final'
			GROUP BY si.item_id, i.item_name
			ORDER BY total_qty DESC
			LIMIT 3
		", array($customer_id, $store_id))->result();

		$trends['top_items'] = array();
		foreach($top_items as $t){
			$trends['top_items'][] = array(
				'name' => $t->item_name,
				'qty' => $t->total_qty,
				'amount' => floatval($t->total_amount)
			);
		}

		return $trends;
	}
}
