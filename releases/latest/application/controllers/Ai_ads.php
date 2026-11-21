<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Merchant-owned Meta Ads workspace. Publishing remains gated on live entitlements and API approval. */
class Ai_ads extends MY_Controller {
    public function __construct() {
        parent::__construct(); $this->load_global();
        $this->load->model('ai_ads_model','ads'); $this->load->library('meta_ads_api');
    }
    private function allowed() {
        return is_admin() || is_store_admin() || $this->permissions('send_email') || $this->permissions('send_sms');
    }
    private function requireAccess() { if (!$this->allowed()) { show_error('AI Ads access denied.',403); return false; } return true; }
    private function isMobile() { return $this->input->get('screen')==='mobile' || is_mobile(); }
    private function storeId() { return (int)get_current_store_id(); }
    private function actorId() { return (int)($this->session->userdata('inv_userid') ?: $this->session->userdata('user_id')); }

    public function index() {
        if (!$this->requireAccess()) return;
        $storeId=$this->storeId(); $connection=$this->ads->connection($storeId); $accounts=array(); $pages=array(); $connectionError='';
        if ($connection && $connection['status']==='connected' && !empty($connection['access_token_ciphertext'])) {
            try { $token=$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']); $accounts=$this->meta_ads_api->accounts($token)['data'] ?? array(); $pages=$this->meta_ads_api->pages($token)['data'] ?? array(); }
            catch(Throwable $e) { $connectionError=$e->getMessage(); }
        }
        $data=array_merge($this->data,array(
            'page_title'=>'AI Ads','SITE_TITLE'=>get_store_name() ?: 'MartPoint','store_name'=>get_store_name() ?: 'MartPoint',
            'connection'=>$connection,'connection_error'=>$connectionError,'accounts'=>$accounts,'pages'=>$pages,
            'subscription'=>$this->ads->subscription($storeId),'has_ads_access'=>$this->ads->hasAccess($storeId),
            'campaigns'=>$this->ads->campaigns($storeId),'products'=>$this->ads->products($storeId),
            'meta_ready'=>mp_is_central()?$this->meta_ads_api->ready():$this->meta_ads_api->tokenEncryptionReady(),'flash_success'=>$this->session->flashdata('success'),'flash_error'=>$this->session->flashdata('error')
        ));
        if ($this->isMobile()) { $this->load->view('mobile/ai_ads',$data); return; }
        $data['content']=$this->load->view('ai_ads/index',$data,true); $this->load->view('mp_layout',$data);
    }

    public function connect() {
        if (!$this->requireAccess()) return;
        try {
            if (!$this->meta_ads_api->tokenEncryptionReady()) throw new RuntimeException('AI Ads token encryption is not configured on this Fleet. Ask your MartPoint administrator to add MP_AI_ADS_ENCRYPTION_KEY.');
            if (!mp_is_central()) { $this->startBrokerFlow(); return; }
            if (!$this->meta_ads_api->oauthReady()) throw new RuntimeException('Meta OAuth is not configured on Central.');
            $state=bin2hex(random_bytes(32)); $this->session->set_userdata('ai_ads_oauth',array('state'=>$state,'store_id'=>$this->storeId(),'created'=>time(),'mobile'=>$this->isMobile()));
            redirect($this->meta_ads_api->authorizationUrl($state));
        } catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); redirect('marketing/ai_ads'); }
    }

    /** Fleet begins OAuth on Central; its own Meta token is saved back to this Fleet. */
    private function startBrokerFlow() {
        $this->load->library('messaging_pricing');
        $identity=$this->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array() ?: array();
        $central=rtrim((string)($identity['fleet_url']??''),'/'); $key=(string)($identity['install_key']??'');
        if (parse_url($central,PHP_URL_SCHEME)!=='https' || !parse_url($central,PHP_URL_HOST) || strlen($key)<32) {
            throw new RuntimeException('This Fleet is not securely enrolled with Central. Check its Central URL and installation key.');
        }
        if (!$this->db->table_exists('db_ai_ads_oauth_states')) throw new RuntimeException('AI Ads broker migration is not installed on this Fleet.');
        $nonce=bin2hex(random_bytes(24)); $storeId=$this->storeId();
        $payload=json_encode(array('nonce'=>$nonce,'store_id'=>$storeId,'mobile'=>$this->isMobile()),JSON_UNESCAPED_SLASHES);
        $installUrl=rtrim(base_url(),'/').'/'; $timestamp=(string)time();
        $this->db->insert('db_ai_ads_oauth_states',array('nonce'=>$nonce,'store_id'=>$storeId,'created_at'=>date('Y-m-d H:i:s')));
        $this->session->set_userdata('ai_ads_broker_pending',$nonce);
        $query=array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$payload,
            'signature'=>Messaging_pricing::signature('ai_ads_broker_start',$timestamp,$installUrl,$payload,$key));
        redirect($central.'/marketing/ai_ads/broker_start?'.http_build_query($query));
    }

    /** Central verifies the registered Fleet signature before taking ownership of OAuth state. */
    public function broker_start() {
        if (!mp_is_central() || strtoupper($this->input->method())!=='GET') show_404();
        try {
            $this->load->library('messaging_pricing');
            $installUrl=(string)$this->input->get('install_url'); $timestamp=(string)$this->input->get('timestamp');
            $raw=(string)$this->input->get('payload'); $signature=(string)$this->input->get('signature');
            if(!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>2000||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!$this->db->table_exists('db_fleet_installs')) throw new RuntimeException('Fleet authentication failed.');
            $fleet=$this->db->where('install_url',$installUrl)->get('db_fleet_installs')->row_array();
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32||!hash_equals(Messaging_pricing::signature('ai_ads_broker_start',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $request=json_decode($raw,true);
            if(!is_array($request)||!preg_match('/^[a-f0-9]{48}$/D',(string)($request['nonce']??''))||!is_int($request['store_id']??null)||$request['store_id']<1) throw new RuntimeException('Invalid Fleet authorization request.');
            if(!$this->meta_ads_api->oauthReady()) throw new RuntimeException('Meta OAuth is not configured on Central.');
            if(!$this->db->table_exists('db_ai_ads_oauth_states')) throw new RuntimeException('AI Ads broker migration is not installed on Central.');
            $this->db->where('created_at <',date('Y-m-d H:i:s',time()-86400))->delete('db_ai_ads_oauth_states');
            $this->db->query('INSERT IGNORE INTO db_ai_ads_oauth_states (nonce,store_id,created_at) VALUES (?,?,?)',array($request['nonce'],$request['store_id'],date('Y-m-d H:i:s')));
            if($this->db->affected_rows()!==1) throw new RuntimeException('This Fleet authorization request was already used.');
            $state=bin2hex(random_bytes(32));
            $this->session->set_userdata('ai_ads_broker_oauth',array('state'=>$state,'created'=>time(),'install_url'=>rtrim($fleet['install_url'],'/').'/','install_key'=>$fleet['install_key'],'nonce'=>$request['nonce'],'store_id'=>$request['store_id'],'mobile'=>!empty($request['mobile'])));
            $this->output->set_header('Cache-Control: no-store')->set_header('Referrer-Policy: no-referrer');
            redirect($this->meta_ads_api->authorizationUrl($state));
        } catch(Throwable $e) { log_message('error','AI Ads broker start failed: '.mb_substr($e->getMessage(),0,250)); show_error('MartPoint could not start the Meta connection. Return to your Fleet and try again.',400); }
    }

    /** Return a per-Fleet token key derived from the Central-only master key.
     *  Fleet install identity is verified against Central's registered install key.
     */
    public function broker_encryption_key() {
        if(!mp_is_central()) show_404();
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $this->load->library('messaging_pricing');
            $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp');
            $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature');
            if(parse_url($installUrl,PHP_URL_SCHEME)!=='https'||rtrim($installUrl,'/').'/'!==$installUrl
                ||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>1000
                ||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!$this->db->table_exists('db_fleet_installs')) throw new RuntimeException('Fleet authentication failed.');
            $fleet=$this->db->where('install_url',$installUrl)->get('db_fleet_installs')->row_array();
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32
                ||!hash_equals(Messaging_pricing::signature('ai_ads_encryption_key_request',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $payload=json_decode($raw,true);
            if(!is_array($payload)||rtrim((string)($payload['install_url']??''),'/').'/'!==$installUrl) throw new RuntimeException('Fleet identity mismatch.');
            $master=$this->meta_ads_api->tokenMasterKey();
            if(strlen($master)!==32) throw new RuntimeException('Central token encryption is unavailable.');
            $derived=hash_hmac('sha256','martpoint:ai_ads:fleet:v1:'.$installUrl,$master,true);
            return $this->output->set_output(json_encode(array('success'=>true,'encryption_key'=>base64_encode($derived))));
        } catch(Throwable $e) {
            log_message('error','AI Ads Fleet key request rejected: '.mb_substr($e->getMessage(),0,200));
            return $this->output->set_status_header(403)->set_output(json_encode(array('success'=>false)));
        }
    }

    /** Fixed Meta callback on Central exchanges the code, then signs a one-time Fleet handoff. */
    public function broker_callback() {
        if(!mp_is_central()) show_404();
        if(!$this->session->userdata('ai_ads_broker_oauth')) { $this->callback(); return; }
        $this->load->library('messaging_pricing');
        $saved=$this->session->userdata('ai_ads_broker_oauth'); $this->session->unset_userdata('ai_ads_broker_oauth');
        try {
            $state=(string)$this->input->get('state'); $code=(string)$this->input->get('code');
            $error=trim((string)$this->input->get('error_description')) ?: trim((string)$this->input->get('error_reason'));
            if($error!=='') throw new RuntimeException('Meta authorization was not completed.');
            if(!$saved||time()-(int)($saved['created']??0)>600||!hash_equals((string)$saved['state'],$state)||$code==='') throw new RuntimeException('Meta connection expired or was cancelled.');
            $token=$this->meta_ads_api->exchangeCode($code); $profile=$this->meta_ads_api->profile($token['access_token']);
            if(empty($profile['client_business_id'])) throw new RuntimeException('Meta did not identify the connected business portfolio.');
            $fleetUrl=rtrim((string)$saved['install_url'],'/'); $key=(string)$saved['install_key'];
            $payload=json_encode(array('nonce'=>$saved['nonce'],'store_id'=>(int)$saved['store_id'],'meta_user_id'=>(string)($profile['id']??''),'meta_business_id'=>(string)$profile['client_business_id'],'access_token'=>$token['access_token'],'expires_in'=>(int)$token['expires_in']),JSON_UNESCAPED_SLASHES);
            $timestamp=(string)time(); $envelope=array('install_url'=>$fleetUrl.'/','timestamp'=>$timestamp,'payload'=>$payload,'signature'=>Messaging_pricing::signature('ai_ads_broker_complete',$timestamp,$fleetUrl.'/',$payload,$key));
            $ch=curl_init($fleetUrl.'/marketing/ai_ads/broker_complete');
            curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
            $body=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
            $result=is_string($body)?json_decode($body,true):null;
            if($http!==200||!is_array($result)||empty($result['success'])) throw new RuntimeException('Central could not securely save the Meta connection to the Fleet.');
            redirect($fleetUrl.'/marketing/ai_ads/broker_return?nonce='.rawurlencode($saved['nonce']).'&status=connected'.(!empty($saved['mobile'])?'&screen=mobile':''));
        } catch(Throwable $e) { log_message('error','AI Ads broker callback failed: '.mb_substr($e->getMessage(),0,250)); show_error('Meta could not be connected to this Fleet. Return to AI Ads and try again.',400); }
    }

    /** HMAC-authenticated Central-to-Fleet callback; token is encrypted locally before storage. */
    public function broker_complete() {
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        $transactionStarted=false;
        try {
            if(mp_is_central()||strtoupper($this->input->method())!=='POST') throw new RuntimeException('Invalid callback.');
            $this->load->library('messaging_pricing');
            $identity=$this->db->select('install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $key=(string)($identity['install_key']??''); $installUrl=(string)$this->input->post('install_url');
            $timestamp=(string)$this->input->post('timestamp'); $raw=(string)$this->input->post('payload'); $sig=(string)$this->input->post('signature');
            $expected=rtrim(base_url(),'/').'/';
            if(strlen($key)<32||rtrim($installUrl,'/').'/'!==$expected||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>12000||!preg_match('/^[a-f0-9]{64}$/D',$sig)||!hash_equals(Messaging_pricing::signature('ai_ads_broker_complete',$timestamp,$expected,$raw,$key),$sig)) throw new RuntimeException('Central authentication failed.');
            $data=json_decode($raw,true); $nonce=(string)($data['nonce']??''); $storeId=(int)($data['store_id']??0); $token=(string)($data['access_token']??'');
            if(!preg_match('/^[a-f0-9]{48}$/D',$nonce)||$storeId<1||$token===''||empty($data['meta_business_id'])||!$this->meta_ads_api->tokenEncryptionReady()||!$this->db->table_exists('db_ai_ads_oauth_states')) throw new RuntimeException('Invalid Meta handoff.');
            $this->db->trans_begin(); $transactionStarted=true;
            $pending=$this->db->query('SELECT nonce,created_at FROM db_ai_ads_oauth_states WHERE nonce=? AND store_id=? FOR UPDATE',array($nonce,$storeId))->row_array();
            if(!$pending||strtotime($pending['created_at'])<time()-900) throw new RuntimeException('Meta handoff expired or was already used.');
            $row=array('store_id'=>$storeId,'meta_user_id'=>(string)($data['meta_user_id']??''),'meta_business_id'=>(string)$data['meta_business_id'],'access_token_ciphertext'=>$this->meta_ads_api->encryptToken($token),'token_expires_at'=>!empty($data['expires_in'])?date('Y-m-d H:i:s',time()+(int)$data['expires_in']):null,'status'=>'connected','last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null);
            $existing=$this->ads->connection($storeId); if($existing)$this->db->where('store_id',$storeId)->update('db_ai_ads_connections',$row);else $this->db->insert('db_ai_ads_connections',$row);
            $this->db->where('nonce',$nonce)->delete('db_ai_ads_oauth_states'); $this->ads->audit($storeId,null,0,'meta_connected','Merchant authorized a Meta connection through Central.');
            if($this->db->trans_status()===false) throw new RuntimeException('Fleet could not save the Meta connection.');
            $this->db->trans_commit(); $transactionStarted=false;
            return $this->output->set_output(json_encode(array('success'=>true)));
        } catch(Throwable $e) { if($transactionStarted) $this->db->trans_rollback(); log_message('error','AI Ads broker handoff rejected: '.mb_substr($e->getMessage(),0,250)); $this->output->set_status_header(403); return $this->output->set_output(json_encode(array('success'=>false))); }
    }

    public function broker_return() {
        $nonce=(string)$this->input->get('nonce'); $expected=(string)$this->session->userdata('ai_ads_broker_pending');
        if($expected!==''&&hash_equals($expected,$nonce)) { $this->session->unset_userdata('ai_ads_broker_pending'); $this->session->set_flashdata('success','Meta is connected. Choose the advertising account and Page you want MartPoint to use.'); }
        else $this->session->set_flashdata('error','Meta broker return could not be verified. Check the connection status and retry if needed.');
        redirect('marketing/ai_ads'.($this->input->get('screen')==='mobile'?'?screen=mobile':''));
    }

    /** Meta's signed Facebook Login deletion request endpoint; Central fans out to enrolled Fleets. */
    public function meta_data_deletion() {
        if(!mp_is_central()) show_404();
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $metaUserId=$this->metaSignedRequestUserId();
            $this->removeMetaUserData($metaUserId,'delete');
            if(!$this->db->table_exists('db_ai_ads_deletion_requests')) throw new RuntimeException('AI Ads deletion status migration is missing.');
            $confirmation=bin2hex(random_bytes(20));
            $this->db->insert('db_ai_ads_deletion_requests',array('confirmation_code'=>$confirmation,'completed_at'=>date('Y-m-d H:i:s')));
            $statusUrl=rtrim(base_url(),'/').'/marketing/ai_ads/meta_deletion_status?code='.rawurlencode($confirmation);
            return $this->output->set_output(json_encode(array('url'=>$statusUrl,'confirmation_code'=>$confirmation),JSON_UNESCAPED_SLASHES));
        } catch(Throwable $e) {
            log_message('error','Meta AI Ads deletion callback failed: '.mb_substr($e->getMessage(),0,200));
            return $this->output->set_status_header(503)->set_output(json_encode(array('error'=>'Deletion request could not yet be completed.')));
        }
    }

    /** Called by Meta when a person removes MartPoint from their Facebook settings. */
    public function meta_deauthorize() {
        if(!mp_is_central()) show_404();
        $this->output->set_content_type('text/plain','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $this->removeMetaUserData($this->metaSignedRequestUserId(),'deauthorize');
            return $this->output->set_output('OK');
        } catch(Throwable $e) {
            log_message('error','Meta AI Ads deauthorization callback failed: '.mb_substr($e->getMessage(),0,200));
            return $this->output->set_status_header(503)->set_output('Callback could not yet be completed.');
        }
    }

    /** Public, code-protected status URL returned to Meta for a deletion request. */
    public function meta_deletion_status() {
        if(!mp_is_central()) show_404();
        $code=(string)$this->input->get('code');
        if(!preg_match('/^[a-f0-9]{40}$/D',$code)||!$this->db->table_exists('db_ai_ads_deletion_requests')) show_404();
        $row=$this->db->where('confirmation_code',$code)->get('db_ai_ads_deletion_requests')->row_array();
        if(!$row) show_404();
        return $this->output->set_content_type('text/plain','UTF-8')->set_header('Cache-Control','no-store')
            ->set_output('MartPoint has completed this Meta data deletion request. Confirmation: '.$code);
    }

    /** Signed Central-to-Fleet deletion/deauthorization operation. */
    public function broker_apply_meta_removal() {
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(mp_is_central()||strtoupper($this->input->method())!=='POST') throw new RuntimeException('Invalid callback.');
            $this->load->library('messaging_pricing');
            $identity=$this->db->select('install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $key=(string)($identity['install_key']??''); $expected=rtrim(base_url(),'/').'/';
            $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp');
            $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature');
            $operation=(string)$this->input->post('operation');
            if(!in_array($operation,array('delete','deauthorize'),true)||strlen($key)<32||rtrim($installUrl,'/').'/'!==$expected
                ||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>1000
                ||!preg_match('/^[a-f0-9]{64}$/D',$signature)
                ||!hash_equals(Messaging_pricing::signature('ai_ads_meta_'.$operation,$timestamp,$expected,$raw,$key),$signature)) throw new RuntimeException('Central authentication failed.');
            $payload=json_decode($raw,true); $metaUserId=(string)($payload['meta_user_id']??'');
            if(!preg_match('/^[0-9]{1,80}$/D',$metaUserId)) throw new RuntimeException('Invalid Meta account identifier.');
            $this->removeMetaUserDataLocally($metaUserId,$operation==='delete');
            return $this->output->set_output(json_encode(array('success'=>true)));
        } catch(Throwable $e) {
            log_message('error','Central Meta removal handoff rejected: '.mb_substr($e->getMessage(),0,200));
            return $this->output->set_status_header(403)->set_output(json_encode(array('success'=>false)));
        }
    }

    private function metaSignedRequestUserId() {
        $secret=(string)$this->meta_ads_api->appSecret();
        $signed=(string)$this->input->post('signed_request',false);
        $parts=explode('.',$signed,2);
        if(strlen($secret)<16||count($parts)!==2) throw new RuntimeException('Invalid Meta signature.');
        $signature=$this->base64UrlDecode($parts[0]); $payloadRaw=$this->base64UrlDecode($parts[1]);
        if($signature===false||$payloadRaw===false||!hash_equals(hash_hmac('sha256',$parts[1],$secret,true),$signature)) throw new RuntimeException('Invalid Meta signature.');
        $payload=json_decode($payloadRaw,true);
        if(!is_array($payload)||strtoupper((string)($payload['algorithm']??''))!=='HMAC-SHA256') throw new RuntimeException('Invalid Meta request format.');
        $userId=(string)($payload['user_id']??'');
        if(!preg_match('/^[0-9]{1,80}$/D',$userId)) throw new RuntimeException('Meta account identifier is missing.');
        return $userId;
    }

    private function base64UrlDecode($value) {
        $value=strtr((string)$value,'-_','+/'); $padding=strlen($value)%4;
        if($padding) $value.=str_repeat('=',4-$padding);
        return base64_decode($value,true);
    }

    /** Clear Meta-provided identifiers/tokens on Central and every enrolled Fleet. */
    private function removeMetaUserData($metaUserId,$operation) {
        if(!in_array($operation,array('delete','deauthorize'),true)) throw new RuntimeException('Invalid Meta removal operation.');
        $this->removeMetaUserDataLocally($metaUserId,$operation==='delete');
        if(!mp_is_central()) return;
        $this->load->library('messaging_pricing');
        if(!$this->db->table_exists('db_fleet_installs')) throw new RuntimeException('Fleet registry is unavailable.');
        $fleets=$this->db->select('install_url,install_key')->get('db_fleet_installs')->result_array();
        if(!$fleets) return;
        if(!function_exists('curl_multi_init')) throw new RuntimeException('cURL multi is required for fleet deletion.');
        $multi=curl_multi_init(); $handles=array(); $invalid=0;
        foreach($fleets as $fleet) {
            $base=rtrim((string)($fleet['install_url']??''),'/'); $key=(string)($fleet['install_key']??'');
            if(parse_url($base,PHP_URL_SCHEME)!=='https'||!parse_url($base,PHP_URL_HOST)||strlen($key)<32) { $invalid++; continue; }
            $installUrl=$base.'/'; $raw=json_encode(array('meta_user_id'=>(string)$metaUserId),JSON_UNESCAPED_SLASHES);
            $timestamp=(string)time();
            $envelope=array('operation'=>$operation,'install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$raw,
                'signature'=>Messaging_pricing::signature('ai_ads_meta_'.$operation,$timestamp,$installUrl,$raw,$key));
            $ch=curl_init($base.'/marketing/ai_ads/broker_apply_meta_removal');
            curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>18,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
            curl_multi_add_handle($multi,$ch); $handles[]=$ch;
        }
        $running=null; $deadline=microtime(true)+20;
        do {
            do { $status=curl_multi_exec($multi,$running); } while($status===CURLM_CALL_MULTI_PERFORM);
            if($running&&microtime(true)<$deadline) { $selected=curl_multi_select($multi,0.25); if($selected===-1) usleep(100000); }
        } while($running&&microtime(true)<$deadline);
        $failed=$invalid;
        foreach($handles as $ch) {
            $body=curl_multi_getcontent($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $result=is_string($body)?json_decode($body,true):null;
            if($http!==200||!is_array($result)||empty($result['success'])) $failed++;
            curl_multi_remove_handle($multi,$ch); curl_close($ch);
        }
        curl_multi_close($multi);
        if($failed) throw new RuntimeException('One or more registered Fleets did not confirm Meta data removal.');
    }

    private function removeMetaUserDataLocally($metaUserId,$deleteMetaData) {
        if(!$this->db->table_exists('db_ai_ads_connections')) return;
        $matches=$this->db->select('store_id')->where('meta_user_id',(string)$metaUserId)->get('db_ai_ads_connections')->result_array();
        $storeIds=array_values(array_unique(array_map(function($row){return (int)$row['store_id'];},$matches)));
        if(!$storeIds) return;
        $this->db->trans_begin();
        $this->db->where('meta_user_id',(string)$metaUserId)->update('db_ai_ads_connections',array(
            'meta_user_id'=>null,'meta_business_id'=>null,'ad_account_id'=>null,'ad_account_name'=>null,
            'page_id'=>null,'page_name'=>null,'access_token_ciphertext'=>null,'token_expires_at'=>null,
            'status'=>'disconnected','last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null));
        if($deleteMetaData&&$this->db->table_exists('db_ai_ads_campaigns')) {
            $this->db->where_in('store_id',$storeIds)->update('db_ai_ads_campaigns',array('meta_campaign_id'=>null,'meta_adset_id'=>null,'meta_ad_id'=>null));
        }
        foreach($storeIds as $storeId) $this->ads->audit($storeId,null,0,$deleteMetaData?'meta_data_deleted':'meta_deauthorized','Meta authorization and stored Meta identifiers were removed.');
        if($this->db->trans_status()===false) { $this->db->trans_rollback(); throw new RuntimeException('Meta connection data could not be removed.'); }
        $this->db->trans_commit();
    }

    public function callback() {
        if (!$this->allowed()) { show_error('AI Ads access denied.',403); return; }
        $saved=$this->session->userdata('ai_ads_oauth'); $this->session->unset_userdata('ai_ads_oauth');
        try {
            $state=(string)$this->input->get('state'); $code=(string)$this->input->get('code');
            $oauthError=trim((string)$this->input->get('error_description'));
            if ($oauthError==='') $oauthError=trim((string)$this->input->get('error_reason'));
            if ($oauthError!=='') throw new RuntimeException('Meta authorization was not completed: '.mb_substr($oauthError,0,250));
            if (!$saved || empty($saved['created']) || time()-(int)$saved['created']>600 || empty($saved['store_id']) || !hash_equals((string)$saved['state'],$state) || $code==='') throw new RuntimeException('Meta connection expired or was cancelled. Please connect again.');
            $token=$this->meta_ads_api->exchangeCode($code); $profile=$this->meta_ads_api->profile($token['access_token']);
            $expires=$token['expires_in']>0?date('Y-m-d H:i:s',time()+$token['expires_in']):null;
            if (empty($profile['client_business_id'])) throw new RuntimeException('Meta did not identify the connected business portfolio. Make sure you used the MartAds Business Login configuration.');
            $row=array('store_id'=>(int)$saved['store_id'],'meta_user_id'=>(string)($profile['id']??''),'meta_business_id'=>(string)$profile['client_business_id'],'access_token_ciphertext'=>$this->meta_ads_api->encryptToken($token['access_token']),'token_expires_at'=>$expires,'status'=>'connected','last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null);
            $existing=$this->ads->connection((int)$saved['store_id']);
            if ($existing) $this->db->where('store_id',(int)$saved['store_id'])->update('db_ai_ads_connections',$row); else $this->db->insert('db_ai_ads_connections',$row);
            $this->ads->audit((int)$saved['store_id'],null,$this->actorId(),'meta_connected','Merchant authorized a Meta connection.');
            $this->session->set_flashdata('success','Meta is connected. Choose the advertising account and Page you want MartPoint to use.');
        } catch(Throwable $e) {
            $this->session->set_flashdata('error',$e->getMessage());
            if (isset($saved['store_id'])) { $this->db->where('store_id',(int)$saved['store_id'])->update('db_ai_ads_connections',array('status'=>'error','last_error'=>mb_substr($e->getMessage(),0,500),'last_checked_at'=>date('Y-m-d H:i:s'))); }
        }
        redirect('marketing/ai_ads'.(!empty($saved['mobile'])?'?screen=mobile':''));
    }

    public function select_assets() {
        if (!$this->requireAccess()) return; if ($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        $storeId=$this->storeId(); $connection=$this->ads->connection($storeId);
        try {
            if (!$connection || $connection['status']!=='connected') throw new RuntimeException('Connect Meta before choosing assets.');
            $token=$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']);
            $accountId=(string)$this->input->post('ad_account_id'); $pageId=(string)$this->input->post('page_id');
            $accounts=$this->meta_ads_api->accounts($token)['data']??array(); $pages=$this->meta_ads_api->pages($token)['data']??array();
            $account=null; foreach($accounts as $a) if((string)($a['id']??'')===$accountId) $account=$a;
            $page=null; foreach($pages as $p) if((string)($p['id']??'')===$pageId) $page=$p;
            if (!$account || !$page) throw new RuntimeException('Select an ad account and Page returned by Meta.');
            $this->db->where('store_id',$storeId)->update('db_ai_ads_connections',array('ad_account_id'=>$accountId,'ad_account_name'=>mb_substr((string)($account['name']??$accountId),0,190),'page_id'=>$pageId,'page_name'=>mb_substr((string)($page['name']??$pageId),0,190),'last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null));
            $this->ads->audit($storeId,null,$this->actorId(),'assets_selected','Merchant selected an ad account and Page.');
            $this->session->set_flashdata('success','Meta assets saved.');
        } catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); }
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    public function disconnect() {
        if (!$this->requireAccess()) return; if ($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        $storeId=$this->storeId(); $this->db->where('store_id',$storeId)->update('db_ai_ads_connections',array('status'=>'disconnected','access_token_ciphertext'=>null,'meta_user_id'=>null,'meta_business_id'=>null,'ad_account_id'=>null,'ad_account_name'=>null,'page_id'=>null,'page_name'=>null,'last_error'=>null));
        $this->ads->audit($storeId,null,$this->actorId(),'meta_disconnected','Merchant disconnected Meta from MartPoint.');
        $this->session->set_flashdata('success','Meta connection removed from MartPoint.'); redirect('marketing/ai_ads');
    }

    public function save_draft() {
        if (!$this->requireAccess()) return; if ($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        try {
            $prompt=trim((string)$this->input->post('prompt')); $headline=trim((string)$this->input->post('headline')); $body=trim((string)$this->input->post('body'));
            $productId=(int)$this->input->post('product_id'); $products=$this->ads->products($this->storeId()); $productOk=$productId===0;
            foreach($products as $product) if((int)$product['id']===$productId) $productOk=true;
            if ($prompt==='' || strlen($prompt)>3000 || $headline==='' || strlen($headline)>255 || $body==='' || strlen($body)>5000 || !$productOk) throw new RuntimeException('Enter what you want to advertise, a headline and ad text, and choose a valid product if applicable.');
            $url=trim((string)$this->input->post('destination_url')); if($url!=='' && (!filter_var($url,FILTER_VALIDATE_URL)||parse_url($url,PHP_URL_SCHEME)!=='https')) throw new RuntimeException('The destination must be a secure HTTPS link.');
            $budget=(int)$this->input->post('daily_budget'); if($budget<0 || $budget>100000000) throw new RuntimeException('Enter a valid daily budget in naira.');
            $duration=(int)$this->input->post('duration_days'); if($duration<1||$duration>365) throw new RuntimeException('Campaign duration must be between 1 and 365 days.');
            $key=(string)$this->input->post('request_key'); if(!preg_match('/^[a-f0-9]{32,64}$/D',$key)) throw new RuntimeException('Refresh the page and try again.');
            $now=new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos'));
            $id=$this->ads->saveDraft($this->storeId(),$this->actorId(),array('request_key'=>$key,'prompt'=>$prompt,'product_id'=>$productId,'headline'=>$headline,'body'=>$body,'destination_url'=>$url,'location_text'=>mb_substr(trim((string)$this->input->post('location_text')),0,190),'daily_budget_minor'=>$budget*100,'starts_at'=>$now->format('Y-m-d H:i:s'),'ends_at'=>$now->modify('+'.$duration.' days')->format('Y-m-d H:i:s')));
            $this->ads->audit($this->storeId(),$id,$this->actorId(),'campaign_draft_saved','Draft saved for merchant review.');
            $this->session->set_flashdata('success','Ad draft saved. Publishing is only available after an AI Ads subscription and approved Meta permissions are active.');
        } catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); }
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    public function generate_copy() {
        if (!$this->requireAccess()) return;
        if ($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            $prompt=trim((string)$this->input->post('prompt')); $productId=(int)$this->input->post('product_id'); $product=array();
            if ($prompt==='' || strlen($prompt)>3000) throw new RuntimeException('Describe the ad in up to 3,000 characters first.');
            if ($productId>0) { foreach($this->ads->products($this->storeId()) as $row) if((int)$row['id']===$productId) {$product=$row;break;} if(!$product) throw new RuntimeException('Choose a product from this store.'); }
            $this->load->library('assist_brain'); $copy=$this->assist_brain->generateAdCopy($prompt,$product);
            if (!$copy) throw new RuntimeException('AI copy generation is unavailable. Enable MartPoint Assist AI in Site Settings and check its provider settings.');
            $this->ads->audit($this->storeId(),null,$this->actorId(),'ai_copy_generated','AI produced ad copy for merchant review.');
            return $this->output->set_output(json_encode(array('success'=>true,'copy'=>$copy)));
        } catch(Throwable $e) {
            $this->output->set_status_header(422);
            return $this->output->set_output(json_encode(array('success'=>false,'message'=>$e->getMessage())));
        }
    }
}
