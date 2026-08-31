<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tills extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('tills_model','tills');
	}

	public function index(){
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		$this->permission_check('tills_view');
		$data = $this->data;
		$data['page_title'] = 'Tills / Cash-in-Hand';
		$data['tills'] = $this->tills->get_all();
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		// DEBUG: log to verify this method runs
		log_message('debug', '[Tills DEBUG] index() called, tills count='.count($data['tills']).' store_id='.get_current_store_id().' user_id='.$this->session->userdata('inv_userid'));
		$this->load->view('tills/list', $data);
	}

	public function new_form(){
		$this->permission_check('tills_add');
		$data = $this->data;
		$data['page_title'] = 'Add Till';
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		$this->load->view('tills/form', $data);
	}

	public function edit_form($id){
		$this->permission_check('tills_edit');
		$data = $this->data;
		$data['page_title'] = 'Edit Till';
		$data['till'] = $this->tills->get($id);
		if(empty($data['till'])){ show_404(); }
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		$this->load->view('tills/form', $data);
	}

	public function save(){
		$this->permission_check('tills_add');
		$id = $this->input->post('id', TRUE);
		$data = array(
			'till_name'       => $this->input->post('till_name', TRUE),
			'cashier_user_id' => $this->input->post('cashier_user_id', TRUE),
			'account_id'      => $this->input->post('account_id', TRUE),
			'is_default'      => $this->input->post('is_default', TRUE),
		);
		$save_id = $this->tills->save($data, $id);
		$this->session->set_flashdata('success', 'Till saved successfully.');
		redirect(base_url('tills'));
	}

	public function delete($id){
		$this->permission_check('tills_delete');
		$this->tills->delete($id);
		$this->session->set_flashdata('success', 'Till deactivated.');
		redirect(base_url('tills'));
	}
}
