<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	
	//Open SMS Form 
	public function index(){
		$this->permission_check('send_sms');
		$data=$this->data;
		$data['page_title']=$this->lang->line('send_sms');
		$data['content'] = $this->load->view('sms', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		
		$this->permission_check('sms_settings');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sms_api');
		$data['content'] = $this->load->view('sms-api', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	//UPDATE SMS API
	public function api_update(){
		$this->permission_check_with_msg('sms_settings');
		$this->load->model('sms_model');
    	echo $this->sms_model->api_update();
	}

	/**
	 * Validate BulkSMSNigeria credentials without spending credit.
	 * Tests the POSTed values first (so an unsaved token can be checked),
	 * falling back to the stored row.
	 */
	public function test_bulksmsng_connection(){
		$this->permission_check_with_msg('sms_settings');
		if(!$this->permissions('sms_settings')){
			echo json_encode(['status'=>false,'message'=>'Access denied']);
			return;
		}
		$this->load->model('bulksmsng_model');
		$store_id = get_current_store_id();

		// Allow testing unsaved input by writing it to a throwaway row overlay.
		$token = trim((string)$this->input->post('api_token', TRUE));
		$base  = trim((string)$this->input->post('base_url', TRUE));
		if($token !== ''){
			$creds = (object)[
				'api_token' => $token,
				'sender_id' => 'BulkSMS',
				'base_url'  => $base !== '' ? $base : 'https://www.bulksmsnigeria.com/api',
				'gateway'   => null,
			];
			$res = $this->_probe_with_credentials($creds, $base);
		} else {
			$res = $this->bulksmsng_model->test_connection($store_id);
		}
		echo json_encode(['status' => !empty($res['ok']), 'message' => $res['message'] ?? 'Connection failed']);
	}

	/** One-shot balance probe against explicit credentials. */
	private function _probe_with_credentials($creds, $base){
		$url = rtrim($base !== '' ? $base : 'https://www.bulksmsnigeria.com/api', '/') . '/wallet/balance';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $creds->api_token,
			'Accept: application/json',
		]);
		$raw  = curl_exec($ch);
		$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$cerr = curl_error($ch);
		curl_close($ch);
		if($cerr !== ''){ return ['ok'=>false,'message'=>'Connection error: ' . $cerr]; }
		$json = json_decode((string)$raw, true);
		if($http >= 200 && $http < 300 && is_array($json) && strtolower((string)($json['status'] ?? '')) === 'success'){
			$data = $json['data'] ?? $json;
			$bal  = is_array($data) ? ($data['balance'] ?? $data['current_balance'] ?? null) : null;
			return [
				'ok' => true,
				'message' => $bal !== null
					? 'Connected. Wallet balance: ' . number_format((float)$bal, 2)
					: 'Connected successfully.',
			];
		}
		$err = is_array($json) ? ($json['message'] ?? null) : null;
		if(!$err){ $err = 'HTTP ' . $http . ' — check the API base URL and token'; }
		return ['ok'=>false,'message'=>$err];
	}
	public function send_SMS_by_Twilio(){
		$this->load->model('twilio_model');
		$this->twilio_model->index();
	}
}

