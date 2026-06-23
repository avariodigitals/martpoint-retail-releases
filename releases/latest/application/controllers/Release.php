<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Release Builder — Web interface to build the upload package
 */
class Release extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        if (!special_access()) {
            show_error('Access Denied', 403, 'Super Admin Only');
        }
    }

    public function index() {
        $data = $this->data;
        $data['page_title'] = 'Build Release Package';
        $this->load->view('release-builder', $data);
    }

    public function build() {
        $sourceDir = FCPATH;
        $manifestPath = $sourceDir . 'release_build/release-manifest.json';
        $uploadDir = $sourceDir . 'release_upload';

        if (!file_exists($manifestPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Manifest not found. Run Manifest Generator first.']);
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!$manifest) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to parse manifest.']);
            return;
        }

        $version = $manifest['version'] ?? 'unknown';
        $latestDir = $uploadDir . '/releases/latest';

        // Clean and recreate
        if (is_dir($uploadDir)) {
            $this->rrmdir($uploadDir);
        }
        @mkdir($latestDir . '/migrations', 0755, true);

        // Copy manifest
        copy($manifestPath, $latestDir . '/release-manifest.json');

        // Copy migrations
        $migrationCount = 0;
        $sourceMigDir = $sourceDir . 'updates/migrations';
        $destMigDir = $latestDir . '/migrations';
        if (is_dir($sourceMigDir)) {
            foreach ($manifest['migrations'] ?? [] as $migFile) {
                $src = $sourceMigDir . '/' . $migFile;
                $dst = $destMigDir . '/' . $migFile;
                if (file_exists($src)) {
                    @mkdir(dirname($dst), 0755, true);
                    copy($src, $dst);
                    $migrationCount++;
                }
            }
        }

        // Copy all files referenced in manifest
        $filesCount = 0;
        $skippedCount = 0;
        $protectedPaths = [
            'application/config/database.php',
            'application/config/config.php',
            'application/config/constants.php',
            'uploads/',
            'backups/',
        ];

        foreach ($manifest['files'] ?? [] as $file) {
            $relPath = $file['path'];
            $isProtected = false;
            foreach ($protectedPaths as $protected) {
                if (strpos($relPath, $protected) === 0) {
                    $isProtected = true;
                    break;
                }
            }
            if ($isProtected) {
                $skippedCount++;
                continue;
            }

            $src = $sourceDir . $relPath;
            $dst = $latestDir . '/' . $relPath;
            if (file_exists($src)) {
                @mkdir(dirname($dst), 0755, true);
                copy($src, $dst);
                $filesCount++;
            }
        }

        echo json_encode([
            'status' => 'ok',
            'version' => $version,
            'files_count' => $filesCount,
            'migrations_count' => $migrationCount,
            'skipped_count' => $skippedCount,
            'output_path' => str_replace(FCPATH, '', $uploadDir),
            'message' => "Release package built with {$filesCount} files and {$migrationCount} migrations.",
        ]);
    }

    private function rrmdir(string $dir) {
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
