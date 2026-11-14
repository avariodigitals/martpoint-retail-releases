<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warranty_claim_model extends CI_Model {
	private $transitions = array(
		'reported' => array('inspecting','approved','rejected','cancelled'),
		'inspecting' => array('approved','rejected','awaiting_parts','repaired','replaced','refunded','resolved','cancelled'),
		'approved' => array('awaiting_parts','repaired','replaced','refunded','resolved'),
		'awaiting_parts' => array('inspecting','repaired','replaced','resolved'),
		'repaired' => array('resolved'), 'replaced' => array('resolved'), 'refunded' => array('resolved'),
		'rejected' => array(), 'resolved' => array(), 'cancelled' => array(),
	);

	public function next_statuses($current) { return $this->transitions[(string)$current] ?? array(); }

	public function list_claims($search = '') {
		$store = (int)get_current_store_id();
		$this->db->select('c.*,si.sold_serial_number,si.sold_imei_number,si.sold_imei_number_2,si.warranty_end,s.sales_code,s.sales_date,i.item_name,cu.customer_name,cu.mobile', false)
			->from('db_warranty_claims c')
			->join('db_salesitems si','si.id=c.salesitems_id AND si.store_id=c.store_id','left')
			->join('db_sales s','s.id=c.sales_id AND s.store_id=c.store_id','left')
			->join('db_items i','i.id=c.item_id AND i.store_id=c.store_id','left')
			->join('db_customers cu','cu.id=c.customer_id AND cu.store_id=c.store_id','left')
			->where('c.store_id',$store);
		if(trim((string)$search)!=='') {
			$term = $this->db->escape_like_str(trim((string)$search));
			$this->db->group_start()->like('c.claim_code',$term)->or_like('si.sold_serial_number',$term)->or_like('si.sold_imei_number',$term)->or_like('si.sold_imei_number_2',$term)->or_like('cu.customer_name',$term)->or_like('cu.mobile',$term)->or_like('s.sales_code',$term)->or_like('i.item_name',$term)->group_end();
		}
		return $this->db->order_by('c.id','DESC')->limit(200)->get()->result();
	}

	public function get_claim($id) {
		return $this->db->select('c.*,si.sold_serial_number,si.sold_imei_number,si.sold_imei_number_2,si.warranty_start,si.warranty_end,s.sales_code,s.sales_date,i.item_name,cu.customer_name,cu.mobile',false)
			->from('db_warranty_claims c')->join('db_salesitems si','si.id=c.salesitems_id AND si.store_id=c.store_id','left')
			->join('db_sales s','s.id=c.sales_id AND s.store_id=c.store_id','left')->join('db_items i','i.id=c.item_id AND i.store_id=c.store_id','left')
			->join('db_customers cu','cu.id=c.customer_id AND cu.store_id=c.store_id','left')
			->where('c.id',(int)$id)->where('c.store_id',(int)get_current_store_id())->get()->row();
	}

	public function events($claim_id) {
		if (!$this->get_claim($claim_id)) return array();
		return $this->db->where('store_id',(int)get_current_store_id())->where('claim_id',(int)$claim_id)->order_by('id','ASC')->get('db_warranty_claim_events')->result();
	}

	public function create($sales_item_id,$issue) {
		$store = (int)get_current_store_id(); $sales_item_id=(int)$sales_item_id; $issue=trim((string)$issue);
		if ($sales_item_id<=0 || $issue==='' || strlen($issue)>8000) throw new InvalidArgumentException('Choose a sold device and describe the fault (up to 8,000 characters).');
		$this->db->trans_begin();
		try {
			$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',array($store));
			$device=$this->db->select('si.id,si.sales_id,si.item_id,si.barcode_id,si.warranty_end,si.sold_serial_number,si.sold_imei_number,si.sold_imei_number_2,s.customer_id,s.sales_status,b.serial_number AS unit_serial,b.imei_number AS unit_imei,b.imei_number_2 AS unit_imei_2')
				->from('db_salesitems si')->join('db_sales s','s.id=si.sales_id AND s.store_id=si.store_id','inner')->join('db_item_barcodes b','b.id=si.barcode_id AND b.item_id=si.item_id','inner')
				->where('si.id',$sales_item_id)->where('si.store_id',$store)->where('s.sales_status','Final')->get()->row();
			if (!$device || (int)$device->barcode_id<=0) throw new InvalidArgumentException('Warranty claims require a sold, serialized device from this store.');
			if (empty($device->sold_serial_number) && empty($device->sold_imei_number) && empty($device->sold_imei_number_2) && empty($device->unit_serial) && empty($device->unit_imei) && empty($device->unit_imei_2)) throw new InvalidArgumentException('This device has no serial number or IMEI on record.');
			if (empty($device->warranty_end) || $device->warranty_end < date('Y-m-d')) throw new InvalidArgumentException('This unit has no active recorded warranty.');
			$now=date('Y-m-d H:i:s');
			$count=(int)$this->db->query('SELECT COUNT(*) AS n FROM db_warranty_claims WHERE store_id=?',array($store))->row()->n;
			$claim_code='WC-'.date('ym').'-'.str_pad((string)($count+1),6,'0',STR_PAD_LEFT);
			$actor=(int)$this->session->userdata('inv_userid');
			$data=array('store_id'=>$store,'claim_code'=>$claim_code,'sales_id'=>(int)$device->sales_id,'salesitems_id'=>$sales_item_id,'barcode_id'=>(int)$device->barcode_id,'customer_id'=>(int)$device->customer_id ?: null,'item_id'=>(int)$device->item_id,'status'=>'reported','issue'=>$issue,'received_at'=>$now,'created_by'=>$actor,'updated_by'=>$actor,'created_at'=>$now,'updated_at'=>$now);
			if (!$this->db->insert('db_warranty_claims',$data)) throw new RuntimeException('Could not create the warranty claim.');
			$id=(int)$this->db->insert_id();
			$this->db->insert('db_warranty_claim_events',array('store_id'=>$store,'claim_id'=>$id,'from_status'=>null,'to_status'=>'reported','note'=>$issue,'created_by'=>$actor,'created_at'=>$now));
			if ($this->db->trans_status()===false) throw new RuntimeException('Could not save the warranty claim.');
			$this->db->trans_commit(); return $id;
		} catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
	}

	public function update_status($claim_id,$status,$decision_note='',$resolution_note='') {
		$claim_id=(int)$claim_id; $status=trim((string)$status); $decision_note=trim((string)$decision_note); $resolution_note=trim((string)$resolution_note);
		if (strlen($decision_note)>8000 || strlen($resolution_note)>8000) throw new InvalidArgumentException('Notes are limited to 8,000 characters.');
		$this->db->trans_begin();
		try {
			$store=(int)get_current_store_id();
			$claim=$this->db->where('id',$claim_id)->where('store_id',$store)->get('db_warranty_claims')->row();
			if (!$claim) throw new InvalidArgumentException('Warranty claim not found.');
			if (!in_array($status,$this->transitions[$claim->status] ?? array(),true)) throw new InvalidArgumentException('That status change is not allowed for this claim.');
			if (in_array($status,array('approved','rejected'),true) && $decision_note==='') throw new InvalidArgumentException('Record the inspection decision before approving or rejecting the claim.');
			if (in_array($status,array('repaired','replaced','refunded','resolved'),true) && $resolution_note==='') throw new InvalidArgumentException('Record how the customer issue was resolved.');
			$now=date('Y-m-d H:i:s'); $actor=(int)$this->session->userdata('inv_userid');
			$update=array('status'=>$status,'updated_by'=>$actor,'updated_at'=>$now);
			if ($decision_note!=='') $update['decision_note']=$decision_note;
			if ($resolution_note!=='') $update['resolution_note']=$resolution_note;
			if (in_array($status,array('rejected','resolved','cancelled'),true)) $update['closed_at']=$now;
			$this->db->where('id',$claim_id)->where('store_id',$store)->update('db_warranty_claims',$update);
			$event_note=trim($decision_note."\n".$resolution_note);
			$this->db->insert('db_warranty_claim_events',array('store_id'=>$store,'claim_id'=>$claim_id,'from_status'=>$claim->status,'to_status'=>$status,'note'=>$event_note ?: null,'created_by'=>$actor,'created_at'=>$now));
			if ($this->db->trans_status()===false) throw new RuntimeException('Could not update the warranty claim.');
			$this->db->trans_commit(); return true;
		} catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
	}
}
