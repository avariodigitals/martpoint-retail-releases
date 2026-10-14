<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Encounters — one row per physical arrival (the stable visit reference).
 *
 * Idempotency: (store_id, checkin_key) is unique. Booked arrivals use
 * "appt:{id}" so a double-tap or network retry returns the same encounter;
 * walk-ins pass a client-generated uuid. Check-in creates NO session
 * deduction and NO charge — those belong to the sessions/billing stages.
 *
 * Queue stage is visit position (waiting_nurse → nursing_intake →
 * waiting_physio → with_physio → awaiting_finance → closed), separate from
 * appointment status and from any financial state.
 */
class Encounters_model extends CI_Model {

	const QUEUE_STAGES = array('waiting_nurse','nursing_intake','waiting_physio','with_physio','awaiting_finance','closed');

	/**
	 * Allowed forward moves + the permission(s) required to enter the target
	 * stage. A string means that single key; an array means ANY of them —
	 * e.g. the nurse OR the receptionist on duty pushes a waiting patient
	 * into With Therapist, one by one.
	 */
	const TRANSITIONS = array(
		'waiting_nurse'    => array('nursing_intake' => 'vitals_add', 'closed' => 'encounters_finalize'),
		'nursing_intake'   => array('waiting_physio' => 'vitals_add', 'waiting_nurse' => 'vitals_add', 'closed' => 'encounters_finalize'),
		'waiting_physio'   => array('with_physio' => array('encounters_add', 'vitals_add', 'care_checkin'), 'waiting_nurse' => 'care_checkin', 'closed' => 'encounters_finalize'),
		'with_physio'      => array('awaiting_finance' => 'encounters_finalize', 'closed' => 'encounters_finalize'),
		'awaiting_finance' => array('closed' => 'patient_funds_view', 'with_physio' => 'encounters_add'),
		'closed'           => array(),
	);

	public function __construct(){
		parent::__construct();
	}

	public function getEncounter($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_encounters')->row();
	}

	public function getByCheckinKey($key, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('checkin_key', $key)->where('store_id', $storeId)->get('db_encounters')->row();
	}

	/** Unclosed visits older than the board's window — what the filter hides. */
	public function countQueueOutside($storeId, $branchIds = null, $days = 1){
		$since = $this->_queueStart($days);
		if($since === null) return 0;
		$this->db->where('store_id', (int)$storeId)
			->where('queue_stage IS NOT NULL', null, FALSE)
			->where('queue_stage !=', 'closed')
			->where('checkin_at <', $since);
		if($branchIds !== null){
			if(empty($branchIds)) return 0;
			$this->db->group_start()->where_in('warehouse_id', $branchIds)
				->or_where('warehouse_id IS NULL', null, FALSE)->group_end();
		}
		return (int)$this->db->count_all_results('db_encounters');
	}

	/**
	 * The one definition of "still on the queue": an arrival that has not been
	 * cleared, in the branches the caller may work. The dashboard tile and the
	 * board both count through this, so a number can never point at a list that
	 * does not contain it.
	 */
	public function countQueueOpen($storeId, $branchIds = null){
		$this->db->where('store_id', (int)$storeId)
			->where('queue_stage IS NOT NULL', null, FALSE)
			->where('queue_stage !=', 'closed');
		if($branchIds !== null){
			if(empty($branchIds)) return 0;
			$this->db->group_start()->where_in('warehouse_id', $branchIds)
				->or_where('warehouse_id IS NULL', null, FALSE)->group_end();
		}
		return (int)$this->db->count_all_results('db_encounters');
	}

	/**
	 * Open visits parked at a branch the caller is not assigned to. The board
	 * cannot list them — but it has to say they exist, otherwise a store-wide
	 * count reads as a ghost and the desk starts hunting patient records for it.
	 */
	public function countQueueElsewhere($storeId, $branchIds){
		if($branchIds === null) return 0; // cross-branch: nothing is out of scope
		$this->db->where('store_id', (int)$storeId)
			->where('queue_stage IS NOT NULL', null, FALSE)
			->where('queue_stage !=', 'closed');
		if(empty($branchIds)){
			// No branch assignment: only branch-marked visits are out of reach.
			$this->db->where('warehouse_id IS NOT NULL', null, FALSE);
		} else {
			$ids = implode(',', array_map('intval', (array)$branchIds));
			$this->db->where('(warehouse_id IS NOT NULL AND warehouse_id NOT IN (' . $ids . '))', null, FALSE);
		}
		return (int)$this->db->count_all_results('db_encounters');
	}

	/**
	 * Where a board window starts. 1 = today, 7/30 = rolling days back,
	 * 365 = this calendar year (so "This year" means what the dashboard's
	 * "This Year" means), 0 = no date bound at all. A visit stuck on a stage
	 * for months is counted by the dashboard tile, so the board must be able
	 * to show it too — otherwise the two screens disagree.
	 */
	private function _queueStart($days){
		$days = (int)$days;
		if($days <= 0) return null;
		if($days >= 365) return date('Y-01-01 00:00:00');
		return date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
	}

