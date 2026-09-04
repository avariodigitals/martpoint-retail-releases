<?php
// Debug script to check clock-in status
chdir(__DIR__);
require_once './system/core/Common.php';
require_once './application/config/constants.php';

$environment = 'development';
$system_path = './system';
$application_folder = './application';
require_once './system/core/CodeIgniter.php';

$CI =& get_instance();
$CI->load->model('attendance_model');

$userId = $CI->session->userdata('inv_userid');
$roleName = trim($CI->session->userdata('role_name') ?: '');

echo "User ID: " . ($userId ?: 'NOT LOGGED IN') . "<br>";
echo "Role Name: " . htmlspecialchars($roleName) . "<br>";
echo "Is Cashier: " . (stripos($roleName, 'cashier') !== false ? 'YES' : 'NO') . "<br><br>";

if($userId){
    $date = date('Y-m-d');
    echo "Today: $date<br>";

    $needsOut = $CI->attendance_model->needsClockOut($userId, $date);
    echo "needsClockOut: " . ($needsOut ? 'true (clocked in, needs out)' : 'false (not clocked in or already out)') . "<br>";

    $record = $CI->attendance_model->getAttendanceRecord($userId, $date);
    if($record){
        echo "Record found:<br>";
        echo "  user_id: {$record->user_id}<br>";
        echo "  attendance_date: {$record->attendance_date}<br>";
        echo "  clock_in: " . ($record->clock_in ?: 'NULL') . "<br>";
        echo "  clock_out: " . ($record->clock_out ?: 'NULL') . "<br>";
    } else {
        echo "No attendance record for today.<br>";
    }
}
