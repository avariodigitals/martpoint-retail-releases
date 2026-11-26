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

	/** Save overrides for one invoice; null values inherit existing defaults. */
	public function updateInvoiceSettings($invoiceId, $storeId, array $data){
		$invoice = $this->db->select('id')->where('id',(int)$invoiceId)->where('store_id',(int)$storeId)->get('db_sales')->row();
		if(!$invoice) return false;
		$allowed = array('enabled','frequency','max_reminders','send_email','send_sms');
		$values = array('updated_at'=>date('Y-m-d H:i:s'));
		foreach($allowed as $key){
			if(array_key_exists($key,$data)) $values[$key] = $data[$key];
		}
		$exists = $this->db->where('store_id',(int)$storeId)->where('invoice_id',(int)$invoiceId)->count_all_results('db_debt_reminder_invoice_state');
		if($exists){
			return $this->db->where('store_id',(int)$storeId)->where('invoice_id',(int)$invoiceId)->update('db_debt_reminder_invoice_state',$values);
		}
		$values['store_id'] = (int)$storeId;
		$values['invoice_id'] = (int)$invoiceId;
		$values['reminder_count'] = 0;
		$values['created_at'] = date('Y-m-d H:i:s');
		return $this->db->insert('db_debt_reminder_invoice_state',$values);
	}

	/**
	 * Get unpaid invoices with invoice-specific reminder state. Existing
	 * customer-level settings remain fallback defaults for older installs.
	 */
	public function getInvoicesWithDebt($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$store = $this->getStoreSettings($storeId);
		$sql = "SELECT s.id AS invoice_id, s.sales_code, s.sales_date, s.due_date,
			c.id AS customer_id, c.customer_name, c.mobile, c.email,
			GREATEST(COALESCE(s.grand_total,0)-COALESCE(s.paid_amount,0),0) AS amount_due,
			st.enabled AS invoice_enabled, st.frequency AS invoice_frequency,
			st.max_reminders AS invoice_max_reminders, st.reminder_count AS invoice_reminder_count,
			st.last_reminder_sent AS invoice_last_reminder_sent,
			st.send_email AS invoice_send_email, st.send_sms AS invoice_send_sms,
			rs.enabled AS customer_enabled, rs.frequency AS customer_frequency,
			rs.max_reminders AS customer_max_reminders, rs.reminder_count AS customer_reminder_count,
			rs.last_reminder_sent AS customer_last_reminder_sent,
			rs.send_email AS customer_send_email, rs.send_sms AS customer_send_sms
			FROM db_sales s
			JOIN db_customers c ON c.id=s.customer_id AND c.store_id=s.store_id
			LEFT JOIN {$this->table} rs ON rs.customer_id=c.id AND rs.store_id=s.store_id
			LEFT JOIN db_debt_reminder_invoice_state st ON st.invoice_id=s.id AND st.store_id=s.store_id
			WHERE s.store_id=? AND c.status=1 AND s.sales_status='Final'
			AND (COALESCE(s.grand_total,0)-COALESCE(s.paid_amount,0))>0
			ORDER BY COALESCE(s.due_date,s.sales_date) ASC, s.id ASC";
		$rows = $this->db->query($sql, array($storeId))->result();
		foreach($rows as $row){
			$row->reminder_count = (int)($row->invoice_reminder_count ?? 0);
			$row->last_reminder_sent = $row->invoice_last_reminder_sent;
			$row->enabled = $row->invoice_enabled !== null ? (int)$row->invoice_enabled : ($row->customer_enabled !== null ? (int)$row->customer_enabled : (int)$store->enabled);
			$row->frequency = $row->invoice_frequency ?: ($row->customer_frequency ?: $store->frequency);
			$row->max_reminders = $row->invoice_max_reminders !== null ? (int)$row->invoice_max_reminders : ($row->customer_max_reminders !== null ? (int)$row->customer_max_reminders : (int)$store->max_reminders);
			$row->send_email = $row->invoice_send_email !== null ? (int)$row->invoice_send_email : ($row->customer_send_email !== null ? (int)$row->customer_send_email : (int)$store->send_email);
			$row->send_sms = $row->invoice_send_sms !== null ? (int)$row->invoice_send_sms : ($row->customer_send_sms !== null ? (int)$row->customer_send_sms : (int)$store->send_sms);
		}
		return $rows;
	}

	/**
	 * Get customers who are due for a reminder now
	 */
	public function getCustomersDueForReminder($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		$invoices = $this->getInvoicesWithDebt($storeId);
		$due = [];
		foreach($invoices as $invoice){
			if(!(int)$invoice->enabled) continue;
			if((int)$invoice->max_reminders > 0 && (int)$invoice->reminder_count >= (int)$invoice->max_reminders) continue;
			if($this->isDue($invoice->last_reminder_sent, $invoice->frequency)) $due[] = $invoice;
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
	 * Mark reminder as sent for a specific invoice.
	 */
	public function markSent($invoiceId, $storeId, $amountDue = null){
		$now = date('Y-m-d H:i:s');
		$this->db->query("INSERT INTO db_debt_reminder_invoice_state (store_id,invoice_id,reminder_count,last_reminder_sent,created_at,updated_at)
			VALUES (?,?,1,?,?,?) ON DUPLICATE KEY UPDATE reminder_count=reminder_count+1,last_reminder_sent=VALUES(last_reminder_sent),updated_at=VALUES(updated_at)",
			array((int)$storeId,(int)$invoiceId,$now,$now,$now));
	}

	/**
	 * Log a reminder in history
	 */
	public function logHistory($storeId, $customerId, $customerName, $amountDue, $channel, $status, $error = '', $invoiceId = null){
		return $this->db->insert($this->historyTable, [
			'store_id' => $storeId,
			'customer_id' => $customerId,
			'invoice_id' => $invoiceId ? (int)$invoiceId : null,
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
		return $this->db->select('h.*,s.sales_code')
			->from($this->historyTable.' h')
			->join('db_sales s','s.id=h.invoice_id AND s.store_id=h.store_id','left')
			->where('h.store_id', $storeId)
			->order_by('h.sent_at', 'DESC')->limit($limit, $offset)->get()->result();
	}

	/**
	 * Count total history records
	 */
	public function countHistory($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('store_id', $storeId)->count_all_results($this->historyTable);
	}
}
