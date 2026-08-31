<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Log Model
 * Tracks all email sends: sent, failed, with retry support.
 */
class Email_log_model extends CI_Model {

	protected $table = 'db_email_logs';

	public function __construct(){
		parent::__construct();
		$this->ensureTable();
	}

	protected function ensureTable(){
		if(!$this->db->table_exists($this->table)){
			log_message('error', 'Missing required table: ' . $this->table . '. Run the 4.0.2 migration via login.');
		}
	}

	public function create(array $data){
		$data['created_at'] = date('Y-m-d H:i:s');
		if(!empty($data['status']) && $data['status'] === 'sent'){
			$data['sent_at'] = date('Y-m-d H:i:s');
		}
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function getById($id){
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function getLogs($storeId = NULL, $filters = [], $limit = 50, $offset = 0){
		if(empty($storeId)){ $storeId = get_current_store_id(); }

		$this->db->where('store_id', $storeId);

		if(!empty($filters['status'])){
			$this->db->where('status', $filters['status']);
		}
		if(!empty($filters['email_type'])){
			$this->db->where('email_type', $filters['email_type']);
		}
		if(!empty($filters['recipient'])){
			$this->db->like('recipient', $filters['recipient']);
		}
		if(!empty($filters['date_from'])){
			$this->db->where('created_at >=', $filters['date_from']);
		}
		if(!empty($filters['date_to'])){
			$this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
		}
		if(!empty($filters['provider'])){
			$this->db->where('provider_used', $filters['provider']);
		}

		$total = $this->db->count_all_results($this->table, FALSE);

		$this->db->order_by('created_at', 'DESC');
		$this->db->limit($limit, $offset);
		$rows = $this->db->get()->result();

		return ['total' => $total, 'rows' => $rows];
	}

	public function getFailedLogs($storeId = NULL, $limit = 50){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db
			->where('store_id', $storeId)
			->where('status', 'failed')
			->order_by('created_at', 'DESC')
			->limit($limit)
			->get($this->table)->result();
	}

	public function updateStatus($id, $status, $error = NULL, $response = NULL){
		$data = ['status' => $status];
		if($status === 'sent'){
			$data['sent_at'] = date('Y-m-d H:i:s');
		}
		if($error !== NULL){
			$data['error_message'] = $error;
		}
		if($response !== NULL){
			$data['provider_response'] = json_encode($response);
		}
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function retry($logId){
		$log = $this->getById($logId);
		if(!$log || $log->status !== 'failed'){
			return ['success' => FALSE, 'message' => 'Log not found or not failed.'];
		}

		$CI =& get_instance();
		$CI->load->model('email_service');
		$result = $CI->email_service->sendRaw(
			$log->recipient,
			$log->subject,
			$log->subject, // We don't store full HTML, so retry may be limited for raw sends
			'',
			['template_key' => $log->email_type, 'related_module' => $log->related_module, 'related_record_id' => $log->related_record_id]
		);

		if($result['success']){
			$this->updateStatus($logId, 'sent');
			return ['success' => TRUE, 'message' => 'Email resent successfully.'];
		} else {
			return ['success' => FALSE, 'message' => $result['message']];
		}
	}

	public function deleteOld($days = 90, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
		return $this->db
			->where('store_id', $storeId)
			->where('created_at <', $cutoff)
			->delete($this->table);
	}
}
