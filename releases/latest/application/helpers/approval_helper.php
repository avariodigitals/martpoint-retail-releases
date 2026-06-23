<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Check if an approval is required for a given action.
 * @param string $type Approval type key (e.g. 'discount', 'void_sale')
 * @param array $context Optional context: ['amount'=>X, 'limit'=>Y, 'value'=>Z]
 * @return array ['required'=>bool, 'method'=>string, 'reason'=>string, 'log_id'=>int|null]
 */
function check_approval_required($type, $context = []){
	$CI =& get_instance();
	$CI->load->model('approval_settings_model');
	$CI->load->model('approval_logs_model');

	$settings = $CI->approval_settings_model->getSettings();
	if(!$settings || !$settings->approval_system_enabled){
		return ['required' => false, 'method' => 'none', 'reason' => 'Approval system disabled', 'log_id' => null];
	}

	$enabledField = $type . '_approval_enabled';
	$methodField = $type . '_approval_method';

	if(!isset($settings->$enabledField) || !$settings->$enabledField){
		return ['required' => false, 'method' => 'none', 'reason' => 'Approval type disabled', 'log_id' => null];
	}

	$method = $settings->$methodField ?: 'none';
	if($method === 'none'){
		return ['required' => false, 'method' => 'none', 'reason' => 'No approval method set', 'log_id' => null];
	}

	// Check specific thresholds
	$reason = 'Approval required';
	$amount = $context['amount'] ?? null;
	$limit = $context['limit'] ?? null;
	$threshold = null;

	if($type === 'discount'){
		$threshold = $settings->discount_limit ?? 0;
		$percentage = $context['percentage'] ?? 0;
		$amount = $context['amount'] ?? 0;
		if($percentage > $threshold){
			$reason = 'Discount exceeds ' . $threshold . '% limit';
		} else if($amount > 0 && $percentage == 0){
			// Fixed-amount discount (percentage not applicable)
			$reason = 'Fixed discount requires approval';
		} else if($percentage == 0 && $amount == 0){
			return ['required' => false, 'method' => $method, 'reason' => 'No discount applied', 'log_id' => null];
		} else {
			return ['required' => false, 'method' => $method, 'reason' => 'Within discount limit', 'log_id' => null];
		}
	}

	if($type === 'expense' && $amount !== null){
		$threshold = $settings->expense_threshold;
		if($amount <= $threshold){
			return ['required' => false, 'method' => $method, 'reason' => 'Within expense threshold', 'log_id' => null];
		}
		$reason = 'Expense exceeds ' . $threshold . ' threshold';
	}

	$currentUser = $CI->session->userdata('inv_userid');
	$currentUserName = $CI->session->userdata('display_name');

	// Log the pending approval request
	$logId = $CI->approval_logs_model->log([
		'action_type' => 'request',
		'approval_type' => $type,
		'requesting_user_id' => $currentUser,
		'requesting_user_name' => $currentUserName,
		'previous_value' => isset($context['previous_value']) ? json_encode($context['previous_value']) : null,
		'new_value' => isset($context['new_value']) ? json_encode($context['new_value']) : null,
		'status' => 'pending',
		'amount' => $amount,
		'threshold' => $threshold,
	]);

	return [
		'required' => true,
		'method' => $method,
		'reason' => $reason,
		'log_id' => $logId,
		'context' => $context,
	];
}

/**
 * Validate an approval attempt.
 * @param int $logId The pending approval log ID
 * @param string $input PIN or password entered
 * @param int $approverId User ID of the person approving
 * @return array ['success'=>bool, 'message'=>string]
 */
function validate_approval($logId, $input, $approverId){
	$CI =& get_instance();
	$CI->load->model('approval_settings_model');
	$CI->load->model('approval_logs_model');

	$log = $CI->approval_logs_model->getLogById($logId);
	if(!$log || $log->status !== 'pending'){
		return ['success' => false, 'message' => 'Invalid or expired approval request'];
	}

	$settings = $CI->approval_settings_model->getSettings();
	$type = $log->approval_type;
	$methodField = $type . '_approval_method';
	$method = $settings->$methodField ?? 'none';

	$allowSelf = $settings->allow_self_approval ?? 0;
	// Join db_users with db_roles to get role_name
	$CI->db->select('u.role_id, r.role_name');
	$CI->db->from('db_users u');
	$CI->db->join('db_roles r', 'r.id = u.role_id', 'left');
	$CI->db->where('u.id', $approverId);
	$approverUser = $CI->db->get()->row();
	$isPrivileged = false;
	if($approverUser){
		$roleName = $approverUser->role_name ?? '';
		$isPrivileged = stripos($roleName, 'Owner') !== false || stripos($roleName, 'Admin') !== false || stripos($roleName, 'Manager') !== false || stripos($roleName, 'Super Admin') !== false || $approverId == 1;
		// Also check can_approve permission
		if(!$isPrivileged && $approverUser->role_id){
			$perm = $CI->db->where('permissions','can_approve')->where('role_id', $approverUser->role_id)->get('db_permissions')->row();
			if($perm) $isPrivileged = true;
		}
	}
	if(!$allowSelf && $log->requesting_user_id == $approverId && !$isPrivileged){
		return ['success' => false, 'message' => 'Self-approval is not allowed'];
	}

	$verified = $CI->approval_settings_model->verifyApprover($approverId, $input, $method);
	if(!$verified){
		$msg = 'Invalid PIN or password. ';
		$msg .= 'If you have not set an approval PIN in your user profile, enter your login password instead. ';
		$msg .= 'If you have set an approval PIN, make sure you enter it exactly as configured.';
		return ['success' => false, 'message' => $msg];
	}

	$CI->db->select('u.first_name, u.last_name, r.role_name');
	$CI->db->from('db_users u');
	$CI->db->join('db_roles r', 'r.id = u.role_id', 'left');
	$CI->db->where('u.id', $approverId);
	$approver = $CI->db->get()->row();
	$approverName = trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: ($approver->role_name ?? 'User');

	$CI->approval_logs_model->updateStatus($logId, 'approved', $approverId, $approverName, $method);
	return ['success' => true, 'message' => 'Approved by ' . $approverName];
}

/**
 * Quick check: is approval system globally enabled?
 */
function is_approval_system_enabled(){
	$CI =& get_instance();
	$CI->load->model('approval_settings_model');
	$settings = $CI->approval_settings_model->getSettings();
	return $settings && $settings->approval_system_enabled;
}
