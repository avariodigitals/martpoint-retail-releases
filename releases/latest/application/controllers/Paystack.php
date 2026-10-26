<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack extends MY_Controller {
	public function __construct(){
		parent::__construct();
		// The webhook endpoint receives signed POSTs from Paystack — there is
		// no merchant session, so it must skip the dashboard auth gate.
		// Authenticity is enforced by verify_webhook_signature() inside.
		if(strtolower($this->router->fetch_method()) !== 'webhook'){
			$this->load_global();
		}
		$this->load->model('paystack_model','paystack');
	}

	// Settings page
	public function settings(){
		$this->permission_check('paystack_settings');
		$data=$this->data;
		$data['page_title']='Paystack Settings';
		$data['settings']=$this->paystack->get_settings();
		$data['content'] = $this->load->view('paystack_settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
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

		$event = json_decode($input, true);
		if(!$event || !isset($event['event']) || empty($event['data']['reference'])){
			http_response_code(400);
			echo 'Invalid payload';
			return;
		}
		$reference = $event['data']['reference'];

		// Resolve the owning store from the payment target — webhooks carry no
		// session, so get_current_store_id() would pick the wrong keys.
		$this->load->model('storefront_model');
		$order = $this->storefront_model->getOrderByPaymentReference($reference);

		if($order){
			// Online-store order: verify signature with THAT store's keys.
			$settings = $this->paystack->get_settings($order->store_id);
		} else {
			// POS / sales payment or unmatched reference. Webhooks carry no
			// session so get_current_store_id() is empty — resolve the store
			// from the local payment link first, else from the signature.
			$known = $this->paystack->get_payment_by_reference($reference);
			$settings = ($known && !empty($known->store_id))
				? $this->paystack->get_settings($known->store_id)
				: $this->paystack->resolve_webhook_settings($input);
		}
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

		// Handle charge.success event
		if($event['event'] == 'charge.success'){
			$data = $event['data'];
			$amount = $data['amount'] / 100;
			$channel = $data['channel'];
			$paid_at = $data['paid_at'];
			$status = $data['status']; // success

			if($order){
				// Server-side verify then atomically claim the paid transition;
				// a replayed/racing webhook cannot fulfil twice.
				$verify = $this->paystack->verify_transaction($reference, $order->store_id);
				$ok = $verify['status'] && $verify['payment_status'] === 'success'
					&& $verify['reference'] === $order->order_code
					&& abs((float)$verify['amount'] - (float)$order->grand_total) < 0.01;
				if($ok){
					$claimed = $this->storefront_model->claimPaidOrder($order->id, [
						'paystack_reference' => $reference,
						'paystack_amount' => $verify['amount'],
						'order_status' => 'paid'
					]);
					if($claimed){
						$this->storefront_model->adjustStock($order->id);
						$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_webhook');
					} else {
						// Payment verified but the order is no longer claimable
						// (cancelled / reservation released / refunded). The
						// money is real — surface it instead of swallowing it.
						$this->load->model('payment_reconcile_model','recon');
						$this->recon->queue_late_payment($order->store_id, array(
							'provider'        => 'paystack',
							'reference'       => $reference,
							'order_id'        => $order->id,
							'amount'          => $verify['amount'],
							'expected_amount' => $order->grand_total,
							'currency'        => $verify['currency'] ?? null,
							'detail'          => 'Verified payment received for order ' . $order->order_code
								. ' which is already ' . $order->order_status . '/' . $order->payment_status
								. ' (stock ' . ($order->stock_state ?? 'n/a') . '). Refund the customer or reinstate the order.',
							'detected_by'     => 'webhook',
							'payload'         => $data,
						));
					}
					// Idempotent, self-healing: runs for the claim winner and
					// retries on later webhooks if fulfilment died mid-flight.
					$this->storefront_model->fulfilPaidOrder($order->id);
				}
			} else {
				// Update paystack payment record
				$known = $this->paystack->get_payment_by_reference($reference);
				$this->paystack->update_payment_status($reference, $status, array(
					'channel' => $channel,
					'paid_at' => $paid_at
				));

				// Confirm the sales payment if linked
				$this->paystack->confirm_sales_payment($reference);

				// Reconciliation: a verified charge matching no local payment
				// link or order is an unmatched exception; a settled amount
				// that disagrees with the initiated amount is a discrepancy.
				if($status === 'success'){
					$this->load->model('payment_reconcile_model','recon');
					if(!$known){
						$this->recon->queue(isset($settings->store_id) ? (int)$settings->store_id : get_current_store_id(), array(
							'exception_type' => 'unmatched',
							'provider'       => 'paystack',
							'reference'      => $reference,
							'amount'         => $amount,
							'currency'       => $data['currency'] ?? null,
							'detail'         => 'Charge succeeded at provider but matches no local order or payment link',
							'detected_by'    => 'webhook',
							'payload'        => $data,
						));
					} else {
						$currency_mismatch = !empty($data['currency']) && !empty($known->currency)
							&& strtoupper($data['currency']) !== strtoupper($known->currency);
						if(abs((float)$amount - (float)$known->amount) > 0.01 || $currency_mismatch){
							$this->recon->queue((int)$known->store_id, array(
								'exception_type'  => 'discrepancy',
								'provider'        => 'paystack',
								'reference'       => $reference,
								'sales_id'        => $known->sales_id,
								'amount'          => $amount,
								'expected_amount' => $known->amount,
								'currency'        => $data['currency'] ?? $known->currency,
								'detail'          => $currency_mismatch
									? 'Settled currency differs from initiated currency'
									: 'Settled amount differs from initiated amount',
								'detected_by'     => 'webhook',
							));
						}
					}
				}
			}
		}

		// Chargeback/dispute events → dispute exception on the matching record.
		if(strpos($event['event'], 'charge.dispute') === 0){
			$this->load->model('payment_reconcile_model','recon');
			$known = $this->paystack->get_payment_by_reference($reference);
			$exc_store = $known ? (int)$known->store_id : ($order ? (int)$order->store_id : get_current_store_id());
			$this->recon->queue($exc_store, array(
				'exception_type' => 'dispute',
				'provider'       => 'paystack',
				'reference'      => $reference,
				'sales_id'       => $known->sales_id ?? null,
				'order_id'       => $order->id ?? null,
				'amount'         => isset($event['data']['amount']) ? $event['data']['amount'] / 100 : null,
				'detail'         => 'Provider dispute event: '.$event['event'],
				'detected_by'    => 'webhook',
				'payload'        => $event['data'],
			));
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
