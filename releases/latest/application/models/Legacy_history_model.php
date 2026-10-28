<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Read-only archive adapter. Every query is store scoped; no catalogue lookups. */
class Legacy_history_model extends CI_Model {
    private $ready;
    public function available(){
        if($this->ready===null)$this->ready=$this->db->table_exists('db_legacy_documents') && $this->db->table_exists('db_legacy_transactions');
        return $this->ready;
    }
    public function counts($customer,$store){
        if(!$this->available())return array();
        return $this->db->select('document_type,COUNT(*) AS n',false)->where('store_id',(int)$store)->where('customer_id',(int)$customer)->group_by('document_type')->get('db_legacy_documents')->result_array();
    }
    public function has_customer($customer,$store){
        return $this->available() && $this->db->where('store_id',(int)$store)->where('customer_id',(int)$customer)->count_all_results('db_legacy_customers')>0;
    }
    public function documents($customer,$store,$type='',$page=1){
        if(!$this->available())return array('rows'=>array(),'total'=>0);
        $this->db->where('store_id',(int)$store)->where('customer_id',(int)$customer);
        if(in_array($type,array('invoice','quote'),true))$this->db->where('document_type',$type);
        $total=$this->db->count_all_results('db_legacy_documents');
        $this->db->where('store_id',(int)$store)->where('customer_id',(int)$customer);
        if(in_array($type,array('invoice','quote'),true))$this->db->where('document_type',$type);
        $rows=$this->db->order_by('document_date','DESC')->order_by('id','DESC')->limit(50,(max(1,(int)$page)-1)*50)->get('db_legacy_documents')->result();
        return array('rows'=>$rows,'total'=>$total);
    }
    public function document($id,$store){
        if(!$this->available())return null;
        $doc=$this->db->where('id',(int)$id)->where('store_id',(int)$store)->get('db_legacy_documents')->row();
        if(!$doc)return null;
        $doc->lines=$this->db->where('document_id',$doc->id)->where('store_id',(int)$store)->order_by('source_id','ASC')->get('db_legacy_lines')->result();
        $source=json_decode($doc->source_json,true)?:array();$doc->notes=$source['notes']??'';
        return $doc;
    }
    public function statement_rows($customer,$store){
        if(!$this->available())return array();
        $rows=array();$docs=$this->db->where('store_id',(int)$store)->where('customer_id',(int)$customer)->order_by('id','ASC')->get('db_legacy_documents')->result();
        // One tenant/customer query for lines; source descriptions remain text.
        $lines=$this->db->select('l.*')->from('db_legacy_lines l')->join('db_legacy_documents d','d.id=l.document_id AND d.store_id=l.store_id')->where('d.store_id',(int)$store)->where('d.customer_id',(int)$customer)->order_by('l.source_id','ASC')->get()->result_array();
        $byDoc=array();foreach($lines as $line)$byDoc[$line['document_id']][]=$line;
        foreach($docs as $d){
            $rows[]=array('date'=>$d->document_date,'sort'=>'h1:'.str_pad($d->id,20,'0',STR_PAD_LEFT),'type'=>'history_'.$d->document_type,'description'=>'Rose '.ucfirst($d->document_type).' · '.$d->source_status,'reference'=>$d->document_number.' (Rose ID '.$d->source_id.')','debit'=>0,'credit'=>0,'balance'=>0,'historical'=>true,'historical_amount'=>$d->total,'historical_paid'=>$d->paid_amount,'history_document_id'=>$d->id,'review_reason'=>$d->review_reason,'raw_date'=>$d->raw_date,'history_lines'=>$byDoc[$d->id]??array());
        }
        $payments=$this->db->where('store_id',(int)$store)->group_start()->where('customer_id',(int)$customer)->or_where('payer_customer_id',(int)$customer)->group_end()->order_by('id','ASC')->get('db_legacy_transactions')->result();
        foreach($payments as $p){
            $reason=$p->review_reason;
            if($p->payer_customer_id && $p->customer_id!=$p->payer_customer_id)$reason=trim(($reason?:'').'; Shown as related activity only; ownership unverified','; ');
            $rows[]=array('date'=>$p->payment_date,'sort'=>'h2:'.str_pad($p->id,20,'0',STR_PAD_LEFT),'type'=>'history_payment','description'=>'Rose transaction · '.($p->payment_method?:'Payment'),'reference'=>'Rose receipt '.$p->source_id,'debit'=>0,'credit'=>0,'balance'=>0,'historical'=>true,'historical_debit'=>$p->debit,'historical_credit'=>$p->credit,'history_document_id'=>$p->document_id,'review_reason'=>$reason,'raw_date'=>$p->raw_date,'history_lines'=>array());
        }
        return $rows;
    }
}
