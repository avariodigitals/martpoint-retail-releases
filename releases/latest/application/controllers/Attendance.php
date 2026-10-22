<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('attendance_model');
		$this->load->model('users_model');
	}

	private function _can_edit(){
		return $this->permissions('attendance_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	private function _can_view(){
		return $this->permissions('attendance_view') || $this->permissions('attendance_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	private function _building(){
		return (mp_get_store_profile()['industry_type'] ?? '') === 'building_materials';
	}

	/** Self-service uses the signed-in member, never a submitted staff/store ID. */
	public function workspace(){
		if(!$this->_building()){show_404();return;}
		$storeId = (int)get_current_store_id();
		$userId = (int)$this->session->userdata('inv_userid');
		$member = $this->db->where('id',$userId)->where('store_id',$storeId)->where('status',1)->get('db_users')->row();
		if(!$member){show_error('Active store membership is required.',403);return;}
		$this->load->helper('building_workspace');
		$record = $this->attendance_model->getAttendanceRecord($userId,date('Y-m-d'),$storeId);
		$history = $this->db->where('store_id',$storeId)->where('user_id',$userId)->order_by('id','desc')->limit(14)->get('db_attendance')->result();
		$data = array_merge($this->data,['page_title'=>'My attendance','attendance_member'=>$member,'attendance_record'=>$record,'attendance_history'=>$history,'attendance_shifts'=>$this->attendance_model->getShiftsByUser($userId,$storeId),'can_attendance_report'=>$this->_can_view(),'can_attendance_edit'=>$this->_can_edit()]);
		bm_workspace_render($data,'building_materials/attendance');
	}

	private function _buildingClockAction($action){
		if($this->input->method() !== 'post'){
			$this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Use POST to record attendance.']));return;
		}
		// Legacy clock endpoints are globally CSRF-exempt for older camera clients.
		// Require a matching token for this industry without changing those clients.
		$token = $this->input->post($this->security->get_csrf_token_name());
		$cookie = $this->input->cookie($this->config->item('cookie_prefix').$this->config->item('csrf_cookie_name'));
		if(!is_string($token) || !is_string($cookie) || !hash_equals($cookie,$token)){
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status'=>'error','message'=>'Refresh the page and try again.']));return;
		}
		$storeId = (int)get_current_store_id();
		$userId = (int)$this->session->userdata('inv_userid');
		$password = $this->input->post('password');
		// New workspace popup requires the account password. Retain the earlier
		// PIN client for compatibility; neither path accepts a photo as identity.
		if($password !== null){
			$member = $this->db->select('password')->where('id',$userId)->where('store_id',$storeId)->where('status',1)->get('db_users')->row();
			$valid = is_string($password) && $password !== '' && strlen($password) <= 255 && $member &&
				(password_verify($password,$member->password) || hash_equals($member->password,md5($password)));
		} else {
			$valid = $this->_verifyUserPin($userId,(string)$this->input->post('pin'),$storeId);
		}
		if(!$valid){
			$result = ['status'=>'error','message'=>$password !== null ? 'Enter your valid account password.' : 'Enter your valid attendance PIN or account password.'];
		} else {
			$result = $this->attendance_model->buildingClock($storeId,$userId,$action);
		}
		$result['csrf_hash'] = $this->security->get_csrf_hash();
		$this->output->set_header('Cache-Control: no-store')->set_content_type('application/json')->set_output(json_encode($result));
	}

	// ============== SHIFTS ==============

	public function shifts(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$shifts = $this->attendance_model->getShifts($storeId);
		$data = array_merge($this->data, [
			'page_title' => 'Shifts',
			'shifts' => $shifts
		]);
		$data['content'] = $this->load->view('attendance/shifts', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function shift_form($id = 0){
		if(!$this->_can_edit()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$shift = $id ? $this->attendance_model->getShift($id, $storeId) : null;
		$data = array_merge($this->data, [
			'page_title' => $id ? 'Edit Shift' : 'New Shift',
			'shift' => $shift
		]);
		$data['content'] = $this->load->view('attendance/shift_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_shift(){
		if(!$this->_can_edit()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$id = (int)$this->input->post('id');
		$payload = [
			'store_id' => $storeId,
			'shift_name' => trim($this->input->post('shift_name')),
			'start_time' => $this->input->post('start_time'),
			'end_time' => $this->input->post('end_time'),
			'grace_minutes' => (int)$this->input->post('grace_minutes'),
			'location_lat' => $this->input->post('location_lat') ?: null,
			'location_lng' => $this->input->post('location_lng') ?: null,
			'location_radius_meters' => (int)($this->input->post('location_radius_meters') ?: 100),
			'status' => 1
		];
		if($id){
			$this->attendance_model->updateShift($id, $payload, $storeId);
		} else {
			$id = $this->attendance_model->createShift($payload);
		}
		echo json_encode(['status'=>'success','message'=>'Shift saved','id'=>$id]);
	}

	public function delete_shift($id = 0){
		if(!$this->_can_edit()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$this->attendance_model->deleteShift($id, $storeId);
		echo json_encode(['status'=>'success','message'=>'Shift deleted']);
	}

	// ============== USER ASSIGNMENTS ==============

	public function assign_shifts(){
		if(!$this->_can_edit()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$excluded_roles = get_excluded_staff_roles($storeId);
		$excluded_users = get_excluded_user_ids($storeId);
		$this->db->where('store_id', $storeId)->where('status', 1)
			->where_not_in('role_id', $excluded_roles)
			->where_not_in('id', $excluded_users);
		$users = $this->db->get('db_users')->result();
		$shifts = $this->attendance_model->getShifts($storeId);
		foreach($users as $u){
			$u->assigned_shifts = $this->attendance_model->getShiftsByUser($u->id, $storeId);
		}
		$data = array_merge($this->data, [
			'page_title' => 'Assign Shifts',
			'users' => $users,
			'shifts' => $shifts
		]);
		$data['content'] = $this->load->view('attendance/assign_shifts', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function get_user_shifts_ajax(){
		if(!$this->_can_view()){ echo json_encode([]); return; }
		$userId = (int)$this->input->post('user_id');
		$storeId = get_current_store_id();
		$shifts = $this->attendance_model->getShiftsByUser($userId, $storeId);
		echo json_encode($shifts);
	}

	public function save_user_shift(){
		if(!$this->_can_edit()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$userId = (int)$this->input->post('user_id');
		$shiftId = (int)$this->input->post('shift_id');
		$action = $this->input->post('action');
		if($this->_building() && (!$this->db->where('store_id',$storeId)->where('id',$userId)->where('status',1)->count_all_results('db_users') || !$this->attendance_model->getShift($shiftId,$storeId))){
			echo json_encode(['status'=>'error','message'=>'Choose active staff and a shift from this store.']);return;
		}
		if($action === 'remove'){
			$result = $this->attendance_model->removeUserShift($userId, $shiftId, $storeId);
			if($result){
				echo json_encode(['status'=>'success','message'=>'Shift unassigned']);
			} else {
				echo json_encode(['status'=>'error','message'=>'Failed to unassign shift']);
			}
		} else {
			$result = $this->attendance_model->assignUserShift($userId, $shiftId, $storeId);
			if($result){
				echo json_encode(['status'=>'success','message'=>'Shift assigned']);
			} else {
				echo json_encode(['status'=>'error','message'=>'Shift already assigned or failed']);
			}
		}
	}

	public function clock_out_ajax(){
		if($this->_building()){
			if($this->input->method() !== 'post'){show_error('Use POST.',405);return;}
			$storeId = (int)get_current_store_id();$target = (int)$this->input->post('user_id');
			if($target === (int)$this->session->userdata('inv_userid')){$this->_buildingClockAction('out');return;}
			if(!$this->_can_edit()){show_error('Attendance edit permission is required.',403);return;}
			echo json_encode($this->attendance_model->buildingClock($storeId,$target,'out'));return;
		}
		$currentUserId = (int)$this->session->userdata('inv_userid');
		$targetUserId = (int)$this->input->post('user_id');
		$targetUser = $this->db->where('id', $targetUserId)->get('db_users')->row();
		if($targetUser && $targetUser->role_id == store_admin_id()){
			echo json_encode(['status'=>'error','message'=>'Store Admin is not eligible for attendance']); return;
		}
		// Allow self clock-out; require edit permission to clock out someone else
		if($targetUserId !== $currentUserId && !$this->_can_edit()){
			echo json_encode(['status'=>'error','message'=>'Access denied']); return;
		}
		$date = date('Y-m-d');
		$result = $this->attendance_model->clockOut($targetUserId, $date, [
			'clock_out' => date('H:i:s'),
			'clock_out_lat' => $this->input->post('lat') ?: null,
			'clock_out_lng' => $this->input->post('lng') ?: null
		]);
		echo json_encode($result);
	}

	public function needs_clock_out_ajax(){
		$userId = $this->session->userdata('inv_userid');
		$needs = false;
		if($userId){
			$needs = $this->attendance_model->needsClockOut($userId);
		}
		echo json_encode(['needs_clock_out' => $needs]);
	}

	// ============== DAILY ATTENDANCE ==============

	public function daily(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$date = $this->input->get('date') ?: date('Y-m-d');
		$report = $this->attendance_model->getDailyReport($storeId, $date);
		$data = array_merge($this->data, [
			'page_title' => 'Daily Attendance',
			'report' => $report,
			'date' => $date
		]);
		$data['content'] = $this->load->view('attendance/daily', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== REPORTS ==============

	public function report(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$excluded_roles = get_excluded_staff_roles($storeId);
		$excluded_users = get_excluded_user_ids($storeId);
		$start = $this->input->get('start') ?: date('Y-m-d', strtotime('-7 days'));
		$end = $this->input->get('end') ?: date('Y-m-d');
		$userId = (int)($this->input->get('user_id') ?: 0);

		$this->db->select('a.*, u.username, u.first_name, u.last_name, s.shift_name, s.location_lat, s.location_lng, s.location_radius_meters');
		$this->db->from('db_attendance a');
		$this->db->join('db_users u', 'u.id = a.user_id');
		$this->db->join('db_shifts s', 's.id = a.shift_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('u.store_id',$storeId);
		$this->db->where('a.attendance_date >=', $start);
		$this->db->where('a.attendance_date <=', $end);
		if($userId) $this->db->where('a.user_id', $userId);
		$this->db->where_not_in('u.role_id', $excluded_roles);
		$this->db->where_not_in('u.id', $excluded_users);
		$this->db->order_by('a.attendance_date', 'DESC');
		$records = $this->db->get()->result();

		$this->db->where('store_id', $storeId)->where('status', 1)
			->where_not_in('role_id', $excluded_roles)
			->where_not_in('id', $excluded_users);
		$users = $this->db->get('db_users')->result();

		$data = array_merge($this->data, [
			'page_title' => 'Attendance Report',
			'records' => $records,
			'users' => $users,
			'start' => $start,
			'end' => $end,
			'user_id' => $userId
		]);
		$data['content'] = $this->load->view('attendance/report', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== CLOCK IN / OUT (POS / AJAX) ==============

	private function _verifyUserPin($userId, $pin, $storeId = null){
		$this->db->where('id', $userId);
		if($storeId) $this->db->where('store_id',$storeId)->where('status',1);
		$user = $this->db->get('db_users')->row();
		if(!$user || empty($pin)) return false;
		if(!empty($user->approval_pin) && password_verify($pin, $user->approval_pin)) return true;
		if(password_verify($pin, $user->password)) return true;
		if($user->password === md5($pin)) return true;
		return false;
	}

	public function clock_in(){
		if($this->_building()){$this->_buildingClockAction('in');return;}
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){
			echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
			exit;
		}
		if(is_store_admin()){
			echo json_encode(['status' => 'error', 'message' => 'Store Admin is not eligible for attendance']);
			exit;
		}
		$storeId = get_current_store_id();

		$date = date('Y-m-d');
		if($this->attendance_model->needsClockOut($userId, $date)){
			echo json_encode(['status' => 'error', 'message' => 'Already clocked in']);
			exit;
		}

		$faceImage = $this->input->post('face_image');
		$pin = $this->input->post('pin');
		$lat = $this->input->post('lat') ?: null;
		$lng = $this->input->post('lng') ?: null;

		if(!$faceImage && !$pin){
			echo json_encode(['status' => 'error', 'message' => 'Face image or PIN is required']);
			exit;
		}

		$facePath = null;
		if($faceImage && strpos($faceImage, 'data:image') === 0){
			$dir = './uploads/attendance/';
			if(!is_dir($dir)) mkdir($dir, 0777, true);
			$parts = explode(',', $faceImage);
			$imgData = base64_decode($parts[1]);
			$facePath = 'uploads/attendance/face-' . $userId . '-' . time() . '.png';
			file_put_contents('./' . $facePath, $imgData);
		} elseif(!$this->_verifyUserPin($userId, $pin)){
			echo json_encode(['status' => 'error', 'message' => 'Invalid PIN']);
			exit;
		}

		$shift = $this->attendance_model->isOnDuty($userId, $storeId);

		$result = $this->attendance_model->clockIn([
			'store_id' => $storeId,
			'user_id' => $userId,
			'shift_id' => $shift ? $shift->id : null,
			'attendance_date' => $date,
			'clock_in' => date('H:i:s'),
			'clock_in_lat' => $lat,
			'clock_in_lng' => $lng,
			'face_image' => $facePath,
			'status' => 'present'
		]);
		if(!empty($result['status']) && $result['status'] === 'success'){
			$result['clock_in'] = date('H:i');
		}
		echo json_encode($result);
		exit;
	}

	public function clock_out(){
		if($this->_building()){$this->_buildingClockAction('out');return;}
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){
			echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
			exit;
		}
		if(is_store_admin()){
			echo json_encode(['status' => 'error', 'message' => 'Store Admin is not eligible for attendance']);
			exit;
		}
		$date = date('Y-m-d');
		$lat = $this->input->post('lat') ?: null;
		$lng = $this->input->post('lng') ?: null;
		$pin = $this->input->post('pin');
		$faceImage = $this->input->post('face_image');

		if(!$faceImage && !$pin){
			echo json_encode(['status' => 'error', 'message' => 'Face image or PIN is required']);
			exit;
		}

		$facePath = null;
		if($faceImage && strpos($faceImage, 'data:image') === 0){
			$dir = './uploads/attendance/';
			if(!is_dir($dir)) mkdir($dir, 0777, true);
			$parts = explode(',', $faceImage);
			$imgData = base64_decode($parts[1]);
			$facePath = 'uploads/attendance/face-out-' . $userId . '-' . time() . '.png';
			file_put_contents('./' . $facePath, $imgData);
		} elseif(!$this->_verifyUserPin($userId, $pin)){
			echo json_encode(['status' => 'error', 'message' => 'Invalid PIN']);
			exit;
		}

		$result = $this->attendance_model->clockOut($userId, $date, [
			'clock_out' => date('H:i:s'),
			'clock_out_lat' => $lat,
			'clock_out_lng' => $lng,
			'face_image_out' => $facePath
		]);
		if(!empty($result['status']) && $result['status'] === 'success'){
			$result['clock_out'] = date('H:i');
		}
		echo json_encode($result);
		exit;
	}

	public function clockin(){
		if($this->_building()){redirect('attendance/workspace');return;}
		$storeId = get_current_store_id() ?: 1;
		$store = $this->db->where('id', $storeId)->get('db_store')->row();
		$this->load->view('attendance/clockin_public', [
			'logged_in' => (bool)$this->session->userdata('inv_userid'),
			'store_name' => $store ? $store->store_name : 'Store'
		]);
	}

	public function status_ajax(){
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){ echo json_encode(['clocked_in'=>false]); exit; }
		if(!$this->db->table_exists('db_attendance') || !$this->db->table_exists('db_user_shifts')){
			echo json_encode(['clocked_in'=>false,'clock_in_time'=>null,'on_duty'=>false,'shift_name'=>null]);
			exit;
		}
		$storeId = get_current_store_id();
		$date = date('Y-m-d');
		$record = $this->attendance_model->getAttendanceRecord($userId, $date, $this->_building() ? $storeId : null);
		$shift = $this->attendance_model->isOnDuty($userId, $storeId);
		// Use needsClockOut for reliable clocked_in check (matches PHP sidebar/logout logic)
		$needsClockOut = $this->attendance_model->needsClockOut($userId, $date, $this->_building() ? $storeId : null);
		echo json_encode([
			'clocked_in' => $needsClockOut,
			'attendance_exempt' => !$this->_building() && is_store_admin(),
			'clock_in_time' => $record ? $record->clock_in : null,
			'on_duty' => $shift ? true : false,
			'shift_name' => $shift ? $shift->shift_name : null
		]);
		exit;
	}
}
