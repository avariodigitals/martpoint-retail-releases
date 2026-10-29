<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Recurring segment campaigns. Every run has a unique slot and a bounded credit budget. */
class Communication_automation_model extends CI_Model {
    private function metadata($id,$storeId){
        if(!$this->db->table_exists('db_communication_auto_workflows'))throw new RuntimeException('Automation plans are waiting for an application update.');
        return $this->db->where('workflow_id',$id)->where('store_id',$storeId)->get('db_communication_auto_workflows')->row_array()?:array('template_key'=>'custom','archived'=>0);
    }
    private function register($storeId,$id,$name,$template,$enabled,$operation='register'){
        $this->load->library('messaging_client');$r=$this->messaging_client->request('automation_workflow',$storeId,array('workflow_key'=>'workflow:'.$id,'name'=>$name,'template_key'=>$template,'enabled'=>$enabled?1:0,'operation'=>$operation));
        if(empty($r['success']))throw new RuntimeException($r['message']??'Administrator could not confirm the automation slot.');
    }
    public function save($storeId,array $p,$emailAllowed,$smsAllowed){
        if(!$this->db->table_exists('db_communication_automations') || !$this->db->table_exists('db_communication_auto_workflows'))throw new RuntimeException('Automation plans are waiting for an application update.');
        $id=(int)($p['id']??0);$this->load->library('messaging_client');$enabled=!empty($p['enabled']);
        if($id){
            $old=$this->db->where('id',$id)->where('store_id',$storeId)->get('db_communication_automations')->row_array();if(!$old)throw new RuntimeException('Automation not found.');$channel=$old['channel'];$meta=$this->metadata($id,$storeId);$template=$meta['template_key'];
        }else{$channel=$p['channel']??'';$template=$p['automation_template_key']??'custom';}
        if(($channel==='email' && !$emailAllowed) || ($channel==='sms' && !$smsAllowed) || !in_array($channel,array('sms','email'),true))throw new RuntimeException('Channel permission is required.');
        if($id && (($p['operation']??'')==='archive' || !$enabled)){
            $this->db->where('id',$id)->where('store_id',$storeId)->update('db_communication_automations',array('enabled'=>0));
            if($template==='abandoned_cart'){$this->load->model('communication_cart_recovery_model');$this->communication_cart_recovery_model->releaseUnsent($storeId,$id);}
            try{$archive=($p['operation']??'')==='archive';$this->register($storeId,$id,$old['name'],$template,false,$archive?'archive':'pause');
                if($archive)$this->db->query('INSERT INTO db_communication_auto_workflows (workflow_id,store_id,template_key,archived) VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE archived=1',array($id,$storeId,$template));
                $this->db->where('id',$id)->update('db_communication_automations',array('last_error'=>null));
            }catch(Throwable $e){$this->db->where('id',$id)->update('db_communication_automations',array('last_error'=>'Paused. Your Administrator could not confirm this change. '.$e->getMessage()));if(($p['operation']??'')==='archive')throw $e;}
            return;
        }
        $snapshot=$this->messaging_client->request('snapshot',$storeId);$policy=$snapshot['communications']['automation']??array();
        if(empty($policy['available']) || empty($snapshot['communications']['modules']['automation']['active']))throw new RuntimeException('Ask your Administrator to activate an Automation plan first.');
        if(!isset($policy['templates'][$template]))throw new RuntimeException('This template is not included in your Automation plan.');
        if($enabled && empty($snapshot['channels'][$channel]))throw new RuntimeException('Activate the selected SMS or email channel before turning this workflow on.');
        if($id){
            if($meta['archived'])throw new RuntimeException('This workflow has been archived. Create a new workflow.');
            $this->register($storeId,$id,$old['name'],$template,true);$this->db->where('id',$id)->where('store_id',$storeId)->update('db_communication_automations',array('enabled'=>1,'last_error'=>null));return;
        }
        if((int)($policy['remaining_workflows']??0)<1)throw new RuntimeException('Your Automation plan has no free slots. Archive a workflow or upgrade.');
        $this->load->model('marketing_model','marketing_m');$segment=$p['segment_key']??'';$interval=(int)($p['interval_days']??0);$budget=(int)($p['budget_units']??0);$name=trim($p['name']??'');$message=trim($p['message']??'');$subject=trim($p['subject']??'');
        if(!$this->marketing_m->canUseSegment($segment,$storeId) || !$name || !$message || strlen($message)>20000 || ($channel==='email' && (!$subject || preg_match('/[\r\n]/',$subject))) || $interval<1 || $interval>365 || $budget<1 || $budget>1000000)throw new RuntimeException('Enter a name, permitted audience, message, subject, interval and message-credit cap.');
        if($template==='birthday' && ($segment!=='birthday_today' || $interval!==1))throw new RuntimeException('Birthday automation must check the Birthdays today audience every day.');
        if(($template==='abandoned_cart')!==($segment==='abandoned_carts'))throw new RuntimeException('Use the Recover abandoned carts template with its cart audience.');
        if($template==='abandoned_cart'){if(strpos($message,'{cart_url}')===false)throw new RuntimeException('Keep {cart_url} in the message so each customer receives their own checkout link.');$this->load->model('communication_cart_recovery_model');$this->communication_cart_recovery_model->settings($storeId);}
        $next=gmdate('Y-m-d H:i:s');if(!empty($p['start_at'])){require_once APPPATH.'models/Communication_schedule_model.php';$next=Communication_schedule_model::utc($p['start_at']);}
        $this->db->trans_begin();try{
            $this->db->insert('db_communication_automations',array('store_id'=>$storeId,'name'=>mb_substr($name,0,150),'channel'=>$channel,'segment_key'=>$segment,'subject'=>mb_substr($subject,0,255),'message'=>$message,'enabled'=>0,'budget_units'=>$budget,'interval_days'=>$interval,'next_run_at'=>$next,'created_at'=>gmdate('Y-m-d H:i:s')));
            $id=$this->db->insert_id();$this->db->insert('db_communication_auto_workflows',array('workflow_id'=>$id,'store_id'=>$storeId,'template_key'=>$template));if(!$this->db->trans_status())throw new RuntimeException('Could not save workflow.');$this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
        try{$this->register($storeId,$id,$name,$template,$enabled);$this->db->where('id',$id)->update('db_communication_automations',array('enabled'=>$enabled?1:0));}
        catch(Throwable $e){$this->db->where('id',$id)->update('db_communication_automations',array('last_error'=>$e->getMessage()));throw $e;}
    }
    private function nextRunAt(array $a){
        if($a['segment_key']==='birthday_today'){$local=(new DateTimeImmutable($a['next_run_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'));$tomorrow=(new DateTimeImmutable('tomorrow',new DateTimeZone('Africa/Lagos')))->setTime((int)$local->format('H'),(int)$local->format('i'));return $tomorrow->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');}
        return gmdate('Y-m-d H:i:s',time()+(int)$a['interval_days']*86400);
    }
    public function run($templateOnly=null){
        $this->load->model('marketing_model','marketing_m');$this->load->library('campaign_messaging');$done=0;
        if(!$this->db->table_exists('db_communication_automations') || !$this->db->table_exists('db_communication_auto_workflows'))return 0;
        if($templateOnly!==null)$this->db->where('m.template_key',$templateOnly);
        $rows=$this->db->select('a.*')->from('db_communication_automations a')->join('db_communication_auto_workflows m','m.workflow_id=a.id AND m.store_id=a.store_id','left')->where('a.enabled',1)->group_start()->where('m.archived IS NULL',null,false)->or_where('m.archived',0)->group_end()->where('a.next_run_at <=',gmdate('Y-m-d H:i:s'))->order_by('a.next_run_at')->limit(3)->get()->result_array();
        foreach($rows as $row){
            $lock='mp_comm_auto_'.(int)$row['id'];$got=$this->db->query('SELECT GET_LOCK(?,0) AS acquired',array($lock))->row();if(!$got || !$got->acquired)continue;
            try{
                $a=$this->db->where('id',$row['id'])->get('db_communication_automations')->row_array();if(!$a['enabled'] || strtotime($a['next_run_at'].' UTC')>time())continue;
                if($a['segment_key']==='birthday_today'){$now=new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos'));$due=(new DateTimeImmutable($a['next_run_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'));if($due->format('Y-m-d')<$now->format('Y-m-d')){$today=$now->setTime((int)$due->format('H'),(int)$due->format('i'));$a['next_run_at']=$today->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');$this->db->where('id',$a['id'])->update('db_communication_automations',array('next_run_at'=>$a['next_run_at']));if($today>$now)continue;}}
                $snapshot=$this->messaging_client->request('snapshot',$a['store_id']);if(empty($snapshot['communications']['modules']['automation']['active']) || empty($snapshot['channels'][$a['channel']]))throw new RuntimeException('Automation or channel subscription is inactive.');
                $meta=$this->metadata($a['id'],$a['store_id']);$this->register($a['store_id'],$a['id'],$a['name'],$meta['template_key'],true);
                $this->db->query('INSERT IGNORE INTO db_communication_automation_runs (automation_id,scheduled_at) VALUES (?,?)',array($a['id'],$a['next_run_at']));
                $r=$this->db->where('automation_id',$a['id'])->where('scheduled_at',$a['next_run_at'])->get('db_communication_automation_runs')->row_array();
                if(!$r['campaign_id']){
                    $this->db->trans_begin();try{$cid=$this->marketing_m->saveCampaign($a['store_id'],array('name'=>$a['name'].' · '.$a['next_run_at'],'channel'=>$a['channel'],'segment_key'=>$a['segment_key'],'subject'=>$a['subject'],'message'=>$a['message']));if(!$cid)throw new RuntimeException('Could not create automation campaign.');$this->db->where('id',$r['id'])->update('db_communication_automation_runs',array('campaign_id'=>$cid));if(!$this->db->trans_status())throw new RuntimeException('Could not record automation run.');$this->db->trans_commit();$r['campaign_id']=$cid;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
                }
                $c=$this->marketing_m->getCampaign($r['campaign_id'],$a['store_id']);
                // A slot whose campaign has already left draft was handled by an earlier pass; it must never stage, bill or report a second send.
                if($c->status!=='draft'){$this->db->where('id',$a['id'])->update('db_communication_automations',array('next_run_at'=>$this->nextRunAt($a)));continue;}
                if(!$this->db->where('campaign_id',$c->id)->count_all_results('db_campaign_sends')){if($meta['template_key']==='abandoned_cart'){$this->load->model('communication_cart_recovery_model');$this->communication_cart_recovery_model->stage($c);}else $this->marketing_m->stageCampaignAudience($c,$this->marketing_m->resolveSegment($a['store_id'],$a['segment_key']));}
                $pending=$this->marketing_m->getPendingSends($c->id,10001);if(count($pending)>10000)throw new RuntimeException('Audience exceeds 10,000. Choose a smaller audience.');
                if($pending){$q=$this->campaign_messaging->estimate($c,$pending);if(empty($q['success']) || empty($q['ready']))throw new RuntimeException($q['message']??'Channel is unavailable.');if((int)$q['credit_units']>$a['budget_units'])throw new RuntimeException('Audience exceeds this automation’s credit limit.');
                    $approved=$this->messaging_client->request('authorize_campaign',$a['store_id'],array('campaign_id'=>(string)$c->id,'channel'=>$a['channel'],'budget_units'=>(int)$q['credit_units'],'budget_kobo'=>(int)$q['total_kobo']));if(empty($approved['success']))throw new RuntimeException($approved['message']??'Credit approval failed.');
                    $this->db->query('INSERT INTO db_messaging_campaign_approvals (store_id,campaign_id,approved_at) VALUES (?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE approved_at=VALUES(approved_at)',array($a['store_id'],$c->id));
                }
                $this->db->where('id',$c->id)->update('db_campaigns',array('status'=>$pending?'sending':'sent'));
                $this->db->where('id',$r['id'])->update('db_communication_automation_runs',array('status'=>'queued'));
                $this->db->where('id',$a['id'])->update('db_communication_automations',array('next_run_at'=>$this->nextRunAt($a),'last_run_at'=>gmdate('Y-m-d H:i:s'),'last_error'=>null));$done++;
            }catch(Throwable $e){$this->db->where('id',$row['id'])->update('db_communication_automations',array('last_error'=>mb_substr($e->getMessage(),0,500)));}
            finally{$this->db->query('SELECT RELEASE_LOCK(?)',array($lock));}
        }return $done;
    }
}
