<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared one-time-password service.
 *
 * Every OTP flow in the app goes through this so that generation rules,
 * expiry, attempt limiting and channel routing behave identically. It is
 * deliberately channel-agnostic: delivery goes through `sms_model` (which
 * resolves whichever provider the store has selected, including
 * BulkSMSNigeria) or `email_service`. It never hardcodes a provider.
 *
 * Storage reuses `db_storefront_customer_otp`, with a `purpose` column that
 * keeps flows isolated: a code issued for 'admin_login' cannot be replayed
 * against 'storefront', and vice-versa.
 *
 * Usage:
 *   $this->load->library('otp_service');
 *   $r = $this->otp_service->send('storefront', 'phone', '08012345678', $storeId);
 *   $r = $this->otp_service->verify('storefront', 'phone', '08012345678', $code, $storeId);
 */
class Otp_service {

	/** Supported purposes. Anything else is rejected, so a typo cannot
	 *  silently create an unverifiable code. */
	const PURPOSES = ['storefront', 'admin_login', 'pos_confirm', 'generic'];

	/** @var CI_Controller */
	private $CI;

	/** Code lifetime in seconds. */
	public $expiry = 600;

	/** Wrong-attempt ceiling before a new code is required. */
	public $max_attempts = 5;

	/** Digits in the generated code. */
	public $length = 6;

	/** Minimum seconds between two requests for the same contact+purpose. */
	public $resend_cooldown = 60;

	public function __construct($params = []){
		$this->CI =& get_instance();
		foreach(['expiry','max_attempts','length','resend_cooldown'] as $k){
			if(isset($params[$k])){ $this->$k = (int)$params[$k]; }
		}
		if($this->length < 4 || $this->length > 10){ $this->length = 6; }
		if($this->expiry < 60){ $this->expiry = 60; }
	}

	private function _table(){
		return 'db_storefront_customer_otp';
	}

	/** True when the transient's `purpose` column exists (post-migration). */
	private function _has_purpose(){
		static $has = null;
		if($has === null){
			$has = $this->CI->db->table_exists($this->_table())
				&& $this->CI->db->field_exists('purpose', $this->_table());
		}
		return $has;
	}

	/**
	 * Normalise a contact so the same phone typed two ways matches one row.
	 * Phones reduce to digits; emails are lowercased.
	 */
	public function normalize_contact($channel, $contact){
		if($channel === 'email'){
			return strtolower(trim((string)$contact));
		}
		$d = preg_replace('/[^0-9]/', '', (string)$contact);
		// Collapse the Nigerian 234 / leading-zero variants to one canonical
		// local form so 08012345678 and +2348012345678 are the same contact.
		if(strpos($d, '234') === 0){
			$rest = substr($d, 3);
			if(strlen($rest) === 11 && $rest[0] === '0'){ $rest = substr($rest, 1); }
			if(strlen($rest) === 10){ return '0' . $rest; }
		}
		return $d;
	}

	/**
	 * Seconds remaining before this contact may request another code.
	 * 0 means it is safe to send now.
	 */
	public function cooldown_remaining($purpose, $channel, $contact, $storeId){
		if($this->resend_cooldown <= 0){ return 0; }
		$contact = $this->normalize_contact($channel, $contact);
		$field = ($channel === 'email') ? 'email' : 'phone';
		$qb = $this->CI->db->select('created_at')
			->where('store_id', $storeId)->where($field, $contact)
			->order_by('id', 'DESC')->limit(1);
		if($this->_has_purpose()){ $qb->where('purpose', $purpose); }
		$row = $qb->get($this->_table())->row();
		if(!$row || empty($row->created_at)){ return 0; }
		$elapsed = time() - strtotime($row->created_at);
		return ($elapsed >= $this->resend_cooldown) ? 0 : ($this->resend_cooldown - $elapsed);
	}

