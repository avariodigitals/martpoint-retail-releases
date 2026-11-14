-- Medical Notes for Pharmacy Workflow
-- Per-patient prescription/dispensing records with doctor info, diagnosis, dosage, and refill tracking

CREATE TABLE IF NOT EXISTS `db_medical_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `note_date` date NOT NULL,
  `prescribing_doctor` varchar(255) DEFAULT NULL,
  `doctor_contact` varchar(50) DEFAULT NULL,
  `diagnosis` varchar(500) DEFAULT NULL,
  `prescription_ref` varchar(100) DEFAULT NULL,
  `allergies_flagged` varchar(500) DEFAULT NULL,
  `dosage_instructions` text,
  `counselling_notes` text,
  `next_refill_date` date DEFAULT NULL,
  `refills_remaining` int(11) DEFAULT 0,
  `staff_id` int(11) DEFAULT NULL,
  `staff_name` varchar(120) DEFAULT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_date` date NOT NULL,
  `created_time` time NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_customer` (`store_id`, `customer_id`),
  KEY `idx_note_date` (`note_date`),
  KEY `idx_next_refill` (`next_refill_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_medical_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medical_note_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `qty` decimal(15,3) DEFAULT 1.000,
  `dosage` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_note_id` (`medical_note_id`),
  KEY `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add prescription_file column for existing installs
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_medical_notes' AND COLUMN_NAME = 'prescription_file');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_medical_notes` ADD COLUMN `prescription_file` varchar(255) DEFAULT NULL AFTER `sales_id`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
