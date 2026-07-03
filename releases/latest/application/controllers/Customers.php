<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('customers_model','customers');
	}

	public function index()
	{
		$this->permission_check('customers_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('customers_list');
		$this->load->view('customers-view',$data);
	}
	public function add()
	{
		$this->permission_check('customers_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('customers');
		$this->load->view('customers',$data);
	}

	public function newcustomers(){
		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
			$result=$this->customers->verify_and_save();
			if($result === 'success'){
				$customer_id = $this->db->insert_id();
				$nin_bvn = $this->input->post('nin_bvn', TRUE);
				$nin_verified = $this->input->post('nin_verified', TRUE);
				$this->db->where('id', $customer_id)->update('db_customers', array(
					'nin_bvn' => $nin_bvn,
					'nin_verified' => (!empty($nin_verified)) ? 1 : 0
				));
			}
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	public function update($id){
		$this->belong_to('db_customers',$id);
		$this->permission_check('customers_edit');
		$data=$this->data;
		$result=$this->customers->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('customers');
		$this->load->view('customers', $data);
	}
	public function update_customers(){
		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {			
			$result=$this->customers->update_customers();
			if($result === 'success'){
				$q_id = $this->input->post('q_id', TRUE);
				$nin_bvn = $this->input->post('nin_bvn', TRUE);
				$nin_verified = $this->input->post('nin_verified', TRUE);
				$this->db->where('id', $q_id)->update('db_customers', array(
					'nin_bvn' => $nin_bvn,
					'nin_verified' => (!empty($nin_verified)) ? 1 : 0
				));
			}
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}


	public function ajax_list()
	{
		$list = $this->customers->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $customers) {

			$opening_balance =(!empty($customers->opening_balance)) ? $customers->opening_balance : 0;
			$opening_balance -=get_paid_cob($customers->id);
			$sales_due =(!empty($customers->sales_due)) ? $customers->sales_due : 0;
			$sales_return_due =(!empty($customers->sales_return_due)) ? $customers->sales_return_due : 0;
			$total = ($opening_balance)+$sales_due-$sales_return_due;
			
			$no++;
			$row = array();
			$disable = ($customers->delete_bit==1) ? 'disabled' : '';
			
			$row[] = ($customers->delete_bit==1) ? '<span data-toggle="tooltip" title="Resticted" class="text-danger fa fa-fw fa-ban"></span>' : '<input type="checkbox" name="checkbox[]" '.$disable.' value='.$customers->id.' class="checkbox column_checkbox" >';
			
			
			$row[] = $customers->customer_code;
			$row[] = $customers->customer_name;
			$row[] = $customers->mobile;
			$row[] = $customers->email;
			$store_name = get_store_name($customers->store_id);
			$location_display = (!empty($store_name)) ? '<span class="label label-default"><i class="fa fa-building"></i> '.$store_name.'</span>' : '-';
			if(!empty($customers->location_link)){
				$location_display .= ' <a target="_blank" title="View on Map" href="'.$customers->location_link.'"><i class="fa fa-fw fa-map-marker text-red"></i></a>';
			}
			$row[] = $location_display;
			$row[] = ($customers->credit_limit==-1) ? "<span class='badge'>No Limit</span>" :store_number_format($customers->credit_limit);
			$row[] = store_number_format($opening_balance+$sales_due);
			
			$row[] = store_number_format($sales_return_due);
			$row[] = store_number_format($customers->tot_advance);
			$row[] = number_format($customers->loyalty_points ?? 0, 0);
			$row[] = '<span class="label label-info">' . (($customers->loyalty_tier ?? '') ?: 'Bronze') . '</span>';
			$row[] = store_number_format($customers->store_credit_balance ?? 0);
			$row[] = store_number_format($customers->gift_card_balance ?? 0);


			 		if($customers->status==1){ 
			 			$str= "<span onclick='update_status(".$customers->id.",0)' id='span_".$customers->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$customers->id.",1)' id='span_".$customers->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;			
					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											if(is_store_admin())
											$str2.='<li>
												<a title="Discount Coupon" href="'.base_url().'customer_coupon/generate/'.$customers->id.'">
													<i class="fa fa-fw fa-tags text-blue"></i>Generate Discount Coupon
												</a>
											</li>';

											if($this->permissions('customers_view')&& $customers->delete_bit!=1)
											$str2.='<li>
												<a title="View Profile" href="'.base_url().'customers/profile/'.$customers->id.'">
													<i class="fa fa-fw fa-user-circle text-blue"></i>Profile
												</a>
											</li>';

											if($this->permissions('customers_edit')&& $customers->delete_bit!=1)
											$str2.='<li>
												<a title="Edit Record ?" href="'.base_url().'customers/update/'.$customers->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('cust_adv_payments_view'))
											$str2.='<li>
												<a title="Advance Payments View" href="'.base_url().'customers_advance">
													<i class="fa fa-fw fa-edit text-blue"></i>Advance Payments
												</a>
											</li>';

											if($this->permissions('sales_payment_view'))
											$str2.='<li>
												<a title="Pay" class="pointer" onclick="view_payments('.$customers->id.')" >
													<i class="fa fa-fw fa-money text-blue"></i>View Payments
												</a>
											</li>';

											if($this->permissions('sales_payment_add'))
											$str2.='<li>
												<a title="Receive Previous Balance & Sales Due Payments" class="pointer" onclick="pay_now('.$customers->id.')" >
													<i class="fa fa-fw fa-money text-blue"></i>Receive Due Payments
												</a>
											</li>';
											if($this->permissions('sales_return_payment_add'))
											$str2.='<li>
												<a title="Pay Return Due" class="pointer" onclick="pay_return_due('.$customers->id.')" >
													<i class="fa fa-fw fa-money text-blue"></i>Pay Return Due
												</a>
											</li>';
											if($this->permissions('customers_delete') && $customers->delete_bit!=1)
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_customers('.$customers->id.')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';			
			$row[] =  $str2;
			

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'] ?? 1,
						"recordsTotal" => $this->customers->count_all(),
						"recordsFiltered" => $this->customers->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function update_status(){
		$this->permission_check_with_msg('customers_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		$result=$this->customers->update_status($id,$status);
		return $result;
	}
	
	public function delete_customers(){
		$this->permission_check_with_msg('customers_delete');
		$id=$this->input->post('q_id');
		return $this->customers->delete_customers_from_table($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('customers_delete');
		$ids=implode (",",$_POST['checkbox']);
		return $this->customers->delete_customers_from_table($ids);
	}
	public function show_pay_now_modal(){
		$this->permission_check_with_msg('sales_payment_add');
		$customer_id=$this->input->post('customer_id');
		echo $this->customers->show_pay_now_modal($customer_id);
	}
	public function save_payment(){
		$this->permission_check_with_msg('sales_payment_add');
		echo $this->customers->save_payment();
	}
	public function show_pay_return_due_modal(){
		$this->permission_check_with_msg('sales_return_payment_add');
		$customer_id=$this->input->post('customer_id');
		echo $this->customers->show_pay_return_due_modal($customer_id);
	}
	public function save_return_due_payment(){
		$this->permission_check_with_msg('sales_payment_add');
		echo $this->customers->save_return_due_payment();
	}
	public function delete_opening_balance_entry(){
		$this->permission_check_with_msg('sales_payment_delete');
		$entry_id = $this->input->post('entry_id');
		echo $this->customers->delete_opening_balance_entry($entry_id);
	}
	/*27-06-2020*/
	public function view_payment_list_modal(){
		$this->permission_check_with_msg('sales_payment_add');
		$customer_id=$this->input->post('customer_id');
		echo $this->customers->view_payment_list_modal($customer_id);
	}
	/*28-06-2020*/
	//Print Customer Bulk Payment Receipt
	public function print_show_receipt($payment_id){
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('payment_receipt');
		$data=array_merge($data,array('payment_id'=>$payment_id));
		$this->load->view('print-cust-payment-receipt',$data);
	}

	public function restore_customer_list(){
		echo get_customers_select_list($this->input->post('customer_id'),get_current_store_id());
	}

	public function profile($id){
		$this->belong_to('db_customers', $id);
		$this->permission_check('customers_view');
		$data = $this->data;
		$store_id = get_current_store_id();

		// Customer details
		$customer = $this->db->where('id', $id)->get('db_customers')->row();
		if(!$customer) redirect('customers');
		$data['customer'] = $customer;

		// Calculate total due
		$opening_balance = $customer->opening_balance ?? 0;
		$opening_balance -= get_paid_cob($id);
		$sales_due = $customer->sales_due ?? 0;
		$sales_return_due = $customer->sales_return_due ?? 0;
		$data['total_due'] = $opening_balance + $sales_due - $sales_return_due;

		// Purchase history
		$data['purchases'] = $this->db->where('customer_id', $id)
								  ->where('store_id', $store_id)
								  ->order_by('id', 'desc')
								  ->get('db_sales')
								  ->result();

		// Payments / statements
		$data['payments'] = $this->db->where('customer_id', $id)
								 ->order_by('id', 'desc')
								 ->get('db_customer_payments')
								 ->result();

		// Gift cards
		if($this->db->table_exists('db_gift_cards')){
			$data['gift_cards'] = $this->db->where('customer_id', $id)
									  ->where('store_id', $store_id)
									  ->order_by('id', 'desc')
									  ->get('db_gift_cards')
									  ->result();
		} else { $data['gift_cards'] = array(); }

		// Store credit
		if($this->db->table_exists('db_store_credit')){
			$data['store_credits'] = $this->db->where('customer_id', $id)
									   ->where('store_id', $store_id)
									   ->order_by('id', 'desc')
									   ->get('db_store_credit')
									   ->result();
		} else { $data['store_credits'] = array(); }

		// Coupons
		if($this->db->table_exists('db_customer_coupons')){
			$data['coupons'] = $this->db->where('customer_id', $id)
									->where('store_id', $store_id)
									->order_by('id', 'desc')
									->get('db_customer_coupons')
									->result();
		} else { $data['coupons'] = array(); }

		// Memberships
		if($this->db->table_exists('db_customer_memberships')){
			$this->load->model('membership_model', 'membership');
			$data['memberships'] = $this->membership->get_customer_memberships($id);
			$data['active_membership'] = $this->membership->get_customer_discount($id);
		} else {
			$data['memberships'] = array();
			$data['active_membership'] = null;
		}

		// Treatment Notes
		if($this->db->table_exists('db_treatment_notes')){
			$this->load->model('treatment_notes_model', 'notes');
			$data['treatment_notes'] = $this->notes->get_by_customer($id);
		} else {
			$data['treatment_notes'] = array();
		}

		// Custom Orders
		if($this->db->table_exists('db_custom_orders')){
			$this->load->model('custom_orders_model', 'custom_orders');
			$data['custom_orders'] = $this->custom_orders->get_by_customer($id);
		} else {
			$data['custom_orders'] = array();
		}

		// Service / Laundry History
		if ($this->db->table_exists('db_laundry_orders')) {
			$data['service_history'] = $this->db->query(
				"SELECT lo.id, lo.sales_id, lo.status, lo.tag_number, lo.created_at, lo.updated_at,
						s.sales_code, s.sales_date, s.grand_total, s.paid_amount,
						(SELECT GROUP_CONCAT(DISTINCT i.item_name SEPARATOR ', ')
						 FROM db_salesitems si
						 JOIN db_items i ON i.id = si.item_id
						 WHERE si.sales_id = lo.sales_id) as items_list
				 FROM db_laundry_orders lo
				 JOIN db_sales s ON s.id = lo.sales_id
				 WHERE s.customer_id = ? AND lo.store_id = ?
				 ORDER BY lo.created_at DESC",
				[$id, $store_id]
			)->result();
		} else {
			$data['service_history'] = array();
		}

		$data['page_title'] = 'Customer Profile';
		$this->load->view('customers/profile', $data);
	}

	public function get_customer_by_barcode(){
		$barcode = $this->input->post('barcode', TRUE);
		if(empty($barcode)) { echo json_encode(array()); return; }

		$store_id = get_current_store_id();
		$customer = null;

		// Try C{numeric_id} format (e.g. C123)
		if(preg_match('/^C(\d+)$/', $barcode, $m)){
			$customer = $this->db->where('id', (int)$m[1])->where('store_id', $store_id)->get('db_customers')->row_array();
		}

		// Fallback: search by customer_code, mobile, or id directly
		if(!$customer){
			$this->db->where('store_id', $store_id);
			$this->db->group_start();
			$this->db->where('customer_code', $barcode);
			$this->db->or_where('mobile', $barcode);
			$this->db->or_where('id', (int)$barcode);
			$this->db->group_end();
			$customer = $this->db->get('db_customers')->row_array();
		}

		if(!$customer) { echo json_encode(array()); return; }

		$customer_id = $customer['id'];
		$customer['previous_due'] = store_number_format(($customer['sales_due'] + $customer['opening_balance']) - get_paid_cob($customer_id), false);
		$customer['tot_advance'] = store_number_format($customer['tot_advance'], 0);

		// Get available benefits
		$benefits = array();

		// Gift cards
		if($this->db->table_exists('db_gift_cards')){
			$gcs = $this->db->where('customer_id', $customer_id)
							->where('store_id', $store_id)
							->where('status', 'active')
							->get('db_gift_cards')
							->result_array();
			foreach($gcs as $g){
				$g['type'] = 'gift_card';
				$g['label'] = 'Gift Card: '.$g['card_number'];
				$g['value'] = $g['current_balance'];
				$g['eligible'] = true;
				$benefits[] = $g;
			}
		}

		// Store credit
		if($this->db->table_exists('db_store_credit')){
			$scs = $this->db->where('customer_id', $customer_id)
							->where('store_id', $store_id)
							->where('status', 'active')
							->get('db_store_credit')
							->result_array();
			foreach($scs as $s){
				$s['type'] = 'store_credit';
				$s['label'] = 'Store Credit: '.$s['credit_code'];
				$s['value'] = $s['balance'];
				$s['eligible'] = true;
				$benefits[] = $s;
			}
		}

		// Coupons
		if($this->db->table_exists('db_customer_coupons')){
			$cps = $this->db->where('customer_id', $customer_id)
							->where('store_id', $store_id)
							->where('status', 1)
							->get('db_customer_coupons')
							->result_array();
			foreach($cps as $c){
				$c['type'] = 'coupon';
				$c['label'] = 'Coupon: '.$c['code'];
				$c['value'] = $c['value'];
				$expired = !empty($c['expire_date']) && strtotime($c['expire_date']) < strtotime(date('Y-m-d'));
				$c['eligible'] = !$expired;
				$c['status_text'] = $expired ? 'Expired' : 'Active';
				$benefits[] = $c;
			}
		}

		$customer['benefits'] = $benefits;
		echo json_encode($customer);
	}

	public function getCustomers($id=''){
		echo $this->customers->getCustomersJson($id);
	}

	/* Returns all customers for offline sync (IndexedDB caching) */
	public function sync_customers_for_offline(){
		$store_id = $this->input->get('store_id');
		$this->db->select("id, customer_name, mobile, sales_due, opening_balance, tot_advance, delete_bit")
				->from('db_customers')
				->where('store_id', $store_id)
				->where('status', 1)
				->limit(5000);
		$query = $this->db->get();
		$display_json = array();
		foreach($query->result() as $res){
			$customer_previous_due = $res->sales_due + $res->opening_balance;
			$customer_previous_due -= get_paid_cob($res->id);
			$json_arr = array();
			$json_arr["id"] = $res->id;
			$json_arr["text"] = $res->customer_name;
			$json_arr["mobile"] = $res->mobile;
			$json_arr["previous_due"] = store_number_format($customer_previous_due, false);
			$json_arr["tot_advance"] = store_number_format($res->tot_advance, 0);
			$json_arr["delete_bit"] = $res->delete_bit;
			array_push($display_json, $json_arr);
		}
		echo json_encode($display_json);exit;
	}

	public function get_customer_details(){
		$customer_id = $this->input->post('customer_id');
		if(empty($customer_id)){
			echo json_encode(array());
			return;
		}
		$row = $this->db->select('id, customer_name, mobile, email, phone, address, nin_bvn, nin_verified, nin_verified_at, nin_waived')
						->where('id', $customer_id)
						->get('db_customers')
						->row_array();
		echo json_encode($row ?: array());
	}
}
