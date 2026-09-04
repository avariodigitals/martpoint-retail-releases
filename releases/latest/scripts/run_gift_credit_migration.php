<?php
$host = 'localhost';
$user = 'marttes';
$pass = 'marttes';
$dbname = 'marttes';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$errors = [];
$created = [];

// db_gift_cards
$sql = "CREATE TABLE IF NOT EXISTS db_gift_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    card_number VARCHAR(50) NOT NULL,
    customer_id INT DEFAULT NULL,
    initial_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    issue_date DATE DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    card_type ENUM('physical','digital') DEFAULT 'physical',
    status ENUM('active','redeemed','expired','cancelled') DEFAULT 'active',
    notes TEXT DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    UNIQUE KEY uk_card_number (card_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($conn->query($sql)) $created[] = "db_gift_cards"; else $errors[] = "db_gift_cards: " . $conn->error;

// db_store_credit
$sql = "CREATE TABLE IF NOT EXISTS db_store_credit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_id INT NOT NULL,
    credit_code VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    source VARCHAR(50) DEFAULT 'manual',
    sales_id INT DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('active','used','expired','cancelled') DEFAULT 'active',
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($conn->query($sql)) $created[] = "db_store_credit"; else $errors[] = "db_store_credit: " . $conn->error;

// db_store_credit_usage
$sql = "CREATE TABLE IF NOT EXISTS db_store_credit_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    credit_id INT NOT NULL,
    sales_id INT DEFAULT NULL,
    amount_used DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_credit (credit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
if ($conn->query($sql)) $created[] = "db_store_credit_usage"; else $errors[] = "db_store_credit_usage: " . $conn->error;

$conn->close();

header('Content-Type: text/plain');
echo "=== GIFT CARDS / STORE CREDIT MIGRATION ===\n\n";
if (!empty($created)) { echo "CREATED:\n"; foreach ($created as $c) echo "  + $c\n"; }
if (!empty($errors)) { echo "\nERRORS:\n"; foreach ($errors as $e) echo "  ! $e\n"; }
if (empty($created) && empty($errors)) echo "Nothing to do.\n";
echo "\nDone. Reload your pages.\n";
