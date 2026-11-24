<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_ads_model extends CI_Model {
    private function defaultEntitlements() {
        return array('copy_generations_per_month'=>20,'ai_images_per_month'=>2,'video_exports_per_month'=>0,'ai_video_generations_per_month'=>0,'media_storage_mb'=>100);
    }
    private function entitlements(array $subscription) {
        $raw=json_decode((string)($subscription['plan_limits_json']??''),true);
        $raw=is_array($raw)?$raw:array(); $defaults=$this->defaultEntitlements();
        $max=array('copy_generations_per_month'=>500,'ai_images_per_month'=>100,'video_exports_per_month'=>100,'ai_video_generations_per_month'=>100,'media_storage_mb'=>10000); $out=array();
        foreach($defaults as $key=>$value){$n=isset($raw[$key])&&is_numeric($raw[$key])?(int)$raw[$key]:$value;$out[$key]=max(0,min($max[$key],$n));}
        return $out;
    }
    private function addMonthsClamped(DateTimeImmutable $anchor, $months) {
        $monthIndex=((int)$anchor->format('Y'))*12+((int)$anchor->format('n'))-1+(int)$months;
        $year=(int)floor($monthIndex/12); $month=($monthIndex%12)+1;
        $first=$anchor->setDate($year,$month,1); $day=min((int)$anchor->format('j'),(int)$first->format('t'));
        return $first->setDate($year,$month,$day);
    }
    private function usageWindow(array $subscription) {
        $utc=new DateTimeZone('UTC'); $anchor=new DateTimeImmutable((string)$subscription['starts_at'],$utc); $now=new DateTimeImmutable('now',$utc);
        $months=((int)$now->format('Y')-(int)$anchor->format('Y'))*12+((int)$now->format('n')-(int)$anchor->format('n'));
        $months=max(0,$months); $start=$this->addMonthsClamped($anchor,$months);
        if($start>$now&&$months>0){$months--; $start=$this->addMonthsClamped($anchor,$months);}
        $end=$this->addMonthsClamped($anchor,$months+1);
        return array($start->format('Y-m-d H:i:s'),$end->format('Y-m-d H:i:s'));
    }
    public function connection($storeId) {
        if (!$this->db->table_exists('db_ai_ads_connections')) return null;
        return $this->db->where('store_id',(int)$storeId)->get('db_ai_ads_connections')->row_array();
    }
    public function subscription($storeId) {
        if (!$this->db->table_exists('db_ai_ads_subscriptions')) return null;
        $subscription=$this->db->where('store_id',(int)$storeId)->order_by('id','desc')->limit(1)->get('db_ai_ads_subscriptions')->row_array();
        if($subscription && !empty($subscription['payment_reference']) && $this->db->table_exists('db_ai_ads_billing')) {
            $billing=$this->db->select('product_code,invoice_number,amount_kobo,billing_period')->where('store_id',(int)$storeId)
                ->where('payment_reference',$subscription['payment_reference'])->order_by('id','desc')->limit(1)->get('db_ai_ads_billing')->row_array();
            if($billing) $subscription=array_merge($subscription,$billing);
        }
        return $subscription;
    }
    public function hasAccess($storeId) {
        $s=$this->subscription($storeId); if (!$s || !in_array($s['status'],array('active','grace'),true) || empty($s['expires_at'])) return false;
        try { $expires=(new DateTimeImmutable($s['expires_at'],new DateTimeZone('UTC')))->getTimestamp()+(int)$s['grace_days']*86400; }
        catch(Throwable $e) { return false; }
        return $expires >= time() && (empty($s['starts_at']) || strtotime($s['starts_at'].' UTC') <= time());
    }
    public function usageSnapshot($storeId) {
        $subscription=$this->subscription($storeId); $limits=$subscription?$this->entitlements($subscription):$this->defaultEntitlements();
        $snapshot=array('limits'=>$limits,'used'=>array('copy_generations'=>0,'ai_images'=>0,'video_exports'=>0,'ai_video_generations'=>0,'media_bytes'=>0),'reset_at'=>null);
        if(!$subscription||empty($subscription['starts_at'])||!$this->db->table_exists('db_ai_ads_usage')) return $snapshot;
        list($start,$end)=$this->usageWindow($subscription); $snapshot['reset_at']=$end;
        $row=$this->db->where('store_id',(int)$storeId)->where('subscription_id',(int)$subscription['id'])->where('cycle_start',$start)->get('db_ai_ads_usage')->row_array();
        if($row)$snapshot['used']=array('copy_generations'=>(int)$row['copy_generations'],'ai_images'=>(int)$row['ai_images'],'video_exports'=>(int)$row['video_exports'],'ai_video_generations'=>(int)$row['ai_video_generations'],'media_bytes'=>0);
        if($this->db->table_exists('db_ai_ads_media_assets')) {
            $stored=$this->db->select_sum('size_bytes','total_bytes')->where('store_id',(int)$storeId)->get('db_ai_ads_media_assets')->row_array();
            $snapshot['used']['media_bytes']=(int)($stored['total_bytes']??0);
        }
        return $snapshot;
    }
    /** Reserve one successful-output credit atomically before calling a provider. */
    public function reserveCreativeGeneration($storeId,$kind) {
        $subscription=$this->subscription($storeId);
        if(!$subscription||!$this->hasAccess($storeId))return array('ok'=>false,'reason'=>'inactive');
        if(!in_array($kind,array('image','video','video_export'),true))return array('ok'=>false,'reason'=>'invalid');
        if(!$this->db->table_exists('db_ai_ads_usage'))return array('ok'=>false,'reason'=>'setup');
        $limits=$this->entitlements($subscription);
        // Every AI-created video uses the same priced allowance, whether it
        // starts from a prompt or from merchant-supplied media.
        $column=$kind==='image'?'ai_images':'ai_video_generations';
        $limitKey=$kind==='image'?'ai_images_per_month':'ai_video_generations_per_month';
        $cap=(int)$limits[$limitKey];
        list($start,$end)=$this->usageWindow($subscription); $store=(int)$storeId; $sub=(int)$subscription['id'];
        if($cap<1)return array('ok'=>false,'reason'=>'limit','used'=>0,'limit'=>$cap,'cycle_end'=>$end);
        $this->db->query('INSERT IGNORE INTO db_ai_ads_usage (store_id,subscription_id,cycle_start,cycle_end) VALUES (?,?,?,?)',array($store,$sub,$start,$end));
        $this->db->query('UPDATE db_ai_ads_usage SET '.$column.'='.$column.'+1, updated_at=UTC_TIMESTAMP() WHERE store_id=? AND subscription_id=? AND cycle_start=? AND '.$column.' < ?',array($store,$sub,$start,$cap));
        if($this->db->affected_rows()!==1) {
            $row=$this->db->query('SELECT '.$column.' AS used FROM db_ai_ads_usage WHERE store_id=? AND subscription_id=? AND cycle_start=?',array($store,$sub,$start))->row_array();
            return array('ok'=>false,'reason'=>'limit','limit'=>$cap,'used'=>(int)($row['used']??$cap),'cycle_end'=>$end);
        }
        $row=$this->db->query('SELECT '.$column.' AS used FROM db_ai_ads_usage WHERE store_id=? AND subscription_id=? AND cycle_start=?',array($store,$sub,$start))->row_array();
        return array('ok'=>true,'kind'=>$kind,'column'=>$column,'store_id'=>$store,'subscription_id'=>$sub,'cycle_start'=>$start,'cycle_end'=>$end,'limit'=>$cap,'used'=>(int)($row['used']??1));
    }
    public function releaseCreativeGeneration(array $reservation) {
        if(empty($reservation['ok'])||!in_array($reservation['column']??'',array('ai_images','ai_video_generations','video_exports'),true)||!$this->db->table_exists('db_ai_ads_usage'))return;
        $column=$reservation['column'];
        $this->db->query('UPDATE db_ai_ads_usage SET '.$column.'=IF('.$column.'>0,'.$column.'-1,0), updated_at=UTC_TIMESTAMP() WHERE store_id=? AND subscription_id=? AND cycle_start=?',array((int)$reservation['store_id'],(int)$reservation['subscription_id'],$reservation['cycle_start']));
    }
    public function mediaAssets($storeId) {
        if(!$this->db->table_exists('db_ai_ads_media_assets'))return array();
        return $this->db->where('store_id',(int)$storeId)->order_by('id','desc')->limit(100)->get('db_ai_ads_media_assets')->result_array();
    }
    public function saveMediaAsset($storeId,$userId,array $asset) {
        $subscription=$this->subscription($storeId);
        if(!$subscription||!$this->hasAccess($storeId))throw new RuntimeException('An active AI Ads plan is required to store campaign media.');
        if(!$this->db->table_exists('db_ai_ads_media_assets'))throw new RuntimeException('AI Ads media storage migration is not installed.');
        $cap=(int)$this->entitlements($subscription)['media_storage_mb']*1024*1024;
        $this->db->trans_begin();
        // Serialize uploads for this subscription so simultaneous requests
        // cannot both pass the aggregate storage check and exceed the cap.
        $this->db->query('SELECT id FROM db_ai_ads_subscriptions WHERE id=? FOR UPDATE',array((int)$subscription['id']))->row_array();
        $sum=$this->db->select_sum('size_bytes','total_bytes')->where('store_id',(int)$storeId)->get('db_ai_ads_media_assets')->row_array();
        $used=(int)($sum['total_bytes']??0); $size=(int)$asset['size_bytes'];
        if($size<1||$used+$size>$cap){$this->db->trans_rollback();throw new RuntimeException('Your plan’s media storage is full. Delete an old file or choose a plan with more storage.');}
        list($start)=$this->usageWindow($subscription);
        $this->db->insert('db_ai_ads_media_assets',array('store_id'=>(int)$storeId,'subscription_id'=>(int)$subscription['id'],'cycle_start'=>$start,
            'asset_type'=>$asset['asset_type'],'source_type'=>$asset['source_type'],'file_name'=>$asset['file_name'],'relative_path'=>$asset['relative_path'],
            'mime_type'=>$asset['mime_type'],'size_bytes'=>$size,'created_by'=>(int)$userId?:null));
        $id=(int)$this->db->insert_id();
        if($this->db->trans_status()===false){$this->db->trans_rollback();throw new RuntimeException('Campaign media could not be registered.');}
        $this->db->trans_commit();
        return $id;
    }
    public function reserveCopyGeneration($storeId) {
        $subscription=$this->subscription($storeId);
        if(!$subscription||!$this->hasAccess($storeId))return array('ok'=>false,'reason'=>'inactive');
        $limits=$this->entitlements($subscription); $cap=(int)$limits['copy_generations_per_month'];
        if($cap<1)return array('ok'=>false,'reason'=>'limit','limit'=>$cap,'used'=>0);
        if(!$this->db->table_exists('db_ai_ads_usage'))return array('ok'=>false,'reason'=>'setup');
        list($start,$end)=$this->usageWindow($subscription); $store=(int)$storeId; $sub=(int)$subscription['id'];
        $this->db->query('INSERT IGNORE INTO db_ai_ads_usage (store_id,subscription_id,cycle_start,cycle_end) VALUES (?,?,?,?)',array($store,$sub,$start,$end));
        $this->db->query('UPDATE db_ai_ads_usage SET copy_generations=copy_generations+1, updated_at=UTC_TIMESTAMP() WHERE store_id=? AND subscription_id=? AND cycle_start=? AND copy_generations < ?',array($store,$sub,$start,$cap));
        if($this->db->affected_rows()!==1) {
            $row=$this->db->query('SELECT copy_generations FROM db_ai_ads_usage WHERE store_id=? AND subscription_id=? AND cycle_start=?',array($store,$sub,$start))->row_array();
            return array('ok'=>false,'reason'=>'limit','limit'=>$cap,'used'=>(int)($row['copy_generations']??$cap),'cycle_end'=>$end);
        }
        $usage=$this->db->query('SELECT copy_generations FROM db_ai_ads_usage WHERE store_id=? AND subscription_id=? AND cycle_start=?',array($store,$sub,$start))->row_array();
        return array('ok'=>true,'store_id'=>$store,'subscription_id'=>$sub,'cycle_start'=>$start,'cycle_end'=>$end,'limit'=>$cap,'used'=>(int)($usage['copy_generations']??1));
    }
    public function releaseCopyGeneration(array $reservation) {
        if(empty($reservation['ok'])||!$this->db->table_exists('db_ai_ads_usage'))return;
        $this->db->query('UPDATE db_ai_ads_usage SET copy_generations=IF(copy_generations>0,copy_generations-1,0), updated_at=UTC_TIMESTAMP() WHERE store_id=? AND subscription_id=? AND cycle_start=?',array((int)$reservation['store_id'],(int)$reservation['subscription_id'],$reservation['cycle_start']));
    }
    public function campaigns($storeId) {
        if (!$this->db->table_exists('db_ai_ads_campaigns')) return array();
        return $this->db->where('store_id',(int)$storeId)->order_by('id','desc')->limit(100)->get('db_ai_ads_campaigns')->result_array();
    }
    public function campaign($storeId,$campaignId) {
        if(!$this->db->table_exists('db_ai_ads_campaigns'))return null;
        return $this->db->where('store_id',(int)$storeId)->where('id',(int)$campaignId)->get('db_ai_ads_campaigns')->row_array();
    }
    public function updateCampaign($storeId,$campaignId,array $fields) {
        $allowed=array('status','destination_type','pixel_id','conversion_event','confirmed_total_minor','launched_at','meta_campaign_id','meta_adset_id','meta_ad_id','last_error');
        $fields=array_intersect_key($fields,array_flip($allowed));
        if(!$fields)return false;
        return $this->db->where('store_id',(int)$storeId)->where('id',(int)$campaignId)->update('db_ai_ads_campaigns',$fields);
    }
    public function claimCampaignForPublish($storeId,$campaignId) {
        $this->db->where('store_id',(int)$storeId)->where('id',(int)$campaignId)->where('status','draft')->update('db_ai_ads_campaigns',array('status'=>'publishing','last_error'=>null));
        return $this->db->affected_rows()===1;
    }
    public function products($storeId) {
        if (!$this->db->table_exists('db_items')) return array();
        $this->db->select('id, item_name'.($this->db->field_exists('sales_price','db_items')?', sales_price':'').($this->db->field_exists('description','db_items')?', description':'').($this->db->field_exists('item_image','db_items')?', item_image':''));
        $this->db->where('store_id',(int)$storeId);
        if ($this->db->field_exists('status','db_items')) $this->db->where('status',1);
        return $this->db->order_by('id','desc')->limit(100)->get('db_items')->result_array();
    }
    public function audit($storeId,$campaignId,$actorId,$event,$details='') {
        if (!$this->db->table_exists('db_ai_ads_audit')) return;
        $this->db->insert('db_ai_ads_audit',array('store_id'=>$storeId?(int)$storeId:null,'campaign_id'=>$campaignId?(int)$campaignId:null,'actor_id'=>$actorId?(int)$actorId:null,'event_key'=>substr($event,0,64),'details'=>mb_substr(strip_tags((string)$details),0,2000)));
    }
    public function saveDraft($storeId,$userId,array $data) {
        $key=hash('sha256',(int)$storeId.'|'.(string)$data['request_key']);
        $existing=$this->db->where('store_id',(int)$storeId)->where('request_key',$key)->get('db_ai_ads_campaigns')->row_array();
        if ($existing) return (int)$existing['id'];
        $this->db->insert('db_ai_ads_campaigns',array('store_id'=>(int)$storeId,'created_by'=>(int)$userId,'request_key'=>$key,'prompt'=>$data['prompt'],'product_id'=>$data['product_id']?:null,'media_asset_id'=>!empty($data['media_asset_id'])?(int)$data['media_asset_id']:null,'headline'=>$data['headline'],'body'=>$data['body'],'destination_url'=>$data['destination_url'],'destination_type'=>$data['destination_type']??null,'pixel_id'=>$data['pixel_id']??null,'conversion_event'=>$data['conversion_event']??null,'location_text'=>$data['location_text'],'daily_budget_minor'=>$data['daily_budget_minor'],'confirmed_total_minor'=>$data['confirmed_total_minor']??null,'starts_at'=>$data['starts_at'],'ends_at'=>$data['ends_at'],'status'=>'draft'));
        return (int)$this->db->insert_id();
    }
}
