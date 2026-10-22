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
			(SELECT COUNT(*) FROM db_service_jobs j WHERE j.equipment_id = e.id AND j.store_id=e.store_id) AS job_count", FALSE);
		$this->db->from('db_customer_equipment e');
		$this->db->join('db_customers c', 'c.id = e.customer_id AND c.store_id=e.store_id', 'left');
		$this->db->join('db_items i', 'i.id = e.item_id AND i.store_id=e.store_id', 'left');
		$this->db->join('db_shippingaddress sa', 'sa.id = e.site_id AND sa.store_id=e.store_id', 'left');
		$this->db->join('db_sales s', 's.id = e.sales_id AND s.store_id=e.store_id', 'left');
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
			->join('db_customers c', 'c.id = e.customer_id AND c.store_id=e.store_id', 'left')
			->join('db_items i', 'i.id = e.item_id AND i.store_id=e.store_id', 'left')
			->join('db_shippingaddress sa', 'sa.id = e.site_id AND sa.store_id=e.store_id', 'left')
			->join('db_sales s', 's.id = e.sales_id AND s.store_id=e.store_id', 'left')
			->where('e.id', (int)$id)->where('e.store_id',get_current_store_id())
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

	private function _next_job_code($sourceStoreId = null){
		$store_id = $sourceStoreId===null ? get_current_store_id() : (int)$sourceStoreId;
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
		try { return 'success<<<###>>>'.$this->save_job_data($data,$job_id); }
        catch (InvalidArgumentException $e) { return $e->getMessage(); }
        catch (Throwable $e) { log_message('error','Service job save: '.$e->getMessage()); return 'Could not save service job.'; }
    }

    /** Shared entry point used by operations and industry adapters. */
    public function save_job_data(array $data, $job_id = 0, $sourceStoreId = null) {
        // Background fulfilment has no merchant session. An explicit store is
        // accepted for creation only; HTTP controllers use the session default.
        if ($sourceStoreId!==null && $job_id) throw new InvalidArgumentException('Background job updates are not permitted.');
        $store_id = $sourceStoreId===null ? (int)get_current_store_id() : (int)$sourceStoreId;
        unset($data['store_id'],$data['id'],$data['job_code'],$data['count_id'],$data['created_by']);
        $customer_id = (int)($data['customer_id'] ?? 0);
        if (!$this->db->where('id',$customer_id)->where('store_id',$store_id)->get('db_customers')->row()) throw new InvalidArgumentException('Customer not found.');
        foreach (['site_id'=>'db_shippingaddress','equipment_id'=>'db_customer_equipment','sales_id'=>'db_sales'] as $column=>$table) {
            if (empty($data[$column])) continue;
            if (!$this->db->where('id',(int)$data[$column])->where('store_id',$store_id)->where('customer_id',$customer_id)->get($table)->row()) throw new InvalidArgumentException('The selected site, equipment or invoice does not belong to this customer.');
        }
        if (!empty($data['assigned_user_id']) && !$this->db->where('id',(int)$data['assigned_user_id'])->where('store_id',$store_id)->where('status',1)->get('db_users')->row()) throw new InvalidArgumentException('Team member not found.');
        if (!in_array($data['status'] ?? 'open',$this->JOB_STATUSES,true)) throw new InvalidArgumentException('Invalid job status.');
        if (empty($data['title'])) $data['title']=ucfirst($data['job_type'] ?? 'Service').' job';
        if (strlen($data['title'])>200) throw new InvalidArgumentException('Enter a job title of at most 200 characters.');
        if (!empty($data['scheduled_date'])) {
            $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$data['scheduled_date']);
            if (!$parsed || $parsed->format('Y-m-d')!==$data['scheduled_date']) throw new InvalidArgumentException('Invalid scheduled date.');
        }
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store_id]);
            if ($job_id) {
                $existing=$this->db->where('id',(int)$job_id)->where('store_id',$store_id)->get('db_service_jobs')->row();
                if (!$existing) throw new InvalidArgumentException('Service job not found.');
                if ((int)$existing->customer_id!==$customer_id) throw new InvalidArgumentException('A service job cannot be moved to another customer.');
                if ($this->db->table_exists('db_service_job_context') && $this->db->where('store_id',$store_id)->where('job_id',(int)$job_id)->get('db_service_job_context')->row()
                    && (($data['status'] ?? $existing->status)!==$existing->status || (int)($data['sales_id'] ?? $existing->sales_id)!==(int)$existing->sales_id)) throw new InvalidArgumentException('Manage project status and invoice linkage from its project workspace.');
                $ok=$this->db->where('id',(int)$job_id)->where('store_id',$store_id)->update('db_service_jobs',$data);
            } else {
                $data['store_id']=$store_id;
                $data['job_code']=$this->_next_job_code($store_id);
                $data['count_id']=(int)($this->db->select_max('count_id')->get('db_service_jobs')->row()->count_id ?? 0)+1;
                $data['created_by']=$sourceStoreId===null ? (int)$this->session->userdata('inv_userid') : 0;
                $data['created_date']=date('Y-m-d');$data['created_time']=date('H:i:s');
                $ok=$this->db->insert('db_service_jobs',$data);$job_id=(int)$this->db->insert_id();
            }
            if (!$ok || $this->db->trans_status()===false) throw new RuntimeException('Could not save job.');
            $this->db->trans_commit();return (int)$job_id;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

	public function get_job_list($filters = array()){
		$store_id = get_current_store_id();
		$this->db->select("j.*, c.customer_name, u.username AS engineer, COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name,
			e.serial_number AS equipment_serial, i.item_name AS equipment_item", FALSE);
		$this->db->from('db_service_jobs j');
		$this->db->join('db_customers c', 'c.id = j.customer_id AND c.store_id=j.store_id', 'left');
		$this->db->join('db_users u', 'u.id = j.assigned_user_id AND u.store_id=j.store_id', 'left');
		$this->db->join('db_shippingaddress sa', 'sa.id = j.site_id AND sa.store_id=j.store_id', 'left');
		$this->db->join('db_customer_equipment e', 'e.id = j.equipment_id AND e.store_id=j.store_id', 'left');
		$this->db->join('db_items i', 'i.id = e.item_id AND i.store_id=e.store_id', 'left');
		$this->db->where('j.store_id', $store_id);
		if(!empty($filters['customer_id'])){ $this->db->where('j.customer_id', $filters['customer_id']); }
		if(!empty($filters['equipment_id'])){ $this->db->where('j.equipment_id', $filters['equipment_id']); }
		if(!empty($filters['status'])){ $this->db->where('j.status', $filters['status']); }
		if(!empty($filters['open_only'])){ $this->db->where_not_in('j.status', array('completed','cancelled')); }
		$this->db->order_by('j.id', 'DESC');
		if (!empty($filters['limit'])) $this->db->limit(min(500,(int)$filters['limit']));
		return $this->db->get()->result();
	}

	public function get_job($id){
		$job = $this->db->select("j.*, c.customer_name, u.username AS engineer, COALESCE(NULLIF(sa.site_name,''), sa.address) AS site_name, sa.address AS site_address,
				e.serial_number AS equipment_serial, e.model AS equipment_model, i.item_name AS equipment_item,
				s.sales_code", FALSE)
			->from('db_service_jobs j')
			->join('db_customers c', 'c.id = j.customer_id AND c.store_id=j.store_id', 'left')
			->join('db_users u', 'u.id = j.assigned_user_id AND u.store_id=j.store_id', 'left')
			->join('db_shippingaddress sa', 'sa.id = j.site_id AND sa.store_id=j.store_id', 'left')
			->join('db_customer_equipment e', 'e.id = j.equipment_id AND e.store_id=j.store_id', 'left')
			->join('db_items i', 'i.id = e.item_id AND i.store_id=e.store_id', 'left')
			->join('db_sales s', 's.id = j.sales_id AND s.store_id=j.store_id', 'left')
			->where('j.id', (int)$id)->where('j.store_id', get_current_store_id())
			->get()->row();
		return $job;
	}

	public function get_job_visits($job_id){
		if (!$this->get_job($job_id)) return [];
		return $this->db->select('v.*, u.username AS engineer_name')
			->from('db_service_job_visits v')
			->join('db_users u', 'u.id = v.engineer_id AND u.store_id=v.store_id', 'left')
			->where('v.job_id', (int)$job_id)->where('v.store_id',get_current_store_id())
			->order_by('v.id', 'DESC')->get()->result();
	}

	public function get_job_parts($job_id){
		if (!$this->get_job($job_id)) return [];
		return $this->db->select('p.*, i.item_name, b.serial_number')
			->from('db_service_job_items p')
			->join('db_items i', 'i.id = p.item_id AND i.store_id=p.store_id', 'left')
			->join('db_item_barcodes b', 'b.id = p.barcode_id AND b.item_id=p.item_id', 'left')
			->where('p.job_id', (int)$job_id)->where('p.store_id',get_current_store_id())
			->order_by('p.id', 'ASC')->get()->result();
	}

	public function save_visit(){
		$job_id = (int)$this->input->post('job_id');
		$job = $this->db->where('id', $job_id)->where('store_id', get_current_store_id())->get('db_service_jobs')->row();
		if(!$job){ return "Service job not found."; }
		$notes = mp_clean_text($this->input->post('visit_notes'));
		if(empty($notes)){ return "Visit notes are required."; }
		$engineer_id=((int)$this->input->post('engineer_id')) ?: ($job->assigned_user_id ?: $this->session->userdata('inv_userid'));
		if (!$this->db->where('id',(int)$engineer_id)->where('store_id',get_current_store_id())->where('status',1)->get('db_users')->row()) return 'Team member not found.';

		$this->db->insert('db_service_job_visits', array(
			'store_id'   => get_current_store_id(),
			'job_id'     => $job_id,
			'visit_date' => ($d = trim((string)$this->input->post('visit_date'))) ? system_fromatted_date($d) : date('Y-m-d'),
			'engineer_id'=> $engineer_id,
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
		if ($this->db->table_exists('db_service_material_requests')) {
			try { $this->consume_material((array)$this->input->post()); return 'success'; }
			catch (InvalidArgumentException $e) { return $e->getMessage(); }
			catch (Throwable $e) { log_message('error','Service material issue: '.$e->getMessage()); return 'Could not record materials.'; }
		}
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

    /** Atomic material issue with a cost snapshot and shared adjustment linkage. */
    public function consume_material(array $input) {
        $storeId=(int)get_current_store_id();$jobId=(int)($input['job_id'] ?? 0);$itemId=(int)($input['item_id'] ?? 0);
        $qtyRaw=(string)($input['qty'] ?? '');$key=(string)($input['request_key'] ?? '');$barcode=(int)($input['barcode_id'] ?? 0);
        if (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D',$qtyRaw) || (float)$qtyRaw<=0 || (float)$qtyRaw>999999999) throw new InvalidArgumentException('Enter a positive material quantity with at most two decimals.');
        if (!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)) throw new InvalidArgumentException('Reload the materials form to obtain a request reference.');
        $qty=(float)$qtyRaw;$price=(float)str_replace(',','',(string)($input['price_per_unit'] ?? 0));
        if (!is_finite($price) || $price<0) throw new InvalidArgumentException('Invalid material charge.');
        $hash=hash('sha256',json_encode([$jobId,$itemId,number_format($qty,2,'.',''),$barcode,number_format($price,4,'.','')]));
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$storeId]);
            $prior=$this->db->where('store_id',$storeId)->where('request_key',$key)->get('db_service_material_requests')->row();
            if ($prior) { if (!hash_equals($prior->payload_hash,$hash)) throw new InvalidArgumentException('Request reference already used.'); $this->db->trans_commit(); return (int)$prior->job_item_id; }
            $job=$this->get_job($jobId);
            if (!$job || in_array($job->status,['completed','cancelled'],true)) throw new InvalidArgumentException('Select an open service job.');
            if ($this->db->table_exists('db_service_job_context')) {
                $context=$this->db->where('store_id',$storeId)->where('job_id',$jobId)->get('db_service_job_context')->row();
                if ($context && $context->stage!=='execution') throw new InvalidArgumentException('Issue project materials only during execution.');
            }
            if ($job->sales_id && $this->db->where('sales_id',(int)$job->sales_id)->where('store_id',$storeId)->where('item_id',$itemId)->get('db_salesitems')->row()) {
                throw new InvalidArgumentException('This material is already issued by the linked invoice. Do not deduct it again.');
            }
            if ($job->sales_id && $this->db->from('db_stockadjustmentitems ai')->join('db_stockadjustment a','a.id=ai.adjustment_id AND a.store_id=ai.store_id')
                ->where('a.store_id',$storeId)->where('a.reference_no','SALE-'.$job->sales_id)->where('ai.item_id',$itemId)->where('ai.adjustment_qty <',0)->count_all_results()) throw new InvalidArgumentException('This material is already issued as an invoice bundle component.');
            $item=$this->db->query('SELECT * FROM db_items WHERE id=? AND store_id=? FOR UPDATE',[$itemId,$storeId])->row();
            if (!$item || (int)$item->service_bit===1 || !empty($item->is_bundle) || in_array($item->item_production_mode,['component','sale_deplete'],true)) throw new InvalidArgumentException('Select a tracked physical material from this store.');
            $warehouseId=get_store_warehouse_id();
            if (!$this->db->where('id',(int)$warehouseId)->where('store_id',$storeId)->get('db_warehouse')->row()) throw new InvalidArgumentException('Branch not found.');
            if (min((float)$item->stock,(float)total_available_qty_items_of_warehouse($warehouseId,$storeId,$itemId))<$qty) throw new InvalidArgumentException('Insufficient available material stock.');
            if ($barcode) {
                $unit=$this->db->where('id',$barcode)->where('item_id',$itemId)->where('warehouse_id',$warehouseId)->where('status',1)->get('db_item_barcodes')->row();
                if (!$unit || (float)$unit->qty<$qty) throw new InvalidArgumentException('Material batch is not available.');
                if (!$this->db->where('id',$barcode)->update('db_item_barcodes',['qty'=>(float)$unit->qty-$qty,'status'=>(float)$unit->qty===$qty ? 0 : 1])) throw new RuntimeException('Could not update material batch.');
            }
            $charge=$price>0 ? $price : (float)$item->sales_price;
            if (!$this->db->insert('db_service_job_items',['store_id'=>$storeId,'job_id'=>$jobId,'item_id'=>$itemId,'barcode_id'=>$barcode ?: null,'qty'=>$qty,
                'price_per_unit'=>$charge,'total'=>$qty*$charge,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_date'=>date('Y-m-d')])) throw new RuntimeException('Could not record job material.');
            $partId=(int)$this->db->insert_id();
            if (!$this->db->insert('db_stockadjustment',['store_id'=>$storeId,'warehouse_id'=>$warehouseId,'reference_no'=>$job->job_code,'adjustment_date'=>date('Y-m-d'),
                'adjustment_note'=>'Material issue for '.$job->job_code,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>$this->session->userdata('inv_username'),'status'=>1])) throw new RuntimeException('Could not create material adjustment.');
            $adjustmentId=(int)$this->db->insert_id();
            if (!$this->db->insert('db_stockadjustmentitems',['store_id'=>$storeId,'warehouse_id'=>$warehouseId,'adjustment_id'=>$adjustmentId,'item_id'=>$itemId,'adjustment_qty'=>-$qty,'description'=>'Service job '.$job->job_code,'status'=>1])) throw new RuntimeException('Could not record material stock movement.');
            if (!$this->db->insert('db_service_material_requests',['store_id'=>$storeId,'job_id'=>$jobId,'request_key'=>$key,'payload_hash'=>$hash,'job_item_id'=>$partId,
                'adjustment_id'=>$adjustmentId,'unit_cost'=>(float)$item->purchase_price,'created_at'=>date('Y-m-d H:i:s')])) throw new RuntimeException('Could not record material audit.');
            $this->load->model('pos_model');
            if (!$this->pos_model->update_items_quantity($itemId) || !update_warehouse_items([['item_id'=>$itemId]])) throw new RuntimeException('Could not reconcile material stock.');
            $this->db->query('UPDATE db_service_jobs SET parts_total=(SELECT COALESCE(SUM(total),0) FROM db_service_job_items WHERE job_id=? AND store_id=?) WHERE id=? AND store_id=?',[$jobId,$storeId,$jobId,$storeId]);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not save material issue.');
            $this->db->trans_commit();return $partId;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

	public function update_job_status(){
		$job_id = (int)$this->input->post('job_id');
		$status = mp_clean_text($this->input->post('status'));
		if(!in_array($status, $this->JOB_STATUSES)){ return 'Invalid status.'; }
		$job = $this->db->where('id', $job_id)->where('store_id', get_current_store_id())->get('db_service_jobs')->row();
		if(!$job){ return "Service job not found."; }
		if ($this->db->table_exists('db_service_job_context') && $this->db->where('store_id',get_current_store_id())->where('job_id',$job_id)->get('db_service_job_context')->row()) return 'Manage project status from its project workspace.';
		$allowed=['open'=>['scheduled','in_progress','cancelled'],'scheduled'=>['in_progress','cancelled'],'in_progress'=>['awaiting_parts','completed','cancelled'],'awaiting_parts'=>['in_progress','cancelled']];
		if ($status!==$job->status && !in_array($status,$allowed[$job->status] ?? [],true)) return 'Invalid service job transition.';
		if ($status===$job->status) return 'success';
		$jobProfile=mp_get_store_profile();
		if (($jobProfile['industry_type'] ?? '')==='garden_centre_landscaping') {
			if ($status==='completed' && !trim((string)$this->input->post('resolution_notes'))) return 'Record completion and customer confirmation notes.';
			if ($status==='scheduled' && (!$job->assigned_user_id || !$job->scheduled_date)) return 'Assign a team member and visit date first.';
		}

		$data = array('status' => $status);
		if($status === 'in_progress' && empty($job->started_date)){ $data['started_date'] = date('Y-m-d'); }
		if($status === 'completed'){
			$data['completed_date'] = date('Y-m-d');
			$resolution = mp_clean_text($this->input->post('resolution_notes'));
			if(!empty($resolution)){ $data['resolution_notes'] = $resolution; }
		}
		if (!$this->db->where('id', $job_id)->where('store_id',get_current_store_id())->where('status',$job->status)->update('db_service_jobs', $data)
			|| $this->db->affected_rows()!==1) return 'Job changed while you were editing. Reload and try again.';

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
		$store_id = get_current_store_id();
		// Exclusion helpers query the same builder; resolve both before composing it.
		$excluded_roles = get_excluded_staff_roles($store_id);
		$excluded_users = get_excluded_user_ids($store_id);
		return $this->db->select('id, username')->where('store_id', $store_id)->where('status', 1)
			->where_not_in('role_id', $excluded_roles)
			->where_not_in('id', $excluded_users)
			->order_by('username')->get('db_users')->result();
	}
}
