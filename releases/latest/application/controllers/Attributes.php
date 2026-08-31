<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attributes extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('attributes_model','attributes');
	}

	public function index(){
		$this->permission_check('attributes_view');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('attributes_list');
		$this->load->view('attributes/attributes-list', $data);
	}

	public function add(){
		$this->permission_check('attributes_add');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('attributes_add');
		$this->load->view('attributes/attributes', $data);
	}

	public function edit($id){
		$this->permission_check('attributes_add');
		$this->belong_to('db_attributes', $id);
		$data = $this->data;
		$detail = $this->attributes->get_details($id);
		if(!$detail){ show_404(); exit; }
		$data['q_id'] = $detail->id;
		$data['attribute_type'] = $detail->attribute_type;
		$data['attribute_value'] = $detail->attribute_value;
		$data['sort_order'] = $detail->sort_order;
		$data['page_title'] = $this->lang->line('attributes_edit');
		$this->load->view('attributes/attributes', $data);
	}

	public function save(){
		$this->form_validation->set_rules('attribute_type', 'Attribute Type', 'trim|required');
		$this->form_validation->set_rules('attribute_value', 'Attribute Value', 'trim|required');
		if($this->form_validation->run() == TRUE){
			$command = $this->input->post('command', TRUE);
			if($command == 'update'){
				echo $this->attributes->update_attribute();
			} else {
				echo $this->attributes->verify_and_save();
			}
		} else {
			echo "Please fill required fields.";
		}
	}

	public function delete(){
		$this->permission_check_with_msg('attributes_delete');
		$id = (int)$this->input->post('q_id', TRUE);
		echo $this->attributes->delete_attribute($id);
	}

	public function ajax_list(){
		$list = $this->attributes->get_datatables();
		$data = array();
		$no = isset($_POST['start']) ? (int)$_POST['start'] : 0;
		foreach($list as $attr){
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value="'.$attr->id.'" class="checkbox column_checkbox">';
			$row[] = htmlspecialchars(ucfirst($attr->attribute_type));
			$row[] = htmlspecialchars($attr->attribute_value);
			$row[] = $attr->sort_order;
			$row[] = ($attr->status == 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
			$str = '<div class="btn-group"><a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a><ul role="menu" class="dropdown-menu dropdown-light pull-right">';
			$str .= '<li><a href="'.base_url('attributes/edit/'.$attr->id).'"><i class="fa fa-fw fa-edit text-blue"></i>Edit</a></li>';
			$str .= '<li><a style="cursor:pointer" onclick="delete_attribute('.$attr->id.')"><i class="fa fa-fw fa-trash text-red"></i>Delete</a></li>';
			$str .= '</ul></div>';
			$row[] = $str;
			$data[] = $row;
		}
		$output = array(
			'draw' => isset($_POST['draw']) ? (int)$_POST['draw'] : 1,
			'recordsTotal' => $this->attributes->count_all(),
			'recordsFiltered' => $this->attributes->count_filtered(),
			'data' => $data,
		);
		echo json_encode($output);
	}
}
