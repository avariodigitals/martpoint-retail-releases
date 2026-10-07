<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sessions_model — plan-bound session entitlements + session lifecycle.
 *
 * db_plan_entitlements holds the counters (units_total / units_used /
 * funded_amount / consumed_amount) and NEVER mixes across plans: each
 * entitlement is tied to exactly one plan_id.
 *
 * Lifecycle (db_treatment_sessions.status):
 *   scheduled → checked_in → in_progress → completed
 *        │          │            │   └→ interrupted (→ in_progress | completed)
 *        └──────────┴──→ cancelled / no_show (terminal, hold released)
 *
 * Money rules:
 *  - check-in / reprint / no-show / cancel: NO fee, NO consumption.
 *  - completed: exactly once — entitlement units_used += 1,
 *    consumed_amount += fee; reservation-funded sessions additionally post a
 *    wallet `consume` txn keyed 'consume:session:<id>'.
 *  - Revenue was recognised at bill time (docs/physiotherapy_funds_ledger.md §5);
 *    completion is NEVER a revenue event.
 *  - Migrated entitlements (source='migration') consume identically but post
 *    no wallet txn and carry no sale.
 */
class Sessions_model extends CI_Model {

	const TRANSITIONS = array(
		'scheduled'   => array('checked_in','in_progress','cancelled','no_show'),
		'checked_in'  => array('in_progress','cancelled','no_show','scheduled'),
		'in_progress' => array('completed','interrupted','cancelled'),
		'interrupted' => array('in_progress','completed','cancelled'),
		'completed'   => array(),
		'cancelled'   => array(),
		'no_show'     => array(),
	);

	public function __construct(){
		parent::__construct();
	}

	// ------------------------------------------------------------------
	// Entitlements
	// ------------------------------------------------------------------

	public function getEntitlement($id){
		return $this->db->where('id', $id)->where('store_id', get_current_store_id())
			->get('db_plan_entitlements')->row();
	}

	public function entitlementsForPlan($planId){
		return $this->db->where('plan_id', $planId)->where('store_id', get_current_store_id())
			->order_by('id')->get('db_plan_entitlements')->result();
	}

	public function entitlementsForPatient($patientId){
		return $this->db->where('patient_id', $patientId)->where('store_id', get_current_store_id())
			->order_by('id')->get('db_plan_entitlements')->result();
	}

	/**
	 * Create a plan-bound entitlement. $source: purchase|migration|grant.
	 * For migration: funded_amount records the historical value paid —
	 * NO sale, NO wallet txn (no duplicate revenue/credit).
	 */
	public function createEntitlement(array $d){
		$this->db->insert('db_plan_entitlements', array(
			'store_id'       => get_current_store_id(),
			'patient_id'     => $d['patient_id'],
			'plan_id'        => $d['plan_id'] ?? null,
			'sale_id'        => $d['sale_id'] ?? null,
			'service_id'     => $d['service_id'] ?? null,
			'units_total'    => $d['units_total'],
			'units_used'     => 0,
			'funded_amount'  => $d['funded_amount'] ?? 0,
			'consumed_amount'=> 0,
			'source'         => $d['source'] ?? 'purchase',
			'status'         => 'active',
			'expiry_date'    => $d['expiry_date'] ?? null,
			'valuation_note' => $d['valuation_note'] ?? null,
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $d['created_by'] ?? ($this->session->userdata('inv_username') ?: 'system'),
		));
		$id = $this->db->insert_id();
		return $id ? array('ok' => true, 'entitlement_id' => $id) : array('ok' => false, 'error' => 'Entitlement failed');
	}

