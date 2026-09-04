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
		];
		$latest_applied = $this->db_version;
		foreach($migrations as $target_version => $file){
			if(version_compare($this->db_version, $target_version, '<')){
				$migration_file = FCPATH . 'updates/migrations/' . $file;
				if(file_exists($migration_file)){
					$this->_run_sql_file($migration_file);
					$latest_applied = $target_version;
				} else {
					log_message('error', 'MartPoint migration file not found: ' . $migration_file);
				}
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
			return;
		}
		// Use the underlying mysqli connection to execute multi-statements.
		$conn = $this->db->conn_id;
		if(!$conn || !($conn instanceof mysqli)){
			log_message('error', 'Migration runner could not access mysqli connection.');
			return;
		}
		$conn->query("SET FOREIGN_KEY_CHECKS = 0");
		$conn->query("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES'");
		if($conn->multi_query($sql)){
			do {
				if($res = $conn->store_result()) $res->free();
			} while($conn->more_results() && $conn->next_result());
		}
		if($conn->error){
			log_message('error', '4.0.2 migration error: ' . $conn->error);
		} else {
			log_message('info', '4.0.2 migration completed successfully.');
		}
		$conn->query("SET FOREIGN_KEY_CHECKS = 1");
	}

}
