<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends MY_Controller {
    public function __construct(){
		parent::__construct();
		
		$this->load_global();
		$this->load->model('site_model');
	}
	public function index(){
		//if not admin
		if(!special_access()){
			echo "Restricted Area!";exit();
		}
		//$this->permission_check('site_edit');
        $data=$this->site_model->get_details();
        $data['page_title']=$this->lang->line('site_settings');
		$this->load->view('site-settings', $data);
	}

	public function update_site(){
		if(demo_app()){
				echo "Restricted in Demo";exit();
			}
		//if not admin
		if(!special_access()){
			echo "Restricted Area!";exit();
		}
		$this->form_validation->set_rules('site_name', 'Site Name', 'trim|required');
		if ($this->form_validation->run() == TRUE) {
			$result=$this->site_model->update_site();
			echo $result;
		} else {
			echo "Please Enter Compulsary(* marked) fields!";
		}
	}
	public function langauge($id){
		$this->load->model('language_model');
        $this->language_model->set($id);
        redirect($_SERVER['HTTP_REFERER']);
	}

	public function get_states_by_country(){
		$country = $this->input->post('country');
		if(empty($country)){
			echo json_encode(array());
			return;
		}
		$states = $this->db->select('id, state')
		                   ->where('status',1)
		                   ->where('country',$country)
		                   ->from('db_states')
		                   ->get()
		                   ->result_array();
		echo json_encode($states);
	}

	public function get_cities_by_state(){
		$state_id = $this->input->post('state_id');
		if(empty($state_id) || !$this->db->table_exists('db_cities')){
			echo json_encode(array());
			return;
		}
		$cities = $this->db->select('id, city')
		                   ->where('status',1)
		                   ->where('state_id',$state_id)
		                   ->from('db_cities')
		                   ->get()
		                   ->result_array();
		echo json_encode($cities);
	}
}
