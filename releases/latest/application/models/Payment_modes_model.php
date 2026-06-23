<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_modes_model extends CI_Model {

	var $table = 'db_payment_modes';
	var $column_order = array('name','code','enabled','is_default','sort_order');
	var $column_search = array('name','code','description');
	var $order = array('sort_order' => 'asc');

	private function _get_datatables_query()
	{
		$this->db->from($this->table);
		$this->db->where("store_id", get_current_store_id());

		$i = 0;
		foreach ($this->column_search as $item) {
			if($_POST['search']['value']) {
				if($i===0) {
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

		if(isset($_POST['order'])) {
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if(isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		$this->db->where("store_id", get_current_store_id());
		return $this->db->count_all_results();
	}

	public function get_details($id, $data)
	{
		$data = array_merge($data, array('id' => $id));
		$data = array_merge($data, $this->db->where('id', $id)->get('db_payment_modes')->row_array());
		return $data;
	}

	public function verify_and_save()
	{
		$code = $this->input->post('code', TRUE);
		$name = $this->input->post('name', TRUE);
		$description = $this->input->post('description', TRUE);
		$enabled = $this->input->post('enabled', TRUE);
		$is_default = $this->input->post('is_default', TRUE);
		$sort_order = $this->input->post('sort_order', TRUE);
		$requires_reference = $this->input->post('requires_reference', TRUE);
		$requires_confirmation = $this->input->post('requires_confirmation', TRUE);
		$affects_cash_in_hand = $this->input->post('affects_cash_in_hand', TRUE);
		$icon_class = $this->input->post('icon_class', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];

		$store_id = get_current_store_id();

		// Check for duplicate code within store
		$exists = $this->db->where('store_id', $store_id)->where('code', $code)->get($this->table)->num_rows();
		if($exists > 0) {
			return "This payment mode code already exists.";
		}

		$info = array(
			'store_id' => $store_id,
			'code' => $code,
			'name' => $name,
			'description' => $description ?? '',
			'enabled' => $enabled ?? 1,
			'is_default' => $is_default ?? 0,
			'is_system' => 0,
			'sort_order' => $sort_order ?? 0,
			'requires_reference' => $requires_reference ?? 0,
			'requires_confirmation' => $requires_confirmation ?? 0,
			'affects_cash_in_hand' => $affects_cash_in_hand ?? 0,
			'icon_class' => $icon_class ?? '',
			'created_date' => $CUR_DATE,
			'created_time' => $CUR_TIME,
			'created_by' => $CUR_USERNAME,
			'system_ip' => $SYSTEM_IP,
			'system_name' => $SYSTEM_NAME,
			'status' => 1
		);

		// If this is set as default, unset any existing default
		if($info['is_default'] == 1) {
			$this->db->where('store_id', $store_id)->update($this->table, array('is_default' => 0));
		}

		if($this->db->insert($this->table, $info)) {
			return "success";
		} else {
			return "failed";
		}
	}

	public function update_payment_mode()
	{
		$q_id = $this->input->post('q_id', TRUE);
		$code = $this->input->post('code', TRUE);
		$name = $this->input->post('name', TRUE);
		$description = $this->input->post('description', TRUE);
		$enabled = $this->input->post('enabled', TRUE);
		$is_default = $this->input->post('is_default', TRUE);
		$sort_order = $this->input->post('sort_order', TRUE);
		$requires_reference = $this->input->post('requires_reference', TRUE);
		$requires_confirmation = $this->input->post('requires_confirmation', TRUE);
		$affects_cash_in_hand = $this->input->post('affects_cash_in_hand', TRUE);
		$icon_class = $this->input->post('icon_class', TRUE);
		$CUR_DATE = $this->data['CUR_DATE'];
		$CUR_TIME = $this->data['CUR_TIME'];
		$CUR_USERNAME = $this->data['CUR_USERNAME'];
		$SYSTEM_IP = $this->data['SYSTEM_IP'];
		$SYSTEM_NAME = $this->data['SYSTEM_NAME'];

		$store_id = get_current_store_id();

		// Check for duplicate code (excluding current record)
		$exists = $this->db->where('store_id', $store_id)->where('code', $code)->where('id !=', $q_id)->get($this->table)->num_rows();
		if($exists > 0) {
			return "This payment mode code already exists.";
		}

		$info = array(
			'code' => $code,
			'name' => $name,
			'description' => $description ?? '',
			'enabled' => $enabled ?? 1,
			'is_default' => $is_default ?? 0,
			'sort_order' => $sort_order ?? 0,
			'requires_reference' => $requires_reference ?? 0,
			'requires_confirmation' => $requires_confirmation ?? 0,
			'affects_cash_in_hand' => $affects_cash_in_hand ?? 0,
			'icon_class' => $icon_class ?? '',
		);

		// If this is set as default, unset any existing default
		if($info['is_default'] == 1) {
			$this->db->where('store_id', $store_id)->update($this->table, array('is_default' => 0));
		}

		$this->db->where('id', $q_id);
		if($this->db->update($this->table, $info)) {
			return "success";
		} else {
			return "failed";
		}
	}

	public function update_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->where('store_id', get_current_store_id());
		$q1 = $this->db->update($this->table, array('enabled' => $status, 'status' => $status));
		return ($q1) ? "success" : "failed";
	}

	public function delete_payment_mode($id)
	{
		$this->db->where('id', $id);
		$this->db->where('store_id', get_current_store_id());
		$q1 = $this->db->delete($this->table);
		return ($q1) ? "success" : "failed";
	}

	public function get_enabled_modes($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)
						->where('enabled', 1)
						->where('status', 1)
						->order_by('sort_order', 'asc')
						->get($this->table)
						->result();
	}

	public function get_default_mode($store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		$row = $this->db->where('store_id', $store_id)
						->where('enabled', 1)
						->where('is_default', 1)
						->where('status', 1)
						->get($this->table)
						->row();
		return $row;
	}

	public function get_mode_by_code($code, $store_id = null)
	{
		if(empty($store_id)) { $store_id = get_current_store_id(); }
		return $this->db->where('store_id', $store_id)
						->where('code', $code)
						->get($this->table)
						->row();
	}

	public function get_mode_by_id($id)
	{
		return $this->db->where('id', $id)->get($this->table)->row();
	}

	public function seed_defaults($store_id)
	{
		$defaults = array(
			array('code'=>'cash','name'=>'Cash','enabled'=>1,'is_default'=>1,'sort_order'=>1,'requires_reference'=>0,'requires_confirmation'=>0,'affects_cash_in_hand'=>1,'icon_class'=>'fa-money'),
			array('code'=>'pos','name'=>'POS','enabled'=>1,'is_default'=>0,'sort_order'=>2,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-credit-card'),
			array('code'=>'bank_transfer','name'=>'Bank Transfer','enabled'=>1,'is_default'=>0,'sort_order'=>3,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-university'),
			array('code'=>'card','name'=>'Card','enabled'=>1,'is_default'=>0,'sort_order'=>4,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-credit-card-alt'),
			array('code'=>'paystack','name'=>'Paystack','enabled'=>1,'is_default'=>0,'sort_order'=>5,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-link'),
			array('code'=>'credit_sale','name'=>'Credit Sale','enabled'=>1,'is_default'=>0,'sort_order'=>6,'requires_reference'=>0,'requires_confirmation'=>0,'affects_cash_in_hand'=>0,'icon_class'=>'fa-handshake-o'),
			array('code'=>'split_payment','name'=>'Split Payment','enabled'=>1,'is_default'=>0,'sort_order'=>7,'requires_reference'=>0,'requires_confirmation'=>0,'affects_cash_in_hand'=>0,'icon_class'=>'fa-columns'),
			array('code'=>'wallet','name'=>'Wallet','enabled'=>0,'is_default'=>0,'sort_order'=>8,'requires_reference'=>0,'requires_confirmation'=>0,'affects_cash_in_hand'=>0,'icon_class'=>'fa-google-wallet'),
			array('code'=>'cheque','name'=>'Cheque','enabled'=>0,'is_default'=>0,'sort_order'=>9,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-file-text-o'),
			array('code'=>'other','name'=>'Other','enabled'=>0,'is_default'=>0,'sort_order'=>10,'requires_reference'=>0,'requires_confirmation'=>0,'affects_cash_in_hand'=>0,'icon_class'=>'fa-ellipsis-h'),
			array('code'=>'flutterwave','name'=>'Flutterwave','enabled'=>1,'is_default'=>0,'sort_order'=>11,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-link'),
			array('code'=>'moniepoint','name'=>'Moniepoint','enabled'=>1,'is_default'=>0,'sort_order'=>12,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-mobile'),
			array('code'=>'opay','name'=>'Opay','enabled'=>1,'is_default'=>0,'sort_order'=>13,'requires_reference'=>1,'requires_confirmation'=>1,'affects_cash_in_hand'=>0,'icon_class'=>'fa-mobile'),
		);

		foreach($defaults as $mode) {
			$exists = $this->db->where('store_id', $store_id)->where('code', $mode['code'])->get($this->table)->num_rows();
			if($exists == 0) {
				$mode['store_id'] = $store_id;
				$mode['is_system'] = 1;
				$mode['created_date'] = date('Y-m-d');
				$mode['created_time'] = date('H:i:s');
				$mode['created_by'] = 'system';
				$mode['status'] = 1;
				$this->db->insert($this->table, $mode);
			}
		}
	}
}
