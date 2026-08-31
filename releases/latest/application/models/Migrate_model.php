<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate_model extends CI_Model {

	private $old_conn = null;
	private $db_config = null;
	private $skip_admin = false;

	// Order matters: lookups and masters before transactions
	public $import_plan = [
		['old' => 'db_roles',              'new' => 'db_roles',              'store' => false],
		['old' => 'db_warehouse',          'new' => 'db_warehouse',          'store' => true],
		['old' => 'db_category',           'new' => 'db_category',           'store' => true],
		['old' => 'db_brands',             'new' => 'db_brands',             'store' => true],
		['old' => 'db_units',              'new' => 'db_units',              'store' => true],
		['old' => 'db_tax',                'new' => 'db_tax',                'store' => true],
		['old' => 'db_paymenttypes',       'new' => 'db_paymenttypes',       'store' => true],
		['old' => 'db_expense_category',   'new' => 'db_expense_category',   'store' => true],
		['old' => 'db_users',              'new' => 'db_users',              'store' => true, 'users' => true],
		['old' => 'db_userswarehouses',    'new' => 'db_userswarehouses',    'store' => true],
		['old' => 'db_customers',          'new' => 'db_customers',          'store' => true],
		['old' => 'db_suppliers',          'new' => 'db_suppliers',          'store' => true],
		['old' => 'db_items',              'new' => 'db_items',              'store' => true],
		['old' => 'db_services',           'new' => 'db_services',           'store' => true],
		['old' => 'db_warehouseitems',     'new' => 'db_warehouseitems',     'store' => true],
		['old' => 'db_sales',              'new' => 'db_sales',              'store' => true],
		['old' => 'db_salesitems',         'new' => 'db_salesitems',         'store' => true],
		['old' => 'db_salespayments',      'new' => 'db_salespayments',      'store' => true],
		['old' => 'db_salesreturn',        'new' => 'db_salesreturn',        'store' => true],
		['old' => 'db_salespaymentsreturn','new' => 'db_salespaymentsreturn','store' => true],
		['old' => 'db_purchase',           'new' => 'db_purchase',           'store' => true],
		['old' => 'db_purchaseitems',      'new' => 'db_purchaseitems',      'store' => true],
		['old' => 'db_purchasepayments',   'new' => 'db_purchasepayments',   'store' => true],
		['old' => 'db_expense',            'new' => 'db_expense',            'store' => true],
	];

	public function __construct(){
		parent::__construct();
	}

	private function _get_db_config(){
		if($this->db_config === null){
			include(APPPATH.'config/database.php');
			$this->db_config = $db[$active_group];
		}
		return $this->db_config;
	}

	private function _connect_staging(){
		if($this->old_conn !== null) return;
		// Staging lives inside the same database with an `old_` prefix.
		$this->old_conn = $this->db;
	}

	private function _old_table($table){
		return 'old_'.$table;
	}

	private function _sanitize_sql_file($in, $out){
		$content = file_get_contents($in);
		if($content === false) return false;

		// Remove mysqldump client messages that sometimes get mixed into the output
		$content = preg_replace('/^mysqldump:.*$/m', '', $content);

		$keywords = [
			'CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?',
			'DROP\s+TABLE\s+IF\s+EXISTS',
			'INSERT\s+INTO',
			'ALTER\s+TABLE',
			'REFERENCES',
			'CONSTRAINT',
			'LOCK\s+TABLES',
		];
		$pattern = '/('.implode('|', $keywords).')\s+(`?)([a-zA-Z0-9_]+)(`?)/i';
		$content = preg_replace_callback($pattern, function($m){
			if(strpos($m[3], 'old_') === 0) return $m[0];
			return $m[1].' '.$m[2].'old_'.$m[3].$m[4];
		}, $content);

		// Strip any CREATE DATABASE statement, we are loading into the live DB
		$content = preg_replace('/^CREATE\s+DATABASE[^;]+;$/mi', '', $content);

		return file_put_contents($out, $content) !== false;
	}

	private function _drop_existing_staging_tables(){
		$this->db->query("SET FOREIGN_KEY_CHECKS = 0");
		$tables = $this->db->query("SHOW TABLES LIKE 'old\\_%'")->result_array();
		foreach($tables as $row){
			$name = array_values((array)$row)[0];
			$this->db->query("DROP TABLE IF EXISTS `{$name}`");
		}
		$this->db->query("SET FOREIGN_KEY_CHECKS = 1");
	}

	public function load_sql_to_staging($sql_path){
		$cfg = $this->_get_db_config();
		$upload_dir = dirname($sql_path);
		$sanitized = $upload_dir.'/migrate_old_sanitized_'.time().'.sql';

		if(!$this->_sanitize_sql_file($sql_path, $sanitized)){
			return false;
		}

		$this->_drop_existing_staging_tables();

		// Prefer the mysql CLI for large dumps.
		$host = escapeshellarg($cfg['hostname']);
		$user = escapeshellarg($cfg['username']);
		$db   = escapeshellarg($cfg['database']);
		$file = escapeshellarg($sanitized);
		putenv('MYSQL_PWD='.$cfg['password']);
		$cmd = "mysql --one-database -h {$host} -u {$user} {$db} < {$file} 2>&1";
		exec($cmd, $out, $ret);
		putenv('MYSQL_PWD');

		if($ret === 0){
			$this->old_conn = $this->db;
			return true;
		}

		// Fallback: load chunk-by-chunk through CI's database class
		return $this->_load_sql_fallback($sanitized);
	}

	private function _load_sql_fallback($sql_path){
		$handle = fopen($sql_path, 'r');
		if(!$handle) return false;
		$buffer = '';
		$in_string = false;
		$string_char = '';
		$escaped = false;
		while(!feof($handle)){
			$chunk = fread($handle, 8192);
			for($i = 0; $i < strlen($chunk); $i++){
				$ch = $chunk[$i];
				if($in_string){
					if($escaped){
						$escaped = false;
					} elseif($ch === '\\'){
						$escaped = true;
					} elseif($ch === $string_char){
						$in_string = false;
					}
				} else {
					if($ch === "'" || $ch === '"' || $ch === '`'){
						$in_string = true;
						$string_char = $ch;
					} elseif($ch === ';'){
						$stmt = trim($buffer);
						$buffer = '';
						if($stmt !== '' && !preg_match('/^(USE|CREATE\s+DATABASE)\s+/i', $stmt)){
							$this->db->query($stmt);
						}
						continue;
					}
				}
				$buffer .= $ch;
			}
		}
		fclose($handle);
		$stmt = trim($buffer);
		if($stmt !== '' && !preg_match('/^(USE|CREATE\s+DATABASE)\s+/i', $stmt)){
			$this->db->query($stmt);
		}
		$this->old_conn = $this->db;
		return true;
	}

	public function old_table_exists($table){
		$this->_connect_staging();
		$old = $this->_old_table($table);
		$q = $this->old_conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$this->old_conn->database, $old]);
		return $q->num_rows() > 0;
	}

	private function _get_table_columns($table, $database){
		$conn = ($database === $this->db->database) ? $this->db : $this->old_conn;
		if($database === $this->db->database){
			// Live target tables keep their original names
			$q = $conn->query("SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE 
								FROM INFORMATION_SCHEMA.COLUMNS 
								WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
								ORDER BY ORDINAL_POSITION", [$database, $table]);
		} else {
			// Staging tables are prefixed with old_
			$q = $conn->query("SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE 
								FROM INFORMATION_SCHEMA.COLUMNS 
								WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
								ORDER BY ORDINAL_POSITION", [$database, $this->_old_table($table)]);
		}
		return $q->result_array();
	}

	public function analyze(){
		$this->_connect_staging();
		$analysis = [];
		foreach($this->import_plan as $item){
			$old_exists = $this->old_table_exists($item['old']);
			$old_count = 0;
			$old_cols = [];
			if($old_exists){
				$old = $this->_old_table($item['old']);
				$q = $this->old_conn->query("SELECT COUNT(*) as c FROM `{$old}`");
				$old_count = (int)$q->row()->c;
				$old_cols = $this->_get_table_columns($item['old'], $this->old_conn->database);
			}
			$q = $this->db->query("SELECT COUNT(*) as c FROM `{$item['new']}`");
			$new_count = (int)$q->row()->c;
			$new_cols = $this->_get_table_columns($item['new'], $this->db->database);

			$old_col_names = array_column($old_cols, 'COLUMN_NAME');
			$new_col_names = array_column($new_cols, 'COLUMN_NAME');
			$common = array_intersect($old_col_names, $new_col_names);
			$missing = array_diff($new_col_names, $old_col_names);

			$analysis[] = [
				'old_table' => $item['old'],
				'new_table' => $item['new'],
				'old_exists' => $old_exists,
				'old_count'  => $old_count,
				'new_count'  => $new_count,
				'common'     => count($common),
				'missing'    => count($missing),
			];
		}
		return $analysis;
	}

	public function import_all($target_store_id, $password_option, $default_password = null, $skip_admin = false){
		$this->skip_admin = $skip_admin;
		$this->_connect_staging();
		$log = [];
		foreach($this->import_plan as $item){
			if(!$this->old_table_exists($item['old'])){
				$log[] = ['old_table' => $item['old'], 'new_table' => $item['new'], 'status' => 'old table not found', 'rows' => 0];
				continue;
			}
			$result = $this->_import_table($item, $target_store_id);
			$log[] = $result;
		}

		if($password_option === 'reset' && !empty($default_password) && $this->old_table_exists('db_users')){
			$hash = password_hash($default_password, PASSWORD_BCRYPT);
			$old = $this->_old_table('db_users');
			$admin_skip = $this->skip_admin ? 'AND u.id != 1 AND ou.id != 1' : '';
			$this->db->query("UPDATE db_users u
							  INNER JOIN `{$old}` ou ON u.id = ou.id
							  SET u.password = ?
							  WHERE u.store_id = ? {$admin_skip}", [$hash, $target_store_id]);
			$log[] = ['old_table' => 'db_users', 'new_table' => 'db_users', 'status' => 'passwords reset', 'rows' => $this->db->affected_rows()];
		}

		return $log;
	}

	private function _import_table($item, $target_store_id){
		$old_table = $this->_old_table($item['old']);
		$new_table = $item['new'];
		$set_store = !empty($item['store']);

		$new_cols = $this->_get_table_columns($new_table, $this->db->database);
		$old_cols = $this->_get_table_columns($item['old'], $this->old_conn->database);
		$old_col_names = array_column($old_cols, 'COLUMN_NAME');
		$db = $this->db->database;

		$insert_cols = [];
		$select_exprs = [];

		foreach($new_cols as $col){
			$name = $col['COLUMN_NAME'];
			if($set_store && $name === 'store_id'){
				$insert_cols[] = '`store_id`';
				$select_exprs[] = (int)$target_store_id;
			} elseif(in_array($name, $old_col_names)){
				$insert_cols[] = '`'.$name.'`';
				$select_exprs[] = 'o.`'.$name.'`';
			}
		}

		if(empty($insert_cols)){
			return ['old_table' => $item['old'], 'new_table' => $new_table, 'status' => 'no matching columns', 'rows' => 0];
		}

		$where = '';
		if($this->skip_admin){
			if($new_table === 'db_users'){
				$where = ' WHERE o.id != 1';
			} elseif($new_table === 'db_userswarehouses'){
				$where = ' WHERE o.user_id != 1';
			}
		}

		$sql = "REPLACE INTO `{$db}`.`{$new_table}` (".implode(', ', $insert_cols).")
				SELECT ".implode(', ', $select_exprs)."
				FROM `{$db}`.`{$old_table}` AS o{$where}";
		$this->db->query($sql);

		return [
			'old_table' => $item['old'],
			'new_table' => $new_table,
			'status'    => 'ok',
			'rows'      => $this->db->affected_rows(),
		];
	}

	public function extract_uploads_zip($zip_path){
		if(!class_exists('ZipArchive')){
			return false;
		}
		$zip = new ZipArchive();
		$extract_to = sys_get_temp_dir().'/mp_migrate_uploads_'.time();
		if(!is_dir($extract_to)) mkdir($extract_to, 0777, true);
		if($zip->open($zip_path) === TRUE){
			$zip->extractTo($extract_to);
			$zip->close();
			return $extract_to;
		}
		return false;
	}

	public function copy_uploads($source_dir, $dest_dir = null){
		if($dest_dir === null) $dest_dir = FCPATH.'uploads';
		if(!is_dir($source_dir)){
			return ['status' => 'error', 'message' => 'Source uploads folder not found: '.$source_dir];
		}
		if(!is_dir($dest_dir)){
			mkdir($dest_dir, 0777, true);
		}

		$src = rtrim($source_dir, '/');
		$dst = rtrim($dest_dir, '/');
		$cmd = "cp -R -n ".escapeshellarg($src)."/* ".escapeshellarg($dst)."/ 2>&1";
		exec($cmd, $out, $ret);
		if($ret === 0){
			return ['status' => 'ok', 'message' => 'Uploads copied'];
		}

		$this->_recurse_copy($src, $dst);
		return ['status' => 'ok', 'message' => 'Uploads copied (PHP fallback)'];
	}

	private function _recurse_copy($src, $dst){
		$dir = opendir($src);
		if(!$dir) return;
		while(($file = readdir($dir)) !== false){
			if($file === '.' || $file === '..') continue;
			$src_file = $src.'/'.$file;
			$dst_file = $dst.'/'.$file;
			if(is_dir($src_file)){
				if(!is_dir($dst_file)) mkdir($dst_file, 0777, true);
				$this->_recurse_copy($src_file, $dst_file);
			} elseif(!file_exists($dst_file)){
				copy($src_file, $dst_file);
			}
		}
		closedir($dir);
	}

	public function cleanup(){
		$this->_drop_existing_staging_tables();
		$this->old_conn = null;
		return true;
	}
}
