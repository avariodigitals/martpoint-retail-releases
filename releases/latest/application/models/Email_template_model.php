<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Template Model
 * CRUD for email templates per store.
 */
class Email_template_model extends CI_Model {

	protected $table = 'db_email_templates';

	public function __construct(){
		parent::__construct();
		$this->ensureTable();
	}

	protected function ensureTable(){
		if($this->db->table_exists($this->table)){
			return;
		}
		$this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`store_id` int(11) unsigned NOT NULL DEFAULT 1,
			`template_key` varchar(64) NOT NULL,
			`template_name` varchar(128) NOT NULL,
			`subject` varchar(255) NOT NULL DEFAULT '',
			`html_body` text,
			`text_body` text,
			`status` tinyint(1) NOT NULL DEFAULT 1,
			`send_copy_to_owner` tinyint(1) NOT NULL DEFAULT 0,
			`created_at` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uk_template_key_store` (`template_key`,`store_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}

	public function getByKey($key, $storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('template_key', $key)->where('store_id', $storeId)->get($this->table)->row();
	}

	public function getAll($storeId = NULL){
		if(empty($storeId)){ $storeId = get_current_store_id(); }
		return $this->db->where('store_id', $storeId)->order_by('template_name', 'ASC')->get($this->table)->result();
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
			['template_key'=>'invoice_sent','template_name'=>'Invoice Sent','subject'=>'Invoice {invoice_number} from {store_name}','html_body'=>"<p>Hello {customer_name},</p><p>Your invoice {invoice_number} from {store_name} is ready.</p><p>Invoice Total: {invoice_total}<br>Amount Paid: {amount_paid}<br>Amount Due: {amount_due}</p><p>View Invoice:<br><a href='{invoice_link}'>{invoice_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nYour invoice {invoice_number} from {store_name} is ready.\n\nInvoice Total: {invoice_total}\nAmount Paid: {amount_paid}\nAmount Due: {amount_due}\n\nView Invoice:\n{invoice_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'payment_link_sent','template_name'=>'Payment Link Sent','subject'=>'Payment Link for Invoice {invoice_number}','html_body'=>"<p>Hello {customer_name},</p><p>You have an outstanding balance of {amount_due}.</p><p>Invoice Number: {invoice_number}</p><p>Please make payment securely using the link below:</p><p><a href='{payment_link}'>{payment_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nYou have an outstanding balance of {amount_due}.\n\nInvoice Number: {invoice_number}\n\nPlease make payment securely using the link below:\n{payment_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'payment_received','template_name'=>'Payment Received','subject'=>'Payment Received — {store_name}','html_body'=>"<p>Hello {customer_name},</p><p>Your payment for invoice {invoice_number} has been received.</p><p>Amount Paid: {amount_paid}<br>Outstanding Balance: {amount_due}</p><p>Payment Reference: {payment_reference}</p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nYour payment for invoice {invoice_number} has been received.\n\nAmount Paid: {amount_paid}\nOutstanding Balance: {amount_due}\nPayment Reference: {payment_reference}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'daily_business_summary','template_name'=>'Daily Business Summary','subject'=>'Daily Business Summary — {store_name} — {report_date}','html_body'=>"<h2>MartPoint Daily Business Summary</h2><p><strong>Store:</strong> {store_name}<br><strong>Date:</strong> {report_date}</p><hr><p><strong>Total Sales:</strong> {total_sales}<br><strong>Profit:</strong> {total_profit}<br><strong>Expenses:</strong> {total_expenses}<br><strong>Net Position:</strong> {net_position}<br><strong>Transactions:</strong> {transaction_count}<br><strong>Cash Expected:</strong> {cash_expected}<br><strong>Outstanding Debts:</strong> {outstanding_debts}</p><p><strong>Attendance:</strong> {attendance_summary}</p><p><strong>Top Selling Products:</strong><br>{top_selling_products}</p><p><strong>Low Stock Items:</strong><br>{low_stock_items}</p><hr><p style='color:#888;font-size:12px;'>Generated by {app_name}.</p>",'text_body'=>"MartPoint Daily Business Summary\n\nStore: {store_name}\nDate: {report_date}\n\nTotal Sales: {total_sales}\nProfit: {total_profit}\nExpenses: {total_expenses}\nNet Position: {net_position}\nTransactions: {transaction_count}\nCash Expected: {cash_expected}\nOutstanding Debts: {outstanding_debts}\nAttendance: {attendance_summary}\n\nGenerated by {app_name}.",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'low_stock_alert','template_name'=>'Low Stock Alert','subject'=>'Low Stock Alert — {store_name}','html_body'=>"<p>Hello,</p><p>The following products are running low:</p><p>{low_stock_items}</p><p>Please review inventory and reorder where necessary.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello,\n\nThe following products are running low:\n{low_stock_items}\n\nPlease review inventory and reorder where necessary.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'debt_reminder','template_name'=>'Debt Reminder','subject'=>'Payment Reminder — Invoice {invoice_number}','html_body'=>"<p>Hello {customer_name},</p><p>This is a friendly reminder that your outstanding balance of {amount_due} is still pending.</p><p>Invoice: {invoice_number}<br>Due Date: {due_date}</p><p>Payment Link:<br><a href='{payment_link}'>{payment_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nThis is a friendly reminder that your outstanding balance of {amount_due} is still pending.\n\nInvoice: {invoice_number}\nDue Date: {due_date}\n\nPayment Link:\n{payment_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'staff_invite','template_name'=>'Staff Invite','subject'=>'Your MartPoint Retail Account','html_body'=>"<p>Hello {staff_name},</p><p>An account has been created for you on MartPoint Retail.</p><p><strong>Store:</strong> {store_name}<br><strong>Role:</strong> {staff_role}</p><p>Login here:<br><a href='{login_link}'>{login_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {staff_name},\n\nAn account has been created for you on MartPoint Retail.\n\nStore: {store_name}\nRole: {staff_role}\n\nLogin here:\n{login_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'password_reset','template_name'=>'Password Reset','subject'=>'Reset Your MartPoint Password','html_body'=>"<p>Hello {user_name},</p><p>Use the link below to reset your password:</p><p><a href='{reset_link}'>{reset_link}</a></p><p>If you did not request this, please ignore this email.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nUse the link below to reset your password:\n{reset_link}\n\nIf you did not request this, please ignore this email.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'receipt_sent','template_name'=>'Receipt Sent','subject'=>'Receipt {receipt_number} from {store_name}','html_body'=>"<p>Hello {customer_name},</p><p>Thank you for shopping with {store_name}. Here is your receipt.</p><p><strong>Receipt Number:</strong> {receipt_number}<br><strong>Total:</strong> {invoice_total}<br><strong>Amount Paid:</strong> {amount_paid}<br><strong>Payment Method:</strong> {payment_method}</p><p>View receipt:<br><a href='{receipt_link}'>{receipt_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nThank you for shopping with {store_name}. Here is your receipt.\n\nReceipt Number: {receipt_number}\nTotal: {invoice_total}\nAmount Paid: {amount_paid}\nPayment Method: {payment_method}\n\nView receipt:\n{receipt_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'smtp_test_email','template_name'=>'SMTP Test Email','subject'=>'MartPoint Email Test — {store_name}','html_body'=>"<p>Hello,</p><p>This is a test email from {app_name}.</p><p><strong>Provider:</strong> {email_provider}<br><strong>Store:</strong> {store_name}<br><strong>Sent At:</strong> {sent_at}</p><p>If you received this email, your email configuration is working.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello,\n\nThis is a test email from {app_name}.\n\nProvider: {email_provider}\nStore: {store_name}\nSent At: {sent_at}\n\nIf you received this email, your email configuration is working.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'password_reset_otp','template_name'=>'Password Reset OTP','subject'=>'Your MartPoint Password Reset OTP','html_body'=>"<p>Hello {user_name},</p><p>Your password reset OTP is:</p><h3>{otp_code}</h3><p>This code will expire in {otp_expiry_minutes} minutes.</p><p>If you did not request this, please ignore this email.</p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {user_name},\n\nYour password reset OTP is:\n\n{otp_code}\n\nThis code will expire in {otp_expiry_minutes} minutes.\n\nIf you did not request this, please ignore this email.\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'supplier_purchase_order','template_name'=>'Supplier Purchase Order','subject'=>'Purchase Order {purchase_order_number} from {store_name}','html_body'=>"<p>Hello {supplier_name},</p><p>Please find the purchase order from {store_name}.</p><p><strong>Purchase Order:</strong> {purchase_order_number}<br><strong>Order Total:</strong> {purchase_order_total}<br><strong>Expected Delivery:</strong> {expected_delivery_date}</p><p><strong>Items:</strong><br>{purchase_order_items}</p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {supplier_name},\n\nPlease find the purchase order from {store_name}.\n\nPurchase Order: {purchase_order_number}\nOrder Total: {purchase_order_total}\nExpected Delivery: {expected_delivery_date}\n\nItems:\n{purchase_order_items}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'customer_statement','template_name'=>'Customer Statement','subject'=>'Customer Statement — {store_name}','html_body'=>"<p>Hello {customer_name},</p><p>Here is your account statement from {store_name}.</p><p><strong>Total Purchases:</strong> {total_purchases}<br><strong>Total Paid:</strong> {total_paid}<br><strong>Outstanding Balance:</strong> {outstanding_balance}</p><p><strong>Statement Period:</strong> {statement_period}</p><p>View Statement:<br><a href='{statement_link}'>{statement_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nHere is your account statement from {store_name}.\n\nTotal Purchases: {total_purchases}\nTotal Paid: {total_paid}\nOutstanding Balance: {outstanding_balance}\n\nStatement Period: {statement_period}\n\nView Statement:\n{statement_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'overdue_debt_notice','template_name'=>'Overdue Debt Notice','subject'=>'Overdue Payment Notice — {store_name}','html_body'=>"<p>Hello {customer_name},</p><p>This is a notice that your outstanding balance of {amount_due} is now overdue.</p><p><strong>Invoice:</strong> {invoice_number}<br><strong>Due Date:</strong> {due_date}</p><p>Please make payment using the link below:</p><p><a href='{payment_link}'>{payment_link}</a></p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nThis is a notice that your outstanding balance of {amount_due} is now overdue.\n\nInvoice: {invoice_number}\nDue Date: {due_date}\n\nPlease make payment using the link below:\n{payment_link}\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'end_of_day_report_ready','template_name'=>'End of Day Report Ready','subject'=>'Your End-of-Day Report is Ready — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your end-of-day business report for {report_date} is ready.</p><p><strong>Total Sales:</strong> {total_sales}<br><strong>Expenses:</strong> {total_expenses}<br><strong>Net Position:</strong> {net_position}</p><p>View Report:<br><a href='{report_link}'>{report_link}</a></p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour end-of-day business report for {report_date} is ready.\n\nTotal Sales: {total_sales}\nExpenses: {total_expenses}\nNet Position: {net_position}\n\nView Report:\n{report_link}\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'payment_failed','template_name'=>'Payment Failed','subject'=>'Payment Failed — Invoice {invoice_number}','html_body'=>"<p>Hello {customer_name},</p><p>Your payment attempt for invoice {invoice_number} was not successful.</p><p><strong>Amount:</strong> {amount_due}</p><p>You can try again using the link below:</p><p><a href='{payment_link}'>{payment_link}</a></p><p>If you already made payment, please contact {store_name}.</p><p>Thank you,<br>{store_name}</p>",'text_body'=>"Hello {customer_name},\n\nYour payment attempt for invoice {invoice_number} was not successful.\n\nAmount: {amount_due}\n\nYou can try again using the link below:\n{payment_link}\n\nIf you already made payment, please contact {store_name}.\n\nThank you,\n{store_name}",'status'=>1,'send_copy_to_owner'=>0],
			['template_key'=>'subscription_renewal_reminder','template_name'=>'Subscription Renewal Reminder','subject'=>'MartPoint Subscription Renewal Reminder — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your MartPoint Retail subscription for {store_name} will expire on {subscription_expiry_date}.</p><p><strong>Days Left:</strong> {subscription_days_left}<br><strong>Plan:</strong> {subscription_plan}<br><strong>Renewal Amount:</strong> {renewal_amount}</p><p>Please contact your MartPoint provider to renew your subscription.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour MartPoint Retail subscription for {store_name} will expire on {subscription_expiry_date}.\n\nDays Left: {subscription_days_left}\nPlan: {subscription_plan}\nRenewal Amount: {renewal_amount}\n\nPlease contact your MartPoint provider to renew your subscription.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'subscription_expired','template_name'=>'Subscription Expired','subject'=>'MartPoint Subscription Expired — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your MartPoint Retail subscription for {store_name} expired on {subscription_expiry_date}.</p><p>Some features may be limited until renewal is completed.</p><p>Please contact your MartPoint provider to renew your subscription.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour MartPoint Retail subscription for {store_name} expired on {subscription_expiry_date}.\n\nSome features may be limited until renewal is completed.\n\nPlease contact your MartPoint provider to renew your subscription.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'subscription_activated','template_name'=>'Subscription Activated','subject'=>'MartPoint Subscription Activated — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your MartPoint Retail subscription has been activated.</p><p><strong>Store:</strong> {store_name}<br><strong>Plan:</strong> {subscription_plan}<br><strong>Start Date:</strong> {subscription_start_date}<br><strong>Expiry Date:</strong> {subscription_expiry_date}<br><strong>Days Active:</strong> {subscription_duration}</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour MartPoint Retail subscription has been activated.\n\nStore: {store_name}\nPlan: {subscription_plan}\nStart Date: {subscription_start_date}\nExpiry Date: {subscription_expiry_date}\nDays Active: {subscription_duration}\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'subscription_renewed','template_name'=>'Subscription Renewed','subject'=>'MartPoint Subscription Renewed — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your MartPoint Retail subscription has been renewed successfully.</p><p><strong>Plan:</strong> {subscription_plan}<br><strong>New Start Date:</strong> {subscription_start_date}<br><strong>New Expiry Date:</strong> {subscription_expiry_date}<br><strong>Days Left:</strong> {subscription_days_left}</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour MartPoint Retail subscription has been renewed successfully.\n\nPlan: {subscription_plan}\nNew Start Date: {subscription_start_date}\nNew Expiry Date: {subscription_expiry_date}\nDays Left: {subscription_days_left}\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
			['template_key'=>'subscription_suspended','template_name'=>'Subscription Suspended','subject'=>'MartPoint Subscription Suspended — {store_name}','html_body'=>"<p>Hello {user_name},</p><p>Your MartPoint Retail subscription for {store_name} has been suspended.</p><p><strong>Reason:</strong> {subscription_suspension_reason}</p><p>Please contact your MartPoint provider for support.</p><p>Thank you,<br>{app_name}</p>",'text_body'=>"Hello {user_name},\n\nYour MartPoint Retail subscription for {store_name} has been suspended.\n\nReason: {subscription_suspension_reason}\n\nPlease contact your MartPoint provider for support.\n\nThank you,\n{app_name}",'status'=>1,'send_copy_to_owner'=>1],
		];

		$created = 0;
		foreach($defaults as $tpl){
			$exists = $this->db->where('store_id', $storeId)->where('template_key', $tpl['template_key'])->count_all_results($this->table);
			if($exists == 0){
				$tpl['store_id'] = $storeId;
				$this->create($tpl);
				$created++;
			}
		}
		return $created;
	}
}
