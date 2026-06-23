<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('paystack_model','paystack');
	}

	// Settings page
	public function settings(){
		$this->permission_check('paystack_settings');
		$data=$this->data;
		$data['page_title']='Paystack Settings';
		$data['settings']=$this->paystack->get_settings();
		$this->load->view('paystack_settings', $data);
	}

	// Save settings
	public function save_settings(){
		$this->permission_check('paystack_settings');
		$secret_key = $this->input->post('secret_key', TRUE);
		$public_key = $this->input->post('public_key', TRUE);
		$enabled = $this->input->post('enabled', TRUE);
		$test_mode = $this->input->post('test_mode', TRUE);
		$webhook_secret = $this->input->post('webhook_secret', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];

		$data = array(
			'secret_key' => $secret_key,
			'public_key' => $public_key,
			'enabled' => $enabled ?? 0,
			'test_mode' => $test_mode ?? 1,
			'webhook_secret' => $webhook_secret ?? '',
			'callback_url' => base_url('paystack/callback'),
			'created_date' => $CUR_DATE,
			'created_time' => $CUR_TIME,
			'created_by' => $CUR_USERNAME
		);

		if($this->paystack->save_settings($data)){
			echo "success";
		} else {
			echo "failed";
		}
	}

	// Generate payment link (AJAX)
	public function generate_link(){
		if(!$this->paystack->is_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'Paystack is not enabled'));
			return;
		}

		$amount = floatval($this->input->post('amount'));
		$email = $this->input->post('email');
		$sales_id = intval($this->input->post('sales_id'));
		$customer_id = intval($this->input->post('customer_id'));
		$phone = $this->input->post('phone');

		if($amount <= 0){
			echo json_encode(array('status'=>false, 'message'=>'Invalid amount'));
			return;
		}
		if(empty($email)){
			echo json_encode(array('status'=>false, 'message'=>'Customer email is required'));
			return;
		}

		$metadata = array(
			'sales_id' => $sales_id,
			'customer_id' => $customer_id,
			'store_id' => get_current_store_id(),
			'phone' => $phone
		);

		$result = $this->paystack->generate_payment_link($amount, $email, $metadata);
		if($result['status']){
			// Update the paystack record with sales_id and customer_id
			$this->db->where('paystack_reference', $result['reference'])->update('db_paystack_payments', array(
				'sales_id' => $sales_id,
				'customer_id' => $customer_id,
				'customer_phone' => $phone
			));
		}
		echo json_encode($result);
	}

	// Customer-facing callback (browser redirect after payment)
	public function callback(){
		$reference = $this->input->get('reference');
		$trxref = $this->input->get('trxref');
		$ref = $reference ?: $trxref;

		if(empty($ref)){
			show_error('No transaction reference found');
			return;
		}

		// Verify the transaction
		$verify = $this->paystack->verify_transaction($ref);
		if($verify['status'] && $verify['payment_status'] == 'success'){
			// Update paystack record
			$this->paystack->update_payment_status($ref, 'success', $verify);
			// Confirm the sales payment
			$this->paystack->confirm_sales_payment($ref);

			$data['message'] = 'Payment successful!';
			$data['reference'] = $ref;
			$this->load->view('paystack_callback', $data);
		} else {
			$data['message'] = 'Payment was not successful. Please try again or contact support.';
			$data['reference'] = $ref;
			$this->load->view('paystack_callback', $data);
		}
	}

	// Webhook endpoint for async payment confirmation
	public function webhook(){
		// Get the raw input
		$input = @file_get_contents('php://input');
		if(empty($input)){
			http_response_code(400);
			echo 'No data received';
			return;
		}

		$settings = $this->paystack->get_settings();
		if(!$settings || empty($settings->secret_key)){
			http_response_code(500);
			echo 'Paystack not configured';
			return;
		}

		// Verify webhook signature (optional but recommended)
		if(!empty($settings->webhook_secret)){
			$valid = $this->paystack->verify_webhook_signature($input, $settings->webhook_secret);
			if(!$valid){
				http_response_code(401);
				echo 'Invalid signature';
				return;
			}
		}

		$event = json_decode($input, true);
		if(!$event || !isset($event['event'])){
			http_response_code(400);
			echo 'Invalid payload';
			return;
		}

		// Handle charge.success event
		if($event['event'] == 'charge.success'){
			$data = $event['data'];
			$reference = $data['reference'];
			$amount = $data['amount'] / 100;
			$channel = $data['channel'];
			$paid_at = $data['paid_at'];
			$status = $data['status']; // success

			// Update paystack payment record
			$this->paystack->update_payment_status($reference, $status, array(
				'channel' => $channel,
				'paid_at' => $paid_at
			));

			// Confirm the sales payment if linked
			$this->paystack->confirm_sales_payment($reference);
		}

		http_response_code(200);
		echo 'OK';
	}

	// Check if Paystack is enabled for this store (API)
	public function is_enabled(){
		echo json_encode(array('enabled' => $this->paystack->is_enabled()));
	}

	// Get public key for frontend JS
	public function get_public_key(){
		$settings = $this->paystack->get_settings();
		echo json_encode(array(
			'public_key' => $settings ? $settings->public_key : '',
			'test_mode' => $settings ? $settings->test_mode : 1
		));
	}
}
