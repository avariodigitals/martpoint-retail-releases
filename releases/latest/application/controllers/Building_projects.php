<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Building Materials project control.
 *
 * This is a coordination layer over the existing service-job, quotation and
 * invoice records. It intentionally does not create a second sales or stock
 * engine. Mutating project actions remain in the shared project model/workbench.
 */
class Building_projects extends MY_Controller {
    private $profile;

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->profile = mp_get_store_profile();
        if (($this->profile['industry_type'] ?? '') !== 'building_materials') {
            $this->show_access_denied_page('This workspace is for Building Materials businesses.');
            exit;
        }
        if (!is_store_admin() && !$this->permissions('sales_view') && !$this->permissions('service_jobs_view')) {
            $this->show_access_denied_page();
            exit;
        }
        $this->load->model('service_project_model', 'project_model');
        $this->load->helper('building_workspace');
    }

    private function stages() {
        return [
            'enquiry' => 'Planning',
            'inspection' => 'Site survey',
            'quoted' => 'Quoted',
            'approved' => 'Approved',
            'deposit' => 'Deposit received',
            'execution' => 'In progress',
            'completed' => 'Completed',
            'paid' => 'Paid',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
        ];
    }

    private function render($view, array $extra = []) {
        $data = array_merge($this->data, [
            'page_title' => 'Projects',
            'building_project_stages' => $this->stages(),
            'building_project_profile' => $this->profile,
        ], $extra);
        $data['extra_css_files'] = array_merge((array)($data['extra_css_files'] ?? []), ['dist/css/building_projects.css?v=' . filemtime(FCPATH . 'theme/dist/css/building_projects.css')]);
        bm_workspace_render($data,'building_projects/'.$view);
    }

    public function create() {
        $this->permission_check('service_jobs_add');
        $this->load->model('building_workspace_model','workspace');
        $choices=$this->workspace->choices();
        $choices['leads']=$this->permissions('leads_view') ? $this->db->select('id,name')->where('store_id',get_current_store_id())->order_by('id','desc')->limit(150)->get('db_leads')->result() : [];
        $this->render('create',array_merge($choices,['page_title'=>'New project','request_key'=>bin2hex(random_bytes(20))]));
    }

    public function save() {
        $this->permission_check('service_jobs_add');
        if($this->input->method()!=='post'){show_404();return;}
        $input=$this->input->post(null,true);
        try {
            foreach(['title','description','customer_id','site_id','assigned_user_id','lead_id','request_key'] as $field) if(isset($input[$field])&&!is_scalar($input[$field]))throw new InvalidArgumentException('Invalid project field.');
            if(trim((string)($input['title'] ?? ''))==='')throw new InvalidArgumentException('Enter a project title.');
            if(strlen((string)($input['description'] ?? ''))>8000)throw new InvalidArgumentException('Project description is too long.');
            if(!empty($input['lead_id'])&&!$this->permissions('leads_view'))$this->permission_check('leads_view');
            $id=$this->project_model->create($input);
            $this->session->set_flashdata('success','Project created.');
            redirect('building_projects/'.$id.($this->input->get('screen')==='mobile'?'?screen=mobile':''));
        }catch(InvalidArgumentException $e){
            $this->load->model('building_workspace_model','workspace');
            $this->output->set_status_header(422);
            $choices=$this->workspace->choices();
            $choices['leads']=$this->permissions('leads_view') ? $this->db->select('id,name')->where('store_id',get_current_store_id())->order_by('id','desc')->limit(150)->get('db_leads')->result() : [];
            $this->render('create',array_merge($choices,['page_title'=>'New project','form_values'=>$input,'form_error'=>$e->getMessage(),'request_key'=>bin2hex(random_bytes(20))]));
        }catch(Throwable $e){log_message('error','Building project create: '.$e->getMessage());show_error('Could not create the project.',500);}
    }

    public function index() {
        if (!$this->db->table_exists('db_service_job_context') || !$this->db->table_exists('db_service_jobs')) {
            $this->render('index', ['building_projects_ready' => false, 'building_projects' => [], 'building_project_counts' => []]);
            return;
        }

        $projects = $this->project_model->listing();
        $labels = $this->stages();
        $counts = array_fill_keys(array_keys($labels), 0);
        foreach ($projects as $project) {
            $stage = (string)($project->stage ?? 'enquiry');
            if (!isset($counts[$stage])) $counts[$stage] = 0;
            $counts[$stage]++;
        }
        $this->render('index', [
            'building_projects_ready' => true,
            'building_projects' => $projects,
            'building_project_counts' => $counts,
        ]);
    }

    public function show($id = 0) {
        if (!$this->db->table_exists('db_service_job_context') || !$this->db->table_exists('db_service_jobs')) {
            show_404();
            return;
        }
        $project = $this->project_model->get((int)$id);
        if (!$project) {
            show_404();
            return;
        }
        $events = $this->db->table_exists('db_service_job_events') ? $this->project_model->history((int)$id) : [];
        $tasks = $this->project_model->workbench_ready() ? $this->project_model->tasks((int)$id) : [];
        $surveys=$this->db->table_exists('db_service_project_records') ? $this->db->where('store_id',get_current_store_id())->where('job_id',(int)$id)->where('record_type','site_survey')->order_by('id','desc')->get('db_service_project_records')->result() : [];
        $materials=$this->project_model->materials((int)$id);
        $procurements=$this->project_model->procurements((int)$id);
        $materialChoices=$this->project_model->material_choices((int)$id);
        $deliveries=$this->project_model->deliveries((int)$id);
        $deliveryChoices=$this->project_model->delivery_choices((int)$id);
        $installationSteps=$this->project_model->installation_steps((int)$id);
        $handover=$this->project_model->handover((int)$id);
        $financials=$this->project_model->financials((int)$id);
        $milestones=$this->project_model->milestones((int)$id);
        $assets=$this->project_model->assets((int)$id);
        $maintenancePlans=$this->project_model->maintenance_plans((int)$id);
        $warrantyClaims=$this->project_model->warranty_claims((int)$id);
        $this->render('show', [
            'page_title' => 'Project · '.((string)($project->title ?: $project->job_code ?: '#'.$project->job_id)),
            'building_project' => $project,
            'building_project_website_source' => $project->lead_id && $this->permissions('leads_view') && $this->permissions('online_store_view') ? $this->db->where('store_id',get_current_store_id())->where('id',$project->lead_id)->where('source','storefront')->count_all_results('db_leads')>0 : false,
            'building_project_events' => $events,
            'building_project_tasks' => $tasks,
            'building_project_surveys' => $surveys,
            'building_project_materials' => $materials,
            'building_project_procurements' => $procurements,
            'building_project_material_choices' => $materialChoices,
            'building_project_deliveries' => $deliveries,
            'building_project_delivery_choices' => $deliveryChoices,
            'building_project_installation_steps' => $installationSteps,
            'building_project_handover' => $handover,
            'building_project_financials' => $financials,
            'building_project_milestones' => $milestones,
            'building_project_assets' => $assets,
            'building_project_maintenance' => $maintenancePlans,
            'building_project_warranty_claims' => $warrantyClaims,
        ]);
    }

    private function project_redirect($id) {
        redirect('building_projects/'.(int)$id.($this->input->get('screen')==='mobile'?'?screen=mobile':''));
    }

    public function save_material($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->add_material((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Material requirement added.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project material: '.$e->getMessage());show_error('Could not save the material requirement.',500);}
    }

    public function reserve_material($id=0,$materialId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->reserve_material((int)$id,(int)$materialId,$this->input->post('quantity',true));$this->session->set_flashdata('success','Material reserved against available stock.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project reservation: '.$e->getMessage());show_error('Could not reserve the material.',500);}
    }

    public function create_procurement($id=0,$materialId=0) {
        $this->permission_check('purchase_add');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->create_procurement((int)$id,(int)$materialId,$this->input->post(null,true));$this->session->set_flashdata('success','Purchase order created.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project procurement: '.$e->getMessage());show_error('Could not create the purchase order.',500);}
    }

    public function receive_procurement($id=0,$procurementId=0) {
        $this->permission_check('purchase_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->receive_procurement((int)$id,(int)$procurementId,$this->input->post('quantity',true));$this->session->set_flashdata('success','Goods receipt recorded and stock reconciled.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project receipt: '.$e->getMessage());show_error('Could not record the receipt.',500);}
    }

    public function create_delivery($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->create_delivery((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Delivery trip planned.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project delivery: '.$e->getMessage());show_error('Could not create the delivery trip.',500);}
    }

    public function update_delivery($id=0,$deliveryId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->update_delivery((int)$id,(int)$deliveryId,$this->input->post(null,true));$this->session->set_flashdata('success','Delivery record updated.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project delivery update: '.$e->getMessage());show_error('Could not update the delivery record.',500);}
    }

    public function update_installation_step($id=0,$stepId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->update_installation_step((int)$id,(int)$stepId,$this->input->post(null,true));$this->session->set_flashdata('success','Installation checklist updated.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project installation: '.$e->getMessage());show_error('Could not update the installation checklist.',500);}
    }

    public function sign_handover($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->sign_handover((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Handover certificate signed.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project handover: '.$e->getMessage());show_error('Could not sign the handover certificate.',500);}
    }

    public function create_milestones($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->create_milestone_plan((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Billing milestone plan created.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project milestones: '.$e->getMessage());show_error('Could not create the billing milestone plan.',500);}
    }

    public function link_milestone_invoice($id=0,$milestoneId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->link_milestone_invoice((int)$id,(int)$milestoneId,(int)$this->input->post('sales_id',true));$this->session->set_flashdata('success','Milestone linked to the shared invoice.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project milestone invoice: '.$e->getMessage());show_error('Could not link the milestone invoice.',500);}
    }

    public function register_asset($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->register_asset((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Installed asset registered with warranty.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project asset: '.$e->getMessage());show_error('Could not register the installed asset.',500);}
    }

    public function schedule_maintenance($id=0,$assetId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->schedule_maintenance((int)$id,(int)$assetId,$this->input->post(null,true));$this->session->set_flashdata('success','Maintenance scheduled.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project maintenance: '.$e->getMessage());show_error('Could not schedule maintenance.',500);}
    }

    public function create_warranty_claim($id=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->create_warranty_claim((int)$id,$this->input->post(null,true));$this->session->set_flashdata('success','Warranty claim opened.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project warranty claim: '.$e->getMessage());show_error('Could not open the warranty claim.',500);}
    }

    public function resolve_warranty_claim($id=0,$claimId=0) {
        $this->permission_check('service_jobs_edit');
        if($this->input->method()!=='post'){show_404();return;}
        try {$this->project_model->resolve_warranty_claim((int)$id,(int)$claimId,$this->input->post(null,true));$this->session->set_flashdata('success','Warranty claim updated.');$this->project_redirect($id);}
        catch(InvalidArgumentException $e){$this->session->set_flashdata('error',$e->getMessage());$this->project_redirect($id);}
        catch(Throwable $e){log_message('error','Building project warranty claim update: '.$e->getMessage());show_error('Could not update the warranty claim.',500);}
    }
}
