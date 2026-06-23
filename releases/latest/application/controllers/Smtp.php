<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Smtp extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	
	//Open SMS Form 
	public function index(){
		redirect(base_url('email_settings'),'refresh');
	}


	//Create Message
	public function send_message(){
		$this->permission_check('send_sms');
		$data=$this->data;
		$this->load->model('sms_model');
		$mobile = $this->input->post('mobile', TRUE);
		$message = $this->input->post('message', TRUE);
		$result= $this->sms_model->send_sms($mobile,$message);
		echo $result;
	}

	
	//Open SMS API Form 
	public function api(){
		if(!is_admin()){
			show_error("Access Denied", 403, $heading = "Unauthorized Access!!");exit();	
		}
		$this->permission_check('sms_api_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sms_api');
		$this->load->view('sms-api', $data);
	}

	//UPDATE SMS API
	public function update_smtp(){
		$this->permission_check_with_msg('smtp_settings');
		$smtp_status = $this->input->post('smtp_status', TRUE);
		$smtp_host = $this->input->post('smtp_host', TRUE);
		$smtp_port = $this->input->post('smtp_port', TRUE);
		$smtp_user = $this->input->post('smtp_user', TRUE);
		$smtp_pass = $this->input->post('smtp_pass', TRUE);

		//Update SMTP Settings
		$info['smtp_status'] = $smtp_status;
		$info['smtp_host'] = $smtp_host;
		$info['smtp_port'] = $smtp_port;
		$info['smtp_user'] = $smtp_user;
		$info['smtp_pass'] = $smtp_pass;

		$q1 = $this->db->where("id",get_current_store_id())->update("db_store",$info);
		if(!$q1){
			echo "failed";
		}
		echo "success";

	}
	public function send_SMS_by_Twilio(){
		$this->load->model('twilio_model');
		$this->twilio_model->index();
	}

	/**
	 * Test SMTP email configuration
	 */
	public function test_email(){
		$this->permission_check_with_msg('smtp_settings');

		$test_email = $this->input->post('test_email');
		if(empty($test_email)){
			echo json_encode(array('status'=>'error','message'=>'Please enter a test email address'));
			exit;
		}

		$this->load->model('email_service');
		$store_rec = get_store_details();

		$result = $this->email_service->sendTemplate(
			'smtp_test_email',
			$test_email,
			[
				'email_provider' => $store_rec->email_provider ?? 'smtp',
				'store_name' => $store_rec->store_name,
				'sent_at' => date('Y-m-d H:i:s'),
				'app_name' => 'MartPoint Retail',
			]
		);
		$success = $result['success'];
		$error = $success ? '' : $result['message'];

		if($success){
			echo json_encode(array('status'=>'success','message'=>'Test email sent successfully to '.htmlspecialchars($test_email)));
		} else {
			echo json_encode(array('status'=>'error','message'=>htmlspecialchars($error)));
		}
	}

	/**
	 * Raw connection test for diagnostics
	 */
	public function test_connection(){
		$this->permission_check_with_msg('smtp_settings');

		$store_rec = get_store_details();
		$host = $store_rec->smtp_host;
		$port = (int)$store_rec->smtp_port;

		echo "Testing connection to {$host}:{$port}...<br><br>";

		// Test 1: Plain TCP
		echo "Test 1: Plain TCP (fsockopen)...<br>";
		$fp = @fsockopen($host, $port, $errno, $errstr, 10);
		if($fp){
			stream_set_timeout($fp, 10);
			$response = fgets($fp, 512);
			echo "CONNECTED. Server says: " . htmlspecialchars($response) . "<br>";
			fclose($fp);
		} else {
			echo "FAILED: {$errno} {$errstr}<br>";
		}

		// Test 2: SSL wrapper
		echo "<br>Test 2: SSL wrapper (ssl://)...<br>";
		$fp = @fsockopen('ssl://'.$host, $port, $errno, $errstr, 10);
		if($fp){
			stream_set_timeout($fp, 10);
			$response = fgets($fp, 512);
			echo "CONNECTED. Server says: " . htmlspecialchars($response) . "<br>";
			fclose($fp);
		} else {
			echo "FAILED: {$errno} {$errstr}<br>";
		}

		// Show current settings
		echo "<br><strong>Current SMTP Settings:</strong><br>";
		echo "Host: " . htmlspecialchars($host) . "<br>";
		echo "Port: " . $port . "<br>";
		echo "User: " . htmlspecialchars($store_rec->smtp_user) . "<br>";
		echo "Status: " . ($store_rec->smtp_status ? 'Enabled' : 'Disabled') . "<br>";
	}
}

