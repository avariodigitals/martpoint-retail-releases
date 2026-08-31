<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attributes_model extends CI_Model {

	var $table = 'db_attributes as a';
	var $column_order = array('a.id','a.attribute_type','a.attribute_value','a.sort_order','a.status');
	var $column_search = array('a.attribute_type','a.attribute_value');
	var $order = array('a.attribute_type' => 'asc', 'a.sort_order' => 'asc', 'a.attribute_value' => 'asc');

	public function __construct(){
		parent::__construct();
	}

	private function _get_datatables_query(){
		$this->db->select('a.*');
		$this->db->from($this->table);
		$this->db->where('a.store_id', get_current_store_id());

		$i = 0;
		foreach($this->column_search as $item){
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])){
				if($i===0){
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}
				if(count($this->column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])){
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if(isset($this->order)){
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables(){
		$this->_get_datatables_query();
		if(isset($_POST['length']) && $_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		return $this->db->get()->result();
	}

	function count_filtered(){
		$this->_get_datatables_query();
		return $this->db->get()->num_rows();
	}

	public function count_all(){
		$this->db->where('store_id', get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function verify_and_save(){
		$store_id = get_current_store_id();
		$attribute_type = strtolower(trim($this->input->post('attribute_type', TRUE)));
		$attribute_value = trim($this->input->post('attribute_value', TRUE));
		$sort_order = (int)$this->input->post('sort_order', TRUE);

		if(empty($attribute_type) || empty($attribute_value)){
			return "Attribute type and value are required.";
		}

		// Check if this type/value already exists
		$this->db->where('store_id', $store_id);
		$this->db->where('attribute_type', $attribute_type);
		$this->db->where('attribute_value', $attribute_value);
		$query = $this->db->get('db_attributes');
		if($query->num_rows() > 0){
			return "This attribute value already exists.";
		}

		$info = array(
			'store_id' => $store_id,
			'attribute_type' => $attribute_type,
			'attribute_value' => $attribute_value,
			'sort_order' => $sort_order,
			'status' => 1,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username'),
		);

		$q1 = $this->db->insert('db_attributes', $info);
		if($q1){
			$this->session->set_flashdata('success', 'Success! Attribute added.');
			return "success";
		}
		return "failed";
	}

	public function update_attribute(){
		$q_id = (int)$this->input->post('q_id', TRUE);
		$store_id = get_current_store_id();
		$attribute_type = strtolower(trim($this->input->post('attribute_type', TRUE)));
		$attribute_value = trim($this->input->post('attribute_value', TRUE));
		$sort_order = (int)$this->input->post('sort_order', TRUE);

		if(empty($attribute_type) || empty($attribute_value)){
			return "Attribute type and value are required.";
		}

		$this->db->where('store_id', $store_id);
		$this->db->where('attribute_type', $attribute_type);
		$this->db->where('attribute_value', $attribute_value);
		$this->db->where('id !=', $q_id);
		$query = $this->db->get('db_attributes');
		if($query->num_rows() > 0){
			return "This attribute value already exists.";
		}

		$info = array(
			'attribute_type' => $attribute_type,
			'attribute_value' => $attribute_value,
			'sort_order' => $sort_order,
		);

		$q1 = $this->db->where('id', $q_id)->where('store_id', $store_id)->update('db_attributes', $info);
		if($q1){
			$this->session->set_flashdata('success', 'Success! Attribute updated.');
			return "success";
		}
		return "failed";
	}

	public function delete_attribute($id){
		$store_id = get_current_store_id();
		$this->db->where('id', $id)->where('store_id', $store_id)->delete('db_attributes');
		return "success";
	}

	public function get_details($id){
		$store_id = get_current_store_id();
		$q = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_attributes');
		if($q->num_rows() == 0) return false;
		return $q->row();
	}

	/**
	 * Get all attribute types and their values for this store.
	 * Returns array keyed by attribute_type.
	 */
	public function get_attribute_map($store_id = null){
		if(empty($store_id)) $store_id = get_current_store_id();
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('attribute_type','asc');
		$this->db->order_by('sort_order','asc');
		$this->db->order_by('attribute_value','asc');
		$q = $this->db->get('db_attributes');
		$map = array();
		foreach($q->result() as $r){
			$type = $r->attribute_type;
			if(!isset($map[$type])) $map[$type] = array();
			$map[$type][] = $r->attribute_value;
		}
		return $map;
	}

	/**
	 * Get distinct attribute types for a store (for dropdowns).
	 */
	public function get_attribute_types($store_id = null){
		if(empty($store_id)) $store_id = get_current_store_id();
		$this->db->select('attribute_type, MIN(sort_order) as so');
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->group_by('attribute_type');
		$this->db->order_by('so','asc');
		$this->db->order_by('attribute_type','asc');
		$q = $this->db->get('db_attributes');
		$types = array();
		foreach($q->result() as $r){
			$types[] = $r->attribute_type;
		}
		return $types;
	}
}
