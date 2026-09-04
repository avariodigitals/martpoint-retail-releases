<?php
// One-time fix for broken manifest.json
$src = __DIR__ . '/manifest2.json';
$dst = __DIR__ . '/manifest.json';
if(file_exists($src)){
    copy($src, $dst);
    echo "manifest.json fixed!\n";
    echo "Content:\n";
    echo file_get_contents($dst);
} else {
    echo "manifest2.json not found. Creating fresh manifest.json...\n";
    $manifest = [
        "name" => "MartPoint Retail",
        "short_name" => "MartPoint",
        "description" => "Retail Management and POS System",
        "start_url" => "/dashboard",
        "display" => "standalone",
        "background_color" => "#ffffff",
        "theme_color" => "#0B1120",
        "orientation" => "any",
        "scope" => "/",
        "icons" => [
            ["src" => "uploads/site/icon.webp", "sizes" => "192x192", "type" => "image/webp", "purpose" => "any maskable"],
            ["src" => "uploads/site/default.png", "sizes" => "512x512", "type" => "image/png", "purpose" => "any maskable"]
        ]
    ];
    file_put_contents($dst, json_encode($manifest, JSON_PRETTY_PRINT));
    echo file_get_contents($dst);
}
