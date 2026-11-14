<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_subscription_license extends CI_Migration {

	public function up()
	{
		// Subscription License table
		if (!$this->db->table_exists('db_subscription_license')) {
			$this->db->query("
				CREATE TABLE `db_subscription_license` (
				  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `store_id` int(11) NOT NULL,
				  `license_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Basic',
				  `subscription_start_date` date DEFAULT NULL,
				  `subscription_end_date` date DEFAULT NULL,
				  `subscription_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE',
				  `branch_limit` int(11) DEFAULT 1,
				  `user_limit` int(11) DEFAULT 5,
				  `renewal_amount` decimal(20,2) DEFAULT NULL,
				  `last_renewal_date` date DEFAULT NULL,
				  `suspension_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `reminder_90_sent` tinyint(1) DEFAULT 0,
				  `reminder_60_sent` tinyint(1) DEFAULT 0,
				  `reminder_30_last_sent` date DEFAULT NULL,
				  `reminder_10_last_sent` date DEFAULT NULL,
				  `expiry_notice_sent` tinyint(1) DEFAULT 0,
				  `expired_followup_count` int(11) DEFAULT 0,
				  `expired_followup_last_sent` date DEFAULT NULL,
				  `activated_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				  `created_date` date DEFAULT NULL,
				  `created_time` time DEFAULT NULL,
				  `updated_date` date DEFAULT NULL,
				  `updated_time` time DEFAULT NULL,
				  `status` int(1) DEFAULT 1,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `store_id` (`store_id`),
				  KEY `license_code` (`license_code`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			");
		}
	}

	public function down()
	{
		$this->dbforge->drop_table('db_subscription_license', TRUE);
	}
}
