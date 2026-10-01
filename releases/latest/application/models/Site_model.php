<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Site_model extends CI_Model {
    public function get_details(){
		$data=$this->data;

		//Validate This suppliers already exist or not
		$query=$this->db->query("select * from db_sitesettings order by id asc limit 1");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			/* QUERY 1*/
			$query=$query->row();
			$data['q_id']=$query->id;
            $data['site_name']=$query->site_name;
            $data['logo']=$query->logo;
            $data['sales_target']=$query->sales_target ?? 0;
            // MartPoint Assist AI settings (columns added by 4.0.9.35 migration)
            $data['assist_ai_enabled']  = isset($query->assist_ai_enabled) ? (int)$query->assist_ai_enabled : 0;
            $data['assist_ai_provider'] = $query->assist_ai_provider ?? 'groq';
            $data['assist_ai_endpoint'] = $query->assist_ai_endpoint ?? '';
            $data['assist_ai_model']    = $query->assist_ai_model ?? '';
            $data['assist_ai_key_set']  = !empty($query->assist_ai_key);
            // Service status / incident banner (columns added by 4.0.9.59 migration)
            $data['incident'] = function_exists('mp_get_incident') ? mp_get_incident() : null;
			return $data;
		}
	}
	public function update_site(){
		$site_name = $this->input->post('site_name', TRUE);
		$change_return = $this->input->post('change_return', TRUE);
		$round_off = $this->input->post('round_off', TRUE);
		$q_id = $this->input->post('q_id', TRUE);

		$sales_target = (float)$this->input->post('sales_target', TRUE);
		//echo "<pre>";print_r($this->security->xss_clean(html_escape(array_merge($this->data,$_POST))));exit();
				
		
		$logo='';
		if(!empty($_FILES['logo']['name'])){
			$site_upload_path = FCPATH . 'uploads/site/';
			if(!is_dir($site_upload_path)){
				@mkdir($site_upload_path, 0775, true);
			}
			$config['upload_path']          = $site_upload_path;
	        $config['allowed_types']        = 'gif|jpg|png|webp';
	        $config['max_size']             = 500;
	        $config['max_width']            = 500;
	        $config['max_height']           = 500;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('logo'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {
	        	   $logo_name=$this->upload->data('file_name');
	        		$logo=" ,logo='/uploads/site/$logo_name' ";
	        }
		}
        
		$change_return = (isset($change_return)) ? 1 : 0;
		$round_off = (isset($round_off)) ? 1 : 0;
        $info = array('site_name' => $site_name);
        $info['sales_target'] = $sales_target;
        if(!empty($logo_name)){
            $info['logo'] = '/uploads/site/'.$logo_name;
        }
        // MartPoint Assist AI settings (columns added by 4.0.9.35 migration)
        if($this->db->field_exists('assist_ai_enabled', 'db_sitesettings')){
            $info['assist_ai_enabled']  = $this->input->post('assist_ai_enabled', TRUE) ? 1 : 0;
            $info['assist_ai_provider'] = trim((string)$this->input->post('assist_ai_provider', TRUE)) ?: 'groq';
            $info['assist_ai_endpoint'] = trim((string)$this->input->post('assist_ai_endpoint', TRUE));
            $info['assist_ai_model']    = trim((string)$this->input->post('assist_ai_model', TRUE));
            // API key: only overwritten when a new one is typed (field stays blank in the form)
            $ai_key = trim((string)$this->input->post('assist_ai_key', TRUE));
            if($ai_key !== ''){
                $info['assist_ai_key'] = $ai_key;
            }
        }
        // Service status / incident banner (columns added by 4.0.9.59 migration).
        // Central-only: on client installs the banner is feed-driven and local
        // POSTs must not be able to write incident state.
        if(function_exists('mp_is_central') && mp_is_central()
            && $this->db->field_exists('incident_active', 'db_sitesettings') && function_exists('mp_set_incident')){
            $prev = mp_get_incident();
            mp_set_incident([
                'active'   => $this->input->post('incident_active', TRUE) ? 1 : 0,
                'severity' => (string)$this->input->post('incident_severity', TRUE),
                'message'  => (string)$this->input->post('incident_message', TRUE),
                'url'      => (string)$this->input->post('incident_url', TRUE),
                // Keep the original start time across edits while still active.
                'started_at' => !empty($prev['active']) ? $prev['started_at'] : null,
            ], 'local');
        }
        $query1 = $this->db->where('id', $q_id)->update('db_sitesettings', $info);
      
		if ($query1){
		    return "success";
		}
		else{
		    return "failed";
		}
	}
}

/* End of file Site_model.php */
