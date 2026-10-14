<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Appointments — physiotherapy bookings.
 *
 * Concurrency: the clinician slot is guarded two ways —
 *   1. db_appointments.uk_store_slot (store_id, slot_key) — unique while the
 *      booking occupies the slot; cleared on cancel/no_show/reschedule.
 *   2. An in-transaction overlap check covering the duration window.
 * Requested/proposed bookings also hold the slot so a tentative request
 * cannot be double-booked silently.
 */
class Appointments_model extends CI_Model {

	const STATUSES = array('requested','proposed','confirmed','checked_in','completed','cancelled','no_show');
	const SLOT_HOLDING = array('requested','proposed','confirmed');
	const DEFAULT_DURATION = 30;

	public function __construct(){
		parent::__construct();
	}

	private function _slotKey($staffId, $scheduledAt){
		return $staffId ? ((int)$staffId . '|' . date('Y-m-d H:i', strtotime($scheduledAt))) : null;
	}

	/** Overlapping active bookings for the same clinician. */
	public function conflicts($storeId, $staffId, $scheduledAt, $durationMin = null, $excludeId = 0){
		if(!$staffId || !$scheduledAt) return array();
		$durationMin = $durationMin ?: self::DEFAULT_DURATION;
		$start = date('Y-m-d H:i:s', strtotime($scheduledAt));
		$end   = date('Y-m-d H:i:s', strtotime($scheduledAt . ' +' . (int)$durationMin . ' minutes'));
		$this->db->select('id, patient_id, scheduled_at, duration_min, status, booking_ref')
			->from('db_appointments')
			->where('store_id', $storeId)
			->where('staff_user_id', $staffId)
			->where_in('status', self::SLOT_HOLDING)
			->where('scheduled_at <', $end)
			->where("DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_min,".(int)self::DEFAULT_DURATION.") MINUTE) > '" . $start . "'", null, FALSE);
		if($excludeId) $this->db->where('id !=', $excludeId);
		return $this->db->get()->result();
	}

