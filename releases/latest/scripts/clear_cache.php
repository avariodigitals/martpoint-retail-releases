<?php
// Clear PHP opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "Opcache cleared.<br>";
}

// Clear any CodeIgniter cache files
$cacheDir = __DIR__ . '/application/cache/';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '*.cache');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "CI cache cleared.<br>";
}

echo "Done. You can now delete this file.";
