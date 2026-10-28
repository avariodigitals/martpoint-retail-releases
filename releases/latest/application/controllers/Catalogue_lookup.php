<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Catalogue_lookup extends MY_Controller {
    public function __construct(){parent::__construct();$this->load_global();$this->load->model('catalogue_lookup_model','lookups');}
    public function create(){
        if(strtoupper($this->input->method())!=='POST'){$this->reply(405,array('success'=>false,'message'=>'POST is required.'));return;}
        $kind=$this->input->post('kind');
        if(!in_array($kind,array('brand','category'),true)){$this->reply(400,array('success'=>false,'message'=>'Invalid catalogue type.'));return;}
        if(!$this->permissions($kind==='category'?'items_category_add':'brand_add')){$this->reply(403,array('success'=>false,'message'=>'You do not have permission to create this catalogue type.'));return;}
        try{
            $row=$this->lookups->create($kind,$this->input->post('name'),get_current_store_id());
            $this->reply(200,array_merge(array('success'=>true),$row));
        }catch(Throwable $e){$this->reply(422,array('success'=>false,'message'=>$e->getMessage()));}
    }
    private function reply($status,$data){
        $data['csrf_name']=$this->security->get_csrf_token_name();$data['csrf_hash']=$this->security->get_csrf_hash();
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
