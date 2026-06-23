<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Controller
 * Run scheduled reports. Should be called by a server cron job.
 *
 * Example cron (every hour at minute 0):
 * 0 * * * * curl -s "https://yoursite.com/cron/run_scheduled_reports?key=YOUR_SECRET_KEY" > /dev/null 2>&1
 *
 * Or via CLI:
 * php index.php cron run_scheduled_reports YOUR_SECRET_KEY
 */
class Cron extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('report_schedule_model');
		$this->load->model('dashboard_model');
		$this->load->model('email_service');
		$this->load->model('sms_model');
		$this->load->model('debt_reminder_model');
	}

	/**
	 * Run all due scheduled reports
	 */
	public function run_scheduled_reports($cliKey = ''){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }

		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');

		if(!$isCli && $requestKey !== $secret){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}

		$schedules = $this->report_schedule_model->getDueSchedules();
		$results = [];

		foreach($schedules as $schedule){
			$result = $this->_processSchedule($schedule);
			$results[] = $result;
		}

		$response = [
			'status' => 'completed',
			'processed' => count($results),
			'results' => $results
		];

		if($isCli){
			echo "Cron completed. Processed: {$response['processed']}\n";
			foreach($results as $r){
				echo "  [{$r['report_type']}] {$r['status']}: {$r['message']}\n";
			}
		} else {
			header('Content-Type: application/json');
			echo json_encode($response);
		}
	}

	protected function _processSchedule($schedule){
		$storeId = $schedule->store_id;
		$type = $schedule->report_type;
		$date = date('Y-m-d');

		// Load store context for this schedule
		$storeRec = $this->db->where('id', $storeId)->get('db_store')->row();
		if(!$storeRec){
			return ['report_type' => $type, 'status' => 'error', 'message' => 'Store not found'];
		}

		$storeName = $storeRec->store_name;
		$result = ['report_type' => $type, 'status' => 'skipped', 'message' => ''];

		// Build report data
		$reportData = [];
		if($type === 'daily_summary'){
			$reportData = $this->dashboard_model->get_daily_summary($date);
			if(!$reportData['has_data']){
				$this->report_schedule_model->updateLastRun($schedule->id);
				return ['report_type' => $type, 'status' => 'skipped', 'message' => 'No business data for today'];
			}
		} else if($type === 'low_stock_alert'){
			$reportData = [
				'low_stock_items' => $this->dashboard_model->get_low_stock_items(),
				'has_data' => true
			];
			if(count($reportData['low_stock_items']) === 0){
				$this->report_schedule_model->updateLastRun($schedule->id);
				return ['report_type' => $type, 'status' => 'skipped', 'message' => 'No low stock items'];
			}
		} else {
			return ['report_type' => $type, 'status' => 'error', 'message' => 'Unknown report type'];
		}

		$errors = [];

		// Send Email
		if($schedule->email_enabled && !empty($schedule->email_recipients)){
			$emails = array_filter(array_map('trim', explode(',', $schedule->email_recipients)));
			foreach($emails as $email){
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
				if($type === 'daily_summary'){
					$topProducts = $this->_buildTopProductsHtml($reportData['top_products'] ?? []);
					$lowStock = $this->_buildLowStockHtml($reportData['low_stock_items'] ?? []);
					$res = $this->email_service->sendTemplate(
						$schedule->email_template_key,
						$email,
						[
							'store_name' => $storeName,
							'report_date' => show_date($date),
							'total_sales' => $this->_currency($reportData['sales']['total'] ?? 0),
							'total_profit' => ($reportData['profit']['available'] ?? false) ? $this->_currency($reportData['profit']['gross_profit']) : 'N/A',
							'total_expenses' => $this->_currency($reportData['expenses']['total'] ?? 0),
							'net_position' => $this->_currency($reportData['net_position'] ?? 0),
							'cash_expected' => $this->_currency($reportData['sales']['cash_expected'] ?? 0),
							'outstanding_debts' => $this->_currency($reportData['outstanding_debts']['total'] ?? 0),
							'top_selling_products' => $topProducts,
							'low_stock_items' => $lowStock,
							'transaction_count' => $reportData['sales']['transactions'] ?? 0,
						],
						['related_module' => $type, 'related_record_id' => $date]
					);
					if(!$res['success']){
						$errors[] = "Email to {$email}: " . $res['message'];
					}
				} else if($type === 'low_stock_alert'){
					$lowStock = $this->_buildLowStockHtml($reportData['low_stock_items'] ?? []);
					$res = $this->email_service->sendTemplate(
						$schedule->email_template_key,
						$email,
						[
							'store_name' => $storeName,
							'low_stock_items' => $lowStock,
						],
						['related_module' => $type, 'related_record_id' => $date]
					);
					if(!$res['success']){
						$errors[] = "Email to {$email}: " . $res['message'];
					}
				}
			}
		}

		// Send WhatsApp
		if($schedule->whatsapp_enabled && !empty($schedule->whatsapp_numbers)){
			$numbers = array_filter(array_map('trim', explode(',', $schedule->whatsapp_numbers)));
			foreach($numbers as $number){
				$msg = $this->_buildWhatsAppMessage($type, $storeName, $date, $reportData, $schedule->whatsapp_message_template);
				$res = $this->sms_model->send_sms($number, $msg);
				if($res !== 'success'){
					$errors[] = "WhatsApp to {$number}: {$res}";
				}
			}
		}

		// Update last run
		$this->report_schedule_model->updateLastRun($schedule->id);

		if(empty($errors)){
			$result = ['report_type' => $type, 'status' => 'success', 'message' => 'Sent successfully'];
		} else {
			$result = ['report_type' => $type, 'status' => 'partial', 'message' => implode('; ', $errors)];
		}

		return $result;
	}

	protected function _buildTopProductsHtml($products){
		if(count($products) === 0) return '<p>No top products for this date.</p>';
		$html = '<ul>';
		foreach($products as $p){
			$html .= '<li>' . htmlspecialchars($p['name']) . ' — Qty: ' . number_format($p['qty']) . ' — Revenue: ' . $this->_currency($p['revenue']) . '</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	protected function _buildLowStockHtml($items){
		if(count($items) === 0) return '<p>No low stock items.</p>';
		$html = '<ul>';
		foreach($items as $item){
			$html .= '<li>' . htmlspecialchars($item['name']) . ' — ' . number_format($item['qty']) . ' left (reorder at ' . number_format($item['min']) . ')</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	protected function _buildWhatsAppMessage($type, $storeName, $date, $reportData, $template){
		if($type === 'daily_summary'){
			$msg = "*MartPoint Daily Report*\n\n";
			$msg .= "Store: " . $storeName . "\n";
			$msg .= "Date: " . show_date($date) . "\n\n";
			$msg .= "*Sales:* " . ($reportData['sales']['total'] ? number_format($reportData['sales']['total']) : '0') . "\n";
			$msg .= "*Profit:* " . (($reportData['profit']['available'] ?? false) ? number_format($reportData['profit']['gross_profit']) : 'N/A') . "\n";
			$msg .= "*Expenses:* " . number_format($reportData['expenses']['total'] ?? 0) . "\n";
			$msg .= "*Net Position:* " . number_format($reportData['net_position'] ?? 0) . "\n\n";
			$msg .= "*Transactions:* " . ($reportData['sales']['transactions'] ?? 0) . "\n";
			if(count($reportData['top_products'] ?? []) > 0){
				$msg .= "\n*Best Seller:*\n" . $reportData['top_products'][0]['name'] . "\n";
			}
			if(count($reportData['low_stock_items'] ?? []) > 0){
				$msg .= "\n*Low Stock:*\n";
				$limit = min(5, count($reportData['low_stock_items']));
				for($i=0; $i<$limit; $i++){
					$msg .= $reportData['low_stock_items'][$i]['name'] . " - " . $reportData['low_stock_items'][$i]['qty'] . " left\n";
				}
			}
			$msg .= "\n*Outstanding Debts:* " . number_format($reportData['outstanding_debts']['total'] ?? 0) . "\n";
			$msg .= "*Cash Expected:* " . number_format($reportData['sales']['cash_expected'] ?? 0) . "\n\n";
			$msg .= "View Report:\n" . base_url('dashboard/daily_summary?date=' . $date) . "\n\n";
			$msg .= "Powered by MartPoint Retail";
			return $msg;
		}

		if($type === 'low_stock_alert'){
			$msg = "*MartPoint Low Stock Alert*\n\n";
			$msg .= "Store: " . $storeName . "\n\n";
			foreach($reportData['low_stock_items'] as $item){
				$msg .= "• " . $item['name'] . " - " . $item['qty'] . " left (reorder at " . $item['min'] . ")\n";
			}
			$msg .= "\nPlease reorder where necessary.";
			return $msg;
		}

		return '';
	}

	/**
	 * Send debt reminders to customers with outstanding balances
	 * Should be called daily via cron.
	 *
	 * Example: curl -s "https://yoursite.com/cron/send_debt_reminders?key=YOUR_SECRET_KEY"
	 */
	public function send_debt_reminders($cliKey = ''){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }

		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');

		if(!$isCli && $requestKey !== $secret){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}

		$results = [
			'status' => 'completed',
			'customers_checked' => 0,
			'emails_sent' => 0,
			'sms_sent' => 0,
			'errors' => []
		];

		// Get all stores with debt reminders enabled
		$stores = $this->db->where('customer_id', 0)->where('enabled', 1)->get('db_debt_reminder_settings')->result();
		foreach($stores as $storeSettings){
			$storeId = $storeSettings->store_id;
			$customers = $this->debt_reminder_model->getCustomersDueForReminder($storeId);
			$results['customers_checked'] += count($customers);

			foreach($customers as $customer){
				$amountDue = $customer->amount_due;
				$sendEmail = $customer->send_email;
				$sendSms = $customer->send_sms;

				// Get customer contact details
				$customerRec = $this->db->where('id', $customer->customer_id)->get('db_customers')->row();
				if(!$customerRec) continue;

				$email = $customerRec->email;
				$mobile = $customerRec->mobile;
				$customerName = $customerRec->customer_name;

				$emailOk = false;
				$smsOk = false;

				// Send Email
				if($sendEmail && !empty($email)){
					// Build placeholder data
					$invoiceRec = $this->db->select('id, sales_code, sales_date')
						->where('customer_id', $customer->customer_id)
						->where('sales_status', 'Final')
						->where('(grand_total - paid_amount) >', 0)
						->order_by('sales_date', 'DESC')
						->limit(1)
						->get('db_sales')
						->row();

					$invoiceNumber = $invoiceRec ? $invoiceRec->sales_code : 'N/A';
					$dueDate = $invoiceRec ? show_date($invoiceRec->sales_date) : 'N/A';

					$res = $this->email_service->sendTemplate(
						'debt_reminder',
						$email,
						[
							'customer_name' => $customerName,
							'store_name' => get_store_name($storeId),
							'invoice_number' => $invoiceNumber,
							'amount_due' => $this->_currency($amountDue),
							'due_date' => $dueDate,
							'payment_link' => base_url('customers')
						],
						['related_module' => 'debt_reminder', 'related_record_id' => $customer->customer_id]
					);

					if($res['success']){
						$emailOk = true;
						$results['emails_sent']++;
					} else {
						$results['errors'][] = "Email to {$email}: " . $res['message'];
					}
					$this->debt_reminder_model->logHistory(
						$storeId, $customer->customer_id, $customerName, $amountDue, 'email',
						$res['success'] ? 'sent' : 'failed',
						$res['success'] ? '' : $res['message']
					);
				}

				// Send SMS
				if($sendSms && !empty($mobile)){
					$msg = "Hi {$customerName}, this is a reminder that you have an outstanding balance of {$this->_currency($amountDue)}. Please settle at your earliest convenience. Thank you, " . get_store_name($storeId);
					$res = $this->sms_model->send_sms($mobile, $msg);
					if($res === 'success'){
						$smsOk = true;
						$results['sms_sent']++;
					} else {
						$results['errors'][] = "SMS to {$mobile}: {$res}";
					}
					$this->debt_reminder_model->logHistory(
						$storeId, $customer->customer_id, $customerName, $amountDue, 'sms',
						$smsOk ? 'sent' : 'failed',
						$smsOk ? '' : $res
					);
				}

				// Mark as sent if at least one channel succeeded
				if($emailOk || $smsOk){
					$this->debt_reminder_model->markSent($customer->customer_id, $storeId, $amountDue);
				}
			}
		}

		if($isCli){
			echo "Debt Reminder Cron completed.\n";
			echo "Customers checked: {$results['customers_checked']}\n";
			echo "Emails sent: {$results['emails_sent']}\n";
			echo "SMS sent: {$results['sms_sent']}\n";
			if(!empty($results['errors'])){
				echo "Errors:\n";
				foreach($results['errors'] as $e){
					echo "  - {$e}\n";
				}
			}
		} else {
			header('Content-Type: application/json');
			echo json_encode($results);
		}
	}

	protected function _currency($amount){
		return store_number_format($amount);
	}
}
