<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Recurrence creates shared service jobs, never invoices or payment entries. */
class Service_schedule_model extends CI_Model {
    public function listing() {
        return $this->db->select('s.*,c.customer_name')->from('db_service_schedules s')
            ->join('db_customers c','c.id=s.customer_id AND c.store_id=s.store_id')
            ->where('s.store_id',get_current_store_id())->order_by('s.id','desc')->limit(250)->get()->result();
    }

    public function create(array $input) {
        $storeId=(int)get_current_store_id();
        $title=trim((string)($input['title'] ?? ''));
        $start=(string)($input['start_date'] ?? '');$end=(string)($input['end_date'] ?? '');
        $frequency=(string)($input['frequency'] ?? '');$days=(int)($input['custom_days'] ?? 0);
        $key=(string)($input['request_key'] ?? '');
        if (!$title || strlen($title)>200 || !preg_match('/^[a-zA-Z0-9_-]{16,80}$/D',$key)) throw new InvalidArgumentException('Enter a title and valid request reference.');
        mp_garden_occurrence($start,$frequency,0,$days);
        if ($end) { $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$end); if (!$parsed || $parsed->format('Y-m-d')!==$end || $end<$start) throw new InvalidArgumentException('End date must be on or after the start date.'); }
        $data=['store_id'=>$storeId,'customer_id'=>(int)($input['customer_id'] ?? 0),'site_id'=>(int)($input['site_id'] ?? 0) ?: null,
            'assigned_user_id'=>(int)($input['assigned_user_id'] ?? 0) ?: null,'title'=>$title,'start_date'=>$start,'end_date'=>$end ?: null,
            'frequency'=>$frequency,'custom_days'=>$frequency==='custom' ? $days : null];
        if (!$this->db->where('id',$data['customer_id'])->where('store_id',$storeId)->where('status',1)->get('db_customers')->row()) throw new InvalidArgumentException('Customer not found.');
        if ($data['site_id'] && !$this->db->where('id',$data['site_id'])->where('store_id',$storeId)->where('customer_id',$data['customer_id'])->get('db_shippingaddress')->row()) throw new InvalidArgumentException('Site not found for this customer.');
        if ($data['assigned_user_id'] && !$this->db->where('id',$data['assigned_user_id'])->where('store_id',$storeId)->where('status',1)->get('db_users')->row()) throw new InvalidArgumentException('Team member not found.');
        $hash=hash('sha256',json_encode($data));
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$storeId]);
            $old=$this->db->where('store_id',$storeId)->where('request_key',$key)->get('db_service_schedules')->row();
            if ($old) { if (!hash_equals($old->payload_hash,$hash)) throw new InvalidArgumentException('Request reference already used.'); $this->db->trans_commit(); return (int)$old->id; }
            $data+=['request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')];
            if (!$this->db->insert('db_service_schedules',$data)) throw new RuntimeException('Could not create schedule.');
            $id=(int)$this->db->insert_id();
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not create schedule.');
            $this->db->trans_commit();return $id;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function generate($scheduleId,$through) {
        $end=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$through);
        if (!$end || $end->format('Y-m-d')!==$through || $through>date('Y-m-d',strtotime('+1 year'))) throw new InvalidArgumentException('Generate up to one year ahead.');
        $storeId=(int)get_current_store_id();
        $this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$storeId]);
            $schedule=$this->db->query('SELECT * FROM db_service_schedules WHERE id=? AND store_id=? FOR UPDATE',[(int)$scheduleId,$storeId])->row();
            if (!$schedule) throw new InvalidArgumentException('Schedule not found.');
            if ($schedule->status!=='active') throw new InvalidArgumentException('Schedule is not active.');
            $this->load->model('equipment_model');
            $created=[];
            for ($i=(int)$schedule->last_generated_index+1;count($created)<100;$i++) {
                if ($schedule->frequency==='once' && $i>0) break;
                $date=mp_garden_occurrence($schedule->start_date,$schedule->frequency,$i,(int)$schedule->custom_days);
                if ($date>$through || ($schedule->end_date && $date>$schedule->end_date)) break;
                $jobId=$this->equipment_model->save_job_data([
                    'customer_id'=>$schedule->customer_id,'site_id'=>$schedule->site_id,'assigned_user_id'=>$schedule->assigned_user_id,
                    'title'=>$schedule->title,'description'=>'Recurring visit: schedule #'.$schedule->id.', occurrence '.$date,
                    'job_type'=>'maintenance','status'=>'open','scheduled_date'=>$date,
                ]);
                // Open means pending; generating a date never asserts resource availability.
                if (!$this->db->insert('db_service_schedule_occurrences',['store_id'=>$storeId,'schedule_id'=>(int)$scheduleId,'occurrence_date'=>$date,'occurrence_index'=>$i,'job_id'=>$jobId])) throw new RuntimeException('Could not record occurrence.');
                $this->db->where('id',$schedule->id)->where('store_id',$storeId)->update('db_service_schedules',['last_generated_index'=>$i]);
                $created[]=$jobId;
            }
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not generate visits.');
            $this->db->trans_commit();return $created;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }

    public function cancel($id) {
        $row=$this->db->where('id',(int)$id)->where('store_id',get_current_store_id())->get('db_service_schedules')->row();
        if (!$row) throw new InvalidArgumentException('Schedule not found.');
        if ($row->status==='cancelled') return true;
        $this->db->where('id',(int)$id)->where('store_id',get_current_store_id())->update('db_service_schedules',['status'=>'cancelled']);
        // Existing visits retain their history and must be cancelled explicitly.
        return $this->db->affected_rows()===1;
    }
}
