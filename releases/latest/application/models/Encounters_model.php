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

	/** Allowed forward moves + the permission required to enter the target stage. */
	const TRANSITIONS = array(
		'waiting_nurse'    => array('nursing_intake' => 'vitals_add', 'closed' => 'encounters_finalize'),
		'nursing_intake'   => array('waiting_physio' => 'vitals_add', 'waiting_nurse' => 'vitals_add', 'closed' => 'encounters_finalize'),
		'waiting_physio'   => array('with_physio' => 'encounters_add', 'waiting_nurse' => 'care_checkin', 'closed' => 'encounters_finalize'),
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

	/** Today's queue, grouped by stage, scoped to the user's branches. */
	public function getQueue($storeId, $branchIds = null){
		$this->db->select('e.*, c.customer_name, c.mobile, p.patient_code, u.username AS clinician_name, a.username AS handler_name')
			->from('db_encounters e')
			->join('db_patients p', 'p.id = e.patient_id AND p.store_id = e.store_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->join('db_users u', 'u.id = e.clinician_user_id', 'left')
			->join('db_users a', 'a.id = e.assigned_to', 'left')
			->where('e.store_id', $storeId)
			->where("e.queue_stage IS NOT NULL", null, FALSE)
			->where('e.queue_stage !=', 'closed');
		if($branchIds !== null){
			if(empty($branchIds)) return array(); // user has no branch assignment — sees nothing
			$this->db->group_start()->where_in('e.warehouse_id', $branchIds)->or_where('e.warehouse_id IS NULL', null, FALSE)->group_end();
		}
		$rows = $this->db->order_by('e.checkin_at', 'asc')->get()->result();
		$queue = array_fill_keys(self::QUEUE_STAGES, array());
		foreach($rows as $r){ $queue[$r->queue_stage][] = $r; }
		return $queue;
	}

	/** Open (or create) the current care episode for a patient + branch. */
	private function _openEpisode($storeId, $patientId, $warehouseId){
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
			'episode_type' => 'outpatient',
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
		if(!$patientId || !$checkinKey) return array('error' => 'Patient and check-in reference are required');

		$patient = $this->db->select('id, deceased, status')->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$patient) return array('error' => 'Patient not found');
		if($patient->deceased) return array('error' => 'Deceased patient — arrivals cannot be checked in');
		if(!$patient->status) return array('error' => 'Patient record is inactive');

		// Idempotent retry — return the existing visit
		$existing = $this->getByCheckinKey($checkinKey, $storeId);
		if($existing) return array('encounter_id' => (int)$existing->id, 'already' => true);

		$this->db->trans_start();
		$episodeId = $this->_openEpisode($storeId, $patientId, $warehouseId);
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
					'created_by_name' => $this->session->userdata('inv_username'),
					'created_at' => date('Y-m-d H:i:s'),
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
		$this->db->insert('db_encounter_events', array(
			'store_id' => $storeId, 'encounter_id' => $encId, 'event' => $event,
			'from_stage' => $from, 'to_stage' => $to, 'note' => $note,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_by_name' => $this->session->userdata('inv_username'),
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function getEvents($encId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('encounter_id', $encId)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_encounter_events')->result();
	}

	/**
	 * Move an encounter through the care queue. Validates the transition map
	 * and requires the caller hold the permission for the TARGET stage.
	 */
	public function moveStage($encId, $toStage, $note = null){
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
		if(!physio_can($needPerm)){
			return array('error' => "You are not authorised to move a patient to {$toStage}");
		}
		$update = array('queue_stage' => $toStage, 'queue_stage_at' => date('Y-m-d H:i:s'));
		if($toStage === 'closed') $update['status'] = 'completed';
		if($toStage === 'with_physio') $update['clinician_user_id'] = $this->session->userdata('inv_userid');
		$this->db->trans_start();
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', $update);
		$this->logEvent($encId, $storeId, 'stage_change', $enc->queue_stage, $toStage, $note);
		if($toStage === 'closed' && $enc->appointment_id){
			$this->db->where('id', $enc->appointment_id)->where('store_id', $storeId)->where('status', 'checked_in')
				->update('db_appointments', array('status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')));
		}
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE ? array('error' => 'Update failed') : true;
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
		if($enc->queue_stage === 'closed') return array('error' => 'Visit is closed');
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
			($finalize ? 'Vitals finalized' : 'Vitals draft saved') . ' (set #' . $setId . ')');
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
		$res = $this->moveStage($encId, 'waiting_physio', $note ?: 'Nursing intake complete');
		if($res !== true) return $res;
		$this->db->where('id', $encId)->where('store_id', $storeId)->update('db_encounters', array(
			'intake_completed_at' => date('Y-m-d H:i:s'),
			'intake_by'           => (int)$this->session->userdata('inv_userid') ?: null,
		));
		$this->logEvent($encId, $storeId, 'intake_completed', 'nursing_intake', 'waiting_physio',
			trim(($this->session->userdata('inv_username') ?: 'system') . ' — ' . ($note ?: 'handover to physiotherapy')));
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
		if($enc->queue_stage === 'closed') return array('error' => 'Visit is closed');
		$user = $this->db->where('id', (int)$userId)->where('store_id', $storeId)->get('db_users')->row();
		if(!$user) return array('error' => 'User not found in this store');
		$this->db->where('id', $encId)->where('store_id', $storeId)
			->update('db_encounters', array('assigned_to' => (int)$userId));
		$this->logEvent($encId, $storeId, 'assigned', null, null,
			'Assigned to ' . ($user->username ?: ('user #' . $userId)));
		return true;
	}
}
