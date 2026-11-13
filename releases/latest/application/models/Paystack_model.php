<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paystack_model extends CI_Model {

	var $table = 'db_paystack_settings';
	protected $fleet_credentials_cleared = false;
	protected $fleet_settings_loaded = false;
	protected $fleet_settings_cache = null;

	// Gateway base URL — overridable for sandbox/mocked testing via
	// MP_PAYSTACK_API env var. Production traffic always uses the default.
	private function api_base()
	{
		$base = getenv('MP_PAYSTACK_API');
		return $base ? rtrim($base, '/') : 'https://api.paystack.co';
	}

	public function get_settings($store_id = null)
	{
		// Fleets read safe settings from Central and proxy provider API calls
		// through it. Provider secrets remain on Central instead of being copied
		// into each fleet database.
		if(function_exists('mp_is_central') && !mp_is_central()){
			if($this->fleet_settings_loaded) return $this->fleet_settings_cache;
			$this->fleet_settings_loaded = true;
			if(empty($store_id)) { $store_id = get_current_store_id(); }
			$this->load->library('messaging_client');
			$result = $this->messaging_client->paystackSettings((int)$store_id);
			if(empty($result['success']) || !is_array($result['settings'] ?? null)) return null;
			if(!empty($result['settings']['configured'])) $this->clearFleetProviderCredentials();
			$this->fleet_settings_cache = (object)$result['settings'];
			return $this->fleet_settings_cache;
		}
		if($this->db->table_exists('db_payment_gateway_accounts')){
			$central = $this->db->where('provider', 'paystack')->get('db_payment_gateway_accounts')->row();
			if($central){ return $central; }
		}
		if(!function_exists('mp_is_central') || !mp_is_central()) return null;
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		if(!$this->db->table_exists($this->table)) { return null; }
		return $this->db->where('store_id', $store_id)->get($this->table)->row();
	}

	/** Remove keys left behind by older Fleet Manager credential pushes. */
	private function clearFleetProviderCredentials()
	{
		if($this->fleet_credentials_cleared) return;
		$this->fleet_credentials_cleared = true;
		foreach(array('db_payment_gateway_accounts','db_paystack_settings') as $table){
			if(!$this->db->table_exists($table)) continue;
			$clear=array();
			foreach(array('secret_key','public_key','webhook_secret') as $column){
				if($this->db->field_exists($column,$table)) $clear[$column]='';
			}
			if($this->db->field_exists('enabled',$table)) $clear['enabled']=0;
			if(!$clear) continue;
			if($table==='db_payment_gateway_accounts') $this->db->where('provider','paystack')->update($table,$clear);
			else $this->db->update($table,$clear);
		}
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
		if(!function_exists('mp_is_central') || !mp_is_central()) return false;
		if($this->db->table_exists('db_payment_gateway_accounts')){
			$table = 'db_payment_gateway_accounts';
			$data['provider'] = 'paystack';
			$existing = $this->db->where('provider', 'paystack')->get('db_payment_gateway_accounts')->row();
		} else {
			$table = $this->table;
			$store_id = get_current_store_id();
			$existing = $this->db->where('store_id', $store_id)->get($table)->row();
			if(!$existing) $data['store_id'] = $store_id;
		}
		// Older Central databases may have the gateway table from migration
		// 4.0.9.264 but not the additive fee columns from 4.0.9.265 yet. Save
		// credentials and mode using the columns that actually exist so bank
		// lookup can be tested without a fee-schema mismatch blocking the save.
		foreach(array_keys($data) as $column){
			if(!$this->db->field_exists($column, $table)) unset($data[$column]);
		}
		if($existing){
			$key = $table === 'db_payment_gateway_accounts' ? 'provider' : 'store_id';
			$value = $table === 'db_payment_gateway_accounts' ? 'paystack' : get_current_store_id();
			return $this->db->where($key, $value)->update($table, $data);
		}
		return $this->db->insert($table, $data);
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
		if(function_exists('mp_is_central') && !mp_is_central()){
			if(empty($store_id)) { $store_id = get_current_store_id(); }
			$this->load->library('messaging_client');
			$result = $this->messaging_client->paystackRequest((int)$store_id, $method, $endpoint, $data);
			if(empty($result['success'])) return array('status'=>false, 'message'=>$result['message'] ?? 'Central could not process the MartPoint Pay request.');
			return array('status'=>!empty($result['status']), 'data'=>$result['data'] ?? array(),
				'raw'=>$result['raw'] ?? array(), 'message'=>$result['message'] ?? null);
		}
		$secret_key = $this->get_secret_key($store_id);
		if(empty($secret_key)){
			return array('status' => false, 'message' => 'MartPoint Pay is not configured');
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
			return array('status'=>false, 'message'=>'The payment provider returned an invalid response (HTTP '.$http_code.')');
		}
		if($http_code < 200 || $http_code >= 300 || empty($result['status'])){
			return array('status'=>false, 'message'=>$result['message'] ?? 'The payment provider request failed', 'raw'=>$result);
		}
		return array('status'=>true, 'data'=>$result['data'] ?? array(), 'raw'=>$result);
	}

	/** Central-only provider request entry point used by the signed fleet relay. */
	public function central_api_request($method, $endpoint, $data = null, $store_id = null)
	{
		if(!function_exists('mp_is_central') || !mp_is_central()){
			return array('status'=>false, 'message'=>'Paystack provider requests must be handled by Central.');
		}
		return $this->api_request($method, $endpoint, $data, $store_id);
	}

	/** Central messaging purchases settle to MartPoint, without merchant payout splits. */
	public function initialize_messaging_payment($reference, $amount, $email, $callback, $store_id)
	{
		return $this->api_request('POST', '/transaction/initialize', array(
			'reference'=>$reference, 'amount'=>(int)round($amount*100), 'currency'=>'NGN',
			'email'=>$email, 'callback_url'=>$callback, 'metadata'=>array('purpose'=>'martpoint_messaging')
		), $store_id);
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
			$message = isset($result['message']) ? $result['message'] : 'The payment provider returned an unknown error';
			return array('status' => false, 'message' => $message);
		}
	}

	/**
	 * Hosted checkout for an online order that is being paid later, from a link
	 * in an SMS or email. Unlike generate_payment_link (which invents its own
	 * reference and settles through db_paystack_payments for the counter), this
	 * keeps the storefront contract: reference = order_code and the callback is
	 * the online-order verifier, so the existing verify path, the webhook and the
	 * amount check all recognise the payment without a new code path.
	 */
	public function initialize_online_order_checkout($order, $callback_url)
	{
		$store_id = (int)$order->store_id;
		$settings = $this->get_settings($store_id);
		if(!$settings || empty($settings->public_key)){
			return array('status'=>false, 'message'=>'This store has no card payment set up yet.');
		}
		$amount = (float)$order->grand_total;
		if($amount <= 0){
			return array('status'=>false, 'message'=>'There is nothing left to pay on this order.');
		}
		$split = $this->get_split_config($amount, $store_id);
		if(!$split['status']){ return $split; }
		$email = filter_var((string)($order->customer_email ?? ''), FILTER_VALIDATE_EMAIL)
			? $order->customer_email
			: 'customer@' . preg_replace('/[^a-z0-9-]/', '', strtolower((string)($order->order_code ?: 'store'))) . '.martpoint.app';
		$post_data = array(
			'email' => $email,
			'amount' => (int)round($amount * 100),
			'reference' => $order->order_code,
			'callback_url' => $callback_url,
			'metadata' => array('order_id' => (int)$order->id, 'store_id' => $store_id, 'source' => 'rx_invoice_link'),
		);
		foreach(array('subaccount','transaction_charge','bearer') as $key){
			if(array_key_exists($key, $split['config'])){ $post_data[$key] = $split['config'][$key]; }
		}
		$result = $this->api_request('POST', '/transaction/initialize', $post_data, $store_id);
		if(!$result['status']){
			return array('status'=>false, 'message'=>$result['message'] ?? 'The card payment page could not be opened.');
		}
		return array('status'=>true,
			'authorization_url' => (string)($result['data']['authorization_url'] ?? ''),
			'reference' => $order->order_code,
			'platform_fee' => $split['platform_fee']);
	}

	/** Return the safe split fields for server- or InlineJS-initialized checkout. */
	public function get_split_config($amount, $store_id = null)
	{
		$account = $this->get_payout_account($store_id);
		$settings = $this->get_settings($store_id);
		$fee_percent = max(0, (float)($settings->platform_fee_percent ?? 0));
		$fee_fixed = max(0, (float)($settings->platform_fee_fixed ?? 0));
		// Older installs may not have the composite fee columns yet. Preserve
		// their existing percentage/flat configuration until the migration runs.
		if(!isset($settings->platform_fee_percent) && !isset($settings->platform_fee_fixed)){
			$legacy_type = strtolower((string)($settings->platform_fee_type ?? 'none'));
			$legacy_value = max(0, (float)($settings->platform_fee_value ?? 0));
			$fee_percent = $legacy_type === 'percentage' ? $legacy_value : 0;
			$fee_fixed = $legacy_type === 'flat' ? $legacy_value : 0;
		}
		$platform_fee = round(($amount * $fee_percent / 100) + $fee_fixed, 2);
		if($platform_fee > $amount){
			return array('status'=>false, 'message'=>'The configured platform fee cannot exceed the payment amount');
		}
		$config = array();
		if($account && !empty($account->provider_subaccount_code) && (int)$account->enabled === 1){
			$config['subaccount'] = $account->provider_subaccount_code;
			if($platform_fee > 0){ $config['transaction_charge'] = (int)round($platform_fee * 100); }
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

	/** Refresh the locally displayed verification state from Paystack's current record. */
	public function refresh_payout_account_status($store_id = null)
	{
		$account = $this->get_payout_account($store_id);
		if(!$account || empty($account->provider_subaccount_code)
			|| !$this->db->table_exists('db_payment_subaccounts')){ return $account; }

		$result = $this->api_request('GET', '/subaccount/'.rawurlencode($account->provider_subaccount_code), null, $store_id);
		if(empty($result['status']) || !is_array($result['data'] ?? null)
			|| !array_key_exists('is_verified', $result['data'])){ return $account; }

		$provider = $result['data'];
		$verified = filter_var($provider['is_verified'], FILTER_VALIDATE_BOOLEAN);
		$this->db->where('id', (int)$account->id)->where('provider', 'paystack')
			->where('store_id', (int)($store_id ?: get_current_store_id()))
			->update('db_payment_subaccounts', array(
				'status' => $verified ? 'ACTIVE' : 'PENDING_VERIFICATION',
				'provider_verified' => $verified ? 1 : 0,
				'provider_payload' => json_encode($provider),
			));
		return $this->get_payout_account($store_id);
	}

	/** True when this fleet accepted the currently configured MartPoint Pay fees. */
	public function has_current_paystack_terms_acceptance($store_id)
	{
		if(!$this->db->table_exists('db_payment_subaccount_acceptances')){ return false; }
		$acceptance = $this->db->where('provider', 'paystack')->where('store_id', (int)$store_id)
			->order_by('id', 'DESC')->limit(1)->get('db_payment_subaccount_acceptances')->row();
		$settings = $this->get_settings($store_id);
		if(!$acceptance || !$settings || empty($settings->public_key)){ return false; }
		$percent = max(0, (float)($settings->platform_fee_percent ?? 0));
		$fixed = max(0, (float)($settings->platform_fee_fixed ?? 0));
		if(!isset($settings->platform_fee_percent) && !isset($settings->platform_fee_fixed)){
			$type = strtolower((string)($settings->platform_fee_type ?? 'none'));
			$value = max(0, (float)($settings->platform_fee_value ?? 0));
			$percent = $type === 'percentage' ? $value : 0;
			$fixed = $type === 'flat' ? $value : 0;
		}
		return $acceptance->terms_version === 'martpoint-pay-fees-2026-11-v1'
			&& abs((float)$acceptance->platform_fee_percent - $percent) < 0.00005
			&& abs((float)$acceptance->platform_fee_fixed - $fixed) < 0.005
			&& $acceptance->fee_bearer === (($settings->fee_bearer ?? 'platform') === 'merchant' ? 'merchant' : 'platform');
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
		$enabled = !empty($data['enabled']);
		$current_terms_accepted = $this->has_current_paystack_terms_acceptance($store_id);
		if($enabled && !$current_terms_accepted && empty($data['terms_accepted'])){
			return array('status'=>false, 'message'=>'Review and accept the MartPoint Pay fees before activating payments');
		}
		if($enabled && !$current_terms_accepted && !empty($data['terms_accepted'])
			&& !$this->record_paystack_terms_acceptance($store_id, $data['accepted_by'] ?? '', (int)($data['accepted_by_user_id'] ?? 0))){
			return array('status'=>false, 'message'=>'Your acceptance could not be saved, so MartPoint Pay was not activated. Please retry.');
		}
		$resolved = $this->resolve_payout_account($account_number, $bank_code, $store_id);
		if(!$resolved['status']){ return array('status'=>false, 'message'=>$resolved['message'] ?? 'Could not verify bank account'); }
		$resolved_name = trim((string)($resolved['data']['account_name'] ?? ''));
		$store = $this->db->select('store_name')->where('id', $store_id)->get('db_store')->row();
		$business_name = trim((string)($data['business_name'] ?? ($store->store_name ?? 'MartPoint Merchant')));
		$existing = $this->get_payout_account($store_id);
		$gateway_settings = $this->get_settings($store_id);
		$percentage_charge = isset($gateway_settings->platform_fee_percent)
			? max(0, (float)$gateway_settings->platform_fee_percent)
			: (strtolower((string)($gateway_settings->platform_fee_type ?? 'none')) === 'percentage'
				? max(0, (float)($gateway_settings->platform_fee_value ?? 0)) : 0);
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
		if(!$ok){ return array('status'=>false, 'message'=>'Payout account was verified but could not be saved locally'); }
		$account = $this->get_payout_account($store_id);
		// A shared Paystack integration has one webhook URL. Register each enabled
		// merchant subaccount with Next Admin so its webhook can be routed back to
		// the fleet that owns this store.
		if($this->db->table_exists('db_payment_gateway_accounts') && $account && !empty($account->provider_subaccount_code)){
			try {
				$this->load->library('messaging_client');
				$route = $this->messaging_client->registerPaystackPayoutRoute(
					$store_id,
					$account->provider_subaccount_code,
					(int)$account->enabled === 1
				);
				if(empty($route['success'])) throw new RuntimeException($route['message'] ?? 'Central could not confirm the payout route.');
			} catch(Throwable $e) {
				log_message('error', 'Paystack Central payout route registration failed: '.mb_substr(strip_tags($e->getMessage()), 0, 300));
				if((int)$account->enabled === 1){
					$this->db->where('id', $account->id)->update('db_payment_subaccounts', array('enabled'=>0, 'last_error'=>'Central webhook route registration is pending.'));
					return array('status'=>false, 'message'=>'Account details were saved, but Central webhook routing is not ready. The payout account remains disabled until Central confirms the route.');
				}
				return array('status'=>false, 'message'=>'Could not clear the previous Central webhook route. Retry this save.');
			}
		}
		return array('status'=>true, 'message'=>'Payout account saved', 'account'=>$this->get_payout_account($store_id));
	}

	/** Save an immutable snapshot of the fee terms accepted at activation. */
	private function record_paystack_terms_acceptance($store_id, $accepted_by, $accepted_by_user_id)
	{
		if(!$this->db->table_exists('db_payment_subaccount_acceptances')){ return false; }
		$settings = $this->get_settings($store_id);
		if(!$settings){ return false; }
		$percent = max(0, (float)($settings->platform_fee_percent ?? 0));
		$fixed = max(0, (float)($settings->platform_fee_fixed ?? 0));
		if(!isset($settings->platform_fee_percent) && !isset($settings->platform_fee_fixed)){
			$type = strtolower((string)($settings->platform_fee_type ?? 'none'));
			$value = max(0, (float)($settings->platform_fee_value ?? 0));
			$percent = $type === 'percentage' ? $value : 0;
			$fixed = $type === 'flat' ? $value : 0;
		}
		$fee_bearer = ($settings->fee_bearer ?? 'platform') === 'merchant' ? 'merchant' : 'platform';
		$example_amount = 10000.00;
		$example_platform_fee = round($example_amount * $percent / 100 + $fixed, 2);
		$terms = 'MartPoint Pay platform charge is '.number_format($percent, 2, '.', '').'% plus NGN '.number_format($fixed, 2, '.', '').' per successful online payment, deducted from the fleet settlement. On NGN 10,000, this platform charge is NGN '.number_format($example_platform_fee, 2, '.', '').'. Payment processing fees are borne by '.($fee_bearer === 'merchant' ? 'the fleet' : 'MartPoint').'. No separate payment surcharge is added to the customer checkout.';
		return $this->db->insert('db_payment_subaccount_acceptances', array(
			'provider'=>'paystack', 'store_id'=>(int)$store_id,
			'terms_version'=>'martpoint-pay-fees-2026-11-v1',
			'platform_fee_percent'=>$percent, 'platform_fee_fixed'=>$fixed,
			'fee_bearer'=>$fee_bearer, 'example_amount'=>$example_amount,
			'example_platform_fee'=>$example_platform_fee,
			'terms_snapshot'=>$terms, 'accepted_by'=>substr((string)$accepted_by, 0, 64),
			'accepted_by_user_id'=>$accepted_by_user_id > 0 ? $accepted_by_user_id : null,
			'accepted_at'=>date('Y-m-d H:i:s'),
		));
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

	/** Initiate a full or partial refund using a stable, caller-owned reference. */
	public function refund_transaction($transactionReference,$amountMinor,$refundReference,$store_id=null,$merchantNote=''){
		$payload=['transaction'=>(string)$transactionReference,'reference'=>(string)$refundReference,'merchant_note'=>mb_substr((string)$merchantNote,0,255)];
		if((int)$amountMinor>0)$payload['amount']=(int)$amountMinor;
		return $this->api_request('POST','/refund',$payload,$store_id);
	}
	public function get_refund_by_reference($refundReference,$store_id=null){
		$result=$this->api_request('GET','/refund?reference='.rawurlencode((string)$refundReference),null,$store_id);
		if(!$result['status'])return $result;
		$data=$result['data']??[];
		if(isset($data[0]))$data=$data[0];
		return ['status'=>true,'data'=>$data];
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
