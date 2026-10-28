<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Authenticated view/print only. No archive write endpoints. */
class Customer_history extends MY_Controller {
    public function __construct(){
        parent::__construct();$this->load_global();
        if($this->input->method(true)!=='GET'){show_error('Historical records are read-only.',405);exit;}
        $this->permission_check('customers_view');$this->load->model('legacy_history_model','history');
    }
    private function mobile(){
        $screen=$this->input->get('screen');
        if(in_array($screen,array('mobile','desktop'),true))return $screen==='mobile';
        return $this->input->get('mobile')==='1' || is_mobile();
    }
    private function render($view,$data){
        $store=$this->db->select('store_name')->where('id',(int)get_current_store_id())->get('db_store')->row();
        $data['SITE_TITLE']=$store?$store->store_name:'MartPoint';
        if($this->mobile()){$data['history_view']=$view;$this->load->view('mobile/customer_history',$data);}
        else{$data['content']=$this->load->view('customer_history/'.$view,$data,true);$this->load->view('mp_layout',$data);}
    }
    public function index($customer_id=0){
        $store=(int)get_current_store_id();$this->belong_to('db_customers',(int)$customer_id);
        $data=$this->data;$data['customer']=$this->db->where('id',(int)$customer_id)->where('store_id',$store)->get('db_customers')->row();
        if(!$data['customer']){show_404();return;}
        $type=$this->input->get('type');if(!in_array($type,array('quote','invoice'),true))$type='';
        $page=max(1,(int)$this->input->get('page'));
        $data=array_merge($data,$this->history->documents($customer_id,$store,$type,$page));
        $data['history_type']=$type;$data['history_page']=$page;$data['history_mobile']=$this->mobile();$data['page_title']='Customer History';
        $data['native_quotes']=$this->db->where('customer_id',(int)$customer_id)->where('store_id',$store)->order_by('quotation_date','DESC')->get('db_quotation')->result();
        $this->render('index',$data);
    }
    public function document($id=0){
        $store=(int)get_current_store_id();$doc=$this->history->document($id,$store);
        if(!$doc){show_404();return;}
        $this->belong_to('db_customers',$doc->customer_id);
        $data=$this->data;$data['document']=$doc;$data['history_mobile']=$this->mobile();$data['page_title']='Historical '.ucfirst($doc->document_type);
        $data['customer']=$this->db->where('id',$doc->customer_id)->where('store_id',$store)->get('db_customers')->row();
        if(!$data['customer']){show_404();return;}
        $this->render('document',$data);
    }
}
