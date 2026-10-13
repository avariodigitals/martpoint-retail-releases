<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient_billing_model — finance handover for treatment plans.
 *
 * Revenue is recognised AT BILL TIME (docs/physiotherapy_funds_ledger.md §5):
 * createBillFromPlan() posts a real db_sales invoice (the revenue event),
 * itemised via db_salesitems + db_patient_bill_items (plan linkage).
 * Payments are db_salespayments rows (partial allowed); payment_status and
 * the customer due are recomputed by the existing Sales_model updater.
 *
 * Approvals (persistent, target-bound):
 *  - md_discount     — bill discounts above the front-desk limit
 *  - credit_override — posting/spending beyond covered funds
 *  - plan_material_change — restricted post-bill adjustments
 *  - refund_wallet   — wallet cash-outs (handled by funds controller)
 * A change to the bill supersedes its approval logs; posting re-checks a
 * CURRENT approved log for the bill's fingerprint version.
 * Finance staff may never approve their own requests — enforced here in
 * addition to the store-wide allow_self_approval setting.
 */
class Patient_billing_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->model('Approval_logs_model', 'al');
	}

	public function getBill($salesId){
		return $this->db->where('id', $salesId)->where('store_id', get_current_store_id())
			->get('db_sales')->row();
	}

	public function billItems($salesId){
		return $this->db->where('sales_id', $salesId)->order_by('id')
			->get('db_patient_bill_items')->result();
	}

	/**
	 * Lines for a printed document. Plan and visit bills mirror their clinical
	 * itemisation in db_patient_bill_items, but a bill raised straight at the
	 * counter only ever lands in db_salesitems — so fall back rather than print
	 * a blank "what this bill covers".
	 */
	public function printItems($salesId){
		$rows = $this->billItems($salesId);
		if($rows) return $rows;
		$out = array();
		$src = $this->db->select('a.description, a.sales_qty, a.price_per_unit, a.total_cost, c.item_name')
			->from('db_salesitems a')
			->join('db_items c', 'c.id = a.item_id', 'left')
			->where('a.sales_id', (int)$salesId)->order_by('a.id')->get()->result();
		foreach($src as $r){
			$desc = trim((string)$r->description) !== '' ? $r->description : (trim((string)$r->item_name) !== '' ? $r->item_name : 'Item');
			$out[] = (object)array(
				'description' => (string)$desc,
				'qty'         => (float)$r->sales_qty,
				'unit_price'  => (float)$r->price_per_unit,
				'total'       => (float)$r->total_cost,
			);
		}
		return $out;
	}

	public function billsForPatient($patientId){
		$storeId = get_current_store_id();
		return $this->db->select('s.*')
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->where('s.store_id', $storeId)->where('p.id', $patientId)
			->where('(s.plan_id IS NOT NULL OR s.encounter_id IS NOT NULL OR s.reference_no LIKE "OPENING-%")', null, false)
			->order_by('s.id', 'desc')->get()->result();
	}

	public function paymentsFor($salesId){
		return $this->db->where('sales_id', $salesId)->where('store_id', get_current_store_id())
			->order_by('id')->get('db_salespayments')->result();
	}

	/**
	 * Visit bills for one care-queue encounter — the bills raised at the
	 * waiting-for-nurse desk (assessment fee or any counter bill). Each is a
	 * normal db_sales invoice tagged with encounter_id, so plan bills and
	 * visit bills read the same downstream.
	 */
	public function billsForEncounter($encounterId){
		return $this->db->select('s.*, p.patient_code, c.customer_name AS patient_name')
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id', 'left')
			->join('db_customers c', 'c.id = s.customer_id AND c.store_id = s.store_id', 'left')
			->where('s.store_id', get_current_store_id())
			->where('s.encounter_id', (int)$encounterId)
			->order_by('s.id', 'desc')->get()->result();
	}

	/**
	 * Visit bill — raised at check-in (waiting-for-nurse stage) for the
	 * assessment fee or any other counter charge. $lines is a list of
	 * array(item_id, description, qty, unit_price). Unlike plan bills these
	 * are NOT plan-linked, so no entitlement is spawned; payments settle them
	 * like any retail invoice. An existing open bill for the same encounter
	 * + reference replays instead of duplicating.
	 */
	public function createVisitBill($encounterId, array $lines, $note = '', $accountId = null, $careType = null){
		$storeId = get_current_store_id();
		$enc = $this->db->where('id', (int)$encounterId)->where('store_id', $storeId)
			->get('db_encounters')->row();
		if(!$enc) return array('ok' => false, 'error' => 'Visit not found');
		$patient = $this->db->where('id', (int)$enc->patient_id)->where('store_id', $storeId)
			->get('db_patients')->row();
		if(!$patient) return array('ok' => false, 'error' => 'No patient record on this visit');

		/* Inpatient vs outpatient is a clinical decision, never a billing one:
		   the physiotherapist records it at the assessment remark, or admitting
		   the patient settles it. A bill raised without an explicit mark (a
		   clinician adding their recommendation, or counter fees before the
		   assessment) inherits the episode's type rather than landing unmarked. */
		$careType = in_array($careType, array('inpatient', 'outpatient'), true) ? $careType : null;
		if($careType === null && !empty($enc->episode_id) && $this->db->field_exists('episode_type', 'db_care_episodes')){
			$epRow = $this->db->select('episode_type')->where('id', (int)$enc->episode_id)->get('db_care_episodes')->row();
			$careType = $epRow && in_array($epRow->episode_type, array('inpatient','outpatient'), true) ? $epRow->episode_type : null;
		}

		$clean = array();
		$subtotal = 0;
		foreach((array)$lines as $ln){
			$desc = trim((string)($ln['description'] ?? ''));
			$qty  = max(0.01, (float)($ln['qty'] ?? 1));
			$price= max(0, (float)($ln['unit_price'] ?? 0));
			if($desc === '' || ($price <= 0 && $qty <= 0)) continue;
			$clean[] = array(
				'item_id'     => (int)($ln['item_id'] ?? 0) ?: null,
				'description' => $desc,
				'qty'         => $qty,
				'unit_price'  => $price,
			);
			$subtotal += $qty * $price;
		}
		if(empty($clean)) return array('ok' => false, 'error' => 'Add at least one billable line');

		$this->load->model('Physio_accounts_model', 'pa');
		$physioAccountId = $this->pa->forCustomer($patient->customer_id, $storeId);
		$systemAccountId = null;
		try {
			$context = $this->pa->systemContext($patient->customer_id, $storeId);
			$systemAccountId = $this->pa->resolveSystemAccount($patient->customer_id, $storeId, $accountId);
		} catch (Exception $e) { /* account wiring is optional on a visit bill */ }

		$this->db->trans_begin();
		$this->db->query('SELECT 1 FROM db_store WHERE id = ? FOR UPDATE', [(int)$storeId]);
		$initCode = get_only_init_code('sales');
		$countId  = autosynch_sales_code();
		$salesCode = $initCode . $countId;

		$invoice = array(
			'init_code'      => $initCode,
			'count_id'       => $countId,
			'sales_code'     => $salesCode,
			'reference_no'   => 'ENC-' . $enc->encounter_code,
			'sales_date'     => date('Y-m-d'),
			'sales_status'   => 'Final',
			'customer_id'    => $patient->customer_id,
			'encounter_id'   => (int)$encounterId,
			'subtotal'       => $subtotal,
			'grand_total'    => $subtotal,
			'paid_amount'    => 0,
			'payment_status' => 'Unpaid',
			'sales_note'     => trim((string)$note) !== '' ? $note : ('Visit bill ' . $enc->encounter_code),
			'care_type'      => $careType,
			'store_id'       => $storeId,
			'warehouse_id'   => $enc->warehouse_id ?: (function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null),
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'      => $this->input->ip_address(),
			'system_name'    => php_uname(),
			'status'         => 1,
		);
		if ($systemAccountId) $invoice['physio_system_account_id'] = $systemAccountId;
		// Migration 4.0.9.145 adds care_type — skip it on an un-migrated database
		// rather than letting the whole INSERT fail on an unknown column.
		if(!$this->db->field_exists('care_type', 'db_sales')) unset($invoice['care_type']);
		$this->db->insert('db_sales', $invoice);
		$salesId = (int)$this->db->insert_id();
		if (!$salesId) { $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Bill insert failed'); }

		if($careType){ $this->applyCareType((int)$encounterId, $careType); }

		foreach ($clean as $ln) {
			$lineTotal = $ln['qty'] * $ln['unit_price'];
			$this->db->insert('db_salesitems', array(
				'sales_id'        => $salesId,
				'physio_account_id'=> $physioAccountId,
				'store_id'        => $storeId,
				'sales_status'    => 'Final',
				'item_id'         => $ln['item_id'],
				'description'     => $ln['description'],
				'sales_qty'       => $ln['qty'],
				'price_per_unit'  => $ln['unit_price'],
				'discount_amt'    => 0,
				'unit_total_cost' => $ln['unit_price'],
				'total_cost'      => $lineTotal,
				'purchase_price'  => 0,
				'status'          => 1,
			));
			// Clinical itemisation mirrors the sales line; plan fields stay
			// NULL because a visit bill is not plan-linked.
			$this->db->insert('db_patient_bill_items', array(
				'store_id'      => $storeId,
				'sales_id'      => $salesId,
				'sales_item_id' => (int)$this->db->insert_id(),
				'item_id'       => $ln['item_id'] ?: 0,
				'description'   => $ln['description'],
				'qty'           => $ln['qty'],
				'unit_price'    => $ln['unit_price'],
				'total'         => $lineTotal,
				'created_date'  => date('Y-m-d'),
				'created_time'  => date('H:i:s'),
				'created_by'    => $this->session->userdata('inv_username') ?: 'system',
			));
		}
		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($salesId, $patient->customer_id);
		$this->db->trans_commit();
		if ($this->db->trans_status() === FALSE) {
			return array('ok' => false, 'error' => 'Bill could not be saved');
		}
		return array('ok' => true, 'sales_id' => $salesId, 'sales_code' => $salesCode);
	}

	/**
	 * Record the care type of a whole visit — the physiotherapist's call at the
	 * assessment remark, or the act of admitting the patient. It is never a
	 * billing decision: the person raising a bill cannot make it.
	 *
	 * Mirrors the mark onto the care episode, so the patient profile groups this
	 * spell under the right heading. An episode is one spell of care under one
	 * type. If this visit is typed differently from the episode it landed on,
	 * the visit belongs to a different spell: retype the episode when this is
	 * its only visit, otherwise give this visit its own new episode. The old
	 * episode is never rewritten or closed — its own visits keep their type.
	 *
	 * Stamps every bill of the visit, so counter fees raised before the decision
	 * follow it instead of keeping whatever they inherited.
	 */
	public function applyCareType($encounterId, $careType){
		$careType = in_array($careType, array('inpatient', 'outpatient'), true) ? $careType : null;
		if(!$careType) return array('ok' => false, 'error' => 'Mark this visit Inpatient or Outpatient');
		$storeId = get_current_store_id();
		$enc = $this->db->where('id', (int)$encounterId)->where('store_id', $storeId)
			->get('db_encounters')->row();
		if(!$enc) return array('ok' => false, 'error' => 'Visit not found');

		if($this->db->field_exists('care_type', 'db_sales')){
			$this->db->where('encounter_id', (int)$encounterId)->where('store_id', $storeId)
				->update('db_sales', array('care_type' => $careType));
		}

		if(!empty($enc->episode_id) && $this->db->field_exists('episode_type', 'db_care_episodes')){
			$ep = $this->db->where('id', (int)$enc->episode_id)->where('store_id', $storeId)
				->get('db_care_episodes')->row();
			if($ep && $ep->episode_type !== $careType){
				$others = $this->db->where('episode_id', (int)$ep->id)
					->where('id !=', (int)$encounterId)->count_all_results('db_encounters');
				if((int)$others === 0){
					// Only this visit sits on the episode — retyping it is honest.
					$this->db->where('id', (int)$ep->id)->update('db_care_episodes', array('episode_type' => $careType));
				} else {
					$countId = function_exists('get_count_id') ? get_count_id('db_care_episodes', $storeId) : null;
					$this->db->insert('db_care_episodes', array(
						'store_id'     => $storeId,
						'patient_id'   => (int)$enc->patient_id,
						'count_id'     => $countId,
						'episode_code' => 'EP-'.str_pad((string)$countId, 5, '0', STR_PAD_LEFT),
						'warehouse_id' => $enc->warehouse_id ?: null,
						'episode_type' => $careType,
						'started_at'   => date('Y-m-d H:i:s'),
						'status'       => 'open',
						'created_date' => date('Y-m-d'),
						'created_time' => date('H:i:s'),
						'created_by'   => $this->session->userdata('inv_username') ?: 'system',
					));
					$newEpId = (int)$this->db->insert_id();
					if($newEpId){
						$this->db->where('id', (int)$encounterId)->where('store_id', $storeId)
							->update('db_encounters', array('episode_id' => $newEpId));
					}
				}
			}
		}
		return array('ok' => true);
	}

	/**
	 * Append one line to a Final bill and re-total it — used when the treating
	 * clinician adds their recommendation to a bill the desk already raised.
	 *
	 * Raising grand_total moves billFingerprint(), so any discount approval
	 * logged against the old total no longer matches and must be re-approved.
	 * That is deliberate: an approver never signs off on a total that has
	 * silently grown underneath them.
	 */
	public function addLineToBill($salesId, array $line){
		$storeId = get_current_store_id();
		$desc = trim((string)($line['description'] ?? ''));
		if($desc === '') return array('ok' => false, 'error' => 'A line description is required');
		$qty   = max(0.01, (float)($line['qty'] ?? 1));
		$price = max(0, (float)($line['unit_price'] ?? 0));

		$this->db->trans_begin();
		$sale = $this->db->query('SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE', [(int)$salesId, (int)$storeId])->row();
		if(!$sale){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Bill not found'); }
		if((string)$sale->sales_status !== 'Final'){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Only a finalised bill can be amended'); }

		$physioAccountId = null;
		$this->load->model('Physio_accounts_model', 'pa');
		try { $physioAccountId = $this->pa->forCustomer($sale->customer_id, $storeId); } catch (Exception $e) { /* optional */ }

		$lineTotal = round($qty * $price, 2);
		$this->db->insert('db_salesitems', array(
			'sales_id'         => (int)$salesId,
			'physio_account_id'=> $physioAccountId,
			'store_id'         => $storeId,
			'sales_status'     => 'Final',
			'item_id'          => (int)($line['item_id'] ?? 0) ?: null,
			'description'      => $desc,
			'sales_qty'        => $qty,
			'price_per_unit'   => $price,
			'discount_amt'     => 0,
			'unit_total_cost'  => $price,
			'total_cost'       => $lineTotal,
			'purchase_price'   => 0,
			'status'           => 1,
		));
		$this->db->insert('db_patient_bill_items', array(
			'store_id'      => $storeId,
			'sales_id'      => (int)$salesId,
			'sales_item_id' => (int)$this->db->insert_id(),
			'item_id'       => (int)($line['item_id'] ?? 0) ?: 0,
			'description'   => $desc,
			'qty'           => $qty,
			'unit_price'    => $price,
			'total'         => $lineTotal,
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'created_by'    => $this->session->userdata('inv_username') ?: 'system',
		));

		$newSubtotal = round((float)$sale->subtotal + $lineTotal, 2);
		$newGrand    = round($newSubtotal - (float)$sale->tot_discount_to_all_amt, 2);
		$this->db->where('id', (int)$salesId)->update('db_sales', array(
			'subtotal'    => $newSubtotal,
			'grand_total' => $newGrand,
		));

		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id((int)$salesId, $sale->customer_id);
		$this->db->trans_commit();
		if($this->db->trans_status() === FALSE){
			return array('ok' => false, 'error' => 'The line could not be added to the bill');
		}
		return array('ok' => true, 'sales_id' => (int)$salesId, 'sales_code' => $sale->sales_code,
			'line_total' => $lineTotal, 'grand_total' => $newGrand);
	}

	/** sha over the bill's mutable values — approvals bind to this. */
	public function billFingerprint($sale){
		// db_approval_logs.target_version is VARCHAR(64) (migration .76) —
		// approvals bind to a sha256 of the bill's mutable money fields.
		// (Replaces crc32: its unsigned value overflowed signed-INT storage,
		// and a 32-bit hash left real collision risk between bill states.)
		return substr(hash('sha256',
			$sale->grand_total . '|' . $sale->tot_discount_to_all_amt . '|' . $sale->payment_status . '|' . $sale->id), 0, 40);
	}

	/**
	 * Plan → itemised bill. One bill per plan version snapshot; a duplicate
	 * call for the same plan+version replays the existing bill.
	 */
	public function createBillFromPlan($planId, $accountId = null){
		$storeId = get_current_store_id();
		$this->load->model('Treatment_plans_model', 'tpm');
		$plan = $this->tpm->getPlan($planId);
		if(!$plan) return array('ok' => false, 'error' => 'Plan not found');
		if(!in_array($plan->status, array('draft','active'))){
			return array('ok' => false, 'error' => 'Cannot bill a ' . $plan->status . ' plan');
		}
		$existing = $this->db->where('plan_id', $planId)->where('store_id', $storeId)
			->where('sales_status', 'Final')->get('db_sales')->row();
		if($existing){
			return array('ok' => true, 'sales_id' => $existing->id, 'sales_code' => $existing->sales_code, 'replayed' => true);
		}

		$items = $this->tpm->currentItems($plan);
		if(empty($items)) return array('ok' => false, 'error' => 'Plan has no billable lines');

		$this->load->model('Physio_accounts_model','pa');
		try{ $systemAccountId=$this->pa->resolveSystemAccount($plan->customer_id,$storeId,$accountId); }
		catch(Exception $e){ return array('ok'=>false,'error'=>$e->getMessage()); }
		$this->db->trans_begin();
		// Serialise invoice-number generation per store.
		$this->load->model('Physio_accounts_model','pa');
		$physioAccountId=$this->pa->forCustomer($plan->customer_id,$storeId);
		$this->db->query('SELECT 1 FROM db_store WHERE id = ? FOR UPDATE', [(int)$storeId]);
		$initCode = get_only_init_code('sales');
		$countId  = autosynch_sales_code();

		$subtotal = 0;
		foreach($items as $it){ $subtotal += $it->qty * $it->unit_price; }

		// Inherit the care type from the plan's episode — a plan bill is raised
		// on existing care, so it should never land unmarked next to it.
		$planCareType = null;
		if(!empty($plan->episode_id) && $this->db->field_exists('episode_type', 'db_care_episodes')){
			$planEp = $this->db->select('episode_type')->where('id', (int)$plan->episode_id)->get('db_care_episodes')->row();
			if($planEp && in_array($planEp->episode_type, array('inpatient','outpatient'), true)) $planCareType = $planEp->episode_type;
		}

		$invoice = array(
			'init_code'      => $initCode,
			'count_id'       => $countId,
			'sales_code'     => $initCode . $countId,
			'reference_no'   => 'PLAN-' . $plan->plan_code . '-v' . $plan->version,
			'sales_date'     => date('Y-m-d'),
			'sales_status'   => 'Final',
			'customer_id'    => $plan->customer_id,
			'plan_id'        => $planId,
			'care_type'      => $planCareType,
			'subtotal'       => $subtotal,
			'grand_total'    => $subtotal,
			'paid_amount'    => 0,
			'payment_status' => 'Unpaid',
			'sales_note'     => 'Treatment plan ' . $plan->plan_code . ' v' . $plan->version,
			'store_id'       => $storeId,
			'warehouse_id'   => function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null,
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'      => $this->input->ip_address(),
			'system_name'    => php_uname(),
			'status'         => 1,
		);
		if($systemAccountId) $invoice['physio_system_account_id']=$systemAccountId;
		if(!$this->db->field_exists('care_type', 'db_sales')) unset($invoice['care_type']);
		$this->db->insert('db_sales',$invoice);
		$salesId = $this->db->insert_id();
		if(!$salesId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Bill insert failed'); }

		foreach($items as $it){
			$lineTotal = $it->qty * $it->unit_price;
			$this->db->insert('db_salesitems', array(
				'sales_id'        => $salesId,
				'physio_account_id'=> $physioAccountId,
				'store_id'        => $storeId,
				'sales_status'    => 'Final',
				'item_id'         => $it->item_id,
				'description'     => $it->item_name,
				'sales_qty'       => $it->qty,
				'price_per_unit'  => $it->unit_price,
				'discount_amt'    => 0,
				'unit_total_cost' => $it->unit_price,
				'total_cost'      => $lineTotal,
				'purchase_price'  => 0,
				'status'          => 1,
			));
			$siId = $this->db->insert_id();
			if(!$siId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Bill line failed'); }
			$this->db->insert('db_patient_bill_items', array(
				'store_id'     => $storeId,
				'sales_id'     => $salesId,
				'sales_item_id'=> $siId,
				'plan_id'      => $planId,
				'plan_item_id' => $it->id,
				'plan_version' => $plan->version,
				'item_id'      => $it->item_id,
				'description'  => $it->item_name,
				'qty'          => $it->qty,
				'unit_price'   => $it->unit_price,
				'total'        => $lineTotal,
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s'),
				'created_by'   => $this->session->userdata('inv_username') ?: 'system',
			));
		}
		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($salesId, $plan->customer_id);
		$this->db->trans_commit();
		return array('ok' => true, 'sales_id' => $salesId, 'sales_code' => $initCode . $countId);
	}

	// ------------------------------------------------------------------
	// Payments — partial allowed. 'patient_wallet' spends derived funds.
	// ------------------------------------------------------------------

	/** Insert the db_salespayments row (no status recompute — caller does). */
	public function insertPaymentRow($salesId, $amount, $type, $note = '', $paymentRef = null, $accountId = null){
		$storeId = get_current_store_id();
		$sale = $this->getBill($salesId);
		// Link the payment mode so cash-flow/cashier reports classify the row
		$this->load->model('Physio_accounts_model','pa');
		$physioAccountId=$this->pa->forCustomer($sale->customer_id,$storeId);
		// correctly (patient_wallet / wallet_reversal are non-cash modes).
		$modeId = null;
		if($this->db->field_exists('payment_mode_id', 'db_salespayments')){
			$mode = $this->db->select('id')->where('store_id', $storeId)
				->where('code', $type)->get('db_payment_modes')->row();
			if(!$mode){
				$mode = $this->db->select('id')->where('store_id', $storeId)
					->where("LOWER(code) = " . $this->db->escape(strtolower($type)), null, false)
					->get('db_payment_modes')->row();
			}
			$modeId = $mode ? (int)$mode->id : null;
		}
		$this->db->insert('db_salespayments', array(
			'count_id'       => get_count_id('db_salespayments', $storeId),
			'payment_code'   => get_init_code('salespayment') . get_count_id('db_salespayments', $storeId),
			'store_id'       => $storeId,
			'sales_id'       => $salesId,
			'payment_date'   => date('Y-m-d'),
			'physio_account_id'=> $physioAccountId,
			'account_id'=> $accountId,
			'payment_type'   => $type,
			'payment_mode_id'=> $modeId,
			'payment'        => $amount,
			'payment_note'   => $note,
			'payment_reference' => $paymentRef,
			'customer_id'    => $sale->customer_id,
			'created_time'   => date('H:i:s'),
			'created_date'   => date('Y-m-d'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'      => $this->input->ip_address(),
			'system_name'    => php_uname(),
			'status'         => 1,
		));
		$paymentId=$this->db->insert_id();
		if($paymentId && $accountId && !in_array($type,array('patient_wallet','wallet_reversal','cust_advance'),true)){
			$this->load->helper('accounts');
			$row=$this->db->where('id',$paymentId)->get('db_salespayments')->row();
			if(!insert_account_transaction(array('transaction_type'=>'SALES PAYMENT','reference_table_id'=>$paymentId,'credit_account_id'=>$accountId,'debit_account_id'=>null,'credit_amt'=>$amount,'debit_amt'=>0,'process'=>'SAVE','note'=>$note,'transaction_date'=>date('Y-m-d'),'payment_code'=>$row->payment_code,'customer_id'=>$sale->customer_id,'supplier_id'=>null))) return false;
		}
		return $paymentId;
	}

	/**
	 * Receive a payment. $type: cash|transfer|pos|cust_advance|patient_wallet.
	 * Wallet payments consume funds inside the same transaction.
	 */
	public function addPayment($salesId, $amount, $type, $note = '', $paymentRef = null, $accountId = null){
		$storeId = get_current_store_id();
		$sale = $this->getBill($salesId);
		if(!$sale) return array('ok' => false, 'error' => 'Bill not found');
		// 'Opening' invoices (migrated debt) accept payments like any other bill.
		if(!in_array($sale->sales_status, array('Final', 'Opening'))){
			return array('ok' => false, 'error' => 'Bill is not active');
		}
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');
		$this->load->model('Physio_accounts_model','pa');
		// Wallet/store-credit settlement applies existing money, never credits a cash account.
		$systemAccountId=null;
		if(!in_array($type,array('patient_wallet','wallet_reversal','cust_advance'),true)){
			try{
				$context=$this->pa->systemContext($sale->customer_id,$storeId);
				$choice=$accountId ?: (!$context['bed'] ? ($sale->physio_system_account_id ?? null) : null);
				$systemAccountId=$this->pa->resolveSystemAccount($sale->customer_id,$storeId,$choice);
			}
			catch(Exception $e){ return array('ok'=>false,'error'=>$e->getMessage()); }
		}
		// Idempotency: a used reference replays ONLY the identical request —
		// same sale, type and amount. Reusing the reference with different
		// details is a conflict, never a second payment.
		if($paymentRef){
			$used = $this->db->where('store_id', $storeId)
				->where('payment_reference', $paymentRef)
				->get('db_salespayments')->result();
			if($used){
				foreach($used as $p){
					if((int)$p->sales_id === (int)$salesId && $p->payment_type === $type
						&& round((float)$p->payment, 2) === $amount
						&& (!$this->pa->systemEnabled($storeId) || (int)$p->account_id === (int)$systemAccountId)){
						return array('ok' => true, 'payment_id' => $p->id, 'replayed' => true);
					}
				}
				return array('ok' => false, 'conflict' => true,
					'error' => 'Reference conflict — this payment reference was already used with different details');
			}
		}
		$due = round($sale->grand_total - $sale->paid_amount, 2);
		if($amount > $due + 0.01) return array('ok' => false, 'error' => 'Payment exceeds outstanding ' . $due);

		if($type === 'patient_wallet'){
			$this->load->model('Patient_funds_model', 'pf');
			$patient = $this->db->where('customer_id', $sale->customer_id)->where('store_id', $storeId)->get('db_patients')->row();
			if(!$patient) return array('ok' => false, 'error' => 'No patient record on this customer');
			// Idempotent on a payment-attempt key passed via note? Callers retry
			// with the same operation_key derived from payment_ref.
			$opKey = 'billpay:' . $salesId . ':' . ($paymentRef ?: uniqid());
			// spend() owns its transaction; then post the payment row.
			$sp = $this->pf->spend($patient->id, $amount, $opKey, array('sales_id' => $salesId, 'note' => 'Bill payment ' . $sale->sales_code));
			if(!$sp['ok']) return $sp;
			$this->db->trans_begin();
			$payId = $this->insertPaymentRow($salesId, $amount, 'patient_wallet', $note, $paymentRef);
			if(!$payId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Payment row failed'); }
			$this->db->where('id', $sp['txn_id'])->update('db_patient_wallet_txns', array('salespayment_id' => $payId));
		} else {
			$this->db->trans_begin();
			$payId = $this->insertPaymentRow($salesId, $amount, $type, $note, $paymentRef, $systemAccountId);
			if(!$payId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Payment row failed'); }
		}
		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($salesId, $sale->customer_id);
		$this->syncEntitlements($salesId);
		if(!$this->db->trans_status()){ $this->db->trans_rollback(); return array('ok'=>false,'error'=>'Payment could not be saved'); }
		$this->db->trans_commit();
		return array('ok' => true, 'payment_id' => $payId);
	}

	/**
	 * Paid coverage → session entitlements. Bill lines are covered in order
	 * by cumulative payments; each fully-or-partially covered unit yields
	 * sessions_per_unit sessions. The per-line entitlement grows as more is
	 * paid — units_total never shrinks below units_used.
	 */
	public function syncEntitlements($salesId){
		$storeId = get_current_store_id();
		$sale = $this->getBill($salesId);
		if(!$sale) return;
		$patient = $this->db->where('customer_id', $sale->customer_id)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$patient) return;
		$this->load->model('Sessions_model', 'sm');

		$remaining = min($sale->paid_amount, $sale->grand_total);
		$items = $this->db->where('sales_id', $salesId)->order_by('id')->get('db_patient_bill_items')->result();
		foreach($items as $it){
			$cover = max(0, min($remaining, $it->total));
			$remaining -= $cover;
			$unitsCovered = ($it->unit_price > 0) ? floor(($cover + 0.0001) / $it->unit_price) : 0;
			$planItem = $it->plan_item_id
				? $this->db->where('id', $it->plan_item_id)->get('db_treatment_plan_items')->row() : null;
			$sessions = (int)round($unitsCovered * ($planItem ? $planItem->sessions_per_unit : 1));
			if($it->entitlement_id){
				$ent = $this->db->where('id', $it->entitlement_id)->get('db_plan_entitlements')->row();
				if($ent && $sessions > $ent->units_total){
					$this->db->where('id', $ent->id)->update('db_plan_entitlements', array(
						'units_total'   => $sessions,
						'funded_amount' => max($ent->funded_amount, $cover),
						'status'        => ($ent->status === 'exhausted' && $sessions > $ent->units_used) ? 'active' : $ent->status,
					));
				}
			} else if($sessions > 0){
				$r = $this->sm->createEntitlement(array(
					'patient_id'    => $patient->id,
					'plan_id'       => $it->plan_id,
					'sale_id'       => $salesId,
					'service_id'    => $it->item_id,
					'units_total'   => $sessions,
					'funded_amount' => $cover,
					'source'        => 'purchase',
					'valuation_note'=> 'Billed on ' . $sale->sales_code,
				));
				if($r['ok']){
					$this->db->where('id', $it->id)->update('db_patient_bill_items', array('entitlement_id' => $r['entitlement_id']));
				}
			}
		}
	}

	// ------------------------------------------------------------------
	// Approvals
	// ------------------------------------------------------------------

	/**
	 * Request a bill discount — posts a persistent md_discount approval log
	 * bound to the bill's current fingerprint version.
	 */
	public function requestDiscount($salesId, $discountAmt, $reason){
		$sale = $this->getBill($salesId);
		if(!$sale) return array('ok' => false, 'error' => 'Bill not found');
		if(!$reason) return array('ok' => false, 'error' => 'A reason is required');
		$discountAmt = round((float)$discountAmt, 2);
		if($discountAmt <= 0 || $discountAmt > $sale->grand_total){
			return array('ok' => false, 'error' => 'Invalid discount amount');
		}
		$logId = $this->al->log(array(
			'action_type'         => 'request',
			'approval_type'       => 'md_discount',
			'requesting_user_id'  => $this->session->userdata('inv_userid'),
			'requesting_user_name'=> $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
			'reason'              => $reason,
			'previous_value'      => json_encode(array('grand_total' => $sale->grand_total, 'discount' => $sale->tot_discount_to_all_amt)),
			'new_value'           => json_encode(array('discount' => $discountAmt)),
			'amount'              => $discountAmt,
			'target_module'       => 'patient_bill',
			'target_id'           => $salesId,
			'target_version'      => $this->billFingerprint($sale),
		));
		return array('ok' => true, 'log_id' => $logId);
	}

	/**
	 * Request a FULL waiver of what is still owed — if the Clinical Director
	 * approves, the patient pays nothing; if they decline, the bill stays
	 * payable. Reuses the md_discount approval gate so no new approval type
	 * is needed, tagged `waiver` so the bill can present it as a waiver
	 * rather than an ordinary discount.
	 *
	 * The logged amount is the outstanding balance (what is being forgiven),
	 * but the discount actually applied must be subtotal − paid_amount so the
	 * bill settles to exactly what has already been received.
	 */
	public function requestWaiver($salesId, $reason){
		$sale = $this->getBill($salesId);
		if(!$sale) return array('ok' => false, 'error' => 'Bill not found');
		if(!$reason) return array('ok' => false, 'error' => 'A reason is required');
		$due = round($sale->grand_total - $sale->paid_amount, 2);
		if($due <= 0) return array('ok' => false, 'error' => 'Nothing is outstanding on this bill');
		$applied = round($sale->subtotal - $sale->paid_amount, 2);
		$logId = $this->al->log(array(
			'action_type'         => 'request',
			'approval_type'       => 'md_discount',
			'requesting_user_id'  => $this->session->userdata('inv_userid'),
			'requesting_user_name'=> $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
			'reason'              => $reason,
			'previous_value'      => json_encode(array('grand_total' => $sale->grand_total, 'discount' => $sale->tot_discount_to_all_amt)),
			'new_value'           => json_encode(array('discount' => $applied, 'waiver' => 1)),
			'amount'              => $due,
			'target_module'       => 'patient_bill',
			'target_id'           => $salesId,
			'target_version'      => $this->billFingerprint($sale),
		));
		return array('ok' => true, 'log_id' => $logId, 'amount' => $due);
	}

	/**
	 * Apply an approved discount. Rejects when:
	 *  - no current approved log matches the bill's live fingerprint
	 *  - the approver is the requester (finance can never self-approve)
	 */
	public function applyApprovedDiscount($salesId, $approverId){
		$storeId = get_current_store_id();
		$this->load->model('Approval_logs_model', 'al');
		$this->db->trans_begin();
		$sale = $this->db->query('SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE', [(int)$salesId, (int)$storeId])->row();
		if(!$sale){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Bill not found'); }

		$fp = $this->billFingerprint($sale);
		$log = $this->al->hasCurrentApproval('md_discount', 'patient_bill', $salesId, $fp);
		if(!$log){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'No current approved discount for this bill — request or re-approve first');
		}
		if((int)$log->requesting_user_id === (int)$approverId){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'The requester cannot approve their own discount');
		}
		$newVal = json_decode($log->new_value, true);
		$discount = (float)($newVal['discount'] ?? 0);
		if($discount <= 0){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Approved log carries no discount'); }

		$newGrand = round($sale->subtotal - $discount, 2);
		$this->db->where('id', $salesId)->update('db_sales', array(
			'discount_to_all_type'    => 'Fixed',
			'discount_to_all_input'   => $discount,
			'tot_discount_to_all_amt' => $discount,
			'grand_total'             => $newGrand,
		));
		// Mark the log applied + supersede any other live requests for the bill.
		$this->db->where('id', $log->id)->update('db_approval_logs', array('status' => 'applied', 'applied_at' => date('Y-m-d H:i:s')));
		$this->al->supersedeTarget('patient_bill', $salesId);
		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($salesId, $sale->customer_id);
		$this->db->trans_commit();
		return array('ok' => true, 'discount' => $discount, 'grand_total' => $newGrand);
	}

	/** Credit exception — bill payment deferred past covered funds. */
	public function requestCreditException($salesId, $reason){
		$sale = $this->getBill($salesId);
		if(!$sale) return array('ok' => false, 'error' => 'Bill not found');
		$due = round($sale->grand_total - $sale->paid_amount, 2);
		$logId = $this->al->log(array(
			'action_type'         => 'request',
			'approval_type'       => 'credit_override',
			'requesting_user_id'  => $this->session->userdata('inv_userid'),
			'requesting_user_name'=> $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
			'reason'              => $reason,
			'amount'              => $due,
			'target_module'       => 'patient_bill',
			'target_id'           => $salesId,
			'target_version'      => $this->billFingerprint($sale),
		));
		return array('ok' => true, 'log_id' => $logId);
	}

	/**
	 * Post a migrated legacy-debt invoice. Approval-gated via the opening-
	 * position flow — this method is only called from approveOpening().
	 * A Final unpaid invoice: the debt appears in receivables, but NO
	 * db_salespayments row is written — historical transactions never
	 * duplicate cash receipts or revenue. Idempotent per item id.
	 */
	public function createLegacyDebtInvoice($patientId, $amount, $posId, $itemId, $note = ''){
		$storeId = get_current_store_id();
		$patient = $this->db->where('id', (int)$patientId)->where('store_id', $storeId)
			->get('db_patients')->row();
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Debt amount must be positive');
		// The invoice is dated at the position's "as of" cutoff, not today —
		// receivables aging buckets migrated debt at its true age and the
		// posting day's sales total is not inflated by legacy revenue.
		$pos = $this->db->where('id', (int)$posId)->where('store_id', $storeId)
			->get('db_opening_positions')->row();
		$asOf = ($pos && !empty($pos->cutoff_date)) ? $pos->cutoff_date : date('Y-m-d');
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOf)) $asOf = date('Y-m-d');

		$ref = 'OPENING-' . (int)$posId . '.' . (int)$itemId;
		$existing = $this->db->where('store_id', $storeId)->where('reference_no', $ref)
			->get('db_sales')->row();
		if($existing) return array('ok' => true, 'sales_id' => $existing->id, 'replayed' => true);

		$item = $this->db->where('store_id', $storeId)->where('item_code', 'LEGACY-DEBT')
			->get('db_items')->row();
		if(!$item) return array('ok' => false, 'error' => 'LEGACY-DEBT service item missing — run migration 4.0.9.74');

		$this->db->query('SELECT 1 FROM db_store WHERE id = ? FOR UPDATE', [(int)$storeId]);
		$initCode = get_only_init_code('sales');
		$countId  = autosynch_sales_code();
		$this->db->insert('db_sales', array(
			'init_code'      => $initCode,
			'count_id'       => $countId,
			'sales_code'     => $initCode . $countId,
			'reference_no'   => $ref,
			'sales_date'     => $asOf,
			// 'Opening' (not 'Final'): every sales/revenue/profit/tax report
			// filters sales_status='Final', so migrated debt is automatically
			// invisible to P&L. Debt surfaces (aging, sales_due, patient
			// statements) include 'Opening' explicitly.
			'sales_status'   => 'Opening',
			'customer_id'    => $patient->customer_id,
			'subtotal'       => $amount,
			'grand_total'    => $amount,
			'paid_amount'    => 0,
			'payment_status' => 'Unpaid',
			'sales_note'     => ('Migrated opening debt as of ' . $asOf . ($note ? ' — ' . $note : ''))
				. ' (opening position #' . (int)$posId . ')',
			'store_id'       => $storeId,
			'warehouse_id'   => function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null,
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'      => $this->input->ip_address(),
			'system_name'    => php_uname(),
			'status'         => 1,
		));
		$salesId = (int)$this->db->insert_id();
		if(!$salesId) return array('ok' => false, 'error' => 'Debt invoice insert failed');
		$this->db->insert('db_salesitems', array(
			'sales_id' => $salesId, 'store_id' => $storeId, 'sales_status' => 'Opening',
			'item_id' => $item->id, 'description' => 'Legacy opening debt (imported)',
			'sales_qty' => 1, 'price_per_unit' => $amount, 'discount_amt' => 0,
			'unit_total_cost' => $amount, 'total_cost' => $amount,
			'purchase_price' => 0, 'status' => 1,
		));
		// Recompute the customer's due so the retail due-list/dashboard show
		// the migrated balance immediately, not only after the first payment.
		$this->load->model('Sales_model', 'sales_m');
		$this->sales_m->update_sales_payment_status_by_sales_id($salesId, (int)$patient->customer_id);
		return array('ok' => true, 'sales_id' => $salesId);
	}

	public function outstandingForPatient($patientId){
		$bills = $this->billsForPatient($patientId);
		$due = 0;
		foreach($bills as $b) $due += max(0, $b->grand_total - $b->paid_amount);
		return round($due, 2);
	}
}
