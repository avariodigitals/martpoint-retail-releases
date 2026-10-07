<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inpatient — admissions, bed board, transfers, porter tasks, nursing
 * care, meals, leave, daily billing, external referrals, discharge and
 * deceased closure. Every endpoint gates on explicit physio_* permission
 * keys; financial mutations live in Inpatient_model transactions.
 */
class Inpatient extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can_any(array(
			'admissions_view','admissions_manage','nursing_tasks_view','porter_tasks_view',
			'meals_view','daily_billing_view','referrals_view','discharge_decide','deceased_record'))){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('inpatient_model', 'ipd');
		$this->load->model('patients_model', 'patients');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}
	private function _deny(){ $this->_json(array('status'=>'error','message'=>'Access denied')); }
	private function _result($r, $okMsg = 'Done'){
		if(!empty($r['ok'])){ $r['status']='success'; $r['message'] = $r['message'] ?? $okMsg; }
		else { $r['status']='error'; $r['message'] = $r['error'] ?? 'Failed'; }
		$this->_json($r);
	}

	// ============================== READ SCREENS ==============================

	/** Admissions register. */
	public function index(){
		physio_require('admissions_view');
		$data = array_merge($this->data, array(
			'page_title'  => 'Admissions',
			'admissions'  => $this->ipd->admissions('all'),
			'patients'    => $this->patients->getPatients(),
			'beds'        => $this->ipd->availableBeds(),
			'can'         => array(
				'manage'   => physio_can('admissions_manage'),
				'deceased' => physio_can('deceased_record'),
			),
		));
		$data['content'] = $this->load->view('inpatient/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Admission workspace: occupancy, tasks, meals, leave, charges, discharge. */
	public function view($admId = 0){
		physio_require('admissions_view');
		$adm = $this->ipd->getAdmission((int)$admId);
		if(!$adm){ $this->show_access_denied_page(); return; }

		// Admission-scoped vs patient-wide finance. The admission account reads
		// only the admission-linked invoice; the wallet and total debt are
		// patient-wide and are labelled as such in the view.
		$account = $this->ipd->admissionAccount($adm->id);

		$canFinance = physio_can('daily_billing_view') || physio_can('patient_funds_view');
		$wallet = null; $patientDebt = null; $admissionStmt = null; $canReminderEdit = false;
		if($canFinance){
			$this->load->model('Patient_funds_model', 'pf');
			$this->load->model('Patient_billing_model', 'pb');
			$this->load->model('Patient_statement_model', 'stmt');
			$wallet = $this->pf->getBalances($adm->patient_id);
			$patientDebt = $this->pb->outstandingForPatient($adm->patient_id);
			$admissionStmt = $this->stmt->admissionStatement($adm->id);
		}
		$canReminderEdit = physio_can('debt_reminder_manage');

		$data = array_merge($this->data, array(
			'page_title' => 'Admission ' . $adm->admission_code,
			'adm'        => $adm,
			'occupancy'  => $this->ipd->currentOccupancy($adm->id),
			'history'    => $this->ipd->occupancyHistory($adm->id),
			'tasks'      => $this->ipd->tasksBoard($adm->id),
			'notes'      => $this->ipd->notes($adm->id),
			'meals'      => $this->ipd->mealOrders($adm->id),
			'meal_types' => $this->ipd->mealTypes(),
			'leaves'     => $this->ipd->leaveRecords($adm->id),
			'charges'    => $this->ipd->chargeRegister($adm->id),
			'discharges' => $this->ipd->discharges($adm->id),
			'referrals'  => $this->ipd->referrals($adm->patient_id),
			'beds'       => $this->ipd->availableBeds(),
			'account'    => $account,
			'wallet'     => $wallet,
			'patient_debt' => $patientDebt,
			'admission_stmt' => $admissionStmt,
			'can'        => array(
				'manage'    => physio_can('admissions_manage'),
				'nursing'   => physio_can('nursing_tasks_complete'),
				'notes'     => physio_can('nursing_notes_add'),
				'meals'     => physio_can('meals_manage'),
				'leave'     => physio_can('leave_manage'),
				'leave_app' => physio_can('leave_approve'),
				'recommend' => physio_can('discharge_recommend'),
				'decide'    => physio_can('discharge_decide'),
				'deceased'  => physio_can('deceased_record'),
				'referral'  => physio_can('referrals_manage'),
				'finance'   => $canFinance,
				'reminder'  => $canReminderEdit,
			),
		));
		$data['content'] = $this->load->view('inpatient/view', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Bed board — ward/bed state with live occupancy. */
	public function beds(){
		physio_require('admissions_view');
		$board = $this->ipd->bedBoard();
		$admIds = array();
		foreach($board['beds'] as $b){ if($b->admission_id) $admIds[] = (int)$b->admission_id; }
		$taskSummary = $this->ipd->bedTaskSummary($admIds);
		$data = array_merge($this->data, array(
			'page_title' => 'Bed Board',
			'wards'      => $board['wards'],
			'beds'       => $board['beds'],
			'task_summary' => $taskSummary,
			'can'        => array(
				'manage' => physio_can('beds_manage'),
				'open'   => physio_can('admissions_view'),
			),
		));
		$data['content'] = $this->load->view('inpatient/beds', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Porter task board + nursing task board. */
	public function tasks(){
		if(!physio_can_any(array('porter_tasks_view','nursing_tasks_view','admissions_view'))){ $this->show_access_denied_page(); return; }
		/*
		 * One screen, two boards. The rail links to each separately, so the
		 * selected board is carried in the query string and the screen shows
		 * that board first — otherwise "Porter tasks" and "Nursing tasks" both
		 * opened the same undifferentiated page.
		 */
		$board = strtolower(trim((string)$this->input->get('board')));
		if(!in_array($board, array('porter','nursing'), true)){
			// Default to whichever board this viewer can actually work.
			if(physio_can('porter_tasks_view') && !physio_can('nursing_tasks_view')){ $board = 'porter'; }
			elseif(physio_can('nursing_tasks_view')){ $board = 'nursing'; }
			else { $board = 'porter'; }
		}
		$canNursing = physio_can('nursing_tasks_view');
		$canPorter  = physio_can('porter_tasks_view');
		if($board === 'nursing' && !$canNursing){ $board = 'porter'; }
		if($board === 'porter'  && !$canPorter){ $board = 'nursing'; }

		$data = array_merge($this->data, array(
			'page_title'  => $board === 'nursing' ? 'Nursing Tasks' : 'Porter Tasks',
			'board'       => $board,
			'can_porter'  => $canPorter,
			'can_nursing' => $canNursing,
			'porter'      => $canPorter ? $this->ipd->porterTasks() : array(),
			'nursing'     => $canNursing ? $this->ipd->tasksBoard() : array(),
			'can'         => array(
				'porter' => physio_can('porter_tasks_complete'),
				'nurse'  => physio_can('nursing_tasks_complete'),
			),
		));
		$data['content'] = $this->load->view('inpatient/tasks', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** External referrals register. */
	public function referrals(){
		physio_require('referrals_view');
		$data = array_merge($this->data, array(
			'page_title' => 'External Referrals',
			'referrals'  => $this->ipd->referrals(),
			'patients'   => $this->patients->getPatients(),
			'can'        => array('manage' => physio_can('referrals_manage')),
		));
		$data['content'] = $this->load->view('inpatient/referrals', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Daily rates + policies (finance/MD configuration). */
	public function billing_setup(){
		physio_require('daily_billing_view');
		$data = array_merge($this->data, array(
			'page_title' => 'Inpatient Billing Setup',
			'rates'      => $this->ipd->rates(),
			'policies'   => $this->ipd->policies(),
			'can'        => array('run' => physio_can('daily_billing_run')),
		));
		$data['content'] = $this->load->view('inpatient/billing_setup', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============================== ADMISSION ==============================

	public function admit(){
		if(!physio_can('admissions_manage')){ $this->_deny(); return; }
		$r = $this->ipd->admit((int)$this->input->post('patient_id'), array(
			'reason'            => $this->input->post('reason', TRUE),
			'care_plan'         => $this->input->post('care_plan', TRUE),
			'clinician_user_id' => $this->input->post('clinician_user_id'),
			'bed_id'            => $this->input->post('bed_id'),
			'admitted_at'       => $this->input->post('admitted_at'),
			'outpatient_decision'      => $this->input->post('outpatient_decision', TRUE),
			'outpatient_decision_note' => $this->input->post('outpatient_decision_note', TRUE),
		));
		$this->_result($r, 'Patient admitted');
	}

	// ============================== BEDS / TRANSFER / PORTER ==============================

	public function save_ward(){
		if(!physio_can('beds_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->saveWard($this->input->post('name', TRUE), $this->input->post('branch_id')), 'Ward saved');
	}
	public function save_bed(){
		if(!physio_can('beds_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->saveBed((int)$this->input->post('ward_id'), $this->input->post('label', TRUE), $this->input->post('daily_rate')), 'Bed saved');
	}
	public function request_transfer($admId = 0){
		if(!physio_can('admissions_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->requestTransfer((int)$admId, (int)$this->input->post('to_bed_id'), $this->input->post('reason', TRUE)), 'Transfer requested — porter task created');
	}
	public function claim_task($taskId = 0){
		if(!physio_can('porter_tasks_complete')){ $this->_deny(); return; }
		$this->_result($this->ipd->claimTask((int)$taskId), 'Task claimed');
	}
	public function complete_task($taskId = 0){
		if(!physio_can('porter_tasks_complete')){ $this->_deny(); return; }
		$this->_result($this->ipd->completeTask((int)$taskId), 'Task completed');
	}
	public function cancel_task($taskId = 0){
		if(!physio_can_any(array('porter_tasks_complete','admissions_manage'))){ $this->_deny(); return; }
		$this->_result($this->ipd->cancelTask((int)$taskId), 'Task cancelled');
	}

	// ============================== NURSING ==============================

	public function complete_nursing_task($taskId = 0){
		if(!physio_can('nursing_tasks_complete')){ $this->_deny(); return; }
		$this->_result($this->ipd->completeTaskItem((int)$taskId, $this->input->post('note', TRUE)), 'Task done');
	}
	public function add_task($admId = 0){
		if(!physio_can_any(array('nursing_tasks_complete','admissions_manage'))){ $this->_deny(); return; }
		$due = $this->input->post('due_at') ?: date('Y-m-d H:i:s', strtotime('+2 hours'));
		$this->_result($this->ipd->addTask((int)$admId, $this->input->post('label', TRUE), $due), 'Task added');
	}
	public function add_note($admId = 0){
		if(!physio_can('nursing_notes_add')){ $this->_deny(); return; }
		$this->_result($this->ipd->recordNote((int)$admId,
			$this->input->post('note_type', TRUE), $this->input->post('body', TRUE),
			$this->input->post('shift', TRUE), (int)$this->input->post('task_id')), 'Recorded');
	}

	// ============================== MEALS ==============================

	public function order_meal($admId = 0){
		if(!physio_can('meals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->orderMeal((int)$admId, $this->input->post('meal_date') ?: date('Y-m-d'),
			$this->input->post('slot', TRUE), (int)$this->input->post('meal_type_id'),
			$this->input->post('diet_note', TRUE)), 'Meal ordered');
	}
	public function provide_meal($orderId = 0){
		if(!physio_can('meals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->provideMeal((int)$orderId), 'Meal provided');
	}

	// ============================== LEAVE ==============================

	public function request_leave($admId = 0){
		if(!physio_can('leave_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->requestLeave((int)$admId, $this->input->post('reason', TRUE),
			$this->input->post('expected_return')), 'Leave requested');
	}
	public function approve_leave($leaveId = 0){
		if(!physio_can('leave_approve')){ $this->_deny(); return; }
		$this->_result($this->ipd->approveLeave((int)$leaveId), 'Leave approved');
	}
	public function depart_leave($leaveId = 0){
		if(!physio_can('leave_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->departLeave((int)$leaveId), 'Departure recorded — bed held');
	}
	public function return_leave($leaveId = 0){
		if(!physio_can('leave_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->returnLeave((int)$leaveId), 'Return recorded');
	}

	// ============================== BILLING ==============================

	public function save_rate(){
		if(!physio_can('daily_billing_run')){ $this->_deny(); return; }
		$this->_result($this->ipd->saveRate($this->input->post('rate_code', TRUE),
			$this->input->post('name', TRUE), $this->input->post('amount'),
			$this->input->post('effective_from') ?: date('Y-m-d')), 'Rate saved — new version effective');
	}
	public function save_policy(){
		if(!physio_can('daily_billing_run')){ $this->_deny(); return; }
		$this->ipd->setPolicy($this->input->post('policy_key', TRUE), $this->input->post('policy_value', TRUE));
		$this->_json(array('status'=>'success','message'=>'Policy updated'));
	}
	public function run_billing(){
		if(!physio_can('daily_billing_run')){ $this->_deny(); return; }
		$date = $this->input->post('date') ?: date('Y-m-d');
		$r = $this->ipd->postDailyCharges(get_current_store_id(), $date);
		$this->_json(array('status'=>'success','message'=>"Charges posted: {$r['posted']}, already posted: {$r['skipped']}, wallet settled: " . number_format($r['settled'],2)));
	}

	// ============================== REFERRALS ==============================

	public function create_referral(){
		if(!physio_can('referrals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->createReferral((int)$this->input->post('patient_id'), array(
			'admission_id' => $this->input->post('admission_id'),
			'episode_id'   => $this->input->post('episode_id'),
			'destination'  => $this->input->post('destination', TRUE),
			'reason'       => $this->input->post('reason', TRUE),
			'urgency'      => $this->input->post('urgency', TRUE),
			'handover_doc_id' => $this->input->post('handover_doc_id'),
		)), 'Referral created');
	}
	public function referral_depart($refId = 0){
		if(!physio_can('referrals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->referralDepart((int)$refId, $this->input->post('exception_note', TRUE)), 'Departure recorded');
	}
	public function referral_update($refId = 0){
		if(!physio_can('referrals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->referralUpdate((int)$refId, $this->input->post('status', TRUE),
			$this->input->post('procedure_status', TRUE)), 'Updated');
	}
	public function referral_review($refId = 0){
		if(!physio_can('referrals_manage')){ $this->_deny(); return; }
		$this->_result($this->ipd->referralReview((int)$refId, (int)$this->input->post('assessment_id') ?: null), 'Return reviewed');
	}

	// ============================== DISCHARGE / DECEASED ==============================

	public function recommend_discharge($admId = 0){
		if(!physio_can('discharge_recommend')){ $this->_deny(); return; }
		$this->_result($this->ipd->recommendDischarge((int)$admId, $this->input->post('note', TRUE)), 'Discharge recommended — awaiting decision');
	}
	public function decide_discharge($dischargeId = 0){
		if(!physio_can('discharge_decide')){ $this->_deny(); return; }
		$this->_result($this->ipd->decideDischarge((int)$dischargeId,
			$this->input->post('approve') === '1',
			$this->input->post('reason', TRUE), $this->input->post('summary', TRUE),
			$this->input->post('follow_up', TRUE), $this->input->post('follow_up_date')),
			$this->input->post('approve') === '1' ? 'Discharge approved — patient may now depart' : 'Discharge rejected');
	}
	public function complete_discharge($dischargeId = 0){
		if(!physio_can('admissions_manage')){ $this->_deny(); return; }
		$res = $this->ipd->completeDischarge((int)$dischargeId);
		if(!empty($res['ok'])){
			$d = $this->db->select('a.patient_id, a.store_id')->from('db_discharges d')
				->join('db_admissions a','a.id=d.admission_id')
				->where('d.id',(int)$dischargeId)->get()->row();
			if($d){
				$this->load->model('portal_model','portal');
				$this->portal->maybeRequestFeedback((int)$d->patient_id, 'admission', (int)$dischargeId, (int)$d->store_id);
			}
		}
		$this->_result($res, 'Patient discharged — departure recorded');
	}
	public function record_deceased($patientId = 0){
		if(!physio_can('deceased_record')){ $this->_deny(); return; }
		$this->_result($this->ipd->recordDeceased((int)$patientId, array(
			'at'    => $this->input->post('at'),
			'notes' => $this->input->post('notes', TRUE),
		)), 'Deceased closure recorded');
	}
}
