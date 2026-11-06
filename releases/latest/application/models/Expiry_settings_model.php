<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expiry_settings_model extends CI_Model {

	public function get_settings($store_id = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		// Expiry settings are an optional extension on older fleets. POS only
		// needs safe defaults when the table has not been installed yet; it must
		// not fail the whole till while waiting for the migration.
		$row = null;
		if($this->db->table_exists('db_expiry_settings')){
			$query = $this->db->where('store_id', $store_id)->get('db_expiry_settings');
			$row = $query ? $query->row() : null;
		}
		if(!$row) {
			// Return defaults
			$row = new stdClass();
			$row->alert_before_days = 30;
			$row->stop_selling_expired = 1;
			$row->email_alerts_enabled = 0;
			$row->alert_email = '';
		}
		// The alert columns arrive with 4.0.9.245; an older schema still has to
		// answer with the behaviour it had before them — no WhatsApp channel, no
		// number, nothing sent yet — instead of an undefined property.
		$row->whatsapp_alerts_enabled = (int)($row->whatsapp_alerts_enabled ?? 0);
		$row->alert_phone = trim((string)($row->alert_phone ?? ''));
		$row->last_alert_slot = trim((string)($row->last_alert_slot ?? ''));
		$this->load->model('Inventory_rotation_model', 'inventory_rotation');
		$row->rotation_method = $this->inventory_rotation->method($store_id);
		return $row;
	}

	public function save_settings() {
		$alert_before_days = $this->input->post('alert_before_days', TRUE);
		$stop_selling_expired = (int)($this->input->post('stop_selling_expired', TRUE) == '1');
		$email_alerts_enabled = (int)($this->input->post('email_alerts_enabled', TRUE) == '1');
		$alert_email = trim((string)$this->input->post('alert_email', TRUE));
		$whatsapp_alerts_enabled = (int)($this->input->post('whatsapp_alerts_enabled', TRUE) == '1');
		$alert_phone = preg_replace('/[^\d+\-\s()]/', '', trim((string)$this->input->post('alert_phone', TRUE)));
		$rotation_method = strtoupper(trim((string)$this->input->post('rotation_method', TRUE)));
		$store_id = get_current_store_id();
		if ($rotation_method !== '' && !in_array($rotation_method, ['MANUAL', 'FEFO', 'FIFO'], true)) return 'failed';
		if (in_array($rotation_method, ['FEFO', 'FIFO'], true) && !$this->db->table_exists('db_inventory_rotation_settings')) return 'failed';

		// A channel is only worth switching on if there is somewhere to send to,
		// and the alert is read by a person, not a machine — a half-typed number
		// or address must fail here rather than silence the digest for a month.
		$hasAlertColumns = $this->db->field_exists('alert_phone', 'db_expiry_settings');
		if ($hasAlertColumns && $alert_email !== '' && !filter_var($alert_email, FILTER_VALIDATE_EMAIL)) return 'failed';
		if ($email_alerts_enabled === 1 && $alert_email === '') return 'failed';
		if ($whatsapp_alerts_enabled === 1) {
			if ($alert_phone === '') return 'failed';
			$digits = preg_replace('/\D+/', '', $alert_phone);
			if (strlen($digits) < 7 || strlen($digits) > 15) return 'failed';
		}

		$info = array(
			'store_id' => $store_id,
			'alert_before_days' => $alert_before_days,
			'stop_selling_expired' => $stop_selling_expired,
			'email_alerts_enabled' => $email_alerts_enabled,
			'alert_email' => $alert_email,
		);
		if ($hasAlertColumns) {
			$info['whatsapp_alerts_enabled'] = $whatsapp_alerts_enabled;
			$info['alert_phone'] = $alert_phone;
		}

		if(!$this->db->table_exists('db_expiry_settings')) return 'failed';
		$this->db->trans_begin();
		$settings_before = $this->db->where('store_id', $store_id)->get('db_expiry_settings')->row();
		$exists = $settings_before ? 1 : 0;
		if($exists > 0) {
			$this->db->where('store_id', $store_id);
			$q1 = $this->db->update('db_expiry_settings', $info);
		} else {
			$q1 = $this->db->insert('db_expiry_settings', $info);
		}

		if (!$q1) { $this->db->trans_rollback(); return 'failed'; }
		if ($rotation_method !== '' && $this->db->table_exists('db_inventory_rotation_settings')) {
			$this->load->model('Inventory_rotation_model', 'inventory_rotation');
			// Rotation is set from Business Profile now. This older form is still
			// a live endpoint, so it honours the same switch guard rather than
			// letting a stale post quietly block the counter.
			$guard = $this->inventory_rotation->switch_guard($store_id, $rotation_method,
				(string)$this->input->post('rotation_ack', TRUE) === '1');
			if (!$guard['ok']) {
				$this->db->trans_rollback();
				return 'failed: ' . $guard['message'];
			}
			if (!$this->inventory_rotation->save_method($store_id, $rotation_method, get_current_user_id())) {
				$this->db->trans_rollback();
				return 'failed';
			}
		}
		if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return 'failed'; }
		$this->_audit_settings_saved($store_id, $settings_before);
		$this->db->trans_commit();
		return 'success';
	}

	/**
	 * Who the daily list goes to, and how much warning it gives, is a decision
	 * with consequences — record the switch that moved, not just the fact that
	 * somebody pressed Save.
	 */
	private function _audit_settings_saved($store_id, $before){
		if (!function_exists('mp_audit_log')) return;
		try {
			$after = $this->db->where('store_id', (int)$store_id)->get('db_expiry_settings')->row();
			if (!$after) return;
			$fields = array(
				'alert_before_days'      => array('label' => 'Days of warning before a batch runs out', 'type' => 'qty'),
				'stop_selling_expired'   => array('label' => 'Refuse to sell stock past its date', 'type' => 'yesno'),
				'email_alerts_enabled'   => array('label' => 'Send the daily list by email', 'type' => 'yesno'),
				'whatsapp_alerts_enabled'=> array('label' => 'Send the daily list by WhatsApp', 'type' => 'yesno'),
				'alert_email'            => array('label' => 'Email the list to', 'type' => 'text'),
				'alert_phone'            => array('label' => 'Send the list to', 'type' => 'text'),
			);
			$changes = mp_audit_diff($before, $after, $fields);
			if (!$changes) return;
			$capture = mp_audit_capture();
			if (!$capture) return;
			$capture->event($store_id, 'settings', 'expiry_settings', 'expiry',
				'Expiry warnings', $changes);
		} catch (Throwable $e) {
			log_message('error', 'Expiry settings audit capture failed: ' . $e->getMessage());
		}
	}

	public function get_expiring_items($store_id = null, $days = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		if(empty($days)) {
			$settings = $this->get_settings($store_id);
			$days = $settings->alert_before_days;
		}
		$future = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');

		return $this->db->where('store_id', $store_id)
						->where('expire_date IS NOT NULL')
						->where('expire_date <=', $future)
						->where('expire_date >=', $today)
						->where('status', 1)
						->get('db_items')
						->result();
	}

	public function get_expired_items($store_id = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$today = date('Y-m-d');
		return $this->db->where('store_id', $store_id)
						->where('expire_date IS NOT NULL')
						->where('expire_date <', $today)
						->where('status', 1)
						->get('db_items')
						->result();
	}

	public function count_expiring($store_id = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$settings = $this->get_settings($store_id);
		$days = $settings->alert_before_days;
		$future = date('Y-m-d', strtotime("+{$days} days"));
		$today = date('Y-m-d');
		return $this->_count_expiry_union($store_id, $today, $future);
	}

	public function count_expired($store_id = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$today = date('Y-m-d');
		return $this->_count_expiry_union($store_id, null, $today);
	}

	private function _count_expiry_union($store_id, $from_date, $to_date) {
		// Include both item-level and batch-level expiry.
		// Count distinct items that have at least one expired/expiring batch with positive stock
		// OR an item-level expiry date in the range.
		$table_barcodes = $this->db->table_exists('db_item_barcodes');
		$params = [$store_id, $to_date];
		if ($from_date) {
			$sql = "SELECT COUNT(DISTINCT item_id) AS total FROM (";
		} else {
			$sql = "SELECT COUNT(DISTINCT item_id) AS total FROM (";
		}
		$sql .= " SELECT a.id AS item_id FROM db_items a WHERE a.store_id = ? AND a.status = 1 AND a.expire_date IS NOT NULL AND a.expire_date <= ?";
		if ($from_date) {
			$sql .= " AND a.expire_date >= ?";
			$params[] = $from_date;
		}
		if ($table_barcodes) {
			$sql .= " UNION SELECT b.item_id FROM db_item_barcodes b JOIN db_items a ON a.id = b.item_id WHERE a.store_id = ? AND a.status = 1 AND b.status = 1 AND b.qty > 0 AND b.expire_date IS NOT NULL AND b.expire_date <= ?";
			$params[] = $store_id;
			$params[] = $to_date;
			if ($from_date) {
				$sql .= " AND b.expire_date >= ?";
				$params[] = $from_date;
			}
		}
		$sql .= ") t";
		$q = $this->db->query($sql, $params);
		return $q ? (int) $q->row()->total : 0;
	}
}
