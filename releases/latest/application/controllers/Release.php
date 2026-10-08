<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Release Builder — Web interface to build the upload package
 */
class Release extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!mp_is_central()) {
            show_404();
        }
        $this->load_global();
        if (!special_access()) {
            show_error('Access Denied', 403, 'Super Admin Only');
        }
        $this->ensureGithubSchema();
    }

    /**
     * GitHub publish settings live in db_sitesettings — self-heal so no
     * migration is needed on the central install.
     */
    private function ensureGithubSchema(): void {
        try {
            if (!$this->db->table_exists('db_sitesettings')) {
                return;
            }
            foreach (['github_repo', 'github_token', 'github_branch'] as $col) {
                if (!$this->db->field_exists($col, 'db_sitesettings')) {
                    $this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `{$col}` VARCHAR(255) NULL");
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Release github schema check failed: ' . $e->getMessage());
        }
    }

    private function getSetting(string $key): string {
        try {
            if (!$this->db->field_exists($key, 'db_sitesettings')) {
                return '';
            }
            $row = $this->db->select($key)->where('id', 1)->get('db_sitesettings')->row();
            return (string) ($row->{$key} ?? '');
        } catch (Exception $e) {
            return '';
        }
    }

    public function index() {
        $data = $this->data;
        $data['page_title'] = 'Build Release Package';
        $data['gh_repo'] = $this->getSetting('github_repo');
        $data['gh_branch'] = $this->getSetting('github_branch') ?: 'main';
        $data['gh_token_set'] = $this->getSetting('github_token') !== '';
        $data['content'] = $this->load->view('release-builder', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save_github() {
        $repo = substr(trim((string) $this->input->post('github_repo')), 0, 255);
        $branch = substr(trim((string) $this->input->post('github_branch')), 0, 50) ?: 'main';
        $token = trim((string) $this->input->post('github_token'));
        $upd = ['github_repo' => $repo, 'github_branch' => $branch];
        if ($token !== '') {
            $upd['github_token'] = $token;
        }
        $this->db->where('id', 1)->update('db_sitesettings', $upd);
        echo json_encode(['status' => 'ok', 'message' => 'GitHub settings saved.']);
    }

    public function build() {
        // This is the slowest step in the whole release flow and it had NO time
        // limit at all — its siblings build_full() and publish() both set one.
        //
        // The work is not large in BYTES (≈120 MB) but enormous in OPERATIONS:
        // delete the previous tree, then ~5,553 copy() calls each with their own
        // mkdir(). Six thousand-plus filesystem calls in one request blows
        // through a shared host's default max_execution_time (often 30s), and
        // the request dies part-way leaving release_upload/ half-populated —
        // which then looks like "the build failed" with no visible reason.
        //
        // set_time_limit(0) matches the scheduler/update paths, and
        // ignore_user_abort keeps it alive if the browser or a proxy hangs up
        // mid-copy rather than abandoning work that was nearly done.
        @set_time_limit(0);
        @ignore_user_abort(true);

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

        // Directory creation is cached.
        //
        // The loops below previously called @mkdir(dirname($dst), ...) for EVERY
        // file — ~5,553 mkdir syscalls where a few hundred distinct directories
        // exist. On shared hosting the syscall overhead is a large share of the
        // build's wall-clock time, and time is exactly what it runs out of.
        $madeDirs = [];
        $ensureDir = function (string $dir) use (&$madeDirs): void {
            if (isset($madeDirs[$dir])) { return; }
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            $madeDirs[$dir] = true;
        };

        // A failed copy() was previously ignored entirely, so a build that ran
        // out of disk or hit a permission error reported SUCCESS with fewer
        // files than the manifest lists — and every client update would then
        // fail its hash check with no clue why. Failures are collected now and
        // surface as an error.
        $failed = [];

        // Copy migrations
        $migrationCount = 0;
        $sourceMigDir = $sourceDir . 'updates/migrations';
        $destMigDir = $latestDir . '/migrations';
        if (is_dir($sourceMigDir)) {
            foreach ($manifest['migrations'] ?? [] as $migFile) {
                $src = $sourceMigDir . '/' . $migFile;
                $dst = $destMigDir . '/' . $migFile;
                if (file_exists($src)) {
                    $ensureDir(dirname($dst));
                    if (@copy($src, $dst)) {
                        $migrationCount++;
                    } else {
                        $failed[] = 'migrations/' . $migFile;
                    }
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
            'application/logs/',
            'application/cache/',
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
                $ensureDir(dirname($dst));
                if (@copy($src, $dst)) {
                    $filesCount++;
                } else {
                    $failed[] = $relPath;
                }
            }
        }

        if (!empty($failed)) {
            echo json_encode([
                'status' => 'error',
                'version' => $version,
                'files_count' => $filesCount,
                'migrations_count' => $migrationCount,
                'skipped_count' => $skippedCount,
                'failed_count' => count($failed),
                'failed_sample' => array_slice($failed, 0, 10),
                'message' => count($failed) . ' file(s) could not be copied — the package is incomplete and must NOT be published. '
                    . 'Usual cause: not enough disk space in release_upload/, or its permissions. First few: '
                    . implode(', ', array_slice($failed, 0, 5)),
            ]);
            return;
        }

        echo json_encode([
            'status' => 'ok',
            'version' => $version,
            'files_count' => $filesCount,
            'migrations_count' => $migrationCount,
            'skipped_count' => $skippedCount,
            'failed_count' => 0,
            'dirs_created' => count($madeDirs),
            'output_path' => str_replace(FCPATH, '', $uploadDir),
            'message' => "Release package built with {$filesCount} files and {$migrationCount} migrations.",
        ]);
    }

    /**
     * Build the FULL install package (martpoint-full.zip) — everything a fresh
     * deployment needs. The deploy.php launcher streams this via fleet/package.
     */
    public function build_full() {
        @set_time_limit(300);

        if (!class_exists('ZipArchive')) {
            echo json_encode(['status' => 'error', 'message' => 'ZipArchive not available on this server.']);
            return;
        }

        $sourceDir = FCPATH;
        $uploadDir = $sourceDir . 'release_upload/releases/latest';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $zipPath = $uploadDir . '/martpoint-full.zip';

        // Version stamped into the package so fresh installs report the
        // release they shipped with — not whatever the seed files say.
        $stampVersion = null;
        $manifestFile = $sourceDir . 'release_build/release-manifest.json';
        if (is_file($manifestFile)) {
            $m = json_decode((string) @file_get_contents($manifestFile), true);
            $stampVersion = $m['version'] ?? null;
        }

        // Never ship per-install state or release tooling to a new customer.
        $excludePrefixes = [
            'release_build/', 'release_upload/',
            'updates/temp/', '.git/', '.idea/', 'node_modules/', 'guides/',
        ];
        // State/runtime dirs: ship the directory tree + placeholder files
        // (index.html / .htaccess) so fresh installs have writable, unlisted
        // targets — but never the stored file contents (customer/dev data).
        $stateDirs = [
            'uploads/', 'backups/', 'dbbackup/',
            'application/logs/', 'application/cache/',
        ];
        // Vendor-only central tooling — a fresh client install can never
        // become a central server (mp_is_central() also gates these).
        $excludeFiles = [
            'application/config/database.php',
            'application/config/installed.lock',
            'application/config/central.php',
            'application/controllers/Fleet.php',
            'application/controllers/Manifest.php',
            'application/controllers/Release.php',
            'application/views/fleet.php',
            'application/views/manifest-generator.php',
            'application/views/release-builder.php',
            '.env', '.gitignore', 'deploy.php', 'generate_manifest.php',
        ];

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot create zip at ' . $zipPath]);
            return;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $fileInfo) {
            $absPath = $fileInfo->getPathname();
            $relPath = str_replace($sourceDir, '', $absPath);
            $relPath = str_replace('\\', '/', $relPath);

            $skip = false;
            foreach ($excludePrefixes as $prefix) {
                if (strpos($relPath . '/', $prefix) === 0 || strpos($relPath, $prefix) === 0) {
                    $skip = true;
                    break;
                }
            }
            if (!$skip && basename($relPath) === '.DS_Store') {
                $skip = true;
            }
            if (!$skip) {
                foreach ($stateDirs as $stateDir) {
                    if (strpos($relPath . '/', $stateDir) !== 0) {
                        continue;
                    }
                    $base = basename($relPath);
                    $keep = $fileInfo->isDir() || $base === 'index.html' || $base === '.htaccess';
                    // Except the uploads defaults a fresh install references:
                    // seeded logo/avatars, no-logo fallbacks, Assist avatar,
                    // import CSV examples, invoice sample.
                    if (!$keep && strpos($relPath, 'uploads/') === 0) {
                        $depth = substr_count($relPath, '/');
                        $keep = (bool) preg_match('#^uploads/(site|no_logo|bg|assist|company)/#', $relPath . '/')
                            || $depth === 1
                            || (strpos($relPath, 'uploads/users/') === 0 && $depth === 2)
                            || strpos($relPath, 'uploads/csv/examples/') === 0;
                    }
                    if (!$keep) {
                        $skip = true;
                    }
                    break;
                }
            }
            if ($skip || in_array($relPath, $excludeFiles, true)) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $zip->addEmptyDir($relPath);
            } else {
                $content = null;
                if ($stampVersion !== null && $relPath === 'application/helpers/custom_helper.php') {
                    $content = preg_replace(
                        "/return '\d+\.\d+\.\d+(\.\d+)?';/",
                        "return '" . $stampVersion . "';",
                        (string) file_get_contents($absPath), 1
                    );
                } elseif ($stampVersion !== null && $relPath === 'setup/install/includes/db.txt') {
                    // db_sitesettings seed row: (1, 'x.y.z', 'MartPoint Retail', ...)
                    $content = preg_replace(
                        "/\(1, '[^']*', 'MartPoint Retail'/",
                        "(1, '" . $stampVersion . "', 'MartPoint Retail'",
                        (string) file_get_contents($absPath), 1
                    );
                }
                if ($content !== null) {
                    $zip->addFromString($relPath, $content);
                } else {
                    $zip->addFile($absPath, $relPath);
                }
                $count++;
            }
        }
        $zip->close();

        // Stamp Central's own working file too — app_version() on Central is
        // the SAME constant, so without this Central always reports the stale
        // dev version and keeps flagging its own release as an update (and a
        // self-update would pull the older published package over newer work).
        if ($stampVersion !== null) {
            $liveHelper = $sourceDir . 'application/helpers/custom_helper.php';
            if (is_writable($liveHelper)) {
                $stamped = preg_replace(
                    "/return '\d+\.\d+\.\d+(\.\d+)?';/",
                    "return '" . $stampVersion . "';",
                    (string) file_get_contents($liveHelper), 1
                );
                if ($stamped !== null) {
                    @file_put_contents($liveHelper, $stamped);
                }
            }
        }

        echo json_encode([
            'status' => 'ok',
            'files_count' => $count,
            'size_mb' => round(filesize($zipPath) / 1048576, 1),
            'stamped_version' => $stampVersion,
            'message' => "Full package built: {$count} files"
                . ($stampVersion ? ", stamped v{$stampVersion}." : ' — no manifest found, version not stamped.'),
        ]);
    }

    /**
     * Push release_upload/releases/latest/ to the GitHub update-channel repo
     * via the Git Data API — no git binary needed on cPanel. Diffs against
     * the repo's current manifest so only changed files upload, then commits
     * atomically. Large first pushes resume across calls (state file).
     */
    public function publish() {
        @set_time_limit(120);
        $repo = trim($this->getSetting('github_repo'));
        $token = $this->getSetting('github_token');
        $branch = trim($this->getSetting('github_branch')) ?: 'main';
        if ($repo === '' || $token === '') {
            echo json_encode(['status' => 'error', 'message' => 'Set the GitHub repo and access token first.']);
            return;
        }
        $latestDir = FCPATH . 'release_upload/releases/latest';
        $manifestFile = $latestDir . '/release-manifest.json';
        if (!is_file($manifestFile)) {
            echo json_encode(['status' => 'error', 'message' => 'No built package found — run Build Release Package first.']);
            return;
        }
        $manifest = json_decode((string) file_get_contents($manifestFile), true);
        if (!$manifest || empty($manifest['version'])) {
            echo json_encode(['status' => 'error', 'message' => 'Built manifest is unreadable — rebuild the package.']);
            return;
        }

        $stateFile = FCPATH . 'release_build/publish-state.json';
        $state = is_file($stateFile) ? json_decode((string) @file_get_contents($stateFile), true) : null;

        // A state file written by an older build can carry tree entries GitHub
        // refuses (e.g. the old "sha": null deletes, which fail the whole tree
        // with 422 GitRPC::BadObjectState). Resuming such a state replays the
        // bad entries forever. Stamp the state with a schema version and
        // discard anything older, forcing a clean diff.
        $stateSchema = 2;
        if (is_array($state) && (int) ($state['schema'] ?? 0) !== $stateSchema) {
            $state = null;
        }

        if (!is_array($state) || ($state['version'] ?? '') !== $manifest['version']) {
            // Fresh run: diff local manifest against the repo's current one.
            $remoteHashes = [];
            $remoteMigs = [];
            $raw = $this->ghApi('GET', "/repos/{$repo}/contents/releases/latest/release-manifest.json?ref={$branch}", null, $token, true);
            if ($raw !== null) {
                $rm = json_decode($raw, true);
                foreach (($rm['files'] ?? []) as $f) {
                    $remoteHashes[$f['path']] = $f['hash'] ?? '';
                }
                $remoteMigs = $rm['migrations'] ?? [];
            }
            $pending = [];
            $entries = [];
            foreach (($manifest['files'] ?? []) as $f) {
                $p = $f['path'];
                if (($remoteHashes[$p] ?? null) !== ($f['hash'] ?? null)) {
                    $pending[] = $p;
                }
                unset($remoteHashes[$p]);
            }
            $localMigs = $manifest['migrations'] ?? [];
            foreach ($localMigs as $m) {
                $pending[] = 'migrations/' . $m;
            }
            // Paths tracked by the old manifest but gone now → delete remotely.
            //
            // GitHub's tree API deletes an entry by sending sha = null WITH
            // mode + type. A bare {path} entry is rejected with
            // "Must supply either tree.sha or tree.content" (verified).
            foreach (array_keys($remoteHashes) as $gone) {
                $entries[] = ['path' => "releases/latest/{$gone}", 'mode' => '100644', 'type' => 'blob', 'sha' => null];
            }
            foreach ($remoteMigs as $m) {
                if (!in_array($m, $localMigs, true)) {
                    $entries[] = ['path' => "releases/latest/migrations/{$m}", 'mode' => '100644', 'type' => 'blob', 'sha' => null];
                }
            }
            $pending[] = 'release-manifest.json';
            $state = [
                'schema'  => $stateSchema,
                'version' => $manifest['version'],
                'pending' => array_values(array_unique($pending)),
                'entries' => $entries,
                'done'    => 0,
            ];
            $state['total'] = count($state['pending']);
        }

        // Upload up to 80 blobs per call — shared-hosting request budget.
        $batch = array_splice($state['pending'], 0, 80);
        foreach ($batch as $rel) {
            $abs = $latestDir . '/' . $rel;
            $state['done']++;
            if (!is_file($abs)) {
                continue;
            }
            $blob = $this->ghApi('POST', "/repos/{$repo}/git/blobs", [
                'content' => base64_encode((string) file_get_contents($abs)),
                'encoding' => 'base64',
            ], $token);
            if (empty($blob['sha'])) {
                echo json_encode(['status' => 'error', 'message' => "Blob upload failed for {$rel} — check the access token permissions (contents: write)."]);
                return;
            }
            $state['entries'][] = ['path' => "releases/latest/{$rel}", 'mode' => '100644', 'type' => 'blob', 'sha' => $blob['sha']];
        }

        if (!empty($state['pending'])) {
            @mkdir(dirname($stateFile), 0755, true);
            @file_put_contents($stateFile, json_encode($state));
            echo json_encode([
                'status' => 'partial',
                'done' => $state['done'],
                'remaining' => count($state['pending']),
                'total' => $state['total'],
            ]);
            return;
        }

        // Finalize: HEAD ref → new tree → commit → move the branch ref.
        $ref = $this->ghApi('GET', "/repos/{$repo}/git/ref/heads/{$branch}", null, $token);
        $headSha = $ref['object']['sha'] ?? null;
        if (!$headSha) {
            echo json_encode(['status' => 'error', 'message' => "Cannot read branch '{$branch}' on {$repo} — check repo name and token."]);
            return;
        }
        $headCommit = $this->ghApi('GET', "/repos/{$repo}/git/commits/{$headSha}", null, $token);
        $baseTree = $headCommit['tree']['sha'] ?? null;

        // --- Sanitise the entry list before sending it -----------------------
        // GitHub rejects the WHOLE tree (422) if any single entry is invalid:
        //   · a delete (sha = null) for a path that does not exist in the repo
        //     -> docs: "Returns an error if you try to delete a file that does
        //     not exist", surfaced as GitRPC::BadObjectState
        //   · an entry with neither sha nor content
        //     -> "Must supply either tree.sha or tree.content"
        // Rather than let one bad entry poison a 122-file publish, drop the
        // invalid ones and report what was skipped.
        $existing = $this->ghTreePaths($repo, $baseTree, $token);
        $clean = [];
        $droppedDeletes = 0;
        $droppedEmpty = 0;
        foreach ($state['entries'] as $e) {
            $path = (string) ($e['path'] ?? '');
            if ($path === '') { $droppedEmpty++; continue; }
            $isDelete = array_key_exists('sha', $e) && $e['sha'] === null;
            if ($isDelete) {
                // Only delete what actually exists in the base tree.
                if ($existing === null || isset($existing[$path])) {
                    $clean[] = ['path' => $path, 'mode' => '100644', 'type' => 'blob', 'sha' => null];
                } else {
                    $droppedDeletes++;
                }
                continue;
            }
            if (empty($e['sha'])) { $droppedEmpty++; continue; }
            $clean[] = ['path' => $path, 'mode' => '100644', 'type' => 'blob', 'sha' => $e['sha']];
        }

        $treeBody = ['tree' => $clean];
        if ($baseTree) { $treeBody['base_tree'] = $baseTree; }

        $tree = $this->ghApi('POST', "/repos/{$repo}/git/trees", $treeBody, $token);
        if (empty($tree['sha'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tree creation failed on GitHub.'
                    . ($this->ghLastError ? ' Reason: ' . $this->ghLastError : ''),
                'entries' => count($clean),
                'dropped_deletes' => $droppedDeletes,
                'dropped_empty' => $droppedEmpty,
                'base_tree' => $baseTree ?: '(none — empty branch)',
            ]);
            return;
        }
        $commit = $this->ghApi('POST', "/repos/{$repo}/git/commits", [
            'message' => 'Release ' . $state['version'] . ' — published from MartPoint Central',
            'tree' => $tree['sha'],
            'parents' => [$headSha],
        ], $token);        if (empty($commit['sha'])) {
            echo json_encode(['status' => 'error', 'message' => 'Commit creation failed on GitHub.']);
            return;
        }
        $refUpdate = $this->ghApi('PATCH', "/repos/{$repo}/git/refs/heads/{$branch}", ['sha' => $commit['sha']], $token);
        if ($refUpdate === null) {
            echo json_encode(['status' => 'error', 'message' => 'Commit created but branch update failed — token may lack write permission.']);
            return;
        }
        @unlink($stateFile);
        echo json_encode([
            'status' => 'ok',
            'message' => "Published {$state['version']} to {$repo}@{$branch}",
            'commit' => $commit['sha'],
            'files' => $state['total'],
        ]);
    }

    /**
     * Minimal GitHub REST client — Bearer PAT, JSON in/out. With $raw=true it
     * returns the file body (Accept: raw) for manifest fetching.
     */
    private function ghApi(string $method, string $path, ?array $body, string $token, bool $raw = false) {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: ' . ($raw ? 'application/vnd.github.raw+json' : 'application/vnd.github+json'),
            'User-Agent: MartPoint-Central',
            'X-GitHub-Api-Version: 2022-11-28',
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        $ch = curl_init('https://api.github.com' . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        // Record WHY a call failed. Without this the caller can only report a
        // generic "Tree creation failed" and the real GitHub reason (bad SHA,
        // invalid path, permission, rate limit) is lost — which makes a YAML-
        // level bug unguessable from the outside.
        $this->ghLastError = null;
        if ($curlErr !== '') {
            $this->ghLastError = 'cURL: ' . $curlErr;
        } elseif ($code < 200 || $code >= 300) {
            $d = json_decode((string) $resp, true);
            $this->ghLastError = 'HTTP ' . $code . ': ' . (string) ($d['message'] ?? substr((string) $resp, 0, 300));
        }

        if ($raw) {
            return ($code === 200 && $resp !== false) ? $resp : null;
        }
        $data = json_decode((string) $resp, true);
        return ($code >= 200 && $code < 300 && is_array($data)) ? $data : null;
    }

    /** Last GitHub API failure reason, or null. @var string|null */
    private $ghLastError = null;

    /**
     * Every blob path present in a tree, as a path => true set.
     *
     * Used to guard deletions: GitHub's tree API errors if you delete a path
     * that does not exist, and one bad entry fails the entire tree. Returns
     * null when the listing could not be read (caller then keeps all deletes
     * rather than silently dropping them).
     */
    private function ghTreePaths(string $repo, ?string $treeSha, string $token) {
        if (!$treeSha) { return null; }
        // Recursive so nested paths come back as full paths ('releases/latest/x').
        $t = $this->ghApi('GET', "/repos/{$repo}/git/trees/{$treeSha}?recursive=1", null, $token);
        if (!is_array($t) || empty($t['tree'])) { return null; }
        $paths = [];
        foreach ($t['tree'] as $node) {
            if (($node['type'] ?? '') === 'blob' && !empty($node['path'])) {
                $paths[(string) $node['path']] = true;
            }
        }
        return $paths;
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
