<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient_funds_model — the ONLY writer to db_patient_wallet_txns.
 *
 * Implements docs/physiotherapy_funds_ledger.md verbatim:
 *
 *  - db_patient_wallet_txns is the single authoritative clinical-funds ledger.
 *    direction: credit|debit|memo. `reserve`/`release` rows are MEMO audit
 *    records — availability is derived from real credits/debits minus open
 *    reservations, so memo rows can never double-count.
 *  - Balances are always derived, never stored:
 *      available = Σcredit(fund,fund_transfer,adjust+,opening)
 *                − Σdebit(consume,refund,transfer_out,adjust−)
 *                − open reservation outstanding
 *      reserved  = open reservation outstanding
 *      pending   = Σ db_payment_evidence where status='submitted'
 *      consumed  = Σ consume debits (history only)
 *  - db_custadvance stays the retail advance. Money crosses between the two
 *    only via an atomic transfer pair (negative custadvance row ↔ wallet
 *    credit, or wallet debit ↔ positive custadvance row).
 *  - Concurrency: every mutating operation locks the db_customers row
 *    (FOR UPDATE) — the patient's financial mutex — before recomputing
 *    balances inside the transaction. Session consumption additionally
 *    locks the reservation row.
 *  - Idempotency: operation_key is UNIQUE per store; a retry finds the
 *    existing txn and replays the recorded result instead of duplicating.
 */
class Patient_funds_model extends CI_Model {

	const CREDIT_TYPES = array('fund','fund_transfer','opening','adjust','reversal');
	const DEBIT_TYPES  = array('consume','refund','transfer_out','adjust');

	public function __construct(){
		parent::__construct();
	}

	// ------------------------------------------------------------------
	// Balance derivation
	// ------------------------------------------------------------------

	/** Lock the customer's financial row inside an open transaction. */
	public function lockCustomer($customerId){
		return $this->db->query(
			'SELECT id FROM db_customers WHERE id = ? FOR UPDATE', [(int)$customerId]
		)->row();
	}

	public function getPatient($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
	}

	/**
	 * Derived balances. Call inside a transaction (after lockCustomer) for a
	 * consistent read during mutation; standalone reads are fine unlocked.
	 */
	public function getBalances($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return null;

		$credits = $this->db->select('COALESCE(SUM(amount),0) AS s', false)
			->where('store_id', $storeId)->where('customer_id', $patient->customer_id)
			->where('direction', 'credit')
			->where_in('txn_type', array('fund','fund_transfer','opening','adjust','reversal'))
			->get('db_patient_wallet_txns')->row()->s;

		$debits = $this->db->select('COALESCE(SUM(amount),0) AS s', false)
			->where('store_id', $storeId)->where('customer_id', $patient->customer_id)
			->where('direction', 'debit')
			->where_in('txn_type', array('consume','refund','transfer_out','adjust'))
			->get('db_patient_wallet_txns')->row()->s;

		$reserved = $this->db->select('COALESCE(SUM(amount_reserved - amount_consumed),0) AS s', false)
			->where('store_id', $storeId)->where('customer_id', $patient->customer_id)
			->where('status', 'active')
			->get('db_fund_reservations')->row()->s;

		$pending = $this->db->select('COALESCE(SUM(amount),0) AS s', false)
			->where('store_id', $storeId)->where('patient_id', $patientId)
			->where('status', 'submitted')
			->get('db_payment_evidence')->row()->s;

		$consumed = $this->db->select('COALESCE(SUM(amount),0) AS s', false)
			->where('store_id', $storeId)->where('customer_id', $patient->customer_id)
			->where('direction', 'debit')->where('txn_type', 'consume')
			->get('db_patient_wallet_txns')->row()->s;

		$reversed = $this->db->select('COALESCE(SUM(amount),0) AS s', false)
			->where('store_id', $storeId)->where('customer_id', $patient->customer_id)
			->where('direction', 'credit')->where('txn_type', 'reversal')
			->get('db_patient_wallet_txns')->row()->s;

		return array(
			'available' => round($credits - $debits - $reserved, 2),
			'reserved'  => round($reserved, 2),
			'pending'   => round($pending, 2),
			'consumed'  => round($consumed - $reversed, 2),
			'retail_advance' => round(get_customer_tot_advance($patient->customer_id), 2),
		);
	}

	// ------------------------------------------------------------------
	// Ledger write — the only insert path
	// ------------------------------------------------------------------

