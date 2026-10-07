<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fleet — Central install registry + deployment package server.
 *
 * Runs on the VENDOR'S central MartPoint install (e.g. central.yourdomain.com).
 * The "central login" is the normal /login of that install — super admins get
 * this panel. Client installs never see it in use, but the code ships
 * everywhere harmlessly.
 *
 * Endpoints:
 *   index / rotate_key / launcher / remove — admin panel (login required)
 *   heartbeat — public telemetry endpoint client installs POST to
 *   package   — streams martpoint-full.zip to the deploy.php launcher
 */
class Fleet extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Central tooling ships to clients but must stay inert there —
        // without the vendor-only config/central.php flag every fleet
        // endpoint (panel, heartbeat, package) simply does not exist.
        if (!mp_is_central()) {
            show_404();
        }
        $this->ensureFleetSchema();
        $this->ensureSitesettingsSchema();
    }

    /**
     * Provisioning settings live in db_sitesettings — self-heal so no
     * migration is needed on the central install.
     */
    private function ensureSitesettingsSchema(): void {
        try {
            if (!$this->db->table_exists('db_sitesettings')) {
                return;
            }
            foreach (['cpanel_host', 'cpanel_user', 'cpanel_token', 'base_domain'] as $col) {
                if (!$this->db->field_exists($col, 'db_sitesettings')) {
                    $this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `{$col}` VARCHAR(255) NULL");
                }
            }
            // Slim-menu toggle for Central — lives in the DB so a release
            // package carrying an older sidebar can't silently un-hide the
            // retail menus. Default ON on central.
            if (!$this->db->field_exists('central_slim_menu', 'db_sitesettings')) {
                $this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `central_slim_menu` TINYINT(1) NOT NULL DEFAULT 1");
            }
        } catch (Exception $e) {
            log_message('error', 'Fleet sitesettings schema check failed: ' . $e->getMessage());
        }
    }

    /**
     * db_fleet_installs is central-only — there is no client-facing migration
     * for its later columns, so the registry grows itself as needed.
     */
    private function ensureFleetSchema(): void {
        try {
            if (!$this->db->table_exists('db_fleet_installs')) {
                return;
            }
            $cols = [
                'license_status' => 'VARCHAR(20) NULL',
                'plan_name'      => 'VARCHAR(100) NULL',
                'days_left'      => 'INT NULL',
                'usage_json'     => 'TEXT NULL',
                'admin_pass'     => 'VARCHAR(100) NULL',
                'cron_key'       => 'VARCHAR(100) NULL',
                'cron_scheduled' => 'TINYINT(1) NOT NULL DEFAULT 0',
                'cron_auto'      => 'TINYINT(1) NOT NULL DEFAULT 0',
                'store_name'     => 'VARCHAR(150) NULL',
                'store_city'     => 'VARCHAR(150) NULL',
                'store_state'    => 'VARCHAR(150) NULL',
                'store_country'  => 'VARCHAR(150) NULL',
                'license_otp_hash'    => 'VARCHAR(64) NULL',
                'license_otp_expires' => 'DATETIME NULL',
            ];
            foreach ($cols as $name => $def) {
                if (!$this->db->field_exists($name, 'db_fleet_installs')) {
                    $this->db->query("ALTER TABLE `db_fleet_installs` ADD COLUMN `{$name}` {$def}");
                }
            }
            // Central is the registry, not a member — drop any row that
            // heartbeats itself in (earlier builds did).
            $centralHost = trim((string) $this->config->item('central_domain'))
                ?: parse_url(base_url(), PHP_URL_HOST);
            if ($centralHost) {
                foreach ($this->db->select('id, install_url')->get('db_fleet_installs')->result() as $row) {
                    if (strcasecmp((string) parse_url($row->install_url, PHP_URL_HOST), $centralHost) === 0) {
                        $this->db->where('id', $row->id)->delete('db_fleet_installs');
                    }
                }
            }
            // Scrub the installer's placeholder store name reported by older
            // builds; the next heartbeat from fixed code fills in the real one.
            if ($this->db->field_exists('store_name', 'db_fleet_installs')) {
                $this->db->where('store_name', 'SAAS ADMIN')->update('db_fleet_installs', [
                    'store_name' => null, 'store_city' => null, 'store_state' => null, 'store_country' => null,
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'Fleet schema check failed: ' . $e->getMessage());
        }
    }

    private function requireAdmin() {
        $this->load_global();
        if (!special_access()) {
            show_error('Access Denied', 403, 'Super Admin Only');
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Admin panel                                                       */
    /* ------------------------------------------------------------------ */

    public function index() {
        $this->requireAdmin();
        $data = $this->data;
        $data['page_title'] = 'Fleet Manager';
        $data['installs'] = $this->db->table_exists('db_fleet_installs')
            ? $this->db->order_by('last_seen', 'DESC')->get('db_fleet_installs')->result()
            : [];
        $data['last_commands'] = [];
        if ($this->db->table_exists('db_fleet_commands')) {
            foreach ($data['installs'] as $i) {
                $data['last_commands'][$i->id] = $this->db->where('install_id', $i->id)
                    ->order_by('id', 'DESC')->limit(1)->get('db_fleet_commands')->row();
            }
        }
        $data['deploy_key'] = $this->getSetting('deploy_key');
        $data['fleet_key'] = $this->getSetting('fleet_key');
        $pkg = FCPATH . 'release_upload/releases/latest/martpoint-full.zip';
        $data['package_exists'] = file_exists($pkg);
        $data['package_size'] = $data['package_exists'] ? round(filesize($pkg) / 1048576, 1) : 0;

        // Latest released version — the manifest generator writes this file
        // on this machine, so the fleet table can flag outdated installs.
        $data['latest_version'] = null;
        $manifestFile = FCPATH . 'release_build/release-manifest.json';
        if (is_file($manifestFile)) {
            $m = json_decode((string) @file_get_contents($manifestFile), true);
            $data['latest_version'] = $m['version'] ?? null;
        }

        $data['stats'] = [
            'total'     => count($data['installs']),
            'active24h' => 0,
            'outdated'  => 0,
            'suspended' => 0,
            'expired'   => 0,
            // How many installs are mid-update or stuck. Counted here so the
            // fleet header answers "is anything broken right now?" without
            // scanning every row.
            'updating'  => 0,
            'stuck'     => 0,
        ];
        $dayAgo = date('Y-m-d H:i:s', time() - 86400);
        foreach ($data['installs'] as $i) {
            if (!empty($i->last_seen) && $i->last_seen >= $dayAgo) $data['stats']['active24h']++;
            if ($data['latest_version'] && !empty($i->version)
                && version_compare($i->version, $data['latest_version'], '<')) $data['stats']['outdated']++;
            if (($i->license_status ?? '') === 'SUSPENDED') $data['stats']['suspended']++;
            if (($i->license_status ?? '') === 'EXPIRED') $data['stats']['expired']++;

            // "Stuck" = the install told us it stopped, or it claims to be
            // working but has not checked in since. Both need a human; a
            // healthy idle install does not.
            $stage = strtolower(trim((string) ($i->update_stage ?? '')));
            if (in_array($stage, ['stalled', 'failed'], true)) {
                $data['stats']['stuck']++;
            } elseif (in_array($stage, ['ready', 'downloading', 'verifying', 'applying', 'migrating', 'finalizing', 'cleanup'], true)) {
                $data['stats']['updating']++;
                // Still "running" but silent for over an hour means the
                // request died — count it as stuck too.
                if (empty($i->last_seen) || $i->last_seen < date('Y-m-d H:i:s', time() - 3600)) {
                    $data['stats']['stuck']++;
                }
            }
        }

        // Provisioning config (cPanel API)
        // Subscription plans for the license modal — same table the local
        // Subscription page uses, so Central offers identical plan limits.
        $data['plans'] = $this->db->table_exists('db_subscription_plans')
            ? $this->db->where('is_active', 1)->order_by('display_order', 'asc')->get('db_subscription_plans')->result()
            : [];
        $data['cpanel_host'] = $this->getSetting('cpanel_host') ?: '127.0.0.1';
        $data['cpanel_user'] = $this->getSetting('cpanel_user');
        $data['cpanel_token_set'] = $this->getSetting('cpanel_token') !== '';
        $data['base_domain'] = $this->getSetting('base_domain')
            ?: preg_replace('/^[^.]+\./', '', (string) $this->config->item('central_domain'));
        $data['content'] = $this->load->view('fleet', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * AJAX: rotate deploy_key or fleet_key
     */
    public function rotate_key() {
        $this->requireAdmin();
        $which = $this->input->post('which');
        if (!in_array($which, ['deploy_key', 'fleet_key'], true)
            || !$this->db->field_exists($which, 'db_sitesettings')) {
            echo json_encode(['status' => 'error', 'message' => 'Unknown key.']);
            return;
        }
        $key = 'mp_' . bin2hex(random_bytes(20));
        $this->db->where('id', 1)->update('db_sitesettings', [$which => $key]);
        echo json_encode(['status' => 'ok', 'key' => $key]);
    }

    /**
     * Download deploy.php with this install's URL baked in as the central
     * package server. Give this file + a deploy key to the engineer.
     */
    public function launcher() {
        $this->requireAdmin();
        $src = FCPATH . 'deploy.php';
        if (!file_exists($src)) {
            show_error('deploy.php not found in the application root.', 404);
            return;
        }
        $code = file_get_contents($src);
        $code = preg_replace(
            "/define\('MP_CENTRAL_URL',\s*'[^']*'\);/",
            "define('MP_CENTRAL_URL', '" . rtrim(base_url(), '/') . "');",
            $code,
            1
        );
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="deploy.php"');
        header('Content-Length: ' . strlen($code));
        echo $code;
    }

    /**
     * AJAX: remove an install row from the registry
     */
    public function remove() {
        $this->requireAdmin();
        $id = (int) $this->input->post('id');
        if ($id > 0 && $this->db->table_exists('db_fleet_installs')) {
            $this->db->where('id', $id)->delete('db_fleet_installs');
        }
        echo json_encode(['status' => 'ok']);
    }

    /**
     * AJAX: save cPanel API settings used for one-click provisioning.
     * Token is write-only — blank keeps the stored value.
     */
    public function save_cpanel() {
        $this->requireAdmin();
        $updates = [
            'cpanel_host'  => substr(trim((string) $this->input->post('cpanel_host')), 0, 255),
            'cpanel_user'  => substr(trim((string) $this->input->post('cpanel_user')), 0, 255),
            'base_domain'  => strtolower(substr(trim((string) $this->input->post('base_domain')), 0, 255)),
        ];
        $token = trim((string) $this->input->post('cpanel_token'));
        if ($token !== '') {
            $updates['cpanel_token'] = substr($token, 0, 255);
        }
        foreach ($updates as $col => $val) {
            if (!$this->db->field_exists($col, 'db_sitesettings')) {
                unset($updates[$col]);
            }
        }
        if ($updates) {
            $this->db->where('id', 1)->update('db_sitesettings', $updates);
        }
        echo json_encode(['status' => 'ok', 'message' => 'cPanel settings saved.']);
    }

    /**
     * AJAX: provision a brand-new client install — creates the subdomain,
     * database + user via the cPanel API, drops a pre-keyed deploy.php in the
     * docroot, and registers the install in the fleet table.
     */
    public function provision() {
        $this->requireAdmin();
        @set_time_limit(120);
        header('Content-Type: application/json');

        $slug = strtolower(trim((string) $this->input->post('slug')));
        $baseDomain = $this->getSetting('base_domain')
            ?: preg_replace('/^[^.]+\./', '', (string) $this->config->item('central_domain'));
        $user  = $this->getSetting('cpanel_user');
        $token = $this->getSetting('cpanel_token');

        if ($user === '' || $token === '' || $baseDomain === '') {
            echo json_encode(['status' => 'error', 'message' => 'Save cPanel API settings first (user, token, base domain).']);
            return;
        }
        if (!preg_match('/^[a-z0-9][a-z0-9-]{0,30}[a-z0-9]$/', $slug)) {
            echo json_encode(['status' => 'error', 'message' => 'Subdomain: lowercase letters, numbers, hyphens (2–32 chars).']);
            return;
        }
        if (in_array($slug, ['central', 'www', 'mail', 'smtp', 'imap', 'pop', 'ftp', 'webmail', 'cpanel', 'whm', 'api', 'ns1', 'ns2'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'That subdomain is reserved.']);
            return;
        }

        $fqdn = $slug . '.' . $baseDomain;
        $docRootRel = 'public_html/' . $fqdn;
        $docRoot = $this->docrootFor($fqdn);

        try {
            // 1. Subdomain — tolerate "already exists" so a retry after a
            //    partial failure (e.g. DB step) still completes.
            try {
                $this->cpanelCall('SubDomain', 'addsubdomain', [
                    'domain' => $slug, 'rootdomain' => $baseDomain, 'dir' => $docRootRel,
                ]);
            } catch (Exception $e) {
                if (stripos($e->getMessage(), 'exist') === false) {
                    throw $e;
                }
            }

            // 2. Database + user + grant — cPanel requires every name to
            //    begin with "{cpanel_user}_", so derive the prefix from the
            //    saved cPanel user (e.g. elitewor_duvet_db).
            $prefix = $user . '_';
            $clean  = preg_replace('/[^a-z0-9]/', '', $slug);
            $dbName = substr($prefix . $clean, 0, 56) . '_db';
            // DB usernames are capped at 16 chars on many cPanel setups and
            // must be unique — prefix + short random suffix stays safe.
            $dbUser = substr($prefix . $clean, 0, 10) . substr(bin2hex(random_bytes(3)), 0, 5) . 'u';
            $dbUser = substr($dbUser, 0, 16);
            $dbPass = bin2hex(random_bytes(10));
            try {
                $this->cpanelCall('Mysql', 'create_database', ['name' => $dbName]);
            } catch (Exception $e) {
                // A retry after a partial run may find the DB already there —
                // that's fine, we still create a fresh user + grants below.
                if (stripos($e->getMessage(), 'exist') === false) {
                    throw $e;
                }
            }
            $this->cpanelCall('Mysql', 'create_user', ['name' => $dbUser, 'password' => $dbPass]);
            $this->cpanelCall('Mysql', 'set_privileges_on_database', [
                'user' => $dbUser, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES',
            ]);

            // 3. Launcher with central URL + deploy key baked in — the partner
            //    just clicks Install. Self-deletes after running.
            $deployKey = $this->getSetting('deploy_key');
            if ($deployKey === '' || !$this->writeLauncher($docRoot, $deployKey)) {
                echo json_encode(['status' => 'error', 'message' => 'Subdomain + DB created but could not write deploy.php into ' . $docRoot]);
                return;
            }
            // Bare domain should launch the installer, not show a directory
            // listing (which would also expose mp_provision.php's filename).
            // The full package overwrites this stub during deployment.
            @file_put_contents(
                $docRoot . '/index.php',
                "<?php header('Location: deploy.php'); exit;"
            );

            // 4. Provision file — DB creds + a unique admin password for this
            //    install (default seeded admin creds are identical everywhere,
            //    so every provisioned install gets its own). The installer
            //    consumes and deletes this file. PHP file: blank output.
            $adminPass = 'MP-' . substr(bin2hex(random_bytes(6)), 0, 10);
            $cronKey = 'ck_' . bin2hex(random_bytes(10));
            @file_put_contents($docRoot . '/mp_provision.php', "<?php return " . var_export([
                'db_host'    => 'localhost',
                'db_name'    => $dbName,
                'db_user'    => $dbUser,
                'db_pass'    => $dbPass,
                'admin_pass' => $adminPass,
                'cron_key'   => $cronKey,
                // Fleet callback details. The install's Updater reads
                // db_sitesettings.fleet_url / fleet_key to POST heartbeats
                // back here. Without them the install never phones home and
                // this install sits at PROVISIONING forever — so Central must
                // hand them over at provision time.
                'fleet_url'  => rtrim((string) base_url(), '/') . '/',
                'fleet_key'  => $this->getSetting('fleet_key'),
            ], true) . ";");

            // Cron jobs — same cPanel account, so they can be scheduled now.
            // Non-fatal if this fails; the row can be scheduled later.
            $cronScheduled = false;
            try {
                $this->scheduleCronLines('https://' . $fqdn . '/', $cronKey);
                $cronScheduled = true;
            } catch (Exception $e) {
                log_message('error', 'Fleet provision: cron scheduling failed for ' . $fqdn . ': ' . $e->getMessage());
            }

            // 5. Register in fleet table (first heartbeat fills real data)
            if ($this->db->table_exists('db_fleet_installs')) {
                $installUrl = 'https://' . $fqdn . '/';
                $existing = $this->db->where('install_url', $installUrl)->get('db_fleet_installs')->row();
                if (!$existing) {
                    $row = [
                        'install_url' => $installUrl,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ];
                    if ($this->db->field_exists('license_status', 'db_fleet_installs')) {
                        $row['license_status'] = 'PROVISIONING';
                    }
                    if ($this->db->field_exists('plan_name', 'db_fleet_installs')) {
                        $row['plan_name'] = substr(trim((string) $this->input->post('partner')), 0, 100);
                    }
                    if ($this->db->field_exists('admin_pass', 'db_fleet_installs')) {
                        $row['admin_pass'] = $adminPass;
                    }
                    if ($this->db->field_exists('cron_key', 'db_fleet_installs')) {
                        $row['cron_key'] = $cronKey;
                    }
                    if ($this->db->field_exists('cron_scheduled', 'db_fleet_installs')) {
                        $row['cron_scheduled'] = $cronScheduled ? 1 : 0;
                    }
                    $this->db->insert('db_fleet_installs', $row);
                }
            }

            echo json_encode([
                'status' => 'ok',
                'url' => 'https://' . $fqdn,
                'deploy_url' => 'https://' . $fqdn . '/deploy.php',
                'admin_pass' => $adminPass,
                'message' => 'Provisioned' . ($cronScheduled ? ' with cron jobs scheduled' : ' (cron scheduling failed — use the Cron button later)') . '. Send the deploy link to the partner — they only click Install.',
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Call the cPanel UAPI on this server. Throws on transport or API errors.
     */
    private function cpanelCall(string $module, string $fn, array $params): array {
        $host = $this->getSetting('cpanel_host') ?: '127.0.0.1';
        $user = $this->getSetting('cpanel_user');
        $token = $this->getSetting('cpanel_token');
        $url = 'https://' . $host . ':2083/execute/' . $module . '/' . $fn . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => ['Authorization: cpanel ' . $user . ':' . $token],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resp === false) {
            throw new Exception('cPanel API unreachable (' . $err . '). Check cpanel_host — try 127.0.0.1 or the server hostname.');
        }
        $data = json_decode((string) $resp, true);
        if (!is_array($data)) {
            throw new Exception('cPanel API returned HTTP ' . $code . ' — check the API token and user.');
        }
        if ((int) ($data['status'] ?? 0) !== 1) {
            $errors = $data['errors'] ?? ['unknown error'];
            throw new Exception('cPanel ' . $module . '::' . $fn . ' failed: ' . implode('; ', (array) $errors));
        }
        return $data;
    }

    /**
     * Absolute docroot for a new subdomain. Subdomain dirs are created under
     * public_html — locate it from this install's own docroot.
     */
    private function docrootFor(string $fqdn): string {
        $docRoot = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/');
        // cPanel layouts differ — this install's docroot may BE
        // ~/public_html, sit beside it (~/sub.domain.com), or inside it.
        // Find the real public_html from the account home, since
        // addsubdomain's dir is relative to the home dir.
        $candidates = [
            dirname($docRoot) . '/public_html',           // ~/public_html when docroot is a sibling subdomain
            $docRoot . '/public_html',                     // nested layout
            getenv('HOME') ? getenv('HOME') . '/public_html' : '',
        ];
        $p = dirname($docRoot);
        while ($p !== '' && $p !== '/') {
            if (is_dir($p . '/public_html')) {
                array_unshift($candidates, $p . '/public_html');
                break;
            }
            $np = dirname($p);
            if ($np === $p) break;
            $p = $np;
        }
        foreach ($candidates as $pub) {
            if ($pub !== '' && is_dir($pub)) {
                return $pub . '/' . $fqdn;
            }
        }
        return dirname($docRoot) . '/public_html/' . $fqdn;
    }

    /**
     * Write a deploy.php into a docroot with this central URL + deploy key
     * baked in (prefilled key — partner never sees it).
     */
    private function writeLauncher(string $docRoot, string $deployKey): bool {
        $src = FCPATH . 'deploy.php';
        if (!is_file($src) || !is_dir($docRoot)) {
            return false;
        }
        $code = file_get_contents($src);
        $code = preg_replace(
            "/define\('MP_CENTRAL_URL',\s*'[^']*'\);/",
            "define('MP_CENTRAL_URL', '" . rtrim(base_url(), '/') . "');",
            $code, 1
        );
        $code = preg_replace(
            "/define\('MP_PREFILL_KEY',\s*'[^']*'\);/",
            "define('MP_PREFILL_KEY', '" . $deployKey . "');",
            $code, 1
        );
        return file_put_contents($docRoot . '/deploy.php', $code) !== false;
    }

    /* ------------------------------------------------------------------ */
    /*  Public endpoints (no session)                                     */
    /* ------------------------------------------------------------------ */

    /**
     * POST — client installs report in. If fleet_key is configured here the
     * caller must present it; otherwise heartbeats are accepted openly
     * (telemetry only — worst case is a junk row).
     */
    public function heartbeat() {
        header('Content-Type: application/json');

        if (!$this->db->table_exists('db_fleet_installs')) {
            echo json_encode(['status' => 'error', 'message' => 'fleet registry not installed']);
            return;
        }

        $requiredKey = $this->getSetting('fleet_key');
        if ($requiredKey !== '' && !hash_equals($requiredKey, (string) $this->input->post('key'))) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'invalid fleet key']);
            return;
        }

        $installUrl = trim((string) $this->input->post('install_url'));
        if (!filter_var($installUrl, FILTER_VALIDATE_URL)
            || !in_array(parse_url($installUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'invalid install_url']);
            return;
        }
        $installUrl = rtrim($installUrl, '/') . '/';

        // Central is the registry, not a member — an older build may still
        // heartbeat itself; ignore it (and self-heal deletes any such row).
        $ownHost = parse_url(base_url(), PHP_URL_HOST);
        $centralHost = trim((string) $this->config->item('central_domain')) ?: $ownHost;
        if (strcasecmp((string) parse_url($installUrl, PHP_URL_HOST), $centralHost) === 0) {
            echo json_encode(['status' => 'ok', 'skipped' => 'central']);
            return;
        }

        // Which version to record for this install.
        //
        // `code_version` is read from the install's custom_helper.php, so it
        // is always what is actually deployed. `version` is the DB value,
        // stamped only at step 7 of an update — an interrupted run leaves it
        // stale indefinitely, and with hundreds of installs that silently
        // misreports the whole fleet. Prefer the code version; fall back to
        // the DB value for installs too old to send it.
        $codeVersion = trim((string) $this->input->post('code_version'));
        $dbVersion   = trim((string) $this->input->post('version'));
        $reported = preg_match('/^\d+(\.\d+)+$/', $codeVersion) ? $codeVersion : $dbVersion;

        $data = [
            'license_code' => substr((string) $this->input->post('license_code'), 0, 255),
            'version'      => substr($reported, 0, 20),
            'php_version'  => substr((string) $this->input->post('php_version'), 0, 20),
            'last_seen'    => date('Y-m-d H:i:s'),
        ];
        $installKey = substr((string) $this->input->post('install_key'), 0, 100);
        if ($installKey !== '') {
            $data['install_key'] = $installKey;
        }
        foreach ([
            'license_status' => 20,
            'plan_name'      => 100,
            'cron_key'       => 100,
            'store_name'     => 150,
            'store_city'     => 150,
            'store_state'    => 150,
            'store_country'  => 150,
            'usage_json'     => 4000,
            'update_detail'  => 255,
        ] as $field => $max) {
            if ($this->db->field_exists($field, 'db_fleet_installs')) {
                $val = substr((string) $this->input->post($field), 0, $max);
                if ($val !== '') {
                    $data[$field] = $val;
                }
            }
        }
        // Stage is the one field that must be able to go BACK to idle after an
        // update finishes — the loop above skips empty values, which would
        // freeze a completed install on its last stage for ever. Always write
        // both when the columns exist; NULL means "never reported", which the
        // UI shows as no-data rather than a false idle.
        if ($this->db->field_exists('update_stage', 'db_fleet_installs')) {
            $stage = substr(trim((string) $this->input->post('update_stage')), 0, 32);
            $data['update_stage'] = $stage !== '' ? $stage : null;
        }
        if ($this->db->field_exists('update_step', 'db_fleet_installs')) {
            $data['update_step'] = max(0, min(8, (int) $this->input->post('update_step')));
        }
        // Installer placeholder store (id 1) — older builds report it when no
        // session is present. Never let it overwrite the client's real name;
        // drop its location too, it's the seed's dummy address.
        if (strcasecmp(trim((string) ($data['store_name'] ?? '')), 'SAAS ADMIN') === 0) {
            unset($data['store_name'], $data['store_city'], $data['store_state'], $data['store_country']);
        }
        if ($this->db->field_exists('days_left', 'db_fleet_installs')) {
            $dl = $this->input->post('days_left');
            if ($dl !== null && $dl !== '') {
                $data['days_left'] = (int) $dl;
            }
        }

        $existing = $this->db->where('install_url', $installUrl)->get('db_fleet_installs')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update('db_fleet_installs', $data);
            $installId = (int) $existing->id;
        } else {
            $data['install_url'] = $installUrl;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('db_fleet_installs', $data);
            $installId = (int) $this->db->insert_id();
        }



        // Auto-schedule cPanel cron lines whenever the install reports a
        // unique key and they aren't up yet — no button click required.
        // cron_auto (armed by the Cron button) retries failures; unarmed
        // first-attempt failures mark cron_scheduled=-1 so a foreign-cPanel
        // install doesn't hammer the API on every heartbeat (the Cron
        // button still schedules manually).
        $row = $this->db->where('id', $installId)->get('db_fleet_installs')->row();
        $key = (string) ($row->cron_key ?? '');
        $scheduled = (int) ($row->cron_scheduled ?? 0);
        $armed = $row && !empty($row->cron_auto);
        if ($row && $key !== '' && $key !== 'martpoint_cron_2024' && $scheduled === 0) {
            try {
                $this->scheduleCronLines($row->install_url, $key);
                $this->db->where('id', $installId)->update('db_fleet_installs', [
                    'cron_scheduled' => 1,
                    'cron_auto' => 0,
                ]);
            } catch (Exception $e) {
                if (!$armed) {
                    $this->db->where('id', $installId)->update('db_fleet_installs', ['cron_scheduled' => -1]);
                }
                log_message('error', 'Fleet cron auto-schedule failed for ' . $installUrl . ': ' . $e->getMessage());
            }
        }

        echo json_encode(['status' => 'ok']);
    }

    /**
     * POST — an install asks for its pending commands. Authenticated by the
     * per-install install_key registered via heartbeat.
     */
    public function commands() {
        header('Content-Type: application/json');
        $install = $this->authInstall();
        if (!$install) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'unknown install or bad key']);
            return;
        }

        $pending = $this->db->where('install_id', $install->id)
            ->where('status', 'pending')
            ->order_by('id', 'ASC')
            ->get('db_fleet_commands')->result();

        foreach ($pending as $cmd) {
            $this->db->where('id', $cmd->id)->update('db_fleet_commands', [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo json_encode(['status' => 'ok', 'commands' => $pending]);
    }

    /**
     * POST — an install reports the outcome of an executed command.
     */
    public function command_result() {
        header('Content-Type: application/json');
        $install = $this->authInstall();
        if (!$install) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'unknown install or bad key']);
            return;
        }

        $id = (int) $this->input->post('command_id');
        $status = in_array($this->input->post('status'), ['done', 'failed'], true)
            ? $this->input->post('status') : 'done';
        $result = substr((string) $this->input->post('result'), 0, 4000);
        $this->db->where('id', $id)->where('install_id', $install->id)
            ->update('db_fleet_commands', [
                'status' => $status,
                'result' => $result,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

        // License OTPs arrive in the result string ("OTP ABC123 — …").
        // If the install's own email couldn't deliver it, Central forwards it
        // from its configured mailer — the vendor is never stranded.
        $cmd = $this->db->select('command, payload')->where('id', $id)->get('db_fleet_commands')->row();
        if ($cmd && $cmd->command === 'request_license_otp' && preg_match('/OTP\s+([A-Z0-9]{6})/', $result, $m)) {
            $this->forwardLicenseOtp($m[1], $install);
        }

        // Reflect confirmed state changes on the fleet row right away — the
        // UI shouldn't wait for the next heartbeat to show "ACTIVE".
        if ($cmd && $status === 'done') {
            $upd = [];
            if ($cmd->command === 'set_license') {
                $lic = json_decode((string) $cmd->payload, true) ?: [];
                $upd['license_status'] = 'ACTIVE';
                if (!empty($lic['plan_name'])) { $upd['plan_name'] = substr((string) $lic['plan_name'], 0, 100); }
                if (!empty($lic['subscription_end_date'])) {
                    $upd['days_left'] = (string) max(0, (int) floor((strtotime($lic['subscription_end_date']) - time()) / 86400));
                }
            } elseif ($cmd->command === 'suspend') {
                $upd['license_status'] = 'SUSPENDED';
            } elseif ($cmd->command === 'resume') {
                $upd['license_status'] = 'ACTIVE';
            } elseif ($cmd->command === 'set_cron_key' && preg_match('/^[A-Za-z0-9_\-]{4,100}$/', (string) $cmd->payload)) {
                $upd['cron_key'] = (string) $cmd->payload;
            }
            $upd = array_filter($upd, function ($k) { return $this->db->field_exists($k, 'db_fleet_installs'); }, ARRAY_FILTER_USE_KEY);
            if ($upd) { $this->db->where('id', $install->id)->update('db_fleet_installs', $upd); }
        }

        echo json_encode(['status' => 'ok']);
    }

    /**
     * Email an install-generated license OTP to the vendor using Central's
     * own mail settings — the fallback channel when the install's email
     * isn't configured.
     */
    private function forwardLicenseOtp(string $otp, $install): void {
        try {
            $host = parse_url($install->install_url, PHP_URL_HOST) ?: $install->install_url;
            $name = (string) ($install->store_name ?? '') ?: $host;
            $subject = "MartPoint License OTP: Activate - {$name}";
            $html = "<h3>MartPoint Retail License OTP</h3>
<p><strong>Install:</strong> " . htmlspecialchars($name) . " ({$host})</p>
<p><strong>Action:</strong> Activate</p>
<p><strong>OTP:</strong> <span style='font-size:24px; font-weight:bold; color:#0057FF;'>{$otp}</span></p>
<p><em>Expires 10 minutes after it was generated on the install — enter it in Fleet → License.</em></p>
<p style='color:#94A3B8; font-size:12px;'>Forwarded by MartPoint Central — the install's own email was unavailable.</p>";
            $text = "MartPoint License OTP\nInstall: {$name} ({$host})\nOTP: {$otp}\nExpires 10 minutes after generation — enter it in Fleet → License.";
            $this->load->model('email_service');
            $this->email_service->sendRaw('rapheal@avariodigitals.com', $subject, $html, $text, [
                'template_key' => 'license_otp',
                'from_name' => 'MartPoint Central',
                'send_copy_to_owner' => false,
            ]);
        } catch (Exception $e) {
            log_message('error', 'Fleet OTP forward failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX (admin) — generate a license OTP on CENTRAL and email it to the
     * vendor address. No install contact needed: the set_license payload later
     * carries an HMAC proof the install verifies with its fleet_key.
     */
    public function request_otp() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $installId = (int) $this->input->post('install_id');
        $install = $this->db->where('id', $installId)->get('db_fleet_installs')->row();
        if (!$install) {
            echo json_encode(['status' => 'error', 'message' => 'Install not found.']);
            return;
        }
        // Central always generates the OTP — no legacy fallback.
        // 60-second resend limit, same as the local flow.
        if (!empty($install->license_otp_expires)) {
            $created = strtotime($install->license_otp_expires) - 600;
            if (time() - $created < 60) {
                echo json_encode(['status' => 'error', 'message' => 'An OTP was already sent less than a minute ago — check the authorized email.']);
                return;
            }
        }
        $otp = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        $this->db->where('id', $installId)->update('db_fleet_installs', [
            'license_otp_hash'    => hash('sha256', $otp),
            'license_otp_expires' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        ]);

        $host = parse_url($install->install_url, PHP_URL_HOST) ?: $install->install_url;
        $name = (string) ($install->store_name ?? '') ?: $host;
        $subject = "MartPoint License OTP: Activate - {$name}";
        $html = "<h3>MartPoint Retail License OTP</h3>
<p><strong>Install:</strong> " . htmlspecialchars($name) . " ({$host})</p>
<p><strong>Action:</strong> Activate license</p>
<p><strong>OTP:</strong> <span style='font-size:24px; font-weight:bold; color:#0057FF;'>{$otp}</span></p>
<p><em>This OTP expires in 10 minutes and can only be used once.</em></p>
<p style='color:#94A3B8; font-size:12px;'>Requested from MartPoint Central — enter it in Fleet → License.</p>";
        $text = "MartPoint License OTP\nInstall: {$name} ({$host})\nOTP: {$otp}\nExpires in 10 minutes, single use.\nEnter it in Fleet → License.";
        try {
            $this->load->model('email_service');
            $result = $this->email_service->sendRaw('rapheal@avariodigitals.com', $subject, $html, $text, [
                'template_key' => 'license_otp',
                'from_name' => 'MartPoint Central',
                'send_copy_to_owner' => false,
            ]);
            if (!empty($result['success'])) {
                echo json_encode(['status' => 'ok', 'message' => 'OTP emailed to the authorized address.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'OTP generated but Central email failed: ' . ($result['message'] ?? 'check email settings.')]);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'OTP email error: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX (admin) — pull the real cron keys out of cPanel itself. The
     * scheduled lines already carry each install's key (…/cron/auto_update?
     * key=X) — read them back and store them on the matching fleet rows, so
     * wake pings work on installs whose heartbeat build predates cron_key
     * reporting. Zero manual lookups.
     */
    public function sync_cron_keys() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $lines = $this->listCronLines();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            return;
        }
        // Group every MartPoint cron line by host: host => [key => [linekeys]]
        $found = [];
        foreach ($lines as $l) {
            if (preg_match('#https?://([^/"\s]+)/cron/[a-z_]+\?key=([A-Za-z0-9_\-]+)#', $l['command'], $hit)) {
                $found[strtolower($hit[1])][$hit[2]][] = $l['linekey'];
            }
        }
        $updated = 0; $repaired = 0; $notes = [];
        foreach ($this->db->get('db_fleet_installs')->result() as $i) {
            $host = strtolower((string) parse_url($i->install_url, PHP_URL_HOST));
            $cpanelKeys = array_keys($found[$host] ?? []);
            // Candidates: what's in cPanel + what Central stored/pushed. The
            // install is the only authority on which one it accepts.
            $cands = array_values(array_unique(array_merge($cpanelKeys, array_diff(
                $this->candidateCronKeys($i), ['martpoint_cron_2024']
            ))));
            if (empty($cands)) { continue; }
            $key = $this->verifyCronKey($i->install_url, $cands);
            if ($key === null) {
                $notes[] = $host . ': install rejected every known key';
                continue;
            }
            if (($i->cron_key ?? '') !== $key) {
                $this->db->where('id', $i->id)->update('db_fleet_installs', ['cron_key' => $key]);
                $updated++;
            }
            // Repair the cPanel lines if they are missing, duplicated, or
            // carry a stale key — exactly three lines, all with the live key.
            $lineCount = count($found[$host][$key] ?? []);
            $stale = count($cpanelKeys) > 1 || !in_array($key, $cpanelKeys, true) || $lineCount !== 3;
            if ($stale) {
                try {
                    $this->scheduleCronLines($i->install_url, $key);
                    $this->db->where('id', $i->id)->update('db_fleet_installs', ['cron_scheduled' => 1]);
                    $repaired++;
                } catch (Exception $e) {
                    $notes[] = $host . ': ' . $e->getMessage();
                }
            }
        }
        $msg = "Keys verified — {$updated} updated, {$repaired} install(s) had cron lines rewritten (duplicates/stale keys removed).";
        if ($notes) { $msg .= ' Issues: ' . implode('; ', $notes); }
        echo json_encode(['status' => $notes ? 'warning' : 'ok', 'message' => $msg]);
    }

    /**
     * All cron lines on this cPanel account as [['linekey'=>…, 'command'=>…]].
     * UAPI first, API2 fallback (same as scheduling).
     */
    private function listCronLines(): array {
        $rows = [];
        try {
            $data = $this->cpanelCall('Cron', 'fetch_lineitems', []);
            $rows = $data['data'] ?? [];
        } catch (Exception $e) {
            if (stripos($e->getMessage(), 'API::Cron') === false && stripos($e->getMessage(), 'load module') === false) {
                throw $e;
            }
            $data = $this->cpanelApi2Call('Cron', 'listcron', []);
            $rows = $data['cpanelresult']['data'] ?? [];
        }
        $out = [];
        foreach ((array) $rows as $r) {
            if (!is_array($r) || empty($r['linekey'])) { continue; }
            $out[] = ['linekey' => (string) $r['linekey'], 'command' => (string) ($r['command'] ?? '')];
        }
        return $out;
    }

    /**
     * Remove every MartPoint cron line pointing at this host — called before
     * (re)scheduling so an install never accumulates duplicates.
     */
    private function removeCronLinesFor(string $host): int {
        $removed = 0;
        $lastErr = null;
        foreach ($this->listCronLines() as $l) {
            if (stripos($l['command'], '://' . $host . '/cron/') === false) { continue; }
            // UAPI takes linekey; API2 has taken both 'line' and 'linekey'
            // across versions — try each until one succeeds.
            $done = false;
            foreach ([
                ['uapi', ['linekey' => $l['linekey']]],
                ['api2', ['linekey' => $l['linekey']]],
                ['api2', ['line' => $l['linekey']]],
            ] as $try) {
                try {
                    $try[0] === 'uapi'
                        ? $this->cpanelCall('Cron', 'remove_line', $try[1])
                        : $this->cpanelApi2Call('Cron', 'remove_line', $try[1]);
                    $done = true;
                    break;
                } catch (Exception $e) {
                    $lastErr = $e->getMessage();
                }
            }
            if ($done) { $removed++; }
        }
        if ($lastErr !== null && $removed === 0) {
            log_message('error', 'Fleet removeCronLinesFor ' . $host . ': ' . $lastErr);
        }
        return $removed;
    }

    /**
     * Test candidate cron keys against a live install — returns the first
     * key the cron endpoint accepts (anything but a 403 key rejection).
     */
    private function verifyCronKey($base, array $keys)
    {
        $endpoint = '/cron/fleet_ping'; // light: heartbeat + poll only
        foreach ($keys as $k) {
            for ($try = 0; $try < 2; $try++) {
                $ch = curl_init(rtrim($base, '/') . $endpoint . '?key=' . urlencode($k));
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 2,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MartPoint-Fleet/1.0)',
                ]);
                curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $timedOut = curl_errno($ch) === CURLE_OPERATION_TIMEDOUT;
                curl_close($ch);
                if ($code === 404 && $endpoint === '/cron/fleet_ping') {
                    $endpoint = '/cron/auto_update'; // pre-4.0.9.46 build
                    continue;
                }
                // 403 = rejected. Anything else that reached the app (200,
                // 500, or a timeout after the request landed) means the key
                // passed the check — the key gate is the very first thing.
                if ($code === 403) { break; }
                if ($code > 0 || $timedOut) { return $k; }
                break;
            }
        }
        return null;
    }

    /**
     * AJAX (admin) — save a cron key for an install manually. For installs
     * whose heartbeat build predates cron_key reporting, this is how Central
     * learns the key — after this, wake pings work immediately.
     */
    public function save_cron_key() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $installId = (int) $this->input->post('install_id');
        $key = trim((string) $this->input->post('cron_key'));
        if ($key !== '' && !preg_match('/^[A-Za-z0-9_\-]{4,100}$/', $key)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid key format.']);
            return;
        }
        if (!$this->db->field_exists('cron_key', 'db_fleet_installs')) {
            echo json_encode(['status' => 'error', 'message' => 'cron_key column missing.']);
            return;
        }
        $this->db->where('id', $installId)->update('db_fleet_installs', ['cron_key' => $key]);
        echo json_encode(['status' => 'ok', 'message' => 'Cron key saved — wake pings use it now.']);
    }

    /**
     * AJAX (admin) — flip the central slim-menu flag. The sidebar reads it
     * from db_sitesettings, so the choice survives release updates.
     */
    public function toggle_menu() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $on = (int) $this->input->post('enabled') === 1 ? 1 : 0;
        if ($this->db->field_exists('central_slim_menu', 'db_sitesettings')) {
            $this->db->where('id', 1)->update('db_sitesettings', ['central_slim_menu' => $on]);
        }
        echo json_encode(['status' => 'ok', 'enabled' => $on]);
    }

    /**
     * AJAX (admin) — latest status/result of a command for an install.
     * The license modal polls this after "Request OTP" to autofill the code.
     */
    /**
     * AJAX — has this install answered the most recent report_status?
     *
     * The Fleet Status column calls this after queuing a `report_status`, so
     * the page can reload the moment the install checks in. Central cannot
     * read an install's migration count directly — it can only ask and wait
     * for the next poll — so the UI polls this cheaply rather than pretending
     * the answer is instant.
     */
    public function status() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $installId = (int) $this->input->post('install_id');
        if ($installId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'install_id required']);
            return;
        }
        if (!$this->db->table_exists('db_fleet_commands')) {
            echo json_encode(['status' => 'ok', 'answered' => false]);
            return;
        }
        $row = $this->db->where('install_id', $installId)
            ->where('command', 'report_status')
            ->order_by('id', 'desc')->limit(1)
            ->get('db_fleet_commands')->row();
        if (!$row) {
            echo json_encode(['status' => 'ok', 'answered' => false]);
            return;
        }
        $st = strtolower((string) $row->status);
        echo json_encode([
            'status' => 'ok',
            // Only 'done'/'failed' mean the install actually replied.
            'answered' => in_array($st, ['done', 'failed'], true),
            'command_status' => $st,
            'result' => (string) ($row->result ?? ''),
        ]);
    }

    public function command_status() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $installId = (int) $this->input->get('install_id');
        $command = (string) $this->input->get('command');
        $row = $this->db->where('install_id', $installId)->where('command', $command)
            ->order_by('id', 'desc')->limit(1)->get('db_fleet_commands')->row();
        echo json_encode([
            'status' => 'ok',
            'command_status' => $row->status ?? 'none',
            'result' => (string) ($row->result ?? ''),
        ]);
    }

    /**
     * AJAX — queue a command for an install
     * ("update_now", "report_status", "set_license").
     * set_license builds a domain-locked license key + subscription record
     * centrally; the install applies it on its next check-in.
     */
    public function queue() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $this->doQueue();
        } catch (Throwable $e) {
            // catch (Exception) misses TypeError/Error — those became the bare
            // 500s the UI was reporting. Surface the real message instead.
            log_message('error', 'Fleet queue failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    private function doQueue() {
        $installId = (int) $this->input->post('install_id');
        $command = (string) $this->input->post('command');
        if (!in_array($command, ['update_now', 'report_status', 'set_license', 'request_license_otp', 'suspend', 'resume', 'set_email', 'set_cron_key', 'set_settings', 'run_backup', 'push_file'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Unknown command.']);
            return;
        }
        if (!$this->db->table_exists('db_fleet_commands')) {
            echo json_encode(['status' => 'error', 'message' => 'Commands table missing — run the latest migration.']);
            return;
        }

        $payload = null;
        if ($command === 'set_license') {
            $install = $this->db->where('id', $installId)->get('db_fleet_installs')->row();
            if (!$install) {
                echo json_encode(['status' => 'error', 'message' => 'Install not found.']);
                return;
            }
            $endDate = trim((string) $this->input->post('end_date'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                echo json_encode(['status' => 'error', 'message' => 'End date must be YYYY-MM-DD.']);
                return;
            }
            if ($endDate <= date('Y-m-d')) {
                echo json_encode(['status' => 'error', 'message' => 'End date must be in the future — a license expiring today/yesterday is EXPIRED the moment it activates.']);
                return;
            }
            // Same gate as the local Subscription page — no activation without
            // the OTP Central generated and emailed to the authorized address.
            $otp = strtoupper(trim((string) $this->input->post('otp_code')));
            if ($otp === '') {
                echo json_encode(['status' => 'error', 'message' => 'OTP required — use "Request OTP" so Central emails one to the authorized address.']);
                return;
            }
            // Two accepted OTP sources:
            //  a) Central-generated (fleet/request_otp) — verified here, then an
            //     HMAC proof rides in the payload so the install can verify it.
            //  b) Install-generated (request_license_otp command) — we can't
            //     verify it centrally; pass it through and the install's own
            //     db_license_otps validation gates it (legacy flow).
            $otpIssued  = !empty($install->license_otp_hash) && !empty($install->license_otp_expires);
            $otpFresh   = $otpIssued && strtotime($install->license_otp_expires) >= time();
            $centralOtp = $otpFresh && hash_equals((string) $install->license_otp_hash, hash('sha256', $otp));
            $legacyInstall = version_compare((string) ($install->version ?? '0'), '4.0.9.46', '<');
            if (!$centralOtp && !$legacyInstall) {
                // Modern install: only a Central-issued OTP can work. Fail
                // here with the real reason instead of letting the install
                // report "No OTP record found".
                $msg = !$otpIssued ? 'No OTP has been requested for this install — click "Request OTP" first.'
                     : (!$otpFresh ? 'That OTP has expired (10-minute limit) — request a new one.'
                     : 'Wrong OTP — check the email and try again.');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                return;
            }
            if ($centralOtp) {
                // Single use — burn it now, even if the push later fails.
                $this->db->where('id', $installId)->update('db_fleet_installs', [
                    'license_otp_hash' => null, 'license_otp_expires' => null,
                ]);
            }
            // Plan limits come from db_subscription_plans — the same source the
            // local Subscription page uses. override=1 (vendor approval) lets
            // the posted values exceed the plan defaults.
            $plan = null;
            $planCode = trim((string) $this->input->post('plan_code'));
            if ($planCode !== '' && $this->db->table_exists('db_subscription_plans')) {
                $plan = $this->db->where('plan_code', $planCode)->get('db_subscription_plans')->row();
            }
            $override = (int) $this->input->post('override') === 1;
            $limit = function (string $key, string $col, int $fallback) use ($plan, $override) {
                if ($override) {
                    $v = (int) $this->input->post($key);
                    if ($v > 0) { return $v; }
                }
                return $plan ? (int) ($plan->{$col} ?? $fallback) : $fallback;
            };
            $lic = [
                'plan_name'              => $plan ? $plan->plan_name
                    : (trim((string) $this->input->post('plan_name')) ?: 'Basic'),
                'subscription_start_date'=> date('Y-m-d'),
                'subscription_end_date'  => $endDate,
                'subscription_status'    => 'ACTIVE',
                'branch_limit'           => $limit('branch_limit', 'branch_limit', 1),
                'user_limit'             => $limit('user_limit', 'user_limit', 5),
                'product_limit'          => $limit('product_limit', 'product_limit', 500),
                'sku_limit'              => $limit('sku_limit', 'sku_limit', 10000),
                'online_product_limit'   => $limit('online_product_limit', 'online_product_limit', 500),
                'service_limit'          => $limit('service_limit', 'service_limit', 100),
                'media_storage_limit_mb' => $limit('media_storage_limit_mb', 'media_storage_limit_mb', 2048),
                'storefront_limit'       => $limit('storefront_limit', 'storefront_limit', 1),
                'custom_domain_limit'    => $limit('custom_domain_limit', 'custom_domain_limit', 1),
                // Same extra fields the local Generate form collects.
                'whatsapp_number'        => substr(trim((string) $this->input->post('whatsapp_number')), 0, 50),
                'renewal_amount'         => substr(trim((string) $this->input->post('renewal_amount')), 0, 50),
                'client_name'            => substr(trim((string) $this->input->post('client_name')), 0, 150),
                'otp_code'               => $otp,
            ];
            $domain = parse_url($install->install_url, PHP_URL_HOST) ?: '';
            $lic['license_code'] = $this->licenseKeyForDomain($domain, $lic);
            // Proof for the install: HMAC over otp+domain keyed by the shared
            // fleet_key — only attached when Central verified the OTP itself.
            // An unverified OTP gets no proof, so the install falls back to
            // checking its own db_license_otps rows (legacy flow).
            if ($centralOtp) {
                $lic['otp_proof'] = hash_hmac('sha256', $otp . '|' . $domain, (string) $this->getSetting('fleet_key'));
            }
            $payload = json_encode($lic);
        } elseif ($command === 'suspend') {
            $payload = substr(trim((string) $this->input->post('reason')), 0, 255);
        } elseif ($command === 'set_email') {
            $emailFields = [];
            foreach ([
                'email_provider', 'email_from_name', 'email_from_email', 'email_reply_to',
                'smtp_crypto', 'resend_api_key', 'resend_from_email', 'resend_from_name',
                'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_status',
            ] as $f) {
                $v = trim((string) $this->input->post($f));
                if ($v !== '') {
                    $emailFields[$f] = substr($v, 0, 255);
                }
            }
            if (empty($emailFields)) {
                echo json_encode(['status' => 'error', 'message' => 'Fill at least one email field.']);
                return;
            }
            $payload = json_encode($emailFields);
        } elseif ($command === 'set_cron_key') {
            // Unique per-install key — install writes it into config.php, then
            // reports it back via heartbeat so schedule_crons can use it.
            $payload = 'ck_' . bin2hex(random_bytes(10));
        } elseif ($command === 'set_settings') {
            // Scoped settings push — the client whitelists columns per scope,
            // so Central forwards {scope, fields} verbatim after validation.
            $scope = trim((string) $this->input->post('scope'));
            if (!in_array($scope, ['assist', 'paystack', 'monnify', 'nin', 'debt_reminder', 'audit'], true)) {
                echo json_encode(['status' => 'error', 'message' => 'Unknown settings scope.']);
                return;
            }
            $fields = [];
            $posted = $this->input->post('fields');
            if (is_array($posted)) {
                foreach ($posted as $k => $v) {
                    if (preg_match('/^[a-z0-9_]+$/', (string) $k)) {
                        $fields[(string) $k] = is_scalar($v) ? substr((string) $v, 0, 2000) : (string) json_encode($v);
                    }
                }
            }
            if ($fields === []) {
                echo json_encode(['status' => 'error', 'message' => 'Fill at least one field.']);
                return;
            }
            $payload = json_encode(['scope' => $scope, 'fields' => $fields]);
        } elseif ($command === 'push_file') {
            // One file per command — {path, content_b64}. Path must live under
            // application/ or theme/ and can never target config/lock files;
            // the install re-validates everything on its side too.
            $relPath = trim(str_replace('\\', '/', (string) $this->input->post('path')));
            $b64     = (string) $this->input->post('content_b64');
            if (!preg_match('#^(application|theme)/[\w\-./]+$#i', $relPath) || strpos($relPath, '..') !== false) {
                echo json_encode(['status' => 'error', 'message' => 'Path must be under application/ or theme/ (e.g. application/models/Roles_model.php).']);
                return;
            }
            if (in_array(strtolower($relPath), [
                'application/config/config.php', 'application/config/database.php',
                'application/config/constants.php', 'application/config/installed.lock',
                'index.php', '.htaccess',
            ], true)) {
                echo json_encode(['status' => 'error', 'message' => 'That file is protected — it cannot be pushed.']);
                return;
            }
            $raw = base64_decode($b64, true);
            if ($raw === false || $raw === '') {
                echo json_encode(['status' => 'error', 'message' => 'Could not read the file content.']);
                return;
            }
            if (strlen($raw) > 900 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'File too large — max ~900KB per push. For bigger changes use the release channel.']);
                return;
            }
            $payload = json_encode(['path' => $relPath, 'content_b64' => base64_encode($raw)]);
        }

        $this->db->insert('db_fleet_commands', [
            'install_id' => $installId,
            'command' => $command,
            'payload' => $payload,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Wake the install now if we know its cron key — it executes the
        // command within seconds instead of waiting for the next check-in.
        $woken = $this->pingInstall($installId, $why);
        echo json_encode([
            'status' => 'ok',
            'woke' => $woken,
            'message' => $woken
                ? 'Install woken — waiting for it to confirm…'
                : 'Queued — could not wake the install (' . ($why !== '' ? $why : 'no response')
                  . '). It runs on the install\'s next cron check-in (within 30 min). Tip: Sync & repair cron keys, or use the key button on the row.',
        ]);
    }

    /**
     * AJAX — bulk version of queue(): the Fleet table's bulk bar posts a chunk
     * of install_ids + one command and every selected install gets its own
     * pending row, exactly as if its row button had been clicked. 'cron' runs
     * the same scheduleCronForId() path as the per-row Cron button.
     */
    public function queue_bulk() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $this->doQueueBulk();
        } catch (Throwable $e) {
            log_message('error', 'Fleet queue_bulk failed: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }

    private function doQueueBulk() {
        set_time_limit(180);
        $command = (string) $this->input->post('command');
        $allowed = ['update_now', 'run_backup', 'report_status', 'suspend', 'resume', 'set_cron_key', 'cron', 'push_file'];
        if (!in_array($command, $allowed, true)) {
            echo json_encode(['status' => 'error', 'message' => 'That command cannot run in bulk.']);
            return;
        }
        $ids = $this->input->post('install_ids');
        if (!is_array($ids)) {
            $ids = preg_split('/[\s,]+/', (string) $ids, -1, PREG_SPLIT_NO_EMPTY);
        }
        $ids = array_slice(array_values(array_unique(array_filter(array_map('intval', (array) $ids)))), 0, 500);
        if (empty($ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No installs selected.']);
            return;
        }
        if ($command !== 'cron' && !$this->db->table_exists('db_fleet_commands')) {
            echo json_encode(['status' => 'error', 'message' => 'Commands table missing — run the latest migration.']);
            return;
        }

        $suspendReason = $command === 'suspend'
            ? substr(trim((string) $this->input->post('reason')), 0, 255) : null;

        // push_file carries the same {path, content_b64} payload as the
        // single-install path, validated identically and once for the whole
        // batch — the install side re-checks it anyway.
        $pushPayload = null;
        if ($command === 'push_file') {
            $relPath = trim(str_replace('\\', '/', (string) $this->input->post('path')));
            $b64     = (string) $this->input->post('content_b64');
            if (!preg_match('#^(application|theme)/[\w\-./]+$#i', $relPath) || strpos($relPath, '..') !== false) {
                echo json_encode(['status' => 'error', 'message' => 'Path must be under application/ or theme/ (e.g. application/libraries/Updater.php).']);
                return;
            }
            if (in_array(strtolower($relPath), [
                'application/config/config.php', 'application/config/database.php',
                'application/config/constants.php', 'application/config/installed.lock',
                'index.php', '.htaccess',
            ], true)) {
                echo json_encode(['status' => 'error', 'message' => 'That file is protected — it cannot be pushed.']);
                return;
            }
            $raw = base64_decode($b64, true);
            if ($raw === false || $raw === '') {
                echo json_encode(['status' => 'error', 'message' => 'Could not read the file content.']);
                return;
            }
            if (strlen($raw) > 900 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'File too large — max ~900KB per push. For bigger changes use the release channel.']);
                return;
            }
            $pushPayload = json_encode(['path' => $relPath, 'content_b64' => base64_encode($raw)]);
        }

        $queued = 0; $wokeIds = []; $errors = [];
        foreach ($ids as $id) {
            if ($command === 'cron') {
                $res = $this->scheduleCronForId($id);
                if ($res['status'] === 'ok') { $queued++; }
                else { $errors[] = '#' . $id . ': ' . $res['message']; }
                continue;
            }
            $install = $this->db->select('id')->where('id', $id)->get('db_fleet_installs')->row();
            if (!$install) {
                $errors[] = '#' . $id . ': not registered';
                continue;
            }
            // set_cron_key needs a unique payload per install — same shape as
            // the single-install path in doQueue().
            $payload = $command === 'set_cron_key' ? 'ck_' . bin2hex(random_bytes(10))
                : ($command === 'suspend' ? $suspendReason
                : ($command === 'push_file' ? $pushPayload : null));
            $this->db->insert('db_fleet_commands', [
                'install_id' => $id,
                'command' => $command,
                'payload' => $payload,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $queued++;
            $wokeIds[] = $id;
        }

        // Best-effort parallel wake — each install picks the command up within
        // seconds instead of waiting for its next cron check-in.
        $woke = $this->wakeInstalls($wokeIds);

        echo json_encode([
            'status' => 'ok',
            'queued' => $queued,
            'woke' => $woke,
            'errors' => array_slice($errors, 0, 10),
        ]);
    }

    /**
     * Wake many installs at once with curl_multi — one request per install,
     * all in flight together, so 50 installs cost ~5s total instead of 5s
     * each. Uses each install's strongest wake path (install_key wake token,
     * else the keyless auto_tick ping). Returns how many answered.
     */
    private function wakeInstalls(array $ids): int {
        if (empty($ids)) { return 0; }
        $mh = curl_multi_init();
        $handles = [];
        foreach (array_slice($ids, 0, 100) as $id) {
            try {
                $install = $this->db->where('id', $id)->get('db_fleet_installs')->row();
                if (!$install || empty($install->install_url)) { continue; }
                $base = rtrim($install->install_url, '/');
                $path = !empty($install->install_key)
                    ? '/cron/fleet_ping?key=' . urlencode(hash_hmac('sha256', 'mp_wake', (string) $install->install_key))
                    : '/system_updates/auto_tick?force=1&commands_only=1';
                $ch = curl_init($base . $path);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 2,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MartPoint-Fleet/1.0)',
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[] = $ch;
            } catch (Throwable $e) {}
        }
        $active = null;
        do { $mrc = curl_multi_exec($mh, $active); } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc === CURLM_OK) {
            if (curl_multi_select($mh) === -1) { usleep(50000); }
            do { $mrc = curl_multi_exec($mh, $active); } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        }
        $woke = 0;
        foreach ($handles as $ch) {
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = (string) curl_multi_getcontent($ch);
            if ($code === 200 && strpos($body, '"status"') !== false && strpos($body, '"idle"') === false) {
                $woke++;
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $woke;
    }

    /**
     * AJAX — write the standard MartPoint cron lines into cPanel for an
     * install. Uses the cron_key the install last reported via heartbeat
     * (or the one generated at provisioning).
     */
    public function schedule_crons() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $id = (int) $this->input->post('install_id');
        echo json_encode($this->scheduleCronForId($id));
    }

    /**
     * Per-install cron setup, shared by schedule_crons() (single row button)
     * and doQueueBulk() (the bulk bar). Returns the same array shape the AJAX
     * endpoint has always echoed.
     */
    private function scheduleCronForId(int $id): array {
        $install = $this->db->where('id', $id)->get('db_fleet_installs')->row();
        if (!$install) {
            return ['status' => 'error', 'message' => 'Install not found.'];
        }
        $cronKey = (string) ($install->cron_key ?? '');
        // Before re-keying, check whether the install already accepts a key
        // we know (stored, previously pushed, or legacy) — re-keying a
        // working install only creates churn.
        $known = $this->verifyCronKey($install->install_url, $this->candidateCronKeys($install));
        if ($known !== null && $known !== 'martpoint_cron_2024') {
            $cronKey = $known;
            if ($cronKey !== (string) ($install->cron_key ?? '')) {
                $this->db->where('id', $id)->update('db_fleet_installs', ['cron_key' => $cronKey]);
            }
        }
        if ($cronKey === '' || $cronKey === 'martpoint_cron_2024') {
            // No unique key yet — push one. Record it on the row NOW (the
            // install will hold it after the next check-in, and Central must
            // never forget a key it generated), schedule the cPanel lines with
            // it, and wake the install so the switch happens immediately.
            $newKey = 'ck_' . bin2hex(random_bytes(10));
            $this->db->insert('db_fleet_commands', [
                'install_id' => $id,
                'command' => 'set_cron_key',
                'payload' => $newKey,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $upd = ['cron_key' => $newKey];
            if ($this->db->field_exists('cron_auto', 'db_fleet_installs')) { $upd['cron_auto'] = 1; }
            $this->db->where('id', $id)->update('db_fleet_installs', $upd);
            $woke = $this->pingInstall($id, $why);
            $scheduledMsg = '';
            try {
                $this->scheduleCronLines($install->install_url, $newKey);
                $this->db->where('id', $id)->update('db_fleet_installs', ['cron_scheduled' => 1]);
                $scheduledMsg = ' cPanel cron lines scheduled with it.';
            } catch (Exception $e) {
                $scheduledMsg = ' (cPanel scheduling failed: ' . $e->getMessage() . ')';
            }
            return ['status' => 'ok', 'message' => 'Pushed a unique cron key to the install' . ($woke ? ' (applied now).' : ' (applies on its next check-in: ' . $why . ').') . $scheduledMsg];
        }
        try {
            $this->scheduleCronLines($install->install_url, $cronKey);
            if ($this->db->field_exists('cron_scheduled', 'db_fleet_installs')) {
                $this->db->where('id', $id)->update('db_fleet_installs', ['cron_scheduled' => 1]);
            }
            return ['status' => 'ok', 'message' => 'Cron jobs scheduled for ' . $install->install_url];
        } catch (Exception $e) {
            // API scheduling unavailable — give the admin the exact lines to
            // paste into cPanel → Cron Jobs so nothing is left half-done.
            $base = rtrim($install->install_url, '/');
            $lines = implode(' | ', [
                '*/30 * * * * → curl -s "' . $base . '/cron/auto_update?key=' . $cronKey . '"',
                '0 * * * * → curl -s "' . $base . '/cron/run_scheduled_reports?key=' . $cronKey . '"',
                '0 8 * * * → curl -s "' . $base . '/cron/send_debt_reminders?key=' . $cronKey . '"',
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage() . ' — add these manually in cPanel → Cron Jobs: ' . $lines,
            ];
        }
    }

    /**
     * Wake-up: hit the install's cron endpoints so it heartbeats and executes
     * queued commands immediately. fleet_ping is the fast path (commands only,
     * no update pipeline); auto_update is the legacy fallback — on old installs
     * it still runs the update pipeline, which is exactly what update_now wants.
     * Tries the reported cron_key AND the shared legacy fallback key; reports
     * the real failure reason via &$why instead of masking 403s as success.
     */
    /**
     * Every cron key this install could plausibly hold, most likely first:
     * the row's key, then any key Central itself pushed via set_cron_key
     * (the Cron button rewrites the install's config.php — if the heartbeat
     * build never reported it back, this is the only record of it), then
     * the legacy shared default.
     */
    private function candidateCronKeys($install): array {
        $keys = [];
        if (!empty($install->cron_key)) { $keys[] = (string) $install->cron_key; }
        try {
            $pushed = $this->db->select('payload')
                ->where('install_id', (int) $install->id)
                ->where('command', 'set_cron_key')
                ->order_by("FIELD(status,'done','sent','pending')", '', false)
                ->order_by('id', 'DESC')
                ->limit(5)->get('db_fleet_commands')->result();
            foreach ($pushed as $p) {
                $k = trim((string) $p->payload, " \t\n\r\"'");
                if (preg_match('/^[A-Za-z0-9_\-]{4,100}$/', $k)) { $keys[] = $k; }
            }
        } catch (Throwable $e) {}
        $keys[] = 'martpoint_cron_2024';
        return array_values(array_unique($keys));
    }

    /** A key just worked — remember it on the row so next time is instant. */
    private function learnCronKey($install, $key): void {
        if ($key === null || $key === '' || (string) ($install->cron_key ?? '') === (string) $key) { return; }
        try {
            $this->db->where('id', (int) $install->id)->update('db_fleet_installs', [
                'cron_key' => $key,
                'cron_scheduled' => 0, // stale cPanel lines carry the old key — reschedule on next heartbeat
            ]);
        } catch (Throwable $e) {}
    }

    private function pingInstall(int $installId, &$why = ''): bool {
        $why = '';
        try {
            $install = $this->db->where('id', $installId)->get('db_fleet_installs')->row();
            if (!$install) {
                $why = 'install not registered';
                return false;
            }
            // Attempt list, cheapest/most reliable first:
            //  1. fleet_ping + wake token (HMAC of install_key — immune to
            //     cron_secret_key drift; works on 4.0.9.48+ installs)
            //  2. auto_tick?force=1&commands_only=1 — KEYLESS: no cron key
            //     needed at all. On new builds it does heartbeat+poll only;
            //     old builds ignore the params and run the full tick if due.
            //  3. fleet_ping / auto_update with known cron keys (legacy).
            $keys = $this->candidateCronKeys($install);
            $base = rtrim($install->install_url, '/');

            // [path, cronKey|null] — the key is remembered so a hit can be
            // written back to the row (self-learning; no manual teaching).
            $attempts = [];
            if (!empty($install->install_key)) {
                $wake = hash_hmac('sha256', 'mp_wake', (string) $install->install_key);
                $attempts[] = ['/cron/fleet_ping?key=' . urlencode($wake), null];
            }
            $attempts[] = ['/system_updates/auto_tick?force=1&commands_only=1', null];
            foreach ($keys as $k) {
                $attempts[] = ['/cron/fleet_ping?key=' . urlencode($k), $k];
                $attempts[] = ['/cron/auto_update?key=' . urlencode($k), $k];
            }
            $attempts[] = ['/system_updates/auto_tick?force=1', null];

            $notes = [];
            foreach ($attempts as $attempt) {
                [$path, $usedKey] = $attempt;
                $tag = strpos($path, 'cron/') !== false
                    ? preg_replace('#\?.*#', '', $path) : 'auto_tick';
                $ch = curl_init($base . $path);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_TIMEOUT => 6,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 2,
                    // WAFs/ModSecurity frequently 403 bare curl UAs — this is
                    // the difference between a browser hit and a server ping.
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MartPoint-Fleet/1.0)',
                ]);
                $body = curl_exec($ch);
                $errno = curl_errno($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $sent = (int) curl_getinfo($ch, CURLINFO_REQUEST_SIZE) > 0;
                curl_close($ch);

                // '"idle"' = the tick was throttled and ran nothing.
                if ($code === 200 && strpos((string) $body, '"status"') !== false
                    && strpos((string) $body, '"idle"') === false) {
                    $this->learnCronKey($install, $usedKey);
                    return true;
                }
                if ($errno === CURLE_OPERATION_TIMEDOUT) {
                    if ($sent) { $this->learnCronKey($install, $usedKey); return true; } // request landed; still working
                    $notes[] = "{$tag}: timeout";
                    break;
                }
                // Decode the common shapes so the report says what it saw.
                if ($code === 403) {
                    parse_str((string) parse_url($path, PHP_URL_QUERY), $q);
                    $tried = isset($q['key']) ? (strlen($q['key']) > 40 ? 'wake-token' : $q['key']) : '-';
                    $note = strpos((string) $body, '"status"') !== false
                        ? "{$tag}: key '{$tried}' rejected by install"
                        : "{$tag}: 403 from host firewall (not the app)";
                }
                elseif ($code === 404) { $note = "{$tag}: 404"; }
                elseif ($code === 200 && stripos((string) $body, 'http-equiv') !== false) {
                    $note = "{$tag}: redirected to login (needs newer client build)";
                }
                elseif ($code === 200 && trim((string) $body) === '') {
                    $note = "{$tag}: 200 but empty (likely a PHP error on the install — check its error_log)";
                }
                else {
                    $snippet = trim(substr((string) preg_replace('/\s+/', ' ', strip_tags((string) $body)), 0, 60));
                    $note = "{$tag}: HTTP {$code}" . ($snippet !== '' ? " — {$snippet}" : '');
                }
                $notes[] = $note;
                if ($code === 0) { break; } // unreachable — later attempts will fail the same
            }
            $why = implode('; ', array_slice(array_unique($notes), -4));
            if ($why === '') { $why = 'unreachable'; }
            return false;
        } catch (Throwable $e) {
            $why = $e->getMessage();
            return false;
        }
    }

    /**
     * The three standard MartPoint cron jobs, scheduled on this cPanel
     * account. All client installs live here, so one API token covers them.
     * Some hosts ship cPanel without the UAPI Cron module — fall back to
     * the older API2 interface automatically.
     */
    private function scheduleCronLines(string $installUrl, string $cronKey): void {
        $base = rtrim($installUrl, '/');
        // Idempotent: clear this host's existing MartPoint lines first so a
        // re-key or re-run never leaves duplicates (or stale-key lines that
        // 403 silently every half hour).
        $host = strtolower((string) parse_url($installUrl, PHP_URL_HOST));
        if ($host !== '') { $this->removeCronLinesFor($host); }
        $jobs = [
            ['*/30', '*', '*', '*', '*', 'cron/auto_update'],
            ['0',    '*', '*', '*', '*', 'cron/run_scheduled_reports'],
            ['0',    '8', '*', '*', '*', 'cron/send_debt_reminders'],
        ];
        foreach ($jobs as $j) {
            $params = [
                'command' => 'curl -s "' . $base . '/' . $j[5] . '?key=' . $cronKey . '" >/dev/null 2>&1',
                'minute'  => $j[0],
                'hour'    => $j[1],
                'day'     => $j[2],
                'month'   => $j[3],
                'weekday' => $j[4],
            ];
            try {
                try {
                    $this->cpanelCall('Cron', 'add_line', $params);
                } catch (Exception $e) {
                    if (stripos($e->getMessage(), 'API::Cron') !== false
                        || stripos($e->getMessage(), 'load module') !== false) {
                        $this->cpanelApi2Call('Cron', 'add_line', $params);
                    } else {
                        throw $e;
                    }
                }
            } catch (Exception $e) {
                // An identical line is already scheduled — that IS the desired
                // end state, not a failure.
                if (stripos($e->getMessage(), 'already exists') === false) { throw $e; }
            }
        }
    }

    /**
     * Same as cpanelCall() but on the legacy API2 endpoint — needed for
     * modules (like Cron) that some hosts only expose there.
     */
    private function cpanelApi2Call(string $module, string $fn, array $params): array {
        $host = $this->getSetting('cpanel_host') ?: '127.0.0.1';
        $user = $this->getSetting('cpanel_user');
        $token = $this->getSetting('cpanel_token');
        $q = array_merge([
            'cpanel_jsonapi_user'       => $user,
            'cpanel_jsonapi_apiversion' => '2',
            'cpanel_jsonapi_module'     => $module,
            'cpanel_jsonapi_func'       => $fn,
        ], $params);
        $url = 'https://' . $host . ':2083/json-api/cpanel?' . http_build_query($q);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => ['Authorization: cpanel ' . $user . ':' . $token],
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            throw new Exception('cPanel API2 unreachable (' . $err . ').');
        }
        $data = json_decode((string) $resp, true);
        if (!is_array($data) || !isset($data['cpanelresult'])) {
            throw new Exception('cPanel API2 returned an unexpected response — cron scheduling is not available on this host.');
        }
        $cr = $data['cpanelresult'];
        // Module-level failure (bad token, missing function…).
        if (!empty($cr['error']) || (isset($cr['event']['result']) && (int) $cr['event']['result'] !== 1)) {
            throw new Exception('cPanel API2 ' . $module . '::' . $fn . ' failed: ' . ($cr['error'] ?? 'unknown error'));
        }
        // Mutating calls (add_line/remove_line) return one row with a status
        // flag; listing calls (listcron) return plain entries with none —
        // only enforce the flag when the response actually carries one.
        $row = $cr['data'][0] ?? [];
        if (is_array($row) && (array_key_exists('status', $row) || array_key_exists('result', $row))) {
            $status = $row['status'] ?? $row['result'];
            if ((int) $status !== 1) {
                $msg = $row['statusmsg'] ?? $row['reason'] ?? 'unknown error';
                throw new Exception('cPanel API2 ' . $module . '::' . $fn . ' failed: ' . $msg);
            }
        }
        return $data;
    }

    /**
     * Same encoding as generate_license_key() but locked to the CLIENT's
     * domain (generate_license_key would lock to this central host instead).
     */
    private function licenseKeyForDomain(string $domain, array $data): string {
        $payload = json_encode([
            'p' => $data['plan_name'] ?? 'Basic',
            's' => $data['subscription_start_date'] ?? date('Y-m-d'),
            'e' => $data['subscription_end_date'] ?? date('Y-m-d'),
            'b' => (int) ($data['branch_limit'] ?? 1),
            'u' => (int) ($data['user_limit'] ?? 3),
            'pr' => (int) ($data['product_limit'] ?? 500),
            'sk' => (int) ($data['sku_limit'] ?? 10000),
            'op' => (int) ($data['online_product_limit'] ?? 500),
            'sv' => (int) ($data['service_limit'] ?? 100),
            'm' => (int) ($data['media_storage_limit_mb'] ?? 2048),
            'sf' => (int) ($data['storefront_limit'] ?? 1),
            'cd' => (int) ($data['custom_domain_limit'] ?? 1),
            'w' => $data['whatsapp_number'] ?? '',
            'r' => $data['renewal_amount'] ?? '',
            'c' => $data['client_name'] ?? '',
            'd' => $domain,
            't' => time(),
        ]);
        $b64 = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $crc = substr(dechex(crc32($b64)), 0, 6);
        return 'MP-' . $b64 . '-' . strtoupper($crc);
    }

    private function authInstall(): ?object {
        if (!$this->db->table_exists('db_fleet_installs')) {
            return null;
        }
        $url = rtrim(trim((string) $this->input->post('install_url')), '/') . '/';
        $key = (string) $this->input->post('install_key');
        if ($url === '/' || $key === '') {
            return null;
        }
        $row = $this->db->where('install_url', $url)->get('db_fleet_installs')->row();
        if (!$row) {
            return null;
        }
        // Per-install key preferred; fleet_key is an accepted fallback (the
        // heartbeat already trusts it) so installs missing install_key still
        // poll commands — the install_url pins the row either way.
        if (!empty($row->install_key) && $key !== '' && hash_equals($row->install_key, $key)) {
            return $row;
        }
        $fleetKey = $this->getSetting('fleet_key');
        if ($fleetKey !== '' && hash_equals($fleetKey, (string) $this->input->post('key'))) {
            return $row;
        }
        return null;
    }

    /**
     * GET — stream the full install package to the deploy.php launcher.
     * Requires a valid deploy_key (rotated from the Fleet panel).
     */
    public function package() {
        $key = (string) $this->input->get('key');
        $deployKey = $this->getSetting('deploy_key');

        if ($deployKey === '' || !hash_equals($deployKey, $key)) {
            http_response_code(403);
            echo 'Invalid deploy key.';
            return;
        }

        $pkg = FCPATH . 'release_upload/releases/latest/martpoint-full.zip';
        if (!file_exists($pkg)) {
            http_response_code(404);
            echo 'No package built yet. Run Release → Build Full Package on the central install.';
            return;
        }

        if (function_exists('session_write_close')) {
            session_write_close();
        }
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($pkg));
        header('Content-Disposition: attachment; filename="martpoint-full.zip"');
        readfile($pkg);
    }

    /* ------------------------------------------------------------------ */

    private function getSetting(string $col): string {
        try {
            if (!$this->db->field_exists($col, 'db_sitesettings')) {
                return '';
            }
            $row = $this->db->select($col)->where('id', 1)->get('db_sitesettings')->row();
            return $row ? (string) ($row->{$col} ?? '') : '';
        } catch (Exception $e) {
            return '';
        }
    }
}
