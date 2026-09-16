<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expiry_settings_model extends CI_Model {

	public function get_settings($store_id = null) {
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$row = $this->db->where('store_id', $store_id)->get('db_expiry_settings')->row();
		if(!$row) {
			// Return defaults
			$row = new stdClass();
			$row->alert_before_days = 30;
			$row->stop_selling_expired = 1;
			$row->email_alerts_enabled = 0;
			$row->alert_email = '';
		}
		return $row;
	}

	public function save_settings() {
		$alert_before_days = $this->input->post('alert_before_days', TRUE);
		$stop_selling_expired = $this->input->post('stop_selling_expired', TRUE);
		$email_alerts_enabled = $this->input->post('email_alerts_enabled', TRUE);
		$alert_email = $this->input->post('alert_email', TRUE);
		$store_id = get_current_store_id();

		$info = array(
			'store_id' => $store_id,
			'alert_before_days' => $alert_before_days,
			'stop_selling_expired' => $stop_selling_expired,
			'email_alerts_enabled' => $email_alerts_enabled,
			'alert_email' => $alert_email,
		);

		$exists = $this->db->where('store_id', $store_id)->get('db_expiry_settings')->num_rows();
		if($exists > 0) {
			$this->db->where('store_id', $store_id);
			$q1 = $this->db->update('db_expiry_settings', $info);
		} else {
			$q1 = $this->db->insert('db_expiry_settings', $info);
		}

		return ($q1) ? "success" : "failed";
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