	/**
	 * The live queue, grouped by stage, scoped to the user's branches.
	 *
	 * $days is how far back a backlog may reach (1 = today, 0 = no date bound).
	 * The board shows visits still ON the queue in that window — a cleared
	 * (closed) visit is history, read on the patient profile or the visit page.
	 * Rows on a stage the app no longer uses still come back: a queue screen
	 * that silently drops them is how a counted visit becomes unfindable.
	 */
	public function getQueue($storeId, $branchIds = null, $days = 1){
		$since = $this->_queueStart($days);
		/* The visit-type / tasked-nurse columns arrive with migration 4.0.9.144.
		   Until that has run the join below is a hard 500 ("Unknown column
		   e.nurse_user_id"), so the board degrades to no nurse column instead of
		   dying — a queue screen that refuses to load blocks the whole clinic. */
		$hasNurseCol = $this->db->field_exists('nurse_user_id', 'db_encounters');
		$this->db->select('e.*, c.customer_name, c.mobile, p.patient_code, u.username AS clinician_name, u.role_id AS clinician_role_id, a.username AS handler_name'
				. ($hasNurseCol ? ", n.username AS nurse_name, n.role_id AS nurse_role_id" : ''))
			->from('db_encounters e')
			->join('db_patients p', 'p.id = e.patient_id AND p.store_id = e.store_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->join('db_users u', 'u.id = e.clinician_user_id', 'left')
			->join('db_users a', 'a.id = e.assigned_to', 'left');
		if($hasNurseCol){
			$this->db->join('db_users n', 'n.id = e.nurse_user_id', 'left');
		}
		$this->db->where('e.store_id', $storeId)
			->where("e.queue_stage IS NOT NULL", null, FALSE)
			->where('e.queue_stage !=', 'closed');
		if($since !== null) $this->db->where('e.checkin_at >=', $since);
		if($branchIds !== null){
			if(empty($branchIds)) return array(); // user has no branch assignment — sees nothing
			$this->db->group_start()->where_in('e.warehouse_id', $branchIds)->or_where('e.warehouse_id IS NULL', null, FALSE)->group_end();
		}
		$rows = $this->db->order_by('e.checkin_at', 'asc')->get()->result();

		/* The nurse's hand-over note travels on the card so the therapist reads
		   it without opening anything. It is stored as the intake-completed
		   event note — one action, one history line. */
		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$encIds = array();
		foreach($rows as $r){ $encIds[] = (int)$r->id; }
		$handover = array();
		if($encIds && $this->db->table_exists('db_encounter_events')){
			$evs = $this->db->select('encounter_id, note, created_by, created_by_name')
				->where_in('encounter_id', $encIds)->where('event', 'intake_completed')
				->order_by('id', 'asc')->get('db_encounter_events')->result();
			foreach($evs as $ev){ $handover[(int)$ev->encounter_id] = $ev; }
		}
		$queue = array_fill_keys(self::QUEUE_STAGES, array());
		foreach($rows as $r){
			$h = isset($handover[(int)$r->id]) ? $handover[(int)$r->id] : null;
			$r->handover_note = $h ? trim((string)$h->note) : '';
			$r->handover_by   = $h ? mp_first_name($h->created_by, $h->created_by_name) : '';
			$queue[$r->queue_stage][] = $r;
		}
		return $queue;
	}

	/**
	 * Repair stale queue rows whose appointment has already been cancelled or
	 * marked no-show. This is called before the board is counted/rendered so
	 * historical stuck rows disappear too, not only new transitions.
	 */
	public function closeTerminalAppointmentVisits($storeId){
		if(!$this->db->table_exists('db_appointments')) return 0;
		$rows = $this->db->select('e.id, e.queue_stage, a.status AS appt_status, a.cancel_reason')
			->from('db_encounters e')
			->join('db_appointments a', 'a.id = e.appointment_id AND a.store_id = e.store_id')
			->where('e.store_id', (int)$storeId)
			->where('e.appointment_id IS NOT NULL', null, false)
			->where('e.queue_stage !=', 'closed')
			->where_in('a.status', array('cancelled','no_show'))
			->limit(100)->get()->result();
		$n = 0;
		foreach($rows as $r){
			$note = 'Visit closed — linked appointment is ' . str_replace('_', ' ', $r->appt_status)
				. (!empty($r->cancel_reason) ? '. Reason: ' . $r->cancel_reason : '.');
			$res = $this->closeUnattended((int)$r->id, $note, $r->appt_status);
			if(!isset($res['error'])) $n++;
		}
		return $n;
	}

	/**
	 * Same repair for treatment-session arrivals. Some older no-show/cancel
	 * paths changed db_treatment_sessions but left the visit open because the
	 * session row did not carry encounter_id. The encounter's checkin_key still
	 * names the session, so close it from there.
	 */
	public function closeTerminalSessionVisits($storeId){
		if(!$this->db->table_exists('db_treatment_sessions')) return 0;
		$rows = $this->db->select('e.id, e.queue_stage, s.id AS session_id, s.status AS session_status, s.cancel_reason, s.session_no, s.units_total')
			->from('db_encounters e')
			->join('db_treatment_sessions s', 's.id = CAST(SUBSTRING(e.checkin_key,6) AS UNSIGNED) AND s.store_id = e.store_id')
			->where('e.store_id', (int)$storeId)
			->where('e.checkin_key LIKE', 'sess:%')
			->where('e.queue_stage !=', 'closed')
			->where_in('s.status', array('cancelled','no_show'))
			->limit(100)->get()->result();
		$n = 0;
		foreach($rows as $r){
			$line = (int)$r->session_no > 0 ? 'session ' . (int)$r->session_no . ' of ' . (int)$r->units_total : 'session #' . (int)$r->session_id;
			$note = 'Visit closed — booked ' . $line . ' is ' . str_replace('_', ' ', $r->session_status)
				. (!empty($r->cancel_reason) ? '. Reason: ' . $r->cancel_reason : '.');
			$res = $this->closeUnattended((int)$r->id, $note, $r->session_status);
			if(!isset($res['error'])) $n++;
		}
		return $n;
	}

	/** Open (or create) the current care episode for a patient + branch. */
	public function openEpisode($storeId, $patientId, $warehouseId, $type = 'outpatient'){
		$ep = $this->db->where('store_id', $storeId)->where('patient_id', $patientId)
			->where('status', 'open')->order_by('id', 'desc')->get('db_care_episodes')->row();
		if($ep) return (int)$ep->id;
		$count_id = get_count_id('db_care_episodes', $storeId);
		$this->db->insert('db_care_episodes', array(
			'store_id'     => $storeId,
			'patient_id'   => $patientId,
			'count_id'     => $count_id,
			'episode_code' => 'EP-'.str_pad($count_id, 5, '0', STR_PAD_LEFT),
			'warehouse_id' => $warehouseId ?: null,
			'episode_type' => $type === 'inpatient' ? 'inpatient' : 'outpatient',
			'started_at'   => date('Y-m-d H:i:s'),
			'status'       => 'open',
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by'   => $this->session->userdata('inv_username') ?: 'system',
		));
		return (int)$this->db->insert_id();
	}

	/**
	 * Check a patient in. Returns array('encounter_id'=>, 'already'=>bool) or
	 * array('error'=>msg). Safe under retries — same checkin_key, same visit.
	 */
	public function checkin(array $d){
		$storeId = get_current_store_id();
		$checkinKey = trim($d['checkin_key'] ?? '');
		$apptId    = (int)($d['appointment_id'] ?? 0);
		$patientId = (int)($d['patient_id'] ?? 0);
		$warehouseId = (int)($d['warehouse_id'] ?? 0) ?: null;
		$clinicianId = (int)($d['clinician_user_id'] ?? 0) ?: null;

		$appt = null;
		if($apptId){
			$appt = $this->db->where('id', $apptId)->where('store_id', $storeId)->get('db_appointments')->row();
			if(!$appt) return array('error' => 'Appointment not found');
			$patientId = (int)$appt->patient_id;
			$checkinKey = 'appt:' . $apptId;
			$warehouseId = $warehouseId ?: (int)$appt->warehouse_id ?: null;
			$clinicianId = $clinicianId ?: (int)$appt->staff_user_id ?: null;
			// A booked arrival may only be checked in once — and not twice-open
			if($appt->status === 'checked_in'){
				$existing = $this->getByCheckinKey($checkinKey, $storeId);
				if($existing) return array('encounter_id' => (int)$existing->id, 'already' => true);
			}
			if(!in_array($appt->status, array('confirmed','requested','proposed','checked_in'))){
				return array('error' => 'Appointment is ' . $appt->status . ' — cannot check in');
			}
		}
		// A booked treatment session arriving on its day: the visit it raises
		// gets linked to that exact session, so the treatment route draws the
		// one already paid for instead of reserving a second.
		$sess = null;
		$sessId = (int)($d['session_id'] ?? 0);
		if($sessId){
			if(!$this->db->table_exists('db_treatment_sessions')) return array('error' => 'Session bookings are not installed on this database yet');
			$sess = $this->db->where('id', $sessId)->where('store_id', $storeId)->get('db_treatment_sessions')->row();
			if(!$sess) return array('error' => 'Booked session not found');
			$patientId = (int)$sess->patient_id;
			$checkinKey = 'sess:' . $sessId;
			$warehouseId = $warehouseId ?: (int)$sess->branch_id ?: null;
			$clinicianId = $clinicianId ?: (int)$sess->clinician_id ?: null;
			if(!empty($sess->encounter_id)){
				$linked = $this->getEncounter((int)$sess->encounter_id, $storeId);
				if($linked && $linked->status === 'open') return array('encounter_id' => (int)$linked->id, 'already' => true);
			}
			if(!in_array($sess->status, array('scheduled','checked_in'), true)){
				return array('error' => 'This session is ' . str_replace('_', ' ', $sess->status) . ' — cannot check in');
			}
		}
		if(!$patientId || !$checkinKey) return array('error' => 'Patient and check-in reference are required');

		$patient = $this->db->select('id, deceased, status')->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$patient) return array('error' => 'Patient not found');
		if($patient->deceased) return array('error' => 'Deceased patient — arrivals cannot be checked in');
		if(!$patient->status) return array('error' => 'Patient record is inactive');

		// Idempotent retry — return the existing visit
		$existing = $this->getByCheckinKey($checkinKey, $storeId);
		if($existing) return array('encounter_id' => (int)$existing->id, 'already' => true);

		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$this->db->trans_start();
		$episodeId = $this->openEpisode($storeId, $patientId, $warehouseId);
		$count_id = get_count_id('db_encounters', $storeId);
		$this->db->insert('db_encounters', array(
			'store_id'          => $storeId,
			'episode_id'        => $episodeId,
			'patient_id'        => $patientId,
			'warehouse_id'      => $warehouseId,
			'appointment_id'    => $apptId ?: null,
			'count_id'          => $count_id,
			'encounter_code'    => 'ENC-'.str_pad($count_id, 5, '0', STR_PAD_LEFT),
			'checkin_key'       => $checkinKey,
			'checkin_at'        => date('Y-m-d H:i:s'),
			'clinician_user_id' => $clinicianId,
			'queue_stage'       => 'waiting_nurse',
			'queue_stage_at'    => date('Y-m-d H:i:s'),
			'status'            => 'open',
			'created_date'      => date('Y-m-d'),
			'created_time'      => date('H:i:s'),
			'created_by'        => $this->session->userdata('inv_username') ?: 'system',
		));
		$encId = (int)$this->db->insert_id();
		if($encId){
			$this->logEvent($encId, $storeId, 'checked_in', null, 'waiting_nurse', $d['note'] ?? null);
			if($appt){
				$this->db->where('id', $apptId)->where('store_id', $storeId)->update('db_appointments', array(
					'status' => 'checked_in', 'arrived_at' => date('Y-m-d H:i:s'), 'slot_key' => null, 'updated_at' => date('Y-m-d H:i:s'),
				));
				$this->db->insert('db_appointment_events', array(
					'store_id' => $storeId, 'appointment_id' => $apptId, 'event' => 'arrived',
					'to_value' => 'checked_in', 'note' => 'Encounter #' . $encId,
					'created_by' => $this->session->userdata('inv_userid'),
					'created_by_name' => mp_first_name($this->session->userdata('inv_userid'), $this->session->userdata('first_name')),
					'created_at' => date('Y-m-d H:i:s'),
				));
			}
			if($sess){
				// One arrival, one record: the visit is now the booked session's
				// visit, and the booking moves to checked-in with it.
				$this->load->model('Sessions_model', 'sessions');
				if($sess->status === 'scheduled') $this->sessions->checkin($sessId);
				$this->db->where('id', $sessId)->where('store_id', $storeId)->update('db_treatment_sessions', array(
					'encounter_id' => $encId,
					'updated_at'   => date('Y-m-d H:i:s'),
				));
			}
		}
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE || !$encId){
			// A concurrent request may have won the unique-key race — if the
			// row now exists, return it rather than an error (idempotent retry).
			$existing = $this->getByCheckinKey($checkinKey, $storeId);
			if($existing) return array('encounter_id' => (int)$existing->id, 'already' => true);
			return array('error' => 'Check-in failed');
		}
		return array('encounter_id' => $encId, 'already' => false);
	}

	public function logEvent($encId, $storeId, $event, $from = null, $to = null, $note = null){
		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$this->db->insert('db_encounter_events', array(
			'store_id' => $storeId, 'encounter_id' => $encId, 'event' => $event,
			'from_stage' => $from, 'to_stage' => $to, 'note' => $note,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_by_name' => mp_first_name($this->session->userdata('inv_userid'), $this->session->userdata('first_name')),
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function getEvents($encId, $storeId = null){
		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db->where('encounter_id', $encId)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_encounter_events')->result();
		foreach($rows as $r){
			$r->created_by_name = mp_first_name($r->created_by, $r->created_by_name);
		}
		return $rows;
	}

	/**
	 * Move an encounter through the care queue. Validates the transition map
	 * and requires the caller hold the permission for the TARGET stage (an
	 * array in the map means ANY of those keys).
	 *
	 * The physio → finance hand-off additionally REQUIRES the assessment
	 * remark: a physio must state how much and what sessions they recommend
	 * before the visit becomes a finance matter.
	 */
	/**
	 * Closed visits are view-only. Amending one needs the explicit
	 * `encounters_edit_closed` grant — admin-class roles hold it by default
	 * and the Store Admin can grant it to other roles from the Roles screen.
	 */
	private function _closedLocked($enc){
		if((string)$enc->queue_stage !== 'closed') return false;
		if(!function_exists('physio_can')) $this->load->helper('physio');
		return !physio_can('encounters_edit_closed');
	}

	public function moveStage($encId, $toStage, $note = null, $event = 'stage_change'){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if($enc->queue_stage === 'closed' || !in_array($enc->queue_stage, self::QUEUE_STAGES)){
			return array('error' => 'Visit is closed');
		}
		$allowed = self::TRANSITIONS[$enc->queue_stage] ?? array();
		if(!isset($allowed[$toStage])){
			return array('error' => "Cannot move from {$enc->queue_stage} to {$toStage}");
		}
		$needPerm = $allowed[$toStage];
		if(!function_exists('physio_can')) $this->load->helper('physio');
		if(is_array($needPerm)){
			if(!physio_can_any($needPerm)){
				return array('error' => "You are not authorised to move a patient to {$toStage}");
			}
		} else if(!physio_can($needPerm)){
			return array('error' => "You are not authorised to move a patient to {$toStage}");
		}
		if($toStage === 'awaiting_finance' && trim((string)($enc->assessment_remark ?? '')) === ''){
			return array('error' => 'Record the assessment remark (recommended amount and sessions) before handing this visit to Finance');
		}

		/* The waiting-for-nurse desk decides what this visit IS, and that forks
		   the journey. Nothing moves on until it is chosen. (Only enforced once
		   migration 4.0.9.144 has added the columns — before then there is no
		   choice to make and the queue must keep flowing.) */
		$visitType = $enc->visit_type ?? null;
		$typed = $this->db->field_exists('visit_type', 'db_encounters');
		if($typed && $toStage === 'nursing_intake' && !$visitType){
			return array('error' => 'Choose whether this visit is an assessment or a treatment session before moving it on');
		}
		if($toStage === 'nursing_intake' && $visitType === 'treatment' && empty($enc->nurse_user_id)){
			return array('error' => 'Choose the nurse tasked with this patient before moving them on');
		}
		if($toStage === 'nursing_intake' && $visitType === 'treatment' && !$this->visitSessionCovered($enc)){
			return array('error' => 'A treatment session must draw from a paid package — this patient has none left. Sell a package or take the Assessment route.');
		}
		if($toStage === 'awaiting_finance' && $visitType !== 'assessment'){
			return array('error' => 'Only assessment visits go to Finance — a treatment session closes with the therapist');
		}
		if($enc->queue_stage === 'with_physio' && $toStage === 'closed' && $visitType === 'assessment'){
			return array('error' => 'An assessment visit goes to Finance first — record the assessment remark');
		}

		/* One patient at a time per clinician. Clinic days are short and a
		   therapist who is already with someone cannot take a second without
		   leaving the first, so the queue refuses rather than pretending. */
		if($toStage === 'with_physio'){
			$busy = $this->db->where('store_id', $storeId)
				->where('queue_stage', 'with_physio')
				->where('clinician_user_id', $this->session->userdata('inv_userid'))
				->where('id !=', (int)$encId)
				->count_all_results('db_encounters');
			if($busy > 0){
				return array('error' => 'You already have a patient with you — finish that visit before starting another');
			}
		}

		/* A treatment visit that closes has been had — its package session is
		   drawn now, exactly once. Drawn before the stage update so the result
		   can travel in the close note, and a failed draw never blocks the
		   clinic from closing a visit that physically happened. */
		$drawNote = '';
		if($toStage === 'closed' && ($enc->visit_type ?? null) === 'treatment'){
			$drawn = $this->_drawVisitSession($enc);
			$drawNote = $drawn === true
				? ' Session drawn from the package.'
				: ' Session NOT drawn from the package: ' . $drawn['error'] . '.';
		}

		$update = array('queue_stage' => $toStage, 'queue_stage_at' => date('Y-m-d H:i:s'));
		if($toStage === 'closed') $update['status'] = 'completed';
		if($toStage === 'with_physio') $update['clinician_user_id'] = $this->session->userdata('inv_userid');
		$eventNote = trim(trim((string)$note) . $drawNote);
		$this->db->trans_start();
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', $update);
		$this->logEvent($encId, $storeId, $event, $enc->queue_stage, $toStage, $eventNote !== '' ? $eventNote : null);
		if($toStage === 'closed' && $enc->appointment_id){
			$this->db->where('id', $enc->appointment_id)->where('store_id', $storeId)->where('status', 'checked_in')
				->update('db_appointments', array('status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')));
		}
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE ? array('error' => 'Update failed') : true;
	}

	/**
	 * Close a visit that will not happen: the desk marked its booked session a
	 * no-show (or cancelled it) while the patient was still on the board.
	 *
	 * This deliberately steps around the stage machine and its grants. The
	 * permission that was tested is the session cancel that called it, and a
	 * visit must never be left in "Waiting for Nurse" after the patient has
	 * gone home. No money moves and no package session is drawn — care that
	 * was not given consumes nothing.
	 */
	public function closeUnattended($encId, $note, $appointmentStatus = 'no_show'){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if((string)$enc->queue_stage === 'closed' || !in_array((string)$enc->status, array('open','in_progress'), true)){
			return array('ok' => true, 'skipped' => true);
		}
		$now = date('Y-m-d H:i:s');
		$this->db->trans_start();
		$this->db->where('id', (int)$enc->id)->where('store_id', $storeId)->update('db_encounters', array(
			'queue_stage' => 'closed', 'queue_stage_at' => $now, 'status' => 'cancelled',
		));
		$this->logEvent((int)$enc->id, $storeId, 'stage_change', $enc->queue_stage, 'closed', $note);
		if(!empty($enc->appointment_id)){
			$this->db->where('id', (int)$enc->appointment_id)->where('store_id', $storeId)
				->where_in('status', array('requested','proposed','confirmed','checked_in'))
				->update('db_appointments', array('status' => $appointmentStatus, 'updated_at' => $now));
		}
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE){
			return array('error' => 'Could not close the visit');
		}
		return array('ok' => true, 'encounter_id' => (int)$enc->id);
	}

	/**
	 * Save a nursing-intake vitals SET (draft or final).
	 *
	 * $entries: list of array(
	 *   vital_key    => bp_systolic|pulse|temperature|…,
	 *   label        => display label (optional, defaults to key),
	 *   value        => raw value or null,
	 *   unit         => mmHg|bpm|°C|%|/min|kg|cm|/10,
	 *   not_measured => bool — explicit "not taken" (never confused with 0),
	 *   measured_at  => Y-m-d H:i:s of the reading (defaults to now)
	 * )
	 *
	 * Draft sets may be re-saved (entries replaced in place). Final sets are
	 * immutable — a corrected reading is recorded as a NEW set so history is
	 * preserved. A snapshot of the latest FINAL set is mirrored into
	 * db_encounters.vitals_json for the queue card only.
	 */
	public function saveVitalsSet($encId, array $entries, $finalize = false, $vitalsSetId = 0, $note = null){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if($this->_closedLocked($enc)) return array('error' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
		if(empty($entries)) return array('error' => 'No vitals supplied');

		$uid  = (int)$this->session->userdata('inv_userid') ?: null;
		$name = $this->session->userdata('inv_username') ?: 'system';
		$now  = date('Y-m-d H:i:s');

		$this->db->trans_start();
		if($vitalsSetId){
			$set = $this->db->where('id', $vitalsSetId)->where('store_id', $storeId)
				->where('encounter_id', $encId)->get('db_encounter_vitals')->row();
			if(!$set) return array('error' => 'Vitals set not found');
			if($set->status === 'final') return array('error' => 'Final vitals are locked — record a new set');
			if($finalize){
				$this->db->where('id', $vitalsSetId)->update('db_encounter_vitals', array(
					'status' => 'final', 'finalized_at' => $now, 'finalized_by' => $uid,
				));
			}
			$this->db->where('vitals_id', $vitalsSetId)->where('store_id', $storeId)->delete('db_encounter_vital_entries');
			$setId = (int)$vitalsSetId;
		} else {
			$this->db->insert('db_encounter_vitals', array(
				'store_id'         => $storeId,
				'encounter_id'     => $encId,
				'patient_id'       => (int)$enc->patient_id,
				'status'           => $finalize ? 'final' : 'draft',
				'recorded_by'      => $uid,
				'recorded_by_name' => $name,
				'finalized_at'     => $finalize ? $now : null,
				'finalized_by'     => $finalize ? $uid : null,
				'created_at'       => $now,
			));
			$setId = (int)$this->db->insert_id();
		}

		foreach($entries as $e){
			$key = preg_replace('/[^a-z0-9_]/i', '', (string)($e['vital_key'] ?? ''));
			if($key === '') continue;
			$nm  = !empty($e['not_measured']) ? 1 : 0;
			$val = $nm ? null : (isset($e['value']) ? trim((string)$e['value']) : null);
			if($val === '') $val = null;
			// value_num only when a clean number was supplied
			$num = ($val !== null && is_numeric($val)) ? $val : null;
			$this->db->insert('db_encounter_vital_entries', array(
				'store_id'     => $storeId,
				'vitals_id'    => $setId,
				'vital_key'    => $key,
				'label'        => isset($e['label']) && $e['label'] !== '' ? substr((string)$e['label'], 0, 80) : $key,
				'value_text'   => $val,
				'value_num'    => $num,
				'unit'         => isset($e['unit']) && $e['unit'] !== '' ? substr((string)$e['unit'], 0, 20) : null,
				'not_measured' => $nm,
				'measured_at'  => $nm ? null : (!empty($e['measured_at']) ? $e['measured_at'] : $now),
				'created_at'   => $now,
			));
		}

		if($note !== null && $note !== ''){
			$this->db->where('id', $setId)->where('store_id', $storeId)
				->update('db_encounter_vitals', array('notes' => substr($note, 0, 1000)));
		}

		if($finalize){
			// Mirror a compact snapshot of the latest FINAL set for the queue card
			$snap = array('final_set_id' => $setId, 'recorded_by' => $name, 'measured_at' => $now, 'values' => array());
			foreach($entries as $e){
				$k = (string)($e['vital_key'] ?? '');
				if($k === '') continue;
				$snap['values'][$k] = !empty($e['not_measured'])
					? 'not measured'
					: ((string)($e['value'] ?? '')) . (!empty($e['unit']) ? ' ' . $e['unit'] : '');
			}
			$this->db->where('id', $encId)->where('store_id', $storeId)
				->update('db_encounters', array('vitals_json' => json_encode($snap)));
		}
		$this->logEvent($encId, $storeId, 'vitals', null, null,
			$finalize ? 'Vitals finalized' : 'Vitals draft saved');
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE
			? array('error' => 'Save failed')
			: array('vitals_id' => $setId, 'status' => $finalize ? 'final' : 'draft');
	}

	/** All vitals sets for an encounter, each with its entries. */
	public function getVitals($encId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$sets = $this->db->where('encounter_id', $encId)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_encounter_vitals')->result();
		if(!$sets) return array();
		$entries = $this->db->where('store_id', $storeId)
			->where_in('vitals_id', array_map(function($s){ return (int)$s->id; }, $sets))
			->order_by('id', 'asc')->get('db_encounter_vital_entries')->result();
		$bySet = array();
		foreach($entries as $e){ $bySet[$e->vitals_id][] = $e; }
		foreach($sets as $s){ $s->entries = $bySet[$s->id] ?? array(); }
		return $sets;
	}

	/**
	 * Complete nursing intake — hands the encounter to the physiotherapist.
	 * Requires at least one FINAL vitals set (a set may be entirely
	 * "not measured", but the nurse must have signed it off). Transition is
	 * permission-gated via moveStage (vitals_add).
	 */
	public function completeIntake($encId, $note = null){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if($enc->queue_stage !== 'nursing_intake'){
			return array('error' => 'Intake can only be completed from nursing intake');
		}
		$hasFinal = $this->db->where('encounter_id', $encId)->where('store_id', $storeId)
			->where('status', 'final')->count_all_results('db_encounter_vitals');
		if(!$hasFinal){
			return array('error' => 'Finalize a vitals set before completing intake');
		}
		$note = is_string($note) ? trim($note) : '';
		/* A treatment session hands the patient to the therapist with nothing
		   but a package behind it, so the nurse's note IS the hand-over: what
		   is being treated today and anything the therapist must know. It is
		   shown on the card at the therapist's stage. */
		if(($enc->visit_type ?? null) === 'treatment' && $note === ''){
			return array('error' => 'Leave the nurse hand-over note for this treatment before completing intake');
		}
		// One action, one history line: the hand-over is recorded as the intake
		// event itself rather than a second, generic stage-change line.
		$res = $this->moveStage($encId, 'waiting_physio', $note !== '' ? $note : null, 'intake_completed');
		if($res !== true) return $res;
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', array(
			'intake_completed_at' => date('Y-m-d H:i:s'),
			'intake_by'           => (int)$this->session->userdata('inv_userid') ?: null,
		));
		return true;
	}

	/**
	 * Assessment remark — the physio's completion note before Finance.
	 *
	 * REQUIRED before with_physio → awaiting_finance: it states how much the
	 * physio recommends and how many sessions. Re-saving replaces the remark
	 * until the visit leaves the therapist's hands; only a therapist (or MD)
	 * holding encounters_finalize may write it.
	 */
	public function saveAssessmentRemark($encId, $remark, $amount = null, $sessions = null){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if($this->_closedLocked($enc)) return array('error' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
		if(!in_array($enc->queue_stage, array('with_physio', 'waiting_physio', 'nursing_intake'))){
			return array('error' => 'The assessment remark is written while the therapist has the visit');
		}
		$remark = trim((string)$remark);
		if($remark === '') return array('error' => 'The assessment remark is required');
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', array(
			'assessment_remark'   => $remark,
			'recommended_amount'  => ($amount === null || $amount === '') ? null : round((float)$amount, 2),
			'recommended_sessions'=> ($sessions === null || $sessions === '') ? null : (int)$sessions,
			'remark_by'           => (int)$this->session->userdata('inv_userid') ?: null,
			'remark_at'           => date('Y-m-d H:i:s'),
		));
		$this->logEvent($encId, $storeId, 'assessment_remark', null, null, $remark);
		return true;
	}

	/**
	 * Assign the encounter to a staff user (clinician/handler). Caller holds
	 * encounters_add; the target user must belong to the encounter's branch
	 * scope unless the caller has clinical_cross_branch.
	 */
	public function assignTo($encId, $userId){
		$storeId = get_current_store_id();
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('error' => 'Visit not found');
		if($this->_closedLocked($enc)) return array('error' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
		$user = $this->db->where('id', (int)$userId)->where('store_id', $storeId)->get('db_users')->row();
		if(!$user) return array('error' => 'User not found in this store');
		$this->db->where('id', $encId)->where('store_id', $storeId)
			->update('db_encounters', array('assigned_to' => (int)$userId));
		$this->logEvent($encId, $storeId, 'assigned', null, null,
			'Assigned to ' . mp_first_name((int)$userId));
		return true;
	}

	/**
	 * Decide what this visit IS — an assessment or a treatment session. Only
	 * the waiting-for-nurse desk sets it, and only before the patient moves on,
	 * because from here the two journeys differ.
	 */
	public function setVisitType($encId, $type, $nurseUserId = null){
		$storeId = get_current_store_id();
		if(!$this->db->field_exists('visit_type', 'db_encounters')){
			return array('status' => 'error', 'message' => 'The database update has not run yet. Sign out and open the sign-in page once — that applies pending updates — then try again.');
		}
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('status' => 'error', 'message' => 'Visit not found');
		/* An untyped visit can be classified wherever it sits — that is how
		   visits raised before visit type existed find their journey, including
		   closed ones being back-filled for history. Once a visit HAS a type it
		   is on a route, so only the registration desk may change it mid-visit. */
		if(!empty($enc->visit_type)){
			if($this->_closedLocked($enc)) return array('status' => 'error', 'message' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
			if(!in_array($enc->queue_stage, array('waiting_nurse', 'nursing_intake'), true)){
				return array('status' => 'error', 'message' => 'This visit already has a type — change it at the registration desk');
			}
		}
		if(!in_array($type, array('assessment', 'treatment'), true)){
			return array('status' => 'error', 'message' => 'Choose assessment or treatment session');
		}
		/* The treatment journey is only real if a PAID session stands behind it:
		   the visit draws one from a package. Nothing is drawn here — the desk
		   RESERVES the session so the package cannot be over-committed, and it is
		   drawn when the visit closes. Without this a treatment visit could reach
		   the therapist with no session bill and no session available at all. */
		$openSession = $this->_openVisitSession($encId, $storeId);
		$releasedBooking = false;
		if($type === 'treatment'){
			if(!$openSession){
				$res = $this->reserveVisitSession($enc);
				if($res !== true) return $res;
			}
		} else if($openSession){
			// Retyped to Assessment: this visit no longer draws the package.
			// When the visit was raised FROM a session's booking, the booking is
			// released back to the diary — it is the patient's paid slot and a
			// desk decision about today's visit must not destroy it. Only a
			// session reserved by this visit itself is cancelled outright.
			if(!empty($enc->checkin_key) && $enc->checkin_key === 'sess:' . (int)$openSession->id){
				$this->db->where('id', (int)$openSession->id)->update('db_treatment_sessions', array(
					'encounter_id' => null, 'updated_at' => date('Y-m-d H:i:s'),
				));
				$releasedBooking = true;
			} else {
				$this->load->model('Sessions_model', 'sessions');
				$this->sessions->cancel((int)$openSession->id, 'Visit retyped as assessment');
			}
		}
		$update = array('visit_type' => $type);
		$nurseName = null;
		if($nurseUserId){
			$nurse = $this->db->where('id', (int)$nurseUserId)->where('store_id', $storeId)->get('db_users')->row();
			if(!$nurse) return array('status' => 'error', 'message' => 'That nurse is not on this store');
			$update['nurse_user_id'] = (int)$nurseUserId;
			$nurseName = mp_first_name((int)$nurseUserId);
		} else if($type === 'assessment'){
			$update['nurse_user_id'] = null;
		}
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', $update);
		$this->logEvent($encId, $storeId, 'visit_type', $enc->visit_type ?: null, $type,
			($type === 'assessment' ? 'Assessment visit' : 'Treatment session')
			. ($nurseName ? ' — ' . $nurseName . ' tasked' : ''));
		/* The desk must never be left guessing what happened to the patient's
		   paid session — say it in the reply, not just in the history. */
		if($type === 'treatment'){
			$msg = 'Treatment session — one session is held from the package and is drawn when the visit closes';
		} else if($releasedBooking){
			$msg = 'Assessment — the booked treatment session has gone back to the diary, so the patient keeps that paid session. Book it again when they come for treatment';
		} else if($openSession){
			$msg = 'Assessment — the session this visit was holding has been released back onto the package';
		} else {
			$msg = 'Assessment — this visit is billed as an assessment and draws nothing from a package';
		}
		return array('status' => 'success', 'message' => $msg, 'released_session' => $releasedBooking);
	}

	/** Point a nurse at a treatment-session patient so the board shows who owns them. */
	public function assignNurse($encId, $userId){
		$storeId = get_current_store_id();
		if(!$this->db->field_exists('nurse_user_id', 'db_encounters')){
			return array('status' => 'error', 'message' => 'The database update has not run yet. Sign out and open the sign-in page once — that applies pending updates — then try again.');
		}
		$enc = $this->getEncounter($encId, $storeId);
		if(!$enc) return array('status' => 'error', 'message' => 'Visit not found');
		if($this->_closedLocked($enc)) return array('status' => 'error', 'message' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
		$nurse = $this->db->where('id', (int)$userId)->where('store_id', $storeId)->get('db_users')->row();
		if(!$nurse) return array('status' => 'error', 'message' => 'That nurse is not on this store');
		$this->db->where('id', $encId)->where('store_id', $storeId)
			->update('db_encounters', array('nurse_user_id' => (int)$userId));
		$this->logEvent($encId, $storeId, 'assigned', null, null,
			mp_first_name((int)$userId) . ' tasked');
		return true;
	}

	/**
	 * Send this patient to a named therapist — or re-route them to another one.
	 *
	 * The stage does NOT move. This only says WHO the patient is waiting for, so
	 * the therapist can see their own patients on the board before anybody taps
	 * "Start Consult", and reception can move a patient off a therapist who is
	 * busy or away. The therapist who eventually starts the visit still becomes
	 * the recorded clinician (see moveStage), so a wrong routing never leaves the
	 * wrong name on the treatment record.
	 */
	public function routeTherapist($encId, $userId){
		$storeId = get_current_store_id();
		if(!function_exists('physio_therapist_select')){ $this->load->helper('physio'); }
		$enc = $this->getEncounter((int)$encId, $storeId);
		if(!$enc) return array('status' => 'error', 'message' => 'Visit not found');
		if($this->_closedLocked($enc)) return array('status' => 'error', 'message' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.');
		$userId = (int)$userId;
		if(!$userId) return array('status' => 'error', 'message' => 'Choose who the patient should see');
		$allowed = array();
		foreach(physio_therapist_select($storeId) as $t){ $allowed[(int)$t->id] = $t; }
		if(!isset($allowed[$userId])){
			return array('status' => 'error', 'message' => 'That staff member is not set up to treat patients on this store');
		}
		$from = (int)($enc->clinician_user_id ?? 0);
		$toName = $this->_staffName($userId);
		if($from === $userId){
			return array('status' => 'success', 'message' => $toName . ' already has this patient');
		}
		$this->db->where('id', (int)$encId)->where('store_id', $storeId)
			->update('db_encounters', array('clinician_user_id' => $userId));
		$fromName = $from ? $this->_staffName($from) : '';
		$this->logEvent((int)$encId, $storeId, 'reassigned',
			$from ? 'user:' . $from : null, 'user:' . $userId,
			$from ? 'Moved from ' . $fromName . ' to ' . $toName : 'Sent to ' . $toName);
		return array('status' => 'success', 'message' => $from
			? 'Re-routed from ' . $fromName . ' to ' . $toName
			: 'Sent to ' . $toName . ' — the patient now shows on their queue');
	}

	/** A person's name for a message or the history feed. A profile with no name
	 *  recorded is shown by sign-in name rather than the generic "System". */
	private function _staffName($userId){
		$userId = (int)$userId;
		if($userId <= 0) return 'a staff member';
		$name = mp_first_name($userId);
		if($name !== '' && $name !== 'System') return $name;
		$row = $this->db->select('username')->where('id', $userId)->get('db_users')->row();
		return $row && trim((string)$row->username) !== '' ? $row->username : 'a staff member';
	}

	/** The package session this visit draws from — its still-open session row. */
	private function _openVisitSession($encId, $storeId){
		if(!$this->db->table_exists('db_treatment_sessions')) return null;
		return $this->db->where('encounter_id', (int)$encId)->where('store_id', (int)$storeId)
			->where_in('status', array('scheduled','checked_in','in_progress','interrupted'))
			->order_by('id', 'desc')->get('db_treatment_sessions')->row();
	}

	/** Can this visit still be a treatment session? A session is reserved on it,
	 *  or the patient has paid sessions left on a package. */
	public function visitSessionCovered($enc){
		if($this->_openVisitSession((int)$enc->id, (int)$enc->store_id)) return true;
		$this->load->model('Sessions_model', 'sessions');
		$ents = $this->sessions->activeEntitlements(array((int)$enc->patient_id));
		return !empty($ents[(int)$enc->patient_id]);
	}

	/**
	 * Reserve a package session for this visit — the gate on the treatment
	 * journey. Returns true, or the plain-English reason it cannot run.
	 */
	public function reserveVisitSession($enc){
		$storeId = (int)$enc->store_id;
		if(!$this->db->table_exists('db_treatment_sessions') || !$this->db->table_exists('db_plan_entitlements')){
			return array('status' => 'error', 'message' => 'Session packages are not installed on this database yet — sign out, open the sign-in page once to apply pending updates, then try again.');
		}
		$this->load->model('Sessions_model', 'sessions');
		$ents = $this->sessions->activeEntitlements(array((int)$enc->patient_id));
		$ent = isset($ents[(int)$enc->patient_id]) ? $ents[(int)$enc->patient_id] : null;
		if(!$ent){
			return array('status' => 'error', 'message' => 'This patient has no paid sessions on a package — a treatment session must draw one. Sell a package first, or take the Assessment route and raise a bill.');
		}
		$patient = $this->db->where('id', (int)$enc->patient_id)->where('store_id', $storeId)->get('db_patients')->row();
		// The value one session consumes out of what was paid for the package.
		$fee = (float)$ent->units_total > 0 ? round((float)$ent->funded_amount / (float)$ent->units_total, 2) : 0;
		$res = $this->sessions->schedule(array(
			'entitlement_id' => (int)$ent->id,
			'customer_id'    => (int)($patient->customer_id ?? 0),
			'encounter_id'   => (int)$enc->id,
			'branch_id'      => (int)$enc->warehouse_id ?: null,
			'clinician_id'   => (int)$enc->clinician_user_id ?: null,
			'scheduled_at'   => date('Y-m-d H:i:s'),
			'fee'            => $fee,
		));
		if(empty($res['ok'])){
			return array('status' => 'error', 'message' => !empty($res['error']) ? $res['error'] : 'Could not reserve a session on this package');
		}
		return true;
	}

	/**
	 * Draw the session when a treatment visit closes: exactly one unit off the
	 * package, never two. Idempotent — a retried close finds the session already
	 * completed and draws nothing more.
	 */
	private function _drawVisitSession($enc){
		if(!$this->db->table_exists('db_treatment_sessions')) return true;
		$storeId = (int)$enc->store_id;
		$this->load->model('Sessions_model', 'sessions');
		$row = $this->db->where('encounter_id', (int)$enc->id)->where('store_id', $storeId)
			->order_by('id', 'desc')->get('db_treatment_sessions')->row();
		if($row && ($row->status === 'completed' || !empty($row->fee_posted))){
			return true; // already drawn — a retry must never draw twice
		}
		if(!$row){
			$res = $this->reserveVisitSession($enc);
			if($res !== true) return array('error' => $res['message']);
			$row = $this->_openVisitSession((int)$enc->id, $storeId);
		}
		if(!$row) return array('error' => 'the session could not be reserved');
		/* A double-tap at the desk can leave a second reservation on the same
		   visit — release the extras so the package is never quietly
		   over-committed by a visit that draws only one session. */
		$extras = $this->db->where('encounter_id', (int)$enc->id)->where('store_id', $storeId)
			->where_in('status', array('scheduled','checked_in','in_progress','interrupted'))
			->where('id !=', (int)$row->id)->get('db_treatment_sessions')->result();
		foreach($extras as $o){
			$this->sessions->cancel((int)$o->id, 'Extra reservation for this visit');
		}
		if(in_array($row->status, array('scheduled', 'checked_in'), true)){
			$this->sessions->start((int)$row->id);
		}
		$done = $this->sessions->complete((int)$row->id);
		return !empty($done['ok']) ? true : array('error' => !empty($done['error']) ? $done['error'] : 'the session could not be completed');
	}
}
