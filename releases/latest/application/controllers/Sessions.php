<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sessions — scheduled treatment sessions against plan-bound entitlements.
 * Only `complete` posts financial effects; check-in and ticket reprints
 * never touch money or counters.
 */
class Sessions extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can('sessions_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('sessions_model', 'sessions');
		$this->load->model('patients_model', 'patients');
		$this->load->model('patient_funds_model', 'funds');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$storeId = get_current_store_id();
		$rows = $this->db->select('s.*, p.patient_code, c.customer_name AS patient_name')
			->from('db_treatment_sessions s')
			->join('db_patients p', 'p.id = s.patient_id')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('s.store_id', $storeId)
			->order_by('s.scheduled_at', 'desc')->limit(300)->get()->result();
		$data = array_merge($this->data, array(
			'page_title' => 'Treatment Sessions',
			'sessions'   => $rows,
			'can'        => array(
				'checkin'  => physio_can('sessions_checkin'),
				'complete' => physio_can('sessions_complete'),
			),
		));
		$data['content'] = $this->load->view('sessions/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function schedule(){
		if(!physio_can('plans_add') && !physio_can('sessions_checkin')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$ent = $this->sessions->getEntitlement((int)$this->input->post('entitlement_id'));
		if(!$ent){ $this->_json(array('status' => 'error', 'message' => 'Entitlement not found')); return; }
		$patient = $this->patients->getPatient((int)$ent->patient_id);
		$res = $this->sessions->schedule(array(
			'entitlement_id' => $ent->id,
			'customer_id'    => $patient->customer_id,
			'scheduled_at'   => $this->input->post('scheduled_at') ?: date('Y-m-d H:i:s'),
			'clinician_id'   => (int)$this->input->post('clinician_id') ?: null,
			'branch_id'      => (int)$this->input->post('branch_id') ?: null,
			'fee'            => (float)$this->input->post('fee'),
			'encounter_id'   => (int)$this->input->post('encounter_id') ?: null,
		));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Session ' . $res['session_no'] . ' scheduled', 'session_id' => $res['session_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function checkin($id = 0){
		if(!physio_can('sessions_checkin')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$key = trim($this->input->post('checkin_key', TRUE) ?: '') ?: null;
		$res = $this->sessions->checkin((int)$id, $key);
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['already']) ? 'Already checked in' : 'Checked in', 'session_id' => $res['session_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function start($id = 0){
		if(!physio_can('sessions_complete') && !physio_can('sessions_checkin')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->sessions->start((int)$id);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Session in progress') : array('status' => 'error', 'message' => $res['error']));
	}

	/** The only money-moving endpoint: consume entitlement + apply fee once. */
	public function complete($id = 0){
		if(!physio_can('sessions_complete')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->sessions->complete((int)$id);
		if(!empty($res['ok']) && empty($res['already'])){
			$s = $this->db->select('patient_id,store_id')->where('id',(int)$id)->get('db_treatment_sessions')->row();
			if($s){
				$this->load->model('portal_model','portal');
				$this->portal->maybeRequestFeedback((int)$s->patient_id, 'session', (int)$id, (int)$s->store_id);
			}
		}
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['already']) ? 'Already completed — nothing double-posted' : 'Session completed — fee applied once', 'session_id' => $res['session_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function cancel($id = 0){
		if(!physio_can('sessions_complete') && !physio_can('plans_amend')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->sessions->cancel((int)$id, trim($this->input->post('reason', TRUE) ?: ''), false);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Session cancelled — hold released') : array('status' => 'error', 'message' => $res['error']));
	}

	public function no_show($id = 0){
		if(!physio_can('sessions_checkin')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->sessions->cancel((int)$id, trim($this->input->post('reason', TRUE) ?: ''), true);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Marked no-show — hold released') : array('status' => 'error', 'message' => $res['error']));
	}

	public function interrupt($id = 0){
		if(!physio_can('sessions_complete')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->sessions->interrupt((int)$id, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Session interrupted') : array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * Reverse a completion. When the session consumed funds, an approved
	 * wallet_adjustment log bound to the session must exist — the first call
	 * raises the request, an approver acts in Approvals, the retry executes.
	 */
	public function reverse($id = 0){
		if(!physio_can('sessions_complete')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$reason = trim($this->input->post('reason', TRUE) ?: '');
		$this->load->model('approval_logs_model', 'al');
		$s = $this->sessions->getSession((int)$id);
		$log = null;
		if($s && $s->consume_txn_id){
			$fp = $this->sessions->reversalFingerprint($s);
			$log = $this->al->hasCurrentApproval('wallet_adjustment', 'treatment_session', (int)$id, $fp);
			if(!$log){
				$logId = $this->al->log(array(
					'action_type' => 'request', 'approval_type' => 'wallet_adjustment',
					'requesting_user_id' => $this->session->userdata('inv_userid'),
					'requesting_user_name' => $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
					'reason' => $reason ?: 'Session completion reversal',
					'amount' => (float)$s->fee,
					'target_module' => 'treatment_session', 'target_id' => (int)$id,
					'target_version' => $fp,
				));
				$this->_json(array('status' => 'approval_required',
					'message' => 'Reversal requires approval — request raised',
					'log_id' => $logId));
				return;
			}
			if((int)$log->requesting_user_id === (int)$this->session->userdata('inv_userid')){
				$this->_json(array('status' => 'error', 'message' => 'You cannot approve your own reversal request'));
				return;
			}
		}
		$res = $this->sessions->reverse((int)$id, $reason, $log);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Completion reversed — entitlement and funds restored') : array('status' => 'error', 'message' => $res['error']));
	}

	/** 80mm print ticket — read-only; reprints post nothing. */
	public function ticket($id = 0){
		$d = $this->sessions->ticketData((int)$id);
		if(!$d){ $this->show_access_denied_page(); return; }
		$this->load->view('sessions/ticket', array_merge($this->data, $d));
	}
}
