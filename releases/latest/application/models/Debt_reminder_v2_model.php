<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debt_reminder_v2_model — scheduling + pause/resume for debt reminders,
 * backed by the outbox (db_notification_queue) so every send inherits
 * dedupe (event_key), retry-with-backoff and delivery-time suppression.
 *
 * Layered pause precedence (most specific wins, any active pause suppresses):
 *   clinic  <  patient  <  invoice
 *
 * Reads the authoritative debt figures from db_sales (Final + Opening when
 * include_opening_debt is on). No clinical detail is ever placed in an email.
 */
class Debt_reminder_v2_model extends CI_Model {

	const FREQS = array('daily','3days','weekly','biweekly','monthly');

	public function __construct(){
		parent::__construct();
		$this->_ensure();
	}

	private function _ensure(){
		foreach(array('db_debt_reminder_config','db_debt_reminder_pauses','db_debt_reminder_audit') as $t){
			if(!$this->db->table_exists($t)){
				// Migrations should have created these; self-heal is a no-op
				// placeholder so tests don't hard-fail on a missing migration.
				log_message('error', 'Debt_reminder_v2_model: missing table ' . $t);
			}
		}
	}

	// ------------------------------------------------------------------
	// Clinic config
	// ------------------------------------------------------------------
	public function config($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id', $storeId)->get('db_debt_reminder_config')->row();
		if($row) return $row;
		$this->db->insert('db_debt_reminder_config', array(
			'store_id' => $storeId,
			'enabled' => 0, 'frequency' => 'weekly', 'grace_days' => 0,
			'send_hour' => 9, 'max_reminders' => 0, 'include_opening_debt' => 1,
			'template_key' => 'debt_reminder',
			'updated_by' => $this->session->userdata('inv_username') ?: 'system',
			'updated_at' => date('Y-m-d H:i:s'),
		));
		return $this->db->where('store_id', $storeId)->get('db_debt_reminder_config')->row();
	}

	public function saveConfig($storeId, array $d){
		$freq = in_array($d['frequency'] ?? '', self::FREQS, true) ? $d['frequency'] : 'weekly';
		$data = array(
			'enabled' => !empty($d['enabled']) ? 1 : 0,
			'frequency' => $freq,
			'grace_days' => max(0, (int)($d['grace_days'] ?? 0)),
			'send_hour' => min(23, max(0, (int)($d['send_hour'] ?? 9))),
			'max_reminders' => max(0, (int)($d['max_reminders'] ?? 0)),
			'include_opening_debt' => !empty($d['include_opening_debt']) ? 1 : 0,
			'template_key' => trim((string)($d['template_key'] ?? 'debt_reminder')) ?: 'debt_reminder',
			'updated_by' => $this->session->userdata('inv_username') ?: 'system',
			'updated_at' => date('Y-m-d H:i:s'),
		);
		$this->db->where('store_id', $storeId)->update('db_debt_reminder_config', $data);
		if(!$this->db->affected_rows()){
			$exists = $this->db->where('store_id', $storeId)->count_all_results('db_debt_reminder_config');
			if(!$exists){ $data['store_id'] = $storeId; $this->db->insert('db_debt_reminder_config', $data); }
		}
		return true;
	}

	// ------------------------------------------------------------------
	// Pause / resume
	// ------------------------------------------------------------------
	/**
	 * Pause. Scope clinic|patient|invoice. resume_at may be null (manual) or a
	 * datetime (auto-resume). Records actor + reason for the audit trail.
	 */
	public function pause($storeId, $scope, $reason, $target = null, $resumeAt = null){
		$scope = in_array($scope, array('clinic','patient','invoice'), true) ? $scope : 'patient';
		$data = array(
			'store_id' => $storeId,
			'pause_scope' => $scope,
			'reason' => trim((string)$reason),
			'paused_by' => (int)$this->session->userdata('inv_userid'),
			'paused_at' => date('Y-m-d H:i:s'),
			'resume_at' => $resumeAt ?: null,
			'active' => 1,
		);
		if($scope === 'patient' && $target){ $data['patient_id'] = (int)$target; }
		if($scope === 'invoice' && $target){ $data['invoice_id'] = (int)$target; }
		$this->db->insert('db_debt_reminder_pauses', $data);
		$id = $this->db->insert_id();
		$this->audit($storeId, 'paused', ($scope === 'patient') ? (int)$target : null, ($scope === 'invoice') ? (int)$target : null, $reason);
		return $id;
	}

