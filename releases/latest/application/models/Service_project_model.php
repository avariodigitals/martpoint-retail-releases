<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Landscaping context for the shared service-job lifecycle and finance records. */
class Service_project_model extends CI_Model {
    public function listing() {
        return $this->db->select('j.*,x.stage,x.quotation_id,x.quotation_revision,c.customer_name,u.username AS assigned_name')->from('db_service_job_context x')
            ->join('db_service_jobs j','j.id=x.job_id AND j.store_id=x.store_id')
            ->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id')
            ->join('db_users u','u.id=j.assigned_user_id AND u.store_id=j.store_id','left')
            ->where('x.store_id',get_current_store_id())->order_by('j.id','desc')->limit(250)->get()->result();
    }

    public function get($jobId) {
        return $this->db->select('x.*,j.title,j.description,j.customer_id,j.site_id,j.assigned_user_id,j.sales_id,j.status,j.job_code,j.job_type,j.priority,j.scheduled_date,c.customer_name,u.username AS assigned_name')->from('db_service_job_context x')
            ->join('db_service_jobs j','j.id=x.job_id AND j.store_id=x.store_id')
            ->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id')
            ->join('db_users u','u.id=j.assigned_user_id AND u.store_id=j.store_id','left')
            ->where('x.store_id',get_current_store_id())->where('x.job_id',(int)$jobId)->get()->row();
    }

