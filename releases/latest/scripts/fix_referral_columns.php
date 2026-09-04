<?php
$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$columns = [
    'referral_enabled' => "TINYINT(1) NOT NULL DEFAULT 0",
    'referrer_reward_type' => "ENUM('points','discount','voucher') DEFAULT 'points'",
    'referrer_reward_value' => "DECIMAL(10,2) DEFAULT 0",
    'new_customer_reward_type' => "ENUM('points','discount','voucher') DEFAULT 'points'",
    'new_customer_reward_value' => "DECIMAL(10,2) DEFAULT 0",
    'referral_approval_required' => "TINYINT(1) DEFAULT 0",
];

foreach ($columns as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM db_loyalty_settings LIKE '$col'");
    if ($res->num_rows == 0) {
        $conn->query("ALTER TABLE db_loyalty_settings ADD COLUMN $col $def");
        echo "ADDED: $col\n";
    } else {
        echo "EXISTS: $col\n";
    }
}

echo "Done.\n";
$conn->close();
