<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_logs_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function log($data){
		if(!$this->db->table_exists('db_approval_logs')) return null;
		$insert = [
			'store_id' => $data['store_id'] ?? get_current_store_id(),
			'branch_id' => $data['branch_id'] ?? null,
			'action_type' => $data['action_type'],
			'approval_type' => $data['approval_type'],
			'requesting_user_id' => $data['requesting_user_id'],
			'requesting_user_name' => $data['requesting_user_name'] ?? null,
			'approving_user_id' => $data['approving_user_id'] ?? null,
			'approving_user_name' => $data['approving_user_name'] ?? null,
			'reason' => $data['reason'] ?? null,
			'previous_value' => $data['previous_value'] ?? null,
			'new_value' => $data['new_value'] ?? null,
			'status' => $data['status'] ?? 'pending',
			'approval_method_used' => $data['approval_method_used'] ?? null,
			'amount' => $data['amount'] ?? null,
			'threshold' => $data['threshold'] ?? null,
			'device_info' => $data['device_info'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
			'ip_address' => $data['ip_address'] ?? $this->input->ip_address(),
			'target_module' => $data['target_module'] ?? null,
			'target_id' => $data['target_id'] ?? null,
			'target_version' => $data['target_version'] ?? null,
			'expires_at' => $data['expires_at'] ?? null,
		];
		$this->db->insert('db_approval_logs', $insert);
		return $this->db->insert_id();
	}

	/**
	 * Supersede all live requests bound to a target — called when the
	 * underlying plan/bill values change so a stale approval can never post.
	 */
	public function supersedeTarget($module, $targetId){
		$this->db->where('target_module', $module)
			->where('target_id', $targetId)
			->where_in('status', ['pending','approved'])
			->update('db_approval_logs', ['status' => 'superseded']);
		return $this->db->affected_rows();
	}

	/**
	 * Is there a current (non-superseded) approved request matching this
	 * target + fingerprint version? Fingerprint lives in target_version.
	 */
	public function hasCurrentApproval($type, $module, $targetId, $version){
		$row = $this->db->where('approval_type', $type)
			->where('target_module', $module)->where('target_id', $targetId)
			->where('target_version', $version)
			->where('status', 'approved')
			->get('db_approval_logs')->row();
		return $row ?: null;
	}

	public function updateStatus($logId, $status, $approverId = null, $approverName = null, $methodUsed = null){
		if(!$this->db->table_exists('db_approval_logs')) return false;
		$update = ['status' => $status, 'approved_at' => date('Y-m-d H:i:s')];
		if($approverId) $update['approving_user_id'] = $approverId;
		if($approverName) $update['approving_user_name'] = $approverName;
		if($methodUsed) $update['approval_method_used'] = $methodUsed;
		$this->db->where('id', $logId)->update('db_approval_logs', $update);
		return $this->db->affected_rows() > 0;
	}

	public function getLogs($filters = [], $limit = 50, $offset = 0){
		if(!$this->db->table_exists('db_approval_logs')){
			return ['total' => 0, 'rows' => []];
		}
		$storeId = $filters['store_id'] ?? get_current_store_id();
		$this->db->where('store_id', $storeId);
		if(!empty($filters['approval_type'])) $this->db->where('approval_type', $filters['approval_type']);
		if(!empty($filters['status'])) $this->db->where('status', $filters['status']);
		if(!empty($filters['date_from'])) $this->db->where('DATE(created_at) >=', $filters['date_from']);
		if(!empty($filters['date_to'])) $this->db->where('DATE(created_at) <=', $filters['date_to']);
		if(!empty($filters['requesting_user_id'])) $this->db->where('requesting_user_id', $filters['requesting_user_id']);
		$this->db->order_by('created_at', 'DESC');
		$count = $this->db->count_all_results('db_approval_logs', false);
		$this->db->limit($limit, $offset);
		$results = $this->db->get()->result();
		return ['total' => $count, 'rows' => $results];
	}

	public function getLogById($id){
		if(!$this->db->table_exists('db_approval_logs')) return null;
		return $this->db->where('id', $id)->get('db_approval_logs')->row();
	}
}