	/**
	 * Post a wallet txn. Idempotent on operation_key: returns the existing
	 * row when the key already landed AND the request matches its details;
	 * a reused key with different patient/amount/type is a CONFLICT.
	 * Caller must hold a transaction.
	 */
	public function postTxn(array $d){
		$storeId = $d['store_id'] ?? get_current_store_id();
		$opKey   = $d['operation_key'];
		$existing = $this->db->where('store_id', $storeId)->where('operation_key', $opKey)
			->get('db_patient_wallet_txns')->row();
		if($existing){
			if(!$this->_txnMatches($existing, $d)){
				return array('ok' => false, 'conflict' => true,
					'error' => 'Reference conflict — this reference was already used with different details');
			}
			return array('ok' => true, 'txn_id' => $existing->id, 'replayed' => true);
		}
		$this->load->model('Physio_accounts_model','pa');
		$physioAccountId=$this->pa->forCustomer($d['customer_id'],$storeId);
		$this->db->insert('db_patient_wallet_txns', array(
			'physio_account_id'=> $physioAccountId,
			'store_id'         => $storeId,
			'customer_id'      => $d['customer_id'],
			'patient_id'       => $d['patient_id'] ?? null,
			'txn_type'         => $d['txn_type'],
			'direction'        => $d['direction'],
			'amount'           => $d['amount'],
			'operation_key'    => $opKey,
			'reservation_id'   => $d['reservation_id'] ?? null,
			'sales_id'         => $d['sales_id'] ?? null,
			'salespayment_id'  => $d['salespayment_id'] ?? null,
			'custadvance_id'   => $d['custadvance_id'] ?? null,
			'note'             => $d['note'] ?? null,
			'created_date'     => date('Y-m-d'),
			'created_time'     => date('H:i:s'),
			'created_by'       => $d['created_by'] ?? ($this->session->userdata('inv_username') ?: 'system'),
		));
		$id = $this->db->insert_id();
		if(!$id){
			// Lost an insert race on the unique key — the twin row exists.
			$existing = $this->db->where('store_id', $storeId)->where('operation_key', $opKey)
				->get('db_patient_wallet_txns')->row();
			if($existing){
				if(!$this->_txnMatches($existing, $d)){
					return array('ok' => false, 'conflict' => true,
						'error' => 'Reference conflict — this reference was already used with different details');
				}
				return array('ok' => true, 'txn_id' => $existing->id, 'replayed' => true);
			}
			return array('ok' => false, 'error' => 'Wallet posting failed');
		}
		return array('ok' => true, 'txn_id' => $id, 'replayed' => false);
	}

	/** Does a stored txn carry the same facts as this request? */
	private function _txnMatches($row, array $d){
		return (int)$row->customer_id === (int)($d['customer_id'] ?? 0)
			&& (int)$row->patient_id  === (int)($d['patient_id'] ?? 0)
			&& $row->txn_type === ($d['txn_type'] ?? '')
			&& $row->direction === ($d['direction'] ?? '')
			&& round((float)$row->amount, 2) === round((float)($d['amount'] ?? 0), 2);
	}

	/** Evidence-ref conflict/replay shared by fundCash + submitEvidence. */
	private function _evidenceRefResult($storeId, $ref, $patientId, $amount, $channel = null){
		$ev = $this->db->where('store_id', $storeId)->where('payment_ref', $ref)
			->get('db_payment_evidence')->row();
		if(!$ev) return array('ok' => false, 'error' => 'Evidence save failed');
		$match = (int)$ev->patient_id === (int)$patientId
			&& round((float)$ev->amount, 2) === round((float)$amount, 2)
			&& ($channel === null || $ev->channel === $channel);
		if(!$match){
			return array('ok' => false, 'conflict' => true,
				'error' => 'Reference conflict — this reference was already used with different details');
		}
		$txn = $this->db->where('store_id', $storeId)->where('operation_key', 'fund:' . $ref)
			->get('db_patient_wallet_txns')->row();
		return array('ok' => true, 'replayed' => true, 'evidence_id' => $ev->id,
			'txn_id' => $txn ? $txn->id : null, 'payment_ref' => $ref);
	}

	// ------------------------------------------------------------------
	// Funding
	// ------------------------------------------------------------------

