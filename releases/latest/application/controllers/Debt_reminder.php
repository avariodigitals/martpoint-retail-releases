<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debt Reminder Controller
 * Manage automated debt reminder settings per store and per customer.
 */
class Debt_reminder extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!$this->permissions('debt_reminder_view') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('debt_reminder_model');
		$this->load->model('dashboard_model');
	}

	/**
	 * Store settings page
	 */
	public function index(){
		$data = array_merge($this->data, [
			'page_title' => 'Debt Reminder Settings',
			'settings' => $this->debt_reminder_model->getStoreSettings(),
			'history' => $this->debt_reminder_model->getHistory(NULL, 50, 0),
			'total_history' => $this->debt_reminder_model->countHistory()
		]);
		$this->load->view('debt_reminder_settings', $data);
	}

	/**
	 * Save store settings
	 */
	public function save_settings(){
		if(!$this->permissions('debt_reminder_edit') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		$storeId = get_current_store_id();
		$data = [
			'enabled' => $this->input->post('enabled') ? 1 : 0,
			'frequency' => $this->input->post('frequency') ?: 'weekly',
			'max_reminders' => (int)($this->input->post('max_reminders') ?: 0),
			'send_email' => $this->input->post('send_email') ? 1 : 0,
			'send_sms' => $this->input->post('send_sms') ? 1 : 0
		];

		$this->debt_reminder_model->updateStoreSettings($storeId, $data);
		echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully']);
	}

	/**
	 * Customers list with debt and reminder overrides
	 */
	public function customers(){
		$data = array_merge($this->data, [
			'page_title' => 'Debt Reminder — Customers',
			'customers' => $this->debt_reminder_model->getCustomersWithDebt(),
			'store_defaults' => $this->debt_reminder_model->getStoreSettings()
		]);
		$this->load->view('debt_reminder_customers', $data);
	}

	/**
	 * Update a single customer's reminder settings
	 */
	public function update_customer(){
		if(!$this->permissions('debt_reminder_edit') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		$customerId = (int)$this->input->post('customer_id');
		$storeId = get_current_store_id();

		$data = [
			'enabled' => $this->input->post('enabled') !== NULL ? ($this->input->post('enabled') ? 1 : 0) : NULL,
			'frequency' => $this->input->post('frequency') ?: NULL,
			'max_reminders' => $this->input->post('max_reminders') !== '' ? (int)$this->input->post('max_reminders') : NULL,
			'send_email' => $this->input->post('send_email') !== NULL ? ($this->input->post('send_email') ? 1 : 0) : NULL,
			'send_sms' => $this->input->post('send_sms') !== NULL ? ($this->input->post('send_sms') ? 1 : 0) : NULL
		];

		// Remove NULL values so they don't overwrite with empty strings
		$data = array_filter($data, function($v){ return $v !== NULL; });

		$this->debt_reminder_model->updateCustomerSettings($customerId, $storeId, $data);
		echo json_encode(['status' => 'success', 'message' => 'Customer reminder settings updated']);
	}

	/**
	 * Manual trigger to send reminders now (for testing)
	 */
	public function trigger_now(){
		if(!$this->permissions('debt_reminder_edit') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		// Load cron controller method directly
		$this->load->model('email_service');
		$this->load->model('sms_model');

		$results = [
			'customers_checked' => 0,
			'emails_sent' => 0,
			'sms_sent' => 0,
			'errors' => []
		];

		$storeId = get_current_store_id();
		$storeDefaults = $this->debt_reminder_model->getStoreSettings($storeId);
		if(!$storeDefaults || !$storeDefaults->enabled){
			echo json_encode(['status' => 'error', 'message' => 'Debt reminders are not enabled for this store.']);
			return;
		}

		$customers = $this->debt_reminder_model->getCustomersDueForReminder($storeId);
		$results['customers_checked'] = count($customers);

		foreach($customers as $customer){
			$amountDue = $customer->amount_due;
			$customerRec = $this->db->where('id', $customer->customer_id)->get('db_customers')->row();
			if(!$customerRec) continue;

			$email = $customerRec->email;
			$mobile = $customerRec->mobile;
			$customerName = $customerRec->customer_name;
			$emailOk = false;
			$smsOk = false;

			if($customer->send_email && !empty($email)){
				$invoiceRec = $this->db->select('id, sales_code, sales_date')
					->where('customer_id', $customer->customer_id)
					->where('sales_status', 'Final')
					->where('(grand_total - paid_amount) >', 0)
					->order_by('sales_date', 'DESC')
					->limit(1)
					->get('db_sales')
					->row();
				$invoiceNumber = $invoiceRec ? $invoiceRec->sales_code : 'N/A';
				$dueDate = $invoiceRec ? show_date($invoiceRec->sales_date) : 'N/A';

				$res = $this->email_service->sendTemplate(
					'debt_reminder',
					$email,
					[
						'customer_name' => $customerName,
						'store_name' => get_store_name($storeId),
						'invoice_number' => $invoiceNumber,
						'amount_due' => store_number_format($amountDue),
						'due_date' => $dueDate,
						'payment_link' => base_url('customers')
					],
					['related_module' => 'debt_reminder', 'related_record_id' => $customer->customer_id]
				);
				if($res['success']){
					$emailOk = true;
					$results['emails_sent']++;
				} else {
					$results['errors'][] = "Email to {$email}: " . $res['message'];
				}
				$this->debt_reminder_model->logHistory(
					$storeId, $customer->customer_id, $customerName, $amountDue, 'email',
					$res['success'] ? 'sent' : 'failed',
					$res['success'] ? '' : $res['message']
				);
			}

			if($customer->send_sms && !empty($mobile)){
				$msg = "Hi {$customerName}, this is a reminder that you have an outstanding balance of " . store_number_format($amountDue) . ". Please settle at your earliest convenience. Thank you, " . get_store_name($storeId);
				$res = $this->sms_model->send_sms($mobile, $msg);
				if($res === 'success'){
					$smsOk = true;
					$results['sms_sent']++;
				} else {
					$results['errors'][] = "SMS to {$mobile}: {$res}";
				}
				$this->debt_reminder_model->logHistory(
					$storeId, $customer->customer_id, $customerName, $amountDue, 'sms',
					$smsOk ? 'sent' : 'failed',
					$smsOk ? '' : $res
				);
			}

			if($emailOk || $smsOk){
				$this->debt_reminder_model->markSent($customer->customer_id, $storeId, $amountDue);
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => "Processed {$results['customers_checked']} customers. Emails: {$results['emails_sent']}, SMS: {$results['sms_sent']}.",
			'details' => $results
		]);
	}
}
