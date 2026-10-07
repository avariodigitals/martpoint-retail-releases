<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intake — authenticated server-to-server API for the public website.
 *
 * NOT MY_Controller: callers have no staff session. Auth is a per-store
 * shared secret (db_store.intake_key) sent as X-MartPoint-Intake-Key.
 *
 * Guarantees:
 *  - Unique submission reference: same ref replayed returns the same lead
 *    (200, deduplicated), never a second row.
 *  - Receipt is confirmed only after the row is durably committed.
 *  - Rate limiting via db_rate_limits (per store+IP, hourly window) plus a
 *    honeypot field. Attribution (utm params, page_url) is captured; anything that
 *    looks like a secret is stripped.
 */
class Intake extends CI_Controller {

	const RATE_LIMIT = 30;   // submissions per store+IP per hour
	const MAX_FIELD  = 2000; // bytes per text field

	public function __construct(){
		parent::__construct();
		$this->load->database();
	}

	private function _respond($code, $payload){
		$this->output->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode($payload));
	}

	/** Resolve the calling store by its intake key (timing-safe compare). */
	private function _storeByKey(){
		$key = $this->input->get_request_header('X-MartPoint-Intake-Key', TRUE)
			?: $this->input->get_request_header('X-Intake-Key', TRUE);
		if(empty($key) || strlen($key) < 16 || strlen($key) > 64) return null;
		if(!$this->db->field_exists('intake_key', 'db_store')) return null;
		$rows = $this->db->select('id, intake_key')->where('intake_key IS NOT NULL', null, FALSE)->get('db_store')->result();
		foreach($rows as $row){
			if(hash_equals((string)$row->intake_key, (string)$key)) return (int)$row->id;
		}
		return null;
	}

	/** Hourly-window counter; true when the request may proceed. */
	private function _rateOk($bucket){
		$this->db->query(
			"INSERT INTO db_rate_limits (bucket, window_start, hits) VALUES (?,?,1)
			 ON DUPLICATE KEY UPDATE hits = hits + 1",
			array($bucket, date('Y-m-d H:00:00'))
		);
		$row = $this->db->select('hits')->where('bucket', $bucket)
			->where('window_start', date('Y-m-d H:00:00'))->get('db_rate_limits')->row();
		return $row && (int)$row->hits <= self::RATE_LIMIT;
	}

	private function _clean($v){
		$v = is_string($v) ? trim(mb_substr($v, 0, self::MAX_FIELD)) : '';
		return $v;
	}

	/**
	 * POST /intake/lead
	 * Fields: submission_ref* (unique per site-side submission), name*,
	 * phone|email (at least one), enquiry, preferred_contact, preferred_date,
	 * preferred_branch_id, attribution {page_url, utm_*}, honeypot: company_website.
	 */
	public function lead(){
		if(strtoupper($this->input->method()) !== 'POST'){
			return $this->_respond(405, array('status' => 'error', 'message' => 'POST required'));
		}
		if(!$this->db->table_exists('db_leads') || !$this->db->table_exists('db_rate_limits')){
			return $this->_respond(503, array('status' => 'error', 'message' => 'Intake not available'));
		}
		$storeId = $this->_storeByKey();
		if(!$storeId){
			return $this->_respond(401, array('status' => 'error', 'message' => 'Invalid credentials'));
		}
		if(function_exists('mp_feature_enabled_for_store') && !mp_feature_enabled_for_store('leads', $storeId)){
			return $this->_respond(403, array('status' => 'error', 'message' => 'Lead intake is not enabled for this store'));
		}
		// Honeypot — bots fill hidden fields; humans never do.
		$hp = $this->input->post('company_website');
		if($hp !== null && $hp !== false && $hp !== ''){
			return $this->_respond(200, array('status' => 'received')); // silent drop
		}
		$ip = $this->input->ip_address();
		if(!$this->_rateOk('intake:' . $storeId . ':' . $ip)){
			return $this->_respond(429, array('status' => 'error', 'message' => 'Rate limit exceeded — retry later'));
		}

		$ref   = $this->_clean($this->input->post('submission_ref', TRUE));
		$name  = $this->_clean($this->input->post('name', TRUE));
		$phone = $this->_clean($this->input->post('phone', TRUE));
		$email = $this->_clean($this->input->post('email', TRUE));
		if($ref === '' || strlen($ref) > 80) return $this->_respond(422, array('status' => 'error', 'message' => 'submission_ref (<=80 chars) is required'));
		if($name === '') return $this->_respond(422, array('status' => 'error', 'message' => 'name is required'));
		if($phone === '' && $email === '') return $this->_respond(422, array('status' => 'error', 'message' => 'phone or email is required'));
		if($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->_respond(422, array('status' => 'error', 'message' => 'email is invalid'));

		// Replay-safe: a delivered submission ref maps to exactly one lead.
		if($this->db->field_exists('submission_ref', 'db_leads')){
			$existing = $this->db->select('id')->where('store_id', $storeId)->where('submission_ref', $ref)->get('db_leads')->row();
			if($existing){
				return $this->_respond(200, array('status' => 'received', 'deduplicated' => true, 'lead_id' => (int)$existing->id));
			}
		}

		// Attribution — capture page/UTMs; strip anything that looks secret.
		$attribution = array();
		foreach(array('page_url','utm_source','utm_medium','utm_campaign','utm_term','utm_content') as $k){
			$v = $this->_clean($this->input->post($k, TRUE));
			if($v !== '' && !preg_match('/(token|secret|key|password)=/i', $v)) $attribution[$k] = $v;
		}

		$data = array(
			'store_id'   => $storeId,
			'name'       => $name,
			'phone'      => $phone ?: null,
			'email'      => $email ?: null,
			'source'     => 'storefront',
			'status'     => 'new',
			'interest'   => $this->_clean($this->input->post('enquiry', TRUE)) ?: null,
			'notes'      => null,
			'created_at' => date('Y-m-d H:i:s'),
		);
		$extra = array(
			'submission_ref'     => $ref,
			'enquiry'            => $this->_clean($this->input->post('enquiry', TRUE)) ?: null,
			'preferred_contact'  => in_array($this->input->post('preferred_contact'), array('phone','whatsapp','email')) ? $this->input->post('preferred_contact') : null,
			'preferred_date'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$this->input->post('preferred_date')) ? $this->input->post('preferred_date') : null,
			'preferred_branch_id'=> (int)$this->input->post('preferred_branch_id') ?: null,
			'attribution_json'   => $attribution ? json_encode($attribution) : null,
		);
		foreach($extra as $col => $val){
			if($this->db->field_exists($col, 'db_leads')) $data[$col] = $val;
		}

		if(!$this->db->insert('db_leads', $data)){
			$err = $this->db->error();
			if(($err['code'] ?? 0) == 1062){
				$existing = $this->db->select('id')->where('store_id', $storeId)->where('submission_ref', $ref)->get('db_leads')->row();
				if($existing) return $this->_respond(200, array('status' => 'received', 'deduplicated' => true, 'lead_id' => (int)$existing->id));
			}
			log_message('error', 'Intake::lead insert failed store ' . $storeId . ' ref ' . $ref . ' — ' . ($err['message'] ?? ''));
			return $this->_respond(500, array('status' => 'error', 'message' => 'Storage failed — safe to retry'));
		}
		$leadId = (int)$this->db->insert_id();

		// Queue the acknowledgement (outbox) — never sent synchronously; a send
		// failure can never roll back the stored lead. physio_notify is a no-op
		// if the queue table is missing.
		if(function_exists('physio_notify')){
			physio_notify('lead.received.' . $leadId, array(
				'store_id'     => $storeId,
				'channel'      => 'email',
				'template_key' => 'lead_ack',
				'recipient'    => $email ?: $phone,
				'payload'      => array('lead_id' => $leadId, 'name' => $name),
			));
		}

		// Confirm receipt only now — the row is durable.
		return $this->_respond(200, array('status' => 'received', 'lead_id' => $leadId, 'ref' => $ref));
	}
}
