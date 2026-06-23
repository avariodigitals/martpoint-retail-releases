<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Settings Controller
 * Central hub for email provider config, templates, logs, and testing.
 */
class Email_settings extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/* ---------- Main Settings Page ---------- */

	public function index(){
		$this->permission_check('smtp_settings');
		$data = $this->data;
		$data['page_title'] = 'Email Settings';

		$this->load->model('email_settings_model');
		$data['settings'] = $this->email_settings_model->getSettings();

		$this->load->view('email-settings', $data);
	}

	/* ---------- Save Settings ---------- */

	public function save(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('email_settings_model');

		$allowed = [
			'email_provider','email_from_name','email_from_email','email_reply_to',
			'smtp_status','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_crypto',
			'resend_api_key','resend_from_email','resend_from_name'
		];

		$update = [];
		foreach($allowed as $key){
			if(isset($_POST[$key])){
				$update[$key] = $this->security->xss_clean(html_escape($_POST[$key]));
			}
		}

		$store_id = get_current_store_id();
		$ok = $this->email_settings_model->saveSettings($store_id, $update);

		echo $ok ? 'success' : 'failed';
	}

	/* ---------- Test Email ---------- */

	public function test_email(){
		$this->permission_check_with_msg('smtp_settings');

		$test_email = $this->input->post('test_email');
		$provider   = $this->input->post('provider'); // optional: test specific provider

		if(empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
			exit;
		}

		$this->load->model('email_service');
		$result = $this->email_service->testProvider($test_email, $provider ?: NULL);

		if($result['success']){
			echo json_encode(['status' => 'success', 'message' => $result['message']]);
		} else {
			echo json_encode(['status' => 'error', 'message' => $result['message']]);
		}
	}

	/* ---------- Templates ---------- */

	public function templates(){
		$this->permission_check('smtp_settings');
		$data = $this->data;
		$data['page_title'] = 'Email Templates';

		$this->load->model('email_template_model');
		$data['templates'] = $this->email_template_model->getAll();

		$this->load->view('email-templates', $data);
	}

	public function template_edit($id = NULL){
		$this->permission_check('smtp_settings');
		$this->load->model('email_template_model');

		$data = $this->data;
		$data['page_title'] = 'Edit Email Template';

		if(!empty($id)){
			$data['template'] = $this->email_template_model->getById($id);
		} else {
			$data['template'] = NULL;
		}

		$this->load->view('email-template-form', $data);
	}

	public function template_save(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('email_template_model');

		$id = (int)$this->input->post('id');
		$store_id = get_current_store_id();

		$fields = [
			'template_key'       => $this->input->post('template_key'),
			'template_name'      => $this->input->post('template_name'),
			'subject'            => $this->input->post('subject'),
			'html_body'          => $this->input->post('html_body'),
			'text_body'          => $this->input->post('text_body'),
			'status'             => (int)$this->input->post('status'),
			'send_copy_to_owner' => (int)$this->input->post('send_copy_to_owner'),
		];

		if($id > 0){
			$template = $this->email_template_model->getById($id);
			if(!$template || $template->store_id != $store_id){
				echo json_encode(['status' => 'error', 'message' => 'Template not found.']);
				exit;
			}
			// Don't allow changing template_key on existing templates
			unset($fields['template_key']);
			$this->email_template_model->update($id, $fields);
		} else {
			$fields['store_id'] = $store_id;
			$this->email_template_model->create($fields);
		}

		echo json_encode(['status' => 'success', 'message' => 'Template saved successfully.']);
	}

	public function template_test(){
		$this->permission_check_with_msg('smtp_settings');

		$template_id = (int)$this->input->post('template_id');
		$test_email  = $this->input->post('test_email');
		$sample_data = $this->input->post('sample_data'); // JSON string

		if(empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
			exit;
		}

		$this->load->model('email_template_model');
		$this->load->model('email_service');

		$template = $this->email_template_model->getById($template_id);
		if(!$template){
			echo json_encode(['status' => 'error', 'message' => 'Template not found.']);
			exit;
		}

		$data = !empty($sample_data) ? json_decode($sample_data, TRUE) : [];
		if(!is_array($data)){ $data = []; }

		$result = $this->email_service->sendTemplate($template->template_key, $test_email, $data);

		if($result['success']){
			echo json_encode(['status' => 'success', 'message' => 'Test email sent successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => $result['message']]);
		}
	}

	public function template_preview(){
		$this->permission_check_with_msg('smtp_settings');

		$template_id = (int)$this->input->post('template_id');
		$sample_data = $this->input->post('sample_data');

		$this->load->model('email_template_model');
		$template = $this->email_template_model->getById($template_id);
		if(!$template){
			echo json_encode(['status' => 'error', 'message' => 'Template not found.']);
			exit;
		}

		$data = !empty($sample_data) ? json_decode($sample_data, TRUE) : [];
		if(!is_array($data)){ $data = []; }

		$this->load->library('email_template_parser');
		$parser = $this->email_template_parser;

		$subject = $parser->parse($template->subject, $data);
		$html    = $parser->parse($template->html_body, $data);

		echo json_encode([
			'status'  => 'success',
			'subject' => $subject,
			'html'    => $html,
			'missing' => $parser->getMissing()
		]);
	}

	public function seed_templates(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('email_template_model');
		$this->email_template_model->seedDefaults();
		$this->session->set_flashdata('success', 'Default templates seeded successfully.');
		redirect('email_settings/templates');
	}

	/* ---------- Logs ---------- */

	public function logs(){
		$this->permission_check('smtp_settings');
		$data = $this->data;
		$data['page_title'] = 'Email Logs';

		$this->load->model('email_log_model');
		$data['logs'] = $this->email_log_model->getLogs(NULL, [], 100, 0);

		$this->load->view('email-logs', $data);
	}

	public function logs_ajax(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('email_log_model');

		$filters = [];
		if($this->input->get('status'))      $filters['status']      = $this->input->get('status');
		if($this->input->get('email_type')) $filters['email_type']  = $this->input->get('email_type');
		if($this->input->get('recipient'))  $filters['recipient']   = $this->input->get('recipient');
		if($this->input->get('date_from'))  $filters['date_from']   = $this->input->get('date_from');
		if($this->input->get('date_to'))    $filters['date_to']     = $this->input->get('date_to');
		if($this->input->get('provider'))   $filters['provider']    = $this->input->get('provider');

		$page  = (int)$this->input->get('page') ?: 1;
		$limit = (int)$this->input->get('limit') ?: 50;
		$offset = ($page - 1) * $limit;

		$result = $this->email_log_model->getLogs(NULL, $filters, $limit, $offset);
		echo json_encode(['status' => 'success', 'data' => $result['rows'], 'total' => $result['total']]);
	}

	public function retry_email(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('email_log_model');

		$logId = (int)$this->input->post('log_id');
		$result = $this->email_log_model->retry($logId);

		echo json_encode($result);
	}

	/* ---------- Scheduled Reports ---------- */

	public function schedules(){
		$this->permission_check('smtp_settings');
		$data = $this->data;
		$data['page_title'] = 'Scheduled Reports';

		$this->load->model('report_schedule_model');
		$this->report_schedule_model->seedDefaults();
		$data['schedules'] = $this->report_schedule_model->getAll();

		$this->load->view('email-schedules', $data);
	}

	public function seed_schedules(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('report_schedule_model');
		$this->report_schedule_model->seedDefaults();
		$this->session->set_flashdata('success', 'Default schedules seeded successfully.');
		redirect('email_settings/schedules');
	}

	public function schedule_run_now(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('report_schedule_model');
		$this->load->model('dashboard_model');
		$this->load->model('email_service');
		$this->load->model('sms_model');

		$id = (int)$this->input->post('id');
		$store_id = get_current_store_id();

		$schedule = $this->report_schedule_model->getById($id, $store_id);
		if(!$schedule){
			echo json_encode(['status' => 'error', 'message' => 'Schedule not found.']);
			exit;
		}

		if(!$schedule->status){
			echo json_encode(['status' => 'error', 'message' => 'Schedule is disabled. Enable it first.']);
			exit;
		}

		$result = $this->_executeSchedule($schedule);

		if($result['status'] === 'success'){
			echo json_encode(['status' => 'success', 'message' => 'Report sent successfully.']);
		} else if($result['status'] === 'partial'){
			echo json_encode(['status' => 'partial', 'message' => $result['message']]);
		} else if($result['status'] === 'skipped'){
			echo json_encode(['status' => 'info', 'message' => $result['message']]);
		} else {
			echo json_encode(['status' => 'error', 'message' => $result['message']]);
		}
	}

	protected function _executeSchedule($schedule){
		$storeId = $schedule->store_id;
		$type = $schedule->report_type;
		$date = date('Y-m-d');

		$storeRec = $this->db->where('id', $storeId)->get('db_store')->row();
		if(!$storeRec){
			return ['report_type' => $type, 'status' => 'error', 'message' => 'Store not found'];
		}

		$storeName = $storeRec->store_name;
		$errors = [];

		if($type === 'daily_summary'){
			$reportData = $this->dashboard_model->get_daily_summary($date);
			if(!$reportData['has_data']){
				$this->report_schedule_model->updateLastRun($schedule->id);
				return ['status' => 'skipped', 'message' => 'No business data for today'];
			}
		} else if($type === 'low_stock_alert'){
			$reportData = ['low_stock_items' => $this->dashboard_model->get_low_stock_items(), 'has_data' => true];
			if(count($reportData['low_stock_items']) === 0){
				$this->report_schedule_model->updateLastRun($schedule->id);
				return ['status' => 'skipped', 'message' => 'No low stock items'];
			}
		} else {
			return ['status' => 'error', 'message' => 'Unknown report type'];
		}

		// Email
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
							'total_sales' => store_number_format($reportData['sales']['total'] ?? 0),
							'total_profit' => ($reportData['profit']['available'] ?? false) ? store_number_format($reportData['profit']['gross_profit']) : 'N/A',
							'total_expenses' => store_number_format($reportData['expenses']['total'] ?? 0),
							'net_position' => store_number_format($reportData['net_position'] ?? 0),
							'cash_expected' => store_number_format($reportData['sales']['cash_expected'] ?? 0),
							'outstanding_debts' => store_number_format($reportData['outstanding_debts']['total'] ?? 0),
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

		// WhatsApp
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

		$this->report_schedule_model->updateLastRun($schedule->id);

		if(empty($errors)){
			return ['status' => 'success', 'message' => 'Sent successfully'];
		}
		return ['status' => 'partial', 'message' => implode('; ', $errors)];
	}

	protected function _buildTopProductsHtml($products){
		if(count($products) === 0) return '<p>No top products for this date.</p>';
		$html = '<ul>';
		foreach($products as $p){
			$html .= '<li>' . htmlspecialchars($p['name']) . ' — Qty: ' . number_format($p['qty']) . ' — Revenue: ' . store_number_format($p['revenue']) . '</li>';
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

	public function schedule_save(){
		$this->permission_check_with_msg('smtp_settings');
		$this->load->model('report_schedule_model');

		$id = (int)$this->input->post('id');
		$store_id = get_current_store_id();

		$fields = [
			'frequency'              => $this->input->post('frequency'),
			'send_time'              => $this->input->post('send_time'),
			'email_enabled'          => (int)$this->input->post('email_enabled'),
			'email_recipients'       => $this->input->post('email_recipients'),
			'email_template_key'     => $this->input->post('email_template_key'),
			'whatsapp_enabled'       => (int)$this->input->post('whatsapp_enabled'),
			'whatsapp_numbers'       => $this->input->post('whatsapp_numbers'),
			'whatsapp_message_template' => $this->input->post('whatsapp_message_template'),
			'status'                 => (int)$this->input->post('status'),
		];

		if($id > 0){
			$schedule = $this->report_schedule_model->getById($id);
			if(!$schedule || $schedule->store_id != $store_id){
				echo json_encode(['status' => 'error', 'message' => 'Schedule not found.']);
				exit;
			}
			$this->report_schedule_model->update($id, $fields);
		} else {
			$fields['store_id'] = $store_id;
			$this->report_schedule_model->create($fields);
		}

		echo json_encode(['status' => 'success', 'message' => 'Schedule saved successfully.']);
	}
}