	/**
	 * Generate + store + deliver a code.
	 *
	 * @return array{ok:bool,message:string,otp?:string,cooldown?:int}
	 */
	public function send($purpose, $channel, $contact, $storeId, array $options = []){
		if(!in_array($purpose, self::PURPOSES, TRUE)){
			return ['ok'=>false,'message'=>'Unknown OTP purpose'];
		}
		$channel = ($channel === 'email') ? 'email' : 'phone';
		$contact = $this->normalize_contact($channel, $contact);
		if($contact === ''){
			return ['ok'=>false,'message'=>$channel === 'email' ? 'Enter a valid email address' : 'Enter a valid phone number'];
		}
		if($channel === 'email' && !filter_var($contact, FILTER_VALIDATE_EMAIL)){
			return ['ok'=>false,'message'=>'Enter a valid email address'];
		}
		if($channel === 'phone' && strlen($contact) < 7){
			return ['ok'=>false,'message'=>'Enter a valid phone number'];
		}

		// Respect the resend cooldown unless the caller explicitly overrides.
		if(empty($options['ignore_cooldown'])){
			$wait = $this->cooldown_remaining($purpose, $channel, $contact, $storeId);
			if($wait > 0){
				return ['ok'=>false,'message'=>"Please wait {$wait} seconds before requesting another code.",'cooldown'=>$wait];
			}
		}

		$otp = $this->_generate();
		$expires = date('Y-m-d H:i:s', time() + $this->expiry);

		// One live code per contact+purpose: invalidate the previous one.
		$this->invalidate($purpose, $channel, $contact, $storeId);

		$insert = [
			'store_id'   => $storeId,
			'customer_id'=> null,
			'otp'        => $otp,
			'verified'   => 0,
			'attempts'   => 0,
			'expires_at' => $expires,
		];
		$insert[($channel === 'email') ? 'email' : 'phone'] = $contact;
		$insert[($channel === 'email') ? 'phone' : 'email'] = '';
		if($this->_has_purpose()){ $insert['purpose'] = $purpose; }

		if(!$this->CI->db->insert($this->_table(), $insert)){
			log_message('error', 'OTP insert failed for purpose ' . $purpose);
			return ['ok'=>false,'message'=>'Could not create a verification code. Please try again.'];
		}

		$delivered = $this->_deliver($channel, $contact, $otp, $storeId, $options);
		if(!$delivered['ok']){
			// No delivery, no usable code — don't leave a live row behind.
			$this->invalidate($purpose, $channel, $contact, $storeId);
			return ['ok'=>false,'message'=>$delivered['message']];
		}

		$out = ['ok'=>true,'message'=>'Verification code sent'];
		// Only expose the code when the caller asked (tests / in-app display).
		if(!empty($options['return_code'])){ $out['otp'] = $otp; }
		return $out;
	}

	/**
	 * Verify a submitted code. On success the row is marked verified so a
	 * code cannot be used twice.
	 *
	 * @return array{ok:bool,message:string}
	 */
	public function verify($purpose, $channel, $contact, $code, $storeId){
		if(!in_array($purpose, self::PURPOSES, TRUE)){
			return ['ok'=>false,'message'=>'Unknown OTP purpose'];
		}
		$channel = ($channel === 'email') ? 'email' : 'phone';
		$contact = $this->normalize_contact($channel, $contact);
		$code = trim((string)$code);
		if($contact === '' || $code === ''){
			return ['ok'=>false,'message'=>'Contact and code are required'];
		}

		$field = ($channel === 'email') ? 'email' : 'phone';
		$qb = $this->CI->db->where('store_id', $storeId)
			->where($field, $contact)
			->where('verified', 0)
			->where('expires_at >', date('Y-m-d H:i:s'));
		if($this->_has_purpose()){ $qb->where('purpose', $purpose); }
		$row = $qb->order_by('id', 'DESC')->limit(1)->get($this->_table())->row();

		if(!$row){
			return ['ok'=>false,'message'=>'Invalid or expired code'];
		}
		if((int)$row->attempts >= $this->max_attempts){
			return ['ok'=>false,'message'=>'Too many attempts. Please request a new code.'];
		}
		if(!hash_equals((string)$row->otp, $code)){
			$this->CI->db->where('id', $row->id)
				->set('attempts', 'attempts + 1', false)
				->update($this->_table());
			$left = $this->max_attempts - ((int)$row->attempts + 1);
			return ['ok'=>false,'message'=>$left > 0 ? "Invalid code. {$left} attempt(s) remaining." : 'Too many attempts. Please request a new code.'];
		}

		// Single-use: mark verified immediately.
		$this->CI->db->where('id', $row->id)->update($this->_table(), ['verified' => 1]);
		return ['ok'=>true,'message'=>'Verified'];
	}

