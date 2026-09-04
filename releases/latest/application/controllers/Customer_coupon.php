<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_coupon extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load_global();
		$this->load->model('customer_coupon_model', 'customer_coupon');
	}

	public function generate($customer_id='') {
		$this->permission_check('customerCouponAdd');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('generatecustomerCoupon');
		$data['customer_id'] = $customer_id;
		$data['content'] = $this->load->view('marketing/desktop/coupons/customer_coupon_generate', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}


	public function save() {
		$this->form_validation->set_rules('customer_id', 'Customer', 'trim|required|greater_than[0]');
		$this->form_validation->set_rules('coupon_id', 'Coupon Name', 'trim|required');
		$this->form_validation->set_rules('code', 'Coupon Code', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
			$result = $this->customer_coupon->save_record();
			echo $result;
		} else {
			echo validation_errors();
		}
	}
	/*public function update($id) {
		$this->belong_to('db_coupons', $id);
		$this->permission_check('customerCouponEdit');
		$data = $this->data;

		$this->load->model('customer_coupon_model');
		$result = $this->customer_coupon_model->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('customerCoupon');
		$this->load->view('coupons/create', $data);
	}*/
	/*public function update_customer_coupon() {
		$this->form_validation->set_rules('customer_coupon', 'customer_coupon', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$this->load->model('customer_coupon_model');
			$result = $this->customer_coupon_model->update_customer_coupon();
			echo $result;
		} else {
			echo "Please Enter Coupon name.";
		}
	}*/
	public function index() {
		$this->permission_check('customerCouponView');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('customerCouponsList');
		$data['content'] = $this->load->view('marketing/desktop/coupons/customer_coupons_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function ajax_list() {
		$list = $this->customer_coupon->get_datatables();

		$data = array();
		$no = $_POST['start'];
		foreach ($list as $customer_coupon) {
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value=' . $customer_coupon->id . ' class="checkbox column_checkbox" >';
			$row[] = $customer_coupon->customer_name;
			$row[] = $customer_coupon->name;
			$row[] = $customer_coupon->code;
					$str='';
					if($customer_coupon->expire_date<date("Y-m-d")){ 
			 			$str = "<span class='label label-danger'>Expired</span>";
			 		}

			$row[] = show_date($customer_coupon->expire_date)."<br>".$str;
			$row[] = store_number_format($customer_coupon->value);
			$row[] = $customer_coupon->type;
			$row[] = $customer_coupon->description;
			

			if ($customer_coupon->status == 1) {
				$str = "<span onclick='update_status(" . $customer_coupon->id . ",0)' id='span_" . $customer_coupon->id . "'  class='label label-success' style='cursor:pointer'>Active </span>";} else {
				$str = "<span onclick='update_status(" . $customer_coupon->id . ",1)' id='span_" . $customer_coupon->id . "'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
			}
			$row[] = $str;

			$str2 = '<div class="mp-actions">';
			if($this->permissions('customerCouponView'))
				$str2 .= '<a class="mp-edit" title="Print" target="_blank" href="'.base_url("customer_coupon/print/".$customer_coupon->id).'"><i class="fa fa-print"></i></a>';
			if($this->permissions('customerCouponDelete'))
				$str2 .= '<button class="mp-delete" title="Delete" onclick="delete_coupon(\''.$customer_coupon->id.'\')"><i class="fa fa-trash"></i></button>';
			$str2 .= '</div>';

			$row[] = $str2;
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->customer_coupon->count_all(),
			"recordsFiltered" => $this->customer_coupon->count_filtered(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}

	public function update_status() {
		$this->permission_check_with_msg('customerCouponEdit');
		$id = $this->input->post('id');
		$status = $this->input->post('status');

		$this->customer_coupon->update_status($id, $status);
	}

	public function delete_coupon() {
		$this->permission_check_with_msg('customerCouponDelete');
		$id = $this->input->post('q_id');
		return $this->customer_coupon->delete_coupons($id);
	}
	public function multi_delete() {
		$this->permission_check_with_msg('customerCouponDelete');
		$ids = implode(",", array_map('intval', $_POST['checkbox'] ?? []));
		$this->customer_coupon->delete_coupons($ids);
	}
	function get_coupon_details(){
		$coupon_code = $this->input->post('coupon_code');
		$invoice_type = $this->input->post('invoice_type');
		$coupon_code = strtoupper($coupon_code);
		$customer_id = $this->input->post('customer_id');
		$cart_subtotal = (float)$this->input->post('cart_subtotal');
		//Get coupon data
		$this->db->select("a.expire_date,a.value,a.type,b.name,a.customer_id");
		$this->db->where("UPPER(a.code)", $coupon_code);
		//$this->db->where("a.customer_id",$customer_id);
		$this->db->from("db_customer_coupons a");
		$this->db->join("db_coupons b","b.id=a.coupon_id");
		$q1 = $this->db->get();
		$data =array();
		if($q1->num_rows()>0){
			$row = $q1->row();

			
			//Verify Customer
			if($row->customer_id!=$customer_id){
				$expire_status = "Invalid";
				$message = "This coupon not belongs to this Customer!!";
				$coupon_value =0;//$row->value; 
				$coupon_type =$row->type; 
				$occasion_name =$row->name; 
				$expire_date =$row->expire_date;
			}
			else if(($row->expire_date>=date('Y-m-d') && $invoice_type=='sales' ) || ($invoice_type=='return')){
				$expire_status = "Valid";
				$message = "Valid Coupon,Expired on ".show_date($row->expire_date)."";
				$coupon_value =$row->value; 
				$coupon_type =$row->type; 
				$occasion_name =$row->name; 
				$expire_date =$row->expire_date; 
			}else{
				$expire_status= "Expired";
				$message = "Coupon Expired on ".show_date($row->expire_date)."!";
				$coupon_value =0;
				$coupon_type =$row->type."(".$row->value.")"; 
				$occasion_name =$row->name;
				$expire_date =$row->expire_date; 
			}


			$data = array(
							'expire_date' =>$expire_date,
							'coupon_value' =>$coupon_value,
							'coupon_type' =>$coupon_type,
							'occasion_name' =>$occasion_name,
							'expire_status' => $expire_status,
							'message' => $message,
							);
		}
		else{
			// Fallback: check db_promotions for a matching promotion_code
			$promo_found = false;
			// Reject walk-in customers for promotion codes (require a real customer)
			$walkin_id = get_walk_in_customer_id();
			$is_walkin = (!empty($walkin_id) && (int)$customer_id === (int)$walkin_id);
			try {
				if($this->db->table_exists('db_promotions')){
					// Case-insensitive match on promotion_code
					$this->db->where('store_id', get_current_store_id());
					$this->db->where('status', 1);
					$this->db->where("UPPER(promotion_code)", $coupon_code);
					$this->db->where('start_date <=', date('Y-m-d'));
					$this->db->where('end_date >=', date('Y-m-d'));
					$promo = $this->db->get('db_promotions')->row();
					if($promo){
						if($is_walkin){
							// Promotion codes require a real customer, not walk-in
							$expire_status = "Invalid";
							$message = "Promotion codes require a real customer. Please select a customer.";
							$data = array(
								'expire_date' => '',
								'coupon_value' => 0,
								'coupon_type' => '',
								'occasion_name' => $promo->promotion_name,
								'expire_status' => $expire_status,
								'message' => $message,
							);
						} else {
						// Check advanced rules: min spend, usage limits
						$this->load->model('Promotions_model','promotions_m');
						$eligibility = $this->promotions_m->check_promotion_eligibility($promo, $customer_id, $cart_subtotal);
						if(!$eligibility['ok']){
							$expire_status = "Invalid";
							$message = $eligibility['message'];
							$data = array(
								'expire_date' => '',
								'coupon_value' => 0,
								'coupon_type' => '',
								'occasion_name' => $promo->promotion_name,
								'expire_status' => $expire_status,
								'message' => $message,
							);
						} else {
						$promo_found = true;
						$expire_status = "Valid";
						$message = "Promotion code applied: " . $promo->promotion_name;
						$coupon_value = $promo->discount_value;
						$coupon_type = ($promo->discount_type == 'Percentage') ? 'Percentage' : 'Fixed';
						$occasion_name = $promo->promotion_name;
						$expire_date = $promo->end_date;
						// If applies_to == 'items', fetch the eligible item IDs
						$eligible_item_ids = array();
						if($promo->applies_to == 'items' && $this->db->table_exists('db_promotion_items')){
							$items = $this->db->select('item_id')->where('promotion_id', $promo->id)->get('db_promotion_items')->result();
							foreach($items as $it){ $eligible_item_ids[] = (int)$it->item_id; }
						}
						$data = array(
							'expire_date' => $expire_date,
							'coupon_value' => $coupon_value,
							'coupon_type' => $coupon_type,
							'occasion_name' => $occasion_name,
							'expire_status' => $expire_status,
							'message' => $message,
							'promotion_id' => $promo->id,
							'applies_to' => $promo->applies_to,
							'category_id' => $promo->category_id,
							'brand_id' => $promo->brand_id,
							'eligible_item_ids' => $eligible_item_ids,
						);
						} // end if eligible
						} // end else (not walk-in)
					} else {
						// Debug: check if promotion exists but is inactive/expired
						$this->db->where('store_id', get_current_store_id());
						$this->db->where("UPPER(promotion_code)", $coupon_code);
						$any_promo = $this->db->get('db_promotions')->row();
						if($any_promo){
							if($any_promo->status != 1){
								$expire_status = "Invalid";
								$message = "Promotion '" . $any_promo->promotion_name . "' is inactive.";
							} else if($any_promo->start_date > date('Y-m-d')){
								$expire_status = "Invalid";
								$message = "Promotion '" . $any_promo->promotion_name . "' starts on " . show_date($any_promo->start_date) . ".";
							} else if($any_promo->end_date < date('Y-m-d')){
								$expire_status = "Expired";
								$message = "Promotion '" . $any_promo->promotion_name . "' expired on " . show_date($any_promo->end_date) . ".";
							}
						}
					}
				}
			} catch (Exception $e) { /* Promotions table not available */ }

			if(!$promo_found){
				if(!isset($expire_status)){
					$expire_status= "Invalid";
					$message = "Invalid Coupon Code!!";
				}

				$data = array(
								'expire_date' =>'',
								'coupon_value' =>0,
								'coupon_type' =>'',
								'occasion_name' =>'',
								'expire_status' => $expire_status,
								'message' => $message,
								);
			}
		}
		echo json_encode($data);
	}

	//Print Coupons 
	public function print($coupon_id)
	{
		$this->belong_to('db_customer_coupons',$coupon_id);
		if(!$this->permissions('customerCouponView')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('coupon_id'=>$coupon_id));
		$data['page_title']=$this->lang->line('discountCouponPrint');
		$this->load->view('coupons/print-coupon',$data);
	}

}
