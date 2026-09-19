<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monnify extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('monnify_model','monnify');
	}

	// Settings page
	public function settings(){
		$this->permission_check('monnify_settings');
		$data=$this->data;
		$data['page_title']='Monnify Settings';
		$data['settings']=$this->monnify->get_settings();
		$data['content'] = $this->load->view('monnify_settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// Save settings
	public function save_settings(){
		$this->permission_check('monnify_settings');
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];

		$data = array(
			'api_key' => $this->input->post('api_key', TRUE),
			'secret_key' => $this->input->post('secret_key', TRUE),
			'contract_code' => $this->input->post('contract_code', TRUE),
			'wallet_account_number' => $this->input->post('wallet_account_number', TRUE),
			'enabled' => $this->input->post('enabled', TRUE) ?? 0,
			'disbursements_enabled' => $this->input->post('disbursements_enabled', TRUE) ?? 0,
			'test_mode' => $this->input->post('test_mode', TRUE) ?? 1,
			'callback_url' => base_url('monnify/callback'),
			// Invalidate any cached token — keys may have changed
			'access_token' => null,
			'token_expires_at' => null,
			'created_date' => $CUR_DATE,
			'created_time' => $CUR_TIME,
			'created_by' => $CUR_USERNAME
		);

		if($this->monnify->save_settings($data)){
			echo "success";
		} else {
			echo "failed";
		}
	}

	// Generate checkout link (AJAX) — hosted page, card/transfer/USSD
	public function generate_link(){
		if(!$this->monnify->is_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'Monnify is not enabled'));
			return;
		}

		$amount = floatval($this->input->post('amount'));
		$customer = array(
			'name' => $this->input->post('customer_name'),
			'email' => $this->input->post('email'),
			'phone' => $this->input->post('phone')
		);
		$sales_id = intval($this->input->post('sales_id'));
		$customer_id = intval($this->input->post('customer_id'));

		if($amount <= 0){
			echo json_encode(array('status'=>false, 'message'=>'Invalid amount'));
			return;
		}

		$metadata = array(
			'sales_id' => $sales_id,
			'customer_id' => $customer_id,
			'store_id' => get_current_store_id(),
			'phone' => $customer['phone']
		);

		$result = $this->monnify->init_transaction($amount, $customer, $metadata);
		if($result['status']){
			$this->db->where('payment_reference', $result['payment_reference'])->update('db_monnify_payments', array(
				'sales_id' => $sales_id,
				'customer_id' => $customer_id
			));
		}
		echo json_encode($result);
	}

	// Pay-with-transfer (AJAX) — dynamic account number for the POS screen
	public function generate_transfer_account(){
		if(!$this->monnify->is_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'Monnify is not enabled'));
			return;
		}

		$amount = floatval($this->input->post('amount'));
		$customer = array(
			'name' => $this->input->post('customer_name'),
			'email' => $this->input->post('email'),
			'phone' => $this->input->post('phone')
		);
		$sales_id = intval($this->input->post('sales_id'));
		$customer_id = intval($this->input->post('customer_id'));

		if($amount <= 0){
			echo json_encode(array('status'=>false, 'message'=>'Invalid amount'));
			return;
		}

		$metadata = array(
			'sales_id' => $sales_id,
			'customer_id' => $customer_id,
			'store_id' => get_current_store_id()
		);

		$init = $this->monnify->init_transaction($amount, $customer, $metadata);
		if(!$init['status']){
			echo json_encode($init);
			return;
		}

		$this->db->where('payment_reference', $init['payment_reference'])->update('db_monnify_payments', array(
			'sales_id' => $sales_id,
			'customer_id' => $customer_id
		));

		$result = $this->monnify->init_bank_transfer($init['payment_reference']);
		if($result['status']){
			$result['amount'] = $amount;
		}
		echo json_encode($result);
	}

	// Poll transaction status (AJAX) — used by the POS "Check Payment" button
	public function verify(){
		$payment_reference = $this->input->post('payment_reference') ?: $this->input->get('payment_reference');
		if(empty($payment_reference)){
			echo json_encode(array('status'=>false, 'message'=>'No payment reference'));
			return;
		}

		$payment = $this->monnify->get_payment_by_reference($payment_reference);
		if(!$payment){
			echo json_encode(array('status'=>false, 'message'=>'Payment not found'));
			return;
		}

		$verify = $this->monnify->verify_transaction($payment_reference, $payment->store_id);
		if($verify['status']){
			$this->monnify->update_payment_status($payment_reference, $verify['payment_status'], $verify);
			if(in_array($verify['payment_status'], array('PAID','OVERPAID'))){
				$this->monnify->confirm_sales_payment($payment_reference);
			}
		}
		echo json_encode($verify);
	}

	// Customer-facing callback (browser redirect after checkout)
	public function callback(){
		$ref = $this->input->get('paymentReference') ?: $this->input->get('transactionReference');
		if(empty($ref)){
			show_error('No transaction reference found');
			return;
		}

		$payment = $this->monnify->get_payment_by_reference($ref);
		if(!$payment){
			$payment = $this->db->where('transaction_reference', $ref)->get('db_monnify_payments')->row();
		}
		if($payment) {
			$ref = $payment->payment_reference;
		}

		$store_id = $payment ? $payment->store_id : null;
		$verify = $this->monnify->verify_transaction($ref, $store_id);
		if($verify['status'] && in_array($verify['payment_status'], array('PAID','OVERPAID'))){
			$this->monnify->update_payment_status($ref, $verify['payment_status'], $verify);
			$this->monnify->confirm_sales_payment($ref);

			$data['message'] = 'Payment successful!';
			$data['reference'] = $ref;
			$this->load->view('monnify_callback', $data);
		} else {
			$data['message'] = 'Payment was not successful. Please try again or contact support.';
			$data['reference'] = $ref;
			$this->load->view('monnify_callback', $data);
		}
	}

	// Webhook endpoint — collections, disbursements, refunds, settlements
	public function webhook(){
		$input = @file_get_contents('php://input');
		if(empty($input)){
			http_response_code(400);
			echo 'No data received';
			return;
		}

		$event = json_decode($input, true);
		if(!$event || !isset($event['eventType'])){
			http_response_code(400);
			echo 'Invalid payload';
			return;
		}

		$event_data = $event['eventData'] ?? array();

		// Resolve which store this notification belongs to — webhooks are
		// unauthenticated so there is no session context. Prefer the local
		// payment/transfer record, then metaData, then the default store.
		$store_id = get_current_store_id();
		if(isset($event_data['metaData']['store_id'])){
			$store_id = intval($event_data['metaData']['store_id']);
		}
		if(!empty($event_data['paymentReference'])){
			$local = $this->monnify->get_payment_by_reference($event_data['paymentReference']);
			if($local) { $store_id = $local->store_id; }
		} elseif(!empty($event_data['reference'])){
			$local = $this->monnify->get_transfer_by_reference($event_data['reference']);
			if($local) { $store_id = $local->store_id; }
		}
		if(empty($store_id)) { $store_id = 1; }
		$settings = $this->monnify->get_settings($store_id);
		if(!$settings || empty($settings->secret_key)){
			http_response_code(500);
			echo 'Monnify not configured';
			return;
		}

		// Signature header is only sent on live notifications — enforce it there
		if($settings->test_mode == 0){
			if(!$this->monnify->verify_webhook_signature($input, $settings->secret_key)){
				http_response_code(401);
				echo 'Invalid signature';
				return;
			}
		}

		switch($event['eventType']){
			case 'SUCCESSFUL_TRANSACTION':
				$ref = $event_data['paymentReference'] ?? '';
				if(!empty($ref)){
					$this->monnify->update_payment_status($ref, $event_data['paymentStatus'] ?? 'PAID', array(
						'payment_method' => $event_data['paymentMethod'] ?? null,
						'paid_on' => isset($event_data['paidOn']) ? date('Y-m-d H:i:s', strtotime($event_data['paidOn'])) : null,
						'amount' => $event_data['amountPaid'] ?? null,
						'transaction_reference' => $event_data['transactionReference'] ?? null
					));
					$this->monnify->confirm_sales_payment($ref);
				}
				break;

			case 'SUCCESSFUL_DISBURSEMENT':
			case 'FAILED_DISBURSEMENT':
			case 'REVERSED_DISBURSEMENT':
				$ref = $event_data['reference'] ?? '';
				if(!empty($ref)){
					$this->monnify->update_transfer_status($ref, $event_data['status'] ?? str_replace('_DISBURSEMENT','',$event['eventType']), array(
						'session_id' => $event_data['sessionId'] ?? null,
						'response_message' => $event_data['responseMessage'] ?? ($event_data['responseDescription'] ?? null)
					));
				}
				break;
		}

		http_response_code(200);
		echo 'OK';
	}

	// ============================================================
	// DISBURSEMENTS
	// ============================================================

	// List NIP banks (AJAX)
	public function banks(){
		$this->permission_check_with_msg('monnify_transfers');
		echo json_encode($this->monnify->get_banks());
	}

	// Name enquiry (AJAX) — required before initiating a transfer
	public function name_enquiry(){
		$this->permission_check_with_msg('monnify_transfers');
		$account_number = $this->input->post('account_number', TRUE);
		$bank_code = $this->input->post('bank_code', TRUE);
		if(empty($account_number) || empty($bank_code)){
			echo json_encode(array('status'=>false, 'message'=>'Account number and bank code are required'));
			return;
		}
		echo json_encode($this->monnify->validate_account($account_number, $bank_code));
	}

	// Initiate a single transfer (AJAX)
	public function send_transfer(){
		$this->permission_check_with_msg('monnify_transfers');
		if(!$this->monnify->disbursements_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'Disbursements not enabled — enable in Monnify Settings after Monnify approves your account'));
			return;
		}

		$amount = floatval($this->input->post('amount'));
		$data = array(
			'amount' => $amount,
			'narration' => $this->input->post('narration', TRUE),
			'destination_bank_code' => $this->input->post('bank_code', TRUE),
			'destination_bank_name' => $this->input->post('bank_name', TRUE),
			'destination_account_number' => $this->input->post('account_number', TRUE),
			'destination_account_name' => $this->input->post('account_name', TRUE),
			'meta' => array('initiated_by' => $this->data['CUR_USERNAME'] ?? 'system')
		);

		if($amount <= 0 || empty($data['destination_bank_code']) || empty($data['destination_account_number']) || empty($data['destination_account_name'])){
			echo json_encode(array('status'=>false, 'message'=>'Amount, bank, account number and verified account name are required'));
			return;
		}

		echo json_encode($this->monnify->initiate_transfer($data));
	}

	// Submit OTP for a PENDING_AUTHORIZATION transfer (AJAX)
	public function authorize_otp(){
		$this->permission_check_with_msg('monnify_transfers');
		$reference = $this->input->post('reference', TRUE);
		$otp = $this->input->post('otp', TRUE);
		if(empty($reference) || empty($otp)){
			echo json_encode(array('status'=>false, 'message'=>'Reference and OTP are required'));
			return;
		}
		echo json_encode($this->monnify->authorize_transfer_otp($reference, $otp));
	}

	// Resend OTP (AJAX)
	public function resend_otp(){
		$this->permission_check_with_msg('monnify_transfers');
		$reference = $this->input->post('reference', TRUE);
		if(empty($reference)){
			echo json_encode(array('status'=>false, 'message'=>'Reference is required'));
			return;
		}
		echo json_encode($this->monnify->resend_transfer_otp($reference));
	}

	// Transfer status (AJAX)
	public function transfer_status(){
		$this->permission_check_with_msg('monnify_transfers');
		$reference = $this->input->post('reference', TRUE) ?: $this->input->get('reference');
		if(empty($reference)){
			echo json_encode(array('status'=>false, 'message'=>'Reference is required'));
			return;
		}
		echo json_encode($this->monnify->get_transfer_status($reference));
	}

	// Check if Monnify is enabled for this store (API)
	public function is_enabled(){
		echo json_encode(array('enabled' => $this->monnify->is_enabled()));
	}

	// Public config for the frontend checkout SDK
	public function get_checkout_config(){
		$settings = $this->monnify->get_settings();
		echo json_encode(array(
			'api_key' => $settings ? $settings->api_key : '',
			'contract_code' => $settings ? $settings->contract_code : '',
			'test_mode' => $settings ? $settings->test_mode : 1
		));
	}
}
