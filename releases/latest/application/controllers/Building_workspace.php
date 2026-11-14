<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Building_workspace extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load_global();
        if ((mp_get_store_profile()['industry_type'] ?? '')!=='building_materials') {
            $this->show_access_denied_page('This workspace is for Building Materials businesses.');exit;
        }
        $this->load->helper('building_workspace');
    }

    private function render($title,$view,array $extra=[]) {
        bm_workspace_render(array_merge($this->data,['page_title'=>$title],$extra),'building_materials/'.$view);
    }

    private function field_guard($permission) {
        $this->permission_check($permission);
        if (!mp_feature_enabled('service_jobs')) {$this->show_access_denied_page('Enable field service operations in Industry Settings.');exit;}
        $this->load->model('building_workspace_model','workspace');
    }

    public function actions() {
        $this->permission_check('dashboard_view');
        $this->load->model('intelligence_model','intelligence');
        $allowed=[];
        foreach(['service_jobs_view','equipment_view','items_view','sales_view','quotation_view','purchase_view','store_view','receivables_aging_report','profit_report','customers_view'] as $p) $allowed[$p]=$this->permissions($p);
        $this->render('Action Centre','actions',['action_groups'=>$this->intelligence->for_building(get_current_store_id(),$allowed)]);
    }

    public function more() {
        $this->render('More tools','more',['tool_groups'=>bm_workspace_tools()]);
    }

    public function surveys() {
        $this->field_guard('service_jobs_view');
        $this->render('Site surveys','surveys',['surveys'=>$this->workspace->surveys()]);
    }

    public function survey($id=0) {
        $this->field_guard('service_jobs_view');
        $survey=$this->workspace->survey((int)$id);
        if (!$survey) {show_404();return;}
        $this->render('Site survey','survey',['survey'=>$survey]);
    }

    public function survey_form() {
        $this->field_guard('service_jobs_add');
        $this->render('New site survey','survey_form',array_merge($this->workspace->choices(),[
            'request_key'=>bin2hex(random_bytes(20)),
            'selected_project'=>(int)$this->input->get('project_id'),
        ]));
    }

    public function save_survey() {
        $this->field_guard('service_jobs_add');
        if ($this->input->method()!=='post') {show_404();return;}
        $input=$this->input->post(null,true);
        try {
            $id=$this->workspace->save_survey(is_array($input)?$input:[]);
            $this->session->set_flashdata('success','Site survey saved.');
            redirect('building_workspace/survey/'.$id.($this->input->get('screen')==='mobile'?'?screen=mobile':''));
        } catch (InvalidArgumentException $e) {
            $this->output->set_status_header(422);
            $this->render('New site survey','survey_form',array_merge($this->workspace->choices(),['request_key'=>bin2hex(random_bytes(20)),'form_error'=>$e->getMessage(),'form_values'=>$input]));
        } catch (Throwable $e) {
            log_message('error','Building survey save: '.$e->getMessage());
            show_error('Could not save this survey. Your existing records have not been changed.',500);
        }
    }

    public function field() {$this->jobs(false);}
    public function services() {$this->jobs(true);}

    private function jobs($services) {
        $this->field_guard('service_jobs_view');
        $this->load->model('equipment_model','equipment');
        $jobs=$this->workspace->field_jobs($services);
        $type=$this->input->get('type');
        if (is_string($type) && in_array($type,$this->equipment->job_types(),true)) $jobs=array_values(array_filter($jobs,function($j)use($type){return $j->job_type===$type;}));
        $this->render($services?'Service requests':'Field teams & installation','field',['jobs'=>$jobs,'services'=>$services,'selected_type'=>is_string($type)?$type:'']);
    }
}
