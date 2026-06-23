<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack_model extends CI_Model {

	var $table = 'db_paystack_settings';

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

	public function generate_payment_link($amount, $email, $metadata = array(), $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$secret_key = $this->get_secret_key($store_id);
		if(empty($secret_key)) {
			return array('status' => false, 'message' => 'Paystack not configured');
		}

		$reference = 'MP_' . uniqid() . '_' . time();
		$post_data = array(
			'email' => $email,
			'amount' => round($amount * 100), // Paystack expects amount in kobo
			'reference' => $reference,
			'callback_url' => base_url('paystack/callback'),
			'metadata' => $metadata
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
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

		if($curl_error) {
			return array('status' => false, 'message' => 'CURL Error: ' . $curl_error);
		}

		$result = json_decode($response, true);
		if($http_code == 200 && isset($result['status']) && $result['status'] === true) {
			// Store the transaction record
			$link_data = array(
				'store_id' => $store_id,
				'customer_email' => $email,
				'amount' => $amount,
				'paystack_reference' => $reference,
				'paystack_access_code' => $result['data']['access_code'],
				'paystack_authorization_url' => $result['data']['authorization_url'],
				'payment_status' => 'pending',
				'meta_data' => json_encode($metadata)
			);
			$this->db->insert('db_paystack_payments', $link_data);

			return array(
				'status' => true,
				'reference' => $reference,
				'authorization_url' => $result['data']['authorization_url'],
				'access_code' => $result['data']['access_code']
			);
		} else {
			$message = isset($result['message']) ? $result['message'] : 'Unknown error from Paystack';
			return array('status' => false, 'message' => $message);
		}
	}

	public function verify_transaction($reference)
	{
		$secret_key = $this->get_secret_key();
		if(empty($secret_key)) {
			return array('status' => false, 'message' => 'Paystack not configured');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . $reference);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $secret_key,
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($http_code != 200) {
			return array('status' => false, 'message' => 'Failed to verify transaction');
		}

		$result = json_decode($response, true);
		if(isset($result['status']) && $result['status'] === true && isset($result['data'])) {
			$data = $result['data'];
			return array(
				'status' => true,
				'reference' => $data['reference'],
				'amount' => $data['amount'] / 100,
				'currency' => $data['currency'],
				'channel' => $data['channel'],
				'paid_at' => $data['paid_at'],
				'customer_email' => $data['customer']['email'],
				'gateway_response' => $data['gateway_response'],
				'payment_status' => $data['status'] // success, failed, abandoned
			);
		}
		return array('status' => false, 'message' => 'Transaction verification failed');
	}

	public function update_payment_status($reference, $status, $data = array())
	{
		$update = array(
			'payment_status' => $status,
			'updated_date' => date('Y-m-d H:i:s')
		);
		if(isset($data['channel'])) { $update['payment_channel'] = $data['channel']; }
		if(isset($data['paid_at'])) { $update['paid_at'] = $data['paid_at']; }

		$this->db->where('paystack_reference', $reference);
		return $this->db->update('db_paystack_payments', $update);
	}

	public function confirm_sales_payment($reference)
	{
		$paystack_payment = $this->db->where('paystack_reference', $reference)->get('db_paystack_payments')->row();
		if(!$paystack_payment) { return false; }

		$sales_id = $paystack_payment->sales_id;
		if(empty($sales_id)) { return false; }

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
		if($sales) {
			$this->sales_model->update_sales_payment_status($sales_id, $sales->customer_id);
		}

		return true;
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