	/**
	 * Submit payment evidence (transfer/cheque/online) — pending, not spendable.
	 */
	public function submitEvidence($patientId, array $d){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$ref = trim($d['payment_ref'] ?? '');
		if($ref === '') $ref = 'EV-' . strtoupper(bin2hex(random_bytes(4)));
		try {
			$this->db->insert('db_payment_evidence', array(
				'store_id'     => $storeId,
				'patient_id'   => $patientId,
				'customer_id'  => $patient->customer_id,
				'sale_id'      => $d['sale_id'] ?? null,
				'plan_id'      => $d['plan_id'] ?? null,
				'amount'       => $d['amount'],
				'channel'      => $d['channel'] ?? 'transfer',
				'payer_name'   => $d['payer_name'] ?? null,
				'payment_ref'  => $ref,
				'document_id'  => $d['document_id'] ?? null,
				'status'       => 'submitted',
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s'),
				'created_by'   => $this->session->userdata('inv_username') ?: 'system',
			));
			$id = $this->db->insert_id();
		} catch (Exception $e) {
			$id = 0;
		}
		if(!$id){
			$err = $this->db->error();
			if(($err['code'] ?? 0) == 1062){
				// Same ref: identical details → replay; different → conflict.
				return $this->_evidenceRefResult($storeId, $ref, $patientId, (float)$d['amount'],
					$d['channel'] ?? 'transfer');
			}
			return array('ok' => false, 'error' => 'Evidence save failed');
		}
		return array('ok' => true, 'evidence_id' => $id, 'payment_ref' => $ref);
	}

