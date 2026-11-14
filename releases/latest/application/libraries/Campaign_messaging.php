<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** One implementation for interactive and background campaign sends. */
class Campaign_messaging {
    protected $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->library('messaging_client'); }
    public function content($campaign,$send,$test=false) {
        $name=$send->customer_name??'there';
        $text=str_replace('{name}',$name?:'there',(string)$campaign->message);
        $unsub=!empty($send->unsub_token)?base_url('storefront/campaign_unsubscribe/'.$send->unsub_token):'';
        $store=$this->CI->db->where('id',$campaign->store_id)->get('db_store')->row();
        $storeName=$store->store_name??'Our store';$text=str_replace('{store}',$storeName,$text);
        $options=array('template_key'=>'campaign','message_category'=>'promotional','related_module'=>'campaign',
            'related_record_id'=>$test?'':$campaign->id,'messaging_key'=>$test?'campaign-test:'.bin2hex(random_bytes(16)):'campaign:'.$campaign->id.':send:'.$send->id,
            'retry_failed'=>true);
        $design=null;
        if($this->CI->db->table_exists('db_communication_campaign_designs'))$design=$this->CI->db->where('campaign_id',$campaign->id)->where('store_id',$campaign->store_id)->get('db_communication_campaign_designs')->row_array();
        if($this->CI->db->table_exists('db_communication_automation_runs')){
            $run=$this->CI->db->select('a.id,a.enabled,a.segment_key,r.scheduled_at')->from('db_communication_automation_runs r')->join('db_communication_automations a','a.id=r.automation_id')->where('r.campaign_id',$campaign->id)->where('a.store_id',$campaign->store_id)->get()->row_array();
            if($run){
                $meta=$this->CI->db->table_exists('db_communication_auto_workflows')?$this->CI->db->where('workflow_id',$run['id'])->where('store_id',$campaign->store_id)->get('db_communication_auto_workflows')->row_array():null;
                $options['birthday_expired']=$run['segment_key']==='birthday_today' && (new DateTimeImmutable($run['scheduled_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d')!==(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d');$options['requires_automation']=true;$options['automation_paused']=empty($run['enabled']) || !empty($meta['archived']);$options['automation_workflow_key']='workflow:'.$run['id'];$options['automation_template_key']=$meta['template_key']??'custom';
            }
        }
        if(($campaign->segment_key??'')==='birthday_today' && $this->CI->db->table_exists('db_communication_campaign_schedules')){$scheduled=$this->CI->db->where('campaign_id',$campaign->id)->where('store_id',$campaign->store_id)->get('db_communication_campaign_schedules')->row_array();if($scheduled)$options['birthday_expired']=(new DateTimeImmutable($scheduled['scheduled_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d')!==(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d');}
        if(($campaign->segment_key??'')==='abandoned_carts'){$this->CI->load->model('communication_cart_recovery_model');try{$cartContext=$this->CI->communication_cart_recovery_model->context($campaign,$send);}catch(Throwable $e){$cartContext=null;}$options['cart_recovery']=true;$options['requires_automation']=true;$options['cart_unavailable']=!$cartContext || empty($options['automation_workflow_key']);$text=str_replace('{cart_url}',$cartContext?$cartContext['url']:'[checkout link unavailable]',$text);}
        // A campaign bound to a stored design renders exactly what its editor showed;
        // older campaigns keep the four built-in designs.
        $html='';
        if($design && (int)($design['template_id']??0)>0){
            $this->CI->load->model('communication_template_model');
            $stored=$this->CI->communication_template_model->get($campaign->store_id,(int)$design['template_id']);
            if($stored && !empty($stored['enabled'])){
                $this->CI->load->library('communication_designs');
                $html=Communication_designs::render($stored,array('store'=>$storeName,'name'=>$name,'subject'=>$campaign->subject,
                    'message'=>$text,'unsubscribe'=>$unsub,'cta_label'=>(string)($design['button_label']??''),'cta_url'=>(string)($design['button_url']??'')));
            }
        }
        if($html===''){
            if($design)$this->CI->load->library('communication_templates');
            $html=$design?Communication_templates::render($design['template_key'],$storeName,$name,$text,$campaign->subject,$design['accent'],$design['button_label']??'',$design['button_url']??'',$unsub):'<p>Hi '.htmlspecialchars($name?:'there',ENT_QUOTES,'UTF-8').',</p><p>'.nl2br(htmlspecialchars($text,ENT_QUOTES,'UTF-8')).'</p><p>— '.htmlspecialchars($storeName,ENT_QUOTES,'UTF-8').'</p>'
                .($unsub?'<p><a href="'.htmlspecialchars($unsub,ENT_QUOTES,'UTF-8').'">Unsubscribe from these emails</a></p>':'');
        }
        // Email campaigns measure engagement; test sends and SMS never do.
        if($campaign->channel==='email' && !$test){
            $this->CI->load->library('communication_tracking');
            $trackToken=Communication_tracking::token($send);
            if($trackToken!=='')$html=Communication_tracking::wrap($html,$trackToken);
        }
        return array('recipient'=>$send->recipient,'subject'=>$campaign->subject?:('A message from '.$storeName),
            'text'=>$text.($unsub?($campaign->channel==='sms'?' Opt out: ':"\n\nUnsubscribe: ").$unsub:''),
            'html'=>$html,'options'=>$options);
    }
    public function send($campaign,$send,$test=false) {
        $p=$this->content($campaign,$send,$test);
        if(!$test && ($campaign->segment_key??'')==='birthday_today'){$this->CI->load->model('marketing_model','marketing_m');$normal=Communication_audience::recipient($campaign->channel,$p['recipient']);$matches=false;foreach($this->CI->marketing_m->birthdayAudience($campaign->store_id) as $birthday){if($normal && Communication_audience::recipient($campaign->channel,$birthday->{$campaign->channel==='sms'?'phone':'email'}??'')===$normal){$matches=true;break;}}if(!$matches)return array('ok'=>false,'rate_limited'=>false,'error'=>'This contact no longer matches today’s birthday audience; no credit was used.');}
        if(!empty($p['options']['birthday_expired']))return array('ok'=>false,'rate_limited'=>false,'error'=>'The birthday delivery date has passed; no credit was used.');
        if(!empty($p['options']['automation_paused']))return array('ok'=>false,'rate_limited'=>true,'error'=>'Automation is paused.');
        if(!empty($p['options']['cart_recovery']) && !$test){try{$claimed=empty($p['options']['cart_unavailable']) && $this->CI->communication_cart_recovery_model->claim($campaign,$send);}catch(Throwable $e){$claimed=false;}if(!$claimed)return array('ok'=>false,'rate_limited'=>false,'error'=>'Cart no longer qualifies for a reminder; no credit was used.');}
        $r=$campaign->channel==='sms'
            ?$this->CI->messaging_client->sendSms($campaign->store_id,$p['recipient'],$p['text'],$p['options'])
            :$this->CI->messaging_client->sendEmail($campaign->store_id,$p['recipient'],$p['subject'],$p['html'],$p['text'],$p['options']);
        $paused=empty($r['success']) && (($r['code']??'')!=='rejected' || preg_match('/rate.?limit|429|too many|throttl/i',$r['message']??''));
        return array('ok'=>!empty($r['success']),'error'=>!empty($r['success'])?null:($r['message']??'Messaging paused.'),
            'rate_limited'=>$paused,'code'=>$r['code']??'');
    }
    public function estimate($campaign,array $sends,$test=false) {
        $texts=array();
        foreach($sends as $send) {
            if(!$test && $this->CI->marketing_m->isSuppressed($campaign->store_id,$campaign->channel,$send->recipient)) continue;
            $p=$this->content($campaign,$send,$test); $texts[]=$p['text'];
        }
        if(!$texts) return array('success'=>false,'message'=>'No eligible queued recipients.');
        return $this->CI->messaging_client->request('estimate',$campaign->store_id,array('channel'=>$campaign->channel,'category'=>'promotional','texts'=>$texts));
    }
}
