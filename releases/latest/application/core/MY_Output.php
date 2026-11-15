<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Keep connected custom-domain storefront URLs on the merchant's host and
 * remove the internal /store/{slug} dispatch path from rendered HTML.
 * The path remains available on MartPoint's shared domain, where the slug is
 * required to identify a store.
 */
class MY_Output extends CI_Output {

	public function _display($output = ''){
		$slug = trim((string)($_SERVER['MP_CUSTOM_DOMAIN_STORE_SLUG'] ?? ''));
		$contentType = strtolower((string)$this->get_content_type());
		if($slug !== '' && strpos($contentType, 'text/html') === 0){
			if($output === '') $output = $this->final_output;
			$config =& load_class('Config', 'core');
			$base = rtrim((string)$config->item('base_url'), '/');
			if($base !== ''){
				$internal = $base . '/store/' . rawurlencode($slug);
				$pattern = '~' . preg_quote($internal, '~') . '(?![A-Za-z0-9_-])~';
				$output = preg_replace($pattern, $base, (string)$output);
			}
		}

		parent::_display($output);
	}
}
