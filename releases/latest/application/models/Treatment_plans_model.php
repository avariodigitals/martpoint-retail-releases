<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Treatment_plans_model — clinician-owned plans linked to the finalized
 * assessment and care episode. Billable lines are immutable price snapshots
 * (services may be repriced later; the plan keeps what was quoted).
 *
 * Versioning: every material change bumps `version`, archives the full
 * prior state into db_treatment_plan_versions, recomputes
 * `billing_fingerprint` (sha of billable content) and SUPERSEDES any
 * approval logs bound to this plan — approval must be re-requested.
 */
class Treatment_plans_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function getPlan($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_treatment_plans')->row();
	}

	public function getItems($planId, $version = null){
		$this->db->where('plan_id', $planId);
		if($version !== null) $this->db->where('plan_version', $version);
		return $this->db->order_by('sort_order,id')->get('db_treatment_plan_items')->result();
	}

	public function currentItems($plan){
		return $this->getItems($plan->id, $plan->version);
	}

	public function listForPatient($patientId, $status = null){
		$this->db->where('store_id', get_current_store_id())->where('patient_id', $patientId);
		if($status) $this->db->where('status', $status);
		return $this->db->order_by('id', 'desc')->get('db_treatment_plans')->result();
	}

	public function versions($planId){
		return $this->db->where('plan_id', $planId)->order_by('version', 'desc')
			->get('db_treatment_plan_versions')->result();
	}

	/** sha of the billable content — the approval-validity token. */
	public function fingerprint(array $items, $careSetting){
		$bill = array();
		foreach($items as $it){
			$bill[] = array((int)$it['item_id'], (float)$it['qty'], (float)$it['unit_price'], (string)($it['funding'] ?? 'bill'));
		}
		return sha1(json_encode($bill) . '|' . $careSetting);
	}

	public function createPlan(array $plan, array $items, $reason = 'Plan created'){
		$storeId = get_current_store_id();
		if(empty($items)) return array('ok' => false, 'error' => 'A plan needs at least one service line');

		$this->db->trans_begin();
		$countId = get_count_id('db_treatment_plans', $storeId);
		$data = array(
			'store_id'     => $storeId,
			'count_id'     => $countId,
			'plan_code'    => 'PL-' . str_pad($countId, 5, '0', STR_PAD_LEFT),
			'patient_id'   => $plan['patient_id'],
			'customer_id'  => $plan['customer_id'],
			'episode_id'   => $plan['episode_id'] ?? null,
			'assessment_id'=> $plan['assessment_id'] ?? null,
			'clinician_id' => $plan['clinician_id'],
			'branch_id'    => $plan['branch_id'] ?? null,
			'status'       => $plan['status'] ?? 'draft',
			'care_setting' => in_array($plan['care_setting'] ?? '', array('outpatient','inpatient')) ? $plan['care_setting'] : 'outpatient',
			'title'        => $plan['title'] ?? null,
			'goals'        => $plan['goals'] ?? null,
			'review_points'=> isset($plan['review_points']) ? json_encode($plan['review_points']) : null,
			'version'      => 1,
			'billing_fingerprint' => $this->fingerprint($items, $plan['care_setting'] ?? 'outpatient'),
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by'   => $this->session->userdata('inv_username') ?: 'system',
		);
		$this->db->insert('db_treatment_plans', $data);
		$planId = $this->db->insert_id();
		if(!$planId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Plan save failed'); }
		$this->insertItems($planId, 1, $items);
		$this->archiveVersion($planId, 1, $reason);
		$this->db->trans_commit();
		return array('ok' => true, 'plan_id' => $planId, 'version' => 1);
	}

	private function insertItems($planId, $version, array $items){
		$storeId = get_current_store_id();
		$i = 0;
		foreach($items as $it){
			$item = $this->db->where('id', $it['item_id'])->get('db_items')->row();
			if(!$item) continue;
			$this->db->insert('db_treatment_plan_items', array(
				'store_id'        => $storeId,
				'plan_id'         => $planId,
				'plan_version'    => $version,
				'item_id'         => $item->id,
				'item_name'       => $item->item_name,
				'qty'             => $it['qty'],
				'unit_price'      => isset($it['unit_price']) ? $it['unit_price'] : $item->sales_price,
				'sessions_per_unit' => $it['sessions_per_unit'] ?? 1,
				'funding'         => $it['funding'] ?? 'bill',
				'sort_order'      => $i++,
				'created_date'    => date('Y-m-d'),
				'created_time'    => date('H:i:s'),
				'created_by'      => $this->session->userdata('inv_username') ?: 'system',
			));
		}
	}

	private function archiveVersion($planId, $version, $reason){
		$plan = $this->db->where('id', $planId)->get('db_treatment_plans')->row();
		$items = $this->getItems($planId, $version);
		$this->db->insert('db_treatment_plan_versions', array(
			'store_id'       => get_current_store_id(),
			'plan_id'        => $planId,
			'version'        => $version,
			'snapshot_json'  => json_encode(array('plan' => $plan, 'items' => $items)),
			'change_reason'  => $reason,
			'changed_by'     => $this->session->userdata('inv_userid'),
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
		));
	}

	/**
	 * Material change → new version + supersede approvals bound to the
	 * previous billing fingerprint. $items=null keeps current lines.
	 */
	public function amendPlan($planId, array $planChanges, $items = null, $reason = ''){
		$plan = $this->getPlan($planId);
		if(!$plan) return array('ok' => false, 'error' => 'Plan not found');
		if(in_array($plan->status, array('completed','cancelled'))){
			return array('ok' => false, 'error' => 'Plan is ' . $plan->status . ' — cannot amend');
		}
		if($items === null){
			$items = array();
			foreach($this->currentItems($plan) as $it){
				$items[] = array('item_id' => $it->item_id, 'qty' => $it->qty, 'unit_price' => $it->unit_price,
					'sessions_per_unit' => $it->sessions_per_unit, 'funding' => $it->funding);
			}
		}
		$careSetting = $planChanges['care_setting'] ?? $plan->care_setting;

		$this->db->trans_begin();
		$newVersion = $plan->version + 1;
		$update = array(
			'version'    => $newVersion,
			'updated_at' => date('Y-m-d H:i:s'),
			'billing_fingerprint' => $this->fingerprint($items, $careSetting),
		);
		foreach(array('title','goals','care_setting','clinician_id') as $f){
			if(array_key_exists($f, $planChanges)) $update[$f] = $planChanges[$f];
		}
		if(array_key_exists('review_points', $planChanges)){
			$update['review_points'] = is_array($planChanges['review_points']) ? json_encode($planChanges['review_points']) : $planChanges['review_points'];
		}
		$this->db->where('id', $planId)->update('db_treatment_plans', $update);
		if($items !== null) $this->insertItems($planId, $newVersion, $items);
		$this->archiveVersion($planId, $newVersion, $reason ?: 'Amended to v' . $newVersion);

		// Changes invalidate prior approvals on this plan.
		$this->db->where('target_module', 'treatment_plan')
			->where('target_id', $planId)
			->where_in('status', array('pending','approved'))
			->update('db_approval_logs', array('status' => 'superseded', 'flagged_for_audit' => 1));

		$this->db->trans_commit();
		return array('ok' => true, 'plan_id' => $planId, 'version' => $newVersion);
	}

	public function setStatus($planId, $status, $reason = null){
		$plan = $this->getPlan($planId);
		if(!$plan) return array('ok' => false, 'error' => 'Plan not found');
		$update = array('status' => $status, 'updated_at' => date('Y-m-d H:i:s'));
		if($status === 'cancelled') $update['cancelled_reason'] = $reason;
		$this->db->where('id', $planId)->update('db_treatment_plans', $update);
		if($status === 'cancelled'){
			$this->db->where('target_module', 'treatment_plan')->where('target_id', $planId)
				->where_in('status', array('pending','approved'))
				->update('db_approval_logs', array('status' => 'superseded'));
		}
		return array('ok' => true);
	}

	/** Service items (service_bit=1) billable for plan lines. */
	public function billableServices(){
		return $this->db->select('id, item_name, sales_price')
			->where('store_id', get_current_store_id())
			->where('service_bit', 1)->where('status', 1)
			->order_by('item_name')->get('db_items')->result();
	}
}
