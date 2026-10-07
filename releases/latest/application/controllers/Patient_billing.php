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

	/** All plan-bills for the store (finance desk). */
	public function index(){
		$storeId = get_current_store_id();
		$rows = $this->db->select('s.*, p.patient_code, c.customer_name AS patient_name')
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->join('db_customers c', 'c.id = s.customer_id AND c.store_id = s.store_id', 'left')
			->where('s.store_id', $storeId)
			->where('s.plan_id IS NOT NULL', null, false)
			->order_by('s.id', 'desc')->limit(200)->get()->result();
		$data = array_merge($this->data, array(
			'page_title' => 'Patient Billing',
			'bills'      => $rows,
			'can_add'    => physio_can('patient_billing_add'),
		));
		$data['content'] = $this->load->view('patient_billing/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Create the itemised bill from a plan (idempotent per plan). */
	public function create_bill($planId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->createBillFromPlan((int)$planId);
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => !empty($res['replayed']) ? 'Bill already exists' : 'Bill created', 'sales_id' => $res['sales_id'], 'sales_code' => $res['sales_code'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Bill view — lines, payments, approvals, receipt links. */
	public function view($salesId = 0){
		$bill = $this->billing->getBill((int)$salesId);
		if(!$bill){ $this->show_access_denied_page(); return; }
		$patient = $this->db->where('customer_id', $bill->customer_id)
			->where('store_id', get_current_store_id())->get('db_patients')->row();
		$plan = $bill->plan_id ? $this->tpm->getPlan($bill->plan_id) : null;
		$data = array_merge($this->data, array(
			'page_title' => 'Bill ' . $bill->sales_code,
			'bill'       => $bill,
			'patient'    => $patient,
			'plan'       => $plan,
			'items'      => $this->billing->billItems($bill->id),
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
		$data['content'] = $this->load->view('patient_billing/view', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Partial payment. type: cash|transfer|pos|cust_advance|patient_wallet. */
	public function add_payment($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->addPayment((int)$salesId, (float)$this->input->post('amount'),
			$this->input->post('payment_type') ?: 'cash',
			trim($this->input->post('note', TRUE) ?: ''),
			trim($this->input->post('payment_ref', TRUE) ?: '') ?: null);
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

	public function request_credit($salesId = 0){
		if(!physio_can('patient_billing_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->billing->requestCreditException((int)$salesId, trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res['ok']
			? array('status' => 'success', 'message' => 'Credit exception requested', 'log_id' => $res['log_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Receipt for one payment — print-friendly. */
	public function receipt($paymentId = 0){
		$pay = $this->db->where('id', (int)$paymentId)->where('store_id', get_current_store_id())
			->get('db_salespayments')->row();
		if(!$pay){ $this->show_access_denied_page(); return; }
		$bill = $this->billing->getBill($pay->sales_id);
		$patient = $this->db->where('customer_id', $bill->customer_id)
			->where('store_id', get_current_store_id())->get('db_patients')->row();
		$this->load->view('patient_billing/receipt', array_merge($this->data, array(
			'bill' => $bill, 'payment' => $pay, 'patient' => $patient,
			'items' => $this->billing->billItems($bill->id),
		)));
	}
}
