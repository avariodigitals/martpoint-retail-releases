<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Catalogue_lookup_model extends CI_Model {
    public function find($kind,$name,$storeId,$exclude=0){
        $table=$kind==='category'?'db_category':'db_brands';
        $field=$kind==='category'?'category_name':'brand_name';
        $key=mb_strtolower(mp_catalogue_name($name),'UTF-8');
        $rows=$this->db->where('store_id',(int)$storeId)->order_by('id')->get($table)->result();
        $match=null;
        foreach($rows as $row){
            if((int)$row->id===(int)$exclude || mb_strtolower(mp_catalogue_name($row->$field),'UTF-8')!==$key)continue;
            if(!$match || ((string)$row->$field===mp_catalogue_name($row->$field) && (string)$match->$field!==mp_catalogue_name($match->$field)))$match=$row;
        }
        return $match;
    }
    public function create($kind,$name,$storeId,$description=''){
        if(!in_array($kind,array('brand','category'),true))throw new InvalidArgumentException('Choose Brand or Category.');
        $name=mp_catalogue_name($name);$storeId=(int)$storeId;
        if($name==='' || mb_strlen($name,'UTF-8')>100 || preg_match('/[<>\x00-\x1f]/u',$name))throw new InvalidArgumentException('Enter a plain-text name of 1–100 characters.');
        $this->db->trans_begin();
        try{
            $store=$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',array($storeId))->row();
            if(!$store)throw new RuntimeException('Store not found.');
            $existing=$this->find($kind,$name,$storeId);
            if($existing){
                if((int)$existing->status!==1)throw new RuntimeException('This name already exists but is inactive. Activate the existing record instead.');
                $this->db->trans_commit();return array('id'=>(int)$existing->id,'name'=>mp_catalogue_name($kind==='category'?$existing->category_name:$existing->brand_name),'existing'=>true);
            }
            $table=$kind==='category'?'db_category':'db_brands';$field=$kind==='category'?'category_name':'brand_name';
            $row=array('store_id'=>$storeId,$field=>$name,'description'=>$description,'status'=>1);
            if($this->db->field_exists('count_id',$table))$row['count_id']=(int)get_count_id($table,$storeId);
            if($kind==='category')$row['category_code']=(string)mp_get_store_inventory_setting($storeId,'category_init','CT/').str_pad((string)$row['count_id'],4,'0',STR_PAD_LEFT);
            if($kind==='category' && $this->db->field_exists('parent_id',$table))$row['parent_id']=null;
            if(!$this->db->insert($table,$row) || !$this->db->trans_status())throw new RuntimeException('Could not save the catalogue name.');
            $id=(int)$this->db->insert_id();$this->db->trans_commit();
            if(isset($this->category_tree_model))$this->category_tree_model->clear();
            return array('id'=>$id,'name'=>$name,'existing'=>false);
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
}
