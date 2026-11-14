<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Bed/outpatient cost-centre accounts, separate from bank/cash accounts. */
class Physio_accounts_model extends CI_Model {
	public function systemEnabled($storeId = null){
		$sid=$storeId ?: get_current_store_id();
		if(!$this->db->field_exists('system_account_id','db_physio_payment_accounts')) return false;
		return $this->db->query('SELECT policy_value FROM db_inpatient_policies WHERE store_id=? AND policy_key="bed_system_accounts_enabled"',array((int)$sid))->row('policy_value') === '1';
	}
	/** Explicit enable provisions every existing bed, without importing old receipts. */
	public function provisionSystemAccounts($storeId){
		if(!$this->db->field_exists('system_account_id','db_physio_payment_accounts') || !$this->db->field_exists('physio_system_account_id','db_sales')) throw new RuntimeException('Run migration 4.0.9.133 before enabling system accounts');
		if(!$this->db->table_exists('ac_accounts') || !$this->db->table_exists('ac_transactions')) throw new RuntimeException('The Accounts module must be installed first');
		$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',array((int)$storeId));
		$beds=$this->db->query('SELECT b.id,b.bed_label,w.name ward_name FROM db_beds b JOIN db_wards w ON w.id=b.ward_id AND w.store_id=b.store_id WHERE b.store_id=?',array((int)$storeId))->result();
		foreach($beds as $b){
			$this->db->query('INSERT IGNORE INTO db_physio_payment_accounts (store_id,account_code,bed_id) VALUES (?,?,?)',array((int)$storeId,'BED-'.$b->id,$b->id));
			$a=$this->db->query('SELECT * FROM db_physio_payment_accounts WHERE store_id=? AND bed_id=? FOR UPDATE',array((int)$storeId,$b->id))->row();
			if($a->system_account_id) continue;
			$code='PHYSIO-BED-'.$b->id;
			if($this->db->where('store_id',$storeId)->where('account_code',$code)->count_all_results('ac_accounts')) throw new RuntimeException('Account code '.$code.' already exists; review it before enabling');
			$sort=$this->db->select('COALESCE(MAX(id),0)+1 AS next_id',false)->get('ac_accounts')->row()->next_id;
			if(!$this->db->insert('ac_accounts',array('store_id'=>$storeId,'count_id'=>get_count_id('ac_accounts',$storeId),'account_code'=>$code,'account_name'=>$b->ward_name.' / '.$b->bed_label.' — Collections','sort_code'=>(string)$sort,'parent_id'=>0,'note'=>'Physio bed collections; created when system-account billing was enabled.','created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>$this->session->userdata('inv_username') ?: 'system','status'=>1))) throw new RuntimeException('Could not create bed system account');
			$id=$this->db->insert_id();
			if(!$this->db->where('id',$a->id)->where('store_id',$storeId)->update('db_physio_payment_accounts',array('system_account_id'=>$id))) throw new RuntimeException('Could not link bed system account');
		}
	}
	public function systemContext($customerId,$storeId){
		if(!$this->systemEnabled($storeId)) return array('enabled'=>false,'bed'=>null,'account'=>null,'choices'=>array());
		$bed=$this->db->query('SELECT b.id,b.bed_label,w.name ward_name,a.system_account_id FROM db_bed_occupancy o JOIN db_patients p ON p.id=o.patient_id AND p.store_id=o.store_id JOIN db_beds b ON b.id=o.bed_id AND b.store_id=o.store_id JOIN db_wards w ON w.id=b.ward_id AND w.store_id=b.store_id LEFT JOIN db_physio_payment_accounts a ON a.bed_id=b.id AND a.store_id=b.store_id WHERE o.store_id=? AND p.customer_id=? AND o.to_at IS NULL ORDER BY o.id DESC LIMIT 1',array((int)$storeId,(int)$customerId))->row();
		$account=$bed && $bed->system_account_id ? $this->db->where('id',$bed->system_account_id)->where('store_id',$storeId)->where('status',1)->where('delete_bit',0)->get('ac_accounts')->row() : null;
		$choices=$bed ? array() : $this->db->query('SELECT a.* FROM ac_accounts a WHERE a.store_id=? AND a.status=1 AND a.delete_bit=0 AND NOT EXISTS (SELECT 1 FROM db_physio_payment_accounts p WHERE p.store_id=a.store_id AND p.system_account_id=a.id AND p.bed_id IS NOT NULL) ORDER BY a.account_name',array((int)$storeId))->result();
		return array('enabled'=>true,'bed'=>$bed,'account'=>$account,'choices'=>$choices);
	}
	public function resolveSystemAccount($customerId,$storeId,$selected=null){
		$c=$this->systemContext($customerId,$storeId);
		if(!$c['enabled']) return null;
		if($c['bed']){
			if(!$c['account']) throw new RuntimeException('The current bed has no active system account. Ask the manager to review its account.');
			if($selected && (int)$selected !== (int)$c['account']->id) throw new RuntimeException('Inpatient payments must use the current bed account');
			return (int)$c['account']->id;
		}
		foreach($c['choices'] as $a) if((int)$a->id===(int)$selected) return (int)$a->id;
		throw new RuntimeException('Choose an active outpatient account that is not attached to a bed');
	}
	public function enabled($storeId = null){
		$storeId=$storeId ?: get_current_store_id();
		return $this->db->query('SELECT policy_value FROM db_inpatient_policies WHERE store_id=? AND policy_key="bed_accounts_enabled"',array((int)$storeId))->row('policy_value') === '1';
	}
	public function account($storeId, $bedId = null){
		if(!$this->enabled($storeId)) return null;
		$key = $bedId ? 'BED-'.(int)$bedId : 'OUTPATIENT';
		$this->db->query('INSERT IGNORE INTO db_physio_payment_accounts (store_id,account_code,bed_id) VALUES (?,?,?)', array((int)$storeId,$key,$bedId ?: null));
		return (int)$this->db->query('SELECT id FROM db_physio_payment_accounts WHERE store_id=? AND account_code=?', array((int)$storeId,$key))->row('id');
	}
	/** Call within the financial transaction; occupancy changes share this lock. */
	public function forCustomer($customerId, $storeId){
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		if(!physio_enabled($storeId) || !$this->enabled($storeId)) return null;
		$p = $this->db->query('SELECT id FROM db_patients WHERE store_id=? AND customer_id=? FOR UPDATE', array((int)$storeId,(int)$customerId))->row();
		if(!$p) return $this->account($storeId);
		$o = $this->db->query('SELECT bed_id FROM db_bed_occupancy WHERE store_id=? AND patient_id=? AND to_at IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE', array((int)$storeId,(int)$p->id))->row();
		return $this->account($storeId,$o ? (int)$o->bed_id : null);
	}
	public function accounts(){
		return $this->db->query('SELECT a.*,b.bed_label,w.name ward_name FROM db_physio_payment_accounts a LEFT JOIN db_beds b ON b.id=a.bed_id AND b.store_id=a.store_id LEFT JOIN db_wards w ON w.id=b.ward_id AND w.store_id=b.store_id WHERE a.store_id=? ORDER BY w.name,b.bed_label,a.id', array(get_current_store_id()))->result();
	}
	public function ledger($accountId){
		$sid=(int)get_current_store_id();
		$a=$this->db->query('SELECT a.*,b.bed_label,w.name ward_name FROM db_physio_payment_accounts a LEFT JOIN db_beds b ON b.id=a.bed_id AND b.store_id=a.store_id LEFT JOIN db_wards w ON w.id=b.ward_id AND w.store_id=b.store_id WHERE a.store_id=? AND a.id=?',array($sid,(int)$accountId))->row();
		if(!$a) return null;
		$charges=$this->db->query('SELECT i.id,i.description,i.total_cost amount,s.id sales_id,s.sales_code,s.customer_id,c.customer_name,s.grand_total,s.paid_amount,s.sales_date date FROM db_salesitems i JOIN db_sales s ON s.id=i.sales_id AND s.store_id=i.store_id JOIN db_customers c ON c.id=s.customer_id AND c.store_id=s.store_id WHERE i.store_id=? AND i.physio_account_id=? AND s.sales_status="Final" AND s.status=1 AND i.status=1 ORDER BY i.id',array($sid,(int)$accountId))->result();
		$payments=$this->db->query('SELECT p.id,p.payment_code,p.payment_type,p.payment amount,p.payment_date date,p.payment_note,s.id sales_id,s.sales_code,s.customer_id,c.customer_name,s.grand_total,s.paid_amount FROM db_salespayments p JOIN db_sales s ON s.id=p.sales_id AND s.store_id=p.store_id JOIN db_customers c ON c.id=s.customer_id AND c.store_id=s.store_id WHERE p.store_id=? AND p.physio_account_id=? AND p.status=1 AND s.status=1 ORDER BY p.id',array($sid,(int)$accountId))->result();
		$deposits=$this->db->query('SELECT t.id,t.txn_type,t.direction,t.amount,t.created_date date,c.customer_name FROM db_patient_wallet_txns t JOIN db_customers c ON c.id=t.customer_id AND c.store_id=t.store_id WHERE t.store_id=? AND t.physio_account_id=? AND t.txn_type IN ("fund","refund") ORDER BY t.id',array($sid,(int)$accountId))->result();
		$totals=array('billed'=>0,'settled'=>0,'collected'=>0,'deposits'=>0,'refunds'=>0);
		foreach($charges as $r) $totals['billed'] += (float)$r->amount;
		foreach($payments as $r){
			$totals['settled'] += (float)$r->amount;
			if(!in_array($r->payment_type,array('patient_wallet','wallet_reversal','cust_advance'))) $totals['collected'] += (float)$r->amount;
		}
		foreach($deposits as $r){ if($r->txn_type==='fund') $totals['deposits']+=(float)$r->amount; else $totals['refunds']+=(float)$r->amount; }
		$totals['net_collected']=$totals['collected']+$totals['deposits']-$totals['refunds'];
		$totals['ledger_balance']=$totals['billed']-$totals['settled'];
		$invoices=array(); foreach(array_merge($charges,$payments) as $r) $invoices[$r->sales_id]=$r;
		return array('account'=>$a,'charges'=>$charges,'payments'=>$payments,'deposits'=>$deposits,'totals'=>$totals,'invoices'=>$invoices);
	}
}
