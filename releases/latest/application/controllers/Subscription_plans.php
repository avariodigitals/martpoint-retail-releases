<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_plans extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index(){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$this->load->model('subscription_plans_model','plans');
		$data = $this->data;
		$data['plans'] = $this->plans->get_all();
		$data['page_title'] = 'Subscription Plans';
		$this->load->view('subscription_plans/index', $data);
	}

	public function add(){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$data = $this->data;
		$data['page_title'] = 'Create Plan';
		$data['plan'] = null;
		$this->load->view('subscription_plans/form', $data);
	}

	public function edit($id){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$this->load->model('subscription_plans_model','plans');
		$plan = $this->plans->get_by_id($id);
		if(!$plan){
			redirect('subscription_plans','refresh');
		}
		$data = $this->data;
		$data['page_title'] = 'Edit Plan';
		$data['plan'] = $plan;
		$this->load->view('subscription_plans/form', $data);
	}

	public function save(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_plans_model','plans');

		$id = (int) $this->input->post('id');
		$data = [
			'plan_name' => trim($this->input->post('plan_name', TRUE)),
			'plan_code' => trim($this->input->post('plan_code', TRUE)),
			'description' => trim($this->input->post('description', TRUE)),
			'branch_limit' => (int) $this->input->post('branch_limit'),
			'user_limit' => (int) $this->input->post('user_limit'),
			'product_limit' => (int) $this->input->post('product_limit'),
			'service_limit' => (int) $this->input->post('service_limit'),
			'media_storage_limit_mb' => (int) $this->input->post('media_storage_limit_mb'),
			'storefront_limit' => (int) $this->input->post('storefront_limit'),
			'custom_domain_limit' => (int) $this->input->post('custom_domain_limit'),
			'is_active' => (int) $this->input->post('is_active'),
			'display_order' => (int) $this->input->post('display_order'),
		];

		if(empty($data['plan_name'])){
			echo json_encode(['status'=>'error','message'=>'Plan name is required']);
			exit;
		}
		if(empty($data['plan_code'])){
			echo json_encode(['status'=>'error','message'=>'Plan code is required']);
			exit;
		}

		if($this->plans->save($data, $id ?: null)){
			echo json_encode(['status'=>'success','message'=>'Plan saved successfully']);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to save plan']);
		}
	}

	public function delete_plan(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_plans_model','plans');
		$id = (int) $this->input->post('id');
		if($this->plans->delete($id)){
			echo json_encode(['status'=>'success','message'=>'Plan deleted']);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to delete plan']);
		}
	}
}
