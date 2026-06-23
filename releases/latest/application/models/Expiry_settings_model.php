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
		return count($this->get_expiring_items($store_id));
	}

	public function count_expired($store_id = null) {
		return count($this->get_expired_items($store_id));
	}
}
