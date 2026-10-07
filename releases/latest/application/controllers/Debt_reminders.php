<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debt_reminders — clinic/patient/invoice-level debt reminder controls and
 * the admission/patient account statements.
 *
 * Permissions: finance-capable roles gate the financial surfaces; every JSON
 * endpoint enforces the same restriction (no admin/role-name bypass here).
 */
class Debt_reminders extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_can')) $this->load->helper('physio');
		$this->load->model('debt_reminder_v2_model', 'dr');
		$this->load->model('patient_statement_model', 'stmt');
	}

	private function _finance(){ return physio_can('daily_billing_view') || physio_can('patient_funds_view'); }
	private function _reminderView(){ return physio_can('debt_reminder_view'); }
	private function _reminderEdit(){ return physio_can('debt_reminder_manage'); }
	private function _json($a){ $this->output->set_content_type('application/json')->set_output(json_encode($a)); }

	// ============================== STATEMENTS ==============================

	/** Admission-only account statement (JSON). */
	public function admission_statement($admId = 0){
		if(!$this->_finance()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$stmt = $this->stmt->admissionStatement((int)$admId);
		$this->_json($stmt ? $stmt : array('status'=>'empty'));
	}

	/** Patient-wide statement (JSON). */
	public function patient_statement($patientId = 0){
		if(!$this->_finance()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$stmt = $this->stmt->patientStatement((int)$patientId);
		$this->_json($stmt ? $stmt : array('invoices'=>array(),'wallet'=>null,'total_debt'=>0.0));
	}

	// ============================== DEBT REMINDER ==============================

	/** Clinic settings + active pauses + recent audit (read-only). */
	public function settings(){
		if(!$this->_reminderView() && !$this->_finance()){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, array(
			'page_title' => 'Debt Reminders',
			'config'  => $this->dr->config(),
			'pauses'  => $this->dr->activePauses(),
			'audit'   => $this->dr->auditRows(),
			'can_view' => $this->_reminderView() || $this->_finance(),
			'can_edit' => $this->_reminderEdit(),
		));
		$data['content'] = $this->load->view('inpatient/debt_reminders', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_config(){
		if(!$this->_reminderEdit()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$storeId = get_current_store_id();
		$this->dr->saveConfig($storeId, array(
			'enabled' => $this->input->post('enabled') ? 1 : 0,
			'frequency' => $this->input->post('frequency', TRUE),
			'grace_days' => $this->input->post('grace_days'),
			'send_hour' => $this->input->post('send_hour'),
			'max_reminders' => $this->input->post('max_reminders'),
			'include_opening_debt' => $this->input->post('include_opening_debt') ? 1 : 0,
			'template_key' => $this->input->post('template_key', TRUE) ?: 'debt_reminder',
		));
		$this->_json(array('status'=>'success','message'=>'Debt reminder settings saved'));
	}

	public function pause(){
		if(!$this->_reminderEdit()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$scope = $this->input->post('scope', TRUE);
		$target = $this->input->post('target');
		$reason = $this->input->post('reason', TRUE);
		$resumeAt = $this->input->post('resume_at');
		if(!in_array($scope, array('clinic','patient','invoice'), true) || trim((string)$reason) === ''){
			$this->_json(array('status'=>'error','message'=>'A scope and reason are required')); return;
		}
		$id = $this->dr->pause(get_current_store_id(), $scope, $reason, $target ?: null, $resumeAt ?: null);
		$this->_json(array('status'=>'success','message'=>'Paused','pause_id'=>$id));
	}

	public function resume($pauseId = 0){
		if(!$this->_reminderEdit()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$ok = $this->dr->resume((int)$pauseId, get_current_store_id());
		$this->_json($ok ? array('status'=>'success','message'=>'Resumed') : array('status'=>'error','message'=>'Not found or already resumed'));
	}

	/** Queue reminders now (test path — sends through the outbox/sink). */
	public function schedule_now(){
		if(!$this->_reminderEdit()){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$r = $this->dr->schedule(get_current_store_id());
		$this->_json(array('status'=>'success','message'=>'Scheduled','details'=>$r));
	}
}
