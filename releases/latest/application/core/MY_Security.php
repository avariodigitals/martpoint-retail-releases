<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Security
 *
 * Two extensions to the stock CI3 behaviour:
 *
 * 1. csrf_verify() also accepts the CSRF token from JSON request bodies and
 *    request headers. PHP never populates $_POST for application/json
 *    payloads, so legitimate fetch()/JSON calls (which already carry the
 *    token in the body or a header) were failing verification on every
 *    request — presenting to users as a security error that no amount of
 *    reloading could fix. It also means the check no longer depends on the
 *    install-specific csrf_exclude_uris list in config.php (which is not
 *    shipped with updates).
 *
 * 2. csrf_show_error() returns a JSON response for AJAX/JSON requests so the
 *    front-end can show a plain-English message instead of a JSON parse
 *    error over an HTML error page.
 */
class MY_Security extends CI_Security {

	public function csrf_verify()
	{
		// If it's not a POST request we will set the CSRF cookie
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
		{
			return $this->csrf_set_cookie();
		}

		// Check if URI has been whitelisted from CSRF checks
		if ($exclude_uris = config_item('csrf_exclude_uris'))
		{
			$uri = load_class('URI', 'core');
			foreach ($exclude_uris as $excluded)
			{
				if (preg_match('#^'.$excluded.'$#i'.(UTF8_ENABLED ? 'u' : ''), $uri->uri_string()))
				{
					return $this;
				}
			}
		}

		$posted = isset($_POST[$this->_csrf_token_name]) ? $_POST[$this->_csrf_token_name] : NULL;

		if ($posted === NULL)
		{
			// application/json bodies never reach $_POST — read the token from
			// the payload or from the headers the app already sends.
			$ctype = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
			if (strpos($ctype, 'application/json') !== FALSE)
			{
				$json = json_decode(file_get_contents('php://input'), TRUE);
				if (is_array($json) && isset($json[$this->_csrf_token_name]))
				{
					$posted = $json[$this->_csrf_token_name];
				}
			}
			if ($posted === NULL)
			{
				$hdr = 'HTTP_'.strtoupper(str_replace('-', '_', $this->_csrf_token_name));
				foreach (array($hdr, 'HTTP_X_CSRF_TOKEN', 'HTTP_X_XSRF_TOKEN') as $h)
				{
					if (!empty($_SERVER[$h]))
					{
						$posted = $_SERVER[$h];
						break;
					}
				}
			}
		}

		// Check CSRF token validity, but don't error on mismatch just yet - we'll want to regenerate
		$valid = $posted !== NULL
			&& isset($_COOKIE[$this->_csrf_cookie_name])
			&& hash_equals($posted, $_COOKIE[$this->_csrf_cookie_name]);

		// We kill this since we're done and we don't want to pollute the _POST array
		unset($_POST[$this->_csrf_token_name]);

		// Regenerate on every submission?
		if (config_item('csrf_regenerate'))
		{
			// Nothing should last forever
			unset($_COOKIE[$this->_csrf_cookie_name]);
			$this->_csrf_hash = NULL;
		}

		$this->_csrf_set_hash();
		$this->csrf_set_cookie();

		if ($valid !== TRUE)
		{
			log_message('error', 'CSRF check failed for '.$_SERVER['REQUEST_METHOD'].' '
				.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')
				.' (token '.($posted === NULL ? 'absent' : 'present').', cookie '
				.(isset($_COOKIE[$this->_csrf_cookie_name]) ? 'present' : 'absent').')');
			$this->csrf_show_error();
		}

		log_message('info', 'CSRF token verified');
		return $this;
	}

	public function csrf_show_error()
	{
		$ctype  = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
		$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		$ajax   = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

		if($ajax || strpos($ctype, 'application/json') !== false || strpos($accept, 'application/json') !== false || isset($_POST['is_ajax'])){
			header('Content-Type: application/json');
			set_status_header(403);
			echo json_encode(array(
				'status'  => 'error',
				'code'    => 'csrf',
				'message' => 'Your security check has expired. Please reload the page and try again.'
			));
			exit;
		}

		parent::csrf_show_error();
	}
}
