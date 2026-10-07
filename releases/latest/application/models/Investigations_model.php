<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Investigations — request → pending → result_received → reviewed.
 *
 * Critical invariant: uploading a result puts the investigation in
 * result_received — NEVER reviewed. Review is a separate, explicitly
 * permissioned clinician action (investigations_review). A reviewed
 * result is locked; correction requires a new result + new review.
 */
class Investigations_model extends CI_Model {

	const STATUSES = array('requested','pending','result_received','reviewed','cancelled');

	public function __construct(){
		parent::__construct();
	}

	public function get($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', (int)$id)->where('store_id', $storeId)
			->get('db_investigations')->row();
	}

	public function listRows($storeId = null, $filters = array()){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('i.*, p.patient_code, c.customer_name, u.username AS clinician_name')
			->from('db_investigations i')
			->join('db_patients p', 'p.id = i.patient_id AND p.store_id = i.store_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->join('db_users u', 'u.id = i.requested_by', 'left')
			->where('i.store_id', $storeId);
		if(!empty($filters['status']))   $this->db->where('i.status', $filters['status']);
		if(!empty($filters['patient_id'])) $this->db->where('i.patient_id', (int)$filters['patient_id']);
		if(!empty($filters['encounter_id'])) $this->db->where('i.encounter_id', (int)$filters['encounter_id']);
		if(isset($filters['branch_ids']) && $filters['branch_ids'] !== null){
			if(empty($filters['branch_ids'])) return array();
			// scope via the encounter's branch where the row carries one
			$this->db->join('db_encounters e', 'e.id = i.encounter_id', 'left');
			$this->db->group_start()
				->where_in('e.warehouse_id', $filters['branch_ids'])
				->or_where('i.encounter_id IS NULL', null, FALSE)
				->or_where('e.warehouse_id IS NULL', null, FALSE)
				->group_end();
		}
		return $this->db->order_by('i.id', 'desc')->limit(500)->get()->result();
	}

	/** Create a request. Caller holds investigations_request. */
	public function request(array $d){
		$storeId = get_current_store_id();
		$patientId = (int)($d['patient_id'] ?? 0);
		if(!$patientId && !empty($d['encounter_id'])){
			$enc = $this->db->where('id', (int)$d['encounter_id'])->where('store_id', $storeId)
				->get('db_encounters')->row();
			if(!$enc) return array('error' => 'Visit not found');
			$patientId = (int)$enc->patient_id;
			$d['episode_id'] = $enc->episode_id;
		}
		$patient = $this->db->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$patient) return array('error' => 'Patient not found');
		$name = trim((string)($d['test_name'] ?? ''));
		if($name === '') return array('error' => 'Test / investigation name is required');

		$count_id = get_count_id('db_investigations', $storeId);
		$ref = 'INV-' . str_pad($count_id, 5, '0', STR_PAD_LEFT);
		$this->db->insert('db_investigations', array(
			'store_id'          => $storeId,
			'patient_id'        => $patientId,
			'episode_id'        => (int)($d['episode_id'] ?? 0) ?: null,
			'encounter_id'      => (int)($d['encounter_id'] ?? 0) ?: null,
			'count_id'          => $count_id,
			'request_ref'       => $ref,
			'test_name'         => substr($name, 0, 160),
			'category'          => substr(trim((string)($d['category'] ?? '')), 0, 60) ?: null,
			'priority'          => in_array($d['priority'] ?? '', array('routine','urgent')) ? $d['priority'] : 'routine',
			'status'            => 'requested',
			'facility_name'     => substr(trim((string)($d['facility_name'] ?? '')), 0, 160) ?: null,
			'external_ref'      => substr(trim((string)($d['external_ref'] ?? '')), 0, 80) ?: null,
			'request_notes'     => trim((string)($d['request_notes'] ?? '')) ?: null,
			'requested_by'      => (int)$this->session->userdata('inv_userid') ?: null,
			'requested_by_name' => $this->session->userdata('inv_username') ?: 'system',
			'requested_at'      => date('Y-m-d H:i:s'),
			'created_date'      => date('Y-m-d'),
			'created_time'      => date('H:i:s'),
			'created_by'        => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'         => $this->input->ip_address(),
			'system_name'       => php_uname('n'),
		));
		$id = (int)$this->db->insert_id();
		return $id ? array('investigation_id' => $id, 'request_ref' => $ref) : array('error' => 'Request failed');
	}

