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

	// ============== SHIFTS ==============

	public function shifts(){
		if(!$this->_can_view()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$shifts = $this->attendance_model->getShifts($storeId);
		$data = array_merge($this->data, [
			'page_title' => 'Shifts',
			'shifts' => $shifts
		]);
		$this->load->view('attendance/shifts', $data);
	}

	public function shift_form($id = 0){
		if(!$this->_can_edit()){ echo json_encode(['status'=>'error','message'=>'Access denied']); return; }
		$storeId = get_current_store_id();
		$shift = $id ? $this->attendance_model->getShift($id, $storeId) : null;
		$data = array_merge($this->data, [
			'page_title' => $id ? 'Edit Shift' : 'New Shift',
			'shift' => $shift
		]);
		$this->load->view('attendance/shift_form', $data);
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
		if(!$this->_can_edit()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$users = $this->db->where('store_id', $storeId)->where('status', 1)->get('db_users')->result();
		$shifts = $this->attendance_model->getShifts($storeId);
		foreach($users as $u){
			$u->assigned_shifts = $this->attendance_model->getShiftsByUser($u->id, $storeId);
		}
		$data = array_merge($this->data, [
			'page_title' => 'Assign Shifts',
			'users' => $users,
			'shifts' => $shifts
		]);
		$this->load->view('attendance/assign_shifts', $data);
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
		$currentUserId = (int)$this->session->userdata('inv_userid');
		$targetUserId = (int)$this->input->post('user_id');
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
		if(!$this->_can_view()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$date = $this->input->get('date') ?: date('Y-m-d');
		$report = $this->attendance_model->getDailyReport($storeId, $date);
		$data = array_merge($this->data, [
			'page_title' => 'Daily Attendance',
			'report' => $report,
			'date' => $date
		]);
		$this->load->view('attendance/daily', $data);
	}

	// ============== REPORTS ==============

	public function report(){
		if(!$this->_can_view()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$start = $this->input->get('start') ?: date('Y-m-d', strtotime('-7 days'));
		$end = $this->input->get('end') ?: date('Y-m-d');
		$userId = (int)($this->input->get('user_id') ?: 0);

		$this->db->select('a.*, u.username, u.first_name, u.last_name, s.shift_name, s.location_lat, s.location_lng, s.location_radius_meters');
		$this->db->from('db_attendance a');
		$this->db->join('db_users u', 'u.id = a.user_id');
		$this->db->join('db_shifts s', 's.id = a.shift_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.attendance_date >=', $start);
		$this->db->where('a.attendance_date <=', $end);
		if($userId) $this->db->where('a.user_id', $userId);
		$this->db->order_by('a.attendance_date', 'DESC');
		$records = $this->db->get()->result();

		$users = $this->db->where('store_id', $storeId)->where('status', 1)->get('db_users')->result();

		$data = array_merge($this->data, [
			'page_title' => 'Attendance Report',
			'records' => $records,
			'users' => $users,
			'start' => $start,
			'end' => $end,
			'user_id' => $userId
		]);
		$this->load->view('attendance/report', $data);
	}

	// ============== CLOCK IN / OUT (POS / AJAX) ==============

	public function clock_in(){
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){
			echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
			return;
		}
		$storeId = get_current_store_id();
		$shift = $this->attendance_model->isOnDuty($userId, $storeId);
		if(!$shift){
			echo json_encode(['status' => 'error', 'message' => 'You are not scheduled for any shift right now']);
			return;
		}

		$date = date('Y-m-d');
		if($this->attendance_model->needsClockOut($userId, $date)){
			echo json_encode(['status' => 'error', 'message' => 'Already clocked in']);
			return;
		}

		$faceImage = $this->input->post('face_image');
		$lat = $this->input->post('lat') ?: null;
		$lng = $this->input->post('lng') ?: null;

		$facePath = null;
		if($faceImage && strpos($faceImage, 'data:image') === 0){
			$dir = './uploads/attendance/';
			if(!is_dir($dir)) mkdir($dir, 0777, true);
			$parts = explode(',', $faceImage);
			$imgData = base64_decode($parts[1]);
			$facePath = 'uploads/attendance/face-' . $userId . '-' . time() . '.png';
			file_put_contents('./' . $facePath, $imgData);
		}

		$result = $this->attendance_model->clockIn([
			'store_id' => $storeId,
			'user_id' => $userId,
			'shift_id' => $shift->id,
			'attendance_date' => $date,
			'clock_in' => date('H:i:s'),
			'clock_in_lat' => $lat,
			'clock_in_lng' => $lng,
			'face_image' => $facePath,
			'status' => 'present'
		]);
		echo json_encode($result);
	}

	public function clock_out(){
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){
			echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
			return;
		}
		$date = date('Y-m-d');
		$lat = $this->input->post('lat') ?: null;
		$lng = $this->input->post('lng') ?: null;
		$faceImage = $this->input->post('face_image');

		$facePath = null;
		if($faceImage && strpos($faceImage, 'data:image') === 0){
			$dir = './uploads/attendance/';
			if(!is_dir($dir)) mkdir($dir, 0777, true);
			$parts = explode(',', $faceImage);
			$imgData = base64_decode($parts[1]);
			$facePath = 'uploads/attendance/face-out-' . $userId . '-' . time() . '.png';
			file_put_contents('./' . $facePath, $imgData);
		}

		$result = $this->attendance_model->clockOut($userId, $date, [
			'clock_out' => date('H:i:s'),
			'clock_out_lat' => $lat,
			'clock_out_lng' => $lng,
			'face_image_out' => $facePath
		]);
		echo json_encode($result);
	}

	public function clockin(){
		$storeId = get_current_store_id() ?: 1;
		$store = $this->db->where('id', $storeId)->get('db_store')->row();
		$this->load->view('attendance/clockin_public', [
			'logged_in' => (bool)$this->session->userdata('inv_userid'),
			'store_name' => $store ? $store->store_name : 'Store'
		]);
	}

	public function status_ajax(){
		$userId = (int)$this->session->userdata('inv_userid');
		if(!$userId){ echo json_encode(['clocked_in'=>false]); return; }
		$storeId = get_current_store_id();
		$date = date('Y-m-d');
		$record = $this->attendance_model->getAttendanceRecord($userId, $date);
		$shift = $this->attendance_model->isOnDuty($userId, $storeId);
		// Use needsClockOut for reliable clocked_in check (matches PHP sidebar/logout logic)
		$needsClockOut = $this->attendance_model->needsClockOut($userId, $date);
		echo json_encode([
			'clocked_in' => $needsClockOut,
			'clock_in_time' => $record ? $record->clock_in : null,
			'on_duty' => $shift ? true : false,
			'shift_name' => $shift ? $shift->shift_name : null
		]);
	}
}
