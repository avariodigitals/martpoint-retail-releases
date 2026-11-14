<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment reconciliation — exception queue + on-demand scan.
 *
 * Sources of exceptions:
 *  - webhooks (Paystack/Monnify) that report money but match no local record → unmatched
 *  - provider amounts that disagree with the local sale/payment       → discrepancy
 *  - sales left partially paid                                        → partial
 *  - recorded sales-payment refunds                                 → refund
 *  - user-flagged                                                   → dispute
 *
 * Idempotent by dedupe_key: rescans and replayed webhooks cannot stack
 * duplicates; ON DUPLICATE refreshes amounts only while still 'open'.
 *
 * provider_owner='merchant' rows come from the store's own gateway keys.
 * Platform-owned flows (subscription/fleet billing) are never scanned here.
 */
class Payment_reconcile_model extends CI_Model {

	const T_UNMATCHED   = 'unmatched';
	const T_DISCREPANCY = 'discrepancy';
	const T_PARTIAL     = 'partial';
	const T_REFUND      = 'refund';
	const T_DISPUTE     = 'dispute';
	// Verified money received against an order that is already cancelled,
	// released or refunded. Acknowledging this does NOT settle it: the cash
	// still has to be refunded or the order reinstated.
	const T_LATE_PAYMENT = 'late_payment';
	// Bookkeeping anomalies (amount mismatch, partial settlement) can be
	// closed by acknowledgement; money-received exceptions cannot.
	const TYPES_MONEY_RECEIVED = array('unmatched', 'late_payment');

	/**
	 * Queue an exception idempotently. Returns the row id (existing or new),
	 * or FALSE on failure. $data keys: exception_type, provider, reference,
	 * sales_id, order_id, salespayment_id, amount, expected_amount, currency,
	 * detail, detected_by, payload, provider_owner.
	 */
	public function queue($store_id, $data){
		$store_id = (int)$store_id;
		$type     = $data['exception_type'] ?? self::T_UNMATCHED;
		$provider = $data['provider'] ?? 'manual';
		$ref      = (string)($data['reference'] ?? '');
		$sid      = isset($data['sales_id']) ? (int)$data['sales_id'] : 0;
		$oid      = isset($data['order_id']) ? (int)$data['order_id'] : 0;
		$dedupe   = md5($type.'|'.$provider.'|'.$ref.'|'.$sid.'|'.$oid);

		$row = array(
			'store_id'        => $store_id,
			'exception_type'  => $type,
			'provider'        => $provider,
			'provider_owner'  => $data['provider_owner'] ?? 'merchant',
			'reference'       => $ref,
			'sales_id'        => $sid ?: null,
			'order_id'        => $oid ?: null,
			'salespayment_id' => isset($data['salespayment_id']) ? (int)$data['salespayment_id'] : null,
			'amount'          => isset($data['amount']) ? (float)$data['amount'] : null,
			'expected_amount' => isset($data['expected_amount']) ? (float)$data['expected_amount'] : null,
			'currency'        => $data['currency'] ?? null,
			'status'          => 'open',
			'detail'          => $data['detail'] ?? null,
			'detected_by'     => $data['detected_by'] ?? 'scan',
			'payload'         => isset($data['payload']) ? $this->_clip_payload($data['payload']) : null,
			'dedupe_key'      => $dedupe,
			'created_date'    => date('Y-m-d H:i:s'),
			'created_by'      => $data['created_by'] ?? 'system',
		);

		// INSERT IGNORE keeps the first sighting; refresh mutable amounts
		// on re-detect while the exception is still open.
		$this->db->query(
			"INSERT IGNORE INTO db_payment_exceptions (".implode(',', array_keys($row)).")
			 VALUES (".implode(',', array_fill(0, count($row), '?')).")",
			array_values($row)
		);
		if($this->db->affected_rows() === 0){
			// already queued — keep the snapshot fresh while open
			$this->db->where('store_id', $store_id)->where('dedupe_key', $dedupe)->where('status', 'open')
				->update('db_payment_exceptions', array(
					'amount'          => $row['amount'],
					'expected_amount' => $row['expected_amount'],
					'detail'          => $row['detail'],
				));
			$existing = $this->db->where('store_id', $store_id)->where('dedupe_key', $dedupe)->get('db_payment_exceptions')->row();
			return $existing ? (int)$existing->id : true;
		}
		return (int)$this->db->insert_id();
	}

