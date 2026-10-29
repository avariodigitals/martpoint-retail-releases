<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Communications extends MY_Controller {
    public function __construct(){parent::__construct();$this->load_global();$this->load->library('messaging_client');$this->load->library('communication_templates');$this->load->library('communication_sms_templates');$this->load->model('marketing_model','marketing_m');$this->load->model('communication_template_model');}
    private function canInbox(){return is_admin() || is_store_admin() || $this->permissions('online_store_view') || $this->permissions('online_store_edit') || $this->permissions('online_store_orders');}
    private function canReply(){return $this->canInbox() && (is_admin() || is_store_admin() || $this->permissions('online_store_edit') || $this->permissions('online_store_orders'));}
    private function can($key){if(in_array($key,array('whatsapp','socials'),true))return $this->canInbox() || $this->permissions('send_sms') || $this->permissions('send_email');return is_admin() || is_store_admin() || $this->permissions($key==='sms'?'send_sms':'send_email') || ($key==='automation' && $this->permissions('send_sms'));}
    private function mobile(){ $screen=$this->input->get('screen')?:$this->input->post('screen');return $screen==='mobile' || ($screen!=='desktop' && is_mobile()); }
    private function guard($key='email'){if(!$this->can($key)){show_error('Communications access denied.',403);exit;}}
    private function post(){if($this->input->method()!=='post'){show_error('POST is required.',405);exit;}}
    private function go($key='',$section='dashboard'){
        $query=array('section'=>$section);if($this->mobile())$query['screen']='mobile';
        redirect('communications'.($key?'/channel/'.$key:'').'?'.http_build_query($query));
    }
    public function index(){if(mp_is_central()){redirect('communications_admin');return;}if(!$this->can('email') && !$this->can('sms') && $this->canInbox()){$this->go('whatsapp','inbox');return;}$this->guard($this->can('email')?'email':'sms');$this->page('');}
    public function channel($key=''){if(!in_array($key,array('sms','email','whatsapp','socials','automation'),true)){show_404();return;}$this->guard($key);$this->page($key);}
    private function page($key){
        $storeId=get_current_store_id();
        $sections=array(
            'sms'=>array('dashboard','campaigns','compose','templates','audience','report','sender','history','recharge','subscription','requests'),
            'email'=>array('dashboard','campaigns','compose','templates','build','audience','report','sender','history','recharge','subscription','requests'),
            'automation'=>array('dashboard','workflows','create','activity','subscription','requests'),
            'whatsapp'=>array('inbox','conversation','connection','campaigns','dashboard'),'socials'=>array('inbox','conversation','connection','dashboard'),''=>array('dashboard'));
        $section=(string)$this->input->get('section');if(!in_array($section,$sections[$key],true))$section=in_array($key,array('whatsapp','socials'),true)?'inbox':'dashboard';
        $titles=array('sms'=>'SMS','email'=>'Email','automation'=>'Automation','whatsapp'=>'WhatsApp','socials'=>'Socials',''=>'Communications');
        $d=$this->data;$d['channel']=$key;$d['page_title']=$titles[$key];$d['store_name']=get_store_name();$d['SITE_TITLE']=$d['store_name'];
        $d['communication_view']=$key?:'overview';$d['workspace_section']=$section;
        $d['messaging']=in_array($key,array('whatsapp','socials'),true)?array():$this->messaging_client->request('snapshot',$storeId,array('history_page'=>(int)$this->input->get('history_page'),'history_channel'=>$key));
        $d['can_inbox']=$this->canInbox();$d['can_reply']=$this->canReply();
        if(in_array($key,array('whatsapp','socials'),true)){
            $this->load->model('communication_inbox_model');$inboxChannel=$key==='whatsapp'?'whatsapp':($this->input->get('inbox_channel')==='messenger'?'messenger':'instagram');$d['inbox_channel']=$inboxChannel;$d['inbox_ready']=$this->communication_inbox_model->ready();$d['inbox_connection']=$this->canInbox()?$this->communication_inbox_model->connection($storeId,$inboxChannel):array('configured'=>false);$d['inbox_page']=max(0,(int)$this->input->get('inbox_page'));$d['conversations']=array();$d['conversation']=null;$d['inbox_messages']=array();
            if($d['inbox_ready'] && $this->canInbox()){$d['conversations']=$this->communication_inbox_model->conversations($storeId,$inboxChannel,$d['inbox_page']);if($section==='conversation'){$id=(int)$this->input->get('conversation');$d['conversation']=$this->communication_inbox_model->conversation($storeId,$id,$inboxChannel);if(!$d['conversation']){show_404();return;}$d['inbox_messages']=$this->communication_inbox_model->messages($storeId,$id,$inboxChannel,(int)$this->input->get('before'));}}
        }
        $d['sms_templates']=Communication_sms_templates::catalogue();$smsTemplate=(string)$this->input->get('sms_template');$d['selected_sms_template']=isset($d['sms_templates'][$smsTemplate])?$smsTemplate:'custom';
        $d['templates']=Communication_templates::catalogue();$template=(string)$this->input->get('template');$d['selected_template']=isset($d['templates'][$template])?$template:'letter';
        // Own designs, the MartPoint hub and the design being edited (Email Studio only).
        $d['designs']=array();$d['hub_designs']=array();$d['hub_message']='';$d['editing']=null;$d['design_family']='any';$d['designs_saved']=false;
        if($key==='email'){
            $d['designs_saved']=$this->canBindDesigns();
            if($d['designs_saved']){
                $d['designs']=$this->communication_template_model->forStore($storeId);
                $industry='';
                if(function_exists('mp_get_store_profile')){try{$profile=mp_get_store_profile($storeId);$industry=(string)($profile['industry_type']??'');}catch(Throwable $e){$industry='';}}
                $d['design_family']=Communication_designs::family($industry);
                if($section==='templates'){
                    if(mp_is_central())$d['hub_designs']=$this->communication_template_model->hub();
                    else{
                        $hub=$this->messaging_client->request('template_hub',$storeId,array());
                        if(!empty($hub['success']) && isset($hub['designs']) && is_array($hub['designs']))$d['hub_designs']=array_slice($hub['designs'],0,Communication_template_model::HUB_LIMIT);
                        else $d['hub_message']=(string)($hub['message']??'Your MartPoint designs are unavailable right now.');
                    }
                }
                if($section==='build' && ($id=(int)$this->input->get('design'))>0)$d['editing']=$this->communication_template_model->get($storeId,$id);
            }
            $d['selected_design']='k:'.$d['selected_template'];
            $useDesign=(int)$this->input->get('use_design');
            if($useDesign>0){foreach($d['designs'] as $ownDesign)if((int)$ownDesign['id']===$useDesign){$d['selected_design']='d:'.$useDesign;break;}}
        }
        $recipe=(string)$this->input->get('recipe');$d['selected_recipe']=preg_match('/^[a-z][a-z0-9_]{1,39}$/D',$recipe)?$recipe:'';
        $d['segments']=Marketing_model::$SEGMENTS;$d['purchase_key']=bin2hex(random_bytes(20));$d['can_email']=$this->can('email');$d['can_sms']=$this->can('sms');
        $d['campaigns']=array_filter($this->marketing_m->listCampaigns($storeId),function($c)use($key){return $c->channel===$key;});
        $this->load->model('communication_schedule_model');foreach($d['campaigns'] as $campaign)$campaign->schedule=$this->communication_schedule_model->get($storeId,$campaign->id);
        $d['local_ready']=$this->db->table_exists('db_communication_contacts');$d['automations']=array();$d['automation_runs']=array();$d['contacts']=array();$d['contact_count']=0;
        $d['audience_sources']=array();$d['audience_options']=array();$d['audience_preview']=null;
        if(in_array($key,array('sms','email'),true)){
            $this->load->library('communication_audience');$this->load->model('communication_lists_model','comm_lists');
            $d['audience_sources']=$this->communication_audience->catalogue($storeId,$key);
            $d['contact_count']=$d['audience_sources']['customer_contacts']['eligible'];
            $d['audience_options'][$key]=$this->marketing_m->audienceOptions($storeId,$key);
            $d['audience_lists']=$this->comm_lists->all($storeId,$key);
            $d['audience_list_cap']=$this->audienceListCap($d['messaging']);
            $source=(string)$this->input->get('audience');if(!isset(Communication_audience::definitions()[$source]) && Communication_lists_model::id($source)<1)$source='all';$d['selected_audience']=$source;$d['selected_segment']=$source;
            if($section==='audience')$d['audience_preview']=$this->marketing_m->audienceSummary($storeId,$source,$key);
        }
        $d['report_campaign']=null;$d['report_list']=array();$d['report_summary']=array();$d['report_timeline']=array();$d['report_links']=array();
        $d['report_recipients']=array('rows'=>array(),'total'=>0,'page'=>1,'pages'=>1,'per_page'=>25,'filter'=>'all');$d['report_filter']='all';$d['report_delivery']=array();
        if(in_array($key,array('sms','email'),true) && $section==='report'){
            $this->load->model('communication_report_model','comm_report');
            if(!$this->comm_report->ready()){$this->go($key,'campaigns');return;}
            $campaignId=(int)$this->input->get('campaign');
            if($campaignId<1){
                $d['report_list']=array_slice($d['campaigns'],0,25);
            }else{
                $campaign=$this->comm_report->campaign($storeId,$campaignId);
                if(!$campaign || $campaign['channel']!==$key){show_404();return;}
                $filter=(string)$this->input->get('filter');$d['report_filter']=in_array($filter,Communication_report_model::FILTERS,true)?$filter:'all';
                $d['report_campaign']=$campaign;
                $d['report_summary']=$this->comm_report->summary($storeId,$campaignId);
                $d['report_timeline']=$this->comm_report->timeline($storeId,$campaignId);
                $d['report_links']=$this->comm_report->links($storeId,$campaignId);
                $d['report_recipients']=$this->comm_report->recipients($storeId,$campaignId,$d['report_filter'],(int)$this->input->get('report_page'));
                // The provider answers MartPoint, not the store, so the report asks for
                // the one thing the app cannot see itself: held vs definitely rejected.
                $delivery=$this->messaging_client->request('campaign_report',$storeId,array('campaign_id'=>(string)$campaignId,'channel'=>$key));
                if(!empty($delivery['success']))$d['report_delivery']=$delivery;
            }
        }
        $d['sender_identity']=array('name'=>'','reply_to'=>'','saved_name'=>'','saved_reply'=>'','pinned_name'=>'','pinned_reply_to'=>'','can_edit'=>false);
        if($key==='email' && $section==='sender'){
            $identity=$this->messaging_client->senderIdentity($storeId);
            $saved=$this->db->table_exists('db_email_settings')?$this->db->where('store_id',$storeId)->get('db_email_settings')->row_array():array();
            $account=$d['messaging']['account']??array();
            $d['sender_identity']=array('name'=>$identity['name'],'reply_to'=>$identity['reply_to'],
                'saved_name'=>(string)($saved['email_from_name']??''),'saved_reply'=>(string)($saved['email_reply_to']??''),
                'pinned_name'=>(string)($account['sender_name']??''),'pinned_reply_to'=>(string)($account['sender_reply_to']??''),
                'can_edit'=>is_admin() || $this->permissions('smtp_settings'));
        }
        if($key==='automation')foreach(array('email','sms') as $audienceChannel)if($d['can_'.$audienceChannel]){$d['audience_options'][$audienceChannel]=$this->marketing_m->audienceOptions($storeId,$audienceChannel);if($this->marketing_m->canUseSegment('abandoned_carts')){$this->load->model('communication_cart_recovery_model');try{$eligible=count($this->communication_cart_recovery_model->candidates($storeId,$audienceChannel));}catch(Throwable $e){$eligible=0;}$d['audience_options'][$audienceChannel]['abandoned_carts']=array('key'=>'abandoned_carts','name'=>'Abandoned online-store carts','group'=>'Online store events','eligible'=>$eligible);}}
        if($key==='automation' && $this->db->table_exists('db_communication_automations')){
            $this->db->select('a.*')->from('db_communication_automations a')->where('a.store_id',$storeId);
            if($this->db->table_exists('db_communication_auto_workflows'))$this->db->join('db_communication_auto_workflows m','m.workflow_id=a.id AND m.store_id=a.store_id','left')->group_start()->where('m.archived IS NULL',null,false)->or_where('m.archived',0)->group_end();
            $d['automations']=$this->db->order_by('a.id','desc')->get()->result_array();
            if($this->db->table_exists('db_communication_automation_runs'))$d['automation_runs']=$this->db->select('r.*,a.name,a.channel')->from('db_communication_automation_runs r')->join('db_communication_automations a','a.id=r.automation_id')->where('a.store_id',$storeId)->order_by('r.id','desc')->limit(50)->get()->result_array();
        }
        if($this->mobile()){$this->load->view('mobile/communications',$d);return;}
        $d['content']=$this->load->view('communications/'.$d['communication_view'],$d,true);$this->load->view('mp_layout',$d);
    }
    public function action(){
        $this->post();$key=(string)$this->input->post('module_key');$this->guard($key);$action=(string)$this->input->post('action');
        if(!in_array($action,array('communication_request','communication_toggle','purchase','verify_purchase'),true)){show_error('Unknown action.',400);return;}
        $p=array();foreach(array('module_key','purpose','period','plan_key','package_id','amount','email','provider','request_key','enabled','reference') as $field)$p[$field]=$this->input->post($field);
        $r=$this->messaging_client->request($action,get_current_store_id(),$p);
        if($action==='purchase' && !empty($r['success']) && parse_url($r['checkout_url']??'',PHP_URL_SCHEME)==='https'){redirect($r['checkout_url']);return;}
        $this->session->set_flashdata(!empty($r['success'])?'success':'failed',$r['message']??'Request saved.');$this->go($key,$action==='verify_purchase'?'requests':($p['purpose']==='recharge'?'recharge':'subscription'));
    }
    public function import_contacts(){
        $this->post();$channel=$this->input->post('channel')==='sms'?'sms':'email';$this->guard($channel);
        $this->load->model('communication_lists_model','comm_lists');$storeId=get_current_store_id();
        try{
            if(!$this->db->table_exists('db_communication_contacts'))throw new RuntimeException('Contact imports are not available yet. Contact your Administrator.');
            if(!$this->input->post('consent'))throw new RuntimeException('Confirm permission to contact these recipients.');
            $raw=(string)$this->input->post('contacts');
            if(!empty($_FILES['contacts_file']['tmp_name'])){if($_FILES['contacts_file']['error']!==UPLOAD_ERR_OK || $_FILES['contacts_file']['size']>1048576 || !is_uploaded_file($_FILES['contacts_file']['tmp_name']))throw new RuntimeException('Upload a CSV or vCard file up to 1 MB.');$raw=file_get_contents($_FILES['contacts_file']['tmp_name']);}
            if(strlen($raw)>1048576)throw new RuntimeException('Import at most 1 MB at a time.');
            // Decide the destination first so a full list allowance never leaves a
            // store with contacts imported into a place they did not ask for.
            $listId=(int)$this->input->post('list_id');$newName=trim((string)$this->input->post('new_list_name'));
            if($newName!=='' || $listId===-1)$listId=$this->comm_lists->create($storeId,$newName,$channel,'',(string)$this->session->userdata('inv_username'),$this->audienceListCap());
            elseif($listId>0 && !$this->comm_lists->get($storeId,$listId))throw new RuntimeException('Choose one of your audience lists, or create a new one.');
            $this->load->library('communication_contacts');$result=$this->communication_contacts->parse($raw,$channel);
            $count=0;$this->db->trans_begin();foreach($result['contacts'] as $r){$this->db->query('INSERT IGNORE INTO db_communication_contacts (store_id,name,email,phone,consent_at) VALUES (?,?,?,?,UTC_TIMESTAMP())',array($storeId,$r['name'],$r['email'],$r['phone']));$count+=$this->db->affected_rows();}if(!$this->db->trans_status())throw new RuntimeException('Import could not be saved.');$this->db->trans_commit();
            $message=$count.' contacts imported; '.$result['invalid'].' invalid rows skipped. Duplicate contacts were ignored.';
            if($listId>0){
                $recipients=array();foreach($result['contacts'] as $r)$recipients[]=$channel==='sms'?$r['phone']:$r['email'];
                $this->comm_lists->attachRecipients($storeId,$listId,$channel,$recipients);
                $list=$this->comm_lists->get($storeId,$listId);
                if($list)$message.=' All of them are now in “'.$list['name'].'”, which holds '.number_format((int)$list['contact_count']).' contacts.';
            }
            $this->session->set_flashdata('success',$message);
        }catch(Throwable $e){$this->db->trans_rollback();$this->session->set_flashdata('failed',$e->getMessage());}$this->go($channel,'audience');
    }
    public function list_action(){
        $this->post();$channel=$this->input->post('channel')==='sms'?'sms':'email';$this->guard($channel);
        $this->load->model('communication_lists_model','comm_lists');$storeId=get_current_store_id();
        $action=(string)$this->input->post('action');$listId=(int)$this->input->post('list_id');
        $actor=(string)($this->session->userdata('inv_username') ?: 'store');
        try{
            if($action==='create'){$this->comm_lists->create($storeId,(string)$this->input->post('name'),$channel,(string)$this->input->post('note'),$actor,$this->audienceListCap());$this->session->set_flashdata('success','Audience list created. Import contacts into it whenever you are ready.');}
            elseif($action==='rename'){$this->comm_lists->rename($storeId,$listId,(string)$this->input->post('name'),(string)$this->input->post('note'));$this->session->set_flashdata('success','Audience list updated.');}
            elseif($action==='delete'){$this->comm_lists->remove($storeId,$listId);$this->session->set_flashdata('success','Audience list removed. Your imported contacts were kept.');}
            else throw new RuntimeException('Choose create, rename or remove.');
        }catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $this->go($channel,'audience');
    }
    /** Your Administrator sets the audience list allowance; an unreadable figure falls back to the app default. */
    private function audienceListCap(array $snapshot=null){
        if($snapshot===null)$snapshot=(array)$this->messaging_client->request('snapshot',get_current_store_id(),array());
        if(!array_key_exists('audience_list_cap',$snapshot))return Communication_lists_model::DEFAULT_CAP;
        return max(0,(int)$snapshot['audience_list_cap']);
    }
    public function save_campaign(){
        $this->post();$channel=$this->input->post('channel')==='sms'?'sms':'email';$this->guard($channel);
        try{
            if(!$this->db->table_exists('db_communication_campaign_designs'))throw new RuntimeException('Campaigns are not available yet. Contact your Administrator.');
            $name=trim((string)$this->input->post('name'));$message=trim((string)$this->input->post('message'));$subject=trim((string)$this->input->post('subject'));$segment=(string)$this->input->post('segment_key');$template=(string)$this->input->post('template_key');
            if($segment==='abandoned_carts')throw new RuntimeException('Use Automation → Recover abandoned carts for cart reminders.');
            if(!$this->marketing_m->canUseSegment($segment,get_current_store_id()))throw new RuntimeException('You do not have permission to use this audience.');
            if(!$name || !$message || strlen($message)>20000 || ($channel==='email' && (!$subject || preg_match('/[\r\n]/',$subject))))throw new RuntimeException('Enter a campaign name, audience, subject and message.');
            $templateId=0;
            if($channel==='email'){
                $design=(string)$this->input->post('design');
                if(preg_match('/^d:([1-9]\d{0,9})$/D',$design,$m)){
                    if(!$this->canBindDesigns())throw new RuntimeException('Your own email designs need an application update. Contact your Administrator.');
                    $stored=$this->communication_template_model->get(get_current_store_id(),(int)$m[1]);
                    if(!$stored || empty($stored['enabled']))throw new RuntimeException('Choose one of your email designs.');
                    $templateId=(int)$m[1];$template='';
                }elseif(preg_match('/^k:([a-z][a-z0-9_]{1,19})$/D',$design,$m) && isset(Communication_templates::catalogue()[$m[1]]))$template=$m[1];
                else throw new RuntimeException('Choose an email design before saving.');
            }
            elseif(!isset(Communication_templates::catalogue()[$template]))throw new RuntimeException('Enter a campaign name, audience, template and message.');
            $url=trim((string)$this->input->post('button_url'));if($url!=='' && (!filter_var($url,FILTER_VALIDATE_URL) || parse_url($url,PHP_URL_SCHEME)!=='https'))throw new RuntimeException('The button link must be a valid HTTPS URL.');
            $mode=(string)$this->input->post('send_mode');if(!in_array($mode,array('','draft','now','later'),true))throw new RuntimeException('Choose Save draft, Send now or Schedule.');
            if($channel==='sms' && preg_match('/\[(offer|date|conditions|occasion|announcement|contact)\]/i',$message))throw new RuntimeException('Replace the template placeholders in square brackets before saving.');
            $this->db->trans_begin();$id=$this->marketing_m->saveCampaign(get_current_store_id(),array('name'=>$name,'message'=>$message,'subject'=>$subject,'channel'=>$channel,'segment_key'=>$segment));if(!$id)throw new RuntimeException('Draft could not be created.');
            $designRow=array('campaign_id'=>$id,'store_id'=>get_current_store_id(),'template_key'=>$template,'button_label'=>mb_substr((string)$this->input->post('button_label'),0,80),'button_url'=>$url);
            if($this->canBindDesigns())$designRow['template_id']=$templateId;
            $this->db->insert('db_communication_campaign_designs',$designRow);if($mode==='later'){$this->load->model('communication_schedule_model');$this->communication_schedule_model->save(get_current_store_id(),$id,(string)$this->input->post('scheduled_at'),$this->input->post('budget_units'));}if(!$this->db->trans_status())throw new RuntimeException('Draft design could not be saved.');$this->db->trans_commit();$this->session->set_flashdata('success',$mode==='later'?'Campaign scheduled in West Africa Time. The due audience will be checked within your credit cap.':'Campaign draft saved. Review the credit estimate before sending.');if($mode==='now'){$query=array('section'=>'campaigns','review_campaign'=>$id);if($this->mobile())$query['screen']='mobile';redirect('communications/channel/'.$channel.'?'.http_build_query($query));return;}
        }catch(Throwable $e){$this->db->trans_rollback();$this->session->set_flashdata('failed',$e->getMessage());}$this->go($channel,'campaigns');
    }
    public function conversation($id=0){
        if(!$this->canInbox()){show_error('Inbox access denied.',403);return;}$this->load->model('communication_inbox_model');$conv=$this->communication_inbox_model->conversation(get_current_store_id(),(int)$id);if(!$conv){show_404();return;}$q=array('section'=>'conversation','conversation'=>(int)$id,'inbox_channel'=>$conv['channel']);if($this->mobile())$q['screen']='mobile';redirect('communications/channel/'.Communication_inbox_model::workspace($conv['channel']).'?'.http_build_query($q));
    }
    public function inbox_reply(){
        $this->post();if(!$this->canReply()){show_error('Reply permission is required.',403);return;}$this->load->model('communication_inbox_model');$channel=Communication_inbox_model::channel((string)$this->input->post('inbox_channel'));$id=(int)$this->input->post('conversation_id');
        try{$this->load->model('communication_inbox_model');$r=$this->communication_inbox_model->reply(get_current_store_id(),$id,$channel,(string)$this->input->post('text'),(string)$this->input->post('request_key'));$this->session->set_flashdata(!empty($r['success'])?'success':'failed',$r['message']);}catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $query=array('section'=>'conversation','conversation'=>$id,'inbox_channel'=>$channel);if($this->mobile())$query['screen']='mobile';redirect('communications/channel/'.Communication_inbox_model::workspace($channel).'?'.http_build_query($query));
    }
    public function sample($channel='sms'){
        $channel=$channel==='email'?'email':'sms';$this->guard($channel);$csv=$channel==='sms'?file_get_contents(FCPATH.'theme/samples/martpoint-sms-contacts-sample.csv'):"name,email,phone\r\nAda Example,ada@example.com,08012345678\r\n";
        $this->output->set_content_type('text/csv','UTF-8')->set_header('Content-Disposition: attachment; filename="martpoint-'.$channel.'-contacts-sample.csv"')->set_header('Cache-Control: no-store')->set_output($csv);
    }
    public function phone_qr(){
        $this->guard('sms');require_once FCPATH.'vendor/autoload.php';$uri=(new \chillerlan\QRCode\QRCode())->render(base_url('communications/channel/sms?section=audience&screen=mobile#contacts'));
        if(!preg_match('#^data:image/(png|svg\+xml);base64,(.+)$#s',$uri,$m)){show_error('QR code unavailable.',503);return;}$this->output->set_header('Cache-Control: private, no-store')->set_content_type('image/'.$m[1])->set_output(base64_decode($m[2],true));
    }
    public function manual_credit(){
        $this->post();$key=$this->input->post('module_key')==='email'?'email':'sms';$this->guard($key);
        try{$this->load->library('communication_documents');$p=array('module_key'=>$key,'purpose'=>'recharge','amount'=>$this->input->post('amount'),'package_id'=>$this->input->post('package_id'),'email'=>$this->input->post('email'),'request_key'=>$this->input->post('request_key'),'receipt'=>Communication_documents::upload('receipt'));$r=$this->messaging_client->request('manual_credit',get_current_store_id(),$p);if(empty($r['success']))throw new RuntimeException($r['message']??'Administrator did not confirm the receipt.');$this->session->set_flashdata('success',$r['message']);}catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}$this->go($key,'requests');
    }
    public function sender_request(){
        $this->post();$this->guard('sms');
        try{$this->load->library('communication_documents');$p=array();foreach(array('sender_id','business_name','email','sample_message','request_key') as $k)$p[$k]=$this->input->post($k);foreach(array('cac','authorization') as $k)$p[$k]=Communication_documents::upload($k);$r=$this->messaging_client->request('sender_request',get_current_store_id(),$p);if(empty($r['success']))throw new RuntimeException($r['message']??'Administrator did not confirm the Sender ID request.');$this->session->set_flashdata('success',$r['message']);}catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}$this->go('sms','sender');
    }
    /** The display name and reply address this store's customers see on its emails. */
    public function sender_identity(){
        $this->post();$this->guard('email');$this->permission_check('smtp_settings');
        try{
            $name=preg_replace('/[\r\n<>]/','',trim((string)$this->input->post('sender_name')));
            if($name==='' || mb_strlen($name)>90)throw new RuntimeException('Enter the name your customers should see, up to 90 characters.');
            $reply=trim((string)$this->input->post('sender_reply_to'));
            if($reply!=='' && !filter_var($reply,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter a valid reply address, or leave it empty to use your business email.');
            $this->load->model('email_settings_model');
            $this->email_settings_model->saveSettings(get_current_store_id(),array('email_from_name'=>$name,'email_reply_to'=>$reply));
            $this->session->set_flashdata('success','Your email sender details are saved.');
        }catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $this->go('email','sender');
    }
    /** A campaign can only be bound to a stored design once the app update is in place. */
    private function canBindDesigns(){return $this->communication_template_model->ready() && $this->db->table_exists('db_communication_campaign_designs') && $this->db->field_exists('template_id','db_communication_campaign_designs');}
    /** Create or update one of this store's own email designs. */
    public function design_save(){
        $this->post();$this->guard('email');
        $id=(int)$this->input->post('template_id');
        try{
            if(!$this->communication_template_model->ready())throw new RuntimeException('Email designs need an application update. Contact your Administrator.');
            $p=array();foreach(array('name','format','category','industry','occasion','subject','preheader','blocks','accent','brand_display','show_unsubscribe','cta_label','cta_url') as $field)$p[$field]=$this->input->post($field);
            $p['kind']='custom';
            $id=$this->communication_template_model->save(get_current_store_id(),$p,(string)$this->session->userdata('inv_username'),$id);
            $this->session->set_flashdata('success','Design saved. It is ready in your email composer.');
        }catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $query=array('section'=>'build','design'=>$id);if($this->mobile())$query['screen']='mobile';
        redirect('communications/channel/email?'.http_build_query($query));
    }
    public function design_remove(){
        $this->post();$this->guard('email');
        try{$this->communication_template_model->remove(get_current_store_id(),(int)$this->input->post('template_id'));$this->session->set_flashdata('success','Design removed.');}
        catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $this->go('email','templates');
    }
    /** Take a copy of a MartPoint design, so your own version is never changed under you. */
    public function design_adopt(){
        $this->post();$this->guard('email');$storeId=get_current_store_id();
        try{
            $hubId=(int)$this->input->post('hub_template_id');
            if(mp_is_central()){$design=$this->communication_template_model->getHub($hubId);$hub=array('success'=>(bool)$design,'designs'=>$design?array($design):array());}
            else $hub=$this->messaging_client->request('template_hub',$storeId,array('id'=>$hubId));
            if(empty($hub['designs'][0]))throw new RuntimeException(!empty($hub['success'])?'That MartPoint design is no longer available.':(string)($hub['message']??'Your MartPoint designs are unavailable right now.'));
            $this->communication_template_model->adopt($storeId,$hub['designs'][0],(string)$this->session->userdata('inv_username'));
            $this->session->set_flashdata('success','MartPoint design added to your templates. Edit it whenever you like.');
        }catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}
        $this->go('email','templates');
    }
    /** The same email the send would build, for the preview pane. */
    public function preview(){
        $this->post();$this->guard('email');
        $html='';
        if($this->input->post('draft')==='1'){
            // Live builder preview: unsaved fields go through the same block rules as a save.
            $p=array();foreach(array('name','format','category','industry','occasion','subject','preheader','accent','brand_display','show_unsubscribe','cta_label','cta_url') as $field)$p[$field]=(string)$this->input->post($field);
            if(trim($p['name'])==='')$p['name']='Email design';
            $blocks=json_decode((string)$this->input->post('blocks'),true);
            $p['blocks']=is_array($blocks)?$blocks:array();
            $html=Communication_designs::preview($p,get_store_name());
        }else{
            $html=$this->previewHtml((string)$this->input->post('design'),
                mb_substr((string)$this->input->post('subject'),0,255),mb_substr((string)$this->input->post('message'),0,20000),
                mb_substr((string)$this->input->post('button_label'),0,80),(string)$this->input->post('button_url'));
        }
        $this->output->set_content_type('application/json')->set_header('Cache-Control: no-store')
            ->set_output(json_encode(array('success'=>true,'html'=>$html),JSON_INVALID_UTF8_SUBSTITUTE));
    }
    private function previewHtml($design,$subject,$message,$label,$url){
        $storeId=get_current_store_id();$store=get_store_name();$name='Ada Example';
        $unsub=base_url('storefront/campaign_unsubscribe/preview');
        if(preg_match('/^d:([1-9]\d{0,9})$/D',$design,$m)){
            $stored=$this->communication_template_model->get($storeId,(int)$m[1]);
            if($stored && !empty($stored['enabled']))return Communication_designs::render($stored,array('store'=>$store,'name'=>$name,'subject'=>$subject,
                'message'=>$message,'unsubscribe'=>$unsub,'cta_label'=>$label,'cta_url'=>$url));
        }
        $key=preg_match('/^k:([a-z][a-z0-9_]{1,19})$/D',$design,$m) && isset(Communication_templates::catalogue()[$m[1]])?$m[1]:'letter';
        return Communication_templates::render($key,$store,$name,$message,$subject,'#6554c0',$label,$url,$unsub);
    }
    public function document($id=0){
        $this->guard($this->can('email')?'email':'sms');$r=$this->messaging_client->request('communication_document',get_current_store_id(),array('document_id'=>(int)$id));if(empty($r['success'])){show_error('Document not available for this store.',404);return;}$this->load->library('communication_documents');$document=Communication_documents::validate($r['document']);Communication_documents::download($document);
    }
    public function schedule_campaign(){
        $this->post();$channel=$this->input->post('channel')==='sms'?'sms':'email';$this->guard($channel);try{$id=(int)$this->input->post('campaign_id');$c=$this->marketing_m->getCampaign($id,get_current_store_id());if(!$c || $c->channel!==$channel)throw new RuntimeException('Campaign not found for this channel.');$this->load->model('communication_schedule_model');$this->db->trans_begin();$this->communication_schedule_model->save(get_current_store_id(),$id,(string)$this->input->post('scheduled_at'),$this->input->post('budget_units'));if(!$this->db->trans_status())throw new RuntimeException('Schedule could not be saved.');$this->db->trans_commit();$this->session->set_flashdata('success','Campaign scheduled in West Africa Time within your credit cap.');}catch(Throwable $e){$this->db->trans_rollback();$this->session->set_flashdata('failed',$e->getMessage());}$this->go($channel,'campaigns');
    }
    public function cancel_schedule(){
        $this->post();$channel=$this->input->post('channel')==='sms'?'sms':'email';$this->guard($channel);try{$this->load->model('communication_schedule_model');$this->communication_schedule_model->cancel(get_current_store_id(),(int)$this->input->post('campaign_id'));$this->session->set_flashdata('success','Schedule cancelled. The campaign is a draft again.');}catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}$this->go($channel,'campaigns');
    }
    public function automation(){
        $this->post();$this->guard('automation');
        try{$this->load->model('communication_automation_model');$this->communication_automation_model->save(get_current_store_id(),$this->input->post(null,true),$this->can('email'),$this->can('sms'));$this->session->set_flashdata('success',($this->input->post('operation')==='archive'?'Workflow archived. Any registered plan slot has been released.':'Automation saved. Messages use the selected channel credits; the workflow uses a plan slot.'));}
        catch(Throwable $e){$this->session->set_flashdata('failed',$e->getMessage());}$this->go('automation','workflows');
    }
}
