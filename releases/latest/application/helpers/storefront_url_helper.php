<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Canonicalize old tenant paths without losing form bodies or changing host. */
if(!function_exists('mp_storefront_legacy_redirect')){
	function mp_storefront_legacy_redirect($slug, $path, $query = '', $base = '', $method = 'GET'){
		$prefix = '/store/' . rawurlencode(trim((string)$slug, '/'));
		if($path !== $prefix && strpos($path, $prefix . '/') !== 0) return null;
		// A double slash or backslash must never turn Location into an
		// attacker-controlled protocol-relative URL.
		$clean = '/' . ltrim(str_replace('\\', '/', substr($path, strlen($prefix))), '/');
		return ['location'=>(string)$base . $clean . (string)$query,
			'status'=>in_array(strtoupper((string)$method), ['GET','HEAD'], true) ? 301 : 307];
	}
}

/**
 * Build a public URL for a storefront page.
 * Connected custom domains identify the tenant by host; shared MartPoint
 * URLs continue to include /store/{slug}.
 */
if(!function_exists('mp_storefront_url')){
	function mp_storefront_url($slug, $path = ''){
		$slug = trim((string)$slug, '/');
		$path = ltrim((string)$path, '/');
		$customDomainSlug = trim((string)($_SERVER['MP_CUSTOM_DOMAIN_STORE_SLUG'] ?? ''));
		if($customDomainSlug !== '' && hash_equals($customDomainSlug, $slug)){
			return base_url($path);
		}
		$storePath = 'store/' . rawurlencode($slug);
		if($path !== '') $storePath .= '/' . $path;
		return base_url($storePath);
	}
}
