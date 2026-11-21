<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_ads_model extends CI_Model {
    public function connection($storeId) {
        if (!$this->db->table_exists('db_ai_ads_connections')) return null;
        return $this->db->where('store_id',(int)$storeId)->get('db_ai_ads_connections')->row_array();
    }
    public function subscription($storeId) {
        if (!$this->db->table_exists('db_ai_ads_subscriptions')) return null;
        return $this->db->where('store_id',(int)$storeId)->order_by('id','desc')->limit(1)->get('db_ai_ads_subscriptions')->row_array();
    }
    public function hasAccess($storeId) {
        $s=$this->subscription($storeId); if (!$s || !in_array($s['status'],array('active','grace'),true) || empty($s['expires_at'])) return false;
        return strtotime($s['expires_at'].' +'.(int)$s['grace_days'].' days') >= time();
    }
    public function campaigns($storeId) {
        if (!$this->db->table_exists('db_ai_ads_campaigns')) return array();
        return $this->db->where('store_id',(int)$storeId)->order_by('id','desc')->limit(100)->get('db_ai_ads_campaigns')->result_array();
    }
    public function products($storeId) {
        if (!$this->db->table_exists('db_items')) return array();
        $this->db->select('id, item_name'.($this->db->field_exists('sales_price','db_items')?', sales_price':'').($this->db->field_exists('description','db_items')?', description':'').($this->db->field_exists('item_image','db_items')?', item_image':''));
        $this->db->where('store_id',(int)$storeId);
        if ($this->db->field_exists('status','db_items')) $this->db->where('status',1);
        return $this->db->order_by('id','desc')->limit(100)->get('db_items')->result_array();
    }
    public function audit($storeId,$campaignId,$actorId,$event,$details='') {
        if (!$this->db->table_exists('db_ai_ads_audit')) return;
        $this->db->insert('db_ai_ads_audit',array('store_id'=>$storeId?(int)$storeId:null,'campaign_id'=>$campaignId?(int)$campaignId:null,'actor_id'=>$actorId?(int)$actorId:null,'event_key'=>substr($event,0,64),'details'=>mb_substr(strip_tags((string)$details),0,2000)));
    }
    public function saveDraft($storeId,$userId,array $data) {
        $key=hash('sha256',(int)$storeId.'|'.(string)$data['request_key']);
        $existing=$this->db->where('store_id',(int)$storeId)->where('request_key',$key)->get('db_ai_ads_campaigns')->row_array();
        if ($existing) return (int)$existing['id'];
        $this->db->insert('db_ai_ads_campaigns',array('store_id'=>(int)$storeId,'created_by'=>(int)$userId,'request_key'=>$key,'prompt'=>$data['prompt'],'product_id'=>$data['product_id']?:null,'headline'=>$data['headline'],'body'=>$data['body'],'destination_url'=>$data['destination_url'],'location_text'=>$data['location_text'],'daily_budget_minor'=>$data['daily_budget_minor'],'starts_at'=>$data['starts_at'],'ends_at'=>$data['ends_at'],'status'=>'draft'));
        return (int)$this->db->insert_id();
    }
}
