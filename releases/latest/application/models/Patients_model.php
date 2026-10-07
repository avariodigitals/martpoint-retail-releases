<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patients — clinical identity registry (db_patients) linked 1:1 to the
 * financial customer row (db_customers). Money stays on the customer;
 * clinical demographics and flags live here. Access is enforced by
 * physio_can() in the controller — no super-admin bypass.
 */
class Patients_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	const GENDERS = ['male','female','other'];
	const BLOOD_GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'];

	// ============== READ ==============

	public function getPatients($storeId = null, $status = '', $search = '', $limit = 500, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('p.*, c.customer_name, c.mobile, c.email, c.customer_code, c.tot_advance, c.sales_due')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.store_id', $storeId);
		if($status === 'deceased'){
			$this->db->where('p.deceased', 1);
		} elseif($status === 'inactive'){
			$this->db->where('p.status', 0)->where('p.deceased', 0);
		} elseif($status === 'active'){
			$this->db->where('p.status', 1)->where('p.deceased', 0);
		}
		if($search !== ''){
			$this->db->group_start()
				->like('c.customer_name', $search)
				->or_like('c.mobile', $search)
				->or_like('c.email', $search)
				->or_like('p.patient_code', $search)
				->or_like('c.customer_code', $search)
			->group_end();
		}
		return $this->db->order_by('p.id', 'desc')->limit($limit, $offset)->get()->result();
	}

	public function getPatient($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->select('p.*, c.customer_name, c.mobile, c.email, c.phone, c.address, c.city, c.customer_code, c.tot_advance, c.sales_due, c.opening_balance')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', $id)->where('p.store_id', $storeId)
			->get()->row();
	}

	public function getPatientStats($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->select('COUNT(*) AS total, SUM(status=1 AND deceased=0) AS active, SUM(deceased=1) AS deceased, SUM(status=0 AND deceased=0) AS inactive')
			->where('store_id', $storeId)->get('db_patients')->row();
		$month = $this->db->where('store_id', $storeId)
			->where('created_date >=', date('Y-m-01'))
			->count_all_results('db_patients');
		return array(
			'total'    => (int)($row->total ?? 0),
			'active'   => (int)($row->active ?? 0),
			'deceased' => (int)($row->deceased ?? 0),
			'inactive' => (int)($row->inactive ?? 0),
			'new_this_month' => (int)$month,
		);
	}

	/**
	 * Duplicate-patient candidates: same mobile, or same name + dob.
	 * Registration warns on these; silent duplicate creation is never allowed
	 * without an explicit confirm flag.
	 */
	public function findDuplicates($storeId, $mobile, $name, $dob, $excludeId = 0){
		$storeId = $storeId ?: get_current_store_id();
		$dups = array();
		if(!empty($mobile)){
			$rows = $this->db->select('p.id, p.patient_code, c.customer_name, c.mobile, p.dob')
				->from('db_patients p')
				->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
				->where('p.store_id', $storeId)->where('c.mobile', $mobile)
				->where('p.deceased', 0)->where('p.status', 1);
			if($excludeId) $rows = $rows->where('p.id !=', $excludeId);
			foreach($rows->get()->result() as $r){ $dups[$r->id] = $r; }
		}
		if(!empty($name) && !empty($dob)){
			$rows = $this->db->select('p.id, p.patient_code, c.customer_name, c.mobile, p.dob')
				->from('db_patients p')
				->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
				->where('p.store_id', $storeId)
				->where('LOWER(TRIM(c.customer_name)) =', strtolower(trim($name)))
				->where('p.dob', $dob)
				->where('p.deceased', 0)->where('p.status', 1);
			if($excludeId) $rows = $rows->where('p.id !=', $excludeId);
			foreach($rows->get()->result() as $r){ $dups[$r->id] = $r; }
		}
		return array_values($dups);
	}

	// ============== WRITE ==============

	/**
	 * Register or update a patient.
	 * $customer = ['name','mobile','email','phone','address','city', 'link_customer_id'?(explicit reuse)]
	 * $patient  = db_patients field array (already whitelisted by controller)
	 * Returns patient id on success, or array('error'=>msg), or
	 * array('duplicates'=>[...]) when unconfirmed matches exist.
	 *
	 * Model-level duplicate gate: on CREATE, if the customer is not an
	 * explicit link and live patients already match on mobile or
	 * name+DOB, the insert refuses unless $customer['confirm_duplicate']
	 * is truthy — controllers cannot bypass the confirmation by calling
	 * this method directly.
	 */
	public function savePatient(array $patient, array $customer, $id = null){
		/*
		 * Store scope.
		 *
		 * get_current_store_id() reads the SESSION, which is empty for a public
		 * request — the booking widget has no login. Every other method here
		 * already accepts an explicit store id, and this one now does too, so a
		 * keyed public endpoint can create a patient against the store its key
		 * belongs to instead of inserting store_id = NULL.
		 *
		 * An explicit value wins; otherwise fall back to the session for the
		 * normal logged-in path, unchanged.
		 */
		$storeId = $patient['store_id'] ?? $customer['store_id'] ?? null;
		if(empty($storeId)){ $storeId = get_current_store_id(); }

		if(!$id && empty($customer['link_customer_id']) && empty($customer['confirm_duplicate'])){
			$dups = $this->findDuplicates($storeId, $customer['mobile'] ?? '', $customer['name'] ?? '', $patient['dob'] ?? '');
			if($dups){
				return array('error' => 'Possible duplicate patient — confirm this is a different person', 'duplicates' => $dups);
			}
		}

		$this->db->trans_start();

		if($id){
			$existing = $this->getPatient($id, $storeId);
			if(!$existing){ $this->db->trans_complete(); return array('error' => 'Patient not found'); }
			$customerId = (int)$existing->customer_id;
			// Keep linked customer contact fields in sync
			$this->db->where('id', $customerId)->where('store_id', $storeId)->update('db_customers', array(
				'customer_name' => $customer['name'],
				'mobile'        => $customer['mobile'],
				'email'         => $customer['email'],
				'phone'         => $customer['phone'],
				'address'       => $customer['address'],
				'city'          => $customer['city'],
			));
			$this->db->where('id', $id)->where('store_id', $storeId)->update('db_patients', $patient);
		} else {
			$customerId = !empty($customer['link_customer_id']) ? (int)$customer['link_customer_id'] : 0;
			if($customerId){
				$ok = $this->db->where('id', $customerId)->where('store_id', $storeId)->count_all_results('db_customers');
				if(!$ok){ $this->db->trans_complete(); return array('error' => 'Linked customer not found'); }
			} else {
				// Create the financial identity (db_customers) for this patient
				$count_id = get_count_id('db_customers', $storeId);
				$this->db->insert('db_customers', array(
					'store_id'      => $storeId,
					'count_id'      => $count_id,
					'customer_code' => get_init_code('customer', $storeId),
					'customer_name' => $customer['name'],
					'mobile'        => $customer['mobile'],
					'email'         => $customer['email'],
					'phone'         => $customer['phone'],
					'address'       => $customer['address'],
					'city'          => $customer['city'],
					'birthday'      => $patient['dob'] ?: null,
					'status'        => 1,
					'created_date'  => date('Y-m-d'),
					'created_time'  => date('H:i:s'),
					'created_by'    => $this->session->userdata('inv_username') ?: 'system',
					'system_ip'     => $this->input->ip_address(),
				));
				$customerId = (int)$this->db->insert_id();
				if(!$customerId){ $this->db->trans_complete(); return array('error' => 'Failed to create customer record'); }
			}

			$patient['store_id']     = $storeId;
			$patient['customer_id']  = $customerId;
			$patient['count_id']     = get_count_id('db_patients', $storeId);
			$patient['created_date'] = date('Y-m-d');
			$patient['created_time'] = date('H:i:s');
			$patient['created_by']   = $this->session->userdata('inv_username') ?: 'system';
			$patient['system_ip']    = $this->input->ip_address();
			// patient_code comes from the auto-increment id, not count_id — the
			// code is unique by construction even under simultaneous registrations.
			$patient['patient_code'] = null;
			$this->db->insert('db_patients', $patient);
			$id = (int)$this->db->insert_id();
			if(!$id){ $this->db->trans_complete(); return array('error' => 'Failed to create patient record'); }
			$code = 'PT-'.str_pad($id, 5, '0', STR_PAD_LEFT);
			$this->db->where('id', $id)->where('store_id', $storeId)
				->update('db_patients', array('patient_code' => $code));
			$this->logEvent($id, $storeId, 'registered', null, array('patient_code' => $code));
		}

		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE){ return array('error' => 'Database error'); }
		return $id;
	}

	public function setStatus($id, $status, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)
			->update('db_patients', array('status' => $status ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')));
	}

	/** Append an audited action to db_patient_events (actor + reason + time). */
	public function logEvent($patientId, $storeId, $event, $reason = null, $meta = null){
		if(!$this->db->table_exists('db_patient_events')) return;
		$this->db->insert('db_patient_events', array(
			'store_id'        => $storeId,
			'patient_id'      => $patientId,
			'event'           => $event,
			'reason'          => $reason,
			'meta_json'       => $meta ? json_encode($meta) : null,
			'created_by'      => $this->session->userdata('inv_userid'),
			'created_by_name' => $this->session->userdata('inv_username'),
			'created_at'      => date('Y-m-d H:i:s'),
		));
	}

	/**
	 * Record a patient as deceased — reason is mandatory; the action is logged
	 * to db_patient_events with actor + timestamp. Deceased suppresses
	 * reminders, portal access and new bookings/arrivals.
	 */
	public function markDeceased($id, $date, $reason, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(trim((string)$reason) === '') return array('error' => 'A reason is required to record a death');
		$this->db->trans_start();
		$this->db->where('id', $id)->where('store_id', $storeId)
			->update('db_patients', array(
				'deceased' => 1,
				'deceased_date' => $date ?: date('Y-m-d'),
				'deceased_recorded_by' => $this->session->userdata('inv_userid'),
				'deceased_notes' => $reason,
				'portal_status' => 'suspended',
				'updated_at' => date('Y-m-d H:i:s'),
			));
		$this->logEvent($id, $storeId, 'deceased', $reason, array('deceased_date' => $date ?: date('Y-m-d')));
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE ? array('error' => 'Update failed') : true;
	}

	/**
	 * Correct a mistaken deceased flag. Audited — the correction reason is
	 * stored on the event, not the patient row, so the history is never erased.
	 */
	public function unmarkDeceased($id, $reason, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(trim((string)$reason) === '') return array('error' => 'A correction reason is required');
		$this->db->trans_start();
		$this->db->where('id', $id)->where('store_id', $storeId)
			->update('db_patients', array(
				'deceased' => 0,
				'deceased_date' => null,
				'deceased_recorded_by' => null,
				'deceased_notes' => null,
				'updated_at' => date('Y-m-d H:i:s'),
			));
		$this->logEvent($id, $storeId, 'deceased_corrected', $reason);
		$this->db->trans_complete();
		return $this->db->trans_status() === FALSE ? array('error' => 'Update failed') : true;
	}

	/**
	 * Mark a registry entry as a duplicate of another patient (soft-merge for
	 * review queue). The loser keeps its row — clinical data is never deleted.
	 */
	public function markDuplicate($dupId, $keepId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$dupId || !$keepId || $dupId == $keepId) return false;
		$res = $this->db->where('id', $dupId)->where('store_id', $storeId)
			->update('db_patients', array(
				'duplicate_of_id' => $keepId,
				'status' => 0,
				'portal_status' => 'suspended',
				'updated_at' => date('Y-m-d H:i:s'),
			));
		if($res) $this->logEvent($dupId, $storeId, 'duplicate_flagged', null, array('kept_patient_id' => $keepId));
		return $res;
	}

	/** Profile audit trail — who did what to this record, and why. */
	public function getEvents($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_patient_events')) return array();
		return $this->db->where('store_id', $storeId)->where('patient_id', $patientId)
			->order_by('id', 'desc')->limit(50)->get('db_patient_events')->result();
	}

	// ============== RELATED LISTS (profile page) ==============

	public function getEpisodes($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_care_episodes')) return array();
		return $this->db->where('store_id', $storeId)->where('patient_id', $patientId)
			->order_by('id', 'desc')->get('db_care_episodes')->result();
	}

	public function getAppointments($patientId, $storeId = null, $limit = 20){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_appointments')) return array();
		return $this->db->select('a.*, s.service_name, u.username AS staff_name')
			->from('db_appointments a')
			->join('db_services s', 's.id = a.service_id', 'left')
			->join('db_users u', 'u.id = a.staff_user_id', 'left')
			->where('a.store_id', $storeId)->where('a.patient_id', $patientId)
			->order_by('a.scheduled_at', 'desc')->limit($limit)->get()->result();
	}
}
