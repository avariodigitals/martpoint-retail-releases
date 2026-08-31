<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permission Audit Controller
 *
 * Scans all controllers for permission checks and hardcoded access guards.
 * Helps identify:
 *  - Which controllers use permission_check() / permissions()
 *  - Which controllers use hardcoded role_id / is_admin / is_store_admin / special_access
 *  - Which controllers have NO permission checks at all (potential security gaps)
 *  - Which permission keys are referenced in code but missing from Roles_model
 *
 * Access: Super Admin (role_id 1) or Store Admin (role_id 2) only.
 */
class Permission_audit extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$role_id = (int)$this->session->userdata('role_id');
		$user_id = (int)$this->session->userdata('inv_userid');
		if($user_id !== 1 && $role_id !== 1 && !is_store_admin()){
			$this->show_access_denied_page();
			return;
		}
	}

	/**
	 * Main audit dashboard
	 */
	public function index(){
		$data = $this->data;
		$data['page_title'] = 'Permission Audit';
		$data['audit'] = $this->run_audit();
		$this->load->view('permission-audit', $data);
	}

	/**
	 * Run the full audit and return structured results
	 */
	private function run_audit(){
		$result = [
			'controllers' => [],
			'unprotected' => [],
			'hardcoded' => [],
			'permission_keys_used' => [],
			'permission_keys_defined' => [],
			'missing_from_roles_model' => [],
			'summary' => []
		];

		$controller_dir = APPPATH . 'controllers';
		$files = $this->scan_controllers($controller_dir);

		// Get all permission keys defined in Roles_model
		$result['permission_keys_defined'] = $this->get_defined_permission_keys();

		foreach($files as $file_info){
			$classname = $file_info['classname'];
			$filepath = $file_info['filepath'];
			$rel_path = str_replace(APPPATH, 'application/', $filepath);

			$content = file_get_contents($filepath);

			$entry = [
				'classname' => $classname,
				'file' => $rel_path,
				'methods_total' => 0,
				'methods_with_checks' => 0,
				'methods_without_checks' => 0,
				'permission_keys' => [],
				'hardcoded_checks' => [],
				'has_constructor_check' => false,
				'status' => 'ok'
			];

			// Extract permission keys used
			preg_match_all("/permissions\(['\"]([a-z_]+)['\"]\)/i", $content, $perm_matches);
			preg_match_all("/permission_check\(['\"]([a-z_]+)['\"]\)/i", $content, $perm_check_matches);
			preg_match_all("/permission_check_with_msg\(['\"]([a-z_]+)['\"]\)/i", $content, $perm_msg_matches);
			$all_perms = array_unique(array_merge(
				$perm_matches[1] ?? [],
				$perm_check_matches[1] ?? [],
				$perm_msg_matches[1] ?? []
			));
			sort($all_perms);
			$entry['permission_keys'] = $all_perms;

			foreach($all_perms as $p){
				if(!isset($result['permission_keys_used'][$p])){
					$result['permission_keys_used'][$p] = [];
				}
				$result['permission_keys_used'][$p][] = $classname;
			}

			// Check for hardcoded role checks
			$hardcoded = [];
			if(preg_match("/role_id\s*==\s*1/", $content) || preg_match("/role_id'\s*\)\s*==\s*1/", $content)){
				$hardcoded[] = 'role_id == 1 (Super Admin)';
			}
			if(preg_match("/role_id\s*==\s*2/", $content) || preg_match("/role_id'\s*\)\s*==\s*2/", $content)){
				$hardcoded[] = 'role_id == 2 (Store Admin)';
			}
			if(strpos($content, 'is_admin()') !== false){
				$hardcoded[] = 'is_admin()';
			}
			if(strpos($content, 'is_store_admin()') !== false){
				$hardcoded[] = 'is_store_admin()';
			}
			if(strpos($content, 'special_access()') !== false){
				$hardcoded[] = 'special_access()';
			}
			if(strpos($content, 'inv_userid') !== false && (preg_match("/inv_userid'\s*\)\s*==\s*[12]/", $content))){
				$hardcoded[] = 'inv_userid == 1|2 (Admin bypass)';
			}
			$entry['hardcoded_checks'] = $hardcoded;

			// Count public methods
			preg_match_all("/public\s+function\s+(\w+)\s*\(/", $content, $method_matches);
			$methods = $method_matches[1] ?? [];
			// Filter out constructor and private helpers
			$methods = array_filter($methods, function($m){
				return $m !== '__construct' && $m !== 'get_instance';
			});
			$entry['methods_total'] = count($methods);

			// Check if constructor has a permission check
			if(preg_match("/function\s+__construct[\s\S]{0,2000}?(permission_check|permissions\(|special_access|is_admin|is_store_admin|role_id)/", $content)){
				$entry['has_constructor_check'] = true;
			}

			// Find methods without any permission check
			// A method is "protected" if the constructor has a check OR the method itself has one
			$unprotected_methods = [];
			foreach($methods as $method){
				// Check if method body contains a permission check
				$pattern = "/function\s+" . preg_quote($method) . "\s*\([\s\S]*?(?:permission_check|permissions\(|special_access|is_admin|is_store_admin|role_id|_can_edit|_can_view)/";
				$method_protected = preg_match($pattern, $content) || $entry['has_constructor_check'];
				if(!$method_protected){
					$unprotected_methods[] = $method;
				}
			}
			$entry['methods_without_checks'] = $unprotected_methods;
			$entry['methods_with_checks'] = count($methods) - count($unprotected_methods);

			// Status
			if(count($unprotected_methods) > 0 && !$entry['has_constructor_check']){
				$entry['status'] = 'warning';
				$result['unprotected'][] = [
					'classname' => $classname,
					'file' => $rel_path,
					'methods' => $unprotected_methods
				];
			}
			if(!empty($hardcoded)){
				$result['hardcoded'][] = [
					'classname' => $classname,
					'file' => $rel_path,
					'checks' => $hardcoded
				];
			}

			$result['controllers'][] = $entry;
		}

		// Find permission keys used in code but not defined in Roles_model
		$result['missing_from_roles_model'] = array_diff(
			array_keys($result['permission_keys_used']),
			$result['permission_keys_defined']
		);
		sort($result['missing_from_roles_model']);

		// Summary
		$result['summary'] = [
			'total_controllers' => count($result['controllers']),
			'total_methods' => array_sum(array_column($result['controllers'], 'methods_total')),
			'methods_with_checks' => array_sum(array_column($result['controllers'], 'methods_with_checks')),
			'methods_without_checks' => count($result['unprotected']),
			'controllers_with_hardcoded' => count($result['hardcoded']),
			'permission_keys_used' => count($result['permission_keys_used']),
			'permission_keys_defined' => count($result['permission_keys_defined']),
			'keys_missing_from_roles_model' => count($result['missing_from_roles_model'])
		];

		return $result;
	}

	/**
	 * Scan the controllers directory recursively for PHP controller files
	 */
	private function scan_controllers($dir){
		$files = [];
		$items = scandir($dir);
		foreach($items as $item){
			if($item === '.' || $item === '..') continue;
			$path = $dir . '/' . $item;
			if(is_dir($path)){
				$files = array_merge($files, $this->scan_controllers($path));
			} else if(preg_match('/\.php$/', $item)){
				$content = file_get_contents($path);
				if(preg_match('/class\s+(\w+)\s+extends\s+MY_Controller/i', $content, $m) ||
				   preg_match('/class\s+(\w+)\s+extends\s+CI_Controller/i', $content, $m)){
					$files[] = [
						'classname' => $m[1],
						'filepath' => $path
					];
				}
			}
		}
		return $files;
	}

	/**
	 * Get all permission keys defined in Roles_model::set_persmissions()
	 */
	private function get_defined_permission_keys(){
		// Read the Roles_model file and extract the permission keys from the array
		$file = APPPATH . 'models/Roles_model.php';
		if(!file_exists($file)){
			return [];
		}
		$content = file_get_contents($file);
		preg_match_all("/'([a-z_]+)'/i", $content, $matches);
		// Filter to only likely permission keys (lowercase with underscores)
		$keys = array_filter($matches[1] ?? [], function($k){
			return preg_match('/^[a-z][a-z_]+$/', $k) && strlen($k) > 3;
		});
		return array_unique($keys);
	}
}
