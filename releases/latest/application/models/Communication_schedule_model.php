<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Scheduled one-off sends resolve their audience when due, within an explicit credit cap. */
class Communication_schedule_model extends CI_Model {
    public static function utc($value){
        $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',(string)$value,new DateTimeZone('Africa/Lagos'));
        if(!$date || $date->format('Y-m-d\TH:i')!==$value || $date->getTimestamp()<=time() || $date->getTimestamp()>time()+366*86400)throw new InvalidArgumentException('Choose a future date/time within one year (West Africa Time).');
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
    public function get($store,$id){if(!$this->db->table_exists('db_communication_campaign_schedules'))return null;return $this->db->where('store_id',$store)->where('campaign_id',$id)->get('db_communication_campaign_schedules')->row_array();}
    public function save($store,$id,$when,$budget){
        if(!$this->db->table_exists('db_communication_campaign_schedules'))throw new RuntimeException('Scheduling is not available yet. Contact your Administrator.');
        $this->db->trans_begin();try{
        $locked=$this->db->query('SELECT id FROM db_campaigns WHERE id=? AND store_id=? FOR UPDATE',array($id,$store))->row_array();if(!$locked)throw new RuntimeException('Campaign not found.');
        $existing=$this->get($store,$id);if($existing && !in_array($existing['status'],array('cancelled','paused'),true))throw new RuntimeException('Cancel the current schedule before rescheduling.');
        $at=self::utc($when);$units=filter_var($budget,FILTER_VALIDATE_INT);$this->load->model('marketing_model','marketing_m');$c=$this->marketing_m->getCampaign($id,$store);
        if(!$c || $c->status!=='draft' || !$this->marketing_m->canUseSegment($c->segment_key,$store) || $units===false || $units<1 || $units>1000000)throw new InvalidArgumentException('Schedule a draft with a permitted audience and a 1–1,000,000 credit cap.');
        // A quoted draft may already have a frozen audience. A schedule must resolve it afresh.
        $this->db->where('store_id',$store)->where('campaign_id',$id)->where('status','pending')->delete('db_campaign_sends');
        $this->db->where('store_id',$store)->where('campaign_id',$id)->delete('db_messaging_campaign_approvals');
        $this->db->query("INSERT INTO db_communication_campaign_schedules (campaign_id,store_id,scheduled_at,budget_units,status,created_at) VALUES (?,?,?,?,'scheduled',UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE scheduled_at=VALUES(scheduled_at),budget_units=VALUES(budget_units),status='scheduled',last_error=NULL",array($id,$store,$at,$units));
        if(!$this->db->trans_status())throw new RuntimeException('Schedule could not be saved.');$this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function cancel($store,$id){
        $this->db->trans_begin();try{
            $r=$this->db->query('SELECT * FROM db_communication_campaign_schedules WHERE campaign_id=? AND store_id=? FOR UPDATE',array($id,$store))->row_array();
            if(!$r || !in_array($r['status'],array('scheduled','paused'),true))throw new RuntimeException('Only a campaign still waiting to start can be cancelled.');
            $this->db->where('campaign_id',$id)->where('store_id',$store)->update('db_communication_campaign_schedules',array('status'=>'cancelled','last_error'=>null));$this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function run(){
        if(!$this->db->table_exists('db_communication_campaign_schedules'))return 0;
        $this->load->model('marketing_model','marketing_m');$this->load->library('campaign_messaging');$this->load->library('messaging_client');$done=0;
        $rows=$this->db->where('status','scheduled')->where('scheduled_at <=',gmdate('Y-m-d H:i:s'))->order_by('scheduled_at')->limit(3)->get('db_communication_campaign_schedules')->result_array();
        foreach($rows as $row){
            // Row lock prevents both duplicate starts and a concurrent cancellation.
            $this->db->trans_begin();try{
                $this->db->query('SELECT id FROM db_campaigns WHERE id=? AND store_id=? FOR UPDATE',array($row['campaign_id'],$row['store_id']));
                $s=$this->db->query('SELECT * FROM db_communication_campaign_schedules WHERE campaign_id=? FOR UPDATE',array($row['campaign_id']))->row_array();if($s['status']!=='scheduled'){$this->db->trans_commit();continue;}
                $c=$this->marketing_m->getCampaign($s['campaign_id'],$s['store_id']);if(!$c || $c->status!=='draft')throw new RuntimeException('The draft changed; review the campaign before rescheduling.');
                $today=(new DateTimeImmutable('now',new DateTimeZone('Africa/Lagos')))->format('Y-m-d');$scheduled=(new DateTimeImmutable($s['scheduled_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d');
                if($c->segment_key==='birthday_today' && $scheduled!==$today)throw new RuntimeException('The birthday send date has passed. Cancel and schedule a new date.');
                $this->db->where('campaign_id',$c->id)->where('status','pending')->delete('db_campaign_sends');
                $this->marketing_m->stageCampaignAudience($c,$this->marketing_m->resolveSegment($s['store_id'],$c->segment_key));$pending=$this->marketing_m->getPendingSends($c->id,10001);
                if(count($pending)>10000)throw new RuntimeException('Audience exceeds 10,000. Choose a smaller audience.');
                if($pending){$q=$this->campaign_messaging->estimate($c,$pending);if(empty($q['success']) || empty($q['ready']))throw new RuntimeException($q['message']??'The channel is unavailable.');if((int)$q['credit_units']>(int)$s['budget_units'])throw new RuntimeException('The due audience exceeds the scheduled credit cap. Cancel and review the cap.');
                    $r=$this->messaging_client->request('authorize_campaign',$s['store_id'],array('campaign_id'=>(string)$c->id,'channel'=>$c->channel,'budget_units'=>(int)$q['credit_units'],'budget_kobo'=>(int)$q['total_kobo']));if(empty($r['success']))throw new RuntimeException($r['message']??'Administrator did not authorize the credit cap.');
                    $this->db->query('INSERT INTO db_messaging_campaign_approvals (store_id,campaign_id,approved_at) VALUES (?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE approved_at=VALUES(approved_at)',array($s['store_id'],$c->id));
                }
                $this->db->where('id',$c->id)->where('store_id',$s['store_id'])->update('db_campaigns',array('status'=>$pending?'sending':'sent'));
                $this->db->where('campaign_id',$c->id)->update('db_communication_campaign_schedules',array('status'=>'queued','last_error'=>null));if(!$this->db->trans_status())throw new RuntimeException('Campaign scheduling could not be saved.');$this->db->trans_commit();$done++;
            }catch(Throwable $e){$this->db->trans_rollback();$this->db->where('campaign_id',$row['campaign_id'])->where('status','scheduled')->update('db_communication_campaign_schedules',array('status'=>'paused','last_error'=>mb_substr($e->getMessage(),0,500)));}
        }return $done;
    }
}
