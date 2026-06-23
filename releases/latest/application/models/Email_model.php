<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_model extends CI_Model {
	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}
	//UPDATE SMS API
	public function api_update(){
		$hidden_rowcount = $this->input->post('hidden_rowcount', TRUE);
		//print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		$store_id = get_current_store_id();
		$this->db->trans_begin();
		if($hidden_rowcount>0){
		$this->db->query("delete from db_emailapi where store_id=".$store_id);
		$this->db->query("ALTER TABLE db_emailapi AUTO_INCREMENT = 1");
			for($i=1; $i<=$hidden_rowcount; $i++){
				if(isset($_POST['info_'.$i])){
					$info 	 	= $_POST['info_'.$i];
					$key 	 	= $_POST['key_'.$i];
					$key_value 	= $_POST['key_val_'.$i];
					
					$q1=$this->db->query("insert into db_emailapi(
								info,`key`,key_value,store_id)
								values(
								'$info',
								'$key',
								'$key_value',
								$store_id
							)");
					if(!$q1){
						return "failed";
					}

				}//if end()
			}//for end()	
		}

		$q2=$this->db->query("update db_store set email_status=$email_status where id=".$store_id);
		if(!$q2){
			return "failed";
		}

		//save Twilio SMS API
		$twilio = array('account_sid' => $account_sid,'auth_token'=>$auth_token,'twilio_phone'=>$twilio_phone );
		$q1=$this->db->select("*")->where("store_id",$store_id)->get("db_twilio");
        if($q1->num_rows()>0){
          $q2 = $this->db->where("store_id",$store_id)->update("db_twilio",$twilio);
        }
        else{
        	$twilio = array_merge($twilio,array('store_id' => $store_id));
        	$q2=$this->db->insert("db_twilio",$twilio);
        }

        if(!$q2){
        	return "failed";
        }

			$this->session->set_flashdata('success', 'Record Successfully Saved!!');
			$this->db->trans_commit();
		    return "success";
	}

	//Send email — delegates to new EmailService for all modern features
	public function send_email(array $content){
		$to = isset($content['to']) ? $content['to'] : '';
		$subject = isset($content['subject']) ? $content['subject'] : '';
		$message = isset($content['message']) ? $content['message'] : '';
		$from = isset($content['from']) ? $content['from'] : '';

		if(empty($to)){
			return "You forgot to add Receipient Email Address";
		}
		if(empty($subject)){
			return "Email Subject is required";
		}

		// Delegate to the new EmailService
		$this->load->model('email_service');

		$result = $this->email_service->sendRaw(
			$to,
			$subject,
			$message,
			strip_tags($message),
			['template_key' => 'legacy_email']
		);

		return $result['success'] ? TRUE : $result['message'];
	}

}