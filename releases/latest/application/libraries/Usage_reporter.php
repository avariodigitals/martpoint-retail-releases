<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Private, fail-open retention check-ins. No network I/O on login or sale submission. */
class Usage_reporter {
    protected $directory;
    protected $payload = [];
    public function __construct() {$this->directory=APPPATH.'cache';}
    protected function diagnostic(string $message): void {
        if(function_exists('log_message')) log_message('error','Client retention reporter: '.$message);
    }
    /** Install-level admins use the POS for support/maintenance, not client activity. */
    public function isAdministrativeUser(): bool {
        $ci =& get_instance();
        if(!$ci || !isset($ci->session))return false;
        $roleId=(int)$ci->session->userdata('role_id');
        $roleName=strtoupper(trim((string)$ci->session->userdata('role_name')));
        if($roleId===1 || in_array($roleName,['ADMIN','SUPER ADMIN','STORE ADMIN'],true))return true;
        // Store Admin can be a store-scoped role with a non-fixed role ID.
        if(function_exists('is_store_admin')){
            try{return (bool)is_store_admin();}catch(Throwable $ignored){}
        }
        return false;
    }
    protected function eligible(string $url): bool {
        $host=strtolower((string)parse_url($url,PHP_URL_HOST));
        return preg_match('~^https?://~i',$url) && $host && strpos($host,'.')!==false
            && !filter_var($host,FILTER_VALIDATE_IP)
            && !preg_match('/\.(test|local|localhost|invalid|example|internal)$/',$host)
            && !(function_exists('mp_is_vendor_box')&&mp_is_vendor_box());
    }
    protected function path(string $url): string {return $this->directory.'/usage-'.hash('sha256',$url).'.json';}
    /** Successful authentication records a timestamp, without contacting another server. */
    public function recordLogin(string $url): bool {
        if(!$this->eligible($url))return false;
        $f=@fopen($this->path($url).'.login','c+');if(!$f){$this->diagnostic('could not write login marker; application/cache is unavailable or not writable.');return false;}
        try{if(!flock($f,LOCK_EX))return false;$prior=(int)stream_get_contents($f);rewind($f);ftruncate($f,0);fwrite($f,(string)max($prior,time()));fflush($f);@chmod($this->path($url).'.login',0600);return true;}finally{fclose($f);}
    }
    protected function identity(): array {
        $ci =& get_instance();$ci->load->database();
        $row=$ci->db->select('install_key')->where('id',1)->get('db_sitesettings')->row();
        return ['install_key'=>(string)($row->install_key ?? '')];
    }
    protected function transport(string $endpoint,array $body): ?array {
        if(!function_exists('curl_init')){$this->diagnostic('PHP cURL is unavailable for '.$endpoint.'.');return null;}
        // Use the canonical host directly. The apex domain issues HTTP 308,
        // and this client intentionally refuses redirects for API requests.
        $ch=@curl_init('https://www.martpoint.com.ng/api/usage/'.$endpoint);
        if($ch===false){$this->diagnostic('could not initialize PHP cURL for '.$endpoint.'.');return null;}
        // This runs from a separate browser request, never from login or sale
        // submission. Enrollment nests a Fleet proof and database writes, so a
        // two-second total timeout was too short for normal cold/remote calls.
        $timeout=$endpoint==='enroll'?10000:5000;
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT_MS=>1500,CURLOPT_TIMEOUT_MS=>$timeout,CURLOPT_NOSIGNAL=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
        $raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);$errno=curl_errno($ch);$error=curl_error($ch);curl_close($ch);
        if($raw===false||$status!==200){$this->diagnostic($endpoint.' request failed (HTTP '.$status.', cURL '.$errno.($error!==''?': '.substr($error,0,180):'').').');return null;}
        $data=json_decode($raw,true);if(!is_array($data)){$this->diagnostic($endpoint.' returned a non-JSON response.');return null;}return $data;
    }
    protected function enrol(string $url): ?string {
        $id=$this->identity();if(strlen($id['install_key'])<32){$this->diagnostic('local Fleet install_key is missing or too short.');return null;}
        $r=$this->transport('enroll',['install_url'=>$url,'install_key'=>$id['install_key']]);
        $token=$r['token'] ?? '';return !empty($r['ok'])&&is_string($token)&&preg_match('/^[A-Za-z0-9_-]{24,128}$/D',$token)?$token:null;
    }
    protected function send(string $url): ?array {return $this->transport('ping',$this->payload);}
    public function report(string $url): bool {return $this->deliver($url,false);}
    public function reportSystem(string $url): bool {return $this->deliver($url,true);}
    protected function deliver(string $url,bool $system): bool {
        if(!$this->eligible($url))return false;
        $path=$this->path($url);$file=@fopen($path,'c+');if(!$file){$this->diagnostic('could not open reporter state; application/cache is unavailable or not writable.');return false;}
        try{
            if(!flock($file,LOCK_EX|LOCK_NB))return false;
            @chmod($path,0600);$state=json_decode(stream_get_contents($file),true) ?: [];$now=time();
            $login=(int)@file_get_contents($path.'.login');$pending=$login>(int)($state['login_sent'] ?? 0);
            $clock=$system?'system':'activity';
            if(!$pending && $now-(int)($state[$clock.'_success'] ?? 0)<86400)return false;
            if($now-(int)($state[$clock.'_attempt'] ?? 0)<900)return false;
            $state[$clock.'_attempt']=$now;$this->writeState($file,$state);
            if(empty($state['token'])){
                $state['token']=$this->enrol($url);$this->writeState($file,$state);
                if(!$state['token']){$this->diagnostic('private install enrollment was rejected; anonymous hostname reporting is disabled.');return false;} // No anonymous hostname attribution for new clients.
            }
            $this->payload=['token'=>$state['token'],'type'=>$system?'system':'usage'];
            $this->payload[$system?'system_at':'activity_at']=gmdate('c',$now);
            if($pending)$this->payload['login_at']=gmdate('c',$login);
            $response=$this->send($url);$matched=$response['matched'] ?? false;
            if(($response['ok'] ?? false)===true && ($matched===true||(is_array($matched)&&($matched['matched'] ?? false)===true))){
                $state[$clock.'_success']=$now;if($pending)$state['login_sent']=$login;$this->writeState($file,$state);return true;
            }
            $this->diagnostic('MartPoint usage API did not confirm the '.$clock.' report.');
            // Revoked/rotated tokens are re-enrolled on the next throttled attempt.
            if(is_array($response)&&($response['ok'] ?? false)===true && $matched===false){unset($state['token']);$this->writeState($file,$state);}
            return false;
        }catch(Throwable $e){$type=get_class($e);$this->diagnostic('unexpected local reporting error ('.$type.').');return false;}finally{fclose($file);}
    }
    protected function writeState($file,array $state): void {rewind($file);ftruncate($file,0);fwrite($file,json_encode($state));fflush($file);}
}
