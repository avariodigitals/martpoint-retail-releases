<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_model extends CI_Model {

	var $table = 'db_store a';
	var $column_order = array(
								'a.id',
								'a.store_code',
								'a.store_name',
								'a.mobile',
								'a.address',
								'a.created_date',
								'a.created_by',
								'b.package_name',
								'b.expire_date',
								'a.status'); //set column field database for datatable orderable

	var $column_search = array(
								'a.id',
								'a.store_code',
								'a.store_name',
								'a.mobile',
								'a.address',
								'a.created_date',
								'a.created_by',
								'b.package_name',
								'b.expire_date',
								'a.status',
								); //set column field database for datatable searchable 

	var $order = array('a.id' => 'desc'); // default order 

	private function _get_datatables_query()
	{
		$this->db->select($this->column_order);
		$this->db->from($this->table);

		$this->db->join('db_subscription b','b.id=a.current_subscriptionlist_id','left');
		//$this->db->join('db_package c','c.id=a.package_id','left');

		//echo $this->db->get_compiled_select();exit;

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
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
		
		if(isset($_POST['order'])) // here order processing
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
		if($_POST['length'] != -1)
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
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

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
        return $data;
	}

	public function create_url_sms_api($store_id){
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_smsapi");
		if($q1->num_rows()==0){
			$insertArray = [
			   [
			      'store_id' => $store_id,
			      'info' => 'url',
			      'key' => 'weblink',
			      'key_value' => 'http://example.com/sendmessage',
			   ],
			   [
			      'store_id' => $store_id,
			      'info' => 'mobile',
			      'key' => 'mobiles',
			      'key_value' => '',
			   ],
			   [
			      'store_id' => $store_id,
			      'info' => 'message',
			      'key' => 'message',
			      'key_value' => '',
			   ],
			   
			];
			if(!$this->db->insert_batch('db_smsapi', $insertArray)){
				return false;
			}
		}
		return true;
	}

	public function create_url_sms_templates($store_id){
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_smstemplates");
		if($q1->num_rows()==0){
			$insertArray = [
			   [
			      'store_id' => $store_id,
			      'template_name' => 'GREETING TO CUSTOMER ON SALES',
			      'content' => "Hi {{customer_name}},
Your sales Id is {{sales_id}},
Sales Date {{sales_date}},
Total amount  {{sales_amount}},
You have paid  {{paid_amt}},
and due amount is  {{due_amt}}
Thank you Visit Again",
			      'variables' => "{{customer_name}}                          
{{sales_id}}
{{sales_date}}
{{sales_amount}}
{{paid_amt}}
{{due_amt}}
{{store_name}}
{{store_mobile}}
{{store_address}}
{{store_website}}
{{store_email}}
",
				'status'	=> 1,
				'undelete_bit'	=> 1,
			   ],
			   [
			      'store_id' => $store_id,
			      'template_name' => 'GREETING TO CUSTOMER ON SALES RETURN',
			      'content' => "Hi {{customer_name}},
Your sales return Id is {{return_id}},
Return Date {{return_date}},
Total amount  {{return_amount}},
We paid  {{paid_amt}},
and due amount is  {{due_amt}}
Thank you Visit Again",
			      'variables' => "{{customer_name}}                          
{{return_id}}
{{return_date}}
{{return_amount}}
{{paid_amt}}
{{due_amt}}
{{company_name}}
{{company_mobile}}
{{company_address}}
{{company_website}}
{{company_email}}
",
				'status'	=> 1,
				'undelete_bit'	=> 1,
			   ],
			   
			];
			if(!$this->db->insert_batch('db_smstemplates', $insertArray)){
				return false;
			}
		}
		return true;
	}

	public function save_registration(){
		$store_code = $this->input->post('store_code', TRUE);
		$store_name = $this->input->post('store_name', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$email = $this->input->post('email', TRUE);
		$country = $this->input->post('country', TRUE);
		$state = $this->input->post('state', TRUE);
		$city = $this->input->post('city', TRUE);
		$category_init = $this->input->post('category_init', TRUE);
		$item_init = $this->input->post('item_init', TRUE);
		$supplier_init = $this->input->post('supplier_init', TRUE);
		$purchase_init = $this->input->post('purchase_init', TRUE);
		$purchase_return_init = $this->input->post('purchase_return_init', TRUE);
		$customer_init = $this->input->post('customer_init', TRUE);
		$sales_init = $this->input->post('sales_init', TRUE);
		$sales_return_init = $this->input->post('sales_return_init', TRUE);
		$expense_init = $this->input->post('expense_init', TRUE);
		$quotation_init = $this->input->post('quotation_init', TRUE);
		$money_transfer_init = $this->input->post('money_transfer_init', TRUE);
		$accounts_init = $this->input->post('accounts_init', TRUE);
		$currency = $this->input->post('currency', TRUE);
		$currency_placement = $this->input->post('currency_placement', TRUE);
		$timezone = $this->input->post('timezone', TRUE);
		$date_format = $this->input->post('date_format', TRUE);
		$time_format = $this->input->post('time_format', TRUE);
		$sales_discount = $this->input->post('sales_discount', TRUE);
		$change_return = $this->input->post('change_return', TRUE);
		$mrp_column = $this->input->post('mrp_column', TRUE);
		$show_signature = $this->input->post('show_signature', TRUE);
		$previous_balance_bit = $this->input->post('previous_balance_bit', TRUE);
		$sales_invoice_format_id = $this->input->post('sales_invoice_format_id', TRUE);
		$pos_invoice_format_id = $this->input->post('pos_invoice_format_id', TRUE);
		$sales_invoice_footer_text = $this->input->post('sales_invoice_footer_text', TRUE);
		$invoice_terms = $this->input->post('invoice_terms', TRUE);
		$round_off = $this->input->post('round_off', TRUE);
		$decimals = $this->input->post('decimals', TRUE);
		$qty_decimals = $this->input->post('qty_decimals', TRUE);
		$sales_payment_init = $this->input->post('sales_payment_init', TRUE);
		$sales_return_payment_init = $this->input->post('sales_return_payment_init', TRUE);
		$purchase_payment_init = $this->input->post('purchase_payment_init', TRUE);
		$purchase_return_payment_init = $this->input->post('purchase_return_payment_init', TRUE);
		$expense_payment_init = $this->input->post('expense_payment_init', TRUE);
		$cust_advance_init = $this->input->post('cust_advance_init', TRUE);
		$language_id = $this->input->post('language_id', TRUE);
		$first_name = $this->input->post('first_name', TRUE);
		$last_name = $this->input->post('last_name', TRUE);
		$password = $this->input->post('password', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];
		$country = $this->db->select("country")->where("id",$country)->get("db_country")->row()->country;
		$state = $this->db->select("state")->where("id",$state)->get("db_states")->row()->state;

		$this->db->query("ALTER TABLE db_store AUTO_INCREMENT = 1");
		$this->db->trans_begin();
		$data = array(
		    				'store_code'				=> $store_code,
		    				'store_name'				=> $store_name,
		    				'store_website'				=> '',
		    				'mobile'					=> $mobile,
		    				'phone'						=> '',
		    				'email'						=> $email,
		    				'country'					=> $country,
		    				'state'						=> $state,
		    				'city'						=> $city,
		    				'address'					=> ' ',
		    				'postcode'					=> '',
		    				'bank_details'				=> '',
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
		    				'date_format'				=> $date_format,
		    				'time_format'				=> $time_format,
		    				'sales_discount'			=> $sales_discount,
		    				'change_return'				=> $change_return,
		    				'mrp_column'				=> $mrp_column,
		    				'show_signature'				=> $show_signature,
		    				'previous_balance_bit'		=> $previous_balance_bit,
		    				'sales_invoice_format_id'	=> $sales_invoice_format_id,
		    				'pos_invoice_format_id'		=> $pos_invoice_format_id,
		    				'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
		    				'invoice_terms'				=> $invoice_terms,
		    				'round_off'					=> $round_off,
		    				'decimals'					=> $decimals,
		    				'qty_decimals'					=> $qty_decimals,
		    				'sales_payment_init'		=> $sales_payment_init,
		    				'sales_return_payment_init'	=> $sales_return_payment_init,
		    				'purchase_payment_init'		=> $purchase_payment_init,
		    				'purchase_return_payment_init'	=> $purchase_return_payment_init,
		    				'expense_payment_init'	=> $expense_payment_init,
		    				'cust_advance_init'	=> $cust_advance_init,
		    			);


		
			$this->db->select("count(*) as store_code_count");
			$this->db->where("upper(store_code)", strtoupper($store_code));
			$store_code_count = $this->db->get('db_store')->row()->store_code_count;
			if($store_code_count>0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}
			$extra_info = array(
							'invoice_view'				=> 1,
		    				'sms_status'				=> 0,
		    				'language_id'				=> $language_id,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $first_name,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);

			$data=array_merge($data,$extra_info);
			
			$q1 = $this->db->insert('db_store', $data);
			if(!$q1){
				echo "failed";exit();
			}

			$store_id = $this->db->insert_id();
			$this->load->model('customers_model');

			$q2=$this->customers_model->create_walk_in_customer($store_id);

			$this->load->model('payment_modes_model');
			$this->payment_modes_model->seed_defaults($store_id);

			$q3 = $this->create_default_warehouse($store_id,null,null);
			if(!$q3){
				echo "failed";exit();
			}

			//Create User
			if(!empty($email)){
			$query = $this->db->where('email', $email)->get('db_users')->num_rows();
			if($query>0){ return "This Email ID already exist.";}
			}
			$info = array(
			    				'username' 				=> $first_name, 
			    				'last_name' 			=> $last_name, 
			    				'password' 				=> md5($password), 
			    				'mobile' 				=> $mobile,
			    				'email' 				=> $email,
			    				/*System Info*/
			    				'created_date' 			=> $CUR_DATE,
			    				'created_time' 			=> $CUR_TIME,
			    				'created_by' 			=> $CUR_USERNAME,
			    				'system_ip' 			=> $SYSTEM_IP,
			    				'system_name' 			=> $SYSTEM_NAME,
			    				'status' 				=> 1,
			    			);
			if(!empty($profile_picture)){
				$info['profile_picture'] = $profile_picture;
			}
			
			$info['role_id'] = store_admin_id();
			
			$info['store_id']=(store_module()) ? $store_id : $this->session->userdata('store_id');	
			$q1 = $this->db->insert('db_users', $info);
			if (!$q1){
				return "failed";
			}
			$user_id = $this->db->insert_id();

			//Update default warehouse id

			$warehouse_id = $this->db->select("id")->where("store_id",$store_id)->get("db_warehouse")->row()->id;

			$this->db->set("default_warehouse_id", $warehouse_id)->where("id",$user_id)->update("db_users");

			//UPDATE THE USER ID INTO STORE
			$this->db->set("user_id",$user_id)
						->where("id",$store_id)
						->update("db_store");
						
			if(warehouse_module() && isset($_POST['warehouses']) && $role_id!=1 && $role_id!=store_admin_id()){
				$warehouses_list = sizeof($_POST['warehouses']);
				foreach ($_POST['warehouses'] as $res => $val) {
					$warehouse_info = array ( 'user_id'=> $user_id, 'warehouse_id'=>$val );
					$q2 = $this->db->insert("db_userswarehouses",$warehouse_info);
					if (!$q2){
						return "failed";
					}
				}
			}

			if(!$this->create_url_sms_api($store_id)){
				return "failed";
			}
			if(!$this->create_url_sms_templates($store_id)){
				return "failed";
			}
			
			$this->db->trans_commit();
			$this->session->set_flashdata('success', 'Account created Succssfully!! Please Login!');
			return "success";
			

		
	}
	public function verify_and_save(){
		$command = $this->input->post('command', TRUE);
		$q_id = $this->input->post('q_id', TRUE);
		$store_code = $this->input->post('store_code', TRUE);
		$store_name = $this->input->post('store_name', TRUE);
		$store_website = $this->input->post('store_website', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$phone = $this->input->post('phone', TRUE);
		$email = $this->input->post('email', TRUE);
		$country = $this->input->post('country', TRUE);
		$state = $this->input->post('state', TRUE);
		$city = $this->input->post('city', TRUE);
		$address = $this->input->post('address', TRUE);
		$postcode = $this->input->post('postcode', TRUE);
		$bank_details = $this->input->post('bank_details', TRUE);
		$category_init = $this->input->post('category_init', TRUE);
		$item_init = $this->input->post('item_init', TRUE);
		$supplier_init = $this->input->post('supplier_init', TRUE);
		$purchase_init = $this->input->post('purchase_init', TRUE);
		$purchase_return_init = $this->input->post('purchase_return_init', TRUE);
		$customer_init = $this->input->post('customer_init', TRUE);
		$sales_init = $this->input->post('sales_init', TRUE);
		$sales_return_init = $this->input->post('sales_return_init', TRUE);
		$expense_init = $this->input->post('expense_init', TRUE);
		$quotation_init = $this->input->post('quotation_init', TRUE);
		$money_transfer_init = $this->input->post('money_transfer_init', TRUE);
		$accounts_init = $this->input->post('accounts_init', TRUE);
		$currency = $this->input->post('currency', TRUE);
		$currency_placement = $this->input->post('currency_placement', TRUE);
		$timezone = $this->input->post('timezone', TRUE);
		$date_format = $this->input->post('date_format', TRUE);
		$time_format = $this->input->post('time_format', TRUE);
		$sales_discount = $this->input->post('sales_discount', TRUE);
		$change_return = $this->input->post('change_return', TRUE);
		$mrp_column = $this->input->post('mrp_column', TRUE);
		$show_signature = $this->input->post('show_signature', TRUE);
		$previous_balance_bit = $this->input->post('previous_balance_bit', TRUE);
		$sales_invoice_format_id = $this->input->post('sales_invoice_format_id', TRUE);
		$pos_invoice_format_id = $this->input->post('pos_invoice_format_id', TRUE);
		$sales_invoice_footer_text = $this->input->post('sales_invoice_footer_text', TRUE);
		$invoice_terms = $this->input->post('invoice_terms', TRUE);
		$round_off = $this->input->post('round_off', TRUE);
		$decimals = $this->input->post('decimals', TRUE);
		$qty_decimals = $this->input->post('qty_decimals', TRUE);
		$sales_payment_init = $this->input->post('sales_payment_init', TRUE);
		$sales_return_payment_init = $this->input->post('sales_return_payment_init', TRUE);
		$purchase_payment_init = $this->input->post('purchase_payment_init', TRUE);
		$purchase_return_payment_init = $this->input->post('purchase_return_payment_init', TRUE);
		$expense_payment_init = $this->input->post('expense_payment_init', TRUE);
		$cust_advance_init = $this->input->post('cust_advance_init', TRUE);
		$t_and_c_status = $this->input->post('t_and_c_status', TRUE);
		$t_and_c_status_pos = $this->input->post('t_and_c_status_pos', TRUE);
		$number_to_words = $this->input->post('number_to_words', TRUE);
		$default_account_id = $this->input->post('default_account_id', TRUE);
		$gst_no = $this->input->post('gst_no', TRUE);
		$vat_no = $this->input->post('vat_no', TRUE);
		$pan_no = $this->input->post('pan_no', TRUE);
		$language_id = $this->input->post('language_id', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];
		
		$this->db->trans_begin();

		
		$store_logo='';
		if(!empty($_FILES['store_logo']['name'])){
			$config['upload_path']          = './uploads/store/';
	        $config['allowed_types']        = 'gif|jpg|jpeg|png';
	        $config['max_size']             = 1000;
	        $config['max_width']            = 1000;
	        $config['max_height']           = 1000;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('store_logo'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                return $error['error'];
	                exit();
	        }
	        else
	        {
	        	   $store_logo='uploads/store/'.$this->upload->data('file_name');
	        }
		}

		$signature='';
		if(!empty($_FILES['signature']['name'])){
			$config['upload_path']          = './uploads/signature/';
	        $config['allowed_types']        = 'gif|jpg|jpeg|png';
	        $config['max_size']             = 1000;
	        $config['max_width']            = 1000;
	        $config['max_height']           = 1000;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('signature'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                return $error['error'];
	                exit();
	        }
	        else
	        {
	        	   $signature='uploads/signature/'.$this->upload->data('file_name');
	        }
		}

		$change_return = (isset($change_return)) ? 1 : 0;
		$mrp_column = (isset($mrp_column)) ? 1 : 0;
		$show_signature = (isset($show_signature)) ? 1 : 0;
		$previous_balance_bit = (isset($previous_balance_bit)) ? 1 : 0;
		$round_off = (isset($round_off)) ? 1 : 0;


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
		    				'date_format'				=> $date_format,
		    				'time_format'				=> $time_format,
		    				'sales_discount'			=> $sales_discount,
		    				'sales_discount'			=> $sales_discount,
		    				'change_return'				=> $change_return,
		    				'mrp_column'				=> $mrp_column,
		    				'show_signature'				=> $show_signature,
		    				'previous_balance_bit'				=> $previous_balance_bit,
		    				'sales_invoice_format_id'	=> $sales_invoice_format_id,
		    				'pos_invoice_format_id'		=> $pos_invoice_format_id,
		    				'sales_invoice_footer_text'	=> $sales_invoice_footer_text,
		    				'invoice_terms'				=> $invoice_terms,
		    				'round_off'					=> $round_off,
		    				'decimals'					=> $decimals,
		    				'qty_decimals'					=> $qty_decimals,
		    				'sales_payment_init'		=> $sales_payment_init,
		    				'sales_return_payment_init'	=> $sales_return_payment_init,
		    				'purchase_payment_init'		=> $purchase_payment_init,
		    				'purchase_return_payment_init'	=> $purchase_return_payment_init,
		    				'expense_payment_init'	=> $expense_payment_init,
		    				'cust_advance_init'	=> $cust_advance_init,
		    				't_and_c_status'	=> $t_and_c_status,
		    				't_and_c_status_pos'	=> $t_and_c_status_pos,
		    				'number_to_words'	=> $number_to_words,
		    				'default_account_id'	=> (!empty($default_account_id))?$default_account_id:null,
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

		


		if($command=='save'){
			$this->db->select("count(*) as store_code_count");
			$this->db->where("upper(store_code)", strtoupper($store_code));
			$store_code_count = $this->db->get('db_store')->row()->store_code_count;
			if($store_code_count>0){
				echo "Sorry! Store Code Already Exist!\nPlease Change Store Code";exit();
			}
			$extra_info = array(
							'invoice_view'				=> 1,
		    				'sms_status'				=> 0,
		    				'language_id'				=> $language_id,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);
			$data=array_merge($data,$extra_info);
			$q1 = $this->db->insert('db_store', $data);
			$store_id = $this->db->insert_id();
			$this->load->model('customers_model');
			$q2=$this->customers_model->create_walk_in_customer($store_id);

			$this->load->model('payment_modes_model');
			$this->payment_modes_model->seed_defaults($store_id);

			if(!$this->create_url_sms_api($store_id)){
				return "failed";
			}
			if(!$this->create_url_sms_templates($store_id)){
				return "failed";
			}
			$q3 = $this->create_default_warehouse($store_id,null,null);
			if(!$q3){
				echo "failed";exit();
			}
			if($q1){
				$this->db->trans_commit();
				$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! ');
				echo "success";
			}

		}
		
		exit();
	}

	//Get store_details
	public function get_details($id){
		$data=$this->data;

		$query1=$this->db->query("select * from db_store where upper(id)=upper('$id')");
		if($query1->num_rows()==0){
			show_404();exit;
		}
		else{
			/* QUERY 1*/
			$query=$this->db->query("select * from db_sitesettings order by id asc limit 1");
			$query=$query->row();
			$data['q_id']=$query1->row()->id;
			return array_merge($data,$query1->row_array());
			return $data;
		}
	}


	public function update_status($id,$status){
       if (set_status_of_table($id,$status,'db_store')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function delete_store_from_table($ids){
			$this->db->trans_begin();
			$query1=$this->db->where_in('id',$ids)->where('id!=1')->delete('db_store');
	        if ($query1){
	        	$this->db->trans_commit();
	            echo "success";
	        }
	        else{
	            echo "failed";
	        }	
	}
	public function create_default_warehouse($store_id,$mobile='',$email=''){
		$created_date = date("Y-m-d");
		$store = get_store_details($store_id);
		$warehouse_name = ($store && !empty($store->store_name)) ? $store->store_name . ' Warehouse' : 'Main Warehouse';
		$query1="insert into db_warehouse(store_id,warehouse_type,warehouse_name,mobile,email,status,created_date) 
									values($store_id,'System','".$this->db->escape_str($warehouse_name)."','$mobile','$email',1,'$created_date')";
		
		if ($this->db->simple_query($query1)){
				$this->session->set_flashdata('success', 'Success!! New Warehouse Created Succssfully!!');
		        return "success";
		}
		else{
		        return "failed";
		}
	}

}
