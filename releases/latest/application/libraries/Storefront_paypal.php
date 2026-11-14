<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Store-scoped PayPal Orders v2 client for online-store checkouts. */
class Storefront_paypal {
	private $CI;
	public function __construct(){ $this->CI =& get_instance(); }

	public function saveAccount($storeId,array $data){
		$storeId=(int)$storeId; if($storeId<=0) return false;
		$old=$this->CI->db->table_exists('db_storefront_paypal_accounts')
			?$this->CI->db->where('store_id',$storeId)->get('db_storefront_paypal_accounts')->row():null;
		$client=trim((string)($data['client_id']??''));
		$secret=trim((string)($data['client_secret']??''));
		if($client==='' && $old) $client=(string)$old->client_id;
		if($client==='' || ($secret==='' && !$old)) throw new InvalidArgumentException('PayPal client ID and secret are required.');
		$environment=($data['environment']??'sandbox')==='live'?'live':'sandbox';
		$webhook=trim((string)($data['webhook_id']??''));
		if($webhook==='' && $old) $webhook=(string)$old->webhook_id;
		$encryptedSecret=$secret!==''?$this->seal($secret):(string)$old->client_secret;
		if(!empty($data['enabled'])&&$webhook==='') throw new InvalidArgumentException('Configure the PayPal webhook ID before enabling checkout.');
		$row=['store_id'=>$storeId,'client_id'=>$client,'client_secret'=>$encryptedSecret,'webhook_id'=>$webhook,
			'environment'=>$environment,'enabled'=>!empty($data['enabled'])?1:0,'updated_at'=>date('Y-m-d H:i:s'),
			'updated_by'=>(int)($data['updated_by']??0)];
		$exists=$old!==null;
		return $exists?$this->CI->db->where('store_id',$storeId)->update('db_storefront_paypal_accounts',$row):$this->CI->db->insert('db_storefront_paypal_accounts',$row);
	}

	/** Public-safe account summary. Never returns the client secret. */
	public function accountSummary($storeId){
		if(!$this->CI->db->table_exists('db_storefront_paypal_accounts')) return ['configured'=>false,'enabled'=>false,'environment'=>'sandbox','webhook_configured'=>false,'client_id'=>''];
		$row=$this->CI->db->where('store_id',(int)$storeId)->get('db_storefront_paypal_accounts')->row();
		return ['configured'=>(bool)($row && $row->client_id && $row->client_secret),
			'enabled'=>(bool)($row->enabled??false),'environment'=>(string)($row->environment??'sandbox'),
			'webhook_configured'=>(bool)($row && !empty($row->webhook_id)), 'client_id'=>(string)($row->client_id??'')];
	}

	public function createOrder($storeId,$orderCode,$currency,$amount,$returnUrl,$cancelUrl,$attemptNumber=1){
		$account=$this->account((int)$storeId,true);
		if($account['webhook_id']==='') throw new RuntimeException('Configure and verify the PayPal webhook before enabling checkout.');
		$amount=number_format((float)$amount,2,'.','');
		$body=['intent'=>'CAPTURE','purchase_units'=>[['reference_id'=>(string)$orderCode,'custom_id'=>(string)$orderCode,
			'invoice_id'=>substr((string)$orderCode,0,127),'amount'=>['currency_code'=>strtoupper($currency),'value'=>$amount]]],
			'application_context'=>['return_url'=>$returnUrl,'cancel_url'=>$cancelUrl,'user_action'=>'PAY_NOW']];
		return $this->request($storeId,'POST','/v2/checkout/orders',$body,
			'create-'.substr(hash('sha256',(string)$orderCode.'|'.strtoupper($currency).'|'.$amount.'|'.(int)$attemptNumber),0,32));
	}
	public function captureOrder($storeId,$providerOrderId,$requestId){
		return $this->request($storeId,'POST','/v2/checkout/orders/'.rawurlencode($providerOrderId).'/capture',[],substr((string)$requestId,0,36));
	}
	public function getOrder($storeId,$providerOrderId){
		return $this->request($storeId,'GET','/v2/checkout/orders/'.rawurlencode($providerOrderId),null,null);
	}
	public function refundCapture($storeId,$captureId,$currency,$amount,$requestId){
		$body=[];
		if((float)$amount>0)$body['amount']=['currency_code'=>strtoupper((string)$currency),'value'=>number_format((float)$amount,2,'.','')];
		return $this->request($storeId,'POST','/v2/payments/captures/'.rawurlencode((string)$captureId).'/refund',$body,substr((string)$requestId,0,36));
	}
	public function getRefund($storeId,$refundId){
		return $this->request($storeId,'GET','/v2/payments/refunds/'.rawurlencode((string)$refundId),null,null);
	}
	public function verifyWebhook($storeId,array $headers,array $event){
		$account=$this->account((int)$storeId,false);
		$required=['paypal-transmission-id','paypal-transmission-time','paypal-cert-url','paypal-auth-algo','paypal-transmission-sig'];
		foreach($required as $name) if(empty($headers[$name])) return false;
		$payload=['auth_algo'=>$headers['paypal-auth-algo'],'cert_url'=>$headers['paypal-cert-url'],
			'transmission_id'=>$headers['paypal-transmission-id'],'transmission_sig'=>$headers['paypal-transmission-sig'],
			'transmission_time'=>$headers['paypal-transmission-time'],'webhook_id'=>$account['webhook_id'],'webhook_event'=>$event];
		try{$result=$this->request($storeId,'POST','/v1/notifications/verify-webhook-signature',$payload,null);
			return strtoupper((string)($result['verification_status']??''))==='SUCCESS';}catch(Throwable $e){return false;}
	}

