<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Read existing contact records in place. Never turns a contact into a subscriber. */
class Communication_audience {
    private $CI;private $rawCache=array();private $blockedCache=array();
    public function __construct(){$this->CI =& get_instance();}
    public static function definitions(){return array(
        'all'=>array('name'=>'Customers · main app','group'=>'Customers','description'=>'Active customer, client and patient contact records.','table'=>'db_customers','name_field'=>'customer_name','email'=>'email','phone'=>'mobile','phone_fallback'=>'phone','permission'=>'customers_view'),
        'leads'=>array('name'=>'Leads · CRM','group'=>'Customers','description'=>'New, contacted and qualified leads. Converted and lost leads are excluded.','table'=>'db_leads','name_field'=>'name','email'=>'email','phone'=>'phone','permission'=>'leads_view'),
        'subscribers'=>array('name'=>'Newsletter subscribers · online store','group'=>'Customers','description'=>'Active storefront newsletter subscriptions. Email only.','table'=>'db_newsletter_subscribers','name_field'=>null,'email'=>'email','phone'=>null,'permission'=>'send_email'),
        'online_buyers'=>array('name'=>'Buyers · online store','group'=>'Customers','description'=>'Contact details on active, non-cancelled storefront orders, including guest buyers.','table'=>'db_online_orders','name_field'=>'customer_name','email'=>'customer_email','phone'=>'customer_phone','permission'=>'online_store_orders'),
        'communications'=>array('name'=>'Imported contacts','group'=>'Customers','description'=>'Contacts uploaded in Communications with permission to contact.','table'=>'db_communication_contacts','name_field'=>'name','email'=>'email','phone'=>'phone','permission'=>null),
        'users'=>array('name'=>'Users / staff · main app','group'=>'Internal','description'=>'Active app users in this store. A separate internal audience.','table'=>'db_users','name_field'=>'username','email'=>'email','phone'=>'mobile','permission'=>'users_view'),
        'suppliers'=>array('name'=>'Suppliers · main app','group'=>'Business','description'=>'Active supplier contacts. Kept separate from customer promotions.','table'=>'db_suppliers','name_field'=>'supplier_name','email'=>'email','phone'=>'mobile','phone_fallback'=>'phone','permission'=>'suppliers_view'),
        'customer_contacts'=>array('name'=>'Combined customer contacts','group'=>'Combined','description'=>'Customers, leads, subscribers, storefront buyers and imports. Deduplicated; staff and supplier lists are not merged.','table'=>null,'permission'=>null)
    );}
    public static function recipient($channel,$value){
        $value=trim((string)$value);
        if($channel==='email')return strlen($value)<=191 && filter_var($value,FILTER_VALIDATE_EMAIL)?strtolower($value):null;
        if($channel!=='sms')return null;$digits=preg_replace('/[^0-9]/','',$value);if(strpos($digits,'00234')===0)$digits=substr($digits,2);
        if(preg_match('/^0[789][01][0-9]{8}$/D',$digits))$digits='234'.substr($digits,1);
        elseif(preg_match('/^[789][01][0-9]{8}$/D',$digits))$digits='234'.$digits;
        return preg_match('/^234[789][01][0-9]{8}$/D',$digits)?$digits:null;
    }
    public function allowed($key){
        $d=self::definitions()[$key]??null;if(!$d)return true;
        if((function_exists('is_admin') && is_admin()) || (function_exists('is_store_admin') && is_store_admin()))return true;
        if(!method_exists($this->CI,'permissions'))return true; // authenticated background worker
        return empty($d['permission']) || $this->CI->permissions($d['permission']);
    }
    public function status($key,$channel){
        $d=self::definitions()[$key]??null;if(!$d)return 'Unknown source';
        if($channel==='sms' && $key!=='customer_contacts' && empty($d['phone']))return 'Email only';
        if(!$this->allowed($key))return 'Permission required';
        if($key==='customer_contacts')return null;
        $db=$this->CI->db;if(!$db->table_exists($d['table']))return 'Source not available';
        if(in_array($key,array('leads','users','subscribers','suppliers'),true) && !$db->field_exists('status',$d['table']))return 'Source status unavailable';
        foreach(array('store_id','id',$d[$channel==='sms'?'phone':'email']) as $field)if(!$db->field_exists($field,$d['table']))return 'Contact field unavailable';
        return null;
    }
    public function raw($storeId,$key,$channel='email'){
        $cache=$storeId.'|'.$key.'|'.$channel;if(isset($this->rawCache[$cache]))return $this->rawCache[$cache];
        if($this->status($key,$channel)!==null)return array();
        if($key==='customer_contacts'){
            $rows=array();foreach(array('all','leads','subscribers','online_buyers','communications') as $source)foreach($this->raw($storeId,$source,$channel) as $row)$rows[]=$row;
            return $this->rawCache[$cache]=$rows;
        }
        $d=self::definitions()[$key];$db=$this->CI->db;$table=$d['table'];
        $name=$d['name_field'] && $db->field_exists($d['name_field'],$table)?$d['name_field']:null;
        $email=!empty($d['email']) && $db->field_exists($d['email'],$table)?$d['email']:null;
        $phone=!empty($d['phone']) && $db->field_exists($d['phone'],$table)?$d['phone']:null;
        $fallback=!empty($d['phone_fallback']) && $db->field_exists($d['phone_fallback'],$table)?$d['phone_fallback']:null;
        $fields='id,'.($name?$name:"''").' AS name,'.($email?$email:"''").' AS email,'.($phone?$phone:"''").' AS phone'.($fallback?','.$fallback.' AS alternative_phone':'');
        $hasStatus=$db->field_exists('status',$table);$hasSystem=$key==='users' && $db->field_exists('is_system',$table);$hasOrderStatus=$key==='online_buyers' && $db->field_exists('order_status',$table);
        $db->select($fields,false)->where('store_id',(int)$storeId);
        if($hasStatus){if($key==='leads')$db->where_in('status',array('new','contacted','qualified'));else $db->where('status',1);}
        if($hasSystem)$db->where('is_system',0);if($hasOrderStatus)$db->where('order_status !=','cancelled');
        $query=$db->order_by('id')->get($table);if(!$query)throw new RuntimeException('This contact source could not be read. Ask your administrator to check the app update.');
        $rows=$query->result();foreach($rows as $row){if(!self::recipient('sms',$row->phone??'') && self::recipient('sms',$row->alternative_phone??''))$row->phone=$row->alternative_phone;$row->source_key=$key;$row->source_name=$d['name'];}
        return $this->rawCache[$cache]=$rows;
    }
    private function blocked($storeId,$channel){
        $cache=$storeId.'|'.$channel;if(isset($this->blockedCache[$cache]))return $this->blockedCache[$cache];$blocked=array();$db=$this->CI->db;
        if($db->table_exists('db_marketing_suppressions'))foreach($db->select('recipient')->where('store_id',(int)$storeId)->where_in('channel',array($channel,'all'))->get('db_marketing_suppressions')->result_array() as $r){$value=self::recipient($channel,$r['recipient']);if($value)$blocked[$value]=true;}
        if($channel==='email' && $db->table_exists('db_newsletter_subscribers') && $db->field_exists('status','db_newsletter_subscribers'))foreach($db->select('email')->where('store_id',(int)$storeId)->where('status',0)->get('db_newsletter_subscribers')->result_array() as $r){$value=self::recipient('email',$r['email']);if($value)$blocked[$value]=true;}
        return $this->blockedCache[$cache]=$blocked;
    }
    public function summarize($storeId,$key,$channel,array $rows=null){
        if(!in_array($channel,array('email','sms'),true))throw new InvalidArgumentException('Choose SMS or email.');
        $status=$rows!==null && !isset(self::definitions()[$key])?null:$this->status($key,$channel);$rows=$rows===null?$this->raw($storeId,$key,$channel):$rows;$blocked=$this->blocked($storeId,$channel);$seen=array();$eligible=array();$invalid=0;$duplicates=0;$opted=0;
        foreach($rows as $row){$recipient=self::recipient($channel,$row->{$channel==='sms'?'phone':'email'}??'');if(!$recipient){$invalid++;continue;}if(isset($seen[$recipient])){$duplicates++;continue;}$seen[$recipient]=true;if(isset($blocked[$recipient])){$opted++;continue;}
            $contact=clone $row;$contact->recipient=$recipient;$contact->{$channel==='sms'?'phone':'email'}=$recipient;$eligible[]=$contact;
        }
        $d=self::definitions()[$key]??array('name'=>$key,'group'=>'Customer segments','description'=>'Customer segment');
        return array('key'=>$key,'name'=>$d['name'],'group'=>$d['group'],'description'=>$d['description'],'status'=>$status,'records'=>count($rows),'eligible'=>count($eligible),'invalid'=>$invalid,'duplicates'=>$duplicates,'opted_out'=>$opted,'contacts'=>$eligible);
    }
    public function catalogue($storeId,$channel){$out=array();foreach(self::definitions() as $key=>$d){$r=$this->summarize($storeId,$key,$channel);unset($r['contacts']);$out[$key]=$r;}return $out;}
}
