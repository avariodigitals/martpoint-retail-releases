<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Public, single-purpose form opened from a Fashion measurement request link. */
class Size_request extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url','form','business_profile','custom']);
    }

    private function request_row($token) {
        if (!preg_match('/^[a-f0-9]{48}$/', (string)$token) || !$this->db->table_exists('db_fashion_size_requests')) return null;
        return $this->db->where('token_hash',hash('sha256',$token))->where('status','open')
            ->group_start()->where('expires_at IS NULL',null,false)->or_where('expires_at >=',date('Y-m-d H:i:s'))->group_end()
            ->get('db_fashion_size_requests')->row();
    }

    public function index($token = '') {
        $request=$this->request_row($token);
        if(!$request || !mp_feature_enabled_for_store('fashion_size_requests',(int)$request->store_id)) show_404();
        $data=$this->page_data($request,$token);
        $this->load->view('mobile/fashion_size_request',$data);
    }

    public function submit($token = '') {
        $request=$this->request_row($token);
        if(!$request || !mp_feature_enabled_for_store('fashion_size_requests',(int)$request->store_id)) show_404();
        $sid=(int)$request->store_id;
        $fields=['bust'=>'Bust / chest','waist'=>'Waist','hip'=>'Hip','shoulder'=>'Shoulder','neck'=>'Neck','sleeve'=>'Sleeve','armhole'=>'Armhole','wrist'=>'Wrist','top_length'=>'Top length','trouser_length'=>'Trouser length','inseam'=>'Inseam','skirt_length'=>'Skirt / gown length','thigh'=>'Thigh','knee'=>'Knee','ankle'=>'Ankle'];
        $posted=$this->input->post(NULL,TRUE);$postedMeasurements=is_array($posted['measurements']??null)?$posted['measurements']:[];
        $measurements=[];
        foreach($fields as $key=>$label){$v=trim((string)($postedMeasurements[$key]??''));if($v!==''&&is_numeric($v)&&(float)$v>0)$measurements[$key]=['label'=>$label,'value'=>round((float)$v,2)];}
        $size=trim((string)$this->input->post('custom_size',TRUE));
        if($size==='') $size=trim((string)$this->input->post('ready_to_wear_size',TRUE));
        if($size!=='')$measurements['ready_to_wear_size']=['label'=>'Ready-to-wear size','value'=>mb_substr($size,0,30)];
        $dressId=(int)$this->input->post('dress_type_id');
        $dress=$this->db->where('id',$dressId)->where_in('store_id',[0,$sid])->where('is_active',1)->get('db_fashion_dress_types')->row();
        $dressName=$dress?$dress->name:trim((string)$this->input->post('custom_dress_type',TRUE));
        $sizeStandard=strtoupper((string)$this->input->post('size_standard',TRUE))==='US'?'US':'UK';
        $unit=strtolower((string)$this->input->post('unit',TRUE))==='in'?'in':'cm';
        $name=trim((string)$this->input->post('customer_name',TRUE));
        $phone=trim((string)$this->input->post('mobile',TRUE));
        $customer=$request->customer_id ? $this->db->where('id',(int)$request->customer_id)->where('store_id',$sid)->get('db_customers')->row() : null;
        if(!$customer && $phone!=='') $customer=$this->db->where('mobile',$phone)->where('store_id',$sid)->get('db_customers')->row();
        if(!$customer && ($name==='' || !$phone)) return $this->render_error($request,$token,'Please enter your name and phone number so the tailor can save your measurements.');
        if(!$measurements || $dressName==='') return $this->render_error($request,$token,'Choose a dress type and enter at least one measurement.');
        $this->db->trans_begin();
        if(!$customer){
            $nextCount=(int)$this->db->select('COALESCE(MAX(count_id),0)+1 AS next_count',false)->where('store_id',$sid)->get('db_customers')->row()->next_count;
            $this->db->insert('db_customers',['store_id'=>$sid,'count_id'=>$nextCount,'customer_code'=>'CUS'.str_pad((string)$nextCount,4,'0',STR_PAD_LEFT),'customer_name'=>$name,'mobile'=>$phone,'email'=>trim((string)$this->input->post('email',TRUE)),'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>'Size request','status'=>1]);
            $customerId=(int)$this->db->insert_id();
        } else $customerId=(int)$customer->id;
        if(!$customerId){$this->db->trans_rollback();return $this->render_error($request,$token,'We could not save your details. Please try again.');}
        $this->db->insert('db_fashion_measurements',['store_id'=>$sid,'customer_id'=>$customerId,'dress_type_id'=>$dress?(int)$dress->id:null,'dress_type_name'=>$dressName,'size_standard'=>$sizeStandard,'unit'=>$unit,'measurements_json'=>json_encode($measurements),'notes'=>trim((string)$this->input->post('notes',TRUE))?:null,'source'=>'customer_link','created_by'=>$customer?$customer->customer_name:$name,'created_at'=>date('Y-m-d H:i:s')]);
        $this->db->where('id',(int)$request->id)->update('db_fashion_size_requests',['customer_id'=>$customerId,'size_standard'=>$sizeStandard,'unit'=>$unit,'status'=>'submitted','submitted_at'=>date('Y-m-d H:i:s')]);
        if($this->db->trans_status()===false){$this->db->trans_rollback();return $this->render_error($request,$token,'We could not save your measurements. Please try again.');}
        $this->db->trans_commit();
        $data=$this->page_data($request,$token);$data['submitted']=true;
        $this->load->view('mobile/fashion_size_request',$data);
    }

    private function render_error($request,$token,$error){$data=$this->page_data($request,$token);$data['error']=$error;$this->load->view('mobile/fashion_size_request',$data);}
    private function page_data($request,$token){
        $store=$this->db->select('store_name')->where('id',(int)$request->store_id)->get('db_store')->row();
        $customer=$request->customer_id?$this->db->select('customer_name')->where('id',(int)$request->customer_id)->where('store_id',(int)$request->store_id)->get('db_customers')->row():null;
        return ['request'=>$request,'token'=>$token,'store_name'=>$store->store_name??'MartPoint','customer'=>$customer,'dress_types'=>$this->db->where('is_active',1)->where_in('store_id',[0,(int)$request->store_id])->order_by('sort_order')->order_by('name')->get('db_fashion_dress_types')->result(),'SITE_TITLE'=>$store->store_name??'MartPoint','theme_link'=>base_url('theme/'),'error'=>null,'submitted'=>false];
    }
}
