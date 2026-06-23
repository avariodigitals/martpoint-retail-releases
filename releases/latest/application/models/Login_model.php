<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Author: Rapheal Ogundiran - Avario
 * Date: 2019 - 2026
 */
class Login_model extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function verify_credentials($email,$password)
	{
		// Brute-force protection
		$attempts = $this->session->userdata('login_attempts') ?: 0;
		$last_attempt = $this->session->userdata('login_last_attempt') ?: 0;
		if($attempts >= 5 && (time() - $last_attempt) < 900){
			$this->session->set_flashdata('failed', 'Too many failed attempts. Please try again after 15 minutes.');
			redirect('login');
			return;
		}

		//Filtering XSS and html escape from user inputs 
		$email=$this->security->xss_clean(html_escape($email));
		$password=$this->security->xss_clean(html_escape($password));


		$this->db->select("a.email,a.store_id,a.id,a.username,a.first_name,a.last_name,a.role_id,b.role_name,a.status,a.password");
		$this->db->from("db_users a");
		$this->db->from("db_roles b");
		$this->db->where("b.id=a.role_id");
		$this->db->group_start();
		$this->db->where('a.email', $email);
		$this->db->or_where('a.username', $email);
		$this->db->group_end();
		$query = $this->db->get();
		if($query->num_rows()==1){
			$user = $query->row();
			$password_valid = false;
			if(password_verify($password, $user->password)){
				$password_valid = true;
			}
			else if($user->password === md5($password)){
				$password_valid = true;
				// Rehash from MD5 to bcrypt
				$this->db->where('id', $user->id);
				$this->db->update('db_users', ['password' => password_hash($password, PASSWORD_BCRYPT)]);
			}
			if(!$password_valid){
				$this->session->set_flashdata('failed', 'Invalid Email or Password!');
				redirect('login');
				return;
			}

			$store_rec = get_store_details($query->row()->store_id);
			//STORE ACTIVE OR NOT
			if(!$store_rec->status){
				$this->session->set_flashdata('failed', 'Your Store Temporarily Inactive!');
				redirect('login');exit;
			}
			//USER ACTIVE OR NOT
			if(!$query->row()->status){
				$this->session->set_flashdata('failed', 'Your account is temporarily inactive!');
				redirect('login');exit;
			}
			//SUBSCRIPTION EXPIRED OR SUSPENDED
			if($this->db->table_exists('db_subscription_license')){
				$this->load->model('subscription_license_model','license');
				$sub = $this->license->get_status($query->row()->store_id);
				if($sub['status'] === 'EXPIRED' || $sub['status'] === 'SUSPENDED'){
					$this->session->set_flashdata('failed', 'Account access restricted. Please contact the administrator.');
					redirect('login');exit;
				}
			}

			$display_name = trim($query->row()->first_name . ' ' . $query->row()->last_name);
			if(empty($display_name)) $display_name = $query->row()->username;

			$logdata = array(
							'inv_username'  => $query->row()->username,
							'user_lname'  => $query->row()->last_name,
							'first_name'  => $query->row()->first_name,
							'display_name' => $display_name,
				        	 'inv_userid'  => $query->row()->id,
				        	 'logged_in' => TRUE,
				        	 'role_id' => $query->row()->role_id,
				        	 'role_name' => trim($query->row()->role_name),
				        	 'store_id' => trim($query->row()->store_id),
				        	 'email' => trim($query->row()->email),
				        	);
			$this->session->set_userdata($logdata);

			// Check subscription license activation
			$needs_activation = false;
			if($this->db->table_exists('db_subscription_license')){
				$this->load->model('subscription_license_model','license');
				$needs_activation = $this->license->needs_activation($query->row()->store_id);
			}
			if($needs_activation){
				if($query->row()->role_id == 2 || $query->row()->id == 1){
					// Admin/Owner can access activation
					$this->session->set_flashdata('warning', 'Please activate your MartPoint Retail subscription.');
					redirect(base_url().'subscription_license/activate_form');
				} else {
					// Normal users blocked
					$this->session->unset_userdata(array_keys($logdata));
					$this->session->set_flashdata('failed', 'MartPoint Retail has not been activated. Please contact your provider.');
					redirect('login');exit;
				}
			}

			$this->session->set_userdata('login_attempts', 0);
			$this->session->set_flashdata('success', 'Welcome '.ucfirst($display_name)." !");
			// Redirect cashier-role users straight to POS
			if(stripos(trim($query->row()->role_name), 'cashier') !== false){
				redirect(base_url().'pos');
			}
			redirect(base_url().'dashboard');
		}
		else{
			$attempts = ($this->session->userdata('login_attempts') ?: 0) + 1;
			$this->session->set_userdata('login_attempts', $attempts);
			$this->session->set_userdata('login_last_attempt', time());
			$this->session->set_flashdata('failed', 'Invalid Email or Password!');
			redirect('login');
		}		
	}
	public function verify_email_send_otp($email)
	{
		
		//Filtering XSS and html escape from user inputs 
		$to=$this->security->xss_clean(html_escape($email));
				
		$this->db->where('email', $email);
		$this->db->where('status', 1);
		$query = $this->db->get('db_users');

		if($query->num_rows() == 0){
			$this->session->set_flashdata('failed', 'This Email ID not Exist in Our Records!');
			return false;
		}

			$store_id = $query->row()->store_id;

			$q1 = $this->db->where('id', $store_id)->get('db_store');
			
			$otp=rand(100000,999999);

			$store_name = $q1->row()->store_name;

			$this->load->model("email_service");
			$result = $this->email_service->sendTemplate(
				'password_reset_otp',
				$to,
				[
					'user_name' => $to,
					'otp_code' => $otp,
					'otp_expiry_minutes' => '15',
					'store_name' => $store_name,
				],
				['related_module' => 'password_reset', 'related_record_id' => $to]
			);
			$response = $result['success'];

			if($response){
				$this->session->set_flashdata('success', 'OTP has been sent to your email ID! (Check Inbox/Spam Box)');
				$otpdata = array('email'  => $to,'otp'  => $otp );
				$this->session->set_userdata($otpdata);
				return true;
			}
			else{
				$this->session->set_flashdata('error', $response);
				return false;
			}
		
			
	}
	public function verify_otp($otp, $email)
	{
		//Filtering XSS and html escape from user inputs
		$otp=$this->security->xss_clean(html_escape($otp));
		$email=$this->security->xss_clean(html_escape($email));
		if($this->session->userdata('email')==$email){ redirect(base_url().'logout','refresh');	}

		$this->db->where('email', $email);
		$this->db->where('status', 1);
		$query = $this->db->get('db_users');
		if($query->num_rows()==1){

			$display_name = trim($query->row()->first_name . ' ' . $query->row()->last_name);
			if(empty($display_name)) $display_name = $query->row()->username;

			$logdata = array(
							'inv_username'  => $query->row()->username,
							'user_lname'  => $query->row()->last_name,
							'first_name'  => $query->row()->first_name,
							'display_name' => $display_name,
				        	 'inv_userid'  => $query->row()->id,
				        	 'logged_in' => TRUE,
				        	 'role_id' => $query->row()->role_id,
				        	 'role_name' => trim($query->row()->role_name),
				        	 'store_id' => trim($query->row()->store_id),
				        	);
			$this->session->set_userdata($logdata);
			return true;
		}
		else{
			return false;
		}		
	}
	public function change_password($password,$email){
			$this->db->where('email', $email);
			$this->db->where('status', 1);
			$query = $this->db->get('db_users');
			if($query->num_rows()==1){
				/*if($query->row()->username == 'admin'){
					echo "Restricted Admin Password Change";exit();
				}*/
				$password = password_hash($password, PASSWORD_BCRYPT);
				$this->db->where('email', $email);
				$q1 = $this->db->update('db_users', ['password' => $password]);
				if ($q1){

				        return true;
				}
				else{
				        return false;
				}
			}
			else{
				return false;
				}

		}
}