	/** requested → pending (sent to / acknowledged by the lab or facility). */
	public function markPending($id){
		return $this->_setStatus($id, array('requested'), 'pending');
	}

	/**
	 * Attach a result (document + summary). Moves to result_received —
	 * explicitly NOT reviewed; review is a separate clinician action.
	 * Caller holds investigations_result_enter.
	 *
	 * Every entry appends an immutable db_investigation_results version —
	 * overwriting the live columns never erases the prior version.
	 */
	public function recordResult($id, $documentId, $summary){
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Investigation not found');
		if($row->status === 'reviewed')  return array('error' => 'Reviewed results are locked — use Amend result');
		if($row->status === 'cancelled') return array('error' => 'Investigation is cancelled');
		if($documentId){
			$doc = $this->db->where('id', (int)$documentId)->where('store_id', $storeId)
				->get('db_patient_documents')->row();
			if(!$doc) return array('error' => 'Attachment not found');
		}
		$this->db->trans_begin();
		$this->_appendResultVersion($row, $documentId, $summary, null);
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_investigations', array(
			'status'             => 'result_received',
			'result_document_id' => (int)$documentId ?: $row->result_document_id,
			'result_summary'     => trim((string)$summary) ?: $row->result_summary,
			'result_received_at' => date('Y-m-d H:i:s'),
			'result_entered_by'  => (int)$this->session->userdata('inv_userid') ?: null,
			'reviewed_by'        => null,
			'reviewed_at'        => null,
			'updated_at'         => date('Y-m-d H:i:s'),
		));
		$this->db->trans_complete();
		return $this->db->trans_status() !== FALSE;
	}

	/**
	 * Amend a REVIEWED result. The reviewed version is preserved as
	 * 'superseded' (document, summary, reviewer and timestamp intact) and a
	 * new version becomes 'current' in result_received — it must be
	 * reviewed again before it is treated as final. A reason is mandatory.
	 * Caller holds investigations_result_enter.
	 */
	public function amendResult($id, $documentId, $summary, $reason){
		$reason = trim((string)$reason);
		if($reason === '') return array('error' => 'An amendment reason is required');
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Investigation not found');
		if($row->status !== 'reviewed') return array('error' => 'Only a reviewed result needs amendment — record a result instead');
		if($documentId){
			$doc = $this->db->where('id', (int)$documentId)->where('store_id', $storeId)
				->get('db_patient_documents')->row();
			if(!$doc) return array('error' => 'Attachment not found');
		}
		if(!$documentId && trim((string)$summary) === ''){
			return array('error' => 'Provide a corrected document or summary');
		}
		$this->db->trans_begin();
		$this->_appendResultVersion($row, $documentId, $summary, $reason);
		// Header returns to result_received with review fields cleared —
		// the original review stays on the superseded version row.
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_investigations', array(
			'status'             => 'result_received',
			'result_document_id' => (int)$documentId ?: $row->result_document_id,
			'result_summary'     => trim((string)$summary) ?: $row->result_summary,
			'result_received_at' => date('Y-m-d H:i:s'),
			'result_entered_by'  => (int)$this->session->userdata('inv_userid') ?: null,
			'reviewed_by'        => null,
			'reviewed_at'        => null,
			'updated_at'         => date('Y-m-d H:i:s'),
		));
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE) return array('error' => 'Amendment failed');
		return true;
	}

	/** Result version history for an investigation. */
	public function resultHistory($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('investigation_id', (int)$id)->where('store_id', $storeId)
			->order_by('version_no', 'desc')->get('db_investigation_results')->result();
	}

	/** Append an immutable result version, superseding the current one. */
	private function _appendResultVersion($inv, $documentId, $summary, $amendReason){
		$storeId = (int)$inv->store_id;
		$prev = $this->db->where('investigation_id', (int)$inv->id)->where('status', 'current')
			->get('db_investigation_results')->row();
		if($prev){
			$this->db->where('id', (int)$prev->id)->update('db_investigation_results',
				array('status' => 'superseded'));
		}
		$ver = ((int)$this->db->select_max('version_no', 'v')
			->where('investigation_id', (int)$inv->id)->get('db_investigation_results')->row()->v) + 1;
		$this->db->insert('db_investigation_results', array(
			'store_id'         => $storeId,
			'investigation_id' => (int)$inv->id,
			'version_no'       => $ver,
			'document_id'      => (int)$documentId ?: null,
			'result_summary'   => trim((string)$summary) ?: null,
			'status'           => 'current',
			'entered_by'       => (int)$this->session->userdata('inv_userid') ?: null,
			'entered_at'       => date('Y-m-d H:i:s'),
			'supersedes_id'    => $prev ? (int)$prev->id : null,
			'amend_reason'     => $amendReason ? substr($amendReason, 0, 500) : null,
			'created_date'     => date('Y-m-d'),
			'created_time'     => date('H:i:s'),
			'created_by'       => $this->session->userdata('inv_username') ?: 'system',
		));
	}

	/**
	 * Explicit clinician review — the only path to 'reviewed'.
	 * Caller holds investigations_review. Reviewed results are locked;
	 * a corrected result must be entered as a new result + new review.
	 */
	public function review($id, $note = null){
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Investigation not found');
		if($row->status !== 'result_received'){
			return array('error' => 'Only a received result can be reviewed (current: ' . $row->status . ')');
		}
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$now = date('Y-m-d H:i:s');
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_investigations', array(
			'status'      => 'reviewed',
			'reviewed_by' => $uid,
			'reviewed_at' => $now,
			'updated_at'  => $now,
		));
		// Stamp the live result version so the reviewed original is provable.
		$this->db->where('investigation_id', (int)$id)->where('store_id', $storeId)
			->where('status', 'current')
			->update('db_investigation_results', array('reviewed_by' => $uid, 'reviewed_at' => $now));
		if($row->encounter_id){
			$this->load->model('encounters_model', 'encounters');
			$this->encounters->logEvent((int)$row->encounter_id, $storeId, 'investigation_reviewed', null, null,
				$row->request_ref . ($note ? ' — ' . $note : ''));
		}
		return true;
	}

	/** Cancel — a reason is mandatory. */
	public function cancel($id, $reason){
		$reason = trim((string)$reason);
		if($reason === '') return array('error' => 'A cancellation reason is required');
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Investigation not found');
		if($row->status === 'reviewed') return array('error' => 'Reviewed investigations cannot be cancelled');
		if($row->status === 'cancelled') return array('error' => 'Already cancelled');
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_investigations', array(
			'status'        => 'cancelled',
			'cancelled_by'  => (int)$this->session->userdata('inv_userid') ?: null,
			'cancelled_at'  => date('Y-m-d H:i:s'),
			'cancel_reason' => substr($reason, 0, 500),
			'updated_at'    => date('Y-m-d H:i:s'),
		));
		return true;
	}

	private function _setStatus($id, array $from, $to){
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Investigation not found');
		if(!in_array($row->status, $from)) return array('error' => 'Cannot move from ' . $row->status . ' to ' . $to);
		$this->db->where('id', (int)$id)->where('store_id', $storeId)
			->update('db_investigations', array('status' => $to, 'updated_at' => date('Y-m-d H:i:s')));
		return true;
	}
}
