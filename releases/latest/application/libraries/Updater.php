<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Updater — Core auto-update orchestrator for MartPoint Retail
 * Handles: manifest fetch, hash diff, download, apply, migrate, restore
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

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('BackupManager');
        $this->backupManager = $this->CI->backupmanager;
        $this->CI->load->helper('file');
    }

    /**
     * Get current installed version from db_sitesettings
     */
    public function getInstalledVersion(): string {
        $row = $this->CI->db->select('version')
            ->from('db_sitesettings')
            ->where('id', 1)
            ->get()
            ->row();
        return $row ? $row->version : '0.0';
    }

    /**
     * Fetch remote manifest from GitHub / update channel
     */
    public function fetchManifest(): ?array {
        $channel = $this->getUpdateChannelUrl();
        $manifestUrl = rtrim($channel, '/') . '/release-manifest.json';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'MartPointUpdater/1.0',
                'follow_location' => 1,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $json = @file_get_contents($manifestUrl, false, $ctx);
        if ($json === false) {
            return null;
        }

        $manifest = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $manifest;
    }

    /**
     * Check if an update is available
     */
    public function checkForUpdate(): array {
        $installed = $this->getInstalledVersion();
        $manifest = $this->fetchManifest();

        if (!$manifest) {
            return [
                'available' => false,
                'error' => 'Unable to fetch release manifest. Check your update channel URL.',
                'installed_version' => $installed,
                'remote_version' => null,
            ];
        }

        $remote = $manifest['version'] ?? '0.0';
        $available = version_compare($remote, $installed, '>');

        return [
            'available' => $available,
            'installed_version' => $installed,
            'remote_version' => $remote,
            'release_date' => $manifest['release_date'] ?? null,
            'changelog' => $manifest['changelog'] ?? 'No changelog provided.',
            'manifest' => $manifest,
        ];
    }

    /**
     * Preview what will change (without downloading yet)
     */
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

        return [
            'files_to_update' => $toDownload,
            'files_to_add' => $toAdd,
            'migrations' => $manifest['migrations'] ?? [],
            'total_operations' => count($toDownload) + count($toAdd) + count($manifest['migrations'] ?? []),
        ];
    }

    /**
     * Start a new update job record
     */
    public function startJob(string $fromVersion, string $toVersion): int {
        $this->CI->db->insert('db_system_updates', [
            'store_id' => get_current_store_id(),
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'status' => 'pending',
            'current_step' => 0,
            'total_steps' => 8,
            'step_label' => 'Initializing...',
            'log' => "Update started: {$fromVersion} → {$toVersion}\n",
        ]);
        $this->updateRecordId = $this->CI->db->insert_id();
        return $this->updateRecordId;
    }

    /**
     * Execute one step of the update (called via AJAX sequentially)
     * Steps: 1=backup_db, 2=backup_files, 3=download, 4=verify, 5=apply_files, 6=migrate, 7=finalize, 8=cleanup
     */
    public function runStep(int $step, array $manifest, array $preview): array {
        $this->ensureJobExists();
        $this->logStep($step, 'running');

        try {
            switch ($step) {
                case 1:
                    return $this->step1BackupDb();
                case 2:
                    return $this->step2BackupFiles($preview);
                case 3:
                    return $this->step3DownloadFiles($manifest, $preview);
                case 4:
                    return $this->step4VerifyFiles($manifest, $preview);
                case 5:
                    return $this->step5ApplyFiles($preview);
                case 6:
                    return $this->step6RunMigrations($manifest);
                case 7:
                    return $this->step7Finalize($manifest);
                case 8:
                    return $this->step8Cleanup();
                default:
                    return ['status' => 'error', 'message' => 'Invalid step number'];
            }
        } catch (Exception $e) {
            $this->logStep($step, 'failed', $e->getMessage());
            $this->markJobFailed($e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage(), 'fatal' => true];
        }
    }

    /**
     * Restore from backup (triggered on failure or manual request)
     */
    public function restore(): array {
        $job = $this->CI->db->where('status', 'failed')
            ->or_where('status', 'running')
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
        }

        // Restore files
        if (!empty($job->backup_files_path) && file_exists($job->backup_files_path)) {
            if (!$this->backupManager->restoreFiles($job->backup_files_path)) {
                $errors[] = 'File restore failed.';
            }
        }

        if (empty($errors)) {
            $this->CI->db->where('id', $job->id)->update('db_system_updates', [
                'status' => 'restored',
                'completed_at' => date('Y-m-d H:i:s'),
                'error_message' => 'Restored to pre-update state.',
            ]);
            return ['status' => 'success', 'message' => 'System restored successfully.'];
        }

        return ['status' => 'error', 'message' => implode(' ', $errors)];
    }

    /**
     * Get current job progress for polling
     */
    public function getProgress(): ?object {
        return $this->CI->db->order_by('id', 'DESC')
            ->limit(1)
            ->get('db_system_updates')
            ->row();
    }

    /* ================================================================ */
    /*  PRIVATE STEP HANDLERS                                           */
    /* ================================================================ */

    protected function step1BackupDb(): array {
        $this->logStep(1, 'running', 'Backing up database...');
        $path = $this->backupManager->backupDatabase();
        if (!$path) {
            throw new Exception('Database backup failed. Check folder permissions on backups/');
        }
        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'backup_db_path' => $path,
        ]);
        $this->logStep(1, 'success', 'Database backed up: ' . basename($path));
        return ['status' => 'ok', 'message' => 'Database backed up'];
    }

    protected function step2BackupFiles(array $preview): array {
        $this->logStep(2, 'running', 'Backing up files...');
        $files = array_merge($preview['files_to_update'], $preview['files_to_add']);
        $path = $this->backupManager->backupFiles($files);
        if ($path) {
            $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
                'backup_files_path' => $path,
            ]);
        }
        $this->logStep(2, 'success', 'Files backed up.');
        return ['status' => 'ok', 'message' => 'Files backed up'];
    }

    protected function step3DownloadFiles(array $manifest, array $preview): array {
        $this->logStep(3, 'running', 'Downloading changed files...');
        $channel = $this->getUpdateChannelUrl();
        $allFiles = array_merge($preview['files_to_update'], $preview['files_to_add']);
        $tempDir = FCPATH . 'updates/temp';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        foreach ($allFiles as $relPath) {
            $remoteUrl = rtrim($channel, '/') . '/' . $relPath;
            $localTemp = $tempDir . '/' . $relPath;
            $dir = dirname($localTemp);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $ctx = stream_context_create([
                'http' => ['timeout' => 30, 'user_agent' => 'MartPointUpdater/1.0'],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);

            $data = @file_get_contents($remoteUrl, false, $ctx);
            if ($data === false) {
                throw new Exception("Failed to download: {$relPath}");
            }
            if (@file_put_contents($localTemp, $data) === false) {
                throw new Exception("Failed to write temp file: {$relPath}");
            }
        }

        $this->logStep(3, 'success', 'All files downloaded.');
        return ['status' => 'ok', 'message' => 'Files downloaded'];
    }

    protected function step4VerifyFiles(array $manifest, array $preview): array {
        $this->logStep(4, 'running', 'Verifying file hashes...');
        $tempDir = FCPATH . 'updates/temp';
        $manifestMap = [];
        foreach ($manifest['files'] as $f) {
            $manifestMap[$f['path']] = $f['hash'];
        }

        $allFiles = array_merge($preview['files_to_update'], $preview['files_to_add']);
        foreach ($allFiles as $relPath) {
            $tempPath = $tempDir . '/' . $relPath;
            if (!file_exists($tempPath)) {
                throw new Exception("Missing downloaded file: {$relPath}");
            }
            $expected = $manifestMap[$relPath] ?? null;
            if ($expected && hash_file('sha256', $tempPath) !== $expected) {
                throw new Exception("Hash mismatch for: {$relPath}");
            }
        }

        $this->logStep(4, 'success', 'All hashes verified.');
        return ['status' => 'ok', 'message' => 'Hashes verified'];
    }

    protected function step5ApplyFiles(array $preview): array {
        $this->logStep(5, 'running', 'Applying file changes...');
        $tempDir = FCPATH . 'updates/temp';
        $allFiles = array_merge($preview['files_to_update'], $preview['files_to_add']);

        foreach ($allFiles as $relPath) {
            if ($this->isProtected($relPath)) {
                continue;
            }
            $source = $tempDir . '/' . $relPath;
            $target = FCPATH . $relPath;
            $dir = dirname($target);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!@copy($source, $target)) {
                throw new Exception("Failed to apply file: {$relPath}");
            }
        }

        $this->logStep(5, 'success', 'Files applied.');
        return ['status' => 'ok', 'message' => 'Files applied'];
    }

    protected function step6RunMigrations(array $manifest): array {
        $this->logStep(6, 'running', 'Running database migrations...');
        $migrations = $manifest['migrations'] ?? [];
        $channel = $this->getUpdateChannelUrl();

        foreach ($migrations as $migrationFile) {
            $already = $this->CI->db->where('filename', $migrationFile)
                ->where('version', $manifest['version'])
                ->get('db_schema_migrations')
                ->num_rows();
            if ($already > 0) {
                continue;
            }

            $sqlUrl = rtrim($channel, '/') . '/migrations/' . $migrationFile;
            $ctx = stream_context_create([
                'http' => ['timeout' => 30, 'user_agent' => 'MartPointUpdater/1.0'],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $sql = @file_get_contents($sqlUrl, false, $ctx);
            if ($sql === false) {
                throw new Exception("Failed to fetch migration: {$migrationFile}");
            }

            // Run migration statements
            $this->CI->db->trans_start();
            $statements = $this->splitSql($sql);
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt)) continue;
                // Skip errors for "already exists" to make migrations idempotent
                $result = @$this->CI->db->query($stmt);
                if ($result === false) {
                    $error = $this->CI->db->error();
                    // If it's a benign "already exists" error, continue
                    $benign = (strpos($error['message'], 'Duplicate') !== false)
                           || (strpos($error['message'], 'already exists') !== false)
                           || (strpos($error['message'], 'Duplicate entry') !== false);
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
        }

        $this->logStep(6, 'success', 'Migrations completed.');
        return ['status' => 'ok', 'message' => 'Migrations completed'];
    }

    protected function step7Finalize(array $manifest): array {
        $this->logStep(7, 'running', 'Finalizing update...');
        $newVersion = $manifest['version'];
        $this->CI->db->where('id', 1)->update('db_sitesettings', [
            'version' => $newVersion,
        ]);

        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'status' => 'success',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logStep(7, 'success', "Updated to {$newVersion}.");
        return ['status' => 'ok', 'message' => "Updated to {$newVersion}"];
    }

    protected function step8Cleanup(): array {
        $this->logStep(8, 'running', 'Cleaning up...');
        $tempDir = FCPATH . 'updates/temp';
        if (is_dir($tempDir)) {
            $this->rrmdir($tempDir);
        }
        $this->backupManager->cleanup(3);

        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'status' => 'success',
            'current_step' => 8,
            'step_label' => 'Update complete',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        return ['status' => 'ok', 'message' => 'Update complete', 'done' => true];
    }

    /* ================================================================ */
    /*  HELPERS                                                         */
    /* ================================================================ */

    protected function getUpdateChannelUrl(): string {
        $row = $this->CI->db->select('update_channel_url')
            ->from('db_sitesettings')
            ->where('id', 1)
            ->get()
            ->row();
        $url = $row->update_channel_url ?? '';
        if (empty($url)) {
            // Fallback placeholder — user MUST set this in Super Admin UI
            return 'https://raw.githubusercontent.com/YOUR_USERNAME/martpoint-retail-releases/main/releases/latest';
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

    protected function ensureJobExists() {
        if ($this->updateRecordId === 0) {
            $job = $this->CI->db->order_by('id', 'DESC')
                ->limit(1)
                ->get('db_system_updates')
                ->row();
            if ($job) {
                $this->updateRecordId = $job->id;
            }
        }
    }

    protected function logStep(int $step, string $status, string $message = '') {
        $label = $this->stepLabel($step);
        $this->CI->db->where('id', $this->updateRecordId)->update('db_system_updates', [
            'current_step' => $step,
            'step_label' => $label . ($message ? " — {$message}" : ''),
            'log' => $message,
        ]);
    }

    protected function markJobFailed(string $message) {
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
            3 => 'Download Files',
            4 => 'Verify Integrity',
            5 => 'Apply Changes',
            6 => 'Run Migrations',
            7 => 'Finalize Update',
            8 => 'Cleanup',
        ];
        return $map[$step] ?? 'Unknown';
    }

    protected function splitSql(string $sql): array {
        $statements = [];
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            if (!$inQuote && ($char === "'" || $char === '`' || $char === '"')) {
                $inQuote = true;
                $quoteChar = $char;
            } elseif ($inQuote && $char === $quoteChar) {
                if ($i > 0 && $sql[$i - 1] === '\\') {
                    // escaped
                } else {
                    $inQuote = false;
                }
            } elseif (!$inQuote && $char === ';') {
                $statements[] = $current;
                $current = '';
                continue;
            }
            $current .= $char;
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
