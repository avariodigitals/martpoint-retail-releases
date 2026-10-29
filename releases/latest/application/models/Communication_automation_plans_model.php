<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Central is the authority for plan limits, template access and workflow slots. */
class Communication_automation_plans_model extends CI_Model {
    public function ready(){foreach(array('plans','templates','subscriptions','request_plans','registrations') as $suffix)if(!$this->db->table_exists('db_communication_auto_'.$suffix))return false;return true;}
    private function central(){if(!mp_is_central() || !$this->ready())throw new RuntimeException('Automation plans are not available yet. Contact your Administrator.');}
    public function plans($all=false){$this->central();$rows=$this->db->order_by('max_workflows')->get('db_communication_auto_plans')->result_array();$out=array();foreach($rows as $r){if(!$all && !$r['enabled'])continue;$r['template_keys']=json_decode($r['template_keys_json'],true)?:array();$out[$r['plan_key']]=$r;}return $out;}
    public function templates($all=false){$this->central();if(!$all)$this->db->where('enabled',1);$rows=$this->db->order_by('name')->get('db_communication_auto_templates')->result_array();$out=array();foreach($rows as $r)$out[$r['template_key']]=$r;return $out;}
    public function plan($key){$plans=$this->plans();if(!isset($plans[$key]))throw new RuntimeException('Choose an available Automation plan.');return $plans[$key];}
    public function assigned($accountId){$this->central();$r=$this->db->where('account_id',$accountId)->get('db_communication_auto_subscriptions')->row_array();return $r['plan_key']??'plan_a';}
    public function assign($accountId,$key){$this->plan($key);$this->db->query('INSERT INTO db_communication_auto_subscriptions (account_id,plan_key) VALUES (?,?) ON DUPLICATE KEY UPDATE plan_key=VALUES(plan_key)',array($accountId,$key));}
    public function requestPlan($requestId){$r=$this->db->where('request_id',$requestId)->get('db_communication_auto_request_plans')->row_array();return $r['plan_key']??'plan_a';}
    public function bindRequest($id,$key){$this->plan($key);$this->db->query('INSERT INTO db_communication_auto_request_plans (request_id,plan_key) VALUES (?,?)',array($id,$key));}
    public function snapshot($accountId){
        if(!$this->ready())return array('available'=>false,'message'=>'Automation plans are not available yet. Contact your Administrator.');
        $plans=$this->plans(true);$key=$this->assigned($accountId);$plan=$plans[$key]??null;$templates=$this->templates();$allowed=array();
        if($plan && $plan['enabled'])foreach($plan['template_keys'] as $t)if(isset($templates[$t]))$allowed[$t]=$templates[$t];
        $registered=$this->db->where('account_id',$accountId)->where('archived',0)->count_all_results('db_communication_auto_registrations');
        return array('available'=>true,'plan'=>$plan,'used_workflows'=>$registered,'remaining_workflows'=>$plan?max(0,(int)$plan['max_workflows']-$registered):0,'templates'=>$allowed,'plans'=>$this->plans(),'all_templates'=>$templates,
            'registrations'=>$this->db->select('workflow_key,name,template_key,enabled,updated_at')->where('account_id',$accountId)->where('archived',0)->order_by('id')->get('db_communication_auto_registrations')->result_array());
    }
    private function policy($accountId,$template){
        $this->central();$this->load->model('communications_model');$this->communications_model->requireAccess($accountId,'automation');$plan=$this->plan($this->assigned($accountId));$templates=$this->templates();
        if(!in_array($template,$plan['template_keys'],true) || !isset($templates[$template]))throw new RuntimeException('This automation template is not included in your active plan.');return $plan;
    }
    public function register($accountId,array $p){
        $this->central();$key=(string)($p['workflow_key']??'');$operation=$p['operation']??'register';
        if(!preg_match('/^workflow:[1-9][0-9]{0,18}$/D',$key) || !in_array($operation,array('register','pause','archive'),true))throw new InvalidArgumentException('Invalid workflow registration.');
        $this->db->trans_begin();try{
            if(!$this->db->query('SELECT id FROM db_messaging_accounts WHERE id=? FOR UPDATE',array($accountId))->row())throw new RuntimeException('Account not found.');
            $old=$this->db->where('account_id',$accountId)->where('workflow_key',$key)->get('db_communication_auto_registrations')->row_array();
            if($operation!=='register'){
                if($old)$this->db->where('id',$old['id'])->update('db_communication_auto_registrations',array('enabled'=>0,'archived'=>$operation==='archive'?1:$old['archived'],'updated_at'=>gmdate('Y-m-d H:i:s')));
            }else{
                $template=(string)($p['template_key']??'');$plan=$this->policy($accountId,$template);$used=$this->db->where('account_id',$accountId)->where('archived',0)->count_all_results('db_communication_auto_registrations');
                if((!$old || $old['archived']) && $used>=(int)$plan['max_workflows'])throw new RuntimeException('Your '.$plan['name'].' plan allows '.$plan['max_workflows'].' saved automations. Archive one or upgrade the plan.');
                if($old && !$old['archived'] && !empty($p['enabled'])){
                    $position=$this->db->where('account_id',$accountId)->where('archived',0)->where('id <=',$old['id'])->count_all_results('db_communication_auto_registrations');if($position>(int)$plan['max_workflows'])throw new RuntimeException('This workflow exceeds the current plan limit. Archive workflows or upgrade.');
                }
                $data=array('template_key'=>$template,'name'=>mb_substr(trim((string)($p['name']??'Workflow')),0,150),'enabled'=>!empty($p['enabled'])?1:0,'archived'=>0,'updated_at'=>gmdate('Y-m-d H:i:s'));
                if($old)$this->db->where('id',$old['id'])->update('db_communication_auto_registrations',$data);else {$data['account_id']=$accountId;$data['workflow_key']=$key;$this->db->insert('db_communication_auto_registrations',$data);}
            }
            if(!$this->db->trans_status())throw new RuntimeException('Workflow registration could not be saved.');$this->db->trans_commit();return array('success'=>true,'message'=>'Automation slot updated.');
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function assertWorkflow($accountId,$key,$template){
        $plan=$this->policy($accountId,$template);$r=$this->db->where('account_id',$accountId)->where('workflow_key',$key)->where('archived',0)->where('enabled',1)->get('db_communication_auto_registrations')->row_array();
        if(!$r || $r['template_key']!==$template)throw new RuntimeException('This workflow is not active. Open Automation to activate it or contact your Administrator.');
        $position=$this->db->where('account_id',$accountId)->where('archived',0)->where('id <=',$r['id'])->count_all_results('db_communication_auto_registrations');
        if($position>(int)$plan['max_workflows'])throw new RuntimeException('This workflow exceeds the current Automation plan limit. Archive workflows or upgrade.');
    }
    public function savePlan(array $p,$actor){
        $this->central();$key=(string)($p['plan_key']??'');$name=trim((string)($p['name']??''));$max=filter_var($p['max_workflows']??'',FILTER_VALIDATE_INT);
        if(!preg_match('/^[a-z][a-z0-9_]{1,39}$/D',$key) || !$name || $max===false || $max<1 || $max>10000)throw new InvalidArgumentException('Enter a plan key, name and a workflow limit from 1 to 10,000.');
        $keys=$p['template_keys']??array();if(!is_array($keys))throw new InvalidArgumentException('Choose templates for this plan.');$available=$this->templates(true);foreach($keys as $t)if(!is_string($t) || !isset($available[$t]))throw new InvalidArgumentException('Unknown automation template.');
        $data=array('name'=>mb_substr($name,0,100),'max_workflows'=>$max,'monthly_kobo'=>Messaging_pricing::money($p['monthly']??'0'),'annual_kobo'=>Messaging_pricing::money($p['annual']??'0'),'template_keys_json'=>json_encode(array_values(array_unique($keys))),'enabled'=>!empty($p['enabled'])?1:0);
        if($this->db->where('plan_key',$key)->count_all_results('db_communication_auto_plans'))$this->db->where('plan_key',$key)->update('db_communication_auto_plans',$data);else{$data['plan_key']=$key;$this->db->insert('db_communication_auto_plans',$data);}
        $this->load->model('communications_model');$this->communications_model->event('auto-plan:'.bin2hex(random_bytes(12)),0,'Automation plan updated',$key.'; max '.$max.'; by '.$actor);
    }
    public function saveTemplate(array $p,$actor){
        $this->central();$this->load->model('marketing_model');$key=(string)($p['template_key']??'');$name=trim((string)($p['name']??''));$segment=(string)($p['segment_key']??'');$days=filter_var($p['interval_days']??'',FILTER_VALIDATE_INT);
        if(!preg_match('/^[a-z][a-z0-9_]{1,39}$/D',$key) || !$name || !isset(Marketing_model::$SEGMENTS[$segment]) || $days===false || $days<1 || $days>365 || trim((string)($p['message']??''))==='' || strlen($p['message'])>20000 || preg_match('/[\r\n]/',$p['subject']??''))throw new InvalidArgumentException('Enter a template key, name, supported audience, subject, message and 1–365 day interval.');
        if(($key==='abandoned_cart')!==($segment==='abandoned_carts') || ($key==='abandoned_cart' && strpos($p['message'],'{cart_url}')===false))throw new InvalidArgumentException('Use the abandoned-cart template with its cart audience and keep {cart_url} in the message.');
        $data=array('name'=>mb_substr($name,0,100),'description'=>mb_substr((string)($p['description']??''),0,500),'segment_key'=>$segment,'subject'=>mb_substr((string)($p['subject']??''),0,255),'message'=>$p['message'],'interval_days'=>$days,'enabled'=>!empty($p['enabled'])?1:0);
        if($this->db->where('template_key',$key)->count_all_results('db_communication_auto_templates'))$this->db->where('template_key',$key)->update('db_communication_auto_templates',$data);else{$data['template_key']=$key;$this->db->insert('db_communication_auto_templates',$data);}
        $this->load->model('communications_model');$this->communications_model->event('auto-template:'.bin2hex(random_bytes(12)),0,'Automation template updated',$key.'; by '.$actor);
    }
}