	/**
	 * Migrated prepaid sessions enter as an OPENING POSITION item — the
	 * entitlement itself is created only when the position passes the
	 * enter → review → approve workflow in Patient_funds_model. There is
	 * deliberately no direct-entitlement path for migration.
	 */
	public function migratePrepaid(array $d){
		$this->load->model('Patient_funds_model', 'pf');
		return $this->pf->enterOpeningItem((int)$d['patient_id'], 'unused_sessions', array(
			'plan_id'        => $d['plan_id'] ?? null,
			'service_id'     => $d['service_id'] ?? null,
			'units_total'    => $d['units_total'],
			'funded_amount'  => $d['funded_amount'] ?? 0,
			'valuation_note' => $d['valuation_note'] ?? 'Migrated prepaid balance',
			'cutoff_date'    => $d['cutoff_date'] ?? null,
			'evidence_document_id' => $d['evidence_document_id'] ?? null,
			'evidence_note'  => $d['evidence_note'] ?? null,
		), (float)($d['funded_amount'] ?? 0));
	}

	// ------------------------------------------------------------------
	// Sessions
	// ------------------------------------------------------------------

	public function getSession($id){
		return $this->db->where('id', $id)->where('store_id', get_current_store_id())
			->get('db_treatment_sessions')->row();
	}

	public function sessionsForPlan($planId){
		return $this->db->where('plan_id', $planId)->where('store_id', get_current_store_id())
			->order_by('scheduled_at,id')->get('db_treatment_sessions')->result();
	}

	public function upcomingForPatient($patientId){
		return $this->db->where('patient_id', $patientId)->where('store_id', get_current_store_id())
			->where_in('status', array('scheduled','checked_in','in_progress','interrupted'))
			->order_by('scheduled_at')->get('db_treatment_sessions')->result();
	}

	/**
	 * Schedule a session against an entitlement. session_no counts scheduled+
	 * later sessions of THIS entitlement only (counters never mix across plans).
	 */
	public function schedule(array $d){
		$storeId = get_current_store_id();
		$ent = $this->getEntitlement($d['entitlement_id']);
		if(!$ent || $ent->status !== 'active'){
			return array('ok' => false, 'error' => 'Entitlement not available');
		}
		// units committed = used + open sessions already scheduled against it
		$open = $this->db->where('entitlement_id', $ent->id)
			->where_in('status', array('scheduled','checked_in','in_progress','interrupted'))
			->count_all_results('db_treatment_sessions');
		$sessionNo = $ent->units_used + $open + 1;
		if($sessionNo > $ent->units_total){
			return array('ok' => false, 'error' => 'No sessions remaining on this entitlement (' . ($ent->units_total - $ent->units_used - $open) . ' left)');
		}
		$this->db->insert('db_treatment_sessions', array(
			'store_id'       => $storeId,
			'plan_id'        => $ent->plan_id,
			'entitlement_id' => $ent->id,
			'patient_id'     => $ent->patient_id,
			'customer_id'    => $d['customer_id'],
			'encounter_id'   => $d['encounter_id'] ?? null,
			'branch_id'      => $d['branch_id'] ?? null,
			'clinician_id'   => $d['clinician_id'] ?? null,
			'scheduled_at'   => $d['scheduled_at'] ?? date('Y-m-d H:i:s'),
			'session_no'     => $sessionNo,
			'units_total'    => $ent->units_total,
			'fee'            => $d['fee'] ?? 0,
			'status'         => 'scheduled',
			'created_date'   => date('Y-m-d'),
			'created_time'   => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
		));
		$id = $this->db->insert_id();
		return $id ? array('ok' => true, 'session_id' => $id, 'session_no' => $sessionNo)
			: array('ok' => false, 'error' => 'Session save failed');
	}

	private function move($id, $to, array $extra = []){
		$s = $this->getSession($id);
		if(!$s) return array('ok' => false, 'error' => 'Session not found');
		if(!in_array($to, self::TRANSITIONS[$s->status] ?? array())){
			return array('ok' => false, 'error' => "Cannot move {$s->status} → {$to}");
		}
		$update = array_merge(array('status' => $to, 'updated_at' => date('Y-m-d H:i:s')), $extra);
		$this->db->where('id', $id)->update('db_treatment_sessions', $update);
		return array('ok' => true, 'session_id' => $id, 'status' => $to);
	}

