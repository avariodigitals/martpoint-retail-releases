<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leads_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	const STATUSES = ['new','contacted','qualified','converted','lost'];
	const SOURCES = ['manual','storefront','walk_in','referral','whatsapp','phone','other'];

	public function getLeads($storeId = null, $status = '', $search = '', $limit = 200, $offset = 0){
		try{
			$storeId = $storeId ?: get_current_store_id();
			if($this->db->field_exists('assigned_to', 'db_leads')){
				$this->db->select('l.*, u.username AS assigned_name')
					->from('db_leads l')
					->join('db_users u', 'u.id = l.assigned_to', 'left');
			} else {
				$this->db->select('l.*')->from('db_leads l');
			}
			if($this->db->field_exists('patient_id', 'db_leads') && $this->db->table_exists('db_patients')){
				$this->db->select('p.patient_code')->join('db_patients p', 'p.id = l.patient_id', 'left');
			}
			$this->db->where('l.store_id', $storeId);
			if($status !== '' && in_array($status, self::STATUSES)) $this->db->where('l.status', $status);
			if($search !== ''){
				$this->db->group_start()
					->like('l.name', $search)
					->or_like('l.phone', $search)
					->or_like('l.email', $search)
					->or_like('l.interest', $search)
				->group_end();
			}
			return $this->db->order_by('l.id', 'desc')->limit($limit, $offset)->get()->result();
		} catch(Exception $e){ return []; }
	}

	public function getLead($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_leads')->row();
	}

	public function getLeadStats($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$stats = array_fill_keys(self::STATUSES, 0);
		try{
			$rows = $this->db->select('status, COUNT(*) AS cnt')->where('store_id', $storeId)->group_by('status')->get('db_leads')->result();
			foreach($rows as $r){ if(isset($stats[$r->status])) $stats[$r->status] = (int)$r->cnt; }
		} catch(Exception $e){}
		$stats['total'] = array_sum($stats);
		return $stats;
	}

	public function saveLead($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->where('store_id', $data['store_id'])->update('db_leads', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_leads', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function updateStatus($id, $status, $storeId = null){
		if(!in_array($status, self::STATUSES)) return false;
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->update('db_leads', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
	}

	public function deleteLead($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete('db_leads');
	}

	// ============== ACTIVITIES / OWNERSHIP (stage 2) ==============

	public function addActivity($leadId, $type, $note = null, $meta = null, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_lead_activities')) return false;
		return $this->db->insert('db_lead_activities', array(
			'store_id' => $storeId,
			'lead_id' => $leadId,
			'activity_type' => $type,
			'note' => $note,
			'meta_json' => $meta ? json_encode($meta) : null,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_by_name' => $this->session->userdata('inv_username'),
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function getActivities($leadId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_lead_activities')) return array();
		return $this->db->where('lead_id', $leadId)->where('store_id', $storeId)
			->order_by('id', 'desc')->limit(100)->get('db_lead_activities')->result();
	}

	/** Assign an owner (db_users.id) and/or follow-up date. */
	public function assign($id, $userId, $followupAt = null, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$lead = $this->getLead($id, $storeId);
		if(!$lead) return false;
		$update = array('updated_at' => date('Y-m-d H:i:s'));
		if($this->db->field_exists('assigned_to', 'db_leads')) $update['assigned_to'] = $userId ?: null;
		if($this->db->field_exists('next_followup_at', 'db_leads')) $update['next_followup_at'] = $followupAt ?: null;
		$res = $this->db->where('id', $id)->where('store_id', $storeId)->update('db_leads', $update);
		if($res){
			$who = $userId ? $this->db->select('username')->where('id', $userId)->get('db_users')->row() : null;
			$this->addActivity($id, 'assignment', null, array('assigned_to' => $userId, 'assigned_name' => $who->username ?? null, 'next_followup_at' => $followupAt), $storeId);
		}
		return $res;
	}

	/**
	 * Convert a lead to a physiotherapy patient — idempotent: a converted lead
	 * returns its existing patient, never creates a second patient, customer
	 * or financial balance.
	 */
	public function convertToPatient($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$lead = $this->getLead($id, $storeId);
		if(!$lead) return array('error' => 'Lead not found');
		if(!empty($lead->patient_id)) return array('patient_id' => (int)$lead->patient_id, 'already' => true);

		// Link an existing patient on the same contact number rather than
		// creating a duplicate registry entry.
		$this->load->model('patients_model', 'patients');
		$patientId = 0;
		if(!empty($lead->phone)){
			$existing = $this->db->select('p.id')
				->from('db_patients p')
				->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
				->where('p.store_id', $storeId)->where('c.mobile', $lead->phone)
				->where('p.status', 1)->where('p.deceased', 0)
				->get()->row();
			if($existing) $patientId = (int)$existing->id;
		}
		if(!$patientId){
			$result = $this->patients->savePatient(
				array('updated_at' => date('Y-m-d H:i:s')),
				array(
					'name'    => $lead->name,
					'mobile'  => $lead->phone ?: '',
					'email'   => $lead->email ?: '',
					'phone'   => '', 'address' => '', 'city' => '',
					'link_customer_id' => !empty($lead->converted_customer_id) ? (int)$lead->converted_customer_id : null,
				)
			);
			if(is_array($result)){
				// Propagate possible-duplicate detail so the UI can surface it
				return isset($result['duplicates'])
					? array('error' => $result['error'], 'duplicates' => $result['duplicates'])
					: array('error' => $result['error']);
			}
			$patientId = (int)$result;
		}
		$patient = $this->db->select('customer_id')->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();

		$update = array('status' => 'converted', 'converted_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));
		if($patient) $update['converted_customer_id'] = (int)$patient->customer_id;
		if($this->db->field_exists('patient_id', 'db_leads')) $update['patient_id'] = $patientId;
		$this->db->where('id', $id)->where('store_id', $storeId)->update('db_leads', $update);
		$this->addActivity($id, 'converted', 'Converted to patient', array('patient_id' => $patientId), $storeId);
		return array('patient_id' => $patientId, 'already' => false);
	}

	/**
	 * Convert a lead into a customer record (db_customers).
	 * Returns ['customer_id' => int] on success, ['error' => msg] on failure.
	 */
	public function convertToCustomer($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$lead = $this->getLead($id, $storeId);
		if(!$lead) return ['error' => 'Lead not found'];
		if($lead->status === 'converted' && $lead->converted_customer_id){
			return ['error' => 'Lead already converted', 'customer_id' => (int)$lead->converted_customer_id];
		}

		// Reuse an existing customer with the same phone if one exists
		$customerId = null;
		if(!empty($lead->phone)){
			$existing = $this->db->where('store_id', $storeId)->where('mobile', $lead->phone)->get('db_customers')->row();
			if($existing) $customerId = (int)$existing->id;
		}

		if(!$customerId){
			$this->db->insert('db_customers', [
				'store_id'      => $storeId,
				'customer_name' => $lead->name,
				'mobile'        => $lead->phone ?: null,
				'email'         => $lead->email ?: null,
				'status'        => 1,
				'created_date'  => date('Y-m-d'),
				'created_by'    => $this->session->userdata('inv_username') ?: 'system',
			]);
			$customerId = (int)$this->db->insert_id();
			if(!$customerId) return ['error' => 'Failed to create customer record'];
		}

		$this->db->where('id', $id)->where('store_id', $storeId)->update('db_leads', [
			'status' => 'converted',
			'converted_customer_id' => $customerId,
			'converted_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		return ['customer_id' => $customerId];
	}
}
