<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Equipment register + service jobs (Scientific Equipment & Lab Supplies industry).
 * Equipment rows are auto-created on serialised sales by
 * mp_register_equipment_from_sale() — this model serves the register UI,
 * service-job lifecycle and calibration scheduling.
 */
class Equipment_model extends CI_Model {

	private $JOB_TYPES = array('installation','commissioning','calibration','maintenance','repair','inspection');
	private $JOB_STATUSES = array('open','scheduled','in_progress','awaiting_parts','completed','cancelled');

	public function job_types(){ return $this->JOB_TYPES; }
	public function job_statuses(){ return $this->JOB_STATUSES; }

	/* ---------------- EQUIPMENT REGISTER ---------------- */

	public function get_equipment_list($filters = array()){
		$store_id = get_current_store_id();
		$this->db->select("e.*, c.customer_name, i.item_name, COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name, sa.address AS site_address, sa.city AS site_city,
			s.sales_code,
			(SELECT COUNT(*) FROM db_service_jobs j WHERE j.equipment_id = e.id) AS job_count", FALSE);
		$this->db->from('db_customer_equipment e');
		$this->db->join('db_customers c', 'c.id = e.customer_id', 'left');
		$this->db->join('db_items i', 'i.id = e.item_id', 'left');
		$this->db->join('db_shippingaddress sa', 'sa.id = e.site_id', 'left');
		$this->db->join('db_sales s', 's.id = e.sales_id', 'left');
		$this->db->where('e.store_id', $store_id);
		if(!empty($filters['customer_id'])){ $this->db->where('e.customer_id', $filters['customer_id']); }
		if(!empty($filters['search'])){
			$term = $this->db->escape_like_str($filters['search']);
			$this->db->group_start()
				->like('e.serial_number', $term)
				->or_like('i.item_name', $term)
				->or_like('e.model', $term)
				->or_like('c.customer_name', $term)
			->group_end();
		}
		if(!empty($filters['warranty_expiring_days'])){
			$days = (int)$filters['warranty_expiring_days'];
			$this->db->where('e.warranty_end IS NOT NULL');
			$this->db->where('e.warranty_end >=', date('Y-m-d'));
			$this->db->where('e.warranty_end <=', date('Y-m-d', strtotime("+$days days")));
		}
		if(!empty($filters['calibration_due'])){
			$this->db->where('e.next_calibration_date IS NOT NULL');
			$this->db->where('e.next_calibration_date <=', date('Y-m-d', strtotime('+30 days')));
		}
		$this->db->order_by('e.id', 'DESC');
		if(!empty($filters['limit'])){ $this->db->limit((int)$filters['limit']); }
		return $this->db->get()->result();
	}

	public function get_equipment($id){
		$eq = $this->db->select("e.*, c.customer_name, c.mobile AS customer_phone, c.email AS customer_email,
				i.item_name, i.warranty_months AS item_warranty_months,
				COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name, sa.address AS site_address, sa.city AS site_city,
				s.sales_code, s.sales_date", FALSE)
			->from('db_customer_equipment e')
			->join('db_customers c', 'c.id = e.customer_id', 'left')
			->join('db_items i', 'i.id = e.item_id', 'left')
			->join('db_shippingaddress sa', 'sa.id = e.site_id', 'left')
			->join('db_sales s', 's.id = e.sales_id', 'left')
			->where('e.id', (int)$id)
			->get()->row();
		return $eq;
	}

	/* Editable register fields — serial/model/sale linkage stay immutable. */
	public function save_equipment(){
		$id = (int)$this->input->post('equipment_id');
		$eq = $this->db->where('id', $id)->where('store_id', get_current_store_id())->get('db_customer_equipment')->row();
		if(!$eq){ return "Equipment record not found."; }

		// Only write columns that were actually posted — a missing key keeps the
		// existing value; a posted-but-empty field clears it deliberately.
		$data = array();
		if($this->input->post('site_id') !== null){
			$data['site_id'] = ((int)$this->input->post('site_id')) ?: null;
		}
		foreach(array('install_date','commissioned_date','warranty_start','warranty_end','next_calibration_date') as $col){
			$v = $this->input->post($col);
			if($v === null){ continue; }
			$v = trim((string)$v);
			$data[$col] = $v !== '' ? system_fromatted_date($v) : null;
		}
		if($this->input->post('coverage_type') !== null){
			$data['coverage_type'] = mp_clean_text($this->input->post('coverage_type'));
		}
		if($this->input->post('equipment_status') !== null){
			$data['equipment_status'] = mp_clean_text($this->input->post('equipment_status'));
		}
		if($this->input->post('calibration_interval_months') !== null){
			$data['calibration_interval_months'] = ((int)$this->input->post('calibration_interval_months')) ?: null;
		}
		if($this->input->post('notes') !== null){
			$data['notes'] = mp_clean_text($this->input->post('notes'));
		}
		if(!empty($data)){
			$this->db->where('id', $id)->update('db_customer_equipment', $data);
		}
		return 'success';
	}

	/* ---------------- SERVICE JOBS ---------------- */

	private function _next_job_code(){
		$store_id = get_current_store_id();
		$count = $this->db->where('store_id', $store_id)->count_all_results('db_service_jobs') + 1;
		return 'JOB-' . str_pad($count, 5, '0', STR_PAD_LEFT);
	}

	public function save_job(){
		$store_id = get_current_store_id();
		$job_id = (int)$this->input->post('job_id');
		$job_type = mp_clean_text($this->input->post('job_type'));
		if(!in_array($job_type, $this->JOB_TYPES)){ $job_type = 'maintenance'; }

		$data = array(
			'customer_id'      => (int)$this->input->post('customer_id'),
			'site_id'          => ((int)$this->input->post('site_id')) ?: null,
			'equipment_id'     => ((int)$this->input->post('equipment_id')) ?: null,
			'sales_id'         => ((int)$this->input->post('sales_id')) ?: null,
			'job_type'         => $job_type,
			'priority'         => mp_clean_text($this->input->post('priority')) ?: 'normal',
			'status'           => mp_clean_text($this->input->post('status')) ?: 'open',
			'assigned_user_id' => ((int)$this->input->post('assigned_user_id')) ?: null,
			'scheduled_date'   => ($d = trim((string)$this->input->post('scheduled_date'))) ? system_fromatted_date($d) : null,
			'scheduled_time'   => mp_clean_text($this->input->post('scheduled_time')),
			'title'            => mp_clean_text($this->input->post('title')),
			'description'      => mp_clean_text($this->input->post('description')),
			'labour_charge'    => (float)str_replace(',', '', (string)$this->input->post('labour_charge')),
		);
		if(empty($data['customer_id'])){ return "A customer is required."; }
		if(empty($data['title'])){ $data['title'] = ucfirst($job_type).' job'; }

		if($job_id > 0){
			$this->db->where('id', $job_id)->where('store_id', $store_id)->update('db_service_jobs', $data);
			return 'success<<<###>>>'.$job_id;
		}
		$data['store_id'] = $store_id;
		$data['job_code'] = $this->_next_job_code();
		$data['count_id'] = (int)$this->db->select_max('count_id')->get('db_service_jobs')->row()->count_id + 1;
		$data['created_by'] = $this->session->userdata('inv_userid');
		$data['created_date'] = date('Y-m-d');
		$data['created_time'] = date('H:i:s');
		$this->db->insert('db_service_jobs', $data);
		$new_id = $this->db->insert_id();
		if(!$new_id){ return 'failed'; }
		return 'success<<<###>>>'.$new_id;
	}

	public function get_job_list($filters = array()){
		$store_id = get_current_store_id();
		$this->db->select("j.*, c.customer_name, u.username AS engineer, COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name,
			e.serial_number AS equipment_serial, i.item_name AS equipment_item", FALSE);
		$this->db->from('db_service_jobs j');
		$this->db->join('db_customers c', 'c.id = j.customer_id', 'left');
		$this->db->join('db_users u', 'u.id = j.assigned_user_id', 'left');
		$this->db->join('db_shippingaddress sa', 'sa.id = j.site_id', 'left');
		$this->db->join('db_customer_equipment e', 'e.id = j.equipment_id', 'left');
		$this->db->join('db_items i', 'i.id = e.item_id', 'left');
		$this->db->where('j.store_id', $store_id);
		if(!empty($filters['customer_id'])){ $this->db->where('j.customer_id', $filters['customer_id']); }
		if(!empty($filters['equipment_id'])){ $this->db->where('j.equipment_id', $filters['equipment_id']); }
		if(!empty($filters['status'])){ $this->db->where('j.status', $filters['status']); }
		if(!empty($filters['open_only'])){ $this->db->where_not_in('j.status', array('completed','cancelled')); }
		$this->db->order_by('j.id', 'DESC');
		return $this->db->get()->result();
	}

	public function get_job($id){
		$job = $this->db->select("j.*, c.customer_name, u.username AS engineer, COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name, sa.address AS site_address,
				e.serial_number AS equipment_serial, e.model AS equipment_model, i.item_name AS equipment_item,
				s.sales_code", FALSE)
			->from('db_service_jobs j')
			->join('db_customers c', 'c.id = j.customer_id', 'left')
			->join('db_users u', 'u.id = j.assigned_user_id', 'left')
			->join('db_shippingaddress sa', 'sa.id = j.site_id', 'left')
			->join('db_customer_equipment e', 'e.id = j.equipment_id', 'left')
			->join('db_items i', 'i.id = e.item_id', 'left')
			->join('db_sales s', 's.id = j.sales_id', 'left')
			->where('j.id', (int)$id)->where('j.store_id', get_current_store_id())
			->get()->row();
		return $job;
	}

	public function get_job_visits($job_id){
		return $this->db->select('v.*, u.username AS engineer_name')
			->from('db_service_job_visits v')
			->join('db_users u', 'u.id = v.engineer_id', 'left')
			->where('v.job_id', (int)$job_id)
			->order_by('v.id', 'DESC')->get()->result();
	}

	public function get_job_parts($job_id){
		return $this->db->select('p.*, i.item_name, b.serial_number')
			->from('db_service_job_items p')
			->join('db_items i', 'i.id = p.item_id', 'left')
			->join('db_item_barcodes b', 'b.id = p.barcode_id', 'left')
			->where('p.job_id', (int)$job_id)
			->order_by('p.id', 'ASC')->get()->result();
	}

	public function save_visit(){
		$job_id = (int)$this->input->post('job_id');
		$job = $this->db->where('id', $job_id)->where('store_id', get_current_store_id())->get('db_service_jobs')->row();
		if(!$job){ return "Service job not found."; }
		$notes = mp_clean_text($this->input->post('visit_notes'));
		if(empty($notes)){ return "Visit notes are required."; }

		$this->db->insert('db_service_job_visits', array(
			'store_id'   => get_current_store_id(),
			'job_id'     => $job_id,
			'visit_date' => ($d = trim((string)$this->input->post('visit_date'))) ? system_fromatted_date($d) : date('Y-m-d'),
			'engineer_id'=> ((int)$this->input->post('engineer_id')) ?: ($job->assigned_user_id ?: $this->session->userdata('inv_userid')),
			'notes'      => $notes,
			'outcome'    => mp_clean_text($this->input->post('outcome')),
			'created_by' => $this->session->userdata('inv_userid'),
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
		));
		if($job->status === 'open' || $job->status === 'scheduled'){
			$this->db->where('id', $job_id)->update('db_service_jobs', array('status' => 'in_progress', 'started_date' => date('Y-m-d')));
		}
		return 'success';
	}

	/**
	 * Record a spare part consumed by a job. Stock is deducted through a real
	 * stock-adjustment document (negative qty) so the ledger stays consistent
	 * with update_items_quantity()'s recalculation.
	 */
	public function save_part(){
		$job_id = (int)$this->input->post('job_id');
		$item_id = (int)$this->input->post('item_id');
		$qty = (float)str_replace(',', '', (string)$this->input->post('qty'));
		$barcode_id = (int)$this->input->post('barcode_id');
		$price = (float)str_replace(',', '', (string)$this->input->post('price_per_unit'));
		$job = $this->db->where('id', $job_id)->where('store_id', get_current_store_id())->get('db_service_jobs')->row();
		if(!$job){ return "Service job not found."; }
		if($item_id <= 0 || $qty <= 0){ return "Item and quantity are required."; }

		$item = $this->db->where('id', $item_id)->get('db_items')->row();
		if(!$item){ return "Item not found."; }
		$warehouse_id = get_store_warehouse_id();

		// Guard against negative stock for physical items
		if((int)$item->service_bit !== 1){
			$available = total_available_qty_items_of_warehouse($warehouse_id, null, $item_id);
			if($available < $qty){
				return $item->item_name." has only ".$available." in stock.";
			}
		}

		if($price <= 0){ $price = (float)$item->sales_price; }

		$this->db->trans_begin();

		$this->db->insert('db_service_job_items', array(
			'store_id' => get_current_store_id(),
			'job_id' => $job_id,
			'item_id' => $item_id,
			'barcode_id' => $barcode_id ?: null,
			'qty' => $qty,
			'price_per_unit' => $price,
			'total' => $qty * $price,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_date' => date('Y-m-d'),
		));

		// Serialised part consumed: mark the physical unit used (not resalable)
		if($barcode_id > 0){
			$this->db->where('id', $barcode_id)->update('db_item_barcodes', array('status' => 0));
		}

		// Ledger-consistent stock deduction via a stock adjustment document
		$adj = array(
			'store_id' => get_current_store_id(),
			'warehouse_id' => $warehouse_id,
			'reference_no' => $job->job_code,
			'adjustment_date' => date('Y-m-d'),
			'adjustment_note' => 'Parts used on service job '.$job->job_code,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => $this->session->userdata('inv_username'),
			'status' => 1,
		);
		$this->db->insert('db_stockadjustment', $adj);
		$adjustment_id = $this->db->insert_id();
		$this->db->insert('db_stockadjustmentitems', array(
			'store_id' => get_current_store_id(),
			'warehouse_id' => $warehouse_id,
			'adjustment_id' => $adjustment_id,
			'item_id' => $item_id,
			'adjustment_qty' => -1 * $qty,
			'description' => 'Service job '.$job->job_code,
			'status' => 1,
		));

		$this->load->model('pos_model');
		$this->pos_model->update_items_quantity($item_id);
		if(function_exists('update_warehouse_items')){ update_warehouse_items(array(array('item_id' => $item_id))); }

		// Keep parts_total current
		$this->db->query("UPDATE db_service_jobs j SET j.parts_total = (SELECT COALESCE(SUM(total),0) FROM db_service_job_items WHERE job_id = j.id) WHERE j.id = ?", array($job_id));

		if($this->db->trans_status() === FALSE){ $this->db->trans_rollback(); return 'failed'; }
		$this->db->trans_commit();
		return 'success';
	}

	public function update_job_status(){
		$job_id = (int)$this->input->post('job_id');
		$status = mp_clean_text($this->input->post('status'));
		if(!in_array($status, $this->JOB_STATUSES)){ return 'Invalid status.'; }
		$job = $this->db->where('id', $job_id)->where('store_id', get_current_store_id())->get('db_service_jobs')->row();
		if(!$job){ return "Service job not found."; }

		$data = array('status' => $status);
		if($status === 'in_progress' && empty($job->started_date)){ $data['started_date'] = date('Y-m-d'); }
		if($status === 'completed'){
			$data['completed_date'] = date('Y-m-d');
			$resolution = mp_clean_text($this->input->post('resolution_notes'));
			if(!empty($resolution)){ $data['resolution_notes'] = $resolution; }
		}
		$this->db->where('id', $job_id)->update('db_service_jobs', $data);

		// Push outcomes back onto the equipment register
		if($job->equipment_id && $status === 'completed'){
			$eq_data = array();
			if($job->job_type === 'installation' || $job->job_type === 'commissioning'){
				$eq_data['install_date'] = $job->job_type === 'installation' ? date('Y-m-d') : null;
				if($job->job_type === 'commissioning'){ $eq_data['commissioned_date'] = date('Y-m-d'); $eq_data['install_date'] = null; }
				$eq_data['equipment_status'] = 'installed';
				if($eq_data['install_date'] === null){ unset($eq_data['install_date']); }
			}
			if($job->job_type === 'calibration'){
				$eq = $this->db->where('id', $job->equipment_id)->get('db_customer_equipment')->row();
				$interval = (int)($eq->calibration_interval_months ?? 12);
				if($interval <= 0){ $interval = 12; }
				$eq_data['last_calibration_date'] = date('Y-m-d');
				$eq_data['next_calibration_date'] = date('Y-m-d', strtotime("+$interval months"));
			}
			if($job->job_type === 'repair'){
				$eq_data['equipment_status'] = 'operational';
			}
			if(!empty($eq_data)){
				$this->db->where('id', $job->equipment_id)->update('db_customer_equipment', $eq_data);
			}
		}
		return 'success';
	}

	/** One-click "schedule calibration" from the register: open job + due date. */
	public function schedule_calibration($equipment_id){
		$eq = $this->get_equipment($equipment_id);
		if(!$eq){ return "Equipment record not found."; }
		$due = trim((string)$this->input->post('scheduled_date'));
		$due = $due ? system_fromatted_date($due) : ($eq->next_calibration_date ?: date('Y-m-d', strtotime('+12 months')));
		$this->db->insert('db_service_jobs', array(
			'store_id' => get_current_store_id(),
			'job_code' => $this->_next_job_code(),
			'count_id' => (int)$this->db->select_max('count_id')->get('db_service_jobs')->row()->count_id + 1,
			'customer_id' => $eq->customer_id,
			'site_id' => $eq->site_id,
			'equipment_id' => $eq->id,
			'sales_id' => $eq->sales_id,
			'job_type' => 'calibration',
			'priority' => 'normal',
			'status' => 'scheduled',
			'assigned_user_id' => ((int)$this->input->post('assigned_user_id')) ?: null,
			'scheduled_date' => $due,
			'title' => 'Calibration — ' . ($eq->item_name ?: $eq->model),
			'description' => 'Scheduled calibration for serial ' . $eq->serial_number,
			'created_by' => $this->session->userdata('inv_userid'),
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
		));
		$job_id = $this->db->insert_id();
		if(!$job_id){ return 'failed'; }
		$this->db->where('id', $eq->id)->update('db_customer_equipment', array('next_calibration_date' => $due));
		return 'success<<<###>>>'.$job_id;
	}

	/* Engineers = active users of the store (job assignment). */
	public function get_engineers(){
		return $this->db->select('id, username')->where('store_id', get_current_store_id())->where('status', 1)->order_by('username')->get('db_users')->result();
	}
}
