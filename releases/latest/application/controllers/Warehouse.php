<?php 
	/**
	 * Author: Rapheal Ogundiran - Avario
	 * Date: 2019 - 2026
	 */
	class Warehouse extends MY_Controller{
		public function __construct(){
			parent::__construct();
			$this->load_global();
			$this->load->model('warehouse_model','warehouse');
		}
		public function index(){
			$this->permission_check('warehouse_view');
			$data=$this->data;//My_Controller constructor data accessed here
			$branch_label = mp_label('warehouse','Branch');
			$branch_label_plural = mp_label('branch','Branches');
			$data['page_title']=$branch_label_plural.' List';
			$data['rows'] = $this->db->where('store_id', get_current_store_id())->order_by('id','desc')->get('db_warehouse')->result();
			$data['crud'] = [
				'page_title' => $branch_label_plural.' List',
				'page_sub' => 'Manage '.$branch_label_plural.' and stock locations',
				'add_url' => base_url('warehouse/add'),
				'add_label' => 'New '.$branch_label,
				'add_permission' => 'warehouse_add',
				'columns' => [
					['title' => $branch_label.' Name', 'field' => 'warehouse_name', 'type' => 'text'],
					['title' => 'Mobile', 'field' => 'mobile', 'type' => 'text'],
					['title' => 'Email', 'field' => 'email', 'type' => 'text'],
					['title' => 'Status', 'field' => 'status', 'type' => 'status'],
					['title' => 'Action', 'type' => 'actions'],
				],
				'module' => 'warehouse',
				'status_url' => 'warehouse/status_update',
				'delete_url' => 'warehouse/delete_warehouse',
				'delete_param' => 'id',
				'edit_url' => base_url('warehouse/edit/{id}'),
				'delete_permission' => 'warehouse_delete',
				'edit_permission' => 'warehouse_edit',
				'bulk_delete' => false,
			];
			$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function save_or_update(){
			
			$data=$this->data;//My_Controller constructor data accessed here
			$this->form_validation->set_rules('warehouse_name', mp_label('warehouse','Branch').' Name', 'required|trim');
			
			if ($this->form_validation->run() == TRUE) {
				if($this->input->post('command')=='save'){
					// Check branch limit
					$branch_check = check_subscription_limit('branch_limit');
					if($branch_check !== true){
						echo $branch_check;
						return;
					}
					$result=$this->warehouse->verify_and_save($data);
				}
				else{
					$result=$this->warehouse->verify_and_update($data);
				}
				
				echo $result;
			} 
			else {
				//echo validation_errors();
				echo "Please Fill Compulsory(* marked) Fields.";
			}
		
		}
		public function add(){
			$this->permission_check('warehouse_add');
			$data=$this->data;
			$branch_label = mp_label('warehouse','Branch');
			$data['page_title']=$branch_label;
			$data['command']='save';
			$data['crud'] = [
				'page_title' => $branch_label,
				'page_sub' => 'Create a new '.$branch_label,
				'form_id' => 'warehouse-form',
				'save_url' => 'warehouse/save_or_update',
				'update_url' => 'warehouse/save_or_update',
				'list_url' => base_url('warehouse'),
				'module' => 'warehouse',
				'fields' => [
					['name' => 'warehouse_name', 'label' => $branch_label.' Name', 'type' => 'text', 'required' => true],
					['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
					['name' => 'email', 'label' => 'Email', 'type' => 'email'],
					['name' => 'command', 'type' => 'hidden'],
				],
			];
			$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function status_update(){
			$this->permission_check('warehouse_edit');
			$id=$this->input->post('id');
			$status=$this->input->post('status');
			$result=$this->warehouse->status_update($id,$status);
			return $result;

		}
		public function edit($id){
			$this->belong_to('db_warehouse',$id);
			$this->permission_check('warehouse_edit');
			$data=$this->warehouse->get_details($id);
			$branch_label = mp_label('warehouse','Branch');
			$data['page_title']=$branch_label;
			$data['command']='update';
			$data['crud'] = [
				'page_title' => $branch_label,
				'page_sub' => 'Update '.$branch_label.' details',
				'form_id' => 'warehouse-form',
				'save_url' => 'warehouse/save_or_update',
				'update_url' => 'warehouse/save_or_update',
				'list_url' => base_url('warehouse'),
				'module' => 'warehouse',
				'fields' => [
					['name' => 'warehouse_name', 'label' => $branch_label.' Name', 'type' => 'text', 'required' => true],
					['name' => 'mobile', 'label' => 'Mobile', 'type' => 'text'],
					['name' => 'email', 'label' => 'Email', 'type' => 'email'],
					['name' => 'command', 'type' => 'hidden'],
				],
			];
			$data['content'] = $this->load->view('admin/desktop/crud_form', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function delete_warehouse(){
			$this->permission_check('warehouse_delete');
			$id=$this->input->post('id');
			try {
				$result=$this->warehouse->delete_warehouse($id);
				echo $result;
			} catch (Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
		}

		/*Used in items-list.php*/
		public function view_warehouse_wise_stock_item(){
			$this->permission_check_with_msg('items_view');
			$this->load->model('warehouse_model');
			$item_id=$this->input->post('item_id');
			echo $this->warehouse_model->view_warehouse_wise_stock_item($item_id);
		}

		public function debug_warehouse_delete(){
			$this->permission_check('warehouse_delete');
			$id = $this->input->get('id');
			$storeId = get_current_store_id();
			$tables = [
				'db_sales' => ['warehouse_id'=>$id],
				'db_purchase' => ['warehouse_id'=>$id],
				'db_stockadjustment' => ['warehouse_id'=>$id],
				'db_hold' => ['warehouse_id'=>$id],
				'db_quotation' => ['warehouse_id'=>$id],
				'db_stocktransfer_from' => ['table'=>'db_stocktransfer','where'=>['warehouse_from'=>$id]],
				'db_stocktransfer_to' => ['table'=>'db_stocktransfer','where'=>['warehouse_to'=>$id]],
				'db_warehouseitems' => ['warehouse_id'=>$id],
				'db_userswarehouses' => ['warehouse_id'=>$id],
				'db_item_barcodes' => ['warehouse_id'=>$id],
				'db_users_default' => ['table'=>'db_users','where'=>['default_warehouse_id'=>$id]],
			];
			$results = [];
			foreach($tables as $name => $cfg){
				$t = isset($cfg['table']) ? $cfg['table'] : $name;
				$w = isset($cfg['where']) ? $cfg['where'] : $cfg;
				$w['store_id'] = $storeId;
				$results[$name] = (int) $this->db->get_where($t, $w)->num_rows();
			}
			$wh = $this->db->where('id', $id)->get('db_warehouse')->row();
			echo json_encode([
				'warehouse_id' => $id,
				'warehouse' => $wh,
				'counts' => $results,
				'total_refs' => array_sum($results)
			], JSON_PRETTY_PRINT);
		}
	}

	

?>
