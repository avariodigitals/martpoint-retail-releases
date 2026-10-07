<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Care Queue — the day's live patient flow.
 *
 * check-in   : one encounter per arrival; idempotent by checkin_key.
 *              Booked: checkin_key = "appt:{id}". Walk-in: caller supplies a
 *              uuid — a retry returns the same encounter.
 *              Check-in never deducts a session or posts a charge.
 * move       : queue stage transitions, permission-gated per target stage.
 * vitals     : nursing intake values on the open encounter.
 */
class Care_queue extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry');
			return;
		}
		if(!physio_can('care_queue_view')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('encounters_model', 'encounters');
		$this->load->model('patients_model', 'patients');
		$this->load->model('appointments_model', 'appts');
		$this->load->model('assessments_model', 'assessments');
		$this->load->model('investigations_model', 'investigations');
		$this->load->model('consents_model', 'consents');
		$this->load->model('patient_docs_model', 'docs');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$storeId = get_current_store_id();
		$branchIds = physio_can('clinical_cross_branch') ? null : physio_branch_ids();
		$today = date('Y-m-d');
		// Today's arrivals not yet checked in — reception's pending list
		$waiting = $this->appts->getAppointments($storeId, array(
			'date' => $today,
			'branch_ids' => physio_can('clinical_cross_branch') ? null : physio_branch_ids(),
		));
		$arrivals = array();
		foreach($waiting as $a){
			if(in_array($a->status, array('confirmed','requested','proposed')) && !$a->arrived_at) $arrivals[] = $a;
		}
		$data = array_merge($this->data, array(
			'page_title' => 'Care Queue',
			'queue'      => $this->encounters->getQueue($storeId, $branchIds),
			'arrivals'   => $arrivals,
			'patients'   => $this->patients->getPatients($storeId, 'active', '', 500),
			'branches'   => physio_branch_ids()
				? $this->db->where('store_id', $storeId)->where_in('id', physio_branch_ids())->get('db_warehouse')->result()
				: array(),
			'can_checkin' => physio_can('care_checkin'),
			'can_vitals'  => physio_can('vitals_add'),
			'can_physio'  => physio_can('encounters_add'),
			'can_finance' => physio_can('patient_funds_view'),
		));
		$data['content'] = $this->load->view('care_queue/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * POST checkin — appointment_id OR (patient_id + checkin_key [+ branch]).
	 * Retries are safe: the unique (store_id, checkin_key) returns the same
	 * encounter; status 'already' flags the dedup.
	 */
	public function checkin(){
		if(!physio_can('care_checkin')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$warehouseId = (int)$this->input->post('warehouse_id');
		if($warehouseId && !physio_can_branch($warehouseId)){
			$this->_json(array('status' => 'error', 'message' => 'Branch not in your scope')); return;
		}
		$res = $this->encounters->checkin(array(
			'appointment_id'    => (int)$this->input->post('appointment_id') ?: null,
			'patient_id'        => (int)$this->input->post('patient_id') ?: null,
			'checkin_key'       => trim($this->input->post('checkin_key', TRUE) ?: ''),
			'warehouse_id'      => $warehouseId ?: null,
			'clinician_user_id' => (int)$this->input->post('clinician_user_id') ?: null,
			'note'              => trim($this->input->post('note', TRUE) ?: '') ?: null,
		));
		if(isset($res['error'])){
			$this->_json(array('status' => 'error', 'message' => $res['error']));
		} else {
			$this->_json(array('status' => 'success',
				'message' => !empty($res['already']) ? 'Already checked in — same visit returned' : 'Checked in to waiting list',
				'encounter_id' => $res['encounter_id'], 'already' => !empty($res['already'])));
		}
	}

	public function move($id = 0){
		$to = trim($this->input->post('to', TRUE) ?: '');
		$note = trim($this->input->post('note', TRUE) ?: '') ?: null;
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->moveStage((int)$id, $to, $note);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Moved to ' . str_replace('_', ' ', $to)) : $res);
	}

	/**
	 * Clinical workspace for one encounter — vitals sets, assessments,
	 * investigations, consents, documents. Every panel is permission-gated;
	 * a user sees only what their role allows.
	 */
	public function encounter($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->show_access_denied_page(); return;
		}
		$patient = $this->patients->getPatient((int)$enc->patient_id);
		$storeId = get_current_store_id();
		$data = array_merge($this->data, array(
			'page_title'    => 'Visit ' . $enc->encounter_code,
			'enc'           => $enc,
			'patient'       => $patient,
			'vitals_sets'   => physio_can('vitals_view') ? $this->encounters->getVitals((int)$id) : array(),
			'assessments'   => physio_can('assessments_view') ? $this->assessments->listForEncounter((int)$id) : array(),
			'templates'     => physio_can('assessments_add') ? $this->assessments->listTemplates() : array(),
			'investigations'=> physio_can('investigations_view') ? $this->investigations->listRows($storeId, array('encounter_id' => (int)$id)) : array(),
			'consents'      => physio_can('patient_docs_view') ? $this->consents->listRows($storeId, array('patient_id' => (int)$enc->patient_id)) : array(),
			'documents'     => physio_can('patient_docs_view') ? $this->docs->listForPatient((int)$enc->patient_id) : array(),
			'events'        => $this->encounters->getEvents((int)$id),
			'can'           => array(
				'vitals_add'      => physio_can('vitals_add'),
				'assessments'     => physio_can('assessments_add'),
				'assess_fin'      => physio_can('assessments_finalize'),
				'amend'           => physio_can('encounters_amend'),
				'inv_request'     => physio_can('investigations_request'),
				'inv_result'      => physio_can('investigations_result_enter'),
				'inv_review'      => physio_can('investigations_review'),
				'docs_view'       => physio_can('patient_docs_view'),
				'docs_upload'     => physio_can('patient_docs_upload'),
				'docs_release'    => physio_can('patient_docs_release'),
				'consent_manage'  => physio_can('patient_docs_upload'),
				'assign'          => physio_can('encounters_add'),
				'intake'          => physio_can('vitals_add'),
			),
			'staff'         => $this->db->where('store_id', $storeId)->order_by('username')->get('db_users')->result(),
		));
		$data['content'] = $this->load->view('care_queue/encounter', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * POST vitals — a structured intake SET. Accepts arrays of per-vital
	 * fields so each entry carries unit + measured_at + explicit
	 * not_measured. finalize=1 locks the set (immutable thereafter).
	 */
	public function vitals($id = 0){
		if(!physio_can('vitals_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		// Two input styles:
		//  A) JSON body field 'entries' => [{vital_key,value,unit,not_measured,measured_at,label}]
		//  B) flat fields vit_key[]/vit_value[]/vit_unit[]/vit_nm[]/vit_at[]
		$entries = array();
		$raw = $this->input->post('entries');
		if($raw){
			$decoded = json_decode($raw, true);
			if(is_array($decoded)) $entries = $decoded;
		} else {
			$keys   = (array)$this->input->post('vit_key');
			$vals   = (array)$this->input->post('vit_value');
			$units  = (array)$this->input->post('vit_unit');
			$nms    = (array)$this->input->post('vit_nm');
			$ats    = (array)$this->input->post('vit_at');
			$labels = (array)$this->input->post('vit_label');
			foreach($keys as $i => $k){
				$entries[] = array(
					'vital_key'    => $k,
					'label'        => $labels[$i] ?? null,
					'value'        => $vals[$i] ?? null,
					'unit'         => $units[$i] ?? null,
					'not_measured' => !empty($nms[$i]),
					'measured_at'  => $ats[$i] ?? null,
				);
			}
		}
		$finalize  = (bool)$this->input->post('finalize');
		$vitalsSet = (int)$this->input->post('vitals_set_id');
		$note      = trim($this->input->post('vitals_notes', TRUE) ?: '') ?: null;
		$res = $this->encounters->saveVitalsSet((int)$id, $entries, $finalize, $vitalsSet, $note);
		if(isset($res['error'])){
			$this->_json(array('status' => 'error', 'message' => $res['error']));
		} else {
			$this->_json(array('status' => 'success',
				'message'   => $finalize ? 'Vitals finalised' : 'Draft saved',
				'vitals_id' => $res['vitals_id'], 'set_status' => $res['status']));
		}
	}

	public function vitals_list($id = 0){
		if(!physio_can('vitals_view')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$this->_json(array('status' => 'success', 'sets' => $this->encounters->getVitals((int)$id)));
	}

	/** Nursing intake completion → hands the visit to the physiotherapist. */
	public function intake_complete($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->completeIntake((int)$id,
			trim($this->input->post('note', TRUE) ?: '') ?: null);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Intake complete — with physiotherapist queue') : $res);
	}

	/** Assign the visit to a staff member (permission + branch controlled). */
	public function assign($id = 0){
		if(!physio_can('encounters_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->assignTo((int)$id, (int)$this->input->post('user_id'));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Assigned') : $res);
	}

	public function events($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		// Decorate with plain-English labels server-side so the feed never
		// prints a raw code such as "intake_completed" or "waiting_physio".
		$events = array();
		foreach($this->encounters->getEvents((int)$id) as $ev){
			$events[] = array(
				'label'      => mp_event_label($ev->event),
				'from_label' => $ev->from_stage ? mp_stage_label($ev->from_stage) : '',
				'to_label'   => $ev->to_stage ? mp_stage_label($ev->to_stage) : '',
				'note'       => $ev->note,
				'created_by_name' => $ev->created_by_name,
				'created_at' => $ev->created_at,
			);
		}
		$this->_json(array('status' => 'success', 'events' => $events));
	}
}
