<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Provider Interface
 * All email providers must implement this.
 */
interface Email_provider_interface {

	/**
	 * Send an email
	 *
	 * @param array $params [
	 *   'to'      => string|array,
	 *   'subject' => string,
	 *   'html'    => string,
	 *   'text'    => string (optional),
	 *   'from'    => ['email' => ..., 'name' => ...],
	 *   'replyTo' => ['email' => ..., 'name' => ...] (optional)
	 * ]
	 * @return array ['success' => bool, 'error' => string|null, 'provider_response' => mixed]
	 */
	public function send(array $params);

	/**
	 * Validate that provider is configured
	 * @return array ['configured' => bool, 'message' => string]
	 */
	public function validateConfig();
}
