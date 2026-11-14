<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Intake_slots — public, keyed availability and self-booking for the embed
 * widget.
 *
 *   GET  /intake_slots?date=YYYY-MM-DD[&service_id=N]   free slots for that day
 *   POST /intake_booking                                 book one (optional)
 *
 * Both authenticate on the SAME per-store key as /intake/lead
 * (X-MartPoint-Intake-Key -> db_store.intake_key). Intake::_storeByKey() is
 * reused deliberately rather than reimplemented: a second copy of the key check
 * is a second place for it to drift.
 *
 * Booking is OFF unless the store turns it on
 * (db_store_sitesettings.public_booking = 1). That default is intentional: a
 * clinic must control its own diary, and a public form that can take any slot
 * will fill the wrong ones. With it off the widget still works — it collects a
 * lead with a preferred time, and staff schedule it.
 *
 * Read-only for slots: this exposes WHEN the clinic is free, never who a patient
 * is, so a leaked key cannot enumerate the patient list.
 */
class Intake_slots extends CI_Controller {

	/** Same window the intake endpoint allows. */
	const RATE_LIMIT = 120;

	/** Appointment length when the service does not specify one. */
	const DEFAULT_DURATION = 30;

	/** How many days ahead a public visitor may look. */
	const MAX_DAYS_AHEAD = 60;

	public function __construct(){
		parent::__construct();
		$this->load->helper('physio');
	}

	/* ------------------------------------------------------------------ */
	/* Auth — identical rule to Intake::_storeByKey()                      */
	/* ------------------------------------------------------------------ */

	private function storeByKey(){
		$key = $this->input->get_request_header('X-MartPoint-Intake-Key', TRUE)
			?: $this->input->get_request_header('X-Intake-Key', TRUE);
		if(empty($key) || strlen($key) < 16 || strlen($key) > 64) return null;
		if(!$this->db->field_exists('intake_key', 'db_store')) return null;
		$rows = $this->db->select('id, intake_key')->where('intake_key IS NOT NULL', null, FALSE)
			->get('db_store')->result();
		foreach($rows as $row){
			if(hash_equals((string)$row->intake_key, (string)$key)) return (int)$row->id;
		}
		return null;
	}

	private function rateOk($bucket){
		if(!$this->db->table_exists('db_rate_limits')) return true;
		$this->db->query(
			"INSERT INTO db_rate_limits (bucket, window_start, hits) VALUES (?,?,1)
			 ON DUPLICATE KEY UPDATE hits = hits + 1",
			array($bucket, date('Y-m-d H:00:00')));
		$row = $this->db->select('hits')->where('bucket', $bucket)
			->where('window_start', date('Y-m-d H:00:00'))->get('db_rate_limits')->row();
		return $row && (int)$row->hits <= self::RATE_LIMIT;
	}

	private function respond($code, array $payload){
		$this->output->set_status_header($code)
			->set_content_type('application/json')
			->set_output(json_encode($payload));
	}

	/** Is self-booking switched on for this store? OFF by default. */
	private function bookingOn($storeId){
		$row = $this->db->select('public_booking')->where('id', $storeId)
			->get('db_store')->row();
		return $row && (int)$row->public_booking === 1;
	}

	/**
	 * Opening hours, per weekday, from db_store.booking_hours_json.
	 * Falls back to a sane clinic day when unset, so a store that never
	 * configured hours still gets a usable widget rather than an empty one.
	 */
	private function openingHours($storeId){
		$default = array();
		foreach(array(1,2,3,4,5) as $d){ $default[$d] = array('09:00', '17:00'); }
		$default[6] = array('09:00', '13:00');   // Saturday half day
		// Sunday (0) absent = closed.

		if(!$this->db->field_exists('booking_hours_json', 'db_store')) return $default;
		$row = $this->db->select('booking_hours_json')->where('id', $storeId)
			->get('db_store')->row();
		if(!$row || empty($row->booking_hours_json)) return $default;
		$decoded = json_decode($row->booking_hours_json, true);
		return is_array($decoded) && $decoded ? $decoded : $default;
	}

