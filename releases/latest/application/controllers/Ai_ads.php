<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Merchant-owned Meta Ads workspace. Publishing remains gated on live entitlements and API approval. */
class Ai_ads extends MY_Controller {
    public function __construct() {
        parent::__construct();
        // These actions authenticate with a signed Fleet/Central request,
        // Meta's signed_request, a one-time OAuth state, or a one-time status
        // code. They must not require an interactive MartPoint session: the
        // server-to-server callers cannot carry a customer's browser cookie.
        $publicActions=array('broker_start','broker_encryption_key','broker_subscription_offers',
            'broker_subscription_checkout','broker_verify_subscription','broker_callback',
            'broker_complete','broker_apply_subscription','broker_return','meta_data_deletion',
            'meta_deauthorize','meta_deletion_status','broker_apply_meta_removal','broker_ai_ads_media');
        if(!in_array((string)$this->router->fetch_method(),$publicActions,true)) $this->load_global();
        $this->load->model('ai_ads_model','ads'); $this->load->library('meta_ads_api');
    }
    private function allowed() {
        return is_admin() || is_store_admin() || is_business_owner() || $this->permissions('send_email') || $this->permissions('send_sms');
    }
    private function canApproveMetaSpend() {
        return is_admin() || is_store_admin() || is_business_owner();
    }
    private function requireAccess() { if (!$this->allowed()) { show_error('AI Ads access denied.',403); return false; } return true; }
    private function isMobile() { return $this->input->get('screen')==='mobile' || is_mobile(); }
    private function storeId() { return (int)get_current_store_id(); }
    private function actorId() { return (int)($this->session->userdata('inv_userid') ?: $this->session->userdata('user_id')); }
    private function normalizeAiAdsEntitlements($value) {
        $value=is_array($value)?$value:array();
        $defaults=array('copy_generations_per_month'=>20,'ai_images_per_month'=>2,'video_exports_per_month'=>0,'ai_video_generations_per_month'=>0,'media_storage_mb'=>100);
        $maxima=array('copy_generations_per_month'=>500,'ai_images_per_month'=>100,'video_exports_per_month'=>100,'ai_video_generations_per_month'=>100,'media_storage_mb'=>10000);
        $out=array();
        foreach($defaults as $key=>$default){$n=isset($value[$key])&&is_numeric($value[$key])?(int)$value[$key]:$default;$out[$key]=max(0,min($maxima[$key],$n));}
        return $out;
    }
    /** Read only the customer-safe, currently purchasable plan prices through Central. */
    private function subscriptionOffers() {
        $offers=array();
        if(mp_is_central()||!function_exists('curl_init')) return $offers;
        $identity=$this->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
        $central=rtrim((string)($identity['fleet_url']??''),'/'); $key=(string)($identity['install_key']??'');
        if(parse_url($central,PHP_URL_SCHEME)!=='https'||strlen($key)<32) {
            log_message('error','AI Ads plan offer lookup unavailable: Fleet Central identity is incomplete.');
            return $offers;
        }
        try {
            $this->load->library('messaging_pricing'); $installUrl=rtrim(base_url(),'/').'/';
            $payload=json_encode(array('store_id'=>$this->storeId()),JSON_UNESCAPED_SLASHES); $timestamp=(string)time();
            $envelope=array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$payload,
                'signature'=>Messaging_pricing::signature('ai_ads_subscription_offers',$timestamp,$installUrl,$payload,$key));
            $ch=curl_init($central.'/marketing/ai_ads/broker_subscription_offers');
            curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
            $body=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $curlError=curl_error($ch); curl_close($ch);
            $result=is_string($body)?json_decode($body,true):null;
            if($http===200&&is_array($result)&&!empty($result['success'])&&is_array($result['offers']??null)) $offers=$result['offers'];
            else log_message('error','AI Ads plan offer lookup failed: Central HTTP '.$http.($curlError!==''?' (Fleet cURL transport error).':(is_array($result)&&!empty($result['success'])?' (offer response was incomplete).':' (broker request was rejected or returned invalid data).')));
        } catch(Throwable $e) { log_message('error','AI Ads plan offer lookup failed before a Central response: '.mb_substr(strip_tags($e->getMessage()),0,160)); }
        return $offers;
    }

    public function index() {
        if (!$this->requireAccess()) return;
        $storeId=$this->storeId(); $connection=$this->ads->connection($storeId); $accounts=array(); $pages=array(); $pixels=array(); $connectionError='';
        if ($connection && $connection['status']==='connected' && !empty($connection['access_token_ciphertext'])) {
            try {
                $token=$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']);
                $accounts=$this->meta_ads_api->accounts($token)['data'] ?? array(); $pages=$this->meta_ads_api->pages($token)['data'] ?? array();
                if(!empty($connection['ad_account_id']))$pixels=$this->meta_ads_api->pixels($token,$connection['ad_account_id'])['data']??array();
            }
            catch(Throwable $e) { $connectionError=$e->getMessage(); }
        }
        $campaigns=$this->ads->campaigns($storeId);
        $mediaAssets=$this->ads->mediaAssets($storeId);
        $mediaMimeById=array(); foreach($mediaAssets as $mediaAsset)$mediaMimeById[(int)$mediaAsset['id']]=(string)$mediaAsset['mime_type'];
        foreach($campaigns as $campaignIndex=>$campaign)if(!empty($campaign['media_asset_id']))$campaigns[$campaignIndex]['media_mime_type']=$mediaMimeById[(int)$campaign['media_asset_id']]??'';
        if($connection&&!empty($connection['access_token_ciphertext'])) {
            try {
                $token=$token??$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']);
                foreach($campaigns as $i=>$campaign) if($i<10&&!empty($campaign['meta_ad_id'])&&in_array($campaign['status'],array('active','paused'),true)) {
                    try {
                        $stats=$this->meta_ads_api->insights($token,$campaign['meta_ad_id'])['data'][0]??array();
                        $campaigns[$i]['insights']=$stats;
                        $campaigns[$i]['result_events']=array('purchases'=>0,'leads'=>0,'conversations'=>0);
                        foreach(($stats['actions']??array()) as $action) {
                            $type=strtolower((string)($action['action_type']??'')); $count=(int)($action['value']??0);
                            if(strpos($type,'purchase')!==false)$campaigns[$i]['result_events']['purchases']+=$count;
                            if(strpos($type,'lead')!==false)$campaigns[$i]['result_events']['leads']+=$count;
                            if(strpos($type,'messaging_conversation')!==false)$campaigns[$i]['result_events']['conversations']+=$count;
                        }
                    } catch(Throwable $ignored) { $campaigns[$i]['insights']=array(); $campaigns[$i]['result_events']=array(); }
                }
            } catch(Throwable $ignored) {}
        }
        $directorPrefill=$this->session->userdata('ai_ads_director_prefill')?:array();
        $generatedMediaPrefill=(int)$this->session->userdata('ai_ads_generated_media_id');
        if($directorPrefill)$this->session->unset_userdata('ai_ads_director_prefill');
        if($generatedMediaPrefill)$this->session->unset_userdata('ai_ads_generated_media_id');
        $data=array_merge($this->data,array(
            'page_title'=>'AI Ads','SITE_TITLE'=>get_store_name() ?: 'MartPoint','store_name'=>get_store_name() ?: 'MartPoint',
            'connection'=>$connection,'connection_error'=>$connectionError,'accounts'=>$accounts,'pages'=>$pages,'pixels'=>$pixels,
            'subscription'=>$this->ads->subscription($storeId),'has_ads_access'=>$this->ads->hasAccess($storeId),'subscription_offers'=>$this->subscriptionOffers(),'usage_snapshot'=>$this->ads->usageSnapshot($storeId),
            'campaigns'=>$campaigns,'products'=>$this->ads->products($storeId),'media_assets'=>$mediaAssets,'director_prefill'=>$directorPrefill,'generated_media_prefill'=>$generatedMediaPrefill,
            'billing_history'=>$this->db->table_exists('db_ai_ads_billing')?$this->db->where('store_id',$storeId)->order_by('id','desc')->limit(5)->get('db_ai_ads_billing')->result_array():array(),
            'meta_ready'=>mp_is_central()?$this->meta_ads_api->ready():$this->meta_ads_api->tokenEncryptionReady(),'flash_success'=>$this->session->flashdata('success'),'flash_error'=>$this->session->flashdata('error')
        ));
        if ($this->isMobile()) { $this->load->view('mobile/ai_ads',$data); return; }
        $data['content']=$this->load->view('ai_ads/index',$data,true); $this->load->view('mp_layout',$data);
    }

    /** Central operator view for secret-safe Meta and Next billing health. */
    public function platform() {
        if(!mp_is_central()) { show_404(); return; }
        // Match the established Central operator gate used by Communications,
        // Fleet and the release tools. Central Store Admins are valid operators
        // even when their role name is not the SaaS "Admin" role.
        if(!special_access()) { show_error('Central administrator access is required.',403); return; }
        $this->load->library('central_billing_client');
        $billingHealth=$this->central_billing_client->health();
        $centralSubscriptions=array();
        if($this->db->table_exists('db_central_ai_ads_subscriptions')&&$this->db->table_exists('db_fleet_installs')) {
            $subscriptionTable='db_central_ai_ads_subscriptions';
            $subscriptionFields=array('s.fleet_install_id','s.store_id');
            $subscriptionFields[]=$this->db->field_exists('plan_code',$subscriptionTable)
                ? 's.plan_code'
                : "IF(s.billing_period='annual','ai_ads_annual','ai_ads_monthly') AS plan_code";
            $subscriptionFields[]=$this->db->field_exists('product_code',$subscriptionTable)
                ? 's.product_code'
                : "IF(s.billing_period='annual','ai-ads-annual','ai-ads-monthly') AS product_code";
            $subscriptionFields=array_merge($subscriptionFields,array('s.billing_period','s.status','s.payment_reference','s.starts_at','s.expires_at','f.install_url'));
            $subscriptionQuery=$this->db->select(implode(',',$subscriptionFields),false)
                ->from($subscriptionTable.' s')->join('db_fleet_installs f','f.id=s.fleet_install_id','left')->order_by('s.id','desc')->limit(100)->get();
            if($subscriptionQuery!==false) $centralSubscriptions=$subscriptionQuery->result_array();
            else log_message('error','AI Ads Central subscription registry query failed: '.(string)($this->db->error()['message']??'database query failed'));
        }
        $data=array_merge($this->data,array('page_title'=>'AI Ads Operations','SITE_TITLE'=>get_store_name()?:'MartPoint',
            'diagnostics'=>$this->meta_ads_api->diagnostics(),
            'billing_configured'=>!empty($billingHealth['central_billing_enabled'])&&!empty($billingHealth['catalogue_ready'])&&!empty($billingHealth['paystack_ready']),
            'billing_health'=>$billingHealth,'subscription_registry_ready'=>$this->db->table_exists('db_central_ai_ads_subscriptions'),
            'central_subscriptions'=>$centralSubscriptions,
            'billing_config_message'=>$this->central_billing_client->configuration_error(),
            'platform_url'=>base_url('marketing/ai_ads/platform')));
        $data['content']=$this->load->view('ai_ads/platform',$data,true); $this->load->view('mp_layout',$data);
    }

    /** Create a canonical AI Ads tier invoice through Central and Next Admin. */
    public function subscribe() {
        if(!$this->requireAccess()) return;
        if(mp_is_central()) { $this->session->set_flashdata('error','AI Ads subscriptions are purchased by each Fleet business from its Fleet.'); redirect('marketing/ai_ads/platform'); return; }
        if($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        try {
            if(!$this->db->table_exists('db_ai_ads_billing')) throw new RuntimeException('Install the AI Ads subscription billing migration before checkout.');
            $productCode=(string)$this->input->post('product_code');
            if(!preg_match('/^ai-ads(?:-[a-z][a-z0-9_]{1,31})?-(monthly|annual)$/D',$productCode,$productMatch)) throw new RuntimeException('Choose an available AI Ads plan.');
            $period=$productMatch[1];
            $pending=$this->db->where('store_id',$this->storeId())->where('product_code',$productCode)->where('status','pending')->where('expires_at >',date('Y-m-d H:i:s'))->order_by('id','desc')->get('db_ai_ads_billing')->row_array();
            if($pending&&!empty($pending['checkout_url'])) { redirect($pending['checkout_url']); return; }
            $identity=$this->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $central=rtrim((string)($identity['fleet_url']??''),'/'); $key=(string)($identity['install_key']??'');
            if(parse_url($central,PHP_URL_SCHEME)!=='https'||strlen($key)<32) throw new RuntimeException('This Fleet is not securely enrolled with Central.');
            if(!function_exists('curl_init')) throw new RuntimeException('PHP cURL is required for Central checkout.');
            $this->load->library('messaging_pricing'); $installUrl=rtrim(base_url(),'/').'/'; $requestKey=bin2hex(random_bytes(16));
            $payload=json_encode(array('store_id'=>$this->storeId(),'product_code'=>$productCode,'request_key'=>$requestKey),JSON_UNESCAPED_SLASHES);
            $timestamp=(string)time(); $envelope=array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$payload,
                'signature'=>Messaging_pricing::signature('ai_ads_subscription_checkout',$timestamp,$installUrl,$payload,$key));
            $ch=curl_init($central.'/marketing/ai_ads/broker_subscription_checkout');
            curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
            $raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
            $result=is_string($raw)?json_decode($raw,true):null;
            if($http!==200||!is_array($result)||empty($result['success'])||parse_url((string)($result['checkout_url']??''),PHP_URL_SCHEME)!=='https') throw new RuntimeException((string)($result['message']??'Central could not create the AI Ads checkout.'));
            $expires=date('Y-m-d H:i:s',time()+3600);
            $row=array('store_id'=>$this->storeId(),'product_code'=>$productCode,'plan_code'=>(string)($result['plan_code']??('ai_ads_'.$period)),'billing_period'=>$period,'status'=>'pending',
                'central_intent_id'=>$result['intent_id'],'invoice_id'=>$result['invoice_id'],'invoice_item_id'=>$result['invoice_item_id'],
                'invoice_number'=>$result['invoice_number'],'payment_reference'=>$result['payment_reference'],'checkout_url'=>$result['checkout_url'],
                'amount_kobo'=>(int)$result['amount_kobo'],'plan_limits_json'=>json_encode($this->normalizeAiAdsEntitlements($result['plan_limits']??array())),'expires_at'=>$expires);
            $this->db->insert('db_ai_ads_billing',$row);
            redirect($result['checkout_url']); return;
        } catch(Throwable $e) { log_message('error','AI Ads checkout start failed: '.mb_substr(strip_tags($e->getMessage()),0,250)); $this->session->set_flashdata('error','We couldn’t start checkout. Please try again later or contact MartPoint support. No payment has been taken.'); redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':'')); }
    }

    /** Paystack returns here; Central verifies payment and sends a signed fulfilment back. */
    public function billing_return() {
        if(!$this->requireAccess()) return;
        $reference=(string)$this->input->get('reference'); $storeId=(int)$this->input->get('store_id');
        try {
            if(!preg_match('/^MPADS_[a-f0-9]{40}$/D',$reference)||$storeId!==$this->storeId()||!$this->db->table_exists('db_ai_ads_billing')) throw new RuntimeException('This AI Ads payment return could not be matched to the signed-in store.');
            $billing=$this->db->where('store_id',$storeId)->where('payment_reference',$reference)->get('db_ai_ads_billing')->row_array();
            if(!$billing) throw new RuntimeException('AI Ads invoice was not found. Contact support before paying again.');
            $identity=$this->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $central=rtrim((string)($identity['fleet_url']??''),'/'); $key=(string)($identity['install_key']??'');
            $this->load->library('messaging_pricing'); $installUrl=rtrim(base_url(),'/').'/'; $request=json_encode(array('store_id'=>$storeId,'intent_id'=>$billing['central_intent_id'],'payment_reference'=>$reference),JSON_UNESCAPED_SLASHES); $timestamp=(string)time();
            $envelope=array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$request,'signature'=>Messaging_pricing::signature('ai_ads_subscription_verify',$timestamp,$installUrl,$request,$key));
            $ch=curl_init($central.'/marketing/ai_ads/broker_verify_subscription'); curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json'))); $raw=curl_exec($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $result=is_string($raw)?json_decode($raw,true):null;
            if($http!==200||!is_array($result)||empty($result['success'])) throw new RuntimeException('Payment verification is pending. Your access will activate after Central confirms the payment; do not pay again.');
            $this->session->set_flashdata('success',!empty($result['fulfillment'])?'Payment confirmed. Your AI Ads access is active.':'Payment confirmed. Your AI Ads access is being activated.');
        } catch(Throwable $e) { log_message('error','AI Ads payment return pending: '.mb_substr(strip_tags($e->getMessage()),0,220)); $this->session->set_flashdata('error','We couldn’t confirm this payment yet. Please wait a few minutes and refresh. Do not pay again; contact MartPoint support if access does not activate.'); }
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    public function connect() {
        if (!$this->requireAccess()) return;
        try {
            if (!$this->meta_ads_api->tokenEncryptionReady()) throw new RuntimeException('This Fleet could not reach Central to prepare its private Meta connection. Check its Central enrollment and try again.');
            if (!mp_is_central()) { $this->startBrokerFlow(); return; }
            if (!$this->meta_ads_api->oauthReady()) throw new RuntimeException('Meta OAuth is not configured on Central.');
            $state=bin2hex(random_bytes(32)); $this->session->set_userdata('ai_ads_oauth',array('state'=>$state,'store_id'=>$this->storeId(),'created'=>time(),'mobile'=>$this->isMobile()));
            redirect($this->meta_ads_api->authorizationUrl($state));
        } catch(Throwable $e) { log_message('error','AI Ads Meta connection could not start: '.mb_substr(strip_tags($e->getMessage()),0,220)); $this->session->set_flashdata('error','We couldn’t start the Meta connection. Please try again later or contact MartPoint support.'); redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':'')); }
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

    /** Fleet billing handoff: Central creates the Next invoice and initializes Paystack. */
    public function broker_subscription_offers() {
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
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32||!hash_equals(Messaging_pricing::signature('ai_ads_subscription_offers',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $request=json_decode($raw,true);
            if(!is_array($request)||(int)($request['store_id']??0)<1) throw new RuntimeException('Invalid plan request.');
            $this->load->library('central_billing_client'); $health=$this->central_billing_client->health();
            $offers=array();
            foreach(($health['ai_ads_offers']??array()) as $offerKey=>$offer) {
                if(!is_array($offer)||empty($offer['enabled'])||(int)($offer['total_kobo']??0)<1) continue;
                $productCode=(string)($offer['product_code']??$offerKey);
                if(in_array($productCode,array('monthly','annual'),true)) $productCode='ai-ads-'.$productCode;
                if(!preg_match('/^ai-ads(?:-[a-z][a-z0-9_]{1,31})?-(monthly|annual)$/D',$productCode,$productMatch)) continue;
                $period=(string)($offer['billing_period']??$productMatch[1]);
                $offers[] = array('enabled'=>true,'product_code'=>$productCode,'plan_code'=>(string)($offer['plan_code']??'ai_ads'),
                    'plan_name'=>(string)($offer['plan_name']??'AI Ads Starter'),'billing_period'=>$period,
                    'total_kobo'=>(int)$offer['total_kobo'],'tax_included'=>!empty($offer['tax_included']),
                    'entitlements'=>$this->normalizeAiAdsEntitlements($offer['entitlements']??array()));
            }
            if(empty($offers)) {
                if(empty($health['success'])) log_message('error','AI Ads offers unavailable: Central-to-Next health check failed: '.mb_substr(strip_tags((string)($health['message']??'No health details returned.')),0,160));
                else log_message('error','AI Ads offers unavailable: checkout='.(!empty($health['central_billing_enabled'])?'on':'off').', catalogue='.(!empty($health['catalogue_ready'])?'ready':'not-ready').', Paystack='.(!empty($health['paystack_ready'])?'ready':'not-ready').'.');
            }
            return $this->output->set_output(json_encode(array('success'=>true,'offers'=>$offers)));
        } catch(Throwable $e) {
            log_message('error','AI Ads offer lookup rejected: '.mb_substr(strip_tags($e->getMessage()),0,180));
            return $this->output->set_status_header(403)->set_output(json_encode(array('success'=>false,'offers'=>array())));
        }
    }

    /** Verify Fleet identity and paid entitlement before relaying creative generation to Next. */
    public function broker_ai_ads_media() {
        if(!mp_is_central()) show_404();
        @set_time_limit(250);
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $this->load->library('messaging_pricing');
            $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp');
            $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature');
            if(parse_url($installUrl,PHP_URL_SCHEME)!=='https'||rtrim($installUrl,'/').'/'!==$installUrl
                ||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>16000000
                ||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!$this->db->table_exists('db_fleet_installs')
                ||!$this->db->table_exists('db_central_ai_ads_subscriptions')) throw new RuntimeException('Fleet authentication failed.');
            $fleet=$this->db->where('install_url',$installUrl)->get('db_fleet_installs')->row_array();
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32
                ||!hash_equals(Messaging_pricing::signature('ai_ads_media_generation',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $request=json_decode($raw,true); $storeId=(int)($request['store_id']??0); $kind=(string)($request['kind']??'');
            if(!is_array($request)||$storeId<1||!in_array($kind,array('image','video'),true)||strlen(trim((string)($request['prompt']??'')))<8) throw new RuntimeException('Invalid creative request.');
            $subscription=$this->db->where('fleet_install_id',(int)$fleet['id'])->where('store_id',$storeId)->where('status','active')->where('starts_at <=',gmdate('Y-m-d H:i:s'))->where('expires_at >',gmdate('Y-m-d H:i:s'))->order_by('id','desc')->limit(1)->get('db_central_ai_ads_subscriptions')->row_array();
            if(!$subscription) throw new RuntimeException('An active AI Ads plan is required for creative generation.');
            $this->load->library('central_billing_client');
            $result=$this->central_billing_client->post('ai-ads/media',array('kind'=>$kind,'prompt'=>trim((string)$request['prompt']),
                'aspect_ratio'=>(string)($request['aspect_ratio']??'1:1'),'reference_media_base64'=>(string)($request['reference_media_base64']??''),
                'reference_media_mime'=>(string)($request['reference_media_mime']??''),'fleet_install_id'=>(int)$fleet['id'],'store_id'=>$storeId),240);
            return $this->output->set_output(json_encode($result,JSON_UNESCAPED_SLASHES));
        } catch(Throwable $e) {
            log_message('error','AI Ads creative broker failed: '.mb_substr(strip_tags($e->getMessage()),0,220));
            return $this->output->set_status_header(503)->set_output(json_encode(array('success'=>false,'message'=>'Creative generation is temporarily unavailable. Please try again later.')));
        }
    }

    public function broker_subscription_checkout() {
        if(!mp_is_central()) show_404();
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $this->load->library('messaging_pricing');
            $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp');
            $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature');
            if(parse_url($installUrl,PHP_URL_SCHEME)!=='https'||rtrim($installUrl,'/').'/'!==$installUrl
                ||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>2000
                ||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!$this->db->table_exists('db_fleet_installs')) throw new RuntimeException('Fleet authentication failed.');
            $fleet=$this->db->where('install_url',$installUrl)->get('db_fleet_installs')->row_array();
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32
                ||!hash_equals(Messaging_pricing::signature('ai_ads_subscription_checkout',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $payload=json_decode($raw,true); $storeId=(int)($payload['store_id']??0); $productCode=(string)($payload['product_code']??''); $requestKey=(string)($payload['request_key']??'');
            if(!is_array($payload)||$storeId<1||!preg_match('/^ai-ads(?:-[a-z][a-z0-9_]{1,31})?-(monthly|annual)$/D',$productCode,$productMatch)||!preg_match('/^[a-f0-9]{32}$/D',$requestKey)) throw new RuntimeException('Invalid AI Ads subscription request.');
            $period=$productMatch[1];
            $this->load->library('central_billing_client');
            $invoice=$this->central_billing_client->post('addon-invoices',array(
                'idempotency_key'=>'ai-ads:'.(int)$fleet['id'].':'.$storeId.':'.$requestKey,
                'fleet_install_id'=>(int)$fleet['id'],'store_id'=>$storeId,'install_url'=>$installUrl,
                'product_code'=>$productCode
            ));
            $planLimits=$this->normalizeAiAdsEntitlements($invoice['details']['entitlements']??array());
            $reference='MPADS_'.substr(hash('sha256','martpoint-ai-ads:'.$fleet['id'].':'.$storeId.':'.$requestKey),0,40);
            $checkout=$this->central_billing_client->post('payments/initialize',array('intent_id'=>$invoice['intent_id'],'provider_reference'=>$reference));
            return $this->output->set_output(json_encode(array('success'=>true,'intent_id'=>$invoice['intent_id'],'invoice_id'=>$invoice['invoice_id'],
                'invoice_item_id'=>$invoice['invoice_item_id'],'invoice_number'=>$invoice['invoice_number'],'payment_reference'=>$reference,
                'checkout_url'=>$checkout['checkout_url'],'amount_kobo'=>(int)$invoice['amount_kobo'],'billing_period'=>$period,
                'product_code'=>$productCode,'plan_code'=>(string)($invoice['addon_key']??'ai_ads').'_'.$period,'plan_name'=>(string)($invoice['name']??'AI Ads'),'plan_limits'=>$planLimits)));
        } catch(Throwable $e) {
            log_message('error','AI Ads Central checkout broker failed: '.mb_substr(strip_tags($e->getMessage()),0,250));
            return $this->output->set_status_header(503)->set_output(json_encode(array('success'=>false,'message'=>'Central could not create the AI Ads invoice or checkout. Check the Central-to-Next health status.')));
        }
    }

    /** Signed Fleet request to verify an AI Ads checkout through Next finance. */
    public function broker_verify_subscription() {
        if(!mp_is_central()) show_404();
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(strtoupper($this->input->method())!=='POST') throw new RuntimeException('POST is required.');
            $this->load->library('messaging_pricing'); $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp'); $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature');
            if(parse_url($installUrl,PHP_URL_SCHEME)!=='https'||rtrim($installUrl,'/').'/'!==$installUrl||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>2000||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!$this->db->table_exists('db_fleet_installs')) throw new RuntimeException('Fleet authentication failed.');
            $fleet=$this->db->where('install_url',$installUrl)->get('db_fleet_installs')->row_array();
            if(!$fleet||strlen((string)($fleet['install_key']??''))<32||!hash_equals(Messaging_pricing::signature('ai_ads_subscription_verify',$timestamp,$installUrl,$raw,$fleet['install_key']),$signature)) throw new RuntimeException('Fleet authentication failed.');
            $p=json_decode($raw,true); if(!is_array($p)||(int)($p['store_id']??0)<1||!preg_match('/^[0-9a-f-]{36}$/i',(string)($p['intent_id']??''))||!preg_match('/^MPADS_[a-f0-9]{40}$/D',(string)($p['payment_reference']??''))) throw new RuntimeException('Invalid payment verification request.');
            $this->load->library('central_billing_client');
            $result=$this->central_billing_client->post('payments/verify',array('intent_id'=>$p['intent_id'],'provider_reference'=>$p['payment_reference'],'fleet_install_id'=>(int)$fleet['id'],'store_id'=>(int)$p['store_id']));
            return $this->output->set_output(json_encode(array('success'=>true,'fulfillment'=>($result['fulfillment']??'')==='acknowledged')));
        } catch(Throwable $e) { log_message('error','AI Ads payment verification broker failed: '.mb_substr(strip_tags($e->getMessage()),0,240)); return $this->output->set_status_header(503)->set_output(json_encode(array('success'=>false,'message'=>'Central has not confirmed this payment yet.'))); }
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

    /** Verified Next invoice delivery, relayed by Central using the Fleet install key. */
    public function broker_apply_subscription() {
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store'); $started=false;
        try {
            if(mp_is_central()||strtoupper($this->input->method())!=='POST') throw new RuntimeException('Invalid callback.');
            $this->load->library('messaging_pricing'); $identity=$this->db->select('install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $key=(string)($identity['install_key']??''); $installUrl=(string)$this->input->post('install_url'); $timestamp=(string)$this->input->post('timestamp'); $raw=(string)$this->input->post('payload'); $signature=(string)$this->input->post('signature'); $expected=rtrim(base_url(),'/').'/';
            if(strlen($key)<32||$installUrl!==$expected||!ctype_digit($timestamp)||abs(time()-(int)$timestamp)>300||strlen($raw)>12000||!preg_match('/^[a-f0-9]{64}$/D',$signature)||!hash_equals(Messaging_pricing::signature('ai_ads_subscription_fulfillment',$timestamp,$expected,$raw,$key),$signature)) throw new RuntimeException('Central authentication failed.');
            $p=json_decode($raw,true);
            if(!is_array($p)||($p['product_type']??'')!=='ADDON'||($p['fulfillment_type']??'')!=='AI_ADS_SUBSCRIPTION'||!preg_match('/^ai_ads(?:_[a-z][a-z0-9_]{1,31})?$/D',(string)($p['addon_key']??''))||!in_array($p['billing_period']??'',array('monthly','annual'),true)||(int)($p['store_id']??0)<1||!preg_match('/^[0-9a-f-]{36}$/i',(string)($p['event_id']??''))||!preg_match('/^[0-9a-f-]{36}$/i',(string)($p['intent_id']??''))||!preg_match('/^[0-9a-f-]{36}$/i',(string)($p['invoice_id']??''))||!preg_match('/^[0-9a-f-]{36}$/i',(string)($p['invoice_item_id']??''))||!preg_match('/^MPADS_[a-f0-9]{40}$/D',(string)($p['payment_reference']??''))||!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D',(string)($p['starts_at']??''))||!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D',(string)($p['expires_at']??''))||strtotime($p['expires_at'].' UTC')<=strtotime($p['starts_at'].' UTC')||!$this->db->table_exists('db_ai_ads_billing')||!$this->db->table_exists('db_ai_ads_fulfillments')) throw new RuntimeException('Invalid AI Ads fulfilment.');
            $this->db->trans_begin(); $started=true;
            $existing=$this->db->where('invoice_item_id',$p['invoice_item_id'])->get('db_ai_ads_fulfillments')->row_array();
            if($existing) { $this->db->trans_commit(); $started=false; return $this->output->set_output(json_encode(array('success'=>true,'applied'=>false))); }
            $billing=$this->db->query('SELECT * FROM db_ai_ads_billing WHERE store_id=? AND central_intent_id=? AND invoice_id=? AND invoice_item_id=? AND billing_period=? FOR UPDATE',array((int)$p['store_id'],$p['intent_id'],$p['invoice_id'],$p['invoice_item_id'],$p['billing_period']))->row_array();
            $expectedPlanCode=(string)$p['addon_key'].'_'.$p['billing_period'];
            $tierSuffix=$p['addon_key']==='ai_ads'?'':substr((string)$p['addon_key'],7).'-';
            $expectedProductCode='ai-ads-'.$tierSuffix.$p['billing_period'];
            if(!$billing||$billing['plan_code']!==$expectedPlanCode||$billing['product_code']!==$expectedProductCode||!in_array($billing['status'],array('pending','paid'),true)) throw new RuntimeException('AI Ads payment is not linked to this Fleet store and plan.');
            $expiry=(string)$p['expires_at'];
            $planLimits=$this->normalizeAiAdsEntitlements(json_decode((string)($billing['plan_limits_json']??''),true));
            $this->db->insert('db_ai_ads_subscriptions',array('store_id'=>(int)$p['store_id'],'plan_code'=>(string)$billing['plan_code'],'status'=>'active','starts_at'=>$p['starts_at'],'expires_at'=>$expiry,'grace_days'=>3,'payment_provider'=>'paystack','payment_reference'=>$billing['payment_reference'],'months_paid'=>$p['billing_period']==='annual'?12:1,'plan_limits_json'=>json_encode($planLimits)));
            $this->db->insert('db_ai_ads_fulfillments',array('invoice_item_id'=>$p['invoice_item_id'],'event_id'=>$p['event_id'],'store_id'=>(int)$p['store_id'],'created_at'=>gmdate('Y-m-d H:i:s')));
            $this->db->where('id',$billing['id'])->update('db_ai_ads_billing',array('status'=>'paid','updated_at'=>gmdate('Y-m-d H:i:s')));
            $this->ads->audit((int)$p['store_id'],null,0,'subscription_activated','Verified Next Admin invoice activated '.$p['billing_period'].' AI Ads access through '.$expiry.'.');
            if($this->db->trans_status()===false) throw new RuntimeException('AI Ads subscription activation failed.');
            $this->db->trans_commit(); $started=false; return $this->output->set_output(json_encode(array('success'=>true,'applied'=>true,'expires_at'=>$expiry)));
        } catch(Throwable $e) { if($started)$this->db->trans_rollback(); log_message('error','AI Ads fulfilment rejected: '.mb_substr(strip_tags($e->getMessage()),0,250)); return $this->output->set_status_header(503)->set_output(json_encode(array('success'=>false))); }
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
            if ($oauthError!=='') throw new RuntimeException('Meta authorization was not completed. Please try connecting again.');
            if (!$saved || empty($saved['created']) || time()-(int)$saved['created']>600 || empty($saved['store_id']) || !hash_equals((string)$saved['state'],$state) || $code==='') throw new RuntimeException('Meta connection expired or was cancelled. Please connect again.');
            $token=$this->meta_ads_api->exchangeCode($code); $profile=$this->meta_ads_api->profile($token['access_token']);
            $expires=$token['expires_in']>0?date('Y-m-d H:i:s',time()+$token['expires_in']):null;
            if (empty($profile['client_business_id'])) throw new RuntimeException('Meta did not return a business portfolio. Check your Meta account access and try again.');
            $row=array('store_id'=>(int)$saved['store_id'],'meta_user_id'=>(string)($profile['id']??''),'meta_business_id'=>(string)$profile['client_business_id'],'access_token_ciphertext'=>$this->meta_ads_api->encryptToken($token['access_token']),'token_expires_at'=>$expires,'status'=>'connected','last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null);
            $existing=$this->ads->connection((int)$saved['store_id']);
            if ($existing) $this->db->where('store_id',(int)$saved['store_id'])->update('db_ai_ads_connections',$row); else $this->db->insert('db_ai_ads_connections',$row);
            $this->ads->audit((int)$saved['store_id'],null,$this->actorId(),'meta_connected','Merchant authorized a Meta connection.');
            $this->session->set_flashdata('success','Meta is connected. Choose the advertising account and Page you want MartPoint to use.');
        } catch(Throwable $e) {
            log_message('error','AI Ads Meta callback failed: '.mb_substr(strip_tags($e->getMessage()),0,220));
            $this->session->set_flashdata('error','We couldn’t complete the Meta connection. Please try again or contact MartPoint support.');
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
            $accountId=(string)$this->input->post('ad_account_id'); $pageId=(string)$this->input->post('page_id'); $pixelId=(string)$this->input->post('pixel_id');
            $accounts=$this->meta_ads_api->accounts($token)['data']??array(); $pages=$this->meta_ads_api->pages($token)['data']??array();
            $account=null; foreach($accounts as $a) if((string)($a['id']??'')===$accountId) $account=$a;
            $page=null; foreach($pages as $p) if((string)($p['id']??'')===$pageId) $page=$p;
            if (!$account || !$page) throw new RuntimeException('Select an ad account and Page returned by Meta.');
            if($pixelId!=='') {
                $pixels=$this->meta_ads_api->pixels($token,$accountId)['data']??array(); $pixelOk=false;
                foreach($pixels as $pixel)if((string)($pixel['id']??'')===$pixelId)$pixelOk=true;
                if(!$pixelOk)throw new RuntimeException('Choose a conversion data source assigned to this ad account.');
            }
            $currency=strtoupper((string)($account['currency']??''));
            if($currency!==''&&!preg_match('/^[A-Z]{3}$/D',$currency))$currency='';
            $this->db->where('store_id',$storeId)->update('db_ai_ads_connections',array('ad_account_id'=>$accountId,'ad_account_name'=>mb_substr((string)($account['name']??$accountId),0,190),'ad_account_currency'=>$currency?:null,'page_id'=>$pageId,'page_name'=>mb_substr((string)($page['name']??$pageId),0,190),'pixel_id'=>$pixelId?:null,'last_checked_at'=>date('Y-m-d H:i:s'),'last_error'=>null));
            $this->ads->audit($storeId,null,$this->actorId(),'assets_selected','Merchant selected an ad account and Page.');
            $this->session->set_flashdata('success','Meta assets saved.');
        } catch(Throwable $e) { log_message('error','AI Ads Meta asset selection failed: '.mb_substr(strip_tags($e->getMessage()),0,220)); $this->session->set_flashdata('error','We couldn’t save those Meta assets. Please choose an account and Page available to your Facebook account.'); }
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
            $url=trim((string)$this->input->post('destination_url')); $destinationType=strtoupper((string)$this->input->post('destination_type'));
            if(!in_array($destinationType,array('WEBSITE','WHATSAPP'),true))$destinationType=in_array(strtolower((string)parse_url($url,PHP_URL_HOST)),array('wa.me','api.whatsapp.com'),true)?'WHATSAPP':'WEBSITE';
            if($url!=='' && (!filter_var($url,FILTER_VALIDATE_URL)||parse_url($url,PHP_URL_SCHEME)!=='https')) throw new RuntimeException('The destination must be a secure HTTPS link.');
            if($destinationType==='WHATSAPP'&&$url!==''&&!in_array(strtolower((string)parse_url($url,PHP_URL_HOST)),array('wa.me','api.whatsapp.com'),true))throw new RuntimeException('Use a WhatsApp link such as https://wa.me/2348000000000.');
            $conversionEvent=strtoupper((string)$this->input->post('conversion_event'));
            if(!in_array($conversionEvent,array('PURCHASE','ADD_TO_CART','INITIATED_CHECKOUT','LEAD','VIEW_CONTENT'),true))$conversionEvent='PURCHASE';
            $connection=$this->ads->connection($this->storeId());
            $budget=(int)$this->input->post('daily_budget'); if($budget<0 || $budget>100000000) throw new RuntimeException('Enter a valid daily budget in naira.');
            $duration=(int)$this->input->post('duration_days'); if($duration<1||$duration>365) throw new RuntimeException('Campaign duration must be between 1 and 365 days.');
            $key=(string)$this->input->post('request_key'); if(!preg_match('/^[a-f0-9]{32,64}$/D',$key)) throw new RuntimeException('Refresh the page and try again.');
            $now=new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos'));
            $mediaId=(int)$this->input->post('media_asset_id');
            if($mediaId>0&&!$this->db->where('id',$mediaId)->where('store_id',$this->storeId())->get('db_ai_ads_media_assets')->row_array()) throw new RuntimeException('Choose media from this store.');
            $id=$this->ads->saveDraft($this->storeId(),$this->actorId(),array('request_key'=>$key,'prompt'=>$prompt,'product_id'=>$productId,'media_asset_id'=>$mediaId,'headline'=>$headline,'body'=>$body,'destination_url'=>$url,'destination_type'=>$destinationType,'pixel_id'=>$destinationType==='WEBSITE'?($connection['pixel_id']??null):null,'conversion_event'=>$conversionEvent,'location_text'=>mb_substr(trim((string)$this->input->post('location_text')),0,190),'daily_budget_minor'=>$budget*100,'starts_at'=>$now->format('Y-m-d H:i:s'),'ends_at'=>$now->modify('+'.$duration.' days')->format('Y-m-d H:i:s')));
            $this->ads->audit($this->storeId(),$id,$this->actorId(),'campaign_draft_saved','Draft saved for merchant review.');
            $this->session->set_flashdata('success','Ad draft saved. This is a draft only; MartPoint has not created or started an ad on Meta.');
        } catch(Throwable $e) { log_message('error','AI Ads draft save failed: '.mb_substr(strip_tags($e->getMessage()),0,220)); $this->session->set_flashdata('error','We couldn’t save your ad draft. Check the required fields and try again.'); }
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    /** Publish only after the owner confirms the exact lifetime spend cap. */
    public function publish_campaign() {
        if(!$this->requireAccess())return;
        if(!$this->canApproveMetaSpend()){show_error('A store owner or administrator must approve Meta ad spend.',403);return;}
        if(strtoupper($this->input->method())!=='POST'){show_error('POST is required.',405);return;}
        $storeId=$this->storeId(); $campaignId=(int)$this->input->post('campaign_id'); $campaign=$this->ads->campaign($storeId,$campaignId);
        $temporaryImagePath='';
        try {
            if(!$campaign||$campaign['status']!=='draft')throw new RuntimeException('This draft is not available to publish.');
            if(!$this->ads->hasAccess($storeId))throw new RuntimeException('An active AI Ads plan is required to publish a campaign.');
            if((string)$this->input->post('approve_spend')!=='1')throw new RuntimeException('Confirm the budget and publishing action first.');
            $connection=$this->ads->connection($storeId);
            if(!$connection||$connection['status']!=='connected'||empty($connection['ad_account_id'])||empty($connection['page_id'])||empty($connection['access_token_ciphertext']))throw new RuntimeException('Connect Meta and select an advertising account and Page before publishing.');
            if(strtoupper((string)($connection['ad_account_currency']??''))!=='NGN')throw new RuntimeException('The selected ad account must use NGN so the budget is shown and confirmed in naira.');
            $token=$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']);
            $grants=$this->meta_ads_api->permissions($token)['data']??array(); $granted=array();
            foreach($grants as $grant)if(($grant['status']??'')==='granted')$granted[(string)($grant['permission']??'')]=true;
            if(empty($granted['ads_management']))throw new RuntimeException('Meta has not granted this connection permission to manage ads. Reconnect Meta and grant advertising access.');
            if(empty($granted['ads_read']))throw new RuntimeException('Meta has not granted reporting access. Reconnect Meta and grant ads_read so you can see campaign results in MartPoint.');
            $accountState=$this->meta_ads_api->account($token,$connection['ad_account_id']);
            if((int)($accountState['account_status']??0)!==1||strtoupper((string)($accountState['currency']??''))!=='NGN')throw new RuntimeException('The selected Meta advertising account is inactive or its currency is not NGN.');
            $pageList=$this->meta_ads_api->pages($token)['data']??array(); $pageValid=false;
            foreach($pageList as $page)if((string)($page['id']??'')===(string)$connection['page_id'])$pageValid=true;
            if(!$pageValid)throw new RuntimeException('The selected Facebook Page is no longer available to this connection.');
            $url=trim((string)$campaign['destination_url']); $destination=(string)($campaign['destination_type']??'WEBSITE');
            if($url===''||!filter_var($url,FILTER_VALIDATE_URL)||parse_url($url,PHP_URL_SCHEME)!=='https')throw new RuntimeException('Add a secure destination link to the draft before publishing.');
            if($destination==='WHATSAPP'&&!in_array(strtolower((string)parse_url($url,PHP_URL_HOST)),array('wa.me','api.whatsapp.com'),true))throw new RuntimeException('Use a WhatsApp link such as https://wa.me/2348000000000.');
            if(!in_array($destination,array('WEBSITE','WHATSAPP'),true))throw new RuntimeException('Choose a website or WhatsApp destination.');
            $daily=(int)$campaign['daily_budget_minor'];
            $startLocal=new DateTimeImmutable((string)$campaign['starts_at'],new DateTimeZone('Africa/Lagos'));
            $endLocal=new DateTimeImmutable((string)$campaign['ends_at'],new DateTimeZone('Africa/Lagos'));
            $duration=max(1,min(365,(int)ceil(($endLocal->getTimestamp()-$startLocal->getTimestamp())/86400)));
            $total=$daily*$duration;
            if($daily<50000||$total<50000||$total>3650000000000)throw new RuntimeException('Set a valid daily budget and campaign duration before publishing.');
            if((int)$this->input->post('confirmed_total_minor')!==$total)throw new RuntimeException('The approved total does not match this draft. Review the budget again.');
            $assetId=(int)($campaign['media_asset_id']??0); $asset=$assetId?$this->db->where('id',$assetId)->where('store_id',$storeId)->get('db_ai_ads_media_assets')->row_array():null;
            if(!$asset||!in_array($asset['mime_type'],array('image/jpeg','image/png','image/webp','video/mp4'),true))throw new RuntimeException('Attach a supported photo or MP4 video to the draft before publishing.');
            $assetPath=realpath(FCPATH.$asset['relative_path']); $assetRoot=realpath(FCPATH.'uploads/ai_ads/'.$storeId);
            if(!$assetPath||!$assetRoot||strpos($assetPath,$assetRoot.DIRECTORY_SEPARATOR)!==0||!is_file($assetPath))throw new RuntimeException('The selected creative is no longer available.');
            if($asset['mime_type']==='image/webp') {
                if(!function_exists('imagecreatefromwebp')||!function_exists('imagejpeg'))throw new RuntimeException('This server cannot convert WebP for Meta. Re-upload the creative as JPG or PNG.');
                $webp=@imagecreatefromwebp($assetPath); if(!$webp)throw new RuntimeException('The WebP image could not be converted. Re-upload it as JPG or PNG.');
                $temporaryImagePath=tempnam(sys_get_temp_dir(),'mp_aiads_');
                if(!is_string($temporaryImagePath)){imagedestroy($webp);throw new RuntimeException('Temporary image storage is unavailable.');}
                $converted=@imagejpeg($webp,$temporaryImagePath,90);
                imagedestroy($webp);
                if(!$converted)throw new RuntimeException('The WebP image could not be converted. Re-upload it as JPG or PNG.');
                $assetPath=$temporaryImagePath; $asset['mime_type']='image/jpeg';
            }
            if($destination==='WEBSITE') {
                $pixelId=(string)($campaign['pixel_id']??$connection['pixel_id']??'');
                if($pixelId==='')throw new RuntimeException('Select a Meta Pixel for website sales campaigns.');
                $pixelList=$this->meta_ads_api->pixels($token,$connection['ad_account_id'])['data']??array(); $pixelValid=false;
                foreach($pixelList as $pixel)if((string)($pixel['id']??'')===$pixelId)$pixelValid=true;
                if(!$pixelValid)throw new RuntimeException('The selected Meta Pixel is no longer assigned to this ad account.');
            } else { $pixelId=''; }
            $targeting=array('geo_locations'=>array('countries'=>array('NG')),'age_min'=>18);
            $location=trim((string)($campaign['location_text']??''));
            if($location!=='') {
                $found=$this->meta_ads_api->findLocation($token,$location)['data']??array();
                if(!$found)throw new RuntimeException('Meta could not match that location. Use a city or leave location blank to target Nigeria.');
                $match=$found[0]; $geo=array('key'=>(string)($match['key']??''));
                if($geo['key']==='')throw new RuntimeException('Meta could not match that location. Use a city or leave location blank to target Nigeria.');
                if(($match['type']??'city')==='region')$targeting['geo_locations']=array('regions'=>array($geo));
                else $targeting['geo_locations']=array('cities'=>array($geo+array('radius'=>10,'distance_unit'=>'kilometer')));
            }
            if(!$this->ads->claimCampaignForPublish($storeId,$campaignId))throw new RuntimeException('This draft is already being published or has changed. Reload AI Ads and try again.');
            $this->ads->updateCampaign($storeId,$campaignId,array('confirmed_total_minor'=>$total,'status'=>'publishing','last_error'=>null));
            $campaignName=mb_substr(preg_replace('/[^\pL\pN _-]+/u','',(string)$campaign['headline']),0,70).' · MartPoint';
            $objective=$destination==='WEBSITE'?'OUTCOME_SALES':'OUTCOME_TRAFFIC';
            $event=strtoupper((string)($campaign['conversion_event']??'PURCHASE'));
            $cta=$destination==='WHATSAPP'?'WHATSAPP_MESSAGE':($event==='LEAD'?'SIGN_UP':(in_array($event,array('VIEW_CONTENT','ADD_TO_CART'),true)?'LEARN_MORE':'SHOP_NOW'));

            // All Meta entities are created paused. The owner’s confirmed request
            // below activates the ad last, after every component exists.
            $created=$this->meta_ads_api->createObject($token,'act_'.preg_replace('/[^0-9]/','',(string)$connection['ad_account_id']).'/campaigns',array(
                'name'=>$campaignName,'objective'=>$objective,'status'=>'PAUSED','buying_type'=>'AUCTION','special_ad_categories'=>json_encode(array())));
            $campaignMetaId=(string)($created['id']??''); if($campaignMetaId==='')throw new RuntimeException('Meta did not confirm campaign creation.');
            $this->ads->updateCampaign($storeId,$campaignId,array('meta_campaign_id'=>$campaignMetaId));
            $adsetFields=array('name'=>$campaignName.' ad set','campaign_id'=>$campaignMetaId,'status'=>'PAUSED','billing_event'=>'IMPRESSIONS',
                'optimization_goal'=>$destination==='WEBSITE'?'OFFSITE_CONVERSIONS':'LINK_CLICKS','destination_type'=>$destination,
                'lifetime_budget'=>(string)$total,'start_time'=>gmdate('c',time()+600),'end_time'=>gmdate('c',time()+600+($duration*86400)),
                'targeting'=>json_encode($targeting,JSON_UNESCAPED_SLASHES));
            $adsetFields['promoted_object']=json_encode($destination==='WEBSITE'?array('pixel_id'=>$pixelId,'custom_event_type'=>$event):array('page_id'=>(string)$connection['page_id']),JSON_UNESCAPED_SLASHES);
            $adset=$this->meta_ads_api->createObject($token,'act_'.preg_replace('/[^0-9]/','',(string)$connection['ad_account_id']).'/adsets',$adsetFields);
            $adsetId=(string)($adset['id']??''); if($adsetId==='')throw new RuntimeException('Meta did not confirm ad set creation.');
            $this->ads->updateCampaign($storeId,$campaignId,array('meta_adset_id'=>$adsetId));
            if($asset['mime_type']==='video/mp4') {
                $uploaded=$this->meta_ads_api->uploadVideo($token,$connection['ad_account_id'],$assetPath); $videoId=(string)($uploaded['id']??'');
                if($videoId==='')throw new RuntimeException('Meta did not confirm video upload.');
                $story=array('page_id'=>(string)$connection['page_id'],'video_data'=>array('video_id'=>$videoId,'message'=>(string)$campaign['body'],'title'=>(string)$campaign['headline'],'link_description'=>(string)$campaign['headline'],'call_to_action'=>array('type'=>$cta,'value'=>array('link'=>$url))));
            } else {
                $image=$this->meta_ads_api->uploadImage($token,$connection['ad_account_id'],$assetPath,(string)$asset['mime_type']);
                $imageRows=$image['images']??array(); $imageData=is_array($imageRows)?reset($imageRows):false; $imageHash=is_array($imageData)?(string)($imageData['hash']??''):'';
                if($imageHash==='')throw new RuntimeException('Meta did not confirm image upload.');
                $story=array('page_id'=>(string)$connection['page_id'],'link_data'=>array('image_hash'=>$imageHash,'link'=>$url,'message'=>(string)$campaign['body'],'name'=>(string)$campaign['headline'],'description'=>(string)$campaign['headline'],'call_to_action'=>array('type'=>$cta,'value'=>array('link'=>$url))));
            }
            $creative=$this->meta_ads_api->createObject($token,'act_'.preg_replace('/[^0-9]/','',(string)$connection['ad_account_id']).'/adcreatives',array('name'=>$campaignName.' creative','object_story_spec'=>json_encode($story,JSON_UNESCAPED_SLASHES)));
            $creativeId=(string)($creative['id']??''); if($creativeId==='')throw new RuntimeException('Meta did not confirm ad creative creation.');
            $ad=$this->meta_ads_api->createObject($token,'act_'.preg_replace('/[^0-9]/','',(string)$connection['ad_account_id']).'/ads',array('name'=>$campaignName.' ad','adset_id'=>$adsetId,'creative'=>json_encode(array('creative_id'=>$creativeId)),'status'=>'PAUSED'));
            $adId=(string)($ad['id']??''); if($adId==='')throw new RuntimeException('Meta did not confirm ad creation.');
            $this->ads->updateCampaign($storeId,$campaignId,array('meta_ad_id'=>$adId));
            $this->meta_ads_api->updateObject($token,$campaignMetaId,array('status'=>'ACTIVE'));
            $this->meta_ads_api->updateObject($token,$adsetId,array('status'=>'ACTIVE'));
            $this->meta_ads_api->updateObject($token,$adId,array('status'=>'ACTIVE'));
            $this->ads->updateCampaign($storeId,$campaignId,array('status'=>'active','launched_at'=>date('Y-m-d H:i:s'),'confirmed_total_minor'=>$total,'last_error'=>null));
            $this->ads->audit($storeId,$campaignId,$this->actorId(),'campaign_published','Owner approved the displayed lifetime spend cap and published the Meta campaign.');
            $this->session->set_flashdata('success','Your ad was published to Meta. Meta will bill ad spend to the selected advertising account.');
        } catch(Throwable $e) {
            if($campaign&&$campaign['status']==='draft')$this->ads->updateCampaign($storeId,$campaignId,array('last_error'=>mb_substr(strip_tags($e->getMessage()),0,500)));
            $fresh=$this->ads->campaign($storeId,$campaignId);
            if($fresh&&$fresh['status']==='publishing') {
                try { $token=!empty($connection['access_token_ciphertext'])?$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']):''; if($token!=='')foreach(array('meta_ad_id','meta_adset_id','meta_campaign_id') as $field)if(!empty($fresh[$field]))$this->meta_ads_api->updateObject($token,$fresh[$field],array('status'=>'PAUSED')); } catch(Throwable $ignored) {}
                $this->ads->updateCampaign($storeId,$campaignId,array('status'=>'needs_review','last_error'=>mb_substr(strip_tags($e->getMessage()),0,500)));
            }
            log_message('error','AI Ads Meta publish failed: '.mb_substr(strip_tags($e->getMessage()),0,220));
            if($fresh&&$fresh['status']==='needs_review') $userMessage='Meta did not confirm the full launch. The campaign is held for review; contact MartPoint support before retrying.';
            elseif(strpos($e->getMessage(),'ads_read')!==false) $userMessage='Reconnect Meta and grant reporting access so MartPoint can show your ad results.';
            elseif(strpos($e->getMessage(),'ads_management')!==false) $userMessage='Reconnect Meta and grant permission to manage ads before publishing.';
            elseif(strpos($e->getMessage(),'Pixel')!==false||strpos($e->getMessage(),'pixel')!==false) $userMessage='Select a Meta Pixel assigned to this ad account before publishing a website Sales ad.';
            else $userMessage='We couldn’t publish this draft. Check the campaign details and Meta account, then try again.';
            $this->session->set_flashdata('error',$userMessage);
        }
        if($temporaryImagePath!==''&&is_file($temporaryImagePath))@unlink($temporaryImagePath);
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    public function pause_campaign() {
        if(!$this->requireAccess())return;
        if(strtoupper($this->input->method())!=='POST'){show_error('POST is required.',405);return;}
        $storeId=$this->storeId(); $campaignId=(int)$this->input->post('campaign_id'); $campaign=$this->ads->campaign($storeId,$campaignId);
        try {
            if(!$campaign||$campaign['status']!=='active'||empty($campaign['meta_campaign_id']))throw new RuntimeException('This campaign is not active.');
            $connection=$this->ads->connection($storeId); if(!$connection||empty($connection['access_token_ciphertext']))throw new RuntimeException('Reconnect Meta to pause this campaign.');
            $token=$this->meta_ads_api->decryptToken($connection['access_token_ciphertext']);
            $this->meta_ads_api->updateObject($token,$campaign['meta_campaign_id'],array('status'=>'PAUSED'));
            $this->ads->updateCampaign($storeId,$campaignId,array('status'=>'paused','last_error'=>null));
            $this->ads->audit($storeId,$campaignId,$this->actorId(),'campaign_paused','Owner paused the Meta campaign from MartPoint.');
            $this->session->set_flashdata('success','The Meta campaign is paused.');
        } catch(Throwable $e) { log_message('error','AI Ads Meta pause failed: '.mb_substr(strip_tags($e->getMessage()),0,220)); $this->session->set_flashdata('error','We couldn’t confirm that the Meta campaign was paused. Check its status in Meta Ads Manager.'); }
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    /** Upload a merchant image/video to the store-scoped private media library. */
    public function upload_media() {
        if(!$this->requireAccess())return;
        if(strtoupper($this->input->method())!=='POST'){show_error('POST is required.',405);return;}
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        try {
            if(!$this->ads->hasAccess($this->storeId()))throw new RuntimeException('An active AI Ads plan is required to add campaign media.');
            if(empty($_FILES['media_file'])||!is_uploaded_file($_FILES['media_file']['tmp_name']))throw new RuntimeException('Choose a photo or video to upload.');
            $file=$_FILES['media_file']; if((int)$file['error']!==UPLOAD_ERR_OK)throw new RuntimeException('The media file could not be uploaded.');
            if((int)$file['size']<1||(int)$file['size']>25*1024*1024)throw new RuntimeException('Each file must be 25 MB or smaller.');
            $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
            $allowed=array('image/jpeg'=>'image','image/png'=>'image','image/webp'=>'image','video/mp4'=>'video');
            if(!isset($allowed[$mime]))throw new RuntimeException('Use a JPG, PNG, WebP photo or MP4 video so the file can be reviewed and sent to Meta.');
            $store=$this->storeId(); $dir=FCPATH.'uploads/ai_ads/'.$store;
            if(!is_dir($dir)&&!@mkdir($dir,0750,true)&&!is_dir($dir))throw new RuntimeException('Campaign media storage is unavailable.');
            $name='upload_'.bin2hex(random_bytes(16)).'.'.strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
            if(!preg_match('/^[a-z0-9]{1,8}$/',$name===basename($name)?strtolower(pathinfo($name,PATHINFO_EXTENSION)):''))throw new RuntimeException('Invalid media file extension.');
            $relative='uploads/ai_ads/'.$store.'/'.$name; $absolute=FCPATH.$relative;
            if(!move_uploaded_file($file['tmp_name'],$absolute))throw new RuntimeException('The media file could not be saved.');
            try {$id=$this->ads->saveMediaAsset($store,$this->actorId(),array('asset_type'=>$allowed[$mime],'source_type'=>'upload','file_name'=>mb_substr(basename((string)$file['name']),0,190),'relative_path'=>$relative,'mime_type'=>$mime,'size_bytes'=>(int)$file['size']));}
            catch(Throwable $e){@unlink($absolute);throw $e;}
            $this->ads->audit($store,null,$this->actorId(),'media_uploaded','Merchant added campaign media.');
            return $this->output->set_output(json_encode(array('success'=>true,'asset'=>array('id'=>$id,'name'=>basename((string)$file['name']),'type'=>$allowed[$mime],'mime_type'=>$mime,'size_bytes'=>(int)$file['size']))));
        } catch(Throwable $e) { $message=$e->getMessage(); return $this->output->set_status_header(422)->set_output(json_encode(array('success'=>false,'message'=>mb_substr(strip_tags($message),0,200)))); }
    }

    /** Generate one paid AI image/video through the signed Central → Next route. */
    public function generate_media() {
        if(!$this->requireAccess())return;
        if(strtoupper($this->input->method())!=='POST'){show_error('POST is required.',405);return;}
        @set_time_limit(250);
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store'); $reservation=array();
        try {
            if(!$this->ads->hasAccess($this->storeId()))throw new RuntimeException('An active AI Ads plan is required to create campaign media.');
            if(!function_exists('curl_init'))throw new RuntimeException('Central creative service is unavailable.');
            $this->load->library('messaging_pricing');
            $kind=(string)$this->input->post('kind'); if(!in_array($kind,array('image','video'),true))throw new RuntimeException('Choose an image or video.');
            $prompt=trim((string)$this->input->post('prompt')); if(strlen($prompt)<8||strlen($prompt)>3000)throw new RuntimeException('Describe the creative in 8 to 3,000 characters.');
            $referenceId=(int)$this->input->post('reference_asset_id'); $reference=''; $referenceMime=''; $usageKind=$kind;
            if($referenceId>0) {
                if($kind!=='video')throw new RuntimeException('Reference media can currently be used to create a video.');
                $asset=$this->db->where('id',$referenceId)->where('store_id',$this->storeId())->get('db_ai_ads_media_assets')->row_array();
                if(!$asset||!in_array($asset['mime_type'],array('image/jpeg','image/png','image/webp','video/mp4'),true))throw new RuntimeException('Choose a JPG, PNG, WebP photo or MP4 clip from this store.');
                $bytes=@file_get_contents(FCPATH.$asset['relative_path']); if(!is_string($bytes)||strlen($bytes)>4*1024*1024)throw new RuntimeException('Choose a reference photo or MP4 clip that is 4 MB or smaller.');
                $reference=base64_encode($bytes); $referenceMime=(string)$asset['mime_type']; $usageKind='video_export';
            }
            $reservation=$this->ads->reserveCreativeGeneration($this->storeId(),$usageKind);
            if(empty($reservation['ok'])) {
                if(($reservation['reason']??'')==='limit')return $this->output->set_status_header(429)->set_output(json_encode(array('success'=>false,'message'=>'You’ve used the included '.($kind==='image'?'image':'video').' allowance for this plan month. It resets at the start of your next plan month.')));
                throw new RuntimeException('Your plan allowance could not be checked. Please try again shortly.');
            }
            $identity=$this->db->select('fleet_url,install_key')->where('id',1)->get('db_sitesettings')->row_array()?:array();
            $central=rtrim((string)($identity['fleet_url']??''),'/'); $key=(string)($identity['install_key']??'');
            if(parse_url($central,PHP_URL_SCHEME)!=='https'||strlen($key)<32)throw new RuntimeException('This Fleet is not securely enrolled with Central.');
            $payload=json_encode(array('store_id'=>$this->storeId(),'kind'=>$kind,'prompt'=>$prompt,'aspect_ratio'=>(string)$this->input->post('aspect_ratio'),
                'reference_media_base64'=>$reference,'reference_media_mime'=>$referenceMime),JSON_UNESCAPED_SLASHES);
            $installUrl=rtrim(base_url(),'/').'/'; $timestamp=(string)time();
            $envelope=array('install_url'=>$installUrl,'timestamp'=>$timestamp,'payload'=>$payload,
                'signature'=>Messaging_pricing::signature('ai_ads_media_generation',$timestamp,$installUrl,$payload,$key));
            $ch=curl_init($central.'/marketing/ai_ads/broker_ai_ads_media');
            curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($envelope),CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>240,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_FOLLOWLOCATION=>false,CURLOPT_HTTPHEADER=>array('Accept: application/json')));
            $raw=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
            $result=is_string($raw)?json_decode($raw,true):null;
            if($status!==200||!is_array($result)||empty($result['success'])||empty($result['data_base64']))throw new RuntimeException(is_array($result)?(string)($result['message']??'Creative service could not complete the request.'):'Creative service could not complete the request.'.($err?' Network request timed out.':''));
            $bytes=base64_decode((string)$result['data_base64'],true); if(!is_string($bytes)||strlen($bytes)>24*1024*1024)throw new RuntimeException('The generated file could not be saved.');
            $mime=$kind==='image'?'image/jpeg':'video/mp4'; $dir=FCPATH.'uploads/ai_ads/'.$this->storeId();
            if(!is_dir($dir)&&!@mkdir($dir,0750,true)&&!is_dir($dir))throw new RuntimeException('Campaign media storage is unavailable.');
            $name='generated_'.bin2hex(random_bytes(16)).($kind==='image'?'.jpg':'.mp4'); $relative='uploads/ai_ads/'.$this->storeId().'/'.$name;
            if(@file_put_contents(FCPATH.$relative,$bytes,LOCK_EX)!==strlen($bytes))throw new RuntimeException('The generated file could not be saved.');
            try {$id=$this->ads->saveMediaAsset($this->storeId(),$this->actorId(),array('asset_type'=>$kind,'source_type'=>'ai','file_name'=>$name,'relative_path'=>$relative,'mime_type'=>$mime,'size_bytes'=>strlen($bytes)));}
            catch(Throwable $e){@unlink(FCPATH.$relative);throw $e;}
            $this->ads->audit($this->storeId(),null,$this->actorId(),'media_generated','AI generated '.$kind.' creative for merchant review.');
            $usage=array('used'=>$reservation['used'],'limit'=>$reservation['limit']); $reservation=array();
            $director=null;
            if($kind==='video'&&isset($result['director'])&&is_array($result['director'])) {
                $copy=$result['director']['meta_ad_copy']??array();
                if(is_array($copy))$director=array('prompt'=>mb_substr(strip_tags($prompt),0,3000),'headline'=>mb_substr(strip_tags((string)($copy['headline']??'')),0,255),'primary_text'=>mb_substr(strip_tags((string)($copy['primary_text']??'')),0,2000));
            }
            if($director)$director['media_asset_id']=$id;
            $this->session->set_userdata('ai_ads_generated_media_id',$id);
            if($director)$this->session->set_userdata('ai_ads_director_prefill',$director);
            return $this->output->set_output(json_encode(array('success'=>true,'asset'=>array('id'=>$id,'name'=>$name,'type'=>$kind,'mime_type'=>$mime,'size_bytes'=>strlen($bytes)),'usage'=>$usage,'director'=>$director)));
        } catch(Throwable $e) {
            if(!empty($reservation['ok']))$this->ads->releaseCreativeGeneration($reservation);
            log_message('error','AI Ads creative generation failed: '.mb_substr(strip_tags($e->getMessage()),0,220));
            return $this->output->set_status_header(422)->set_output(json_encode(array('success'=>false,'message'=>mb_substr(strip_tags($e->getMessage()),0,200))));
        }
    }

    public function media_preview($assetId=0) {
        if(!$this->requireAccess())return;
        $asset=$this->db->where('id',(int)$assetId)->where('store_id',$this->storeId())->get('db_ai_ads_media_assets')->row_array();
        if(!$asset||!in_array($asset['mime_type'],array('image/jpeg','image/png','image/webp','video/mp4','video/quicktime','video/webm'),true))show_404();
        $path=realpath(FCPATH.$asset['relative_path']); $root=realpath(FCPATH.'uploads/ai_ads/'.$this->storeId());
        if(!$path||!$root||strpos($path,$root.DIRECTORY_SEPARATOR)!==0||!is_file($path))show_404();
        $this->output->set_content_type($asset['mime_type'])->set_header('Cache-Control','private, no-store')->set_output(file_get_contents($path));
    }

    public function delete_media() {
        if(!$this->requireAccess())return;
        if(strtoupper($this->input->method())!=='POST'){show_error('POST is required.',405);return;}
        $id=(int)$this->input->post('asset_id');
        $asset=$this->db->where('id',$id)->where('store_id',$this->storeId())->get('db_ai_ads_media_assets')->row_array();
        if(!$asset){$this->session->set_flashdata('error','That media file is no longer available.');redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));return;}
        if($this->db->where('store_id',$this->storeId())->where('media_asset_id',$id)->count_all_results('db_ai_ads_campaigns')>0){$this->session->set_flashdata('error','This file is attached to an ad draft. Remove it from the draft before deleting it.');redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));return;}
        $path=realpath(FCPATH.$asset['relative_path']); $root=realpath(FCPATH.'uploads/ai_ads/'.$this->storeId());
        if($path&&$root&&strpos($path,$root.DIRECTORY_SEPARATOR)===0&&is_file($path))@unlink($path);
        $this->db->where('id',$id)->where('store_id',$this->storeId())->delete('db_ai_ads_media_assets');
        $this->ads->audit($this->storeId(),null,$this->actorId(),'media_deleted','Merchant deleted campaign media to free storage.');
        $this->session->set_flashdata('success','Campaign media deleted. The space is available again.');
        redirect('marketing/ai_ads'.($this->isMobile()?'?screen=mobile':''));
    }

    public function generate_copy() {
        if (!$this->requireAccess()) return;
        if ($this->input->method()!=='post') { show_error('POST is required.',405); return; }
        $this->output->set_content_type('application/json','UTF-8')->set_header('Cache-Control','no-store');
        $reservation=array();
        try {
            if(!$this->ads->hasAccess($this->storeId())) throw new RuntimeException('AI copy suggestions require an active monthly or annual AI Ads subscription. You can still connect Meta and save drafts for free.');
            $prompt=trim((string)$this->input->post('prompt')); $productId=(int)$this->input->post('product_id'); $product=array();
            if ($prompt==='' || strlen($prompt)>3000) throw new RuntimeException('Describe the ad in up to 3,000 characters first.');
            if ($productId>0) { foreach($this->ads->products($this->storeId()) as $row) if((int)$row['id']===$productId) {$product=$row;break;} if(!$product) throw new RuntimeException('Choose a product from this store.'); }
            $reservation=$this->ads->reserveCopyGeneration($this->storeId());
            if(empty($reservation['ok'])) {
                if(($reservation['reason']??'')==='limit') { $reset=!empty($reservation['cycle_end'])?gmdate('M j',strtotime($reservation['cycle_end'].' UTC')):'your next plan month'; return $this->output->set_status_header(429)->set_output(json_encode(array('success'=>false,'message'=>'You’ve used this month’s AI copy allowance ('.(int)($reservation['used']??0).' of '.(int)($reservation['limit']??0).'). It resets '.$reset.' UTC.'))); }
                throw new RuntimeException('AI Ads monthly usage could not be checked. Please try again shortly.');
            }
            $this->load->library('assist_brain'); $copy=$this->assist_brain->generateAdCopy($prompt,$product);
            if (!$copy) { $this->ads->releaseCopyGeneration($reservation); $reservation=array(); throw new RuntimeException('AI copy suggestions are temporarily unavailable. Please try again later.'); }
            $this->ads->audit($this->storeId(),null,$this->actorId(),'ai_copy_generated','AI produced ad copy for merchant review.');
            return $this->output->set_output(json_encode(array('success'=>true,'copy'=>$copy,'usage'=>array('used'=>$reservation['used'],'limit'=>$reservation['limit'],'resets_at'=>$reservation['cycle_end']))));
        } catch(Throwable $e) {
            if(!empty($reservation['ok'])) $this->ads->releaseCopyGeneration($reservation);
            log_message('error','AI Ads copy generation failed: '.mb_substr(strip_tags($e->getMessage()),0,220));
            $this->output->set_status_header(422);
            $message=strpos($e->getMessage(),'active monthly or annual')!==false?'AI copy suggestions require an active plan. You can still connect Meta and save drafts for free.':
                (strpos($e->getMessage(),'Describe the ad')!==false||strpos($e->getMessage(),'Choose a product')!==false?$e->getMessage():'AI copy suggestions are temporarily unavailable. Please try again later.');
            return $this->output->set_output(json_encode(array('success'=>false,'message'=>$message)));
        }
    }
}
