<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services_model extends CI_Model {

	public function verify_and_save(){
		$item_code = $this->input->post('item_code', TRUE);
		$item_name = $this->input->post('item_name', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$price = $this->input->post('price', TRUE);
		$tax_id = $this->input->post('tax_id', TRUE);
		$purchase_price = $this->input->post('purchase_price', TRUE);
		$tax_type = $this->input->post('tax_type', TRUE);
		$sales_price = $this->input->post('sales_price', TRUE);
		$deposit_required = $this->input->post('deposit_required', TRUE);
		$deposit_percent = $this->input->post('deposit_percent', TRUE);
		$laundry_service_type = $this->input->post('laundry_service_type', TRUE);
		$commission_type = $this->input->post('commission_type', TRUE) ?? 'none';
		$commission_value = $this->input->post('commission_value', TRUE) ?? 0;
		$seller_points = $this->input->post('seller_points', TRUE);
		$custom_barcode = $this->input->post('custom_barcode', TRUE);
		$description = $this->input->post('description', TRUE);
		$hsn = $this->input->post('hsn', TRUE);
		$sac = $this->input->post('sac', TRUE);
		$discount_type = $this->input->post('discount_type', TRUE);
		$discount = $this->input->post('discount', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'] ?? date("Y-m-d");
		$CUR_TIME = $this->data['CUR_TIME'] ?? date("h:i:s a");
		$CUR_USERNAME = $this->data['CUR_USERNAME'] ?? 'System';
		$SYSTEM_IP = $this->data['SYSTEM_IP'] ?? $_SERVER['REMOTE_ADDR'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'] ?? 'localhost';

		$this->db->trans_begin();
		$this->db->trans_strict(TRUE);

		$file_name='';
		if(!empty($_FILES['item_image']['name'])){
			$new_name = time();
			$config['file_name'] = $new_name;
			$config['upload_path']          = './uploads/items/';
	        $config['allowed_types']        = 'jpg|png|jpeg';
	        $config['max_size']             = 1024;
	        $config['max_width']            = 1500;
	        $config['max_height']           = 1500;
	       
	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('item_image'))
	        {	
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {		
	        	$file_name=$this->upload->data('file_name');
	        	$config['image_library'] = 'gd2';
				$config['source_image'] = 'uploads/items/'.$file_name;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 75;
				$config['height']       = 50;
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
	        }
		}

		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();
		$this->db->query("ALTER TABLE db_items AUTO_INCREMENT = 1");

		$info = array(
								'count_id' 					=> get_count_id('db_items'), 
			    				'item_code' 				=> $item_code,
			    				'item_name' 				=> $item_name,
			    				'category_id' 				=> $category_id,
			    				'price' 					=> $price,
			    				'tax_id' 					=> $tax_id,
			    				'purchase_price' 			=> $purchase_price,
			    				'tax_type' 					=> $tax_type,
			    				'sales_price' 				=> $sales_price,
			    				'created_date' 				=> $CUR_DATE,
			    				'created_time' 				=> $CUR_TIME,
			    				'created_by' 				=> $CUR_USERNAME,
			    				'system_ip' 				=> $SYSTEM_IP,
			    				'system_name' 				=> $SYSTEM_NAME,
			    				'status' 					=> 1,
			    				'service_bit' 				=> 1,
			    				'seller_points'				=> $seller_points,
			    				'custom_barcode'			=> $custom_barcode,
			    				'description'				=> $description,
			    				'hsn'						=> $hsn,
			    				'sac'						=> $sac,
			    				'discount_type'				=> $discount_type,
			    				'discount'					=> $discount,
			    				'deposit_required'			=> $deposit_required,
			    				'deposit_percent'			=> $deposit_percent,
			    				'laundry_service_type'		=> !empty($laundry_service_type) ? $laundry_service_type : null,
			    				'laundry_service_type'		=> !empty($laundry_service_type) ? $laundry_service_type : null,
			    				'commission_type'			=> $commission_type,
			    				'commission_value'			=> $commission_value,
			    			);
		if(!empty($file_name)){
			$info['item_image'] = 'uploads/items/'.$file_name;
		}

		$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();

		$query1 = $this->db->insert('db_items', $info);
		if(!$query1){
			return "failed";
		}
		
		$item_id = $this->db->insert_id();

		if ($query1){
			$this->db->query("update db_items set expire_date=null where expire_date='0000-00-00'");
			$this->db->trans_commit();
			$this->session->set_flashdata('success', 'Success!! New Service Added Successfully!');
	        return "success";
		}
		else{
			$this->db->trans_rollback();
	        return "failed";
		}
	}

	public function update_services(){
		$q_id = $this->input->post('q_id', TRUE);
		$item_code = $this->input->post('item_code', TRUE);
		$item_name = $this->input->post('item_name', TRUE);
		$category_id = $this->input->post('category_id', TRUE);
		$price = $this->input->post('price', TRUE);
		$tax_id = $this->input->post('tax_id', TRUE);
		$purchase_price = $this->input->post('purchase_price', TRUE);
		$tax_type = $this->input->post('tax_type', TRUE);
		$sales_price = $this->input->post('sales_price', TRUE);
		$deposit_required = $this->input->post('deposit_required', TRUE);
		$deposit_percent = $this->input->post('deposit_percent', TRUE);
		$laundry_service_type = $this->input->post('laundry_service_type', TRUE);
		$commission_type = $this->input->post('commission_type', TRUE) ?? 'none';
		$commission_value = $this->input->post('commission_value', TRUE) ?? 0;
		$seller_points = $this->input->post('seller_points', TRUE);
		$custom_barcode = $this->input->post('custom_barcode', TRUE);
		$description = $this->input->post('description', TRUE);
		$hsn = $this->input->post('hsn', TRUE);
		$sac = $this->input->post('sac', TRUE);
		$discount_type = $this->input->post('discount_type', TRUE);
		$discount = $this->input->post('discount', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'] ?? date("Y-m-d");
		$CUR_TIME = $this->data['CUR_TIME'] ?? date("h:i:s a");
		$CUR_USERNAME = $this->data['CUR_USERNAME'] ?? 'System';
		$SYSTEM_IP = $this->data['SYSTEM_IP'] ?? $_SERVER['REMOTE_ADDR'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'] ?? 'localhost';
		
		$store_id=(store_module() && is_admin()) ? $store_id : get_current_store_id();
		$this->db->trans_begin();

		$file_name=$item_image='';
		if(!empty($_FILES['item_image']['name'])){
			$new_name = time();
			$config['file_name'] = $new_name;
			$config['upload_path']          = './uploads/items/';
	        $config['allowed_types']        = 'jpg|png';
	        $config['max_size']             = 1024;
	        $config['max_width']            = 1500;
	        $config['max_height']           = 1500;
	       
	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('item_image'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {		
	        	$file_name=$this->upload->data('file_name');
	        	$config['image_library'] = 'gd2';
				$config['source_image'] = 'uploads/items/'.$file_name;
				$config['create_thumb'] = TRUE;
				$config['maintain_ratio'] = TRUE;
				$config['width']         = 75;
				$config['height']       = 50;
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				$item_image=$config['source_image'];
	        }
		}

		$info = array(
    							'item_name' 				=> $item_name,
    							'item_code' 				=> $item_code,
    							'category_id' 				=> $category_id,		    					
    							'price' 					=> $price,
    							'tax_id' 					=> $tax_id,
    							'purchase_price' 			=> $purchase_price,
    							'tax_type' 					=> $tax_type,
    							'sales_price' 				=> $sales_price,
    							'seller_points'				=> $seller_points,
    							'custom_barcode'			=> $custom_barcode,
    							'description'				=> $description,
    							'hsn'						=> $hsn,
    							'sac'						=> $sac,
    							'discount_type'				=> $discount_type,
    							'discount'					=> $discount,
    							'deposit_required'			=> $deposit_required,
    							'deposit_percent'			=> $deposit_percent,
    							'laundry_service_type'		=> !empty($laundry_service_type) ? $laundry_service_type : null,
    							'laundry_service_type'		=> !empty($laundry_service_type) ? $laundry_service_type : null,
			    				'commission_type'			=> $commission_type,
			    				'commission_value'			=> $commission_value,
    						);
		if(!empty($file_name)){
			$info['item_image'] = 'uploads/items/'.$file_name;
		}

		$info['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();

		$query1 = $this->db->where('id',$q_id)->update('db_items', $info);

		if(!$query1){
			return "failed";
		}

		if ($query1){
			   $this->db->query("update db_items set expire_date=null where expire_date='0000-00-00'");
			   $this->db->trans_commit();
			   $this->session->set_flashdata('success', 'Success!! Service Item Updated Successfully!');
		        return "success";
		}
		else{
				$this->db->trans_rollback();
		        return "failed";
		}
	}

}
