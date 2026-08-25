<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Settings Model
 * Manages email provider configuration per store.
 * Settings are stored in db_store columns for backward compatibility.
 */
class Email_settings_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Get email settings for a store
	 * @param int $storeId
	 * @return array
	 */
	public function getSettings($storeId = NULL){
		if(empty($storeId)){
			$storeId = get_current_store_id();
		}

		$row = $this->db->where('id', $storeId)->get('db_store')->row();
		if(!$row){
			return $this->defaultSettings();
		}

		return [
			'provider'        => !empty($row->email_provider) ? $row->email_provider : 'smtp',
			'from_name'       => !empty($row->email_from_name) ? $row->email_from_name : ($row->store_name ?? 'MartPoint Retail'),
			'from_email'      => !empty($row->email_from_email) ? $row->email_from_email : ($row->smtp_user ?? ''),
			'reply_to'        => !empty($row->email_reply_to) ? $row->email_reply_to : ($row->smtp_user ?? ''),
			'smtp_status'     => (int)($row->smtp_status ?? 0),
			'smtp_host'       => $row->smtp_host ?? '',
			'smtp_port'       => $row->smtp_port ?? '',
			'smtp_user'       => $row->smtp_user ?? '',
			'smtp_pass'       => $row->smtp_pass ?? '',
			'smtp_crypto'     => !empty($row->smtp_crypto) ? $row->smtp_crypto : '',
			'resend_api_key'  => $row->resend_api_key ?? '',
			'resend_from_email'=> $row->resend_from_email ?? '',
			'resend_from_name'=> $row->resend_from_name ?? '',
		];
	}

	public function defaultSettings(){
		return [
			'provider'         => 'smtp',
			'from_name'        => 'MartPoint Retail',
			'from_email'       => '',
			'reply_to'         => '',
			'smtp_status'      => 0,
			'smtp_host'        => '',
			'smtp_port'        => '',
			'smtp_user'        => '',
			'smtp_pass'        => '',
			'smtp_crypto'      => '',
			'resend_api_key'   => '',
			'resend_from_email'=> '',
			'resend_from_name' => '',
		];
	}

	/**
	 * Get provider-specific config array for the adapter
	 */
	public function getProviderConfig($provider, $storeId = NULL){
		if(empty($storeId)){
			$storeId = get_current_store_id();
		}
		$settings = $this->getSettings($storeId);

		if($provider === 'smtp'){
			return [
				'smtp_host'   => $settings['smtp_host'],
				'smtp_port'   => $settings['smtp_port'],
				'smtp_user'   => $settings['smtp_user'],
				'smtp_pass'   => $settings['smtp_pass'],
				'smtp_crypto' => $settings['smtp_crypto'],
				'from_name'   => $settings['from_name'],
				'from_email'  => $settings['from_email'],
			];
		}

		if($provider === 'resend'){
			return [
				'resend_api_key'    => $settings['resend_api_key'],
				'resend_from_email' => $settings['resend_from_email'] ?: $settings['from_email'],
				'resend_from_name'  => $settings['resend_from_name'] ?: $settings['from_name'],
			];
		}

		return [];
	}

	/**
	 * Save email settings
	 * @param int $storeId
	 * @param array $data
	 * @return bool
	 */
	public function saveSettings($storeId, array $data){
		$allowed = [
			'email_provider','email_from_name','email_from_email','email_reply_to',
			'smtp_status','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_crypto',
			'resend_api_key','resend_from_email','resend_from_name'
		];

		$update = [];
		foreach($allowed as $key){
			if(array_key_exists($key, $data)){
				$update[$key] = $data[$key];
			}
		}

		if(empty($update)){
			return FALSE;
		}

		return $this->db->where('id', $storeId)->update('db_store', $update);
	}

	/**
	 * Check if email provider is configured and ready
	 */
	public function isReady($storeId = NULL){
		$settings = $this->getSettings($storeId);
		if($settings['provider'] === 'smtp'){
			return !empty($settings['smtp_host']) && !empty($settings['smtp_user']) && !empty($settings['smtp_pass']);
		}
		if($settings['provider'] === 'resend'){
			return !empty($settings['resend_api_key']) && !empty($settings['resend_from_email']);
		}
		return FALSE;
	}
}
