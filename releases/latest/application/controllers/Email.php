<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	
	//Open Email Form
	public function index(){
		$this->permission_check('send_email');
		$data=$this->data;
		$data['page_title']=$this->lang->line('send_email');
		$this->load->model('email_template_model');
		$data['email_templates'] = $this->email_template_model->getAll();
		$this->load->view('email', $data);
	}


	//Create Message
	public function send_message(){
		$this->permission_check('send_email');
		$data=$this->data;
		$this->load->model('email_model');
		//echo $this->input->post('email_content');exit;
		$email_info = array(
							'to' 				=> $this->input->post('email_to'), 
							'subject' 			=> $this->input->post('email_subject'), 
							'message' 			=> $this->input->post('email_content'), 
						);
		$response = $this->email_model->send_email($email_info);
		if($response){
			$this->session->set_flashdata('success', 'Success!! Email Sent Successfully! ');
		}
		else{
			$this->session->set_flashdata('error', $response);
		}
		redirect('email/index','refresh');
	}
}

