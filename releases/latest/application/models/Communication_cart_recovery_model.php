<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Cart reminders use the campaign pipeline, with an immutable cart/attempt binding. */
class Communication_cart_recovery_model extends CI_Model {
    public function ready(){return $this->db->table_exists('db_communication_cart_sends') && $this->db->table_exists('db_storefront_carts') && $this->db->field_exists('opt_out','db_storefront_carts') && $this->db->table_exists('db_storefront_cart_reminders') && $this->db->table_exists('db_storefront_settings');}
    public function settings($store){
        if(!$this->ready())throw new RuntimeException('Cart recovery is not available yet. Contact your Administrator.');
        $this->load->model('storefront_model');$s=$this->storefront_model->getSettings($store);
        if(!$s || empty($s->cart_recovery_enabled) || ($s->store_status??'')!=='active' || empty($s->store_slug))throw new RuntimeException('Enable cart tracking for an active online store before using this workflow.');return $s;
    }
    private function valid($cart,$hours,$ignoreOwnClaim=false){
        $items=json_decode((string)$cart->items_json,true);if(!is_array($items) || !$items)return false;
        $copy=clone $cart;if($ignoreOwnClaim){$copy->reminder_count=max(0,(int)$copy->reminder_count-1);$copy->reminder_sent_at=null;}
        return $this->storefront_model->cartReminderEligible($copy,$hours);
    }
    public function candidates($store,$channel){
        $s=$this->settings($store);$hours=max(1,(int)($s->abandoned_after_hours??24));$this->load->library('communication_audience');$this->load->model('marketing_model','marketing_m');$out=array();$seen=array();
        foreach($this->storefront_model->getAbandonedCartsForRecovery($hours,500,$store) as $cart){
            $recipient=Communication_audience::recipient($channel,$cart->{$channel==='sms'?'customer_phone':'customer_email'}??'');
            if(!$recipient || isset($seen[$recipient]))continue;$seen[$recipient]=true;if(!$this->valid($cart,$hours) || $this->marketing_m->isSuppressed($store,$channel,$recipient))continue;
            // One currently queued recovery per cart, even across workflow/channel choices.
            if($this->db->query("SELECT x.send_id FROM db_communication_cart_sends x JOIN db_campaign_sends s ON s.id=x.send_id WHERE x.store_id=? AND x.cart_id=? AND s.status IN ('pending','sending') LIMIT 1",array($store,$cart->id))->num_rows())continue;
            $out[]=array('cart'=>$cart,'recipient'=>$recipient);
        }return $out;
    }
    public function stage($campaign){
        $candidates=$this->candidates($campaign->store_id,$campaign->channel);$count=0;
        foreach($candidates as $candidate){$cart=$candidate['cart'];$slot=(int)$cart->reminder_count+1;
            $this->db->trans_begin();try{
                $this->db->query('INSERT IGNORE INTO db_campaign_sends (store_id,campaign_id,channel,recipient,customer_name,status,unsub_token,created_at) VALUES (?,?,?,?,?,\'pending\',?,NOW())',array($campaign->store_id,$campaign->id,$campaign->channel,$candidate['recipient'],mb_substr($cart->customer_name?:'there',0,190),bin2hex(random_bytes(32))));
                if($this->db->affected_rows()!==1){$this->db->trans_commit();continue;}$send=$this->db->insert_id();
                $this->db->query('INSERT IGNORE INTO db_communication_cart_sends (send_id,store_id,cart_id,reminder_slot) VALUES (?,?,?,?)',array($send,$campaign->store_id,$cart->id,$slot));
                if($this->db->affected_rows()!==1)$this->db->where('id',$send)->delete('db_campaign_sends');else $count++;
                if(!$this->db->trans_status())throw new RuntimeException('The cart reminder could not be queued.');$this->db->trans_commit();
            }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
        }
        $total=$this->db->where('campaign_id',$campaign->id)->count_all_results('db_campaign_sends');$this->db->where('id',$campaign->id)->where('store_id',$campaign->store_id)->update('db_campaigns',array('audience_count'=>$total));return $count;
    }
    public function context($campaign,$send){
        if(!$this->ready() || empty($send->id))return null;
        $meta=$this->db->where('send_id',$send->id)->where('store_id',$campaign->store_id)->get('db_communication_cart_sends')->row_array();if(!$meta)return null;
        $this->load->model('storefront_model');$cart=$this->storefront_model->getCartById($meta['cart_id'],$campaign->store_id);if(!$cart || !preg_match('/^[a-f0-9]{32,64}$/D',(string)$cart->cart_token))return null;
        $s=$this->settings($campaign->store_id);return array('meta'=>$meta,'cart'=>$cart,'url'=>base_url('store/'.rawurlencode($s->store_slug).'/cart?cart='.rawurlencode($cart->cart_token)));
    }
    public function claim($campaign,$send){
        $s=$this->settings($campaign->store_id);$this->load->model('storefront_model');$this->load->library('communication_audience');$this->db->trans_begin();try{
            $meta=$this->db->where('send_id',$send->id)->where('store_id',$campaign->store_id)->get('db_communication_cart_sends')->row_array();if(!$meta)throw new RuntimeException('This cart reminder has no recovery context.');
            $cart=$this->db->query('SELECT * FROM db_storefront_carts WHERE id=? AND store_id=? FOR UPDATE',array($meta['cart_id'],$campaign->store_id))->row();if(!$cart)throw new RuntimeException('Cart no longer exists.');
            $recipient=Communication_audience::recipient($campaign->channel,$cart->{$campaign->channel==='sms'?'customer_phone':'customer_email'}??'');
            $own=!empty($meta['claimed_at']) && (int)$cart->reminder_count===(int)$meta['reminder_slot'] && $cart->reminder_sent_at===$meta['claimed_at'];
            if($recipient!==$send->recipient || !$this->valid($cart,max(1,(int)($s->abandoned_after_hours??24)),$own))throw new RuntimeException('Cart no longer qualifies for this reminder.');
            if(!$own){
                if((int)$cart->reminder_count+1!==(int)$meta['reminder_slot'] || !$this->storefront_model->claimCartReminder($cart->id,$campaign->store_id,$campaign->channel,'auto',$recipient,'Automation campaign #'.$campaign->id,'automation'))throw new RuntimeException('Another reminder already used this cart slot.');
                $fresh=$this->storefront_model->getCartById($cart->id,$campaign->store_id);$this->db->where('send_id',$send->id)->update('db_communication_cart_sends',array('claimed_at'=>$fresh->reminder_sent_at));
            }
            if(!$this->db->trans_status())throw new RuntimeException('Cart reminder could not be claimed.');$this->db->trans_commit();return true;
        }catch(Throwable $e){$this->db->trans_rollback();return false;}
    }
    public function releaseUnsent($store,$workflow){
        if(!$this->ready())return;
        $rows=$this->db->query("SELECT x.send_id FROM db_communication_cart_sends x JOIN db_campaign_sends s ON s.id=x.send_id JOIN db_communication_automation_runs r ON r.campaign_id=s.campaign_id WHERE x.store_id=? AND r.automation_id=? AND x.claimed_at IS NULL AND s.status='pending'",array($store,$workflow))->result_array();
        foreach($rows as $row){$this->db->where('id',$row['send_id'])->where('status','pending')->update('db_campaign_sends',array('status'=>'suppressed','error'=>'Cart workflow paused before sending.'));$this->db->where('send_id',$row['send_id'])->where('claimed_at IS NULL',null,false)->delete('db_communication_cart_sends');}
    }
}