	/**
	 * Queue a late_payment exception: a verified provider payment arrived for
	 * an order that is no longer payable (cancelled, released, or refunded).
	 * The callback itself stays idempotent and does not resurrect the order —
	 * but the money is real, so it must surface in the merchant queue.
	 */
	public function queue_late_payment($store_id, array $data){
		$data['exception_type'] = self::T_LATE_PAYMENT;
		$data['detected_by']    = $data['detected_by'] ?? 'webhook';
		return $this->queue($store_id, $data);
	}

	private function _clip_payload($payload){
		if(is_array($payload) || is_object($payload)){ $payload = json_encode($payload); }
		return mb_substr((string)$payload, 0, 16000);
	}

	/**
	 * Resolve an exception: acknowledge | resolve | dismiss | dispute.
	 *
	 * Money-received exceptions (unmatched, late_payment) cannot be closed by
	 * a bare acknowledgement: the cash is real, so a note is required stating
	 * how it was handled (refunded to the customer, order reinstated, or
	 * otherwise accounted for). Acknowledging must never make received money
	 * disappear from the queue without a record of what happened to it.
	 */
	public function resolve($id, $store_id, $action, $note, $username){
		$allowed = array('acknowledged','resolved','dismissed','dispute');
		if(!in_array($action, $allowed, true)){ return false; }
		$row = $this->db->where('id', (int)$id)->where('store_id', (int)$store_id)->get('db_payment_exceptions')->row();
		if(!$row){ return false; }
		$note = trim((string)$note);
		if(in_array($row->exception_type, self::TYPES_MONEY_RECEIVED, true) && $note === ''){
			return false;
		}
		$status = ($action === 'dispute') ? 'open' : $action;
		$update = array(
			'status'          => $status,
			'resolved_date'   => date('Y-m-d H:i:s'),
			'resolved_by'     => $username,
			'resolution_note' => $note,
		);
		if($action === 'dispute'){ $update['exception_type'] = self::T_DISPUTE; }
		return $this->db->where('id', (int)$id)->where('store_id', (int)$store_id)
			->update('db_payment_exceptions', $update);
	}

	public function counts($store_id){
		$rows = $this->db->select("exception_type, status, COUNT(*) c")
			->where('store_id', (int)$store_id)->where('status_flag', 1)
			->group_by(array('exception_type','status'))->get('db_payment_exceptions')->result();
		$out = array('open'=>0);
		foreach($rows as $r){
			$out[$r->exception_type.'_'.$r->status] = (int)$r->c;
			if($r->status === 'open'){ $out['open'] += (int)$r->c; }
		}
		return $out;
	}

	public function get_exceptions($store_id, $status = 'open', $type = null, $limit = 200){
		$this->db->where('store_id', (int)$store_id)->where('status_flag', 1);
		if($status !== 'all'){ $this->db->where('status', $status); }
		if($type){ $this->db->where('exception_type', $type); }
		return $this->db->order_by('id', 'desc')->limit((int)$limit)->get('db_payment_exceptions')->result();
	}

