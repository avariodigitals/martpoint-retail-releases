<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class State extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('state_model','state');
	}

	public function index(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('states_list');
		$data['rows'] = $this->state->get_datatables();
		$data['crud'] = [
			'page_title' => $this->lang->line('states_list'),
			'page_sub' => 'Manage states and provinces',
			'add_url' => base_url('state/add'),
			'add_label' => 'New State',
			'columns' => [
				['title' => 'State', 'field' => 'state', 'type' => 'text'],
				['title' => 'Country', 'field' => 'country', 'type' => 'text'],
				['title' => 'Status', 'field' => 'status', 'type' => 'status'],
				['title' => 'Action', 'type' => 'actions'],
			],
			'module' => 'state',
			'status_url' => 'state/update_status',
			'delete_url' => 'state/delete_state',
			'edit_url' => base_url('state/update/{id}'),
			'bulk_delete' => false,
		];
		$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	private function _country_options(){
		$allowed = ['Nigeria','Ghana','Benin','Burkina Faso','Cape Verde',"Cote d'Ivoire","Côte d'Ivoire",'Gambia','Guinea','Guinea-Bissau','Liberia','Mali','Mauritania','Niger','Senegal','Sierra Leone','Togo','Cameroon','United Kingdom','UK','Great Britain','United States','USA','United States of America','US'];
		$options = ['' => '-Select-'];
		$q = $this->db->where('status',1)->where_in('country',$allowed)->get('db_country');
		foreach($q->result() as $row){
			$options[$row->country] = $row->country;
		}
		return $options;
	}
	public function newstate(){
		$this->form_validation->set_rules('state', 'State', 'trim|required');
		$this->form_validation->set_rules('country', 'Country', 'trim|required');
		if ($this->form_validation->run() == TRUE) {
			$result=$this->state->verify_and_save();
			echo $result;
		} else {
			echo "Please enter compulsary(* marked) fields!";
		}
	}
	public function update($id){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$result=$this->state->get_details($id);
		$data=array_merge($this->data,$result);
		$data['page_title']=$this->lang->line('state');
		$data['crud'] = [
			'page_title' => $this->lang->line('state'),
			'page_sub' => 'Update state',
			'form_id' => 'state-form',
			'save_url' => 'state/newstate',
			'update_url' => 'state/update_state',
			'list_url' => base_url('state'),
			'module' => 'state',
			'fields' => [
				['name' => 'state', 'label' => 'State Name', 'type' => 'text', 'required' => true],
				['name' => 'country', 'label' => 'Country', 'type' => 'select', 'required' => true, 'options' => $this->_country_options()],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function update_state(){
		
		$this->form_validation->set_rules('state', 'State', 'trim|required');
		$this->form_validation->set_rules('country', 'Country', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');
		if ($this->form_validation->run() == TRUE) {
			$result=$this->state->update_state();
			echo $result;
		} else {
			echo "Please Enter state name.";
		}
	}
	public function add(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('state');
		$data['crud'] = [
			'page_title' => $this->lang->line('state'),
			'page_sub' => 'Create a new state',
			'form_id' => 'state-form',
			'save_url' => 'state/newstate',
			'update_url' => 'state/update_state',
			'list_url' => base_url('state'),
			'module' => 'state',
			'fields' => [
				['name' => 'state', 'label' => 'State Name', 'type' => 'text', 'required' => true],
				['name' => 'country', 'label' => 'Country', 'type' => 'select', 'required' => true, 'options' => $this->_country_options()],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function ajax_list()
	{
		$list = $this->state->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $state) {
			$no++;
			$row = array();
			
			$row[] = $state->state;
			$row[] = $state->country;
			

			 		if($state->status==1){ 
			 			$str= "<span onclick='update_status(".$state->id.",0)' id='span_".$state->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$state->id.",1)' id='span_".$state->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;			
			         $str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											//if($this->permissions('places_edit'))
											$str2.='<li>
												<a title="Edit Record ?" href="'.base_url().'state/update/'.$state->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											//if($this->permissions('places_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_state('.$state->id.')">
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
						"recordsTotal" => $this->state->count_all(),
						"recordsFiltered" => $this->state->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function update_status(){
		//$this->permission_check_with_msg('places_edit');
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$id=$this->input->post('id');
		$status=$this->input->post('status');
		$result=$this->state->update_status($id,$status);
		echo $result;
	}
	public function delete_state(){
		//$this->permission_check_with_msg('places_delete');
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$id=$this->input->post('q_id');
		$result=$this->state->delete_state($id);
		return $result;
	}
}

