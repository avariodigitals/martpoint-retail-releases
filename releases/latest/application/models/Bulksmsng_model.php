<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * BulkSMSNigeria SMS provider.
 *
 * API v2 contract (verified against the vendor's own Postman collection):
 *   Base URL : https://www.bulksmsnigeria.com/api
 *   Send     : POST /v2/sms
 *   Auth     : Authorization: Bearer <api_token>
 *   Body     : { "from": <sender_id>, "to": <string|string[]>, "body": <message>,
 *                "gateway"?: "mtn"|"airtel"|"glo"|"9mobile",
 *                "schedule_time"?: "Y-m-d H:i:s" }
 *
 * Notes that matter:
 *  - There is NO `api_token` in the body; auth is the bearer header only.
 *  - The message field is `body`, NOT `message`.
 *  - `to` accepts a single number, a comma-separated list, or an array. Bulk is
 *    therefore ONE call, not a loop — `send_bulk()` exploits that.
 *  - Credentials are per store. `base_url` is stored (not hardcoded) so the
 *    endpoint can be corrected from the UI without a code change, and can be
 *    pointed at a mock via the MP_BULKSMSNG_API environment variable.
 */
class Bulksmsng_model extends CI_Model {

	const DEFAULT_BASE = 'https://www.bulksmsnigeria.com/api';
	const TIMEOUT      = 80;

	/** Base URL resolution order: env override -> stored setting -> default. */
	private function _base_url($creds = null){
		$env = getenv('MP_BULKSMSNG_API');
		if($env){ return rtrim($env, '/'); }
		if($creds && !empty($creds->base_url)){ return rtrim($creds->base_url, '/'); }
		return self::DEFAULT_BASE;
	}

	public function get_credentials($store_id = null){
		$store_id = $store_id ?: (function_exists('get_current_store_id') ? get_current_store_id() : 0);
		if(!$store_id || !$this->db->table_exists('db_bulksmsng')){ return null; }
		return $this->db->where('store_id', $store_id)->get('db_bulksmsng')->row();
	}

	/**
	 * Normalise a Nigerian number to the 234XXXXXXXXXX form the API prefers.
	 * Accepts 08012345678, +2348012345678, 2348012345678, 8012345678.
	 * Returns '' when the input cannot be a usable number.
	 */
	public function normalize_phone($number){
		$digits = preg_replace('/[^0-9]/', '', (string)$number);
		if($digits === ''){ return ''; }
		// Strip a leading international 00 (0034... / 00234...).
		if(strpos($digits, '00') === 0){ $digits = substr($digits, 2); }
		if(strpos($digits, '234') === 0){
			$rest = substr($digits, 3);
			// 234 + 0 + 10 digits => the local zero is redundant.
			if(strlen($rest) === 11 && $rest[0] === '0'){ $rest = substr($rest, 1); }
			if(strlen($rest) !== 10){ return ''; }
			return '234' . $rest;
		}
		if(strlen($digits) === 11 && $digits[0] === '0'){
			return '234' . substr($digits, 1);
		}
		if(strlen($digits) === 10){ return '234' . $digits; }
		// Anything else is passed through unchanged so non-Nigerian numbers
		// and future formats still have a chance of delivery.
		return $digits;
	}

	/**
	 * Low-level call. Returns:
	 *   ['ok'=>bool,'http'=>int,'json'=>array|null,'raw'=>string,'error'=>?string]
	 * The raw body is always returned so a failure is diagnosable in logs
	 * instead of collapsing to a bare 'failed'.
	 */
	private function _call($method, $path, array $payload = null, $store_id = null, array $query = []){
		$creds = $this->get_credentials($store_id);
		if(!$creds || empty($creds->api_token)){
			return ['ok'=>false,'http'=>0,'json'=>null,'raw'=>'','error'=>'Invalid BulkSMSNigeria API token!'];
		}

		$url = $this->_base_url($creds) . $path;
		if(!empty($query)){ $url .= '?' . http_build_query($query); }

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $creds->api_token,
			'Accept: application/json',
			'Content-Type: application/json',
		]);
		if(strtoupper($method) === 'POST'){
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		}

		$raw  = curl_exec($ch);
		$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$cerr = curl_error($ch);
		curl_close($ch);

		if($cerr !== ''){
			log_message('error', 'BulkSMSNigeria cURL error: ' . $cerr);
			return ['ok'=>false,'http'=>$http,'json'=>null,'raw'=>(string)$raw,'error'=>'Connection error: ' . $cerr];
		}

		$json = json_decode((string)$raw, true);

		// A transport that returns 200 with a non-"success" status is still a
		// failure (the vendor uses HTTP 200 + status:"error" for some cases).
		$statusOk = is_array($json) && isset($json['status']) && strtolower((string)$json['status']) === 'success';
		$httpOk   = ($http >= 200 && $http < 300);
		if($httpOk && $statusOk){
			return ['ok'=>true,'http'=>$http,'json'=>$json,'raw'=>(string)$raw,'error'=>null];
		}

		// Prefer the human message, then the vendor error code, then the body.
		$err = null;
		if(is_array($json)){
			$err = $json['message'] ?? null;
			if(!empty($json['code'])){
				$err = trim(($json['code'] ?? '') . ' — ' . (string)$err, ' —');
			}
		}
		if(!$err){ $err = $http ? ('HTTP ' . $http) : 'Unknown error'; }

		log_message('error', 'BulkSMSNigeria ' . $method . ' ' . $path . ' failed. HTTP ' . $http . ' Response: ' . (string)$raw);
		return ['ok'=>false,'http'=>$http,'json'=>$json,'raw'=>(string)$raw,'error'=>$err];
	}

	/**
	 * Send one message. Keeps the 'success'|'failed' string contract used by
	 * every other provider model in this app.
	 */
	public function index($to, $message, $storeId = null){
		$res = $this->send($to, $message, $storeId);
		return $res['ok'] ? 'success' : 'failed';
	}

	/**
	 * Same as index() but returns the diagnostic envelope, so callers (OTP,
	 * campaigns) can surface WHY a send failed.
	 */
	public function send($to, $message, $storeId = null, $scheduleTime = null){
		$creds = $this->get_credentials($storeId);
		if(!$creds || empty($creds->api_token)){
			return ['ok'=>false,'http'=>0,'json'=>null,'raw'=>'','error'=>'Invalid BulkSMSNigeria API token!'];
		}

		$phone = $this->normalize_phone($to);
		if($phone === ''){
			return ['ok'=>false,'http'=>0,'json'=>null,'raw'=>'','error'=>'Invalid recipient phone number'];
		}
		if(trim((string)$message) === ''){
			return ['ok'=>false,'http'=>0,'json'=>null,'raw'=>'','error'=>'Message body is empty'];
		}

		$payload = [
			'from' => $creds->sender_id ?: 'BulkSMS',
			'to'   => $phone,
			'body' => (string)$message,
		];
		if(!empty($creds->gateway)){ $payload['gateway'] = $creds->gateway; }
		if(!empty($scheduleTime)){ $payload['schedule_time'] = $scheduleTime; }

		return $this->_call('POST', '/v2/sms', $payload, $storeId);
	}

	/** Native bulk — one API call for every recipient. */
	public function send_bulk(array $recipients, $message, $storeId = null, $scheduleTime = null){
		$creds = $this->get_credentials($storeId);
		if(!$creds || empty($creds->api_token)){
			return ['ok'=>false,'error'=>'Invalid BulkSMSNigeria API token!','raw'=>''];
		}

		$numbers = [];
		foreach($recipients as $r){
			$p = $this->normalize_phone($r);
			if($p !== ''){ $numbers[] = $p; }
		}
		$numbers = array_values(array_unique($numbers));
		if(empty($numbers)){
			return ['ok'=>false,'error'=>'No valid recipient phone numbers','raw'=>''];
		}

		$payload = [
			'from' => $creds->sender_id ?: 'BulkSMS',
			'to'   => $numbers,
			'body' => (string)$message,
		];
		if(!empty($creds->gateway)){ $payload['gateway'] = $creds->gateway; }
		if(!empty($scheduleTime)){ $payload['schedule_time'] = $scheduleTime; }

		$res = $this->_call('POST', '/v2/sms', $payload, $storeId);
		return ['ok'=>$res['ok'],'error'=>$res['error'],'raw'=>$res['raw'],'json'=>$res['json']];
	}

	/**
	 * Validate credentials WITHOUT spending credit — reads the wallet balance.
	 * Used by the settings screen's "Test connection" action.
	 */
	public function test_connection($storeId = null){
		$res = $this->_call('GET', '/wallet/balance', null, $storeId);
		if(!$res['ok']){
			return ['ok'=>false,'message'=>$res['error']];
		}
		$data = $res['json']['data'] ?? $res['json'];
		$bal  = is_array($data) ? ($data['balance'] ?? $data['current_balance'] ?? null) : null;
		$cur  = is_array($data) ? ($data['currency'] ?? 'NGN') : 'NGN';
		return [
			'ok'      => true,
			'message' => $bal !== null
				? 'Connected. Wallet balance: ' . $cur . ' ' . number_format((float)$bal, 2)
				: 'Connected successfully.',
			'balance' => $bal,
		];
	}

	/** Registered sender IDs + approval status. */
	public function list_sender_ids($storeId = null){
		$res = $this->_call('GET', '/sender-ids', null, $storeId);
		if(!$res['ok']){ return ['ok'=>false,'message'=>$res['error'],'items'=>[]]; }
		$items = $res['json']['data'] ?? $res['json'];
		return ['ok'=>true,'message'=>'OK','items'=>is_array($items) ? $items : []];
	}
}
