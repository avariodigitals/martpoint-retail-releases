<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Report Schedule Model
 * Manages automated report delivery schedules per store.
 */
class Report_schedule_model extends CI_Model {

	protected $table = 'db_report_schedules';

	public function __construct(){
		parent::__construct();
		$this->ensureTable();
	}

	protected function ensureTable(){
		if($this->db->table_exists($this->table)){ return; }
		$this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`store_id` int(11) unsigned NOT NULL DEFAULT 1,
			`report_type` varchar(64) NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
			`template_name` varchar(128) DEFAULT NULL,
			`frequency` varchar(16) NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
			`send_time` varchar(8) NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
			`email_enabled` tinyint(1) NOT NULL DEFAULT 1,
			`email_recipients` varchar(500) DEFAULT NULL COMMENT 'comma-separated emails',
			`email_template_key` varchar(64) DEFAULT 'daily_business_summary',
			`whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
			`whatsapp_numbers` varchar(500) DEFAULT NULL COMMENT 'comma-separated with country code',
			`whatsapp_message_template` text DEFAULT NULL,
			`last_run_at` datetime DEFAULT NULL,
			`status` tinyint(1) NOT NULL DEFAULT 1,
			`created_at` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uk_report_type_store` (`report_type`,`store_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	public function getByType($type, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('report_type', $type)->where('store_id', $storeId)->get($this->table)->row();
	}

	public function getAll($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('store_id', $storeId)->order_by('report_type', 'ASC')->get($this->table)->result();
	}

	public function getDueSchedules(){
		$now = date('H:i');
		$today = date('Y-m-d');
		$this->db->where('status', 1);
		$this->db->where("(last_run_at IS NULL OR DATE(last_run_at) < '{$today}')", NULL, FALSE);
		$this->db->where("send_time <=", $now);
		return $this->db->get($this->table)->result();
	}

	public function create(array $data){
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}

	public function update($id, array $data){
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', $id)->update($this->table, $data);
	}

	public function updateLastRun($id){
		return $this->db->where('id', $id)->update($this->table, ['last_run_at' => date('Y-m-d H:i:s')]);
	}

	public function delete($id, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete($this->table);
	}

	public function getById($id, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('id', $id)->where('store_id', $storeId)->get($this->table)->row();
	}

	public function seedDefaults($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }

		$defaults = [
			[
				'report_type' => 'daily_summary',
				'template_name' => 'Daily Business Summary',
				'frequency' => 'daily',
				'send_time' => '18:00',
				'email_enabled' => 1,
				'email_recipients' => '',
				'email_template_key' => 'daily_business_summary',
				'whatsapp_enabled' => 0,
				'whatsapp_numbers' => '',
				'whatsapp_message_template' => "*MartPoint Daily Report*\n\nStore: {store_name}\nDate: {report_date}\n\n*Sales:* {total_sales}\n*Profit:* {total_profit}\n*Expenses:* {total_expenses}\n*Net Position:* {net_position}\n\nView Report:\n{report_link}",
				'status' => 0
			],
			[
				'report_type' => 'low_stock_alert',
				'template_name' => 'Low Stock Alert',
				'frequency' => 'daily',
				'send_time' => '09:00',
				'email_enabled' => 1,
				'email_recipients' => '',
				'email_template_key' => 'low_stock_alert',
				'whatsapp_enabled' => 0,
				'whatsapp_numbers' => '',
				'whatsapp_message_template' => "*MartPoint Low Stock Alert*\n\nStore: {store_name}\n\n{low_stock_items}\n\nPlease reorder where necessary.",
				'status' => 0
			],
		];

		$created = 0;
		foreach($defaults as $tpl){
			$exists = $this->db->where('store_id', $storeId)->where('report_type', $tpl['report_type'])->count_all_results($this->table);
			if($exists == 0){
				$tpl['store_id'] = $storeId;
				$this->create($tpl);
				$created++;
			}
		}
		return $created;
	}
}