	/** Resume an active pause. */
	public function resume($pauseId, $storeId){
		$this->db->where('id', (int)$pauseId)->where('store_id', $storeId)->where('active', 1)
			->update('db_debt_reminder_pauses', array(
				'active' => 0, 'resumed_by' => (int)$this->session->userdata('inv_userid'),
				'resumed_at' => date('Y-m-d H:i:s'),
			));
		return $this->db->affected_rows() > 0;
	}

	/** Expire pauses whose resume_at has passed (auto-resume). */
	public function expireDue(){
		$this->db->where('active', 1)->where('resume_at IS NOT NULL', null, false)
			->where('resume_at <=', date('Y-m-d H:i:s'))
			->update('db_debt_reminder_pauses', array('active' => 0, 'resumed_at' => date('Y-m-d H:i:s')));
		return $this->db->affected_rows();
	}

	/** Active pauses for a store (for the settings/list UI). */
	public function activePauses($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('store_id', $storeId)->where('active', 1)
			->order_by('id', 'desc')->get('db_debt_reminder_pauses')->result();
	}

	/**
	 * Is sending suppressed for (store, patient, invoice)? Any active pause
	 * at clinic, patient or invoice level suppresses.
	 */
	public function isPaused($storeId, $patientId = null, $invoiceId = null){
		return $this->_paused($storeId, $patientId, $invoiceId);
	}

	private function _paused($storeId, $patientId, $invoiceId){
		// Use a single explicit WHERE with OR branches, each constrained to the
		// same store and to active pauses only. build_query_string is avoided
		// (shared builder); raw IN list is safe here — ids are int-cast.
		$storeId = (int)$storeId;
		$conds = array("(store_id = {$storeId} AND active = 1 AND pause_scope = 'clinic')");
		if($patientId){
			$conds[] = "(store_id = {$storeId} AND active = 1 AND pause_scope = 'patient' AND patient_id = " . (int)$patientId . ")";
		}
		if($invoiceId){
			$conds[] = "(store_id = {$storeId} AND active = 1 AND pause_scope = 'invoice' AND invoice_id = " . (int)$invoiceId . ")";
		}
		$sql = 'SELECT 1 FROM db_debt_reminder_pauses WHERE ' . implode(' OR ', $conds) . ' LIMIT 1';
		return $this->db->query($sql)->row() ? true : false;
	}

	// ------------------------------------------------------------------
	// Eligibility + debt
	// ------------------------------------------------------------------
	/**
	 * Legacy compatibility: the pre-existing db_debt_reminder_settings table is
	 * the authority for per-store and per-customer reminder state (enabled,
	 * frequency, max_reminders/reminder_count, send_email/send_sms,
	 * last_reminder_sent). When the outbox path is enabled, these values STILL
	 * govern — they are mapped in here, not dropped.
	 *
	 * Returns array('store_enabled'=>, 'store_freq'=>, 'store_max'=>,
	 *                'customer'=> row|null)
	 */
	private function legacyEffective($storeId, $customerId = null){
		$s = $this->db->where('store_id', (int)$storeId)->where('customer_id', 0)
			->get('db_debt_reminder_settings')->row();
		$out = array(
			'store_enabled' => $s ? (int)$s->enabled : 0,
			'store_freq'    => $s ? $s->frequency : 'weekly',
			'store_max'     => $s ? (int)$s->max_reminders : 0,
		);
		$out['customer'] = null;
		if($customerId){
			$out['customer'] = $this->db->where('store_id', (int)$storeId)
				->where('customer_id', (int)$customerId)->get('db_debt_reminder_settings')->row();
		}
		return $out;
	}

	/** Legacy frequency → seconds (mirrors Debt_reminder_model::isDue). */
	private function legacyIsDue($lastSent, $frequency){
		if(empty($lastSent)) return true;
		$last = strtotime($lastSent); $now = time();
		switch($frequency){
			case 'daily':    return ($now - $last) >= 86400;
			case '3days':    return ($now - $last) >= 259200;
			case 'weekly':   return ($now - $last) >= 604800;
			case 'biweekly': return ($now - $last) >= 1209600;
			case 'monthly':  return ($now - $last) >= 2592000;
			default:         return ($now - $last) >= 604800;
		}
	}

