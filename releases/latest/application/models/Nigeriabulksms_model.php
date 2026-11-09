<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Nigeria Bulk SMS HTTP gateway (portal.nigeriabulksms.com). */
class Nigeriabulksms_model extends CI_Model {
	private function credentials($store_id = null){
		$store_id = (int)($store_id ?: get_current_store_id());
		if(!$store_id || !$this->db->table_exists('db_nigeriabulksms')) return null;
		$cfg = $this->db->where('store_id',$store_id)->get('db_nigeriabulksms')->row();
		return ($cfg && $cfg->username && $cfg->password) ? $cfg : null;
	}

	private function request(array $params, $store_id = null){
		$cfg = $this->credentials($store_id);
		if(!$cfg) return ['ok'=>false,'error'=>'Configure Nigeria Bulk SMS credentials first','data'=>null];
		$params = array_merge(['username'=>$cfg->username,'password'=>$cfg->password], $params);
		$url = 'https://portal.nigeriabulksms.com/api/?'.http_build_query($params);
		$ch=curl_init($url);
		curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>30,
			CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,
			CURLOPT_HTTPHEADER=>['Accept: application/json']]);
		$raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
		$data=is_string($raw)?json_decode($raw,true):null;
		$status=strtolower((string)($data['status']??''));
		$ok=$err==='' && $http>=200 && $http<300 && is_array($data) && (in_array($status,['ok','success'],true) || (string)($data['errno']??'')==='000');
		if(!$ok){
			$message=(string)($data['error']??$data['message']??($err!==''?'Gateway connection failed':'Gateway request was rejected'));
			log_message('error','Nigeria Bulk SMS gateway request failed. HTTP '.$http.' errno '.(string)($data['errno']??'').' '.$err);
			return ['ok'=>false,'error'=>$message,'errno'=>$data['errno']??null,'data'=>$data];
		}
		return ['ok'=>true,'error'=>null,'errno'=>$data['errno']??null,'data'=>$data];
	}

	private function normalize_numbers($to){
		$numbers=is_array($to)?$to:preg_split('/[,;\n]+/',(string)$to);
		$valid=[];
		foreach($numbers as $number){
			$digits = preg_replace('/[^0-9]/','',(string)$number);
			if(strpos($digits,'00')===0) $digits=substr($digits,2);
			if(strpos($digits,'234')===0 && strlen($digits)===14 && substr($digits,3,1)==='0') $digits='234'.substr($digits,4);
			elseif(strpos($digits,'0')===0 && strlen($digits)===11) $digits='234'.substr($digits,1);
			elseif(strlen($digits)===10) $digits='234'.$digits;
			if(strlen($digits)>=10 && strlen($digits)<=15) $valid[]=$digits;
		}
		return array_values(array_unique($valid));
	}

	public function send($to, $message, $store_id = null){
		$cfg=$this->credentials($store_id);
		$numbers=$this->normalize_numbers($to);
		if(!$cfg || !$numbers || !trim((string)$message)) return false;
		return $this->request(['message'=>(string)$message,'sender'=>$cfg->sender_id,'mobiles'=>implode(',',$numbers)],$store_id)['ok'];
	}

	public function send_voice($to,$message,$type='call',$sender=null,$store_id=null){
		$cfg=$this->credentials($store_id); $numbers=$this->normalize_numbers($to);
		if(!$cfg || !$numbers || !trim((string)$message) || !in_array($type,['call','tts'],true)) return ['ok'=>false,'error'=>'Invalid voice message details'];
		$from=trim((string)($sender ?: $cfg->sender_id));
		if($from==='') return ['ok'=>false,'error'=>'A caller ID is required'];
		return $this->request(['message'=>(string)$message,'type'=>$type,'sender'=>$from,'mobiles'=>implode(',',$numbers)],$store_id);
	}

	public function send_audio($to,$audio_reference,$sender,$store_id=null){
		$numbers=$this->normalize_numbers($to);
		if(!$numbers || trim((string)$audio_reference)==='' || trim((string)$sender)==='') return ['ok'=>false,'error'=>'A recipient, audio reference and caller ID are required'];
		return $this->request(['message'=>(string)$audio_reference,'type'=>'call','sender'=>(string)$sender,'mobiles'=>implode(',',$numbers)],$store_id);
	}

	public function upload_audio($url,$store_id=null){
		if(!filter_var($url,FILTER_VALIDATE_URL) || strtolower((string)parse_url($url,PHP_URL_SCHEME))!=='https') return ['ok'=>false,'error'=>'Provide a public HTTPS audio URL'];
		return $this->request(['action'=>'upload','url'=>$url],$store_id);
	}

	public function fetch_data($action,array $filters=[],$store_id=null){
		$allowed=['login','profile','balance','contacts','numbers','groups','audios','history','scheduled','reports','payments'];
		if(!in_array($action,$allowed,true)) return ['ok'=>false,'error'=>'Unsupported gateway data action'];
		$params=['action'=>$action];
		foreach(['type','contacts','groups','numbers'] as $key){ if(isset($filters[$key]) && $filters[$key]!=='') $params[$key]=$filters[$key]; }
		return $this->request($params,$store_id);
	}
}
