<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inpatient_model — admissions, beds, occupancy, transfers, porter tasks,
 * nursing care, meals, leave, daily billing, external referrals, discharge
 * and deceased closure for the physiotherapy_rehabilitation business type.
 *
 * Financial model:
 *  - Every active admission owns ONE running db_sales invoice
 *    (db_sales.admission_id); daily charges append db_salesitems lines and
 *    recompute totals — revenue is recognised as charges post, never twice.
 *  - db_daily_charges UNIQUE (admission_id, charge_date, charge_code) makes
 *    every retry of the daily run a safe no-op. Bed transfers can never
 *    double-charge a day — the charge keys on the admission, not the bed.
 *  - Wallet auto-settlement posts 'patient_wallet' payments through
 *    Patient_billing_model::addPayment (consume + payment + recompute in
 *    one transaction, idempotent on payment_reference).
 *  - Discharge closes occupancy/clinical activity only — the invoice and
 *    its debt remain visible and payable afterwards.
 */
class Inpatient_model extends CI_Model {

	const DAY_SECS = 86400;

	/** Store context for cron loops that post charges across stores. */
	private $_ipdStoreId = null;
	private function _sid(){ return $this->_ipdStoreId ?: (int)get_current_store_id(); }

	// ------------------------------------------------------------------
	// Policies — tenant-configurable charging rules with defaults.
	// ------------------------------------------------------------------
	public function policy($key, $default = null){
		return $this->policyFor($this->_sid(), $key, $default);
	}
	public function policyFor($storeId, $key, $default = null){
		$row = $this->db->where('store_id', $storeId)->where('policy_key', $key)
			->get('db_inpatient_policies')->row();
		return $row ? $row->policy_value : $default;
	}
	public function policies(){
		$rows = $this->db->where('store_id', get_current_store_id())
			->get('db_inpatient_policies')->result();
		$out = array();
		foreach($rows as $r) $out[$r->policy_key] = $r->policy_value;
		return $out;
	}
	public function setPolicy($key, $value){
		$storeId = get_current_store_id();
		$this->db->query(
			'INSERT INTO db_inpatient_policies (store_id, policy_key, policy_value, updated_by, updated_at)
			 VALUES (?,?,?,?,NOW())
			 ON DUPLICATE KEY UPDATE policy_value=VALUES(policy_value), updated_by=VALUES(updated_by), updated_at=NOW()',
			array($storeId, $key, (string)$value, $this->session->userdata('inv_username') ?: 'system'));
		return true;
	}

	// ------------------------------------------------------------------
	// Wards & beds
	// ------------------------------------------------------------------
	public function saveWard($name, $branchId = null){
		$storeId = get_current_store_id();
		$name = trim((string)$name);
		if($name === '') return array('ok' => false, 'error' => 'Ward name is required');
		$this->db->insert('db_wards', array(
			'store_id' => $storeId, 'branch_id' => $branchId ?: null, 'name' => $name,
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username'),
		));
		$id = $this->db->insert_id();
		return $id ? array('ok' => true, 'ward_id' => $id) : array('ok' => false, 'error' => 'Ward exists or insert failed');
	}
	public function saveBed($wardId, $label, $dailyRate = null){
		$storeId = get_current_store_id();
		$ward = $this->db->where('id', $wardId)->where('store_id', $storeId)->get('db_wards')->row();
		if(!$ward) return array('ok' => false, 'error' => 'Ward not found');
		$this->db->insert('db_beds', array(
			'store_id' => $storeId, 'ward_id' => $wardId, 'bed_label' => trim($label),
			'daily_rate' => ($dailyRate === '' || $dailyRate === null) ? null : (float)$dailyRate,
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username'),
		));
		$id = $this->db->insert_id();
		return $id ? array('ok' => true, 'bed_id' => $id) : array('ok' => false, 'error' => 'Bed label exists or insert failed');
	}
	public function bedBoard(){
		$storeId = get_current_store_id();
		$wards = $this->db->where('store_id', $storeId)->where('status', 1)->order_by('name')->get('db_wards')->result();
		$beds = $this->db->select('b.*, o.id AS occupancy_id, o.admission_id, c.customer_name AS patient_name')
			->from('db_beds b')
			->join('db_bed_occupancy o', 'o.bed_id = b.id AND o.to_at IS NULL', 'left')
			->join('db_patients p', 'p.id = o.patient_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id', 'left')
			->where('b.store_id', $storeId)->order_by('b.ward_id, b.bed_label')->get()->result();
		return array('wards' => $wards, 'beds' => $beds);
	}
	public function wards(){ return $this->db->where('store_id', get_current_store_id())->where('status',1)->order_by('name')->get('db_wards')->result(); }
	public function availableBeds(){
		return $this->db->where('store_id', get_current_store_id())->where('status','available')->order_by('ward_id,bed_label')->get('db_beds')->result();
	}

