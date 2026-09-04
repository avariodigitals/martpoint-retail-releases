<?php
// Run this in browser: http://localhost:8888/migrate_staff_assignment.php
header('Content-Type: text/plain');

$cfg_file = file_get_contents('application/config/database.php');
$cfg = [];
foreach (['hostname','username','password','database'] as $k) {
    if (preg_match("/'$k'\s*=>\s*'([^']+)'/", $cfg_file, $m)) {
        $cfg[$k] = $m[1];
    }
}
if (empty($cfg['database'])) {
    die("Could not parse database config.\n");
}

$mysqli = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    die("DB connect failed: " . $mysqli->connect_error . "\n");
}

echo "=== Staff Assignment Migration ===\n\n";

// Create db_service_staff junction table
$sql = "CREATE TABLE IF NOT EXISTS `db_service_staff` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `store_id` INT(10) NOT NULL,
    `service_id` INT(10) NOT NULL,
    `staff_id` INT(10) NOT NULL,
    `status` INT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_service_staff` (`store_id`,`service_id`,`staff_id`),
    KEY `idx_service_id` (`service_id`),
    KEY `idx_staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "OK: db_service_staff table created/exists\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

// Add assigned_staff_id to db_services if not exists
$r = $mysqli->query("SHOW COLUMNS FROM `db_services` LIKE 'assigned_staff_id'");
if ($r && $r->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE `db_services` ADD COLUMN `assigned_staff_id` INT(10) NULL AFTER `status`")) {
        echo "OK: assigned_staff_id column added to db_services\n";
    } else {
        echo "ERROR adding assigned_staff_id: " . $mysqli->error . "\n";
    }
} else {
    echo "OK: assigned_staff_id already exists in db_services\n";
}

// Add staff_commission_percent to db_services if not exists
$r = $mysqli->query("SHOW COLUMNS FROM `db_services` LIKE 'staff_commission_percent'");
if ($r && $r->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE `db_services` ADD COLUMN `staff_commission_percent` DECIMAL(5,2) DEFAULT 0 AFTER `assigned_staff_id`")) {
        echo "OK: staff_commission_percent column added to db_services\n";
    } else {
        echo "ERROR adding staff_commission_percent: " . $mysqli->error . "\n";
    }
} else {
    echo "OK: staff_commission_percent already exists in db_services\n";
}

echo "\nMigration complete.\n";
$mysqli->close();
