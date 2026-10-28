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
        $storeName=$store->store_name??'Our store';
        $options=array('template_key'=>'campaign','message_category'=>'promotional','related_module'=>'campaign',
            'related_record_id'=>$test?'':$campaign->id,'messaging_key'=>$test?'campaign-test:'.bin2hex(random_bytes(16)):'campaign:'.$campaign->id.':send:'.$send->id,
            'retry_failed'=>true);
        return array('recipient'=>$send->recipient,'subject'=>$campaign->subject?:('A message from '.$storeName),
            'text'=>$text.($unsub?($campaign->channel==='sms'?' Opt out: ':"\n\nUnsubscribe: ").$unsub:''),
            'html'=>'<p>Hi '.htmlspecialchars($name?:'there',ENT_QUOTES,'UTF-8').',</p><p>'.nl2br(htmlspecialchars($text,ENT_QUOTES,'UTF-8')).'</p><p>— '.htmlspecialchars($storeName,ENT_QUOTES,'UTF-8').'</p>'
                .($unsub?'<p><a href="'.htmlspecialchars($unsub,ENT_QUOTES,'UTF-8').'">Unsubscribe from these emails</a></p>':''),'options'=>$options);
    }
    public function send($campaign,$send,$test=false) {
        $p=$this->content($campaign,$send,$test);
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
