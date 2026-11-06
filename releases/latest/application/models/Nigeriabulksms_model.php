<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Nigeria Bulk SMS HTTP gateway (portal.nigeriabulksms.com). */
class Nigeriabulksms_model extends CI_Model {
	public function send($to, $message, $store_id = null){
		$store_id = (int)($store_id ?: get_current_store_id());
		if(!$store_id || !$this->db->table_exists('db_nigeriabulksms')) return false;
		$cfg = $this->db->where('store_id',$store_id)->get('db_nigeriabulksms')->row();
		if(!$cfg || !$cfg->username || !$cfg->password || !$cfg->sender_id || !trim((string)$message)) return false;
		$digits = preg_replace('/[^0-9]/','',(string)$to);
		if(strpos($digits,'00')===0) $digits=substr($digits,2);
		if(strpos($digits,'0')===0 && strlen($digits)===11) $digits='234'.substr($digits,1);
		elseif(strlen($digits)===10) $digits='234'.$digits;
		if(strlen($digits)<10 || strlen($digits)>15) return false;
		$url = 'https://portal.nigeriabulksms.com/api/?'.http_build_query([
			'username'=>$cfg->username,'password'=>$cfg->password,'message'=>(string)$message,
			'sender'=>$cfg->sender_id,'mobiles'=>$digits,
		]);
		$ch=curl_init($url);
		curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,
			CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,
			CURLOPT_HTTPHEADER=>['Accept: application/json']]);
		$raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
		$data=is_string($raw)?json_decode($raw,true):null;
		if($err!=='' || $http<200 || $http>=300 || !is_array($data) || strtoupper((string)($data['status']??''))!=='OK'){
			log_message('error','Nigeria Bulk SMS send failed. HTTP '.$http.' '.(string)$raw.' '.$err);
			return false;
		}
		return true;
	}
}