	public function getAppointments($storeId, $filters = array()){
		$this->db->select('a.*, c.customer_name, c.mobile, s.service_name, u.username AS staff_name, u.role_id AS staff_role_id, w.warehouse_name AS branch_name')
			->from('db_appointments a')
			->join('db_patients p', 'p.id = a.patient_id AND p.store_id = a.store_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->join('db_services s', 's.id = a.service_id', 'left')
			->join('db_users u', 'u.id = a.staff_user_id', 'left')
			->join('db_warehouse w', 'w.id = a.warehouse_id', 'left')
			->where('a.store_id', $storeId);
		if(!empty($filters['branch_ids'])){
			$this->db->group_start()->where_in('a.warehouse_id', $filters['branch_ids'])->or_where('a.warehouse_id IS NULL', null, FALSE)->group_end();
		}
		if(!empty($filters['status'])) $this->db->where('a.status', $filters['status']);
		if(!empty($filters['date'])) $this->db->where('DATE(a.scheduled_at) =', $filters['date']);
		if(!empty($filters['date_from'])) $this->db->where('DATE(a.scheduled_at) >=', $filters['date_from']);
		if(!empty($filters['date_to'])) $this->db->where('DATE(a.scheduled_at) <=', $filters['date_to']);
		if(!empty($filters['staff_user_id'])) $this->db->where('a.staff_user_id', $filters['staff_user_id']);
		if(!empty($filters['patient_id'])) $this->db->where('a.patient_id', $filters['patient_id']);
		return $this->db->order_by('a.scheduled_at', 'asc')->limit(300)->get()->result();
	}

	public function getAppointment($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_appointments')->row();
	}

	public function getEvents($appointmentId, $storeId = null){
		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db->where('appointment_id', $appointmentId)->where('store_id', $storeId)
			->order_by('id', 'asc')->get('db_appointment_events')->result();
		foreach($rows as $r){
			$r->created_by_name = mp_first_name($r->created_by, $r->created_by_name);
		}
		return $rows;
	}

	private function _logEvent($apptId, $storeId, $event, $from = null, $to = null, $note = null){
		if(!function_exists('mp_first_name')) $this->load->helper('physio');
		$this->db->insert('db_appointment_events', array(
			'store_id' => $storeId, 'appointment_id' => $apptId, 'event' => $event,
			'from_value' => $from, 'to_value' => $to, 'note' => $note,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_by_name' => mp_first_name($this->session->userdata('inv_userid'), $this->session->userdata('first_name')),
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Book an appointment. Returns appointment id, or array('error'=>msg).
	 * Deceased patients are refused — no ordinary booking flow for them.
	 */
	public function book(array $d){
		$storeId = get_current_store_id();
		$patientId = (int)($d['patient_id'] ?? 0);
		$staffId   = (int)($d['staff_user_id'] ?? 0);
		$when      = $d['scheduled_at'] ?? null;
		$duration  = (int)($d['duration_min'] ?? 0) ?: self::DEFAULT_DURATION;

		if(!$patientId || !$when) return array('error' => 'Patient and scheduled time are required');

		$patient = $this->db->select('id, deceased, status')->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$patient) return array('error' => 'Patient not found');
		if($patient->deceased) return array('error' => 'Deceased patient — bookings are blocked');
		if(!$patient->status) return array('error' => 'Patient record is inactive');

		$this->db->trans_start();
		if($staffId && $this->conflicts($storeId, $staffId, $when, $duration)){
			$this->db->trans_complete();
			return array('error' => 'Clinician already has a booking overlapping that time');
		}
		$count_id = get_count_id('db_appointments', $storeId);
		$insert = array(
			'store_id'      => $storeId,
			'warehouse_id'  => (int)($d['warehouse_id'] ?? 0) ?: null,
			'patient_id'    => $patientId,
			'lead_id'       => (int)($d['lead_id'] ?? 0) ?: null,
			'service_id'    => (int)($d['service_id'] ?? 0) ?: null,
			'staff_user_id' => $staffId ?: null,
			'count_id'      => $count_id,
			'booking_ref'   => 'BK-'.str_pad($count_id, 5, '0', STR_PAD_LEFT),
			'scheduled_at'  => date('Y-m-d H:i:s', strtotime($when)),
			'duration_min'  => $duration,
			'status'        => in_array(($d['status'] ?? ''), self::STATUSES) ? $d['status'] : 'requested',
			'source'        => in_array(($d['source'] ?? ''), array('manual','lead','portal','walk_in')) ? $d['source'] : 'manual',
			'notes'         => $d['notes'] ?? null,
			'slot_key'      => $this->_slotKey($staffId, $when),
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'created_by'    => $this->session->userdata('inv_username') ?: 'system',
		);
		$this->db->insert('db_appointments', $insert);
		$id = (int)$this->db->insert_id();
		if($id) $this->_logEvent($id, $storeId, 'booked', null, $insert['status'], $insert['notes']);
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE || !$id){
			$err = $this->db->error();
			if(($err['code'] ?? 0) == 1062) return array('error' => 'That clinician slot was just taken — pick another time');
			return array('error' => 'Booking failed');
		}
		return $id;
	}

	/**
	 * Status transition with event log. Arrived → handled by check-in which
	 * creates the encounter; 'checked_in' is set there, not here.
	 */
	public function transition($id, $to, $note = null){
		$storeId = get_current_store_id();
		$appt = $this->getAppointment($id, $storeId);
		if(!$appt) return array('error' => 'Appointment not found');
		$allowed = array(
			'requested'  => array('proposed','confirmed','cancelled'),
			'proposed'   => array('confirmed','cancelled'),
			'confirmed'  => array('cancelled','no_show'),
			'checked_in' => array('completed','cancelled'),
			'completed'  => array(),
			'cancelled'  => array(),
			'no_show'    => array(),
		);
		if(!in_array($to, self::STATUSES) || !in_array($to, $allowed[$appt->status] ?? array())){
			return array('error' => "Cannot move from {$appt->status} to {$to}");
		}
		$update = array('status' => $to, 'updated_at' => date('Y-m-d H:i:s'));
		if($to === 'cancelled' || $to === 'no_show') $update['slot_key'] = null;
		if($to === 'cancelled') $update['cancel_reason'] = $note;
		$this->db->trans_start();
		$this->db->where('id', $id)->where('store_id', $storeId)->update('db_appointments', $update);
		$this->_logEvent($id, $storeId, $to, $appt->status, $to, $note);
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE) return array('error' => 'Update failed');

		/* A booking that was already checked in has a visit on the Care Queue.
		   Killing the booking while that visit stays open leaves the patient in
		   "Waiting for Nurse" for good, and any session ticket drawn at
		   check-in bound to a dead visit. End the visit and hand the session
		   back to the diary so the package still owes it. */
		if(in_array($to, array('cancelled','no_show'), true) && $appt->status === 'checked_in'){
			$openEnc = $this->db->select('id')->where('store_id', $storeId)->where('appointment_id', $id)
				->where('queue_stage !=', 'closed')->get('db_encounters')->row();
			$encId = $openEnc ? (int)$openEnc->id : 0;
			if($encId){
				$this->load->model('encounters_model', 'enc');
				$this->enc->closeUnattended($encId, 'Visit closed — the appointment was '
					. ($to === 'no_show' ? 'marked as did not attend' : 'cancelled')
					. ($note ? '. Reason: ' . $note : '.'), $to);
				$this->db->where('encounter_id', $encId)->where('store_id', $storeId)
					->where('status !=', 'completed')->where('fee_posted', 0)
					->update('db_treatment_sessions', array(
						'status' => 'scheduled', 'encounter_id' => null, 'checkin_key' => null,
						'checked_in_at' => null, 'updated_at' => date('Y-m-d H:i:s'),
					));
			}
		}
		return true;
	}

	/**
	 * Reschedule — keeps the row, logs the move, frees the old slot.
	 */
	public function reschedule($id, $newWhen, $reason = null){
		$storeId = get_current_store_id();
		$appt = $this->getAppointment($id, $storeId);
		if(!$appt) return array('error' => 'Appointment not found');
		if(!in_array($appt->status, array('requested','proposed','confirmed'))){
			return array('error' => 'Only pending or confirmed bookings can be rescheduled');
		}
		$duration = $appt->duration_min ?: self::DEFAULT_DURATION;
		$this->db->trans_start();
		if($appt->staff_user_id && $this->conflicts($storeId, $appt->staff_user_id, $newWhen, $duration, $id)){
			$this->db->trans_complete();
			return array('error' => 'Clinician already has a booking overlapping that time');
		}
		$upd = array(
			'scheduled_at' => date('Y-m-d H:i:s', strtotime($newWhen)),
			'slot_key'     => $this->_slotKey($appt->staff_user_id, $newWhen),
			'updated_at'   => date('Y-m-d H:i:s'),
		);
		// A queued/sent reminder referenced the old time — re-arm so the
		// next reminders cron run sends one for the new slot, AND suppress any
		// still-pending reminder row so the stale time is never delivered.
		if($this->db->field_exists('reminder_queued', 'db_appointments')){
			$upd['reminder_queued'] = 0;
		}
		$this->db->where('id', $id)->where('store_id', $storeId)->update('db_appointments', $upd);
		if($this->db->table_exists('db_notification_queue')){
			$this->db->where('store_id', $storeId)
				->where_in('status', array('queued', 'retry'))
				->like('event_key', 'appt.reminder.' . (int)$id, 'after')
				->update('db_notification_queue', array(
					'status' => 'suppressed', 'last_error' => 'superseded by reschedule',
				));
		}
		$this->_logEvent($id, $storeId, 'rescheduled', $appt->scheduled_at, $newWhen, $reason);
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE){
			$err = $this->db->error();
			return array('error' => ($err['code'] ?? 0) == 1062 ? 'That clinician slot was just taken — pick another time' : 'Reschedule failed');
		}
		return true;
	}
}
