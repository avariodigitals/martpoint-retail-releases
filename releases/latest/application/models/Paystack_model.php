<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack_model extends CI_Model {

	var $table = 'db_paystack_settings';

	// Gateway base URL — overridable for sandbox/mocked testing via
	// MP_PAYSTACK_API env var. Production traffic always uses the default.
	private function api_base()
	{
		$base = getenv('MP_PAYSTACK_API');
		return $base ? rtrim($base, '/') : 'https://api.paystack.co';
	}

	public function get_settings($store_id = null)
	{
		// Provider credentials are centrally owned.  Keep the old per-store
		// table as a migration-safe fallback until the central account is saved.
		if($this->db->table_exists('db_payment_gateway_accounts')){
			$central = $this->db->where('provider', 'paystack')->get('db_payment_gateway_accounts')->row();
			if($central){ return $central; }
		}
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)->get($this->table)->row();
	}

	/**
	 * Resolve the store a signed webhook belongs to when the reference has
	 * no local order or payment link. Webhooks carry no session so
	 * get_current_store_id() is empty here — test each enabled account's
	 * webhook secret against the signature; the matching store owns the
	 * event. With no signature match (or no secrets configured) fall back
	 * to the first enabled account, preserving single-store behaviour.
	 */
	public function resolve_webhook_settings($input)
	{
		$sig = isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '';
		if($this->db->table_exists('db_payment_gateway_accounts')){
			$central = $this->db->where('provider', 'paystack')->where('enabled', 1)->get('db_payment_gateway_accounts')->row();
			if($central){ return $central; }
		}
		$rows = $this->db->where('enabled', 1)->get($this->table)->result();
		foreach($rows as $row){
			if(!empty($row->webhook_secret) && $sig !== ''
				&& hash_equals(hash_hmac('sha512', $input, $row->webhook_secret), $sig)){
				return $row;
			}
		}
		return !empty($rows) ? $rows[0] : null;
	}

	public function save_settings($data)
	{
		if($this->db->table_exists('db_payment_gateway_accounts')){
			$data['provider'] = 'paystack';
			$existing = $this->db->where('provider', 'paystack')->get('db_payment_gateway_accounts')->row();
			if($existing){
				return $this->db->where('provider', 'paystack')->update('db_payment_gateway_accounts', $data);
			}
			return $this->db->insert('db_payment_gateway_accounts', $data);
		}
		$store_id = get_current_store_id();
		$exists = $this->db->where('store_id', $store_id)->get($this->table)->num_rows();
		if($exists > 0) {
			$this->db->where('store_id', $store_id);
			return $this->db->update($this->table, $data);
		} else {
			$data['store_id'] = $store_id;
			return $this->db->insert($this->table, $data);
		}
	}

	public function is_enabled($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return ($settings && $settings->enabled == 1);
	}

	public function get_secret_key($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return $settings ? $settings->secret_key : null;
	}

	public function get_public_key($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return $settings ? $settings->public_key : null;
	}

	public function is_test_mode($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return ($settings && $settings->test_mode == 1);
	}

	private function api_request($method, $endpoint, $data = null, $store_id = null)
	{
		$secret_key = $this->get_secret_key($store_id);
		if(empty($secret_key)){
			return array('status' => false, 'message' => 'Paystack not configured');
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_base() . $endpoint);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		if($data !== null){ curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); }
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $secret_key,
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);
		if($curl_error){ return array('status'=>false, 'message'=>'CURL Error: '.$curl_error); }
		$result = json_decode($response, true);
		if(!is_array($result)){
			return array('status'=>false, 'message'=>'Invalid response from Paystack (HTTP '.$http_code.')');
		}
		if($http_code < 200 || $http_code >= 300 || empty($result['status'])){
			return array('status'=>false, 'message'=>$result['message'] ?? 'Paystack request failed', 'raw'=>$result);
		}
		return array('status'=>true, 'data'=>$result['data'] ?? array(), 'raw'=>$result);
	}

	public function generate_payment_link($amount, $email, $metadata = array(), $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$reference = 'MP_' . uniqid() . '_' . time();
		$post_data = array(
			'email' => $email,
			'amount' => round($amount * 100), // Paystack expects amount in kobo
			'reference' => $reference,
			'callback_url' => base_url('paystack/callback'),
			'metadata' => $metadata
		);
		$split = $this->get_split_config($amount, $store_id);
		if(!$split['status']){ return $split; }
		foreach(array('subaccount','transaction_charge','bearer') as $key){
			if(array_key_exists($key, $split['config'])){ $post_data[$key] = $split['config'][$key]; }
		}

		$result = $this->api_request('POST', '/transaction/initialize', $post_data, $store_id);
		if($result['status']) {
			$data = $result['data'];
			// Store the transaction record
			$link_data = array(
				'store_id' => $store_id,
				'customer_email' => $email,
				'amount' => $amount,
				'platform_fee' => $split['platform_fee'],
				'paystack_reference' => $reference,
				'paystack_access_code' => $data['access_code'] ?? null,
				'paystack_authorization_url' => $data['authorization_url'] ?? null,
				'payment_status' => 'pending',
				'meta_data' => json_encode($metadata)
			);
			$this->db->insert('db_paystack_payments', $link_data);

			return array(
				'status' => true,
				'reference' => $reference,
				'authorization_url' => $data['authorization_url'] ?? '',
				'access_code' => $data['access_code'] ?? '',
				'platform_fee' => $split['platform_fee']
			);
		} else {
			$message = isset($result['message']) ? $result['message'] : 'Unknown error from Paystack';
			return array('status' => false, 'message' => $message);
		}
	}

	/** Return the safe split fields for server- or InlineJS-initialized checkout. */
	public function get_split_config($amount, $store_id = null)
	{
		$account = $this->get_payout_account($store_id);
		$settings = $this->get_settings($store_id);
		$fee_type = strtolower((string)($settings->platform_fee_type ?? 'none'));
		$fee_value = max(0, (float)($settings->platform_fee_value ?? 0));
		$platform_fee = $fee_type === 'percentage' ? round($amount * $fee_value / 100, 2) : ($fee_type === 'flat' ? $fee_value : 0);
		if($platform_fee > $amount){
			return array('status'=>false, 'message'=>'The configured platform fee cannot exceed the payment amount');
		}
		$config = array();
		if($account && !empty($account->provider_subaccount_code) && (int)$account->enabled === 1){
			$config['subaccount'] = $account->provider_subaccount_code;
			if($fee_type === 'flat' && $platform_fee > 0){ $config['transaction_charge'] = (int)round($platform_fee * 100); }
			if(($settings->fee_bearer ?? 'platform') === 'merchant'){ $config['bearer'] = 'subaccount'; }
		}
		return array('status'=>true, 'config'=>$config, 'platform_fee'=>$platform_fee, 'payout_account'=>$account);
	}

	public function get_payout_account($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		if(!$this->db->table_exists('db_payment_subaccounts')){ return null; }
		return $this->db->where('provider', 'paystack')->where('store_id', (int)$store_id)
			->get('db_payment_subaccounts')->row();
	}

	public function get_banks($store_id = null)
	{
		$result = $this->api_request('GET', '/bank?country=nigeria&perPage=100', null, $store_id);
		return $result['status'] ? array('status'=>true, 'banks'=>$result['data']) : $result;
	}

	public function resolve_payout_account($account_number, $bank_code, $store_id = null)
	{
		return $this->api_request('GET', '/bank/resolve?account_number='.rawurlencode($account_number).'&bank_code='.rawurlencode($bank_code), null, $store_id);
	}

	/** Validate and register one merchant bank account as the central subaccount. */
	public function save_payout_account($store_id, $data)
	{
		$store_id = (int)$store_id;
		$account_number = preg_replace('/\D+/', '', (string)($data['account_number'] ?? ''));
		$bank_code = trim((string)($data['bank_code'] ?? ''));
		$email = trim((string)($data['email'] ?? ''));
		if(strlen($account_number) !== 10 || $bank_code === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			return array('status'=>false, 'message'=>'A valid bank, 10-digit account number and payout email are required');
		}
		$resolved = $this->resolve_payout_account($account_number, $bank_code, $store_id);
		if(!$resolved['status']){ return array('status'=>false, 'message'=>$resolved['message'] ?? 'Could not verify bank account'); }
		$resolved_name = trim((string)($resolved['data']['account_name'] ?? ''));
		$store = $this->db->select('store_name')->where('id', $store_id)->get('db_store')->row();
		$business_name = trim((string)($data['business_name'] ?? ($store->store_name ?? 'MartPoint Merchant')));
		$existing = $this->get_payout_account($store_id);
		$fee_type = strtolower((string)($this->get_settings($store_id)->platform_fee_type ?? 'none'));
		$percentage_charge = $fee_type === 'percentage' ? (float)($this->get_settings($store_id)->platform_fee_value ?? 0) : 0;
		$payload = array(
			'business_name' => $business_name,
			'description' => 'MartPoint merchant payout account',
			'primary_contact_email' => $email,
			'primary_contact_name' => $resolved_name,
			'primary_contact_phone' => trim((string)($data['phone'] ?? '')),
			'percentage_charge' => $percentage_charge,
			'metadata' => json_encode(array('martpoint_store_id'=>$store_id))
		);
		if($existing && !empty($existing->provider_subaccount_code)){
			$payload['bank_code'] = $bank_code;
			$payload['account_number'] = $account_number;
			$payload['active'] = !empty($data['enabled']);
			$api = $this->api_request('PUT', '/subaccount/'.rawurlencode($existing->provider_subaccount_code), $payload, $store_id);
		} else {
			$payload['settlement_bank'] = $bank_code;
			$payload['account_number'] = $account_number;
			$api = $this->api_request('POST', '/subaccount', $payload, $store_id);
		}
		if(!$api['status']){ return array('status'=>false, 'message'=>$api['message'] ?? 'Could not register payout account'); }
		$provider = $api['data'];
		$sub_code = $provider['subaccount_code'] ?? ($existing->provider_subaccount_code ?? null);
		$verified = !empty($provider['is_verified']);
		$row = array(
			'provider' => 'paystack', 'store_id' => $store_id, 'business_name' => $business_name,
			'contact_email' => $email, 'contact_phone' => trim((string)($data['phone'] ?? '')),
			'bank_code' => $bank_code, 'bank_name' => trim((string)($data['bank_name'] ?? '')),
			'account_number' => $account_number, 'account_name' => $provider['account_name'] ?? $resolved_name,
			'provider_subaccount_code' => $sub_code, 'provider_account_id' => isset($provider['id']) ? (string)$provider['id'] : null,
			'status' => $verified ? 'ACTIVE' : 'PENDING_VERIFICATION', 'provider_verified' => $verified ? 1 : 0,
			'enabled' => !empty($data['enabled']) ? 1 : 0, 'last_error' => null,
			'provider_payload' => json_encode($provider)
		);
		if($existing){ $ok = $this->db->where('id', $existing->id)->update('db_payment_subaccounts', $row); }
		else { $ok = $this->db->insert('db_payment_subaccounts', $row); }
		return $ok ? array('status'=>true, 'message'=>'Payout account saved', 'account'=>$this->get_payout_account($store_id))
			: array('status'=>false, 'message'=>'Payout account was verified but could not be saved locally');
	}

	public function verify_transaction($reference, $store_id = null)
	{
		$result = $this->api_request('GET', '/transaction/verify/' . rawurlencode($reference), null, $store_id);
		if($result['status'] && !empty($result['data'])) {
			$data = $result['data'];
			return array(
				'status' => true,
				'reference' => $data['reference'] ?? $reference,
				'amount' => ((float)($data['amount'] ?? 0)) / 100,
				'currency' => $data['currency'] ?? 'NGN',
				'channel' => $data['channel'] ?? '',
				'paid_at' => $data['paid_at'] ?? null,
				'customer_email' => $data['customer']['email'] ?? '',
				'gateway_response' => $data['gateway_response'] ?? '',
				'payment_status' => $data['status'] ?? 'pending'
			);
		}
		return array('status' => false, 'message' => $result['message'] ?? 'Transaction verification failed');
	}

	public function update_payment_status($reference, $status, $data = array())
	{
		$update = array(
			'payment_status' => $status,
			'updated_date' => date('Y-m-d H:i:s')
		);
		if(isset($data['channel'])) { $update['payment_channel'] = $data['channel']; }
		if(isset($data['paid_at'])) { $update['paid_at'] = $data['paid_at']; }
		if(isset($data['amount'])) { $update['amount_paid'] = $data['amount']; }

		$this->db->where('paystack_reference', $reference);
		return $this->db->update('db_paystack_payments', $update);
	}

	public function payment_matches($reference, $amount, $currency = 'NGN')
	{
		$payment = $this->get_payment_by_reference($reference);
		if(!$payment || abs((float)$payment->amount - (float)$amount) > 0.01){ return false; }
		return empty($currency) || empty($payment->currency) || strtoupper($payment->currency) === strtoupper($currency);
	}

	public function confirm_sales_payment($reference)
	{
		$paystack_payment = $this->db->where('paystack_reference', $reference)->get('db_paystack_payments')->row();
		if(!$paystack_payment) { return false; }

		$sales_id = $paystack_payment->sales_id;
		if(empty($sales_id)) { return false; }

		$this->db->trans_begin();
		try {
			// Update the salespayment confirmation status
			$this->db->where('sales_id', $sales_id)
					  ->where('payment_type', 'Paystack')
					  ->where('confirmation_status', 0)
					  ->update('db_salespayments', array(
						  'confirmation_status' => 1,
						  'payment_reference' => $reference,
						  'confirmed_by' => 'Paystack Webhook',
						  'confirmed_date' => date('Y-m-d H:i:s')
					  ));

			// Update sales payment status
			$this->load->model('sales_model');
			$sales = $this->db->where('id', $sales_id)->get('db_sales')->row();
			if(!$sales){
				$this->db->trans_rollback();
				return false;
			}
			if(!$this->sales_model->update_sales_payment_status($sales_id, $sales->customer_id)){
				$this->db->trans_rollback();
				return false;
			}
			if($this->db->trans_status() === false){
				$this->db->trans_rollback();
				return false;
			}
			$this->db->trans_commit();
			return true;
		} catch(Throwable $e) {
			$this->db->trans_rollback();
			log_message('error', 'Paystack confirmation failed for ' . $reference . ': ' . $e->getMessage());
			return false;
		}
	}

	public function get_payment_by_reference($reference)
	{
		return $this->db->where('paystack_reference', $reference)->get('db_paystack_payments')->row();
	}

	public function get_pending_payments($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)
						->where('payment_status', 'pending')
						->get('db_paystack_payments')
						->result();
	}

	public function verify_webhook_signature($input, $secret_key)
	{
		$signature = isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '';
		if(empty($signature)) { return false; }
		$computed = hash_hmac('sha512', $input, $secret_key);
		return hash_equals($computed, $signature);
	}
}
