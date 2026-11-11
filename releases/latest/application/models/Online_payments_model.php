<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Money presented against an online order — one row per sum, so a part-payment,
 * a claimed transfer and a replayed card charge are each a fact the store can see
 * instead of a status the last person to touch the screen typed.
 *
 * The order's payment_status stops deciding itself: settleOrder() adds up the
 * verified rows and only then says unpaid / partially_paid / paid. Nothing here
 * is trusted to "check then insert" — the unique (store_id, dedupe_key) and the
 * status-conditional updates are the guards.
 *
 * Migration 4.0.9.234_online_order_payments.sql owns the table, and available()
 * creates it on first use as well, so an install that uploaded this code without
 * running the migration still records money instead of silently dropping it. If
 * the table cannot be created at all every method no-ops, which leaves the
 * existing card funnel behaving exactly as it did before.
 */
class Online_payments_model extends CI_Model {

	const PENDING  = 'pending';
	const VERIFIED = 'verified';
	const REJECTED = 'rejected';

	/** Kept identical to migration 4.0.9.234_online_order_payments.sql. */
	const TABLE_DDL = 'CREATE TABLE IF NOT EXISTS `db_online_order_payments` (
		`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		`store_id` INT NOT NULL,
		`online_order_id` INT NOT NULL,
		`provider` VARCHAR(20) NOT NULL,
		`amount` DECIMAL(12,2) NOT NULL,
		`currency` VARCHAR(8) NULL,
		`reference` VARCHAR(120) NULL,
		`status` VARCHAR(12) NOT NULL,
		`note` VARCHAR(255) NULL,
		`recorded_by` INT NULL,
		`recorded_at` DATETIME NULL,
		`verified_by` INT NULL,
		`verified_at` DATETIME NULL,
		`salespayment_id` INT NULL,
		`dedupe_key` CHAR(32) NOT NULL,
		`updated_at` DATETIME NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `uq_online_payment_dedupe` (`store_id`,`dedupe_key`),
		KEY `idx_online_payment_order` (`store_id`,`online_order_id`,`status`)
	)';

	/**
	 * Is the money ledger usable? Migration 4.0.9.234 owns the table; it is also
	 * created here on first use, because a store that uploaded the code without
	 * running the migration would otherwise take money for an order and record
	 * nothing anywhere. Returns false if the table still cannot be made.
	 */
	public function available(){
		static $ready = null;
		if($ready !== null) return $ready;
		if($this->db->table_exists('db_online_order_payments')) return $ready = true;
		try {
			$this->db->query(self::TABLE_DDL);
		} catch (Throwable $e) {
			log_message('error', 'Online order payments ledger unavailable: '.$e->getMessage());
			return $ready = false;
		}
		return $ready = $this->db->table_exists('db_online_order_payments');
	}

	/** The word a person uses for this channel. Never a raw code. */
	public function channelLabel($provider){
		$p = strtolower(trim((string)$provider));
		if($p === 'transfer' || $p === 'bank_transfer' || $p === 'bank') return 'Bank transfer';
		if($p === 'paystack' || $p === 'card') return 'Card payment';
		if($p === 'cash') return 'Cash';
		if($p === 'monnify') return 'Card payment';
		if($p === 'pay_on_delivery') return 'Cash on delivery';
		if($p === 'appointment') return 'Pay at collection';
		return $p === '' ? 'Payment' : ucfirst(str_replace('_', ' ', $p));
	}

	/**
	 * Record money presented against an order. A gateway verification arrives
	 * already verified; anything a person types about a transfer starts pending,
	 * because a typed-in bank reference is a claim until somebody who can see the
	 * account confirms it.
	 *
	 * @return array ok, id, status, duplicate, message
	 */
	public function recordPayment($storeId, $orderId, array $data){
		if(!$this->available()){
			return ['ok' => false, 'id' => 0, 'status' => '', 'duplicate' => false,
				'message' => 'Money tracking is not switched on for this store yet.'];
		}
		$storeId = (int)$storeId; $orderId = (int)$orderId;
		$amount = round((float)($data['amount'] ?? 0), 2);
		if($amount <= 0){
			return ['ok' => false, 'id' => 0, 'status' => '', 'duplicate' => false,
				'message' => 'Enter an amount greater than zero.'];
		}
		$order = $this->db->select('id, store_id, order_code, grand_total, customer_id')
			->where('id', $orderId)->where('store_id', $storeId)->get('db_online_orders')->row();
		if(!$order){
			return ['ok' => false, 'id' => 0, 'status' => '', 'duplicate' => false,
				'message' => 'That order is not one of yours.'];
		}
		$provider = strtolower(trim((string)($data['provider'] ?? 'transfer'))) ?: 'transfer';
		$reference = trim((string)($data['reference'] ?? ''));
		$status = in_array((string)($data['status'] ?? ''), [self::PENDING, self::VERIFIED, self::REJECTED], true)
			? (string)$data['status'] : self::PENDING;

		// A gateway reference identifies the money, so the same charge replayed by
		// a webhook, a browser retry or a double click becomes one row. A hand-typed
		// entry with no reference at all cannot be identified, so it must not be
		// swallowed as a duplicate of the last one either.
		$dedupe = md5($provider . '|' . $orderId . '|' . strtoupper($reference) . '|' . number_format($amount, 2, '.', ''));
		if($reference === '') $dedupe = md5($dedupe . '|' . bin2hex(random_bytes(6)));

		$now = date('Y-m-d H:i:s');
		$row = [
			'store_id'        => $storeId,
			'online_order_id' => $orderId,
			'provider'        => $provider,
			'amount'          => $amount,
			'currency'        => $data['currency'] ?? null,
			'reference'       => $reference !== '' ? mb_substr($reference, 0, 120) : null,
			'status'          => $status,
			'note'            => isset($data['note']) ? mb_substr(trim((string)$data['note']), 0, 255) : null,
			'recorded_by'     => !empty($data['recorded_by']) ? (int)$data['recorded_by'] : null,
			'recorded_at'     => $now,
			'verified_by'     => $status === self::VERIFIED && !empty($data['verified_by']) ? (int)$data['verified_by'] : null,
			'verified_at'     => $status === self::VERIFIED ? $now : null,
			'updated_at'      => $now,
			'dedupe_key'      => $dedupe,
		];
		if($this->db->field_exists('base_amount','db_online_order_payments')) $row['base_amount']=round((float)($data['base_amount']??$amount),4);
		if($this->db->field_exists('transaction_amount','db_online_order_payments')) $row['transaction_amount']=round((float)($data['transaction_amount']??$amount),4);
		if($this->db->field_exists('transaction_currency','db_online_order_payments')) $row['transaction_currency']=strtoupper((string)($data['transaction_currency']??$data['currency']??''));
		if($this->db->field_exists('provider_reference','db_online_order_payments')) $row['provider_reference']=trim((string)($data['provider_reference']??$reference)) ?: null;
		if($this->db->field_exists('provider_status','db_online_order_payments')) $row['provider_status']=isset($data['provider_status'])?mb_substr((string)$data['provider_status'],0,24):null;
		$this->db->query('INSERT IGNORE INTO db_online_order_payments ('
			 . implode(',', array_keys($row)) . ') VALUES ('
			 . implode(',', array_fill(0, count($row), '?')) . ')', array_values($row));

		if($this->db->affected_rows() === 0){
			$existing = $this->db->where('store_id', $storeId)->where('dedupe_key', $dedupe)
				->get('db_online_order_payments')->row();
			return ['ok' => true, 'id' => $existing ? (int)$existing->id : 0, 'order' => $order,
				'status' => $existing ? $existing->status : '', 'duplicate' => true,
				'message' => 'That payment is already recorded for this order.'];
		}
		$lineId = (int)$this->db->insert_id();

		// A caller that owns the paid transition itself (the card verifiers) asks
		// for the row only, so the order is not claimed out from under them.
		$settle = !array_key_exists('settle', $data) || !empty($data['settle']);
		if($status === self::VERIFIED && $settle) $this->settleOrder($storeId, $orderId, $row['verified_by']);
		return ['ok' => true, 'id' => $lineId, 'status' => $status, 'duplicate' => false, 'order' => $order,
			'message' => $status === self::VERIFIED
				? $this->channelLabel($provider) . ' of ' . $this->_money($amount, $order->store_id) . ' recorded.'
				: $this->channelLabel($provider) . ' of ' . $this->_money($amount, $order->store_id) . ' noted, waiting to be confirmed.'];
	}

	/**
	 * A payment gateway says money actually moved for this order. Record what it
	 * settled — whatever the amount — instead of throwing away a charge that is
	 * not a match to the cent, which is what the verifiers used to do.
	 *
	 * Deliberately does NOT change the order's status: the caller owns the atomic
	 * unpaid->paid claim and its stamps. covered tells it whether the invoiced
	 * total is now fully in.
	 *
	 * A charge that lands short of what the order owed at that moment is real
	 * money but not the invoiced money, and the caller's own status logic would
	 * leave it as a line in a log file, so it goes into the merchant's
	 * reconciliation queue as its own exception. Over payments are left to
	 * settleOrder(), which words them better ("paid X more than the order is
	 * worth") and lands on the same deduped row.
	 *
	 * @return array recorded, duplicate, covered, applied, due, message
	 */
	public function recordGatewayMoney($order, array $money, $detectedBy = 'the payment provider'){
		$storeId = (int)($order->store_id ?? 0);
		$orderId = (int)($order->id ?? 0);
		$due = round((float)($order->grand_total ?? 0), 2);
		$out = ['recorded' => false, 'duplicate' => false, 'covered' => false,
			'applied' => 0.0, 'due' => $due, 'message' => ''];
		if($storeId <= 0 || $orderId <= 0) return $out;
		if(strtolower(trim((string)($money['payment_status'] ?? 'success'))) !== 'success') return $out;
		$charge = round((float)($money['amount'] ?? 0), 2);
		if($charge <= 0) return $out;
		$alreadyIn = $this->verifiedTotal($storeId, $orderId);
		$res = $this->recordPayment($storeId, $orderId, [
			'provider'  => $money['provider'] ?? 'card',
			'reference' => $money['reference'] ?? '',
			'amount'    => $charge,
			'currency'  => $money['currency'] ?? null,
			'base_amount' => $money['base_amount'] ?? $charge,
			'transaction_currency' => $money['transaction_currency'] ?? $money['currency'] ?? null,
			'provider_reference' => $money['provider_reference'] ?? ($money['reference'] ?? ''),
			'provider_status' => $money['provider_status'] ?? 'COMPLETED',
			'status'    => self::VERIFIED,
			'note'      => trim((string)($money['note'] ?? '')) ?: ('Confirmed by ' . $detectedBy),
			'settle'    => false,
		]);
		if(empty($res['ok'])){ $out['message'] = $res['message']; return $out; }
		$out['recorded']  = empty($res['duplicate']);
		$out['duplicate'] = !empty($res['duplicate']);
		$out['applied']   = $this->verifiedTotal($storeId, $orderId);
		$out['covered']   = $due > 0 && $out['applied'] + 0.005 >= $due;
		$owed = round($due - $alreadyIn, 2);
		if($out['recorded'] && $owed > 0 && $charge + 0.005 < $owed){
			$this->_queueMismatch($storeId, $order, $charge, $owed, 'discrepancy',
				strtolower($this->channelLabel($money['provider'] ?? 'card')) . ' of '
				. $this->_money($charge, $storeId) . ' came in against an invoice of '
				. $this->_money($owed, $storeId), $detectedBy);
		}
		// More than the remaining balance, with other money already on the order:
		// the customer has paid twice. The pay page refuses to offer the card
		// button once money is in, so this only happens from a stale link or a
		// replayed webhook — both worth a human looking at.
		if($out['recorded'] && $alreadyIn > 0 && $charge > $owed + 0.005){
			$this->_queueMismatch($storeId, $order, $charge, $owed, 'discrepancy',
				strtolower($this->channelLabel($money['provider'] ?? 'card')) . ' of '
				. $this->_money($charge, $storeId) . ' came in when only '
				. $this->_money($owed, $storeId) . ' was still owed', $detectedBy);
		}
		return $out;
	}

	/**
	 * A human answers "is this money real". Only a pending row can move, so the
	 * second click on the same line says so instead of double-posting.
	 */
	public function decidePayment($storeId, $lineId, $decision, $actorUserId){
		if(!$this->available()) return ['ok' => false, 'message' => 'Money tracking is not switched on for this store yet.'];
		if(!in_array($decision, [self::VERIFIED, self::REJECTED], true)){
			return ['ok' => false, 'message' => 'That is not an answer I understand.'];
		}
		$storeId = (int)$storeId; $lineId = (int)$lineId;
		$line = $this->db->where('id', $lineId)->where('store_id', $storeId)
			->get('db_online_order_payments')->row();
		if(!$line) return ['ok' => false, 'message' => 'That payment is not one of yours.'];

		$now = date('Y-m-d H:i:s');
		$changed = $this->db->where('id', $lineId)->where('store_id', $storeId)
			->where('status', self::PENDING)
			->update('db_online_order_payments', [
				'status'      => $decision,
				'verified_by' => $decision === self::VERIFIED ? (int)$actorUserId : null,
				'verified_at' => $decision === self::VERIFIED ? $now : null,
				'updated_at'  => $now,
			]);
		if(!$changed || $this->db->affected_rows() !== 1){
			return ['ok' => false, 'status' => $line->status,
				'message' => 'That payment has already been ' . ($line->status === self::VERIFIED ? 'confirmed' : 'dismissed') . '.'];
		}
		$this->load->model('storefront_model');
		$order = $this->storefront_model->getOrder((int)$line->online_order_id);
		$settle = $order ? $this->settleOrder($storeId, (int)$line->online_order_id, (int)$actorUserId)
			: ['state' => 'missing', 'applied' => 0, 'due' => 0];

		$this->load->helper('custom');
		mp_audit_log('Online orders', $decision === self::VERIFIED ? 'payment_confirmed' : 'payment_rejected',
			(string)$line->online_order_id,
			($decision === self::VERIFIED ? 'Confirmed' : 'Dismissed') . ' ' . strtolower($this->channelLabel($line->provider))
			. ' of ' . $this->_money($line->amount, $storeId)
			. ' on order ' . ($order->order_code ?? '') . '. ' . $this->_stateSentence($settle));
		return ['ok' => true, 'message' => ($decision === self::VERIFIED
				? 'Payment confirmed. ' : 'Payment dismissed. ') . $this->_stateSentence($settle),
			'settle' => $settle];
	}

	public function paymentsFor($storeId, $orderId){
		if(!$this->available()) return [];
		$lines = $this->db->where('store_id', (int)$storeId)->where('online_order_id', (int)$orderId)
			->order_by('recorded_at', 'asc')->order_by('id', 'asc')
			->get('db_online_order_payments')->result();
		foreach($lines as $line){
			$line->channel_name = $this->channelLabel($line->provider);
			$line->person_name = $this->_personName($line->verified_by ?: $line->recorded_by, $storeId);
		}
		return $lines;
	}

	public function verifiedTotal($storeId, $orderId){ return $this->_total($storeId, $orderId, self::VERIFIED); }
	public function pendingTotal($storeId, $orderId){ return $this->_total($storeId, $orderId, self::PENDING); }

	private function _total($storeId, $orderId, $status){
		if(!$this->available()) return 0.0;
		$row = $this->db->select('COALESCE(SUM(amount),0) AS t', false)
			->where('store_id', (int)$storeId)->where('online_order_id', (int)$orderId)
			->where('status', $status)->get('db_online_order_payments')->row();
		return round((float)($row->t ?? 0), 2);
	}

	/**
	 * The one place that decides what an order's payment status means. Money only
	 * counts when it is verified, and the paid transition still goes through
	 * Storefront_model::claimPaidOrder(), so a racing webhook cannot post the
	 * ledger twice.
	 */
	public function settleOrder($storeId, $orderId, $actorUserId = null, $detectedBy = 'order payments'){
		$storeId = (int)$storeId; $orderId = (int)$orderId;
		if(!$this->available()){
			return ['ok' => false, 'state' => 'unavailable', 'applied' => 0, 'due' => 0, 'pending' => 0];
		}
		$order = $this->db->where('id', $orderId)->where('store_id', $storeId)
			->get('db_online_orders')->row();
		if(!$order){
			return ['ok' => false, 'state' => 'missing', 'applied' => 0, 'due' => 0, 'pending' => 0];
		}
		$due = round((float)$order->grand_total, 2);
		$applied = $this->verifiedTotal($storeId, $orderId);
		$pending = $this->pendingTotal($storeId, $orderId);
		$out = ['ok' => true, 'due' => $due, 'applied' => $applied, 'pending' => $pending,
			'state' => (string)$order->payment_status, 'posted' => 0];
		if($due <= 0) return $out;

		$this->load->model('storefront_model');

		if($applied + 0.005 >= $due){
			if((string)$order->payment_status === 'paid'){
				$salesId = (int)$order->sales_id ?: (int)$this->storefront_model->ensureSalesLedger($orderId);
				$out['posted'] = $this->postVerifiedLines($storeId, $orderId, $salesId);
				$out['state'] = 'paid';
			} else {
				$claimed = $this->storefront_model->claimPaidOrder($orderId, []);
				if($claimed){
					$this->storefront_model->adjustStock($orderId);
					$salesId = (int)$this->storefront_model->ensureSalesLedger($orderId);
					$out['posted'] = $this->postVerifiedLines($storeId, $orderId, $salesId);
					$this->storefront_model->recordPurchaseEvent($orderId, 'payment_reconciled');
					$this->storefront_model->fulfilPaidOrder($orderId);
					$out['state'] = 'paid';
				} else {
					// The order was paid, refunded or cancelled by somebody else
					// while this money was arriving. Real cash against a dead order
					// belongs in the merchant queue, not in a log file.
					$out['state'] = 'late';
					$this->_queueMismatch($storeId, $order, $applied, $due, 'late_payment',
						'The order is already ' . $order->order_status . '/' . $order->payment_status, $detectedBy);
				}
			}
			if($applied > $due + 0.005){
				$this->_queueMismatch($storeId, $order, $applied, $due, 'discrepancy',
					'The customer has paid ' . $this->_money($applied - $due, $storeId) . ' more than the order is worth', $detectedBy);
			}
			return $out;
		}

		if($applied > 0){
			if((string)$order->payment_status === 'unpaid'){
				$this->db->where('id', $orderId)->where('store_id', $storeId)
					->where('payment_status', 'unpaid')
					->update('db_online_orders', ['payment_status' => 'partially_paid', 'updated_at' => date('Y-m-d H:i:s')]);
			}
			$salesId = (int)$order->sales_id ?: (int)$this->storefront_model->ensureSalesLedger($orderId);
			$out['posted'] = $this->postVerifiedLines($storeId, $orderId, $salesId);
			$out['state'] = 'partially_paid';
			if($pending > 0){
				$this->_queueMismatch($storeId, $order, $applied, $due, 'partial',
					rtrim(rtrim(number_format($due - $applied, 2, '.', ''), '0'), '.') . ' is still owed and '
					. $this->_money($pending, $storeId) . ' more is waiting to be confirmed', $detectedBy);
			}
			return $out;
		}

		if($pending > 0 && in_array((string)$order->payment_status, ['unpaid', 'partially_paid'], true)){
			$this->_queueMismatch($storeId, $order, $applied, $due, 'partial',
				'Nothing has been confirmed yet and ' . $this->_money($pending, $storeId) . ' is waiting for somebody to check it', $detectedBy);
		}
		return $out;
	}

	/**
	 * Turn confirmed money into the counter's own payment lines, once each. The
	 * ledger row this lands on is the one the order already owns, so a store's
	 * sales report shows online money and till money the same way.
	 */
	public function postVerifiedLines($storeId, $orderId, $salesId){
		if(!$this->available() || (int)$salesId <= 0) return 0;
		$lines = $this->db->where('store_id', (int)$storeId)->where('online_order_id', (int)$orderId)
			->where('status', self::VERIFIED)->where('salespayment_id IS NULL')->get('db_online_order_payments')->result();
		if(!$lines) return 0;
		$order = $this->db->where('id', (int)$orderId)->where('store_id', (int)$storeId)
			->get('db_online_orders')->row();
		if(!$order) return 0;
		$customerId = (int)($order->customer_id ?? 0);
		$posted = 0;
		foreach($lines as $line){
			$when = $line->verified_at ?: $line->recorded_at;
			$this->db->insert('db_salespayments', [
				'store_id'            => (int)$storeId,
				'sales_id'            => (int)$salesId,
				'payment_date'        => date('Y-m-d', strtotime((string)$when)),
				'payment_type'        => $this->channelLabel($line->provider),
				'payment'             => (float)$line->amount,
				'payment_reference'   => $line->reference,
				'payment_note'        => 'Online order ' . $order->order_code,
				'confirmation_status' => 1,
				'created_date'        => date('Y-m-d'),
				'created_time'        => date('H:i:s'),
				'created_by'          => 'OnlineStore',
				'status'              => 1,
				'customer_id'         => $customerId > 0 ? $customerId : null,
			]);
			$paymentId = (int)$this->db->insert_id();
			if($paymentId){
				$this->db->where('id', (int)$line->id)->update('db_online_order_payments',
					['salespayment_id' => $paymentId, 'updated_at' => date('Y-m-d H:i:s')]);
				$posted++;
			}
		}
		if($posted){
			$this->load->model('sales_model');
			if(method_exists($this->sales_model, 'update_sales_payment_status_by_sales_id')){
				$this->sales_model->update_sales_payment_status_by_sales_id((int)$salesId, $customerId);
			}
		}
		return $posted;
	}

	/** Has any verified money reached this order's ledger? Used to stop the card
	 *  funnel posting a second, full-total payment line beside these. */
	public function hasVerifiedLines($storeId, $orderId){
		if(!$this->available()) return false;
		$row = $this->db->where('store_id', (int)$storeId)->where('online_order_id', (int)$orderId)
			->where('status', self::VERIFIED)->get('db_online_order_payments')->row();
		return (bool)$row;
	}

	/**
	 * Everything the money box needs about one order, in one call: the lines, the
	 * running totals, and the plain-English sentence for what is still missing.
	 */
	public function summaryFor($order){
		$storeId = (int)($order->store_id ?? 0);
		$orderId = (int)($order->id ?? 0);
		$due = round((float)($order->grand_total ?? 0), 2);
		$box = ['lines' => [], 'verified' => 0.0, 'pending' => 0.0, 'outstanding' => $due,
			'covered' => false, 'any' => false, 'due' => $due, 'state' => ''];
		if($storeId <= 0 || $orderId <= 0 || !$this->available()) return $box;
		$box['lines']     = $this->paymentsFor($storeId, $orderId);
		$grossVerified  = $this->verifiedTotal($storeId, $orderId);
		$box['refunded'] = 0.0;
		if($this->db->table_exists('db_online_order_refunds')){
			$refunds=$this->db->select('COALESCE(SUM(base_amount),0) AS total',false)->where('store_id',$storeId)->where('online_order_id',$orderId)
				->where_in('provider_status',['completed','processed','success'])->get('db_online_order_refunds')->row();
			$box['refunded']=round((float)($refunds->total??0),2);
		}
		$box['gross_verified']=$grossVerified;
		$box['verified']=round(max(0,$grossVerified-$box['refunded']),2);
		$box['pending']   = $this->pendingTotal($storeId, $orderId);
		$box['outstanding'] = round(max(0, $due - $box['verified']), 2);
		$box['covered']   = $due > 0 && $box['verified'] + 0.005 >= $due && (string)($order->payment_status??'')!=='refunded';
		$box['any']       = $box['verified'] > 0 || $box['pending'] > 0 || (bool)$box['lines'];
		$box['state']     = $box['covered'] ? 'fully' : ($box['verified'] > 0 ? 'short' : ($box['pending'] > 0 ? 'unconfirmed' : 'none'));
		return $box;
	}

	private function _queueMismatch($storeId, $order, $applied, $due, $type, $why, $detectedBy = 'order payments'){
		$this->load->model('payment_reconcile_model', 'recon');
		$this->recon->queue((int)$storeId, [
			'exception_type'  => $type,
			'provider'        => 'online_store',
			'reference'       => (string)$order->order_code,
			'order_id'        => (int)$order->id,
			'sales_id'        => (int)$order->sales_id > 0 ? (int)$order->sales_id : null,
			'amount'          => $applied,
			'expected_amount' => $due,
			'detail'          => 'Order ' . $order->order_code . ': ' . $why . '.',
			'detected_by'     => $detectedBy,
		]);
	}

	private function _stateSentence($settle){
		$state = $settle['state'] ?? '';
		if($state === 'paid') return 'The order is now fully paid.';
		if($state === 'partially_paid'){
			return 'Still owed: ' . $this->_money(round((float)($settle['due'] ?? 0) - (float)($settle['applied'] ?? 0), 2), 0) . '.';
		}
		if($state === 'late') return 'But this order is already closed, so the money needs a decision.';
		return 'Nothing else has changed on the order.';
	}

	private function _personName($userId, $storeId){
		$userId = (int)$userId;
		if($userId <= 0 || !$this->db->table_exists('db_users')) return null;
		$user = $this->db->select('username, first_name')->where('id', $userId)->get('db_users')->row();
		if(!$user) return null;
		$name = trim((string)($user->first_name ?? ''));
		return $name !== '' ? $name : $user->username;
	}

	private function _money($amount, $storeId){
		static $symbols = [];
		$storeId = (int)$storeId;
		$symbol = '';
		if($storeId > 0){
			if(!isset($symbols[$storeId])){
				$symbols[$storeId] = '';
				if($this->db->table_exists('db_currency')){
					// Same resolution the storefront theme uses: db_currency.currency
					// is the glyph this install stores for that currency.
					$row = $this->db->query('SELECT a.currency AS symbol FROM db_currency a INNER JOIN db_store b ON b.currency_id = a.id WHERE b.id = ? LIMIT 1',
						[$storeId])->row();
					$symbols[$storeId] = trim((string)($row->symbol ?? ''));
				}
			}
			$symbol = $symbols[$storeId];
		}
		$number = function_exists('store_number_format') ? store_number_format((float)$amount) : number_format((float)$amount, 2);
		return trim($symbol . ' ' . $number);
	}
}
