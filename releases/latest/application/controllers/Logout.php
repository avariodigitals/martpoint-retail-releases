<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_info();
	}
	public function index()
	{
		$userId = $this->session->userdata('inv_userid');
		// Check if user needs to clock out first
		if($userId){
			$this->load->model('attendance_model');
			if($this->attendance_model->needsClockOut($userId)){
				$this->session->set_flashdata('warning', 'Please clock out before logging out.');
				redirect(base_url('dashboard'));
			}
		}

		$this->session->userdata('language');

		$cookie= array(
           'name'   => 'language',
           'value'  => $this->session->userdata('language'),
           'expire' => '3600',
       	);
        $this->input->set_cookie($cookie);


		$data = $this->data;
		//DELETE THE EXPIRED SESSION FROM SESSION, WHICH SAVED
		$this->db->where("timestamp<=",time()-config_item('sess_expiration'))->delete(config_item('sess_save_path'));
		//CLEAR ALL SESSION FROM VIRTUAL VARIABLES
		$this->session->sess_destroy();
		//LOGOUT
		redirect(base_url('login'));
	}
}
