<?php
require_once 'system/core/CodeIgniter.php';

$CI = get_instance();
$CI->load->database();

$version = $CI->db->select('version')->from('db_sitesettings')->get()->row()->version;

echo "DB Version: " . $version . "<br>";
echo "Source Version: " . (new MY_Controller())->source_version . "<br>";

// Also show the actual code being executed
$code = file_get_contents('application/controllers/Updates.php');
$lines = explode("\n", $code);
for ($i = 43; $i <= 48; $i++) {
    echo "Line " . ($i+1) . ": " . htmlspecialchars($lines[$i]) . "<br>";
}
