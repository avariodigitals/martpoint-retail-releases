<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Updater — Robust chunked auto-update orchestrator for MartPoint Retail
 *
 * Designed for cPanel / shared hosting:
 * - No single PHP request runs for more than a few seconds
 * - Progress is persisted to disk so a page refresh can resume
 * - Each step is split into small batches (download / apply / verify)
 * - Session lock is never held during long operations
 * - File downloads have retry logic
 * - Migrations are idempotent (ignore already-exists errors)
 */
class Updater {

    protected $CI;
    protected $backupManager;
    protected $updateRecordId = 0;
    protected $protectedPaths = [
        'application/config/database.php',
        'application/config/config.php',
        'uploads/',
        'backups/',
        '.env',
        'application/config/constants.php',
    ];

    // How many files to download/verify/apply in one PHP request.
    // Keeps each request under ~5-10 seconds on shared hosting.
    protected $batchSize = 50;

    // Wall-clock budget per run_step request. Host request limits (FPM,
    // LiteSpeed, mod_php) kill a request that runs too long — and a killed
    // request never writes state, so the batch restarts and dies again in an
    // infinite "running" loop. Bound every step well under typical limits.
    protected $stepTimeBudget = 40;

    // State file used to resume across HTTP requests.
    protected $statePath;
    protected $tempDir;
    protected $lastManifestError = null;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('BackupManager');
        $this->backupManager = $this->CI->backupmanager;
        $this->CI->load->helper('file');
        $this->CI->config->load('updater', false, true);

