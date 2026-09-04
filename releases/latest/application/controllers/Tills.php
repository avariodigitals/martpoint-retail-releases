<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tills extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('tills_model','tills');
	}

	public function index(){
		if(is_mobile()){ redirect('mobile/finance/tills'); }
		$this->permission_check('tills_view');
		$data = $this->data;
		$data['page_title'] = 'Tills / Cash-in-Hand';
		$data['tills'] = $this->tills->get_all();
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		$data['content'] = $this->load->view('finance/desktop/tills/list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function new_form(){
		if(is_mobile()){ redirect('mobile/finance/tills/form'); }
		$this->permission_check('tills_add');
		$data = $this->data;
		$data['page_title'] = 'Add Till';
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		$data['content'] = $this->load->view('finance/desktop/tills/form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function edit_form($id){
		if(is_mobile()){ redirect('mobile/finance/tills/form/'.$id); }
		$this->permission_check('tills_edit');
		$data = $this->data;
		$data['page_title'] = 'Edit Till';
		$data['till'] = $this->tills->get($id);
		if(empty($data['till'])){ show_404(); }
		$data['users'] = $this->db->where('status', 1)->where('store_id', get_current_store_id())->get('db_users')->result();
		$data['accounts'] = get_accounts_select_list();
		$data['content'] = $this->load->view('finance/desktop/tills/form', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