	/**
	 * On-demand reconciliation scan. Merchant-owned sources only:
	 * db_salespayments (POS/invoice receipts), db_paystack_payments,
	 * db_monnify_payments, db_sales (partial), db_salespaymentsreturn (refunds).
	 * Subscription/fleet billing tables are platform-owned and excluded.
	 */
	public function scan($store_id){
		$store_id = (int)$store_id;
		$found = array('unmatched'=>0,'discrepancy'=>0,'partial'=>0,'refund'=>0);

		// a) Provider receipts recorded at POS/invoice but never confirmed by
		//    the provider → unmatched. Manual transfers stay explicitly
		//    labelled 'manual' — they are merchant-recorded, not verified.
		$unconfirmed = $this->db->where('store_id', $store_id)->where('status', 1)
			->where('confirmation_status', 0)
			->where_in('payment_type', array('Paystack','Monnify','Bank Transfer'))
			->get('db_salespayments')->result();
		foreach($unconfirmed as $sp){
			$manual = ($sp->payment_type === 'Bank Transfer');
			$this->queue($store_id, array(
				'exception_type'  => self::T_UNMATCHED,
				'provider'        => $manual ? 'manual' : strtolower($sp->payment_type),
				'reference'       => $sp->payment_reference ?: ('salespayment-'.$sp->id),
				'sales_id'        => $sp->sales_id,
				'salespayment_id' => $sp->id,
				'amount'          => $sp->payment,
				'detail'          => $manual
					? 'Manually recorded bank transfer — awaiting provider/bank verification'
					: ucfirst($sp->payment_type).' receipt recorded but not confirmed by provider',
				'detected_by'     => 'scan',
			)) && $found['unmatched']++;
		}

		// b) Provider payment rows whose settled amount disagrees with the sale.
		$pp = $this->db->where('store_id', $store_id)
			->where_in('payment_status', array('success','paid'))
			->get('db_paystack_payments')->result();
		foreach($pp as $p){
			$expected = null;
			if($p->sales_id){
				$sale = $this->db->select('grand_total')->where('id', $p->sales_id)->where('store_id', $store_id)->get('db_sales')->row();
				$expected = $sale ? (float)$sale->grand_total : null;
			}
			if($expected !== null && abs((float)$p->amount - $expected) > 0.01){
				$this->queue($store_id, array(
					'exception_type'  => self::T_DISCREPANCY,
					'provider'        => 'paystack',
					'reference'       => $p->paystack_reference,
					'sales_id'        => $p->sales_id,
					'amount'          => $p->amount,
					'expected_amount' => $expected,
					'currency'        => $p->currency,
					'detail'          => 'Provider amount differs from sale total',
					'detected_by'     => 'scan',
				)) && $found['discrepancy']++;
			}
		}
		$mp = $this->db->where('store_id', $store_id)
			->where_in('payment_status', array('PAID','OVERPAID'))
			->get('db_monnify_payments')->result();
		foreach($mp as $p){
			$paid = (float)($p->amount_paid ?: $p->amount);
			$expected = null;
			if($p->sales_id){
				$sale = $this->db->select('grand_total')->where('id', $p->sales_id)->where('store_id', $store_id)->get('db_sales')->row();
				$expected = $sale ? (float)$sale->grand_total : null;
			}
			if(($expected !== null && abs($paid - $expected) > 0.01) || $p->payment_status === 'OVERPAID'){
				$this->queue($store_id, array(
					'exception_type'  => self::T_DISCREPANCY,
					'provider'        => 'monnify',
					'reference'       => $p->payment_reference,
					'sales_id'        => $p->sales_id,
					'amount'          => $paid,
					'expected_amount' => $expected ?: $p->amount,
					'currency'        => $p->currency,
					'detail'          => 'Monnify settled amount differs from expected'.($p->payment_status==='OVERPAID' ? ' (OVERPAID)' : ''),
					'detected_by'     => 'scan',
				)) && $found['discrepancy']++;
			}
		}

		// c) Sales still partially paid → partial.
		$partials = $this->db->where('store_id', $store_id)->where('status', 1)
			->where('payment_status', 'Partial')
			->get('db_sales')->result();
		foreach($partials as $s){
			$this->queue($store_id, array(
				'exception_type'  => self::T_PARTIAL,
				'provider'        => 'manual',
				'reference'       => 'sale-'.$s->id,
				'sales_id'        => $s->id,
				'amount'          => $s->paid_amount,
				'expected_amount' => $s->grand_total,
				'detail'          => 'Sale partially paid — outstanding '.round($s->grand_total - $s->paid_amount, 2),
				'detected_by'     => 'scan',
			)) && $found['partial']++;
		}

		// d) Recorded sales-payment refunds → refund.
		$refunds = $this->db->where('store_id', $store_id)->where('status', 1)
			->get('db_salespaymentsreturn')->result();
		foreach($refunds as $r){
			$this->queue($store_id, array(
				'exception_type'  => self::T_REFUND,
				'provider'        => 'manual',
				'reference'       => 'return-'.$r->id,
				'sales_id'        => $r->sales_id,
				'amount'          => $r->payment,
				'detail'          => 'Sales payment refund recorded'.($r->payment_note ? ': '.$r->payment_note : ''),
				'detected_by'     => 'scan',
			)) && $found['refund']++;
		}

		return $found;
	}
}
