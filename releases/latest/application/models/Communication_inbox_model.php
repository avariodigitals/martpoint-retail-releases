<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Read the existing conversation history in place, scoped to its store/channel. */
class Communication_inbox_model extends CI_Model {
    public function ready(){
        foreach(array('db_messaging_conversations'=>array('id','store_id','channel','last_message_at','customer_name','customer_phone','external_conversation_id','status'),'db_communication_inbox_messages'=>array('id','conversation_id','body','direction','created_at'),'db_communication_inbox_replies'=>array('request_key','status')) as $table=>$fields){if(!$this->db->table_exists($table))return false;foreach($fields as $field)if(!$this->db->field_exists($field,$table))return false;}return true;
    }
    public static function channel($value){return in_array($value,array('whatsapp','instagram','messenger'),true)?$value:'whatsapp';}
    public static function workspace($channel){return $channel==='whatsapp'?'whatsapp':'socials';}
    public function connection($store,$channel){
        $this->load->model('storefront_model');$s=$this->storefront_model->getSettings($store);$configured=false;
        if($channel==='whatsapp')$configured=!empty($s->whatsapp_api_enabled) && !empty($s->whatsapp_api_token) && !empty($s->whatsapp_phone_number_id);
        elseif($channel==='instagram')$configured=!empty($s->instagram_access_token) && !empty($s->instagram_business_account_id);
        elseif($channel==='messenger')$configured=!empty($s->facebook_page_access_token) && !empty($s->facebook_page_id);
        return array('configured'=>$configured); // Never expose credentials to the workspace.
    }
    public function conversations($store,$channel,$page=0){
        if(!$this->ready())return array();$channel=self::channel($channel);$page=max(0,min(10000,(int)$page));
        return $this->db->query('SELECT c.*,(SELECT m.body FROM db_communication_inbox_messages m WHERE m.conversation_id=c.id ORDER BY m.id DESC LIMIT 1) AS preview FROM db_messaging_conversations c WHERE c.store_id=? AND c.channel=? ORDER BY c.last_message_at DESC,c.id DESC LIMIT 51 OFFSET '.($page*50),array((int)$store,$channel))->result_array();
    }
    public function conversation($store,$id,$channel=null){
        if(!$this->ready())return null;$this->db->where('store_id',(int)$store)->where('id',(int)$id);if($channel!==null)$this->db->where('channel',self::channel($channel));return $this->db->get('db_messaging_conversations')->row_array();
    }
    public function messages($store,$id,$channel,$before=0){
        if(!$this->conversation($store,$id,$channel))throw new RuntimeException('Conversation not found for this account.');
        $this->db->where('conversation_id',(int)$id);if((int)$before>0)$this->db->where('id <',(int)$before);
        return $this->db->order_by('id','desc')->limit(101)->get('db_communication_inbox_messages')->result_array();
    }
    protected function deliver($store,$conv,$text){
        $this->load->library('meta_messaging');$service=new Meta_messaging($store);
        if($conv['channel']==='whatsapp')return $service->sendWhatsappText($conv['customer_phone'],$text);
        if($conv['channel']==='instagram')return $service->sendInstagramText($conv['external_conversation_id'],$text);
        return $service->sendMessengerText($conv['external_conversation_id'],$text);
    }
    public function reply($store,$id,$channel,$text,$key){
        if(!$this->ready())throw new RuntimeException('The inbox is not available yet. Contact your Administrator.');
        $text=trim((string)$text);if(!$text || mb_strlen($text)>4000 || !preg_match('//u',$text) || !preg_match('/^[a-f0-9]{40}$/D',(string)$key))throw new InvalidArgumentException('Enter a reply of up to 4,000 characters and reload the conversation if needed.');
        $conv=$this->conversation($store,$id,$channel);if(!$conv)throw new RuntimeException('Conversation not found for this account.');
        $connection=$this->connection($store,$channel);
        $hash=hash('sha256',$id."\n".$channel."\n".$text);
        $this->db->trans_begin();try{
            $this->db->query('SELECT id FROM db_messaging_conversations WHERE store_id=? AND id=? FOR UPDATE',array((int)$store,(int)$id));
            $old=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_communication_inbox_replies')->row_array();
            if($old){if(!hash_equals($old['payload_hash'],$hash))throw new RuntimeException('This reply key belongs to a different message. Reload the conversation.');$this->db->trans_commit();return array('success'=>$old['status']==='sent','message'=>$old['status']==='sent'?'Reply already sent.':'This reply was already submitted. Check its delivery before trying again.');}
            if(empty($connection['configured']))throw new RuntimeException('This channel is not configured. Contact your Administrator.');
            $this->db->insert('db_communication_inbox_replies',array('store_id'=>$store,'conversation_id'=>$id,'request_key'=>$key,'payload_hash'=>$hash,'created_at'=>gmdate('Y-m-d H:i:s')));$replyId=$this->db->insert_id();if(!$this->db->trans_status())throw new RuntimeException('The reply could not be recorded.');$this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
        // A retry of a pending/unknown request never makes another provider call.
        try{$r=$this->deliver($store,$conv,$text);}catch(Throwable $e){$r=array('ok'=>false,'http_code'=>0);}
        if(empty($r['ok'])){$unknown=empty($r['http_code']) || (int)$r['http_code']>=500;$this->db->where('id',$replyId)->update('db_communication_inbox_replies',array('status'=>$unknown?'unknown':'failed'));return array('success'=>false,'message'=>$unknown?'Reply delivery is awaiting confirmation. Check the conversation before sending again.':'The reply could not be sent. Ask your Administrator to check this channel.');}
        $this->db->trans_begin();$ok=$this->db->insert('db_communication_inbox_messages',array('conversation_id'=>$id,'direction'=>'outbound','message_type'=>'text','body'=>$text,'meta_message_id'=>$r['data']['messages'][0]['id']??$r['data']['message_id']??null,'status'=>'sent','created_at'=>date('Y-m-d H:i:s')));
        $this->db->where('id',$id)->where('store_id',$store)->update('db_messaging_conversations',array('last_message_at'=>date('Y-m-d H:i:s'),'status'=>'open'));
        $this->db->where('id',$replyId)->update('db_communication_inbox_replies',array('status'=>'sent'));
        if(!$ok || !$this->db->trans_status()){$this->db->trans_rollback();$this->db->where('id',$replyId)->update('db_communication_inbox_replies',array('status'=>'unknown'));return array('success'=>false,'message'=>'The service accepted the reply, but its history needs confirmation. Contact your Administrator before resending.');}$this->db->trans_commit();return array('success'=>true,'message'=>'Reply sent.');
    }
}
