<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Send_email_model extends CI_Model {
	
	public function send_message($to_email='',$subject='',$message)
	{
		$this->load->model('email_service');
		$result = $this->email_service->sendRaw(
			$to_email,
			$subject,
			$message,
			strip_tags($message),
			['template_key' => 'legacy_send_email']
		);

		if($result['success']){
			echo "Email Sent Successfully!!";
		} else {
			echo "Failed to send Email!! " . $result['message'];
		}
	}

}

/* End of file Send_email_model.php */
/* Location: ./application/models/Send_email_model.php */