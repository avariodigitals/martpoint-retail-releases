<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_license_model extends CI_Model {

	private $table = 'db_subscription_license';

	public function get_by_store($store_id = ''){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		if(!$this->db->table_exists($this->table)){ return null; }
		return $this->db->where('store_id', $store_id)->get($this->table)->row();
	}

	public function save($data){
		if(!$this->db->table_exists($this->table)){ return false; }
		$store_id = $data['store_id'] ?? get_current_store_id();
		$existing = $this->db->where('store_id', $store_id)->get($this->table)->row();
		if($existing){
			$data['updated_date'] = date('Y-m-d');
			$data['updated_time'] = date('H:i:s');
			return $this->db->where('id', $existing->id)->update($this->table, $data);
		} else {
			$data['created_date'] = date('Y-m-d');
			$data['created_time'] = date('H:i:s');
			return $this->db->insert($this->table, $data);
		}
	}

	public function activate($store_id, $license_data){
		$license_data['store_id'] = $store_id;
		$license_data['subscription_status'] = 'ACTIVE';
		$license_data['activated_by'] = $this->session->userdata('inv_username');
		return $this->save($license_data);
	}

	public function get_status($store_id = ''){
		$rec = $this->get_by_store($store_id);
		if(!$rec || empty($rec->subscription_end_date)){
			return ['status'=>'NOT_ACTIVATED', 'days_left'=>0, 'end_date'=>null];
		}
		$days_left = $this->days_left($rec->subscription_end_date);
		$status = $rec->subscription_status;
		if($status === 'SUSPENDED'){
			return ['status'=>'SUSPENDED', 'days_left'=>$days_left, 'end_date'=>$rec->subscription_end_date, 'reason'=>$rec->suspension_reason];
		}
		if($days_left <= 0){
			$status = 'EXPIRED';
		} elseif($days_left <= 30){
			$status = 'EXPIRING_SOON';
		} else {
			$status = 'ACTIVE';
		}
		return ['status'=>$status, 'days_left'=>$days_left, 'end_date'=>$rec->subscription_end_date, 'plan'=>$rec->plan_name];
	}

	public function days_left($end_date){
		$end = new DateTime($end_date);
		$today = new DateTime(date('Y-m-d'));
		$diff = $today->diff($end);
		return (int) $diff->format('%r%a');
	}

	public function get_all_licenses(){
		if(!$this->db->table_exists($this->table)){ return []; }
		return $this->db->get($this->table)->result();
	}

	public function update_reminder_flag($store_id, $field, $value){
		if(!$this->db->table_exists($this->table)){ return false; }
		return $this->db->where('store_id', $store_id)->update($this->table, [$field => $value]);
	}

	public function needs_activation($store_id = ''){
		$rec = $this->get_by_store($store_id);
		if(!$rec) return true;
		if(empty($rec->license_code)) return true;
		if(empty($rec->subscription_end_date)) return true;
		return false;
	}

	/* ================= OTP ================= */

	public function generate_otp($store_id, $type = 'generate'){
		$otp = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
		$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
		$this->db->insert('db_license_otps', [
			'store_id' => $store_id,
			'otp_code' => $otp,
			'otp_type' => $type,
			'expires_at' => $expires,
			'used' => 0,
			'created_at' => date('Y-m-d H:i:s')
		]);
		return $otp;
	}

	public function validate_otp($store_id, $otp_code, $type = 'generate'){
		if(!$this->db->table_exists('db_license_otps')){
			return 'OTP table missing. Run database update.';
		}
		$now = date('Y-m-d H:i:s');
		$otp_upper = strtoupper(trim($otp_code));

		// Check all records for this store + code
		$all = $this->db->where('store_id', $store_id)
			->where('otp_code', $otp_upper)
			->order_by('id', 'desc')
			->get('db_license_otps')
			->result();
		if(empty($all)){
			return 'No OTP record found. Please request a new OTP.';
		}

		$rec = $this->db->where('store_id', $store_id)
			->where('otp_code', $otp_upper)
			->where('otp_type', $type)
			->where('used', 0)
			->where('expires_at >=', $now)
			->order_by('id', 'desc')
			->get('db_license_otps')
			->row();
		if(!$rec){
			$latest = $all[0];
			if($latest->otp_type !== $type){
				return "OTP type mismatch (expected {$type}, got {$latest->otp_type})";
			} elseif($latest->used){
				return "OTP already used. Request a new OTP.";
			} elseif($latest->expires_at < $now){
				return "OTP expired at {$latest->expires_at}. Request a new OTP.";
			}
			return "OTP validation failed.";
		}
		$this->db->where('id', $rec->id)->update('db_license_otps', ['used' => 1]);
		return true;
	}

	/* ================= HISTORY ================= */

	public function add_history($store_id, $license_code, $plan_name, $domain, $status = 'active'){
		if(!$this->db->table_exists('db_license_history')) return false;
		return $this->db->insert('db_license_history', [
			'store_id' => $store_id,
			'license_code' => $license_code,
			'plan_name' => $plan_name,
			'domain' => $domain,
			'activated_at' => date('Y-m-d H:i:s'),
			'status' => $status,
			'created_at' => date('Y-m-d H:i:s')
		]);
	}

	public function get_history($store_id = ''){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		if(!$this->db->table_exists('db_license_history')) return [];
		return $this->db->where('store_id', $store_id)->order_by('id', 'desc')->get('db_license_history')->result();
	}

	public function deactivate_license($history_id, $store_id = ''){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		if(!$this->db->table_exists('db_license_history')) return false;
		return $this->db->where('id', $history_id)->where('store_id', $store_id)->update('db_license_history', [
			'status' => 'deactivated',
			'deactivated_at' => date('Y-m-d H:i:s')
		]);
	}

	public function get_active_history_count($store_id = ''){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		if(!$this->db->table_exists('db_license_history')) return 0;
		return $this->db->where('store_id', $store_id)->where('status', 'active')->count_all_results('db_license_history');
	}

	/* ================= DOMAIN LOCK ================= */

	public function is_domain_locked($license_code){
		$decoded = decode_license_key($license_code);
		if($decoded === false) return false;
		if(empty($decoded['domain'])) return true; // legacy keys pass
		$current = $_SERVER['HTTP_HOST'] ?? '';
		return $decoded['domain'] === $current;
	}
}
