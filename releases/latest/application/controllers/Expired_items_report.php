<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expired_items_report extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('expiry_settings_model','expiry');
	}

	public function index(){
		$this->permission_check('expired_items_report');
		$data = $this->data;
		$data['page_title'] = 'Expired / Expiring Items Report';
		$data['settings'] = $this->expiry->get_settings();
		$data['expired'] = $this->expiry->get_expired_items();
		$data['expiring'] = $this->expiry->get_expiring_items();
		$this->load->view('expired_items_report', $data);
	}
}
