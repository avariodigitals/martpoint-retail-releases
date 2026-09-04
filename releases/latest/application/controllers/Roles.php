<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('roles_model','roles');
	}

	public function add(){
		$this->permission_check('roles_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('new_role');
		$data['crud'] = [
			'page_title' => $this->lang->line('new_role'),
			'page_sub' => 'Create a new role',
			'form_id' => 'role-form',
			'save_url' => 'roles/newrole',
			'update_url' => 'roles/update_role',
			'list_url' => base_url('roles/view'),
			'module' => 'roles',
			'fields' => [
				['name' => 'role_name', 'label' => 'Role Name', 'type' => 'text', 'required' => true],
				['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function newrole(){
		$this->form_validation->set_rules('role_name', 'Role Name', 'trim|required');
		if ($this->form_validation->run() == TRUE) {
			
			$this->load->model('roles_model');
			$result=$this->roles_model->verify_and_save();
			echo $result;
		} else {
			echo "Please Enter Role Name.";
		}
	}
	public function update($id){
		$this->belong_to('db_roles',$id);
		if($id==1){
			$this->session->set_flashdata('error', "Restricted!! Admin Permissions Can't Update!");
			redirect(base_url('roles/view'),'refresh');
		}
		$this->permission_check('roles_edit');
		$data=$this->data;

		$this->load->model('roles_model');
		$result=$this->roles_model->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('roles');
		$data['crud'] = [
			'page_title' => $this->lang->line('roles'),
			'page_sub' => 'Update role details',
			'form_id' => 'role-form',
			'save_url' => 'roles/newrole',
			'update_url' => 'roles/update_role',
			'list_url' => base_url('roles/view'),
			'module' => 'roles',
			'fields' => [
				['name' => 'role_name', 'label' => 'Role Name', 'type' => 'text', 'required' => true],
				['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function update_role(){
		$this->form_validation->set_rules('role_name', 'Role Name', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$this->load->model('roles_model');
			$result=$this->roles->update_role();
			echo $result;
		} else {
			echo "Please Enter Role Name.";
		}
	}
	public function view(){
		$this->permission_check('roles_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('roles_list');

		// Fetch roles directly for inline rendering
		$this->db->select('r.*, s.store_name');
		$this->db->from('db_roles r');
		$this->db->join('db_store s', 's.id = r.store_id', 'left');
		if(!is_admin()){
			$this->db->where('r.store_id', get_current_store_id());
		}
		$this->db->order_by('r.id', 'ASC');
		$q = $this->db->get();
		$rows = [];
		$no = 0;
		foreach($q->result() as $role){
			$no++;
			$row = new stdClass();
			$row->id = $role->id;
			$row->no = $no;
			$row->store_name = $role->store_name ?? '-';
			$row->role_name = $role->role_name;
			$row->description = $role->description;
			$row->status = $role->status;
			$row->restricted = ($role->id == 1);
			$rows[] = $row;
		}
		$data['rows'] = $rows;

		$columns = [
			['title' => '#', 'field' => 'no', 'type' => 'text'],
		];
		if(is_admin()){
			$columns[] = ['title' => 'Store', 'field' => 'store_name', 'type' => 'text'];
		}
		$columns[] = ['title' => 'Role Name', 'field' => 'role_name', 'type' => 'text'];
		$columns[] = ['title' => 'Description', 'field' => 'description', 'type' => 'text'];
		$columns[] = ['title' => 'Status', 'type' => 'custom', 'callback' => function($row){
			if($row->restricted) return "<span class='label label-warning'>Restricted</span>";
			if($row->status == 1) return "<span onclick='update_status(".$row->id.",0)' id='span_".$row->id."' class='label label-success' style='cursor:pointer'>Active</span>";
			return "<span onclick='update_status(".$row->id.",1)' id='span_".$row->id."' class='label label-danger' style='cursor:pointer'>Inactive</span>";
		}];
		$columns[] = ['title' => 'Action', 'type' => 'custom', 'callback' => function($row){
			$CI =& get_instance();
			$out = '<div class="mp-actions">';
			if($CI->permissions('roles_edit') && !$row->restricted){
				$out .= '<a href="'.base_url('roles/update/'.$row->id).'" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			if($CI->permissions('roles_delete') && $row->id != store_admin_id()){
				$out .= '<button type="button" class="mp-delete" title="Delete" onclick="delete_roles('.$row->id.')"><i class="fa fa-trash"></i></button>';
			}
			$out .= '</div>';
			return $out;
		}];

		$data['crud'] = [
			'page_title' => $this->lang->line('roles_list'),
			'page_sub' => 'Manage user roles',
			'add_url' => base_url('roles/add'),
			'add_label' => 'New Role',
			'add_permission' => 'roles_add',
			'columns' => $columns,
			'module' => 'roles',
			'status_url' => 'roles/update_status',
			'delete_url' => 'roles/delete_roles',
			'edit_url' => base_url('roles/update/{id}'),
			'delete_permission' => 'roles_delete',
			'edit_permission' => 'roles_edit',
			'bulk_delete' => false,
		];
		$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function ajax_list()
	{
		// Release the session lock early so concurrent AJAX requests don't block
		// on the session file while this query runs (fixes "Processing..." / Ajax error).
		session_write_close();

		$list = $this->roles->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $roles) {
			$no++;
			$row = array();
			$row[] = $no;
			if(is_admin()){
				$row[] = get_store_name($roles->store_id);
			}
			$row[] = $roles->role_name;
			$row[] = $roles->description;
					if($roles->id==1){
						$str ="<span class='label label-warning' style=''> Restricted </span>";
					}
					else{
						if($roles->status==1){ 
			 			$str= "<span onclick='update_status(".$roles->id.",0)' id='span_".$roles->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
						else{ 
							$str = "<span onclick='update_status(".$roles->id.",1)' id='span_".$roles->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
						}	
					}
			 		
			$row[] = $str;	

					 $str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" data-container="body" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light dropdown-menu-right">';

											if($this->permissions('roles_edit'))
											$str2.='<li>
												<a title="Edit Record ?" href="'.base_url().'roles/update/'.$roles->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('roles_delete') && $roles->id!=store_admin_id())
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_roles('.$roles->id.')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>';
												
												$str2.='</ul>
									</div>';

			$row[] = ($roles->id==1) ? '--':$str2;			


			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->roles->count_all(),
						"recordsFiltered" => $this->roles->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function update_status(){
		$this->permission_check_with_msg('roles_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		$this->load->model('roles_model');
		$result=$this->roles_model->update_status($id,$status);
		return $result;
	}
	
	public function delete_roles(){
		$this->permission_check_with_msg('roles_delete');
		$id=$this->input->post('q_id');
		return $this->roles->delete_roles_from_table($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('roles_delete');
		$ids=implode (",",$_POST['checkbox']);
		return $this->roles->delete_roles_from_table($ids);
	}

}

