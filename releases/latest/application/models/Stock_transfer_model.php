<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfer_model extends CI_Model {

	//Datatable start
	var $table = 'db_stocktransfer as a';
	// Column order mapping aligns with the DataTables display order:
	// [checkbox, transfer_date, from_warehouse, to_warehouse, details, arrival, note, created_by, action]
	var $column_order = array( 'a.id','a.transfer_date','wf.warehouse_name','wt.warehouse_name','a.id','a.receipt_status','a.note','a.created_by','a.id'); //set column field database for datatable orderable
	var $column_search = array('a.id','a.transfer_date','a.note','a.receipt_status','wf.warehouse_name','wt.warehouse_name','a.created_by'); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order  

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
	}

	private function _get_datatables_query()
	{
		
		$this->db->select('a.*, wf.warehouse_name as warehouse_from_name, wt.warehouse_name as warehouse_to_name');
		$this->db->from($this->table);
		$this->db->join('db_warehouse as wf','wf.id = a.warehouse_from','left');
		$this->db->join('db_warehouse as wt','wt.id = a.warehouse_to','left');

		/*If warehouse selected*/
		$warehouse_id = $this->input->post('warehouse_id');
		if(!empty($warehouse_id)){
			$this->db->group_start();
			$this->db->where('a.warehouse_from',$warehouse_id);
			$this->db->or_where('a.warehouse_to',$warehouse_id);
			$this->db->group_end();
		}

		/* Staff see only the loads that touch a location they are assigned to. */
		$this->_apply_location_scope();

		/* Queue filter: In Transit / Awaiting Approval / Received / Posted */
		$receipt_status = $this->input->post('receipt_status');
		if(!empty($receipt_status)){
			$this->db->where('a.receipt_status',$receipt_status);
		}
		
		//if(!is_admin()){
	      $this->db->where("a.store_id",get_current_store_id());
	    //}
	    
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])) // if datatable send POST for search
			{
				
				

				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

					$this->db->like($item, $_POST['search']['value']);

				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				


				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if(isset($_POST['length']) && $_POST['length'] != -1)
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
		$this->db->where("a.store_id",get_current_store_id());
		$this->db->from($this->table);
		$this->_apply_location_scope();
		return $this->db->count_all_results();
	}

	/**
	 * Restrict the board to loads that touch a location the signed-in user is
	 * assigned to. Store owners and Super Admin are never scoped down and keep
	 * seeing the whole company.
	 */
	private function _apply_location_scope(){
		if(!function_exists('mp_location_assigned_ids')) return;
		$allowed = mp_location_assigned_ids(get_current_store_id());
		if(!is_array($allowed)) return;
		$allowed = array_map('intval',$allowed);
		if(empty($allowed)) $allowed = array(0);
		$this->db->group_start();
		$this->db->where_in('a.warehouse_from',$allowed);
		$this->db->or_where_in('a.warehouse_to',$allowed);
		$this->db->group_end();
	}
	//Datatable end

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}

	/* Batch ledger (db_item_barcodes) controls ---------------------------- */

	private function _lot_transfer_enabled(){
		return $this->db->table_exists('db_item_barcodes')
			&& $this->db->table_exists('db_stocktransfer_batch_allocations');
	}

	/**
	 * Drain lots from warehouse_from in FEFO order and rebuild the matching
	 * lots in warehouse_to. Items without lot rows keep the legacy aggregate
	 * behavior. Returns '' on success, otherwise a blocking error message;
	 * the caller owns the transaction.
	 */
	private function _move_lots($stocktransfer_id, $transfer_item_id, $item_id, $warehouse_from, $warehouse_to, $qty){
		$need = round((float)$qty, 4);
		if ($need <= 0 || (int)$warehouse_from <= 0 || (int)$warehouse_to <= 0) return '';
		if ((int)$warehouse_from === (int)$warehouse_to) return '';
		if (!$this->_lot_transfer_enabled()) return '';

		$sql = "SELECT id, item_id, batch_lot, expire_date, mfg_date, purchase_id, purchaseitems_id,
                       purchase_price, sales_price, mrp, qty
                FROM db_item_barcodes
                WHERE item_id = ? AND warehouse_id = ? AND status = 1 AND qty > 0
                  AND ((batch_lot IS NOT NULL AND batch_lot <> '') OR expire_date IS NOT NULL)
                  AND (serial_number IS NULL OR serial_number = '')
                  AND (imei_number IS NULL OR imei_number = '')
                ORDER BY (expire_date IS NULL) ASC, expire_date ASC, created_date ASC, id ASC
                FOR UPDATE";
		$query = $this->db->query($sql, [(int)$item_id, (int)$warehouse_from]);
		if (!$query) return 'Could not check batch stock in the source warehouse. No stock was moved.';
		$lots = $query->result();
		if (!$lots) return ''; // not a batch-tracked item; aggregate stock covers it

		$available = 0.0;
		foreach ($lots as $lot) $available += (float)$lot->qty;
		if ($available + 0.00001 < $need) {
			return 'Batch stock in the source warehouse is short for this item (need '
				.format_qty($need).', batched stock '.format_qty($available)
				.'). Reconcile the batch quantities before transferring.';
		}

		$remaining = $need;
		foreach ($lots as $lot) {
			if ($remaining <= 0.00001) break;
			$take = min($remaining, (float)$lot->qty);
			$drained = $this->db->query(
				'UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND status = 1 AND qty >= ?',
				[$take, $take, (int)$lot->id, $take]
			);
			if (!$drained || $this->db->affected_rows() !== 1) {
				return 'A batch changed while the transfer was being saved. Please retry the transfer; nothing was committed.';
			}
			$dest_id = $this->_upsert_destination_lot($lot, $take, $warehouse_to);
			if ($dest_id === false) {
				return 'Could not create the batch record in the destination warehouse. Please retry the transfer.';
			}
			$inserted = $this->db->insert('db_stocktransfer_batch_allocations', [
				'stocktransfer_id'      => (int)$stocktransfer_id,
				'stocktransferitems_id' => (int)$transfer_item_id,
				'item_id'               => (int)$item_id,
				'warehouse_from'        => (int)$warehouse_from,
				'warehouse_to'          => (int)$warehouse_to,
				'source_barcode_id'     => (int)$lot->id,
				'dest_barcode_id'       => (int)$dest_id,
				'qty'                   => $take,
				'created_date'          => date('Y-m-d'),
			]);
			if (!$inserted) return 'Could not record the batch movement. Please retry the transfer.';
			$remaining -= $take;
		}
		return $remaining > 0.00001 ? 'Not enough batch stock was moved. Please retry the transfer.' : '';
	}

	/** Add qty to the destination warehouse's matching lot, or create that lot. */
	private function _upsert_destination_lot($lot, $qty, $warehouse_to){
		$batch_lot = (string)($lot->batch_lot ?? '');
		$expire = $lot->expire_date ? substr($lot->expire_date, 0, 10) : '';
		$row = $this->db->query(
			"SELECT id FROM db_item_barcodes
			 WHERE item_id = ? AND warehouse_id = ?
			   AND IFNULL(batch_lot, '') = ? AND IFNULL(DATE(expire_date), '') = ?
			   AND (serial_number IS NULL OR serial_number = '')
			   AND (imei_number IS NULL OR imei_number = '')
			 ORDER BY IF(status = 1, 0, 1) ASC, id ASC LIMIT 1",
			[(int)$lot->item_id, (int)$warehouse_to, $batch_lot, $expire]
		)->row();
		if ($row) {
			$added = $this->db->query('UPDATE db_item_barcodes SET qty = qty + ?, status = 1 WHERE id = ?', [$qty, (int)$row->id]);
			return ($added && $this->db->affected_rows() >= 1) ? (int)$row->id : false;
		}
		$created = $this->db->insert('db_item_barcodes', [
			'item_id'          => (int)$lot->item_id,
			'purchase_id'      => $lot->purchase_id,
			'purchaseitems_id' => $lot->purchaseitems_id,
			'batch_lot'        => $lot->batch_lot,
			'purchase_price'   => $lot->purchase_price,
			'sales_price'      => $lot->sales_price,
			'mrp'              => $lot->mrp,
			'qty'              => $qty,
			'expire_date'      => $lot->expire_date,
			'mfg_date'         => $lot->mfg_date,
			'warehouse_id'     => (int)$warehouse_to,
			'status'           => 1,
			'created_date'     => date('Y-m-d'),
			'created_time'     => date('H:i:s'),
		]);
		return $created ? (int)$this->db->insert_id() : false;
	}

	/**
	 * Undo every lot movement recorded for a transfer: pull qty back out of
	 * the destination lot and restore the source lot. Returns false when the
	 * destination stock has already been sold or moved on, in which case the
	 * caller must block the edit/delete.
	 */
	private function _restore_lots($stocktransfer_id){
		if (!$this->_lot_transfer_enabled()) return true;
		$rows = $this->db->where('stocktransfer_id', (int)$stocktransfer_id)
			->get('db_stocktransfer_batch_allocations')->result();
		foreach ($rows as $row) {
			$qty = (float)$row->qty;
			if ($qty <= 0) continue;
			$pulled = $this->db->query(
				'UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND qty >= ?',
				[$qty, $qty, (int)$row->dest_barcode_id, $qty]
			);
			if (!$pulled || $this->db->affected_rows() !== 1) return false;
			$restored = $this->db->query('UPDATE db_item_barcodes SET qty = qty + ?, status = 1 WHERE id = ?', [$qty, (int)$row->source_barcode_id]);
			if (!$restored || $this->db->affected_rows() !== 1) return false;
		}
		return $this->db->where('stocktransfer_id', (int)$stocktransfer_id)->delete('db_stocktransfer_batch_allocations');
	}

	//Save Stock
	public function verify_save_and_update(){
		$command = $this->input->post_get('command', TRUE);
		$transfer_date = $this->input->post('transfer_date', TRUE);
		$note = $this->input->post('note', TRUE);
		$store_from = $this->input->post('store_from', TRUE);
		$warehouse_from = $this->input->post('warehouse_from', TRUE);
		$warehouse_to = $this->input->post('warehouse_to', TRUE);
		$stocktransfer_id = $this->input->post('stocktransfer_id', TRUE);
		$rowcount = $this->input->post_get('rowcount', TRUE);
		//echo "<pre>";print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		
		$this->db->trans_begin();
		$transfer_date=system_fromatted_date($transfer_date);

		$store_id=(store_module() && is_admin()) ? $store_from : get_current_store_id();  	
		//$to_store_id=(store_module() && is_admin()) ? $store_to : get_current_store_id();  	
		
		$prev_item_ids = array();

		/*
		 * Arrival receipt: when the store records what actually arrives, a new
		 * load leaves the sending site but does not land at the destination until
		 * somebody counts it in. Without the capability the transfer posts the
		 * sent quantity immediately, exactly as it always has.
		 */
		$receipt_mode = mp_location_feature_enabled('transfer_receipt', (int)$warehouse_to) ? 'In Transit' : 'Posted';

		if($command=='update'){
			$existing_receipt = $this->db->select('receipt_status')->where('id',$stocktransfer_id)->where('store_id',$store_id)->get('db_stocktransfer')->row();
			if($existing_receipt && in_array((string)$existing_receipt->receipt_status, array('Received','Awaiting Approval'), true)){
				$this->db->trans_rollback();
				return 'This load has already been counted in at the receiving location. Undo the arrival there before changing the quantity.';
			}
		}

	    if($command=='save'){//Create stock code unique if first time entry

			
		    $stock_entry = array(
		    				'note' 						=> $note, 
		    				'store_id' 					=> $store_id, 
		    				//'to_store_id' 				=> $to_store_id, 
		    				'transfer_date' 			=> $transfer_date,
		    				'warehouse_from' 			=> $warehouse_from,
		    				'warehouse_to' 				=> $warehouse_to,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    				'receipt_status' 			=> $receipt_mode,
		    			);
		   
			$q1 = $this->db->insert('db_stocktransfer', $stock_entry);
			$stocktransfer_id = $this->db->insert_id();
		}
		else if($command=='update'){	
			$stock_entry = array(
							'store_id' 					=> $store_id, 
		    				//'to_store_id' 				=> $to_store_id, 
		    				'note' 						=> $note, 
		    				'transfer_date' 			=> $transfer_date,
		    				'warehouse_from' 			=> $warehouse_from,
		    				'warehouse_to' 				=> $warehouse_to,
		    				'receipt_status' 			=> $receipt_mode,
		    			);
		
			$q1 = $this->db->where('id',$stocktransfer_id)->update('db_stocktransfer', $stock_entry);

			// Undo the previously recorded lot movements before the lines are
			// re-inserted below; if the destination stock has already moved on
			// we must not silently corrupt the batch ledger.
			if (!$this->_restore_lots($stocktransfer_id)) {
				$this->db->trans_rollback();
				return "failed: The batch stock from this transfer has already been sold or moved on from the destination warehouse, so it cannot be edited.";
			}

			##############################################START
			//FIND THE PREVIOUSE ITEM LIST ID'S
			$prev_item_ids = $this->db->select("item_id")->from("db_stocktransferitems")->where("stocktransfer_id",$stocktransfer_id)->get()->result_array();
			##############################################END

			$q11=$this->db->where('stocktransfer_id', $stocktransfer_id)->delete('db_stocktransferitems');
			if(!$q11){
				return "failed";
			}
		}
		//end

		//Import post data from form
		// What each outlet held before the move, so the trail can state both
		// sides of it instead of only the quantity that left.
		$audit_before = $this->_audit_warehouse_snapshot($warehouse_from, $warehouse_to, $store_id);
		for($i=1;$i<=$rowcount;$i++){
		
			if(isset($_REQUEST['tr_item_id_'.$i]) && !empty($_REQUEST['tr_item_id_'.$i])){

				$item_id 			=$this->xss_html_filter(trim($_REQUEST['tr_item_id_'.$i]));
				$transfer_qty		=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_3']));
				

				//find item details
				$res_1 = $this->db->select("*")->where("id",$item_id)->get("db_items")->row();
				$item_name = $res_1->item_name;
				$category_id = $res_1->category_id;
				$brand_id 	 = $res_1->brand_id;
				$unit_id 	 = $res_1->unit_id;
				$tax_id 	 = $res_1->tax_id;

				
				$stockitems_entry = array(
							'store_id' 			=> $store_id, 
		    				//'to_store_id' 		=> $to_store_id, 
		    				'stocktransfer_id' 	=> $stocktransfer_id, 
		    				'item_id' 			=> $item_id, 
		    				'warehouse_from' 	=> $warehouse_from,
		    				'warehouse_to' 		=> $warehouse_to,
		    				'transfer_qty' 		=> $transfer_qty,
		    				'status'	 		=> 1,
		    			);
				
				$q2 = $this->db->insert('db_stocktransferitems', $stockitems_entry);
				$lot_err = $this->_move_lots($stocktransfer_id, $this->db->insert_id(), $item_id, $warehouse_from, $warehouse_to, $transfer_qty);
				if ($lot_err !== '') {
					$this->db->trans_rollback();
					return "failed: " . $lot_err;
				}
				
			}
		
		}//for end


		/*Update all store warehouses*/
		/*$q_1 = $this->db->select("id")->get("db_store");
		foreach($q_1->result() as $res1){
			$q_2 = update_warehousewise_items_qty_by_store($res1->id);
			if(!$q_2){
				return "failed";
			}
		}*/
		##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$curr_item_ids = $this->db->select("item_id")->from("db_stocktransferitems")->where("stocktransfer_id",$stocktransfer_id)->get()->result_array();
		$two_array = array_merge($prev_item_ids,$curr_item_ids);

		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($two_array);
		if(!$q7){
			return "failed";
		}
		##############################################END
		/**/

		$this->_audit_transfer($store_id, $stocktransfer_id, $warehouse_from, $warehouse_to, $audit_before, $note);
		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! ');
		return "success<<<###>>>$stocktransfer_id";
		
	}//verify_save_and_update() function end

	/** What each side of the move held, per product, before anything shifts. */
	private function _audit_warehouse_snapshot($warehouse_from, $warehouse_to, $store_id){
		$rowcount = (int)($this->input->post_get('rowcount', TRUE) ?: 0);
		$out = array();
		for($i = 1; $i <= $rowcount; $i++){
			if(empty($_REQUEST['tr_item_id_'.$i])) continue;
			$item_id = (int)$_REQUEST['tr_item_id_'.$i];
			if($item_id <= 0 || isset($out[$item_id])) continue;
			$out[$item_id] = array(
				'from' => $this->_audit_location_qty($item_id, $warehouse_from, $store_id),
				'to'   => $this->_audit_location_qty($item_id, $warehouse_to, $store_id),
			);
		}
		return $out;
	}

	private function _audit_location_qty($item_id, $warehouse_id, $store_id){
		$warehouse_id = (int)$warehouse_id;
		if($warehouse_id <= 0 || !$this->db->table_exists('db_warehouseitems')) return null;
		$row = $this->db->select('available_qty')->where('item_id', (int)$item_id)
			->where('warehouse_id', $warehouse_id)->limit(1)->get('db_warehouseitems')->row();
		return $row ? (float)$row->available_qty : 0.0;
	}

	private function _audit_location_name($warehouse_id){
		if(!function_exists('mp_catalogue_name') || !$this->db->table_exists('db_warehouse')) return 'outlet';
		$row = $this->db->select('warehouse_name')->where('id', (int)$warehouse_id)->limit(1)->get('db_warehouse')->row();
		$name = mp_catalogue_name($row->warehouse_name ?? '');
		return $name !== '' ? $name : 'outlet';
	}

	/**
	 * A transfer is where the stock was and where it ended up. Both sides are
	 * read back after the move, so the entry cannot claim delivery that the
	 * warehouse rows do not show.
	 */
	private function _audit_transfer($store_id, $transfer_id, $warehouse_from, $warehouse_to, array $before, $note){
		if(!$transfer_id || !function_exists('mp_audit_log')) return;
		try {
			$rows = $this->db->where('stocktransfer_id', (int)$transfer_id)->get('db_stocktransferitems')->result_array();
			if(!$rows) return;
			$capture = mp_audit_capture();
			if (!$capture) return;
			$src = $this->_audit_location_name($warehouse_from);
			$dst = $this->_audit_location_name($warehouse_to);
			$changes = array();
			$units = 0.0;
			foreach($rows as $row){
				$qty = abs((float)$row['transfer_qty']);
				if($qty <= 0) continue;
				$item_id = (int)$row['item_id'];
				$units += $qty;
				$name = $capture->item_name($item_id);
				if($name === '' || !isset($before[$item_id])) continue;
				$pairs = array(
					array($src, $warehouse_from, $before[$item_id]['from']),
					array($dst, $warehouse_to, $before[$item_id]['to']),
				);
				foreach($pairs as $pair){
					$was = $pair[2];
					$now = $this->_audit_location_qty($item_id, $pair[1], $store_id);
					if($was === null || $now === null || abs($was - $now) < 0.0000001) continue;
					$changes[] = mp_audit_change($name . ' at ' . $pair[0],
						mp_audit_format_value($was, 'qty'), mp_audit_format_value($now, 'qty'));
				}
			}
			if(!$changes) return;
			$said = rtrim(rtrim(number_format($units, 4, '.', ''), '0'), '.') . ' unit(s) moved between outlets';
			$note = trim((string)$note);
			if($note !== '') $said .= ' — note: ' . mp_catalogue_name(mb_substr($note, 0, 120));
			$capture->event($store_id, 'inventory', 'stock_transfer', (int)$transfer_id,
				$src . ' to ' . $dst, $changes, $said);
		} catch (Throwable $e) {
			log_message('error', 'Stock transfer audit capture failed: ' . $e->getMessage());
		}
	}


	//Get stock_details
	public function get_details($id,$data){
		//Validate This stock already exist or not
		$query=$this->db->query("select * from db_stocktransfer where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['stocktransfer_id']=$query->id;
			$data['note']=$query->note;
			$data['warehouse_from']=$query->warehouse_from;
			$data['warehouse_to']=$query->warehouse_to;
			$data['store_id']=$query->store_id;
			return $data;
		}
	}

	public function delete_stock($ids){
      	$this->db->trans_begin();
      	/* A deleted load must not leave a stale approval waiting on a manager. */
      	$CI =& get_instance();
      	$CI->load->model('approval_logs_model');
      	$deleted_ids = array_map('intval', array_filter(explode(',', (string)$ids)));
      	if(!empty($deleted_ids)){
      		foreach($this->db->select('id')->where('store_id',get_current_store_id())->where_in('id',$deleted_ids)->get('db_stocktransfer')->result() as $stale){
      			$CI->approval_logs_model->supersedeTarget('stock_transfer',(int)$stale->id);
      		}
      	}
      	##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$prev_item_ids = $this->db->select("item_id")->from("db_stocktransferitems")->where("stocktransfer_id in ($ids)")->get()->result_array();
		##############################################END

		// Return each moved lot to its source warehouse first; a transfer
		// whose destination stock has already been sold cannot be undone.
		foreach (explode(',', (string)$ids) as $del_id) {
			if (!$this->_restore_lots((int)$del_id)) {
				$this->db->trans_rollback();
				return "failed: This transfer moved batch stock that has already been sold or moved on from the destination warehouse, so it cannot be deleted.";
			}
		}

		#----------------------------------
		$this->db->where("id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q3=$this->db->delete("db_stocktransfer");
		if(!$q3){
			return "failed";
		}
		#----------------------------------
		
		##############################################START
		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($prev_item_ids);
		if(!$q7){
			return "failed";
		}
		##############################################END
		
		
		$this->db->trans_commit();
		return "success";
	}
	/* ============================================================
	 * Arrival receipt (oil & gas commercial base)
	 *
	 * A load that leaves one site does not become the receiving site's
	 * stock until somebody counts it in. What they count is what lands;
	 * a shortfall wider than the tolerance head office allows waits for
	 * approval before anything posts.
	 * ============================================================ */

	public function get_receipt_header($id){
		return $this->db->select('*')
			->where('id',(int)$id)
			->where('store_id',get_current_store_id())
			->get('db_stocktransfer')->row();
	}

	public function get_receipt_lines($id){
		return $this->db->select("a.id, a.item_id, a.warehouse_from, a.warehouse_to, a.transfer_qty, a.received_qty, i.item_name, i.purchase_price, COALESCE(u.unit_name,'') AS unit_name")
			->from('db_stocktransferitems a')
			->join('db_items i','i.id = a.item_id','left')
			->join('db_units u','u.id = i.unit_id','left')
			->where('a.stocktransfer_id',(int)$id)
			->get()->result();
	}

	/** Can this user count loads into the destination location? */
	public function may_receive($header){
		if(!function_exists('mp_location_assigned_ids')) return true;
		$allowed = mp_location_assigned_ids((int)$header->store_id);
		if(!is_array($allowed)) return true; // unrestricted (store owner / admin)
		return in_array((int)$header->warehouse_to, array_map('intval',$allowed), true);
	}

	public function save_receipt($id, $posted_lines, $reason){
		$header = $this->get_receipt_header($id);
		if(!$header) return "failed";
		if(!in_array((string)$header->receipt_status, array('In Transit','Awaiting Approval','Received'), true)){
			return 'This transfer does not need an arrival count.';
		}
		if(!$this->may_receive($header)){
			return 'You are not assigned to the location this load arrives at.';
		}
		$lines = $this->get_receipt_lines($id);
		if(empty($lines)) return "failed";

		$sent_total = 0; $received_total = 0; $shortfall_value = 0;
		$counts = array(); $item_ids = array();
		foreach($lines as $line){
			$sent = (float)$line->transfer_qty;
			$raw = isset($posted_lines[$line->id]) ? trim((string)$posted_lines[$line->id]) : '';
			$counted = ($raw === '') ? $sent : (float)str_replace(',','',$raw);
			if($counted < 0) $counted = 0;
			$sent_total += $sent;
			$received_total += $counted;
			if($sent > $counted) $shortfall_value += ($sent - $counted) * (float)$line->purchase_price;
			$counts[(int)$line->id] = $counted;
			$item_ids[] = array('item_id'=>(int)$line->item_id);
		}

		$variance = $sent_total - $received_total;
		$has_variance = abs($variance) > 0.0001;
		$variance_percent = ($sent_total > 0) ? ($variance / $sent_total) * 100 : 0;
		$tolerance = function_exists('mp_transfer_loss_tolerance_percent')
			? mp_transfer_loss_tolerance_percent($header->store_id) : 0;
		$needs_approval = ($has_variance && $variance_percent > $tolerance);

		$this->db->trans_begin();
		foreach($counts as $line_id => $counted){
			$this->db->where('id',$line_id)->update('db_stocktransferitems', array('received_qty'=>$counted));
		}
		$this->db->where('id',(int)$id)->update('db_stocktransfer', array(
			'receipt_status'  => $needs_approval ? 'Awaiting Approval' : 'Received',
			'received_by'     => $this->session->userdata('display_name') ?: $this->session->userdata('inv_username'),
			'received_date'   => date('Y-m-d'),
			'variance_reason' => $has_variance ? $this->xss_html_filter($reason) : null,
		));

		if($needs_approval){
			$CI =& get_instance();
			$CI->load->model('approval_logs_model');
			$fingerprint = md5($id.'|'.$sent_total.'|'.$received_total.'|'.$tolerance);
			$CI->approval_logs_model->supersedeTarget('stock_transfer',(int)$id);
			$log_id = $CI->approval_logs_model->log(array(
				'store_id'              => (int)$header->store_id,
				'branch_id'             => (int)$header->warehouse_to,
				'action_type'           => 'transfer_receipt',
				'approval_type'         => 'inventory_transfer',
				'requesting_user_id'    => (int)$this->session->userdata('inv_userid'),
				'requesting_user_name'  => $this->session->userdata('display_name'),
				'reason'                => $has_variance ? $this->xss_html_filter($reason) : 'Short load in transit',
				'previous_value'        => json_encode(array('sent'=>$sent_total)),
				'new_value'             => json_encode(array('received'=>$received_total,'variance_percent'=>round($variance_percent,3),'tolerance_percent'=>$tolerance)),
				'status'                => 'pending',
				'amount'                => $shortfall_value,
				'threshold'             => $tolerance,
				'target_module'         => 'stock_transfer',
				'target_id'             => (int)$id,
				'target_version'        => $fingerprint,
			));
			if($log_id){
				$this->db->where('id',(int)$id)->update('db_stocktransfer', array('approval_log_id'=>(int)$log_id));
			}
		}else{
			$CI =& get_instance();
			$CI->load->model('approval_logs_model');
			$CI->approval_logs_model->supersedeTarget('stock_transfer',(int)$id);
		}

		if(function_exists('update_warehouse_items')) update_warehouse_items($item_ids);
		$this->db->trans_commit();

		if($needs_approval){
			return 'Recorded. The load is '.format_qty($variance).' short, which is past the '.rtrim(rtrim(number_format($tolerance,2),'0'),'.').'% you accept in transit, so it is held for a manager to approve before the stock appears at the destination.';
		}
		return 'success<<<###>>>'.$id;
	}

	/** Manager decision: accept the shortfall and let the counted quantity land. */
	public function approve_receipt_variance($id, $note){
		$header = $this->get_receipt_header($id);
		if(!$header || (string)$header->receipt_status !== 'Awaiting Approval') return "failed";

		$this->db->trans_begin();
		$this->db->where('id',(int)$id)->update('db_stocktransfer', array(
			'receipt_status' => 'Received',
			'approval_note'  => $this->xss_html_filter($note),
		));
		$CI =& get_instance();
		$CI->load->model('approval_logs_model');
		if(!empty($header->approval_log_id)){
			$CI->approval_logs_model->updateStatus((int)$header->approval_log_id,'approved',
				(int)$this->session->userdata('inv_userid'), $this->session->userdata('display_name'), 'workspace');
		}
		$lines = $this->get_receipt_lines($id);
		$item_ids = array();
		foreach($lines as $line) $item_ids[] = array('item_id'=>(int)$line->item_id);
		if(function_exists('update_warehouse_items')) update_warehouse_items($item_ids);
		$this->db->trans_commit();
		return 'success<<<###>>>'.$id;
	}

	/** Manager decision: refuse the count and send the load back to be re-counted. */
	public function reject_receipt_variance($id, $note){
		$header = $this->get_receipt_header($id);
		if(!$header || (string)$header->receipt_status !== 'Awaiting Approval') return "failed";

		$this->db->trans_begin();
		$this->db->where('stocktransfer_id',(int)$id)->update('db_stocktransferitems', array('received_qty'=>null));
		$this->db->where('id',(int)$id)->update('db_stocktransfer', array(
			'receipt_status' => 'In Transit',
			'received_by'    => null,
			'received_date'  => null,
			'approval_note'  => $this->xss_html_filter($note),
		));
		$CI =& get_instance();
		$CI->load->model('approval_logs_model');
		if(!empty($header->approval_log_id)){
			$CI->approval_logs_model->updateStatus((int)$header->approval_log_id,'rejected',
				(int)$this->session->userdata('inv_userid'), $this->session->userdata('display_name'), 'workspace');
		}
		$lines = $this->get_receipt_lines($id);
		$item_ids = array();
		foreach($lines as $line) $item_ids[] = array('item_id'=>(int)$line->item_id);
		if(function_exists('update_warehouse_items')) update_warehouse_items($item_ids);
		$this->db->trans_commit();
		return 'success<<<###>>>'.$id;
	}

	/** Counts for the workspace: what has not arrived, and what is stuck for a decision. */
	public function receipt_queue_counts($store_id){
		$counts = array('awaiting'=>0,'variance'=>0);
		if(!$this->db->table_exists('db_stocktransfer') || !$this->db->field_exists('receipt_status','db_stocktransfer')){
			return $counts;
		}
		$allowed = function_exists('mp_location_assigned_ids') ? mp_location_assigned_ids((int)$store_id) : null;
		if(is_array($allowed) && empty($allowed)) return $counts;

		foreach(array('awaiting'=>'In Transit','variance'=>'Awaiting Approval') as $key => $status){
			$this->db->select('COUNT(*) AS total')->from('db_stocktransfer')
				->where('store_id',(int)$store_id)
				->where('receipt_status',$status);
			if(is_array($allowed)) $this->db->where_in('warehouse_to',array_map('intval',$allowed));
			$row = $this->db->get()->row();
			$counts[$key] = $row ? (int)$row->total : 0;
		}
		return $counts;
	}

	public function search_item($q){
		$json_array=array();
        $query1="select id,item_name from db_items where (upper(item_name) like upper('%$q%') or upper(item_code) like upper('%$q%'))";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>(int)$value->id, 'text'=>$value->item_name];
            }
        }
        return json_encode($json_array);
	}
	
	public function find_item_details($id){
		$json_array=array();
        $query1="select id,hsn,alert_qty,unit_name,purchase_price,sales_price,gst_percentage,available_qty from db_items where id=$id";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array=['id'=>$value->id, 
        			 'hsn'=>$value->hsn,
        			 'alert_qty'=>$value->alert_qty,
        			 'unit_name'=>$value->unit_name,
        			 'purchase_price'=>$value->purchase_price,
        			 'sales_price'=>$value->sales_price,
        			 'gst_percentage'=>$value->gst_percentage,
        			 'available_qty'=>$value->available_qty,
        			];
            }
        }
        return json_encode($json_array);
	}

	
	public function get_items_info($rowcount,$item_id){
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$q1=$this->db->select('*')->from('db_items')->where("id=$item_id")->get();
		$tax=$this->db->query("select tax from db_tax where id=".$q1->row()->tax_id)->row()->tax;

		$info['item_id'] = $q1->row()->id;
		$info['item_name'] = $q1->row()->item_name;
		$info['item_available_qty'] = total_available_qty_items_of_warehouse($warehouse_id,null,$q1->row()->id);
		$info['item_stock_qty'] = ($info['item_available_qty']>1) ? 1 : $info['item_available_qty'];
		$this->return_row_with_data($rowcount,$info);
	}
	/* For Purchase Items List Retrieve*/
	public function return_stock_list($stock_id){
		$q1=$this->db->select('*')->from('db_stocktransferitems')->where("stocktransfer_id=$stock_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$q2=$this->db->query("select id,item_name,stock,price,sales_price,tax_type from db_items where id=".$res1->item_id);
			$warehouse_id = $res1->warehouse_from;
			$info['item_id'] = $res1->item_id;
			$info['item_name'] = $q2->row()->item_name;
			$info['item_available_qty'] = total_available_qty_items_of_warehouse($warehouse_id,null,$q2->row()->id)+$res1->transfer_qty;
			$info['item_stock_qty'] = $res1->transfer_qty;
			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}

	public function return_row_with_data($rowcount,$info){
		$item_id = isset($info['item_id']) ? $info['item_id'] : '';
		$item_name = isset($info['item_name']) ? $info['item_name'] : '';
		$stock = isset($info['stock']) ? $info['stock'] : '';
		$available_qty = isset($info['available_qty']) ? $info['available_qty'] : '';
		$item_stock_qty = isset($info['item_stock_qty']) ? $info['item_stock_qty'] : 1;
		$item_available_qty = isset($info['item_available_qty']) ? $info['item_available_qty'] : '';
		
		?>
            <tr id="row_<?=$rowcount;?>" data-row='<?=$rowcount;?>'>
               <td id="td_<?=$rowcount;?>_1">
                  <!-- item name  -->
                  <input type="text" style="font-weight: bold;" id="td_data_<?=$rowcount;?>_1" class="form-control no-padding" value='<?=$item_name;?>' readonly >
               </td>
               <!-- Qty -->
               <td id="td_<?=$rowcount;?>_3">
                  <div class="input-group ">
                     <span class="input-group-btn">
                     <button onclick="decrement_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
                     <input typ="text" value="<?=format_qty($item_stock_qty);?>" class="form-control no-padding text-center" onkeyup="calculate_quantity(<?=$rowcount;?>)" id="td_data_<?=$rowcount;?>_3" name="td_data_<?=$rowcount;?>_3">
                     <span class="input-group-btn">
                     <button onclick="increment_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
                  </div>
               </td>
               
               <!-- ADD button -->
               <td id="td_<?=$rowcount;?>_16" style="text-align: center;">
                  <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow(<?=$rowcount;?>)" title="Delete ?" name="td_data_<?=$rowcount;?>_16" id="td_data_<?=$rowcount;?>_16"></a>
               </td>
            
               <input type="hidden" id="tr_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr_item_id_<?=$rowcount;?>" name="tr_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
            </tr>
		<?php

	}


}
