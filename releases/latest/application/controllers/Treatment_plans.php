<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Treatment Plans — clinician-owned care plans.
 * items carry price snapshots; amendments version the plan and supersede
 * any pending/approved billing approvals bound to it.
 */
class Treatment_plans extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled()){
			$this->show_access_denied_page(); return;
		}
		if(!physio_can('plans_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('treatment_plans_model', 'tpm');
		$this->load->model('patients_model', 'patients');
		$this->load->model('sessions_model', 'sessions');
		$this->load->model('assessments_model', 'assessments');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	/** Patient's plans — linked from the patient profile. */
	public function index($patientId = 0){
		$patient = $this->patients->getPatient((int)$patientId);
		if(!$patient){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, array(
			'page_title' => 'Treatment Plans — ' . $patient->patient_code,
			'patient'    => $patient,
			'plans'      => $this->tpm->listForPatient((int)$patientId),
			'can_add'    => physio_can('plans_add'),
			'can_amend'  => physio_can('plans_amend'),
			'can_view'   => physio_can('plans_view'),
		));
		$data['content'] = $this->load->view('treatment_plans/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** New plan form. */
	public function add($patientId = 0){
		if(!physio_can('plans_add')){ $this->show_access_denied_page(); return; }
		$patient = $this->patients->getPatient((int)$patientId);
		if(!$patient){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, array(
			'page_title'  => 'New Treatment Plan — ' . $patient->patient_code,
			'patient'     => $patient,
			'services'    => $this->tpm->billableServices(),
			'assessments' => $this->db->where('patient_id', $patient->id)->where('store_id', $storeId)
				->order_by('id', 'desc')->get('db_assessments')->result(),
			'episodes'    => $this->db->where('patient_id', $patient->id)->where('store_id', $storeId)
				->order_by('id', 'desc')->get('db_care_episodes')->result(),
			'clinicians'  => $this->db->where('store_id', $storeId)->order_by('username')->get('db_users')->result(),
		));
		$data['content'] = $this->load->view('treatment_plans/form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save(){
		if(!physio_can('plans_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$patientId = (int)$this->input->post('patient_id');
		$patient = $this->patients->getPatient($patientId);
		if(!$patient){ $this->_json(array('status' => 'error', 'message' => 'Patient not found')); return; }
		$items = json_decode($this->input->post('items'), true);
		if(!is_array($items) || empty($items)){
			$this->_json(array('status' => 'error', 'message' => 'Add at least one service line')); return;
		}
		$res = $this->tpm->createPlan(array(
			'patient_id'    => $patientId,
			'customer_id'   => $patient->customer_id,
			'episode_id'    => (int)$this->input->post('episode_id') ?: null,
			'assessment_id' => (int)$this->input->post('assessment_id') ?: null,
			'clinician_id'  => (int)$this->input->post('clinician_id') ?: (int)$this->session->userdata('inv_userid'),
			'branch_id'     => (int)$this->input->post('branch_id') ?: null,
			'status'        => $this->input->post('status') === 'active' ? 'active' : 'draft',
			'care_setting'  => $this->input->post('care_setting') === 'inpatient' ? 'inpatient' : 'outpatient',
			'title'         => trim($this->input->post('title', TRUE) ?: '') ?: null,
			'goals'         => trim($this->input->post('goals', TRUE) ?: '') ?: null,
			'review_points' => json_decode($this->input->post('review_points'), true) ?: null,
		), $items);
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Plan created', 'plan_id' => $res['plan_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function view($id = 0){
		$plan = $this->tpm->getPlan((int)$id);
		if(!$plan){ $this->show_access_denied_page(); return; }
		$patient = $this->patients->getPatient((int)$plan->patient_id);
		$clinician = $this->db->where('id', $plan->clinician_id)->get('db_users')->row();
		$bill = $this->db->where('plan_id', $plan->id)->where('store_id', get_current_store_id())
			->get('db_sales')->row();
		$data = array_merge($this->data, array(
			'page_title'   => 'Plan ' . $plan->plan_code,
			'plan'         => $plan,
			'patient'      => $patient,
			'clinician'    => $clinician,
			'items'        => $this->tpm->currentItems($plan),
			'versions'     => $this->tpm->versions($plan->id),
			'sessions'     => $this->sessions->sessionsForPlan($plan->id),
			'entitlements' => $this->sessions->entitlementsForPlan($plan->id),
			'bill'         => $bill,
			'review_points'=> $plan->review_points ? json_decode($plan->review_points, true) : array(),
			'can'          => array(
				'amend'        => physio_can('plans_amend'),
				'billing'      => physio_can('patient_billing_add'),
				'billing_view' => physio_can('patient_billing_view'),
				'sessions'     => physio_can('sessions_view'),
			),
		));
		$data['content'] = $this->load->view('treatment_plans/view', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function amend($id = 0){
		if(!physio_can('plans_amend')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$plan = $this->tpm->getPlan((int)$id);
		if(!$plan){ $this->_json(array('status' => 'error', 'message' => 'Plan not found')); return; }
		$items = json_decode($this->input->post('items'), true);
		$changes = array();
		foreach(array('title','goals','care_setting','clinician_id') as $f){
			$v = $this->input->post($f);
			if($v !== null && $v !== false && $v !== '') $changes[$f] = $v;
		}
		$rp = $this->input->post('review_points');
		if($rp !== null && $rp !== false) $changes['review_points'] = json_decode($rp, true);
		$res = $this->tpm->amendPlan((int)$id, $changes, is_array($items) ? $items : null,
			trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Plan amended to v' . $res['version'], 'version' => $res['version'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function status($id = 0){
		if(!physio_can('plans_amend')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$to = $this->input->post('status');
		if(!in_array($to, array('draft','active','completed','cancelled'))){
			$this->_json(array('status' => 'error', 'message' => 'Bad status')); return;
		}
		$res = $this->tpm->setStatus((int)$id, $to, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Plan ' . $to) : array('status' => 'error', 'message' => $res['error']));
	}
}