	/**
	 * Whether the outbox path should send to this customer, honouring BOTH the
	 * new clinic toggle AND the legacy store/customer settings.
	 *   - legacy store enabled=0  → suppressed (store-level pause)
	 *   - legacy customer enabled=0 → suppressed (customer-level pause)
	 *   - legacy customer send_email=0 → suppressed (no email channel)
	 *   - frequency not yet due (last_reminder_sent) → not due
	 *   - max_reminders reached → suppressed
	 */
	public function legacyGate($storeId, $patientId, $customerId){
		$leg = $this->legacyEffective($storeId, $customerId);
		if($leg['store_enabled'] !== 1) return array('suppress' => true, 'reason' => 'legacy store reminders disabled');
		$c = $leg['customer'];
		if($c){
			if((int)$c->enabled === 0) return array('suppress' => true, 'reason' => 'legacy customer paused');
			if((int)$c->send_email === 0) return array('suppress' => true, 'reason' => 'legacy email channel disabled');
			$freq = $c->frequency ?: $leg['store_freq'];
			$max  = (int)$c->max_reminders;
			$cnt  = (int)$c->reminder_count;
			$last = $c->last_reminder_sent;
		} else {
			$freq = $leg['store_freq'];
			$max  = $leg['store_max'];
			$cnt  = 0;
			$last = null;
		}
		if($max > 0 && $cnt >= $max) return array('suppress' => true, 'reason' => 'legacy max reminders reached');
		if(!$this->legacyIsDue($last, $freq)) return array('suppress' => true, 'reason' => 'legacy frequency not due');
		return array('suppress' => false, 'reason' => null);
	}