	/* ------------------------------------------------------------------ */
	/* GET — free slots                                                    */
	/* ------------------------------------------------------------------ */

	public function index(){
		if(strtoupper($this->input->method()) !== 'GET'){
			return $this->respond(405, array('status' => 'error', 'message' => 'GET required'));
		}
		$storeId = $this->storeByKey();
		if(!$storeId){
			return $this->respond(401, array('status' => 'error', 'message' => 'Invalid credentials'));
		}
		if(!$this->rateOk('slots:' . $storeId . ':' . $this->input->ip_address())){
			return $this->respond(429, array('status' => 'error', 'message' => 'Too many requests — retry later'));
		}
		if(function_exists('mp_feature_enabled_for_store') && !mp_feature_enabled_for_store('leads', $storeId)){
			return $this->respond(403, array('status' => 'error', 'message' => 'Booking is not enabled for this store'));
		}

		$date = (string)$this->input->get('date', TRUE);
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)){
			return $this->respond(422, array('status' => 'error', 'message' => 'date (YYYY-MM-DD) is required'));
		}
		$day = strtotime($date);
		if($day === false){
			return $this->respond(422, array('status' => 'error', 'message' => 'date is not a real date'));
		}
		$today = strtotime(date('Y-m-d'));
		if($day < $today){
			return $this->respond(422, array('status' => 'error', 'message' => 'date is in the past'));
		}
		if($day > strtotime('+' . self::MAX_DAYS_AHEAD . ' days')){
			return $this->respond(422, array('status' => 'error', 'message' => 'date is too far ahead'));
		}

		$hours = $this->openingHours($storeId);
		$dow = (int)date('w', $day);
		if(empty($hours[$dow]) && empty($hours[(string)$dow])){
			return $this->respond(200, array(
				'status' => 'ok', 'date' => $date, 'open' => false, 'slots' => array(),
				'booking_enabled' => $this->bookingOn($storeId),
			));
		}
		$window = $hours[$dow] ?? $hours[(string)$dow];
		$open  = substr((string)$window[0], 0, 5);
		$close = substr((string)$window[1], 0, 5);

		// One query for the day's bookings, then work out the gaps in PHP.
		$busy = array();
		if($this->db->table_exists('db_appointments')){
			$rows = $this->db->select('scheduled_at, duration_min')
				->where('store_id', $storeId)
				->where('DATE(scheduled_at)', $date)
				->where("LOWER(status) NOT IN ('cancelled','no_show','noshow')", null, FALSE)
				->get('db_appointments')->result();
			foreach($rows as $r){
				$busy[] = array(
					'start' => strtotime($r->scheduled_at),
					'end'   => strtotime($r->scheduled_at) + ((int)$r->duration_min ?: self::DEFAULT_DURATION) * 60,
				);
			}
		}

		$step = 30 * 60;   // slots every 30 minutes
		$dur  = self::DEFAULT_DURATION;
		if((int)$this->input->get('service_id')){
			$svc = $this->db->select('duration_min')->where('id', (int)$this->input->get('service_id'))
				->where('store_id', $storeId)->get('db_items')->row();
			if($svc && (int)$svc->duration_min > 0){ $dur = (int)$svc->duration_min; }
		}

		$slots = array();
		$start = strtotime($date . ' ' . $open);
		$end   = strtotime($date . ' ' . $close);
		for($t = $start; $t + ($dur * 60) <= $end; $t += $step){
			$slotEnd = $t + $dur * 60;
			if($day === $today && $t <= time() + 3600) continue;   // needs an hour's notice
			$free = true;
			foreach($busy as $b){
				if($t < $b['end'] && $slotEnd > $b['start']){ $free = false; break; }
			}
			if($free){ $slots[] = date('H:i', $t); }
		}

		return $this->respond(200, array(
			'status'          => 'ok',
			'date'            => $date,
			'open'            => true,
			'opens'           => $open,
			'closes'          => $close,
			'duration_min'    => $dur,
			'slots'           => $slots,
			'booking_enabled' => $this->bookingOn($storeId),
		));
	}

	/* ------------------------------------------------------------------ */
	/* POST — book a slot (only when the store allows it)                  */
	/* ------------------------------------------------------------------ */

	public function book(){
		if(strtoupper($this->input->method()) !== 'POST'){
			return $this->respond(405, array('status' => 'error', 'message' => 'POST required'));
		}
		$storeId = $this->storeByKey();
		if(!$storeId){
			return $this->respond(401, array('status' => 'error', 'message' => 'Invalid credentials'));
		}
		if(!$this->rateOk('book:' . $storeId . ':' . $this->input->ip_address())){
			return $this->respond(429, array('status' => 'error', 'message' => 'Too many requests — retry later'));
		}
		if(function_exists('mp_feature_enabled_for_store') && !mp_feature_enabled_for_store('leads', $storeId)){
			return $this->respond(403, array('status' => 'error', 'message' => 'Booking is not enabled for this store'));
		}
		if(!$this->bookingOn($storeId)){
			return $this->respond(403, array(
				'status' => 'error',
				'message' => 'This clinic takes booking requests rather than instant bookings. Please send the form and they will confirm a time.',
			));
		}

		// Honeypot — silent drop, exactly as the lead endpoint does.
		$hp = $this->input->post('company_website');
		if($hp !== null && $hp !== false && $hp !== ''){ return $this->respond(200, array('status' => 'received')); }

		$ref = trim((string)$this->input->post('submission_ref', TRUE));
		if($ref === '' || strlen($ref) > 80){
			return $this->respond(422, array('status' => 'error', 'message' => 'submission_ref (<=80 chars) is required'));
		}
		$name  = trim((string)$this->input->post('name', TRUE));
		$phone = trim((string)$this->input->post('phone', TRUE));
		$email = trim((string)$this->input->post('email', TRUE));
		$date  = trim((string)$this->input->post('date', TRUE));
		$time  = trim((string)$this->input->post('time', TRUE));
		if($name === ''){ return $this->respond(422, array('status' => 'error', 'message' => 'name is required')); }
		if($phone === '' && $email === ''){ return $this->respond(422, array('status' => 'error', 'message' => 'phone or email is required')); }
		if($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)){ return $this->respond(422, array('status' => 'error', 'message' => 'email is invalid')); }
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)){
			return $this->respond(422, array('status' => 'error', 'message' => 'A date and time are required'));
		}
		$when = $date . ' ' . $time . ':00';
		if(strtotime($when) === false || strtotime($when) <= time()){
			return $this->respond(422, array('status' => 'error', 'message' => 'That time is not in the future'));
		}

		// Replay-safe: one submission_ref books at most one appointment.
		$existing = $this->db->select('id, public_ref')->where('store_id', $storeId)
			->where('public_ref', $ref)->get('db_appointments')->row();
		if($existing){
			return $this->respond(200, array('status' => 'received', 'deduplicated' => true,
				'appointment_id' => (int)$existing->id));
		}

		// The slot must still be free — checked here, and again by book().
		if($this->slotTaken($storeId, $when)){
			return $this->respond(409, array('status' => 'error', 'message' => 'Sorry, that time has just been taken. Please pick another.'));
		}

		// The clinic-side models resolve the store from the SESSION
		// (get_current_store_id()), and a public request has none. Establishing
		// the store context here — FROM THE KEY, never from the form — lets the
		// proven models do the work instead of duplicating their insert logic.
		// It also means created_by records "website" rather than an empty user.
		$this->session->set_userdata(array(
			'store_id'     => (string)$storeId,
			'inv_username' => 'website',
		));

		$this->load->model('patients_model', 'patients_m');
		$patientId = $this->findOrCreatePatient($storeId, $name, $phone, $email);
		if(isset($patientId['error'])){ return $this->respond(500, array('status' => 'error', 'message' => $patientId['error'])); }
		$patientId = (int)$patientId;

		$this->load->model('appointments_model', 'appts');
		$result = $this->appts->book(array(
			'patient_id'    => $patientId,
			'warehouse_id'  => null,
			'service_id'    => (int)$this->input->post('service_id') ?: null,
			'staff_user_id' => (int)$this->input->post('staff_user_id') ?: null,
			'scheduled_at'  => $when,
			'duration_min'  => self::DEFAULT_DURATION,
			'notes'         => trim((string)$this->input->post('enquiry', TRUE)) ?: null,
			'source'        => 'website',
		));
		if(is_array($result)){
			return $this->respond(409, array('status' => 'error', 'message' => $result['error'] ?? 'Could not book that time'));
		}

		// Record the public reference so a retry is safe.
		if($this->db->field_exists('public_ref', 'db_appointments')){
			$this->db->where('id', (int)$result)->update('db_appointments', array('public_ref' => $ref));
		}

		return $this->respond(200, array(
			'status'         => 'received',
			'appointment_id' => (int)$result,
			'when'           => $when,
			'message'        => 'Your appointment is booked.',
		));
	}

	private function slotTaken($storeId, $when){
		$dur = self::DEFAULT_DURATION;
		$rows = $this->db->select('scheduled_at, duration_min')
			->where('store_id', $storeId)
			->where("LOWER(status) NOT IN ('cancelled','no_show','noshow')", null, FALSE)
			->where('DATE(scheduled_at)', date('Y-m-d', strtotime($when)))
			->get('db_appointments')->result();
		$t = strtotime($when);
		$end = $t + $dur * 60;
		foreach($rows as $r){
			$s = strtotime($r->scheduled_at);
			$e = $s + ((int)$r->duration_min ?: $dur) * 60;
			if($t < $e && $end > $s) return true;
		}
		return false;
	}

	/**
	 * Match an existing patient by phone or email before creating one, so a
	 * returning patient books against their own record instead of a duplicate.
	 */
	private function findOrCreatePatient($storeId, $name, $phone, $email){
		$q = $this->db->select('c.id')
			->from('db_customers c')
			->where('c.store_id', $storeId);
		if($phone !== '' && $email !== ''){
			$q->where("(c.mobile = " . $this->db->escape($phone) . " OR LOWER(c.email) = " . $this->db->escape(strtolower($email)) . ")", null, FALSE);
		} elseif($phone !== ''){
			$q->where('c.mobile', $phone);
		} else {
			$q->where('LOWER(c.email)', strtolower($email));
		}
		$cust = $q->get()->row();
		if($cust){
			$p = $this->db->select('id')->where('customer_id', $cust->id)
				->where('store_id', $storeId)->get('db_patients')->row();
			if($p) return (int)$p->id;
		}

		$this->load->model('patients_model', 'patients_m');
		$res = $this->patients_m->savePatient(
			// store_id is passed explicitly: a public request has no session, and
			// savePatient() otherwise resolves the store from it (-> NULL -> the
			// insert fails). The store comes from the key, never from the form.
			array('gender' => null, 'dob' => null, 'store_id' => $storeId),
			array('name' => $name, 'mobile' => $phone, 'email' => $email,
				'phone' => '', 'address' => '', 'city' => '',
				'store_id' => $storeId, 'confirm_duplicate' => 1),
			null
		);
		if(is_array($res)) return array('error' => $res['error'] ?? 'Could not create the patient record');
		return (int)$res;
	}

	public function index_help(){
		return $this->respond(200, array('status' => 'ok'));
	}
}
