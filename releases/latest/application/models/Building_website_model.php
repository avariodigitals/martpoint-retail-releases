<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Website intake is the existing CRM lead, with links to canonical operations. */
class Building_website_model extends CI_Model {
    public function inbox(array $filter=[], $canRead=false) {
        $result=['enquiries'=>[],'intake_stats'=>[],'intake_filter'=>'','intake_search'=>''];
        if(!$canRead)return $result;
        $store=(int)get_current_store_id();
        $result['intake_stats']=(array)$this->db->query("SELECT COUNT(*) AS total, COALESCE(SUM(status='new'),0) AS new_count, COALESCE(SUM(assigned_to IS NULL AND status NOT IN ('converted','lost')),0) AS unassigned, COALESCE(SUM(next_followup_at < CURDATE() AND status NOT IN ('converted','lost')),0) AS overdue FROM db_leads WHERE store_id=? AND source='storefront'",[$store])->row();
        $kind=is_string($filter['kind'] ?? null)?$filter['kind']:'';
        $search=is_string($filter['search'] ?? null)?mb_substr(trim($filter['search']),0,150):'';
        $this->db->select('l.*,u.username AS assigned_name')->from('db_leads l')->join('db_users u','u.id=l.assigned_to AND u.store_id=l.store_id','left')->where('l.store_id',$store)->where('l.source','storefront');
        if($kind==='new')$this->db->where('l.status','new');
        elseif($kind==='unassigned')$this->db->where('l.assigned_to',null)->where_not_in('l.status',['converted','lost']);
        elseif($kind==='overdue')$this->db->where('l.next_followup_at <',date('Y-m-d'))->where_not_in('l.status',['converted','lost']);
        elseif($kind==='service')$this->db->where('l.enquiry_type','Maintenance / repair');
        elseif($kind==='survey')$this->db->where('l.enquiry_type','Site survey');
        elseif($kind==='project')$this->db->where_in('l.enquiry_type',['New project / quotation','Technical specification']);
        else $kind='';
        if($search!=='')$this->db->group_start()->like('l.name',$search)->or_like('l.interest',$search)->or_like('l.email',$search)->or_like('l.phone',$search)->group_end();
        $result['enquiries']=$this->db->order_by('l.id','desc')->limit(200)->get()->result();
        foreach($result['enquiries'] as $lead)$this->decorate($lead);
        $result['intake_filter']=$kind;$result['intake_search']=$search;
        return $result;
    }
    private function decorate($lead) {
        $meta=json_decode((string)$lead->notes,true);$lead->website_meta=is_array($meta)?$meta:($lead->notes?['merchant_notes'=>(string)$lead->notes]:[]);
        $type=(string)(($lead->enquiry_type ?? '') ?: ($lead->website_meta['enquiry_type'] ?? ''));
        $lead->request_label=$type ?: 'General enquiry';
        $lead->suggested_route=$type==='Maintenance / repair'?'service':($type==='Site survey'?'survey':'project');
        return $lead;
    }
    public function enquiry($id) {
        $lead=$this->db->select('l.*,u.username AS assigned_name')->from('db_leads l')->join('db_users u','u.id=l.assigned_to AND u.store_id=l.store_id','left')->where('l.id',(int)$id)->where('l.store_id',get_current_store_id())->where('l.source','storefront')->get()->row();
        return $lead?$this->decorate($lead):null;
    }
    /** Resolve saved pointers against this store; never trust a raw metadata URL. */
    public function links($lead) {
        $store=(int)get_current_store_id();$links=[];
        $project=$this->db->where('store_id',$store)->where('lead_id',$lead->id)->get('db_service_job_context')->row();
        if($project)$links['project']=['id'=>(int)$project->job_id,'path'=>'building_projects/'.(int)$project->job_id,'label'=>'Project'];
        foreach(['survey','service'] as $kind){
            $id=(int)($lead->website_meta['building_handoffs'][$kind] ?? 0);if(!$id)continue;
            if($kind==='survey')$row=$this->db->where('store_id',$store)->where('id',$id)->where('record_type','site_survey')->get('db_service_project_records')->row();
            else $row=$this->db->where('store_id',$store)->where('id',$id)->where_in('job_type',['maintenance','repair'])->get('db_service_jobs')->row();
            if($row)$links[$kind]=['id'=>$id,'path'=>$kind==='survey'?'building_workspace/survey/'.$id:'operations/service_job_view/'.$id,'label'=>$kind==='survey'?'Site survey':'Service job'];
        }
        return $links;
    }
    private function scalarInput(array $input) {
        foreach($input as $v)if(!is_scalar($v)&&$v!==null)throw new InvalidArgumentException('Please check the request fields.');
    }
    public function triage($id,array $input) {
        $this->scalarInput($input);$status=(string)($input['status'] ?? '');
        if(!in_array($status,['new','contacted','qualified','lost','converted'],true))throw new InvalidArgumentException('Choose a CRM status. Conversion is managed by the operational handoff.');
        $date=(string)($input['followup'] ?? '');$parsed=$date?DateTimeImmutable::createFromFormat('!Y-m-d',$date):null;
        if($date&&(!$parsed||$parsed->format('Y-m-d')!==$date))throw new InvalidArgumentException('Choose a valid follow-up date.');
        $this->load->model('leads_model');$this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)get_current_store_id()]);
            $lead=$this->enquiry($id);if(!$lead)throw new InvalidArgumentException('Enquiry not found.');
            if($status==='converted' && $lead->status!=='converted')throw new InvalidArgumentException('Use an operational handoff to convert this request.');
            if($lead->status==='converted')$status='converted';
            if(!$this->leads_model->assign($id,(int)($input['assigned_to'] ?? 0),$date?:null) || !$this->leads_model->updateStatus($id,$status) || !$this->db->trans_status())throw new InvalidArgumentException('Choose an active team member from this business.');
            $this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function handoff($id,array $input) {
        $this->scalarInput($input);$kind=(string)($input['route'] ?? '');
        if(!in_array($kind,['project','survey','service'],true))throw new InvalidArgumentException('Choose project, site survey or service.');
        $this->load->model(['leads_model','service_project_model','building_workspace_model','equipment_model']);
        $store=(int)get_current_store_id();$this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $lead=$this->enquiry($id);if(!$lead)throw new InvalidArgumentException('Enquiry not found.');
            $links=$this->links($lead);
            if(isset($links[$kind])){$this->db->trans_commit();return $links[$kind]['id'];}
            $customer=(int)($input['customer_id'] ?? 0);
            if($lead->converted_customer_id){
                if($customer && $customer!==(int)$lead->converted_customer_id)throw new InvalidArgumentException('This request is already linked to another customer.');
                $customer=(int)$lead->converted_customer_id;
            }elseif(!$customer){
                $result=$this->leads_model->convertToCustomer($id,$store);$customer=(int)($result['customer_id'] ?? 0);
                if(!$customer)throw new InvalidArgumentException('Could not register the customer.');
            }
            if(!$this->db->where('store_id',$store)->where('id',$customer)->where('status',1)->get('db_customers')->row())throw new InvalidArgumentException('Choose an active customer from this business.');
            $title=trim((string)($input['title'] ?? ''));if(!$title||strlen($title)>200)throw new InvalidArgumentException('Enter a work title of at most 200 characters.');
            $description='Website enquiry #'.$id."\n".$lead->enquiry;
            $location=(string)($lead->project_location ?: ($lead->website_meta['project_location'] ?? ''));
            if($location)$description.="\nSite requested: ".$location;
            $job=['customer_id'=>$customer,'site_id'=>(int)($input['site_id'] ?? 0)?:null,'assigned_user_id'=>(int)($input['assigned_user_id'] ?? 0)?:null,'title'=>$title,'description'=>$description];
            // Canonical writers validate site and engineer tenancy. Store lock and
            // saved lead pointer make retries (including a new form) idempotent.
            if($kind==='project'){
                $record=$this->service_project_model->create(array_merge($job,['lead_id'=>$id,'request_key'=>'web_'.bin2hex(random_bytes(20))]));
            }elseif($kind==='survey'){
                $record=$this->building_workspace_model->save_survey(array_merge($job,['project_id'=>(int)($links['project']['id'] ?? 0),'request_key'=>bin2hex(random_bytes(20)),'status'=>'scheduled','survey_date'=>(string)($input['visit_date'] ?? ''),'notes'=>$description,'location'=>$location]));
            }else{
                $type=(string)($input['service_type'] ?? 'repair');if(!in_array($type,['repair','maintenance'],true))throw new InvalidArgumentException('Choose repair or maintenance.');
                $record=$this->equipment_model->save_job_data(array_merge($job,['job_type'=>$type,'status'=>'open','scheduled_date'=>trim((string)($input['visit_date'] ?? ''))?:null]));
            }
            $meta=$lead->website_meta;$meta['form']=$meta['form'] ?? 'website_enquiry';$meta['building_handoffs'][$kind]=(int)$record;
            $this->db->where('store_id',$store)->where('id',$id)->update('db_leads',['notes'=>json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'status'=>'converted','converted_customer_id'=>$customer,'converted_at'=>$lead->converted_at?:date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
            if(!$this->leads_model->addActivity($id,'conversion',ucfirst($kind).' handoff confirmed',['kind'=>$kind,'record_id'=>$record,'customer_id'=>$customer],$store)||!$this->db->trans_status())throw new RuntimeException('Could not save the handoff history.');
            $this->db->trans_commit();return (int)$record;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    public function save_website_settings(array $input) {
        $this->scalarInput($input);$store=(int)get_current_store_id();$this->load->model('storefront_model');
        $exists=$this->db->where('store_id',$store)->count_all_results('db_storefront_settings');
        if(!$exists){$limit=get_subscription_limit('storefront_limit');if($limit>0 && get_storefront_usage()>=$limit)throw new InvalidArgumentException('Your subscription website limit has been reached.');}
        $row=[];foreach(['store_email'=>255,'store_phone'=>30,'whatsapp_number'=>30,'store_address'=>1500,'business_hours'=>3000,'store_description'=>3000] as $key=>$max){$row[$key]=trim((string)($input[$key] ?? ''));if(mb_strlen($row[$key])>$max)throw new InvalidArgumentException('Please shorten '.str_replace('_',' ',$key).'.');}
        if($row['store_email']&&!filter_var($row['store_email'],FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Enter a valid email address.');
        $row['store_status']=(string)($input['store_status'] ?? '');if(!in_array($row['store_status'],['active','maintenance','deactivated'],true))throw new InvalidArgumentException('Choose a publishing status.');
        if(!$exists){
            $slug='business-'.$store;
            if($this->db->where('store_slug',$slug)->count_all_results('db_storefront_settings'))$slug.='-'.bin2hex(random_bytes(4));
            $row['store_slug']=$slug;
        }
        if(!$this->storefront_model->saveSettings($store,$row))throw new RuntimeException('Could not save website settings.');
        if(function_exists('mp_audit_log'))mp_audit_log('online_store','update','storefront','Updated B2B website publishing and contact details');
    }
}
