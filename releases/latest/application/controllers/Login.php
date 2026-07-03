<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->php_verification();
		$this->load_info();
		is_sql_full_group_by_enabled();
	}

	public function php_verification(){
		$phpversion = phpversion();
		if(version_compare($phpversion, '7.4', '<')){
			 echo 'Application requires PHP Version 7.4 or higher, Your server loaded with PHP Version '.$phpversion;exit;
		}
	}

	public function langauge($id){
		$this->load->model('language_model');
        $this->language_model->set($id);
        redirect($_SERVER['HTTP_REFERER']);
	}

	public function index()
	{	
		//Verify PHP version
		if(version_compare(phpversion(), '7.4', '<')){
			$heading = "Invalid Server Configuration!";
			$message = "Application requires PHP Version <b>7.4 or higher</b>, Your server loaded with PHP Version <b>".phpversion()."</b>";
			$message .= "<br><a href=".base_url().">Refresh</a>";

			show_error($message, null, $heading);
			exit;
		}
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;

		// Check if subscription is expired or suspended before showing login
		if($this->db->table_exists('db_subscription_license')){
			$this->load->model('subscription_license_model','lic');
			$sub = $this->lic->get_status(1); // check default store
			if($sub['status'] === 'SUSPENDED' || $sub['status'] === 'EXPIRED'){
				$lic_rec = $this->lic->get_by_store(1);
				$suspended_data = [
					'sub_status' => $sub['status'],
					'suspension_reason' => $lic_rec->suspension_reason ?? null,
					'whatsapp_number' => $lic_rec->whatsapp_number ?? '',
					'store_name' => $data['store_name'] ?? null,
				];
				$this->load->view('subscription_suspended', $suspended_data);
				return;
			}
		}

		$this->load->view('login',$data);
	}
	public function verify()
	{
		//print_r($_POST);exit;
		$this->form_validation->set_rules('email','Email/Username','required');
		$this->form_validation->set_rules('pass','Password','required');
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Please Enter Email & password!');
			redirect('login');
		}
		else{

			$email=$this->input->post('email');
			$password=$this->input->post('pass');
			

			$this->load->model('login_model');//Model
			$this->login_model->verify_credentials($email,$password);
			
		}
	}
	public function forgot_password(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;
		$this->load->view('forgot-password',$data);
	}
	public function send_otp(){		
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid Email!');
			redirect(base_url().'login/forgot_password');
		}
		else{
			$email=$this->input->post('email');
			$this->load->model('login_model');//Model
			$response = $this->login_model->verify_email_send_otp($email);

			if($response==true){//Model->Method
				redirect(base_url().'login/otp');
			}
			else{
				redirect(base_url().'login/forgot_password');
			}			
		}
	}
	public function otp(){
		if($this->session->userdata('logged_in')==1){ redirect(base_url().'dashboard','refresh');	}
		$data = $this->data;
		$this->load->view('otp',$data);
	}
	public function verify_otp(){
		$this->form_validation->set_rules('otp', 'OTP', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
		
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Invalid OTP!');
			redirect(base_url().'login/otp');
		}
		else{
			$otp=$this->input->post('otp');
			$email=$this->input->post('email');
			
			if($this->session->userdata('email')==$email && $this->session->userdata('otp')==$otp){
				$data=$this->data;
				$data['email']=$email;
				$data['otp']=$otp;
				
				$this->load->view("change-login-password",$data);
			}
			else{
				$this->session->set_flashdata('failed', 'Invalid OTP!!');
				redirect(base_url().'login/otp');
			}			
		}
	}
	public function change_password(){

		$this->form_validation->set_rules('otp', 'OTP', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('cpassword', 'Confirm Password', 'required');
		
		//print_r($_POST);exit;
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('failed', 'Please Enter Correct Passwords!');
			redirect(base_url().'login/verify_otp');
		}
		else{
			$otp=$this->input->post('otp');
			$email=$this->input->post('email');
			$password=$this->input->post('password');
			$cpassword=$this->input->post('cpassword');
			$this->load->model('login_model');//Model
			if($password==$cpassword && $this->session->userdata('email')==$email && $this->session->userdata('otp')==$otp){
				if($this->login_model->change_password($password,$email)){//Model->Method
					$data = $this->data;
					$array_items = array('email','otp');
					$this->session->unset_userdata($array_items);
					$this->session->set_flashdata('success', 'Password Changed Successfully!');
					redirect(base_url().'login','refresh');
				}
				else{
					$data=$this->data;
    				$data['email']=$email;
    				$data['otp']=$otp;
    				$this->session->set_flashdata('failed', 'Password Mismatched!');
    				$this->load->view("change-login-password",$data);
				}			
			}
			else{
			        $data=$this->data;
    				$data['email']=$email;
    				$data['otp']=$otp;
    				$this->session->set_flashdata('failed', 'Password Mismatched!');
    				$this->load->view("change-login-password",$data);
			}
		}

	}
	

}
