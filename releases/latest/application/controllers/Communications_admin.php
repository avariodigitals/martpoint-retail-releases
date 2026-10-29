<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Communications_admin extends MY_Controller {
    public function __construct(){parent::__construct();if(!mp_is_central()){show_404();exit;}$this->load_global();if(!special_access()){show_error('Central administrator access is required.',403);exit;}$this->load->model('communications_model');$this->load->library('messaging_pricing');}
    public function index(){
        $d=$this->data;$d['page_title']='Communications · Central';$d['SITE_TITLE']='MartPoint Central';$d['central_console']=true;$d['comm_ready']=$this->communications_model->ready();
        if($d['comm_ready']){
            $review=$this->communications_model->review();$d['sms_review']=$review?array('settings'=>$review->settings(),'bundles'=>$review->bundles(null,true),'requests'=>$this->db->order_by('id','desc')->limit(100)->get('db_communication_sender_requests')->result_array(),'documents'=>$this->db->select('id,account_id,kind,reference_id,slot,filename')->order_by('id','desc')->limit(500)->get('db_communication_documents')->result_array()):null;
            $policy=$this->communications_model->automationPlans();$this->load->model('marketing_model');
            $d['automation_admin']=$policy?array('available'=>true,'plans'=>$policy->plans(true),'templates'=>$policy->templates(true)):array('available'=>false);$d['segments']=Marketing_model::$SEGMENTS;
            $d['catalogue']=$this->communications_model->catalogue();$d['comm_settings']=$this->db->where('id',1)->get('db_communication_settings')->row_array();
            $this->db->select('a.*,f.install_url')->from('db_messaging_accounts a')->join('db_fleet_installs f','f.id=a.fleet_id','left')->where('a.fleet_id >',0);
            if($fleetId=(int)$this->input->get('fleet'))$this->db->where('a.fleet_id',$fleetId);
            $d['accounts']=$this->db->order_by('a.id','desc')->limit(500)->get()->result_array();
            $d['fleets']=$this->db->select('id,install_url')->order_by('id','desc')->limit(500)->get('db_fleet_installs')->result_array();
            $d['fleet_states']=$this->db->get('db_communication_fleet_modules')->result_array();
            $d['selected_fleet']=(int)$this->input->get('fleet');
            $d['module_states']=$this->db->get('db_communication_modules')->result_array();
            $d['requests']=$this->db->select('r.*,a.store_name,f.install_url')->from('db_communication_requests r')->join('db_messaging_accounts a','a.id=r.account_id')->join('db_fleet_installs f','f.id=a.fleet_id','left')->order_by('r.id','desc')->limit(200)->get()->result_array();
            if($policy)foreach($d['requests'] as &$request)if($request['module_key']==='automation')$request['automation_plan_key']=$policy->requestPlan($request['id']);unset($request);
            $d['pending_notifications']=$this->db->where('delivered_at IS NULL',null,false)->count_all_results('db_communication_events');
            $d['selected']=null;if($id=(int)$this->input->get('account')){$a=$this->db->where('id',$id)->get('db_messaging_accounts')->row_array();if($a)$d['selected']=array('account'=>$a,'communications'=>$this->communications_model->snapshot($id));}
        }
        $screen=$this->input->get('screen');
        $this->load->model('communication_template_model');
        $d['hub_ready']=$this->communication_template_model->ready();$d['hub_designs']=array();$d['hub_editing']=null;
        if($d['hub_ready']){
            if($this->communication_template_model->hubCount()===0){try{$this->communication_template_model->seedHub((string)$this->session->userdata('username'));}catch(Throwable $e){$this->db->trans_rollback();}}
            $d['hub_designs']=$this->communication_template_model->hub(Communication_template_model::HUB_LIMIT,false);
            $hubParam=(string)$this->input->get('hub_design');
            if($hubParam==='new')$d['hub_editing']=array();
            elseif((int)$hubParam>0)$d['hub_editing']=$this->communication_template_model->getHub((int)$hubParam);
        }
        if($screen==='mobile' || ($screen!=='desktop' && is_mobile())){$this->load->view('mobile/communications',$d);return;}$d['content']=$this->load->view('communications/central',$d,true);$this->load->view('mp_layout',$d);
    }
    public function document($id=0){$this->load->library('communication_documents');$r=$this->db->where('id',(int)$id)->get('db_communication_documents')->row_array();if(!$r){show_404();return;}Communication_documents::download($r);}
    /** Preview of an unsaved library design, built by the same renderer a store sends with. */
    public function preview(){
        if($this->input->method()!=='post'){show_error('POST is required.',405);return;}
        $this->load->model('communication_template_model');
        $p=array();foreach(array('name','format','category','industry','occasion','subject','preheader','accent','brand_display','show_unsubscribe','cta_label','cta_url') as $field)$p[$field]=(string)$this->input->post($field);
        if(trim($p['name'])==='')$p['name']='MartPoint design';
        $blocks=json_decode((string)$this->input->post('blocks'),true);
        $p['blocks']=is_array($blocks)?$blocks:array();
        $this->output->set_content_type('application/json')->set_header('Cache-Control: no-store')
            ->set_output(json_encode(array('success'=>true,'html'=>Communication_designs::preview($p,'Your store')),JSON_INVALID_UTF8_SUBSTITUTE));
    }
    public function save(){
        if($this->input->method()!=='post'){show_error('POST is required.',405);return;}
        $id=(int)$this->input->post('account_id');$actor=(string)$this->session->userdata('username');
        $message='Communications updated.';$hubEditor=null;
        $this->load->model('communication_template_model');
        try{
            $action=$this->input->post('action');$p=$this->input->post(null,true);
            if($action==='sms_master')$this->communications_model->review()->saveSettings($p,$actor);
            elseif($action==='credit_bundle')$this->communications_model->review()->saveBundle($p,$actor);
            elseif($action==='sender_review')$this->communications_model->review()->reviewSender((int)$p['request_id'],$p['status'],$p['note']??'',$actor);
            elseif($action==='automation_plan')$this->communications_model->automationPlans(true)->savePlan($p,$actor);
            elseif($action==='automation_template')$this->communications_model->automationPlans(true)->saveTemplate($p,$actor);
            elseif($action==='catalogue')$this->communications_model->saveCatalogue($p,$actor);
            elseif($action==='settings'){
                foreach(array('sales_email','technical_email') as $field)if(($field==='sales_email' || !empty($p[$field])) && !filter_var($p[$field]??'',FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter valid sales and optional technical email addresses.');
                $this->db->where('id',1)->update('db_communication_settings',array('sales_email'=>$p['sales_email'],'technical_email'=>$p['technical_email']??'','bank_instructions'=>mb_substr($p['bank_instructions']??'',0,4000)));
            }elseif($action==='fleet_allow')$this->communications_model->allowFleet((int)$p['fleet_id'],$p['module_key'],!empty($p['allowed']),$actor);
            elseif($action==='allow')$this->communications_model->allow($id,$p['module_key'],!empty($p['allowed']),$actor);
            elseif($action==='activate')$this->communications_model->activate($id,$p['module_key'],$p['period'],$p['source'],true,$actor,$p['note']??'',$p['operation_key']??'',$p['plan_key']??null);
            elseif($action==='resolve')$this->communications_model->resolve((int)$p['request_id'],!empty($p['approve']),$actor,$p['note']??'');
            elseif($action==='enrol'){
                $this->load->model('messaging_wallet_model');$fleet=(int)($p['fleet_id']??0);if(!$this->db->where('id',$fleet)->count_all_results('db_fleet_installs'))throw new RuntimeException('Fleet not found.');
                $a=$this->messaging_wallet_model->account($fleet,array('store_id'=>(int)($p['store_id']??0),'store_name'=>trim($p['store_name']??'Store')));$this->communications_model->provision($a['id']);$id=$a['id'];
            }elseif($action==='transfer_legacy'){
                // Explicit conversion only. Never silently reinterpret an existing paid NGN wallet.
                $this->load->model('messaging_wallet_model','messaging_wallet');$channel=$p['module_key']??'';if(!in_array($channel,array('email','sms'),true))throw new RuntimeException('Choose SMS or email.');$amount=Messaging_pricing::money($p['amount']??'');if($amount<1)throw new RuntimeException('Enter an amount.');
                $operationKey=(string)($p['operation_key']??'');if(!preg_match('/^[a-f0-9]{40}$/D',$operationKey))throw new RuntimeException('Reload the conversion form.');
                $this->db->trans_begin();try{$a=$this->db->query('SELECT * FROM db_messaging_accounts WHERE id=? FOR UPDATE',array($id))->row_array();$event='legacy:'.$operationKey;$already=$this->db->where('account_id',$id)->where('event_key',$event)->count_all_results('db_messaging_ledger');if($already){$this->db->trans_commit();redirect('communications_admin?account='.$id);return;}if(!$a || (int)$a['balance_kobo']<$amount)throw new RuntimeException('Insufficient legacy balance.');$units=intdiv($amount,$this->communications_model->rate($channel));if($units<1)throw new RuntimeException('Amount is below one credit.');$debit=$units*$this->communications_model->rate($channel);$this->communications_model->provision($id);$this->communications_model->changeUnits($id,$channel,$units,$event,null,'Legacy NGN balance conversion.',$actor,true);$this->db->where('id',$id)->update('db_messaging_accounts',array('balance_kobo'=>(int)$a['balance_kobo']-$debit));$this->db->insert('db_messaging_ledger',array('account_id'=>$id,'event_key'=>$event,'kind'=>'conversion','amount_kobo'=>-$debit,'balance_after_kobo'=>(int)$a['balance_kobo']-$debit,'note'=>'Converted to '.$units.' '.$channel.' credits.','actor'=>$actor,'created_at'=>gmdate('Y-m-d H:i:s')));if(!$this->db->trans_status())throw new RuntimeException('Conversion failed.');$this->db->trans_commit();}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
            }elseif($action==='verify'){
                $this->load->library('messaging_payments');$r=$this->db->where('id',(int)$p['request_id'])->get('db_communication_requests')->row_array();$purchase=$r?$this->db->where('reference',$r['payment_reference'])->get('db_messaging_purchases')->row_array():null;if(!$purchase)throw new RuntimeException('Payment not found.');$v=$this->messaging_payments->verify($purchase['provider'],$purchase['reference']);if(empty($v['success']))throw new RuntimeException($v['message']);
            }elseif($action==='hub_template_save'){
                // Read the design fields unfiltered: block text is escaped when the email is built,
                // so it must reach the library exactly as the editor wrote it.
                $design=array();foreach(array('name','format','category','industry','occasion','subject','preheader','blocks','accent','brand_display','show_unsubscribe','cta_label','cta_url') as $field)$design[$field]=$this->input->post($field);
                $design['kind']='prebuilt';
                $hubEditor=$this->communication_template_model->save(Communication_template_model::HUB,$design,$actor,(int)$this->input->post('template_id'));
                $message='Design published to the MartPoint library. Stores can add a copy of it.';
            }elseif($action==='hub_template_enable'){
                $this->communication_template_model->setEnabled(Communication_template_model::HUB,(int)($p['template_id']??0),!empty($p['enabled']));
                $message='Library design updated.';
            }elseif($action==='hub_template_delete'){
                $this->communication_template_model->remove(Communication_template_model::HUB,(int)($p['template_id']??0));
                $message='Design removed from the MartPoint library. Copies stores already added stay in their accounts.';
            }elseif($action==='hub_seed'){
                $added=$this->communication_template_model->seedHub($actor);
                $message=$added?$added.' designs added to the MartPoint library.':'The MartPoint library already has every current design.';
            }elseif($action==='notify')$this->communications_model->notify(3);
            else throw new RuntimeException('Unknown action.');
            $this->session->set_flashdata('success',$message);
            // Notifications use the durable outbox. Cron is the delivery worker.
        }catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $query=array();
        if($hubEditor!==null)$query['hub_design']=(int)$hubEditor;
        elseif($id)$query['account']=$id;
        if($this->input->post('screen')==='mobile')$query['screen']='mobile';
        redirect('communications_admin'.($query?('?'.http_build_query($query)):''));
    }
}
