<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_settings_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function getSettings($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(!$this->db->table_exists('db_approval_settings')){
			return (object)$this->buildDefaultArray($storeId);
		}
		$query = $this->db->where('store_id', $storeId)->get('db_approval_settings');
		if($query === false){
			return (object)$this->buildDefaultArray($storeId);
		}
		$row = $query->row();
		if(!$row){
			$row = $this->createDefault($storeId);
		}
		return $row;
	}

	private function buildDefaultArray($storeId){
		$defaults = [
			'store_id' => $storeId,
			'approval_system_enabled' => 0,
			'business_control_mode' => 'simple',
			'allow_self_approval' => 1,
		];
		$types = $this->getApprovalTypes();
		foreach($types as $key => $label){
			$defaults[$key.'_approval_enabled'] = 0;
			$defaults[$key.'_approval_method'] = 'none';
		}
		$defaults['discount_limit'] = 0;
		$defaults['expense_threshold'] = 0;
		return $defaults;
	}

	public function createDefault($storeId){
		$defaults = $this->buildDefaultArray($storeId);
		if($this->db->table_exists('db_approval_settings')){
			$this->db->insert('db_approval_settings', $defaults);
		}
		return (object)$defaults;
	}

	public function saveSettings($storeId, $data){
		if(!$this->db->table_exists('db_approval_settings')){
			return false;
		}
		$exists = $this->db->where('store_id', $storeId)->count_all_results('db_approval_settings');
		if($exists){
			$this->db->where('store_id', $storeId);
			return $this->db->update('db_approval_settings', $data);
		}
		$data['store_id'] = $storeId;
		return $this->db->insert('db_approval_settings', $data);
	}

	public function getApprovalTypes(){
		return [
			'discount' => 'Discount Approval',
			'price_override' => 'Price Override Approval',
			'void_sale' => 'Sale Cancellation / Void',
			'sale_return' => 'Sale Return',
			'edit_completed_sale' => 'Edit Completed Sale',
			'credit_sale' => 'Credit Sale',
			'credit_limit_override' => 'Customer Credit Limit Override',
			'customer_balance_adjustment' => 'Customer Balance Adjustment',
			'negative_stock_sale' => 'Negative Stock Sale',
			'stock_adjustment' => 'Stock Adjustment',
			'inventory_transfer' => 'Inventory Transfer',
			'cost_price_change' => 'Cost Price Change',
			'selling_price_change' => 'Selling Price Change',
			'expense' => 'Expense Approval',
			'cash_variance' => 'Cash Variance',
			'reopen_shift' => 'Reopen Closed Shift',
			'online_refund' => 'Online Refund',
			'cancel_online_order' => 'Cancel Paid Online Order',
			'manual_payment_confirmation' => 'Manual Payment Confirmation',
			'purchase' => 'Purchase Approval',
			'purchase_price_override' => 'Purchase Price Override',
			'hold_delete' => 'Delete Hold Invoice',
		];
	}

	public function getApprovalMethods(){
		return [
			'none' => 'No Approval',
			'manager_pin' => 'Manager PIN',
			'manager_password' => 'Manager Password',
			'owner_pin' => 'Owner PIN',
			'owner_password' => 'Owner Password',
			'either' => 'Either Manager or Owner',
		];
	}

	public function canApprove($userId, $requiredRoles = ['Owner','Admin','Manager','Super Admin']){
		$this->db->select('u.*, r.role_name');
		$this->db->from('db_users u');
		$this->db->join('db_roles r', 'r.id = u.role_id', 'left');
		$this->db->where('u.id', $userId);
		$user = $this->db->get()->row();
		if(!$user) return false;
		$role = trim($user->role_name ?? '');
		foreach($requiredRoles as $r){
			if(stripos($role, $r) !== false) return true;
		}
		return false;
	}

	public function verifyApprover($approverId, $input, $allowedIds){
		if(!$approverId || !$input || !$allowedIds || $allowedIds === 'none') return false;
		
		// $allowedIds is now a comma-separated string of allowed approver user IDs
		$allowedList = array_map('trim', explode(',', $allowedIds));
		// Detect legacy mode: if any item is not numeric, it's a legacy method string (e.g. 'manager_pin')
		$numericIds = array_filter($allowedList, function($v){ return ctype_digit((string)$v); });
		$isLegacy = count($numericIds) === 0 || count($numericIds) !== count($allowedList);
		
		// Only enforce allowed list check when using new user-ID-based configuration
		if(!$isLegacy && !in_array((string)$approverId, $allowedList) && !in_array($approverId, $allowedList)){
			return false; // Selected approver is not in the allowed list
		}
		
		// Join db_users with db_roles to get role_name
		$this->db->select('u.*, r.role_name');
		$this->db->from('db_users u');
		$this->db->join('db_roles r', 'r.id = u.role_id', 'left');
		$this->db->where('u.id', $approverId);
		$user = $this->db->get()->row();
		if(!$user) return false;

		// Check if user has can_approve permission or is admin/owner/manager by role
		$roleId = $user->role_id ?? 0;
		$roleName = $user->role_name ?? '';
		$canApprove = false;
		if($approverId == 1 || stripos($roleName, 'Admin') !== false || stripos($roleName, 'Owner') !== false || stripos($roleName, 'Manager') !== false || stripos($roleName, 'Super Admin') !== false){
			$canApprove = true;
		}
		// Also check db_permissions table
		if(!$canApprove && $roleId){
			$perm = $this->db->where('permissions','can_approve')->where('role_id',$roleId)->get('db_permissions')->row();
			$canApprove = (bool)$perm;
		}

		if(!$canApprove) return false;

		// Always verify against the user's approval_pin (or password fallback)
		$pinHash = !empty($user->approval_pin) ? $user->approval_pin : $user->password;
		return md5($input) === $pinHash;
	}
}
