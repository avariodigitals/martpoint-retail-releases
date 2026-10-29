<?php
// Shared account controls; each channel owns its page, navigation and content.
$this->load->view('communications/styles');
$h=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
$money=function($value){return '₦'.number_format((int)$value/100,2);};
$mobile=!empty($communication_mobile);
$channel=$workspace_key;
$names=array('sms'=>'SMS','email'=>'Email','whatsapp'=>'WhatsApp','socials'=>'Socials','automation'=>'Automation');
$comm=$messaging['communications']??null;
require_once APPPATH.'libraries/Communication_copy.php';
$status_text=function($text)use($h){return $h(Communication_copy::message($text));};
$connection_message=Communication_copy::message($messaging['message']??'Account status is unavailable. Please contact your Administrator.');
$m=$comm['modules'][$channel]??array('active'=>false,'allowed'=>false,'merchant_enabled'=>false,'subscribed_until'=>null);
$c=$comm['catalogue'][$channel]??array('enabled'=>false,'monthly_kobo'=>0,'annual_kobo'=>0,'credit_price_kobo'=>0,'packages'=>array());
$wallet=$comm['wallets'][$channel]??null;
// Central bills VAT on subscriptions, recharges and add-ons; catalogue prices stay net.
$taxBps=(int)($comm['tax_bps']??0);$taxPercent=$taxBps>0?(round($taxBps/100,2)):'0';
$gross=function($netKobo)use($taxBps){return (int)round((int)$netKobo*(10000+$taxBps)/10000);};
$vat_note=$taxBps>0?'VAT of '.$h(rtrim(rtrim($taxPercent,'0'),'.')).'% is added at checkout and shown on your request.':'No VAT is charged on Communications purchases.';
$subscribed=!empty($m['subscribed_until']) && strtotime($m['subscribed_until'].' UTC')>time();
$expiry=!empty($m['subscribed_until'])?(new DateTimeImmutable($m['subscribed_until'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('d M Y, H:i').' WAT':null;
$workspace_section=$workspace_section??'dashboard';
$workspace_url=function($section='dashboard',$extra=array())use($channel,$mobile){
    $query=array_merge(array('section'=>$section),$extra);if($mobile)$query['screen']='mobile';
    return base_url('communications/channel/'.$channel).'?'.http_build_query($query);
};
$form=function($action,$key,$extra=array())use($h,$mobile){
    echo form_open('communications/action');
    foreach(array_merge(array('action'=>$action,'module_key'=>$key,'screen'=>$mobile?'mobile':'desktop'),$extra) as $field=>$value)echo '<input type="hidden" name="'.$h($field).'" value="'.$h($value).'">';
};
$campaigns=$campaigns??array();$automations=$automations??array();$contacts=$contacts??array();$automation_runs=$automation_runs??array();
$campaign_totals=array('audience'=>0,'accepted'=>0,'failed'=>0,'drafts'=>0,'queued'=>0);
foreach($campaigns as $campaign){
    $campaign_totals['audience']+=(int)$campaign->audience_count;
    $campaign_totals['accepted']+=(int)$campaign->sent_count;
    $campaign_totals['failed']+=(int)$campaign->failed_count;
    if($campaign->status==='draft')$campaign_totals['drafts']++;
    if(in_array($campaign->status,array('sending','partial'),true))$campaign_totals['queued']++;
}
