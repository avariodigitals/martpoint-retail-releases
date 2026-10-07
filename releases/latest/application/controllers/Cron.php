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

	/**
	 * Automated abandoned-cart recovery. Runs per store that enabled
	 * auto_recovery in Online Store settings. Sends through the store's
	 * configured email/SMS provider only — with no provider configured or
	 * test mode on, nothing reaches a real customer and every attempt is
	 * still audited in db_storefront_cart_reminders.
	 *
	 * GET /cron/cart_recovery?key=<cron_secret_key>  (or CLI)
	 */
	public function cart_recovery($cliKey = ''){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && $requestKey !== $secret){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		$this->load->model('storefront_model');
		if(!$this->db->table_exists('db_storefront_carts') || !$this->db->field_exists('auto_recovery_enabled', 'db_storefront_settings')){
			$this->_cartRecoveryOut($isCli, ['status'=>'skipped','message'=>'feature tables not installed']);
			return;
		}
		$stores = $this->db->where('auto_recovery_enabled', 1)->get('db_storefront_settings')->result();
		$results = [];
		foreach($stores as $st){
			$storeId = (int)$st->store_id;
			$testMode = (int)($st->auto_recovery_test ?? 1) === 1;
			$channel = ($st->auto_recovery_channel === 'sms') ? 'sms' : 'email';
			$hours = max(1, (int)($st->abandoned_after_hours ?? 24));
			$store = $this->db->where('id', $storeId)->get('db_store')->row();
			$testRecipient = $store->email ?? null;

			foreach($this->storefront_model->getAbandonedCartsForRecovery($hours, 500) as $cart){
				if((int)$cart->store_id !== $storeId) continue;
				// Recheck eligibility at send time — ordered/opted-out/
				// recently-nudged carts are skipped even if just listed.
				if(!$this->storefront_model->cartReminderEligible($cart, $hours)) continue;

				// Atomically claim the reminder slot first so concurrent
				// cron runs / manual nudges can never double-send.
				$claimed = $this->storefront_model->claimCartReminder(
					$cart->id, $storeId, $channel, 'auto',
					$testMode ? $testRecipient : ($channel === 'sms' ? $cart->customer_phone : $cart->customer_email),
					$testMode ? 'test-mode send' : 'auto recovery send', 'cron');
				if(!$claimed) continue;

				$restoreUrl = base_url('store/' . $st->store_slug . '/cart?cart=' . $cart->cart_token);
				$body = $this->_cartRecoveryMessage($store->store_name ?? 'our store', $cart, $restoreUrl);
				$sent = false; $note = '';

				if($testMode){
					$note = 'test mode — redirected to store owner';
				}
				if($channel === 'email'){
					$to = $testMode ? $testRecipient : $cart->customer_email;
					if(empty($to)){ $note = trim($note . ' no recipient'); }
					else {
						$this->email_service->setStoreId($storeId);
						$res = $this->email_service->sendRaw($to, 'You left items in your cart', $body['html'], $body['text'], ['template_key' => 'cart_recovery', 'related_module' => 'storefront_cart', 'related_record_id' => $cart->id]);
						$sent = !empty($res['success']);
						$note = trim($note . ' ' . ($sent ? 'sent' : 'send failed: ' . $res['message']));
					}
				} else {
					$to = $testMode ? ($store->phone ?? $testRecipient) : $cart->customer_phone;
					if(empty($to)){ $note = trim($note . ' no recipient'); }
					else {
						$resp = $this->sms_model->send_sms($to, $body['text']);
						$sent = is_string($resp) ? (stripos($resp, 'success') !== false || stripos($resp, 'sent') !== false) : (bool)$resp;
						$note = trim($note . ' ' . ($sent ? 'sent' : 'send failed'));
					}
				}
				if(!$sent){ $this->storefront_model->recordCartSendAttempt($cart->id, $storeId); }
				$results[] = ['cart' => (int)$cart->id, 'store' => $storeId, 'channel' => $channel, 'sent' => $sent, 'note' => $note];
			}
		}
		$this->_cartRecoveryOut($isCli, ['status'=>'completed','processed'=>count($results),'results'=>$results]);
	}

	/**
	 * Back-in-stock notifier. Finds subscribed alerts whose item now has
	 * stock and emails each subscriber once (idempotent via notified flag).
	 * GET /cron/back_in_stock?key=<cron_secret_key>  (or CLI)
	 */
	public function back_in_stock($cliKey = ''){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && $requestKey !== $secret){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		$this->load->model('marketing_model', 'marketing_m');
		if(!$this->db->table_exists('db_stock_alerts')){
			$this->_cartRecoveryOut($isCli, ['status'=>'skipped','message'=>'stock_alerts table not installed']);
			return;
		}
		// Recover alerts stranded in the claimed state (notified=2) by a
		// crashed run — last_attempt_at is stamped at claim time, so a claim
		// older than 30 minutes is released back to pending. send_attempts
		// still caps total retries at 5.
		$this->db->where('notified', 2)
			->where('(last_attempt_at IS NULL OR last_attempt_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE))', null, false)
			->update('db_stock_alerts', ['notified' => 0]);
		$sent = 0; $failed = 0;
		foreach($this->marketing_m->getRestockableAlerts(200) as $alert){
			// Claim first — a second cron run can never re-notify.
			$this->db->where('id', $alert->id)->where('notified', 0)
				->update('db_stock_alerts', ['notified' => 2, 'last_attempt_at' => date('Y-m-d H:i:s')]);
			if($this->db->affected_rows() !== 1) continue;
			$store = $this->db->where('id', $alert->store_id)->get('db_store')->row();
			$settings = $this->db->where('store_id', $alert->store_id)->get('db_storefront_settings')->row();
			$itemUrl = base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $alert->item_id);
			$unsub = base_url('storefront/stock_unsubscribe/' . $alert->token);
			$name = $alert->live_name ?: $alert->item_name;
			$ok = false; $err = 'no provider';
			if(!empty($alert->email)){
				$this->email_service->setStoreId($alert->store_id);
				$res = $this->email_service->sendRaw($alert->email,
					$name . ' is back in stock at ' . ($store->store_name ?? 'our store'),
					'<p>Good news — <b>' . htmlspecialchars($name) . '</b> is back in stock.</p>'
					. '<p><a href="' . htmlspecialchars($itemUrl) . '">View it here</a></p>'
					. '<p style="color:#888;font-size:12px;"><a href="' . htmlspecialchars($unsub) . '">Stop these alerts</a></p>',
					$name . " is back in stock — view: {$itemUrl}\nStop alerts: {$unsub}",
					['template_key' => 'back_in_stock', 'related_module' => 'stock_alert', 'related_record_id' => $alert->id]);
				$ok = !empty($res['success']);
				$err = $ok ? null : ($res['message'] ?? 'send failed');
			}
			// Settle the claim: notified on success; on failure increment
			// send_attempts — the resolver drops alerts after 5 attempts.
			$this->db->where('id', $alert->id)->update('db_stock_alerts', [
				'notified' => $ok ? 1 : 0,
				'notified_at' => $ok ? date('Y-m-d H:i:s') : null,
				'send_attempts' => $ok ? $alert->send_attempts : $alert->send_attempts + 1,
				'last_attempt_at' => date('Y-m-d H:i:s'),
			]);
			$ok ? $sent++ : $failed++;
		}
		$this->_cartRecoveryOut($isCli, ['status'=>'completed','sent'=>$sent,'failed'=>$failed]);
	}

	/**
	 * Background campaign dispatch sweep. Resumes campaigns left
	 * 'partial'/'sending' (batch limit hit, interruption, or rate limit)
	 * and processes up to 60 queued sends per run. Same claim + suppression
	 * + retry semantics as the interactive send path.
	 * GET /cron/campaign_sends?key=<cron_secret_key>  (or CLI)
	 */
	public function campaign_sends($cliKey = ''){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && $requestKey !== $secret){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		$this->load->model('marketing_model', 'marketing_m');
		if(!$this->db->table_exists('db_campaign_sends')){
			$this->_cartRecoveryOut($isCli, ['status'=>'skipped','message'=>'campaign tables not installed']);
			return;
		}
		$this->marketing_m->releaseStuckSends();
		$done = 0; $rateLimited = false;
		foreach($this->marketing_m->getUnfinishedCampaigns() as $campaign){
			foreach($this->marketing_m->getPendingSends($campaign->id, 60 - $done) as $send){
				if($done >= 60) break 2;
				$claimed = $this->marketing_m->claimSend($send->id);
				if(!$claimed) continue;
				if($this->marketing_m->isSuppressed($campaign->store_id, $campaign->channel, $send->recipient)){
					$this->marketing_m->markSend($send->id, 'suppressed', 'recipient opted out');
					continue;
				}
				// Dispatch via the store's own controller path so provider
				// handling (Resend/SMTP/SMS) stays in one place.
				$res = $this->_campaignProviderSend($campaign, $send);
				if(!empty($res['rate_limited'])){
					$this->db->where('id', $send->id)->update('db_campaign_sends', ['status' => 'pending', 'error' => 'rate limited']);
					$rateLimited = true;
					break 2;
				}
				$this->marketing_m->markSend($send->id, $res['ok'] ? 'sent' : 'failed', $res['error'] ?? null);
				$done++;
			}
			$stats = $this->marketing_m->campaignSendStats($campaign->id);
			if(($stats['pending'] + $stats['sending']) === 0){
				$this->db->where('id', $campaign->id)->update('db_campaigns', ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
			}
		}
		$this->_cartRecoveryOut($isCli, ['status'=>'completed','sent'=>$done,'rate_limited'=>$rateLimited]);
	}

	private function _campaignProviderSend($campaign, $send){
		$recipient = $send->recipient;
		$name = $send->customer_name ?? '';
		$text = str_replace('{name}', $name ?: 'there', (string)$campaign->message);
		$unsubUrl = !empty($send->unsub_token) ? base_url('storefront/campaign_unsubscribe/' . $send->unsub_token) : null;
		if($campaign->channel === 'sms'){
			if($unsubUrl) $text .= ' Opt out: ' . $unsubUrl;
			$resp = $this->sms_model->send_sms($recipient, $text);
			$ok = is_string($resp) ? (stripos($resp, 'success') !== false || stripos($resp, 'sent') !== false) : (bool)$resp;
			$err = $ok ? null : (is_string($resp) ? $resp : 'SMS send failed');
			return ['ok' => $ok, 'error' => $err, 'rate_limited' => $err && preg_match('/rate.?limit|429|too many|throttl/i', $err)];
		}
		$store = $this->db->where('id', $campaign->store_id)->get('db_store')->row();
		$storeName = $store->store_name ?? 'Our store';
		$this->email_service->setStoreId($campaign->store_id);
		$html = '<p>Hi ' . htmlspecialchars($name ?: 'there') . ',</p><p>' . nl2br(htmlspecialchars($text)) . '</p>'
			. '<p style="color:#888;font-size:12px;">— ' . htmlspecialchars($storeName) . '</p>'
			. ($unsubUrl ? '<p style="color:#888;font-size:11px;"><a href="' . htmlspecialchars($unsubUrl) . '">Unsubscribe from these emails</a></p>' : '');
		$plain = $text . ($unsubUrl ? "\n\nUnsubscribe: {$unsubUrl}" : '');
		$res = $this->email_service->sendRaw($recipient, $campaign->subject ?: ('A message from ' . $storeName), $html, $plain,
			['template_key' => 'campaign', 'related_module' => 'campaign', 'related_record_id' => $campaign->id]);
		$err = empty($res['success']) ? ($res['message'] ?? 'send failed') : null;
		return ['ok' => !empty($res['success']), 'error' => $err, 'rate_limited' => $err && preg_match('/rate.?limit|429|too many|throttl/i', $err)];
	}

	private function _cartRecoveryOut($isCli, $payload){
		if($isCli){ echo "Cart recovery: " . json_encode($payload) . "\n"; return; }
		header('Content-Type: application/json'); echo json_encode($payload);
	}

	private function _cartRecoveryMessage($storeName, $cart, $restoreUrl){
		$items = json_decode((string)$cart->items_json, true) ?: [];
		$lines = [];
		foreach(array_slice($items, 0, 10) as $i){
			$lines[] = '- ' . ($i['qty'] ?? 1) . ' x ' . ($i['name'] ?? 'Item');
		}
		$text = "Hi " . ($cart->customer_name ?: 'there') . ",\n\n"
			. "You left items in your cart at {$storeName}:\n" . implode("\n", $lines)
			. "\n\nComplete your order: {$restoreUrl}\n\n"
			. "To stop these reminders, open the link and choose \"Don't remind me\".";
		$html = '<p>Hi ' . htmlspecialchars($cart->customer_name ?: 'there') . ',</p>'
			. '<p>You left items in your cart at <b>' . htmlspecialchars($storeName) . '</b>:</p>'
			. '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $lines)) . '</li></ul>'
			. '<p><a href="' . htmlspecialchars($restoreUrl) . '">Complete your order</a></p>'
			. '<p style="color:#888;font-size:12px;">To stop these reminders, open the link and choose "Don\'t remind me".</p>';
		return ['html' => $html, 'text' => $text];
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

		// Get all stores that have debt reminders enabled at store level OR any per-customer override enabled
		$storeIds = $this->db->query("SELECT DISTINCT store_id FROM db_debt_reminder_settings WHERE (customer_id = 0 AND enabled = 1) OR (customer_id > 0 AND enabled = 1)")->result_array();
		foreach($storeIds as $storeRow){
			$storeId = $storeRow['store_id'];

			// Single delivery path — one of the two schedulers drives a store;
			// never both. The switch is per-store on db_debt_reminder_config:
			//   enabled=1  → outbox scheduler (debt_reminder_v2_model::schedule)
			//                queues through db_notification_queue and inherits
			//                legacy store/customer settings via legacyGate().
			//   enabled=0  → legacy direct-send path below, which itself only
			//                runs when db_debt_reminder_settings.enabled=1.
			// Disabling the outbox toggle therefore does NOT restart legacy for
			// a store that had legacy disabled — it simply re-enters the legacy
			// branch, which still honours its own enabled flag.
			if($this->db->table_exists('db_debt_reminder_config')){
				$cfg = $this->db->where('store_id', $storeId)->get('db_debt_reminder_config')->row();
				if($cfg && (int)$cfg->enabled === 1){
					$this->load->model('debt_reminder_v2_model', 'drv2');
					$sched = $this->drv2->schedule($storeId);
					$results['customers_checked'] += ($sched['queued'] + $sched['skipped'] + $sched['paused'] + $sched['suppressed']);
					$results['errors'][] = "store {$storeId}: deferred to outbox scheduler (queued {$sched['queued']})";
					continue;
				}
			}

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

	/**
	 * Unattended system update — pulls the pending release from the update
	 * channel and applies it. Runs within a wall-clock budget and resumes on
	 * the next invocation, so a daily cron is enough.
	 *
	 * Example: curl -s "https://yoursite.com/cron/auto_update?key=YOUR_SECRET_KEY"
	 * Or CLI:  php index.php cron auto_update YOUR_SECRET_KEY
	 */
	/**
	 * Lightweight fleet wake-up — heartbeat + pending command execution ONLY.
	 * Unlike auto_update this never touches the update pipeline, so Central's
	 * ping executes queued commands (license, OTP, suspend, settings, backup)
	 * in seconds even when the release channel is slow.
	 */
	public function fleet_ping(){
		if(!$this->cronKeyValid($this->input->get('key'))){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		@set_time_limit(60);
		@ignore_user_abort(true);
		header('Content-Type: application/json');
		try {
			$this->load->library('Updater');
			$this->updater->sendHeartbeat();
			$commands = $this->updater->pollFleetCommands();
			if(!empty($commands)){ $this->updater->sendHeartbeat(); } // report post-command state (license, suspension…)

			// A queued `update_now` MUST advance here.
			//
			// This endpoint is what Central's wake ping hits, and it was
			// deliberately built to skip the update pipeline so license/OTP
			// commands answered in seconds. But that also meant "Update now"
			// was polled, marked done, and then never actually ran the update
			// — the only thing that advanced an install was the 30-minute
			// cron. Central said "woken now" while nothing moved.
			//
			// The update is resumable by design (persisted state + step
			// runner), so a bounded slice here is safe: it makes progress
			// whether or not cron ever fires, and the next ping continues.
			$wantsUpdate = false;
			foreach ((array) $commands as $c) {
				if (($c['command'] ?? '') === 'update_now') { $wantsUpdate = true; break; }
			}
			$update = null;
			if ($wantsUpdate) {
				// Short slice — this request may be Central's 5s curl, which
				// disconnects early. ignore_user_abort keeps it running to
				// the budget so the work is not wasted.
				$update = $this->updater->runAutoUpdate(25);
				$this->updater->sendHeartbeat();
			}

			echo json_encode(['status'=>'ok','commands'=>$commands,'update'=>$update]);
		} catch (Throwable $e) {
			// Never blank-500 a wake ping — report the fault so Central shows it.
			log_message('error', 'fleet_ping failed: ' . $e->getMessage());
			echo json_encode(['status'=>'error','message'=>get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()]);
		}
	}

	public function auto_update($cliKey = ''){
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');

		if(!$isCli && !$this->cronKeyValid($requestKey)){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}

		@set_time_limit(120);
		// Central pings this URL to wake the install for queued commands —
		// keep running even if the caller disconnects early.
		@ignore_user_abort(true);
		$commands = [];
		try {
			$this->load->library('Updater');
			// Heartbeat + command poll FIRST — this request is usually Central's
			// wake ping; queued commands (update_now, set_license, OTP…) must run
			// even if the update pipeline below stalls on a slow channel fetch.
			$this->updater->sendHeartbeat();
			$commands = $this->updater->pollFleetCommands();
			$result = $this->updater->runAutoUpdate(90);
			// Report the post-update state back immediately.
			$this->updater->sendHeartbeat();
		} catch (Throwable $e) {
			log_message('error', 'auto_update failed: ' . $e->getMessage());
			$result = ['status'=>'error','message'=>get_class($e) . ': ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine()];
		}
		$result['commands'] = $commands;

		if($isCli){
			echo "Auto-update: [{$result['status']}] {$result['message']}\n";
		} else {
			header('Content-Type: application/json');
			echo json_encode($result);
		}
	}

	/**
	 * Notification outbox consumer — queued lead/appointment events.
	 * CLI: php index.php cron physio_notifications
	 * Web: /cron/physio_notifications?key=<cron_secret_key>
	 * Per-event isolation: a failed delivery marks that row only; business
	 * records (leads, appointments) are never touched here. Define
	 * MP_NOTIFY_SINK to a log path as the development test email sink.
	 */
	public function physio_notifications($cliKey = ''){
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && !$this->cronKeyValid($requestKey)){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		if(!function_exists('physio_notification_process')){ $this->load->helper('physio'); }
		$result = physio_notification_process(50);
		if($isCli){
			echo "Notifications: sent={$result['sent']} failed={$result['failed']} suppressed={$result['suppressed']}\n";
		} else {
			header('Content-Type: application/json');
			echo json_encode(['status'=>'ok'] + $result);
		}
	}

	/**
	 * Queue appointment reminders — confirmed appointments inside the
	 * store's reminder_hours_before window are queued once (reminder_queued
	 * flag) and delivered by physio_notifications with suppression recheck.
	 * CLI: php index.php cron physio_appointment_reminders
	 */
	public function physio_appointment_reminders($cliKey = ''){
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && !$this->cronKeyValid($requestKey)){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		if(!function_exists('physio_notify')){ $this->load->helper('physio'); }
		if(!$this->db->table_exists('db_portal_policies')){ echo "no portal policies\n"; return; }
		$queued = 0;
		$stores = $this->db->select('store_id')->distinct()
			->where('industry_type','physiotherapy_rehabilitation')
			->get('db_store_industry_settings')->result();
		foreach($stores as $s){
			$sid = (int)$s->store_id;
			$pol = $this->db->where('store_id',$sid)->where('policy_key','reminder_hours_before')
				->get('db_portal_policies')->row();
			$hours = $pol ? max(1, (int)$pol->policy_value) : 24;
			$rows = $this->db->where('store_id',$sid)->where('status','confirmed')
				->where('reminder_queued',0)
				->where('scheduled_at >=', date('Y-m-d H:i:s'))
				->where('scheduled_at <=', date('Y-m-d H:i:s', strtotime('+'.$hours.' hours')))
				->get('db_appointments')->result();
			foreach($rows as $a){
				// Key the reminder to the slot, not just the appointment —
				// a reschedule queues a fresh reminder (and suppresses the
				// stale one) instead of replaying the old key.
				if(physio_notify('appt.reminder.'.$a->id.'.'.strtotime($a->scheduled_at), array(
					'store_id'=>$sid,'channel'=>'email','template_key'=>'appt_reminder',
					'payload'=>array('appointment_id'=>(int)$a->id,'patient_id'=>(int)$a->patient_id,
						'scheduled_at'=>$a->scheduled_at),
				))){
					$this->db->where('id',$a->id)->update('db_appointments', array('reminder_queued'=>1));
					$queued++;
				}
			}
		}
		if($isCli){ echo "Appointment reminders queued: {$queued}\n"; }
		else { header('Content-Type: application/json'); echo json_encode(['status'=>'ok','queued'=>$queued]); }
	}

	/**
	 * Post inpatient daily charges for every physiotherapy store.
	 * Retry-safe: db_daily_charges UNIQUE(admission_id, charge_date,
	 * charge_code) makes re-runs no-ops; wallet auto-settlement replays on
	 * payment_reference. CLI: php index.php cron inpatient_daily_billing
	 */
	public function inpatient_daily_billing($cliKey = ''){
		$requestKey = $this->input->get('key') ?: $cliKey;
		$isCli = (php_sapi_name() === 'cli');
		if(!$isCli && !$this->cronKeyValid($requestKey)){
			http_response_code(403);
			echo json_encode(['status'=>'error','message'=>'Invalid or missing cron key.']);
			return;
		}
		$this->load->model('inpatient_model','ipd');
		$date = $this->input->get('date') ?: date('Y-m-d');
		$stores = $this->db->select('store_id')->distinct()
			->where('industry_type','physiotherapy_rehabilitation')
			->get('db_store_business_profile')->result();
		$total = array('posted'=>0,'skipped'=>0,'settled'=>0);
		foreach($stores as $s){
			$r = $this->ipd->postDailyCharges((int)$s->store_id, $date);
			$total['posted'] += $r['posted']; $total['skipped'] += $r['skipped']; $total['settled'] += $r['settled'];
		}
		if($isCli){
			echo "Inpatient billing {$date}: posted={$total['posted']} skipped={$total['skipped']} settled={$total['settled']}\n";
		} else {
			header('Content-Type: application/json');
			echo json_encode(['status'=>'ok','date'=>$date] + $total);
		}
	}

	/**
	 * Accepts the configured cron secret, the legacy shared fallback, or a
	 * wake token derived from this install's install_key — Central can always
	 * compute it (the heartbeat reports install_key), so pings no longer
	 * depend on the install's cron_secret_key being known.
	 */
	protected function cronKeyValid($key){
		$secret = $this->config->item('cron_secret_key');
		if(empty($secret)){ $secret = 'martpoint_cron_2024'; }
		if(hash_equals($secret, (string)$key)){ return true; }
		try {
			$this->load->database();
			if($this->db->field_exists('install_key','db_sitesettings')){
				$ik = $this->db->select('install_key')->where('id',1)->get('db_sitesettings')->row();
				if($ik && $ik->install_key !== ''
					&& hash_equals(hash_hmac('sha256','mp_wake',$ik->install_key), (string)$key)){
					return true;
				}
			}
		} catch (Exception $e) {}
		return false;
	}

	protected function _currency($amount){
		return store_number_format($amount);
	}
}
