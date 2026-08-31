<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class City extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']='Cities List';
		$this->load->view('city-list', $data);
	}

	public function add(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']='Add City';
		$data['q_id'] = '';
		$data['city'] = '';
		$data['state_id'] = '';
		$this->load->view('city_form', $data);
	}

	public function newcity(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$city = trim($this->input->post('city', TRUE));
		$state_id = (int)$this->input->post('state_id', TRUE);
		$store_id = (int)$this->input->post('store_id', TRUE);

		if(empty($city) || empty($state_id)){
			$this->session->set_flashdata('danger', 'City name and State are required.');
			redirect('city/add');
		}

		$exists = $this->db->where('state_id', $state_id)
						   ->where('UPPER(city)', strtoupper($city))
						   ->get('db_cities')->num_rows();
		if($exists > 0){
			$this->session->set_flashdata('danger', 'City already exists for the selected state.');
			redirect('city/add');
		}

		$info = array(
			'city' => $city,
			'state_id' => $state_id,
			'store_id' => $store_id,
			'status' => 1,
		);
		$q1 = $this->db->insert('db_cities', $info);
		if($q1){
			$this->session->set_flashdata('success', 'New City Added Successfully!');
			redirect('city');
		} else {
			$this->session->set_flashdata('danger', 'Failed to add city. Try again!');
			redirect('city/add');
		}
	}
}
