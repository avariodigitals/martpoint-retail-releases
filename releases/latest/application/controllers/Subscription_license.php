<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subscription_license extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/* ================= SUPER ADMIN ONLY ================= */

	public function index(){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$this->load->model('subscription_license_model','license');
		$data = $this->data;
		$data['license'] = $this->license->get_by_store();
		$data['license_history'] = $this->license->get_history();
		$data['plans'] = $this->db->table_exists('db_subscription_plans') ? $this->db->where('is_active',1)->order_by('display_order','asc')->get('db_subscription_plans')->result() : [];
		$data['branch_used'] = get_branch_usage();
		$data['user_used'] = get_user_usage();
		$data['product_used'] = get_product_usage();
		$data['service_used'] = get_service_usage();
		$data['media_used'] = get_media_storage_usage_mb();
		$data['storefront_used'] = get_storefront_usage();
		$data['domain_used'] = get_custom_domain_usage();
		$data['page_title'] = 'License Management';
		$this->load->view('subscription_license/index', $data);
	}

	public function activate_form(){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$this->load->model('subscription_license_model','license');
		$data = $this->data;
		$data['license'] = $this->license->get_by_store();
		$data['page_title'] = 'Activate MartPoint Retail';
		$this->load->view('subscription_license/activate', $data);
	}

	public function usage(){
		if(!special_access()){
			redirect('dashboard','refresh');
		}
		$this->load->model('subscription_license_model','license');
		$data = $this->data;
		$data['license'] = $this->license->get_by_store();
		$data['branch_used'] = get_branch_usage();
		$data['user_used'] = get_user_usage();
		$data['product_used'] = get_product_usage();
		$data['service_used'] = get_service_usage();
		$data['media_used'] = get_media_storage_usage_mb();
		$data['page_title'] = 'License Usage';
		$this->load->view('subscription_license/usage', $data);
	}

	/* ----- Request OTP (AJAX) ----- */

	public function request_otp(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');
		$store_id = get_current_store_id();
		$type = $this->input->post('otp_type') ?: 'generate';

		// Rate limit: prevent duplicate OTP requests within 60 seconds
		$recent = $this->db->where('store_id', $store_id)
			->where('otp_type', $type)
			->where('used', 0)
			->where('created_at >=', date('Y-m-d H:i:s', strtotime('-60 seconds')))
			->order_by('id', 'desc')
			->get('db_license_otps')
			->row();
		if($recent){
			$remaining = max(0, 60 - (time() - strtotime($recent->created_at)));
			echo json_encode([
				'status' => 'warning',
				'message' => 'An OTP was already sent ' . (60 - $remaining) . ' seconds ago. Please check your email, or wait ' . $remaining . ' seconds to request a new one.'
			]);
			exit;
		}

		$otp = $this->license->generate_otp($store_id, $type);
		$result = $this->_send_otp_email($otp, $type);
		if($result['success']){
			echo json_encode(['status'=>'success','message'=>'OTP sent to authorized email.']);
		} else {
			log_message('error', 'License OTP email delivery failed for store_id=' . $store_id . ': ' . ($result['message'] ?? 'Unknown error'));
			echo json_encode([
				'status' => 'error',
				'message' => 'Failed to send OTP email. Please check your email configuration in Settings and try again, or contact support.'
			]);
		}
	}

	/* ----- Generate License Key (AJAX) ----- */

	public function generate_license(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}

		$plan_name = $this->input->post('plan_name');
		$start_date = $this->input->post('subscription_start_date');
		$end_date = $this->input->post('subscription_end_date');
		$branch_limit = (int) $this->input->post('branch_limit');
		$user_limit = (int) $this->input->post('user_limit');
		$product_limit = (int) $this->input->post('product_limit');
		$service_limit = (int) $this->input->post('service_limit');
		$media_storage_limit_mb = (int) $this->input->post('media_storage_limit_mb');
		$storefront_limit = (int) $this->input->post('storefront_limit');
		$custom_domain_limit = (int) $this->input->post('custom_domain_limit');
		$whatsapp_number = $this->input->post('whatsapp_number');
		$renewal_amount = $this->input->post('renewal_amount');
		$client_name = $this->input->post('client_name');
		$otp_code = strtoupper(trim($this->input->post('otp_code') ?: ''));

		if(empty($plan_name) || empty($start_date) || empty($end_date)){
			echo json_encode(['status'=>'error','message'=>'Plan, Start Date and End Date are required']);
			exit;
		}

		$this->load->model('subscription_license_model','license');
		$store_id = get_current_store_id();

		if(empty($otp_code)){
			echo json_encode(['status'=>'otp_required','message'=>'OTP required. Please request an OTP.']);
			exit;
		}
		$otp_result = $this->license->validate_otp($store_id, $otp_code, 'generate');
		if($otp_result !== true){
			echo json_encode(['status'=>'error','message'=>$otp_result]);
			exit;
		}

		$key_data = [
			'plan_name' => $plan_name,
			'subscription_start_date' => $start_date,
			'subscription_end_date' => $end_date,
			'branch_limit' => $branch_limit ?: 1,
			'user_limit' => $user_limit ?: 3,
			'product_limit' => $product_limit ?: 500,
			'service_limit' => $service_limit ?: 100,
			'media_storage_limit_mb' => $media_storage_limit_mb ?: 2048,
			'storefront_limit' => $storefront_limit ?: 1,
			'custom_domain_limit' => $custom_domain_limit ?: 1,
			'whatsapp_number' => $whatsapp_number ?: '',
			'renewal_amount' => $renewal_amount ?: '',
			'client_name' => $client_name ?: '',
		];

		$license_key = generate_license_key($key_data);
		$this->session->set_userdata('last_generated_license', array_merge($key_data, ['license_key' => $license_key]));

		echo json_encode([
			'status' => 'success',
			'license_key' => $license_key,
			'data' => $key_data
		]);
	}

	/* ----- Activate Subscription (decode license key) ----- */

	public function save_activation(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');

		$license_code = trim($this->input->post('license_code'));
		$otp_code = strtoupper(trim($this->input->post('otp_code') ?: ''));

		if(empty($license_code)){
			echo json_encode(['status'=>'error','message'=>'License key is required']);
			exit;
		}

		$decoded = decode_license_key($license_code);
		if($decoded === false){
			echo json_encode(['status'=>'error','message'=>'Invalid license key']);
			exit;
		}

		if(!validate_license_domain($license_code)){
			echo json_encode(['status'=>'error','message'=>'This license key is not valid for this domain.']);
			exit;
		}

		$store_id = get_current_store_id();
		$existing = $this->license->get_by_store($store_id);
		$was_activated = $this->license->needs_activation($store_id);

		if($existing && !empty($existing->license_code)){
			if(empty($otp_code)){
				echo json_encode(['status'=>'otp_required','message'=>'OTP required to replace existing license.']);
				exit;
			}
			$otp_result = $this->license->validate_otp($store_id, $otp_code, 'activate');
			if($otp_result !== true){
				echo json_encode(['status'=>'error','message'=>$otp_result]);
				exit;
			}
			$this->license->add_history($store_id, $existing->license_code, $existing->plan_name, $existing->domain, 'active');
		}

		$domain = $_SERVER['HTTP_HOST'] ?: '';
		$save_data = [
			'store_id' => $store_id,
			'license_code' => $license_code,
			'plan_name' => $decoded['plan_name'],
			'subscription_start_date' => $decoded['subscription_start_date'],
			'subscription_end_date' => $decoded['subscription_end_date'],
			'branch_limit' => $decoded['branch_limit'] ?? 1,
			'user_limit' => $decoded['user_limit'] ?? 3,
			'product_limit' => $decoded['product_limit'] ?? 500,
			'service_limit' => $decoded['service_limit'] ?? 100,
			'media_storage_limit_mb' => $decoded['media_storage_limit_mb'] ?? 2048,
			'storefront_limit' => $decoded['storefront_limit'] ?? 1,
			'custom_domain_limit' => $decoded['custom_domain_limit'] ?? 1,
			'whatsapp_number' => $decoded['whatsapp_number'] ?? '',
			'renewal_amount' => $decoded['renewal_amount'] ?? '',
			'client_name' => $decoded['client_name'] ?? '',
			'domain' => $domain,
		];

		if($this->license->activate($store_id, $save_data)){
			$days_left = $this->license->days_left($decoded['subscription_end_date']);
			$this->send_subscription_email($was_activated ? 'subscription_activated' : 'subscription_renewed', $save_data);
			echo json_encode([
				'status' => 'success',
				'message' => 'Subscription activated successfully',
				'data' => array_merge($decoded, ['days_left' => $days_left])
			]);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to save subscription']);
		}
	}

	/* ----- Extend / Renew Subscription ----- */

	public function extend(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');

		$license_code = trim($this->input->post('license_code'));
		$otp_code = strtoupper(trim($this->input->post('otp_code') ?: ''));

		if(empty($license_code)){
			echo json_encode(['status'=>'error','message'=>'License key is required']);
			exit;
		}

		$decoded = decode_license_key($license_code);
		if($decoded === false){
			echo json_encode(['status'=>'error','message'=>'Invalid license key']);
			exit;
		}

		if(!validate_license_domain($license_code)){
			echo json_encode(['status'=>'error','message'=>'This license key is not valid for this domain.']);
			exit;
		}

		$store_id = get_current_store_id();

		if(empty($otp_code)){
			echo json_encode(['status'=>'otp_required','message'=>'OTP required for renewal.']);
			exit;
		}
		$otp_result = $this->license->validate_otp($store_id, $otp_code, 'renew');
		if($otp_result !== true){
			echo json_encode(['status'=>'error','message'=>$otp_result]);
			exit;
		}

		$existing = $this->license->get_by_store($store_id);
		if($existing && !empty($existing->license_code)){
			$this->license->add_history($store_id, $existing->license_code, $existing->plan_name, $existing->domain, 'active');
		}

		$save_data = [
			'store_id' => $store_id,
			'license_code' => $license_code,
			'plan_name' => $decoded['plan_name'],
			'subscription_start_date' => $decoded['subscription_start_date'],
			'subscription_end_date' => $decoded['subscription_end_date'],
			'branch_limit' => $decoded['branch_limit'] ?? 1,
			'user_limit' => $decoded['user_limit'] ?? 3,
			'product_limit' => $decoded['product_limit'] ?? 500,
			'service_limit' => $decoded['service_limit'] ?? 100,
			'media_storage_limit_mb' => $decoded['media_storage_limit_mb'] ?? 2048,
			'storefront_limit' => $decoded['storefront_limit'] ?? 1,
			'custom_domain_limit' => $decoded['custom_domain_limit'] ?? 1,
			'whatsapp_number' => $decoded['whatsapp_number'] ?? '',
			'renewal_amount' => $decoded['renewal_amount'] ?? '',
			'client_name' => $decoded['client_name'] ?? '',
			'last_renewal_date' => date('Y-m-d'),
			'subscription_status' => 'ACTIVE',
			'suspension_reason' => null,
			'domain' => $_SERVER['HTTP_HOST'] ?: '',
		];

		// Reset reminder flags on renew
		$this->db->where('store_id', $store_id)->update('db_subscription_license', [
			'reminder_90_sent' => 0,
			'reminder_60_sent' => 0,
			'reminder_30_last_sent' => null,
			'reminder_10_last_sent' => null,
			'expiry_notice_sent' => 0,
			'expired_followup_count' => 0,
			'expired_followup_last_sent' => null,
		]);

		if($this->license->save($save_data)){
			$this->send_subscription_email('subscription_renewed', $save_data);
			echo json_encode(['status'=>'success','message'=>'Subscription renewed successfully']);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to renew subscription']);
		}
	}

	public function suspend(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');
		$store_id = get_current_store_id();
		$reason = $this->input->post('reason');

		$this->license->save([
			'store_id' => $store_id,
			'subscription_status' => 'SUSPENDED',
			'suspension_reason' => $reason
		]);

		$this->send_subscription_email('subscription_suspended', ['suspension_reason'=>$reason]);
		echo json_encode(['status'=>'success','message'=>'Subscription suspended']);
	}

	public function reactivate(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');
		$store_id = get_current_store_id();
		$this->license->save([
			'store_id' => $store_id,
			'subscription_status' => 'ACTIVE',
			'suspension_reason' => null
		]);
		echo json_encode(['status'=>'success','message'=>'Subscription reactivated']);
	}

	/* ----- Deactivate History License (AJAX) ----- */

	public function get_plan_preset(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$plan_code = $this->input->post('plan_code', TRUE);
		if(empty($plan_code)){
			echo json_encode(['status'=>'error','message'=>'Plan code required']);
			exit;
		}
		if(!$this->db->table_exists('db_subscription_plans')){
			echo json_encode(['status'=>'error','message'=>'Plans table not found']);
			exit;
		}
		$plan = $this->db->where('plan_code', $plan_code)->where('is_active', 1)->get('db_subscription_plans')->row();
		if(!$plan){
			echo json_encode(['status'=>'error','message'=>'Plan not found']);
			exit;
		}
		echo json_encode([
			'status' => 'success',
			'data' => [
				'plan_name' => $plan->plan_name,
				'branch_limit' => (int) $plan->branch_limit,
				'user_limit' => (int) $plan->user_limit,
				'product_limit' => (int) $plan->product_limit,
				'service_limit' => (int) $plan->service_limit,
				'media_storage_limit_mb' => (int) $plan->media_storage_limit_mb,
				'storefront_limit' => (int) $plan->storefront_limit,
				'custom_domain_limit' => (int) $plan->custom_domain_limit,
			]
		]);
	}

	public function update_limits(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');
		$store_id = get_current_store_id();

		$otp_code = strtoupper(trim($this->input->post('otp_code') ?: ''));
		if(empty($otp_code)){
			echo json_encode(['status'=>'otp_required','message'=>'OTP required to update limits. Please request an OTP.']);
			exit;
		}
		$otp_result = $this->license->validate_otp($store_id, $otp_code, 'update_limits');
		if($otp_result !== true){
			echo json_encode(['status'=>'error','message'=>$otp_result]);
			exit;
		}

		// Only include fields that actually exist in the table
		$all_fields = [
			'branch_limit','user_limit','product_limit','service_limit',
			'media_storage_limit_mb','storefront_limit','custom_domain_limit'
		];
		$data = ['store_id' => $store_id];
		foreach($all_fields as $f){
			if($this->db->field_exists($f, 'db_subscription_license')){
				$data[$f] = (int) $this->input->post($f);
			}
		}

		$override_enabled = (int) $this->input->post('override_enabled');
		$override_reason = trim($this->input->post('override_reason', TRUE));
		$override_expiry = $this->input->post('override_expiry', TRUE);

		$has_override_cols = $this->db->field_exists('override_branch_limit', 'db_subscription_license');

		if($override_enabled && $has_override_cols){
			$override_fields = [
				'override_branch_limit','override_user_limit','override_product_limit',
				'override_service_limit','override_media_storage_limit_mb'
			];
			foreach($override_fields as $f){
				if($this->db->field_exists($f, 'db_subscription_license')){
					$base = str_replace('override_', '', $f);
					$data[$f] = (int) $this->input->post($base);
				}
			}
			if($this->db->field_exists('override_reason', 'db_subscription_license')){
				$data['override_reason'] = $override_reason;
			}
			if($this->db->field_exists('override_expiry', 'db_subscription_license')){
				$data['override_expiry'] = !empty($override_expiry) ? $override_expiry : null;
			}
			$existing = $this->license->get_by_store($store_id);
			if($existing){
				foreach($all_fields as $f){
					if(!$this->db->field_exists($f, 'db_subscription_license')){ continue; }
					$orig = (int) ($existing->{$f} ?? 0);
					$newv = (int) $this->input->post($f);
					if($newv !== $orig){
						log_license_override($store_id, $f, $orig, $newv, $override_reason, !empty($override_expiry) ? $override_expiry : null);
					}
				}
			}
		} elseif($has_override_cols) {
			$override_fields = [
				'override_branch_limit','override_user_limit','override_product_limit',
				'override_service_limit','override_media_storage_limit_mb',
				'override_reason','override_expiry'
			];
			foreach($override_fields as $f){
				if($this->db->field_exists($f, 'db_subscription_license')){
					$data[$f] = null;
				}
			}
		}

		if($this->license->save($data)){
			echo json_encode(['status'=>'success','message'=>'Limits updated successfully']);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to update limits. Please run the SQL migration if override columns are missing.']);
		}
	}

	public function deactivate_history(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('subscription_license_model','license');
		$history_id = (int) $this->input->post('history_id');
		if($this->license->deactivate_license($history_id)){
			echo json_encode(['status'=>'success','message'=>'License deactivated']);
		} else {
			echo json_encode(['status'=>'error','message'=>'Failed to deactivate']);
		}
	}

	/* ================= CRON: Reminders ================= */

	public function run_reminders(){
		// Cron authentication
		$key = $this->input->get('key');
		$expected = $this->config->item('cron_secret_key');
		if(empty($expected) || $key !== $expected){
			show_error('Unauthorized - Invalid cron key', 403);
			return;
		}
		$this->load->model('subscription_license_model','license');
		$this->load->model('email_service');

		$licenses = $this->license->get_all_licenses();
		foreach($licenses as $lic){
			if($lic->subscription_status === 'SUSPENDED') continue;
			if(empty($lic->subscription_end_date)) continue;

			$days_left = $this->license->days_left($lic->subscription_end_date);
			$store = $this->db->where('id', $lic->store_id)->get('db_store')->row();
			if(!$store) continue;

			$this->process_reminders($lic, $days_left, $store);
		}
		echo "Reminders processed.\n";
	}

	private function process_reminders($lic, $days_left, $store){
		$store_id = $lic->store_id;
		$today = date('Y-m-d');

		if($days_left <= 90 && $days_left > 60 && !$lic->reminder_90_sent){
			$this->send_subscription_email('subscription_renewal_reminder', [
				'subscription_days_left' => $days_left,
				'subscription_expiry_date' => show_date($lic->subscription_end_date)
			]);
			$this->license->update_reminder_flag($store_id, 'reminder_90_sent', 1);
		}

		if($days_left <= 60 && $days_left > 30 && !$lic->reminder_60_sent){
			$this->send_subscription_email('subscription_renewal_reminder', [
				'subscription_days_left' => $days_left,
				'subscription_expiry_date' => show_date($lic->subscription_end_date)
			]);
			$this->license->update_reminder_flag($store_id, 'reminder_60_sent', 1);
		}

		if($days_left <= 30 && $days_left > 10){
			$last_sent = $lic->reminder_30_last_sent;
			if(empty($last_sent) || (strtotime($today) - strtotime($last_sent)) >= (3 * 86400)){
				$this->send_subscription_email('subscription_renewal_reminder', [
					'subscription_days_left' => $days_left,
					'subscription_expiry_date' => show_date($lic->subscription_end_date)
				]);
				$this->license->update_reminder_flag($store_id, 'reminder_30_last_sent', $today);
			}
		}

		if($days_left <= 10 && $days_left > 0){
			$last_sent = $lic->reminder_10_last_sent;
			if(empty($last_sent) || $last_sent !== $today){
				$this->send_subscription_email('subscription_renewal_reminder', [
					'subscription_days_left' => $days_left,
					'subscription_expiry_date' => show_date($lic->subscription_end_date)
				]);
				$this->license->update_reminder_flag($store_id, 'reminder_10_last_sent', $today);
			}
		}

		if($days_left === 0 && !$lic->expiry_notice_sent){
			$this->send_subscription_email('subscription_expired', [
				'subscription_expiry_date' => show_date($lic->subscription_end_date)
			]);
			$this->license->update_reminder_flag($store_id, 'expiry_notice_sent', 1);
		}

		if($days_left < 0 && $lic->expired_followup_count < 7){
			$last_sent = $lic->expired_followup_last_sent;
			if(empty($last_sent) || $last_sent !== $today){
				$this->send_subscription_email('subscription_expired', [
					'subscription_expiry_date' => show_date($lic->subscription_end_date)
				]);
				$this->db->where('store_id', $store_id)->set('expired_followup_count', 'expired_followup_count+1', FALSE)->update('db_subscription_license');
				$this->license->update_reminder_flag($store_id, 'expired_followup_last_sent', $today);
			}
		}
	}

	private function send_subscription_email($template_key, $extra_data = []){
		$this->load->model('email_service');
		$this->load->model('subscription_license_model','license');

		$store = get_store_details();
		$lic = $this->license->get_by_store();
		$owner = $this->db->where('store_id', $store->id)->where('role_id', 2)->get('db_users')->row();
		$admin = $this->db->where('store_id', $store->id)->where('role_id', 1)->get('db_users')->row();

		$to_emails = [];
		if($owner && !empty($owner->email)){ $to_emails[] = $owner->email; }
		if($admin && !empty($admin->email) && (!isset($owner->email) || $admin->email !== $owner->email)){ $to_emails[] = $admin->email; }

		if(empty($to_emails)) return;

		$data = array_merge([
			'user_name' => $owner ? $owner->first_name : 'Store Owner',
			'store_name' => $store->store_name,
			'license_code' => $lic->license_code ?? '',
			'subscription_plan' => $lic->plan_name ?? 'Basic',
			'subscription_start_date' => isset($lic->subscription_start_date) ? show_date($lic->subscription_start_date) : '',
			'subscription_expiry_date' => isset($lic->subscription_end_date) ? show_date($lic->subscription_end_date) : '',
			'subscription_days_left' => isset($lic->subscription_end_date) ? $this->license->days_left($lic->subscription_end_date) : 0,
			'subscription_duration' => '',
			'subscription_status' => $lic->subscription_status ?? 'ACTIVE',
			'renewal_amount' => $lic->renewal_amount ?? '',
			'subscription_suspension_reason' => $lic->suspension_reason ?? '',
			'branch_limit' => $lic->branch_limit ?? 1,
			'user_limit' => $lic->user_limit ?? 3,
			'product_limit' => $lic->product_limit ?? 500,
			'service_limit' => $lic->service_limit ?? 100,
			'media_storage_limit_mb' => $lic->media_storage_limit_mb ?? 2048,
			'storefront_limit' => $lic->storefront_limit ?? 1,
			'custom_domain_limit' => $lic->custom_domain_limit ?? 1,
			'client_name' => $lic->client_name ?? '',
			'app_name' => $store->store_name ?? 'MartPoint Retail',
		], $extra_data);

		foreach($to_emails as $email){
			$this->email_service->sendTemplate($template_key, $email, $data, ['related_module'=>'subscription_license']);
		}
	}

	/**
	 * Test email provider diagnostics (AJAX)
	 */
	public function test_email(){
		if(!special_access()){
			echo json_encode(['status'=>'error','message'=>'Unauthorized']);
			exit;
		}
		$this->load->model('email_service');
		$this->load->model('email_settings_model');

		$settings = $this->email_settings_model->getSettings();
		$provider = $settings['provider'] ?? 'none';

		$store = get_store_details();
		$owner = $this->db->where('store_id', $store->id)->where('role_id', 2)->get('db_users')->row();
		$to_email = ($owner && !empty($owner->email)) ? $owner->email : 'rapheal@avariodigitals.com';

		$result = $this->email_service->sendRaw($to_email, 'MartPoint Test Email', '<p>This is a test email.</p>', 'This is a test email.', [
			'template_key' => 'test',
			'from_name' => 'MartPoint Retail',
			'send_copy_to_owner' => false
		]);

		$diag = [
			'provider' => $provider,
			'from_email' => $settings['from_email'] ?? '',
			'from_name' => $settings['from_name'] ?? '',
			'to_email' => $to_email,
			'send_result' => $result,
		];

		// If Resend, also try domain check
		if($provider === 'resend' && !empty($settings['resend_api_key'])){
			$ch = curl_init('https://api.resend.com/domains');
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $settings['resend_api_key']],
				CURLOPT_TIMEOUT => 10,
			]);
			$resp = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			$diag['resend_domains_http'] = $code;
			$diag['resend_domains'] = json_decode($resp, TRUE);
		}

		echo json_encode(['status'=>'success','data'=>$diag]);
	}

	/* ----- Send OTP to authorized email ----- */

	private function _send_otp_email($otp, $type = 'generate'){
		$this->load->model('email_service');
		$store = get_store_details();
		$domain = $_SERVER['HTTP_HOST'] ?: 'Unknown';
		$typeLabel = ucfirst(str_replace('_', ' ', $type));

		$username = $this->session->userdata('inv_username') ?? 'Unknown';
		$full_name = '';
		if(!empty($username)){
			$u = $this->db->where('username', $username)->get('db_users')->row();
			if($u){ $full_name = trim($u->first_name . ' ' . $u->last_name); }
		}
		$full_name = $full_name ?: $username;
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
		$device = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

		$subject = "MartPoint License OTP: {$typeLabel} - {$store->store_name}";
		$html = "<h3>MartPoint Retail License OTP</h3>
<p><strong>Business:</strong> " . htmlspecialchars($store->store_name) . "</p>
<p><strong>Domain:</strong> {$domain}</p>
<p><strong>Action:</strong> {$typeLabel}</p>
<p><strong>OTP:</strong> <span style='font-size:24px; font-weight:bold; color:#2563EB;'>{$otp}</span></p>
<p><em>This OTP expires in 10 minutes and can only be used once.</em></p>
<hr>
<p><strong>Request Details (Audit)</strong></p>
<ul>
  <li><strong>User:</strong> " . htmlspecialchars($username) . "</li>
  <li><strong>Name:</strong> " . htmlspecialchars($full_name) . "</li>
  <li><strong>IP Address:</strong> " . htmlspecialchars($ip) . "</li>
  <li><strong>Device:</strong> " . htmlspecialchars($device) . "</li>
  <li><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</li>
</ul>
<hr>
<p style='color:#94A3B8; font-size:12px;'>MartPoint Retail License Security</p>";
		$text = "MartPoint Retail License OTP\nBusiness: " . $store->store_name . "\nDomain: {$domain}\nAction: {$typeLabel}\nOTP: {$otp}\nThis OTP expires in 10 minutes and can only be used once.\n\n--- Request Details (Audit) ---\nUser: {$username}\nName: {$full_name}\nIP Address: {$ip}\nDevice: {$device}\nTime: " . date('Y-m-d H:i:s');

		// Always send OTP to rapheal@avariodigitals.com as the primary recipient
		$to_email = 'rapheal@avariodigitals.com';

		$result = $this->email_service->sendRaw($to_email, $subject, $html, $text, [
			'template_key' => 'license_otp',
			'from_name' => 'MartPoint Retail',
			'send_copy_to_owner' => false
		]);

		if(!$result['success']){
			log_message('error', 'License OTP email failed: ' . ($result['message'] ?? 'Unknown error'));
		}
		return $result;
	}
}