	private function request($storeId,$method,$path,$body=null,$requestId=null){
		$account=$this->account((int)$storeId,false);
		$base=$account['environment']==='live'?'https://api-m.paypal.com':'https://api-m.sandbox.paypal.com';
		$token=$this->accessToken($base,$account);
		$headers=['Authorization: Bearer '.$token,'Content-Type: application/json','Accept: application/json'];
		if($requestId) $headers[]='PayPal-Request-Id: '.$requestId;
		$ch=curl_init($base.$path);
		curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,
			CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
		if($body!==null) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
		$response=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
		if($response===false) throw new RuntimeException('PayPal is temporarily unavailable. '.$err);
		$data=json_decode($response,true);
		if($status<200||$status>=300||!is_array($data)){
			$error=is_array($data)?(string)($data['message']??$data['name']??'request rejected'):'invalid response';
			throw new RuntimeException('PayPal request failed ('.$status.'): '.$error);
		}
		return $data;
	}
	private function accessToken($base,array $account){
		$ch=curl_init($base.'/v1/oauth2/token');
		curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'grant_type=client_credentials',
			CURLOPT_USERPWD=>$account['client_id'].':'.$account['client_secret'],CURLOPT_HTTPHEADER=>['Accept: application/json','Accept-Language: en_US'],
			CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
		$response=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
		$data=json_decode((string)$response,true);
		if($response===false||$status<200||$status>=300||empty($data['access_token'])) throw new RuntimeException('PayPal authentication failed. '.$err);
		return $data['access_token'];
	}
	private function account($storeId,$requireEnabled){
		$row=$this->CI->db->where('store_id',(int)$storeId)->get('db_storefront_paypal_accounts')->row();
		if(!$row || empty($row->client_id)||empty($row->client_secret)||($requireEnabled&&!$row->enabled)) throw new RuntimeException('PayPal checkout is not configured for this store.');
		return ['client_id'=>(string)$row->client_id,'client_secret'=>$this->unseal((string)$row->client_secret),
			'webhook_id'=>(string)($row->webhook_id??''),'environment'=>(string)($row->environment??'sandbox')];
	}
	private function key(){
		$key=(string)$this->CI->config->item('encryption_key'); if($key==='') throw new RuntimeException('Application encryption key is not configured.');
		return hash('sha256',$key,true);
	}
	private function seal($plain){
		$iv=random_bytes(16);$cipher=openssl_encrypt($plain,'AES-256-CBC',$this->key(),OPENSSL_RAW_DATA,$iv);
		if($cipher===false) throw new RuntimeException('Could not protect PayPal credentials.');
		$mac=hash_hmac('sha256',$iv.$cipher,$this->key(),true);return base64_encode($iv.$mac.$cipher);
	}
	private function unseal($encoded){
		$raw=base64_decode($encoded,true);if($raw===false||strlen($raw)<49) throw new RuntimeException('PayPal credentials could not be read.');
		$iv=substr($raw,0,16);$mac=substr($raw,16,32);$cipher=substr($raw,48);
		if(!hash_equals($mac,hash_hmac('sha256',$iv.$cipher,$this->key(),true))) throw new RuntimeException('PayPal credentials could not be verified.');
		$plain=openssl_decrypt($cipher,'AES-256-CBC',$this->key(),OPENSSL_RAW_DATA,$iv);
		if($plain===false) throw new RuntimeException('PayPal credentials could not be decrypted.');return $plain;
	}
}
