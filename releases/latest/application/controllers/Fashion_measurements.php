<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fashion_measurements extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load_global();
    }

    private function enabled() {
        if (!mp_feature_enabled('fashion_measurements')) {
            $this->show_feature_not_activated('fashion_measurements');
            return false;
        }
        return true;
    }

    private function dress_types() {
        $sid = (int)get_current_store_id();
        return $this->db->where('is_active', 1)->where_in('store_id', [0, $sid])
            ->order_by('sort_order', 'asc')->order_by('name', 'asc')->get('db_fashion_dress_types')->result();
    }

    public function save($customer_id) {
        $this->belong_to('db_customers', $customer_id);
        $this->permission_check('customers_edit');
        if (!$this->enabled()) return;
        $sid = (int)get_current_store_id();
        $customer = $this->db->where('id',(int)$customer_id)->where('store_id',$sid)->get('db_customers')->row();
        if (!$customer) show_404();
        $dressId = (int)$this->input->post('dress_type_id');
        $dress = $this->db->where('id',$dressId)->where_in('store_id',[0,$sid])->where('is_active',1)->get('db_fashion_dress_types')->row();
        $dressName = $dress ? $dress->name : trim((string)$this->input->post('custom_dress_type', TRUE));
        $values = $this->measurement_values($this->input->post());
        if ($dressName === '' || !$values) {
            $this->session->set_flashdata('failed','Choose a dress type and enter at least one measurement.');
            redirect($this->return_profile($customer_id)); return;
        }
        $this->db->insert('db_fashion_measurements', [
            'store_id'=>$sid,'customer_id'=>(int)$customer_id,'dress_type_id'=>$dress ? (int)$dress->id : null,
            'dress_type_name'=>$dressName,'size_standard'=>$this->size_standard($this->input->post('size_standard')),
            'unit'=>$this->unit($this->input->post('unit')),'measurements_json'=>json_encode($values),
            'notes'=>trim((string)$this->input->post('notes', TRUE)) ?: null,'source'=>'staff',
            'created_by'=>$this->session->userdata('inv_username'),'created_by_id'=>(int)$this->session->userdata('inv_userid'),
            'created_at'=>date('Y-m-d H:i:s')
        ]);
        $this->session->set_flashdata('success','Measurements saved to the customer notebook.');
        redirect($this->return_profile($customer_id));
    }

    public function add_dress_type($customer_id) {
        $this->belong_to('db_customers', $customer_id);
        $this->permission_check('customers_edit');
        if (!$this->enabled()) return;
        $name = trim((string)$this->input->post('name', TRUE));
        if ($name !== '') {
            $name=mb_substr($name,0,100);$sid=(int)get_current_store_id();
            if(!$this->db->where('store_id',$sid)->where('name',$name)->count_all_results('db_fashion_dress_types')) $this->db->insert('db_fashion_dress_types',['store_id'=>$sid,'name'=>$name,'sort_order'=>500]);
        }
        redirect($this->return_profile($customer_id));
    }

    public function update_dress_type($customer_id, $type_id) {
        $this->belong_to('db_customers', $customer_id);
        $this->permission_check('customers_edit');
        if (!$this->enabled()) return;
        $name=trim((string)$this->input->post('name',TRUE));
        $sid=(int)get_current_store_id();$name=mb_substr($name,0,100);
        if($name==='') {
            $this->db->where('id',(int)$type_id)->where('store_id',$sid)->update('db_fashion_dress_types',['is_active'=>0]);
        } else {
            $duplicate=$this->db->where('store_id',$sid)->where('name',$name)->where('id !=',(int)$type_id)->count_all_results('db_fashion_dress_types');
            if(!$duplicate) $this->db->where('id',(int)$type_id)->where('store_id',$sid)->update('db_fashion_dress_types',['name'=>$name,'is_active'=>1]);
        }
        redirect($this->return_profile($customer_id));
    }

    public function create_request($customer_id = 0) {
        $this->permission_check('customers_edit');
        if (!mp_feature_enabled('fashion_size_requests')) { $this->show_feature_not_activated('fashion_size_requests'); return; }
        $sid=(int)get_current_store_id();
        $customer=null;
        if((int)$customer_id>0){
            $this->belong_to('db_customers', $customer_id);
            $customer=$this->db->where('id',(int)$customer_id)->where('store_id',$sid)->get('db_customers')->row();
            if(!$customer) show_404();
        }
        $token=bin2hex(random_bytes(24));
        $this->db->insert('db_fashion_size_requests',[
            'store_id'=>$sid,'customer_id'=>$customer?(int)$customer->id:null,'token_hash'=>hash('sha256',$token),
            'size_standard'=>$this->size_standard($this->input->post('size_standard')),
            'unit'=>$this->unit($this->input->post('unit')),'status'=>'open',
            'expires_at'=>date('Y-m-d H:i:s',strtotime('+30 days')),'created_by'=>(int)$this->session->userdata('inv_userid'),
            'created_at'=>date('Y-m-d H:i:s')
        ]);
        $this->session->set_flashdata('size_request_link',base_url('size-request/'.$token));
        redirect((int)$customer_id>0?$this->return_profile($customer_id):($this->input->post('mobile_view')?'mobile/customers':'customers'));
    }

    private function measurement_values($post) {
        $labels=['bust'=>'Bust / chest','waist'=>'Waist','hip'=>'Hip','shoulder'=>'Shoulder','neck'=>'Neck','sleeve'=>'Sleeve','armhole'=>'Armhole','wrist'=>'Wrist','top_length'=>'Top length','trouser_length'=>'Trouser length','inseam'=>'Inseam','skirt_length'=>'Skirt / gown length','thigh'=>'Thigh','knee'=>'Knee','ankle'=>'Ankle'];
        $out=[];
        foreach($labels as $key=>$label) {
            $value=trim((string)($post['measurements'][$key] ?? ''));
            if($value!=='' && is_numeric($value) && (float)$value>0) $out[$key]=['label'=>$label,'value'=>round((float)$value,2)];
        }
        $ready=trim((string)($post['ready_to_wear_size'] ?? ''));
        if($ready!=='') $out['ready_to_wear_size']=['label'=>'Ready-to-wear size','value'=>mb_substr($ready,0,30)];
        return $out;
    }
    private function size_standard($v) { return strtoupper((string)$v)==='US'?'US':'UK'; }
    private function unit($v) { return strtolower((string)$v)==='in'?'in':'cm'; }
    private function return_profile($customer_id) {
        return $this->input->post('mobile_view') ? 'mobile/customer_profile/'.(int)$customer_id.'?tab=measurements' : 'customers/profile/'.(int)$customer_id.'#measurements';
    }
}
