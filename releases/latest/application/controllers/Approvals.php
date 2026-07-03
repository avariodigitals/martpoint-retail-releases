<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approvals extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!mp_feature_enabled('manager_approvals')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('approval_settings_model');
		$this->load->model('approval_logs_model');
		$this->load->helper('approval');
	}

	// ============== SETTINGS ==============

	public function settings(){
		if(!$this->permissions('approval_settings_edit') && !is_admin() && !is_store_admin()){
			show_404(); exit;
		}
		$storeId = get_current_store_id();
		$settings = $this->approval_settings_model->getSettings($storeId);
		$approvers = $this->getApproverUsers($storeId);
		$data = array_merge($this->data, [
			'page_title' => 'Security & Approvals',
			'settings' => $settings,
			'approval_types' => $this->approval_settings_model->getApprovalTypes(),
			'approval_methods' => $this->approval_settings_model->getApprovalMethods(), // keep for backward compat
			'approver_users' => $approvers,
		]);
		$this->load->view('approvals/settings', $data);
	}

	private function getApproverUsers($storeId){
		$this->db->select('u.id, u.first_name, u.last_name, u.username, u.role_id, r.role_name');
		$this->db->from('db_users u');
		$this->db->join('db_roles r', 'r.id = u.role_id', 'left');
		$this->db->where('u.store_id', $storeId);
		$this->db->where('u.status', 1);
		$users = $this->db->get()->result();
		$approvers = [['id'=>'none','name'=>'No Approval','role'=>'']];
		foreach($users as $user){
			$canApprove = false;
			$roleName = $user->role_name ?? '';
			if($user->id == 1 || stripos($roleName, 'Admin') !== false || stripos($roleName, 'Owner') !== false || stripos($roleName, 'Manager') !== false || stripos($roleName, 'Super Admin') !== false){
				$canApprove = true;
			}
			if(!$canApprove && $user->role_id){
				$perm = $this->db->where('permissions','can_approve')->where('role_id', $user->role_id)->get('db_permissions')->row();
				if($perm) $canApprove = true;
			}
			if($canApprove){
				$name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username;
				$approvers[] = ['id' => $user->id, 'name' => $name, 'role' => $roleName];
			}
		}
		return $approvers;
	}

	public function save_settings(){
		if(!$this->permissions('approval_settings_edit') && !is_admin() && !is_store_admin()){
			echo json_encode(['status'=>'error','message'=>'Access denied']); return;
		}
		$storeId = get_current_store_id();
		$post = $this->input->post();

		$save = [
			'approval_system_enabled' => !empty($post['approval_system_enabled']) ? 1 : 0,
			'business_control_mode' => $post['business_control_mode'] ?? 'simple',
			'allow_self_approval' => !empty($post['allow_self_approval']) ? 1 : 0,
			'discount_limit' => (float)($post['discount_limit'] ?? 0),
			'expense_threshold' => (float)($post['expense_threshold'] ?? 0),
		];

		$types = $this->approval_settings_model->getApprovalTypes();
		foreach($types as $key => $label){
			$save[$key.'_approval_enabled'] = !empty($post[$key.'_enabled']) ? 1 : 0;
			$methodVal = $post[$key.'_method'] ?? 'none';
			if(is_array($methodVal)){
				$methodVal = implode(',', $methodVal);
			}
			$save[$key.'_approval_method'] = $methodVal ?: 'none';
		}

		$this->approval_settings_model->saveSettings($storeId, $save);
		$this->session->set_flashdata('success', 'Approval settings saved');
		echo json_encode(['status'=>'success']);
	}

	// ============== AJAX APPROVAL ==============

	public function check_ajax(){
		$this->load->helper('approval');
		$type = $this->input->post('type');
		$context = json_decode($this->input->post('context') ?: '{}', true);
		$result = check_approval_required($type, $context);
		echo json_encode($result);
	}

	public function get_approvers(){
		$storeId = get_current_store_id();
		$currentUserId = (int)$this->session->userdata('inv_userid');

		// Join db_users with db_roles to get role_name
		$this->db->select('u.id, u.first_name, u.last_name, u.username, u.role_id, r.role_name');
		$this->db->from('db_users u');
		$this->db->join('db_roles r', 'r.id = u.role_id', 'left');
		$this->db->where('u.store_id', $storeId);
		$this->db->where('u.status', 1);
		$users = $this->db->get()->result();

		$approvers = [];
		foreach($users as $user){
			// Check if user has can_approve permission or is admin/manager/owner
			$canApprove = false;
			$roleName = $user->role_name ?? '';
			if($user->id == 1 || stripos($roleName, 'Admin') !== false || stripos($roleName, 'Owner') !== false || stripos($roleName, 'Manager') !== false || stripos($roleName, 'Super Admin') !== false){
				$canApprove = true;
			}
			if(!$canApprove && $user->role_id){
				$perm = $this->db->where('permissions','can_approve')->where('role_id', $user->role_id)->get('db_permissions')->row();
				if($perm) $canApprove = true;
			}
			if($canApprove){
				$approvers[] = [
					'id' => $user->id,
					'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
					'role' => $roleName
				];
			}
		}
		// Fallback: if no explicit approvers found, return all active users so approval isn't completely blocked
		if(empty($approvers) && !empty($users)){
			foreach($users as $user){
				$roleName = $user->role_name ?? '';
				$approvers[] = [
					'id' => $user->id,
					'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
					'role' => $roleName
				];
			}
		}
		// Always include current user in the list so they can self-approve if allowed
		$currentUserInList = false;
		foreach($approvers as $a){ if($a['id'] == $currentUserId){ $currentUserInList = true; break; } }
		if(!$currentUserInList && $currentUserId > 0){
			$cur = $this->db->select('u.first_name, u.last_name, u.username, r.role_name')
				->from('db_users u')
				->join('db_roles r', 'r.id = u.role_id', 'left')
				->where('u.id', $currentUserId)
				->get()->row();
			if($cur){
				$approvers[] = [
					'id' => $currentUserId,
					'name' => trim(($cur->first_name ?? '') . ' ' . ($cur->last_name ?? '')) ?: $cur->username,
					'role' => $cur->role_name ?? 'User'
				];
			}
		}
		echo json_encode(['approvers' => $approvers]);
	}

	public function validate(){
		$logId = (int)$this->input->post('log_id');
		$input = trim($this->input->post('input'));
		$approverId = (int)$this->input->post('approver_id');

		if(!$approverId){
			$approverId = (int)$this->session->userdata('inv_userid');
		}

		if(!$approverId){
			echo json_encode(['success'=>false,'message'=>'Not logged in']); return;
		}

		$result = validate_approval($logId, $input, $approverId);
		echo json_encode($result);
	}

	// ============== LOGS ==============

	public function logs(){
		if(!$this->permissions('approval_logs_view') && !is_admin() && !is_store_admin()){
			show_404(); exit;
		}
		$storeId = get_current_store_id();
		$filters = [
			'store_id' => $storeId,
			'approval_type' => $this->input->get('type') ?: null,
			'status' => $this->input->get('status') ?: null,
			'date_from' => $this->input->get('date_from') ?: null,
			'date_to' => $this->input->get('date_to') ?: null,
		];
		$page = max(1, (int)$this->input->get('page'));
		$limit = 25;
		$offset = ($page - 1) * $limit;
		$report = $this->approval_logs_model->getLogs($filters, $limit, $offset);

		$data = array_merge($this->data, [
			'page_title' => 'Approval Logs',
			'logs' => $report['rows'],
			'total' => $report['total'],
			'page' => $page,
			'pages' => ceil($report['total'] / $limit),
			'filters' => $filters,
			'approval_types' => $this->approval_settings_model->getApprovalTypes(),
		]);
		$this->load->view('approvals/logs', $data);
	}
}
