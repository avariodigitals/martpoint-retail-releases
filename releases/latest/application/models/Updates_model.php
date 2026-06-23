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
		if($this->db_version <=2.8){
			
			$result = $this->db->query("SHOW COLUMNS FROM `db_store` LIKE 'qty_decimals'");

            if(!$result->num_rows()){
            	//Update for 2.8 version only
				$q1 = $this->db->query("ALTER TABLE `db_store` ADD COLUMN `qty_decimals` INT(5) DEFAULT 2 NULL");if(!$q1){ echo "failed"; exit();}
            }
			

		}

	}

}
