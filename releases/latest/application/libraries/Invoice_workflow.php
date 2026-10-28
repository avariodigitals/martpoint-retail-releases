<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Standard document state is separate from payment state and POS checkout. */
class Invoice_workflow {
    private $CI;
    public function __construct() { $this->CI =& get_instance(); }
    public function status($invoice) {
        if ((string)($invoice->sales_status ?? '') === 'Draft') return 'draft';
        return ($invoice->invoice_status ?? '') === 'sent' ? 'sent' : 'issued';
    }
    public function prepare($standard, $command, $id, $storeId, $postedState, $timing, $amount, $mode, $customerId) {
        $db=$this->CI->db; $previous=null;
        if ($command==='update') {
            $q=$db->query('SELECT * FROM db_sales WHERE id=? AND store_id=? FOR UPDATE',array((int)$id,(int)$storeId));
            $previous=$q?$q->row():null;
            if (!$previous) throw new RuntimeException('Invoice not found for this store.');
        }
        if (!$standard) {
            if ($previous && $this->status($previous)==='draft') throw new RuntimeException('Open this draft in the standard invoice editor before issuing it.');
            return array('state'=>'issued','sales_status'=>'Final','previous'=>$previous,'paid'=>0);
        }
        $state=strtolower(trim((string)$postedState));
        if ($state==='') $state=$previous?$this->status($previous):'issued';
        if (!in_array($state,array('draft','issued','sent'),true)) throw new RuntimeException('Choose Draft or Issued for this invoice.');
        if ($state==='sent' && (!$previous || $this->status($previous)!=='sent')) throw new RuntimeException('Save the issued invoice, then mark it as sent after sharing it.');
        if ($previous && $this->status($previous)!=='draft' && $state==='draft') throw new RuntimeException('An issued invoice cannot be changed back to a draft.');
        if ($previous && $this->status($previous)==='sent' && $state==='issued') $state='sent';
        if ($previous && !empty($previous->pos) && $state==='draft') throw new RuntimeException('POS sales cannot be changed into invoice drafts.');
        $q=$db->query('SELECT id,store_id FROM db_customers WHERE id=?',array((int)$customerId));
        $customer=$q?$q->row():null;
        if (!$customer || ((int)$customer->store_id!==(int)$storeId && !is_walk_in_customer($customerId))) throw new RuntimeException('Select a customer belonging to this store.');
        if (!is_finite((float)$amount) || (float)$amount<0) throw new RuntimeException('Enter a valid non-negative payment amount.');
        $q=$previous?$db->query('SELECT COALESCE(SUM(payment),0) AS paid FROM db_salespayments WHERE sales_id=? AND status=1',array((int)$id)):null;
        $paid=$q?(float)$q->row()->paid:0;
        if ($previous && $paid>0 && (int)$previous->customer_id!==(int)$customerId) throw new RuntimeException('An invoice with payments cannot be reassigned to another customer.');
        if ($state==='draft' && ((float)$amount>0 || $paid>0)) throw new RuntimeException('Draft invoices cannot accept payments. Issue the invoice first.');
        if (!in_array((string)$timing,array('','later','now'),true)) throw new RuntimeException('Choose Pay later or Record a payment now.');
        if ($timing==='later' && (float)$amount>0) throw new RuntimeException('Pay later cannot record a payment now. Clear the amount or choose Record a payment now.');
        if ((float)$amount>0 && trim((string)$mode)==='') throw new RuntimeException('Choose a payment method for the amount being recorded.');
        if ($timing==='now' && $state!=='draft' && (float)$amount<=0) throw new RuntimeException('Enter the payment received, or choose Pay later.');
        return array('state'=>$state,'sales_status'=>$state==='draft'?'Draft':'Final','previous'=>$previous,'paid'=>$paid);
    }
    public function markSent($id,$storeId) {
        $db=$this->CI->db;
        if (!$db->field_exists('invoice_status','db_sales') || !$db->field_exists('invoice_sent_at','db_sales')) throw new RuntimeException('Apply the standard invoice workflow migration before marking invoices as sent.');
        $db->trans_begin();
        try {
            $q=$db->query('SELECT * FROM db_sales WHERE id=? AND store_id=? FOR UPDATE',array((int)$id,(int)$storeId));
            $row=$q?$q->row():null;
            if (!$row || !empty($row->pos)) throw new RuntimeException('Standard invoice not found for this store.');
            if ($this->status($row)==='draft') throw new RuntimeException('Issue the draft before marking it as sent.');
            if ($this->status($row)!=='sent' && !$db->where('id',(int)$id)->where('store_id',(int)$storeId)->update('db_sales',array('invoice_status'=>'sent','invoice_sent_at'=>gmdate('Y-m-d H:i:s')))) throw new RuntimeException('Could not record the sent status.');
            if (!$db->trans_status()) throw new RuntimeException('Could not record the sent status.');
            $db->trans_commit(); return true;
        } catch (Throwable $e) { $db->trans_rollback(); throw $e; }
    }
}
