<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currency extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('currency_model','currency');
	}

	public function add(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('currency');
		$data['crud'] = [
			'page_title' => $this->lang->line('currency'),
			'page_sub' => 'Create a new currency',
			'form_id' => 'currency-form',
			'save_url' => 'currency/newcurrency',
			'update_url' => 'currency/update_currency',
			'list_url' => base_url('currency/view'),
			'module' => 'currency',
			'fields' => [
				['name' => 'currency_name', 'label' => 'Currency Name', 'type' => 'text', 'required' => true],
				['name' => 'currency_code', 'label' => 'Currency Code', 'type' => 'text', 'required' => true],
				['name' => 'currency', 'label' => 'Symbol', 'type' => 'text', 'required' => true],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function newcurrency(){
		$this->form_validation->set_rules('currency_name', 'Currency Name', 'trim|required');
		$this->form_validation->set_rules('currency', 'Currency', 'trim|required');
	
		if ($this->form_validation->run() == TRUE) {
			
			$this->load->model('currency_model');
			$result=$this->currency_model->verify_and_save();
			echo $result;
		} else {
			echo "Please Enter Compulsory(*) Fields!";
		}
	}
	public function update($id){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;

		$this->load->model('currency_model');
		$result=$this->currency_model->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('currency');
		$data['crud'] = [
			'page_title' => $this->lang->line('currency'),
			'page_sub' => 'Update currency details',
			'form_id' => 'currency-form',
			'save_url' => 'currency/newcurrency',
			'update_url' => 'currency/update_currency',
			'list_url' => base_url('currency/view'),
			'module' => 'currency',
			'fields' => [
				['name' => 'currency_name', 'label' => 'Currency Name', 'type' => 'text', 'required' => true],
				['name' => 'currency_code', 'label' => 'Currency Code', 'type' => 'text', 'required' => true],
				['name' => 'currency', 'label' => 'Symbol', 'type' => 'text', 'required' => true],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function update_currency(){
		$this->form_validation->set_rules('currency_name', 'Currency Name', 'trim|required');
		$this->form_validation->set_rules('currency', 'Currency', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$this->load->model('currency_model');
			$result=$this->currency_model->update_currency();
			echo $result;
		} else {
			echo "Please Enter Compulsory(*) Fields!";
		}
	}
	public function view(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('currencies_list');
		$data['rows'] = $this->currency->get_datatables();
		$data['crud'] = [
			'page_title' => $this->lang->line('currencies_list'),
			'page_sub' => 'Manage global currencies',
			'add_url' => base_url('currency/add'),
			'add_label' => 'New Currency',
			'columns' => [
				['title' => '', 'type' => 'checkbox'],
				['title' => 'Currency Name', 'field' => 'currency_name', 'type' => 'text'],
				['title' => 'Currency Code', 'field' => 'currency_code', 'type' => 'text'],
				['title' => 'Symbol', 'field' => 'currency', 'type' => 'text'],
				['title' => 'Status', 'field' => 'status', 'type' => 'status'],
				['title' => 'Action', 'type' => 'actions'],
			],
			'module' => 'currency',
			'status_url' => 'currency/update_status',
			'delete_url' => 'currency/delete_currency',
			'multi_delete_url' => 'currency/multi_delete',
			'edit_url' => base_url('currency/update/{id}'),
			'bulk_delete' => true,
		];
		$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function ajax_list()
	{
		$list = $this->currency->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $currency) {
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$currency->id.' class="checkbox column_checkbox" >';
			$row[] = $currency->currency_name;
			$row[] = $currency->currency_code;
			$row[] = $currency->currency;

			 		if($currency->status==1){ 
			 			$str= "<span onclick='update_status(".$currency->id.",0)' id='span_".$currency->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$currency->id.",1)' id='span_".$currency->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;			
					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											//if($this->permissions('currency_edit'))
											$str2.='<li>
												<a title="Edit Record ?" href="'.base_url().'currency/update/'.$currency->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											//if($this->permissions('currency_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_currency('.$currency->id.')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';			

			$row[] = $str2;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->currency->count_all(),
						"recordsFiltered" => $this->currency->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function update_status(){
		//$this->permission_check_with_msg('currency_edit');
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		$this->load->model('currency_model');
		$result=$this->currency_model->update_status($id,$status);
		echo $result;
	}
	
	public function delete_currency(){
		//$this->permission_check_with_msg('currency_delete');
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$id=$this->input->post('q_id');
		return $this->currency->delete_currencies_from_table($id);
	}
	public function multi_delete(){
		//$this->permission_check_with_msg('currency_delete');
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$ids=implode (",",$_POST['checkbox']);
		return $this->currency->delete_currencies_from_table($ids);
	}

}

