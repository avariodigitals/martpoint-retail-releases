<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SMTP Email Provider Adapter
 * Wraps CI3 Email library with proper config handling.
 */
class Smtp_provider implements Email_provider_interface {

	protected $CI;
	protected $config;

	public function __construct(array $config = array()){
		$this->CI =& get_instance();
		$this->config = $config;
	}

	public function validateConfig(){
		$required = ['smtp_host','smtp_port','smtp_user','smtp_pass'];
		foreach($required as $key){
			if(empty($this->config[$key])){
				return ['configured' => FALSE, 'message' => 'Missing SMTP '.str_replace('smtp_','',$key)];
			}
		}
		return ['configured' => TRUE, 'message' => 'SMTP is configured'];
	}

	public function send(array $params){
		$validate = $this->validateConfig();
		if(!$validate['configured']){
			return ['success' => FALSE, 'error' => $validate['message'], 'provider_response' => NULL];
		}

		// Fix HELO hostname for Exim servers that reject 'localhost'
		$hostname = 'localhost';
		if(!empty($this->config['smtp_user']) && strpos($this->config['smtp_user'], '@') !== FALSE){
			$parts = explode('@', $this->config['smtp_user']);
			$hostname = $parts[1];
		}
		$_SERVER['SERVER_NAME'] = $hostname;

		// Determine crypto based on port/settings
		$crypto = '';
		if(isset($this->config['smtp_crypto']) && in_array($this->config['smtp_crypto'], ['ssl','tls'])){
			$crypto = $this->config['smtp_crypto'];
		} elseif((int)$this->config['smtp_port'] === 465){
			$crypto = 'ssl';
		} elseif((int)$this->config['smtp_port'] === 587){
			$crypto = 'tls';
		}

		$emailConfig = array(
			'protocol'  => 'smtp',
			'smtp_host' => $this->config['smtp_host'],
			'smtp_port' => (int)$this->config['smtp_port'],
			'smtp_user' => $this->config['smtp_user'],
			'smtp_pass' => $this->config['smtp_pass'],
			'smtp_crypto' => $crypto,
			'mailtype'  => 'html',
			'charset'   => 'utf-8',
			'wordwrap'  => TRUE,
			'crlf'      => "\r\n",
			'newline'   => "\r\n"
		);

		// Load / reconfigure CI Email library
		if(!isset($this->CI->email)){
			$this->CI->load->library('email');
		}
		$this->CI->email->initialize($emailConfig);

		// From
		$fromEmail = !empty($params['from']['email']) ? $params['from']['email'] : $this->config['smtp_user'];
		$fromName  = !empty($params['from']['name'])  ? $params['from']['name']  : ($this->config['from_name'] ?? '');
		$this->CI->email->from($fromEmail, $fromName);

		// To
		$to = is_array($params['to']) ? implode(',', $params['to']) : $params['to'];
		$this->CI->email->to($to);

		// Reply-To
		if(!empty($params['replyTo']['email'])){
			$replyName = !empty($params['replyTo']['name']) ? $params['replyTo']['name'] : '';
			$this->CI->email->reply_to($params['replyTo']['email'], $replyName);
		}

		// Subject & Message
		$this->CI->email->subject($params['subject']);
		$this->CI->email->message($params['html']);
		if(!empty($params['text'])){
			$this->CI->email->set_alt_message($params['text']);
		}

		// Send
		if($this->CI->email->send()){
			return ['success' => TRUE, 'error' => NULL, 'provider_response' => 'SMTP_SENT'];
		} else {
			$error = $this->CI->email->print_debugger();
			$error = !empty($error) ? strip_tags($error) : 'SMTP send failed';
			return ['success' => FALSE, 'error' => $error, 'provider_response' => NULL];
		}
	}
}
