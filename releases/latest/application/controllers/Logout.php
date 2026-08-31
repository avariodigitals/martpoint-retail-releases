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
				if(is_mobile()){
					redirect(base_url('mobile/clock'));
				}
				$return_to = $this->input->server('HTTP_REFERER') ?: base_url('dashboard');
				redirect($return_to);
			}
		}

		// Check if cashier has an open shift (Z-Report) to close first
		if($userId && $this->permissions('cashier_shifts_manage') && mp_feature_enabled('cashier_shifts') && $this->db->table_exists('db_cashier_shifts')){
			$this->load->model('cashier_shifts_model');
			$open = $this->cashier_shifts_model->get_open_shift(get_current_store_id(), $userId);
			if($open){
				$this->session->set_flashdata('warning', 'You have an open cashier shift ('.htmlspecialchars($open->shift_code).'). Please close it and count cash before logging out.');
				redirect(base_url('cashier_shifts/close_form'));
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
		//DELETE THE EXPIRED SESSION FROM SESSION, WHICH SAVED (only for database driver)
		if(config_item('sess_driver') === 'database'){
			$this->db->where("timestamp<=",time()-config_item('sess_expiration'))->delete(config_item('sess_save_path'));
		}
		//CLEAR ALL SESSION FROM VIRTUAL VARIABLES
		$this->session->sess_destroy();
		//LOGOUT
		redirect(base_url('login'));
	}
}
