<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_plans_model extends CI_Model {

	private $table = 'db_subscription_plans';

	public function get_all($active_only = false){
		if(!$this->db->table_exists($this->table)){ return []; }
		if($active_only){
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('display_order', 'asc')->get($this->table)->result();
	}

	public function get_by_id($id){
		if(!$this->db->table_exists($this->table)){ return null; }
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function get_by_code($code){
		if(!$this->db->table_exists($this->table)){ return null; }
		return $this->db->where('plan_code', $code)->where('is_active', 1)->get($this->table)->row();
	}

	public function save($data, $id = null){
		if(!$this->db->table_exists($this->table)){ return false; }
		if($id){
			return $this->db->where('id', $id)->update($this->table, $data);
		} else {
			$data['created_date'] = date('Y-m-d');
			$data['created_time'] = date('H:i:s');
			return $this->db->insert($this->table, $data);
		}
	}

	public function delete($id){
		if(!$this->db->table_exists($this->table)){ return false; }
		return $this->db->where('id', $id)->delete($this->table);
	}
}
