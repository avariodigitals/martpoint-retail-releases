<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Referrals — the external referral partner directory and the referrals made
 * to those partners for individual patients.
 *
 * index          : directory of labs, hospitals and doctors (referrals_view).
 * save_partner   : add or edit a partner (referrals_manage).
 * remove_partner : remove a partner; patient history keeps the name (referrals_manage).
 * save           : record / re-point a patient referral (referrals_manage).
 * status         : open → in progress → completed / cancelled (referrals_manage).
 * meeting        : log a dated meeting at the partner and set what is next due.
 * remove_meeting : delete a meeting entry (referrals_manage).
 */
class Referrals extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can_any(array('referrals_view','referrals_manage'))){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('referrals_model', 'referrals');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}
	private function _manage(){
		if(physio_can('referrals_manage')) return true;
		$this->_json(array('status' => 'error', 'message' => 'Access denied'));
		return false;
	}
	private function _result($r, $okMsg = 'Done'){
		if(!empty($r['ok'])){ $r['status'] = 'success'; $r['message'] = $r['message'] ?? $okMsg; }
		else { $r['status'] = 'error'; $r['message'] = $r['error'] ?? 'Failed'; }
		$this->_json($r);
	}

	/** Referral partner directory — labs, hospitals and doctors. */
	public function index(){
		if(!physio_can_any(array('referrals_view','referrals_manage'))){
			$this->show_access_denied_page(); return;
		}
		$type = $this->input->get('type', TRUE);
		if(!in_array($type, array('lab','hospital','doctor'), true)) $type = null;

		// First visit: lay down the starting hospital directory so nobody has
		// to type in a list of hospitals before they can refer anyone. Later
		// visits change nothing — the seed is idempotent and skips what exists.
		// Only someone who may edit the directory ever writes here.
		if(physio_can('referrals_manage')){
			$this->referrals->ensureOptionSeeds();
			if(!$this->referrals->partners(null, false)){
				$this->referrals->seedHospitalDirectory();
			}
		}

		$counts = array('lab' => 0, 'hospital' => 0, 'doctor' => 0);
		foreach($this->referrals->partners(null, false) as $p){
			if(isset($counts[$p->partner_type])) $counts[$p->partner_type]++;
		}

		$specialtyOptions = array();
		foreach($this->referrals->partnerTypes() as $t){
			$specialtyOptions[$t] = $this->referrals->specialtyOptions($t);
		}

		$data = array_merge($this->data, array(
			'page_title'         => 'Referral partners',
			'partners'           => $this->referrals->partners($type, false),
			'counts'             => $counts,
			'active_type'        => $type,
			'specialty_options'  => $specialtyOptions,
			'reasons'            => $this->referrals->optionList('reason', false),
			'urgencies'          => $this->referrals->optionList('urgency', false),
			'can'                => array('manage' => physio_can('referrals_manage')),
		));
		$data['content'] = $this->load->view('referrals/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_partner($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->savePartner((int)$id, array(
			'partner_type'   => $this->input->post('partner_type', TRUE),
			'name'           => $this->input->post('name', TRUE),
			'specialty'      => $this->input->post('specialty', TRUE),
			'contact_person' => $this->input->post('contact_person', TRUE),
			'phone'          => $this->input->post('phone', TRUE),
			'email'          => $this->input->post('email', TRUE),
			'address'        => $this->input->post('address', TRUE),
			'notes'          => $this->input->post('notes', TRUE),
			'is_active'      => (int)$this->input->post('is_active'),
		)));
	}

	public function remove_partner($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->removePartner((int)$id));
	}

	/** Add the standard hospital directory back (idempotent). */
	public function seed_hospitals(){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->seedHospitalDirectory());
	}

	/** Add or rename an entry in the reasons / urgency lists. */
	public function save_option($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->saveOption(
			$this->input->post('group_code', TRUE),
			$this->input->post('code', TRUE),
			$this->input->post('label', TRUE),
			$this->input->post('sort_order'),
			$this->input->post('is_default') === '1',
			(int)$id
		));
	}

	public function remove_option($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->removeOption((int)$id));
	}

	/** Flag which entry a new referral starts on. */
	public function default_option(){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->setDefaultOption(
			$this->input->post('group_code', TRUE),
			$this->input->post('code', TRUE)
		));
	}

	/** Record a patient referral, or re-point an open one at another partner. */
	public function save($id = 0){
		if(!$this->_manage()) return;
		// Reason comes from the configured list; "Other" carries what they typed.
		$reason = trim((string)$this->input->post('reason_other', TRUE));
		if($reason === '') $reason = trim((string)$this->input->post('reason', TRUE));
		$this->_result($this->referrals->saveReferral(array(
			'patient_id'     => (int)$this->input->post('patient_id'),
			'partner_id'     => (int)$this->input->post('partner_id'),
			'source'         => $this->input->post('source', TRUE),
			'source_id'      => (int)$this->input->post('source_id'),
			'encounter_id'   => (int)$this->input->post('encounter_id'),
			'assessment_id'  => (int)$this->input->post('assessment_id'),
			'reason'         => $reason,
			'notes'          => $this->input->post('notes', TRUE),
			'urgency'        => $this->input->post('urgency', TRUE),
			'next_due_date'  => $this->input->post('next_due_date', TRUE),
		), (int)$id));
	}

	public function status($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->setReferralStatus((int)$id, (string)$this->input->post('status', TRUE)));
	}

	public function meeting($referralId = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->addMeeting((int)$referralId, array(
			'meeting_date'  => $this->input->post('meeting_date', TRUE),
			'met_with'      => $this->input->post('met_with', TRUE),
			'outcome'       => $this->input->post('outcome', TRUE),
			'notes'         => $this->input->post('notes', TRUE),
			'next_due_date' => $this->input->post('next_due_date', TRUE),
		)));
	}

	public function remove_meeting($id = 0){
		if(!$this->_manage()) return;
		$this->_result($this->referrals->removeMeeting((int)$id));
	}
}
