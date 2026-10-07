<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient Funds — the wallet ledger UI. Balances are derived live:
 * available / reserved / pending-verification shown separately.
 * All movements via Patient_funds_model (atomic + idempotent).
 */
class Patient_funds extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can('patient_funds_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('patient_funds_model', 'funds');
		$this->load->model('patients_model', 'patients');
		$this->load->model('sessions_model', 'sessions');
		$this->load->model('patient_billing_model', 'billing');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	/** Funds dashboard for one patient + traceable ledger. */
	public function index($patientId = 0){
		$patient = $this->patients->getPatient((int)$patientId);
		if(!$patient){
			// No patient selected yet. This is the route the "Patient accounts"
			// nav entry points at, so showing access-denied here stranded every
			// role that holds patient_funds_view without a patient id in the
			// URL. Present the picker instead; choosing a patient opens the
			// real funds dashboard below.
			if((int)$patientId > 0){
				// A specific patient was asked for but is not in this store.
				$this->show_access_denied_page('That patient is not in your store or you do not have access to it.');
				return;
			}
			$storeId = get_current_store_id();
			$search = trim($this->input->get('search', TRUE) ?: '');
			$data = array_merge($this->data, array(
				'page_title' => 'Patient accounts',
				'patients'   => $this->patients->getPatients($storeId, 'active', $search, 100),
				'search'     => $search,
				'openings_pending' => $this->funds->openingPositionsPendingCount(),
			));
			$data['content'] = $this->load->view('patient_funds/picker', $data, TRUE);
			$this->load->view('mp_layout', $data);
			return;
		}
		$data = array_merge($this->data, array(
			'page_title'    => 'Patient Funds — ' . $patient->patient_code,
			'patient'       => $patient,
			'balances'      => $this->funds->getBalances((int)$patientId),
			'ledger'        => $this->funds->ledgerForPatient((int)$patientId),
			'reservations'  => $this->funds->reservationsForPatient((int)$patientId),
			'evidence'      => $this->funds->evidenceForPatient((int)$patientId),
			'entitlements'  => $this->sessions->entitlementsForPatient((int)$patientId),
			'bills'         => $this->billing->billsForPatient((int)$patientId),
			'outstanding'   => $this->billing->outstandingForPatient((int)$patientId),
			'can'           => array(
				'add'    => physio_can('patient_funds_add'),
				'verify' => physio_can('payment_evidence_verify'),
				'adjust' => physio_can('funds_adjust_request'),
				'refund' => physio_can('refund_request'),
				'billing'=> physio_can('patient_billing_view'),
			),
		));
		$data['content'] = $this->load->view('patient_funds/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Cash funding — verified immediately. */
	public function fund($patientId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$amount = (float)$this->input->post('amount');
		$ref    = trim($this->input->post('payment_ref', TRUE) ?: '');
		if($ref === '') $ref = 'CSH-' . strtoupper(bin2hex(random_bytes(4)));
		$res = $this->funds->fundCash((int)$patientId, $amount, $ref, $this->session->userdata('inv_userid'));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Funds credited' . (!empty($res['replayed']) ? ' (already received)' : ''), 'txn_id' => $res['txn_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Submit payment evidence (transfer/cheque) — pending verification. */
	public function submit_evidence($patientId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->submitEvidence((int)$patientId, array(
			'amount'      => (float)$this->input->post('amount'),
			'channel'     => $this->input->post('channel') ?: 'transfer',
			'payer_name'  => trim($this->input->post('payer_name', TRUE) ?: '') ?: null,
			'payment_ref' => trim($this->input->post('payment_ref', TRUE) ?: '') ?: null,
			'document_id' => (int)$this->input->post('document_id') ?: null,
			'sale_id'     => (int)$this->input->post('sale_id') ?: null,
			'plan_id'     => (int)$this->input->post('plan_id') ?: null,
		));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Evidence submitted — pending verification', 'evidence_id' => $res['evidence_id'], 'payment_ref' => $res['payment_ref'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function verify_evidence($id = 0){
		if(!physio_can('payment_evidence_verify')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->verifyEvidence((int)$id, $this->session->userdata('inv_userid'));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Already verified' : 'Verified — funds credited', 'txn_id' => $res['txn_id'] ?? null)
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function reject_evidence($id = 0){
		if(!physio_can('payment_evidence_verify')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->rejectEvidence((int)$id, trim($this->input->post('reason', TRUE) ?: ''), $this->session->userdata('inv_userid'));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Evidence rejected') : array('status' => 'error', 'message' => $res['error']));
	}

	/** Move retail store credit into patient care funds — atomic pair. */
	public function transfer_from_advance($patientId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->transferFromAdvance((int)$patientId, (float)$this->input->post('amount'),
			trim($this->input->post('note', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Store credit moved to patient funds') : array('status' => 'error', 'message' => $res['error']));
	}

	public function transfer_to_advance($patientId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$key = trim($this->input->post('operation_key', TRUE) ?: '') ?: uniqid('w2a');
		$res = $this->funds->transferToAdvance((int)$patientId, (float)$this->input->post('amount'), $key,
			trim($this->input->post('note', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Funds moved to store credit') : array('status' => 'error', 'message' => $res['error']));
	}

	/** Reserve available funds against a plan/entitlement. */
	public function reserve($patientId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->reserve((int)$patientId, (float)$this->input->post('amount'),
			(int)$this->input->post('plan_id') ?: null,
			(int)$this->input->post('entitlement_id') ?: null,
			trim($this->input->post('note', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Funds reserved', 'reservation_id' => $res['reservation_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function release($reservationId = 0){
		if(!physio_can('patient_funds_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->releaseReservation((int)$reservationId, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Reservation released') : array('status' => 'error', 'message' => $res['error']));
	}

	/** Cash refund — requires refund_wallet approval (checked via log). */
	public function refund($patientId = 0){
		if(!physio_can('refund_request')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$amount = (float)$this->input->post('amount');
		$reason = trim($this->input->post('reason', TRUE) ?: '');
		if(!$reason){ $this->_json(array('status' => 'error', 'message' => 'A reason is required')); return; }
		// Persistent approval: a current approved refund_wallet log must
		// exist for this patient+amount, and finance can't self-approve.
		$this->load->model('approval_logs_model', 'al');
		$patient = $this->funds->getPatient((int)$patientId);
		$fp = substr(hash('sha256', $patientId . '|' . $amount), 0, 40);
		$log = $this->al->hasCurrentApproval('refund_wallet', 'patient_wallet', (int)$patientId, $fp);
		if(!$log){
			// Raise the request instead — returns a pending approval log.
			$logId = $this->al->log(array(
				'action_type' => 'request', 'approval_type' => 'refund_wallet',
				'requesting_user_id' => $this->session->userdata('inv_userid'),
				'requesting_user_name' => $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
				'reason' => $reason, 'amount' => $amount,
				'target_module' => 'patient_wallet', 'target_id' => (int)$patientId, 'target_version' => $fp,
			));
			$this->_json(array('status' => 'approval_required', 'message' => 'Refund requires approval', 'log_id' => $logId));
			return;
		}
		if((int)$log->requesting_user_id === (int)$this->session->userdata('inv_userid')){
			$this->_json(array('status' => 'error', 'message' => 'You cannot approve your own refund request'));
			return;
		}
		$res = $this->funds->refund((int)$patientId, $amount, $reason, 'log:' . $log->id);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Refund posted') : array('status' => 'error', 'message' => $res['error']));
	}

	// ------------------------------------------------------------------
	// Opening positions — migrated balances enter here and post ONLY after
	// enter → review → approve. Approver is never the enterer.
	// ------------------------------------------------------------------

	/** Opening positions board. */
	public function openings(){
		if(!physio_can('opening_positions_view')){ $this->show_access_denied_page(); return; }
		$rows = $this->funds->openingPositions();
		$items = array();
		foreach($rows as $r){ $items[$r->id] = $this->funds->openingItems($r->id); }
		$data = array_merge($this->data, array(
			'page_title' => 'Opening Positions',
			'positions'  => $rows,
			'items'      => $items,
			'patients'   => $this->patients->getPatients(null, '', '', 500),
			'can'        => array(
				'enter'  => physio_can('opening_positions_enter'),
				'review' => physio_can('opening_positions_review'),
			),
		));
		$data['content'] = $this->load->view('patient_funds/openings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * Enter an opening-position item — debt, unused funds or remaining
	 * sessions are entered SEPARATELY, each with evidence and an "as of"
	 * (cutoff) date. Nothing posts until review + approval.
	 */
	public function opening_enter($patientId = 0){
		if(!physio_can('opening_positions_enter')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$type = $this->input->post('item_type') ?: 'unused_sessions';
		$common = array(
			'patient_id'           => (int)$patientId,
			'cutoff_date'          => $this->input->post('cutoff_date') ?: null,
			'evidence_document_id' => (int)$this->input->post('evidence_document_id') ?: null,
			'evidence_note'        => trim($this->input->post('evidence_note', TRUE) ?: '') ?: null,
		);
		if($type === 'unused_sessions'){
			$res = $this->sessions->migratePrepaid($common + array(
				'plan_id'       => (int)$this->input->post('plan_id') ?: null,
				'service_id'    => (int)$this->input->post('service_id') ?: null,
				'units_total'   => (int)$this->input->post('units_total'),
				'funded_amount' => (float)($this->input->post('funded_amount') ?: $this->input->post('amount')),
				'valuation_note'=> trim($this->input->post('note', TRUE) ?: '') ?: null,
			));
		} elseif(in_array($type, array('unused_money','outstanding_debt'))){
			$res = $this->funds->enterOpeningItem((int)$patientId, $type, $common + array(
				'note' => trim($this->input->post('note', TRUE) ?: '') ?: null,
			), (float)$this->input->post('amount'), trim($this->input->post('note', TRUE) ?: ''));
		} else {
			$res = array('ok' => false, 'error' => 'Unsupported item type');
		}
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Opening position entered — pending review', 'position_id' => $res['position_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function opening_review($posId = 0){
		if(!physio_can('opening_positions_review')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->reviewOpening((int)$posId);
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Reviewed — ready for approval') : array('status' => 'error', 'message' => $res['error']));
	}

	public function opening_approve($posId = 0){
		if(!physio_can('opening_positions_review')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->approveOpening((int)$posId);
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Already approved' : 'Approved — balance/entitlement posted')
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function opening_reject($posId = 0){
		if(!physio_can('opening_positions_review')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->funds->rejectOpening((int)$posId, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok'] ? array('status' => 'success', 'message' => 'Opening position rejected') : array('status' => 'error', 'message' => $res['error']));
	}

	/** Full patient statement — separate sections, traceable lines. */
	public function statement($patientId = 0){
		$patient = $this->patients->getPatient((int)$patientId);
		if(!$patient){ $this->show_access_denied_page(); return; }
		$this->load->view('patient_funds/statement', array_merge($this->data, array(
			'patient'      => $patient,
			'balances'     => $this->funds->getBalances((int)$patientId),
			'ledger'       => $this->funds->ledgerForPatient((int)$patientId, 500),
			'reservations' => $this->funds->reservationsForPatient((int)$patientId),
			'evidence'     => $this->funds->evidenceForPatient((int)$patientId),
			'entitlements' => $this->sessions->entitlementsForPatient((int)$patientId),
			'bills'        => $this->billing->billsForPatient((int)$patientId),
			'outstanding'  => $this->billing->outstandingForPatient((int)$patientId),
		)));
	}
}
