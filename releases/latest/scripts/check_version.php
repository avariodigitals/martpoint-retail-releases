<?php
// Standalone DB check — no CodeIgniter
$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$db   = 'marttes';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('DB connection failed: ' . $conn->connect_error);
}

$result = $conn->query("SELECT version FROM db_sitesettings WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    echo 'DB Version: ' . $row['version'] . "\n";
} else {
    echo 'Query failed: ' . $conn->error . "\n";
}
$conn->close();

// Show the actual Updates.php code
$code = file_get_contents('application/controllers/Updates.php');
$lines = explode("\n", $code);
echo "\n--- Updates.php lines 43-48 ---\n";
for ($i = 42; $i < 48; $i++) {
    echo ($i+1) . ': ' . $lines[$i] . "\n";
}