	/** Customers with outstanding debt (patient-wide, Final [+ Opening]). */
	public function debtors($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$cfg = $this->config($storeId);
		$statuses = array('Final');
		if((int)$cfg->include_opening_debt === 1) $statuses[] = 'Opening';

		$this->db->select('p.id AS patient_id, p.customer_id, c.customer_name, c.email, c.mobile, c.status AS customer_status, p.deceased,
				COALESCE(SUM(s.grand_total - s.paid_amount),0) AS amount_due')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->join('db_sales s', 's.customer_id = p.customer_id AND s.store_id = p.store_id', 'left')
			->where('p.store_id', $storeId)
			->where_in('s.sales_status', $statuses)
			->where('(s.grand_total - s.paid_amount) >', 0)
			->group_by('p.id')
			->having('amount_due > 0')
			->order_by('amount_due', 'desc');
		return $this->db->get()->result();
	}

	/** Invoices a single patient owes (for per-invoice pause + statements UI). */
	public function patientInvoices($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$p = $this->db->where('id', (int)$patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$p || !$p->customer_id) return array();
		return $this->db->where('store_id', $storeId)->where('customer_id', (int)$p->customer_id)
			->where_in('sales_status', array('Final','Opening'))
			->where('(grand_total - paid_amount) >', 0)
			->order_by('id', 'desc')->get('db_sales')->result();
	}

	// ------------------------------------------------------------------
	// Scheduling (outbox-backed)
	// ------------------------------------------------------------------
	/**
	 * Queue reminders for all due debtors. Returns counts. Idempotent via
	 * event_key — a re-run on the same day never double-queues. Re-checks
	 * pause state + eligibility immediately before enqueueing.
	 */
	public function schedule($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$cfg = $this->config($storeId);
		$this->expireDue();

		$result = array('queued' => 0, 'skipped' => 0, 'paused' => 0, 'suppressed' => 0);
		if((int)$cfg->enabled !== 1) return $result;

		if(!function_exists('physio_notify')) $this->load->helper('physio');

		foreach($this->debtors($storeId) as $d){
			// Suppress ineligible recipients: customer inactive, deceased.
			if((int)$d->customer_status !== 1 || (int)$d->deceased === 1){
				$result['suppressed']++;
				$this->audit($storeId, 'suppressed', (int)$d->patient_id, null, 'ineligible recipient');
				continue;
			}
			if($this->isPaused($storeId, (int)$d->patient_id, null)){
				$result['paused']++;
				continue;
			}
			// Legacy store/customer settings still govern the outbox path.
			$leg = $this->legacyGate($storeId, (int)$d->patient_id, (int)$d->customer_id);
			if(!empty($leg['suppress'])){
				$result['suppressed']++;
				$this->audit($storeId, 'suppressed', (int)$d->patient_id, null, $leg['reason']);
				continue;
			}
			if(empty($d->email)){
				$result['suppressed']++;
				$this->audit($storeId, 'suppressed', (int)$d->patient_id, null, 'no email');
				continue;
			}
			$eventKey = 'debt.reminder.' . $storeId . '.' . (int)$d->patient_id . '.' . date('Y-m-d');
			$ok = physio_notify($eventKey, array(
				'store_id' => $storeId,
				'channel' => 'email',
				'template_key' => $cfg->template_key,
				'recipient' => $d->email,
				'scheduled_at' => date('Y-m-d H:i:s'),
				'payload' => array(
					'patient_id' => (int)$d->patient_id,
					'amount_due' => round((float)$d->amount_due, 2),
					'customer_name' => $d->customer_name,
				),
			));
			$result[ $ok ? 'queued' : 'skipped' ]++;
			if($ok){
				$this->audit($storeId, 'queued', (int)$d->patient_id, null, null, $eventKey, (float)$d->amount_due);
				$this->legacyMarkSent($storeId, (int)$d->customer_id);
			}
		}
		return $result;
	}

	/** Advance the legacy last_reminder_sent/reminder_count so frequency + cap
	 *  stay authoritative across the outbox path (no double-send drift). */
	private function legacyMarkSent($storeId, $customerId){
		$exists = $this->db->where('store_id', $storeId)->where('customer_id', (int)$customerId)
			->count_all_results('db_debt_reminder_settings');
		if($exists){
			$this->db->where('store_id', $storeId)->where('customer_id', (int)$customerId);
			$this->db->set('reminder_count', 'reminder_count + 1', FALSE);
			$this->db->update('db_debt_reminder_settings', array('last_reminder_sent' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
		}
	}

	// ------------------------------------------------------------------
	// Audit
	// ------------------------------------------------------------------
	public function audit($storeId, $action, $patientId = null, $invoiceId = null, $reason = null, $eventKey = null, $amountDue = null){
		return $this->db->insert('db_debt_reminder_audit', array(
			'store_id' => $storeId,
			'patient_id' => $patientId,
			'invoice_id' => $invoiceId,
			'action' => $action,
			'reason' => $reason ? substr($reason, 0, 250) : null,
			'actor' => $this->session->userdata('inv_username') ?: 'system',
			'event_key' => $eventKey,
			'amount_due' => $amountDue,
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function auditRows($storeId = null, $limit = 100){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('store_id', $storeId)->order_by('id', 'desc')->limit($limit)->get('db_debt_reminder_audit')->result();
	}

	// ------------------------------------------------------------------
	// Delivery-time re-check (called from physio_notification_process just
	// before physio_deliver). Returns:
	//   array('suppress' => bool, 'reason' => ?string, 'amount' => ?float)
	// A still-outstanding debt refreshes the amount (partial payment lowers
	// it); a fully-paid / paused / ineligible recipient suppresses the send.
	// ------------------------------------------------------------------
	public function deliveryCheck($row, &$amount){
		$storeId = (int)$row->store_id;
		$payload = json_decode($row->payload_json, true) ?: array();
		$patientId = !empty($payload['patient_id']) ? (int)$payload['patient_id'] : 0;
		if(!$patientId) return array('suppress' => true, 'reason' => 'no patient context');

		// 1. Clinic config still enabled?
		$cfg = $this->config($storeId);
		if((int)$cfg->enabled !== 1) return array('suppress' => true, 'reason' => 'reminders disabled');

		// 2. Any active pause (clinic/patient/invoice)?
		if($this->isPaused($storeId, $patientId, !empty($payload['invoice_id']) ? (int)$payload['invoice_id'] : null)){
			return array('suppress' => true, 'reason' => 'paused');
		}

		// 2b. Legacy customer/store settings still govern at delivery time (a
		//     customer-level pause or frequency change after queuing must win).
		$p0 = $this->db->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if($p0 && $p0->customer_id && $this->db->table_exists('db_debt_reminder_settings')){
			$lg = $this->legacyGate($storeId, $patientId, (int)$p0->customer_id);
			if(!empty($lg['suppress'])) return array('suppress' => true, 'reason' => 'legacy: ' . $lg['reason']);
		}

		// 3. Recipient eligibility (customer active, not deceased).
		$p = $this->db->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
		if(!$p || (int)$p->deceased === 1) return array('suppress' => true, 'reason' => 'ineligible recipient');
		$c = $this->db->where('id', (int)$p->customer_id)->get('db_customers')->row();
		if(!$c || (int)$c->status !== 1) return array('suppress' => true, 'reason' => 'customer inactive');
		if(empty($c->email)) return array('suppress' => true, 'reason' => 'no email');

		// 4. Re-read CURRENT outstanding debt. Full payment (or zero/negative)
		//    suppresses; partial payment refreshes the amount for the email.
		$statuses = array('Final');
		if((int)$cfg->include_opening_debt === 1) $statuses[] = 'Opening';
		$due = (float)$this->db->select('COALESCE(SUM(grand_total - paid_amount),0) AS d', false)
			->where('store_id', $storeId)->where('customer_id', (int)$p->customer_id)
			->where_in('sales_status', $statuses)
			->get('db_sales')->row()->d;
		$amount = round($due, 2);
		if($amount <= 0.001) return array('suppress' => true, 'reason' => 'fully paid');

		return array('suppress' => false, 'reason' => null, 'amount' => $amount);
	}
}