    public function create(array $input) {
        $this->load->model('equipment_model');
        $storeId=(int)get_current_store_id();$leadId=(int)($input['lead_id'] ?? 0);
        $key=(string)($input['request_key'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)) throw new InvalidArgumentException('Reload the project form to obtain a request reference.');
        $hash=hash('sha256',json_encode([(int)($input['customer_id'] ?? 0),(int)($input['site_id'] ?? 0),(int)($input['assigned_user_id'] ?? 0),trim((string)($input['title'] ?? '')),trim((string)($input['description'] ?? '')),$leadId]));
        if ($leadId && !$this->db->where('id',$leadId)->where('store_id',$storeId)->get('db_leads')->row()) throw new InvalidArgumentException('Enquiry not found.');
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$storeId]);
            $request=$this->db->where('store_id',$storeId)->where('request_key',$key)->get('db_service_job_context')->row();
            if ($request) { if (!hash_equals($request->payload_hash,$hash)) throw new InvalidArgumentException('Request reference already used.'); $this->db->trans_commit();return (int)$request->job_id; }
            if ($leadId) {
                $prior=$this->db->where('store_id',$storeId)->where('lead_id',$leadId)->get('db_service_job_context')->row();
                if ($prior) { $this->db->trans_commit();return (int)$prior->job_id; }
            }
            $jobId=$this->equipment_model->save_job_data(['customer_id'=>(int)($input['customer_id'] ?? 0),'site_id'=>(int)($input['site_id'] ?? 0) ?: null,
                'assigned_user_id'=>(int)($input['assigned_user_id'] ?? 0) ?: null,'title'=>trim((string)($input['title'] ?? '')),
                'description'=>trim((string)($input['description'] ?? '')),'job_type'=>'installation','status'=>'open']);
            if (!$this->db->insert('db_service_job_context',['store_id'=>$storeId,'job_id'=>$jobId,'lead_id'=>$leadId ?: null,'request_key'=>$key,'payload_hash'=>$hash,'created_at'=>date('Y-m-d H:i:s')])) throw new RuntimeException('Could not create project context.');
            $this->event($jobId,null,'enquiry','Project created from shared service job.');
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not create project.');
            $this->db->trans_commit();return $jobId;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    private function event($id,$from,$to,$note) {
        if (!$this->db->insert('db_service_job_events',['store_id'=>get_current_store_id(),'job_id'=>(int)$id,'from_stage'=>$from,'to_stage'=>$to,
            'note'=>$note,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')])) throw new RuntimeException('Could not record project history.');
    }

    public function history($id) {
        return $this->db->where('store_id',get_current_store_id())->where('job_id',(int)$id)->order_by('id','asc')->get('db_service_job_events')->result();
    }

    public function workbench_ready() {
        return $this->db->table_exists('db_service_project_records') && $this->db->table_exists('db_service_project_tasks');
    }

    public function records($jobId) {
        if (!$this->get($jobId) || !$this->workbench_ready()) return [];
        return $this->db->select('r.*,u.username AS author')->from('db_service_project_records r')->join('db_users u','u.id=r.created_by AND u.store_id=r.store_id','left')
            ->where('r.store_id',get_current_store_id())->where('r.job_id',(int)$jobId)->order_by('r.id','desc')->limit(100)->get()->result();
    }

    public function tasks($jobId) {
        if (!$this->get($jobId) || !$this->workbench_ready()) return [];
        return $this->db->select('t.*,u.username AS assignee')->from('db_service_project_tasks t')->join('db_users u','u.id=t.assigned_user_id AND u.store_id=t.store_id','left')
            ->where('t.store_id',get_current_store_id())->where('t.job_id',(int)$jobId)->order_by('t.id')->limit(250)->get()->result();
    }

    private function writable($jobId) {
        $project=$this->get($jobId);
        if (!$project || in_array($project->stage,['closed','cancelled'],true)) throw new InvalidArgumentException('Choose an open project.');
        return $project;
    }

    public function save_record($jobId,array $input) {
        if (!$this->workbench_ready()) throw new InvalidArgumentException('Run migration 4.0.9.159 before saving field records.');
        foreach (['record_type','note','request_key'] as $field) if (isset($input[$field]) && !is_scalar($input[$field])) throw new InvalidArgumentException('Invalid field record input.');
        $type=$input['record_type'] ?? 'inspection';$note=trim((string)($input['note'] ?? ''));
        if (!in_array($type,['inspection','progress','completion'],true) || strlen($note)>8000) throw new InvalidArgumentException('Choose a valid record type and note.');
        $fields=$type==='inspection' ? mp_garden_clean_inspection((array)($input['fields'] ?? [])) : [];
        if ($type!=='inspection' && !$note) throw new InvalidArgumentException('Enter the field note.');
        $key=(string)($input['request_key'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)) throw new InvalidArgumentException('Reload the form for a record reference.');
        $hash=hash('sha256',json_encode([(int)$jobId,$type,$fields,$note]));$store=(int)get_current_store_id();
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $project=$this->writable($jobId);
            $prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_service_project_records')->row();
            if ($prior) { if (!hash_equals($prior->payload_hash,$hash)) throw new InvalidArgumentException('Record reference already used.');$this->db->trans_commit();return (int)$prior->id; }
            if (!$this->db->insert('db_service_project_records',['store_id'=>$store,'job_id'=>(int)$jobId,'record_type'=>$type,'fields_json'=>json_encode($fields),'note'=>$note,
                'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')])) throw new RuntimeException('Could not save field record.');
            $id=(int)$this->db->insert_id();
            if ($type==='inspection') $this->db->where('store_id',$store)->where('job_id',(int)$jobId)->update('db_service_job_context',['inspection_json'=>json_encode(['fields'=>$fields,'notes'=>$note,'recorded_at'=>date('Y-m-d H:i:s'),'staff_id'=>(int)$this->session->userdata('inv_userid')])]);
            $this->event($jobId,$project->stage,$project->stage,ucfirst($type).' field record #'.$id.' saved.');
            if ($type==='inspection' && !empty($input['complete_inspection']) && $project->stage==='enquiry') $this->advance($jobId,'inspection',[]);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not save field record.');
            $this->db->trans_commit();return $id;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function create_task($jobId,array $input) {
        if (!$this->workbench_ready()) throw new InvalidArgumentException('Run migration 4.0.9.159 before adding tasks.');
        foreach (['title','description','due_date','assigned_user_id','request_key'] as $field) if (isset($input[$field]) && !is_scalar($input[$field])) throw new InvalidArgumentException('Invalid task input.');
        $title=trim((string)($input['title'] ?? ''));$description=trim((string)($input['description'] ?? ''));$due=(string)($input['due_date'] ?? '');$staff=(int)($input['assigned_user_id'] ?? 0);
        $key=(string)($input['request_key'] ?? '');$store=(int)get_current_store_id();
        if (!$title || strlen($title)>200 || strlen($description)>8000 || !preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)) throw new InvalidArgumentException('Enter a task title and valid reference.');
        if ($due) { $date=DateTimeImmutable::createFromFormat('!Y-m-d',$due);if (!$date || $date->format('Y-m-d')!==$due) throw new InvalidArgumentException('Invalid task due date.'); }
        $hash=hash('sha256',json_encode([(int)$jobId,$title,$description,$staff,$due]));
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$project=$this->writable($jobId);
            if ($staff && !$this->db->where('id',$staff)->where('store_id',$store)->where('status',1)->get('db_users')->row()) throw new InvalidArgumentException('Team member not found.');
            $prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_service_project_tasks')->row();
            if ($prior) { if (!hash_equals($prior->payload_hash,$hash)) throw new InvalidArgumentException('Task reference already used.');$this->db->trans_commit();return (int)$prior->id; }
            $now=date('Y-m-d H:i:s');
            if (!$this->db->insert('db_service_project_tasks',['store_id'=>$store,'job_id'=>(int)$jobId,'title'=>$title,'description'=>$description,'assigned_user_id'=>$staff ?: null,'due_date'=>$due ?: null,
                'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now])) throw new RuntimeException('Could not add task.');
            $id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Task #'.$id.' added: '.$title);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not add task.');
            $this->db->trans_commit();return $id;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function move_task($jobId,$taskId,$to,$version) {
        if (!in_array($to,['todo','in_progress','completed'],true)) throw new InvalidArgumentException('Choose a valid task column.');
        $store=(int)get_current_store_id();$this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$project=$this->writable($jobId);
            $task=$this->db->where('id',(int)$taskId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_service_project_tasks')->row();
            if (!$task) throw new InvalidArgumentException('Task not found.');
            if ($task->status===$to) { $this->db->trans_commit();return true; }
            if ((int)$task->version!==(int)$version) throw new InvalidArgumentException('This task changed. Refresh the board before moving it.');
            if (!$this->db->where('id',$task->id)->where('store_id',$store)->where('version',(int)$version)->update('db_service_project_tasks',['status'=>$to,'version'=>(int)$version+1,'updated_at'=>date('Y-m-d H:i:s')])) throw new RuntimeException('Could not move task.');
            $this->event($jobId,$project->stage,$project->stage,'Task #'.$task->id.': '.$task->status.' → '.$to.'.');
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not move task.');
            $this->db->trans_commit();return true;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function expense_cards($jobId) {
        if (!$this->get($jobId)) throw new InvalidArgumentException('Project not found.');
        return $this->db->select('e.*')->from('db_service_job_expense_links l')->join('db_expense e','e.id=l.expense_id AND e.store_id=l.store_id')
            ->where('l.store_id',get_current_store_id())->where('l.job_id',(int)$jobId)->order_by('l.id','desc')->get()->result();
    }

    public function document_choices($jobId) {
        $project=$this->get($jobId);if (!$project) throw new InvalidArgumentException('Project not found.');$store=(int)get_current_store_id();
        return ['quotes'=>$this->db->where('store_id',$store)->where('customer_id',$project->customer_id)->order_by('id','desc')->limit(150)->get('db_quotation')->result(),
            'invoices'=>$this->db->where('store_id',$store)->where('customer_id',$project->customer_id)->where('quotation_id',(int)$project->quotation_id)->order_by('id','desc')->limit(100)->get('db_sales')->result(),
            'expenses'=>$this->db->where('store_id',$store)->where('status',1)->order_by('id','desc')->limit(200)->get('db_expense')->result()];
    }

    /** Runs inside the originating shared save transaction. No new financial record. */
    public function attach_document($jobId,$kind,$id) {
        $store=(int)get_current_store_id();
        $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$project=$this->writable($jobId);
        if ($kind==='expense') { if (!$this->link_expense($jobId,$id)) throw new RuntimeException('Could not link expense.');return true; }
        if ($kind==='quotation') {
            $quote=$this->db->where('id',(int)$id)->where('store_id',$store)->where('customer_id',$project->customer_id)->get('db_quotation')->row();
            if (!$quote || in_array($quote->lifecycle_status,['cancelled','declined','expired'],true)) throw new InvalidArgumentException('Choose a current quotation for this project customer.');
            if (!in_array($project->stage,['enquiry','inspection','quoted'],true) && ((int)$project->quotation_id!==(int)$id || (int)$project->quotation_revision!==(int)$quote->revision_no)) throw new InvalidArgumentException('Approved quotation changes need a project revision before continuing.');
            if ($this->db->where('store_id',$store)->where('quotation_id',(int)$id)->where('job_id !=',(int)$jobId)->count_all_results('db_service_job_context')) throw new InvalidArgumentException('Quotation already linked to another project.');
            if ((int)$project->quotation_id===(int)$id && (int)$project->quotation_revision===(int)$quote->revision_no) return true;
            if (!$this->db->where('store_id',$store)->where('job_id',(int)$jobId)->update('db_service_job_context',['quotation_id'=>(int)$id,'quotation_revision'=>(int)$quote->revision_no])) throw new RuntimeException('Could not link quotation.');
        } elseif ($kind==='invoice') {
            $invoice=$this->db->where('id',(int)$id)->where('store_id',$store)->where('customer_id',$project->customer_id)->get('db_sales')->row();
            if (!$invoice || !$project->quotation_id || (int)$invoice->quotation_id!==(int)$project->quotation_id) throw new InvalidArgumentException('Create the invoice from this project quotation for the same customer.');
            $quote=$this->db->where('id',(int)$project->quotation_id)->where('store_id',$store)->where('customer_id',$project->customer_id)->get('db_quotation')->row();
            if (!$quote || $quote->lifecycle_status!=='accepted' || (int)$quote->revision_no!==(int)$project->quotation_revision) throw new InvalidArgumentException('Record customer approval of the current project quotation before invoicing.');
            if ($project->sales_id && (int)$project->sales_id!==(int)$id) throw new InvalidArgumentException('This project already has an invoice.');
            if ($this->db->where('store_id',$store)->where('sales_id',(int)$id)->where('id !=',(int)$jobId)->count_all_results('db_service_jobs')) throw new InvalidArgumentException('Invoice already linked to another job.');
            if ((int)$project->sales_id===(int)$id) return true;
            if (!$this->db->where('store_id',$store)->where('id',(int)$jobId)->update('db_service_jobs',['sales_id'=>(int)$id])) throw new RuntimeException('Could not link invoice.');
        } else throw new InvalidArgumentException('Unknown project document type.');
        $this->event($jobId,$project->stage,$project->stage,ucfirst($kind).' #'.(int)$id.' linked.');return true;
    }

    public function approve_quote($jobId,$revision,$evidence) {
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[(int)get_current_store_id()]);
            $project=$this->writable($jobId);
            if (!in_array($project->stage,['quoted','approved'],true) || !$project->quotation_id || (int)$project->quotation_revision!==(int)$revision) throw new InvalidArgumentException('Link the quotation revision before recording approval.');
            $this->load->model('quotation_model');
            $this->quotation_model->record_customer_acceptance($project->quotation_id,$project->customer_id,$revision,$evidence);
            $this->advance($jobId,'approved',['note'=>$evidence]);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not record approval.');
            $this->db->trans_commit();return true;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function financials($jobId) {
        $context=$this->get($jobId);
        if (!$context) throw new InvalidArgumentException('Project not found.');
        $storeId=(int)get_current_store_id();
        $quote=$context->quotation_id ? $this->db->where('id',$context->quotation_id)->where('store_id',$storeId)->where('customer_id',$context->customer_id)->get('db_quotation')->row() : null;
        $invoice=$context->sales_id ? $this->db->where('id',$context->sales_id)->where('store_id',$storeId)->where('customer_id',$context->customer_id)->get('db_sales')->row() : null;
        $paid=$invoice ? (float)$this->db->select('COALESCE(SUM(payment),0) AS amount',false)->where('store_id',$storeId)->where('sales_id',$invoice->id)->get('db_salespayments')->row()->amount : 0;
        $material=$this->db->select('COALESCE(SUM(i.qty*r.unit_cost),0) AS cost',false)->from('db_service_job_items i')
            ->join('db_service_material_requests r','r.job_item_id=i.id AND r.store_id=i.store_id')->where('i.store_id',$storeId)->where('i.job_id',(int)$jobId)->get()->row();
        $expenses=$this->db->select('COALESCE(SUM(e.expense_amt),0) AS cost',false)->from('db_service_job_expense_links l')
            ->join('db_expense e','e.id=l.expense_id AND e.store_id=l.store_id')->where('l.store_id',$storeId)->where('l.job_id',(int)$jobId)->where('e.status',1)->get()->row();
        $projectMaterial=(object)['planned'=>0,'committed'=>0,'actual'=>0];
        if($this->db->table_exists('db_building_project_materials')) $projectMaterial=$this->db->select('COALESCE(SUM(required_qty*unit_cost),0) AS planned,COALESCE(SUM(ordered_qty*unit_cost),0) AS committed,COALESCE(SUM(issued_qty*unit_cost),0) AS actual',false)
            ->where('store_id',$storeId)->where('job_id',(int)$jobId)->get('db_building_project_materials')->row()?:$projectMaterial;
        $invoicedMaterial=0;
        if ($invoice && $invoice->sales_status==='Final') {
            $invoicedMaterial=(float)$this->db->select('COALESCE(SUM(COALESCE(si.base_unit_qty,si.sales_qty)*si.purchase_price),0) AS cost',false)
                ->from('db_salesitems si')->join('db_items i','i.id=si.item_id AND i.store_id=si.store_id')
                ->where('si.store_id',$storeId)->where('si.sales_id',$invoice->id)->where('i.service_bit !=',1)->where('i.is_bundle',0)->get()->row()->cost;
        }
        $revenue=$invoice ? (float)$invoice->grand_total : ($quote ? (float)$quote->grand_total : 0);
        $actualMaterial=(float)$material->cost+$invoicedMaterial+(float)$projectMaterial->actual;
        $expensesCost=(float)$expenses->cost;
        return ['quotation'=>$quote,'invoice'=>$invoice,'revenue'=>$revenue,'cash_collected'=>$paid,'outstanding'=>$invoice ? max(0,$revenue-$paid) : null,
            'planned_material_cost'=>(float)$projectMaterial->planned,'committed_material_cost'=>(float)$projectMaterial->committed,'project_material_cost'=>(float)$projectMaterial->actual,
            'actual_material_cost'=>$actualMaterial,'linked_expenses'=>$expensesCost,'gross_margin'=>$revenue-$actualMaterial-$expensesCost];
    }

    public function link_expense($jobId,$expenseId) {
        if (!$this->get($jobId)) throw new InvalidArgumentException('Project not found.');
        $storeId=(int)get_current_store_id();
        if (!$this->db->where('id',(int)$expenseId)->where('store_id',$storeId)->where('status',1)->get('db_expense')->row()) throw new InvalidArgumentException('Expense not found.');
        $existing=$this->db->where('store_id',$storeId)->where('expense_id',(int)$expenseId)->get('db_service_job_expense_links')->row();
        if ($existing) { if ((int)$existing->job_id!==(int)$jobId) throw new InvalidArgumentException('Expense already belongs to another project.'); return true; }
        return $this->db->insert('db_service_job_expense_links',['store_id'=>$storeId,'job_id'=>(int)$jobId,'expense_id'=>(int)$expenseId,
            'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')]);
    }

    public function advance($jobId,$to,array $input) {
        $transitions=['enquiry'=>['inspection','cancelled'],'inspection'=>['quoted','cancelled'],'quoted'=>['approved','cancelled'],
            'approved'=>['deposit','cancelled'],'deposit'=>['execution','cancelled'],'execution'=>['completed'],'completed'=>['paid'],'paid'=>['closed']];
        $storeId=(int)get_current_store_id();
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$storeId]);
            $context=$this->get($jobId);
            if (!$context) throw new InvalidArgumentException('Project not found.');
            if ($context->stage===$to) { $this->db->trans_commit();return true; }
            if (!in_array($to,$transitions[$context->stage] ?? [],true)) throw new InvalidArgumentException('Invalid project transition.');
            $update=['stage'=>$to];$note=trim((string)($input['note'] ?? ''));
            if (strlen($note)>8000) throw new InvalidArgumentException('Notes are too long.');
            if ($to==='inspection') {
                $assessment=json_decode($context->inspection_json ?? '',true) ?: [];
                if (empty($assessment['fields']) || !is_array($assessment['fields'])) throw new InvalidArgumentException('Complete the structured site assessment before continuing. A note alone is not an inspection.');
                mp_garden_clean_inspection($assessment['fields']);
            }
            if ($to==='quoted') {
                $quoteId=(int)($input['quotation_id'] ?? $context->quotation_id ?? 0);
                $quote=$this->db->where('id',$quoteId)->where('store_id',$storeId)->where('customer_id',$context->customer_id)->get('db_quotation')->row();
                if (!$quote || in_array($quote->lifecycle_status,['declined','cancelled','expired'],true)) throw new InvalidArgumentException('Choose a current quotation for this customer.');
                if ($this->db->where('store_id',$storeId)->where('quotation_id',$quoteId)->where('job_id !=',(int)$jobId)->get('db_service_job_context')->row()) throw new InvalidArgumentException('Quotation already linked to another project.');
                $update['quotation_id']=$quoteId;$update['quotation_revision']=(int)$quote->revision_no;$note.=' Quotation #'.$quoteId.', revision '.$quote->revision_no.'.';
            }
            $finance=$this->financials($jobId);
            if (in_array($to,['approved','deposit','execution'],true) && (!$finance['quotation']
                || (int)$context->quotation_revision!==(int)$finance['quotation']->revision_no)) throw new InvalidArgumentException('The linked quotation revision has changed. Reconcile the revised quotation before continuing.');
            if ($to==='approved' && (!$finance['quotation'] || $finance['quotation']->lifecycle_status!=='accepted')) throw new InvalidArgumentException('Customer approval must be recorded through the shared quotation workflow.');
            if ($to==='deposit') {
                if ($finance['quotation']->lifecycle_status!=='accepted') throw new InvalidArgumentException('The linked quotation must still have customer approval.');
                $invoiceId=(int)($input['sales_id'] ?? $context->sales_id ?? ($finance['quotation']->converted_sales_id ?? 0));
                if (!$invoiceId) { $linked=$this->db->where('store_id',$storeId)->where('customer_id',$context->customer_id)->where('quotation_id',(int)$context->quotation_id)->where('sales_status','Final')->get('db_sales')->row();$invoiceId=$linked ? (int)$linked->id : 0; }
                $invoice=$this->db->where('id',$invoiceId)->where('store_id',$storeId)->where('customer_id',$context->customer_id)
                    ->where('quotation_id',(int)$context->quotation_id)->where('sales_status','Final')->get('db_sales')->row();
                if (!$invoice) throw new InvalidArgumentException('Convert the approved quotation to a final shared invoice.');
                if ($this->db->where('store_id',$storeId)->where('sales_id',$invoiceId)->where('id !=',(int)$jobId)->get('db_service_jobs')->row()) throw new InvalidArgumentException('Invoice already linked to another job.');
                $paid=(float)$this->db->select('COALESCE(SUM(payment),0) AS amount',false)->where('store_id',$storeId)->where('sales_id',$invoiceId)->get('db_salespayments')->row()->amount;
                if ($paid<=0) throw new InvalidArgumentException('Record the deposit against the shared invoice first.');
                $this->db->where('id',(int)$jobId)->where('store_id',$storeId)->update('db_service_jobs',['sales_id'=>$invoiceId]);
            }
            if ($to==='execution' && (!$finance['invoice'] || $finance['invoice']->sales_status!=='Final' || $finance['cash_collected']<=0)) throw new InvalidArgumentException('A valid invoice and recorded deposit are required.');
            if ($to==='completed') {
                if (!$note) throw new InvalidArgumentException('Record completion and customer approval notes.');
                if ($this->workbench_ready() && $this->db->where('store_id',$storeId)->where('job_id',(int)$jobId)->where('status !=','completed')->count_all_results('db_service_project_tasks')) throw new InvalidArgumentException('Complete the remaining team task cards before signing off the project.');
                $update['completion_notes']=$note;
            }
            if (in_array($to,['paid','closed'],true) && (!$finance['invoice'] || $finance['invoice']->sales_status!=='Final' || $finance['outstanding']>0.005)) throw new InvalidArgumentException('The final invoice balance must be settled.');
            if (in_array($to,['execution','completed','cancelled'],true)) $this->db->where('id',(int)$jobId)->where('store_id',$storeId)->update('db_service_jobs',['status'=>['execution'=>'in_progress','completed'=>'completed','cancelled'=>'cancelled'][$to]]);
            if (!$this->db->where('job_id',(int)$jobId)->where('store_id',$storeId)->update('db_service_job_context',$update)) throw new RuntimeException('Could not update project.');
            $this->event($jobId,$context->stage,$to,$note);
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not update project.');
            $this->db->trans_commit();return true;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    /** Return the catalogue and supplier choices used by the project procurement panel. */
    public function material_choices($jobId) {
        $this->writable($jobId);
        $store=(int)get_current_store_id();
        return [
            'items'=>$this->db->select('id,item_name,item_code,stock,purchase_price')->where('store_id',$store)->where('status',1)->order_by('item_name')->get('db_items')->result(),
            'suppliers'=>$this->db->select('id,supplier_name')->where('store_id',$store)->where('status',1)->order_by('supplier_name')->get('db_suppliers')->result(),
        ];
    }

    private function physical_stock($itemId) {
        $item=$this->db->select('id,stock')->where('id',(int)$itemId)->where('store_id',(int)get_current_store_id())->where('status',1)->get('db_items')->row();
        if (!$item) return null;
        return (float)$item->stock;
    }

    public function materials($jobId) {
        if (!$this->get($jobId) || !$this->db->table_exists('db_building_project_materials')) return [];
        $store=(int)get_current_store_id();
        return $this->db->select('m.*,i.item_name,i.item_code,s.supplier_name,p.purchase_code,pi.purchase_qty,pi.received_qty AS purchase_received_qty')
            ->from('db_building_project_materials m')->join('db_items i','i.id=m.item_id AND i.store_id=m.store_id','left')
            ->join('db_suppliers s','s.id=m.supplier_id AND s.store_id=m.store_id','left')
            ->join('db_purchase p','p.id=m.purchase_id AND p.store_id=m.store_id','left')
            ->join('db_purchaseitems pi','pi.id=m.purchase_item_id AND pi.store_id=m.store_id','left')
            ->where('m.store_id',$store)->where('m.job_id',(int)$jobId)->order_by('m.id','desc')->get()->result();
    }

    public function procurements($jobId) {
        if (!$this->get($jobId) || !$this->db->table_exists('db_building_project_procurement')) return [];
        return $this->db->select('p.*,s.supplier_name,po.purchase_code,po.purchase_status,po.grand_total')
            ->from('db_building_project_procurement p')->join('db_suppliers s','s.id=p.supplier_id AND s.store_id=p.store_id','left')
            ->join('db_purchase po','po.id=p.purchase_id AND po.store_id=p.store_id','left')
            ->where('p.store_id',(int)get_current_store_id())->where('p.job_id',(int)$jobId)->order_by('p.id','desc')->get()->result();
    }

    public function add_material($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();
        $key=(string)($input['request_key'] ?? '');$itemId=(int)($input['item_id'] ?? 0);$name=trim((string)($input['material_name'] ?? ''));
        $qty=(float)($input['required_qty'] ?? 0);$unit=trim((string)($input['unit_label'] ?? 'unit'));$cost=(float)($input['unit_cost'] ?? 0);$supplier=(int)($input['supplier_id'] ?? 0);$date=trim((string)($input['expected_date'] ?? ''));
        if (!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key) || $qty<=0 || $qty>100000000 || $unit==='' || strlen($unit)>50 || $cost<0 || strlen($name)>200) throw new InvalidArgumentException('Enter a material, positive quantity and valid request reference.');
        if ($date) { $d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$d || $d->format('Y-m-d')!==$date) throw new InvalidArgumentException('Invalid expected date.'); }
        $item=$itemId ? $this->db->where('id',$itemId)->where('store_id',$store)->where('status',1)->get('db_items')->row() : null;
        if ($itemId && !$item) throw new InvalidArgumentException('Catalogue item not found.');
        if ($supplier && !$this->db->where('id',$supplier)->where('store_id',$store)->where('status',1)->get('db_suppliers')->row()) throw new InvalidArgumentException('Supplier not found.');
        if (!$name) $name=$item ? trim((string)$item->item_name) : '';
        if (!$name) throw new InvalidArgumentException('Choose a catalogue item or enter a material name.');
        $hash=hash('sha256',json_encode([(int)$jobId,$itemId,$name,$qty,$unit,$cost,$supplier,$date]));
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_materials')->row();
            if ($prior) { if(!hash_equals($prior->payload_hash,$hash)) throw new InvalidArgumentException('Material request reference already used.');$this->db->trans_commit();return (int)$prior->id; }
            $available=$item ? $this->physical_stock($itemId) : 0;
            $row=['store_id'=>$store,'job_id'=>(int)$project->job_id,'item_id'=>$itemId ?: null,'material_name'=>$name,'required_qty'=>$qty,'unit_label'=>$unit,'unit_cost'=>$cost,'available_qty'=>$available,'supplier_id'=>$supplier ?: null,'status'=>'planned','expected_date'=>$date ?: null,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
            if (!$this->db->insert('db_building_project_materials',$row)) throw new RuntimeException('Could not save project material.');
            $id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Material requirement #'.$id.' added.');
            if($this->db->trans_status()===false)throw new RuntimeException('Could not save project material.');$this->db->trans_commit();return $id;
        } catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function reserve_material($jobId,$materialId,$quantity) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$quantity=(float)$quantity;
        if($quantity<=0)throw new InvalidArgumentException('Enter a positive reservation quantity.');
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $m=$this->db->where('id',(int)$materialId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_materials')->row();
            if(!$m)throw new InvalidArgumentException('Project material not found.');if(!$m->item_id)throw new InvalidArgumentException('Manual materials cannot be reserved until linked to a catalogue item.');
            $stock=$this->physical_stock($m->item_id);$other=(float)$this->db->select('COALESCE(SUM(reserved_qty),0) AS qty',false)->where('store_id',$store)->where('item_id',(int)$m->item_id)->where('status','reserved')->where('id !=',(int)$m->id)->get('db_building_project_materials')->row()->qty;
            $remaining=max(0,(float)$m->required_qty-(float)$m->reserved_qty);if($quantity>$remaining+0.0001)throw new InvalidArgumentException('Reservation exceeds the project requirement.');
            if($stock-$other-(float)$m->reserved_qty<$quantity-0.0001)throw new InvalidArgumentException('Insufficient available stock for this reservation.');
            $new=(float)$m->reserved_qty+$quantity;$status=$new+0.0001>=(float)$m->required_qty?'reserved':'part_reserved';
            $this->db->where('id',(int)$m->id)->where('store_id',$store)->update('db_building_project_materials',['available_qty'=>$stock,'reserved_qty'=>$new,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);
            $this->event($jobId,$project->stage,$project->stage,'Material #'.$m->id.' reserved: '.$quantity.' '.$m->unit_label.'.');
            $this->db->trans_commit();return true;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function create_procurement($jobId,$materialId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$supplier=(int)($input['supplier_id']??0);$date=trim((string)($input['expected_date']??''));$note=trim((string)($input['note']??''));
        if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||!$supplier)throw new InvalidArgumentException('Choose a supplier and valid request reference.');
        if($date){$d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$d||$d->format('Y-m-d')!==$date)throw new InvalidArgumentException('Invalid expected date.');}
        if(!$this->db->where('id',$supplier)->where('store_id',$store)->where('status',1)->get('db_suppliers')->row())throw new InvalidArgumentException('Supplier not found.');
        $this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_procurement')->row();
            if($prior){$this->db->trans_commit();return (int)$prior->id;}
            $m=$this->db->where('id',(int)$materialId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_materials')->row();
            if(!$m||!$m->item_id)throw new InvalidArgumentException('A catalogue material is required for procurement.');if($m->purchase_id)throw new InvalidArgumentException('This material already has a purchase order.');
            $qty=max(0,(float)$m->required_qty-(float)$m->reserved_qty-(float)$m->received_qty);if($qty<=0)throw new InvalidArgumentException('No outstanding quantity to procure.');
            $total=$qty*(float)$m->unit_cost;$now=date('Y-m-d H:i:s');$purchase=['store_id'=>$store,'warehouse_id'=>get_store_warehouse_id(),'count_id'=>get_count_id('db_purchase'),'purchase_code'=>get_init_code('purchase'),'reference_no'=>'PROJECT-'.$jobId.'-'.$m->id,'purchase_date'=>date('Y-m-d'),'purchase_status'=>'Ordered','supplier_id'=>$supplier,'subtotal'=>$total,'grand_total'=>$total,'purchase_note'=>$note,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>(string)$this->session->userdata('inv_username'),'status'=>1];
            if(!$this->db->insert('db_purchase',$purchase))throw new RuntimeException('Could not create purchase order.');$purchaseId=(int)$this->db->insert_id();
            $line=['store_id'=>$store,'purchase_id'=>$purchaseId,'purchase_status'=>'Ordered','item_id'=>(int)$m->item_id,'unit_name'=>$m->unit_label,'conversion_factor'=>1,'purchase_qty'=>$qty,'base_unit_qty'=>0,'received_qty'=>0,'price_per_unit'=>(float)$m->unit_cost,'unit_total_cost'=>(float)$m->unit_cost,'total_cost'=>$total,'description'=>'Building project #'.$jobId,'status'=>1];
            if(!$this->db->insert('db_purchaseitems',$line))throw new RuntimeException('Could not create purchase order line.');$lineId=(int)$this->db->insert_id();$hash=hash('sha256',json_encode([(int)$jobId,(int)$materialId,$supplier,$date,$note]));
            $this->db->insert('db_building_project_procurement',['store_id'=>$store,'job_id'=>(int)$jobId,'supplier_id'=>$supplier,'purchase_id'=>$purchaseId,'status'=>'ordered','expected_date'=>$date?:null,'note'=>$note,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now]);$id=(int)$this->db->insert_id();
            $this->db->where('id',(int)$m->id)->where('store_id',$store)->update('db_building_project_materials',['supplier_id'=>$supplier,'purchase_id'=>$purchaseId,'purchase_item_id'=>$lineId,'ordered_qty'=>$qty,'status'=>'ordered','expected_date'=>$date?:null,'updated_at'=>$now]);$this->event($jobId,$project->stage,$project->stage,'Purchase order '.$purchaseId.' created for material #'.$m->id.'.');
            if($this->db->trans_status()===false)throw new RuntimeException('Could not save procurement.');$this->db->trans_commit();return $id;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function receive_procurement($jobId,$procurementId,$quantity) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$quantity=(float)$quantity;if($quantity<=0)throw new InvalidArgumentException('Enter a positive receipt quantity.');
        $this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$p=$this->db->where('id',(int)$procurementId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_procurement')->row();
            if(!$p)throw new InvalidArgumentException('Project purchase order not found.');$m=$this->db->where('purchase_id',(int)$p->purchase_id)->where('store_id',$store)->get('db_building_project_materials')->row();$line=$this->db->where('id',(int)$m->purchase_item_id)->where('purchase_id',(int)$p->purchase_id)->where('store_id',$store)->get('db_purchaseitems')->row();
            if(!$m||!$line)throw new InvalidArgumentException('Purchase order line not found.');$already=(float)($line->received_qty??0);$outstanding=(float)$line->purchase_qty-$already;if($quantity>$outstanding+0.0001)throw new InvalidArgumentException('Receipt exceeds the outstanding purchase quantity.');$received=$already+$quantity;$status=$received+0.0001>=(float)$line->purchase_qty?'Received':'Partially Received';
            $this->db->where('id',(int)$line->id)->where('store_id',$store)->update('db_purchaseitems',['purchase_status'=>$status,'received_qty'=>$received,'base_unit_qty'=>$received]);$this->db->where('id',(int)$p->purchase_id)->where('store_id',$store)->update('db_purchase',['purchase_status'=>$status]);$newMaterial=(float)$m->received_qty+$quantity;$materialStatus=((float)$m->reserved_qty+$newMaterial)+0.0001>=(float)$m->required_qty?'received':'part_received';$this->db->where('id',(int)$m->id)->where('store_id',$store)->update('db_building_project_materials',['received_qty'=>$newMaterial,'status'=>$materialStatus,'updated_at'=>date('Y-m-d H:i:s')]);
            $this->load->model('pos_model');$this->pos_model->update_items_quantity((int)$m->item_id);update_warehouse_items([['item_id'=>(int)$m->item_id]]);$this->db->where('id',(int)$p->id)->where('store_id',$store)->update('db_building_project_procurement',['status'=>strtolower(str_replace(' ','_',$status)),'updated_at'=>date('Y-m-d H:i:s')]);$this->event($jobId,$project->stage,$project->stage,'Received '.$quantity.' '.$m->unit_label.' for purchase order '.$p->purchase_id.'.');
            if($this->db->trans_status()===false)throw new RuntimeException('Could not record receipt.');$this->db->trans_commit();return true;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function delivery_choices($jobId) {
        $this->writable($jobId);
        $rows=$this->materials($jobId);
        return array_values(array_filter($rows,function($row){return $row->item_id && ((float)$row->received_qty-(float)$row->issued_qty)>0.0001;}));
    }

    public function deliveries($jobId) {
        if (!$this->get($jobId) || !$this->db->table_exists('db_building_project_deliveries')) return [];
        $store=(int)get_current_store_id();$rows=$this->db->where('store_id',$store)->where('job_id',(int)$jobId)->order_by('trip_no','asc')->get('db_building_project_deliveries')->result();
        foreach($rows as $row){$row->items=$this->db->select('i.*,m.material_name,m.item_id')->from('db_building_project_delivery_items i')->join('db_building_project_materials m','m.id=i.material_id AND m.store_id=i.store_id')->where('i.store_id',$store)->where('i.delivery_id',(int)$row->id)->get()->result();}
        return $rows;
    }

    public function create_delivery($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$materialId=(int)($input['material_id']??0);$quantity=(float)($input['quantity']??0);$date=trim((string)($input['scheduled_date']??''));$driver=trim((string)($input['driver_name']??''));$vehicle=trim((string)($input['vehicle']??''));
        if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||$quantity<=0||strlen($driver)>160||strlen($vehicle)>120)throw new InvalidArgumentException('Choose a material, positive quantity and valid request reference.');
        if($date){$d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$d||$d->format('Y-m-d')!==$date)throw new InvalidArgumentException('Invalid scheduled date.');}
        $this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_deliveries')->row();if($prior){$this->db->trans_commit();return (int)$prior->id;}
            $m=$this->db->where('id',$materialId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_materials')->row();if(!$m||!$m->item_id)throw new InvalidArgumentException('Delivery material not found.');$available=(float)$m->received_qty-(float)$m->issued_qty;if($quantity>$available+0.0001)throw new InvalidArgumentException('Delivery exceeds the received project quantity.');
            $trip=(int)($this->db->select_max('trip_no')->where('store_id',$store)->where('job_id',(int)$jobId)->get('db_building_project_deliveries')->row()->trip_no)+1;if($trip<1)$trip=1;$code='DLV-'.$jobId.'-'.str_pad((string)$trip,3,'0',STR_PAD_LEFT);$now=date('Y-m-d H:i:s');$hash=hash('sha256',json_encode([(int)$jobId,$materialId,$quantity,$date,$driver,$vehicle]));
            if(!$this->db->insert('db_building_project_deliveries',['store_id'=>$store,'job_id'=>(int)$jobId,'delivery_code'=>$code,'trip_no'=>$trip,'status'=>'planned','scheduled_date'=>$date?:null,'driver_name'=>$driver?:null,'vehicle'=>$vehicle?:null,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now,'request_key'=>$key,'payload_hash'=>$hash]))throw new RuntimeException('Could not create delivery trip.');$id=(int)$this->db->insert_id();
            if(!$this->db->insert('db_building_project_delivery_items',['store_id'=>$store,'delivery_id'=>$id,'material_id'=>$materialId,'quantity'=>$quantity,'unit_label'=>$m->unit_label,'created_at'=>$now]))throw new RuntimeException('Could not save delivery line.');
            $this->event($jobId,$project->stage,$project->stage,'Delivery trip '.$code.' planned for '.$quantity.' '.$m->unit_label.'.');if($this->db->trans_status()===false)throw new RuntimeException('Could not create delivery trip.');$this->db->trans_commit();return $id;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function update_delivery($jobId,$deliveryId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$status=(string)($input['status']??'');$proof=trim((string)($input['proof_reference']??''));$note=trim((string)($input['proof_note']??''));$signed=trim((string)($input['signed_by']??''));
        if(!in_array($status,['planned','out_for_delivery','delivered','failed','cancelled'],true)||strlen($proof)>255||strlen($note)>8000||strlen($signed)>160)throw new InvalidArgumentException('Invalid delivery update.');
        $this->db->trans_begin();
        try{
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$delivery=$this->db->where('id',(int)$deliveryId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_deliveries')->row();if(!$delivery)throw new InvalidArgumentException('Delivery trip not found.');if($delivery->status==='delivered'&&$status==='delivered'){$this->db->trans_commit();return true;}if($status==='delivered'&&(!$proof||!$signed))throw new InvalidArgumentException('Proof reference and customer sign-off are required to complete delivery.');
            $line=$this->db->where('delivery_id',(int)$deliveryId)->where('store_id',$store)->get('db_building_project_delivery_items')->row();$m=$line?$this->db->where('id',(int)$line->material_id)->where('store_id',$store)->get('db_building_project_materials')->row():null;if(!$line||!$m)throw new InvalidArgumentException('Delivery line not found.');
            if($status==='delivered'&&$delivery->status!=='delivered'){
                $stock=$this->physical_stock($m->item_id);if($stock===null||$stock+0.0001<(float)$line->quantity)throw new InvalidArgumentException('Insufficient stock to issue this delivery.');
                $now=date('Y-m-d H:i:s');$reference='PROJECT-'.$jobId.'-'.$delivery->delivery_code;if(!$this->db->insert('db_stockadjustment',['store_id'=>$store,'warehouse_id'=>get_store_warehouse_id(),'reference_no'=>$reference,'adjustment_date'=>date('Y-m-d'),'adjustment_note'=>'Project delivery '.$delivery->delivery_code,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>(string)$this->session->userdata('inv_username'),'system_ip'=>$_SERVER['REMOTE_ADDR']??'localhost','system_name'=>gethostname(),'status'=>1]))throw new RuntimeException('Could not create delivery stock issue.');$adjustment=(int)$this->db->insert_id();if(!$this->db->insert('db_stockadjustmentitems',['store_id'=>$store,'warehouse_id'=>get_store_warehouse_id(),'adjustment_id'=>$adjustment,'item_id'=>(int)$m->item_id,'adjustment_qty'=>-(float)$line->quantity,'description'=>'Project delivery '.$delivery->delivery_code,'status'=>1]))throw new RuntimeException('Could not create delivery stock issue line.');$this->db->where('id',(int)$m->id)->where('store_id',$store)->update('db_building_project_materials',['issued_qty'=>(float)$m->issued_qty+(float)$line->quantity,'updated_at'=>$now]);$this->load->model('pos_model');$this->pos_model->update_items_quantity((int)$m->item_id);update_warehouse_items([['item_id'=>(int)$m->item_id]]);
                $this->db->where('id',(int)$deliveryId)->where('store_id',$store)->update('db_building_project_deliveries',['status'=>'delivered','proof_reference'=>$proof,'proof_note'=>$note?:null,'signed_by'=>$signed,'signed_at'=>$now,'updated_at'=>$now]);$this->event($jobId,$project->stage,$project->stage,'Delivery '.$delivery->delivery_code.' signed by '.$signed.'.');
            }else{$this->db->where('id',(int)$deliveryId)->where('store_id',$store)->update('db_building_project_deliveries',['status'=>$status,'proof_note'=>$note?:null,'updated_at'=>date('Y-m-d H:i:s')]);$this->event($jobId,$project->stage,$project->stage,'Delivery '.$delivery->delivery_code.' marked '.$status.'.');}
            if($this->db->trans_status()===false)throw new RuntimeException('Could not update delivery trip.');$this->db->trans_commit();return true;
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    private function installation_defaults() {
        return [
            'site_preparation'=>'Confirm site access, measurements and protection.',
            'materials_checked'=>'Check delivered materials against the project scope.',
            'installation_complete'=>'Complete installation and record any exceptions.',
            'testing_complete'=>'Test operation, safety controls and customer use.',
            'customer_walkthrough'=>'Walk the customer through operation and care.',
        ];
    }

    public function installation_steps($jobId) {
        if (!$this->get($jobId) || !$this->db->table_exists('db_building_project_installation_steps')) return [];
        $store=(int)get_current_store_id();$now=date('Y-m-d H:i:s');
        foreach($this->installation_defaults() as $key=>$title) {
            if(!$this->db->where('store_id',$store)->where('job_id',(int)$jobId)->where('step_key',$key)->count_all_results('db_building_project_installation_steps')) $this->db->insert('db_building_project_installation_steps',['store_id'=>$store,'job_id'=>(int)$jobId,'step_key'=>$key,'title'=>$title,'status'=>'pending','created_at'=>$now,'updated_at'=>$now]);
        }
        return $this->db->where('store_id',$store)->where('job_id',(int)$jobId)->order_by('id')->get('db_building_project_installation_steps')->result();
    }

    public function update_installation_step($jobId,$stepId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$status=(string)($input['status']??'');$note=trim((string)($input['note']??''));if(!in_array($status,['pending','in_progress','completed','blocked'],true)||strlen($note)>8000)throw new InvalidArgumentException('Invalid installation checklist update.');if($status==='completed'&&!$note)throw new InvalidArgumentException('Record the completion note before closing a checklist step.');
        $step=$this->db->where('id',(int)$stepId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_installation_steps')->row();if(!$step)throw new InvalidArgumentException('Installation checklist step not found.');$now=date('Y-m-d H:i:s');
        $this->db->where('id',(int)$stepId)->where('store_id',$store)->update('db_building_project_installation_steps',['status'=>$status,'note'=>$note?:null,'completed_by'=>$status==='completed'?(int)$this->session->userdata('inv_userid'):null,'completed_at'=>$status==='completed'?$now:null,'updated_at'=>$now]);$this->event($jobId,$project->stage,$project->stage,'Installation step “'.$step->title.'” marked '.$status.'.');return true;
    }

    public function handover($jobId) {
        if(!$this->get($jobId)||!$this->db->table_exists('db_building_project_handovers'))return null;
        return $this->db->where('store_id',(int)get_current_store_id())->where('job_id',(int)$jobId)->get('db_building_project_handovers')->row();
    }

    public function sign_handover($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$reference=trim((string)($input['certificate_reference']??''));$customer=trim((string)($input['customer_name']??''));$note=trim((string)($input['note']??''));if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||!$reference||strlen($reference)>120||!$customer||strlen($customer)>160||strlen($note)>8000)throw new InvalidArgumentException('Enter the certificate reference, customer name and valid request reference.');
        $steps=$this->installation_steps($jobId);foreach($steps as $step)if($step->status!=='completed')throw new InvalidArgumentException('Complete every installation checklist step before handover.');if($this->db->table_exists('db_building_project_deliveries')&&$this->db->where('store_id',$store)->where('job_id',(int)$jobId)->where('status !=','delivered')->count_all_results('db_building_project_deliveries'))throw new InvalidArgumentException('Complete every delivery trip before handover.');
        $hash=hash('sha256',json_encode([(int)$jobId,$reference,$customer,$note]));$this->db->trans_begin();
        try{$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_handovers')->row();if($prior){if(!hash_equals($prior->payload_hash,$hash))throw new InvalidArgumentException('Handover request reference already used.');$this->db->trans_commit();return (int)$prior->id;}$now=date('Y-m-d H:i:s');if(!$this->db->insert('db_building_project_handovers',['store_id'=>$store,'job_id'=>(int)$jobId,'certificate_reference'=>$reference,'customer_name'=>$customer,'note'=>$note?:null,'status'=>'signed','signed_by'=>(int)$this->session->userdata('inv_userid'),'signed_at'=>$now,'request_key'=>$key,'payload_hash'=>$hash]))throw new RuntimeException('Could not save handover certificate.');$id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Handover certificate '.$reference.' signed by '.$customer.'.');if($this->db->trans_status()===false)throw new RuntimeException('Could not save handover certificate.');$this->db->trans_commit();return $id;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function milestones($jobId) {
        if(!$this->get($jobId)||!$this->db->table_exists('db_building_project_milestones'))return [];
        $store=(int)get_current_store_id();$rows=$this->db->select('m.*,s.sales_code,s.grand_total')->from('db_building_project_milestones m')->join('db_sales s','s.id=m.sales_id AND s.store_id=m.store_id','left')->where('m.store_id',$store)->where('m.job_id',(int)$jobId)->order_by('m.id')->get()->result();
        foreach($rows as $row)if($row->status==='invoiced'&&$row->sales_id){$paid=(float)$this->db->select('COALESCE(SUM(payment),0) AS amount',false)->where('store_id',$store)->where('sales_id',(int)$row->sales_id)->get('db_salespayments')->row()->amount;if($paid+0.005>=(float)$row->amount){$this->db->where('id',(int)$row->id)->where('store_id',$store)->update('db_building_project_milestones',['status'=>'paid','updated_at'=>date('Y-m-d H:i:s')]);$row->status='paid';}}
        return $rows;
    }

    public function create_milestone_plan($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$rows=(array)($input['milestones']??[]);$finance=$this->financials($jobId);$revenue=(float)$finance['revenue'];if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||$revenue<=0||count($rows)<1)throw new InvalidArgumentException('Link a quotation or invoice before creating billing milestones.');
        $clean=[];$total=0;foreach($rows as $row){$title=trim((string)($row['title']??''));$percentage=(float)($row['percentage']??0);$milestoneKey=preg_replace('/[^a-z0-9_-]+/i','_',strtolower((string)($row['key']??$title)));if(!$milestoneKey||!$title||$percentage<=0||$percentage>100||strlen($title)>160)throw new InvalidArgumentException('Enter valid milestone titles and percentages.');$clean[]=['key'=>$milestoneKey,'title'=>$title,'percentage'=>$percentage];$total+=$percentage;}if(abs($total-100)>0.001)throw new InvalidArgumentException('Billing milestone percentages must total 100%.');$hash=hash('sha256',json_encode([(int)$jobId,$clean,$revenue]));$this->db->trans_begin();
        try{$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_milestones')->row();if($prior){if(!hash_equals($prior->payload_hash,$hash))throw new InvalidArgumentException('Milestone request reference already used.');$this->db->trans_commit();return (int)$prior->id;}$now=date('Y-m-d H:i:s');$first=0;foreach($clean as $row){$requestKey=$first?$key.'-'.$row['key']:$key;$this->db->insert('db_building_project_milestones',['store_id'=>$store,'job_id'=>(int)$jobId,'milestone_key'=>$row['key'],'title'=>$row['title'],'percentage'=>$row['percentage'],'amount'=>round($revenue*$row['percentage']/100,2),'status'=>'planned','request_key'=>$requestKey,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now]);if(!$first)$first=(int)$this->db->insert_id();}$this->event($jobId,$project->stage,$project->stage,'Billing milestone plan created.');if($this->db->trans_status()===false)throw new RuntimeException('Could not save milestone plan.');$this->db->trans_commit();return $first;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function link_milestone_invoice($jobId,$milestoneId,$salesId) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$milestone=$this->db->where('id',(int)$milestoneId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_milestones')->row();$invoice=$this->db->where('id',(int)$salesId)->where('store_id',$store)->where('customer_id',(int)$project->customer_id)->where('sales_status','Final')->get('db_sales')->row();if(!$milestone||!$invoice)throw new InvalidArgumentException('Choose a final invoice belonging to this project customer.');$this->db->where('id',(int)$milestoneId)->where('store_id',$store)->update('db_building_project_milestones',['sales_id'=>(int)$salesId,'status'=>'invoiced','updated_at'=>date('Y-m-d H:i:s')]);$this->event($jobId,$project->stage,$project->stage,'Milestone '.$milestone->title.' linked to invoice '.$invoice->sales_code.'.');return true;
    }

    public function assets($jobId) {
        if(!$this->get($jobId)||!$this->db->table_exists('db_building_project_assets'))return [];
        return $this->db->select('a.*,e.equipment_status,i.item_name')->from('db_building_project_assets a')->join('db_customer_equipment e','e.id=a.equipment_id AND e.store_id=a.store_id')->join('db_items i','i.id=e.item_id AND i.store_id=e.store_id','left')->where('a.store_id',(int)get_current_store_id())->where('a.job_id',(int)$jobId)->order_by('a.id','desc')->get()->result();
    }

    public function register_asset($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();if(!$this->handover($jobId))throw new InvalidArgumentException('Sign the project handover before registering installed assets.');$key=(string)($input['request_key']??'');$materialId=(int)($input['material_id']??0);$tag=trim((string)($input['asset_tag']??''));$serial=trim((string)($input['serial_number']??''));$model=trim((string)($input['model']??''));$months=(int)($input['warranty_months']??12);$note=trim((string)($input['note']??''));if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||!$tag||strlen($tag)>120||strlen($serial)>120||strlen($model)>160||$months<0||$months>120||strlen($note)>8000)throw new InvalidArgumentException('Enter an asset tag and valid warranty details.');
        $this->db->trans_begin();
        try{$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_assets')->row();if($prior){$this->db->trans_commit();return (int)$prior->id;}$m=$this->db->where('id',$materialId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_materials')->row();if(!$m||!$m->item_id)throw new InvalidArgumentException('Choose a delivered catalogue material for the installed asset.');$item=$this->db->where('id',(int)$m->item_id)->where('store_id',$store)->where('status',1)->get('db_items')->row();if(!$item)throw new InvalidArgumentException('Installed asset item not found.');$start=date('Y-m-d');$end=$months?date('Y-m-d',strtotime('+'.$months.' months',strtotime($start))):null;$now=date('Y-m-d H:i:s');$equipment=['store_id'=>$store,'customer_id'=>(int)$project->customer_id,'site_id'=>(int)$project->site_id?:null,'item_id'=>(int)$item->id,'serial_number'=>$serial?:null,'model'=>$model?:$item->item_name,'sales_id'=>(int)$project->sales_id?:null,'install_date'=>$start,'commissioned_date'=>$start,'warranty_months'=>$months,'warranty_start'=>$start,'warranty_end'=>$end,'coverage_type'=>'standard','equipment_status'=>'installed','notes'=>$note?:null,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_date'=>$start,'created_time'=>date('H:i:s')];if(!$this->db->insert('db_customer_equipment',$equipment))throw new RuntimeException('Could not register installed equipment.');$equipmentId=(int)$this->db->insert_id();$hash=hash('sha256',json_encode([(int)$jobId,$materialId,$tag,$serial,$model,$months,$note]));if(!$this->db->insert('db_building_project_assets',['store_id'=>$store,'job_id'=>(int)$jobId,'equipment_id'=>$equipmentId,'material_id'=>$materialId,'asset_tag'=>$tag,'serial_number'=>$serial?:null,'model'=>$model?:$item->item_name,'warranty_months'=>$months,'warranty_start'=>$start,'warranty_end'=>$end,'status'=>'installed','note'=>$note?:null,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now]))throw new RuntimeException('Could not save project asset.');$id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Installed asset '.$tag.' registered.');if($this->db->trans_status()===false)throw new RuntimeException('Could not register installed asset.');$this->db->trans_commit();return $id;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }

    public function maintenance_plans($jobId) {
        if(!$this->get($jobId)||!$this->db->table_exists('db_building_project_maintenance'))return [];
        return $this->db->select('m.*,a.asset_tag')->from('db_building_project_maintenance m')->join('db_building_project_assets a','a.id=m.asset_id AND a.store_id=m.store_id')->where('m.store_id',(int)get_current_store_id())->where('m.job_id',(int)$jobId)->order_by('m.due_date')->get()->result();
    }

    public function schedule_maintenance($jobId,$assetId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$date=trim((string)($input['due_date']??''));$interval=(int)($input['interval_months']??12);$note=trim((string)($input['note']??''));$d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||!$d||$d->format('Y-m-d')!==$date||$interval<1||$interval>120||strlen($note)>8000)throw new InvalidArgumentException('Enter a valid maintenance date and interval.');
        $asset=$this->db->where('id',(int)$assetId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_assets')->row();if(!$asset)throw new InvalidArgumentException('Installed asset not found.');$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_maintenance')->row();if($prior)return (int)$prior->id;$now=date('Y-m-d H:i:s');if(!$this->db->insert('db_building_project_maintenance',['store_id'=>$store,'job_id'=>(int)$jobId,'asset_id'=>(int)$assetId,'due_date'=>$date,'interval_months'=>$interval,'status'=>'planned','note'=>$note?:null,'request_key'=>$key,'payload_hash'=>hash('sha256',json_encode([(int)$jobId,$assetId,$date,$interval,$note])),'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now]))throw new RuntimeException('Could not schedule maintenance.');$id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Maintenance scheduled for asset '.$asset->asset_tag.'.');return $id;
    }

    public function warranty_claims($jobId) {
        if(!$this->get($jobId)||!$this->db->table_exists('db_building_project_warranty_claims'))return [];
        return $this->db->select('c.*,a.asset_tag')->from('db_building_project_warranty_claims c')->join('db_building_project_assets a','a.id=c.asset_id AND a.store_id=c.store_id')->where('c.store_id',(int)get_current_store_id())->where('c.job_id',(int)$jobId)->order_by('c.id','desc')->get()->result();
    }

    public function create_warranty_claim($jobId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$key=(string)($input['request_key']??'');$assetId=(int)($input['asset_id']??0);$reference=trim((string)($input['claim_reference']??''));$issue=trim((string)($input['issue']??''));if(!preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)||!$reference||strlen($reference)>120||!$issue||strlen($issue)>8000)throw new InvalidArgumentException('Enter a claim reference and issue description.');$asset=$this->db->where('id',$assetId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_assets')->row();if(!$asset)throw new InvalidArgumentException('Installed asset not found.');$prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_building_project_warranty_claims')->row();if($prior)return (int)$prior->id;$now=date('Y-m-d H:i:s');if(!$this->db->insert('db_building_project_warranty_claims',['store_id'=>$store,'job_id'=>(int)$jobId,'asset_id'=>$assetId,'claim_reference'=>$reference,'issue'=>$issue,'status'=>'open','request_key'=>$key,'payload_hash'=>hash('sha256',json_encode([(int)$jobId,$assetId,$reference,$issue])),'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>$now,'updated_at'=>$now]))throw new RuntimeException('Could not save warranty claim.');$id=(int)$this->db->insert_id();$this->event($jobId,$project->stage,$project->stage,'Warranty claim '.$reference.' opened for asset '.$asset->asset_tag.'.');return $id;
    }

    public function resolve_warranty_claim($jobId,$claimId,array $input) {
        $project=$this->writable($jobId);$store=(int)get_current_store_id();$status=(string)($input['status']??'');$resolution=trim((string)($input['resolution']??''));if(!in_array($status,['open','approved','rejected','resolved'],true)||($status==='resolved'&&!$resolution)||strlen($resolution)>8000)throw new InvalidArgumentException('Choose a valid claim status and resolution.');$claim=$this->db->where('id',(int)$claimId)->where('job_id',(int)$jobId)->where('store_id',$store)->get('db_building_project_warranty_claims')->row();if(!$claim)throw new InvalidArgumentException('Warranty claim not found.');$this->db->where('id',(int)$claimId)->where('store_id',$store)->update('db_building_project_warranty_claims',['status'=>$status,'resolution'=>$resolution?:null,'updated_at'=>date('Y-m-d H:i:s')]);$this->event($jobId,$project->stage,$project->stage,'Warranty claim '.$claim->claim_reference.' marked '.$status.'.');return true;
    }
}
