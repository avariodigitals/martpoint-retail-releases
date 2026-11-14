<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tills_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function get_all($store_id = null, $status = null){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		$this->db->select("t.*, a.account_name, COALESCE(a.balance, 0) as balance, u.username, u.first_name, u.last_name");
		$this->db->from("db_tills t");
		$this->db->join("ac_accounts a", "a.id = t.account_id", "left");
		$this->db->join("db_users u", "u.id = t.cashier_user_id", "left");
		$this->db->where("t.store_id", $store_id);
		if($status !== null){ $this->db->where("t.status", $status); }
		$this->db->order_by("t.till_name", "asc");
		return $this->db->get()->result();
	}

	public function get_by_cashier($cashier_user_id, $store_id = null){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		return $this->db->where("store_id", $store_id)
			->where("cashier_user_id", $cashier_user_id)
			->where("status", 1)
			->order_by("is_default", "desc")
			->order_by("id", "asc")
			->get("db_tills")
			->result();
	}

	public function get($id, $store_id = null){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		return $this->db->where("id", $id)->where("store_id", $store_id)->get("db_tills")->row();
	}

	public function save($data, $id = null){
		$store_id = get_current_store_id();
		$till_name = trim($data['till_name']);
		$cashier_user_id = !empty($data['cashier_user_id']) ? intval($data['cashier_user_id']) : null;

		// Create an account if one isn't provided
		if(!empty($data['account_id'])){
			$account_id = intval($data['account_id']);
		} else {
			$account_id = ensure_till_account($till_name, $store_id, $cashier_user_id);
		}

		$insert = array(
			'store_id'        => $store_id,
			'till_name'       => $till_name,
			'cashier_user_id' => $cashier_user_id,
			'account_id'      => $account_id,
			'is_default'      => !empty($data['is_default']) ? 1 : 0,
			'status'          => 1,
		);

		if($id){
			$this->db->where("id", $id)->where("store_id", $store_id)->update("db_tills", $insert);
			return $id;
		} else {
			$this->db->insert("db_tills", $insert);
			return $this->db->insert_id();
		}
	}

	public function delete($id, $store_id = null){
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		return $this->db->where("id", $id)->where("store_id", $store_id)->update("db_tills", array('status' => 0));
	}

	public function active_tills_list($store_id = null){
		$tills = $this->get_all($store_id, 1);
		$options = '';
		foreach($tills as $t){
			$label = htmlspecialchars($t->till_name);
			if($t->first_name || $t->last_name){
				$label .= ' — ' . htmlspecialchars(trim($t->first_name.' '.$t->last_name));
			} else if($t->username){
				$label .= ' — ' . htmlspecialchars($t->username);
			}
			if($t->is_default) $label .= ' (Default)';
			$options .= '<option value="'.intval($t->id).'">'.$label.'</option>';
		}
		return $options;
	}
}
