<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Bed/outpatient cost-centre accounts, separate from bank/cash accounts. */
class Physio_accounts_model extends CI_Model {
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
