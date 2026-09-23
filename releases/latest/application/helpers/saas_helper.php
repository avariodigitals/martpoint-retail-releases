<?php

function store_module(){
    if (function_exists('mp_feature_flag_raw')) {
        $flag = mp_feature_flag_raw('multi_store');
        if ($flag !== null) {
            return $flag;
        }
    }
    return false;
  }

function special_access(){
	if(is_admin()){//is saas admin
		return true;
	}
	else if(is_store_admin()){
		return true;
	}
	return false;
}

/**
 * True only on the vendor's central install. The flag lives in
 * application/config/central.php, which is excluded from release packages —
 * client installs lack the file and always return false here, so central
 * tooling (Fleet, Manifest, Release, package serving) stays inert on them.
 */
function mp_is_central(){
	$CI =& get_instance();
	$CI->config->load('central', false, true);
	if ($CI->config->item('is_central') !== true) {
		return false;
	}
	// Optional domain pin: even if this config file ever reached a client
	// install, central features stay off unless the request host matches.
	$domain = trim((string) $CI->config->item('central_domain'));
	if ($domain !== '') {
		$host = $_SERVER['HTTP_HOST'] ?? '';
		return strcasecmp($host, $domain) === 0;
	}
	return true;
}