<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sendchamp SMS Provider
 */
class Sendchamp_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->model('storefront_model');
	}

	public function index($to, $message, $storeId = null){
		$storeId = $storeId ?: (function_exists('get_current_store_id') ? get_current_store_id() : 0);
		if(!$storeId) return 'API Not Available';

		$creds = $this->storefront_model->getSendchampCredentials($storeId);
		if(!$creds || empty($creds->api_key)){
			return 'API Not Available';
		}

		$phone = preg_replace('/[^0-9]/', '', $to);
		if(empty($phone)) return 'failed';

		$apiKey = $creds->api_key;
		$senderId = !empty($creds->sender_id) ? $creds->sender_id : 'MartPoint';
		$route = !empty($creds->route) ? $creds->route : 'non_dnd_nigeria';

		$payload = [
			'to' => [$phone],
			'message' => $message,
			'sender_name' => $senderId,
			'route' => $route
		];

		$ch = curl_init('https://api.sendchamp.com/api/v1/sms/send');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $apiKey,
			'Accept: application/json',
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_TIMEOUT, 80);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if(curl_error($ch)){
			curl_close($ch);
			return 'failed';
		}
		curl_close($ch);

		if($httpCode == 200 && !empty($response)){
			$json = json_decode($response, true);
			$status = $json['status'] ?? null;
			if($status === 200 || (is_string($status) && strtolower($status) === 'success')){
				return 'success';
			}
		}
		return 'failed';
	}
}
