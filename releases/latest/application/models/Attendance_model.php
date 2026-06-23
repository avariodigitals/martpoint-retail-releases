<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Attendance Model
 * Manages shifts, user-shift assignments, attendance records, and clock-in/out.
 */
class Attendance_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->ensureTables();
	}

	/**
	 * Auto-create necessary tables if they don't exist
	 */
	private function ensureTables(){
		// Shifts table
		$this->db->query("CREATE TABLE IF NOT EXISTS db_shifts (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			shift_name VARCHAR(100) NOT NULL DEFAULT '',
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			grace_minutes INT NOT NULL DEFAULT 0,
			location_lat DECIMAL(10,8) DEFAULT NULL,
			location_lng DECIMAL(11,8) DEFAULT NULL,
			location_radius_meters INT NOT NULL DEFAULT 100,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// User-Shifts mapping
		$this->db->query("CREATE TABLE IF NOT EXISTS db_user_shifts (
			id INT AUTO_INCREMENT PRIMARY KEY,
			user_id INT NOT NULL,
			shift_id INT NOT NULL,
			store_id INT NOT NULL DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Attendance records
		$this->db->query("CREATE TABLE IF NOT EXISTS db_attendance (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			user_id INT NOT NULL,
			shift_id INT DEFAULT NULL,
			attendance_date DATE NOT NULL,
			clock_in TIME DEFAULT NULL,
			clock_out TIME DEFAULT NULL,
			clock_in_lat DECIMAL(10,8) DEFAULT NULL,
			clock_in_lng DECIMAL(11,8) DEFAULT NULL,
			clock_out_lat DECIMAL(10,8) DEFAULT NULL,
			clock_out_lng DECIMAL(11,8) DEFAULT NULL,
			face_image VARCHAR(255) DEFAULT NULL,
			face_image_out VARCHAR(255) DEFAULT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'present',
			notes TEXT,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_date (attendance_date),
			INDEX idx_user (user_id),
			INDEX idx_store (store_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Add store location fields to db_store if not exists
		if(!$this->db->field_exists('location_lat','db_store')){
			$this->db->query("ALTER TABLE db_store ADD COLUMN location_lat DECIMAL(10,8) DEFAULT NULL AFTER address");
		}
		if(!$this->db->field_exists('location_lng','db_store')){
			$this->db->query("ALTER TABLE db_store ADD COLUMN location_lng DECIMAL(11,8) DEFAULT NULL AFTER location_lat");
		}
	}

	// ========== SHIFTS ==========

	public function getShifts($storeId){
		return $this->db->where('store_id', $storeId)->where('status', 1)->get('db_shifts')->result();
	}

	public function getShift($id, $storeId = null){
		$this->db->where('id', $id);
		if($storeId) $this->db->where('store_id', $storeId);
		return $this->db->get('db_shifts')->row();
	}

	public function createShift($data){
		$this->db->insert('db_shifts', $data);
		return $this->db->insert_id();
	}

	public function updateShift($id, $data, $storeId = null){
		$this->db->where('id', $id);
		if($storeId) $this->db->where('store_id', $storeId);
		return $this->db->update('db_shifts', $data);
	}

	public function deleteShift($id, $storeId = null){
		$this->db->where('id', $id);
		if($storeId) $this->db->where('store_id', $storeId);
		return $this->db->delete('db_shifts');
	}

	// ========== USER SHIFTS ==========

	public function getUserShifts($userId, $storeId = null){
		$this->db->where('user_id', $userId);
		if($storeId) $this->db->where('store_id', $storeId);
		return $this->db->get('db_user_shifts')->result();
	}

	public function getUsersByShift($shiftId, $storeId = null){
		$this->db->select('u.id, u.username, u.first_name, u.last_name, u.profile_picture');
		$this->db->from('db_user_shifts us');
		$this->db->join('db_users u', 'u.id = us.user_id');
		$this->db->where('us.shift_id', $shiftId);
		if($storeId) $this->db->where('us.store_id', $storeId);
		return $this->db->get()->result();
	}

	public function assignUserShift($userId, $shiftId, $storeId){
		$exists = $this->db->where('user_id', $userId)->where('shift_id', $shiftId)->where('store_id', $storeId)->count_all_results('db_user_shifts');
		if($exists) return false;
		return $this->db->insert('db_user_shifts', [
			'user_id' => $userId,
			'shift_id' => $shiftId,
			'store_id' => $storeId
		]);
	}

	public function removeUserShift($userId, $shiftId, $storeId = null){
		$this->db->where('user_id', $userId)->where('shift_id', $shiftId);
		if($storeId) $this->db->where('store_id', $storeId);
		return $this->db->delete('db_user_shifts');
	}

	public function getShiftsByUser($userId, $storeId = null){
		$this->db->select('s.*');
		$this->db->from('db_user_shifts us');
		$this->db->join('db_shifts s', 's.id = us.shift_id');
		$this->db->where('us.user_id', $userId);
		if($storeId) $this->db->where('us.store_id', $storeId);
		return $this->db->get()->result();
	}

	// ========== ATTENDANCE ==========

	public function clockIn($data){
		$exists = $this->db
			->where('user_id', $data['user_id'])
			->where('attendance_date', $data['attendance_date'])
			->where('clock_out IS NULL')
			->count_all_results('db_attendance');
		if($exists){
			return ['status' => 'error', 'message' => 'Already clocked in today'];
		}
		$this->db->insert('db_attendance', $data);
		return ['status' => 'success', 'message' => 'Clocked in', 'id' => $this->db->insert_id()];
	}

	public function clockOut($userId, $date, $data){
		$this->db->where('user_id', $userId)->where('attendance_date', $date)->where('clock_out IS NULL');
		$record = $this->db->get('db_attendance')->row();
		if(!$record){
			return ['status' => 'error', 'message' => 'Not clocked in'];
		}
		$this->db->where('id', $record->id);
		$this->db->update('db_attendance', $data);
		return ['status' => 'success', 'message' => 'Clocked out'];
	}

	public function getAttendanceByDate($storeId, $date){
		$this->db->select('a.*, u.username, u.first_name, u.last_name, u.profile_picture, s.shift_name, s.start_time, s.end_time, s.location_lat, s.location_lng, s.location_radius_meters');
		$this->db->from('db_attendance a');
		$this->db->join('db_users u', 'u.id = a.user_id');
		$this->db->join('db_shifts s', 's.id = a.shift_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.attendance_date', $date);
		return $this->db->get()->result();
	}

	public function getAttendanceByUser($userId, $startDate = null, $endDate = null){
		$this->db->select('a.*, s.shift_name');
		$this->db->from('db_attendance a');
		$this->db->join('db_shifts s', 's.id = a.shift_id', 'left');
		$this->db->where('a.user_id', $userId);
		if($startDate) $this->db->where('a.attendance_date >=', $startDate);
		if($endDate) $this->db->where('a.attendance_date <=', $endDate);
		$this->db->order_by('a.attendance_date', 'DESC');
		return $this->db->get()->result();
	}

	public function getTodayAttendance($storeId, $date = null){
		$date = $date ?: date('Y-m-d');
		return $this->getAttendanceByDate($storeId, $date);
	}

	public function needsClockOut($userId, $date = null){
		$date = $date ?: date('Y-m-d');
		$query = $this->db->query(
			"SELECT COUNT(*) as cnt FROM db_attendance WHERE user_id = ? AND attendance_date = ? AND clock_in IS NOT NULL AND clock_in != '' AND (clock_out IS NULL OR clock_out = '')",
			[$userId, $date]
		);
		return (int)$query->row()->cnt > 0;
	}

	public function getDailyReport($storeId, $date){
		// Get all users assigned to any shift in this store
		$this->db->select('DISTINCT(u.id), u.username, u.first_name, u.last_name, u.profile_picture');
		$this->db->from('db_user_shifts us');
		$this->db->join('db_users u', 'u.id = us.user_id');
		$this->db->where('us.store_id', $storeId);
		$assignedUsers = $this->db->get()->result();

		$attendance = $this->getAttendanceByDate($storeId, $date);
		$attMap = [];
		foreach($attendance as $a){
			$attMap[$a->user_id] = $a;
		}

		$report = [];
		foreach($assignedUsers as $user){
			$a = $attMap[$user->id] ?? null;
			$report[] = [
				'user_id' => $user->id,
				'username' => $user->username,
				'name' => trim(($user->first_name ?: '') . ' ' . ($user->last_name ?: '')),
				'profile_picture' => $user->profile_picture,
				'present' => $a ? true : false,
				'clock_in' => $a ? $a->clock_in : null,
				'clock_out' => $a ? $a->clock_out : null,
				'status' => $a ? $a->status : 'absent',
				'shift_name' => $a ? ($a->shift_name ?: '-') : '-',
				'location_ok' => $this->_isLocationOk($a),
				'face_image' => $a ? $a->face_image : null,
			];
		}
		return $report;
	}

	private function _isLocationOk($attendanceRecord){
		if(!$attendanceRecord || !$attendanceRecord->clock_in_lat) return false;
		if(!$attendanceRecord->location_lat || !$attendanceRecord->location_lng) return true; // no location set
		$dist = $this->haversine(
			$attendanceRecord->location_lat, $attendanceRecord->location_lng,
			$attendanceRecord->clock_in_lat, $attendanceRecord->clock_in_lng
		);
		$radius = $attendanceRecord->location_radius_meters ?: 100;
		return $dist <= $radius;
	}

	public function haversine($lat1, $lng1, $lat2, $lng2){
		$R = 6371000; // Earth radius in meters
		$dLat = deg2rad($lat2 - $lat1);
		$dLng = deg2rad($lng2 - $lng1);
		$a = sin($dLat/2) * sin($dLat/2) +
			cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
			sin($dLng/2) * sin($dLng/2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		return $R * $c;
	}

	public function isOnDuty($userId, $storeId){
		$shifts = $this->getShiftsByUser($userId, $storeId);
		$now = date('H:i:s');
		foreach($shifts as $s){
			if($now >= $s->start_time && $now <= $s->end_time){
				return $s;
			}
		}
		return null;
	}

	public function getAttendanceRecord($userId, $date){
		return $this->db
			->where('user_id', $userId)
			->where('attendance_date', $date)
			->get('db_attendance')
			->row();
	}
}
