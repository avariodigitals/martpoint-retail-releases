<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Central-owned recharge evidence, commercial bundles and approved SMS identities. */
class Communication_review_model extends CI_Model {
    public function ready(){foreach(array('documents','sms_settings','sender_requests','sender_assignments','credit_rules','credit_packages','request_packages') as $t)if(!$this->db->table_exists('db_communication_'.$t))return false;return true;}
    private function guard(){if(!mp_is_central() || !$this->ready())throw new RuntimeException('This feature is not available yet. Contact your Administrator.');}
    public function settings(){$this->guard();return $this->db->where('id',1)->get('db_communication_sms_settings')->row_array();}
    public function saveSettings(array $p,$actor){
        $this->guard();$mode=$p['sender_mode']??'shared';$sender=trim($p['shared_sender']??'');$bank=trim($p['bank_name']??'');$name=trim($p['bank_account_name']??'');$number=trim($p['bank_account_number']??'');
        if(!in_array($mode,array('shared','hybrid'),true) || ($sender!=='' && !preg_match('/^(?=.*[a-zA-Z])[a-zA-Z0-9]{3,11}$/D',$sender)))throw new InvalidArgumentException('Choose a Sender ID mode and a 3–11 character brand using letters and numbers.');
        if(($bank!=='' || $name!=='' || $number!=='') && (!$bank || !$name || !preg_match('/^[0-9]{10}$/D',$number)))throw new InvalidArgumentException('Enter the bank name, account name and ten-digit account number together.');
        $this->db->where('id',1)->update('db_communication_sms_settings',array('sender_mode'=>$mode,'shared_sender'=>$sender,'bank_name'=>mb_substr($bank,0,100),'bank_account_name'=>mb_substr($name,0,190),'bank_account_number'=>$number));
        $this->load->model('communications_model');$this->communications_model->event('sms-settings:'.bin2hex(random_bytes(12)),0,'Communications master settings changed','Sender mode: '.$mode.'; by '.$actor.'.');
    }
    public function bundles($channel=null,$all=false){$this->guard();if($channel)$this->db->where('channel',$channel);if(!$all)$this->db->where('enabled',1);return $this->db->order_by('amount_kobo')->get('db_communication_credit_packages')->result_array();}
    public function saveBundle(array $p,$actor){
        $this->guard();$channel=$p['module_key']??'';$amount=Messaging_pricing::money($p['amount']??'');$units=filter_var($p['credit_units']??'',FILTER_VALIDATE_INT);$name=trim($p['name']??'');$id=(int)($p['package_id']??0);
        if(!in_array($channel,array('sms','email'),true) || !$name || $units===false || $units<1 || $units>10000000 || $amount<10000 || $amount>50000000)throw new InvalidArgumentException('Enter a bundle name, channel, ₦100–₦500,000 price and whole credit quantity.');
        $data=array('channel'=>$channel,'name'=>mb_substr($name,0,100),'amount_kobo'=>$amount,'credit_units'=>$units,'enabled'=>!empty($p['enabled'])?1:0);
        if($id){$old=$this->db->where('id',$id)->get('db_communication_credit_packages')->row_array();if(!$old || $old['channel']!==$channel)throw new RuntimeException('Bundle not found for this channel.');$this->db->where('id',$id)->update('db_communication_credit_packages',$data);}else $this->db->insert('db_communication_credit_packages',$data);
        $this->load->model('communications_model');$this->communications_model->event('bundle:'.bin2hex(random_bytes(12)),0,'Credit bundle changed',$name.'; '.$channel.'; by '.$actor.'. Existing requests keep their purchased conversion.');
    }
    public function minimum($channel){$r=$this->db->where('channel',$channel)->get('db_communication_credit_rules')->row_array();return (int)($r['minimum_kobo']??10000);}
    public function quote($channel,array $p,$rate){
        $this->guard();$id=(int)($p['package_id']??0);
        if($id){$b=$this->db->where('id',$id)->where('channel',$channel)->where('enabled',1)->get('db_communication_credit_packages')->row_array();if(!$b)throw new RuntimeException('This credit bundle is not available.');return array('amount'=>(int)$b['amount_kobo'],'units'=>(int)$b['credit_units'],'package_id'=>$id);}
        $amount=Messaging_pricing::money($p['amount']??'');$min=$this->minimum($channel);if($amount<$min || $amount>50000000)throw new InvalidArgumentException('Recharge between ₦'.number_format($min/100,2).' and ₦500,000.');$units=intdiv($amount,$rate);if($units<1)throw new InvalidArgumentException('This amount does not buy one credit.');return array('amount'=>$amount,'units'=>$units,'package_id'=>0);
    }
    public function requestPackage($id){$r=$this->db->where('request_id',$id)->get('db_communication_request_packages')->row_array();return (int)($r['package_id']??0);}
    public function snapshot($account){
        $s=$this->settings();$active=$this->db->select('r.sender_id')->from('db_communication_sender_assignments a')->join('db_communication_sender_requests r','r.id=a.request_id AND r.account_id=a.account_id')->where('a.account_id',$account)->where('r.status','approved')->get()->row_array();
        $s['approved_sender']=$s['sender_mode']==='hybrid'?($active['sender_id']??''):'';
        $s['sender_requests']=$this->db->select('id,sender_id,business_name,status,note,created_at,reviewed_at')->where('account_id',$account)->order_by('id','desc')->limit(20)->get('db_communication_sender_requests')->result_array();
        $s['documents']=$this->db->select('id,kind,reference_id,slot,filename')->where('account_id',$account)->order_by('id','desc')->limit(100)->get('db_communication_documents')->result_array();return $s;
    }
    public function effectiveSender($account){$s=$this->settings();if($s['sender_mode']==='hybrid'){$r=$this->db->select('r.sender_id')->from('db_communication_sender_assignments a')->join('db_communication_sender_requests r','r.id=a.request_id AND r.account_id=a.account_id')->where('a.account_id',$account)->where('r.status','approved')->get()->row_array();if($r)return $r['sender_id'];}return $s['shared_sender']?:null;}
    public function manualCredit(array $a,array $p){
        $this->guard();if(($p['purpose']??'')!=='recharge' || !in_array($p['module_key']??'',array('sms','email'),true))throw new InvalidArgumentException('Choose SMS or email credits.');
        $s=$this->settings();if(!$s['bank_account_number'])throw new RuntimeException('Your Administrator has not provided bank details. Contact Sales before transferring.');
        $this->load->library('communication_documents');Communication_documents::validate($p['receipt']??array());$this->load->model('communications_model');
        $this->db->trans_begin();try{
            $r=$this->communications_model->request($a,$p);
            if(!empty($r['payment_reference']) || in_array($r['status'],array('approved','rejected'),true))throw new RuntimeException('Use an unresolved manual request for this receipt.');
            $id=$this->communication_documents->store($a['id'],'receipt',$r['id'],'receipt',$p['receipt']);
            $this->communications_model->event('receipt:'.$r['id'],$a['id'],'Manual credit payment receipt — '.$a['store_name'],'Request #'.$r['id'].'; '.$r['module_key'].'; ₦'.number_format($r['amount_kobo']/100,2).'; '.$r['credit_units'].' credits. Receipt #'.$id.'. Contact: '.$r['contact_email'].'. Review the private receipt at '.base_url('communications_admin?account='.$a['id']).'. Confirm the bank payment before adding credits.');
            if(!$this->db->trans_status())throw new RuntimeException('Receipt request could not be saved.');$this->db->trans_commit();return $r;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function senderRequest(array $a,array $p){
        $this->guard();if($this->settings()['sender_mode']!=='hybrid')throw new RuntimeException('Your Administrator currently uses a shared Sender ID. Custom requests are not enabled.');
        $sender=trim($p['sender_id']??'');$business=trim($p['business_name']??'');$email=trim($p['email']??'');$sample=trim($p['sample_message']??'');$key=(string)($p['request_key']??'');
        if(!preg_match('/^(?=.*[a-zA-Z])[a-zA-Z0-9]{3,11}$/D',$sender) || !$business || !filter_var($email,FILTER_VALIDATE_EMAIL) || !$sample || strlen($sample)>2000 || !preg_match('/^[a-zA-Z0-9:_-]{16,100}$/D',$key))throw new InvalidArgumentException('Enter a 3–11 character Sender ID, business name, email and sample message.');
        $this->load->library('communication_documents');foreach(array('cac','authorization') as $slot)Communication_documents::validate($p[$slot]??array());
        $this->db->trans_begin();try{
            $this->db->query('SELECT id FROM db_messaging_accounts WHERE id=? FOR UPDATE',array($a['id']));
            $old=$this->db->where('account_id',$a['id'])->where('request_key',$key)->get('db_communication_sender_requests')->row_array();
            if($old){if($old['sender_id']!==$sender || $old['business_name']!==$business || $old['contact_email']!==$email || $old['sample_message']!==$sample)throw new RuntimeException('This request key belongs to different Sender ID details.');$id=(int)$old['id'];}
            else{
                if($this->db->where('account_id',$a['id'])->where('status','pending')->count_all_results('db_communication_sender_requests'))throw new RuntimeException('A Sender ID request is already awaiting review.');
                $this->db->insert('db_communication_sender_requests',array('account_id'=>$a['id'],'request_key'=>$key,'sender_id'=>$sender,'business_name'=>mb_substr($business,0,190),'contact_email'=>$email,'sample_message'=>$sample,'created_at'=>gmdate('Y-m-d H:i:s')));$id=$this->db->insert_id();
            }
            foreach(array('cac','authorization') as $slot)$this->communication_documents->store($a['id'],'sender',$id,$slot,$p[$slot]);
            $this->load->model('communications_model');$this->communications_model->event('sender:'.$id,$a['id'],'Custom SMS Sender ID request — '.$a['store_name'],'Sender ID '.$sender.'; business '.$business.'; contact '.$email.'. Review request #'.$id.' and its private documents at '.base_url('communications_admin?account='.$a['id']).'. Submit to the selected provider; approve only after provider/network confirmation.');
            if(!$this->db->trans_status())throw new RuntimeException('Sender ID request could not be saved.');$this->db->trans_commit();return $id;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function reviewSender($id,$status,$note,$actor){
        $this->guard();if(!in_array($status,array('approved','rejected','revoked'),true) || mb_strlen(trim($note))<5)throw new InvalidArgumentException('Record the provider confirmation/reference or rejection reason.');
        $r=$this->db->where('id',(int)$id)->get('db_communication_sender_requests')->row_array();if(!$r)throw new RuntimeException('Sender request not found.');
        $this->db->trans_begin();try{
            $this->db->query('SELECT id FROM db_messaging_accounts WHERE id=? FOR UPDATE',array($r['account_id']));$r=$this->db->where('id',$r['id'])->get('db_communication_sender_requests')->row_array();
            if($r['status']===$status){$this->db->trans_commit();return;}
            if(($status==='revoked' && $r['status']!=='approved') || ($status!=='revoked' && $r['status']!=='pending'))throw new RuntimeException('This Sender ID request has already been resolved.');
            if($status==='approved'){
                if($this->db->where('account_id',$r['account_id'])->where('kind','sender')->where('reference_id',$r['id'])->count_all_results('db_communication_documents')!==2)throw new RuntimeException('Both registration documents are required.');
                $this->db->query('INSERT INTO db_communication_sender_assignments (account_id,request_id) VALUES (?,?) ON DUPLICATE KEY UPDATE request_id=VALUES(request_id)',array($r['account_id'],$r['id']));
            }elseif($status==='revoked')$this->db->where('account_id',$r['account_id'])->where('request_id',$r['id'])->delete('db_communication_sender_assignments');
            $this->db->where('id',$r['id'])->update('db_communication_sender_requests',array('status'=>$status,'note'=>mb_substr($note,0,500),'reviewed_by'=>$actor,'reviewed_at'=>gmdate('Y-m-d H:i:s')));
            $this->load->model('communications_model');$this->communications_model->event('sender-review:'.bin2hex(random_bytes(12)),$r['account_id'],'SMS Sender ID '.$status,$r['sender_id'].'; by '.$actor.'. '.$note);
            if(!$this->db->trans_status())throw new RuntimeException('Sender review could not be saved.');$this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
}
