<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Per-store storefront currency resolution and deterministic NGN conversion. */
class Storefront_currency {
	private $CI;
	private $supported = ['NGN','CAD','USD','EUR','GBP'];
	public function __construct(){ $this->CI =& get_instance(); }

	public function baseCode($storeId){
		$row=$this->CI->db->select('c.currency_code,c.currency_name,c.currency')->from('db_store s')->join('db_currency c','c.id=s.currency_id','left')->where('s.id',(int)$storeId)->get()->row();
		if(!$row) return 'NGN';
		$code=strtoupper(trim((string)($row->currency_code ?? '')));
		if(!preg_match('/^[A-Z]{3}$/',$code) && preg_match('/\(([A-Z]{3})\)/i',(string)($row->currency_name ?? ''),$m)) $code=strtoupper($m[1]);
		if(!preg_match('/^[A-Z]{3}$/',$code) && preg_match('/^[A-Z]{3}$/i',trim((string)($row->currency ?? '')))) $code=strtoupper(trim($row->currency));
		return preg_match('/^[A-Z]{3}$/',$code) ? $code : 'NGN';
	}

	public function settings($storeId){
		$row=$this->CI->db->where('store_id',(int)$storeId)->get('db_storefront_settings')->row();
		return $row ?: (object)[];
	}
	private function json($raw,$fallback=[]){ $v=json_decode((string)$raw,true); return is_array($v)?$v:$fallback; }
	public function enabledCurrencies($storeId){
		$s=$this->settings($storeId); $base=$this->baseCode($storeId);
		if(empty($s->multicurrency_enabled)) return [$base];
		$list=$this->json($s->checkout_currencies_json,[$base]); $out=[];
		foreach($list as $c){$c=strtoupper(trim((string)$c));if(in_array($c,$this->supported,true)&&!in_array($c,$out,true))$out[]=$c;}
		if(!in_array($base,$out,true)) array_unshift($out,$base);
		return $out;
	}
	public function selectedCode($storeId){
		$base=$this->baseCode($storeId); $s=$this->settings($storeId);
		if(empty($s->multicurrency_enabled)) return $base;
		$key='sf_currency_'.$storeId; $saved=strtoupper(trim((string)$this->CI->session->userdata($key)));
		$list=$this->enabledCurrencies($storeId);
		if(in_array($saved,$list,true)){try{$this->rate($storeId,$saved);return $saved;}catch(Throwable $e){}}
		$country=$this->selectedCountry($storeId);
		$default=$country!==''?$this->countryDefault($storeId,$country):strtoupper(trim((string)($s->currency_default_code ?? '')));
		if(in_array($default,$list,true)){try{$this->rate($storeId,$default);return $default;}catch(Throwable $e){}}
		return $base;
	}
	public function selectedCountry($storeId){return strtoupper(trim((string)$this->CI->session->userdata('sf_country_'.(int)$storeId)));}
	public function setCountry($storeId,$country){
		$country=strtoupper(trim((string)$country));if(!preg_match('/^[A-Z]{2}$/',$country))return false;
		$this->CI->session->set_userdata('sf_country_'.(int)$storeId,$country);return true;
	}
	/** 1 unit foreign currency = X units base currency. Base uses rate 1. */
	public function rate($storeId,$currency){
		$currency=strtoupper(trim((string)$currency)); $base=$this->baseCode($storeId);
		if($currency===$base) return 1.0;
		if(!in_array($currency,$this->enabledCurrencies($storeId),true)) throw new InvalidArgumentException('That checkout currency is not enabled.');
		$s=$this->settings($storeId); $rates=$this->json($s->currency_rates_json,[]); $rate=$rates[$currency] ?? null;
		if(!is_numeric($rate) || !is_finite((float)$rate) || (float)$rate<=0) throw new InvalidArgumentException('The exchange rate for '.$currency.' is missing or invalid. Please contact the store.');
		return (float)$rate;
	}
	public function convertBaseToTransaction($amount,$rate){
		if(!is_finite((float)$amount)||!is_finite((float)$rate)||(float)$rate<=0) throw new InvalidArgumentException('Invalid monetary amount or exchange rate.');
		return round((float)$amount/(float)$rate,2,PHP_ROUND_HALF_UP);
	}
	public function convertTransactionToBase($amount,$rate){
		if(!is_finite((float)$amount)||!is_finite((float)$rate)||(float)$rate<=0) throw new InvalidArgumentException('Invalid monetary amount or exchange rate.');
		return round((float)$amount*(float)$rate,4,PHP_ROUND_HALF_UP);
	}
	public function setCurrency($storeId,$currency){
		$currency=strtoupper(trim((string)$currency));
		if(!in_array($currency,$this->enabledCurrencies($storeId),true)) return false;
		try{$this->rate($storeId,$currency);}catch(Throwable $e){return false;}
		$this->CI->session->set_userdata('sf_currency_'.(int)$storeId,$currency); return true;
	}
	public function countryDefault($storeId,$country){
		$s=$this->settings($storeId); $map=$this->json($s->currency_country_defaults_json,[]);
		$country=strtoupper(trim((string)$country)); $candidate=strtoupper(trim((string)($map[$country] ?? '')));
		if($candidate==='' && $country==='NG')$candidate='NGN';
		if($candidate==='' && $country==='CA')$candidate='CAD';
		if($candidate==='' && $country==='GB')$candidate='GBP';
		if($candidate==='' && in_array($country,['AT','BE','CY','DE','EE','ES','FI','FR','GR','HR','IE','IT','LT','LU','LV','MT','NL','PT','SI','SK'],true))$candidate='EUR';
		if($candidate==='' && $country!=='')$candidate='USD';
		if(in_array($candidate,$this->enabledCurrencies($storeId),true)) return $candidate;
		$default=strtoupper(trim((string)($s->currency_default_code??'')));
		return in_array($default,$this->enabledCurrencies($storeId),true)?$default:$this->baseCode($storeId);
	}
	public function label($currency){
		$symbols=['NGN'=>'NGN ₦','CAD'=>'CAD $','USD'=>'USD $','EUR'=>'EUR €','GBP'=>'GBP £'];
		return $symbols[strtoupper((string)$currency)] ?? strtoupper((string)$currency);
	}
}
