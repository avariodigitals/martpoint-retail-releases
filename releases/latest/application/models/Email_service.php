<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Central Email Service
 * All outgoing emails must pass through here.
 */
class Email_service extends CI_Model {

	protected $providers = ['smtp' => 'Smtp_provider', 'resend' => 'Resend_provider'];
	protected $storeId;

	/** Mask an email address for safe logging */
	protected function _maskEmail($email){
		if(empty($email) || !strpos($email, '@')) return $email;
		list($local, $domain) = explode('@', $email, 2);
		$maskLocal = strlen($local) <= 2 ? $local : substr($local, 0, 1) . str_repeat('*', strlen($local) - 2) . substr($local, -1);
		$domainParts = explode('.', $domain);
		$ext = array_pop($domainParts);
		$dom = implode('.', $domainParts);
		$maskDom = strlen($dom) <= 2 ? $dom : substr($dom, 0, 1) . str_repeat('*', strlen($dom) - 2) . substr($dom, -1);
		return $maskLocal . '@' . $maskDom . '.' . $ext;
	}

	protected function _maskEmails($recipients){
		if(is_array($recipients)){
			return implode(',', array_map([$this, '_maskEmail'], $recipients));
		}
		return $this->_maskEmail($recipients);
	}

	public function __construct(){
		parent::__construct();
		$this->load->model('email_settings_model');
		$this->load->model('email_template_model');
		$this->load->model('email_log_model');
		$this->storeId = get_current_store_id();
	}

	/**
	 * Send an email using a template
	 *
	 * @param string $templateKey   e.g. 'invoice_sent'
	 * @param string|array $to       Recipient email(s)
	 * @param array  $data           Placeholder data
	 * @param array  $options        ['cc','bcc','reply_to','attachments']
	 * @return array ['success' => bool, 'message' => string, 'log_id' => int|null]
	 */
	public function sendTemplate($templateKey, $to, array $data = [], array $options = []){
		$template = $this->email_template_model->getByKey($templateKey, $this->storeId);

		if(!$template){
			return [
				'success' => FALSE,
				'message' => "Template '{$templateKey}' not found.",
				'log_id'  => $this->_logFailure($templateKey, $to, 'Template not found', NULL, $data)
			];
		}

		if(!(int)$template->status){
			return [
				'success' => TRUE,
				'message' => "Template '{$templateKey}' is disabled. Skipped.",
				'log_id'  => NULL
			];
		}

		// Parse placeholders
		$CI =& get_instance();
		$CI->load->library('email_template_parser');
		$parser = $CI->email_template_parser;
		$subject = $parser->parse($template->subject, $data);
		$html    = $parser->parse($template->html_body, $data);
		$text    = !empty($template->text_body) ? $parser->parse($template->text_body, $data) : '';

		// Detect missing placeholders
		$missing = $parser->getMissing();
		if(!empty($missing)){
			log_message('debug', 'EmailService: Missing placeholders in template ' . $templateKey . ': ' . implode(', ', $missing));
		}

		// Merge options
		$sendOptions = array_merge([
			'template_key' => $templateKey,
			'send_copy_to_owner' => (bool)$template->send_copy_to_owner
		], $options);

		$result = $this->sendRaw($to, $subject, $html, $text, $sendOptions);

		return $result;
	}

	/**
	 * Send a raw email through the active provider
	 */
	public function sendRaw($to, $subject, $html, $text = '', array $options = []){
		$settings = $this->email_settings_model->getSettings($this->storeId);

		if(empty($settings['provider'])){
			$msg = 'No email provider configured.';
			return [
				'success' => FALSE,
				'message' => $msg,
				'log_id'  => $this->_logFailure($options['template_key'] ?? 'raw', $to, $msg, NULL, $options)
			];
		}

		$providerKey = $settings['provider'];
		if(!isset($this->providers[$providerKey])){
			$msg = "Unknown provider: {$providerKey}";
			return [
				'success' => FALSE,
				'message' => $msg,
				'log_id'  => $this->_logFailure($options['template_key'] ?? 'raw', $to, $msg, NULL, $options)
			];
		}

		// Build provider config
		$providerConfig = $this->email_settings_model->getProviderConfig($providerKey, $this->storeId);
		$providerClass = $this->providers[$providerKey];
		$CI =& get_instance();
		require_once APPPATH . 'libraries/Email_provider_interface.php';
		$CI->load->library($providerClass, $providerConfig, 'email_provider');
		$provider = $CI->email_provider;

		// Validate config
		$validate = $provider->validateConfig();
		if(!$validate['configured']){
			return [
				'success' => FALSE,
				'message' => $validate['message'],
				'log_id'  => $this->_logFailure($options['template_key'] ?? 'raw', $to, $validate['message'], $providerKey, $options)
			];
		}

		// Build from / reply-to
		$from = [
			'email' => !empty($settings['from_email']) ? $settings['from_email'] : ($providerConfig['smtp_user'] ?? $providerConfig['resend_from_email'] ?? ''),
			'name'  => !empty($settings['from_name'])  ? $settings['from_name']  : ''
		];
		$replyTo = [
			'email' => !empty($settings['reply_to']) ? $settings['reply_to'] : $from['email'],
			'name'  => $from['name']
		];

		if(!empty($options['from_email'])){ $from['email'] = $options['from_email']; }
		if(!empty($options['from_name'])) { $from['name']  = $options['from_name']; }
		if(!empty($options['reply_to']))  { $replyTo['email'] = $options['reply_to']; }

		$params = [
			'to'      => $to,
			'subject' => $subject,
			'html'    => $html,
			'text'    => $text,
			'from'    => $from,
			'replyTo' => $replyTo
		];

		// Send
		$result = $provider->send($params);

		if($result['success']){
			$logId = $this->_logSuccess(
				$options['template_key'] ?? 'raw',
				$providerKey,
				$to,
				$subject,
				$result['provider_response'],
				$options
			);

			// Send copy to owner if configured
			if(!empty($options['send_copy_to_owner']) && !empty($settings['from_email'])){
				$copyParams = $params;
				$copyParams['to'] = $settings['from_email'];
				$copyParams['subject'] = '[COPY] ' . $subject;
				$provider->send($copyParams);
			}

			return ['success' => TRUE, 'message' => 'Email sent successfully.', 'log_id' => $logId];
		} else {
			$logId = $this->_logFailure(
				$options['template_key'] ?? 'raw',
				$to,
				$result['error'],
				$providerKey,
				$options
			);
			return ['success' => FALSE, 'message' => $result['error'], 'log_id' => $logId];
		}
	}

