<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_profile_model extends CI_Model {

	public function update_store(){
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
		$sales_invoice_format_id = $this->input->post('sales_invoice_format_id', TRUE);
		$pos_invoice_format_id = $this->input->post('pos_invoice_format_id', TRUE);
		$sales_invoice_footer_text = $this->input->post('sales_invoice_footer_text', TRUE);
		$invoice_terms = $this->input->post('invoice_terms', TRUE);
		$round_off = $this->input->post('round_off', TRUE);
		$language_id = $this->input->post('language_id', TRUE);
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
		$mrp_column = $this->input->post('mrp_column', TRUE);
		$show_signature = $this->input->post('show_signature', TRUE);
		$previous_balance_bit = $this->input->post('previous_balance_bit', TRUE);
		$gst_no = $this->input->post('gst_no', TRUE);
		$vat_no = $this->input->post('vat_no', TRUE);
		$pan_no = $this->input->post('pan_no', TRUE);
		 	//echo "<pre>";print_r($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));exit();

		//if not admin
		if(!is_admin()){
			if($q_id!=get_current_store_id()){
				echo "Something Went Wrong";exit();
			}
		}

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
				echo "success";
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
			$data['q_id']=$query1->row()->id;
			return array_merge($data,$query1->row_array());
			return $data;
		}
	}

}
