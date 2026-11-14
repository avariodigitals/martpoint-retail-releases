<?php

/**
 * Is this the vendor's CENTRAL install?
 *
 * Central is the control box that runs the Fleet Manager and generates every
 * client release. Code that must exist on client installs but stay inert there
 * (Fleet panel, Manifest generator, Release server, incident controls) gates
 * itself on this function.
 *
 * The switch is `application/config/central.php`, which is deliberately NEVER
 * shipped to clients — it is excluded from both the release manifest and the
 * full deployment package. So on every client install this returns false and
 * the gated features are unreachable.
 *
 * central_domain is a second lock: even if central.php leaked, central tooling
 * only activates on the exact host, so a copied file cannot turn a customer's
 * store into a central server. Empty string = allow any host.
 *
 * NOTE: this function is autoloaded (saas helper) because Fleet's constructor
 * calls it unguarded — an undefined function here is a hard fatal.
 *
 * @return bool
 */
if ( ! function_exists('mp_is_central')) {
    function mp_is_central() {
        static $is_central = null;
        if ($is_central !== null) {
            return $is_central;
        }

        $is_central = false;

        $CI = get_instance();
        if ( ! is_object($CI)) {
            return $is_central;
        }

        // -------------------------------------------------------------------
        // Read config/central.php DIRECTLY.
        //
        // This file is NOT in autoload.php and nothing calls config->load(),
        // so `$CI->config->item('is_central')` returns NULL even on the vendor
        // box — the gate would evaluate false forever and /fleet would 404 with
        // the flag file sitting right there. Reading it here makes the switch
        // self-contained: the file's presence IS the switch.
        //
        // Included once (static-cached below), guarded, and never fatal on
        // clients because the file simply does not exist there.
        // -------------------------------------------------------------------
        $flag   = null;
        $domain = '';

        $cfgFile = APPPATH . 'config/central.php';
        if (is_file($cfgFile)) {
            // CI3 config files expect $config to exist and return nothing.
            $config = array();
            include $cfgFile;
            if (is_array($config) && array_key_exists('is_central', $config)) {
                $flag   = $config['is_central'];
                $domain = (string) ($config['central_domain'] ?? '');
            }
        }

        // Fall back to CI config in case a host DOES load it (e.g. autoload.php
        // was updated); keeps both wiring styles working.
        if ($flag === null) {
            $flag   = $CI->config->item('is_central');
            $domain = (string) $CI->config->item('central_domain');
        }

        if ($flag !== true) {
            return $is_central;
        }

        if ($domain === '') {
            $is_central = true;
            return $is_central;
        }

        $host = '';

        // Host is authoritative; HTTP_HOST is client-supplied so it is only a
        // fallback for CLI/cron. Both are normalised: lowercased, port stripped,
        // and a trailing root dot removed (FQDN form some proxies emit).
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '') {
            $host = $_SERVER['SERVER_NAME'];
        } elseif (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '') {
            $host = $_SERVER['HTTP_HOST'];
        }

        if ($host !== '') {
            $host = strtolower(trim($host));
            // strip port
            $host = preg_replace('/:\d+$/', '', $host);
            // strip trailing root dot
            $host = rtrim($host, '.');
        }

        $is_central = ($host !== '' && strcasecmp($host, rtrim(strtolower($domain), '.')) === 0);

        return $is_central;
    }
}

/**
 * Is this the VENDOR's code, regardless of which hostname it answers on?
 *
 * mp_is_central() additionally requires the request host to match
 * `central_domain`. That is the right gate for exposing the Fleet/Manifest
 * panels — a leaked copy must not turn a client install into a central server.
 * But it is the WRONG signal for "is this our own box", because it recognises
 * exactly one hostname.
 *
 * The vendor runs this same tree on a dev host (martpointretailapp.test), a
 * staging clone, or a restored dump. None of those match central_domain, so
 * mp_is_central() is false — the box treats itself as a CLIENT, adopts fleet_url
 * from the release manifest and registers in the live fleet. The vendor's own
 * machine then appears in the Fleet Manager as a customer install.
 *
 * config/central.php is vendor-only and excluded from every release, so its
 * presence IS the reliable "this is our code" marker — correct on any hostname.
 *
 * Deliberately enables NO privileged panel; keep using mp_is_central() for that.
 *
 * @return bool
 */
if ( ! function_exists('mp_is_vendor_box')) {
    function mp_is_vendor_box() {
        static $is_vendor = null;
        if ($is_vendor !== null) {
            return $is_vendor;
        }
        $is_vendor = false;
        // The marker file is the switch — same reasoning as mp_is_central().
        if (is_file(APPPATH . 'config/central.php')) {
            $is_vendor = true;
        } else {
            $CI = get_instance();
            if (is_object($CI) && $CI->config->item('is_central') !== null) {
                $is_vendor = true;
            }
        }
        return $is_vendor;
    }
}

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