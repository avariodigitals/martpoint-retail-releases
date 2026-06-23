<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_modes extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('payment_modes_model','payment_modes');
	}

	public function index(){
		$this->permission_check('payment_modes_view');
		$data=$this->data;
		$data['page_title']='Payment Modes';
		$this->load->view('payment_modes_list', $data);
	}

	public function add(){
		$this->permission_check('payment_modes_add');
		$data=$this->data;
		$data['page_title']='Payment Modes';
		$this->load->view('payment_modes', $data);
	}

	public function new_payment_mode(){
		$this->permission_check('payment_modes_add');
		$this->form_validation->set_rules('code', 'Code', 'trim|required');
		$this->form_validation->set_rules('name', 'Name', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result=$this->payment_modes->verify_and_save();
			echo $result;
		} else {
			echo "Please fill all required fields.";
		}
	}

	public function update($id){
		$this->belong_to('db_payment_modes',$id);
		$this->permission_check('payment_modes_edit');
		$data=$this->data;
		$result=$this->payment_modes->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']='Payment Modes';
		$this->load->view('payment_modes', $data);
	}

	public function update_payment_mode(){
		$this->permission_check('payment_modes_edit');
		$this->form_validation->set_rules('code', 'Code', 'trim|required');
		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result=$this->payment_modes->update_payment_mode();
			echo $result;
		} else {
			echo "Please fill all required fields.";
		}
	}

	public function ajax_list()
	{
		$list = $this->payment_modes->get_datatables();

		$data = array();
		$no = $_POST['start'];
		foreach ($list as $mode) {
			$no++;
			$row = array();
			$row[] = $mode->name;
			$row[] = $mode->code;
			$row[] = ($mode->enabled==1) ? '<span class="label label-success">Enabled</span>' : '<span class="label label-danger">Disabled</span>';
			$row[] = ($mode->is_default==1) ? '<span class="label label-primary">Default</span>' : '';
			$row[] = ($mode->requires_reference==1) ? 'Yes' : 'No';
			$row[] = ($mode->requires_confirmation==1) ? 'Yes' : 'No';
			$row[] = ($mode->affects_cash_in_hand==1) ? 'Yes' : 'No';

			$str2 = '<div class="btn-group" title="View Account">
						<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
							Action <span class="caret"></span>
						</a>
						<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

			if($this->permissions('payment_modes_edit'))
			$str2.='<li>
								<a class="pointer" href="'.base_url().'payment_modes/update/'.$mode->id.'" title="Edit Record" >
									<i class="fa fa-fw fa-edit text-blue"></i>Edit
								</a>
							</li>';

			if($this->permissions('payment_modes_delete') && $mode->is_system==0)
			$str2.='<li>
								<a class="pointer" onclick="delete_payment_mode('.$mode->id.')" title="Delete Record" >
									<i class="fa fa-fw fa-trash text-red"></i>Delete
								</a>
							</li>';

			$str2.='</ul>
					</div>';

			$row[] = $str2;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->payment_modes->count_all(),
						"recordsFiltered" => $this->payment_modes->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function delete_payment_mode(){
		$this->permission_check('payment_modes_delete');
		$id=$this->input->post('q_id');
		echo $this->payment_modes->delete_payment_mode($id);
	}

	public function set_default(){
		$this->permission_check('payment_modes_edit');
		$id = $this->input->post('id');
		$store_id = get_current_store_id();
		$this->db->where('store_id', $store_id)->update('db_payment_modes', array('is_default' => 0));
		$this->db->where('id', $id)->where('store_id', $store_id)->update('db_payment_modes', array('is_default' => 1));
		echo "success";
	}
}
