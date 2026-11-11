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
		// Users may keep Central open during a release. Run registered migrations
		// here too, so this screen never depends on manually applying SQL.
		if(function_exists('mp_is_central') && mp_is_central()){
			$this->update_db();
		}
		$data=$this->data;
		$data['page_title']='MartPoint Pay';
		$data['settings']=$this->paystack->get_settings();
		$data['payout_account']=$this->paystack->get_payout_account();
		$data['central_managed'] = !function_exists('mp_is_central') || !mp_is_central();
		if(!$data['central_managed']){
			$billing_base = rtrim(trim((string)getenv('CENTRAL_BILLING_API_URL')), '/');
			$billing_host = $billing_base !== '' ? parse_url($billing_base, PHP_URL_HOST) : false;
			$billing_scheme = $billing_base !== '' ? parse_url($billing_base, PHP_URL_SCHEME) : false;
			if(!$billing_host || $billing_scheme !== 'https'){
				$billing_base = 'https://martpoint.com.ng';
			}
			$data['paystack_shared_webhook_url'] = $billing_base.'/api/payments/webhooks/paystack';
		}
		$data['paystack_terms_accepted'] = $this->paystack->has_current_paystack_terms_acceptance((int)get_current_store_id());
		$data['content'] = $this->load->view('paystack_settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// Save settings
	public function save_settings(){
		$this->permission_check('paystack_settings');
		if(!function_exists('mp_is_central') || !mp_is_central()){
			echo 'central_managed';
			return;
		}
		// Catch up the Central schema before saving provider configuration.
		$this->update_db();
		$secret_key = $this->input->post('secret_key', TRUE);
		$public_key = $this->input->post('public_key', TRUE);
		$enabled = $this->input->post('enabled', TRUE);
		$test_mode = $this->input->post('test_mode', TRUE);
		$webhook_secret = $this->input->post('webhook_secret', TRUE);
		$test_mode = (string)($test_mode ?? '1') === '1' ? 1 : 0;
		$expected_secret_prefix = $test_mode ? 'sk_test_' : 'sk_live_';
		$expected_public_prefix = $test_mode ? 'pk_test_' : 'pk_live_';
		if(!is_string($secret_key) || strpos(trim($secret_key), $expected_secret_prefix) !== 0
			|| !is_string($public_key) || strpos(trim($public_key), $expected_public_prefix) !== 0){
			http_response_code(422);
			echo 'The selected mode does not match the Paystack key pair. Test mode needs sk_test_ and pk_test_ keys; live mode needs sk_live_ and pk_live_ keys.';
			return;
		}
		$secret_key = trim($secret_key);
		$public_key = trim($public_key);
		$platform_fee_percent = min(100, max(0, (float)$this->input->post('platform_fee_percent')));
		$platform_fee_fixed = max(0, (float)$this->input->post('platform_fee_fixed'));
		$fee_bearer = $this->input->post('fee_bearer', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];

		$data = array(
			'secret_key' => $secret_key,
			'public_key' => $public_key,
			'enabled' => $enabled ?? 0,
			'test_mode' => $test_mode,
			'webhook_secret' => $webhook_secret ?? '',
			// Keep the legacy fields in sync for older integrations while the
			// transaction split uses the combined percentage + fixed fee below.
			'platform_fee_type' => $platform_fee_percent > 0 ? 'percentage' : ($platform_fee_fixed > 0 ? 'flat' : 'none'),
			'platform_fee_value' => $platform_fee_percent > 0 ? $platform_fee_percent : $platform_fee_fixed,
			'platform_fee_percent' => $platform_fee_percent,
			'platform_fee_fixed' => $platform_fee_fixed,
			'fee_bearer' => $fee_bearer === 'merchant' ? 'merchant' : 'platform',
			'callback_url' => base_url('paystack/callback'),
			'created_date' => $CUR_DATE,
			'created_time' => $CUR_TIME,
			'created_by' => $CUR_USERNAME
		);

		if($this->paystack->save_settings($data)){
			$saved = $this->paystack->get_settings();
			if($saved && hash_equals((string)($saved->secret_key ?? ''), $secret_key)
				&& hash_equals((string)($saved->public_key ?? ''), $public_key)
				&& (int)($saved->test_mode ?? -1) === $test_mode){
				$table = $this->db->table_exists('db_payment_gateway_accounts')
					? 'db_payment_gateway_accounts' : 'db_paystack_settings';
				$fee_schema_ready = $this->db->field_exists('platform_fee_percent', $table)
					&& $this->db->field_exists('platform_fee_fixed', $table);
				if(!$fee_schema_ready && ($platform_fee_percent > 0 || $platform_fee_fixed > 0)){
					echo 'credentials_saved_fee_schema_missing';
					return;
				}
				echo "success";
				return;
			}
			http_response_code(500);
			echo 'The database write returned success, but the saved Paystack settings could not be confirmed. Check the Central database migration and provider row.';
		} else {
			$db_error = $this->db->error();
			http_response_code(500);
			$message = trim((string)($db_error['message'] ?? ''));
			echo $message !== ''
				? 'Paystack settings were not saved: '.$message
				: 'Paystack settings were not saved. Check that the Central Paystack migration has been applied.';
		}
	}

	// Generate payment link (AJAX)
	public function generate_link(){
		if(!$this->paystack->is_enabled()){
			echo json_encode(array('status'=>false, 'message'=>'MartPoint Pay is not enabled'));
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
		$payment = $this->paystack->get_payment_by_reference($ref);
		$store_id = $payment ? $payment->store_id : null;
		$verify = $this->paystack->verify_transaction($ref, $store_id);
		if($verify['status'] && $verify['payment_status'] == 'success'
			&& $this->paystack->payment_matches($ref, $verify['amount'], $verify['currency'])){
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
        if(mp_is_central() && $this->db->table_exists('db_messaging_purchases') && is_array($event)){
            $this->load->library('messaging_payments');
            $handled=$this->messaging_payments->webhook('paystack',$input,$event);
            if($handled!==null){
                $this->output->set_status_header($handled['http']??200)->set_content_type('application/json')->set_output(json_encode($handled));
                return;
            }
        }


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
			echo 'MartPoint Pay is not configured';
			return;
		}

		// Verify webhook signature (optional but recommended)
		if($settings->test_mode == 0){
			$valid = !empty($settings->webhook_secret)
				&& $this->paystack->verify_webhook_signature($input, $settings->webhook_secret);
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
				$moneyIsReal = !empty($verify['status'])
					&& (string)($verify['payment_status'] ?? '') === 'success'
					&& (string)($verify['reference'] ?? '') === (string)$order->order_code;
				$exact = $moneyIsReal && abs((float)$verify['amount'] - (float)$order->grand_total) < 0.01;
				// Whatever the amount, money the provider confirms for this order is
				// recorded against it. Before this, an in-full charge that arrived
				// here while the browser callback had not run was the only way an
				// order got paid, and anything short of the invoice was dropped.
				$this->load->model('online_payments_model', 'onlinepay');
				if(!$moneyIsReal){
					// The provider confirmed a charge that does not carry this
					// order's invoice number. It is not this order's money, so it
					// must not sit in the order's ledger waiting for somebody to
					// confirm another payment and settle the whole lot.
					$this->load->model('payment_reconcile_model','recon');
					$this->recon->queue((int)$order->store_id, array(
						'exception_type'  => 'unmatched',
						'provider'        => 'paystack',
						'reference'       => (string)$reference,
						'order_id'        => (int)$order->id,
						'amount'          => $verify['amount'] ?? $amount,
						'expected_amount' => (float)$order->grand_total,
						'currency'        => $verify['currency'] ?? ($data['currency'] ?? null),
						'detail'          => 'A charge came in against reference ' . $reference
							. ', which is not the invoice number of order ' . $order->order_code
							. ' (' . $order->order_status . '/' . $order->payment_status . '). Nothing was recorded on the order.',
						'detected_by'     => 'webhook',
						'payload'         => $data,
					));
				} else {
					$ledger = $this->onlinepay->recordGatewayMoney($order, [
						'provider'       => 'paystack',
						'reference'      => $reference,
						'amount'         => $verify['amount'] ?? 0,
						'currency'       => $verify['currency'] ?? null,
						'payment_status' => $verify['payment_status'] ?? '',
					], 'the payment provider');
					if(!$exact && ($ledger['recorded'] || $ledger['duplicate'])){
						$settle = $this->onlinepay->settleOrder((int)$order->store_id, (int)$order->id, null, 'card payment');
						if(($settle['state'] ?? '') === 'paid'){
							$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_webhook');
							$this->storefront_model->fulfilPaidOrder($order->id);
						}
					} elseif($exact){
						$claimed = $this->storefront_model->claimPaidOrder($order->id, [
							'paystack_reference' => $reference,
							'paystack_amount' => $verify['amount'],
							'order_status' => 'paid'
						]);
						if($claimed){
							$this->storefront_model->adjustStock($order->id);
							$salesId = (int)$this->storefront_model->ensureSalesLedger($order->id);
							$this->onlinepay->postVerifiedLines((int)$order->store_id, (int)$order->id, $salesId);
							$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_webhook');
						} else {
							// Payment verified but the order is no longer claimable
							// (cancelled / reservation released / already paid).
							// A replay of a charge this order has already been given
							// credit for is not news — the browser callback won the
							// claim a moment ago. Anything else is real money that
							// arrived where it cannot be applied, so surface it.
							if(empty($ledger['duplicate'])){
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
						}
						// Idempotent, self-healing: runs for the claim winner and
						// retries on later webhooks if fulfilment died mid-flight.
						$this->storefront_model->fulfilPaidOrder($order->id);
					}
				}
			} else {
				// Update paystack payment record
				$known = $this->paystack->get_payment_by_reference($reference);
				$this->paystack->update_payment_status($reference, $status, array(
					'channel' => $channel,
					'paid_at' => $paid_at
				));

				// Confirm only an exact local amount/currency match. A webhook is
				// not allowed to turn an under/over-payment into a paid sale.
				if($known && $this->paystack->payment_matches($reference, $amount, $data['currency'] ?? 'NGN')){
					$this->paystack->confirm_sales_payment($reference);
				}

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

	public function banks(){
		$this->permission_check_with_msg('paystack_settings');
		echo json_encode($this->paystack->get_banks());
	}

	public function resolve_payout_account(){
		$this->permission_check_with_msg('paystack_settings');
		$accountNumber = preg_replace('/\D+/', '', (string)$this->input->post('account_number', TRUE));
		$bankCode = trim((string)$this->input->post('bank_code', TRUE));
		if(strlen($accountNumber) !== 10 || $bankCode === ''){
			echo json_encode(['status'=>false, 'message'=>'Select a bank and enter a 10-digit account number.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		$result = $this->paystack->resolve_payout_account($accountNumber, $bankCode, (int)get_current_store_id());
		if(!$result['status']){
			echo json_encode(['status'=>false, 'message'=>$result['message'] ?? 'The account could not be verified. Check the bank and account number.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		echo json_encode(['status'=>true,
			'account_number'=>(string)($result['data']['account_number'] ?? $accountNumber),
			'account_name'=>(string)($result['data']['account_name'] ?? ''),
			'csrf_hash'=>$this->security->get_csrf_hash()]);
	}

	public function save_payout_account(){
		$this->permission_check_with_msg('paystack_settings');
		$store_id = (int)get_current_store_id();
		$result = $this->paystack->save_payout_account($store_id, array(
			'account_number' => $this->input->post('payout_account_number', TRUE),
			'bank_code' => $this->input->post('payout_bank_code', TRUE),
			'bank_name' => $this->input->post('payout_bank_name', TRUE),
			'email' => $this->input->post('payout_email', TRUE),
			'phone' => $this->input->post('payout_phone', TRUE),
			'terms_accepted' => (int)$this->input->post('terms_accepted', TRUE) === 1,
			'accepted_by' => (string)($this->data['CUR_USERNAME'] ?? ''),
			'accepted_by_user_id' => (int)($this->data['CUR_USERID'] ?? 0),
			'enabled' => function_exists('mp_is_central') && mp_is_central()
				? (int)$this->input->post('payout_enabled', TRUE) : 1,
		));
		$result['csrf_hash'] = $this->security->get_csrf_hash();
		echo json_encode($result);
	}
}
