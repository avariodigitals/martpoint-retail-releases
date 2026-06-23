<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {
	
	public function __construct()
	{
		parent::__construct();
	}
	public function verify_and_save($data){
		
		$store_id = isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : get_current_store_id();
		
		//varify max sales usage of the package subscription
		validate_package_offers('max_users','db_users',$store_id);
		//END

		// Explicit POST extraction (security: never use extract($_POST))
		$username = $this->input->post('username', TRUE);
		$new_user = $this->input->post('new_user', TRUE);
		$last_name = $this->input->post('last_name', TRUE);
		$pass = $this->input->post('pass', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$email = $this->input->post('email', TRUE);
		$role_id = $this->input->post('role_id', TRUE);
		$default_warehouse_id = $this->input->post('default_warehouse_id', TRUE);
		$warehouses = $this->input->post('warehouses');
		$approval_pin = $this->input->post('approval_pin', TRUE);
		$CUR_DATE = isset($data['CUR_DATE']) ? $data['CUR_DATE'] : date('Y-m-d');
		$CUR_TIME = isset($data['CUR_TIME']) ? $data['CUR_TIME'] : date('H:i:s');
		$CUR_USERNAME = isset($data['CUR_USERNAME']) ? $data['CUR_USERNAME'] : '';
		$SYSTEM_IP = isset($data['SYSTEM_IP']) ? $data['SYSTEM_IP'] : '';
		$SYSTEM_NAME = isset($data['SYSTEM_NAME']) ? $data['SYSTEM_NAME'] : '';
		$this->db->trans_begin();

		$profile_picture='';
		if(!empty($_FILES['profile_picture']['name'])){
			$config['upload_path']          = './uploads/users/';
	        $config['allowed_types']        = 'gif|jpg|png';
	        $config['max_size']             = 500;
	        $config['max_width']            = 500;
	        $config['max_height']           = 500;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('profile_picture'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {
	        	   $profile_picture='uploads/users/'.$this->upload->data('file_name');
	        }
		}


		$this->db->where('UPPER(username)', strtoupper($username));
		$query = $this->db->get('db_users')->num_rows();
		if($query>0){ return "This username already exist.";}

		if(!empty($mobile)){
			$this->db->where('mobile', $mobile);
			$query = $this->db->get('db_users')->num_rows();
			if($query>0){ return "This Moble Number already exist.";}
		}
		if(!empty($email)){
			$this->db->where('email', $email);
			$query = $this->db->get('db_users')->num_rows();
			if($query>0){ return "This Email ID already exist.";}
		}
		$info = array(
		    				'username' 				=> $username, 
		    				'first_name' 				=> $new_user, 
		    				'last_name' 			=> $last_name, 
		    				'password' 				=> password_hash($pass, PASSWORD_BCRYPT), 
	    					 
		    				'mobile' 				=> $mobile,
		    				'email' 				=> $email,
		    				/*System Info*/
		    				'created_date' 			=> $CUR_DATE,
		    				'created_time' 			=> $CUR_TIME,
		    				'created_by' 			=> $CUR_USERNAME,
		    				'system_ip' 			=> $SYSTEM_IP,
		    				'system_name' 			=> $SYSTEM_NAME,
		    				'default_warehouse_id' 	=> $default_warehouse_id,
		    				'status' 				=> 1,
		    			);
		if($this->db->field_exists('approval_pin', 'db_users')){
			$info['approval_pin'] = !empty($approval_pin) ? password_hash($approval_pin, PASSWORD_BCRYPT) : null;
		}
		//print_r($info);exit;
		if(!empty($profile_picture)){
			$info['profile_picture'] = $profile_picture;
		}
		if(isset($role_id)){
			$info['role_id'] = $role_id;
		}
		$info['store_id']=(store_module()) ? $store_id : $this->session->userdata('store_id');	
		$q1 = $this->db->insert('db_users', $info);
		if (!$q1){
			return "failed";
		}
		if(warehouse_module() && isset($_POST['warehouses']) && $role_id!=1 && $role_id!=store_admin_id()){

			$warehouseSelectionFlag = false;

			$user_id = $this->db->insert_id();

			$warehouses_list = sizeof($_POST['warehouses']);
			foreach ($_POST['warehouses'] as $res => $val) {
				$warehouse_info = array ( 'user_id'=> $user_id, 'warehouse_id'=>$val );
				$q2 = $this->db->insert("db_userswarehouses",$warehouse_info);
				if (!$q2){
					return "failed";
				}

				//Varify the default warehouse should selected in all warehouse selection?
				if($default_warehouse_id == $val){
					$warehouseSelectionFlag = true;
				}


			}

			if($warehouseSelectionFlag == false){
				return "Please ensure that the Default Warehouse is selected in the group selection box";
			}
			
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! New User created Succssfully!!');
		return "success";

		

	}
	public function verify_and_update($data){
		
		// Explicit POST extraction (security: never use extract($_POST))
		$username = $this->input->post('username', TRUE);
		$new_user = $this->input->post('new_user', TRUE);
		$last_name = $this->input->post('last_name', TRUE);
		$pass = $this->input->post('pass', TRUE);
		$confirm = $this->input->post('confirm', TRUE);
		$mobile = $this->input->post('mobile', TRUE);
		$email = $this->input->post('email', TRUE);
		$role_id = $this->input->post('role_id', TRUE);
		$default_warehouse_id = $this->input->post('default_warehouse_id', TRUE);
		$warehouses = $this->input->post('warehouses');
		$approval_pin = $this->input->post('approval_pin', TRUE);
		$q_id = $this->input->post('q_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$CUR_USERID = isset($data['CUR_USERID']) ? $data['CUR_USERID'] : 0;
		$this->db->trans_begin();

		$profile_picture='';
		if(!empty($_FILES['profile_picture']['name'])){
			
			$config['upload_path']          = './uploads/users/';
	        $config['allowed_types']        = 'gif|jpg|png';
	        $config['max_size']             = 500;
	        $config['max_width']            = 500;
	        $config['max_height']           = 500;

	        $this->load->library('upload', $config);

	        if ( ! $this->upload->do_upload('profile_picture'))
	        {
	                $error = array('error' => $this->upload->display_errors());
	                print($error['error']);
	                exit();
	        }
	        else
	        {
	        	   $profile_picture='uploads/users/'.$this->upload->data('file_name');
	        }
		}


		if(!is_admin()){
			$user_store_id = $this->db->select('store_id')->where("id",$q_id)->get('db_users')->row()->store_id;
			if(empty($user_store_id)){
				echo "Something went Wrong!!";exit();
			}
			if($user_store_id!=get_current_store_id()){
				echo "Something went Wrong!!";exit();
			}
		}

		$this->db->where('UPPER(username)', strtoupper($username));
		$this->db->where('id !=', $q_id);
		$query = $this->db->get('db_users')->num_rows();
		if($query>0){ return "This username already exist.";}

		if(!empty($mobile)){
			$this->db->where('mobile', $mobile);
			$this->db->where('id !=', $q_id);
			$query = $this->db->get('db_users')->num_rows();
			if($query>0){ return "This Moble Number already exist.";}
		}
		if(!empty($email)){
			$this->db->where('email', $email);
			$this->db->where('id !=', $q_id);
			$query = $this->db->get('db_users')->num_rows();
			if($query>0){ return "This Email ID already exist.";}
		}
		
		$user_data = array(
		    				'username' 				=> $username, 
		    				'first_name' 			=> $new_user, 
		    				'last_name' 			=> $last_name, 
		    				'mobile' 				=> $mobile,
		    				'email' 				=> $email,
		    				
		    			);
		if(isset($role_id)){
			$user_data['default_warehouse_id'] = $default_warehouse_id;
			$user_data['role_id'] = $role_id;
		}
		$user_data['store_id']=(store_module()) ? $store_id : $this->session->userdata('store_id');	


		if(!empty($profile_picture)){
			$user_data['profile_picture'] = $profile_picture;
		}


		$pass = trim($pass);
		if(!empty($pass)){
			if($pass == $confirm){
				$user_data['password'] = password_hash($pass, PASSWORD_BCRYPT);
			}
			else{
				return "Password Mismatched!!";
			}
		}

		if($this->db->field_exists('approval_pin', 'db_users')){
			$approval_pin = trim($approval_pin ?? '');
			if($approval_pin !== ''){
				$user_data['approval_pin'] = password_hash($approval_pin, PASSWORD_BCRYPT);
			}
		}

		$q1 = $this->db->where('id',$q_id)->update('db_users', $user_data);
		if (!$q1){
			return "failed";
		}

		

		if(warehouse_module() && isset($_POST['warehouses']) && $role_id!=1 && $role_id!=store_admin_id()){

			$warehouseSelectionFlag = false;

			$this->db->where('user_id',$q_id)->delete("db_userswarehouses");
			$warehouses_list = sizeof($_POST['warehouses']);
			foreach ($_POST['warehouses'] as $res => $val) {
				$warehouse_info = array ( 'user_id'=> $q_id, 'warehouse_id'=>$val );
				$q2 = $this->db->insert("db_userswarehouses",$warehouse_info);
				if (!$q2){
					return "failed";
				}

				//Varify the default warehouse should selected in all warehouse selection?
				if($default_warehouse_id == $val){
					$warehouseSelectionFlag = true;
				}

			}

			if($warehouseSelectionFlag == false){
				return "Please ensure that the Default Warehouse is selected in the group selection box";
			}
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! User Updated Succssfully!!');
		return "success";
	}

	public function status_update($id,$status){
		if($id==$this->session->userdata('inv_userid')){
			echo "You Can't Diactivate Yourself!";exit();
		}
       if (set_status_of_table($id,$status,'db_users')){
            echo "success";
        }
        else{
            echo "failed";
        }
	}
	public function password_update($currentpass,$newpass,$data){
		
        $this->db->where('id', $data['CUR_USERID']);
		$query = $this->db->get('db_users');
		if($query->num_rows()==1){
			$user = $query->row();
			$password_valid = false;
			if(password_verify($currentpass, $user->password)){
				$password_valid = true;
			}
			else if($user->password === md5($currentpass)){
				$password_valid = true;
			}
			if(!$password_valid){
				return "Invalid Current Password!";
			}

			$this->db->where('id', $data['CUR_USERID']);
			$q1 = $this->db->update('db_users', ['password' => password_hash($newpass, PASSWORD_BCRYPT)]);
			if ($q1){
			        return "success";
			}
			else{
			        return "failed";
			}
		}
		else{
			return "Invalid Current Password!";
			}
	}
	//Get users deatils
	public function get_details($id){
		$data=$this->data;

		//Validate This suppliers already exist or not
		$query = $this->db->where('id', $id)->get('db_users');
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['username']=$query->username;
			$data['first_name']=$query->first_name;
			$data['last_name']=$query->last_name;
			$data['store_id']=$query->store_id;
			$data['mobile']=$query->mobile;
			$data['email']=$query->email;
			$data['role_id']=$query->role_id;
			$data['profile_picture']=$query->profile_picture;
			$data['default_warehouse_id']=$query->default_warehouse_id;
			return $data;
		}
	}

	public function delete_user($id){

		if($id==1){
			echo "Restricted! Can't Delete User Admin!!";
			exit();
		}
		if(demo_app()==true){
			echo "Restricted! Can't Delete Users in Demo mode!!";
			exit();	
		}
		if($id==$this->session->userdata('inv_userid')){
			echo "You Can't Delete Yourself!";exit();
		}
		$this->db->where('id', $id);
		$this->db->where('id !=', $this->session->userdata('inv_userid'));
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$query1=$this->db->delete("db_users");
        if ($query1){
        	$this->session->set_flashdata('success', 'Success!! User Deleted Succssfully!');
            echo "success";
        }
        else{
            echo "failed";
        }	
	}

}
