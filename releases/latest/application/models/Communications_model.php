<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Central authority. Mutations share the messaging account row lock. */
class Communications_model extends CI_Model {
    public static $modules = array('sms'=>'SMS','email'=>'Email','whatsapp'=>'WhatsApp','socials'=>'Socials','automation'=>'Automation');
    public function ready() {
        foreach(array('db_communication_fleet_modules','db_communication_catalogue','db_communication_settings','db_communication_modules','db_communication_wallets','db_communication_ledger','db_communication_requests','db_communication_events','db_communication_budgets','db_messaging_accounts','db_messaging_settings','db_messaging_dispatches') as $table)if(!$this->db->table_exists($table))return false;
        return true;
    }
    private function central() { if (!mp_is_central() || !$this->ready()) throw new RuntimeException('Communications is not available yet. Contact your Administrator.'); }
    public function review(){ $this->load->model('communication_review_model');$ci =& get_instance();return isset($ci->communication_review_model) && $ci->communication_review_model->ready()?$ci->communication_review_model:null; }
    public function catalogue() { $out=array(); foreach($this->db->get('db_communication_catalogue')->result_array() as $r) { $r['packages']=json_decode($r['packages_json'],true) ?: array(); $r['name']=self::$modules[$r['module_key']]; $r['development']=in_array($r['module_key'],array('whatsapp','socials'),true); $out[$r['module_key']]=$r; } $review=$this->review();if($review)foreach(array('sms','email') as $key)if(isset($out[$key])){$out[$key]['minimum_kobo']=$review->minimum($key);$out[$key]['credit_bundles']=$review->bundles($key);}return $out; }
    public function provision($id) {
        foreach(self::$modules as $key=>$name) $this->db->query('INSERT IGNORE INTO db_communication_modules (account_id,module_key) VALUES (?,?)',array($id,$key));
        foreach(array('sms','email') as $channel) $this->db->query('INSERT IGNORE INTO db_communication_wallets (account_id,channel) VALUES (?,?)',array($id,$channel));
    }
    public function fleetAllowed($id,$key) {
        $row=$this->db->query('SELECT f.allowed FROM db_messaging_accounts a JOIN db_communication_fleet_modules f ON f.fleet_id=a.fleet_id AND f.module_key=? WHERE a.id=?',array($key,$id))->row_array();
        return !$row || !empty($row['allowed']);
    }
    public function allowFleet($fleetId,$key,$allowed,$actor) {
        $this->central();if(!isset(self::$modules[$key]) || in_array($key,array('whatsapp','socials'),true))throw new RuntimeException('This module is under development.');
        if(!$this->db->where('id',(int)$fleetId)->count_all_results('db_fleet_installs'))throw new RuntimeException('Registered fleet not found.');
        $this->db->trans_begin();try{
            $this->db->query('SELECT id FROM db_fleet_installs WHERE id=? FOR UPDATE',array((int)$fleetId));
            $this->db->query('INSERT INTO db_communication_fleet_modules (fleet_id,module_key,allowed,updated_by,updated_at) VALUES (?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE allowed=VALUES(allowed),updated_by=VALUES(updated_by),updated_at=VALUES(updated_at)',array((int)$fleetId,$key,$allowed?1:0,$actor));
            $this->event('fleet:'.bin2hex(random_bytes(12)),0,'Fleet Communications availability changed','Fleet #'.(int)$fleetId.'; '.$key.'; '.($allowed?'allowed':'disabled').'; by '.$actor);$this->commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function access($id,$key) {
        $this->central();
        $row=$this->db->query('SELECT m.*,c.enabled FROM db_communication_modules m JOIN db_communication_catalogue c ON c.module_key=m.module_key WHERE m.account_id=? AND m.module_key=?',array($id,$key))->row_array();
        return $row && $this->fleetAllowed($id,$key) && !in_array($key,array('whatsapp','socials'),true) && (int)$row['allowed'] && (int)$row['enabled'] && (int)$row['merchant_enabled'] && !empty($row['subscribed_until']) && strtotime($row['subscribed_until'].' UTC')>time();
    }
    public function requireAccess($id,$key) { if(!$this->access($id,$key)) throw new RuntimeException('Your '.(self::$modules[$key]??'Communications').' module is disabled, awaiting Administrator activation, or expired. Open Communications to subscribe.'); }
    private function lock($id) { $a=$this->db->query('SELECT * FROM db_messaging_accounts WHERE id=? FOR UPDATE',array($id))->row_array(); if(!$a)throw new RuntimeException('Account not found.'); return $a; }
    private function commit() { if(!$this->db->trans_status())throw new RuntimeException('Communications could not be saved.'); $this->db->trans_commit(); }
    public function snapshot($id,$page=0,$channel=null) {
        $this->central();$this->provision($id);$mods=array();
        foreach($this->db->where('account_id',$id)->get('db_communication_modules')->result_array() as $r){$r['fleet_allowed']=$this->fleetAllowed($id,$r['module_key']);$r['allowed']=$r['allowed'] && $r['fleet_allowed']?1:0;$r['active']=$this->access($id,$r['module_key']);$mods[$r['module_key']]=$r;}
        $wallets=array();foreach($this->db->where('account_id',$id)->get('db_communication_wallets')->result_array() as $r)$wallets[$r['channel']]=$r;
        $settings=$this->db->where('id',1)->get('db_communication_settings')->row_array();
        $page=max(0,min(100000,(int)$page));$this->db->where('account_id',$id);
        if(in_array($channel,array('email','sms'),true))$this->db->where('channel',$channel);
        $history=$this->db->order_by('id','desc')->limit(101,$page*100)->get('db_communication_ledger')->result_array();$more=count($history)>100;
        $auto=$this->automationPlans();
        $requests=$this->db->select('r.*,p.checkout_url')->from('db_communication_requests r')->join('db_messaging_purchases p','p.reference=r.payment_reference COLLATE utf8mb4_unicode_ci','left',false)->where('r.account_id',$id)->order_by('r.id','desc')->limit(50)->get()->result_array();
        if($auto)foreach($requests as &$request)if($request['module_key']==='automation')$request['automation_plan_key']=$auto->requestPlan($request['id']);unset($request);
        return array('review'=>$this->review()?$this->review()->snapshot($id):array(),'automation'=>$auto?$auto->snapshot($id):array('available'=>false,'message'=>'Automation plans are not available yet. Contact your Administrator.'),'history_page'=>$page,'history_has_more'=>$more,'tax_bps'=>$this->taxBps(),'catalogue'=>$this->catalogue(),'modules'=>$mods,'wallets'=>$wallets,'bank_instructions'=>$settings['bank_instructions'],
            'requests'=>$requests,
            'ledger'=>array_slice($history,0,100));
    }
    public function automationPlans($required=false){
        $this->load->model('communication_automation_plans_model');$ci =& get_instance();
        $model=isset($ci->communication_automation_plans_model)?$ci->communication_automation_plans_model:null;
        if($model && $model->ready())return $model;
        if($required)throw new RuntimeException('Automation plans are not available yet. Contact your Administrator.');return null;
    }
    public function rate($key) { $r=$this->db->where('module_key',$key)->get('db_communication_catalogue')->row_array();return max(1,(int)$r['credit_price_kobo']); }
    /** VAT basis points Central charges on subscriptions, recharges and add-ons. */
    public function taxBps() { return (int)$this->db->select('tax_bps')->where('id',1)->get('db_messaging_settings')->row()->tax_bps; }
    public function vat($netKobo) { return Messaging_pricing::vat($netKobo,$this->taxBps()); }
    public function units($id,$channel) { $w=$this->db->where('account_id',$id)->where('channel',$channel)->get('db_communication_wallets')->row_array();return (int)($w['balance_units']??0); }
    public function saveCatalogue(array $p,$actor) {
        $this->central();$key=$p['module_key']??'';if(!isset(self::$modules[$key]))throw new InvalidArgumentException('Choose a module.');
        $monthly=Messaging_pricing::money($p['monthly']??'0');$annual=Messaging_pricing::money($p['annual']??'0');$rate=Messaging_pricing::money($p['credit_price']??'1');
        if($rate<1 || $rate>10000000)throw new InvalidArgumentException('Enter a credit price between ₦0.01 and ₦100,000.');
        $packages=array();foreach(preg_split('/[,\s]+/',trim($p['packages']??''),-1,PREG_SPLIT_NO_EMPTY) as $n){$v=Messaging_pricing::money($n);if($v<10000 || $v>50000000)throw new InvalidArgumentException('Recharge packages must be between ₦100 and ₦500,000.');$packages[]=$v/100;}
        if(count($packages)>12)throw new InvalidArgumentException('Use at most 12 recharge packages.');
        $review=$this->review();if($review && in_array($key,array('sms','email'),true) && isset($p['minimum_topup'])){$min=Messaging_pricing::money($p['minimum_topup']);if($min<10000 || $min>50000000)throw new InvalidArgumentException('Minimum top-up must be between ₦100 and ₦500,000.');$this->db->where('channel',$key)->update('db_communication_credit_rules',array('minimum_kobo'=>$min));}
        $this->db->where('module_key',$key)->update('db_communication_catalogue',array('monthly_kobo'=>$monthly,'annual_kobo'=>$annual,'credit_price_kobo'=>$rate,'packages_json'=>json_encode(array_values(array_unique($packages))),'enabled'=>!in_array($key,array('whatsapp','socials'),true) && !empty($p['enabled'])?1:0));
        $this->event('catalogue:'.bin2hex(random_bytes(12)),0,'Communications catalogue updated','Module: '.$key.'. Updated by '.$actor.'. Monthly: '.$monthly.' kobo; annual: '.$annual.' kobo; credit: '.$rate.' kobo.');
    }
    public function request(array $a,array $p) {
        $this->central();$id=$a['id'];$key=$p['module_key']??'';$purpose=$p['purpose']??'subscription';$catalogue=$this->catalogue();$c=$catalogue[$key]??null;
        if(!$c || $c['development'] || !$c['enabled'])throw new RuntimeException('This module is not available.');
        if(!in_array($purpose,array('subscription','recharge'),true))throw new InvalidArgumentException('Choose subscription or recharge.');
        $email=trim((string)($p['email']??''));if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Enter a contact email.');
        $requestKey=(string)($p['request_key']??'');if(!preg_match('/^[a-zA-Z0-9:_-]{16,100}$/D',$requestKey))throw new InvalidArgumentException('Reload this form before submitting.');
        $period=$p['period']??'monthly';$units=0;$autoPlan=null;$review=$this->review();$packageId=0;$net=0;
        if($key==='automation'){$policy=$this->automationPlans(true);$autoPlan=$policy->plan($p['plan_key']??'plan_a');}
        if($purpose==='subscription'){
            if(!in_array($period,array('monthly','annual'),true))throw new InvalidArgumentException('Choose monthly or annual.');
            $net=(int)($autoPlan?$autoPlan[$period.'_kobo']:$c[$period.'_kobo']);if($net<1)throw new RuntimeException('Your Administrator has not set a subscription price for this module yet.');
        }else{
            if(!in_array($key,array('sms','email'),true))throw new InvalidArgumentException('Only SMS and email have credits.');
            if($review){$quote=$review->quote($key,$p,(int)$c['credit_price_kobo']);$net=$quote['amount'];$units=$quote['units'];$packageId=$quote['package_id'];}
            else{if(!empty($p['package_id']))throw new RuntimeException('Credit bundles are not available yet. Contact your Administrator.');$net=Messaging_pricing::money($p['amount']??'');if($net<10000 || $net>50000000)throw new InvalidArgumentException('Recharge between ₦100 and ₦500,000.');$units=intdiv($net,(int)$c['credit_price_kobo']);if($units<1)throw new InvalidArgumentException('This amount does not buy one credit.');}$period=null;
        }
        // The store is charged net + VAT; credit units always come from the net price.
        $vat=$this->vat($net);$amount=(int)$vat['total_kobo'];
        $this->db->trans_begin();try{
            $this->lock($id);$this->provision($id);
            $m=$this->db->where('account_id',$id)->where('module_key',$key)->get('db_communication_modules')->row_array();if(!$m['allowed'] || !$this->fleetAllowed($id,$key))throw new RuntimeException('Your Administrator has disabled this module for your account.');
            if($purpose==='recharge')$this->requireAccess($id,$key);
            $old=$this->db->where('account_id',$id)->where('request_key',$requestKey)->get('db_communication_requests')->row_array();
            if($old){if($purpose==='recharge' && $review && $review->requestPackage($old['id'])!==$packageId)throw new RuntimeException('This request key belongs to another credit bundle.');if($autoPlan && $policy->requestPlan($old['id'])!==$autoPlan['plan_key'])throw new RuntimeException('This request key belongs to another Automation plan.');if($old['module_key']!==$key || $old['purpose']!==$purpose || $old['billing_period']!==$period || (int)($old['net_kobo']?:$old['amount_kobo'])!==$net)throw new RuntimeException('This request key belongs to another purchase.');$this->commit();return $old;}
            // Reuse an unresolved manual request instead of creating repeated sales alerts.
            $pending=$this->db->where('account_id',$id)->where('module_key',$key)->where('purpose',$purpose)->where('billing_period',$period)->where('amount_kobo',$amount)->where_in('status',array('requested','awaiting_payment','paid'))->order_by('id','desc')->get('db_communication_requests')->row_array();
            if($pending && (int)$pending['credit_units']===$units && (!$review || $review->requestPackage($pending['id'])===$packageId) && (!$autoPlan || $policy->requestPlan($pending['id'])===$autoPlan['plan_key'])){$this->commit();return $pending;}
            $row=array('account_id'=>$id,'request_key'=>$requestKey,'module_key'=>$key,'purpose'=>$purpose,'billing_period'=>$period,'amount_kobo'=>$amount,'net_kobo'=>$vat['net_kobo'],'tax_bps'=>$vat['tax_bps'],'tax_kobo'=>$vat['tax_kobo'],'total_kobo'=>$vat['total_kobo'],'credit_units'=>$units,'contact_email'=>$email,'created_at'=>gmdate('Y-m-d H:i:s'));
            if(!$this->db->insert('db_communication_requests',$row))throw new RuntimeException('Could not record the request.');$row['id']=$this->db->insert_id();$row['status']='requested';
            if($packageId)$this->db->insert('db_communication_request_packages',array('request_id'=>$row['id'],'package_id'=>$packageId));
            if($autoPlan)$policy->bindRequest($row['id'],$autoPlan['plan_key']);
            if($purpose==='subscription')$this->db->where('account_id',$id)->where('module_key',$key)->update('db_communication_modules',array('merchant_enabled'=>1));
            $this->event('request:'.$row['id'],$id,'Communications '.$purpose.' request — '.$a['store_name'],'Request #'.$row['id'].'; '.$key.'; '.($autoPlan?$autoPlan['name'].' ('.$autoPlan['max_workflows'].' workflows); ':'').($period?:'credits').'; net ₦'.number_format($vat['net_kobo']/100,2).'; VAT ₦'.number_format($vat['tax_kobo']/100,2).' ('.$vat['tax_bps'].' bps); total ₦'.number_format($vat['total_kobo']/100,2).'; credits '.$units.'. Contact: '.$email.'. Open '.base_url('communications_admin?account='.$id));
            $this->commit();return $row;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function toggle($id,$key,$enabled) {
        $this->central();if(!isset(self::$modules[$key]) || in_array($key,array('whatsapp','socials'),true))throw new RuntimeException('This module is under development.');
        $this->db->trans_begin();try{$this->lock($id);$this->provision($id);$m=$this->db->where('account_id',$id)->where('module_key',$key)->get('db_communication_modules')->row_array();
            if($enabled && (!$this->fleetAllowed($id,$key) || !$m['allowed'] || !$this->catalogue()[$key]['enabled']))throw new RuntimeException('Your Administrator has disabled this module.');
            if($enabled && (!$m['subscribed_until'] || strtotime($m['subscribed_until'].' UTC')<=time()))throw new RuntimeException('Select a monthly or annual subscription first.');
            if((bool)$m['merchant_enabled']===(bool)$enabled){$this->commit();return;}
            $this->db->where('account_id',$id)->where('module_key',$key)->update('db_communication_modules',array('merchant_enabled'=>$enabled?1:0));
            $this->event('toggle:'.bin2hex(random_bytes(12)),$id,'Communications preference changed',$key.' switched '.($enabled?'on':'off').' by the fleet.');$this->commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function activate($id,$key,$period,$source,$allowed,$actor,$note,$operationKey=null,$planKey=null) {
        $this->central();if(!isset(self::$modules[$key]) || in_array($key,array('whatsapp','socials'),true))throw new RuntimeException('This module is under development.');
        if(!in_array($period,array('monthly','annual'),true) || !in_array($source,array('manual_payment','annual_bill','complimentary'),true))throw new InvalidArgumentException('Choose a billing period and billing source.');
        if(mb_strlen(trim($note))<5)throw new InvalidArgumentException('Record the payment or billing reason.');
        $this->db->trans_begin();try{$this->lock($id);$this->provision($id);
            if($operationKey!==null){if(!preg_match('/^[a-f0-9]{40}$/D',$operationKey))throw new RuntimeException('Reload the activation form.');if($this->db->where('event_key','direct:'.$operationKey)->count_all_results('db_communication_events')){$this->commit();return;}}
            if(!$this->fleetAllowed($id,$key))throw new RuntimeException('Enable this module for the fleet before activating.');
            $this->grantSubscription($id,$key,$period,$source,$allowed,$actor,$planKey);
            $this->event($operationKey?'direct:'.$operationKey:'activate:'.bin2hex(random_bytes(12)),$id,'Communications activated',$key.'; '.$period.'; source '.$source.'; by '.$actor.'. '.$note);$this->commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    private function grantSubscription($id,$key,$period,$source,$allowed,$actor,$planKey=null) {
        if($key==='automation'){$policy=$this->automationPlans(true);$policy->assign($id,$planKey??$policy->assigned($id));}
        $m=$this->db->where('account_id',$id)->where('module_key',$key)->get('db_communication_modules')->row_array();
        $start=max(time(),empty($m['subscribed_until'])?0:strtotime($m['subscribed_until'].' UTC'));
        $this->db->where('account_id',$id)->where('module_key',$key)->update('db_communication_modules',array('allowed'=>$allowed?1:0,'merchant_enabled'=>1,'subscribed_until'=>gmdate('Y-m-d H:i:s',$start+($period==='annual'?365:30)*86400),'billing_period'=>$period,'billing_source'=>$source,'updated_by'=>$actor,'updated_at'=>gmdate('Y-m-d H:i:s')));
    }
    public function allow($id,$key,$allowed,$actor) {
        $this->central();if(!isset(self::$modules[$key]) || in_array($key,array('whatsapp','socials'),true))throw new RuntimeException('This module is under development.');
        $this->db->trans_begin();try{$this->lock($id);$this->provision($id);$this->db->where('account_id',$id)->where('module_key',$key)->update('db_communication_modules',array('allowed'=>$allowed?1:0,'updated_by'=>$actor,'updated_at'=>gmdate('Y-m-d H:i:s')));$this->event('allow:'.bin2hex(random_bytes(12)),$id,'Communications availability changed',$key.' '.($allowed?'enabled':'disabled').' by '.$actor);$this->commit();}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function resolve($requestId,$approve,$actor,$note) {
        $this->central();$r=$this->db->where('id',(int)$requestId)->get('db_communication_requests')->row_array();if(!$r)throw new RuntimeException('Request not found.');
        if(mb_strlen(trim($note))<5)throw new InvalidArgumentException('Record payment evidence or a reason.');
        $this->db->trans_begin();try{$this->lock($r['account_id']);$r=$this->db->where('id',$r['id'])->get('db_communication_requests')->row_array();
            if(in_array($r['status'],array('approved','rejected'),true)){$this->commit();return;}
            if(!empty($r['payment_reference']) && $r['status']!=='paid')throw new RuntimeException('Verify the card payment before approving or rejecting this request.');
            if(!$approve && $r['status']==='paid')throw new RuntimeException('A verified payment needs fulfilment. Refund at the provider before resolving outside this workflow.');
            if($approve){$m=$this->db->where('account_id',$r['account_id'])->where('module_key',$r['module_key'])->get('db_communication_modules')->row_array();if(!$m || !$m['allowed'] || !$this->fleetAllowed($r['account_id'],$r['module_key']) || !$this->catalogue()[$r['module_key']]['enabled'])throw new RuntimeException('Enable this module before approving.');
                if($r['purpose']==='subscription')$this->grantSubscription($r['account_id'],$r['module_key'],$r['billing_period'],$r['status']==='paid'?'card':'manual_payment',true,$actor,$r['module_key']==='automation'?$this->automationPlans(true)->requestPlan($r['id']):null);
                else $this->changeUnits($r['account_id'],$r['module_key'],(int)$r['credit_units'],'request:'.$r['id'],null,'Manual payment confirmed: '.$note,$actor,true);
            }
            $this->db->where('id',$r['id'])->update('db_communication_requests',array('status'=>$approve?'approved':'rejected','note'=>mb_substr($note,0,500),'resolved_by'=>$actor,'resolved_at'=>gmdate('Y-m-d H:i:s')));
            $this->event('resolve:'.$r['id'],$r['account_id'],'Communications request '.($approve?'approved':'rejected'),'Request #'.$r['id'].'; '.$r['module_key'].'; by '.$actor.'. '.$note);$this->commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    /** Called inside the verified purchase transaction with the account lock held. */
    public function paid(array $purchase) {
        $r=$this->db->where('payment_reference',$purchase['reference'])->get('db_communication_requests')->row_array();if(!$r)return false;
        if($r['status']==='approved' || $r['status']==='paid')return true;
        if($r['purpose']==='recharge')$this->changeUnits($r['account_id'],$r['module_key'],(int)$r['credit_units'],'request:'.$r['id'],null,'Verified card recharge.','payment',true);
        $this->db->where('id',$r['id'])->update('db_communication_requests',array('status'=>$r['purpose']==='recharge'?'approved':'paid','resolved_at'=>$r['purpose']==='recharge'?gmdate('Y-m-d H:i:s'):null));
        $this->event('paid:'.$r['id'],$r['account_id'],'Communications payment verified','Request #'.$r['id'].'; '.$r['module_key'].'; '.$r['purpose'].'. '.($r['purpose']==='subscription'?'Administrator activation is required.':'Credits added.'));return true;
    }
    /** All credit changes execute in the caller's transaction under account lock. */
    public function changeUnits($id,$channel,$delta,$event,$messageId=null,$note='',$actor=null,$purchase=false) {
        $old=$this->db->where('account_id',$id)->where('event_key',$event)->get('db_communication_ledger')->row_array();if($old)return;
        $w=$this->db->query('SELECT * FROM db_communication_wallets WHERE account_id=? AND channel=? FOR UPDATE',array($id,$channel))->row_array();
        if(!$w || (int)$w['balance_units']+$delta<0)throw new RuntimeException('Insufficient '.strtoupper($channel).' credits. Open Communications to recharge.');
        $balance=(int)$w['balance_units']+$delta;
        $this->db->where('account_id',$id)->where('channel',$channel)->update('db_communication_wallets',array('balance_units'=>$balance,'purchased_units'=>(int)$w['purchased_units']+($purchase?max(0,$delta):0)));
        if(!$this->db->insert('db_communication_ledger',array('account_id'=>$id,'channel'=>$channel,'event_key'=>$event,'units'=>$delta,'balance_units'=>$balance,'message_id'=>$messageId,'note'=>mb_substr($note,0,500),'actor'=>$actor,'created_at'=>gmdate('Y-m-d H:i:s'))))throw new RuntimeException('Could not record credits.');
    }
    public function reserve($id,$channel,$units,$messageId,$campaignId) {
        $this->requireAccess($id,$channel);
        if($campaignId!==''){
            $b=$this->db->where('account_id',$id)->where('campaign_id',$campaignId)->get('db_communication_budgets')->row_array();
            if(!$b || $b['channel']!==$channel || (int)$b['used_units']+$units>(int)$b['budget_units'])throw new RuntimeException('The campaign credit limit has been reached.');
            $this->db->query('UPDATE db_communication_budgets SET used_units=used_units+? WHERE account_id=? AND campaign_id=?',array($units,$id,$campaignId));
        }
        $this->changeUnits($id,$channel,-$units,'reserve:'.$messageId,$messageId,'Message credit reservation.');
    }
    public function refund(array $m,$actor) {
        $r=$this->db->where('account_id',$m['account_id'])->where('event_key','reserve:'.$m['id'])->get('db_communication_ledger')->row_array();if(!$r)return false;
        $this->changeUnits($m['account_id'],$m['channel'],(int)$m['units'],'refund:'.$m['id'],$m['id'],'Provider confirmed rejection.',$actor);
        if(!empty($m['campaign_id']))$this->db->query('UPDATE db_communication_budgets SET used_units=GREATEST(0,used_units-?) WHERE account_id=? AND campaign_id=?',array($m['units'],$m['account_id'],$m['campaign_id']));return true;
    }
    public function isCreditMessage($id) { return $this->db->where('message_id',$id)->where('units <',0)->count_all_results('db_communication_ledger')>0; }
    public function authorize($id,array $p) {
        $channel=$p['channel']??'';$campaign=(string)($p['campaign_id']??'');$units=(int)($p['budget_units']??0);
        if(!in_array($channel,array('email','sms'),true) || !preg_match('/^[0-9]{1,18}$/D',$campaign) || $units<1)throw new InvalidArgumentException('Confirm the channel and credit estimate.');
        $this->db->trans_begin();try{$this->lock($id);$this->requireAccess($id,$channel);if($units>$this->units($id,$channel))throw new RuntimeException('Recharge before authorizing this campaign.');
            $this->db->query('INSERT INTO db_communication_budgets (account_id,campaign_id,channel,budget_units) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE budget_units=used_units+VALUES(budget_units)',array($id,$campaign,$channel,$units));
            $b=$this->db->where('account_id',$id)->where('campaign_id',$campaign)->get('db_communication_budgets')->row_array();if($b['channel']!==$channel)throw new RuntimeException('Campaign channel changed. Create a new campaign.');$this->commit();return array('success'=>true,'message'=>'Campaign credit limit approved.');
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function event($key,$id,$subject,$body) { $this->db->query('INSERT IGNORE INTO db_communication_events (event_key,account_id,subject,body,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())',array($key,$id,$subject,$body)); }
    /** Durable outbox; Central cron retries failures without needing an admin login. */
    public function notify($limit=5) {
        $this->central();$settings=$this->db->where('id',1)->get('db_communication_settings')->row_array();$provider=$this->db->where('id',1)->get('db_messaging_settings')->row_array();
        $rows=$this->db->where('delivered_at IS NULL',null,false)->group_start()->where('retry_at IS NULL',null,false)->or_where('retry_at <',gmdate('Y-m-d H:i:s'))->group_end()->order_by('id')->limit($limit)->get('db_communication_events')->result_array();
        $this->load->model('email_service');$this->email_service->setStoreId($provider['provider_store_id']);$sent=0;
        foreach($rows as $r){
            $this->db->where('id',$r['id'])->where('delivered_at IS NULL',null,false)->group_start()->where('retry_at IS NULL',null,false)->or_where('retry_at <',gmdate('Y-m-d H:i:s'))->group_end()->update('db_communication_events',array('retry_at'=>gmdate('Y-m-d H:i:s',time()+600),'attempts'=>(int)$r['attempts']+1));if($this->db->affected_rows()!==1)continue;
            $recipients=array_values(array_unique(array_filter(array($settings['sales_email'],$settings['technical_email']),function($e){return filter_var($e,FILTER_VALIDATE_EMAIL);})));if(!$recipients)continue;
            try{$result=$this->email_service->sendRaw($recipients,$r['subject'],'<p>'.nl2br(htmlspecialchars($r['body'],ENT_QUOTES,'UTF-8')).'</p>',$r['body'],array('email_scope'=>'platform','template_key'=>'communications_notice','messaging_key'=>'comm-event:'.$r['id'],'retry_failed'=>true));
                if(!empty($result['success'])){$this->db->where('id',$r['id'])->update('db_communication_events',array('delivered_at'=>gmdate('Y-m-d H:i:s')));$sent++;}
            }catch(Throwable $e){log_message('error','Communications notification pending: '.$r['id']);}
        }return $sent;
    }
}
