<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Template Placeholder Parser
 * Replaces {placeholder} with provided data.
 */
class Email_template_parser {

	protected $missing = [];

	/**
	 * Parse a string, replacing {key} with values from $data
	 *
	 * @param string $content
	 * @param array  $data
	 * @return string
	 */
	public function parse($content, array $data = []){
		$this->missing = [];

		$CI =& get_instance();

		// Resolve branch_name from session or default warehouse
		$branch_name = 'Main Branch';
		$store_name  = 'MartPoint';
		if(function_exists('get_current_store_id')){
			$selected_branch = $CI->session->userdata('selected_branch_id');
			if(!empty($selected_branch)){
				$wh = $CI->db->where('id', $selected_branch)->get('db_warehouse')->row();
				if($wh){ $branch_name = $wh->warehouse_name; }
			} else {
				$wh = $CI->db->where('store_id', get_current_store_id())->where('warehouse_type', 'System')->get('db_warehouse')->row();
				if($wh){ $branch_name = $wh->warehouse_name; }
			}
			$store_details = function_exists('get_store_details') ? get_store_details() : null;
			if($store_details && isset($store_details->store_name)){
				$store_name = $store_details->store_name;
			}
		}

		// Add global placeholders
		$data['app_name']     = $data['app_name']     ?? 'MartPoint Retail';
		$data['current_year'] = $data['current_year'] ?? date('Y');
		$data['branch_name']  = $data['branch_name']  ?? $branch_name;
		$data['store_name']   = $data['store_name']   ?? $store_name;

		return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function($matches) use ($data){
			$key = $matches[1];
			if(array_key_exists($key, $data)){
				return $data[$key];
			}
			$this->missing[] = $key;
			return ''; // empty string for missing placeholders
		}, $content);
	}

	/**
	 * Get list of placeholders that were missing during last parse
	 * @return array
	 */
	public function getMissing(){
		return array_unique($this->missing);
	}

	/**
	 * Extract all placeholders from a template string
	 * @param string $content
	 * @return array
	 */
	public function extractPlaceholders($content){
		preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $content, $matches);
		return array_unique($matches[1]);
	}
}
