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

    // State file used to resume across HTTP requests.
    protected $statePath;
    protected $tempDir;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('BackupManager');
        $this->backupManager = $this->CI->backupmanager;
        $this->CI->load->helper('file');

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
        $manifestUrl = rtrim($channel, '/') . '/release-manifest.json';

        $json = $this->httpGet($manifestUrl, 60);
        if ($json === null) {
            return null;
        }

        $manifest = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
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

        return [
            'files_to_update' => $toDownload,
            'files_to_add' => $toAdd,
            'migrations' => $manifest['migrations'] ?? [],
            'total_operations' => count($toDownload) + count($toAdd) + count($manifest['migrations'] ?? []),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Job / database state                                              */
    /* ------------------------------------------------------------------ */

    public function startJob(string $fromVersion, string $toVersion): int {
        $this->CI->db->insert('db_system_updates', [
            'store_id' => get_current_store_id(),
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

        // We always need a record id. If none, this is the first call to step 1.
        $state = $this->readState();
        if (empty($state)) {
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
        $batchEnd = min($offset + count($batch), $total);

        foreach ($batch as $relPath) {
            $this->resetTimer();
            $remoteUrl = rtrim($channel, '/') . '/' . $relPath;
            $localTemp = $this->tempDir . '/' . $relPath;
            $dir = dirname($localTemp);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            // Download with retry
            $data = $this->httpGet($remoteUrl, 30);
            if ($data === null) {
                throw new Exception("Failed to download after retries: {$relPath}");
            }

            if (@file_put_contents($localTemp, $data) === false) {
                throw new Exception("Failed to write temp file: {$relPath}");
            }
        }

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
        $offset = $state['batch'] ?? 0;
        $total = count($allFiles);

        // For step 4, we start from the beginning (we just finished step 3 at $total)
        if ($offset === $total && $state['step'] === 3) {
            $offset = 0;
            $state['batch'] = 0;
        }

        if ($offset >= $total) {
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

        $batch = array_slice($allFiles, $offset, $this->batchSize);
        foreach ($batch as $relPath) {
            $this->resetTimer();
            $tempPath = $this->tempDir . '/' . $relPath;
            if (!file_exists($tempPath)) {
                throw new Exception("Missing downloaded file: {$relPath}");
            }
            $expected = $manifestMap[$relPath] ?? null;
            if ($expected && hash_file('sha256', $tempPath) !== $expected) {
                throw new Exception("Hash mismatch for: {$relPath}");
            }
        }

        $batchEnd = min($offset + count($batch), $total);
        $state['batch'] = $batchEnd;
        $this->writeState($state);

        $message = "Verified {$batchEnd} / {$total} files";
        $this->logJob(4, 'Verify File Integrity', $message);

        return [
            'status' => 'ok',
            'message' => $message,
            'step_label' => 'Verify File Integrity',
            'done' => ($batchEnd >= $total),
            'step' => 4,
            'progress' => $batchEnd,
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

        $batch = array_slice($allFiles, $offset, $this->batchSize);
        foreach ($batch as $relPath) {
            $this->resetTimer();
            if ($this->isProtected($relPath)) {
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

        $sqlUrl = rtrim($channel, '/') . '/migrations/' . $migrationFile;
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
            'status' => 'running',
        ]);
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

            $lastError = error_get_last()['message'] ?? 'unknown';
            if ($attempts < $maxAttempts) {
                sleep(min($attempts, 3)); // Backoff: 1s, 2s, 3s
            }
        }

        log_message('error', "Updater httpGet failed for {$url}: {$lastError}");
        return null;
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

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $nextChar = ($i + 1 < $len) ? $sql[$i + 1] : '';
            $nextNextChar = ($i + 2 < $len) ? $sql[$i + 2] : '';

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

            // Statement split
            if (!$inQuote && !$inLineComment && !$inBlockComment && $char === ';') {
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
