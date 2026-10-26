<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monnify_model extends CI_Model {

	var $table = 'db_monnify_settings';

	const SANDBOX_URL = 'https://sandbox.monnify.com';
	const LIVE_URL    = 'https://api.monnify.com';

	public function get_settings($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)->get($this->table)->row();
	}

	public function save_settings($data)
	{
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

	public function disbursements_enabled($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return ($settings && $settings->disbursements_enabled == 1);
	}

	public function is_test_mode($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return ($settings && $settings->test_mode == 1);
	}

	public function base_url($store_id = null)
	{
		return $this->is_test_mode($store_id) ? self::SANDBOX_URL : self::LIVE_URL;
	}

	public function get_api_key($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return $settings ? $settings->api_key : null;
	}

	public function get_secret_key($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return $settings ? $settings->secret_key : null;
	}

	public function get_contract_code($store_id = null)
	{
		$settings = $this->get_settings($store_id);
		return $settings ? $settings->contract_code : null;
	}

	/**
	 * OAuth2 login — Monnify issues a bearer token (expires ~1hr) against
	 * Basic base64(apiKey:secretKey). We cache it on the settings row.
	 */
	public function get_access_token($store_id = null, $force_refresh = false)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$settings = $this->get_settings($store_id);
		if(!$settings || empty($settings->api_key) || empty($settings->secret_key)) {
			return false;
		}

		// Return cached token if it still has >60s of life
		if(!$force_refresh && !empty($settings->access_token) && !empty($settings->token_expires_at)
			&& strtotime($settings->token_expires_at) > time() + 60) {
			return $settings->access_token;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->base_url($store_id) . '/api/v1/auth/login');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic ' . base64_encode($settings->api_key . ':' . $settings->secret_key),
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$result = json_decode($response, true);
		if($http_code == 200 && !empty($result['requestSuccessful']) && !empty($result['responseBody']['accessToken'])) {
			$token = $result['responseBody']['accessToken'];
			$expires_in = intval($result['responseBody']['expiresIn'] ?? 3600);
			$this->db->where('store_id', $store_id)->update($this->table, array(
				'access_token' => $token,
				'token_expires_at' => date('Y-m-d H:i:s', time() + $expires_in)
			));
			return $token;
		}
		return false;
	}

	/**
	 * Authenticated API request. Returns the decoded response array or
	 * array('status'=>false,'message'=>...) on transport failure.
	 */
	private function api_request($method, $endpoint, $data = null, $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$token = $this->get_access_token($store_id);
		if(!$token) {
			return array('status' => false, 'message' => 'Monnify authentication failed — check API Key and Secret Key');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->base_url($store_id) . $endpoint);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		if($data !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $token,
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if($curl_error) {
			return array('status' => false, 'message' => 'CURL Error: ' . $curl_error);
		}

		$result = json_decode($response, true);
		if(!is_array($result)) {
			return array('status' => false, 'message' => 'Invalid response from Monnify (HTTP ' . $http_code . ')');
		}

		if(empty($result['requestSuccessful'])) {
			$message = isset($result['responseMessage']) ? $result['responseMessage'] : 'Monnify request failed';
			return array('status' => false, 'message' => $message, 'response_code' => $result['responseCode'] ?? '', 'raw' => $result);
		}

		return array('status' => true, 'body' => $result['responseBody'] ?? array());
	}

	// ============================================================
	// COLLECTIONS
	// ============================================================

	/**
	 * Initialize a collection transaction. Returns checkout_url so the
	 * customer can pay by card / transfer / USSD on the hosted checkout.
	 */
	public function init_transaction($amount, $customer, $metadata = array(), $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$contract_code = $this->get_contract_code($store_id);
		if(empty($contract_code)) {
			return array('status' => false, 'message' => 'Monnify not configured — missing contract code');
		}

		$reference = 'MPM_' . uniqid() . '_' . time();

		// Monnify metaData values must be strings
		$meta = array();
		foreach($metadata as $k => $v) { $meta[$k] = (string)$v; }

		$post_data = array(
			'amount' => round(floatval($amount), 2),
			'customerName' => !empty($customer['name']) ? $customer['name'] : 'Walk-in Customer',
			'customerEmail' => !empty($customer['email']) ? $customer['email'] : 'customer@martpoint.local',
			'paymentReference' => $reference,
			'paymentDescription' => !empty($metadata['description']) ? $metadata['description'] : 'MartPoint Payment ' . $reference,
			'currencyCode' => 'NGN',
			'contractCode' => $contract_code,
			'redirectUrl' => base_url('monnify/callback'),
			'paymentMethods' => array('CARD', 'ACCOUNT_TRANSFER', 'USSD'),
			'metaData' => $meta
		);
		if(!empty($customer['phone'])) {
			$post_data['customerPhoneNumber'] = $customer['phone'];
		}

		$result = $this->api_request('POST', '/api/v1/merchant/transactions/init-transaction', $post_data, $store_id);
		if(!$result['status']) { return $result; }

		$body = $result['body'];
		$this->db->insert('db_monnify_payments', array(
			'store_id' => $store_id,
			'customer_name' => $post_data['customerName'],
			'customer_email' => $post_data['customerEmail'],
			'customer_phone' => $customer['phone'] ?? null,
			'amount' => $amount,
			'payment_reference' => $reference,
			'transaction_reference' => $body['transactionReference'] ?? null,
			'checkout_url' => $body['checkoutUrl'] ?? null,
			'payment_status' => 'PENDING',
			'meta_data' => json_encode($metadata)
		));

		return array(
			'status' => true,
			'payment_reference' => $reference,
			'transaction_reference' => $body['transactionReference'] ?? '',
			'checkout_url' => $body['checkoutUrl'] ?? ''
		);
	}

	/**
	 * Pay with bank transfer — turns an initialized transaction into a
	 * dynamic, time-bound NUBAN the customer transfers into.
	 */
	public function init_bank_transfer($payment_reference, $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$payment = $this->get_payment_by_reference($payment_reference);
		if(!$payment || empty($payment->transaction_reference)) {
			return array('status' => false, 'message' => 'Transaction not initialized');
		}

		$result = $this->api_request('POST', '/api/v1/merchant/bank-transfer/init-payment', array(
			'transactionReference' => $payment->transaction_reference
		), $store_id);
		if(!$result['status']) { return $result; }

		$body = $result['body'];
		$this->db->where('payment_reference', $payment_reference)->update('db_monnify_payments', array(
			'transfer_account_number' => $body['accountNumber'] ?? null,
			'transfer_bank_name' => $body['bankName'] ?? null,
			'transfer_bank_code' => $body['bankCode'] ?? null,
			'transfer_account_name' => $body['accountName'] ?? null,
			'transfer_account_expiry' => $body['expiryTime'] ?? ($body['expiresOn'] ?? null)
		));

		return array(
			'status' => true,
			'payment_reference' => $payment_reference,
			'account_number' => $body['accountNumber'] ?? '',
			'account_name' => $body['accountName'] ?? '',
			'bank_name' => $body['bankName'] ?? '',
			'bank_code' => $body['bankCode'] ?? '',
			'ussd_payment' => $body['ussdPayment'] ?? '',
			'expiry_time' => $body['expiryTime'] ?? ($body['expiresOn'] ?? '')
		);
	}

	/**
	 * Query transaction status (server-side verification).
	 */
	public function verify_transaction($payment_reference, $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$result = $this->api_request('GET', '/api/v2/merchant/transactions/query?paymentReference=' . urlencode($payment_reference), null, $store_id);
		if(!$result['status']) { return $result; }

		$body = $result['body'];
		return array(
			'status' => true,
			'payment_reference' => $body['paymentReference'] ?? $payment_reference,
			'transaction_reference' => $body['transactionReference'] ?? '',
			'amount' => floatval($body['amountPaid'] ?? ($body['amount'] ?? 0)),
			'currency' => $body['currencyCode'] ?? 'NGN',
			'payment_method' => $body['paymentMethod'] ?? '',
			'paid_on' => $body['paidOn'] ?? '',
			'payment_status' => $body['paymentStatus'] ?? 'PENDING'
		);
	}

	public function update_payment_status($payment_reference, $status, $data = array())
	{
		$update = array(
			'payment_status' => $status,
			'updated_date' => date('Y-m-d H:i:s')
		);
		if(isset($data['payment_method'])) { $update['payment_method'] = $data['payment_method']; }
		if(isset($data['paid_on'])) { $update['paid_at'] = $data['paid_on']; }
		if(isset($data['amount'])) { $update['amount_paid'] = $data['amount']; }
		if(isset($data['transaction_reference'])) { $update['transaction_reference'] = $data['transaction_reference']; }

		$this->db->where('payment_reference', $payment_reference);
		return $this->db->update('db_monnify_payments', $update);
	}

	/**
	 * Mark the linked db_salespayments row confirmed once Monnify reports PAID.
	 * Matches the 'moniepoint' payment-mode code case-insensitively; falls back
	 * to the newest unconfirmed moniepoint payment of the same amount when the
	 * sale was saved after the transfer account was generated.
	 */
	public function confirm_sales_payment($payment_reference)
	{
		$payment = $this->db->where('payment_reference', $payment_reference)->get('db_monnify_payments')->row();
		if(!$payment) { return false; }

		$confirmed_ref = array(
			'confirmation_status' => 1,
			'payment_reference' => $payment_reference,
			'confirmed_by' => 'Monnify Webhook',
			'confirmed_date' => date('Y-m-d H:i:s')
		);

		$this->db->where('sales_id', (int)$payment->sales_id)
				  ->where('store_id', $payment->store_id)
				  ->where('LOWER(payment_type)', 'moniepoint')
				  ->where('confirmation_status', 0)
				  ->update('db_salespayments', $confirmed_ref);

		// Fallback: sale saved after the transfer account was generated — the POS
		// auto-fills the reference input, so match on it before amount.
		if($this->db->affected_rows() == 0) {
			$this->db->where('store_id', $payment->store_id)
					  ->where('LOWER(payment_type)', 'moniepoint')
					  ->where('confirmation_status', 0)
					  ->where('payment_reference', $payment_reference)
					  ->update('db_salespayments', $confirmed_ref);
		}
		if($this->db->affected_rows() == 0) {
			$sp_match = $this->db->where('store_id', $payment->store_id)
					  ->where('LOWER(payment_type)', 'moniepoint')
					  ->where('confirmation_status', 0)
					  ->where('payment', $payment->amount)
					  ->order_by('id', 'DESC')
					  ->limit(1)
					  ->get('db_salespayments')->row();
			if($sp_match) {
				$this->db->where('id', $sp_match->id)->update('db_salespayments', $confirmed_ref);
			}
		}

		// Backfill sales_id on the monnify payment from whichever row matched
		if(empty($payment->sales_id)) {
			$sp = $this->db->where('payment_reference', $payment_reference)
						   ->where('store_id', $payment->store_id)
						   ->where('LOWER(payment_type)', 'moniepoint')
						   ->get('db_salespayments')->row();
			if($sp) {
				$this->db->where('id', $payment->id)->update('db_monnify_payments', array('sales_id' => $sp->sales_id));
				$payment->sales_id = $sp->sales_id;
			}
		}

		if(empty($payment->sales_id)) { return false; }

		$this->load->model('sales_model');
		$sales = $this->db->where('id', $payment->sales_id)->get('db_sales')->row();
		if($sales) {
			$this->sales_model->update_sales_payment_status($payment->sales_id, $sales->customer_id);
		}
		return true;
	}

	public function get_payment_by_reference($payment_reference)
	{
		return $this->db->where('payment_reference', $payment_reference)->get('db_monnify_payments')->row();
	}

	public function get_pending_payments($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)
						->where('payment_status', 'PENDING')
						->get('db_monnify_payments')
						->result();
	}

	// ============================================================
	// DISBURSEMENTS (single transfers)
	// ============================================================

	/** List NIP banks with codes. */
	public function get_banks($store_id = null)
	{
		$result = $this->api_request('GET', '/api/v1/sdk/transactions/banks', null, $store_id);
		if(!$result['status']) { return $result; }
		return array('status' => true, 'banks' => $result['body']);
	}

	/** Name enquiry — MUST run before initiating a transfer. */
	public function validate_account($account_number, $bank_code, $store_id = null)
	{
		$result = $this->api_request('GET', '/api/v1/disbursements/account/validate?accountNumber=' . urlencode($account_number) . '&bankCode=' . urlencode($bank_code), null, $store_id);
		if(!$result['status']) { return $result; }
		return array('status' => true, 'account' => $result['body']);
	}

	/**
	 * Initiate a single NIP transfer. $data keys:
	 * amount, narration, destination_bank_code, destination_account_number,
	 * destination_account_name, async, sender (optional array).
	 * Records the transfer locally regardless of outcome.
	 */
	public function initiate_transfer($data, $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$settings = $this->get_settings($store_id);
		if(empty($settings->wallet_account_number)) {
			return array('status' => false, 'message' => 'Monnify wallet account number not configured — set it in Monnify Settings');
		}

		$reference = 'MPT_' . uniqid() . '_' . time();
		$post_data = array(
			'amount' => round(floatval($data['amount']), 2),
			'reference' => $reference,
			'narration' => substr($data['narration'] ?? ('MartPoint Transfer ' . $reference), 0, 100),
			'destinationBankCode' => $data['destination_bank_code'],
			'destinationAccountNumber' => $data['destination_account_number'],
			'destinationAccountName' => $data['destination_account_name'],
			'currency' => 'NGN',
			'sourceAccountNumber' => $settings->wallet_account_number,
			'async' => true
		);
		if(!empty($data['sender']) && is_array($data['sender'])) {
			$post_data['senderInformation'] = $data['sender'];
		}

		$result = $this->api_request('POST', '/api/v2/disbursements/single', $post_data, $store_id);

		$body = $result['status'] ? $result['body'] : array();
		$this->db->insert('db_monnify_transfers', array(
			'store_id' => $store_id,
			'reference' => $reference,
			'amount' => $data['amount'],
			'narration' => $post_data['narration'],
			'destination_bank_code' => $data['destination_bank_code'],
			'destination_bank_name' => $data['destination_bank_name'] ?? null,
			'destination_account_number' => $data['destination_account_number'],
			'destination_account_name' => $data['destination_account_name'],
			'source_account_number' => $settings->wallet_account_number,
			'transfer_status' => $body['status'] ?? 'FAILED',
			'response_message' => $result['status'] ? ($body['responseDescription'] ?? '') : ($result['message'] ?? ''),
			'initiated_by' => $this->session->userdata('inv_username'),
			'meta_data' => isset($data['meta']) ? json_encode($data['meta']) : null
		));

		if(!$result['status']) { return $result; }

		return array(
			'status' => true,
			'reference' => $reference,
			'transfer_status' => $body['status'] ?? 'PENDING'
		);
	}

	/** Submit the emailed OTP to authorize a PENDING_AUTHORIZATION transfer. */
	public function authorize_transfer_otp($reference, $otp, $store_id = null)
	{
		$result = $this->api_request('POST', '/api/v2/disbursements/single/validate-otp', array(
			'reference' => $reference,
			'authorizationCode' => $otp
		), $store_id);

		if($result['status'] && isset($result['body']['status'])) {
			$this->update_transfer_status($reference, $result['body']['status']);
		}
		return $result;
	}

	public function resend_transfer_otp($reference, $store_id = null)
	{
		return $this->api_request('POST', '/api/v2/disbursements/single/resend-otp', array(
			'reference' => $reference
		), $store_id);
	}

	public function get_transfer_status($reference, $store_id = null)
	{
		$result = $this->api_request('GET', '/api/v2/disbursements/single/summary?reference=' . urlencode($reference), null, $store_id);
		if(!$result['status']) { return $result; }

		$body = $result['body'];
		$this->update_transfer_status($reference, $body['status'] ?? 'PENDING');
		return array(
			'status' => true,
			'reference' => $reference,
			'transfer_status' => $body['status'] ?? 'PENDING'
		);
	}

	public function update_transfer_status($reference, $status, $extra = array())
	{
		$update = array(
			'transfer_status' => $status,
			'updated_date' => date('Y-m-d H:i:s')
		);
		if(isset($extra['session_id'])) { $update['session_id'] = $extra['session_id']; }
		if(isset($extra['response_message'])) { $update['response_message'] = $extra['response_message']; }

		$this->db->where('reference', $reference);
		return $this->db->update('db_monnify_transfers', $update);
	}

	public function get_transfer_by_reference($reference)
	{
		return $this->db->where('reference', $reference)->get('db_monnify_transfers')->row();
	}

	// ============================================================
	// WEBHOOKS
	// ============================================================

	/**
	 * monnify-signature = SHA-512 HMAC of the raw request body, keyed with
	 * the client secret. Header only present on production notifications.
	 */
	public function verify_webhook_signature($input, $secret_key)
	{
		$signature = isset($_SERVER['HTTP_MONNIFY_SIGNATURE']) ? $_SERVER['HTTP_MONNIFY_SIGNATURE'] : '';
		if(empty($signature)) { return false; }
		$computed = hash_hmac('sha512', $input, $secret_key);
		return hash_equals($computed, $signature);
	}
}
