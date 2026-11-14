<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Safe email designs: merchant text is escaped; no arbitrary scripts or HTML. */
class Communication_templates {
    public static function catalogue() {
        return array(
            'letter'=>array('name'=>'Personal letter','subject'=>'A message from our team','message'=>"We have an update for you.\n\nThank you for choosing us."),
            'welcome'=>array('name'=>'Welcome','subject'=>'Welcome — we are glad you are here','message'=>"Welcome to our community.\n\nDiscover our products and services, or reply to this email if you need help."),
            'offer'=>array('name'=>'Special offer','subject'=>'Something special for you','message'=>"Our latest offer is here.\n\nAdd the details of your offer, dates, and terms here."),
            'newsletter'=>array('name'=>'Newsletter','subject'=>'Your latest news and updates','message'=>"Here is what is new this month.\n\nShare your news, useful tips, and upcoming events."));
    }
    public static function render($key,$store,$name,$message,$subject,$accent='#6554c0',$label='',$url='',$unsubscribe='') {
        if(!isset(self::catalogue()[$key]))$key='letter';
        $h=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
        if(!preg_match('/^#[a-f0-9]{6}$/iD',$accent))$accent='#6554c0';
        $heading=$key==='letter'?'':('<h1 style="font-size:28px;line-height:1.25;margin:18px 0">'.$h($subject).'</h1>');
        $button=filter_var($url,FILTER_VALIDATE_URL) && parse_url($url,PHP_URL_SCHEME)==='https' && $label!==''?'<p style="margin:30px 0"><a href="'.$h($url).'" style="background:'.$accent.';color:white;display:inline-block;padding:14px 22px;border-radius:8px;text-decoration:none">'.$h($label).'</a></p>':'';
        return '<!doctype html><html><body style="margin:0;background:#f3f4f7;font:16px Arial,sans-serif;color:#202331"><div style="max-width:620px;margin:28px auto;background:white;border-radius:14px;overflow:hidden"><div style="height:8px;background:'.$accent.'"></div><div style="padding:34px"><p style="font-size:13px;font-weight:bold;color:'.$accent.'">'.$h($store).'</p>'.$heading.'<p>Hi '.$h($name?:'there').',</p><p style="line-height:1.8">'.nl2br($h($message)).'</p>'.$button.'<p style="margin-top:32px">'.$h($store).'</p></div><div style="padding:20px 34px;background:#fafafa;font-size:12px;color:#687080">Sent by '.$h($store).($unsubscribe?' · <a href="'.$h($unsubscribe).'">Unsubscribe</a>':'').'</div></div></body></html>';
    }
}
