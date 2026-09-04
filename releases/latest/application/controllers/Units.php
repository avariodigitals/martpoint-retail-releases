<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Units extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('units_model','units');
	}

	public function add(){
		$this->permission_check('units_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('units');
		$data['all_units'] = $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_units')->result();
		$unit_options = ['' => '-Select-'];
		foreach ($data['all_units'] as $u) {
			$unit_options[$u->id] = $u->unit_name;
		}
		$data['crud'] = [
			'page_title' => $this->lang->line('units'),
			'page_sub' => 'Create a new unit of measure',
			'form_id' => 'unit-form',
			'save_url' => 'units/new_unit',
			'update_url' => 'units/update_Unit',
			'list_url' => base_url('units'),
			'module' => 'units',
			'fields' => [
				['name' => 'unit_name', 'label' => 'Unit Name', 'type' => 'text', 'required' => true],
				['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
				['name' => 'parent_unit_id', 'label' => 'Parent Unit', 'type' => 'select', 'options' => $unit_options],
				['name' => 'conversion_factor', 'label' => 'Conversion Factor', 'type' => 'number'],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function new_unit(){

		$this->form_validation->set_rules('unit_name', 'Unit Name', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
			
			$result=$this->units->verify_and_save();
			echo $result;
		} else {
			echo "Please Enter Unit Name.";
		}
	}
	public function update($id){
		$this->belong_to('db_units',$id);
		$this->permission_check('units_edit');
		$data=$this->data;
		$result=$this->units->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('units');
		$data['all_units'] = $this->db->where('store_id', get_current_store_id())->where('status', 1)->where('id !=', $id)->get('db_units')->result();
		$unit_options = ['' => '-Select-'];
		foreach ($data['all_units'] as $u) {
			$unit_options[$u->id] = $u->unit_name;
		}
		$data['crud'] = [
			'page_title' => $this->lang->line('units'),
			'page_sub' => 'Update unit of measure',
			'form_id' => 'unit-form',
			'save_url' => 'units/new_unit',
			'update_url' => 'units/update_Unit',
			'list_url' => base_url('units'),
			'module' => 'units',
			'fields' => [
				['name' => 'unit_name', 'label' => 'Unit Name', 'type' => 'text', 'required' => true],
				['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
				['name' => 'parent_unit_id', 'label' => 'Parent Unit', 'type' => 'select', 'options' => $unit_options],
				['name' => 'conversion_factor', 'label' => 'Conversion Factor', 'type' => 'number'],
			],
		];
		$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function update_Unit(){
		$this->form_validation->set_rules('unit_name', 'Unit Name', 'trim|required');
		$this->form_validation->set_rules('q_id', '', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result=$this->units->update_Unit();
			echo $result;
		} else {
			echo "Please Enter Unit name.";
		}
	}
	public function index(){
		$this->permission_check('units_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('units_list');
		$data['rows'] = $this->units->get_datatables();
		$data['crud'] = [
			'page_title' => $this->lang->line('units_list'),
			'page_sub' => 'Manage product units of measure',
			'add_url' => base_url('units/add'),
			'add_label' => 'New Unit',
			'add_permission' => 'units_add',
			'columns' => [
				['title' => 'Unit Name', 'field' => 'unit_name', 'type' => 'text'],
				['title' => 'Description', 'field' => 'description', 'type' => 'text'],
				['title' => 'Status', 'field' => 'status', 'type' => 'status'],
				['title' => 'Action', 'type' => 'actions'],
			],
			'module' => 'units',
			'status_url' => 'units/update_status',
			'delete_url' => 'units/delete_unit',
			'edit_url' => base_url('units/update/{id}'),
			'delete_permission' => 'units_delete',
			'edit_permission' => 'units_edit',
			'bulk_delete' => false,
		];
		$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function ajax_list()
	{
		$list = $this->units->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $unit) {
			$no++;
			$row = array();

			$parent_str = "";
			if (!empty($unit->parent_unit_id)) {
				$parent = $this->db->where("id", $unit->parent_unit_id)->get("db_units")->row();
				$parent_str = " <small class=\"text-muted\">(".$unit->conversion_factor." per ".($parent ? $parent->unit_name : "parent").")</small>";
			}
			$row[] = $unit->unit_name . $parent_str;
			$row[] = $unit->description;

			 		if($unit->status==1){ 
			 			$str= "<span onclick='update_status(".$unit->id.",0)' id='span_".$unit->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$unit->id.",1)' id='span_".$unit->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;			
			         $str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											if($this->permissions('units_edit'))
											$str2.='<li>
												<a title="Editd Record ?" href="'.base_url('units/update/'.$unit->id).'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('units_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_unit('.$unit->id.')">
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
						"recordsTotal" => $this->units->count_all(),
						"recordsFiltered" => $this->units->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	public function update_status(){
		$this->permission_check_with_msg('units_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');
		$result=$this->units->update_status($id,$status);
		return $result;
	}
	public function delete_unit(){
		$this->permission_check_with_msg('units_delete');
		$id=$this->input->post('q_id');
		$result=$this->units->delete_unit($id);
		return $result;
	}
	//ITS FROM POP UP MODAL
   public function add_unit_modal(){
      $this->form_validation->set_rules('unit_name', 'Unit Name', 'trim|required');
      if ($this->form_validation->run() == TRUE) {
         $result=$this->units->verify_and_save();
         //fetch latest item details
         $res=array();
         $query=$this->db->select("id,unit_name")
                  ->where('store_id',get_current_store_id())
                  ->from('db_units')
                  ->order_by('id','desc')
                  ->limit(1)->get();
         $res['id']=$query->row()->id;
         $res['unit']=$query->row()->unit_name;
         $res['result']=$result;
         
         echo json_encode($res);

      } 
      else {
         echo "Please Fill Compulsory(* marked) Fields.";
      }
   }
   //END
}