	/** Drop any live codes for this contact+purpose (called on request/consume). */
	public function invalidate($purpose, $channel, $contact, $storeId){
		$channel = ($channel === 'email') ? 'email' : 'phone';
		$contact = $this->normalize_contact($channel, $contact);
		if($contact === ''){ return; }
		$field = ($channel === 'email') ? 'email' : 'phone';
		$qb = $this->CI->db->where('store_id', $storeId)->where($field, $contact);
		if($this->_has_purpose()){ $qb->where('purpose', $purpose); }
		$qb->delete($this->_table());
	}

	/** Housekeeping: remove expired rows. Safe to call from cron. */
	public function purge_expired(){
		if(!$this->CI->db->table_exists($this->_table())){ return 0; }
		$this->CI->db->where('expires_at <', date('Y-m-d H:i:s', time() - 86400));
		$this->CI->db->delete($this->_table());
		return $this->CI->db->affected_rows();
	}

	private function _generate(){
		// random_int is CSPRNG-backed; sprintf pads to a fixed width so the
		// code is always the configured length (including leading zeros).
		$max = (int)str_repeat('9', $this->length);
		return sprintf('%0' . $this->length . 'd', random_int(0, $max));
	}

	/**
	 * Deliver over the requested channel. SMS goes through the shared
	 * dispatcher, so the store's selected provider (any of the six,
	 * including BulkSMSNigeria) is used automatically.
	 */
	private function _deliver($channel, $contact, $otp, $storeId, array $options){
		$storeName = $options['store_name'] ?? null;
		if($storeName === null){
			$store = function_exists('get_store_details') ? get_store_details($storeId) : null;
			$storeName = $store->store_name ?? 'MartPoint';
		}
		$minutes = (int)round($this->expiry / 60);

		if($channel === 'email'){
			$subject = $options['subject'] ?? ('Your ' . $storeName . ' verification code');
			// A caller may supply a builder that receives the real code, so a
			// branded template stays in the caller while the service owns the
			// code. Falls back to the neutral template below.
			if(isset($options['html_builder']) && is_callable($options['html_builder'])){
				$html = call_user_func($options['html_builder'], $otp, $minutes);
			} else {
				$html = $options['html'] ?? $this->_default_email_html($otp, $storeName, $minutes);
			}
			$text = $options['text'] ?? ("Your {$storeName} verification code is {$otp}. It expires in {$minutes} minutes.\n\nIf you did not request this, please ignore it.");
			// A caller-supplied plain-text body may use {{OTP}} / {{MINUTES}}
			// placeholders so the real code is always the one stored.
			if(isset($options['text'])){
				$text = str_replace(['{{OTP}}','{{MINUTES}}'], [$otp, $minutes], $text);
			}
			if(!$this->CI->load->is_loaded('email_service')){
				$this->CI->load->model('email_service');
			}
			$this->CI->email_service->setStoreId($storeId);
			$res = $this->CI->email_service->sendRaw($contact, $subject, $html, $text);
			$ok = !empty($res['success']);
			return ['ok'=>$ok,'message'=>$ok ? 'sent' : ($res['message'] ?? 'Could not send email. Please try again.')];
		}

		// SMS
		$message = $options['message'] ?? ("Your {$storeName} verification code is {$otp}. Valid for {$minutes} minutes. Do not share this code.");
		if(!$this->CI->load->is_loaded('sms_model')){
			$this->CI->load->model('sms_model');
		}
		$resp = $this->CI->sms_model->send_sms($contact, $message);
		$ok = is_string($resp)
			? (stripos($resp,'success') !== false || stripos($resp,'sent') !== false)
			: (bool)$resp;
		return [
			'ok' => $ok,
			'message' => $ok ? 'sent' : (is_string($resp) && $resp !== '' ? $resp : 'Could not send SMS. Please try again or use email.'),
		];
	}

	private function _default_email_html($otp, $storeName, $minutes){
		$otp = htmlspecialchars($otp, ENT_QUOTES);
		$storeName = htmlspecialchars($storeName, ENT_QUOTES);
		return '<div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;">'
			. '<h2 style="color:#0f172a;">' . $storeName . '</h2>'
			. '<p>Use this code to verify your identity:</p>'
			. '<p style="font-size:32px;font-weight:bold;letter-spacing:6px;color:#0057FF;margin:18px 0;">' . $otp . '</p>'
			. '<p style="color:#555;">This code expires in ' . (int)$minutes . ' minutes. If you did not request it, you can safely ignore this email.</p>'
			. '</div>';
	}
}
