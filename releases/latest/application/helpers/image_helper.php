<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('mp_minified_image_url')){
	/**
	 * Return a URL for a width-constrained, cached copy of an uploaded image.
	 * On first request the image is resized on demand by Storefront::image();
	 * subsequent requests are served as a static file from uploads/cache/img/.
	 */
	function mp_minified_image_url($path = '', $width = 600, $quality = 85){
		if(empty($path)) return '';
		$width = (int)$width;
		if($width < 10) return base_url($path);

		$rel = ltrim(str_replace('\\', '/', $path), '/');
		// Pass external / remote URLs through unchanged
		if(strpos($rel, 'http') === 0) return $rel;

		$ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
		$allowed = ['jpg','jpeg','png','gif','webp','bmp'];
		if(!in_array($ext, $allowed)) return base_url($path);

		$cacheRel = 'uploads/cache/img/' . $width . '/' . md5($rel . '|q' . $quality) . '.' . $ext;
		$cacheFull = FCPATH . $cacheRel;
		$origFull = FCPATH . $rel;

		if(file_exists($cacheFull) && file_exists($origFull) && filemtime($cacheFull) >= filemtime($origFull)){
			return base_url($cacheRel);
		}

		$segments = explode('/', $rel);
		$encoded = implode('/', array_map('rawurlencode', $segments));
		return base_url('image/' . $width . '/' . $encoded);
	}
}

if(!function_exists('mp_image_tag')){
	/**
	 * Build an <img> tag with lazy loading, async decoding and optional minified src.
	 */
	function mp_image_tag($src = '', $attrs = []){
		if(empty($src)) return '';
		$width = isset($attrs['width']) ? (int)$attrs['width'] : 0;
		$srcAttr = $width > 0 ? mp_minified_image_url($src, $width, $attrs['quality'] ?? 85) : $src;

		$lazy = isset($attrs['lazy']) && $attrs['lazy'] === false ? false : true;
		$tag = '<img src="' . $srcAttr . '"';
		if($lazy) $tag .= ' loading="lazy" decoding="async"';
		unset($attrs['width'], $attrs['quality'], $attrs['lazy']);
		foreach($attrs as $k => $v){
			$tag .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
		}
		$tag .= '>';
		return $tag;
	}
}