	/**
	 * Test the active provider by sending a test email
	 */
	public function testProvider($testEmail, $providerKey = NULL){
		if(empty($providerKey)){
			$settings = $this->email_settings_model->getSettings($this->storeId);
			$providerKey = $settings['provider'];
		}

		if(empty($providerKey)){
			return ['success' => FALSE, 'message' => 'No email provider configured.'];
		}

		$providerClass = $this->providers[$providerKey];
		$providerConfig = $this->email_settings_model->getProviderConfig($providerKey, $this->storeId);
		$CI =& get_instance();
		require_once APPPATH . 'libraries/Email_provider_interface.php';
		$CI->load->library($providerClass, $providerConfig, 'email_provider');
		$provider = $CI->email_provider;

		$validate = $provider->validateConfig();
		if(!$validate['configured']){
			return ['success' => FALSE, 'message' => $validate['message']];
		}

		$settings = $this->email_settings_model->getSettings($this->storeId);
		$from = [
			'email' => !empty($settings['from_email']) ? $settings['from_email'] : ($providerConfig['smtp_user'] ?? $providerConfig['resend_from_email'] ?? ''),
			'name'  => !empty($settings['from_name']) ? $settings['from_name'] : 'MartPoint Retail'
		];

		$params = [
			'to'      => $testEmail,
			'subject' => 'MartPoint Test Email via ' . strtoupper($providerKey),
			'html'    => '<h2>Test Email</h2><p>This is a test email from MartPoint Retail using ' . strtoupper($providerKey) . '.</p><p>If you received this, the email provider is working correctly!</p><hr><p style="color:#888;font-size:12px;">Sent at: ' . date('Y-m-d H:i:s') . '</p>',
			'text'    => "Test Email\n\nThis is a test email from MartPoint Retail using " . strtoupper($providerKey) . ".\n\nSent at: " . date('Y-m-d H:i:s'),
			'from'    => $from,
			'replyTo' => $from
		];

		$result = $provider->send($params);

		if($result['success']){
			$this->_logSuccess('smtp_test_email', $providerKey, $testEmail, $params['subject'], $result['provider_response'], []);
			return ['success' => TRUE, 'message' => 'Test email sent successfully via ' . strtoupper($providerKey) . '.'];
		} else {
			$this->_logFailure('smtp_test_email', $testEmail, $result['error'], $providerKey, []);
			return ['success' => FALSE, 'message' => $result['error']];
		}
	}

	/* ---------- Logging helpers ---------- */

	protected function _logSuccess($templateKey, $provider, $recipient, $subject, $response, $options){
		return $this->email_log_model->create([
			'email_type'       => $templateKey,
			'provider_used'    => $provider,
			'recipient'        => $this->_maskEmails($recipient),
			'subject'          => $subject,
			'status'           => 'sent',
			'error_message'    => NULL,
			'triggered_by'     => $this->session->userdata('username') ?? 'system',
			'related_module'   => $options['related_module'] ?? NULL,
			'related_record_id'=> $options['related_record_id'] ?? NULL,
			'provider_response'=> json_encode($response),
			'store_id'         => $this->storeId
		]);
	}

	protected function _logFailure($templateKey, $recipient, $error, $provider, $options){
		return $this->email_log_model->create([
			'email_type'       => $templateKey,
			'provider_used'    => $provider ?? 'none',
			'recipient'        => $this->_maskEmails($recipient),
			'subject'          => $options['subject'] ?? '',
			'status'           => 'failed',
			'error_message'    => $error,
			'triggered_by'     => $this->session->userdata('username') ?? 'system',
			'related_module'   => $options['related_module'] ?? NULL,
			'related_record_id'=> $options['related_record_id'] ?? NULL,
			'store_id'         => $this->storeId
		]);
	}
}