        $this->tempDir = FCPATH . 'updates/temp';
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }
        $this->statePath = $this->tempDir . '/update-state.json';
    }

    /* ------------------------------------------------------------------ */
    /*  Version / manifest                                                */
    /* ------------------------------------------------------------------ */

    public function getInstalledVersion(): string {
        $row = $this->CI->db->select('version')
            ->from('db_sitesettings')
            ->where('id', 1)
            ->get()
            ->row();
        return $row ? $row->version : '0.0';
    }

    public function fetchManifest(): ?array {
        $channel = $this->getUpdateChannelUrl();
        // Cache-bust: a stale cached manifest paired with fresh files (or vice
        // versa) produces hash mismatches that have nothing to do with the
        // release itself — GitHub's raw CDN caches each URL independently.
        $manifestUrl = rtrim($channel, '/') . '/release-manifest.json?t=' . time();

        $json = $this->httpGet($manifestUrl, 60);
        if ($json === null) {
            // httpGet() already set $this->lastManifestError with the HTTP status detail
            return null;
        }

        $manifest = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastManifestError = "Manifest at {$manifestUrl} is not valid JSON (" . json_last_error_msg() . "). The file may be missing (404) or the channel URL may point at the wrong path.";
            return null;
        }

        // Guard against the PWA web-app manifest being served by mistake —
        // it has no "version" or "files" keys and would silently break updates.
        if (!isset($manifest['version']) || !isset($manifest['files'])) {
            $this->lastManifestError = "Manifest at {$manifestUrl} is missing required 'version'/'files' keys. The channel URL may be pointing at the PWA manifest.json instead of release-manifest.json.";
            return null;
        }

        return $manifest;
    }

    public function checkForUpdate(): array {
        $installed = $this->getInstalledVersion();
        $manifest = $this->fetchManifest();

        if (!$manifest) {
            return [
                'available' => false,
                'error' => $this->lastManifestError
                    ?? 'Unable to fetch release manifest. Check your update channel URL.',
                'installed_version' => $installed,
                'remote_version' => null,
            ];
        }

        if (!$this->verifyManifestSignature($manifest)) {
            return [
                'available' => false,
                'error' => 'Release manifest signature verification failed. The update channel may be compromised or misconfigured — update refused.',
                'installed_version' => $installed,
                'remote_version' => null,
            ];
        }

        // Manifest is trusted at this point — let it (re)point installs at the
        // central fleet registry so heartbeat endpoints can move without a
        // per-customer settings change.
        $this->applyManifestSettings($manifest);

        $remote = $manifest['version'] ?? '0.0';
        $available = version_compare($remote, $installed, '>');

        $blockReason = null;
        if ($available) {
            $blockReason = $this->phpVersionAllowed($manifest);
            if ($blockReason === null && !$this->licenseAllowsUpdate()) {
                $blockReason = 'Subscription expired or suspended — renew the subscription to receive updates.';
            }
        }

        return [
            'available' => $available,
            'blocked' => $blockReason !== null,
            'block_reason' => $blockReason,
            'auto_update' => $this->autoUpdateEnabled(),
            'installed_version' => $installed,
            'remote_version' => $remote,
            'release_date' => $manifest['release_date'] ?? null,
            'changelog' => $manifest['changelog'] ?? 'No changelog provided.',
            'manifest' => $manifest,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Auto-update orchestration (cron / lazy login check)               */
    /* ------------------------------------------------------------------ */

    /**
     * Run the whole update pipeline server-side within a wall-clock budget.
     * Safe to call repeatedly — the persisted state resumes mid-update, so a
     * daily cron or the login-time lazy check can each do a slice of work.
     */
    public function runAutoUpdate(int $budgetSeconds = 45): array {
        if (!$this->autoUpdateEnabled()) {
            return ['status' => 'skipped', 'message' => 'Auto-update is disabled.'];
        }

        $check = $this->checkForUpdate();
        if (!empty($check['error'])) {
            return ['status' => 'error', 'message' => $check['error']];
        }
        $state = $this->readState();
        $resuming = !empty($state) && empty($state['done']) && empty($state['failed']);

        if (empty($check['available']) && !$resuming) {
            return ['status' => 'ok', 'done' => true, 'message' => 'No update available.'];
        }
        if (!$resuming && !empty($check['blocked'])) {
            return ['status' => 'blocked', 'message' => $check['block_reason']];
        }

        $manifest = $check['manifest'] ?? ($state['manifest'] ?? []);
        if (empty($manifest)) {
            return ['status' => 'error', 'message' => 'No manifest available to run update.'];
        }
        $preview = $this->previewChanges($manifest);

        $deadline = microtime(true) + $budgetSeconds;
        $last = null;
        for ($i = 0; $i < 2000; $i++) {
            $state = $this->readState();
            $step = (!empty($state['step'])) ? (int) $state['step'] : 1;
            $last = $this->runStep($step, $manifest, $preview);

            if (($last['status'] ?? '') === 'error' || !empty($last['failed'])) {
                return ['status' => 'error', 'message' => $last['message'] ?? 'Update failed.', 'step' => $step];
            }
            if (!empty($last['done']) && (int) ($last['step'] ?? $step) >= 8) {
                $this->sendHeartbeat();
                return ['status' => 'ok', 'done' => true, 'message' => 'Updated to ' . ($check['remote_version'] ?? ($state['to_version'] ?? 'latest')) . '.'];
            }
            if (microtime(true) >= $deadline) {
                return [
                    'status' => 'ok',
                    'done' => false,
                    'message' => 'Update in progress — will resume on the next run.',
                    'step' => (int) ($last['step'] ?? $step),
                    'step_label' => $last['step_label'] ?? '',
                ];
            }
            usleep(100000);
        }
        return ['status' => 'ok', 'done' => false, 'message' => 'Update still in progress.'];
    }

    /**
     * Whether unattended updates are allowed on this install.
     * Defaults to enabled; missing column (pre-migration install) is treated
     * as enabled so an update delivering this code is never self-blocking.
     */
    public function autoUpdateEnabled(): bool {
        try {
            if (!$this->CI->db->field_exists('auto_update_enabled', 'db_sitesettings')) {
                return true;
            }
            $row = $this->CI->db->select('auto_update_enabled')
                ->from('db_sitesettings')->where('id', 1)->get()->row();
            return !$row || (int) $row->auto_update_enabled === 1;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Throttle for the login-time lazy check (default: every 6 hours).
     * Returns true immediately when an update is mid-flight so it resumes.
     */
    public function shouldAutoCheck(int $intervalSeconds = 21600): bool {
        $state = $this->readState();
        if (!empty($state) && empty($state['done']) && empty($state['failed'])) {
            return true;
        }
        $stamp = $this->tempDir . '/auto-check.stamp';
        if (!file_exists($stamp)) {
            return true;
        }
        return (time() - (int) @file_get_contents($stamp)) >= $intervalSeconds;
    }

    public function touchAutoCheck(): void {
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }
        @file_put_contents($this->tempDir . '/auto-check.stamp', (string) time());
    }

    /**
     * Report this install to the central fleet registry. Fire-and-forget —
     * failures are logged but never affect the caller.
     */
    public function sendHeartbeat(): void {
        try {
            // Central is the registry, not a member — never register itself.
            if (function_exists('mp_is_central') && mp_is_central()) {
                return;
            }
            $fleetUrl = $this->getSitesetting('fleet_url');
            if (empty($fleetUrl)) {
                return;
            }
            $payload = [
                'key'         => $this->getSitesetting('fleet_key'),
                'install_url' => base_url(),
                'install_key' => $this->installKey(),
                'version'     => $this->getInstalledVersion(),
                'php_version' => PHP_VERSION,
                'license_code' => $this->getLicenseCode(),
                'cron_key'    => $this->cronKey(),
            ];
            $meta = $this->storeMeta();
            $payload['store_name']    = $meta['store_name'];
            $payload['store_city']    = $meta['city'];
            $payload['store_state']   = $meta['state'];
            $payload['store_country'] = $meta['country'];
            $summary = $this->licenseUsageSummary();
            if ($summary) {
                $payload['license_status'] = $summary['status'] ?? '';
                $payload['plan_name']      = $summary['plan_name'] ?? '';
                $payload['days_left']      = (string) ($summary['days_left'] ?? '');
                $payload['usage_json']     = json_encode($summary['quotas'] ?? []);
            }
            $this->httpPost(rtrim($fleetUrl, '/') . '/fleet/heartbeat', $payload, 8);
        } catch (Exception $e) {
            log_message('error', 'Updater heartbeat failed: ' . $e->getMessage());
        }
    }

    /**
     * Store identity + location for the fleet registry — read straight from
     * db_store. city/state/country power Central's installs-by-region stats.
     */
    protected function storeMeta(): array {
        $meta = ['store_name' => '', 'city' => '', 'state' => '', 'country' => ''];
        try {
            if ($this->CI->db->table_exists('db_store')) {
                $cols = array_intersect(
                    ['store_name', 'city', 'state', 'country'],
                    $this->CI->db->list_fields('db_store')
                );
                if ($cols) {
                    $s = $this->CI->db->select(implode(',', $cols))
                        ->where('id', $this->resolveStoreId())->get('db_store')->row();
                    if ($s) {
                        foreach ($cols as $c) {
                            $meta[$c] = substr((string) ($s->{$c} ?? ''), 0, 150);
                        }
                    }
                }
            }
        } catch (Exception $e) {
        }
        return $meta;
    }

    /**
     * Per-install secret — generated once and kept in db_sitesettings. Sent
     * with the heartbeat so central can authenticate command polling; it is
     * never exposed in the UI or the (public) release manifest.
     */
    protected function installKey(): string {
        $key = $this->getSitesetting('install_key');
        if ($key !== '') {
            return $key;
        }
        try {
            if (!$this->CI->db->field_exists('install_key', 'db_sitesettings')) {
                return '';
            }
            $key = 'ik_' . bin2hex(random_bytes(20));
            $this->CI->db->where('id', 1)->update('db_sitesettings', ['install_key' => $key]);
            return $key;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Ask central for pending commands for this install, execute them, and
     * post the results back. Lets the vendor trigger actions (e.g. update now)
     * from the Fleet panel without logging into this install.
     */
    public function pollFleetCommands(): array {
        try {
            if (function_exists('mp_is_central') && mp_is_central()) {
                return [];
            }
            $fleetUrl = $this->getSitesetting('fleet_url');
            $installKey = $this->installKey();
            if (empty($fleetUrl) || empty($installKey)) {
                return [];
            }
            $base = rtrim($fleetUrl, '/');
            $resp = $this->httpPost($base . '/fleet/commands', [
                'install_url' => base_url(),
                'install_key' => $installKey,
            ], 8);
            if ($resp === null) {
                return [];
            }
            $data = json_decode($resp, true);
            $results = [];
            foreach (($data['commands'] ?? []) as $cmd) {
                $id = (int) ($cmd['id'] ?? 0);
                $command = (string) ($cmd['command'] ?? '');
                $result = $this->executeFleetCommand($command, (string) ($cmd['payload'] ?? ''));
                $this->httpPost($base . '/fleet/command_result', [
                    'install_url' => base_url(),
                    'install_key' => $installKey,
                    'command_id'  => $id,
                    'status'      => $result['ok'] ? 'done' : 'failed',
                    'result'      => substr((string) $result['message'], 0, 2000),
                ], 8);
                $results[] = ['command' => $command] + $result;
            }
            return $results;
        } catch (Exception $e) {
            log_message('error', 'Updater pollFleetCommands failed: ' . $e->getMessage());
            return [];
        }
    }

    protected function executeFleetCommand(string $command, string $payload = ''): array {
        switch ($command) {
            case 'update_now':
                $r = $this->runAutoUpdate(90);
                return [
                    'ok' => in_array($r['status'] ?? '', ['ok', 'skipped'], true),
                    'message' => ($r['status'] ?? '?') . ': ' . ($r['message'] ?? ''),
                ];
            case 'report_status':
                return ['ok' => true, 'message' => 'v' . $this->getInstalledVersion() . ' / PHP ' . PHP_VERSION];
            case 'set_license':
                return $this->applyPushedLicense($payload);
            case 'request_license_otp':
                return $this->pushLicenseOtp();
            case 'suspend':
                return $this->setSubscriptionSuspended(true, $payload);
            case 'resume':
                return $this->setSubscriptionSuspended(false);
            case 'set_email':
                return $this->applyEmailSettings($payload);
            case 'set_cron_key':
                return $this->applyCronKey($payload);
            case 'set_settings':
                return $this->applyPushedSettings($payload);
            case 'run_backup':
                return $this->runDatabaseBackup();
            default:
                return ['ok' => false, 'message' => 'Unknown command: ' . $command];
        }
    }

    /**
     * Effective cron secret — same fallback the Cron controller uses, so the
     * value reported to central always matches what the endpoint expects.
     */
    protected function cronKey(): string {
        $k = (string) $this->CI->config->item('cron_secret_key');
        return $k !== '' ? $k : 'martpoint_cron_2024';
    }

    /**
     * Apply email/provider settings pushed from central. Writes whichever
     * whitelisted columns exist in db_email_settings (and mirrors legacy
     * smtp_* columns in db_store_notification_settings when present).
     */
    protected function applyEmailSettings(string $payload): array {
        $fields = json_decode($payload, true);
        if (!is_array($fields) || empty($fields)) {
            return ['ok' => false, 'message' => 'Invalid email payload.'];
        }
        $allowed = [
            'email_provider', 'email_from_name', 'email_from_email', 'email_reply_to',
            'smtp_crypto', 'resend_api_key', 'resend_from_email', 'resend_from_name',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_status',
        ];
        $storeId = $this->resolveStoreId();
        $written = 0;
        try {
            foreach (['db_email_settings', 'db_store_notification_settings'] as $table) {
                if (!$this->CI->db->table_exists($table)) {
                    continue;
                }
                $data = [];
                foreach ($allowed as $k) {
                    if (isset($fields[$k]) && $fields[$k] !== '' && $this->CI->db->field_exists($k, $table)) {
                        $data[$k] = $fields[$k];
                    }
                }
                if (empty($data)) {
                    continue;
                }
                $exists = $this->CI->db->where('store_id', $storeId)->get($table)->row();
                if ($exists) {
                    $this->CI->db->where('store_id', $storeId)->update($table, $data);
                } else {
                    $data['store_id'] = $storeId;
                    $this->CI->db->insert($table, $data);
                }
                $written++;
            }
            return [
                'ok' => $written > 0,
                'message' => $written > 0
                    ? 'Email settings applied (' . ($fields['email_provider'] ?? 'resend') . ').'
                    : 'No matching email columns on this install.',
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'Email apply error: ' . $e->getMessage()];
        }
    }

    /**
     * Set this install's cron_secret_key in config.php — central generates a
     * unique key per install so it can schedule the cPanel cron lines.
     */
    protected function applyCronKey(string $payload): array {
        $key = trim($payload);
        if (!preg_match('/^[A-Za-z0-9_\-]{6,64}$/', $key)) {
            return ['ok' => false, 'message' => 'Invalid cron key format.'];
        }
        $path = FCPATH . 'application/config/config.php';
        try {
            if (!is_writable($path)) {
                return ['ok' => false, 'message' => 'config.php not writable — set permissions and retry.'];
            }
            $code = (string) file_get_contents($path);
            $line = "\$config['cron_secret_key'] = '" . $key . "';";
            if (strpos($code, "cron_secret_key") !== false) {
                $code = preg_replace("/\\\$config\['cron_secret_key'\]\s*=\s*'[^']*';/", $line, $code, 1);
            } else {
                $code = rtrim($code) . "\n\n" . $line . "\n";
            }
            $ok = file_put_contents($path, $code) !== false;
            return ['ok' => $ok, 'message' => $ok ? 'Cron key set.' : 'Could not write config.php.'];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'Cron key error: ' . $e->getMessage()];
        }
    }

    /**
     * Push a group of operational settings from Central — payload:
     * {"scope":"assist|paystack|monnify|nin|debt_reminder|audit","fields":{...}}
     * Each scope maps to a whitelisted table + column set; anything else is
     * rejected. Store-keyed tables upsert on this install's store row.
     */
    protected function applyPushedSettings(string $payload): array {
        $in = json_decode($payload, true);
        $scope = is_array($in) ? (string) ($in['scope'] ?? '') : '';
        $fields = is_array($in) && is_array($in['fields'] ?? null) ? $in['fields'] : [];
        if ($fields === []) {
            return ['ok' => false, 'message' => 'No settings fields in payload.'];
        }
        $storeId = $this->resolveStoreId();
        try {
            switch ($scope) {
                case 'assist':
                    return $this->writeSettingFields('db_sitesettings', ['id' => 1], [
                        'assist_ai_enabled', 'assist_ai_provider', 'assist_ai_endpoint',
                        'assist_ai_model', 'assist_ai_key',
                    ], $fields, 'Assist AI');

                case 'paystack':
                    return $this->writeSettingFields('db_paystack_settings', ['store_id' => $storeId], [
                        'enabled', 'public_key', 'secret_key', 'test_mode', 'webhook_secret',
                    ], $fields, 'Paystack', true);

                case 'monnify':
                    return $this->writeSettingFields('db_monnify_settings', ['store_id' => $storeId], [
                        'enabled', 'api_key', 'secret_key', 'contract_code',
                        'wallet_account_number', 'disbursements_enabled', 'test_mode',
                    ], $fields, 'Monnify', true);

                case 'nin':
                    return $this->writeSettingFields('db_store', ['id' => $storeId], [
                        'nin_api_enabled', 'nin_api_url', 'nin_api_key', 'nin_api_provider',
                        'nin_provider', 'bvn_provider',
                        'interswitch_client_id', 'interswitch_client_secret',
                    ], $fields, 'NIN verification');

                case 'debt_reminder':
                    return $this->writeSettingFields('db_debt_reminder_settings',
                        ['store_id' => $storeId, 'customer_id' => 0], [
                        'enabled', 'frequency', 'max_reminders', 'send_email', 'send_sms',
                    ], $fields, 'Debt reminders', true);

                case 'audit':
                    // Column self-heals so this works even before the audit
                    // toggle migration reaches this install.
                    if (!$this->CI->db->field_exists('audit_trail_enabled', 'db_sitesettings')) {
                        $this->CI->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `audit_trail_enabled` TINYINT(1) NOT NULL DEFAULT 1");
                    }
                    return $this->writeSettingFields('db_sitesettings', ['id' => 1],
                        ['audit_trail_enabled'], $fields, 'Audit trail');
            }
            return ['ok' => false, 'message' => 'Unknown settings scope: ' . $scope];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'Settings error: ' . $e->getMessage()];
        }
    }

    /**
     * Filter the pushed fields to a whitelist of existing columns and write
     * them — update when the row exists, insert (with the where values) when
     * the table is keyed per-store and has none yet.
     */
    protected function writeSettingFields(string $table, array $where, array $allowed, array $fields, string $label, bool $upsert = false): array {
        if (!$this->CI->db->table_exists($table)) {
            return ['ok' => false, 'message' => $label . ' table missing on this install.'];
        }
        $data = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $fields) && $this->CI->db->field_exists($col, $table)) {
                $v = $fields[$col];
                $data[$col] = is_scalar($v) ? substr((string) $v, 0, 2000) : (string) json_encode($v);
            }
        }
        if ($data === []) {
            return ['ok' => false, 'message' => 'No writable ' . $label . ' fields — the install may need an update for these settings.'];
        }
        $exists = $this->CI->db->where($where)->get($table)->num_rows() > 0;
        if ($exists) {
            $this->CI->db->where($where)->update($table, $data);
        } elseif ($upsert) {
            $this->CI->db->insert($table, $where + $data);
        } else {
            return ['ok' => false, 'message' => $label . ' row not found.'];
        }
        if (function_exists('mp_audit_log')) {
            // Column names only — never log pushed values (keys/secrets).
            mp_audit_log('fleet', 'settings_push', null,
                'Central pushed ' . $label . ' settings: ' . implode(', ', array_keys($data)));
        }
        return ['ok' => true, 'message' => $label . ' settings updated (' . count($data) . ' fields).'];
    }

    /**
     * Run a full database backup on this install via the existing
     * BackupManager — lands in dbbackup/ like the update-time backups.
     */
    protected function runDatabaseBackup(): array {
        try {
            $this->CI->load->library('BackupManager');
            $path = $this->CI->backupmanager->backupDatabase();
            if (!$path || !is_file($path)) {
                return ['ok' => false, 'message' => 'Backup produced no file.'];
            }
            return [
                'ok' => true,
                'message' => basename($path) . ' (' . round(filesize($path) / 1048576, 1) . ' MB)',
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'Backup error: ' . $e->getMessage()];
        }
    }

    /**
     * License status + quota usage for the fleet heartbeat. Returns null when
     * the helper or license tables aren't present on this install.
     */
    protected function licenseUsageSummary(): ?array {
        try {
            if (function_exists('mp_get_license_usage_summary')) {
                $s = mp_get_license_usage_summary($this->resolveStoreId());
                if (is_array($s)) {
                    return $s;
                }
            }
            // Fallback — installs whose custom_helper.php predates the summary
            // helper would otherwise report nothing and show "unknown" in the
            // fleet. Read the license record directly; quotas stay empty.
            if ($this->CI->db->table_exists('db_subscription_license')) {
                $rec = $this->CI->db->where('store_id', $this->resolveStoreId())
                    ->get('db_subscription_license')->row();
                if (!$rec) {
                    // License may sit on another store_id on this install —
                    // report the most recent row rather than nothing.
                    $rec = $this->CI->db->order_by('id', 'desc')->limit(1)
                        ->get('db_subscription_license')->row();
                }
                if ($rec) {
                    $status = (string) ($rec->subscription_status ?? 'NOT_ACTIVATED');
                    $daysLeft = null;
                    if (!empty($rec->subscription_end_date)) {
                        $daysLeft = (int) floor((strtotime($rec->subscription_end_date) - time()) / 86400);
                        if ($status === 'ACTIVE' && $daysLeft < 0) {
                            $status = 'EXPIRED';
                        } elseif ($status === 'ACTIVE' && $daysLeft <= 30) {
                            $status = 'EXPIRING_SOON';
                        }
                    }
                    return [
                        'status' => $status,
                        'days_left' => max(0, (int) $daysLeft),
                        'end_date' => $rec->subscription_end_date ?? null,
                        'plan_name' => (string) ($rec->plan_name ?? ''),
                        'license_code' => $rec->license_code ?? null,
                        'has_license' => !empty($rec->license_code),
                        'quotas' => [],
                    ];
                }
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Suspend/resume this install's subscription from a central fleet command.
     * SUSPENDED is enforced by MY_Controller::enforce_subscription() — every
     * page except dashboard/subscription/login is blocked.
     */
    protected function setSubscriptionSuspended(bool $suspend, string $payload = ''): array {
        try {
            if (!$this->CI->db->table_exists('db_subscription_license')) {
                return ['ok' => false, 'message' => 'License table missing on this install.'];
            }
            $data = [
                'store_id'            => $this->resolveStoreId(),
                'subscription_status' => $suspend ? 'SUSPENDED' : 'ACTIVE',
            ];
            if ($this->CI->db->field_exists('suspension_reason', 'db_subscription_license')) {
                $reason = trim($payload);
                $data['suspension_reason'] = $suspend
                    ? ($reason !== '' ? substr($reason, 0, 255) : 'Suspended by vendor.')
                    : null;
            }
            if ($suspend) {
                // get_status() returns NOT_ACTIVATED when no end date exists —
                // stamp one so the SUSPENDED flag actually gates the install.
                $this->CI->load->model('subscription_license_model', 'mp_lic_susp');
                $rec = $this->CI->mp_lic_susp->get_by_store($data['store_id']);
                if (!$rec || empty($rec->subscription_end_date)) {
                    $data['subscription_end_date'] = date('Y-m-d');
                }
            }
            $this->CI->load->model('subscription_license_model', 'mp_lic_cmd');
            $ok = $this->CI->mp_lic_cmd->save($data);
            return [
                'ok' => (bool) $ok,
                'message' => $ok
                    ? ($suspend ? 'Subscription suspended.' : 'Subscription resumed.')
                    : 'Status save failed.',
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'Suspend error: ' . $e->getMessage()];
        }
    }

    /**
     * Apply a license/subscription pushed from central. Replicates the local
     * Subscription_license::activate flow — decode the MP- key (authoritative
     * quotas), domain-check it, archive the replaced license to history, then
     * activate(). Columns are still field_exists-filtered so older installs
     * can't SQL-error.
     */
    protected function applyPushedLicense(string $payload): array {
        $lic = json_decode($payload, true);
        if (!is_array($lic) || empty($lic['subscription_end_date'])) {
            return ['ok' => false, 'message' => 'Invalid license payload.'];
        }
        try {
            if (!$this->CI->db->table_exists('db_subscription_license')) {
                return ['ok' => false, 'message' => 'License table missing on this install.'];
            }
            if (!function_exists('decode_license_key')) {
                $this->CI->load->helper('custom');
            }
            $storeId = $this->resolveStoreId();
            $this->CI->load->model('subscription_license_model', 'mp_lic_cmd');

            // OTP gate — two accepted forms:
            //  a) otp_proof — HMAC(otp|domain, fleet_key) from Central's own
            //     OTP generator (Central emails it to the vendor address).
            //  b) a local 'activate' OTP row in db_license_otps (the flow the
            //     install's Subscription page uses).
            $otp = strtoupper(trim((string) ($lic['otp_code'] ?? '')));
            $proof = (string) ($lic['otp_proof'] ?? '');
            $ownDomain = (string) parse_url(base_url(), PHP_URL_HOST);
            if ($proof !== '') {
                $expected = hash_hmac('sha256', $otp . '|' . $ownDomain, $this->getSitesetting('fleet_key'));
                if ($otp === '' || !hash_equals($expected, $proof)) {
                    return ['ok' => false, 'message' => 'OTP rejected: invalid vendor proof.'];
                }
            } else {
                if ($otp === '') {
                    return ['ok' => false, 'message' => 'OTP required. Use Request OTP in Central — it is emailed to the authorized address.'];
                }
                $otpCheck = $this->CI->mp_lic_cmd->validate_otp($storeId, $otp, 'activate');
                if ($otpCheck !== true) {
                    return ['ok' => false, 'message' => 'OTP rejected: ' . $otpCheck];
                }
            }

            // Prefer the signed key's own decoded data — same source of truth
            // a store admin gets when pasting the key locally.
            $decoded = (!empty($lic['license_code']) && function_exists('decode_license_key'))
                ? decode_license_key($lic['license_code'])
                : false;
            if ($decoded !== false && !empty($decoded['domain'])) {
                $own = (string) parse_url(base_url(), PHP_URL_HOST);
                if ($decoded['domain'] !== $own) {
                    return ['ok' => false, 'message' => 'License key is locked to ' . $decoded['domain'] . ' — this install is ' . $own . '.'];
                }
            }

            $saveData = ['store_id' => $storeId];
            if ($decoded !== false) {
                foreach ([
                    'plan_name', 'subscription_start_date', 'subscription_end_date',
                    'branch_limit', 'user_limit', 'product_limit', 'sku_limit',
                    'online_product_limit', 'service_limit', 'media_storage_limit_mb',
                    'storefront_limit', 'custom_domain_limit', 'whatsapp_number',
                    'renewal_amount', 'client_name',
                ] as $k) {
                    $saveData[$k] = $decoded[$k];
                }
                $saveData['license_code'] = $lic['license_code'];
            } else {
                // Legacy payload without a usable key — honour posted fields.
                foreach ([
                    'license_code', 'plan_name', 'subscription_start_date', 'subscription_end_date',
                    'branch_limit', 'user_limit', 'product_limit', 'sku_limit',
                    'invoice_limit', 'online_product_limit', 'service_limit', 'media_storage_limit_mb',
                    'storefront_limit', 'custom_domain_limit', 'whatsapp_number', 'renewal_amount',
                    'client_name',
                ] as $k) {
                    if (isset($lic[$k])) {
                        $saveData[$k] = $lic[$k];
                    }
                }
            }
            $saveData['domain'] = (string) parse_url(base_url(), PHP_URL_HOST);
            $saveData['last_renewal_date'] = date('Y-m-d');
            $saveData['suspension_reason'] = null;
            foreach ($saveData as $k => $v) {
                if ($k !== 'store_id' && !$this->CI->db->field_exists($k, 'db_subscription_license')) {
                    unset($saveData[$k]);
                }
            }

            // Archive the license being replaced — mirrors activate()/extend().
            $existing = $this->CI->mp_lic_cmd->get_by_store($storeId);
            if ($existing && !empty($existing->license_code)) {
                $this->CI->mp_lic_cmd->add_history(
                    $storeId, $existing->license_code,
                    $existing->plan_name ?? '', $existing->domain ?? '', 'active'
                );
            }

            // Reset reminder flags like the renew flow does.
            $reset = [
                'reminder_90_sent' => 0, 'reminder_60_sent' => 0,
                'reminder_30_last_sent' => null, 'reminder_10_last_sent' => null,
                'expiry_notice_sent' => 0, 'expired_followup_count' => 0,
                'expired_followup_last_sent' => null,
            ];
            foreach ($reset as $k => $v) {
                if ($this->CI->db->field_exists($k, 'db_subscription_license')) {
                    $this->CI->db->where('store_id', $storeId)->update('db_subscription_license', [$k => $v]);
                }
            }

            $ok = $this->CI->mp_lic_cmd->activate($storeId, $saveData);
            return [
                'ok' => (bool) $ok,
                'message' => $ok
                    ? 'License activated: ' . ($saveData['plan_name'] ?? '') . ' until ' . $saveData['subscription_end_date']
                    : 'License activation failed.',
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'License apply error: ' . $e->getMessage()];
        }
    }

    /**
     * Generate a license-activation OTP on this install and email it to the
     * authorized vendor address — mirrors Subscription_license::request_otp
     * + _send_otp_email, but triggered by a Central fleet command instead of
     * a logged-in session. The returned OTP is what a set_license payload
     * must carry to activate.
     */
    protected function pushLicenseOtp(): array {
        try {
            if (!$this->CI->db->table_exists('db_license_otps')) {
                return ['ok' => false, 'message' => 'OTP table missing on this install — run the database update.'];
            }
            $storeId = $this->resolveStoreId();

            // Same 60-second rate limit as the local request_otp endpoint.
            $recent = $this->CI->db->where('store_id', $storeId)
                ->where('otp_type', 'activate')
                ->where('created_at >', date('Y-m-d H:i:s', strtotime('-60 seconds')))
                ->get('db_license_otps')->row();
            if ($recent) {
                return ['ok' => false, 'message' => 'An OTP was already sent less than 60 seconds ago — check the authorized email.'];
            }

            $this->CI->load->model('subscription_license_model', 'mp_lic_otp');
            $otp = $this->CI->mp_lic_otp->generate_otp($storeId, 'activate');

            // Same recipient + audit body as _send_otp_email().
            $storeName = '';
            if ($this->CI->db->table_exists('db_store')) {
                $s = $this->CI->db->where('id', $storeId)->get('db_store')->row();
                $storeName = (string) ($s->store_name ?? '');
            }
            $domain = (string) parse_url(base_url(), PHP_URL_HOST);
            $subject = "MartPoint License OTP: Activate - {$storeName}";
            $html = "<h3>MartPoint Retail License OTP</h3>
<p><strong>Business:</strong> " . htmlspecialchars($storeName) . "</p>
<p><strong>Domain:</strong> {$domain}</p>
<p><strong>Action:</strong> Activate</p>
<p><strong>OTP:</strong> <span style='font-size:24px; font-weight:bold; color:#2563EB;'>{$otp}</span></p>
<p><em>This OTP expires in 10 minutes and can only be used once.</em></p>
<hr>
<p><strong>Request Details (Audit)</strong></p>
<ul>
  <li><strong>User:</strong> MartPoint Central (fleet command)</li>
  <li><strong>IP Address:</strong> " . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'cron') . "</li>
  <li><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</li>
</ul>
<hr>
<p style='color:#94A3B8; font-size:12px;'>MartPoint Retail License Security</p>";
            $text = "MartPoint Retail License OTP\nBusiness: {$storeName}\nDomain: {$domain}\nAction: Activate\nOTP: {$otp}\nExpires in 10 minutes, single use.\nRequested by: MartPoint Central (fleet command)\nTime: " . date('Y-m-d H:i:s');

            $this->CI->load->model('email_service');
            $result = $this->CI->email_service->sendRaw('rapheal@avariodigitals.com', $subject, $html, $text, [
                'template_key' => 'license_otp',
                'from_name' => 'MartPoint Retail',
                'send_copy_to_owner' => false,
            ]);
            // Always surface the OTP in the command result — Central is a
            // vendor-only panel, it can display/forward it even when this
            // install's email isn't configured.
            if (!empty($result['success'])) {
                return ['ok' => true, 'message' => "OTP {$otp} — emailed to the authorized address."];
            }
            return ['ok' => true, 'message' => "OTP {$otp} — install email failed, Central will forward it."];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => 'OTP error: ' . $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Gates                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Returns null when the host PHP satisfies the manifest's declared bounds,
     * otherwise a human-readable refusal reason.
     */
    protected function phpVersionAllowed(array $manifest): ?string {
        $min = $manifest['requires_php_min'] ?? null;
        $max = $manifest['requires_php_max'] ?? null;
        if ($min && version_compare(PHP_VERSION, $min, '<')) {
            return "This release requires PHP {$min}+; this server runs " . PHP_VERSION . '. Change the PHP handler first.';
        }
        if ($max && version_compare(PHP_VERSION, $max, '>')) {
            return "This release requires PHP up to {$max}; this server runs " . PHP_VERSION . ' (e.g. PHP 8 breaks the CI3 core — set the handler to 7.4).';
        }
        return null;
    }

    /**
     * License gate for updates. Expired/suspended subscriptions stop receiving
     * code; NOT_ACTIVATED and missing tables stay allowed so fresh or very old
     * installs can always reach the version that introduced licensing.
     */
    protected function licenseAllowsUpdate(): bool {
        try {
            // The vendor's own console must always take updates — it ships them.
            if (function_exists('mp_is_central') && mp_is_central()) {
                return true;
            }
            if (!$this->CI->db->table_exists('db_subscription_license')) {
                return true;
            }
            $this->CI->load->model('subscription_license_model', 'mp_lic_upd');
            $status = $this->CI->mp_lic_upd->get_status($this->resolveStoreId());
            $s = strtoupper($status['status'] ?? '');
            return !in_array($s, ['EXPIRED', 'SUSPENDED'], true);
        } catch (Exception $e) {
            return true;
        }
    }

    // Session store in web context; the primary store when run from cron/CLI
    // where no session exists.
    protected function resolveStoreId(): int {
        $storeId = (int) get_current_store_id();
        if ($storeId > 0) {
            return $storeId;
        }
        try {
            $row = $this->CI->db->select_min('id')->get('db_store')->row();
            return (int) ($row->id ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    protected function getLicenseCode(): string {
        try {
            if (!$this->CI->db->table_exists('db_subscription_license')) {
                return '';
            }
            $rec = $this->CI->db->select('license_code')
                ->where('store_id', $this->resolveStoreId())
                ->get('db_subscription_license')->row();
            if (!$rec) {
                // Single-store installs may carry the license on a different
                // store_id — fall back to the most recent license row.
                $rec = $this->CI->db->select('license_code')
                    ->order_by('id', 'desc')->limit(1)
                    ->get('db_subscription_license')->row();
            }
            return $rec ? (string) ($rec->license_code ?? '') : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Verify the manifest's ed25519 signature against the public key in
     * config/updater.php. When no public key is configured the check passes —
     * signing is opt-in hardening; once configured it is strictly enforced.
     */
    protected function verifyManifestSignature(array $manifest): bool {
        $pubkey = (string) $this->CI->config->item('update_pubkey');
        if ($pubkey === '') {
            return true;
        }
        $sig = $manifest['signature'] ?? null;
        if (empty($sig) || !function_exists('sodium_crypto_sign_verify_detached')) {
            return false;
        }
        try {
            return sodium_crypto_sign_verify_detached(
                sodium_hex2bin($sig),
                $this->manifestPayload($manifest),
                sodium_hex2bin($pubkey)
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Canonical payload that gets signed: the manifest without its signature.
     * Key order is stable because the generator writes the same structure.
     */
    protected function manifestPayload(array $manifest): string {
        unset($manifest['signature']);
        return json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Let the (verified) manifest point installs at the fleet registry —
     * fleet_url/fleet_key ship inside the manifest so a channel move or a
     * first-time rollout reaches every install without manual settings edits.
     */
    protected function applyManifestSettings(array $manifest): void {
        try {
            $updates = [];
            if (!empty($manifest['fleet_url']) && $this->CI->db->field_exists('fleet_url', 'db_sitesettings')) {
                $updates['fleet_url'] = $manifest['fleet_url'];
            }
            if (!empty($manifest['fleet_key']) && $this->CI->db->field_exists('fleet_key', 'db_sitesettings')) {
                $updates['fleet_key'] = $manifest['fleet_key'];
            }
            if (!empty($updates)) {
                $this->CI->db->where('id', 1)->update('db_sitesettings', $updates);
            }
        } catch (Exception $e) {
            log_message('error', 'Updater applyManifestSettings failed: ' . $e->getMessage());
        }
    }

    protected function getSitesetting(string $col): string {
        try {
            if (!$this->CI->db->field_exists($col, 'db_sitesettings')) {
                return '';
            }
            $row = $this->CI->db->select($col)->from('db_sitesettings')->where('id', 1)->get()->row();
            return $row ? (string) ($row->{$col} ?? '') : '';
        } catch (Exception $e) {
            return '';
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Preview diff                                                      */
    /* ------------------------------------------------------------------ */

    public function previewChanges(array $manifest): array {
        $files = $manifest['files'] ?? [];
        $toDownload = [];
        $toAdd = [];

        foreach ($files as $file) {
            $path = $file['path'];
            if ($this->isProtected($path)) {
                continue;
            }
            $abs = FCPATH . $path;
            if (!file_exists($abs)) {
                $toAdd[] = $path;
            } else {
                $localHash = hash_file('sha256', $abs);
                if ($localHash !== $file['hash']) {
                    $toDownload[] = $path;
                }
            }
        }

        $migrations = $manifest['migrations'] ?? [];
        if(!empty($migrations) && $this->CI->db->table_exists('db_schema_migrations')){
            $applied = array_column(
                $this->CI->db->select('filename')->get('db_schema_migrations')->result_array(),
                'filename'
            );
            $migrations = array_values(array_diff($migrations, $applied));
        }

        return [
            'files_to_update' => $toDownload,
            'files_to_add' => $toAdd,
            'migrations' => $migrations,
            'total_operations' => count($toDownload) + count($toAdd) + count($migrations),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Job / database state                                              */
    /* ------------------------------------------------------------------ */

    public function startJob(string $fromVersion, string $toVersion): int {
        $this->CI->db->insert('db_system_updates', [
            'store_id' => $this->resolveStoreId(),
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'status' => 'running',
            'current_step' => 1,
            'total_steps' => 8,
            'step_label' => 'Initializing...',
            'log' => "Update started: {$fromVersion} → {$toVersion}\n",
        ]);
        $this->updateRecordId = $this->CI->db->insert_id();
        $this->clearState();
        $this->writeState([
            'record_id' => $this->updateRecordId,
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'step' => 1,
            'step_label' => 'Initializing...',
            'message' => '',
            'done' => false,
            'failed' => false,
            'batch' => 0,
            'total' => 0,
            'files_to_update' => [],
            'files_to_add' => [],
            'migrations' => [],
            'manifest' => [],
        ]);
        return $this->updateRecordId;
    }

    public function getProgress(): ?object {
        $this->failStalledJobs();
        // Prefer the latest DB record; fallback to state file
        $job = $this->CI->db->order_by('id', 'DESC')
            ->limit(1)
            ->get('db_system_updates')
            ->row();

        if (!$job) {
            $state = $this->readState();
            if ($state) {
                return (object) [
                    'status' => $state['failed'] ? 'failed' : ($state['done'] ? 'success' : 'running'),
                    'current_step' => $state['step'],
                    'total_steps' => 8,
                    'step_label' => $state['step_label'] . ($state['message'] ? ' — ' . $state['message'] : ''),
                    'from_version' => $state['from_version'],
                    'to_version' => $state['to_version'],
                    'error_message' => $state['failed'] ? $state['message'] : '',
                    'log' => $state['message'],
                    'completed_at' => null,
                ];
            }
            return null;
        }

        return $job;
    }

    /* ------------------------------------------------------------------ */
    /*  Main step runner (chunked / resumable)                            */
    /* ------------------------------------------------------------------ */

    /**
     * Run (or resume) a step. Each call processes one small batch then returns.
     * The frontend keeps calling with the same step until the returned `done` is true.
     *
     * @param int $step 1-8
     * @param array $manifest Fetched remote manifest
     * @param array $preview Output of previewChanges()
     * @return array {status, message, step, done, failed, progress, total}
     */
    public function runStep(int $step, array $manifest, array $preview): array {
        $this->resetTimer();
        $this->failStalledJobs();

        // We always need a record id. If none, this is the first call to step 1.
        $state = $this->readState();
        if (empty($state)) {
            // A request for any step other than 1 with no stored state means a
            // stray/retried call arrived after cleanup cleared the state.
            // Report done — never spawn a second update job for it.
            if ($step > 1) {
                return [
                    'status' => 'ok',
                    'message' => 'Update already completed.',
                    'step_label' => $this->stepLabel($step),
                    'done' => true,
                    'step' => $step,
                ];
            }
            $this->updateRecordId = 0;
        } else {
            $this->updateRecordId = $state['record_id'] ?? 0;
        }
        $this->ensureJobExists();

        // If the manifest/preview changed (new call), store them in state.
        if (!empty($state)) {
            if (empty($state['manifest'])) {
                $state['manifest'] = $manifest;
                $state['files_to_update'] = $preview['files_to_update'];
                $state['files_to_add'] = $preview['files_to_add'];
                $state['migrations'] = $preview['migrations'];
                $state['total'] = count($preview['files_to_update']) + count($preview['files_to_add']) + count($preview['migrations']);
                $this->writeState($state);
            }
        } else {
            $this->startJob($this->getInstalledVersion(), $manifest['version'] ?? '0.0');
            $state = $this->readState();
            $state['manifest'] = $manifest;
            $state['files_to_update'] = $preview['files_to_update'];
            $state['files_to_add'] = $preview['files_to_add'];
            $state['migrations'] = $preview['migrations'];
            $state['total'] = count($preview['files_to_update']) + count($preview['files_to_add']) + count($preview['migrations']);
            $this->writeState($state);
        }

        // Step mismatch: if frontend is asking for a different step than stored,
        // it usually means the previous step just completed. Move to next.
        if ($state['step'] !== $step) {
            $step = $state['step'];
        }

        try {
            switch ($step) {
                case 1:
                    $result = $this->step1BackupDb($state);
                    break;
                case 2:
                    $result = $this->step2BackupFiles($state);
                    break;
                case 3:
                    $result = $this->step3DownloadFiles($state);
                    break;
                case 4:
                    $result = $this->step4VerifyFiles($state);
                    break;
                case 5:
                    $result = $this->step5ApplyFiles($state);
                    break;
                case 6:
                    $result = $this->step6RunMigrations($state);
                    break;
                case 7:
                    $result = $this->step7Finalize($state);
                    break;
                case 8:
                    $result = $this->step8Cleanup($state);
                    break;
                default:
                    return ['status' => 'error', 'message' => 'Invalid step number'];
            }

            $this->logJob($step, $result['step_label'] ?? $this->stepLabel($step), $result['message'] ?? '');
            $this->updateStateFromResult($state, $result);

            return $result;

        } catch (Exception $e) {
            $this->markJobFailed($e->getMessage());
            $state['failed'] = true;
            $state['message'] = $e->getMessage();
            $this->writeState($state);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'failed' => true,
                'step' => $step,
            ];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  RESTORE                                                           */
    /* ------------------------------------------------------------------ */

    public function restore(): array {
        // Latest job with a backup (failed, running, or success)
        $job = $this->CI->db->where('status', 'failed')
            ->or_where('status', 'running')
            ->or_where('status', 'success')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('db_system_updates')
            ->row();

        if (!$job) {
            return ['status' => 'error', 'message' => 'No recent update job to restore from.'];
        }

        $errors = [];

        // Restore DB
        if (!empty($job->backup_db_path) && file_exists($job->backup_db_path)) {
            if (!$this->backupManager->restoreDatabase($job->backup_db_path)) {
                $errors[] = 'Database restore failed.';
            }
        } else {
            $errors[] = 'Database backup not found. Cannot restore DB.';
        }

        // Restore files
        if (!empty($job->backup_files_path) && file_exists($job->backup_files_path)) {
            if (!$this->backupManager->restoreFiles($job->backup_files_path)) {
                $errors[] = 'File restore failed.';
            }
        } else {
            $errors[] = 'File backup not found. Cannot restore files.';
        }

        if (empty($errors)) {
            $this->CI->db->where('id', $job->id)->update('db_system_updates', [
                'status' => 'restored',
                'completed_at' => date('Y-m-d H:i:s'),
                'error_message' => 'Restored to pre-update state.',
            ]);
            $this->clearState();
            return ['status' => 'success', 'message' => 'System restored successfully.'];
        }

        return ['status' => 'error', 'message' => implode(' ', $errors)];
    }

    /* ------------------------------------------------------------------ */
    /*  STEP HANDLERS                                                     */
    /* ------------------------------------------------------------------ */

    protected function step1BackupDb(array &$state): array {
        $this->resetTimer();
        $this->logJob(1, 'Backup Database', 'Creating SQL dump...');

        $path = $this->backupManager->backupDatabase();
        if (!$path) {
            throw new Exception('Database backup failed. Check backups/ folder permissions.');
        }

        // Save the backup path in the job record for restore
        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'backup_db_path' => $path,
        ]);

        return [
            'status' => 'ok',
            'message' => 'Database backed up: ' . basename($path),
            'step_label' => 'Backup Database',
            'done' => true,
            'step' => 1,
        ];
    }

    protected function step2BackupFiles(array &$state): array {
        $this->resetTimer();
        $this->logJob(2, 'Backup Files', 'Zipping files to be changed...');

        $files = array_merge($state['files_to_update'] ?? [], $state['files_to_add'] ?? []);
        $path = $this->backupManager->backupFiles($files);

        if (!$path) {
            // File backup is not fatal; we still have DB backup.
            $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
                'backup_files_path' => '',
            ]);
            return [
                'status' => 'ok',
                'message' => 'Files backup skipped (ZipArchive not available).',
                'step_label' => 'Backup Files',
                'done' => true,
                'step' => 2,
            ];
        }

        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'backup_files_path' => $path,
        ]);

        return [
            'status' => 'ok',
            'message' => 'Files backed up.',
            'step_label' => 'Backup Files',
            'done' => true,
            'step' => 2,
        ];
    }

    protected function step3DownloadFiles(array &$state): array {
        $this->resetTimer();

        $channel = $this->getUpdateChannelUrl();
        $allFiles = array_merge($state['files_to_update'] ?? [], $state['files_to_add'] ?? []);

        // Determine resume offset
        $offset = $state['batch'] ?? 0;
        $total = count($allFiles);

        if ($offset >= $total) {
            return [
                'status' => 'ok',
                'message' => 'All files downloaded.',
                'step_label' => 'Download Changed Files',
                'done' => true,
                'step' => 3,
                'progress' => $total,
                'total' => $total,
            ];
        }

        $batch = array_slice($allFiles, $offset, $this->batchSize);
        $deadline = microtime(true) + $this->stepTimeBudget;
        $processed = 0;

        foreach ($batch as $relPath) {
            // Hand control back to the browser before the host kills us —
            // the next call resumes from $state['batch'].
            if (microtime(true) >= $deadline) {
                break;
            }
            $this->resetTimer();
            $processed++;
            $remoteUrl = rtrim($channel, '/') . '/' . $relPath;
            $localTemp = $this->tempDir . '/' . $relPath;
            $dir = dirname($localTemp);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            // Download with retry
            $data = $this->httpGet($remoteUrl, 30);
            if ($data === null) {
                if ($this->isNonCritical($relPath)) {
                    $state['files_skipped'][] = $relPath;
                    $this->writeState($state);
                    continue;
                }
                throw new Exception("Failed to download after retries: {$relPath}");
            }

            if (@file_put_contents($localTemp, $data) === false) {
                if ($this->isNonCritical($relPath)) {
                    $state['files_skipped'][] = $relPath;
                    $this->writeState($state);
                    continue;
                }
                throw new Exception("Failed to write temp file: {$relPath}");
            }
        }

        $batchEnd = $offset + $processed;
        $state['batch'] = $batchEnd;
        $this->writeState($state);

        $message = "Downloaded {$batchEnd} / {$total} files";
        $this->logJob(3, 'Download Changed Files', $message);

        return [
            'status' => 'ok',
            'message' => $message,
            'step_label' => 'Download Changed Files',
            'done' => ($batchEnd >= $total),
            'step' => 3,
            'progress' => $batchEnd,
            'total' => $total,
        ];
    }

    protected function step4VerifyFiles(array &$state): array {
        $this->resetTimer();

        $manifest = $state['manifest'] ?? [];
        $manifestMap = [];
        foreach ($manifest['files'] ?? [] as $f) {
            $manifestMap[$f['path']] = $f['hash'];
        }

        $allFiles = array_merge($state['files_to_update'] ?? [], $state['files_to_add'] ?? []);

        // Files that mismatched on earlier passes wait here for the channel's
        // CDN cache to expire (raw.githubusercontent.com caches each URL for
        // ~5 minutes and ignores query strings, so an instant retry serves the
        // same stale bytes — the failure must be deferred, not fatal).
        $pendingMap = $state['hash_pending'] ?? [];
        $queue = !empty($pendingMap) ? array_keys($pendingMap) : $allFiles;
        $total = count($allFiles);
        $queueTotal = count($queue);

        $offset = !empty($pendingMap) ? 0 : ($state['batch'] ?? 0);

        // For step 4, we start from the beginning (we just finished step 3 at $total)
        if ($offset === $total && $state['step'] === 3) {
            $offset = 0;
            $state['batch'] = 0;
        }

        if ($offset >= $queueTotal && empty($pendingMap)) {
            return [
                'status' => 'ok',
                'message' => 'All hashes verified.',
                'step_label' => 'Verify File Integrity',
                'done' => true,
                'step' => 4,
                'progress' => $total,
                'total' => $total,
            ];
        }

        $batch = array_slice($queue, $offset, $this->batchSize);
        foreach ($batch as $relPath) {
            $this->resetTimer();
            $tempPath = $this->tempDir . '/' . $relPath;
            $expected = $manifestMap[$relPath] ?? null;

            $ok = file_exists($tempPath)
                && (!$expected || hash_file('sha256', $tempPath) === $expected);

            // Self-heal: a stale or truncated temp file from an earlier failed
            // run must not doom every retry — re-fetch before deciding. A few
            // short-spaced attempts inside this request cover fast-expiring
            // caches; longer staleness defers to the next poll via hash_pending.
            for ($attempt = 0; !$ok && $attempt < 3; $attempt++) {
                if ($attempt > 0) sleep(2);
                if ($this->redownloadFile($relPath, $tempPath)) {
                    $ok = file_exists($tempPath)
                        && (!$expected || hash_file('sha256', $tempPath) === $expected);
                }
            }

            if (!$ok) {
                if ($this->isNonCritical($relPath)) {
                    @unlink($tempPath);
                    unset($state['hash_pending'][$relPath], $state['hash_retry'][$relPath]);
                    $state['files_skipped'][] = $relPath;
                    $this->writeState($state);
                    continue;
                }
                if (!file_exists($tempPath)) {
                    throw new Exception("Missing downloaded file: {$relPath}");
                }
                $retries = ($state['hash_retry'][$relPath] ?? 0) + 1;
                $state['hash_retry'][$relPath] = $retries;
                if ($retries > 60) {
                    unset($state['hash_pending'][$relPath]);
                    throw new Exception("Hash mismatch for: {$relPath} — the update channel is still serving a cached copy. Wait a few minutes and press Resume; the update continues from this checkpoint.");
                }
                $state['hash_pending'][$relPath] = true;
                continue;
            }
            unset($state['hash_pending'][$relPath], $state['hash_retry'][$relPath]);
        }

        $stillPending = !empty($state['hash_pending']);
        // Advance the main-pass offset only while draining the full list —
        // pending-mode passes must not move it or the unverified tail of
        // $allFiles would be skipped once the pending files finally match.
        $batchEnd = $total;
        if (empty($pendingMap)) {
            $batchEnd = min($offset + count($batch), $total);
            $state['batch'] = $batchEnd;
        }
        $this->writeState($state);

        $verifiedCount = $total - count($state['hash_pending'] ?? []);
        $message = $stillPending
            ? "Verified {$verifiedCount} / {$total} files — waiting for the update channel cache to refresh (this clears itself; keep this page open)"
            : "Verified {$batchEnd} / {$total} files";
        $this->logJob(4, 'Verify File Integrity', $message);

        return [
            'status' => 'ok',
            'message' => $message,
            'step_label' => 'Verify File Integrity',
            'done' => false,
            'step' => 4,
            'progress' => $verifiedCount,
            'total' => $total,
        ];
    }

    protected function step5ApplyFiles(array &$state): array {
        $this->resetTimer();

        $allFiles = array_merge($state['files_to_update'] ?? [], $state['files_to_add'] ?? []);
        $total = count($allFiles);

        // Reset offset if we just finished step 4 at $total
        $offset = $state['batch'] ?? 0;
        if ($offset === $total && $state['step'] === 4) {
            $offset = 0;
            $state['batch'] = 0;
        }

        if ($offset >= $total) {
            return [
                'status' => 'ok',
                'message' => 'All file changes applied.',
                'step_label' => 'Apply File Changes',
                'done' => true,
                'step' => 5,
                'progress' => $total,
                'total' => $total,
            ];
        }

        $skipped = array_flip($state['files_skipped'] ?? []);
        $batch = array_slice($allFiles, $offset, $this->batchSize);
        foreach ($batch as $relPath) {
            $this->resetTimer();
            if ($this->isProtected($relPath) || isset($skipped[$relPath])) {
                continue;
            }
            $source = $this->tempDir . '/' . $relPath;
            $target = FCPATH . $relPath;
            $dir = dirname($target);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!@copy($source, $target)) {
                throw new Exception("Failed to apply file: {$relPath}");
            }
        }

        $batchEnd = min($offset + count($batch), $total);
        $state['batch'] = $batchEnd;
        $this->writeState($state);

        $message = "Applied {$batchEnd} / {$total} files";
        $this->logJob(5, 'Apply File Changes', $message);

        return [
            'status' => 'ok',
            'message' => $message,
            'step_label' => 'Apply File Changes',
            'done' => ($batchEnd >= $total),
            'step' => 5,
            'progress' => $batchEnd,
            'total' => $total,
        ];
    }

    protected function step6RunMigrations(array &$state): array {
        $this->resetTimer();

        $manifest = $state['manifest'] ?? [];
        $migrations = $state['migrations'] ?? [];
        $channel = $this->getUpdateChannelUrl();

        $offset = $state['batch'] ?? 0;
        $total = count($migrations);

        if ($offset >= $total) {
            return [
                'status' => 'ok',
                'message' => 'Migrations completed.',
                'step_label' => 'Run Database Migrations',
                'done' => true,
                'step' => 6,
                'progress' => $total,
                'total' => $total,
            ];
        }

        $migrationFile = $migrations[$offset];

        // Skip if already applied
        $already = $this->CI->db->where('filename', $migrationFile)
            ->where('version', $manifest['version'])
            ->get('db_schema_migrations')
            ->num_rows();
        if ($already > 0) {
            $state['batch'] = $offset + 1;
            $this->writeState($state);
            return [
                'status' => 'ok',
                'message' => 'Skipped ' . $migrationFile . ' (already applied).',
                'step_label' => 'Run Database Migrations',
                'done' => false,
                'step' => 6,
                'progress' => $offset + 1,
                'total' => $total,
            ];
        }

        $sqlUrl = rtrim($channel, '/') . '/migrations/' . $migrationFile . '?t=' . time();
        $sql = $this->httpGet($sqlUrl, 60);
        if ($sql === null) {
            throw new Exception("Failed to fetch migration: {$migrationFile}");
        }

        $this->CI->db->trans_start();
        $statements = $this->splitSql($sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (empty($stmt)) continue;
            $result = @$this->CI->db->query($stmt);
            if ($result === false) {
                $error = $this->CI->db->error();
                $benign = (stripos($error['message'], 'Duplicate') !== false)
                       || (stripos($error['message'], 'already exists') !== false)
                       || (stripos($error['message'], 'Duplicate entry') !== false);
                if (!$benign) {
                    $this->CI->db->trans_rollback();
                    throw new Exception("Migration failed [{$migrationFile}]: " . $error['message']);
                }
            }
        }
        $this->CI->db->trans_complete();

        $this->CI->db->insert('db_schema_migrations', [
            'version' => $manifest['version'],
            'filename' => $migrationFile,
        ]);

        $state['batch'] = $offset + 1;
        $this->writeState($state);

        $message = 'Ran migration ' . ($offset + 1) . ' / ' . $total . ': ' . $migrationFile;
        $this->logJob(6, 'Run Database Migrations', $message);

        return [
            'status' => 'ok',
            'message' => $message,
            'step_label' => 'Run Database Migrations',
            'done' => ($offset + 1 >= $total),
            'step' => 6,
            'progress' => $offset + 1,
            'total' => $total,
        ];
    }

    protected function step7Finalize(array &$state): array {
        $this->resetTimer();

        $newVersion = $state['to_version'] ?? '0.0';
        $this->CI->db->where('id', 1)->update('db_sitesettings', [
            'version' => $newVersion,
        ]);

        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'status' => 'success',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'ok',
            'message' => "Updated to {$newVersion}.",
            'step_label' => 'Finalize Update',
            'done' => true,
            'step' => 7,
        ];
    }

    protected function step8Cleanup(array &$state): array {
        $this->resetTimer();

        if (is_dir($this->tempDir)) {
            $this->rrmdir($this->tempDir);
        }
        $this->backupManager->cleanup(3);

        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'status' => 'success',
            'current_step' => 8,
            'step_label' => 'Update complete',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->clearState();

        return [
            'status' => 'ok',
            'message' => 'Update complete',
            'step_label' => 'Cleanup',
            'done' => true,
            'step' => 8,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  STATE HELPERS                                                     */
    /* ------------------------------------------------------------------ */

    public function getPersistedState(): ?array {
        return $this->readState();
    }

    protected function readState(): ?array {
        if (file_exists($this->statePath)) {
            $json = file_get_contents($this->statePath);
            $data = json_decode($json, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return null;
    }

    protected function writeState(array $state): void {
        @file_put_contents($this->statePath, json_encode($state, JSON_PRETTY_PRINT));
    }

    protected function clearState(): void {
        if (file_exists($this->statePath)) {
            @unlink($this->statePath);
        }
    }

    protected function updateStateFromResult(array &$state, array $result): void {
        $state['step'] = $result['step'] ?? $state['step'];
        $state['step_label'] = $result['step_label'] ?? $this->stepLabel($state['step']);
        $state['message'] = $result['message'] ?? '';
        $state['done'] = $result['done'] ?? false;

        if ($result['done']) {
            $state['batch'] = 0; // Reset batch for next step
            $state['step'] = min($state['step'] + 1, 8);
        }

        $this->writeState($state);
    }

    /* ------------------------------------------------------------------ */
    /*  DB job helpers                                                    */
    /* ------------------------------------------------------------------ */

    protected function logJob(int $step, string $label, string $message = ''): void {
        if ($this->updateRecordId <= 0) {
            return;
        }
        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'current_step' => $step,
            'step_label' => $label . ($message ? " — {$message}" : ''),
            'log' => $message,
        ]);
        // Terminal steps (finalize/cleanup) already wrote success — never
        // regress a finished job back to running, or the watchdog will mark
        // it failed ten minutes after it actually completed.
        $this->CI->db->where('id', $this->updateRecordId)
            ->where_not_in('status', ['success', 'restored'])
            ->update('db_system_updates', ['status' => 'running']);
    }

    protected function ensureJobExists(): void {
        if ($this->updateRecordId > 0) {
            return;
        }
        $job = $this->CI->db->order_by('id', 'DESC')
            ->limit(1)
            ->get('db_system_updates')
            ->row();
        if ($job) {
            $this->updateRecordId = $job->id;
        }
    }

    protected function markJobFailed(string $message): void {
        if ($this->updateRecordId <= 0) {
            return;
        }
        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'status' => 'failed',
            'error_message' => $message,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function stepLabel(int $step): string {
        $map = [
            1 => 'Backup Database',
            2 => 'Backup Files',
            3 => 'Download Changed Files',
            4 => 'Verify File Integrity',
            5 => 'Apply File Changes',
            6 => 'Run Database Migrations',
            7 => 'Finalize Update',
            8 => 'Cleanup',
        ];
        return $map[$step] ?? 'Unknown';
    }

    /* ------------------------------------------------------------------ */
    /*  Network / misc                                                    */
    /* ------------------------------------------------------------------ */

    protected function httpGet(string $url, int $timeout = 30): ?string {
        $attempts = 0;
        $maxAttempts = 3;
        $lastError = '';
        $lastHttpStatus = '';

        while ($attempts < $maxAttempts) {
            $attempts++;
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => $timeout,
                    'user_agent' => 'MartPointUpdater/1.0',
                    'follow_location' => 1,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $data = @file_get_contents($url, false, $ctx);
            if ($data !== false) {
                return $data;
            }

            // Capture the HTTP status line if available (set by PHP in $http_response_header)
            if (isset($http_response_header) && is_array($http_response_header) && !empty($http_response_header[0])) {
                $lastHttpStatus = $http_response_header[0];
            }
            $lastError = error_get_last()['message'] ?? ($lastHttpStatus ?: 'unknown');
            if ($attempts < $maxAttempts) {
                sleep(min($attempts, 3)); // Backoff: 1s, 2s, 3s
            }
        }

        $detail = $lastHttpStatus ?: $lastError;
        log_message('error', "Updater httpGet failed for {$url}: {$detail}");
        $this->lastManifestError = "Could not download {$url} — {$detail}";
        return null;
    }

    // Minimal POST helper for the fleet heartbeat — no retry loop, telemetry
    // must never hold up the request that triggered it.
    protected function httpPost(string $url, array $payload, int $timeout = 8): ?string {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => $timeout,
                'user_agent' => 'MartPointUpdater/1.0',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $data = @file_get_contents($url, false, $ctx);
        return $data === false ? null : $data;
    }

    protected function getUpdateChannelUrl(): string {
        try {
            $row = $this->CI->db->select('update_channel_url')
                ->from('db_sitesettings')
                ->where('id', 1)
                ->get()
                ->row();
            $url = $row ? ($row->update_channel_url ?? '') : '';
        } catch (Exception $e) {
            $url = '';
        }
        if (empty($url)) {
            return 'https://raw.githubusercontent.com/avariodigitals/martpoint-retail-releases/main/releases/latest';
        }
        return $url;
    }

    protected function isProtected(string $path): bool {
        foreach ($this->protectedPaths as $protected) {
            if (strpos($path, $protected) === 0) {
                return true;
            }
        }
        return false;
    }

    // Non-executable content (docs/guides) must never block a code update:
    // a stale or missing doc is skipped with a warning, not fatal.
    protected function isNonCritical(string $path): bool {
        return strpos($path, 'docs/') === 0;
    }

    // A killed request leaves status='running' forever (state was never
    // written). Any "running" job with no DB write for 10+ minutes is dead —
    // mark it failed so the UI shows the truth; the persisted state file lets
    // a retry resume from the last checkpoint.
    protected function failStalledJobs(): void {
        if (!$this->CI->db->field_exists('updated_at', 'db_system_updates')) {
            return;
        }
        $staleBefore = date('Y-m-d H:i:s', time() - 600);
        $this->CI->db->where('status', 'running')
            ->where('updated_at <', $staleBefore)
            ->update('db_system_updates', [
                'status' => 'failed',
                'error_message' => 'Update stalled — the server stopped responding mid-step. Retry the update; it resumes from the last checkpoint.',
            ]);
    }

    // Re-fetch a single file from the update channel into the temp dir.
    // Used by verify to repair stale/truncated downloads from earlier runs.
    // The cache-busting query forces raw.githubusercontent.com (and any CDN
    // in front of the channel) off its cached copy — without it the repair
    // re-download returns the same stale bytes that just failed the hash check.
    protected function redownloadFile(string $relPath, string $tempPath): bool {
        $channel = $this->getUpdateChannelUrl();
        $url = rtrim($channel, '/') . '/' . $relPath . '?rb=' . time() . mt_rand(1000, 9999);
        $data = $this->httpGet($url, 30);
        if ($data === null) {
            return false;
        }
        $dir = dirname($tempPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return @file_put_contents($tempPath, $data) !== false;
    }

    protected function resetTimer(): void {
        @set_time_limit(120);
        @ini_set('max_execution_time', 120);
        // Tell the browser we are still alive
        if (function_exists('flush') && !headers_sent()) {
            @ob_flush();
            @flush();
        }
    }

    protected function splitSql(string $sql): array {
        $statements = [];
        $current = '';
        $len = strlen($sql);

        $inQuote = false;
        $quoteChar = '';
        $inLineComment = false;
        $inBlockComment = false;
        $delimiter = ';';
        $delimLen = strlen($delimiter);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $nextChar = ($i + 1 < $len) ? $sql[$i + 1] : '';

            // End of line comment
            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            // End of block comment
            if ($inBlockComment) {
                if ($char === '*' && $nextChar === '/') {
                    $inBlockComment = false;
                    $i++; // skip the '/'
                }
                continue;
            }

            // Start of comments (not inside a quote)
            if (!$inQuote) {
                if ($char === '-' && $nextChar === '-') {
                    $inLineComment = true;
                    $i++; // skip second '-'
                    continue;
                }
                if ($char === '/' && $nextChar === '*') {
                    $inBlockComment = true;
                    $i++; // skip the '*'
                    continue;
                }
            }

            // Quote handling
            if (!$inQuote && ($char === "'" || $char === '`' || $char === '"')) {
                $inQuote = true;
                $quoteChar = $char;
            } elseif ($inQuote && $char === $quoteChar) {
                // SQL escapes quotes by doubling them (e.g. '' inside '' or `` inside ``)
                if ($nextChar === $quoteChar) {
                    $current .= $char . $nextChar;
                    $i++; // skip doubled quote
                    continue;
                }
                $inQuote = false;
                $quoteChar = '';
            }

            $current .= $char;

            // DELIMITER is a mysql client command (not a server statement).
            // When a complete DELIMITER line is seen, switch terminator and discard.
            if (!$inQuote && !$inLineComment && !$inBlockComment && $char === "\n") {
                $line = trim(substr($current, 0, -1)); // drop the newline
                if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $m)) {
                    $delimiter = $m[1];
                    $delimLen = strlen($delimiter);
                    $current = '';
                    continue;
                }
            }

            // Statement split when the current delimiter is found outside quotes/comments
            if (!$inQuote && !$inLineComment && !$inBlockComment && $delimLen > 0) {
                if (substr($current, -$delimLen) === $delimiter) {
                    $stmt = substr($current, 0, -$delimLen);
                    $stmt = trim($stmt);
                    if ($stmt !== '') {
                        $statements[] = $stmt;
                    }
                    $current = '';
                    continue;
                }
            }
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }
        return $statements;
    }

    protected function rrmdir(string $dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') continue;
                $path = $dir . '/' . $object;
                if (is_dir($path)) {
                    $this->rrmdir($path);
                } else {
                    @unlink($path);
                }
            }
            @rmdir($dir);
        }
    }
}
