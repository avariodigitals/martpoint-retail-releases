<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session_lock
 *
 * AJAX endpoints for the inactivity warning + snooze (screen lock) feature.
 * The PHP session stays alive while the screen is snoozed — these endpoints
 * simply verify the returning user's password or approval PIN and keep the
 * session warm.
 */
class Session_lock extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/**
	 * POST: verify the current user's password or approval PIN to resume
	 * a snoozed session.
	 */
	public function verify(){
		header('Content-Type: application/json');

		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){
			echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.', 'expired' => true]);
			return;
		}

		$secret = (string)$this->input->post('secret');
		if($secret === ''){
			echo json_encode(['status' => 'error', 'message' => 'Please enter your password or PIN.']);
			return;
		}

		$user = $this->db->select('id,password,approval_pin')->where('id', $userId)->get('db_users')->row();
		if(!$user){
			echo json_encode(['status' => 'error', 'message' => 'User not found.', 'expired' => true]);
			return;
		}

		$ok = false;
		if(!empty($user->approval_pin) && password_verify($secret, $user->approval_pin)){
			$ok = true;
		} elseif(password_verify($secret, $user->password)){
			$ok = true;
		} elseif($user->password === md5($secret)){ // legacy md5 passwords
			$ok = true;
			$this->db->where('id', $userId)->update('db_users', ['password' => password_hash($secret, PASSWORD_BCRYPT)]);
		}

		if($ok){
			// Touch the session so it does not expire while the user is active
			$this->session->set_userdata('idle_unlocked_at', time());
			echo json_encode(['status' => 'success']);
		} else {
			log_message('error', 'Session_lock: unlock failed for user_id=' . $userId);
			echo json_encode(['status' => 'error', 'message' => 'Incorrect password or PIN. Try again.']);
		}
	}

	/**
	 * GET: lightweight keep-alive ping while the screen is snoozed so the
	 * session and store context stay warm.
	 */
	public function ping(){
		header('Content-Type: application/json');
		echo json_encode(['status' => 'success', 'ts' => time()]);
	}
}
