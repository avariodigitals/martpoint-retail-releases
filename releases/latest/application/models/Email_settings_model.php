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
		$this->ensureTable();
	}

	/**
	 * db_store has hit MySQL's InnoDB row-size ceiling (too many VARCHAR
	 * columns accumulated over years of feature additions), so email
	 * provider settings live in their own dedicated table instead of
	 * being bolted onto db_store.
	 */
	private function ensureTable(){
		if(!$this->db->table_exists('db_email_settings')){
			$this->db->query("CREATE TABLE IF NOT EXISTS db_email_settings (
				store_id INT NOT NULL PRIMARY KEY,
				email_provider VARCHAR(50) DEFAULT 'smtp',
				email_from_name VARCHAR(255) NULL DEFAULT NULL,
				email_from_email VARCHAR(255) NULL DEFAULT NULL,
				email_reply_to VARCHAR(255) NULL DEFAULT NULL,
				smtp_crypto VARCHAR(50) NULL DEFAULT NULL,
				resend_api_key VARCHAR(255) NULL DEFAULT NULL,
				resend_from_email VARCHAR(255) NULL DEFAULT NULL,
				resend_from_name VARCHAR(255) NULL DEFAULT NULL,
				updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		}
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

		$cfg = $this->db->where('store_id', $storeId)->get('db_email_settings')->row();

		return [
			'provider'        => !empty($cfg->email_provider) ? $cfg->email_provider : 'smtp',
			'from_name'       => !empty($cfg->email_from_name) ? $cfg->email_from_name : ($row->store_name ?? 'MartPoint Retail'),
			'from_email'      => !empty($cfg->email_from_email) ? $cfg->email_from_email : ($row->smtp_user ?? ''),
			'reply_to'        => !empty($cfg->email_reply_to) ? $cfg->email_reply_to : ($row->smtp_user ?? ''),
			'smtp_status'     => (int)($row->smtp_status ?? 0),
			'smtp_host'       => $row->smtp_host ?? '',
			'smtp_port'       => $row->smtp_port ?? '',
			'smtp_user'       => $row->smtp_user ?? '',
			'smtp_pass'       => $row->smtp_pass ?? '',
			'smtp_crypto'     => !empty($cfg->smtp_crypto) ? $cfg->smtp_crypto : '',
			'resend_api_key'  => $cfg->resend_api_key ?? '',
			'resend_from_email'=> $cfg->resend_from_email ?? '',
			'resend_from_name'=> $cfg->resend_from_name ?? '',
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
		$storeFields = ['smtp_status','smtp_host','smtp_port','smtp_user','smtp_pass'];
		$emailFields = [
			'email_provider','email_from_name','email_from_email','email_reply_to',
			'smtp_crypto','resend_api_key','resend_from_email','resend_from_name'
		];

		$storeUpdate = [];
		foreach($storeFields as $key){
			if(array_key_exists($key, $data)){
				$storeUpdate[$key] = $data[$key];
			}
		}

		$emailUpdate = [];
		foreach($emailFields as $key){
			if(array_key_exists($key, $data)){
				$emailUpdate[$key] = $data[$key];
			}
		}

		$ok = TRUE;
		if(!empty($storeUpdate)){
			$ok = $this->db->where('id', $storeId)->update('db_store', $storeUpdate);
		}

		if(!empty($emailUpdate)){
			$exists = $this->db->where('store_id', $storeId)->get('db_email_settings')->row();
			if($exists){
				$ok = $this->db->where('store_id', $storeId)->update('db_email_settings', $emailUpdate) && $ok;
			} else {
				$emailUpdate['store_id'] = $storeId;
				$ok = $this->db->insert('db_email_settings', $emailUpdate) && $ok;
			}
		}

		return $ok;
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
