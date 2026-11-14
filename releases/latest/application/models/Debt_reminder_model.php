<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Debt Reminder Model
 * Manages automated debt reminder settings and sending history per customer.
 */
class Debt_reminder_model extends CI_Model {

	protected $table = 'db_debt_reminder_settings';
	protected $historyTable = 'db_debt_reminder_history';

	public function __construct(){
		parent::__construct();
		$this->ensureTables();
	}

	protected function ensureTables(){
		if(!$this->db->table_exists($this->table)){
			$this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
				`id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
				`store_id` INT(11) unsigned NOT NULL DEFAULT 1,
				`customer_id` INT(11) unsigned NOT NULL,
				`enabled` TINYINT(1) NOT NULL DEFAULT 1,
				`frequency` VARCHAR(16) NOT NULL DEFAULT 'weekly',
				`max_reminders` INT(11) NOT NULL DEFAULT 0,
				`reminder_count` INT(11) NOT NULL DEFAULT 0,
				`last_reminder_sent` DATETIME DEFAULT NULL,
				`send_email` TINYINT(1) NOT NULL DEFAULT 1,
				`send_sms` TINYINT(1) NOT NULL DEFAULT 0,
				`created_at` DATETIME DEFAULT NULL,
				`updated_at` DATETIME DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `uk_customer_store` (`customer_id`, `store_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
			log_message('info', 'Created missing table: ' . $this->table);
		}
		if(!$this->db->table_exists($this->historyTable)){
			$this->db->query("CREATE TABLE IF NOT EXISTS `{$this->historyTable}` (
				`id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
				`store_id` INT(11) unsigned NOT NULL DEFAULT 1,
				`customer_id` INT(11) unsigned NOT NULL,
				`customer_name` VARCHAR(255) DEFAULT NULL,
				`amount_due` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
				`channel` VARCHAR(16) NOT NULL DEFAULT 'email',
				`status` VARCHAR(16) NOT NULL DEFAULT 'sent',
				`error_message` TEXT DEFAULT NULL,
				`sent_at` DATETIME DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `idx_customer` (`customer_id`),
				KEY `idx_sent_at` (`sent_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
			log_message('info', 'Created missing table: ' . $this->historyTable);
		}
	}

	/**
	 * Get store-wide default settings (single row per store)
	 */
	public function getStoreSettings($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$row = $this->db->where('store_id', $storeId)->where('customer_id', 0)->get($this->table)->row();
		if(!$row){
			// Seed defaults
			$this->db->insert($this->table, [
				'store_id' => $storeId,
				'customer_id' => 0,
				'enabled' => 0,
				'frequency' => 'weekly',
				'max_reminders' => 0,
				'send_email' => 1,
				'send_sms' => 0,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
			return $this->db->where('store_id', $storeId)->where('customer_id', 0)->get($this->table)->row();
		}
		return $row;
	}

	/**
	 * Update store-wide default settings
	 */
	public function updateStoreSettings($storeId, array $data){
		$data['updated_at'] = date('Y-m-d H:i:s');
		$exists = $this->db->where('store_id', $storeId)->where('customer_id', 0)->count_all_results($this->table);
		if($exists){
			return $this->db->where('store_id', $storeId)->where('customer_id', 0)->update($this->table, $data);
		}
		$data['store_id'] = $storeId;
		$data['customer_id'] = 0;
		$data['created_at'] = date('Y-m-d H:i:s');
		return $this->db->insert($this->table, $data);
	}

	/**
	 * Get customer-specific reminder settings (or inherit store defaults)
	 */
	public function getCustomerSettings($customerId, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$row = $this->db->where('store_id', $storeId)->where('customer_id', $customerId)->get($this->table)->row();
		if(!$row){
			$defaults = $this->getStoreSettings($storeId);
			return (object)[
				'id' => 0,
				'customer_id' => $customerId,
				'store_id' => $storeId,
				'enabled' => $defaults->enabled,
				'frequency' => $defaults->frequency,
				'max_reminders' => $defaults->max_reminders,
				'send_email' => $defaults->send_email,
				'send_sms' => $defaults->send_sms,
				'last_reminder_sent' => NULL,
				'reminder_count' => 0
			];
		}
		return $row;
	}

	/**
	 * Update customer-specific reminder settings
	 */
	public function updateCustomerSettings($customerId, $storeId, array $data){
		$data['updated_at'] = date('Y-m-d H:i:s');
		$exists = $this->db->where('store_id', $storeId)->where('customer_id', $customerId)->count_all_results($this->table);
		if($exists){
			return $this->db->where('store_id', $storeId)->where('customer_id', $customerId)->update($this->table, $data);
		}
		$data['store_id'] = $storeId;
		$data['customer_id'] = $customerId;
		$data['created_at'] = date('Y-m-d H:i:s');
		return $this->db->insert($this->table, $data);
	}

	/**
	 * Get all customers with debt and their reminder settings
	 */
	public function getCustomersWithDebt($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }

		$sql = "SELECT 
			c.id as customer_id,
			c.customer_name,
			c.mobile,
			c.email,
			COALESCE(SUM(s.grand_total - s.paid_amount), 0) as amount_due,
			rs.enabled,
			rs.frequency,
			rs.max_reminders,
			rs.reminder_count,
			rs.last_reminder_sent,
			rs.send_email,
			rs.send_sms
		FROM db_customers c
		LEFT JOIN db_sales s ON s.customer_id = c.id AND s.sales_status = 'Final' AND (s.grand_total - s.paid_amount) > 0 AND s.store_id = ?
		LEFT JOIN {$this->table} rs ON rs.customer_id = c.id AND rs.store_id = ?
		WHERE c.store_id = ? AND c.status = 1
		GROUP BY c.id
		HAVING amount_due > 0
		ORDER BY amount_due DESC";

		return $this->db->query($sql, [$storeId, $storeId, $storeId])->result();
	}

	/**
	 * Get customers who are due for a reminder now
	 */
	public function getCustomersDueForReminder($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$storeDefaults = $this->getStoreSettings($storeId);
		$customers = $this->getCustomersWithDebt($storeId);
		$due = [];
		foreach($customers as $c){
			// Determine effective enabled: customer override takes precedence, then store default
			$customerEnabled = $c->enabled;
			if($customerEnabled === NULL){
				$effectiveEnabled = (int)$storeDefaults->enabled;
			} else {
				$effectiveEnabled = (int)$customerEnabled;
			}
			if(!$effectiveEnabled) continue;

			// Determine effective max reminders
			$max = (int)($c->max_reminders !== NULL ? $c->max_reminders : $storeDefaults->max_reminders);
			if($max > 0 && (int)$c->reminder_count >= $max) continue;

			// Determine effective frequency and channels
			$freq = $c->frequency !== NULL ? $c->frequency : $storeDefaults->frequency;
			$c->frequency = $freq;
			$c->max_reminders = $max;
			$c->send_email = $c->send_email !== NULL ? $c->send_email : $storeDefaults->send_email;
			$c->send_sms = $c->send_sms !== NULL ? $c->send_sms : $storeDefaults->send_sms;

			// Check frequency
			$lastSent = $c->last_reminder_sent;
			if($this->isDue($lastSent, $freq)){
				$due[] = $c;
			}
		}
		return $due;
	}

	/**
	 * Check if a reminder is due based on frequency and last sent date
	 */
	protected function isDue($lastSent, $frequency){
		if(empty($lastSent)) return true;
		$last = strtotime($lastSent);
		$now = time();
		switch($frequency){
			case 'daily': return ($now - $last) >= 86400;
			case '3days': return ($now - $last) >= 259200;
			case 'weekly': return ($now - $last) >= 604800;
			case 'biweekly': return ($now - $last) >= 1209600;
			case 'monthly': return ($now - $last) >= 2592000;
			default: return ($now - $last) >= 604800;
		}
	}

	/**
	 * Mark reminder as sent for a customer
	 */
	public function markSent($customerId, $storeId, $amountDue){
		$data = [
			'last_reminder_sent' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		];
		$this->db->set('reminder_count', 'reminder_count + 1', FALSE);
		$exists = $this->db->where('store_id', $storeId)->where('customer_id', $customerId)->count_all_results($this->table);
		if($exists){
			$this->db->where('store_id', $storeId)->where('customer_id', $customerId);
			$this->db->update($this->table, $data);
		} else {
			$defaults = $this->getStoreSettings($storeId);
			$this->db->insert($this->table, [
				'store_id' => $storeId,
				'customer_id' => $customerId,
				'enabled' => $defaults->enabled,
				'frequency' => $defaults->frequency,
				'max_reminders' => $defaults->max_reminders,
				'send_email' => $defaults->send_email,
				'send_sms' => $defaults->send_sms,
				'last_reminder_sent' => date('Y-m-d H:i:s'),
				'reminder_count' => 1,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
	}

	/**
	 * Log a reminder in history
	 */
	public function logHistory($storeId, $customerId, $customerName, $amountDue, $channel, $status, $error = ''){
		return $this->db->insert($this->historyTable, [
			'store_id' => $storeId,
			'customer_id' => $customerId,
			'customer_name' => $customerName,
			'amount_due' => $amountDue,
			'channel' => $channel,
			'status' => $status,
			'error_message' => $error,
			'sent_at' => date('Y-m-d H:i:s')
		]);
	}

	/**
	 * Get reminder history (paginated)
	 */
	public function getHistory($storeId = NULL, $limit = 50, $offset = 0){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('store_id', $storeId)
			->order_by('sent_at', 'DESC')
			->limit($limit, $offset)
			->get($this->historyTable)
			->result();
	}

	/**
	 * Count total history records
	 */
	public function countHistory($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('store_id', $storeId)->count_all_results($this->historyTable);
	}
}
