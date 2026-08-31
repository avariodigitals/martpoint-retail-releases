<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recipe_category_model extends CI_Model {

	var $table = 'db_recipe_categories';
	var $column_order = array('name','status');
	var $column_search = array('name');
	var $order = array('name' => 'asc');

	private function _get_datatables_query() {
		$this->db->from($this->table);
		$this->db->where("store_id", get_current_store_id());
		$i = 0;
		foreach ($this->column_search as $item) {
			if ($_POST['search']['value']) {
				if ($i === 0) {
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}
				if (count($this->column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}
		if (isset($_POST['order'])) {
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables() {
		$this->_get_datatables_query();
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered() {
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all() {
		$this->db->where("store_id", get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function verify_and_save() {
		$name = trim($this->input->post('name', TRUE));
		$store_id = get_current_store_id();
		$this->db->where("UPPER(name)", strtoupper($name));
		$this->db->where('store_id', $store_id);
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0) {
			return "This Category Name already exists.";
		}
		$info = [
			'store_id' => $store_id,
			'name' => $name,
			'status' => 1,
		];
		$q1 = $this->db->insert($this->table, $info);
		if ($q1) {
			$this->session->set_flashdata('success', 'Recipe Category Added Successfully!');
			return "success";
		}
		return "failed";
	}

	public function get_details($id, $data) {
		$query = $this->db->query("SELECT * FROM {$this->table} WHERE id = " . (int)$id);
		if ($query->num_rows() == 0) {
			show_404();
			exit;
		}
		$res = $query->row();
		$data['q_id'] = $res->id;
		$data['category_name'] = $res->name;
		$data['status'] = $res->status;
		return $data;
	}

	public function update_category() {
		$q_id = (int)$this->input->post('q_id', TRUE);
		$name = trim($this->input->post('name', TRUE));
		$store_id = get_current_store_id();
		$this->db->where("UPPER(name)", strtoupper($name));
		$this->db->where("id !=", $q_id);
		$this->db->where('store_id', $store_id);
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0) {
			return "This Category Name already exists.";
		}
		$q1 = $this->db->where('id', $q_id)->update($this->table, ['name' => $name]);
		if ($q1) {
			$this->session->set_flashdata('success', 'Recipe Category Updated Successfully!');
			return "success";
		}
		return "failed";
	}

	public function update_status($id, $status) {
		if (set_status_of_table($id, $status, $this->table)) {
			echo "success";
		} else {
			echo "failed";
		}
	}

	public function delete_categories_from_table($ids) {
		$this->db->where("id in ($ids)");
		if (!is_admin()) {
			$this->db->where("store_id", get_current_store_id());
		}
		$query1 = $this->db->delete($this->table);
		if ($query1) {
			echo "success";
		} else {
			echo "failed";
		}
	}

	public function get_active($store_id = null) {
		$store_id = $store_id ?: get_current_store_id();
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('name', 'asc');
		return $this->db->get($this->table)->result();
	}
}
