<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/**
	 * Administration desktop landing page.
	 * Provides an overview of the admin area and quick links to every
	 * administration menu item in the new mp_layout shell.
	 */
	public function index()
	{
		$admin_perms = ['store_edit','store_view','users_view','roles_view','warehouse_view','tax_view','units_view','payment_types_view','payment_modes_view','send_sms','sms_template_view','sms_api_view','smtp_settings','gateway_view','package_view','subscription','paystack_settings','expiry_settings','debt_reminder_view','approval_settings_edit','nin_usage','nin_logs'];
		$can_access = special_access() || is_store_admin() || $this->session->userdata('role_id') == 1;
		if (!$can_access) {
			foreach ($admin_perms as $p) {
				if ($this->permissions($p)) { $can_access = true; break; }
			}
		}
		if (!$can_access) {
			$this->show_access_denied_page();
		}

		$data = $this->data;
		$store_id = get_current_store_id();
		$data['store_id'] = $store_id;

		/* Store context counts */
		if(!is_admin()){
			$this->db->where('store_id', $store_id);
		}
		$data['total_users'] = $this->db->count_all_results('db_users');

		if(!is_admin()){
			$this->db->where('store_id', $store_id);
		}
		$data['total_roles'] = $this->db->count_all_results('db_roles');

		$data['total_branches'] = $this->db->where('store_id', $store_id)->where('status', 1)->count_all_results('db_warehouse');

		if ($this->permissions('store_view') && is_admin()) {
			$data['total_stores'] = $this->db->where('status', 1)->count_all_results('db_store');
		} else {
			$data['total_stores'] = 1;
		}

		/* Configuration counts */
		$data['total_taxes'] = $this->db->where('store_id', $store_id)->count_all_results('db_tax');
		$data['total_units'] = $this->db->where('store_id', $store_id)->count_all_results('db_units');
		$data['total_payment_types'] = $this->db->where('store_id', $store_id)->count_all_results('db_paymenttypes');
		$data['total_payment_modes'] = $this->db->where('store_id', $store_id)->count_all_results('db_payment_modes');
		$data['total_currencies'] = $this->db->count_all_results('db_currency');
		$data['total_countries'] = $this->db->count_all_results('db_country');
		$data['total_states'] = $this->db->count_all_results('db_states');
		$data['total_cities'] = $this->db->count_all_results('db_cities');

		$data['page_title'] = 'Administration';
		$data['content'] = $this->load->view('admin/desktop/dashboard', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
}
