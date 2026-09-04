<?php
// Run this in your browser: http://localhost/martpoint%20retail/run_loyalty_migration.php
// It will create all missing loyalty tables and columns

$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$errors = [];
$created = [];

// 1. Add loyalty columns to db_customers (ignore if already exist)
$customer_cols = [
    'loyalty_points' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
    'lifetime_spend' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
    'loyalty_tier' => 'VARCHAR(50) DEFAULT \'Bronze\'',
    'store_credit_balance' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
    'gift_card_balance' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
    'referral_code' => 'VARCHAR(20) DEFAULT NULL',
    'referred_by' => 'INT DEFAULT NULL',
    'referral_count' => 'INT NOT NULL DEFAULT 0',
    'birthday' => 'DATE DEFAULT NULL',
    'last_purchase_date' => 'DATE DEFAULT NULL',
    'average_order_value' => 'DECIMAL(15,2) DEFAULT 0',
    'favourite_products' => 'TEXT DEFAULT NULL',
    'photo' => 'VARCHAR(255) DEFAULT NULL',
];

foreach ($customer_cols as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM db_customers LIKE '$col'");
    if ($res->num_rows == 0) {
        if ($conn->query("ALTER TABLE db_customers ADD COLUMN $col $def")) {
            $created[] = "db_customers.$col";
        } else {
            $errors[] = "db_customers.$col: " . $conn->error;
        }
    }
}

// 2. Create db_loyalty_settings
$sql = "CREATE TABLE IF NOT EXISTS db_loyalty_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    loyalty_enabled TINYINT(1) NOT NULL DEFAULT 0,
    earning_type ENUM('spend_based','percentage_based','product_specific','service_specific') DEFAULT 'spend_based',
    spend_amount DECIMAL(15,2) DEFAULT 1000,
    points_earned DECIMAL(10,2) DEFAULT 1,
    percentage_rate DECIMAL(5,2) DEFAULT 2.00,
    redemption_rate DECIMAL(10,2) DEFAULT 10.00,
    minimum_redemption_points DECIMAL(10,2) DEFAULT 100,
    maximum_redemption_per_sale DECIMAL(15,2) DEFAULT 0,
    allow_partial_redemption TINYINT(1) DEFAULT 1,
    tier_calculation ENUM('lifetime_spend','points') DEFAULT 'lifetime_spend',
    flexpay_points_timing ENUM('full_payment','immediately','disabled') DEFAULT 'full_payment',
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    UNIQUE KEY uk_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    $created[] = "db_loyalty_settings";
} else {
    $errors[] = "db_loyalty_settings: " . $conn->error;
}

// 3. Create db_loyalty_tiers
$sql = "CREATE TABLE IF NOT EXISTS db_loyalty_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    tier_name VARCHAR(50) NOT NULL,
    minimum_spend DECIMAL(15,2) DEFAULT 0,
    minimum_points DECIMAL(15,2) DEFAULT 0,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    bonus_points_percentage DECIMAL(5,2) DEFAULT 0,
    priority_service TINYINT(1) DEFAULT 0,
    birthday_reward_type ENUM('discount','voucher','points','product') DEFAULT 'points',
    birthday_reward_value DECIMAL(10,2) DEFAULT 100,
    sort_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    $created[] = "db_loyalty_tiers";
} else {
    $errors[] = "db_loyalty_tiers: " . $conn->error;
}

// 4. Create db_loyalty_points
$sql = "CREATE TABLE IF NOT EXISTS db_loyalty_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_id INT NOT NULL,
    points DECIMAL(15,2) NOT NULL DEFAULT 0,
    points_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    transaction_type ENUM('earn','redeem','expire','adjust','bonus','referral') DEFAULT 'earn',
    description VARCHAR(255) DEFAULT NULL,
    sales_id INT DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    $created[] = "db_loyalty_points";
} else {
    $errors[] = "db_loyalty_points: " . $conn->error;
}

// 5. Create db_loyalty_bonus_rules
$sql = "CREATE TABLE IF NOT EXISTS db_loyalty_bonus_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    rule_type ENUM('birthday','first_purchase','weekend','holiday','referral','custom') DEFAULT 'custom',
    multiplier DECIMAL(5,2) DEFAULT 1.00,
    bonus_points DECIMAL(10,2) DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    days_of_week VARCHAR(50) DEFAULT NULL,
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    $created[] = "db_loyalty_bonus_rules";
} else {
    $errors[] = "db_loyalty_bonus_rules: " . $conn->error;
}

// 6. Create db_loyalty_product_points
$sql = "CREATE TABLE IF NOT EXISTS db_loyalty_product_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    item_id INT NOT NULL,
    points_awarded DECIMAL(10,2) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    INDEX idx_item (item_id),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    $created[] = "db_loyalty_product_points";
} else {
    $errors[] = "db_loyalty_product_points: " . $conn->error;
}

// 7. Insert default settings for each store
$res = $conn->query("SELECT id FROM db_store");
while ($store = $res->fetch_assoc()) {
    $store_id = $store['id'];
    $check = $conn->query("SELECT id FROM db_loyalty_settings WHERE store_id = $store_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO db_loyalty_settings (store_id, loyalty_enabled, earning_type, spend_amount, points_earned, percentage_rate, redemption_rate, minimum_redemption_points, maximum_redemption_per_sale, allow_partial_redemption, tier_calculation, flexpay_points_timing, created_date) VALUES ($store_id, 0, 'spend_based', 1000, 1, 2.00, 10.00, 100, 0, 1, 'lifetime_spend', 'full_payment', CURDATE())");
    }
}

// 8. Insert default tiers for each store
$res = $conn->query("SELECT id FROM db_store");
while ($store = $res->fetch_assoc()) {
    $store_id = $store['id'];
    $check = $conn->query("SELECT id FROM db_loyalty_tiers WHERE store_id = $store_id AND status = 1");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO db_loyalty_tiers (store_id, tier_name, minimum_spend, minimum_points, discount_percentage, bonus_points_percentage, priority_service, birthday_reward_type, birthday_reward_value, sort_order, status, created_date) VALUES 
            ($store_id, 'Bronze', 0, 0, 0, 0, 0, 'points', 100, 1, 1, CURDATE()),
            ($store_id, 'Silver', 50000, 500, 2, 5, 0, 'discount', 5, 2, 1, CURDATE()),
            ($store_id, 'Gold', 150000, 1500, 5, 10, 1, 'discount', 10, 3, 1, CURDATE())");
    }
}

$conn->close();

header('Content-Type: text/plain');
echo "=== MARTPOINT LOYALTY MIGRATION ===\n\n";

if (!empty($created)) {
    echo "CREATED:\n";
    foreach ($created as $c) echo "  + $c\n";
}

if (!empty($errors)) {
    echo "\nERRORS:\n";
    foreach ($errors as $e) echo "  ! $e\n";
}

if (empty($created) && empty($errors)) {
    echo "Nothing to do. All tables and columns already exist.\n";
}

echo "\nDone. Reload your Loyalty Settings page.\n";
