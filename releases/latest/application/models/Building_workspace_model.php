<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Field records extend canonical service jobs; no stock or finance writes. */
class Building_workspace_model extends CI_Model {
    public function choices() {
        $store=(int)get_current_store_id();
        $this->load->model('equipment_model');
        $this->load->model('service_project_model');
        return [
            'customers'=>$this->db->select('id,customer_name')->where('store_id',$store)->where('status',1)->order_by('customer_name')->get('db_customers')->result(),
            'sites'=>$this->db->select('id,customer_id,site_name,address')->where('store_id',$store)->order_by('id','desc')->get('db_shippingaddress')->result(),
            'staff'=>$this->equipment_model->get_engineers(),
            'projects'=>$this->service_project_model->listing(),
        ];
    }

    public function surveys() {
        if (!$this->db->table_exists('db_service_project_records')) return [];
        return $this->db->select('r.*,j.job_code,j.title,j.customer_id,c.customer_name,u.username AS engineer')
            ->from('db_service_project_records r')->join('db_service_jobs j','j.id=r.job_id AND j.store_id=r.store_id')
            ->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id','left')
            ->join('db_users u','u.id=j.assigned_user_id AND u.store_id=r.store_id','left')
            ->where('r.store_id',get_current_store_id())->where('r.record_type','site_survey')->order_by('r.id','desc')->limit(250)->get()->result();
    }

    public function survey($id) {
        return $this->db->select('r.*,j.job_code,j.customer_id,j.site_id,j.title,c.customer_name,u.username AS engineer,a.username AS author,sa.site_name,sa.address AS site_address,x.job_id AS project_id')
            ->from('db_service_project_records r')->join('db_service_jobs j','j.id=r.job_id AND j.store_id=r.store_id')
            ->join('db_customers c','c.id=j.customer_id AND c.store_id=j.store_id','left')
            ->join('db_users u','u.id=j.assigned_user_id AND u.store_id=r.store_id','left')
            ->join('db_users a','a.id=r.created_by AND a.store_id=r.store_id','left')
            ->join('db_shippingaddress sa','sa.id=j.site_id AND sa.store_id=j.store_id','left')
            ->join('db_service_job_context x','x.job_id=j.id AND x.store_id=j.store_id','left')
            ->where('r.store_id',get_current_store_id())->where('r.id',(int)$id)->where('r.record_type','site_survey')->get()->row();
    }

    public function save_survey(array $input) {
        if (!$this->db->table_exists('db_service_project_records')) throw new InvalidArgumentException('Site survey storage is not available. Contact your administrator.');
        foreach(['request_key','project_id','customer_id','site_id','assigned_user_id','title','status','width','height','quantity','unit','location','specification','risks','notes','survey_date'] as $name) {
            if (isset($input[$name]) && !is_scalar($input[$name])) throw new InvalidArgumentException('Invalid survey field.');
        }
        $key=(string)($input['request_key'] ?? '');
        if (!preg_match('/^[a-f0-9]{40}$/D',$key)) throw new InvalidArgumentException('Reload the form before saving.');
        $title=trim((string)($input['title'] ?? ''));
        if ($title==='' || strlen($title)>200) throw new InvalidArgumentException('Enter a survey title of at most 200 characters.');
        $status=(string)($input['status'] ?? 'scheduled');
        if (!in_array($status,['scheduled','completed'],true)) throw new InvalidArgumentException('Choose a valid survey status.');
        $date=(string)($input['survey_date'] ?? '');
        $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if (!$parsed || $parsed->format('Y-m-d')!==$date) throw new InvalidArgumentException('Enter a valid survey date.');
        $unit=(string)($input['unit'] ?? 'mm');
        if (!in_array($unit,['mm','cm','m'],true)) throw new InvalidArgumentException('Choose a measurement unit.');
        foreach(['width','height','quantity'] as $name) {
            $v=trim((string)($input[$name] ?? ''));
            if ($v!=='' && (!is_numeric($v) || (float)$v<=0 || (float)$v>10000000)) throw new InvalidArgumentException('Measurements and quantity must be positive numbers.');
        }
        if ($status==='completed' && trim((string)($input['specification'] ?? ''))==='' && trim((string)($input['notes'] ?? ''))==='') throw new InvalidArgumentException('Record the specification or findings before completing a survey.');
        $fields=['status'=>$status,'survey_date'=>$date,'unit'=>$unit];
        foreach(['width','height','quantity','location','specification','risks'] as $name) {$fields[$name]=trim((string)($input[$name] ?? ''));if(strlen($fields[$name])>8000)throw new InvalidArgumentException('A survey note is too long.');}
        $note=trim((string)($input['notes'] ?? ''));if(strlen($note)>8000)throw new InvalidArgumentException('Survey notes are too long.');
        $store=(int)get_current_store_id();$projectId=(int)($input['project_id'] ?? 0);
        $hash=hash('sha256',json_encode([$projectId,(int)($input['customer_id'] ?? 0),(int)($input['site_id'] ?? 0),(int)($input['assigned_user_id'] ?? 0),$title,$fields,$note]));
        $this->load->model('equipment_model');
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $prior=$this->db->where('store_id',$store)->where('request_key',$key)->get('db_service_project_records')->row();
            if ($prior) {if(!hash_equals($prior->payload_hash,$hash))throw new InvalidArgumentException('This request reference was used for different survey details.');$this->db->trans_commit();return (int)$prior->id;}
            if ($projectId) {
                $this->load->model('service_project_model');$project=$this->service_project_model->get($projectId);
                if (!$project || in_array($project->stage,['closed','cancelled'],true)) throw new InvalidArgumentException('Choose an open project from this store.');
                $jobId=$projectId;
            } else {
                $jobId=$this->equipment_model->save_job_data([
                    'customer_id'=>(int)($input['customer_id'] ?? 0),'site_id'=>(int)($input['site_id'] ?? 0) ?: null,
                    'assigned_user_id'=>(int)($input['assigned_user_id'] ?? 0) ?: null,'job_type'=>'inspection',
                    'title'=>$title,'description'=>$note,'status'=>$status,'scheduled_date'=>$date,
                ]);
            }
            $fields['title']=$title;
            $this->db->insert('db_service_project_records',['store_id'=>$store,'job_id'=>$jobId,'record_type'=>'site_survey','fields_json'=>json_encode($fields),'note'=>$note,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')]);
            $id=(int)$this->db->insert_id();
            if ($projectId) $this->db->insert('db_service_job_events',['store_id'=>$store,'job_id'=>$jobId,'from_stage'=>$project->stage,'to_stage'=>$project->stage,'note'=>'Site survey #'.$id.' '.$status.': '.$title,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')]);
            if (!$id || $this->db->trans_status()===false) throw new RuntimeException('Could not save the survey.');
            $this->db->trans_commit();return $id;
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
    }

    public function field_jobs($services=false) {
        $this->load->model('equipment_model');
        $jobs=$this->equipment_model->get_job_list(['limit'=>500]);
        $parents=$this->db->select('job_id')->where('store_id',get_current_store_id())->get('db_service_job_context')->result();
        $parentIds=array_map('intval',array_column($parents,'job_id'));
        return array_values(array_filter($jobs,function($job)use($services,$parentIds){
            $job->is_project=in_array((int)$job->id,$parentIds,true);
            return $services ? !$job->is_project && in_array($job->job_type,['maintenance','repair','calibration'],true) : in_array($job->job_type,['installation','commissioning'],true);
        }));
    }
}