	/**
	 * Verify submitted evidence → posts the wallet `fund` credit atomically.
	 * Retry-safe: verifying an already-verified row replays the txn lookup.
	 */
	public function verifyEvidence($evidenceId, $verifierId){
		$storeId = get_current_store_id();
		$this->db->trans_begin();
		$ev = $this->db->query(
			'SELECT * FROM db_payment_evidence WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$evidenceId, (int)$storeId]
		)->row();
		if(!$ev){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Evidence not found'); }
		if($ev->status === 'verified'){
			$this->db->trans_commit();
			return array('ok' => true, 'evidence_id' => $ev->id, 'replayed' => true);
		}
		if($ev->status !== 'submitted'){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Evidence is ' . $ev->status);
		}
		$this->lockCustomer($ev->customer_id);
		$this->db->where('id', $ev->id)->update('db_payment_evidence', array(
			'status' => 'verified', 'verified_by' => $verifierId, 'verified_at' => date('Y-m-d H:i:s'),
		));
		$r = $this->postTxn(array(
			'customer_id'    => $ev->customer_id,
			'patient_id'     => $ev->patient_id,
			'txn_type'       => 'fund',
			'direction'      => 'credit',
			'amount'         => $ev->amount,
			'operation_key'  => 'verify:' . $ev->id,
			'sales_id'       => $ev->sale_id,
			'note'           => 'Verified payment ' . $ev->payment_ref . ' (' . $ev->channel . ')',
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$payId = null;
		// Bill-linked evidence settles the sale in the same transaction:
		// fund lands then is immediately consumed — the statement shows the
		// full in/out trail and the wallet net is zero.
		if($ev->sale_id){
			$sale = $this->db->where('id', $ev->sale_id)->where('store_id', $storeId)->get('db_sales')->row();
			if($sale){
				$due = round($sale->grand_total - $sale->paid_amount, 2);
				$settle = min($ev->amount, $due);
				if($settle > 0){
					$this->load->model('Patient_billing_model', 'pb');
					$payId = $this->pb->insertPaymentRow($ev->sale_id, $settle, 'patient_wallet',
						'Verified ' . $ev->channel . ' ' . $ev->payment_ref);
					if(!$payId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Payment posting failed'); }
					$c = $this->postTxn(array(
						'customer_id' => $ev->customer_id, 'patient_id' => $ev->patient_id,
						'txn_type' => 'consume', 'direction' => 'debit', 'amount' => $settle,
						'operation_key' => 'settle:' . $ev->id,
						'sales_id' => $ev->sale_id, 'salespayment_id' => $payId,
						'note' => 'Settled bill #' . $sale->sales_code,
					));
					if(!$c['ok']){ $this->db->trans_rollback(); return $c; }
				}
				$this->load->model('Sales_model', 'sales_m');
				$this->sales_m->update_sales_payment_status_by_sales_id($ev->sale_id, $ev->customer_id);
			}
		}
		$this->db->trans_commit();
		return array('ok' => true, 'evidence_id' => $ev->id, 'txn_id' => $r['txn_id'], 'payment_id' => $payId, 'replayed' => $r['replayed']);
	}

	public function rejectEvidence($evidenceId, $reason, $verifierId){
		$storeId = get_current_store_id();
		$this->db->trans_begin();
		$ev = $this->db->query(
			'SELECT * FROM db_payment_evidence WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$evidenceId, (int)$storeId]
		)->row();
		if(!$ev || $ev->status !== 'submitted'){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Evidence not pending');
		}
		$this->db->where('id', $ev->id)->update('db_payment_evidence', array(
			'status' => 'rejected', 'reject_reason' => $reason,
			'verified_by' => $verifierId, 'verified_at' => date('Y-m-d H:i:s'),
		));
		$this->db->trans_commit();
		return array('ok' => true);
	}

	/** Immediate verified funding — cash at the desk. */
	public function fundCash($patientId, $amount, $ref, $byUser){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');

		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		// Evidence tail for audit — verified at receipt.
		// mysqli raises on duplicate key; a repeat ref replays the ledger txn.
		try {
			$this->db->insert('db_payment_evidence', array(
				'store_id' => $storeId, 'patient_id' => $patientId,
				'customer_id' => $patient->customer_id, 'amount' => $amount,
				'channel' => 'cash', 'payment_ref' => $ref,
				'status' => 'verified', 'verified_by' => $byUser, 'verified_at' => date('Y-m-d H:i:s'),
				'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
				'created_by' => $this->session->userdata('inv_username') ?: 'system',
			));
			$evId = $this->db->insert_id();
		} catch (Exception $e) {
			$evId = 0;
		}
		if(!$evId){
			$err = $this->db->error();
			$this->db->trans_rollback();
			if(($err['code'] ?? 0) == 1062){
				// Same ref: identical details → replay; different → conflict.
				return $this->_evidenceRefResult($storeId, $ref, $patientId, $amount, 'cash');
			}
			return array('ok' => false, 'error' => 'Evidence save failed');
		}
		$r = $this->postTxn(array(
			'customer_id'   => $patient->customer_id,
			'patient_id'    => $patientId,
			'txn_type'      => 'fund', 'direction' => 'credit', 'amount' => $amount,
			'operation_key' => 'fund:' . $ref,
			'note'          => 'Cash funding ' . $ref,
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$this->db->trans_commit();
		return array('ok' => true, 'evidence_id' => $evId, 'txn_id' => $r['txn_id'], 'replayed' => $r['replayed']);
	}

	// ------------------------------------------------------------------
	// Custadvance ⇄ wallet transfers — the only bridge between the two
	// balances; both legs land or neither does.
	// ------------------------------------------------------------------

	public function transferFromAdvance($patientId, $amount, $note = ''){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');

		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		$advance = get_customer_tot_advance($patient->customer_id);
		if($advance < $amount){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient store credit (available ' . $advance . ')');
		}
		// Leg 1: negative custadvance removes the money from retail credit.
		$caCount = get_count_id('db_custadvance', $storeId);
		$this->db->insert('db_custadvance', array(
			'store_id'      => $storeId,
			'count_id'      => $caCount,
			'payment_code'  => get_init_code('custadvance') . $caCount,
			'payment_date'  => date('Y-m-d'),
			'customer_id'   => $patient->customer_id,
			'amount'        => -$amount,
			'payment_type'  => 'transfer_to_patient_wallet',
			'note'          => 'Moved to patient care funds' . ($note ? ' — ' . $note : ''),
			'created_by'    => $this->session->userdata('inv_username') ?: 'system',
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'status'        => 1,
		));
		$caId = $this->db->insert_id();
		if(!$caId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Transfer leg failed'); }
		if(!set_customer_tot_advance($patient->customer_id)){
			$this->db->trans_rollback(); return array('ok' => false, 'error' => 'Advance recompute failed');
		}
		// Leg 2: wallet credit pointing at the leg that removed the money.
		$r = $this->postTxn(array(
			'customer_id'    => $patient->customer_id,
			'patient_id'     => $patientId,
			'txn_type'       => 'fund_transfer', 'direction' => 'credit',
			'amount'         => $amount,
			'operation_key'  => 'adv2wallet:' . $caId,
			'custadvance_id' => $caId,
			'note'           => 'Transfer from store credit',
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$this->db->trans_commit();
		return array('ok' => true, 'custadvance_id' => $caId, 'txn_id' => $r['txn_id']);
	}

	public function transferToAdvance($patientId, $amount, $operationKey, $note = ''){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');

		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		$bal = $this->getBalances($patientId, $storeId);
		if($bal['available'] < $amount){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient patient funds (available ' . $bal['available'] . ')');
		}
		$r = $this->postTxn(array(
			'customer_id'   => $patient->customer_id,
			'patient_id'    => $patientId,
			'txn_type'      => 'transfer_out', 'direction' => 'debit',
			'amount'        => $amount,
			'operation_key' => 'wallet2adv:' . $operationKey,
			'note'          => 'Transfer to store credit' . ($note ? ' — ' . $note : ''),
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$txnId = $r['txn_id'];
		// Leg 2: positive custadvance restores retail credit.
		$caCount = get_count_id('db_custadvance', $storeId);
		$this->db->insert('db_custadvance', array(
			'store_id'      => $storeId,
			'count_id'      => $caCount,
			'payment_code'  => get_init_code('custadvance') . $caCount,
			'payment_date'  => date('Y-m-d'),
			'customer_id'   => $patient->customer_id,
			'amount'        => $amount,
			'payment_type'  => 'transfer_from_patient_wallet',
			'note'          => 'From patient care funds — wallet txn ' . $txnId,
			'created_by'    => $this->session->userdata('inv_username') ?: 'system',
			'created_date'  => date('Y-m-d'),
			'created_time'  => date('H:i:s'),
			'status'        => 1,
		));
		$caId = $this->db->insert_id();
		if(!$caId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Transfer leg failed'); }
		$this->db->where('id', $txnId)->update('db_patient_wallet_txns', array('custadvance_id' => $caId));
		if(!set_customer_tot_advance($patient->customer_id)){
			$this->db->trans_rollback(); return array('ok' => false, 'error' => 'Advance recompute failed');
		}
		$this->db->trans_commit();
		return array('ok' => true, 'custadvance_id' => $caId, 'txn_id' => $txnId);
	}

	// ------------------------------------------------------------------
	// Reservations
	// ------------------------------------------------------------------

	/**
	 * Hold available funds against a plan/entitlement. Available recomputes
	 * under the customer lock — two simultaneous reservations cannot both
	 * spend the same money.
	 */
	public function reserve($patientId, $amount, $planId = null, $entitlementId = null, $note = ''){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');

		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		$bal = $this->getBalances($patientId, $storeId);
		if($bal['available'] < $amount){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient funds — available ' . $bal['available']);
		}
		$this->db->insert('db_fund_reservations', array(
			'store_id' => $storeId, 'customer_id' => $patient->customer_id,
			'patient_id' => $patientId, 'plan_id' => $planId,
			'entitlement_id' => $entitlementId,
			'amount_reserved' => $amount, 'status' => 'active',
			'source' => 'wallet',
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username') ?: 'system',
		));
		$resId = $this->db->insert_id();
		if(!$resId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Reservation failed'); }
		// Memo audit row — does NOT affect balances (reservation row does).
		$this->postTxn(array(
			'customer_id' => $patient->customer_id, 'patient_id' => $patientId,
			'txn_type' => 'reserve', 'direction' => 'memo', 'amount' => $amount,
			'operation_key' => 'reserve:' . $resId, 'reservation_id' => $resId,
			'note' => 'Funds held' . ($note ? ' — ' . $note : ''),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'reservation_id' => $resId);
	}

	/** Release an active reservation back to available (cancel/no-show). */
	public function releaseReservation($reservationId, $reason = ''){
		$storeId = get_current_store_id();
		$this->db->trans_begin();
		$res = $this->db->query(
			'SELECT * FROM db_fund_reservations WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$reservationId, (int)$storeId]
		)->row();
		if(!$res){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Reservation not found'); }
		if($res->status !== 'active'){
			$this->db->trans_commit();
			return array('ok' => true, 'replayed' => true, 'reservation_id' => $res->id);
		}
		$this->lockCustomer($res->customer_id);
		$this->db->where('id', $res->id)->update('db_fund_reservations', array(
			'status' => 'released', 'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->postTxn(array(
			'customer_id' => $res->customer_id, 'patient_id' => $res->patient_id,
			'txn_type' => 'release', 'direction' => 'memo',
			'amount' => $res->amount_reserved - $res->amount_consumed,
			'operation_key' => 'release:' . $res->id, 'reservation_id' => $res->id,
			'note' => 'Hold released' . ($reason ? ' — ' . $reason : ''),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'reservation_id' => $res->id);
	}

	/**
	 * Spend from an open reservation — the completion-path debit.
	 * Locking the reservation row serialises simultaneous consumers; the
	 * balance re-check happens inside the transaction.
	 */
	public function consume($reservationId, $amount, $operationKey, array $links = []){
		$storeId = get_current_store_id();
		$this->db->trans_begin();
		$res = $this->db->query(
			'SELECT * FROM db_fund_reservations WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$reservationId, (int)$storeId]
		)->row();
		if(!$res || $res->status !== 'active'){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'No active reservation to consume');
		}
		$amount = round((float)$amount, 2);
		$remaining = $res->amount_reserved - $res->amount_consumed;
		if($amount > $remaining + 0.001){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient reserved funds (' . $remaining . ' remaining)');
		}
		$r = $this->postTxn(array(
			'customer_id'      => $res->customer_id,
			'patient_id'       => $res->patient_id,
			'txn_type'         => 'consume', 'direction' => 'debit', 'amount' => $amount,
			'operation_key'    => $operationKey,
			'reservation_id'   => $res->id,
			'sales_id'         => $links['sales_id'] ?? null,
			'salespayment_id'  => $links['salespayment_id'] ?? null,
			'note'             => $links['note'] ?? 'Funds consumed',
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$consumed = $res->amount_consumed + $amount;
		$this->db->where('id', $res->id)->update('db_fund_reservations', array(
			'amount_consumed' => $consumed,
			'status'          => ($consumed >= $res->amount_reserved - 0.001) ? 'exhausted' : 'active',
			'updated_at'      => date('Y-m-d H:i:s'),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'txn_id' => $r['txn_id'], 'replayed' => $r['replayed']);
	}

	/**
	 * Direct wallet spend without a reservation — bill payment from wallet.
	 * Locks the customer row so concurrent payments cannot overspend.
	 */
	public function spend($patientId, $amount, $operationKey, array $links = []){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$amount = round((float)$amount, 2);
		if($amount <= 0) return array('ok' => false, 'error' => 'Amount must be positive');

		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		$bal = $this->getBalances($patientId, $storeId);
		if($bal['available'] < $amount){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient funds — available ' . $bal['available'], 'insufficient' => true);
		}
		$r = $this->postTxn(array(
			'customer_id'      => $patient->customer_id,
			'patient_id'       => $patientId,
			'txn_type'         => 'consume', 'direction' => 'debit', 'amount' => $amount,
			'operation_key'    => $operationKey,
			'sales_id'         => $links['sales_id'] ?? null,
			'salespayment_id'  => $links['salespayment_id'] ?? null,
			'note'             => $links['note'] ?? 'Wallet spend',
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$this->db->trans_commit();
		return array('ok' => true, 'txn_id' => $r['txn_id'], 'replayed' => $r['replayed']);
	}

	/** Money leaves the till — approval-gated at the controller. */
	public function refund($patientId, $amount, $reason, $operationKey){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$this->db->trans_begin();
		$this->lockCustomer($patient->customer_id);
		$bal = $this->getBalances($patientId, $storeId);
		if($bal['available'] < $amount){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Insufficient funds — available ' . $bal['available']);
		}
		$r = $this->postTxn(array(
			'customer_id' => $patient->customer_id, 'patient_id' => $patientId,
			'txn_type' => 'refund', 'direction' => 'debit', 'amount' => $amount,
			'operation_key' => 'refund:' . $operationKey,
			'note' => 'Refund — ' . $reason,
		));
		if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
		$this->db->trans_commit();
		return array('ok' => true, 'txn_id' => $r['txn_id']);
	}

	// ------------------------------------------------------------------
	// Opening positions — the ONLY gate for migrated balances.
	// enter → review → approve; nothing posts to the wallet or to
	// entitlements before approval. The approver may never be the enterer.
	// ------------------------------------------------------------------

	public function openingPosition($id){
		return $this->db->where('id', (int)$id)->where('store_id', get_current_store_id())
			->get('db_opening_positions')->row();
	}

	public function openingItems($positionId){
		return $this->db->where('position_id', (int)$positionId)
			->where('store_id', get_current_store_id())
			->order_by('id')->get('db_opening_position_items')->result();
	}

	/** Opening positions still awaiting review or approval (count only). */
	public function openingPositionsPendingCount(){
		if(!$this->db->table_exists('db_opening_positions')) return 0;
		return (int)$this->db->where('store_id', get_current_store_id())
			->where_in('status', array('entered', 'reviewed'))
			->count_all_results('db_opening_positions');
	}

	public function openingPositions(){
		$storeId = get_current_store_id();
		return $this->db->select('o.*, p.patient_code, c.customer_name,
				e.username AS entered_by_name, r.username AS reviewed_by_name, a.username AS approved_by_name')
			->from('db_opening_positions o')
			->join('db_patients p', 'p.id = o.patient_id AND p.store_id = o.store_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = o.store_id', 'left')
			->join('db_users e', 'e.id = o.entered_by', 'left')
			->join('db_users r', 'r.id = o.reviewed_by', 'left')
			->join('db_users a', 'a.id = o.approved_by', 'left')
			->where('o.store_id', $storeId)
			->order_by('o.id', 'desc')->limit(200)->get()->result();
	}

	/**
	 * Enter (or attach to) a patient's opening position. Items land as
	 * 'entered'; nothing posts. If the position was already under review,
	 * new items return it to 'entered' — it must be reviewed again.
	 */
	public function enterOpeningItem($patientId, $itemType, array $payload, $amount = null, $note = ''){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array('ok' => false, 'error' => 'Patient not found');
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$uname = $this->session->userdata('inv_username') ?: 'system';

		$this->db->trans_begin();
		$pos = $this->db->query(
			'SELECT * FROM db_opening_positions WHERE store_id = ? AND patient_id = ? FOR UPDATE',
			[(int)$storeId, (int)$patientId])->row();
		if($pos && in_array($pos->status, array('approved','rejected'))){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Opening position is already ' . $pos->status);
		}
		$cutoff = !empty($payload['cutoff_date']) ? substr($payload['cutoff_date'], 0, 10) : date('Y-m-d');
		if(!$pos){
			$this->db->insert('db_opening_positions', array(
				'store_id' => $storeId, 'patient_id' => $patientId,
				'cutoff_date' => $cutoff, 'status' => 'entered',
				'entered_by' => $uid, 'entered_at' => date('Y-m-d H:i:s'),
				'notes' => $note ?: null,
				'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
				'created_by' => $uname,
			));
			$posId = (int)$this->db->insert_id();
		} else {
			$posId = (int)$pos->id;
			$this->db->where('id', $posId)->update('db_opening_positions', array(
				'status' => 'entered', 'entered_by' => $uid, 'entered_at' => date('Y-m-d H:i:s'),
				'cutoff_date' => $cutoff,
			));
		}
		if(!$posId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Opening position failed'); }

		$this->db->insert('db_opening_position_items', array(
			'store_id' => $storeId, 'position_id' => $posId,
			'item_type' => $itemType, 'payload_json' => json_encode($payload),
			'amount' => $amount, 'status' => 'entered',
			'evidence_document_id' => (int)($payload['evidence_document_id'] ?? 0) ?: null,
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => $uname,
		));
		$itemId = (int)$this->db->insert_id();
		if(!$itemId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Opening item failed'); }
		$this->db->trans_commit();
		return array('ok' => true, 'position_id' => $posId, 'item_id' => $itemId, 'status' => 'entered');
	}

	/** Reviewer marks items reviewed. Reviewer ≠ enterer. */
	public function reviewOpening($posId){
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$pos = $this->openingPosition($posId);
		if(!$pos) return array('ok' => false, 'error' => 'Opening position not found');
		if($pos->status !== 'entered') return array('ok' => false, 'error' => 'Position is ' . $pos->status . ', not awaiting review');
		if((int)$pos->entered_by === $uid){
			return array('ok' => false, 'error' => 'The enterer cannot review their own opening position');
		}
		$this->db->trans_begin();
		$this->db->where('id', $pos->id)->update('db_opening_positions', array(
			'status' => 'reviewed', 'reviewed_by' => $uid, 'reviewed_at' => date('Y-m-d H:i:s'),
		));
		$this->db->where('position_id', $pos->id)->where('status', 'entered')
			->update('db_opening_position_items', array(
				'status' => 'reviewed', 'reviewed_by' => $uid, 'reviewed_at' => date('Y-m-d H:i:s'),
			));
		$this->db->trans_commit();
		return array('ok' => true, 'position_id' => $pos->id, 'status' => 'reviewed');
	}

	public function rejectOpening($posId, $reason){
		$reason = trim((string)$reason);
		if($reason === '') return array('ok' => false, 'error' => 'A reject reason is required');
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$pos = $this->openingPosition($posId);
		if(!$pos) return array('ok' => false, 'error' => 'Opening position not found');
		if(in_array($pos->status, array('approved','rejected'))){
			return array('ok' => false, 'error' => 'Position is already ' . $pos->status);
		}
		if((int)$pos->entered_by === $uid){
			return array('ok' => false, 'error' => 'The enterer cannot reject their own opening position');
		}
		$this->db->trans_begin();
		$this->db->where('id', $pos->id)->update('db_opening_positions', array(
			'status' => 'rejected', 'reject_reason' => substr($reason, 0, 255),
			'approved_by' => $uid, 'approved_at' => date('Y-m-d H:i:s'),
		));
		$this->db->where('position_id', $pos->id)->update('db_opening_position_items',
			array('status' => 'rejected', 'reviewed_by' => $uid, 'reviewed_at' => date('Y-m-d H:i:s')));
		$this->db->trans_commit();
		return array('ok' => true);
	}

	/**
	 * Approve a reviewed position — the ONLY posting path for migrated
	 * balances. Approver ≠ enterer. Per item:
	 *   unused_sessions → db_plan_entitlements (source 'migration')
	 *   unused_money / wallet_balance → wallet 'opening' credit
	 *   outstanding_debt → Final UNPAID invoice (debt visible; no payment
	 *     row — historical cash/revenue is never duplicated)
	 *   anything else → not supported in this stage (blocks approval).
	 */
	public function approveOpening($posId){
		$storeId = get_current_store_id();
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$this->db->trans_begin();
		$pos = $this->db->query(
			'SELECT * FROM db_opening_positions WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$posId, (int)$storeId])->row();
		if(!$pos){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Opening position not found'); }
		if($pos->status === 'approved'){
			$this->db->trans_commit();
			return array('ok' => true, 'position_id' => $pos->id, 'replayed' => true);
		}
		if($pos->status !== 'reviewed'){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Position must be reviewed before approval (currently ' . $pos->status . ')');
		}
		if((int)$pos->entered_by === $uid){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'The enterer cannot approve their own opening position');
		}
		$items = $this->db->where('position_id', $pos->id)->where('store_id', $storeId)
			->where('status', 'reviewed')->get('db_opening_position_items')->result();
		if(empty($items)){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'No reviewed items to post'); }
		$unsupported = array_diff(array_unique(array_column($items, 'item_type')),
			array('unused_sessions','unused_money','wallet_balance','outstanding_debt'));
		if($unsupported){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Posting not yet supported for item type: ' . implode(', ', $unsupported));
		}
		$patient = $this->getPatient($pos->patient_id, $storeId);
		$this->lockCustomer($patient->customer_id);
		$this->load->model('Sessions_model', 'sm');
		$firstRef = null;
		foreach($items as $it){
			$p = json_decode($it->payload_json, true) ?: array();
			if($it->item_type === 'unused_sessions'){
				$e = $this->sm->createEntitlement(array(
					'patient_id'    => $pos->patient_id,
					'plan_id'       => $p['plan_id'] ?? null,
					'service_id'    => $p['service_id'] ?? null,
					'units_total'   => $p['units_total'] ?? 0,
					'funded_amount' => $p['funded_amount'] ?? ($it->amount ?: 0),
					'source'        => 'migration',
					'valuation_note'=> ($p['valuation_note'] ?? 'Migrated prepaid balance')
						. ' — opening position #' . $pos->id . ', approved user #' . $uid,
					'created_by'    => 'opening-position',
				));
				if(!$e['ok']){ $this->db->trans_rollback(); return $e; }
				$p['entitlement_id'] = $e['entitlement_id'];
				$firstRef = $firstRef ?: $e['entitlement_id'];
			} elseif($it->item_type === 'outstanding_debt'){
				$amt = round((float)$it->amount, 2);
				$this->load->model('Patient_billing_model', 'pbm');
				$d = $this->pbm->createLegacyDebtInvoice($pos->patient_id, $amt, $pos->id, $it->id,
					substr((string)($p['evidence_note'] ?? $p['note'] ?? ''), 0, 160));
				if(!$d['ok']){ $this->db->trans_rollback(); return $d; }
				$p['debt_sales_id'] = $d['sales_id'];
				$firstRef = $firstRef ?: $d['sales_id'];
			} else {
				$amt = round((float)($it->amount ?? ($p['amount'] ?? 0)), 2);
				if($amt <= 0){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Opening item has no amount'); }
				$r = $this->postTxn(array(
					'customer_id' => $patient->customer_id, 'patient_id' => $pos->patient_id,
					'txn_type' => 'opening', 'direction' => 'credit', 'amount' => $amt,
					'operation_key' => 'opening:item:' . $it->id,
					'note' => 'Opening balance — position #' . $pos->id . ', approved user #' . $uid,
				));
				if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
				$p['wallet_txn_id'] = $r['txn_id'];
				$firstRef = $firstRef ?: $r['txn_id'];
			}
			$this->db->where('id', $it->id)->update('db_opening_position_items', array(
				'status' => 'approved', 'payload_json' => json_encode($p),
			));
		}
		$this->db->where('id', $pos->id)->update('db_opening_positions', array(
			'status' => 'approved', 'approved_by' => $uid, 'approved_at' => date('Y-m-d H:i:s'),
			'ref_id' => $firstRef,
		));
		$this->db->trans_commit();
		return array('ok' => true, 'position_id' => $pos->id);
	}

	// ------------------------------------------------------------------
	// Statement
	// ------------------------------------------------------------------

	public function ledgerForPatient($patientId, $limit = 200){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array();
		return $this->db->where('store_id', $storeId)
			->where('customer_id', $patient->customer_id)
			->order_by('id', 'desc')->limit($limit)
			->get('db_patient_wallet_txns')->result();
	}

	public function reservationsForPatient($patientId){
		$storeId = get_current_store_id();
		$patient = $this->getPatient($patientId, $storeId);
		if(!$patient) return array();
		return $this->db->where('store_id', $storeId)
			->where('customer_id', $patient->customer_id)
			->order_by('id', 'desc')->get('db_fund_reservations')->result();
	}

	public function evidenceForPatient($patientId){
		$storeId = get_current_store_id();
		return $this->db->where('store_id', $storeId)->where('patient_id', $patientId)
			->order_by('id', 'desc')->get('db_payment_evidence')->result();
	}
}
