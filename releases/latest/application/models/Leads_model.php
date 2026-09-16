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
			$this->db->where('store_id', $storeId);
			if($status !== '' && in_array($status, self::STATUSES)) $this->db->where('status', $status);
			if($search !== ''){
				$this->db->group_start()
					->like('name', $search)
					->or_like('phone', $search)
					->or_like('email', $search)
					->or_like('interest', $search)
				->group_end();
			}
			return $this->db->order_by('id', 'desc')->limit($limit, $offset)->get('db_leads')->result();
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
