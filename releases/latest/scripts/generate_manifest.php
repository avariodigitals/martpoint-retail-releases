<?php
$projectRoot = dirname(__DIR__);
/**
 * generate_manifest.php — MartPoint Release Manifest Generator
 * ==============================================================
 * Run this from the project root BEFORE pushing a release to GitHub.
 *
 * Usage:
 *   php generate_manifest.php 3.10.0
 *
 * This script will:
 * 1. Scan application/, theme/, system/, vendor/ for changed files
 * 2. Compute SHA256 hashes for every file
 * 3. Generate release-manifest.json
 * 4. Detect new migration SQL files in updates/migrations/
 * 5. Output a ready-to-upload release package
 *
 * After running, upload the generated files to your GitHub releases repo:
 *   releases/latest/release-manifest.json
 *   releases/latest/martpoint-v{VERSION}.zip
 *   releases/latest/migrations/*.sql
 */

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'cgi-fcgi') {
    exit('Run this from the command line: php generate_manifest.php 3.10.0');
}

$version = $argv[1] ?? '';
if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
    echo "ERROR: Provide a valid semantic version, e.g. php generate_manifest.php 3.10.0\n";
    exit(1);
}

$previousVersion = $argv[2] ?? readline("Previous version (e.g. 3.9.0): ");
if (empty($previousVersion)) {
    echo "ERROR: Previous version is required to detect changed files.\n";
    exit(1);
}

$releaseDir = $projectRoot . '/release_build';
if (!is_dir($releaseDir)) {
    mkdir($releaseDir, 0755, true);
}

// Directories to include in the manifest scan
$scanDirs = [
    'application' => ['php'],
    'theme' => ['php', 'css', 'js', 'html'],
    'system' => ['php'],
    'vendor' => ['php'],
    'index.php' => ['php'],
];

// Paths to NEVER include in the release manifest (protected on client side)
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
        foreach (['index.php', 'sw.js', 'manifest.json', 'offline.html', '.htaccess'] as $rel) {
            $abs = $projectRoot . '/' . $rel;
            if (file_exists($abs)) {
                $files[] = [
                    'path' => $rel,
                    'hash' => hash_file('sha256', $abs),
                    'size' => filesize($abs),
                ];
            }
        }
        continue;
    }

    $basePath = $projectRoot . '/' . $dir;
    if (!is_dir($basePath)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        $absPath = $fileInfo->getPathname();
        $relPath = str_replace($projectRoot . '/', '', $absPath);

        // Skip excluded paths
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

// Detect migration files
$migrations = [];
$migrationDir = $projectRoot . '/updates/migrations';
if (is_dir($migrationDir)) {
    foreach (glob($migrationDir . '/*.sql') as $sqlFile) {
        $migrations[] = basename($sqlFile);
    }
}

$manifest = [
    'version' => $version,
    'previous_version' => $previousVersion,
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
    // signature field added later when RSA signing is implemented in Phase 3
    'signature' => null,
    'changelog' => 'Auto-generated release ' . $version,
];

$manifestPath = $releaseDir . '/release-manifest.json';
file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Also copy migration files into release build
if (!empty($migrations)) {
    $relMigDir = $releaseDir . '/migrations';
    if (!is_dir($relMigDir)) mkdir($relMigDir, 0755, true);
    foreach ($migrations as $mig) {
        copy($migrationDir . '/' . $mig, $relMigDir . '/' . $mig);
    }
}

echo "=============================================================\n";
echo " MartPoint Release Manifest Generated\n";
echo "=============================================================\n";
echo "Version:        {$version}\n";
echo "Previous:       {$previousVersion}\n";
echo "Files scanned:  " . count($files) . "\n";
echo "Migrations:     " . count($migrations) . "\n";
echo "Manifest:       {$manifestPath}\n";
echo "\nNext steps:\n";
echo "1. Review {$manifestPath}\n";
echo "2. Create a ZIP of the changed files (or full release ZIP)\n";
echo "3. Upload to your GitHub releases repo under releases/latest/\n";
echo "=============================================================\n";
