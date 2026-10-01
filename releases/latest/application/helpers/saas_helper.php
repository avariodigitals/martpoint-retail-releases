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

/**
 * Current incident/banner state for this install (db_sitesettings row id=1).
 * Written locally via Site Settings or mirrored from the MartPoint backend's
 * /api/status feed by Updater::syncStatusFeed(). Returns a normalized array;
 * always 'active' => false when the columns are not migrated yet, so callers
 * never need a schema check.
 */
function mp_get_incident(){
	if (isset($GLOBALS['__mp_incident_cache'])) {
		return $GLOBALS['__mp_incident_cache'];
	}
	$incident = [
		'active'     => false,
		'severity'   => 'investigating',
		'message'    => '',
		'url'        => 'https://www.martpoint.com.ng/status',
		'started_at' => '',
		'source'     => 'local',
	];
	try {
		$CI =& get_instance();
		if (!$CI->db->field_exists('incident_active', 'db_sitesettings')) {
			return $incident;
		}
		$row = $CI->db->select('incident_active,incident_severity,incident_message,incident_url,incident_started_at,incident_source')
			->where('id', 1)->get('db_sitesettings')->row();
		if ($row) {
			$incident['active']     = (int) ($row->incident_active ?? 0) === 1;
			$incident['severity']   = (string) ($row->incident_severity ?? 'investigating') ?: 'investigating';
			$incident['message']    = (string) ($row->incident_message ?? '');
			$incident['url']        = (string) ($row->incident_url ?? '') ?: 'https://www.martpoint.com.ng/status';
			$incident['started_at'] = (string) ($row->incident_started_at ?? '');
			$incident['source']     = (string) ($row->incident_source ?? 'local') ?: 'local';
		}
	} catch (Throwable $e) {
	}
	$GLOBALS['__mp_incident_cache'] = $incident;
	return $incident;
}

/**
 * Write the incident state. Self-heals the columns so feed sync and local
 * saves still land on installs that haven't run the 4.0.9.59 migration.
 * Pass $source 'central' when the state came from the remote status API —
 * the next all-clear may then clear it without touching a store's own
 * locally-set notice.
 */
function mp_set_incident(array $fields, $source = 'local'){
	$CI =& get_instance();
	try {
		if (!$CI->db->field_exists('incident_active', 'db_sitesettings')) {
			$CI->db->query("ALTER TABLE `db_sitesettings`
				ADD COLUMN `incident_active` TINYINT(1) NOT NULL DEFAULT 0,
				ADD COLUMN `incident_severity` VARCHAR(20) NOT NULL DEFAULT 'investigating',
				ADD COLUMN `incident_message` VARCHAR(255) NOT NULL DEFAULT '',
				ADD COLUMN `incident_url` VARCHAR(255) NOT NULL DEFAULT 'https://www.martpoint.com.ng/status',
				ADD COLUMN `incident_started_at` DATETIME NULL DEFAULT NULL,
				ADD COLUMN `incident_source` VARCHAR(20) NOT NULL DEFAULT 'local',
				ADD COLUMN `status_feed_url` VARCHAR(255) NOT NULL DEFAULT 'https://www.martpoint.com.ng/api/status'");
		}
		$cur = mp_get_incident();
		$data = [];
		if (array_key_exists('active', $fields)) {
			$data['incident_active'] = !empty($fields['active']) ? 1 : 0;
		}
		if (isset($fields['severity'])) {
			$sev = (string) $fields['severity'];
			$data['incident_severity'] = in_array($sev, ['investigating', 'identified', 'monitoring', 'maintenance'], true) ? $sev : 'investigating';
		}
		if (isset($fields['message'])) {
			$data['incident_message'] = substr((string) $fields['message'], 0, 255);
		}
		if (isset($fields['url'])) {
			$url = trim((string) $fields['url']);
			$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
			$ok = $url !== '' && in_array($scheme, ['http', 'https'], true)
				&& filter_var($url, FILTER_VALIDATE_URL);
			$data['incident_url'] = $ok ? substr($url, 0, 255) : 'https://www.martpoint.com.ng/status';
		}
		if (isset($fields['started_at'])) {
			// Remote feeds send ISO 8601 (2026-10-01T14:00:00Z) — normalize to a
			// MySQL DATETIME literal; unparseable input clears rather than fails.
			$ts = strtotime((string) $fields['started_at']);
			$data['incident_started_at'] = $ts ? date('Y-m-d H:i:s', $ts) : null;
		}
		$activating = ($data['incident_active'] ?? $cur['active']) && !$cur['active'];
		if ($activating && !isset($data['incident_started_at'])) {
			$data['incident_started_at'] = date('Y-m-d H:i:s');
		}
		if (!empty($data['incident_active']) && ($data['incident_message'] ?? $cur['message']) === '') {
			$data['incident_message'] = 'We are investigating a technical issue.';
		}
		$data['incident_source'] = $source === 'central' ? 'central' : 'local';
		$CI->db->where('id', 1)->update('db_sitesettings', $data);
	} catch (Throwable $e) {
		log_message('error', 'mp_set_incident failed: ' . $e->getMessage());
		return false;
	}
	// Bust the per-request cache so a same-request render sees the new state.
	unset($GLOBALS['__mp_incident_cache']);
	return true;
}