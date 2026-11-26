<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debt Reminder Controller
 * Manage automated debt reminder settings per store and per invoice.
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
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
		$data = array_merge($this->data, [
			'page_title' => 'Debt Reminder Settings',
			'settings' => $this->debt_reminder_model->getStoreSettings(),
			'history' => $this->debt_reminder_model->getHistory(NULL, 50, 0),
			'total_history' => $this->debt_reminder_model->countHistory()
		]);
		$data['content'] = $this->load->view('debt_reminder_settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		$enabled = $this->input->post('enabled') ? 1 : 0;
		$frequency = $this->input->post('frequency') ?: 'weekly';
		$maxReminders = (int)($this->input->post('max_reminders') ?: 0);
		$sendEmail = $this->input->post('send_email') ? 1 : 0;
		$sendSms = $this->input->post('send_sms') ? 1 : 0;

		// Server-side validation: at least one channel
		if(!$sendEmail && !$sendSms){
			echo json_encode(['status' => 'error', 'message' => 'At least one channel (Email or SMS) must be selected.']);
			return;
		}

		// Validate frequency
		$validFreqs = ['daily', '3days', 'weekly', 'biweekly', 'monthly'];
		if(!in_array($frequency, $validFreqs)){
			$frequency = 'weekly';
		}

		$data = [
			'enabled' => $enabled,
			'frequency' => $frequency,
			'max_reminders' => $maxReminders,
			'send_email' => $sendEmail,
			'send_sms' => $sendSms
		];

		$result = $this->debt_reminder_model->updateStoreSettings($storeId, $data);
		if($result){
			echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully']);
		} else {
			$error = $this->db->error();
			echo json_encode(['status' => 'error', 'message' => 'Failed to save settings: ' . ($error['message'] ?? 'Unknown error')]);
		}
	}

	/**
	 * Unpaid invoices with invoice-specific reminder controls
	 */
	public function customers(){
		$data = array_merge($this->data, [
			'page_title' => 'Debt Reminder — Unpaid Invoices',
			'invoices' => $this->debt_reminder_model->getInvoicesWithDebt(),
			'store_defaults' => $this->debt_reminder_model->getStoreSettings()
		]);
		$data['content'] = $this->load->view('debt_reminder_customers', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * Update one invoice's reminder settings
	 */
	public function update_customer(){
		if(!$this->permissions('debt_reminder_edit') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}

		$invoiceId = (int)$this->input->post('invoice_id');
		$storeId = get_current_store_id();

		$data = [
			'enabled' => $this->input->post('enabled') !== NULL ? ($this->input->post('enabled') ? 1 : 0) : NULL,
			'frequency' => $this->input->post('frequency') ?: 'weekly',
			'max_reminders' => $this->input->post('max_reminders') !== '' ? max(0,(int)$this->input->post('max_reminders')) : NULL,
			'send_email' => $this->input->post('send_email') !== NULL ? ($this->input->post('send_email') ? 1 : 0) : NULL,
			'send_sms' => $this->input->post('send_sms') !== NULL ? ($this->input->post('send_sms') ? 1 : 0) : NULL
		];
		if(!in_array($data['frequency'], ['daily','3days','weekly','biweekly','monthly'], true)) $data['frequency'] = 'weekly';
		$ok = $this->debt_reminder_model->updateInvoiceSettings($invoiceId, $storeId, $data);
		echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Invoice reminder settings updated' : 'Invoice not found']);
	}

	/**
	 * Manually process invoices whose reminder schedule is due
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
			'invoices_checked' => 0,
			'emails_sent' => 0,
			'sms_sent' => 0,
			'errors' => []
		];

		$storeId = get_current_store_id();
        $this->email_service->setStoreId($storeId);
		$storeDefaults = $this->debt_reminder_model->getStoreSettings($storeId);

		$invoices = $this->debt_reminder_model->getCustomersDueForReminder($storeId);
		$results['invoices_checked'] = count($invoices);

		if(empty($invoices)){
			echo json_encode([
				'status' => 'success',
				'message' => 'No invoices are due for a reminder at this time.',
				'details' => $results
			]);
			return;
		}

		foreach($invoices as $customer){
			$amountDue = $customer->amount_due;
			$email = $customer->email;
			$mobile = $customer->mobile;
			$customerName = $customer->customer_name;
			$emailOk = false;
			$smsOk = false;

			if($customer->send_email && !empty($email)){
				$invoiceNumber = $customer->sales_code;
				$dueDate = !empty($customer->due_date) ? show_date($customer->due_date) : 'N/A';
				$this->load->library('invoice_payment_link');
				$paymentLink = $this->invoice_payment_link->forSales((int)$customer->invoice_id,(int)$storeId);

				$res = $this->email_service->sendTemplate(
					'debt_reminder',
					$email,
					[
						'customer_name' => $customerName,
						'store_name' => get_store_name($storeId),
						'invoice_number' => $invoiceNumber,
						'amount_due' => store_number_format($amountDue),
						'due_date' => $dueDate,
						'payment_link' => $paymentLink
					],
					['related_module' => 'debt_reminder', 'related_record_id' => $customer->invoice_id]
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
					$res['success'] ? '' : $res['message'], $customer->invoice_id
				);
			}

			if($customer->send_sms && !empty($mobile)){
				$msg = "Hi {$customerName}, invoice {$customer->sales_code} has an outstanding balance of " . store_number_format($amountDue) . (!empty($customer->due_date) ? " and was due on " . show_date($customer->due_date) : '') . ". Please settle at your earliest convenience. Thank you, " . get_store_name($storeId);
				$res = $this->sms_model->send_sms($mobile, $msg,array('store_id'=>$storeId,'message_category'=>'transactional'));
				if($res === 'success'){
					$smsOk = true;
					$results['sms_sent']++;
				} else {
					$results['errors'][] = "SMS to {$mobile}: {$res}";
				}
				$this->debt_reminder_model->logHistory(
					$storeId, $customer->customer_id, $customerName, $amountDue, 'sms',
					$smsOk ? 'sent' : 'failed',
					$smsOk ? '' : $res, $customer->invoice_id
				);
			}

			if($emailOk || $smsOk){
				$this->debt_reminder_model->markSent($customer->invoice_id, $storeId, $amountDue);
			}
		}

		echo json_encode([
			'status' => 'success',
			'message' => "Processed {$results['invoices_checked']} invoices. Emails: {$results['emails_sent']}, SMS: {$results['sms_sent']}.",
			'details' => $results
		]);
	}
}
