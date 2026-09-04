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
		$data['rows'] = $this->db->select('c.id, c.city, s.state, s.country, c.status')
								 ->from('db_cities c')
								 ->join('db_states s','s.id = c.state_id','left')
								 ->get()->result();
		$data['crud'] = [
			'page_title' => 'Cities List',
			'page_sub' => 'Manage cities',
			'add_url' => base_url('city/add'),
			'add_label' => 'New City',
			'columns' => [
				['title' => 'City', 'field' => 'city', 'type' => 'text'],
				['title' => 'State', 'field' => 'state', 'type' => 'text'],
				['title' => 'Country', 'field' => 'country', 'type' => 'text'],
				['title' => 'Status', 'field' => 'status', 'type' => 'status'],
			],
			'module' => 'city',
			'status_url' => '',
			'delete_url' => '',
			'bulk_delete' => false,
		];
		$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		$data['store_id'] = get_current_store_id();

		$state_options = ['' => '-Select-'];
		$q = $this->db->where('status', 1)->order_by('state', 'asc')->get('db_states');
		foreach($q->result() as $row){
			$state_options[$row->id] = $row->state . ' (' . $row->country . ')';
		}

		$data['crud'] = [
			'page_title' => 'Add City',
			'page_sub' => 'Create a new city',
			'form_id' => 'city-form',
			'use_ajax' => false,
			'save_url' => 'city/newcity',
			'list_url' => base_url('city'),
			'module' => 'city',
			'fields' => [
				['name' => 'state_id', 'label' => 'State', 'type' => 'select', 'required' => true, 'options' => $state_options],
				['name' => 'city', 'label' => 'City Name', 'type' => 'text', 'required' => true],
				['name' => 'store_id', 'label' => 'Store', 'type' => 'hidden', 'value' => get_current_store_id()],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
