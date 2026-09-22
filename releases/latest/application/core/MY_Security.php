<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Security — returns a JSON response instead of the HTML "The action you
 * have requested is not allowed." page when a fetch/AJAX request fails CSRF
 * verification, so the app can show a plain-English message instead of the
 * browser's JSON parse error.
 */
class MY_Security extends CI_Security {

	public function csrf_show_error()
	{
		$ctype  = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
		$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		$ajax   = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

		if($ajax || strpos($ctype, 'application/json') !== false || strpos($accept, 'application/json') !== false || isset($_POST['is_ajax'])){
			log_message('error', 'CSRF check failed for '.$_SERVER['REQUEST_METHOD'].' '.(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));
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
