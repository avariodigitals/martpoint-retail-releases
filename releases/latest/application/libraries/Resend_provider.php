<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Resend Email Provider Adapter
 * Uses Resend REST API (https://resend.com/docs/api-reference/emails/send-email)
 */
class Resend_provider implements Email_provider_interface {

	protected $CI;
	protected $config;
	protected $apiBase = 'https://api.resend.com/emails';

	public function __construct(array $config = array()){
		$this->CI =& get_instance();
		$this->config = $config;
	}

	/** Mask email for safe logging */
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

	protected function _maskPayloadEmails(array $payload){
		$masked = $payload;
		if(!empty($masked['to']) && is_array($masked['to'])){
			$masked['to'] = array_map([$this, '_maskEmail'], $masked['to']);
		} elseif(!empty($masked['to'])){
			$masked['to'] = $this->_maskEmail($masked['to']);
		}
		if(!empty($masked['from'])){
			$masked['from'] = $this->_maskEmail($masked['from']);
		}
		if(!empty($masked['reply_to'])){
			$masked['reply_to'] = $this->_maskEmail($masked['reply_to']);
		}
		return $masked;
	}

	public function validateConfig(){
		if(empty($this->config['resend_api_key'])){
			return ['configured' => FALSE, 'message' => 'Missing Resend API Key'];
		}
		if(empty($this->config['resend_from_email'])){
			return ['configured' => FALSE, 'message' => 'Missing Resend From Email'];
		}
		return ['configured' => TRUE, 'message' => 'Resend is configured'];
	}

	public function send(array $params){
		$validate = $this->validateConfig();
		if(!$validate['configured']){
			return ['success' => FALSE, 'error' => $validate['message'], 'provider_response' => NULL];
		}

		$payload = [
			'from'    => $this->buildFromAddress($params),
			'to'      => is_array($params['to']) ? $params['to'] : [$params['to']],
			'subject' => $params['subject'],
		];

		if(!empty($params['html'])){
			$payload['html'] = $params['html'];
		}
		if(!empty($params['text'])){
			$payload['text'] = $params['text'];
		}

		// Safety: Resend requires at least html or text
		if(empty($payload['html']) && empty($payload['text'])){
			$payload['text'] = $params['subject'];
		}

		if(!empty($params['replyTo']['email'])){
			$payload['reply_to'] = $params['replyTo']['email'];
		}

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $this->config['resend_api_key']
		];

		$maskedPayload = json_encode($this->_maskPayloadEmails($payload));
		log_message('debug', 'ResendProvider payload: ' . $maskedPayload);

		$ch = curl_init($this->apiBase);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POST           => TRUE,
			CURLOPT_POSTFIELDS     => json_encode($payload),
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_SSL_VERIFYHOST => 2,
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if($curlError){
			return ['success' => FALSE, 'error' => 'cURL Error: ' . $curlError, 'provider_response' => NULL];
		}

		if($httpCode >= 200 && $httpCode < 300){
			$decoded = json_decode($response, TRUE);
			return [
				'success'           => TRUE,
				'error'             => NULL,
				'provider_response' => $decoded
			];
		}

		$decoded = json_decode($response, TRUE);
		$errorMsg = isset($decoded['message']) ? $decoded['message'] : ('HTTP ' . $httpCode . ': ' . $response);
		return [
			'success'           => FALSE,
			'error'             => $errorMsg,
			'provider_response' => $decoded
		];
	}

	protected function buildFromAddress(array $params){
		$email = !empty($params['from']['email'])
			? $params['from']['email']
			: $this->config['resend_from_email'];

		$name = !empty($params['from']['name'])
			? $params['from']['name']
			: (!empty($this->config['resend_from_name']) ? $this->config['resend_from_name'] : '');

		if(!empty($name)){
			return $name . ' <' . $email . '>';
		}
		return $email;
	}
}
