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
		// Preserve which store this session belonged to so the logged-out
		// page shows that store's own branding and copy - a clinic must
		// never land on retail marketing.
		$logout_store = (int) $this->session->userdata('store_id');
		// CENTRAL IS EXEMPT. Central is not a store, so carrying ?store=N sent
		// the vendor to a CLIENT store's login page (e.g. ?store=2) with that
		// customer's branding. Central must always return to its own login.
		$is_central = function_exists('mp_is_central') && mp_is_central();
		//CLEAR ALL SESSION FROM VIRTUAL VARIABLES
		$this->session->sess_destroy();
		//LOGOUT - return to the logged-out page for this store.
		//
		// The store id travels in a cookie, not the URL. It used to be
		// /login?store=2, which put an internal id in the address bar for no
		// reason anyone could see. Same fact, quieter place. The cookie is
		// non-secret (a store id is not a credential) and Read is scoped to
		// the login page, so it cannot leak anywhere else.
		if ($logout_store && !$is_central) {
			$this->input->set_cookie([
				'name'   => 'mp_login_store',
				'value'  => (string) $logout_store,
				'expire' => 3600,
				'path'   => '/',
			]);
		}
		redirect(base_url('login'));
	}
}
