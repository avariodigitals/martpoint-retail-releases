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
		$data['content'] = $this->load->view('site-settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * AJAX — save just the incident banner state (Service Status section).
	 * Used by the mobile status screen; the desktop Site Settings form still
	 * posts everything through update_site().
	 */
	public function save_incident(){
		header('Content-Type: application/json');
		if(demo_app()){
			echo json_encode(['status' => 'error', 'message' => 'Restricted in Demo']);return;
		}
		if(!special_access()){
			set_status_header(403);
			echo json_encode(['status' => 'error', 'message' => 'Restricted Area!']);return;
		}
		// Client installs must not write incident state — the banner only
		// reflects real incidents from the martpoint.com.ng status API.
		if(!function_exists('mp_is_central') || !mp_is_central()){
			set_status_header(403);
			echo json_encode(['status' => 'error', 'message' => 'Incidents are managed centrally on martpoint.com.ng.']);return;
		}
		if(!function_exists('mp_set_incident')){
			echo json_encode(['status' => 'error', 'message' => 'Status feature unavailable — run the latest migration.']);return;
		}
		$prev = mp_get_incident();
		$ok = mp_set_incident([
			'active'     => $this->input->post('incident_active', TRUE) ? 1 : 0,
			'severity'   => (string)$this->input->post('incident_severity', TRUE),
			'message'    => (string)$this->input->post('incident_message', TRUE),
			'url'        => (string)$this->input->post('incident_url', TRUE),
			'started_at' => !empty($prev['active']) ? $prev['started_at'] : null,
		], 'local');
		if($ok && function_exists('mp_audit_log')){
			mp_audit_log('settings', 'update', 'site',
				$this->input->post('incident_active') ? 'Activated incident banner' : 'Cleared incident banner');
		}
		echo json_encode($ok
			? ['status' => 'ok', 'message' => 'Status saved.']
			: ['status' => 'error', 'message' => 'Could not save.']);
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
			if($result === 'success' && function_exists('mp_audit_log')){
				mp_audit_log('settings', 'update', 'site', 'Updated site settings');
			}
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
		// Fallback: if no cities mapped to this state, return all active cities
		if(empty($cities)){
			$cities = $this->db->select('id, city')
			                   ->where('status',1)
			                   ->from('db_cities')
			                   ->get()
			                   ->result_array();
		}
		echo json_encode($cities);
	}
}
