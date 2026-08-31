<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('Services_model','services');
	}
	
	
	public function add()
	{
		$this->permission_check('services_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('services');
		$this->load->view('services/services',$data);
	}

	public function newservices(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');

		// Laundry businesses must specify service type
		$profile = mp_get_store_profile();
		$is_laundry = ($profile['industry_type'] ?? '') === 'laundry' || mp_feature_enabled('laundry_workflow');
		if ($is_laundry) {
			$this->form_validation->set_rules('laundry_service_type', 'Laundry Service Type', 'trim|required');
		}

		if ($this->form_validation->run() == TRUE) {
			$service_check = check_subscription_limit('service_limit');
			if($service_check !== true){
				echo $service_check;
				return;
			}
			if(!empty($_FILES['item_image']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo $media_check;
					return;
				}
			}
			$result=$this->services->verify_and_save();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	public function update($id){
		$this->belong_to('db_items',$id);
		$this->permission_check('services_edit');
		$data=$this->data;
		$this->load->model('items_model');
		$result=$this->items_model->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('services');
		$this->load->view('services/services', $data);
	}
	public function update_services(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');

		// Laundry businesses must specify service type
		$profile = mp_get_store_profile();
		$is_laundry = ($profile['industry_type'] ?? '') === 'laundry' || mp_feature_enabled('laundry_workflow');
		if ($is_laundry) {
			$this->form_validation->set_rules('laundry_service_type', 'Laundry Service Type', 'trim|required');
		}

		if ($this->form_validation->run() == TRUE) {
			$result=$this->services->update_services();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function get_brand_name($brand_id=''){
		if($brand_id==NULL || $brand_id=='' || $brand_id ==0){
			return;
		}
		return $this->db->query('select brand_name from db_brands where id="'.$brand_id.'"')->row()->brand_name;
	}
	
	
	
}
