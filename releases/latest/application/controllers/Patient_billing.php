<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient Billing — itemised bills from treatment plans, partial payments,
 * receipts, outstanding debt. Discounts and credit exceptions are
 * persistent approvals bound to the bill's fingerprint; finance staff can
 * never approve their own requests.
 */
class Patient_billing extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !physio_can('patient_billing_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('patient_billing_model', 'billing');
		$this->load->model('patients_model', 'patients');
		$this->load->model('treatment_plans_model', 'tpm');
		$this->load->model('approval_logs_model', 'al');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	/** Valid ?days= window, defaulting to 30 days. */
	private function _windowDays(){
		return in_array((int)$this->input->get('days'), array(1, 7, 30, 365, 0), TRUE) ? (int)$this->input->get('days') : 30;
	}

	/** Bills base query — joins keep rows with no patient record out, exactly like the on-screen list. */
	private function _billsBase($storeId, $patientId = 0){
		$q = $this->db->select('s.*, p.id AS patient_id, p.patient_code, c.customer_name AS patient_name')
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->join('db_customers c', 'c.id = s.customer_id AND c.store_id = s.store_id', 'left')
			->where('s.store_id', $storeId)
			->where('(s.plan_id IS NOT NULL OR s.encounter_id IS NOT NULL OR s.reference_no LIKE "OPENING-%")', null, false);
		if((int)$patientId > 0){
			$q->where('p.id', (int)$patientId);
		}
		return $q;
	}

	/** Charged / collected / outstanding across the whole window (not just the 200 shown). */
	private function _windowTotals($storeId, $days, $patientId = 0){
		$row = $this->db->select('COUNT(*) AS cnt, COALESCE(SUM(s.grand_total),0) AS charged, COALESCE(SUM(s.paid_amount),0) AS collected', false)
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->where('s.store_id', $storeId)
			->where('(s.plan_id IS NOT NULL OR s.encounter_id IS NOT NULL OR s.reference_no LIKE "OPENING-%")', null, false);
		if((int)$patientId > 0){ $row = $row->where('p.id', (int)$patientId); }
		if($days > 0){ $row = $row->where('s.sales_date >=', date('Y-m-d', strtotime('-'.($days - 1).' days'))); }
		$row = $row->get()->row();
		return array(
			'count' => (int)($row->cnt ?? 0),
			'charged' => round((float)($row->charged ?? 0), 2),
			'collected' => round((float)($row->collected ?? 0), 2),
			'outstanding' => round((float)($row->charged ?? 0) - (float)($row->collected ?? 0), 2),
		);
	}

	/** All patient bills for the store (finance desk) — plan invoices, visit bills and opening balances. */
	public function index(){
		$storeId = get_current_store_id();
		// Same window as the Care Queue: 1=today, 7/30/365 days back, 0=everything.
		$days = $this->_windowDays();
		$patientId = (int)$this->input->get('patient_id');
		$patient = null;
		if($patientId > 0){
			$patient = $this->patients->getPatient($patientId, $storeId);
			if(!$patient){ show_404(); return; }
		}
		$rowsQ = $this->_billsBase($storeId, $patientId);
		if($days > 0){ $rowsQ->where('s.sales_date >=', date('Y-m-d', strtotime('-'.($days - 1).' days'))); }
		$rows = $rowsQ->order_by('s.id', 'desc')->limit(200)->get()->result();
		$billsHidden = 0;
		if($days > 0){
			$outQ = $this->_billsBase($storeId, $patientId);
			$outQ->where('s.sales_date <', date('Y-m-d', strtotime('-'.($days - 1).' days')));
			$billsHidden = $outQ->count_all_results();
		}
		$data = array_merge($this->data, array(
			'page_title' => $patient ? 'Patient Bills' : 'Patient Billing',
			'bills'      => $rows,
			'bill_days'  => $days,
			'bill_hidden' => $billsHidden,
			'bill_totals' => $this->_windowTotals($storeId, $days, $patientId),
			'bill_patient' => $patient,
			'bill_patient_id' => $patientId,
			'can_add'    => physio_can('patient_billing_add'),
		));
		$data['content'] = $this->load->view('patient_billing/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** CSV of every bill in the chosen window — same rows the desk is looking at. */
	public function export(){
		$storeId = get_current_store_id();
		$days = $this->_windowDays();
		$patientId = (int)$this->input->get('patient_id');
		$patient = $patientId > 0 ? $this->patients->getPatient($patientId, $storeId) : null;
		if($patientId > 0 && !$patient){ show_404(); return; }
		$rows = $this->_exportRows($storeId, $days, $patientId);
		$store = get_store_details($storeId);
		$scope = $patient ? ($patient->patient_code ?: ('patient-'.$patientId)) : 'all';
		$filename = 'patient-bills-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($scope))
			. '-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($store->store_name ?? 'store'))
			. '-' . ($days > 0 ? $days . 'd' : 'all') . '-' . date('Ymd') . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$out = fopen('php://output', 'w');
		fputcsv($out, array('Invoice','Patient','Patient Code','Care','Date','Total','Paid','Due','Status'));
		foreach($rows as $b){
			fputcsv($out, array(
				$b->sales_code, $b->patient_name, $b->patient_code, $b->care_type ?: 'Not marked',
				$b->sales_date, round((float)$b->grand_total, 2), round((float)$b->paid_amount, 2),
				round((float)$b->grand_total - (float)$b->paid_amount, 2), $b->payment_status,
			));
		}
		fclose($out);
		exit;
	}

	/** A4 statement of the chosen window — prints or saves as PDF from the browser. */
	public function export_pdf(){
		$storeId = get_current_store_id();
		$days = $this->_windowDays();
		$patientId = (int)$this->input->get('patient_id');
		$patient = $patientId > 0 ? $this->patients->getPatient($patientId, $storeId) : null;
		if($patientId > 0 && !$patient){ show_404(); return; }
		$this->load->view('patient_billing/statement_a4', array(
			'bills'       => $this->_exportRows($storeId, $days, $patientId),
			'bill_days'   => $days,
			'bill_window' => $this->_windowLabel($days),
			'bill_totals' => $this->_windowTotals($storeId, $days, $patientId),
			'bill_patient' => $patient,
		));
	}

	private function _windowLabel($days){
		if($days === 1) return 'Today';
		if($days === 365) return 'This year';
		if($days === 0) return 'All time';
		return 'Last ' . $days . ' days';
	}

	private function _exportRows($storeId, $days, $patientId = 0){
		$q = $this->_billsBase($storeId, $patientId);
		if($days > 0){ $q->where('s.sales_date >=', date('Y-m-d', strtotime('-'.($days - 1).' days'))); }
		return $q->order_by('s.id', 'desc')->limit(10000)->get()->result();
	}

	/** Create the itemised bill from a plan (idempotent per plan). */
	public function create_bill($planId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->createBillFromPlan((int)$planId, (int)$this->input->post('account_id'));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Bill already exists' : 'Bill created', 'sales_id' => $res['sales_id'], 'sales_code' => $res['sales_code'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Bill view — lines, payments, approvals, receipt links. */
	public function view($salesId = 0){
		$bill = $this->billing->getBill((int)$salesId);
		if(!$bill){ $this->show_access_denied_page(); return; }
		$patient = $this->db->select('p.*, c.customer_name, c.mobile, c.email, c.address')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.customer_id', $bill->customer_id)
			->where('p.store_id', get_current_store_id())->get()->row();
		$plan = $bill->plan_id ? $this->tpm->getPlan($bill->plan_id) : null;
		$data = array_merge($this->data, array(
			'page_title' => 'Bill ' . $bill->sales_code,
			'bill'       => $bill,
			'patient'    => $patient,
			'plan'       => $plan,
			'items'      => $this->billing->printItems($bill->id),
			'payments'   => $this->billing->paymentsFor($bill->id),
			'due'        => round($bill->grand_total - $bill->paid_amount, 2),
			'approvals'  => $this->db->where('target_module', 'patient_bill')->where('target_id', $bill->id)
				->order_by('id', 'desc')->get('db_approval_logs')->result(),
			'can'        => array(
				'add'      => physio_can('patient_billing_add'),
				'md'       => physio_can('md_authority') || physio_can('can_approve'),
				'reminder' => physio_can('debt_reminder_manage'),
			),
		));

		// Invoice-level debt-reminder pause status (the same pause the admission
		// workspace exposes, keyed on this invoice).
		$data['reminder_pause'] = null;
		if(physio_can('debt_reminder_view') || physio_can('debt_reminder_manage')){
			if($this->db->table_exists('db_debt_reminder_pauses')){
				$data['reminder_pause'] = $this->db->where('store_id', get_current_store_id())
					->where('active', 1)->where('pause_scope', 'invoice')
					->where('invoice_id', (int)$salesId)
					->order_by('id', 'desc')->limit(1)->get('db_debt_reminder_pauses')->row();
			}
		}
		$this->load->model('Physio_accounts_model','pa');
		$data['system_accounts']=$this->pa->systemContext($bill->customer_id,get_current_store_id());
		$data['content'] = $this->load->view('patient_billing/view', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Partial payment. type: cash|transfer|pos|cust_advance|patient_wallet. */
	public function add_payment($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->addPayment((int)$salesId, (float)$this->input->post('amount'),
			$this->input->post('payment_type') ?: 'cash',
			trim($this->input->post('note', TRUE) ?: ''),
			trim($this->input->post('payment_ref', TRUE) ?: '') ?: null,
			(int)$this->input->post('account_id'));
		if(!empty($res['ok']) && empty($res['replayed'])){
			// Queue the receipt — delivery-side suppression + sink handled centrally.
			$patientId = $this->db->select('p.id')->from('db_sales s')
				->join('db_patients p','p.customer_id = s.customer_id')
				->where('s.id',(int)$salesId)->get()->row();
			physio_notify('billing.receipt.'.(int)$res['payment_id'], array(
				'channel'=>'email','template_key'=>'payment_receipt',
				'payload'=>array('patient_id'=>$patientId ? (int)$patientId->id : 0,
					'sales_id'=>(int)$salesId,'payment_id'=>(int)$res['payment_id'],
					'amount'=>(float)$this->input->post('amount')),
			));
		}
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Payment already recorded' : 'Payment received', 'payment_id' => $res['payment_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Request MD approval for a discount. */
	public function request_discount($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->requestDiscount((int)$salesId, (float)$this->input->post('amount'),
			trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Discount approval requested', 'log_id' => $res['log_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * Apply a previously-approved discount. The approver identity is the
	 * logged-in user with MD/approval authority — never the requester.
	 */
	public function apply_discount($salesId = 0){
		if(!(physio_can('md_authority') || physio_can('can_approve'))){
			$this->_json(array('status' => 'error', 'message' => 'MD authority required')); return;
		}
		$res = $this->billing->applyApprovedDiscount((int)$salesId, $this->session->userdata('inv_userid'));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Discount applied', 'grand_total' => $res['grand_total'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Approve a pending discount request (MD/PIN path). */
	public function approve_discount($logId = 0){
		if(!(physio_can('md_authority') || physio_can('can_approve'))){
			$this->_json(array('status' => 'error', 'message' => 'MD authority required')); return;
		}
		$log = $this->al->getLogById((int)$logId);
		if(!$log || $log->status !== 'pending'){
			$this->_json(array('status' => 'error', 'message' => 'Request not pending')); return;
		}
		if((int)$log->requesting_user_id === (int)$this->session->userdata('inv_userid')){
			$this->_json(array('status' => 'error', 'message' => 'You cannot approve your own request')); return;
		}
		$input = trim($this->input->post('pin', TRUE) ?: '');
		$res = validate_approval((int)$logId, $input, $this->session->userdata('inv_userid'));
		$this->_json($res['success']
			? array('status' => 'success', 'message' => $res['message'] . ' — apply the discount to post it')
			: array('status' => 'error', 'message' => $res['message']));
	}

	/**
	 * Request a full waiver of the outstanding balance. Raises the approval
	 * immediately, so the bill goes straight to "pending approval" and the
	 * Clinical Director's decision settles whether the patient pays.
	 */
	public function request_waiver($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->requestWaiver((int)$salesId, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Waiver sent for approval — the Clinical Director decides whether this bill is paid')
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Decline a pending discount / waiver — the bill stays payable. */
	public function decline_discount($logId = 0){
		if(!(physio_can('md_authority') || physio_can('can_approve'))){
			$this->_json(array('status' => 'error', 'message' => 'MD authority required')); return;
		}
		$log = $this->al->getLogById((int)$logId);
		if(!$log || $log->status !== 'pending'){
			$this->_json(array('status' => 'error', 'message' => 'Request not pending')); return;
		}
		if((int)$log->requesting_user_id === (int)$this->session->userdata('inv_userid')){
			$this->_json(array('status' => 'error', 'message' => 'You cannot decide your own request')); return;
		}
		$this->al->updateStatus((int)$logId, 'rejected', (int)$this->session->userdata('inv_userid'),
			$this->session->userdata('display_name') ?: $this->session->userdata('inv_username'), 'declined');
		$this->_json(array('status' => 'success', 'message' => 'Declined — this bill stays payable'));
	}

	public function request_credit($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->requestCreditException((int)$salesId, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Credit exception requested', 'log_id' => $res['log_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * Everything the three print documents need about one bill: the lines,
	 * every payment so far, and the balance. The balance comes from the
	 * invoice's own running total, so the paper always agrees with the
	 * screen the desk is looking at.
	 */
	private function _printData($bill){
		if(!function_exists('mp_first_name')){ $this->load->helper('physio'); }
		$patient = $this->db->select('p.*, c.customer_name, c.mobile, c.email, c.address')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.customer_id', $bill->customer_id)
			->where('p.store_id', get_current_store_id())->get()->row();
		$customer = $this->db->where('id', $bill->customer_id)->get('db_customers')->row();
		$payments = $this->billing->paymentsFor($bill->id);
		foreach($payments as $p){ $p->by_name = $this->_staffName($p->created_by); }
		return array(
			'bill'          => $bill,
			'items'         => $this->billing->printItems($bill->id),
			'payments'      => $payments,
			'patient'       => $patient,
			'customer'      => $customer,
			'patient_name'  => trim((string)($patient->customer_name ?? ($customer->customer_name ?? ''))),
			'patient_code'  => trim((string)($patient->patient_code ?? '')),
			'paid'          => round((float)$bill->paid_amount, 2),
			'due'           => round((float)$bill->grand_total - (float)$bill->paid_amount, 2),
		);
	}

	/** Staff are named on documents, never their login handle. */
	private function _staffName($username){
		$u = $this->db->select('id')->where('UPPER(username)', strtoupper((string)$username))
			->get('db_users')->row();
		return mp_first_name($u ? (int)$u->id : 0, '');
	}

	/** Receipt for one payment — narrow paper, shows the whole bill and the balance. */
	public function receipt($paymentId = 0){
		$pay = $this->db->where('id', (int)$paymentId)->where('store_id', get_current_store_id())
			->get('db_salespayments')->row();
		if(!$pay){ $this->show_access_denied_page(); return; }
		$bill = $this->billing->getBill($pay->sales_id);
		if(!$bill){ $this->show_access_denied_page(); return; }
		if(!function_exists('mp_first_name')){ $this->load->helper('physio'); }
		$pay->by_name = $this->_staffName($pay->created_by);
		$this->load->view('patient_billing/receipt', array_merge($this->data, $this->_printData($bill), array(
			'payment' => $pay,
		)));
	}

	/** Itemised bill on narrow paper — the ticket that states what is being paid for. */
	public function invoice($salesId = 0){
		$bill = $this->billing->getBill((int)$salesId);
		if(!$bill){ $this->show_access_denied_page(); return; }
		$this->load->view('patient_billing/invoice_pos',
			array_merge($this->data, $this->_printData($bill)));
	}

	/** Full A4 bill — for insurance, HMO and employer paperwork. */
	public function invoice_a4($salesId = 0){
		$bill = $this->billing->getBill((int)$salesId);
		if(!$bill){ $this->show_access_denied_page(); return; }
		$this->load->view('patient_billing/invoice_a4',
			array_merge($this->data, $this->_printData($bill)));
	}
}
