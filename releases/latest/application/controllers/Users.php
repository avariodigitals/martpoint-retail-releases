<?php 
	/**
	 * Author: Rapheal Ogundiran - Avario
	 * Date: 2019 - 2026
	 */
	class Users extends MY_Controller{
		public function __construct(){
			parent::__construct();
			$this->load_global();

			$this->load->model('state_model','state');
			// Debug mode removed for security — never expose errors in production
		}
		
		public function index(){
			$this->permission_check('users_add');
			$data=$this->data;//My_Controller constructor data accessed here
			$data['page_title']=$this->lang->line('create_users');
			$data['content'] = $this->load->view('admin/desktop/users_form', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function save_or_update(){
			$data=$this->data;//My_Controller constructor data accessed here
			$this->form_validation->set_rules('username', 'Username Name', 'required|trim|min_length[2]|max_length[50]');

			$this->form_validation->set_rules('new_user', 'First Name', 'required|trim|min_length[2]|max_length[50]');
			
			$cmd = $this->input->get('command') ?: $this->input->post('command');
			if($cmd!='update'){
				$this->form_validation->set_rules('pass', 'Password', 'required|trim|min_length[2]|max_length[50]');
			}

			if ($this->form_validation->run() == TRUE) {
				$this->load->model('users_model');
				
				if($cmd!='update'){
					// Check user limit
					$user_check = check_subscription_limit('user_limit');
					if($user_check !== true){
						echo $user_check;
						return;
					}
					$result=$this->users_model->verify_and_save($data);

					// Send welcome email on success
					if($result == 'success' && !empty($this->input->post('email'))){
						$this->load->model('email_service');
						$this->load->model('email_template_model');
						$this->email_template_model->seedDefaults();

						$role_name = '';
						if($this->input->post('role_id')){
							$role = $this->db->where('id', $this->input->post('role_id'))->get('db_roles')->row();
							$role_name = $role ? $role->role_name : '';
						}
						$store = get_store_details();

						$this->email_service->sendTemplate('staff_invite', $this->input->post('email'), [
							'staff_name'  => $this->input->post('new_user'),
							'store_name'  => $store->store_name ?? 'MartPoint',
							'staff_role'  => $role_name,
							'login_link'  => base_url('login')
						]);
					}
				}
				else{
					
					$result=$this->users_model->verify_and_update($data);
				}
				
				echo $result;
			} 
			else {
				echo validation_errors();
				//echo  "Username & Password must have 5 to 15 Characters!";
			}
		
		}
		public function view(){
			$this->permission_check('users_view');
			$data=$this->data;
			$data['page_title']=$this->lang->line('users_list');

			// Replicate the original user list query
			if(!is_admin() && !is_store_admin()){
				$this->db->where(" role_id not in (2)");
			}
			$this->db->select("a.*,b.role_name");
			$this->db->where("b.id=a.role_id");
			if(!is_admin()){
				$this->db->where("a.store_id",get_current_store_id());
			}
			$q1=$this->db->from('db_users as a, db_roles as b')->order_by('a.id','desc')->get();

			$rows = [];
			foreach($q1->result() as $res1){
				$store_rec = get_store_details($res1->store_id);
				$wh_q = $this->db->select('w.warehouse_name')
					->from('db_userswarehouses uw')
					->join('db_warehouse w','w.id=uw.warehouse_id','left')
					->where('uw.user_id',$res1->id)
					->get();
				$wnames = $wh_q->num_rows()>0 ? array_map(function($w){ return $w->warehouse_name; }, $wh_q->result()) : [];
				$warehouses = $wnames ? '<span class="label label-info">' . implode('</span> <span class="label label-info">', $wnames) . '</span>' : '<span class="text-muted">-</span>';
				$store_name = $store_rec ? $store_rec->store_name : '-';
				$store_admin_badge = ($store_rec && $store_rec->user_id==$res1->id) ? " <span class='label label-success' title='Store Admin'>Store Admin</span>" : '';
				$row = new stdClass();
				$row->id = $res1->id;
				$row->store_name = $store_name;
				$row->username = $res1->username;
				$row->full_name = $res1->first_name.' '.$res1->last_name.$store_admin_badge;
				$row->mobile = $res1->mobile;
				$row->email = $res1->email;
				$row->role_name = $res1->role_name;
				$row->warehouses = $warehouses;
				$row->created_date = show_date($res1->created_date);
				$row->status = $res1->status;
				$rows[] = $row;
			}
			$data['rows'] = $rows;

			$data['crud'] = [
				'page_title' => $this->lang->line('users_list'),
				'page_sub' => 'Manage staff and store users',
				'add_url' => base_url('users/'),
				'add_label' => 'Create User',
				'add_permission' => 'users_add',
				'columns' => [
					['title' => 'Store', 'field' => 'store_name', 'type' => 'text'],
					['title' => 'Username', 'field' => 'username', 'type' => 'text'],
					['title' => 'Name', 'field' => 'full_name', 'type' => 'text'],
					['title' => 'Mobile', 'field' => 'mobile', 'type' => 'text'],
					['title' => 'Email', 'field' => 'email', 'type' => 'text'],
					['title' => 'Role', 'field' => 'role_name', 'type' => 'text'],
					['title' => mp_label('warehouse','Branches'), 'field' => 'warehouses', 'type' => 'raw'],
					['title' => 'Created', 'field' => 'created_date', 'type' => 'text'],
					['title' => 'Status', 'type' => 'custom', 'callback' => function($row){
						if($row->id == 1) return "<span class='label label-default' disabled style='cursor:disabled'>Restricted</span>";
						if($row->status == 1) return "<span onclick='update_status(".$row->id.",0)' id='span_".$row->id."' class='label label-success' style='cursor:pointer'>Active</span>";
						return "<span onclick='update_status(".$row->id.",1)' id='span_".$row->id."' class='label label-danger' style='cursor:pointer'>Inactive</span>";
					}],
					['title' => 'Action', 'type' => 'custom', 'callback' => function($row){
						$CI =& get_instance();
						$out = '<div class="mp-actions">';
						if($CI->permissions('users_edit')){
							$out .= '<a href="'.base_url('users/edit/'.$row->id).'" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>';
						}
						if($CI->permissions('users_delete') && $row->id != 1){
							$out .= '<button type="button" class="mp-delete" title="Delete" onclick="delete_user('.$row->id.')"><i class="fa fa-trash"></i></button>';
						}
						$out .= '</div>';
						return $out;
					}],
				],
				'module' => 'users',
				'status_url' => 'users/status_update',
				'delete_url' => 'users/delete_user',
				'edit_url' => base_url('users/edit/{id}'),
				'delete_permission' => 'users_delete',
				'edit_permission' => 'users_edit',
				'bulk_delete' => false,
			];
			$data['content'] = $this->load->view('admin/desktop/crud_list', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function status_update(){
			$this->permission_check_with_msg('users_edit');
			$userid=$this->input->post('id');
			$status=$this->input->post('status');

			$this->load->model('users_model');
			$result=$this->users_model->status_update($userid,$status);
			return $result;

		}
		public function password_reset(){
			$data=$this->data;//My_Controller constructor data accessed here
			$data['page_title']=$this->lang->line('change_password');
			$data['content'] = $this->load->view('change-pass', $data, TRUE);
		$this->load->view('mp_layout', $data);
		}
		public function password_update(){
			if($this->session->userdata('inv_username')=='admin' && demo_app()){
	        	echo "Restricted Admin Password Change";exit();
	        }
	        if(demo_app()){
				echo "Restricted in Demo";exit();
			}
			$data=$this->data;//My_Controller constructor data accessed here
			$currentpass=$this->input->post('currentpass');
			$newpass=$this->input->post('newpass');

			$this->load->model('users_model');
			$result=$this->users_model->password_update(md5($currentpass),md5($newpass),$data);
			echo $result;

		}
		public function dbbackup(){
			if(demo_app()){
				echo "Restricted in Demo";exit();
			}
			if(!special_access()){
				$this->permission_check_with_msg('database_backup');
			}

			if(!special_access()){
				show_error("Access Denied", 403, $heading = "Unauthorized Access!!");exit();	
			}
	            
			// Load the DB utility class
			$this->load->dbutil();
			$prefs = array( 'newline' => "\n",
				'format' => 'zip',
				'filename' => 'database_backup.sql',
				'foreign_key_checks' => FALSE,
				);


			// Backup your entire database and assign it to a variable
			$backup = $this->dbutil->backup($prefs);

			// Load the file helper and write the file to your server
			$this->load->helper('file');
			write_file('dbbackup/dbbackup'.date('d-M-Y-h-m-s').'.gz', $backup);

			// Load the download helper and send the file to your desktop
			$this->load->helper('download');
			force_download('dbbackup/dbbackup'.date('d-M-Y-h-m-s').'.gz', $backup);

		}

		public function edit($id){
			if(!is_admin()){
				$user_store_id = $this->db->select('store_id')->where("id",$id)->get('db_users')->row()->store_id;
				if(empty($user_store_id)){
					show_error("Invalid Data", 403, $heading = "You have entered Invalid Data!!");exit();
				}
				if($user_store_id!=get_current_store_id()){
					show_error("Access Denied", 403, $heading = "Unauthorized Access!!");exit();
				}
			}
			$this->permission_check('users_edit');
			$this->load->model('users_model');
			$data=$this->users_model->get_details($id);
			$data['page_title']=$this->lang->line('edit_user');
			$data['content'] = $this->load->view('admin/desktop/users_form', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		public function delete_user(){
			$this->permission_check_with_msg('users_delete');
			$this->load->model('users_model');
			$id=$this->input->post('q_id');
			$result=$this->users_model->delete_user($id);
			return $result;
		}

		public function get_roles_select_list(){
			echo get_roles_select_list(null,$_POST['store_id']);
		}
	}

	

?>
