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
		$registry = require APPPATH . 'config/migrations.php';
		$migrations = [];
		foreach ($registry as $version => $files) {
			$migrations[rtrim((string)$version, 'v')] = $files;
		}
		uksort($migrations, 'version_compare');
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
			$ok = false;
			$error = '';
			// Deadlocks and lock-wait timeouts are TRANSIENT — InnoDB rolls the
			// statement back and it will normally succeed on retry. ALTER TABLE
			// in particular takes an exclusive lock, so on a busy install (or
			// one where another request holds a lock) a migration can deadlock
			// even though nothing is wrong. Retrying a few times with a short
			// pause turns a spurious "Migration failed [deadlock]" into a clean
			// run, instead of aborting the whole chain on a recoverable blip.
			$attempts = 0;
			$maxAttempts = 4;
			while($attempts < $maxAttempts){
				$attempts++;
				try{
					$ok = $conn->query($stmt);
					$error = $conn->error;
				} catch(mysqli_sql_exception $e){
					$ok = false;
					$error = $e->getMessage();
				}
				if($ok){
					break;
				}
				$retryable = (stripos($error, 'Deadlock') !== false)
					|| (stripos($error, 'Lock wait timeout') !== false)
					|| (stripos($error, 'try restarting transaction') !== false);
				if(!$retryable || $attempts >= $maxAttempts){
					break;
				}
				usleep(250000 * $attempts); // 0.25s, 0.5s, 0.75s
			}
			if($attempts > 1 && $ok){
				log_message('info', "Migration {$file} statement succeeded after {$attempts} attempts (was: deadlock/lock wait).");
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