	/** Idempotent check-in — 'checkin:<session>' key; never posts money. */
	public function checkin($id, $key = null){
		$s = $this->getSession($id);
		if(!$s) return array('ok' => false, 'error' => 'Session not found');
		if($s->status === 'checked_in' && $s->checkin_key === $key){
			return array('ok' => true, 'session_id' => $id, 'already' => true);
		}
		$key = $key ?: ('ck:' . $id . ':' . uniqid());
		// The unique key protects against double check-in — a repeat request
		// with a taken key must replay, not error.
		$existing = $this->db->where('store_id', get_current_store_id())
			->where('checkin_key', $key)->get('db_treatment_sessions')->row();
		if($existing){
			if((int)$existing->id !== (int)$id){
				return array('ok' => false, 'conflict' => true,
					'error' => 'Check-in reference conflict — already used by another session');
			}
			return array('ok' => true, 'session_id' => $existing->id, 'already' => true);
		}
		try {
			return $this->move($id, 'checked_in', array(
				'checkin_key'   => $key,
				'checked_in_at' => date('Y-m-d H:i:s'),
			));
		} catch (Exception $e) {
			// Insert raced another check-in with the same key — return that row.
			$existing = $this->db->where('store_id', get_current_store_id())
				->where('checkin_key', $key)->get('db_treatment_sessions')->row();
			if($existing && (int)$existing->id === (int)$id)
				return array('ok' => true, 'session_id' => $existing->id, 'already' => true);
			if($existing) return array('ok' => false, 'conflict' => true, 'error' => 'Check-in reference conflict');
			return array('ok' => false, 'error' => 'Check-in failed');
		}
	}

	public function start($id){
		return $this->move($id, 'in_progress', array('started_at' => date('Y-m-d H:i:s')));
	}