	// ------------------------------------------------------------------
	// Admission — identity reuse, explicit outpatient-package decision.
	// ------------------------------------------------------------------
	public function admit($patientId, array $d){
		$storeId = get_current_store_id();
		$bedId   = !empty($d['bed_id']) ? (int)$d['bed_id'] : null;
		$decision= in_array(($d['outpatient_decision'] ?? ''), array('continue','pause','replace'), true)
			? $d['outpatient_decision'] : null;
		if(!$decision) return array('ok' => false, 'error' => 'Outpatient package decision is required (continue / pause / replace)');
		if(trim((string)($d['reason'] ?? '')) === '') return array('ok' => false, 'error' => 'Admission reason is required');

		$this->db->trans_begin();
		// Patient row is the financial mutex — one active admission per patient.
		$patient = $this->db->query('SELECT * FROM db_patients WHERE id = ? AND store_id = ? FOR UPDATE',
			array($patientId, $storeId))->row();
		if(!$patient){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Patient not found'); }
		if((int)$patient->deceased === 1){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Cannot admit a deceased patient'); }
		$active = $this->db->where('patient_id', $patientId)->where('store_id', $storeId)
			->where('status', 'active')->get('db_admissions')->row();
		if($active){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Patient already has an active admission ' . $active->admission_code, 'conflict' => true, 'admission_id' => $active->id); }

		$count = $this->db->where('store_id', $storeId)->count_all_results('db_admissions') + 1;
		$code  = 'ADM-' . str_pad($count, 5, '0', STR_PAD_LEFT);
		while($this->db->where('store_id',$storeId)->where('admission_code',$code)->count_all_results('db_admissions')){ $count++; $code='ADM-'.str_pad($count,5,'0',STR_PAD_LEFT); }

		$admId = 0;
		$insertData = array(
			'store_id' => $storeId,
			'branch_id' => function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null,
			'patient_id' => $patientId, 'admission_code' => $code,
			'reason' => trim($d['reason']), 'care_plan' => trim((string)($d['care_plan'] ?? '')),
			'clinician_user_id' => !empty($d['clinician_user_id']) ? (int)$d['clinician_user_id'] : (int)$this->session->userdata('inv_userid'),
			'admitted_by' => (int)$this->session->userdata('inv_userid'),
			'admitted_at' => !empty($d['admitted_at']) ? $d['admitted_at'] : date('Y-m-d H:i:s'),
			'outpatient_decision' => $decision,
			'outpatient_decision_note' => trim((string)($d['outpatient_decision_note'] ?? '')),
			'status' => 'active',
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username'),
		);
		// The code is a count-based guess — a concurrent admit can take it. Retry
		// a few times on a duplicate admission_code before giving up.
		for($try = 0; $try < 4 && !$admId; $try++){
			$insertData['admission_code'] = $code;
			try {
				$admId = $this->db->insert('db_admissions', $insertData) ? (int)$this->db->insert_id() : 0;
			} catch (Throwable $e) {
				$admId = 0;
			}
			if(!$admId){
				$count++;
				$code = 'ADM-' . str_pad($count, 5, '0', STR_PAD_LEFT);
			}
		}
		if(!$admId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Admission insert failed'); }

		if($bedId){
			$r = $this->_openOccupancy($admId, $patientId, $bedId, 'admission');
			if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		}
		$r = $this->_ensureInvoice($admId, $patient->customer_id);
		if(!$r){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Admission invoice failed'); }

		$this->_applyOutpatientDecision($patientId, $decision);
		$this->generateTasks($admId, date('Y-m-d'));

		$this->db->trans_commit();
		return array('ok' => true, 'admission_id' => $admId, 'admission_code' => $code);
	}

	public function getAdmission($id){
		return $this->db->select('a.*, c.customer_name AS full_name, p.patient_code, p.customer_id, c.mobile AS patient_mobile, u.username AS clinician_name')
			->from('db_admissions a')
			->join('db_patients p', 'p.id = a.patient_id')
			->join('db_customers c', 'c.id = p.customer_id', 'left')
			->join('db_users u', 'u.id = a.clinician_user_id', 'left')
			->where('a.id', $id)->where('a.store_id', get_current_store_id())->get()->row();
	}
	public function admissions($status = 'active'){
		$q = $this->db->select('a.*, c.customer_name AS full_name, p.patient_code, b.bed_label, w.name AS ward_name')
			->from('db_admissions a')
			->join('db_patients p', 'p.id = a.patient_id')
			->join('db_customers c', 'c.id = p.customer_id', 'left')
			->join('db_bed_occupancy o', 'o.admission_id = a.id AND o.to_at IS NULL', 'left')
			->join('db_beds b', 'b.id = o.bed_id', 'left')
			->join('db_wards w', 'w.id = b.ward_id', 'left')
			->where('a.store_id', get_current_store_id());
		if($status !== 'all') $q->where('a.status', $status);
		return $q->order_by('a.id', 'desc')->get()->result();
	}
	public function currentOccupancy($admId){
		return $this->db->select('o.*, b.bed_label, w.name AS ward_name, w.id AS ward_id')
			->from('db_bed_occupancy o')->join('db_beds b','b.id=o.bed_id')->join('db_wards w','w.id=b.ward_id')
			->where('o.admission_id', $admId)->where('o.to_at IS NULL')->get()->row();
	}
	public function occupancyHistory($admId){
		return $this->db->select('o.*, b.bed_label, w.name AS ward_name')
			->from('db_bed_occupancy o')->join('db_beds b','b.id=o.bed_id')->join('db_wards w','w.id=b.ward_id')
			->where('o.admission_id', $admId)->order_by('o.from_at')->get()->result();
	}

	/**
	 * Bed allocation — the ONLY path that opens an occupancy row. The bed row
	 * is locked FOR UPDATE so two concurrent allocations serialise: the loser
	 * sees status != available and gets a conflict, never a double-booked bed.
	 * Must be called inside an open transaction.
	 */
	private function _openOccupancy($admId, $patientId, $bedId, $reason){
		$bed = $this->db->query('SELECT * FROM db_beds WHERE id = ? FOR UPDATE', array($bedId))->row();
		if(!$bed || (int)$bed->store_id !== (int)get_current_store_id())
			return array('ok' => false, 'error' => 'Bed not found');
		$open = $this->db->where('bed_id', $bedId)->where('to_at IS NULL')->count_all_results('db_bed_occupancy');
		if($open || $bed->status !== 'available')
			return array('ok' => false, 'conflict' => true, 'error' => 'Bed ' . $bed->bed_label . ' is already allocated');
		$this->db->insert('db_bed_occupancy', array(
			'store_id' => get_current_store_id(), 'admission_id' => $admId,
			'bed_id' => $bedId, 'patient_id' => $patientId,
			'from_at' => date('Y-m-d H:i:s'), 'open_reason' => $reason,
			'booked_by' => $this->session->userdata('inv_username'),
		));
		if(!$this->db->insert_id()) return array('ok' => false, 'error' => 'Occupancy insert failed');
		$this->db->where('id', $bedId)->update('db_beds', array('status' => 'occupied'));
		return array('ok' => true);
	}

	// ------------------------------------------------------------------
	// Transfers — requested by a clinician, EXECUTED by the porter task.
	// The destination bed may be taken between request and completion;
	// completion re-checks and fails the transfer instead of double-booking.
	// ------------------------------------------------------------------
	public function requestTransfer($admId, $toBedId, $reason){
		$adm = $this->getAdmission($admId);
		if(!$adm || $adm->status !== 'active') return array('ok' => false, 'error' => 'No active admission');
		$occ = $this->currentOccupancy($admId);
		if(!$occ) return array('ok' => false, 'error' => 'Patient has no current bed');
		if((int)$occ->bed_id === (int)$toBedId) return array('ok' => false, 'error' => 'Already in that bed');
		$to = $this->db->where('id', $toBedId)->where('store_id', get_current_store_id())->get('db_beds')->row();
		if(!$to) return array('ok' => false, 'error' => 'Destination bed not found');
		$pending = $this->db->where('admission_id', $admId)->where('status', 'requested')->get('db_bed_transfers')->row();
		if($pending) return array('ok' => false, 'error' => 'A transfer is already pending');

		$this->db->trans_begin();
		$this->db->insert('db_bed_transfers', array(
			'store_id' => get_current_store_id(), 'admission_id' => $admId,
			'from_bed_id' => $occ->bed_id, 'to_bed_id' => $toBedId,
			'reason' => trim((string)$reason),
			'requested_by' => $this->session->userdata('inv_username'),
			'status' => 'requested', 'created_at' => date('Y-m-d H:i:s'),
		));
		$tid = $this->db->insert_id();
		$this->db->insert('db_porter_tasks', array(
			'store_id' => get_current_store_id(), 'task_type' => 'bed_transfer', 'ref_id' => $tid,
			'admission_id' => $admId, 'patient_id' => $adm->patient_id,
			'from_location' => $occ->ward_name . ' / ' . $occ->bed_label,
			'to_location'   => $this->db->select('w.name')->from('db_beds b')->join('db_wards w','w.id=b.ward_id')->where('b.id',$toBedId)->get()->row('name') . ' / ' . $to->bed_label,
			'status' => 'open',
			'requested_by' => $this->session->userdata('inv_username'),
			'created_at' => date('Y-m-d H:i:s'),
		));
		$ptid = $this->db->insert_id();
		$this->db->where('id', $tid)->update('db_bed_transfers', array('porter_task_id' => $ptid));
		$this->db->trans_commit();
		return array('ok' => true, 'transfer_id' => $tid, 'porter_task_id' => $ptid);
	}

	public function transfers($admId){
		return $this->db->select('t.*, fb.bed_label AS from_bed, tb.bed_label AS to_bed')
			->from('db_bed_transfers t')
			->join('db_beds fb','fb.id=t.from_bed_id')->join('db_beds tb','tb.id=t.to_bed_id')
			->where('t.admission_id', $admId)->order_by('t.id','desc')->get()->result();
	}

	// ------------------------------------------------------------------
	// Porter tasks
	// ------------------------------------------------------------------
	public function porterTasks($status = null){
		$q = $this->db->select('t.*, c.customer_name AS patient_name, p.patient_code, a.admission_code')
			->from('db_porter_tasks t')
			->join('db_patients p','p.id=t.patient_id','left')
			->join('db_customers c','c.id=p.customer_id','left')
			->join('db_admissions a','a.id=t.admission_id','left')
			->where('t.store_id', get_current_store_id());
		if($status) $q->where('t.status', $status);
		return $q->order_by('t.id','desc')->limit(100)->get()->result();
	}
	public function claimTask($taskId){
		$t = $this->db->where('id',$taskId)->where('store_id',get_current_store_id())->get('db_porter_tasks')->row();
		if(!$t || $t->status !== 'open') return array('ok' => false, 'error' => 'Task is not open');
		$this->db->where('id',$taskId)->where('status','open')->update('db_porter_tasks', array(
			'status' => 'in_progress', 'assigned_to' => (int)$this->session->userdata('inv_userid')));
		return array('ok' => $this->db->affected_rows() > 0, 'claimed' => $this->db->affected_rows() > 0);
	}
	/**
	 * Porter completes a task. For bed_transfer the occupancy swap happens
	 * HERE — inside one transaction with bed-row locks — so a destination
	 * taken since the request fails the task with a conflict, never a
	 * double-booked bed.
	 */
	public function completeTask($taskId){
		$storeId = get_current_store_id();
		$this->db->trans_begin();
		$t = $this->db->query('SELECT * FROM db_porter_tasks WHERE id = ? AND store_id = ? FOR UPDATE', array($taskId,$storeId))->row();
		if(!$t || !in_array($t->status, array('open','in_progress'), true)){
			$this->db->trans_rollback(); return array('ok' => false, 'error' => 'Task already closed');
		}
		if($t->task_type === 'bed_transfer'){
			$tr = $this->db->where('id', $t->ref_id)->get('db_bed_transfers')->row();
			if(!$tr || $tr->status !== 'requested'){
				$this->db->trans_rollback(); return array('ok' => false, 'error' => 'Transfer is no longer pending');
			}
			$occ = $this->currentOccupancy($t->admission_id);
			// Lock destination bed inside the txn — conflict if taken.
			$r = $this->_openOccupancy($t->admission_id, $t->patient_id, $tr->to_bed_id, 'transfer');
			if(!$r['ok']){
				$this->db->where('id',$tr->id)->update('db_bed_transfers', array('status'=>'failed','failure_note'=>$r['error']));
				$this->db->where('id',$taskId)->update('db_porter_tasks', array('status'=>'failed','notes'=>$r['error']));
				$this->db->trans_commit();
				return array('ok' => false, 'conflict' => true, 'error' => $r['error']);
			}
			if($occ){
				$this->db->where('id',$occ->id)->update('db_bed_occupancy', array('to_at'=>date('Y-m-d H:i:s'),'close_reason'=>'transfer'));
				$this->db->where('id',$occ->bed_id)->update('db_beds', array('status'=>'available'));
			}
			$this->db->where('id',$tr->id)->update('db_bed_transfers', array('status'=>'completed','completed_at'=>date('Y-m-d H:i:s')));
		}
		$this->db->where('id',$taskId)->update('db_porter_tasks', array(
			'status' => 'completed',
			'completed_by' => (int)$this->session->userdata('inv_userid'),
			'completed_at' => date('Y-m-d H:i:s')));
		$this->db->trans_commit();
		return array('ok' => true);
	}
	public function cancelTask($taskId){
		$this->db->trans_begin();
		$t = $this->db->query('SELECT * FROM db_porter_tasks WHERE id = ? AND store_id = ? FOR UPDATE', array($taskId,get_current_store_id()))->row();
		if(!$t || !in_array($t->status, array('open','in_progress'), true)){
			$this->db->trans_rollback(); return array('ok' => false, 'error' => 'Task already closed');
		}
		$this->db->where('id',$taskId)->update('db_porter_tasks', array('status'=>'cancelled'));
		if($t->task_type === 'bed_transfer' && $t->ref_id){
			$this->db->where('id',$t->ref_id)->where('status','requested')
				->update('db_bed_transfers', array('status'=>'cancelled'));
		}
		$this->db->trans_commit();
		return array('ok' => true);
	}

	// ------------------------------------------------------------------
	// Nursing care — generated morning/evening tasks + ad hoc. Generation
	// is retry-safe via UNIQUE(admission_id, task_code, task_date).
	// Overdue = open AND due_at < now (derived, never stored stale).
	// ------------------------------------------------------------------
	public function generateTasks($admId, $date){
		$adm = $this->db->where('id',$admId)->where('store_id',get_current_store_id())->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('generated' => 0);
		$md = $this->policy('morning_due', '08:00'); $ed = $this->policy('evening_due', '20:00');
		$defs = array(
			array('morning_check', 'Morning observations & checks',  $date . ' ' . $md . ':00'),
			array('evening_check', 'Evening observations & checks',  $date . ' ' . $ed . ':00'),
		);
		// Weekly review on every 7th day of admission.
		$dayNo = (int)floor((strtotime($date) - strtotime(substr($adm->admitted_at,0,10))) / self::DAY_SECS) + 1;
		if($dayNo >= 7 && $dayNo % 7 === 0){
			$defs[] = array('weekly_review', 'Weekly scheduled review', $date . ' ' . $ed . ':00');
		}
		$gen = 0;
		foreach($defs as $t){
			$this->db->query(
				'INSERT IGNORE INTO db_nursing_tasks (store_id, admission_id, patient_id, task_code, label, task_date, due_at, status, created_at)
				 VALUES (?,?,?,?,?,?,?,"open",NOW())',
				array(get_current_store_id(), $admId, $adm->patient_id, $t[0], $t[1], $date, $t[2]));
			$gen += $this->db->affected_rows();
		}
		return array('generated' => $gen);
	}
	public function addTask($admId, $label, $dueAt){
		$adm = $this->db->where('id',$admId)->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok' => false, 'error' => 'No active admission');
		$seq = (int)$this->db->where('admission_id',$admId)->count_all_results('db_nursing_tasks') + 1;
		$this->db->insert('db_nursing_tasks', array(
			'store_id'=>get_current_store_id(),'admission_id'=>$admId,'patient_id'=>$adm->patient_id,
			'task_code'=>'adhoc_'.$seq,
			'label'=>trim($label),'task_date'=>substr($dueAt,0,10),'due_at'=>$dueAt,
			'status'=>'open','created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'task_id'=>$id) : array('ok'=>false,'error'=>'Task insert failed');
	}
	public function completeTaskItem($taskId, $note = ''){
		$t = $this->db->where('id',$taskId)->where('store_id',get_current_store_id())->get('db_nursing_tasks')->row();
		if(!$t) return array('ok'=>false,'error'=>'Task not found');
		if($t->status !== 'open') return array('ok'=>false,'error'=>'Task is '.$t->status);
		$adm = $this->db->where('id',$t->admission_id)->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok'=>false,'error'=>'Admission is closed');
		$this->db->where('id',$taskId)->where('status','open')->update('db_nursing_tasks', array(
			'status'=>'done','done_at'=>date('Y-m-d H:i:s'),
			'done_by'=>(int)$this->session->userdata('inv_userid'),'result_note'=>substr(trim($note),0,255)));
		return array('ok' => $this->db->affected_rows() > 0);
	}
	public function tasksBoard($admId = null, $status = null){
		$q = $this->db->select('t.*, c.customer_name AS patient_name, a.admission_code, (t.status="open" AND t.due_at < NOW()) AS is_overdue')
			->from('db_nursing_tasks t')
			->join('db_patients p','p.id=t.patient_id')
			->join('db_customers c','c.id=p.customer_id','left')
			->join('db_admissions a','a.id=t.admission_id')
			->where('t.store_id', get_current_store_id());
		if($admId) $q->where('t.admission_id', $admId);
		if($status === 'open') $q->where('t.status','open');
		elseif($status === 'overdue') $q->where('t.status','open')->where('t.due_at <', date('Y-m-d H:i:s'));
		return $q->order_by('t.due_at','asc')->limit(200)->get()->result();
	}
	public function recordNote($admId, $type, $body, $shift = null, $taskId = null){
		if(!in_array($type, array('observation','handover','review'), true)) return array('ok'=>false,'error'=>'Bad note type');
		$adm = $this->db->where('id',$admId)->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok'=>false,'error'=>'No active admission');
		if(trim((string)$body)==='') return array('ok'=>false,'error'=>'Note body is required');
		$this->db->insert('db_nursing_notes', array(
			'store_id'=>get_current_store_id(),'admission_id'=>$admId,'task_id'=>$taskId,
			'note_type'=>$type,'shift'=>in_array($shift,array('morning','evening'),true)?$shift:null,
			'body'=>trim($body),'recorded_by'=>$this->session->userdata('inv_username'),
			'recorded_by_id'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'note_id'=>$id) : array('ok'=>false,'error'=>'Note insert failed');
	}
	public function notes($admId, $type = null){
		$q = $this->db->where('store_id',get_current_store_id())->where('admission_id',$admId);
		if($type) $q->where('note_type',$type);
		return $q->order_by('id','desc')->get('db_nursing_notes')->result();
	}

	// ------------------------------------------------------------------
	// Meals — choices, dietary notes, provision records, charge snapshots.
	// ------------------------------------------------------------------
	public function mealTypes(){
		return $this->db->where('store_id',get_current_store_id())->where('status',1)->order_by('slot,name')->get('db_meal_types')->result();
	}
	public function orderMeal($admId, $date, $slot, $typeId, $dietNote = ''){
		$adm = $this->db->where('id',$admId)->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok'=>false,'error'=>'No active admission');
		$type = $this->db->where('id',$typeId)->where('store_id',get_current_store_id())->where('status',1)->get('db_meal_types')->row();
		if(!$type) return array('ok'=>false,'error'=>'Meal type not found');
		$this->db->insert('db_meal_orders', array(
			'store_id'=>get_current_store_id(),'admission_id'=>$admId,'meal_date'=>$date,
			'slot'=>in_array($slot,array('breakfast','lunch','dinner','snack'),true)?$slot:'snack',
			'meal_type_id'=>$typeId,'diet_note'=>substr(trim($dietNote),0,255),
			'ordered_by'=>$this->session->userdata('inv_username'),'created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'order_id'=>$id) : array('ok'=>false,'error'=>'Meal order failed');
	}
	public function provideMeal($orderId){
		$o = $this->db->where('id',$orderId)->where('store_id',get_current_store_id())->get('db_meal_orders')->row();
		if(!$o) return array('ok'=>false,'error'=>'Meal order not found');
		if($o->status !== 'ordered') return array('ok'=>false,'error'=>'Meal already '.$o->status);
		$type = $this->db->where('id',$o->meal_type_id)->get('db_meal_types')->row();
		$this->db->where('id',$orderId)->where('status','ordered')->update('db_meal_orders', array(
			'status'=>'provided','provided_at'=>date('Y-m-d H:i:s'),
			'provided_by'=>(int)$this->session->userdata('inv_userid'),
			'charge_amount'=>$type ? (float)$type->charge : 0));
		return array('ok' => $this->db->affected_rows() > 0);
	}
	public function mealOrders($admId){
		return $this->db->select('o.*, t.name AS meal_name')->from('db_meal_orders o')
			->join('db_meal_types t','t.id=o.meal_type_id','left')
			->where('o.admission_id',$admId)->order_by('o.meal_date desc, o.id')->get()->result();
	}

	// ------------------------------------------------------------------
	// Temporary leave — approval, departure, expected/actual return,
	// bed held while away, billing policy snapshotted at approval.
	// ------------------------------------------------------------------
	public function requestLeave($admId, $reason, $expectedReturn){
		$adm = $this->db->where('id',$admId)->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok'=>false,'error'=>'No active admission');
		$open = $this->db->where('admission_id',$admId)->where_in('status',array('requested','approved','out'))->get('db_leave_records')->row();
		if($open) return array('ok'=>false,'error'=>'A leave record is already open');
		$this->db->insert('db_leave_records', array(
			'store_id'=>get_current_store_id(),'admission_id'=>$admId,'reason'=>substr(trim($reason),0,255),
			'expected_return'=>$expectedReturn ?: null,'status'=>'requested',
			'requested_by'=>$this->session->userdata('inv_username'),'created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'leave_id'=>$id) : array('ok'=>false,'error'=>'Leave request failed');
	}
	public function approveLeave($leaveId){
		$this->db->trans_begin();
		$l = $this->db->query('SELECT * FROM db_leave_records WHERE id = ? AND store_id = ? FOR UPDATE', array($leaveId,get_current_store_id()))->row();
		if(!$l || $l->status !== 'requested'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Leave is not pending'); }
		if($l->requested_by === $this->session->userdata('inv_username')){
			$this->db->trans_rollback(); return array('ok'=>false,'error'=>'The requester cannot approve their own leave request');
		}
		$this->db->where('id',$leaveId)->update('db_leave_records', array(
			'status'=>'approved','approved_by'=>(int)$this->session->userdata('inv_userid'),
			'approved_at'=>date('Y-m-d H:i:s'),
			'billing_policy'=>$this->policy('leave_billing','half'),  // snapshot at approval
			'bed_hold'=>1));
		$this->db->trans_commit();
		return array('ok'=>true);
	}
	public function departLeave($leaveId){
		$this->db->trans_begin();
		$l = $this->db->query('SELECT * FROM db_leave_records WHERE id = ? AND store_id = ? FOR UPDATE', array($leaveId,get_current_store_id()))->row();
		if(!$l || $l->status !== 'approved'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Leave must be approved before departure'); }
		$this->db->where('id',$leaveId)->update('db_leave_records', array('status'=>'out','departed_at'=>date('Y-m-d H:i:s')));
		if($l->bed_hold){
			$occ = $this->currentOccupancy($l->admission_id);
			if($occ) $this->db->where('id',$occ->bed_id)->update('db_beds', array('status'=>'held'));
		}
		$this->db->trans_commit();
		return array('ok'=>true);
	}
	public function returnLeave($leaveId){
		$this->db->trans_begin();
		$l = $this->db->query('SELECT * FROM db_leave_records WHERE id = ? AND store_id = ? FOR UPDATE', array($leaveId,get_current_store_id()))->row();
		if(!$l || $l->status !== 'out'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Patient is not out on leave'); }
		$now = date('Y-m-d H:i:s');
		$overdue = ($l->expected_return && $now > $l->expected_return) ? 1 : 0;
		$this->db->where('id',$leaveId)->update('db_leave_records', array('status'=>'returned','returned_at'=>$now,'overdue'=>$overdue));
		if($l->bed_hold){
			$occ = $this->db->where('admission_id',$l->admission_id)->where('to_at IS NULL')->get('db_bed_occupancy')->row();
			if($occ) $this->db->where('id',$occ->bed_id)->update('db_beds', array('status'=>'occupied'));
		}
		$this->db->trans_commit();
		return array('ok'=>true,'overdue'=>$overdue);
	}
	public function leaveRecords($admId){
		return $this->db->where('store_id',get_current_store_id())->where('admission_id',$admId)->order_by('id','desc')->get('db_leave_records')->result();
	}

	// ------------------------------------------------------------------
	// Daily billing — versioned rates, calendar-day boundary, retry-safe
	// via UNIQUE(admission_id, charge_date, charge_code). Runs repeatedly:
	// a re-run of the same date posts nothing twice.
	// ------------------------------------------------------------------
	public function saveRate($code, $name, $amount, $effectiveFrom){
		$storeId = get_current_store_id();
		if($amount === '' || !is_numeric($amount)) return array('ok'=>false,'error'=>'Amount required');
		$this->db->trans_begin();
		// Close the current version the day before the new one starts.
		$this->db->where('store_id',$storeId)->where('rate_code',$code)->where('effective_to IS NULL')
			->update('db_daily_rates', array('effective_to'=>date('Y-m-d', strtotime($effectiveFrom.' -1 day'))));
		$this->db->insert('db_daily_rates', array(
			'store_id'=>$storeId,'rate_code'=>$code,'name'=>trim($name),'amount'=>(float)$amount,
			'effective_from'=>$effectiveFrom,'status'=>1,
			'created_by'=>$this->session->userdata('inv_username'),'created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		$this->db->trans_commit();
		return $id ? array('ok'=>true,'rate_id'=>$id) : array('ok'=>false,'error'=>'Rate insert failed');
	}
	public function rateFor($code, $date){
		return $this->db->where('store_id',get_current_store_id())->where('rate_code',$code)
			->where('effective_from <=',$date)
			->group_start()->where('effective_to IS NULL')->or_where('effective_to >=',$date)->group_end()
			->where('status',1)->order_by('effective_from','desc')->get('db_daily_rates')->row();
	}
	public function rates(){ return $this->db->where('store_id',get_current_store_id())->order_by('rate_code, effective_from desc')->get('db_daily_rates')->result(); }

	private function _invoice($admId){
		return $this->db->where('admission_id',$admId)->where('store_id',$this->_sid())
			->where('sales_status','Final')->get('db_sales')->row();
	}
	private function _ensureInvoice($admId, $customerId){
		$inv = $this->_invoice($admId);
		if($inv) return $inv;
		$storeId = $this->_sid();
		// Serialise invoice-number generation per store (same as plan billing).
		$this->db->query('SELECT 1 FROM db_store WHERE id = ? FOR UPDATE', array($storeId));
		$initCode = get_only_init_code('sales');
		$countId  = autosynch_sales_code();
		$this->db->insert('db_sales', array(
			'init_code'=>$initCode,'count_id'=>$countId,'sales_code'=>$initCode.$countId,
			'reference_no'=>'IPD-'.$admId,'sales_date'=>date('Y-m-d'),'sales_status'=>'Final',
			'customer_id'=>$customerId,'admission_id'=>$admId,
			'subtotal'=>0,'grand_total'=>0,'paid_amount'=>0,'payment_status'=>'Unpaid',
			'sales_note'=>'Inpatient admission '.$admId,
			'store_id'=>$storeId,
			'warehouse_id'=>function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null,
			'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),
			'created_by'=>$this->session->userdata('inv_username') ?: 'system',
			'system_ip'=>$this->input->ip_address(),'system_name'=>php_uname(),'status'=>1));
		$id = $this->db->insert_id();
		if($id) $this->db->where('id',$admId)->update('db_admissions', array('invoice_id'=>$id));
		return $id ? $this->db->where('id',$id)->get('db_sales')->row() : null;
	}
	/** Inpatient charge item (system service row INP-DAILY). */
	private function _chargeItemId(){
		$it = $this->db->select('id')->where('store_id',$this->_sid())
			->where('item_code','INP-DAILY')->get('db_items')->row();
		if($it) return (int)$it->id;
		$any = $this->db->select('id')->where('store_id',$this->_sid())->where('service_bit',1)->get('db_items')->row();
		return $any ? (int)$any->id : 0;
	}
	/** Append one charge line to the running invoice; recompute totals. */
	private function _postCharge($adm, $date, $code, $desc, $qty, $amount){
		// Idempotency first — the unique key is the retry contract.
		$exists = $this->db->where('admission_id',$adm->id)->where('charge_date',$date)
			->where('charge_code',$code)->get('db_daily_charges')->row();
		if($exists) return array('ok'=>true,'replayed'=>true,'charge_id'=>$exists->id);
		$inv = $this->_ensureInvoice($adm->id, $adm->customer_id);
		if(!$inv) return array('ok'=>false,'error'=>'No invoice for admission');
		$amount = round((float)$amount, 2);
		if($amount <= 0){
			// Zero-value charge still recorded once (audit) but no sale line.
			$this->db->insert('db_daily_charges', array(
				'store_id'=>$this->_sid(),'admission_id'=>$adm->id,'charge_date'=>$date,
				'charge_code'=>$code,'description'=>$desc,'qty'=>$qty,'amount'=>0,
				'sales_id'=>$inv->id,'posted_at'=>date('Y-m-d H:i:s')));
			return $this->db->insert_id() ? array('ok'=>true,'charge_id'=>$this->db->insert_id()) : array('ok'=>true,'replayed'=>true);
		}
		$this->db->insert('db_salesitems', array(
			'sales_id'=>$inv->id,'store_id'=>$this->_sid(),'sales_status'=>'Final',
			'item_id'=>$this->_chargeItemId(),'description'=>$desc,'sales_qty'=>$qty,
			'price_per_unit'=>$amount,'discount_amt'=>0,'unit_total_cost'=>$amount,
			'total_cost'=>round($qty*$amount,2),'purchase_price'=>0,'status'=>1));
		$siId = $this->db->insert_id();
		if(!$siId) return array('ok'=>false,'error'=>'Charge line failed');
		$this->db->insert('db_daily_charges', array(
			'store_id'=>$this->_sid(),'admission_id'=>$adm->id,'charge_date'=>$date,
			'charge_code'=>$code,'description'=>$desc,'qty'=>$qty,'amount'=>$amount,
			'sales_id'=>$inv->id,'sales_item_id'=>$siId,'posted_at'=>date('Y-m-d H:i:s')));
		if(!$this->db->insert_id()) return array('ok'=>false,'error'=>'Charge record failed');
		// Recompute invoice totals (additive only; sales row stays Final).
		$tot = $this->db->select('COALESCE(SUM(total_cost),0) t',false)->where('sales_id',$inv->id)->get('db_salesitems')->row();
		$this->db->where('id',$inv->id)->update('db_sales', array(
			'subtotal'=>$tot->t,'grand_total'=>$tot->t));
		$this->load->model('Sales_model','sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($inv->id, $inv->customer_id);
		return array('ok'=>true,'charge_id'=>$this->db->insert_id());
	}
	/**
	 * Post charges for one date over every active admission in the store.
	 * Chargeable window = admission date .. min(charge date, day-before-closure);
	 * discharge-day and leave policies shape the bed charge. Re-runnable.
	 */
	public function postDailyCharges($storeId, $date = null){
		$date = $date ?: date('Y-m-d');
		$this->_ipdStoreId = (int)$storeId;
		$results = array('posted'=>0,'skipped'=>0,'settled'=>0,'errors'=>array());
		$adms = $this->db->where('store_id',$storeId)
			->where_in('status', array('active','discharged','deceased','transferred_out'))
			->get('db_admissions')->result();
		foreach($adms as $a0){
			$this->db->trans_begin();
			$adm = $this->db->query('SELECT a.*, p.customer_id FROM db_admissions a JOIN db_patients p ON p.id=a.patient_id WHERE a.id = ? AND a.store_id = ? FOR UPDATE', array($a0->id,$storeId))->row();
			if(!$adm){ $this->db->trans_rollback(); continue; }
			$from = substr($adm->admitted_at, 0, 10);
			$to   = $date;
			if($adm->closed_at){
				$closedDay = substr($adm->closed_at, 0, 10);
				if($closedDay < $to) $to = $closedDay;
			}
			if($from > $to){ $this->db->trans_commit(); continue; }
			// Leave days: policy-adjusted bed charge while the patient is out.
			$leaveDays = array();
			$leaves = $this->db->where('admission_id',$adm->id)->where_in('status',array('out','returned'))->get('db_leave_records')->result();
			foreach($leaves as $l){
				$d1 = substr($l->departed_at ?: '',0,10); $d2 = substr($l->returned_at ?: $date.' 23:59',0,10);
				for($d=$d1; $d && $d<=$d2; $d=date('Y-m-d',strtotime($d.' +1 day'))) $leaveDays[$d]=$l->billing_policy;
			}
			for($d=$from; $d<=$to; $d=date('Y-m-d',strtotime($d.' +1 day'))){
				$isDischargeDay = $adm->closed_at && $d === substr($adm->closed_at,0,10);
				$skipBed = $isDischargeDay && $this->policyFor($storeId,'discharge_day_charge','none') === 'none';
				if(!$skipBed){
					// Bed transfer mid-day never double-charges: one 'bed'
					// charge per admission-day, priced at the LAST bed held
					// that day (override rate if the bed has one).
					$occ = $this->db->query('SELECT o.bed_id, b.daily_rate FROM db_bed_occupancy o JOIN db_beds b ON b.id=o.bed_id WHERE o.admission_id=? AND o.from_at <= ? ORDER BY o.id DESC LIMIT 1',
						array($adm->id, $d.' 23:59:59'))->row();
					if($occ){
						$rate = $occ->daily_rate !== null ? (object)array('amount'=>$occ->daily_rate) : $this->rateFor('bed',$d);
						$amt = $rate ? (float)$rate->amount : 0;
						$desc = 'Bed charge';
						if(isset($leaveDays[$d])){
							$p = $leaveDays[$d];
							if($p === 'none'){ $amt = 0; $desc='Bed charge (authorised leave — not charged)'; }
							elseif($p === 'half'){ $amt = round($amt/2,2); $desc='Bed charge (authorised leave — 50%)'; }
						}
						$r = $this->_postCharge($adm,$d,'bed',$desc,1,$amt);
						!empty($r['replayed']) ? $results['skipped']++ : $results['posted']++;
					}
				}
				// Nursing day rate (optional rule)
				$nrate = $this->rateFor('nursing',$d);
				if($nrate && (float)$nrate->amount > 0 && !$skipBed){
					$r = $this->_postCharge($adm,$d,'nursing','Nursing care — daily',1,$nrate->amount);
					!empty($r['replayed']) ? $results['skipped']++ : $results['posted']++;
				}
				// Provided meals on this date each post once (meal:<order_id>)
				$meals = $this->db->where('admission_id',$adm->id)->where('meal_date',$d)
					->where('status','provided')->where('charge_amount >',0)->get('db_meal_orders')->result();
				foreach($meals as $m){
					$r = $this->_postCharge($adm,$d,'meal:'.$m->id,'Meal — '.$m->slot.' ('.$d.')',1,$m->charge_amount);
					!empty($r['replayed']) ? $results['skipped']++ : $results['posted']++;
				}
			}
			// Wallet auto-settlement — available wallet covers the new debt;
			// same-amount retries replay on the payment_reference.
			if($this->policyFor($storeId,'auto_settle_wallet','1') === '1'){
				$inv = $this->_invoice($adm->id);
				if($inv){
					$due = round($inv->grand_total - $inv->paid_amount, 2);
					if($due > 0){
						$this->load->model('Patient_funds_model','pf');
						$bal = $this->pf->getBalances($adm->patient_id);
						$pay = min($due, $bal['available']);
						if($pay > 0.001){
							$this->load->model('Patient_billing_model','pb');
							$r = $this->pb->addPayment($inv->id, $pay, 'patient_wallet',
								'Inpatient wallet settlement '.$date, 'ipd-settle:'.$adm->id.':'.$date.':'.$pay);
							if(!empty($r['ok'])) $results['settled'] += $pay;
						}
					}
				}
			}
			$this->db->trans_commit();
		}
		$this->_ipdStoreId = null;
		return $results;
	}
	public function chargeRegister($admId){
		return $this->db->where('admission_id',$admId)->order_by('charge_date, charge_code')->get('db_daily_charges')->result();
	}

	// ------------------------------------------------------------------
	// External referrals — destination, handover, procedure progress,
	// return + clinical review. Emergency may depart without the handover
	// doc but is flagged as a documented exception until reviewed.
	// ------------------------------------------------------------------
	public function createReferral($patientId, array $d){
		$urg = in_array(($d['urgency']??'routine'), array('routine','urgent','emergency'), true) ? $d['urgency'] : 'routine';
		if(trim((string)($d['destination']??''))==='') return array('ok'=>false,'error'=>'Destination is required');
		$this->db->insert('db_external_referrals', array(
			'store_id'=>get_current_store_id(),'patient_id'=>$patientId,
			'admission_id'=>!empty($d['admission_id'])?(int)$d['admission_id']:null,
			'episode_id'=>!empty($d['episode_id'])?(int)$d['episode_id']:null,
			'destination'=>trim($d['destination']),'reason'=>trim((string)($d['reason']??'')),
			'urgency'=>$urg,'requested_by'=>$this->session->userdata('inv_username'),
			'handover_doc_id'=>!empty($d['handover_doc_id'])?(int)$d['handover_doc_id']:null,
			'status'=>'open','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'referral_id'=>$id) : array('ok'=>false,'error'=>'Referral failed');
	}
	public function referralDepart($refId, $exceptionNote = ''){
		$this->db->trans_begin();
		$r = $this->db->query('SELECT * FROM db_external_referrals WHERE id=? AND store_id=? FOR UPDATE', array($refId,get_current_store_id()))->row();
		if(!$r || $r->status !== 'open'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Referral is not open'); }
		$exception = 0;
		if(!$r->handover_doc_id){
			if($r->urgency === 'emergency' && trim((string)$exceptionNote) !== ''){
				$exception = 1; // documented exception — must be reviewed on return
			} else {
				$this->db->trans_rollback();
				return array('ok'=>false,'error'=>'Handover document required — emergency departures need an exception note');
			}
		}
		$this->db->where('id',$refId)->update('db_external_referrals', array(
			'status'=>'departed','departed_at'=>date('Y-m-d H:i:s'),
			'exception_flag'=>$exception,'exception_note'=>$exception?substr(trim($exceptionNote),0,255):null,
			'updated_at'=>date('Y-m-d H:i:s')));
		$this->db->trans_commit();
		return array('ok'=>true,'exception'=>$exception);
	}
	public function referralUpdate($refId, $status, $procedureStatus = null){
		$r = $this->db->where('id',$refId)->where('store_id',get_current_store_id())->get('db_external_referrals')->row();
		if(!$r) return array('ok'=>false,'error'=>'Referral not found');
		$allowed = array('departed'=>array('procedure','returned','cancelled'),'procedure'=>array('returned','cancelled'),'open'=>array('cancelled'));
		if(!in_array($status, $allowed[$r->status] ?? array(), true))
			return array('ok'=>false,'error'=>"Cannot move {$r->status} → {$status}");
		$upd = array('status'=>$status,'updated_at'=>date('Y-m-d H:i:s'));
		if($status==='procedure' && $procedureStatus!==null) $upd['procedure_status']=substr($procedureStatus,0,255);
		if($status==='returned') $upd['returned_at']=date('Y-m-d H:i:s');
		$this->db->where('id',$refId)->update('db_external_referrals',$upd);
		return array('ok'=>true);
	}
	/** Return review — mandatory after return; clears documented exceptions. */
	public function referralReview($refId, $assessmentId = null){
		$this->db->trans_begin();
		$r = $this->db->query('SELECT * FROM db_external_referrals WHERE id=? AND store_id=? FOR UPDATE', array($refId,get_current_store_id()))->row();
		if(!$r || $r->status !== 'returned'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Only returned referrals can be reviewed'); }
		$this->db->where('id',$refId)->update('db_external_referrals', array(
			'status'=>'reviewed','reviewed_by'=>$this->session->userdata('inv_username'),
			'reviewed_at'=>date('Y-m-d H:i:s'),'return_assessment_id'=>$assessmentId,
			'exception_flag'=>0,'updated_at'=>date('Y-m-d H:i:s')));
		$this->db->trans_commit();
		return array('ok'=>true);
	}
	public function referrals($patientId = null, $status = null){
		$q = $this->db->select('r.*, c.customer_name AS patient_name, p.patient_code, a.admission_code')
			->from('db_external_referrals r')
			->join('db_patients p','p.id=r.patient_id')
			->join('db_customers c','c.id=p.customer_id','left')
			->join('db_admissions a','a.id=r.admission_id','left')
			->where('r.store_id',get_current_store_id());
		if($patientId) $q->where('r.patient_id',$patientId);
		if($status) $q->where('r.status',$status);
		return $q->order_by('r.id','desc')->get()->result();
	}

	// ------------------------------------------------------------------
	// Discharge — recommend → decide → actual departure. Approval never
	// closes the admission: only completeDischarge records the real
	// departure time that occupancy and billing depend on.
	// ------------------------------------------------------------------
	public function recommendDischarge($admId, $note){
		$adm = $this->db->where('id',$admId)->where('store_id',get_current_store_id())->get('db_admissions')->row();
		if(!$adm || $adm->status !== 'active') return array('ok'=>false,'error'=>'No active admission');
		$open = $this->db->where('admission_id',$admId)->where_in('status',array('recommended','approved'))->get('db_discharges')->row();
		if($open) return array('ok'=>false,'error'=>'A discharge is already in progress','discharge_id'=>$open->id);
		$this->db->insert('db_discharges', array(
			'store_id'=>get_current_store_id(),'admission_id'=>$admId,'status'=>'recommended',
			'recommended_by'=>(int)$this->session->userdata('inv_userid'),
			'recommended_at'=>date('Y-m-d H:i:s'),
			'recommendation_note'=>substr(trim((string)$note),0,255),'created_at'=>date('Y-m-d H:i:s')));
		$id=$this->db->insert_id();
		return $id ? array('ok'=>true,'discharge_id'=>$id) : array('ok'=>false,'error'=>'Discharge record failed');
	}
	public function decideDischarge($dischargeId, $approve, $reason, $summary, $followUp, $followUpDate = null){
		$this->db->trans_begin();
		$d = $this->db->query('SELECT * FROM db_discharges WHERE id=? AND store_id=? FOR UPDATE', array($dischargeId,get_current_store_id()))->row();
		if(!$d || $d->status !== 'recommended'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Discharge is not pending a decision'); }
		if($approve){
			if(trim((string)$reason)==='' || trim((string)$summary)===''){
				$this->db->trans_rollback(); return array('ok'=>false,'error'=>'Discharge reason and summary are required');
			}
			$this->db->where('id',$dischargeId)->update('db_discharges', array(
				'status'=>'approved','decided_by'=>(int)$this->session->userdata('inv_userid'),
				'decided_at'=>date('Y-m-d H:i:s'),'decision_note'=>null,
				'discharge_reason'=>substr(trim($reason),0,255),'summary'=>trim($summary),
				'follow_up'=>substr(trim((string)$followUp),0,255),
				'follow_up_date'=>$followUpDate ?: null));
		} else {
			if(trim((string)$reason)===''){
				$this->db->trans_rollback(); return array('ok'=>false,'error'=>'A rejection note is required');
			}
			$this->db->where('id',$dischargeId)->update('db_discharges', array(
				'status'=>'rejected','decided_by'=>(int)$this->session->userdata('inv_userid'),
				'decided_at'=>date('Y-m-d H:i:s'),'decision_note'=>substr(trim($reason),0,255)));
		}
		$this->db->trans_commit();
		return array('ok'=>true);
	}
	/** Records the ACTUAL departure — closes occupancy, suppresses future tasks, runs final billing. */
	public function completeDischarge($dischargeId){
		$this->db->trans_begin();
		$d = $this->db->query('SELECT * FROM db_discharges WHERE id=? AND store_id=? FOR UPDATE', array($dischargeId,get_current_store_id()))->row();
		if(!$d || $d->status !== 'approved'){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Discharge must be approved before the patient departs'); }
		$now = date('Y-m-d H:i:s');
		$adm = $this->db->where('id',$d->admission_id)->get('db_admissions')->row();
		$this->_closeOccupancy($adm->id, 'discharge', $now);
		$this->db->where('id',$adm->id)->update('db_admissions', array(
			'status'=>'discharged','closed_at'=>$now,'closed_by'=>(int)$this->session->userdata('inv_userid')));
		$this->db->where('id',$dischargeId)->update('db_discharges', array(
			'status'=>'completed','actual_discharge_at'=>$now,
			'porter_task_id'=>$this->_createMoveTask($adm,'discharge_move','Ward discharge — patient leaving')));
		// Patient has left — every outstanding task (overdue or future) is suppressed.
		$this->db->where('admission_id',$adm->id)->where('status','open')
			->update('db_nursing_tasks', array('status'=>'suppressed'));
		// Outpatient packages paused at admission resume for outpatient care.
		$this->db->where('patient_id',$adm->patient_id)->where('status','paused')
			->update('db_plan_entitlements', array('status'=>'active'));
		$this->db->trans_commit();
		// Final billing after commit boundary — posts remaining days then settles.
		$this->postDailyCharges($adm->store_id, date('Y-m-d'));
		return array('ok'=>true,'discharged_at'=>$now);
	}
	public function discharges($admId = null){
		$q = $this->db->select('d.*, a.admission_code, c.customer_name AS patient_name')
			->from('db_discharges d')->join('db_admissions a','a.id=d.admission_id')
			->join('db_patients p','p.id=a.patient_id')
			->join('db_customers c','c.id=p.customer_id','left')
			->where('d.store_id',get_current_store_id());
		if($admId) $q->where('d.admission_id',$admId);
		return $q->order_by('d.id','desc')->get()->result();
	}

	// ------------------------------------------------------------------
	// Deceased closure — authorised record, immediate suppression of all
	// future activity, bed release, invoice left intact for settlement.
	// ------------------------------------------------------------------
	public function recordDeceased($patientId, array $d){
		$storeId = get_current_store_id();
		if(trim((string)($d['notes']??''))==='') return array('ok'=>false,'error'=>'Documentation notes are required');
		$this->db->trans_begin();
		$p = $this->db->query('SELECT * FROM db_patients WHERE id=? AND store_id=? FOR UPDATE', array($patientId,$storeId))->row();
		if(!$p){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Patient not found'); }
		if((int)$p->deceased===1){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Already recorded deceased','replayed'=>true); }
		$at = !empty($d['at']) ? $d['at'] : date('Y-m-d H:i:s');
		$this->db->where('id',$patientId)->update('db_patients', array(
			'deceased'=>1,'deceased_date'=>substr($at,0,10),
			'deceased_recorded_by'=>(int)$this->session->userdata('inv_userid'),
			'deceased_notes'=>trim($d['notes'])));
		$adm = $this->db->where('patient_id',$patientId)->where('status','active')->get('db_admissions')->row();
		if($adm){
			$this->_closeOccupancy($adm->id,'deceased',$at);
			$this->db->where('id',$adm->id)->update('db_admissions', array(
				'status'=>'deceased','closed_at'=>$at,'closed_by'=>(int)$this->session->userdata('inv_userid')));
			$this->db->where('admission_id',$adm->id)->where('status','open')
				->update('db_nursing_tasks', array('status'=>'suppressed'));
			$this->db->where('admission_id',$adm->id)->where_in('status',array('open','in_progress'))
				->update('db_porter_tasks', array('status'=>'cancelled','notes'=>'admission closed — deceased'));
			$this->db->where('admission_id',$adm->id)->where_in('status',array('requested','approved'))
				->update('db_leave_records', array('status'=>'cancelled'));
			$this->db->where('admission_id',$adm->id)->where_in('status',array('recommended','approved'))
				->update('db_discharges', array('status'=>'rejected','decision_note'=>'Superseded — deceased closure'));
			$this->_createMoveTask($adm,'deceased_move','Deceased patient — mortuary/exit transfer');
		}
		// Future notifications for this patient stop; history stays.
		if($this->db->table_exists('db_notification_queue')){
			$this->db->query(
				'UPDATE db_notification_queue SET status="suppressed", last_error="deceased patient — suppressed at closure"
				 WHERE store_id = ? AND status IN ("queued","retry") AND payload_json LIKE ?',
				array($storeId, '%"patient_id":'.(int)$patientId.'%'));
		}
		$this->db->trans_commit();
		$this->postDailyCharges($storeId, substr($at,0,10));
		return array('ok'=>true,'admission_id'=>$adm ? $adm->id : null);
	}

	// ------------------------------------------------------------------
	// Internals
	// ------------------------------------------------------------------
	private function _closeOccupancy($admId, $reason, $at){
		$occ = $this->db->where('admission_id',$admId)->where('to_at IS NULL')->get('db_bed_occupancy')->row();
		if($occ){
			$this->db->where('id',$occ->id)->update('db_bed_occupancy', array('to_at'=>$at,'close_reason'=>$reason));
			$this->db->where('id',$occ->bed_id)->update('db_beds', array('status'=>'available'));
			return;
		}
		/*
		 * No open occupancy row for this admission — but the bed may still be
		 * flagged occupied, which is how a bed becomes permanently unusable.
		 *
		 * The flag and the occupancy row have to agree. If a discharge, a
		 * deceased closure or an interrupted transfer left the admission with no
		 * open row, the bed it held must be freed anyway: _openOccupancy()
		 * refuses any bed whose status is not 'available', so a stale flag makes
		 * the bed unadmittable forever while the board still counts it as full.
		 * (Observed on the dev store: 2 beds flagged occupied with no occupancy
		 * row at all, on admissions that had already closed.)
		 *
		 * Only beds with NO open occupancy anywhere are freed, so a bed that is
		 * genuinely in use by another admission is untouched.
		 */
		$beds = $this->db->query(
			'SELECT b.id FROM db_beds b
			  WHERE b.store_id = ? AND b.status = \'occupied\'
			    AND NOT EXISTS (SELECT 1 FROM db_bed_occupancy o
			                    WHERE o.bed_id = b.id AND o.to_at IS NULL)
			    AND EXISTS (SELECT 1 FROM db_bed_transfers t
			                WHERE t.admission_id = ? AND t.to_bed_id = b.id)',
			array(get_current_store_id(), $admId))->result();
		foreach($beds as $b){
			$this->db->where('id',$b->id)->update('db_beds', array('status'=>'available'));
		}
	}
	private function _createMoveTask($adm, $type, $note){
		$this->db->insert('db_porter_tasks', array(
			'store_id'=>get_current_store_id(),'task_type'=>$type,'ref_id'=>$adm->id,
			'admission_id'=>$adm->id,'patient_id'=>$adm->patient_id,
			'from_location'=>'ward','to_location'=>($type==='deceased_move'?'mortuary':'exit'),
			'status'=>'open','notes'=>$note,
			'requested_by'=>$this->session->userdata('inv_username') ?: 'system',
			'created_at'=>date('Y-m-d H:i:s')));
		return $this->db->insert_id();
	}
	/** Pause / replace outpatient entitlements on admission decision. */
	private function _applyOutpatientDecision($patientId, $decision){
		if($decision === 'continue') return;
		if($decision === 'pause'){
			$this->db->where('patient_id',$patientId)->where('status','active')
				->update('db_plan_entitlements', array('status'=>'paused'));
		} elseif($decision === 'replace'){
			// End active entitlements; release their open reservations so the
			// money returns to the wallet — the receivable itself is untouched.
			$ents = $this->db->where('patient_id',$patientId)->where('status','active')->get('db_plan_entitlements')->result();
			foreach($ents as $e){
				$res = $this->db->where('entitlement_id',$e->id)->where('status','active')->get('db_fund_reservations')->result();
				foreach($res as $r){
					$this->db->where('id',$r->id)->update('db_fund_reservations', array('status'=>'released','updated_at'=>date('Y-m-d H:i:s')));
					$this->db->insert('db_patient_wallet_txns', array(
						'store_id'=>get_current_store_id(),'patient_id'=>$patientId,
						'txn_type'=>'release','direction'=>'memo','amount'=>round($r->amount_reserved-$r->amount_consumed,2),
						'operation_key'=>'release:adm-replace:'.$e->id.':'.$r->id,
						'reservation_id'=>$r->id,'note'=>'Reservation released — outpatient package replaced at admission',
						'created_at'=>date('Y-m-d H:i:s'),'created_by'=>$this->session->userdata('inv_username') ?: 'system'));
				}
				$this->db->where('id',$e->id)->update('db_plan_entitlements', array('status'=>'replaced'));
			}
		}
	}
}
