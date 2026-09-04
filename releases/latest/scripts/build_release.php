<?php
/**
 * build_release.php — Build a ready-to-upload release folder
 * ==============================================================
 * Run this after generate_manifest.php to create a folder you can
 * drag into GitHub or your other IDE window.
 *
 * Usage (browser or CLI):
 *   php build_release.php
 *
 * This creates:
 *   release_upload/
 *   └── releases/
 *       └── latest/
 *           ├── release-manifest.json
 *           ├── migrations/
 *           │   └── *.sql
 *           └── [all changed files in correct paths]
 *
 * Then just drag the entire 'release_upload/' folder contents into GitHub.
 */

if (PHP_SAPI !== 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    // Allow browser access for convenience
}

$sourceDir = dirname(__DIR__);
$manifestPath = $sourceDir . '/release_build/release-manifest.json';
$uploadDir = $sourceDir . '/release_upload';

if (!file_exists($manifestPath)) {
    echo "❌ Manifest not found at: {$manifestPath}\n";
    echo "Run generate_manifest.php first, then come back here.\n";
    exit(1);
}

$manifest = json_decode(file_get_contents($manifestPath), true);
if (!$manifest) {
    echo "❌ Failed to parse manifest.\n";
    exit(1);
}

$version = $manifest['version'] ?? 'unknown';
$latestDir = $uploadDir . '/releases/latest';

// Clean and recreate
if (is_dir($uploadDir)) {
    rrmdir($uploadDir);
}
@mkdir($latestDir . '/migrations', 0755, true);

echo "=============================================================\n";
echo " MartPoint Release Builder\n";
echo " Version: {$version}\n";
echo "=============================================================\n\n";

// 1. Copy manifest
$destManifest = $latestDir . '/release-manifest.json';
copy($manifestPath, $destManifest);
echo "✅ Copied manifest → releases/latest/release-manifest.json\n";

// 2. Copy migrations
$migrationCount = 0;
$sourceMigDir = $sourceDir . '/updates/migrations';
$destMigDir = $latestDir . '/migrations';
if (is_dir($sourceMigDir)) {
    foreach ($manifest['migrations'] ?? [] as $migFile) {
        $src = $sourceMigDir . '/' . $migFile;
        $dst = $destMigDir . '/' . $migFile;
        if (file_exists($src)) {
            @mkdir(dirname($dst), 0755, true);
            copy($src, $dst);
            echo "✅ Copied migration → releases/latest/migrations/{$migFile}\n";
            $migrationCount++;
        } else {
            echo "⚠️  Migration source not found: {$src}\n";
        }
    }
}

// 3. Copy all files referenced in manifest
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
    
    // Skip protected paths
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

    $src = $sourceDir . '/' . $relPath;
    $dst = $latestDir . '/' . $relPath;
    
    if (file_exists($src)) {
        @mkdir(dirname($dst), 0755, true);
        copy($src, $dst);
        $filesCount++;
    } else {
        echo "⚠️  Source file not found: {$src}\n";
    }
}

echo "\n=============================================================\n";
echo " Build Complete\n";
echo "=============================================================\n";
echo "Files copied:      {$filesCount}\n";
echo "Migrations copied: {$migrationCount}\n";
echo "Protected skipped: {$skippedCount}\n";
echo "Output folder:     {$uploadDir}/\n";
echo "\nNext step:\n";
echo "Drag the 'release_upload/' folder into your GitHub repo.\n";
echo "Or zip it and upload via GitHub web UI.\n";
echo "=============================================================\n";

function rrmdir(string $dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') continue;
            $path = $dir . '/' . $object;
            if (is_dir($path)) {
                rrmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
