<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Manifest Generator — Web-based version of generate_manifest.php
 * Accessible only to Super Admin via browser
 */
class Manifest extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        if (!special_access()) {
            show_error('Access Denied', 403, 'Super Admin Only');
        }
    }

    public function index() {
        $data = $this->data;
        $data['page_title'] = 'Generate Release Manifest';
        $this->load->view('manifest-generator', $data);
    }

    public function generate() {
        $version = trim($this->input->post('version'));
        $previous = trim($this->input->post('previous_version'));

        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid version format. Use x.x.x']);
            return;
        }
        if (empty($previous)) {
            echo json_encode(['status' => 'error', 'message' => 'Previous version is required.']);
            return;
        }

        $releaseDir = FCPATH . 'release_build';
        if (!is_dir($releaseDir)) {
            @mkdir($releaseDir, 0755, true);
        }

        $scanDirs = [
            'application' => ['php'],
            'theme' => ['php', 'css', 'js', 'html'],
            // system/ and vendor/ are excluded — they are CI core and
            // third-party libraries that don't change between MartPoint releases.
            // Existing installations already have them.
            'index.php' => ['php'],
        ];

        $excludePaths = [
            'application/config/database.php',
            'application/config/config.php',
            'application/config/constants.php',
            'uploads/',
            'backups/',
            '.env',
            '.git/',
            '.gitignore',
            '.idea/',
            'release_build/',
            'generate_manifest.php',
            'martpoint_auto_update_v1.sql',
        ];

        $files = [];
        foreach ($scanDirs as $dir => $exts) {
            if ($dir === 'index.php') {
                if (file_exists(FCPATH . 'index.php')) {
                    $files[] = [
                        'path' => 'index.php',
                        'hash' => hash_file('sha256', FCPATH . 'index.php'),
                        'size' => filesize(FCPATH . 'index.php'),
                    ];
                }
                continue;
            }
            $basePath = FCPATH . $dir;
            if (!is_dir($basePath)) continue;
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $fileInfo) {
                $absPath = $fileInfo->getPathname();
                $relPath = str_replace(FCPATH, '', $absPath);
                $skip = false;
                foreach ($excludePaths as $ex) {
                    if (strpos($relPath, $ex) === 0) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;
                $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
                if ($exts !== '*' && !in_array($ext, $exts, true)) continue;
                $files[] = [
                    'path' => $relPath,
                    'hash' => hash_file('sha256', $absPath),
                    'size' => filesize($absPath),
                ];
            }
        }

        $migrations = [];
        $migrationDir = FCPATH . 'updates/migrations';
        if (is_dir($migrationDir)) {
            foreach (glob($migrationDir . '/*.sql') as $sqlFile) {
                $migrations[] = basename($sqlFile);
            }
        }

        $manifest = [
            'version' => $version,
            'previous_version' => $previous,
            'release_date' => date('Y-m-d'),
            'files' => $files,
            'migrations' => $migrations,
            'protected_paths' => [
                'application/config/database.php',
                'application/config/config.php',
                'application/config/constants.php',
                'uploads/',
                'backups/',
            ],
            'signature' => null,
            'changelog' => 'Release ' . $version,
        ];

        $manifestPath = $releaseDir . '/release-manifest.json';
        $saved = file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (!empty($migrations)) {
            $relMigDir = $releaseDir . '/migrations';
            if (!is_dir($relMigDir)) @mkdir($relMigDir, 0755, true);
            foreach ($migrations as $mig) {
                @copy($migrationDir . '/' . $mig, $relMigDir . '/' . $mig);
            }
        }

        if ($saved) {
            echo json_encode([
                'status' => 'ok',
                'manifest_path' => str_replace(FCPATH, base_url(), $manifestPath),
                'files_count' => count($files),
                'migrations_count' => count($migrations),
                'manifest' => $manifest,
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to write manifest. Check folder permissions.']);
        }
    }
}
