<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_profile extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('store_profile_model','store');
	}

	public function update($id){
		//if not admin
		if(!is_admin()){
			if($id!=get_current_store_id()){
				show_error("Access Denied", 403, $heading = "You Don't Have Enough Permission!!");exit();
			}
		}

		$this->permission_check('store_edit');
		$data=$this->store->get_details($id);
		$data['page_title']=$this->lang->line('store');
		$this->load->view('store', $data);
	}
	public function update_store(){
		// Always save NIN fields — model may not include them in its $data array
		$q_id = $this->input->post('q_id', TRUE);
		$nin_api_url = $this->input->post('nin_api_url', TRUE);
		$nin_api_key = $this->input->post('nin_api_key', TRUE);
		$nin_api_provider = $this->input->post('nin_api_provider', TRUE);
		$nin_api_cost = $this->input->post('nin_api_cost', TRUE);
		$nin_provider = $this->input->post('nin_provider', TRUE);
		$bvn_provider = $this->input->post('bvn_provider', TRUE);
		$interswitch_client_id = $this->input->post('interswitch_client_id', TRUE);
		$interswitch_client_secret = $this->input->post('interswitch_client_secret', TRUE);
		
		$can_edit_nin_settings = $this->permissions('nin_settings');
		$nin_api_enabled = $this->input->post('nin_api_enabled', TRUE);
		
		if(!empty($q_id)){
			$nin_data = array(
				'nin_api_url' => $nin_api_url,
				'nin_api_key' => $nin_api_key,
				'nin_api_provider' => $nin_api_provider,
				'nin_api_cost' => (!empty($nin_api_cost) && is_numeric($nin_api_cost)) ? $nin_api_cost : 50.00,
				'nin_provider' => $nin_provider,
				'bvn_provider' => $bvn_provider,
				'interswitch_client_id' => $interswitch_client_id,
				'interswitch_client_secret' => $interswitch_client_secret,
			);
			if($can_edit_nin_settings){
				$nin_data['nin_api_enabled'] = (!empty($nin_api_enabled)) ? 1 : 0;
			} else {
				// Preserve existing enabled state for users without nin_settings permission
				$existing = $this->db->where('id', $q_id)->get('db_store')->row();
				$nin_data['nin_api_enabled'] = isset($existing->nin_api_enabled) ? $existing->nin_api_enabled : 0;
			}
			$this->db->where('id', $q_id)->update('db_store', $nin_data);
		}
		
		$result=$this->store->update_store();
		echo $result;
	}

}