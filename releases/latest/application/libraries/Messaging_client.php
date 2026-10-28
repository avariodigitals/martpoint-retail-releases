<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Fleet transport. Provider keys and balances never travel to the fleet. */
class Messaging_client {
    protected $CI;
    public function __construct() { $this->CI =& get_instance(); $this->CI->load->library('messaging_pricing'); }

    protected function identity() {
        return $this->CI->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array() ?: array();
    }

    public function managed() {
        if (mp_is_central()) return false;
        $id = $this->identity();
        return !empty($id['fleet_url']) && !empty($id['install_key']);
    }

    public function request($action, $storeId, array $payload = array()) {
        $id = $this->identity();
        if (mp_is_central() || empty($id['fleet_url']) || strlen($id['install_key'] ?? '') < 32) {
            return array('success'=>false,'code'=>'not_enrolled','message'=>'Connect this installation to MartPoint Central before buying credits or sending campaigns.');
        }
        $url = rtrim($id['fleet_url'],'/');
        if (parse_url($url,PHP_URL_SCHEME) !== 'https') return array('success'=>false,'message'=>'The Central connection must use HTTPS.');
        $store = $this->CI->db->where('id',(int)$storeId)->get('db_store')->row();
        if (!$store) return array('success'=>false,'message'=>'Store not found.');
        $payload['store_id'] = (int)$storeId;
        $payload['store_name'] = $store->store_name;
        $payload['reply_to'] = filter_var($store->email ?? '',FILTER_VALIDATE_EMAIL) ? $store->email : '';
        $payload['nonce'] = bin2hex(random_bytes(12));
        $body = json_encode($payload,JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
        $timestamp = (string)time();
        $installUrl = rtrim(base_url(),'/').'/';
        $envelope = array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$body,
            'signature'=>Messaging_pricing::signature($action,$timestamp,$installUrl,$body,$id['install_key']));
        return $this->transport($url.'/messaging_api/'.$action,$envelope);
    }

    protected function transport($url, array $body) {
        $ch = curl_init($url);
        curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($body),
            CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>45,
            CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_HTTPHEADER=>array('Accept: application/json')));
        $raw = curl_exec($ch); $http = (int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        $result = is_string($raw) ? json_decode($raw,true) : null;
        if (!is_array($result) || $http !== 200) return array('success'=>false,'code'=>'central_unavailable',
            'message'=>'MartPoint Central could not confirm this request. Check the messaging history before retrying.');
        return $result;
    }

    public function sendEmail($storeId, $to, $subject, $html, $text, array $options = array()) {
        $recipients = is_array($to) ? $to : preg_split('/[,;]/',(string)$to);
        if (!empty($options['send_copy_to_owner'])) {
            $store = $this->CI->db->where('id',(int)$storeId)->get('db_store')->row();
            if (!empty($store->email)) $recipients[] = $store->email;
        }
        $recipients = array_values(array_unique(array_map(function($email){return strtolower(trim($email));},$recipients)));
        foreach ($recipients as $recipient) {
            if (!filter_var($recipient,FILTER_VALIDATE_EMAIL)) return array('success'=>false,'message'=>'A recipient email address is invalid.');
        }
        if (!$recipients || count($recipients)>100) return array('success'=>false,'message'=>'Send between 1 and 100 recipients per request.');
        $rootKey = $options['messaging_key'] ?? bin2hex(random_bytes(20));
        $accepted = 0; $cost = 0;
        foreach ($recipients as $recipient) {
            $payload = array('channel'=>'email','category'=>Messaging_pricing::category($options),
                'recipient'=>$recipient,'subject'=>$subject,'html'=>$html,'text'=>$text,
                'request_key'=>hash('sha256',$rootKey.'|'.strtolower($recipient)),
                'campaign_id'=>(string)(($options['related_module'] ?? '') === 'campaign' ? ($options['related_record_id'] ?? '') : ''),
                'retry_failed'=>!empty($options['retry_failed']));
            // Only server-rendered, allowlisted security/subscription templates
            // can use the uncharged platform lane. Raw HTML cannot opt into it.
            if (!empty($options['platform_data']) && Messaging_pricing::platformTemplate($options['template_key'] ?? '')) {
                $payload['platform_template'] = $options['template_key'];
                $payload['platform_data'] = $options['platform_data'];
                $payload['category'] = 'platform';
                unset($payload['html'],$payload['text'],$payload['subject']);
            }
            $result = $this->request('send',$storeId,$payload);
            if (empty($result['success'])) {
                $result['message'] = ($accepted ? $accepted.' recipient(s) accepted before this pause. ' : '').($result['message'] ?? 'Send paused.');
                return $result;
            }
            $accepted++; $cost += (int)($result['cost_kobo'] ?? 0);
        }
        return array('success'=>true,'message'=>$accepted.' email(s) accepted for sending.','cost_kobo'=>$cost,'accepted'=>$accepted);
    }

    public function sendSms($storeId, $recipient, $text, array $options = array()) {
        return $this->request('send',$storeId,array('channel'=>'sms','category'=>Messaging_pricing::category($options),
            'recipient'=>$recipient,'text'=>$text,'request_key'=>hash('sha256',($options['messaging_key'] ?? bin2hex(random_bytes(20))).'|'.$recipient),
            'campaign_id'=>(string)($options['related_record_id'] ?? ''),'retry_failed'=>!empty($options['retry_failed'])));
    }
}
