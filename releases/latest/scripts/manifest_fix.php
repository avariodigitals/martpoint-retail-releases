<?php
// Overwrite manifest.json with valid JSON
$json = '{
  "name": "MartPoint Retail",
  "short_name": "MartPoint",
  "description": "Retail Management and POS System",
  "start_url": "/dashboard",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#0B1120",
  "orientation": "any",
  "scope": "/",
  "icons": [
    {
      "src": "uploads/site/icon-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "uploads/site/default.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ]
}';
file_put_contents(__DIR__ . '/manifest.json', $json);
echo "manifest.json fixed.\n";
echo "Validate: " . (json_decode($json) ? "VALID JSON" : "INVALID") . "\n";
