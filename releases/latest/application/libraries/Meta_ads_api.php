<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Small official Graph API client. It never logs or returns access tokens. */
class Meta_ads_api {
    private $CI;
    private $appId;
    private $loginConfigId;
    private $appSecret;
    private $version;
    private $redirectUri;
    private $key;
    private $keyIsRaw = false;
    private $centralSettingsLoaded = false;
    private $centralFetchStatus = 'not_checked';
    private $centralFetchMessage = 'Central Meta settings have not been requested yet.';
    private $nextAppSecretPresent = false;
    private $nextMasterKeyPresent = false;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->config->load('meta_ads', true);
        $this->appId = (string)$this->CI->config->item('meta_ads_app_id', 'meta_ads');
        $this->loginConfigId = (string)$this->CI->config->item('meta_ads_login_config_id', 'meta_ads');
        $this->appSecret = (string)$this->CI->config->item('meta_ads_app_secret', 'meta_ads');
        $this->version = (string)$this->CI->config->item('meta_ads_graph_version', 'meta_ads');
        $this->redirectUri = (string)$this->CI->config->item('meta_ads_redirect_uri', 'meta_ads');
        $this->key = (string)$this->CI->config->item('meta_ads_token_key', 'meta_ads');
    }

    /** Fetch the rotatable Meta app credentials from Next Admin's encrypted settings vault.
     *  The stable Central billing HMAC authenticates this server-to-server request.
     */
    private function loadCentralCredentials() {
        if ($this->centralSettingsLoaded || !function_exists('mp_is_central') || !mp_is_central()) return;
        $this->centralSettingsLoaded = true;
        $base = rtrim((string)getenv('CENTRAL_BILLING_API_URL'), '/');
        $secret = (string)getenv('CENTRAL_BILLING_API_SECRET');
        if ($base === '' || parse_url($base, PHP_URL_SCHEME) !== 'https' || strlen($secret) < 32 || !function_exists('curl_init')) {
            $this->centralFetchStatus='not_configured';
            $this->centralFetchMessage='Central-to-Next HTTPS URL, 32-character service secret, and PHP cURL are required.';
            return;
        }
        $path = '/api/internal/meta-ads/config'; $timestamp = (string)time();
        $signature = hash_hmac('sha256', $timestamp."\n".$path."\n", $secret);
        $ch = curl_init($base.$path);
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>10,
            CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_HTTPHEADER=>array('Accept: application/json','X-Central-Timestamp: '.$timestamp,'X-Central-Signature: '.$signature)));
        $body = curl_exec($ch); $status = (int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        $data = is_string($body) ? json_decode($body,true) : null;
        if ($status !== 200 || !is_array($data) || empty($data['success'])) {
            $this->centralFetchStatus='unavailable';
            $this->centralFetchMessage='Next Admin settings route returned HTTP '.$status.' or an invalid response.';
            return;
        }
        $this->appId = (string)($data['appId'] ?? $this->appId);
        $this->loginConfigId = (string)($data['loginConfigId'] ?? $this->loginConfigId);
        $this->redirectUri = (string)($data['redirectUri'] ?? $this->redirectUri);
        $this->version = (string)($data['graphVersion'] ?? $this->version);
        if (!empty($data['appSecret'])) { $this->appSecret = (string)$data['appSecret']; $this->nextAppSecretPresent = true; }
        if (!empty($data['tokenMasterKey'])) {
            $master = base64_decode((string)$data['tokenMasterKey'], true);
            if (is_string($master) && strlen($master) === 32) { $this->key = $master; $this->keyIsRaw = true; $this->nextMasterKeyPresent = true; }
        }
        $this->centralFetchStatus='connected';
        $this->centralFetchMessage='Central fetched the platform settings securely from Next Admin.';
    }

    public function appSecret() {
        $this->loadCentralCredentials();
        return $this->appSecret;
    }

    public function ready() {
        return $this->oauthReady() && $this->tokenEncryptionReady();
    }

    /** Safe Central operator diagnostics; never returns secret or key material. */
    public function diagnostics() {
        $central=function_exists('mp_is_central') && mp_is_central();
        if($central) { $this->loadCentralCredentials(); $this->oauthReady(); $this->tokenEncryptionReady(); }
        $base=rtrim((string)getenv('CENTRAL_BILLING_API_URL'),'/'); $secret=(string)getenv('CENTRAL_BILLING_API_SECRET');
        return array(
            'is_central'=>$central,
            'api_url_present'=>$base!=='',
            'api_url_https'=>$base!=='' && parse_url($base,PHP_URL_SCHEME)==='https' && (bool)parse_url($base,PHP_URL_HOST),
            'service_secret_present'=>strlen($secret)>=32,
            'curl_available'=>function_exists('curl_init'),
            'next_settings_status'=>$this->centralFetchStatus,
            'next_settings_message'=>$this->centralFetchMessage,
            'app_id_present'=>$this->appId!=='',
            'login_config_present'=>$this->loginConfigId!=='',
            'redirect_uri_present'=>$this->redirectUri!=='',
            'app_secret_present'=>$this->appSecret!=='',
            'next_app_secret_present'=>$this->nextAppSecretPresent,
            'next_master_key_present'=>$this->nextMasterKeyPresent,
            'master_key_present'=>strlen($this->key)===32,
            'oauth_ready'=>$this->oauthReady(),
            'encryption_ready'=>$this->tokenEncryptionReady()
        );
    }

    /** OAuth credentials live on Central; token encryption remains fleet-local. */
    public function oauthReady() {
        $this->loadCentralCredentials();
        return $this->appId !== '' && $this->loginConfigId !== '' && $this->appSecret !== ''
            && function_exists('curl_init');
    }

    public function tokenEncryptionReady() {
        if (function_exists('mp_is_central') && mp_is_central()) $this->loadCentralCredentials();
        elseif (strlen($this->key) < 32) $this->loadFleetEncryptionKey();
        return strlen($this->key) >= 32 && function_exists('openssl_encrypt');
    }

    public function tokenMasterKey() {
        $this->loadCentralCredentials();
        return strlen($this->key) === 32 ? $this->key : '';
    }

    /** Fleet keys are derived centrally from a stable platform key and Fleet identity.
     *  The derived key exists only in this PHP request; it is never placed in cPanel.
     */
    private function loadFleetEncryptionKey() {
        if (strlen($this->key) >= 32 || !function_exists('mp_is_central') || mp_is_central()) return;
        if (!function_exists('curl_init')) return;
        $identity = $this->CI->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array() ?: array();
        $central = rtrim((string)($identity['fleet_url'] ?? ''),'/'); $installKey = (string)($identity['install_key'] ?? '');
        if (parse_url($central,PHP_URL_SCHEME)!=='https' || strlen($installKey)<32) return;
        $installUrl = rtrim(base_url(),'/').'/'; $timestamp = (string)time();
        $payload = json_encode(array('install_url'=>$installUrl),JSON_UNESCAPED_SLASHES);
        $this->CI->load->library('messaging_pricing');
        $signature = Messaging_pricing::signature('ai_ads_encryption_key_request',$timestamp,$installUrl,$payload,$installKey);
        $ch = curl_init($central.'/marketing/ai_ads/broker_encryption_key');
        curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$payload,'signature'=>$signature)),
            CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
        $body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        $data=is_string($body)?json_decode($body,true):null;
        if($status===200&&is_array($data)&&!empty($data['encryption_key'])) {
            $derived=base64_decode((string)$data['encryption_key'],true);
            if(is_string($derived)&&strlen($derived)===32) { $this->key=$derived; $this->keyIsRaw=true; }
        }
    }

    public function authorizationUrl($state) {
        $this->loadCentralCredentials();
        if (!$this->oauthReady()) throw new RuntimeException('Meta Ads OAuth is not configured on Central.');
        // Facebook Login for Business configurations carry the requested scopes/assets.
        // Business Integration System User tokens require the authorization-code grant.
        $query = array('client_id'=>$this->appId, 'redirect_uri'=>$this->redirectUri,
            'state'=>$state, 'config_id'=>$this->loginConfigId, 'response_type'=>'code',
            'override_default_response_type'=>'true');
        return 'https://www.facebook.com/'.$this->version.'/dialog/oauth?'.http_build_query($query);
    }

    public function exchangeCode($code) {
        $this->loadCentralCredentials();
        $token = $this->request('GET', 'https://graph.facebook.com/'.$this->version.'/oauth/access_token', array(
            'client_id'=>$this->appId, 'client_secret'=>$this->appSecret, 'redirect_uri'=>$this->redirectUri, 'code'=>$code));
        if (empty($token['access_token'])) throw new RuntimeException('Meta did not return a business integration access token.');
        return array('access_token'=>$token['access_token'], 'expires_in'=>(int)($token['expires_in'] ?? 0));
    }

    public function accounts($token) {
        return $this->request('GET', 'https://graph.facebook.com/'.$this->version.'/me/adaccounts', array(
            'fields'=>'id,name,account_status,currency,timezone_name', 'limit'=>100, 'access_token'=>$token));
    }

    public function pages($token) {
        return $this->request('GET', 'https://graph.facebook.com/'.$this->version.'/me/accounts', array(
            'fields'=>'id,name', 'limit'=>100, 'access_token'=>$token));
    }

    public function profile($token) {
        $this->loadCentralCredentials();
        return $this->request('GET', 'https://graph.facebook.com/'.$this->version.'/me', array('fields'=>'id,client_business_id','access_token'=>$token));
    }

    public function encryptToken($token) {
        if (!$this->tokenEncryptionReady()) throw new RuntimeException('Central AI Ads token encryption is unavailable.');
        $key = $this->encryptionKey(); $iv = random_bytes(12); $tag = '';
        $cipher = openssl_encrypt($token, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) throw new RuntimeException('Meta token could not be encrypted.');
        return base64_encode($iv.$tag.$cipher);
    }

    public function decryptToken($payload) {
        $raw = base64_decode((string)$payload, true);
        if ($raw === false || strlen($raw) < 29 || !$this->tokenEncryptionReady()) throw new RuntimeException('Stored Meta connection cannot be decrypted.');
        $token = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $this->encryptionKey(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($token === false) throw new RuntimeException('Stored Meta connection cannot be decrypted.');
        return $token;
    }

    private function encryptionKey() {
        return $this->keyIsRaw ? $this->key : hash('sha256', $this->key, true);
    }

    private function request($method, $url, array $params) {
        if (!function_exists('curl_init')) throw new RuntimeException('cURL is required for Meta Ads.');
        if (!empty($params['access_token']) && $this->appSecret !== '') {
            $params['appsecret_proof'] = hash_hmac('sha256', (string)$params['access_token'], $this->appSecret);
        }
        if ($method === 'GET') $url .= '?'.http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8); curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if ($method !== 'GET') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); }
        $body = curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
        if ($body === false) throw new RuntimeException('Meta could not be reached: '.($err ?: 'network error'));
        $data = json_decode($body, true);
        if (!is_array($data) || $status < 200 || $status >= 300 || isset($data['error'])) {
            $message = is_array($data) ? ($data['error']['message'] ?? '') : '';
            throw new RuntimeException($message !== '' ? mb_substr($message, 0, 300) : 'Meta returned an unreadable response.');
        }
        return $data;
    }
}
