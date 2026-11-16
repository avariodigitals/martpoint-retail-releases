<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Store-scoped invoice payment URLs shared by authenticated and signed PDFs. */
class Invoice_payment_link {
    private $CI;
    public function __construct(){ $this->CI =& get_instance(); }

    public function forSales($salesId, $storeId): string {
        $salesId=(int)$salesId;$storeId=(int)$storeId;
        if($salesId<1 || $storeId<1 || !$this->CI->db->field_exists('invoice_payment_token','db_sales')) return '';
        $invoice=$this->CI->db->select('id,store_id,sales_status,grand_total,invoice_payment_token')
            ->where('id',$salesId)->where('store_id',$storeId)->get('db_sales')->row();
        if(!$invoice || $invoice->sales_status!=='Final') return '';
        $confirmed=$this->CI->db->field_exists('confirmation_status','db_salespayments')
            ? ' AND COALESCE(confirmation_status,1)=1' : '';
        $paid=$this->CI->db->query('SELECT COALESCE(SUM(payment),0) AS paid FROM db_salespayments WHERE sales_id=? AND store_id=? AND status=1'.$confirmed,[$salesId,$storeId])->row();
        if(round((float)$invoice->grand_total-(float)($paid->paid ?? 0),2)<=0) return '';
        $this->CI->load->library('storefront_currency');
        if($this->CI->storefront_currency->baseCode($storeId)!=='NGN') return '';
        $this->CI->load->model('paystack_model','invoice_paystack');
        if(!$this->CI->invoice_paystack->is_enabled($storeId)) return '';
        $gateway=$this->CI->invoice_paystack->get_settings($storeId);
        if(!$gateway || empty($gateway->public_key)
            || (isset($gateway->configured) ? empty($gateway->configured) : empty($gateway->secret_key))) return '';
        $payout=$this->CI->invoice_paystack->get_payout_account($storeId);
        if(!$payout || empty($payout->provider_subaccount_code) || (int)$payout->enabled!==1) return '';
        $token=trim((string)($invoice->invoice_payment_token ?? ''));
        if($token===''){
            try{$candidate=bin2hex(random_bytes(32));}catch(Throwable $e){log_message('error','Could not securely create an invoice payment token.');return '';}
            if(!$this->CI->db->query("UPDATE db_sales SET invoice_payment_token=? WHERE id=? AND store_id=? AND (invoice_payment_token IS NULL OR invoice_payment_token='')",[$candidate,$salesId,$storeId])) return '';
            // A concurrent PDF request may have minted the winning token.
            $row=$this->CI->db->select('invoice_payment_token')->where('id',$salesId)->where('store_id',$storeId)->get('db_sales')->row();
            $token=trim((string)($row->invoice_payment_token ?? ''));
        }
        return preg_match('/^[a-f0-9]{64}$/D',$token) ? site_url('invoice/pay/'.$token) : '';
    }
}
