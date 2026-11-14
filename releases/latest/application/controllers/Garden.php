<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Garden extends MY_Controller {
    private $profile;
    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->helper('garden');
        $this->profile = mp_get_store_profile();
        if (($this->profile['industry_type'] ?? '') !== 'garden_centre_landscaping') {
            $this->show_access_denied_page('This workspace is for Garden Centre & Landscaping businesses.');
            exit;
        }
        $this->load->model('garden_model','garden');
    }

    private function guard($permission, $mode = null) {
        $this->permission_check($permission);
        if ($mode && !mp_garden_has_mode($this->profile,$mode)) {
            $this->show_access_denied_page('This operating mode is not enabled.'); exit;
        }
        if (!$this->garden->ready()) show_error('Run the pending Garden Centre migration (4.0.9.155) before opening this workspace.',503);
    }

    private function render($title, $view, array $extra = []) {
        $d = array_merge($this->data,['page_title'=>$title,'garden_profile'=>$this->profile],$extra);
        $d['garden_overview'] = ($view === 'overview');
        if (!empty($d['store_name'])) $d['SITE_TITLE']=$d['store_name'];
        $d['garden_content'] = $this->load->view('garden/'.$view,$d,true);
        // Dimensions/orientation are resolved in the browser; the mobile document
        // is rendered independently and never includes mp_layout.
        if ($this->input->get('screen') === 'mobile') {
            $d['garden_content'] = mp_garden_mobile_links($d['garden_content']);
            $this->load->view('mobile/garden',$d);
        } else {
            $d['content'] = $this->load->view('garden/desktop',$d,true);
            $this->load->view('mp_layout',$d);
        }
    }

    public function index() {
        $this->guard('dashboard_view');
        $allowed=[];foreach (['items_view','service_jobs_view'] as $permission) $allowed[$permission]=$this->permissions($permission);
        // Reuse the main dashboard's range calculations and accounting sources.
        $this->load->model('dashboard_model');
        $range=$this->input->get('range');
        if (!is_string($range) || !in_array($range,['Today','7Days','30Days','LastMonth','ThisMonth','ThisYear'],true)) $range='Today';
        $pulse=['range'=>$range,'period'=>$this->dashboard_model->get_range_info($range),'cards'=>[]];
        if ($this->permissions('sales_view')) {
            $sales=$this->dashboard_model->get_sales_by_range($range);
            $invoices=$this->dashboard_model->get_invoices_by_range($range);
            $pulse['cards'][]=['Sales',$sales['today'],true,'Final invoices in this period','sales','fa-line-chart'];
            $pulse['cards'][]=['Invoices',$invoices['count'],false,'Final invoices in this period','sales','fa-file-text-o'];
        }
        if ($this->permissions('profit_report')) {
            $profit=$this->dashboard_model->get_profit_by_range($range);
            $pulse['cards'][]=['Gross profit',$profit['profit'],true,'Sales less recorded product costs','reports/profit_loss','fa-pie-chart'];
        }
        if ($this->permissions('expense_view')) $pulse['cards'][]=['Expenses',$this->dashboard_model->get_expenses_by_range($range),true,'Recorded in this period','expense','fa-credit-card'];
        if ($this->permissions('sales_view') && $this->permissions('customers_view')) {
            $outstanding=$this->dashboard_model->get_outstanding_debts();
            $pulse['cards'][]=['Customer balances',$outstanding['total'],true,'Current outstanding · all dates','customers','fa-money'];
        }
        $daily=$this->garden->daily_workspace($allowed,$this->profile);
        $this->load->model('intelligence_model');
        $canSales=$this->permissions('sales_view');$canItems=$this->permissions('items_view');
        $intel=$this->intelligence_model->for_retail(get_current_store_id(),[
            'stock_cover'=>$canSales && $canItems,'sales_trend'=>$canSales,
            'best_performer'=>$canSales && $canItems,'debt'=>$canSales && $this->permissions('customers_view'),
            'slow_movers'=>$canSales && $canItems && $this->permissions('profit_report'),
        ]);
        if (!empty($daily['unassigned'])) array_unshift($intel,['tone'=>'warn','icon'=>'fa-calendar-check-o',
            'text'=>$daily['unassigned'].' open job(s) have no team member assigned. Assign the work before confirming visits.',
            'action'=>['label'=>'Review team work','url'=>base_url('garden/work')]]);
        $this->render('Garden Workspace','overview',['garden_daily'=>$daily,'garden_pulse'=>$pulse,'garden_intel'=>array_slice($intel,0,8),'garden_license'=>function_exists('mp_get_license_usage_summary') ? mp_get_license_usage_summary() : null]);
    }

    public function catalogue() {
        $this->guard('items_view');
        $page=max(1,(int)$this->input->get('page'));
        $query=trim((string)$this->input->get('q'));
        $catalogue=$this->garden->catalogue_page($page,$query);
        $this->render('Catalogue','catalogue',[
            'garden_items'=>$catalogue['items'],'garden_catalogue_page'=>$catalogue['page'],
            'garden_catalogue_pages'=>$catalogue['pages'],'garden_catalogue_total'=>$catalogue['total'],
            'garden_catalogue_query'=>$query,'garden_catalogue_counts'=>$catalogue['counts'],
        ]);
    }

    public function plant($id = 0) {
        $this->guard('items_edit');
        $item = $this->db->where('id',(int)$id)->where('store_id',get_current_store_id())->get('db_items')->row();
        if (!$item) { show_404(); return; }
        $attributes = $this->garden->attributes($id);
        $this->render('Plant Attributes','plant',['garden_item'=>$item,'plant_attributes'=>$attributes,'garden_fields'=>mp_garden_attributes()]);
    }

    public function save_plant() {
        $this->guard('items_edit');
        $this->mutate(function() {
            if (!$this->garden->save_attributes((int)$this->input->post('item_id'),(array)$this->input->post('attributes'),(bool)$this->input->post('is_living'))) {
                throw new RuntimeException('Could not save plant attributes.');
            }
        },'garden/catalogue');
    }

    public function nursery() {
        $this->guard('stock_adjustment_view','nursery');
        $this->render('Nursery Stock & Losses','nursery',[
            'garden_items'=>$this->garden->catalogue(true),'garden_losses'=>$this->garden->losses(),
            'garden_batches'=>$this->garden->batches(),
            'garden_warehouses'=>$this->db->where('store_id',get_current_store_id())->where('status',1)->get('db_warehouse')->result(),
            'request_key'=>bin2hex(random_bytes(20)),
        ]);
    }

    public function record_loss() {
        $this->guard('stock_adjustment_add','nursery');
        $this->mutate(function() { $this->garden->record_loss((array)$this->input->post()); },'garden/nursery');
    }

    public function settings() {
        $this->guard('business_profile');
        $this->render('Operating Modes','settings',['garden_modes'=>mp_get_business_presets()['garden_centre_landscaping']['operating_modes']]);
    }

    public function save_settings() {
        $this->guard('business_profile');
        $this->mutate(function() {
            $modes = mp_garden_modes($this->input->post('modes'));
            $settings = $this->profile['settings'] ?? [];
            $settings['garden_operating_modes'] = $modes;
            $this->load->model('business_profile_model');
            if (!$this->business_profile_model->update_profile(get_current_store_id(),['industry_settings_json'=>json_encode($settings)])) {
                throw new RuntimeException('Could not save operating modes.');
            }
        },'garden/settings');
    }

    private function service_guard($permission, $mode) {
        $this->guard($permission,$mode);
        if (!mp_feature_enabled('service_jobs')) { $this->show_feature_not_activated('service_jobs'); exit; }
        foreach (['db_service_schedules','db_service_job_context','db_service_material_requests'] as $table) {
            if (!$this->db->table_exists($table)) show_error('Run pending service operations migrations (4.0.9.156–157).',503);
        }
    }

    private function service_form_data() {
        $storeId=get_current_store_id();
        return ['garden_customers'=>$this->db->where('store_id',$storeId)->where('status',1)->order_by('customer_name')->get('db_customers')->result(),
            'garden_staff'=>$this->db->where('store_id',$storeId)->where('status',1)->where_not_in('role_id',get_excluded_staff_roles($storeId))->where_not_in('id',get_excluded_user_ids($storeId))->order_by('username')->get('db_users')->result(),
            'garden_sites'=>$this->db->select('s.*,c.customer_name')->from('db_shippingaddress s')->join('db_customers c','c.id=s.customer_id AND c.store_id=s.store_id')->where('s.store_id',$storeId)->get()->result(),
            'request_key'=>bin2hex(random_bytes(20))];
    }

    public function projects() {
        $this->service_guard('service_jobs_view','landscaping');
        $this->load->model('service_project_model');
        $this->render('Landscaping Projects','projects',array_merge($this->service_form_data(),['garden_projects'=>$this->service_project_model->listing()]));
    }

    public function create_project() {
        $this->service_guard('service_jobs_add','landscaping');
        $this->mutate(function() { $this->load->model('service_project_model');$this->service_project_model->create((array)$this->input->post()); },'garden/projects');
    }

    public function project($jobId = 0) {
        $this->service_guard('service_jobs_view','landscaping');
        $this->load->model('service_project_model');$this->load->model('equipment_model');
        $project=$this->service_project_model->get($jobId);
        if (!$project) { show_404();return; }
        $finance=$this->permissions('sales_view') && $this->permissions('expense_view') ? $this->service_project_model->financials($jobId) : null;
        $choices=$this->service_project_model->document_choices($jobId);
        $this->render('Project — '.$project->title,'project',array_merge($this->service_form_data(),[
            'garden_project'=>$project,'garden_finance'=>$finance,'garden_history'=>$this->service_project_model->history($jobId),
            'garden_items'=>$this->permissions('items_view') ? $this->garden->catalogue() : [],'request_key'=>bin2hex(random_bytes(20)),
            'garden_workbench_ready'=>$this->service_project_model->workbench_ready(),
            'garden_records'=>$this->service_project_model->records($jobId),'garden_tasks'=>$this->service_project_model->tasks($jobId),
            'garden_quotes'=>$this->permissions('quotation_view') ? $choices['quotes'] : [],
            'garden_invoices'=>$this->permissions('sales_view') ? $choices['invoices'] : [],
            'garden_expenses'=>$this->permissions('expense_view') ? $choices['expenses'] : [],
            'garden_linked_expenses'=>$this->permissions('expense_view') ? $this->service_project_model->expense_cards($jobId) : [],
        ]));
    }

    public function project_record() {
        $this->service_guard('service_jobs_edit','landscaping');
        $this->mutate(function(){ $this->load->model('service_project_model');$this->service_project_model->save_record((int)$this->input->post('job_id'),(array)$this->input->post()); },'garden/project/'.(int)$this->input->post('job_id').'#project-site');
    }

    public function project_task() {
        $this->service_guard('service_jobs_edit','landscaping');
        $this->mutate(function(){ $this->load->model('service_project_model');$this->service_project_model->create_task((int)$this->input->post('job_id'),(array)$this->input->post()); },'garden/project/'.(int)$this->input->post('job_id').'#project-tasks');
    }

    public function project_task_move() {
        $this->service_guard('service_jobs_edit','landscaping');
        $this->mutate(function(){ $this->load->model('service_project_model');if (!$this->service_project_model->workbench_ready()) throw new InvalidArgumentException('Run migration 4.0.9.159.');$this->service_project_model->move_task((int)$this->input->post('job_id'),(int)$this->input->post('task_id'),$this->input->post('status'),(int)$this->input->post('version')); },'garden/project/'.(int)$this->input->post('job_id').'#project-tasks');
    }

    public function project_approval() {
        $this->service_guard('service_jobs_edit','landscaping');$this->permission_check('quotation_edit');
        $this->mutate(function(){
            if ($this->input->post('approval_confirmed')!=='1') throw new InvalidArgumentException('Confirm that customer approval was actually received.');
            $this->load->model('service_project_model');$this->service_project_model->approve_quote((int)$this->input->post('job_id'),(int)$this->input->post('revision'),$this->input->post('evidence'));
        },'garden/project/'.(int)$this->input->post('job_id'));
    }

    public function advance_project() {
        $this->service_guard('service_jobs_edit','landscaping');
        $this->mutate(function() {
            $stage=$this->input->post('stage');
            if ($stage==='approved') $this->permission_check('quotation_edit');
            if (in_array($stage,['deposit','paid','closed'],true)) $this->permission_check('sales_view');
            $this->load->model('service_project_model');$this->service_project_model->advance((int)$this->input->post('job_id'),$stage,(array)$this->input->post());
        },'garden/project/'.(int)$this->input->post('job_id'));
    }

    public function project_material() {
        $this->service_guard('service_jobs_edit','landscaping');
        $this->mutate(function() {
            $this->load->model('service_project_model');$project=$this->service_project_model->get((int)$this->input->post('job_id'));
            if (!$project || $project->stage!=='execution') throw new InvalidArgumentException('Issue materials while the project is in execution.');
            $this->load->model('equipment_model');$this->equipment_model->consume_material((array)$this->input->post());
        },'garden/project/'.(int)$this->input->post('job_id'));
    }

    public function project_expense() {
        $this->service_guard('service_jobs_edit','landscaping');$this->permission_check('expense_view');
        $this->mutate(function() {
            $this->load->model('service_project_model');
            if (!$this->service_project_model->link_expense((int)$this->input->post('job_id'),(int)$this->input->post('expense_id'))) throw new RuntimeException('Could not link expense.');
        },'garden/project/'.(int)$this->input->post('job_id'));
    }

    public function maintenance() {
        $this->service_guard('service_jobs_view','maintenance');$this->load->model('service_schedule_model');
        $this->render('Maintenance Schedules','maintenance',array_merge($this->service_form_data(),['garden_schedules'=>$this->service_schedule_model->listing()]));
    }

    public function create_schedule() {
        $this->service_guard('service_jobs_add','maintenance');
        $this->mutate(function() { $this->load->model('service_schedule_model');$this->service_schedule_model->create((array)$this->input->post()); },'garden/maintenance');
    }

    public function generate_visits() {
        $this->service_guard('service_jobs_add','maintenance');
        $this->mutate(function() { $this->load->model('service_schedule_model');$this->service_schedule_model->generate((int)$this->input->post('schedule_id'),$this->input->post('through')); },'garden/maintenance');
    }

    public function cancel_schedule() {
        $this->service_guard('service_jobs_edit','maintenance');
        $this->mutate(function() { $this->load->model('service_schedule_model');$this->service_schedule_model->cancel((int)$this->input->post('schedule_id')); },'garden/maintenance');
    }

    public function work() {
        $this->service_guard('service_jobs_view',mp_garden_has_mode($this->profile,'maintenance') ? 'maintenance' : 'landscaping');
        $this->load->model('equipment_model');
        $this->render('Service Work & Visits','work',array_merge($this->service_form_data(),[
            'garden_work'=>$this->equipment_model->get_job_list(['limit'=>250]),
            'garden_project_ids'=>array_flip(array_column($this->db->select('job_id')->where('store_id',get_current_store_id())->get('db_service_job_context')->result_array(),'job_id')),
            'garden_items'=>$this->permissions('items_view') ? $this->garden->catalogue() : [],
        ]));
    }

    public function work_status() {
        $this->service_guard('service_jobs_edit',mp_garden_has_mode($this->profile,'maintenance') ? 'maintenance' : 'landscaping');
        $this->mutate(function() {
            $this->load->model('equipment_model');$result=$this->equipment_model->update_job_status();
            if ($result!=='success') throw new InvalidArgumentException($result);
        },'garden/work');
    }

    public function work_assign() {
        $this->service_guard('service_jobs_edit',mp_garden_has_mode($this->profile,'maintenance') ? 'maintenance' : 'landscaping');
        $this->mutate(function() {
            $this->load->model('equipment_model');$job=$this->equipment_model->get_job((int)$this->input->post('job_id'));
            if (!$job || in_array($job->status,['completed','cancelled'],true)) throw new InvalidArgumentException('Choose an open job.');
            $this->equipment_model->save_job_data(['customer_id'=>$job->customer_id,'site_id'=>$job->site_id,'equipment_id'=>$job->equipment_id,
                'sales_id'=>$job->sales_id,'status'=>$job->status,'title'=>$job->title,
                'assigned_user_id'=>(int)$this->input->post('assigned_user_id') ?: null,'scheduled_date'=>$this->input->post('scheduled_date') ?: null],$job->id);
        },'garden/work');
    }

    public function work_material() {
        $this->service_guard('service_jobs_edit',mp_garden_has_mode($this->profile,'maintenance') ? 'maintenance' : 'landscaping');$this->permission_check('items_view');
        $this->mutate(function() { $this->load->model('equipment_model');$this->equipment_model->consume_material((array)$this->input->post()); },'garden/work');
    }

    public function business_tools() {
        if($this->input->get('screen')==='mobile'){redirect('mobile/more');return;}
        $html=$this->load->view('mp_sidebar',array_merge($this->data,['garden_tools_only'=>true]),true);
        $this->render('Business tools','business_tools',['business_tool_groups'=>mp_garden_business_tool_groups($html)]);
    }

    public function sales_report() {
        $this->guard('sales_report');
        $from=$this->input->get('date_from') ?? date('Y-m-01');
        $to=$this->input->get('date_to') ?? date('Y-m-d');
        foreach ([$from,$to] as $value) {
            $date=is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d',$value) : false;
            if (!$date || $date->format('Y-m-d')!==$value) { show_error('Choose valid report dates.',400);return; }
        }
        if ($from>$to) { show_error('The end date must follow the start date.',400);return; }
        $branches=$this->garden->report_branches();
        $branch=$this->input->get('branch_id') ?? '';
        if (!is_scalar($branch) || ($branch!=='' && (!ctype_digit((string)$branch) || !in_array((int)$branch,array_map(function($row){return (int)$row->id;},$branches),true)))) {
            show_error('Choose a branch available to your account.',400);return;
        }
        $ids=array_map(function($row){return (int)$row->id;},$branches);
        if ($branch!=='') $ids=[(int)$branch];
        $invoice_page=max(1,(int)$this->input->get('invoice_page'));
        $this->output->set_header('Cache-Control: no-store');
        $this->render('Sales & Collections','sales_report',[
            'report_from'=>$from,'report_to'=>$to,'report_branch'=>(string)$branch,'report_branches'=>$branches,
            'sales_report'=>$this->garden->sales_collections($from,$to,$ids,$invoice_page),
        ]);
    }

    public function daily_summary() {
        $this->guard('dashboard_view');$from=$this->input->get('date_from') ?: ($this->input->get('date') ?: date('Y-m-d'));$to=$this->input->get('date_to') ?: $from;
        foreach([$from,$to] as $date){if(!is_string($date)){show_error('Choose valid summary dates.',400);return;}$d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);if(!$d||$d->format('Y-m-d')!==$date){show_error('Choose valid summary dates.',400);return;}}
        if($from>$to){show_error('The end date must follow the start date.',400);return;}
        $allowed=[];foreach(['sales_view','profit_report','expense_view','customers_view','stock_adjustment_view','service_jobs_view','production_batches_view'] as $p)$allowed[$p]=$this->permissions($p);
        $allowed['production_batches_view']=$allowed['production_batches_view'] && mp_feature_enabled('production_workflow');$allowed['service_jobs_view']=$allowed['service_jobs_view'] && mp_feature_enabled('service_jobs');$allowed['stock_adjustment_view']=$allowed['stock_adjustment_view'] && mp_garden_has_mode($this->profile,'nursery');
        $this->load->model('dashboard_model');$summary=$this->dashboard_model->get_daily_summary($from,$to);
        $this->output->set_header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $this->render('Garden daily summary','daily_summary',['garden_summary'=>$summary,'garden_activity'=>$this->garden->period_workspace($from,$to,$allowed),'summary_permissions'=>$allowed,'summary_from'=>$from,'summary_to'=>$to]);
    }

    private function production_guard($permission,$feature='production_workflow') {
        $this->guard($permission);if(!mp_feature_enabled($feature)) { $this->show_feature_not_activated($feature);exit; }
        $this->load->model('garden_production_model','production');
        if(!$this->production->ready())show_error('Run Garden production migration 4.0.9.160 before using production.',503);
    }
    private function production_form_data() {
        return ['production_items'=>$this->db->select('i.*,u.unit_name')->from('db_items i')->join('db_units u','u.id=i.unit_id','left')->where('i.store_id',get_current_store_id())->where('i.status',1)->where('i.service_bit',0)->where('i.is_bundle',0)->order_by('i.item_name')->get()->result(),
            'production_warehouses'=>$this->db->where('store_id',get_current_store_id())->where('status',1)->get('db_warehouse')->result(),'request_key'=>bin2hex(random_bytes(20))];
    }
    public function materials() {
        $this->production_guard('recipes_view','recipe_tracking');$templates=$this->production->templates();$lines=[];foreach($templates as $t)$lines[$t->id]=$this->production->ingredients($t->id);
        $this->render('Materials & costing','materials',array_merge($this->production_form_data(),['production_templates'=>$templates,'template_lines'=>$lines]));
    }
    public function production_template_save() {
        $this->production_guard('recipes_add','recipe_tracking');$this->permission_check('items_view');
        $this->mutate(function(){$this->production->create_template((array)$this->input->post());},'garden/materials');
    }
    public function production() {
        $this->production_guard('production_batches_view');$this->render('Production batches','production',array_merge($this->production_form_data(),['production_templates'=>$this->production->templates(),'production_batches'=>$this->production->listing()]));
    }
    public function production_create() {
        $this->production_guard('production_batches_add');$this->permission_check('recipes_view');$this->permission_check('items_view');
        $this->mutate(function(){$this->production->create_batch((array)$this->input->post());},'garden/production');
    }
    public function production_batch($id=0) {
        $this->production_guard('production_batches_view');$batch=$this->production->get($id);if(!$batch){show_404();return;}
        $this->render($batch->batch_name,'production_batch',array_merge($this->production_form_data(),['production_batch'=>$batch,'production_entries'=>$this->production->entries($id),'production_totals'=>$this->production->totals($id)]));
    }
    public function production_action() {
        $this->production_guard('production_batches_edit');$this->permission_check('items_view');
        $this->mutate(function(){$this->production->act((int)$this->input->post('batch_id'),(array)$this->input->post());},'garden/production_batch/'.(int)$this->input->post('batch_id'));
    }

    private function mutate(callable $fn, $return) {
        if ($this->input->method() !== 'post') { show_error('POST required.',405); return; }
        try { $fn(); $this->session->set_flashdata('garden_success','Saved successfully.'); }
        catch (InvalidArgumentException $e) { $this->session->set_flashdata('garden_error',$e->getMessage()); }
        catch (Throwable $e) {
            log_message('error','Garden operation failed: '.$e->getMessage());
            $this->session->set_flashdata('garden_error','Could not save. No changes were committed.');
        }
        redirect($return.($this->input->post('screen') === 'mobile' ? '?screen=mobile' : ''));
    }
}