	/**
	 * Complete — the ONLY financial transition. Exactly once:
	 * entitlement.units_used += 1, consumed_amount += fee, and a wallet
	 * `consume` txn when the entitlement is backed by a fund reservation.
	 * Row locks on entitlement (+reservation) serialise simultaneous calls.
	 */
	public function complete($id){
		$storeId = get_current_store_id();
		$this->load->model('Patient_funds_model', 'pf');

		$this->db->trans_begin();
		$s = $this->db->query(
			'SELECT * FROM db_treatment_sessions WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$id, (int)$storeId]
		)->row();
		if(!$s){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Session not found'); }
		if($s->status === 'completed' && $s->fee_posted){
			$this->db->trans_commit();
			return array('ok' => true, 'session_id' => $id, 'already' => true, 'replayed' => true);
		}
		if($s->reversed){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Session was reversed — schedule a new session instead');
		}
		if(!in_array('completed', self::TRANSITIONS[$s->status] ?? array())){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => "Cannot complete a {$s->status} session");
		}

		$consumeTxnId = null;
		if($s->entitlement_id){
			// Lock entitlement — the units_used guard serialises races.
			$ent = $this->db->query(
				'SELECT * FROM db_plan_entitlements WHERE id = ? FOR UPDATE', [(int)$s->entitlement_id]
			)->row();
			if(!$ent || $ent->status !== 'active' || $ent->units_used >= $ent->units_total){
				$this->db->trans_rollback();
				return array('ok' => false, 'error' => 'No remaining sessions on this entitlement');
			}
			// The receivable this consumption settles: the entitlement's own
			// sale, else the plan's final bill. Locked while we read the due.
			$receivable = null;
			if($ent->sale_id){
				$receivable = $this->db->query(
					'SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE',
					[(int)$ent->sale_id, (int)$storeId])->row();
			} elseif($ent->plan_id){
				$receivable = $this->db->query(
					'SELECT * FROM db_sales WHERE plan_id = ? AND store_id = ? AND sales_status = "Final" ORDER BY id DESC LIMIT 1 FOR UPDATE',
					[(int)$ent->plan_id, (int)$storeId])->row();
			}
			$due = $receivable ? round((float)$receivable->grand_total - (float)$receivable->paid_amount, 2) : null;
			// Reservation-funded? Consume the held money in the same txn.
			$res = $this->db->query(
				'SELECT * FROM db_fund_reservations WHERE entitlement_id = ? AND status = "active" FOR UPDATE',
				[(int)$ent->id]
			)->row();
			$consumeAmt = 0;
			if($res && (float)$s->fee > 0){
				if($due !== null && $due <= 0.001){
					// The linked invoice is already settled (e.g. paid in
					// cash) — consuming reserved funds too would collect the
					// same money twice. The hold stays until released.
					$consumeAmt = 0;
				} else {
					$remaining = $res->amount_reserved - $res->amount_consumed;
					// Consume only what is still owed on the receivable —
					// never more than the fee and never past the hold.
					$consumeAmt = min((float)$s->fee, $remaining, $due !== null ? $due : $remaining);
					if($consumeAmt <= 0 && $s->fee > $remaining + 0.001 && $due === null){
						$this->db->trans_rollback();
						return array('ok' => false, 'error' => 'Insufficient reserved funds (' . $remaining . ' remaining)');
					}
				}
			}
			if($consumeAmt > 0.001){
				$r = $this->pf->postTxn(array(
					'customer_id'    => $res->customer_id, 'patient_id' => $res->patient_id,
					'txn_type'       => 'consume', 'direction' => 'debit', 'amount' => $consumeAmt,
					'operation_key'  => 'consume:session:' . $s->id,
					'reservation_id' => $res->id,
					'sales_id'       => $receivable ? (int)$receivable->id : null,
					'note'           => 'Session ' . $s->id . ' completed — fee applied',
				));
				if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
				$consumeTxnId = $r['txn_id'];
				$consumed = $res->amount_consumed + $consumeAmt;
				$this->db->where('id', $res->id)->update('db_fund_reservations', array(
					'amount_consumed' => $consumed,
					'status'          => ($consumed >= $res->amount_reserved - 0.001) ? 'exhausted' : 'active',
					'updated_at'      => date('Y-m-d H:i:s'),
				));
				// Settle the receivable with the same money — a
				// 'patient_wallet' payment row, NOT a cash receipt.
				if($receivable){
					$this->load->model('Patient_billing_model', 'pb');
					$payId = $this->pb->insertPaymentRow((int)$receivable->id, $consumeAmt,
						'patient_wallet', 'Session ' . $s->id . ' — wallet settlement');
					if(!$payId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Settlement posting failed'); }
					$this->db->where('id', $consumeTxnId)
						->update('db_patient_wallet_txns', array('salespayment_id' => $payId));
					$this->load->model('Sales_model', 'sales_m');
					$this->sales_m->update_sales_payment_status_by_sales_id((int)$receivable->id, (int)$receivable->customer_id);
					// Newly covered units become entitlement capacity — the
					// reservation drawdown grows the package as it settles.
					$this->pb->syncEntitlements((int)$receivable->id);
				}
			}
			// syncEntitlements may have grown units_total since we locked —
			// read the fresh row so 'exhausted' is decided correctly.
			$entNow = $this->db->where('id', $ent->id)->get('db_plan_entitlements')->row();
			$this->db->where('id', $ent->id)->update('db_plan_entitlements', array(
				'units_used'      => $ent->units_used + 1,
				'consumed_amount' => $ent->consumed_amount + $s->fee,
				'status'          => ($ent->units_used + 1 >= $entNow->units_total) ? 'exhausted' : 'active',
			));
		}

		$this->db->where('id', $s->id)->update('db_treatment_sessions', array(
			'status' => 'completed', 'completed_at' => date('Y-m-d H:i:s'),
			'fee_posted' => 1, 'consume_txn_id' => $consumeTxnId,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'session_id' => $id, 'status' => 'completed', 'consume_txn_id' => $consumeTxnId);
	}

	/** Cancel / no-show — release any hold, never a charge. */
	public function cancel($id, $reason, $asNoShow = false){
		$s = $this->getSession($id);
		if(!$s) return array('ok' => false, 'error' => 'Session not found');
		if(!$reason) return array('ok' => false, 'error' => 'A reason is required');
		$to = $asNoShow ? 'no_show' : 'cancelled';
		$r = $this->move($id, $to, array('cancel_reason' => $reason, 'cancelled_by' => $this->session->userdata('inv_userid')));
		if(!$r['ok']) return $r;
		$this->load->model('Patient_funds_model', 'pf');
		// Release the residual hold once the session is dead.
		$res = $this->db->where('entitlement_id', $s->entitlement_id)->where('status', 'active')
			->get('db_fund_reservations')->row();
		if($res){
			$remaining = $res->amount_reserved - $res->amount_consumed;
			if($remaining <= 0.001 || $this->openSessionsOnEntitlement($s->entitlement_id) === 0){
				$this->pf->releaseReservation($res->id, 'Session ' . $to . ': ' . $reason);
			}
		}
		return $r;
	}

	public function openSessionsOnEntitlement($entitlementId){
		return $this->db->where('entitlement_id', $entitlementId)
			->where_in('status', array('scheduled','checked_in','in_progress','interrupted'))
			->count_all_results('db_treatment_sessions');
	}

	public function interrupt($id, $reason){
		if(!$reason) return array('ok' => false, 'error' => 'A reason is required');
		return $this->move($id, 'interrupted', array('notes' => $reason));
	}

	/** Approval fingerprint a wallet_adjustment log binds to for reversal. */
	public function reversalFingerprint($session){
		// VARCHAR(64) target_version — sha256 binding (see billFingerprint).
		return substr(hash('sha256', $session->id . '|' . $session->fee . '|' . $session->consume_txn_id), 0, 40);
	}

	/**
	 * Reverse a completed session — once. Requires an approved
	 * wallet_adjustment approval log (passed as $approvalLog) whenever the
	 * completion moved money, so the acting approver + reason are on record.
	 *
	 * Money semantics:
	 *  - a `reversal` CREDIT always offsets the consume debit;
	 *  - the original reservation hold is RESTORED where it is still live or
	 *    exhausted (status → active, consumed − fee) — restricted funds go
	 *    back under the hold, they do NOT become freely spendable;
	 *  - only where the hold was already released/cancelled does the credit
	 *    land in available funds (the restriction ended with that release);
	 *  - if the consume settled an invoice, a correcting negative
	 *    'wallet_reversal' payment row un-settles it — the receivable's due
	 *    reopens, matching the restored hold.
	 */
	public function reverse($id, $reason, $approvalLog = null){
		$storeId = get_current_store_id();
		if(!$reason) return array('ok' => false, 'error' => 'A reason is required');
		$this->load->model('Patient_funds_model', 'pf');

		$this->db->trans_begin();
		$s = $this->db->query(
			'SELECT * FROM db_treatment_sessions WHERE id = ? AND store_id = ? FOR UPDATE',
			[(int)$id, (int)$storeId]
		)->row();
		if(!$s){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Session not found'); }
		if($s->status !== 'completed' || $s->reversed){
			$this->db->trans_rollback();
			return array('ok' => false, 'error' => 'Only a completed session can be reversed (once)');
		}
		$moneyMoved = (bool)$s->consume_txn_id;
		if($moneyMoved && !$approvalLog){
			$this->db->trans_rollback();
			return array('ok' => false, 'requires_approval' => true,
				'error' => 'Reversing a funded session requires an approved wallet adjustment');
		}

		if($s->entitlement_id){
			$ent = $this->db->query('SELECT * FROM db_plan_entitlements WHERE id = ? FOR UPDATE', [(int)$s->entitlement_id])->row();
			if($ent){
				$this->db->where('id', $ent->id)->update('db_plan_entitlements', array(
					'units_used'      => max(0, $ent->units_used - 1),
					'consumed_amount' => max(0, $ent->consumed_amount - $s->fee),
					'status'          => 'active',
				));
			}
		}
		// Wallet undo: consume txn existed? Offset it and restore the hold.
		if($s->consume_txn_id){
			$txn = $this->db->where('id', $s->consume_txn_id)->get('db_patient_wallet_txns')->row();
			if($txn){
				$undoAmt = (float)$txn->amount;
				$res = null;
				if($txn->reservation_id){
					$res = $this->db->query('SELECT * FROM db_fund_reservations WHERE id = ? FOR UPDATE', [(int)$txn->reservation_id])->row();
				}
				if($res){
					$newConsumed = max(0, $res->amount_consumed - $undoAmt);
					// Restore the ORIGINAL hold: an exhausted reservation
					// re-opens; a released/cancelled one is not resurrected —
					// its funds were already freed by an explicit release.
					$newStatus = in_array($res->status, array('active','exhausted')) ? 'active' : $res->status;
					$this->db->where('id', $res->id)->update('db_fund_reservations', array(
						'amount_consumed' => $newConsumed,
						'status'          => $newStatus,
						'updated_at'      => date('Y-m-d H:i:s'),
					));
				}
				$approver = $approvalLog
					? ' — approved by ' . (($approvalLog->approving_user_name ?: 'user #' . $approvalLog->approving_user_id) . ' (log #' . $approvalLog->id . ')')
					: '';
				$r = $this->pf->postTxn(array(
					'customer_id' => $txn->customer_id, 'patient_id' => $txn->patient_id,
					'txn_type' => 'reversal', 'direction' => 'credit',
					'amount' => $undoAmt,
					'operation_key' => 'reversal:session:' . $s->id,
					'reservation_id' => $txn->reservation_id ?: null,
					'sales_id' => $txn->sales_id ?: null,
					'note' => 'Session ' . $s->id . ' reversed — ' . $reason . $approver,
				));
				if(!$r['ok']){ $this->db->trans_rollback(); return $r; }
				// Un-settle the invoice the consume paid: correcting negative
				// payment row (SUM-based recompute reads it correctly).
				if($txn->salespayment_id){
					$pay = $this->db->where('id', (int)$txn->salespayment_id)
						->where('store_id', $storeId)->get('db_salespayments')->row();
					if($pay){
						$this->load->model('Patient_billing_model', 'pb');
						$negId = $this->pb->insertPaymentRow((int)$pay->sales_id, -round((float)$pay->payment, 2),
							'wallet_reversal',
							'Reversal — session ' . $s->id . ($approvalLog ? ' (approval #' . $approvalLog->id . ')' : ''));
						if(!$negId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Settlement reversal failed'); }
						$this->load->model('Sales_model', 'sales_m');
						$this->sales_m->update_sales_payment_status_by_sales_id((int)$pay->sales_id, (int)$pay->customer_id);
					}
				}
			}
		}
		$this->db->where('id', $s->id)->update('db_treatment_sessions', array(
			'status' => 'cancelled', 'reversed' => 1,
			'cancel_reason' => 'Reversed: ' . $reason,
			'cancelled_by' => $this->session->userdata('inv_userid'),
			'reversed_by'  => (int)$this->session->userdata('inv_userid') ?: null,
			'reversed_at'  => date('Y-m-d H:i:s'),
			'reversal_approval_id' => $approvalLog ? (int)$approvalLog->id : null,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'session_id' => $id, 'reversed' => true);
	}

	// ------------------------------------------------------------------
	// 80mm ticket + statement data
	// ------------------------------------------------------------------

	public function ticketData($sessionId){
		$s = $this->getSession($sessionId);
		if(!$s) return null;
		$patient = $this->db->where('id', $s->patient_id)->get('db_patients')->row();
		$ent = $s->entitlement_id ? $this->db->where('id', $s->entitlement_id)->get('db_plan_entitlements')->row() : null;
		$item = null;
		if($ent && $ent->service_id){
			$item = $this->db->select('item_name')->where('id', $ent->service_id)->get('db_items')->row();
		}
		$paymentStatus = 'prepaid';
		if($ent && $ent->source === 'purchase' && $ent->sale_id){
			$sale = $this->db->select('payment_status')->where('id', $ent->sale_id)->get('db_sales')->row();
			$paymentStatus = $sale ? strtolower($sale->payment_status) : 'paid';
		}
		return array(
			'session' => $s, 'patient' => $patient, 'entitlement' => $ent,
			'treatment' => $item ? $item->item_name : 'Treatment session',
			'payment_status' => $paymentStatus,
		);
	}
}
