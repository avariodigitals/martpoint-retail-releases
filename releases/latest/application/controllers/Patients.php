<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patients — physiotherapy & rehabilitation clinical registry.
 *
 * Security model (review item 3): every action uses physio_can() — an explicit
 * db_permissions grant on the user's role. The inv_userid 1/2 super-admin
 * bypass in MY_Controller::permissions() deliberately does NOT apply to
 * clinical records.
 */
class Patients extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry');
			return;
		}
		if(!physio_can_any(['patients_view','patients_add'])){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('patients_model', 'patients');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	// ============== LIST ==============

	public function index(){
		if(!physio_can('patients_view')){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$status = trim($this->input->get('status', TRUE) ?: '');
		$search = trim($this->input->get('search', TRUE) ?: '');
		$data = array_merge($this->data, array(
			'page_title'    => mp_label('customer').'s',
			'patients'      => $this->patients->getPatients($storeId, $status, $search),
			'stats'         => $this->patients->getPatientStats($storeId),
			'status_filter' => $status,
			'search'        => $search,
			'can_add'       => physio_can('patients_add'),
			'can_edit'      => physio_can('patients_edit'),
			'can_merge'     => physio_can('patients_merge'),
			'can_export'    => physio_can('patients_export'),
		));
		$data['content'] = $this->load->view('patients/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== PROFILE ==============

	public function profile($id = 0){
		if(!physio_can('patients_view')){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$patient = $this->patients->getPatient((int)$id, $storeId);
		if(!$patient){ show_404(); return; }
		$deceasedBy = null;
		if($patient->deceased && $patient->deceased_recorded_by){
			$u = $this->db->select('username')->where('id', (int)$patient->deceased_recorded_by)->get('db_users')->row();
			$deceasedBy = $u ? $u->username : 'user #' . (int)$patient->deceased_recorded_by;
		}
		$data = array_merge($this->data, array(
			'page_title'   => $patient->customer_name,
			'patient'      => $patient,
			'episodes'     => $this->patients->getEpisodes($patient->id, $storeId),
			'appointments' => $this->patients->getAppointments($patient->id, $storeId),
			'events'       => $this->patients->getEvents($patient->id, $storeId),
			'deceased_by'  => $deceasedBy,
			'can_edit'     => physio_can('patients_edit'),
			'can_reminder' => physio_can('debt_reminder_manage'),
		));

		// Patient-level debt-reminder pause status (shown only when the
		// viewer can manage reminders; read-only otherwise).
		if(physio_can('debt_reminder_view') || physio_can('debt_reminder_manage')){
			if(!$this->db->table_exists('db_debt_reminder_pauses')){
				$data['reminder_pause'] = null;
			} else {
				$data['reminder_pause'] = $this->db->where('store_id', $storeId)
					->where('active', 1)->where('pause_scope', 'patient')
					->where('patient_id', (int)$patient->id)
					->order_by('id', 'desc')->limit(1)->get('db_debt_reminder_pauses')->row();
			}
		} else {
			$data['reminder_pause'] = null;
			$data['can_reminder'] = false;
		}
		$data['content'] = $this->load->view('patients/profile', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== SAVE (register / edit) ==============

	public function save(){
		$id = (int)$this->input->post('patient_id');
		if($id ? !physio_can('patients_edit') : !physio_can('patients_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		try{
			$name = trim($this->input->post('name', TRUE) ?: '');
			if($name === ''){ $this->_json(array('status' => 'error', 'message' => 'Patient name is required')); return; }
			$mobile = trim($this->input->post('mobile', TRUE) ?: '');
			$dob    = trim($this->input->post('dob', TRUE) ?: '');

			// Duplicate review — never silently create a second identity
			$storeId = get_current_store_id();
			$confirm = (int)$this->input->post('confirm_duplicate');
			$dups = $this->patients->findDuplicates($storeId, $mobile, $name, $dob ?: null, $id);
			if($dups && !$confirm){
				$this->_json(array('status' => 'duplicates', 'message' => 'Possible duplicate patient(s) found', 'duplicates' => $dups)); return;
			}

			$gender = trim($this->input->post('gender', TRUE) ?: '');
			$blood  = trim($this->input->post('blood_group', TRUE) ?: '');
			$patient = array(
				'gender'          => in_array($gender, Patients_model::GENDERS) ? $gender : null,
				'dob'             => $dob ?: null,
				'marital_status'  => trim($this->input->post('marital_status', TRUE) ?: '') ?: null,
				'occupation'      => trim($this->input->post('occupation', TRUE) ?: '') ?: null,
				'blood_group'     => in_array($blood, Patients_model::BLOOD_GROUPS) ? $blood : null,
				'nok_name'        => trim($this->input->post('nok_name', TRUE) ?: '') ?: null,
				'nok_phone'       => trim($this->input->post('nok_phone', TRUE) ?: '') ?: null,
				'nok_relationship'=> trim($this->input->post('nok_relationship', TRUE) ?: '') ?: null,
				'updated_at'      => date('Y-m-d H:i:s'),
			);
			$customer = array(
				'name'    => $name,
				'mobile'  => $mobile,
				'email'   => trim($this->input->post('email', TRUE) ?: ''),
				'phone'   => trim($this->input->post('phone', TRUE) ?: ''),
				'address' => trim($this->input->post('address', TRUE) ?: ''),
				'city'    => trim($this->input->post('city', TRUE) ?: ''),
				'link_customer_id' => (int)$this->input->post('link_customer_id') ?: null,
				'confirm_duplicate'=> $confirm,
			);
			$result = $this->patients->savePatient($patient, $customer, $id ?: null);
			if(is_array($result)){
				if(!empty($result['duplicates'])){
					$this->_json(array('status' => 'duplicates', 'message' => $result['error'], 'duplicates' => $result['duplicates'])); return;
				}
				$this->_json(array('status' => 'error', 'message' => $result['error']));
			} else {
				$this->_json(array('status' => 'success', 'message' => mp_label('customer').' saved', 'patient_id' => $result));
			}
		} catch(Exception $e){
			$this->_json(array('status' => 'error', 'message' => 'Error: '.$e->getMessage()));
		}
	}

	// ============== STATUS / FLAGS ==============

	public function set_status($id = 0){
		if(!physio_can('patients_edit')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$status = (int)$this->input->post('status') ? 1 : 0;
		$this->_json($this->patients->setStatus((int)$id, $status)
			? array('status' => 'success', 'message' => 'Status updated')
			: array('status' => 'error', 'message' => 'Could not update status'));
	}

	public function mark_deceased($id = 0){
		if(!physio_can('patients_edit')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$date  = trim($this->input->post('deceased_date', TRUE) ?: '');
		$reason = trim($this->input->post('reason', TRUE) ?: '');
		if($reason === ''){ $this->_json(array('status' => 'error', 'message' => 'A reason is required to record a death')); return; }
		$res = $this->patients->markDeceased((int)$id, $date, $reason);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Patient marked deceased — reminders and future bookings are suppressed')
			: $res);
	}

	/** Audited correction of a mistaken deceased flag — reason required. */
	public function unmark_deceased($id = 0){
		if(!physio_can('patients_edit')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$reason = trim($this->input->post('reason', TRUE) ?: '');
		if($reason === ''){ $this->_json(array('status' => 'error', 'message' => 'A correction reason is required')); return; }
		$res = $this->patients->unmarkDeceased((int)$id, $reason);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Deceased flag corrected — the change is recorded in the audit log')
			: $res);
	}

	public function mark_duplicate($dupId = 0){
		if(!physio_can('patients_merge')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$keepId = (int)$this->input->post('keep_patient_id');
		if(!$keepId){ $this->_json(array('status' => 'error', 'message' => 'Choose the patient record to keep')); return; }
		$this->_json($this->patients->markDuplicate((int)$dupId, $keepId)
			? array('status' => 'success', 'message' => 'Marked as duplicate — record retained for review, clinical data is never deleted')
			: array('status' => 'error', 'message' => 'Could not mark duplicate'));
	}

	// ============== EXPORT CSV ==============

	public function export(){
		if(!physio_can('patients_export')){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$rows = $this->patients->getPatients($storeId, '', '', 10000);
		$store = get_store_details($storeId);
		$filename = 'patients-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($store->store_name ?? 'store')) . '-' . date('Ymd') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$out = fopen('php://output', 'w');
		fputcsv($out, array('Patient Code','Name','Mobile','Email','Gender','DOB','NOK Name','NOK Phone','Status','Deceased','Registered'));
		foreach($rows as $r){
			fputcsv($out, array(
				$r->patient_code, $r->customer_name, $r->mobile, $r->email, $r->gender, $r->dob,
				$r->nok_name, $r->nok_phone,
				$r->status ? 'active' : 'inactive', $r->deceased ? 'yes' : 'no', $r->created_date,
			));
		}
		fclose($out);
		exit;
	}

	// ============== PATIENT PORTAL ACCESS ==============

	public function portal_access($id = 0){
		if(!physio_can('portal_manage')){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$patient = $this->patients->getPatient((int)$id, $storeId);
		if(!$patient){ show_404(); return; }
		$this->load->model('portal_model', 'portal');
		$data = array_merge($this->data, array(
			'page_title'   => 'Portal access — '.$patient->customer_name,
			'patient'      => $patient,
			'account'      => $this->portal->portalAccount($patient->id),
			'proxies'      => $this->portal->proxies($patient->id),
			'policies'     => $this->portal->policies($storeId),
			'invite'       => $this->session->flashdata('portal_invite'),
			'proxy_invite' => $this->session->flashdata('proxy_invite'),
		));
		$data['content'] = $this->load->view('patients/portal_access', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function portal_invite($id = 0){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$patient = $this->patients->getPatient((int)$id, get_current_store_id());
		if(!$patient){ $this->_json(array('status'=>'error','message'=>'Unknown patient')); return; }
		$this->load->model('portal_model', 'portal');
		$res = $this->portal->invitePatient($patient->id);
		if(empty($res['ok'])){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->session->set_flashdata('portal_invite', $res);
		$this->_json(array('status'=>'success','message'=>'Invitation created — the one-time link is shown on the Portal Access page'));
	}

	public function portal_revoke($id = 0){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$this->load->model('portal_model', 'portal');
		$res = $this->portal->revokePatient((int)$id);
		$this->_json(!empty($res['ok'])
			? array('status'=>'success','message'=>'Portal access revoked — takes effect immediately')
			: array('status'=>'error','message'=>$res['error']));
	}

	public function portal_reinstate($id = 0){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$this->load->model('portal_model', 'portal');
		$res = $this->portal->reinstatePatient((int)$id);
		$this->_json(!empty($res['ok'])
			? array('status'=>'success','message'=>'Account reinstated — patient must use a fresh invitation')
			: array('status'=>'error','message'=>$res['error']));
	}

	public function proxy_invite($id = 0){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$patient = $this->patients->getPatient((int)$id, get_current_store_id());
		if(!$patient){ $this->_json(array('status'=>'error','message'=>'Unknown patient')); return; }
		$scopes = $this->input->post('scopes');
		if(!is_array($scopes) || !$scopes){ $this->_json(array('status'=>'error','message'=>'Choose at least one access scope')); return; }
		$this->load->model('portal_model', 'portal');
		$res = $this->portal->inviteProxy($patient->id, array(
			'identity'     => trim((string)$this->input->post('identity', TRUE)),
			'name'         => trim((string)$this->input->post('name', TRUE)),
			'relationship' => trim((string)$this->input->post('relationship', TRUE)),
			'scopes'       => $scopes,
		));
		if(empty($res['ok'])){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->session->set_flashdata('proxy_invite', $res);
		$this->_json(array('status'=>'success','message'=>'Caregiver invitation created — the one-time link is shown on the Portal Access page'));
	}

	public function proxy_revoke($id = 0){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$this->load->model('portal_model', 'portal');
		$res = $this->portal->revokeProxy((int)$id);
		$this->_json(!empty($res['ok'])
			? array('status'=>'success','message'=>'Caregiver access revoked — effective immediately')
			: array('status'=>'error','message'=>$res['error']));
	}

	public function portal_policy_save(){
		if(!physio_can('portal_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$this->load->model('portal_model', 'portal');
		foreach(array('invite_expiry_hours','feedback_cooldown_days','reminder_hours_before','portal_enabled','feedback_enabled','testimonials_enabled') as $k){
			$this->portal->setPolicy($k, (string)$this->input->post($k));
		}
		$this->_json(array('status'=>'success','message'=>'Portal policies saved'));
	}
}
