<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Updates_model extends CI_Model {

	public $app_version = null;
	public $db_version = null;
	public $version_check = array();

	public function __construct()
	{
		parent::__construct();
		//Do your magic here

		//$this->app_version = (float)app_version();

		//$this->version_check =array(2.8);

		$this->db_version = $this->get_current_version_of_db();

	}

	public function get_current_version_of_db(){

      return $this->db->select('version')->from('db_sitesettings')->get()->row()->version;

    }

	public function index()
	{
		// Run sequential migrations to bring older installations up to the
		// current installer schema without creating tables at runtime.
		$migrations = [
			'4.0.0' => '3.0_to_4.0.0.sql',
			'4.0.1' => '4.0.0_to_4.0.1_purchase_batch.sql',
			'4.0.2' => '4.0.1_to_4.0.2.sql',
			'4.0.3' => '4.0.2_to_4.0.3_db_store_modularization.sql',
			'4.0.4' => '4.0.3_to_4.0.4.sql',
			'4.0.5' => '4.0.4_to_4.0.5.sql',
			'4.0.6' => '4.0.5_to_4.0.6.sql',
			'4.0.7' => '4.0.6_to_4.0.7_fashion_intelligence.sql',
			'4.0.7-attr' => '4.0.7_attribute_driven_variants.sql',
			'4.0.8' => '4.0.8_featured_products.sql',
			'4.0.9' => '4.0.8_to_4.0.9_compatibility.sql',
			'4.0.9.1' => '4.0.8_to_4.0.9_holditems_staff_commission.sql',
			'4.0.9.2' => '4.0.9_to_4.0.9.2_promotions_loyalty.sql',
			'4.0.9.3' => '4.0.9.3_sku_limit.sql',
			'4.0.9.4' => '4.0.9_new_arrival_products.sql',
			'4.0.9.5' => '4.0.9_online_excluded.sql',
			'4.0.9.6' => '4.0.9.5_to_4.0.9.6_multi_unit_selling.sql',
			'4.0.9.7' => '4.0.9.6_to_4.0.9.7_multi_unit_variants.sql',
			'4.0.9.8' => '4.0.9.7_to_4.0.9.8_multi_unit_wholesale.sql',
			'4.0.9.9' => '4.0.9.8_newsletter_subscribers.sql',
			'4.0.9.10' => '4.0.6_b2b_customer_warehouse.sql',
			'4.0.9.11' => '4.0.9.11_leads_crm.sql',
			'4.0.9.12' => '4.0.9.12_industry_modules.sql',
			'4.0.9.13' => '4.0.9.13_storefront_columns.sql',
			'4.0.9.20' => '4.0.9.20_marquee_setting.sql',
			'4.0.9.22' => '4.0.9.22_manual_shipping.sql',
			'4.0.9.24' => ['4.0.9.24_perfumery_module.sql', '4.0.9.24_audit_trail.sql'],
			'4.0.9.25' => '4.0.9.25_monnify_integration.sql',
			'4.0.9.26' => ['4.0.9.26_bottling_runs.sql', '4.0.9.26_parfum_themes.sql'],
			'4.0.9.27' => '4.0.9.27_bottling_sku_link.sql',
			'4.0.9.28' => '4.0.9.28_customer_notes.sql',
			'4.0.9.29' => '4.0.9.29_storefront_settings_columns.sql',
			'4.0.9.30' => '4.0.9.30_stock_adjustment_repair.sql',
			'4.0.9.31' => '4.0.9.31_stock_recalculation_repair.sql',
			'4.0.9.32' => '4.0.9.32_warehouse_stock_repair.sql',
			'4.0.9.35' => '4.0.9.35_assist_ai_settings.sql',
			'4.0.9.38' => '4.0.9.38_payment_account_columns.sql',
			'4.0.9.53' => '4.0.9.53_nylon_polythene.sql',
			'4.0.9.55' => '4.0.9.55_city_shipping.sql',
			'4.0.9.56' => '4.0.9.56_skincare.sql',
			'4.0.9.60' => '4.0.9.60_physiotherapy_foundation.sql',
			'4.0.9.61' => '4.0.9.61_physiotherapy_intake_queue.sql',
			'4.0.9.62' => '4.0.9.62_physiotherapy_clinical.sql',
			'4.0.9.63' => '4.0.9.63_tracker_consent.sql',
			'4.0.9.64' => '4.0.9.64_physiotherapy_plans_funds.sql',
			'4.0.9.65' => '4.0.9.65_feature_coverage_variants.sql',
			'4.0.9.66' => '4.0.9.66_customer_import.sql',
			'4.0.9.67' => '4.0.9.67_physiotherapy_stage4_hardening.sql',
			'4.0.9.68' => '4.0.9.68_payment_reconciliation.sql',
			'4.0.9.69' => '4.0.9.69_storefront_coupons.sql',
			'4.0.9.70' => '4.0.9.70_physiotherapy_inpatient.sql',
			'4.0.9.71' => '4.0.9.71_storefront_events_carts.sql',
			'4.0.9.73' => '4.0.9.73_storefront_phase2_hardening.sql',
			'4.0.9.72' => '4.0.9.72_physiotherapy_portal_feedback.sql',
			'4.0.9.74' => '4.0.9.74_physiotherapy_imports.sql',
			'4.0.9.75' => '4.0.9.75_phase3_segments_campaigns.sql',
			'4.0.9.76' => '4.0.9.76_physiotherapy_approval_binding.sql',
			'4.0.9.77' => '4.0.9.77_phase3_hardening.sql',
			'4.0.9.78' => '4.0.9.78_phase4_commerce.sql',
			'4.0.9.79' => '4.0.9.79_refund_traceability.sql',
		];
		$latest_applied = $this->db_version;
		foreach($migrations as $target_version => $files){
			if(version_compare($this->db_version, $target_version, '<')){
				$migration_failed = false;
				foreach((array)$files as $file){
					$migration_file = FCPATH . 'updates/migrations/' . $file;
					if(!file_exists($migration_file)){
						// A registered migration that is missing on disk is a
						// release defect — log it AND record a failed job row so
						// it surfaces in System Updates → Recent Jobs instead of
						// being silently skipped.
						log_message('error', 'MartPoint migration file not found: ' . $migration_file);
						$this->_record_migration_failure($target_version, $file, 'Registered migration file is missing on disk.');
						$migration_failed = true;
						break;
					}
					if(!$this->_run_sql_file($migration_file)){
						$this->_record_migration_failure($target_version, $file, 'Migration SQL failed; the database version was not advanced.');
						$migration_failed = true;
						break;
					}
				}
				if($migration_failed){
					break;
				}
				$latest_applied = $target_version;
			}
		}
		if(version_compare($this->db_version, $latest_applied, '<')){
			$this->db->where('id', 1)->update('db_sitesettings', ['version' => $latest_applied]);
		}
	}

	private function _run_sql_file($path)
	{
		$sql = file_get_contents($path);
		if($sql === false){
			log_message('error', 'Failed to read migration file: ' . $path);
			return false;
		}
		$conn = $this->db->conn_id;
		if(!$conn || !($conn instanceof mysqli)){
			log_message('error', 'Migration runner could not access mysqli connection.');
			return false;
		}
		$file = basename($path);
		$failed = 0;
		try{
			$conn->query("SET FOREIGN_KEY_CHECKS = 0");
			$conn->query("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
			// Demote row-size failures to warnings where permitted (MariaDB and
			// privileged MySQL 8 users); a silent no-op on locked-down hosts.
			@$conn->query("SET SESSION innodb_strict_mode = OFF");
		} catch(mysqli_sql_exception $e){
			$failed++;
			log_message('error', "Migration {$file} session setup failed: {$e->getMessage()}");
		}
		// Execute statements one at a time so a single failure cannot drop the
		// rest of the file (multi_query aborts remaining statements silently).
		foreach($this->_split_sql_statements($sql) as $stmt){
			try{
				$ok = $conn->query($stmt);
				$error = $conn->error;
			} catch(mysqli_sql_exception $e){
				$ok = false;
				$error = $e->getMessage();
			}
			if(!$ok){
				$failed++;
				log_message('error', "Migration {$file} statement failed: {$error} | " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 300));
			}
		}
		try{
			$conn->query("SET FOREIGN_KEY_CHECKS = 1");
		} catch(mysqli_sql_exception $e){
			$failed++;
			log_message('error', "Migration {$file} could not restore foreign-key checks: {$e->getMessage()}");
		}
		if($failed){
			log_message('error', "Migration {$file} completed with {$failed} failed statement(s).");
		} else {
			log_message('info', "Migration {$file} completed successfully.");
		}
		return $failed === 0;
	}

	/**
	 * Split a multi-statement migration file on top-level semicolons,
	 * respecting quotes, backtick identifiers and -- / /*-comments.
	 */
	private function _split_sql_statements($sql){
		$statements = array();
		$current = '';
		$len = strlen($sql);
		$in_quote = false; $quote_char = '';
		$in_line_comment = false; $in_block_comment = false;
		for ($i = 0; $i < $len; $i++) {
			$char = $sql[$i];
			$next = ($i + 1 < $len) ? $sql[$i + 1] : '';
			if ($in_block_comment) {
				$current .= $char;
				if ($char === '*' && $next === '/') { $current .= $next; $in_block_comment = false; $i++; }
				continue;
			}
			if ($in_line_comment) {
				$current .= $char;
				if ($char === "\n") { $in_line_comment = false; }
				continue;
			}
			if ($in_quote) {
				$current .= $char;
				if ($char === $quote_char && ($i === 0 || $sql[$i - 1] !== '\\')) { $in_quote = false; $quote_char = ''; }
				continue;
			}
			if ($char === '/' && $next === '*') { $current .= $char . $next; $in_block_comment = true; $i++; continue; }
			if ($char === '-' && $next === '-' && ($next === '-' && ($i + 2 >= $len || $sql[$i + 2] === ' ' || $sql[$i + 2] === "\n"))) { $current .= $char . $next; $in_line_comment = true; $i++; continue; }
			if ($char === "'" || $char === '"' || $char === '`') { $current .= $char; $in_quote = true; $quote_char = $char; continue; }
			if ($char === ';') {
				$trimmed = trim($current);
				if ($trimmed !== '') { $statements[] = $trimmed; }
				$current = '';
				continue;
			}
			$current .= $char;
		}
		$trimmed = trim($current);
		if ($trimmed !== '') { $statements[] = $trimmed; }
		return $statements;
	}

	/**
	 * Record a failed registered migration in db_system_updates (deduplicated)
	 * so incomplete upgrades surface in System Updates → Recent Jobs.
	 */
	private function _record_migration_failure($target_version, $file, $reason){
		try{
			if(!$this->db->table_exists('db_system_updates')){ return; }
			$storeId = function_exists('get_current_store_id') ? get_current_store_id() : 1;
			$msg = $reason . ' File: ' . $file;
			$exists = $this->db->where('to_version', $target_version)
				->where('status', 'failed')
				->like('error_message', $msg)
				->count_all_results('db_system_updates');
			if(!$exists){
				$this->db->insert('db_system_updates', [
					'store_id' => $storeId ?: 1,
					'from_version' => (string)$this->get_current_version_of_db(),
					'to_version' => $target_version,
					'status' => 'failed',
					'step_label' => 'Migrations',
					'error_message' => $msg,
					'completed_at' => date('Y-m-d H:i:s'),
				]);
			}
		} catch(Exception $e){
			// Never let failure reporting break the migration run itself.
			log_message('error', 'Could not record missing-migration job row: ' . $e->getMessage());
		}
	}

}
